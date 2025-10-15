<?php
include __DIR__ . '/../../config/supabase.php';

$filterTanggal = $_GET['filter_tanggal'] ?? null;
if ($filterTanggal) {
    $endpoint = 'reservasi?tanggal_pendakian=eq.' . $filterTanggal . '&select=*,profiles(nama_lengkap)';
} else {
    $endpoint = 'reservasi?order=tanggal_pendakian.desc&select=*,profiles(nama_lengkap)';
}

$data = supabase_request('GET', $endpoint);
if (!$data || isset($data['error'])) {
    $data = [];
}

$currentMonth = date('m');
$currentYear = date('Y');
$semuaReservasi = supabase_request('GET', 'reservasi'); 

$totalReservasiBulanIni = 0;
$totalPendakiBulanIni = 0;
$totalPendapatanBulanIni = 0;

if ($semuaReservasi && !isset($semuaReservasi['error'])) {
    foreach ($semuaReservasi as $row) {
        $tanggalPendakian = date('m-Y', strtotime($row['tanggal_pendakian']));
        if ($tanggalPendakian === "$currentMonth-$currentYear") {
            $totalReservasiBulanIni++;
            $totalPendakiBulanIni += $row['jumlah_pendaki'];
            $totalPendapatanBulanIni += $row['total_harga'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Validasi Reservasi Pendakian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/simaksi/assets/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="menu-container">
    <div class="menu-header">
        <h2>Validasi Reservasi</h2>
    </div>

    <div class="status-bar">
        <div class="card green">
            <span class="icon"><i class="fa-solid fa-book-bookmark"></i></span>
            Reservasi Bulan Ini
            <span class="value"><?= $totalReservasiBulanIni ?></span>
        </div>
        <div class="card red">
            <span class="icon"><i class="fa-solid fa-users"></i></span>
            Total Pendaki
            <span class="value"><?= $totalPendakiBulanIni ?></span>
        </div>
        <div class="card blue">
            <span class="icon"><i class="fa-solid fa-money-bill-wave"></i></span>
            Pendapatan
            <span class="value">Rp <?= number_format($totalPendapatanBulanIni, 0, ',', '.') ?></span>
        </div>
    </div>

    <div class="filter-section">
        <form action="" method="GET" class="filter-form">
            <input type="hidden" name="page" value="reservasi"> 
            <div class="filter-group">
                <input type="date" name="filter_tanggal" id="filterDate" value="<?= htmlspecialchars($_GET['filter_tanggal'] ?? '') ?>">
                <button type="submit" class="filter-btn-icon" title="Terapkan Filter">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
            <?php if (!empty($_GET['filter_tanggal'])): ?>
                <a href="index.php?page=reservasi" class="reset-btn">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode Reservasi</th>
                    <th>Nama Ketua</th>
                    <th>Tgl. Pendakian</th>
                    <th>Jumlah Pendaki</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $index => $row): ?>
                    <tr style="--animation-order: <?= $index + 1 ?>;">
                        <td><?= htmlspecialchars($row['kode_reservasi']) ?></td>
                        <td><?= htmlspecialchars($row['profiles']['nama_lengkap'] ?? 'N/A') ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal_pendakian'])) ?></td>
                        <td><?= htmlspecialchars($row['jumlah_pendaki']) ?></td>
                        <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn blue btn-validasi" data-id="<?= $row['id_reservasi'] ?>">
                                <i class="fa-solid fa-check-to-slot"></i> Validasi / Detail
                            </button>
                            <button class="btn red btn-hapus" data-id="<?= $row['id_reservasi'] ?>">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">Belum ada data reservasi.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="reservasi-modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="reservasi-modal-title">Detail Reservasi</h3>
            <button class="modal-close-btn" id="reservasi-modal-close">&times;</button>
        </div>
        <div class="modal-body" id="reservasi-modal-body">
            </div>
    </div>
</div>

<script src="/simaksi/assets/js/reservasi.js"></script>

</body>
</html>