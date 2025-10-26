<?php
include __DIR__ . '/../../config/supabase.php';

$filterTanggal = $_GET['filter_tanggal'] ?? null;
if ($filterTanggal) {
    $endpoint = 'kuota_harian?tanggal_kuota=eq.' . $filterTanggal;
} else {
    $endpoint = 'kuota_harian?order=tanggal_kuota.asc'; 
}

$data = supabase_request('GET', $endpoint);
if (!$data || isset($data['error'])) {
    $data = []; 
}

$currentMonth = date('m');
$currentYear = date('Y');
$semuaKuota = supabase_request('GET', 'kuota_harian');
$totalKuotaBulanIni = 0;
$totalTerdaftar = 0;

if ($semuaKuota && !isset($semuaKuota['error'])) {
    foreach ($semuaKuota as $row) {
        $tanggal = date('m-Y', strtotime($row['tanggal_kuota']));
        if ($tanggal === "$currentMonth-$currentYear") {
            $totalKuotaBulanIni += $row['kuota_maksimal'];
            $totalTerdaftar += $row['kuota_terpesan'];
        }
    }
}

$kuotaTersisa = $totalKuotaBulanIni - $totalTerdaftar;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kuota Pendakian</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="/simaksi/assets/css/style.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="icon" type="image/x-icon" href="/simaksi/assets/images/favicon.ico">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="menu-container">

  <div class="filter-section">
    <form action="" method="GET" class="filter-form">
        <input type="hidden" name="page" value="kuota_pendakian"> 

        <div class="filter-group">
            <input type="date" name="filter_tanggal" id="filterDate" value="<?= htmlspecialchars($_GET['filter_tanggal'] ?? '') ?>">
            <button type="submit" class="filter-btn-icon" title="Terapkan Filter">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>

        <?php if (!empty($_GET['filter_tanggal'])): ?>
            <a href="index.php?page=kuota_pendakian" class="reset-btn">Reset</a>
        <?php endif; ?>
    </form>
</div>

  <div class="table-container">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal Kuota</th>
          <th>Kuota Maksimal</th>
          <th>Kuota Terpesan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($data)): ?>
        <?php $index = 0;  ?>
        <?php foreach ($data as $row): ?>
          <?php $status = ($row['kuota_maksimal'] - $row['kuota_terpesan']) > 0 ? 'Tersedia' : 'Penuh'; ?>
          <?php $index++; ?>
          <tr style="--animation-order: <?= $index ?>;"> <td><?= $row['id_kuota'] ?></td>
            <td><?= date('d-m-Y', strtotime($row['tanggal_kuota'])) ?></td>
            <td><?= $row['kuota_maksimal'] ?></td>
            <td><?= $row['kuota_terpesan'] ?></td>
            <td><?= $status ?></td>
            <td>
              <button class="btn blue btn-edit" data-id="<?= $row['id_kuota'] ?>">
                  <i class="fa-solid fa-pencil"></i> Edit
              </button>
              <button class="btn red btn-hapus" data-id="<?= $row['id_kuota'] ?>">
                  <i class="fa-solid fa-trash-can"></i> Hapus
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">Belum ada data.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="action-bar">
    <button class="btn green" id="tambahKuota">Tambah Kuota</button>
  </div>
</div>
<!-- untuk pop up -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-container">
    <div class="modal-header">
      <h3 id="modalTitle">Tambah Pengeluaran Baru</h3> 
      <button class="modal-close-btn" id="closeModal">&times;</button> 
    </div>
  <div class="modal-body" id="modalBody"></div></div>
</div>

<script src="/simaksi/assets/js/kuota_pendakian.js"></script>
</body>
</html>
