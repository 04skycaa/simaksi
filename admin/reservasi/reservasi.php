<?php
include __DIR__ . '/../api/config.php';

if (!function_exists('makeSupabaseRequest')) {
    die("Error: Gagal memuat konfigurasi Supabase atau fungsi makeSupabaseRequest tidak ditemukan. Periksa path include: " . __DIR__ . '/../api/config.php');
}

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'reservasi';
if (isset($_GET['action'])) {
    
    header('Content-Type: application/json');

    $action = $_GET['action'];
    $response = ['success' => false, 'message' => 'Aksi tidak valid.'];
    if ($action === 'get_detail_reservasi' && isset($_GET['id_reservasi'])) {
        $id_reservasi = $_GET['id_reservasi'];
        $profile_join_hint = 'id_profile:profiles(nama_lengkap,nomor_hp:nomor_telepon)';
        $select_detail = 'id_reservasi,kode_reservasi,tanggal_pendakian,jumlah_pendaki,total_harga,status,status_sampah,' .
                         $profile_join_hint; 
        
        $detail_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi) . '&select=' . urlencode($select_detail);
        $api_response = makeSupabaseRequest($detail_endpoint, 'GET');
        $detailReservasi = $api_response['data'] ?? null;

        if (!empty($detailReservasi) && !isset($api_response['error'])) {
            if (isset($detailReservasi[0])) {
                $response = ['success' => true, 'data' => $detailReservasi[0]];
            } else {
                $response = ['success' => false, 'message' => 'Data reservasi dengan ID tersebut tidak ditemukan.'];
            }
        } else {
            $errorMessage = 'Gagal mengambil detail.';
            if (isset($api_response['error'])) {
                $errorMessage = "Supabase Error: " . (is_array($api_response['error']) ? json_encode($api_response['error']) : $api_response['error']);
            } elseif (empty($detailReservasi)) {
                $errorMessage = "Supabase mengembalikan data kosong. Cek id_reservasi atau RLS.";
            }
            $response = ['success' => false, 'message' => $errorMessage];
        }

    } elseif ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_reservasi = $_POST['id_reservasi'] ?? null;
        $new_status = $_POST['new_status'] ?? null;

        if ($id_reservasi && $new_status && function_exists('makeSupabaseRequest')) {
            if (in_array($new_status, ['terkonfirmasi', 'selesai'])) { 
                $update_data = ['status' => $new_status]; 
                $update_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi);
                $result = makeSupabaseRequest($update_endpoint, 'PATCH', $update_data);
                
                if (empty($result) || !isset($result['error'])) {
                    $response = ['success' => true, 'message' => 'Status berhasil diubah menjadi ' . htmlspecialchars($new_status)];
                } else {
                    $response = ['success' => false, 'message' => 'Gagal update di Supabase. ' . ($result['error']['message'] ?? 'Error tidak diketahui.')];
                }
            } else {
                 $response = ['success' => false, 'message' => 'Status baru tidak valid.'];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID Reservasi atau Status Baru tidak ada.'];
        }

    } elseif ($action === 'validasi_reservasi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_reservasi = $_POST['id_reservasi'] ?? null;
        if ($id_reservasi && function_exists('makeSupabaseRequest')) {
            $update_data = ['status' => 'tervalidasi'];
            $update_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi);
            $result = makeSupabaseRequest($update_endpoint, 'PATCH', $update_data);
            
            if (empty($result) || !isset($result['error'])) {
                    $response = ['success' => true, 'message' => 'Reservasi berhasil divalidasi!'];
            } else {
                    $response = ['success' => false, 'message' => 'Gagal validasi di Supabase. ' . ($result['error']['message'] ?? 'Error tidak diketahui.')];
            }
        } else {
            $response = ['success' => false, 'message' => 'ID Reservasi tidak valid atau fungsi PATCH tidak tersedia.'];
        }

    }

    echo json_encode($response);
    die(); 
}

if ($current_tab === 'reservasi') {
    $kodeBooking = $_GET['kode_booking'] ?? null;
    $namaKetua = $_GET['nama_ketua'] ?? null;

    $select_query = 'id_reservasi,kode_reservasi,tanggal_pendakian,jumlah_pendaki,total_harga,status,profiles(nama_lengkap),status_sampah';
    
    $endpointDataTabel = 'reservasi?';
    $query_parts = [];
    if ($kodeBooking || $namaKetua) {
        
        if ($kodeBooking) {
            $query_parts[] = 'kode_reservasi=ilike.*' . urlencode($kodeBooking) . '*';
        }
        
        if ($namaKetua) {
            $query_parts[] = 'profiles.nama_lengkap=ilike.*' . urlencode($namaKetua) . '*';
        }
        
        $endpointDataTabel .= implode('&', $query_parts);
        $headerTabel = 'Hasil Pencarian';

    } else {
        $headerTabel = 'Reservasi Keseluruhan';
    }

    if (!empty($query_parts)) {
        $endpointDataTabel .= '&';
    }
    
    $endpointDataTabel .= 'select=' . urlencode($select_query) . '&order=tanggal_pendakian.desc'; 
    $responseAwal = makeSupabaseRequest($endpointDataTabel, 'GET');
    $dataAwal = $responseAwal['data'] ?? null;

    if (!$dataAwal || isset($responseAwal['error'])) {
        if (isset($responseAwal['error'])) {
            error_log("Gagal memuat data reservasi dari Supabase. Detail: " . print_r($responseAwal['error'], true));
        }
        $dataAwal = [];
    }

    $dataTabel = $dataAwal;
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
    <style>
        .content-wrapper { padding: 30px; max-width: 1400px; margin: 0 auto; background-color: #f8f9fa; min-height: 80vh; }
        .secondary-nav { 
            display: flex; 
            margin-bottom: 25px; 
            border-bottom: 3px solid #e0e0e0; 
            background-color: #ffffff;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .secondary-nav a { 
            text-decoration: none; 
            color: #555; 
            font-weight: 700; 
            padding: 15px 20px; 
            margin-right: 5px; 
            transition: all 0.3s; 
            border-bottom: 3px solid transparent; 
            font-size: 1rem;
        }
        .secondary-nav a:hover { color: #35542E; }
        .secondary-nav a.active { color: #75B368; border-bottom: 3px solid #75B368; background-color: #f9f9f9; }

        .main-content-area {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            min-height: 500px;
        }
        .search-container { 
            background-color: #f0fdf4;
            border: 1px solid #d1fae5;
            border-radius: 8px; 
            padding: 25px; 
            margin-bottom: 25px; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); 
        }
        .search-header { font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; color: #166534; }
        .search-header i { color: #10b981; margin-right: 10px; }
        .search-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .input-group { flex-grow: 1; min-width: 200px; }
        .input-group label { font-weight: 600; color: #333; }
        .btn-cari { 
            padding: 10px 20px; 
            background-color: #35542E; 
            color: white; 
            border-radius: 6px; 
            transition: background-color 0.2s, transform 0.1s; 
            height: 42px;
        }
        .btn-cari:hover { background-color: #35542E; transform: translateY(-1px); }
        .btn.red { background-color: #ef4444; height: 42px; line-height: 22px; padding: 10px 15px; }
        .btn.red:hover { background-color: #dc2626; }

        .data-section { padding: 0; }
        .data-header { font-size: 1.2rem; font-weight: 700; margin-bottom: 15px; color: #1f2937; }
        .table-container { overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 8px; }
        .data-table { width: 100%; border-collapse: collapse; background-color: #ffffff; }
        .data-table th { background-color: #f3f4f6; padding: 14px; text-align: left; font-weight: 700; color: #4b5563; font-size: 0.9rem; white-space: nowrap; border-bottom: 2px solid #e5e7eb; }
        .data-table td { padding: 14px; border-bottom: 1px solid #eef; vertical-align: middle; }
        .data-table tr:hover { background-color: #f9f9f9; }

        /* Status Badges */
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

        .data-table .btn-aksi-status,
        .data-table .btn-aksi-status:hover,
        .data-table .btn-aksi-status:visited {
            text-decoration: none !important;
        }


        /* Warna Status */
        .status-pending, .status-belum-dicek { background-color: #f59e0b; }
        .status-terkonfirmasi, .status-sudah-dicek { background-color: #35542E; } 
        .status-ditolak, .status-batal { background-color: #ef4444; } 
        .status-pembayaran-tertunda { background-color: #3b82f6; }

        .status-disetujui {
            background-color: #75B368; 
            border: 1px solid #35542E; 
        }

        .status-ditolak {
            background-color: #6b7280; 
            border: 1px solid #4b5563; 
        }

        /* Tombol Aksi */
        .data-table button.btn { margin: 0 2px; }
        .data-table button.blue { background-color: #3b82f6; }
        .data-table button.blue:hover { background-color: #2563eb; }
        .data-table button.red { background-color: #ef4444; } 
        .data-table button.red:hover { background-color: #dc2626; }

        .pagination-info { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; font-size: 0.9rem; color: #6b7280; }

        /* untuk pop up detail */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .swal2-container {
            z-index: 99999 !important; 
            background-color: rgba(0, 0, 0, 0.6) !important; 
        }

        .swal2-detail-popup {
            max-width: 800px !important; 
            width: 90% !important; 
            z-index: 100000 !important; 
            text-align: left;
            border-radius: 12px; 
            animation: fadeIn 0.4s ease-out;
            background-color: #f7f9fc !important; 
        }

        .swal2-detail-popup .swal2-title {
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
        body.swal2-height-auto {
            height: auto !important;
            overflow-y: auto !important; 
            overflow: auto !important;
        }

        .swal2-detail-popup .swal2-icon.swal2-info {
            display: none !important; 
        }

        .swal2-detail-popup .swal2-html-container {
            max-height: 65vh; 
            overflow-y: auto;
            padding: 1.5em !important;
            line-height: 1.5;
            background-color: #f7f9fc;
        }

        .swal2-detail-section {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .swal2-detail-section h4 {
            color: #35542E;
            font-size: 1.25em;
            font-weight: 700;
            border-bottom: 1px solid #e0e0e0; 
            padding-bottom: 10px;
            margin-bottom: 1.5rem;
            margin-top: 0;
        }

        .swal2-detail-section:first-child h4 {
            margin-top: 0;
        }

        .swal2-validation-form label {
            display: block;
            font-weight: 600;
            margin-top: 10px;
            margin-bottom: 5px;
            font-size: 0.9em; 
            color: #374151; 
        }

        .swal2-validation-form input[type="text"],
        .swal2-validation-form input[type="number"],
        .swal2-validation-form input[type="date"],
        .swal2-validation-form select {
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

        .swal2-validation-form input:not([readonly]):focus,
        .swal2-validation-form select:not([disabled]):focus {
            border-color: #75B368;
            box-shadow: 0 0 0 3px rgba(117, 179, 104, 0.2);
            outline: none;
        }

        .swal2-validation-form input[readonly],
        .swal2-validation-form select[disabled] {
            background-color: #f3f4f6; 
            cursor: not-allowed;
            box-shadow: none;
        }

        .swal2-validation-form
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 5px;
        }
        .form-row .form-group {
            flex: 1;
        }

        .swal2-form-array-container {
            max-height: 300px; 
            overflow-y: auto; 
            padding: 10px;
            border: 1px solid #e5e7eb; 
            border-radius: 8px; 
            margin-bottom: 10px;
            background-color: #f9fafb; 
        }

        .rombongan-item, .barang-item {
            padding: 1.25rem; 
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px; 
            background-color: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .rombongan-item:last-child, .barang-item:last-child {
            margin-bottom: 0;
        }

        .rombongan-item h5, .barang-item h5 {
            font-size: 1em;
            font-weight: 700;
            color: #35542E;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .btn {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .btn.green { background-color: #35542E; color: white; }
        .btn.red { background-color: #dc3545; color: white; }

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

        .data-table .btn-aksi-status.status-menunggu { 
            background-color: #fef9c3; 
            color: #854d0e !important;
            border-color: #fde68a;
        }
        .data-table .btn-aksi-status.status-menunggu:hover {
            background-color: #fde68a;
            color: #854d0e !important;
        }
        .data-table .btn-aksi-status.status-terkonfirmasi { 
            background-color: #dbeafe; 
            color: #1e40af !important; 
            border-color: #bfdbfe;
        }
        .data-table .btn-aksi-status.status-terkonfirmasi:hover {
            background-color: #bfdbfe;
            color: #1e40af !important; 
        }
        
        .swal2-clickable-link {
            display: block;
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-sizing: border-box; 
            font-size: 0.95em;
            background-color: #e5e7eb;
            color: #3b82f6; 
            text-decoration: underline;
            cursor: pointer;
            word-break: break-all; 
            transition: all 0.2s;
        }
        .swal2-clickable-link:hover {
            color: #2563eb;
            background-color: #f3f4f6;
        }
    </style>
    
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
                            <label for="kodeBookingInput"></label>
                            <input type="text" name="kode_booking" id="kodeBookingInput" class="search-input" 
                                   placeholder="Masukkan kode booking" 
                                   value="<?= htmlspecialchars($kodeBooking ?? '') ?>">
                        </div>
                        <div class="input-group">
                            <label for="namaKetuaInput"></label>
                            <input type="text" name="nama_ketua" id="namaKetuaInput" class="search-input" 
                                   placeholder="Masukkan nama ketua rombongan" 
                                   value="<?= htmlspecialchars($namaKetua ?? '') ?>">
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
                                    <th><i class="fa-solid fa-hashtag"></i> KODE RESERVASI</th>
                                    <th><i class="fa-solid fa-user-group"></i> NAMA KETUA</th>
                                    <th><i class="fa-solid fa-calendar"></i> TGL. PENDAKIAN</th>
                                    <th><i class="fa-solid fa-person-hiking"></i> JUMLAH PENDAKI</th>
                                    <th><i class="fa-solid fa-money-bill-wave"></i> STATUS PEMBAYARAN</th>
                                    <th><i class="fa-solid fa-trash-can-arrow-up"></i> STATUS SAMPAH</th>
                                    <th><i class="fa-solid fa-gears"></i> AKSI</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php if (!empty($dataTabel)): ?>
                                <?php foreach ($dataTabel as $index => $row): ?>
                                    <tr style="--animation-order: <?= $index + 1 ?>;">
                                        <td><?= htmlspecialchars($row['kode_reservasi'] ?? 'N/A') ?></td>
                                        
                                        <td><?= htmlspecialchars($row['profiles']['nama_lengkap'] ?? 'N/A') ?></td>
                                        
                                        <td>
                                            <?php 
                                            $tanggal = $row['tanggal_pendakian'] ?? null;
                                            echo $tanggal ? date('d/m/Y', strtotime($tanggal)) : 'N/A';
                                            ?>
                                        </td> 
                                        
                                        <td><?= htmlspecialchars($row['jumlah_pendaki'] ?? '0') ?></td>
                                        <td>
                                            <?php 
                                            $status = $row['status'] ?? null; 
                                            $status_text = $status;
                                            $status_class = strtolower(str_replace([' ', '_'], '-', $status ?? 'none'));
                                            
                                            if ($status === 'menunggu_pembayaran') { $status_text = 'Menunggu Pembayaran'; } 
                                            elseif ($status === 'terkonfirmasi') { $status_text = 'Terkonfirmasi'; } 
                                            elseif ($status === 'selesai') { $status_text = 'Selesai'; } 
                                            elseif ($status === 'tervalidasi') { $status_text = 'Tervalidasi'; }
                                            ?>
                                            <span class="status-badge status-<?= htmlspecialchars($status_class) ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('_',' ',$status_text ?? 'N/A'))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $statusSampah = $row['status_sampah'] ?? 'belum_dicek'; 
                                            $status_text_sampah = ($statusSampah === 'belum_dicek') ? 'undefined' : $statusSampah;
                                            $status_class_sampah = ($statusSampah === 'belum_dicek') ? 'undefined' : strtolower(str_replace([' ', '_'], '-', $statusSampah));
                                            ?>
                                            <span class="status-badge status-<?= htmlspecialchars($status_class_sampah) ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('_',' ',$status_text_sampah))) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <button 
                                                class="btn blue btn-validasi icon-only" 
                                                data-id="<?= htmlspecialchars($row['id_reservasi'] ?? '') ?>" 
                                                title="Validasi Data / Edit"> 
                                                <i class="fa-solid fa-check-to-slot"></i>
                                            </button>

                                            <?php $status_aksi = $row['status'] ?? null; ?>
                                            <?php if ($status_aksi === 'menunggu_pembayaran'): ?>
                                                <a href="#" 
                                                   class="btn-aksi-status status-menunggu" 
                                                   data-id="<?= htmlspecialchars($row['id_reservasi'] ?? '') ?>"
                                                   data-new-status="terkonfirmasi"
                                                   data-kode="<?= htmlspecialchars($row['kode_reservasi'] ?? 'N/A') ?>"
                                                   data-ketua="<?= htmlspecialchars($row['profiles']['nama_lengkap'] ?? 'N/A') ?>"
                                                   title="Konfirmasi Pembayaran">
                                                   Konfirmasi
                                                </a>
                                            <?php elseif ($status_aksi === 'terkonfirmasi'): ?>
                                                 <a href="#" 
                                                   class="btn-aksi-status status-terkonfirmasi" 
                                                   data-id="<?= htmlspecialchars($row['id_reservasi'] ?? '') ?>"
                                                   data-new-status="selesai"
                                                   data-kode="<?= htmlspecialchars($row['kode_reservasi'] ?? 'N/A') ?>"
                                                   data-ketua="<?= htmlspecialchars($row['profiles']['nama_lengkap'] ?? 'N/A') ?>"
                                                   title="Tandai Reservasi Selesai">
                                                   Selesai
                                                 </a>
                                            <?php endif; ?> 
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
            $file_tambah = __DIR__ . '/tambah_reservasi.php';
            
            if (file_exists($file_tambah)) {
                include $file_tambah; 
            } else {
                echo '<div style="color: red; padding: 20px; background: #ffebee; border: 1px solid #e57373; border-radius: 8px;">Error: File "tambah_reservasi.php" tidak ditemukan di ' . $file_tambah . '.</div>';
            }
            ?>
            
        <?php endif; ?>

    </div>
</div>

<div class="modal-overlay" id="modal-validasi">...</div> 

<script>
let rombonganCounter = 0; 
let barangCounter = 0; 
let rombonganToDelete = [];
let barangToDelete = []; 
const HARGA_TIKET_MASUK = 20000;
const HARGA_TIKET_PARKIR = 5000;

function removeStuckOverlay() {
    const containers = document.querySelectorAll('.swal2-container');
    containers.forEach(container => {
        if (container.parentElement) {
            container.parentElement.removeChild(container);
        }
    });
    
    document.body.classList.remove('swal2-shown', 'swal2-height-auto', 'swal2-no-backdrop', 'swal2-toast-shown'); 
    document.documentElement.classList.remove('swal2-shown', 'swal2-height-auto'); 
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

document.addEventListener('DOMContentLoaded', function() {
    
    document.querySelectorAll('.btn-validasi').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            const reservasiId = this.getAttribute('data-id');
            if (reservasiId) {
                fetchDetailReservasi(reservasiId);
            }
        });
    });

    document.querySelectorAll('.btn-aksi-status').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            
            const reservasiId = this.getAttribute('data-id');
            const newStatus = this.getAttribute('data-new-status');
            const actionText = (newStatus === 'terkonfirmasi') ? 'mengonfirmasi' : 'menyelesaikan';
            const kodeReservasi = this.getAttribute('data-kode');
            const namaKetua = this.getAttribute('data-ketua');

            Swal.fire({
                title: `Konfirmasi Aksi`,
                html: `Apakah Anda yakin ingin <strong>${actionText}</strong> reservasi:
                       <br><br>
                       Kode: <strong>${kodeReservasi}</strong>
                       <br>
                       Ketua: <strong>${namaKetua}</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Ya, ${actionText}!`,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    handleStatusUpdate(reservasiId, newStatus);
                }
            });
        });
    });

    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const id = this.dataset.id;
            const tanggal = this.dataset.tanggal;
            const jumlah = this.dataset.jumlah;

            Swal.fire({
                title: 'Anda Yakin?',
                html: `Anda akan menghapus reservasi untuk tanggal <strong>${tanggal}</strong> (${jumlah} pendaki).<br>Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id_reservasi', id);

                    fetch('index.php?page=reservasi&action=hapus_reservasi', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', data.message, 'success')
                            .then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    })
                    .catch(_ => { 
                        Swal.fire('Error!', 'Terjadi kesalahan saat menghubungi server.', 'error');
                    });
                }
            });
        });
    });

});

function formatRupiah(number) {
    if (isNaN(number) || number === null) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
}

function fetchDetailReservasi(id) {
    removeStuckOverlay(); 

    Swal.fire({ title: 'Memuat Data...', text: 'Sedang mengambil detail reservasi.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

    fetch(`api/get_reservasi_detail.php?id_reservasi=${id}`) 
        .then(response => {
            if (!response.ok) { throw new Error('Gagal menghubungi server. Status: ' + response.status); }
            return response.json();
        })
        .then(data => {
            Swal.close(true);
            if (data.success && data.detail) {
                rombonganCounter = 0; 
                barangCounter = 0;
                showValidationPopup(data.detail);
            } else {
                const message = data.message || 'Detail reservasi tidak ditemukan.';
                Swal.fire({ icon: 'error', title: 'Gagal Memuat Detail', text: message });
            }
        })
        .catch(error => {
            Swal.close(true); 
            removeStuckOverlay(); 
            Swal.fire({ icon: 'error', title: 'Kesalahan Server', text: 'Terjadi kesalahan saat mengambil data: ' + error.message });
        });
}

window.createRombonganFields = function(data = {}, index) {
    if (!data || data.id_pendaki === null || data.id_pendaki === undefined) { 
        data = {}; 
    }

    const id = data.id_pendaki ? data.id_pendaki : 'new_' + rombonganCounter++;
    const isNew = id.toString().startsWith('new');
    let suratSehatHTML = '';
    if (data.url_surat_sehat) {
        suratSehatHTML = `
            <label>URL Surat Sehat (Klik untuk Buka)</label>
            <a href="${data.url_surat_sehat}" target="_blank" rel="noopener noreferrer" class="swal2-clickable-link">
                ${data.url_surat_sehat}
            </a>`;
    } else {
        suratSehatHTML = `
            <label>URL Surat Sehat</label>
            <input type="text" value="TIDAK ADA FILE" readonly class="search-input">`;
    }

    return `
        <div class="rombongan-item" data-id="${id}">
            <h5 style="border-bottom: 1px dashed #ddd; padding-bottom: 5px;">Pendaki #${index + 1} ${isNew ? ' (BARU)' : ''}</h5>
            <input type="hidden" name="rombongan[${id}][id]" value="${id}">
            
            <label>Nama Lengkap</label>
            <input type="text" name="rombongan[${id}][nama_lengkap]" value="${data.nama_lengkap || ''}" readonly class="search-input">

            <label>NIK</label>
            <input type="text" name="rombongan[${id}][nik]" value="${data.nik || ''}" readonly class="search-input">

            <label>Nomor Telepon</label>
            <input type="tel" name="rombongan[${id}][nomor_telepon]" value="${data.nomor_telepon || ''}" readonly class="search-input">

            <div class="form-row">
                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="rombongan[${id}][alamat]" value="${data.alamat || ''}" readonly class="search-input">
                </div>
                <div class="form-group">
                    <label>Kontak Darurat</label>
                    <input type="text" name="rombongan[${id}][kontak_darurat]" value="${data.kontak_darurat || ''}" readonly class="search-input">
                </div>
            </div>
            
            <!-- PERBAIKAN: Menampilkan field URL Surat Sehat (bisa diklik) -->
            ${suratSehatHTML}
            
            <!-- PERBAIKAN: Tombol Hapus Pendaki Dihilangkan -->
        </div>
    `;
}

window.createBarangFields = function(data = {}, index) {
    if (!data || data.id_barang === null || data.id_barang === undefined) { 
        data = {}; 
    }

    const id = data.id_barang ? data.id_barang : 'new_barang_' + barangCounter++;
    const isNew = id.toString().startsWith('new_barang');
    
    let jenisSampah = data.jenis_sampah || 'organik';
    if (jenisSampah === 'anorganik') {
        jenisSampah = 'non-organik';
    }

    return `
        <div class="barang-item" data-id="${id}">
            <h5 style="border-bottom: 1px dashed #ddd; padding-bottom: 5px;">Barang #${index + 1} ${isNew ? ' (BARU)' : ''}</h5>
            <input type="hidden" name="barang[${id}][id]" value="${id}">
            
            <label>Nama Barang</label>
            <input type="text" name="barang[${id}][nama_barang]" value="${data.nama_barang || ''}" readonly class="search-input">

            <div class="form-row">
                <div class="form-group">
                    <label>Jenis Sampah</label>
                    <select name="barang[${id}][jenis_sampah]" disabled class="search-input">
                        <option value="organik" ${jenisSampah === 'organik' ? 'selected' : ''}>Organik</option>
                        <option value="non-organik" ${jenisSampah === 'non-organik' ? 'selected' : ''}>Anorganik</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah (Unit)</label>
                    <input type="number" name="barang[${id}][jumlah]" value="${data.jumlah || 0}" readonly class="search-input">
                </div>
            </div>
            
            <!-- PERBAIKAN: Tombol Hapus Barang Dihilangkan -->
        </div>
    `;
}


function showValidationPopup(detail) {
    Swal.close(true); 
    removeStuckOverlay(); 

    const r = detail.reservasi;
    const p = detail.profiles;
    const namaKetua = p && p.nama_lengkap ? p.nama_lengkap : 'N/A';
    const idReservasi = r.id_reservasi; 
    
    // Hitung jumlah pendaki awal
    const jumlahPendakiAwal = detail.pendaki_rombongan ? detail.pendaki_rombongan.length : 0;
    // Jika 0, setidaknya 1 (ketua)
    const jumlahPendakiValid = (jumlahPendakiAwal === 0 && r.jumlah_pendaki > 0) ? r.jumlah_pendaki : jumlahPendakiAwal;

    const formReservasiHtml = `
        <form id="form-reservasi-validasi">
            <div class="swal2-detail-section">
                <h4><i class="fa-solid fa-file-invoice"></i> Detail Pemesanan</h4>
                
                <label>Kode Booking</label>
                <input type="text" name="kode_reservasi" value="${r.kode_reservasi || 'N/A'}" readonly class="search-input">

                <label>Ketua Rombongan</label>
                <input type="text" name="nama_ketua" value="${namaKetua}" readonly class="search-input">

                <div class="form-row">
                    <div class="form-group">
                        <label>Tgl. Pendakian</label>
                        <!-- PERBAIKAN: Input TANGGAL dibuat readonly -->
                        <input type="date" name="tanggal_pendakian" value="${r.tanggal_pendakian || ''}" readonly class="search-input">
                    </div>
                    <div class="form-group">
                        <label>Jumlah Pendaki</label>
                        <!-- PERBAIKAN: Input diubah menjadi teks (read-only) dan diisi otomatis -->
                        <input type="number" id="jumlah_pendaki_modal" name="jumlah_pendaki" value="${jumlahPendakiValid}" readonly class="search-input" style="background-color: #f3f4f6; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tiket Parkir</label>
                        <!-- PERBAIKAN: Input TIKET PARKIR dibuat readonly -->
                        <input type="number" name="jumlah_tiket_parkir" value="${r.jumlah_tiket_parkir || 0}" readonly class="search-input">
                    </div>
                    <div class="form-group">
                        <label>Total Harga (Rp)</label>
                        <input type="text" name="total_harga" value="${r.total_harga || 0}" readonly class="search-input">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Status Pembayaran</label>
                        <select name="status" class="search-input">
                            <option value="menunggu_pembayaran" ${r.status === 'menunggu_pembayaran' ? 'selected' : ''}>Menunggu Pembayaran</option>
                            <!-- PERBAIKAN: Opsi 'sudah_bayar' dihapus karena tidak ada di ENUM database -->
                            <!-- <option value="sudah_bayar" ${r.status === 'sudah_bayar' ? 'selected' : ''}>Sudah Bayar</option> -->
                            <option value="terkonfirmasi" ${r.status === 'terkonfirmasi' ? 'selected' : ''}>Terkonfirmasi</option>
                            <option value="selesai" ${r.status === 'selesai' ? 'selected' : ''}>Selesai</option>
                            <option value="dibatalkan" ${r.status === 'dibatalkan' ? 'selected' : ''}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status Sampah</label>
                        <select name="status_sampah" class="search-input">
                            <option value="belum_dicek" ${r.status_sampah === 'belum_dicek' ? 'selected' : ''}>Belum Dicek</option>
                            <option value="sesuai" ${r.status_sampah === 'sesuai' ? 'selected' : ''}>Sesuai</option>
                            <option value="tidak_sesuai" ${r.status_sampah === 'tidak_sesuai' ? 'selected' : ''}>Tidak Sesuai</option>
                        </select>
                    </div>
                </div>
                <hr>
            </div>
        </form>
    `;

    let rombonganHtml = '<h4><i class="fa-solid fa-users"></i> Data Rombongan</h4>';
    rombonganHtml += '<div id="rombongan-container" class="swal2-form-array-container">';

    if (detail.pendaki_rombongan && detail.pendaki_rombongan.length > 0) {
        detail.pendaki_rombongan.forEach((item, index) => {
            rombonganHtml += createRombonganFields(item, index);
        });
    } else {
        // Jika tidak ada data rombongan, tambahkan 1 form kosong
        rombonganHtml += createRombonganFields(null, 0);
    }

    rombonganHtml += '</div>';
    // PERBAIKAN: Tombol Tambah Pendaki Dihilangkan
    // rombonganHtml += `<button type="button" class="btn green" id="btn-tambah-rombongan" style="margin-top: 10px;"> ... </button>`;

    let barangHtml = '<h4><i class="fa-solid fa-box-open"></i> Barang & Sampah Bawaan</h4>';
    barangHtml += '<div id="barang-container" class="swal2-form-array-container">';

    if (detail.barang_sampah_bawaan && detail.barang_sampah_bawaan.length > 0) {
        detail.barang_sampah_bawaan.forEach((item, index) => {
            barangHtml += createBarangFields(item, index);
        });
    } else {
        // Jika tidak ada barang, tambahkan 1 form kosong
        barangHtml += createBarangFields(null, 0);
    }

    barangHtml += '</div>';
    // PERBAIKAN: Tombol Tambah Barang Dihilangkan
    // barangHtml += `<button type="button" class="btn green" id="btn-tambah-barang" style="margin-top: 10px;"> ... </button>`;

    const content = formReservasiHtml + rombonganHtml + barangHtml;

    Swal.fire({
        title: `Detail Reservasi (ID: ${idReservasi})`,
        icon: 'info',
        html: content,
        width: '85%', 
        showCloseButton: false, 
        allowOutsideClick: false, 
        showCancelButton: true,
        confirmButtonText: 'Simpan Perubahan',
        cancelButtonText: 'Tutup',
        customClass: { popup: 'swal2-detail-popup swal2-validation-form' },
        didOpen: () => {
             // PERBAIKAN: Listener untuk Tambah/Hapus Dihilangkan
             // document.getElementById('btn-tambah-rombongan').addEventListener('click', tambahPendaki);
             // document.getElementById('btn-tambah-barang').addEventListener('click', tambahBarang);
             
             rombonganToDelete = [];
             barangToDelete = [];

             // PERBAIKAN: Listener untuk Tambah/Hapus Dihilangkan
             // document.getElementById('rombongan-container').addEventListener('click', function(e) { ... });
             // document.getElementById('barang-container').addEventListener('click', function(e) { ... });

             // PERBAIKAN: Tambahkan listener untuk input harga
             const jumlahPendakiInput = document.getElementById('jumlah_pendaki_modal');
             const tiketParkirInput = document.querySelector('input[name="jumlah_tiket_parkir"]');
             
             // PERBAIKAN: Listener tidak diperlukan jika input readonly, tapi tidak masalah jika ada
             jumlahPendakiInput.addEventListener('change', updateModalTotalHarga); 
             tiketParkirInput.addEventListener('input', updateModalTotalHarga);

             // Panggil sekali saat modal dibuka
             updateModalTotalHarga();
        },
        preConfirm: () => {
             return handleValidationUpdate(idReservasi);
        },
        willClose: () => {
             removeStuckOverlay(); 
        }
    });
}


window.removeRombonganItem = function(button) { 
    const item = button.closest('.rombongan-item');
    const id = item.getAttribute('data-id');
    
    // PERBAIKAN: Jika item ini ada di database (bukan 'new_'),
    // tandai untuk dihapus saat disimpan.
    if (id && !id.startsWith('new_')) {
        rombonganToDelete.push(id);
    }
    
    item.remove(); 
    updateModalPendakiCount();
}
window.removeBarangItem = function(button) { 
    const item = button.closest('.barang-item');
    const id = item.getAttribute('data-id');

    // PERBAIKAN: Tandai barang untuk dihapus
    if (id && !id.startsWith('new_barang_')) {
        barangToDelete.push(id);
    }

    item.remove(); 
}

window.tambahPendaki = function() {
    const container = document.getElementById('rombongan-container');
    if (container) { 
        // PERBAIKAN: 'index' sekarang dihitung dari 'container.children.length'
        // bukan 'rombonganCounter'
        container.insertAdjacentHTML('beforeend', createRombonganFields({}, container.children.length)); 
        updateModalPendakiCount();
    }
}
window.tambahBarang = function() {
    const container = document.getElementById('barang-container');
    // PERBAIKAN: 'index' sekarang dihitung dari 'container.children.length'
    if (container) { container.insertAdjacentHTML('beforeend', createBarangFields({}, container.children.length)); }
}

function updateModalPendakiCount() {
    const container = document.getElementById('rombongan-container');
    const inputJumlah = document.getElementById('jumlah_pendaki_modal');
    if (container && inputJumlah) {
        inputJumlah.value = container.children.length;
        // PERBAIKAN: Picu event 'change' agar harga ter-update
        inputJumlah.dispatchEvent(new Event('change'));
    }
}

// PERBAIKAN: Fungsi baru untuk menghitung total harga di modal
function updateModalTotalHarga() {
    const jumlahPendakiInput = document.getElementById('jumlah_pendaki_modal');
    const tiketParkirInput = document.querySelector('input[name="jumlah_tiket_parkir"]');
    const totalHargaInput = document.querySelector('input[name="total_harga"]');

    if (!jumlahPendakiInput || !tiketParkirInput || !totalHargaInput) return;

    const jumlahPendaki = parseInt(jumlahPendakiInput.value) || 0;
    const jumlahParkir = parseInt(tiketParkirInput.value) || 0;

    const total = (jumlahPendaki * HARGA_TIKET_MASUK) + (jumlahParkir * HARGA_TIKET_PARKIR);

    totalHargaInput.value = total;
}


async function handleValidationUpdate(id) {
    const form = document.getElementById('form-reservasi-validasi');
    const formData = new FormData(form);
    
    // PERBAIKAN: Logika untuk mengumpulkan data rombongan dihapus
    // const rombonganData = {};
    // ...
    
    // PERBAIKAN: Logika untuk mengumpulkan data barang dihapus
    // const barangData = {};
    // ...

    const payload = Object.fromEntries(formData.entries());
    payload.id_reservasi = id;
    
    // PERBAIKAN: Data rombongan dan barang tidak lagi dikirim
    // payload.rombongan = rombonganData;
    // payload.barang = barangData;
    
    // PERBAIKAN: Jumlah pendaki tetap dikirim (karena ada di form utama)
    // dan sudah dihitung dengan benar
    payload.jumlah_pendaki = document.getElementById('jumlah_pendaki_modal').value;

    // PERBAIKAN: Array hapus tidak lagi dikirim
    // payload.rombongan_to_delete = rombonganToDelete;
    // payload.barang_to_delete = barangToDelete;

    // PERBAIKAN: Menggunakan path RELATIF dari index.php
    const updateUrl = 'api/update_reservasi_status.php'; 

    try {
        const response = await fetch(updateUrl, {
            method: 'PATCH', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Gagal menyimpan perubahan.');
        }

        Swal.fire({
            title: 'Berhasil!',
            text: result.message || 'Data reservasi berhasil diperbarui.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            location.reload(); 
        });

    } catch (error) {
        Swal.showValidationMessage(`Gagal: ${error.message}`);
    }
}

function handleStatusUpdate(id, newStatus) {
    Swal.fire({
        title: 'Memperbarui Status...',
        text: 'Mohon tunggu sebentar...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const formData = new FormData();
    formData.append('id_reservasi', id);
    formData.append('new_status', newStatus);

    fetch('index.php?page=reservasi&action=update_status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil!', data.message, 'success')
            .then(() => location.reload());
        } else {
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(_ => {
        Swal.fire('Error!', 'Terjadi kesalahan saat menghubungi server.', 'error');
    });
}
</script>

</body>
</html>