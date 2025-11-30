<?php
include __DIR__ . '/../../config/supabase.php';

$tableName = 'pendaki_rombongan';
$filterNama = trim($_GET['filter_nama'] ?? '');
$filterIDPendaki = trim($_GET['filter_id_pendaki'] ?? '');
$filterNIK = trim($_GET['filter_nik'] ?? '');

$queryParams = [];

if (!empty($filterNama)) {
    $queryParams[] = 'nama_lengkap=ilike.*' . urlencode($filterNama) . '*';
}

if (!empty($filterIDPendaki)) {
    $queryParams[] = 'id_pendaki=eq.' . urlencode($filterIDPendaki);
}

if (!empty($filterNIK)) {
    $queryParams[] = 'nik=eq.' . urlencode($filterNIK);
}

if (!empty($queryParams)) {
    $endpoint = $tableName . '?' . implode('&', $queryParams) . '&order=id_reservasi.desc';
} else {
    $endpoint = $tableName . '?order=id_reservasi.desc';
}

$dataPendakian = supabase_request('GET', $endpoint);

if (!$dataPendakian || isset($dataPendakian['error'])) {
    $dataPendakian = []; 
}

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
    <link rel="stylesheet" href="/simaksi/assets/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
</head>
<body>

<div class="menu-container">
    
    <div class="filter-section">
        <form action="index.php" method="GET" class="filter-form">
            <input type="hidden" name="page" value="manage_pendakian">

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
                                <i class="fa-solid fa-eye"></i> detail pendaki
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

<div class="modal-overlay" id="modalOverlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalTitle">Judul Modal</h3>
            <button class="modal-close-btn" id="closeModal">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
        </div>
    </div>
</div>
<script src="/simaksi/assets/js/manage_pendakian.js"></script> 
</body>
</html>