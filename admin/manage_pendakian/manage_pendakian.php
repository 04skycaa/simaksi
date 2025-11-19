<?php
// manage_pendakian.php
// Lokasi diasumsikan di root atau folder yang bisa mengakses config/supabase.php

// Pastikan path ini benar sesuai struktur file Anda
include __DIR__ . '/../../config/supabase.php';

// Tabel yang akan digunakan
$tableName = 'pendaki_rombongan';

// Parameter filtering
$filterNama = trim($_GET['filter_nama'] ?? '');
$filterIDPendaki = trim($_GET['filter_id_pendaki'] ?? '');
$filterNIK = trim($_GET['filter_nik'] ?? '');

$queryParams = [];

// Logika Filter
if (!empty($filterNama)) {
    // Mencari nama yang mengandung teks filter (ilike)
    $queryParams[] = 'nama_lengkap.ilike.%' . urlencode($filterNama) . '%';
}

if (!empty($filterIDPendaki)) {
    // Mencari ID Pendaki yang sama persis (eq)
    $queryParams[] = 'id_pendaki=eq.' . urlencode($filterIDPendaki);
}

if (!empty($filterNIK)) {
    // Mencari NIK yang sama persis (eq)
    $queryParams[] = 'nik=eq.' . urlencode($filterNIK);
}

// Endpoint untuk mengambil data pendaki rombongan
if (!empty($queryParams)) {
    $endpoint = $tableName . '?' . implode('&', $queryParams) . '&order=id_reservasi.desc';
} else {
    $endpoint = $tableName . '?order=id_reservasi.desc';
}

$dataPendakian = supabase_request('GET', $endpoint);

if (!$dataPendakian || isset($dataPendakian['error'])) {
    $dataPendakian = []; 
}

// Data Statistik
$semuaPendakian = supabase_request('GET', $tableName);
$totalPendakian = 0;

if ($semuaPendakian && !isset($semuaPendakian['error'])) {
    $totalPendakian = count($semuaPendakian);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pendakian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Library untuk pop-up notifikasi (wajib ada) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Gaya Dasar */
        .menu-container { padding: 20px; font-family: sans-serif; }
        
        /* Filter Section */
        .filter-section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .filter-form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .filter-input-text, .filter-btn-icon, .reset-btn { padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; }
        .filter-btn-icon { background-color: #28a745; color: white; border: none; cursor: pointer; }
        .reset-btn { background-color: #6c757d; color: white; text-decoration: none; border: none; padding: 10px 15px; border-radius: 4px; }

        /* Table Styles */
        .table-container { overflow-x: auto; margin-top: 20px; }
        .data-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .data-table th, .data-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .data-table th { background-color: #f2f2f2; font-weight: 600; }
        
        /* Action Buttons */
        .btn { padding: 8px 12px; margin-right: 5px; border: none; border-radius: 4px; cursor: pointer; color: white; transition: background-color 0.3s; }
        .btn.blue { background-color: #007bff; }
        .btn.red { background-color: #dc3545; }
        .btn:hover { opacity: 0.9; }
        
        /* KRITIS: CSS Modal (Pop-up) - Menggunakan visibility/opacity untuk keandalan */
        .modal-overlay { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.7); 
            z-index: 2147483647; /* Nilai Z-index Maksimal */
            
            /* Kontrol Visibilitas */
            display: flex; /* Selalu flex untuk centering */
            visibility: hidden; /* Default: Sembunyikan */
            opacity: 0; /* Default: Tidak terlihat */
            transition: opacity 0.3s, visibility 0.3s;
            
            justify-content: center; 
            align-items: center;
        }
        /* KRITIS: Ketika class active ditambahkan, pastikan muncul */
        .modal-overlay.active { 
            visibility: visible; /* Jadikan terlihat */
            opacity: 1; /* Transisikan menjadi penuh */
        }
        .modal-container { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            max-width: 550px; 
            width: 90%; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .modal-header h3 { font-size: 1.5rem; margin: 0; }
        .modal-close-btn { border: none; background: none; font-size: 2rem; cursor: pointer; color: #333; }
        .modal-body form label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem;}
        .modal-body form input { display: block; margin-bottom: 15px; width: 95%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        .form-actions { margin-top: 25px; text-align: right; }
    </style>
</head>
<body>

<div class="menu-container">
    
    <div class="filter-section">
        <form action="index.php?page=manage_pendakian" method="GET" class="filter-form">
            <input type="text" name="filter_nama" id="filterNama" placeholder="Cari Nama Lengkap..." value="<?= htmlspecialchars($filterNama ?? '') ?>" class="filter-input-text">

            <input type="text" name="filter_nik" id="filterNIK" placeholder="Cari NIK..." value="<?= htmlspecialchars($filterNIK ?? '') ?>" class="filter-input-text">

            <input type="number" name="filter_id_pendaki" id="filterIDPendaki" placeholder="ID Pendaki..." value="<?= htmlspecialchars($filterIDPendaki ?? '') ?>" class="filter-input-text">

            <button type="submit" class="filter-btn-icon" title="Terapkan Filter">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>

            <?php if (!empty($filterNama) || !empty($filterIDPendaki) || !empty($filterNIK)): ?>
                <a href="index.php?page=manage_pendakian" class="reset-btn">Reset Filter</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Reservasi</th>
                    <th>ID Pendaki</th>
                    <th>Nama Lengkap</th>
                    <th>NIK</th>
                    <th>Alamat</th>
                    <th>No. Telepon</th>
                    <th>Kontak Darurat</th>
                    <th>Surat Sehat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($dataPendakian)): ?>
                <?php foreach ($dataPendakian as $row): ?>
                    <tr> 
                        <td><?= htmlspecialchars($row['id_reservasi'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['id_pendaki'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nik'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['alamat'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['nomor_telepon'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['kontak_darurat'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($row['url_surat_sehat'])): ?>
                                <a href="<?= htmlspecialchars($row['url_surat_sehat']) ?>" target="_blank" title="Lihat Surat Sehat">Link</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn blue btn-edit" data-id="<?= htmlspecialchars($row['id_reservasi']) ?>">
                                <i class="fa-solid fa-pencil"></i> Edit
                            </button>
                            <button class="btn red btn-hapus" data-id="<?= htmlspecialchars($row['id_reservasi']) ?>">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" style="text-align:center; padding: 20px;">Data pendakian tidak ditemukan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Elemen Modal (Pop-up) -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalTitle">Judul Modal</h3>
            <button class="modal-close-btn" id="closeModal">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Form Edit akan dimuat di sini oleh JS -->
        </div>
    </div>
</div>
<script src="../../assets/js/manage_pendakian.js"></script> 
</body>
</html>