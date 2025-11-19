<?php
include __DIR__ . '/../../config/supabase.php';

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'reservasi';

// untuk menangani aksi AJAX

if (isset($_GET['action'])) {
    
    header('Content-Type: application/json');

    $action = $_GET['action'];
    $response = ['success' => false, 'message' => 'Aksi tidak valid.'];

    // untuk mengambil detail reservasi
    if ($action === 'get_detail_reservasi' && isset($_GET['id_reservasi'])) {
    $id_reservasi = $_GET['id_reservasi'];
    $profile_join_hint = 'id_profile:profiles(nama_lengkap,nomor_hp:nomor_telepon)';
    $select_detail = 'id_reservasi,kode_reservasi,tanggal_pendakian,jumlah_pendaki,total_harga,status,status_sampah,' .
                     $profile_join_hint; 
    
    $detail_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi) . '&select=' . urlencode($select_detail);$detail_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi) . '&select=' . urlencode($select_detail);    if (function_exists('supabase_request')) {
            $detailReservasi = supabase_request('GET', $detail_endpoint);
        } else {
            $detailReservasi = null;
        }

        if (!empty($detailReservasi) && !isset($detailReservasi['error'])) {
        $response = ['success' => true, 'data' => $detailReservasi[0]];
    } else {
    $errorMessage = 'Gagal mengambil detail.';
    if (isset($detailReservasi['error'])) {
        $httpCode = $detailReservasi['error']['http_code'] ?? 'N/A';
        $messageDetail = $detailReservasi['error']['message'] ?? 'Pesan error Supabase kosong.';
        $errorMessage = "Supabase Error {$httpCode}: {$messageDetail}";
    } elseif (empty($detailReservasi)) {
        $errorMessage = "Supabase mengembalikan data kosong. Cek id_reservasi atau RLS.";
    }
    $response = ['success' => false, 'message' => $errorMessage];
}

    // untuk mengupdate status reservasi
    } elseif ($action === 'validasi_reservasi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_reservasi = $_POST['id_reservasi'] ?? null;
        if ($id_reservasi && function_exists('supabase_request_patch')) {
            $update_data = json_encode(['status' => 'tervalidasi']);
            $update_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi);
            $result = supabase_request_patch($update_endpoint, $update_data);
            
            if (empty($result) || !isset($result['error'])) {
                 $response = ['success' => true, 'message' => 'Reservasi berhasil divalidasi!'];
            } else {
                 $response = ['success' => false, 'message' => 'Gagal validasi di Supabase. ' . ($result['error']['message'] ?? 'Error tidak diketahui.')];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID Reservasi tidak valid atau fungsi PATCH tidak tersedia.'];
        }
    
    // untuk menghapus reservasi
    } elseif ($action === 'hapus_reservasi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_reservasi = $_POST['id_reservasi'] ?? null;
        if ($id_reservasi && function_exists('supabase_request_delete')) {
            $delete_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi);
            $result = supabase_request_delete($delete_endpoint);
            
            if (empty($result) || !isset($result['error'])) {
                 $response = ['success' => true, 'message' => 'Reservasi berhasil dihapus!'];
            } else {
                 $response = ['success' => false, 'message' => 'Gagal menghapus di Supabase. ' . ($result['error']['message'] ?? 'Error tidak diketahui.')];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID Reservasi tidak valid atau fungsi DELETE tidak tersedia.'];
        }
    }

    echo json_encode($response);
    die(); 
}

// untuk menampilkan halaman reservasi
if ($current_tab === 'reservasi') {
    $kodeBooking = $_GET['kode_booking'] ?? null;
    $namaKetua = $_GET['nama_ketua'] ?? null;
    $select_query = 'id_reservasi,kode_reservasi,tanggal_pendakian,jumlah_pendaki,total_harga,status,profiles(nama_lengkap),status_sampah';
    $endpointSemuaReservasi = 'reservasi?select=' . $select_query . '&order=tanggal_pendakian.desc'; 

    // Panggil API Supabase
    if (function_exists('supabase_request')) {
        $dataAwal = supabase_request('GET', $endpointSemuaReservasi);
    } else {
        $dataAwal = null;
    }

    if (!$dataAwal || isset($dataAwal['error'])) {
        if (isset($dataAwal['error'])) {
            error_log("Gagal memuat semua reservasi dari Supabase. Detail: " . print_r($dataAwal['error'], true));
        }
        $dataAwal = [];
    }
    $dataTabel = $dataAwal;
    $headerTabel = 'Reservasi Keseluruhan';

    // Logika Pencarian
    if ($kodeBooking || $namaKetua) {
        $query_parts = [];
        $search_endpoint = 'reservasi?select=' . $select_query . '&order=tanggal_pendakian.desc';

        if ($kodeBooking) {
            $query_parts[] = 'kode_reservasi=ilike.*' . urlencode($kodeBooking) . '*';
        }
        
        if (!empty($query_parts)) {
            $search_endpoint = 'reservasi?' . implode('&', $query_parts) . '&select=' . $select_query . '&order=tanggal_pendakian.desc';
        }

        if (function_exists('supabase_request')) {
            $dataHasilPencarian = supabase_request('GET', $search_endpoint);
        } else {
            $dataHasilPencarian = null;
        }

        if (!$dataHasilPencarian || isset($dataHasilPencarian['error'])) {
            $dataHasilPencarian = [];
        }
        
        if ($namaKetua) {
            $namaKetuaLower = strtolower($namaKetua);
            $dataHasilPencarian = array_filter($dataHasilPencarian, function($row) use ($namaKetuaLower) {
                $namaLengkap = strtolower($row['profiles']['nama_lengkap'] ?? '');
                return strpos($namaLengkap, $namaKetuaLower) !== false; 
            });
        }
        
        $dataTabel = $dataHasilPencarian;
        $headerTabel = 'Hasil Pencarian';
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reservasi dan Validasi Data</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
</head>
<body>

<div class="content-wrapper">

    <div class="secondary-nav">
        <a href="index.php?page=reservasi&tab=reservasi" 
           class="<?= $current_tab == 'reservasi' ? 'active' : '' ?>" 
           id="nav-reservasi">Reservasi</a>

        <a href="index.php?page=reservasi&tab=tambah" 
           class="<?= $current_tab == 'tambah' ? 'active' : '' ?>" 
           id="nav-tambah-reservasi">Tambah Reservasi</a>
    </div>

    <div id="main-content-area" class="main-content-area">

        <?php if ($current_tab === 'reservasi'): ?>
        
            <div id="reservasi-content">
                
                <div class="search-container">
                    <div class="search-header"> <i class="fa-solid fa-search"></i> Cari Reservasi </div>
                    <form action="index.php" method="GET" class="search-form">
                        <input type="hidden" name="page" value="reservasi">
                        <input type="hidden" name="tab" value="reservasi">
                        <div class="input-group">
                            <label for="kodeBookingInput"><i class="fa-solid fa-hashtag"></i> Kode Booking</label>
                            <input type="text" name="kode_booking" id="kodeBookingInput" class="search-input" placeholder="" value="<?= htmlspecialchars($kodeBooking ?? '') ?>">
                        </div>
                        <div class="input-group">
                            <label for="namaKetuaInput"><i class="fa-solid fa-user-group"></i> Nama Ketua Rombongan</label>
                            <input type="text" name="nama_ketua" id="namaKetuaInput" class="search-input" placeholder="" value="<?= htmlspecialchars($namaKetua ?? '') ?>">
                        </div>
                        <button type="submit" class="btn-cari">
                            <i class="fa-solid fa-search"></i> Cari
                        </button>
                        <?php if ($kodeBooking || $namaKetua): ?>
                            <a href="index.php?page=reservasi&tab=reservasi" class="btn red" style="align-self: flex-end; padding: 10px 15px; height: 42px; line-height: 22px;">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="data-section">
                    <div class="data-header"> <i class="fa-solid fa-calendar-check"></i> <?= htmlspecialchars($headerTabel ?? 'Reservasi Keseluruhan') ?> </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><i class="fa-solid fa-hashtag"></i> Kode Booking</th>
                                    <th><i class="fa-solid fa-user-group"></i> Nama Ketua</th>
                                    <th><i class="fa-solid fa-calendar"></i> Tgl. Pendakian</th>
                                    <th><i class="fa-solid fa-person-hiking"></i> Jumlah </th>
                                    <th><i class="fa-solid fa-money-bill-wave"></i> Status </th>
                                    <th><i class="fa-solid fa-trash-can-arrow-up"></i> Status Sampah</th>
                                    <th><i class="fa-solid fa-gears"></i> AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($dataTabel)): ?>
                                <?php foreach ($dataTabel as $index => $row): ?>
                                    <tr style="--animation-order: <?= $index + 1 ?>;">
                                        <td><?= htmlspecialchars($row['kode_reservasi'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['profiles']['nama_lengkap'] ?? 'N/A') ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['tanggal_pendakian'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars($row['jumlah_pendaki'] ?? '0') ?></td>
                                        <td>
                                            <?php $status = $row['status'] ?? null; $status_class = strtolower(str_replace([' ', '_'], '-', $status ?? 'none')); ?>
                                            <span class="status-badge status-<?= htmlspecialchars($status_class) ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('_',' ',$status ?? 'N/A'))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php $statusSampah = $row['status_sampah'] ?? 'belum_dicek'; $status_class_sampah = strtolower(str_replace([' ', '_'], '-', $statusSampah)); ?>
                                            <span class="status-badge status-<?= htmlspecialchars($status_class_sampah) ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('_',' ',$statusSampah))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button 
                                                class="btn blue btn-validasi icon-only" 
                                                data-id="<?= htmlspecialchars($row['id_reservasi'] ?? '') ?>" 
                                                title="Validasi Data"> 
                                                <i class="fa-solid fa-check-to-slot"></i>
                                            </button>
                                            
                                            <button 
                                                class="btn red btn-hapus icon-only" 
                                                data-id="<?= htmlspecialchars($row['id_reservasi'] ?? '') ?>"
                                                title="Hapus Reservasi"> 
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" style="text-align:center; padding: 20px;">Tidak ada data reservasi yang ditemukan.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-info"> <span>Menampilkan <?= count($dataTabel ?? []) ?> dari <?= count($dataTabel ?? []) ?> entri</span> <div class="pagination-controls"> <button disabled><</button> <button class="btn blue">1</button> <button disabled>></button> </div> </div>
                </div>
            </div>

        <?php elseif ($current_tab === 'tambah'): ?>

            <?php 
            include 'tambah_reservasi.php'; 
            ?>
            
        <?php endif; ?>

    </div>
</div>

<div class="modal-overlay" id="modal-validasi">...</div> 

<script src="../assets/js/reservasi.js"></script> 

</body>
</html>