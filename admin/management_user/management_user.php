<?php
include __DIR__ . '/../../config/supabase.php';

$filterNama = trim($_GET['filter_nama'] ?? '');
$filterPeran = trim($_GET['filter_peran'] ?? '');
$queryParams = [];

if (!empty($filterNama)) {
    // 1. Kita siapkan search term-nya
    // PERUBAHAN 1: Gunakan rawurlencode agar spasi menjadi %20, bukan +
    $searchTerm = rawurlencode($filterNama);

    // 2. Kita gunakan filter 'or' dari Supabase
    // PERUBAHAN 2: Ganti wildcard % (SQL) menjadi * (API Supabase)
    $queryParams[] = "or=(nama_lengkap.ilike.*{$searchTerm}*,email.ilike.*{$searchTerm}*)";
}

if (!empty($filterPeran)) {
    // PERBAIKAN: Ganti 'peran_pengguna' kembali menjadi 'peran'
    $queryParams[] = 'peran=eq.' . urlencode($filterPeran);
}

// Gabungkan query dan tambahkan sorting
if (!empty($queryParams)) {
    $endpoint = 'profiles?' . implode('&', $queryParams) . '&order=nama_lengkap.asc';
} else {
    // Default jika tidak ada filter, tetap sort
    $endpoint = 'profiles?order=nama_lengkap.asc';
}

$data = supabase_request('GET', $endpoint);
if (!$data || isset($data['error'])) {
    $data = []; 
}

// Hitung statistik
$semuaPengguna = supabase_request('GET', 'profiles');
$totalPengguna = 0;
$totalAdmin = 0;
$totalPendaki = 0;

if ($semuaPengguna && !isset($semuaPengguna['error'])) {
    $totalPengguna = count($semuaPengguna);
    foreach ($semuaPengguna as $user) {
        // PERBAIKAN: Ganti 'peran_pengguna' kembali menjadi 'peran'
        if ($user['peran'] === 'admin') {
            $totalAdmin++;
        // PERBAIKAN: Ganti 'peran_pengguna' kembali menjadi 'peran'
        } elseif ($user['peran'] === 'pendaki') {
            $totalPendaki++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen User</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="/simaksi/assets/css/style.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- TAMBAHKAN CSS INI UNTUK LAYOUT BARU -->
  <style>
    .filter-section {
        display: flex;
        justify-content: space-between; /* Mendorong form ke kiri & tombol ke kanan */
        align-items: center; /* Menjaga agar tetap sejajar */
        gap: 20px; /* Jarak antara form dan tombol */
    }
    .filter-form {
        flex-grow: 1; /* Membuat form filter mengambil sisa ruang */
    }
    /* Pastikan tombol baru tidak ter-wrap */
    #tambahUser {
        white-space: nowrap;
    }
  </style>
</head>
<body>

<div class="menu-container">
  <div class="status-bar">
    <div class="card blue">
      <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"><path fill="currentColor" d="M16 17v2H8v-2h8m4-4v2H4v-2h16m0-4v2H4V9h16m0-4v2H4V5h16"/></svg>
      </span>
      Total Pengguna
      <span class="value"><?= $totalPengguna ?></span>
    </div>

    <div class="card green">
      <span class="icon">
         <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"><path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12c5.16-1.26 9-6.45 9-12V5l-9-4m0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
      </span>
      Admin
      <span class="value"><?= $totalAdmin ?></span>
    </div>

    <div class="card red">
      <span class="icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a5 5 0 1 1 0 10a5 5 0 0 1 0-10m0 12c-4.336 0-8 2.239-8 5v3h16v-3c0-2.761-3.664-5-8-5"/></svg>
      </span>
      Pendaki
      <span class="value"><?= $totalPendaki ?></span>
    </div>
  </div>

  <div class="filter-section">
    <form action="index.php" method="GET" class="filter-form">
        <input type="hidden" name="page" value="user">
      
        <div class="filter-group">
            <!-- Placeholder diubah di sini -->
            <input type="text" name="filter_nama" id="filterNama" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($filterNama ?? '') ?>">
            
            <select name="filter_peran" id="filterPeran">
                <option value="">Semua Peran</option>
                <option value="admin" <?= ($filterPeran === 'admin') ? 'selected' : '' ?>>Admin</option>
                <option value="pendaki" <?= ($filterPeran === 'pendaki') ? 'selected' : '' ?>>Pendaki</option>
            </select>

            <button type="submit" class="filter-btn-icon" title="Terapkan Filter">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>

        <?php if (!empty($filterNama) || !empty($filterPeran)): ?>
            <a href="index.php?page=user" class="reset-btn">Reset</a>
        <?php endif; ?>
    </form>

    <!-- TOMBOL DIPINDAHKAN DAN DIUBAH TEKSNYA -->
    <button class="btn green" id="tambahUser">Tambah Admin</button>
  </div>

  <div class="table-container">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID Pengguna</th>
          <th>Nama Lengkap</th>
          <th>Email</th>
          <th>No. Telepon</th>
          <th>Alamat</th>
          <th>Peran</th> <!-- Biarkan header tabel tetap 'Peran' -->
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($data)): ?>
        <?php $index = 0; ?>
        <?php foreach ($data as $row): ?>
          <?php $index++; ?>
          <tr style="--animation-order: <?= $index ?>;">
            <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['nama_lengkap'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['nomor_telepon'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['alamat'] ?? '') ?></td>
            <!-- PERBAIKAN: Ganti 'peran_pengguna' kembali menjadi 'peran' -->
            <td><?= htmlspecialchars($row['peran'] ?? '') ?></td>
            <td>
              <button class="btn blue btn-edit" data-id="<?= htmlspecialchars($row['id']) ?>">
                  <i class="fa-solid fa-eye"></i> lihat Detail
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center;">Data pengguna tidak ditemukan.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- DIV INI DIHAPUS -->
  <!-- 
  <div class="action-bar">
    <button class="btn green" id="tambahUser">Tambah Pengguna</button>
  </div>
  -->
</div>

<!-- Modal HTML -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalTitle">Judul Modal</h3>
            <button class="modal-close-btn" id="closeModal">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Konten form akan dimuat di sini oleh JS -->
        </div>
    </div>
</div>

<!-- Link ke file JavaScript -->
<script src="/simaksi/assets/js/management_user.js"></script>
</body>
</html>