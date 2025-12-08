<?php
include __DIR__ . '/../../config/supabase.php';

// Determine if this page is being included as content or accessed directly
$is_included = (strpos($_SERVER['SCRIPT_NAME'], '/admin/index.php') !== false && isset($_GET['page']) && $_GET['page'] === 'kuota_pendakian');

// Define asset path based on context
$asset_path = $is_included ? '../assets' : '../../assets';

// --- START: Pagination & Filtering Logic ---
$rowsPerPage = 10;
// Mengambil nomor halaman saat ini dari parameter 'p', default ke 1
$currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($currentPage < 1) $currentPage = 1;

$filterTanggal = $_GET['filter_tanggal'] ?? null;
$endpoint = 'kuota_harian?order=tanggal_kuota.asc'; 

// Jika filter tanggal diterapkan, kita hanya mengambil data untuk tanggal itu
if ($filterTanggal) {
    $endpoint = 'kuota_harian?tanggal_kuota=eq.' . $filterTanggal;
}

// Fetch semua data (atau data yang difilter)
$data = supabase_request('GET', $endpoint);
if (!$data || isset($data['error'])) {
    $data = []; 
}

$totalData = count($data);
$totalPages = ceil($totalData / $rowsPerPage);

// Memastikan halaman saat ini valid
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
} elseif ($totalPages === 0) {
    $currentPage = 1;
}

$startIndex = ($currentPage - 1) * $rowsPerPage;

// Ambil subset data untuk halaman saat ini (Pagination)
$paginatedData = array_slice($data, $startIndex, $rowsPerPage);

// --- END: Pagination & Filtering Logic ---


// --- START: Monthly Summary Logic (unchanged) ---
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
// --- END: Monthly Summary Logic ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kuota Pendakian</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?php echo $asset_path; ?>/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="icon" type="image/x-icon" href="<?php echo $asset_path; ?>/images/favicon.ico">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    /* CSS Tambahan untuk Tata Letak Baru, Warna, dan Animasi */
    .header-controls {
        display: flex;
        /* Mengatur agar filter di kiri dan tombol tambah di kanan */
        justify-content: space-between; 
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap; /* Pastikan responsif */
        gap: 15px;
    }

    .pagination-controls {
        display: flex;
        justify-content: space-between; 
        align-items: center;
        margin-top: 20px;
        padding: 10px 0;
        border-top: 1px solid #ddd;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pagination-buttons {
        display: flex;
        gap: 5px;
    }

    .pagination-buttons .btn {
        padding: 8px 12px;
        min-width: 40px;
        text-align: center;
        border-radius: 6px;
        text-decoration: none;
        transition: background-color 0.2s, transform 0.1s; /* Tambahkan animasi */
        font-size: 0.9em;
    }

    .pagination-buttons .btn.active {
        background-color: #3b82f6; /* Warna biru untuk halaman aktif */
        color: white;
        pointer-events: none;
    }

    .pagination-buttons .btn.gray {
        background-color: #f3f4f6; /* Abu-abu muda untuk tombol non-aktif */
        color: #4b5563;
    }

    .pagination-buttons .btn:not(.disabled):hover {
        background-color: #e5e7eb; /* Efek hover */
        transform: translateY(-1px); /* Efek mengangkat sedikit */
    }

    .pagination-buttons .btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .page-info {
        font-size: 0.9em;
        color: #555;
    }

    /* Memperbaiki tata letak filter agar lebih rapi */
    .filter-section {
        display: flex;
        gap: 10px;
        align-items: center;
        order: -1; /* Mengatur Filter/Search ke posisi paling kiri */
    }

    .filter-form {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* Warna dan Animasi Tambahan untuk Tombol Aksi */
    .btn.blue, .btn.green, .btn.red {
        transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.1s;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
    }
    
    .btn.blue:hover {
        background-color: #2563eb;
        transform: translateY(-2px);
    }

    .btn.green {
        background-color: #35542E; /* Hijau yang lebih cerah */
    }

    .btn.green:hover {
        background-color: #75B368;
        transform: translateY(-2px);
    }

    .btn.red:hover {
        background-color: #dc2626;
        transform: translateY(-2px);
    }

    /* Animasi untuk baris tabel saat dimuat */
    .data-table tbody tr {
        opacity: 0;
        animation: fadeInRow 0.5s forwards;
        animation-delay: calc(var(--animation-order) * 0.05s);
    }

    @keyframes fadeInRow {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
  </style>
</head>
<body>

<div class="menu-container">

    <!-- START: Header Controls (Tombol Tambah & Filter) -->
    <div class="header-controls">
        <!-- 2. Filter Section (tetap di sini, order CSS membuatnya di kiri) -->
        <div class="filter-section">
            <form action="" method="GET" class="filter-form">
                <input type="hidden" name="page" value="kuota_pendakian"> 

                <div class="filter-group">
                    <input type="date" name="filter_tanggal" id="filterDate" value="<?= htmlspecialchars($_GET['filter_tanggal'] ?? '') ?>" class="input-date">
                    <button type="submit" class="filter-btn-icon" title="Terapkan Filter">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>

                <?php if (!empty($_GET['filter_tanggal'])): ?>
                    <a href="index.php?page=kuota_pendakian" class="reset-btn">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- 1. Tombol Tambah Kuota (tetap di sini, akan berada di kanan secara default) -->
        <div class="action-header">
            <button class="btn green" id="tambahKuota">
                <i class="fa-solid fa-plus"></i> Tambah Kuota
            </button>
        </div>
    </div>
    <!-- END: Header Controls -->

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
      <?php if (!empty($paginatedData)): ?>
        <?php $index = 0; ?>
        <?php foreach ($paginatedData as $row): ?>
          <?php 
            // Penentuan warna status
            $isAvailable = ($row['kuota_maksimal'] - $row['kuota_terpesan']) > 0;
            $status = $isAvailable ? 'Tersedia' : 'Penuh'; 
            $statusClass = $isAvailable ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'; // Menggunakan warna teks untuk status
            $index++; 
          ?>
          <tr style="--animation-order: <?= $index ?>;">
            <td><?= $row['id_kuota'] ?></td>
            <td><?= date('d-m-Y', strtotime($row['tanggal_kuota'])) ?></td>
            <td><?= $row['kuota_maksimal'] ?></td>
            <td><?= $row['kuota_terpesan'] ?></td>
            <td class="<?= $statusClass ?>"><?= $status ?></td>
            <td>
              <!-- Tombol Edit di setiap baris (sudah ada) -->
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
    
  <!-- START: Pagination Controls (Kontrol Halaman 1-10, 11-20, dst.) -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination-controls">
    <span class="page-info">
      Menampilkan data 
      <?= $startIndex + 1 ?> 
      - 
      <?= min($startIndex + $rowsPerPage, $totalData) ?> 
      dari 
      <?= $totalData ?>
    </span>
    <div class="pagination-buttons">
      <?php 
        // Membangun query string dasar, mempertahankan filter lain (terutama filter_tanggal)
        $queryString = $_GET;
        unset($queryString['p']);
        $baseUrl = 'index.php?' . http_build_query($queryString);
        
        // Menentukan halaman yang akan ditampilkan (misalnya, 5 halaman di sekitar halaman saat ini)
        $maxVisiblePages = 5;
        $startPage = max(1, $currentPage - floor($maxVisiblePages / 2));
        $endPage = min($totalPages, $startPage + $maxVisiblePages - 1);

        if ($endPage - $startPage + 1 < $maxVisiblePages) {
            $startPage = max(1, $endPage - $maxVisiblePages + 1);
        }
      ?>

      <!-- Tombol Sebelumnya -->
      <a href="<?= $currentPage > 1 ? $baseUrl . '&p=' . ($currentPage - 1) : '#' ?>" 
         class="btn gray <?= $currentPage == 1 ? 'disabled' : '' ?>">
         <i class="fa-solid fa-chevron-left"></i> Sebelumnya
      </a>

      <!-- Tombol Halaman (1, 2, 3, ...) -->
      <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
        <a href="<?= $baseUrl . '&p=' . $p ?>" 
           class="btn <?= $p == $currentPage ? 'blue active' : 'gray' ?>">
           <?= $p ?>
        </a>
      <?php endfor; ?>

      <!-- Tombol Berikutnya -->
      <a href="<?= $currentPage < $totalPages ? $baseUrl . '&p=' . ($currentPage + 1) : '#' ?>" 
         class="btn gray <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
         Berikutnya <i class="fa-solid fa-chevron-right"></i>
      </a>
    </div>
  </div>
  <?php endif; ?>
  <!-- END: Pagination Controls -->
  
  <!-- Hapus div action-bar lama di bagian bawah -->
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

<script src="<?php echo $asset_path; ?>/js/kuota_pendakian.js"></script>
</body>
</html>