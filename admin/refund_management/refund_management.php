<?php
// Muat konfigurasi untuk halaman utama (bukan AJAX)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../api/config.php';

if (!function_exists('makeSupabaseRequest')) {
    die("Error: Gagal memuat konfigurasi Supabase atau fungsi makeSupabaseRequest tidak ditemukan.");
}

// Fetch refund requests - Using correct column names from schema
try {
    $endpointDataTabel = 'reservasi?status=eq.pengajuan_refund&select=id_reservasi,kode_reservasi,nominal_refund,bank_refund,no_rek_refund,atas_nama_refund,id_pengguna:profiles(nama_lengkap),dipesan_pada&order=dipesan_pada.desc';
    $responseAwal = makeSupabaseRequest($endpointDataTabel, 'GET');
    $dataAwal = $responseAwal['data'] ?? null;

    if (!$dataAwal || isset($responseAwal['error'])) {
        if (isset($responseAwal['error'])) {
            error_log("Gagal memuat data refund dari Supabase. Detail: " . print_r($responseAwal['error'], true));
        }
        $dataAwal = [];
    }

    // Format data to ensure profile is accessible in 'profiles' key for consistency
    $dataTabel = array_map(function($item) {
        if (isset($item['id_pengguna'])) {
            $item['profiles'] = $item['id_pengguna'];
        }
        return $item;
    }, $dataAwal);

    // Debug: Tampilkan info tentang data yang ditemukan
    error_log("Jumlah data pengajuan refund: " . count($dataTabel));
    if (count($dataTabel) > 0) {
        error_log("Contoh data pertama: " . print_r($dataTabel[0], true));
    }
} catch (Exception $e) {
    error_log("Error saat mengambil data refund: " . $e->getMessage());
    $dataTabel = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Refund</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .content-wrapper {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
            background-color: #f8f9fa;
            min-height: 80vh;
        }

        .main-content-area {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            min-height: 500px;
        }

        .data-section { padding: 0; }
        .data-header {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-container {
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
        }

        .data-table th {
            background-color: #f3f4f6;
            padding: 14px;
            text-align: left;
            font-weight: 700;
            color: #4b5563;
            font-size: 0.9rem;
            white-space: nowrap;
            border-bottom: 2px solid #e5e7eb;
        }

        .data-table td {
            padding: 14px;
            border-bottom: 1px solid #eef;
            vertical-align: middle;
        }

        .data-table tr:hover {
            background-color: #f9f9f9;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .status-pending { background-color: #f59e0b; }
        .status-terkonfirmasi { background-color: #35542E; }
        .status-ditolak { background-color: #ef4444; }
        .status-pembayaran-tertunda { background-color: #3b82f6; }
        .status-pengajuan-refund { background-color: #fbbf24; }
        .status-refund-selesai { background-color: #10b981; }

        .data-table button.btn {
            margin: 0 2px;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .data-table button.blue {
            background-color: #3b82f6;
            color: white;
        }

        .data-table button.blue:hover {
            background-color: #2563eb;
        }

        .pagination-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            font-size: 0.9rem;
            color: #6b7280;
        }

        /* Modal styles */
        .swal2-container {
            z-index: 99999 !important;
            background-color: rgba(0, 0, 0, 0.6) !important;
        }

        .swal2-refund-popup {
            max-width: 800px !important;
            width: 90% !important;
            z-index: 100000 !important;
            text-align: left;
            border-radius: 12px;
            background-color: #f7f9fc !important;
        }

        .swal2-refund-popup .swal2-title {
            font-size: 1.5em !important;
            font-weight: 700 !important;
            color: #1f2937 !important;
            padding-bottom: 1.5rem !important;
            border-bottom: 1px solid #e5e7eb;
            background-color: #ffffff;
            border-radius: 12px 12px 0 0;
            margin: 0 !important;
            padding: 1.5rem !important;
        }

        body:not(.swal2-shown) > .swal2-container {
            display: none !important;
            visibility: hidden !important;
        }

        .swal2-refund-popup .swal2-icon.swal2-info {
            display: none !important;
        }

        .swal2-refund-popup .swal2-html-container {
            max-height: 65vh;
            overflow-y: auto;
            padding: 1.5em !important;
            line-height: 1.5;
            background-color: #f7f9fc;
        }

        .swal2-refund-section {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .swal2-refund-section h4 {
            color: #35542E;
            font-size: 1.25em;
            font-weight: 700;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 1.5rem;
            margin-top: 0;
        }

        .refund-form-group {
            margin-bottom: 1.25rem;
        }

        .refund-form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9em;
            color: #374151;
        }

        .refund-form-group p {
            margin: 0;
            padding: 0.5rem 0;
            font-size: 1em;
            color: #4b5563;
        }

        .refund-form-group input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 0.95em;
            background-color: #ffffff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) inset;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .refund-form-group input[type="file"]:focus {
            border-color: #75B368;
            box-shadow: 0 0 0 3px rgba(117, 179, 104, 0.2);
            outline: none;
        }

        .swal2-actions {
            border-top: 1px solid #e5e7eb;
            background-color: #ffffff;
            border-radius: 0 0 12px 12px;
            padding: 1.5rem !important;
            margin: 0 !important;
            width: 100% !important;
            box-sizing: border-box;
        }

        .swal2-actions button {
            padding: 10px 20px !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
            transition: all 0.2s;
        }

        .swal2-confirm {
            background-color: #35542E !important;
            border: 1px solid #35542E !important;
        }

        .swal2-confirm:hover {
            background-color: #2a4325 !important;
        }

        .swal2-cancel {
            background-color: #ffffff !important;
            color: #374151 !important;
            border: 1px solid #d1d5db !important;
        }

        .swal2-cancel:hover {
            background-color: #f9fafb !important;
        }
    </style>
</head>
<body>

<div class="content-wrapper">
    <div id="main-content-area" class="main-content-area">
        <div class="data-section">
            <div class="data-header">
                <i class="fa-solid fa-undo"></i> Manajemen Refund
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-hashtag"></i> KODE RESERVASI</th>
                            <th><i class="fa-solid fa-user"></i> NAMA PENGGUNA</th>
                            <th><i class="fa-solid fa-university"></i> NAMA BANK</th>
                            <th><i class="fa-solid fa-credit-card"></i> NOMOR REKENING</th>
                            <th><i class="fa-solid fa-user-tag"></i> PEMILIK REKENING</th>
                            <th><i class="fa-solid fa-money-bill-wave"></i> JUMLAH REFUND</th>
                            <th><i class="fa-solid fa-gears"></i> AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dataTabel)): ?>
                            <?php foreach ($dataTabel as $index => $row): ?>
                                <tr style="--animation-order: <?= $index + 1 ?>;">
                                    <td><?= htmlspecialchars($row['kode_reservasi'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        // Check both possible locations for profile data
                                        $profile_data = $row['id_pengguna']['nama_lengkap'] ?? $row['profiles']['nama_lengkap'] ?? 'N/A';
                                        echo htmlspecialchars($profile_data);
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['bank_refund'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['no_rek_refund'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['atas_nama_refund'] ?? 'N/A') ?></td>
                                    <td>Rp <?= htmlspecialchars(number_format($row['nominal_refund'] ?? 0, 0, ',', '.')) ?></td>
                                    <td>
                                        <button
                                            class="btn blue btn-process-refund"
                                            data-id="<?= htmlspecialchars($row['id_reservasi'] ?? '') ?>"
                                            title="Proses Refund">
                                            <i class="fa-solid fa-check"></i> Process
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #6b7280;">
                                    <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                    Tidak ada permintaan refund yang menunggu.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Process refund button event listeners
        document.querySelectorAll('.btn-process-refund').forEach(button => {
            button.addEventListener('click', function() {
                const idReservasi = this.getAttribute('data-id');
                openProcessModal(idReservasi);
            });
        });
    });

    function openProcessModal(idReservasi) {
        // Fetch refund details using the dedicated API endpoint
        fetch('./api/refund_api.php?action=get_refund_detail&id_reservasi=' + idReservasi)
            .then(response => {
                // Check if response is ok before parsing JSON
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showProcessModal(data.data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Check if it's a JSON parsing error
                if (error instanceof SyntaxError) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan format data (kemungkinan respons bukan JSON)',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat mengambil data refund: ' + error.message,
                        confirmButtonText: 'OK'
                    });
                }
            });
    }

    function showProcessModal(refundData) {
        const formattedRefund = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(refundData.nominal_refund || 0);

        Swal.fire({
            title: 'Proses Refund',
            html: `
                <div class="swal2-refund-popup">
                    <div class="swal2-refund-section">
                        <h4>Detail Refund</h4>
                        <div class="refund-form-group">
                            <label>Kode Reservasi</label>
                            <p>${refundData.kode_reservasi || 'N/A'}</p>
                        </div>
                        <div class="refund-form-group">
                            <label>Nama Pengguna</label>
                            <p>${refundData.id_pengguna?.nama_lengkap || refundData.profiles?.nama_lengkap || 'N/A'}</p>
                        </div>
                        <div class="refund-form-group">
                            <label>Nama Bank</label>
                            <p>${refundData.bank_refund || 'N/A'}</p>
                        </div>
                        <div class="refund-form-group">
                            <label>Nomor Rekening</label>
                            <p>${refundData.no_rek_refund || 'N/A'}</p>
                        </div>
                        <div class="refund-form-group">
                            <label>Pemilik Rekening</label>
                            <p>${refundData.atas_nama_refund || 'N/A'}</p>
                        </div>
                        <div class="refund-form-group">
                            <label>Jumlah Refund</label>
                            <p>${formattedRefund}</p>
                        </div>
                    </div>
                    <div class="swal2-refund-section">
                        <h4>Upload Bukti Transfer</h4>
                        <div class="refund-form-group">
                            <label for="bukti_refund">Upload Transfer Proof</label>
                            <input type="file" id="bukti_refund" name="bukti_refund" accept="image/*,.pdf" required>
                            <small>Format file yang diperbolehkan: JPG, JPEG, PNG, GIF, PDF</small>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Proses Refund',
            cancelButtonText: 'Batal',
            width: '800px',
            customClass: {
                popup: 'swal2-refund-popup'
            },
            preConfirm: () => {
                const buktiRefund = document.getElementById('bukti_refund');

                if (!buktiRefund.files.length) {
                    Swal.showValidationMessage('Silakan upload bukti transfer');
                    return false;
                }

                return {
                    buktiRefund: buktiRefund.files[0]
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id_reservasi', refundData.id_reservasi);
                formData.append('bukti_refund', result.value.buktiRefund);

                // Simulate processing
                Swal.fire({
                    title: 'Sedang Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send the form data to the backend
                fetch('./api/refund_api.php?action=process_refund', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Refresh the table
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat memproses refund',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    }
</script>

</body>
</html>