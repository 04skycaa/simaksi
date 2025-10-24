<?php
include __DIR__ . '/../../config/supabase.php';

$filterTanggal = $_GET['filter_tanggal'] ?? null;
$select_query = 'id_reservasi,kode_reservasi,tanggal_pendakian,jumlah_pendaki,total_harga,status,profiles(nama_lengkap)';

if ($filterTanggal) {
    $endpoint = 'reservasi?tanggal_pendakian=eq.' . $filterTanggal . '&select=' . $select_query;
} else {
    $endpoint = 'reservasi?order=tanggal_pendakian.desc&select=' . $select_query;
}

$data = supabase_request('GET', $endpoint);
if (!$data || isset($data['error'])) {
    $data = [];
}

$tanggalAwalBulan = date('Y-m-01');
$tanggalAkhirBulan = date('Y-m-t');
$endpointBulanIni = "reservasi?tanggal_pendakian=gte.{$tanggalAwalBulan}&tanggal_pendakian=lte.{$tanggalAkhirBulan}&status=eq.terkonfirmasi";
$reservasiBulanIni = supabase_request('GET', $endpointBulanIni);

$totalReservasiBulanIni = 0;
$totalPendakiBulanIni = 0;
$totalPendapatanBulanIni = 0;

if ($reservasiBulanIni && !isset($reservasiBulanIni['error'])) {
    $totalReservasiBulanIni = count($reservasiBulanIni);
    foreach ($reservasiBulanIni as $row) {
        $totalPendakiBulanIni += $row['jumlah_pendaki'] ?? 0;
        $totalPendapatanBulanIni += $row['total_harga'] ?? 0;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reservasi dan Validasi Data</title>
    <link rel="stylesheet" href="/simaksi/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="menu-container">
    <div class="status-bar">
        <div class="card green">
            <span class="icon">
                <i class="fa-solid fa-book-bookmark"></i>
            </span>
            Reservasi
            <span class="value"><?= $totalReservasiBulanIni ?></span>
        </div>
        <div class="card red">
            <span class="icon">
                <i class="fa-solid fa-users"></i>
            </span>
            Total Pendaki
            <span class="value"><?= $totalPendakiBulanIni ?></span>
        </div>
        <div class="card blue">
            <span class="icon">
                <i class="fa-solid fa-money-bill-wave"></i>
            </span>
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
                    <th>Jml. Pendaki</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $index => $row): ?>
                    <tr style="--animation-order: <?= $index + 1 ?>;">
                        <td><?= htmlspecialchars($row['kode_reservasi'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['profiles']['nama_lengkap'] ?? 'N/A') ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal_pendakian'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($row['jumlah_pendaki'] ?? '0') ?></td>
                        <td>Rp <?= number_format($row['total_harga'] ?? 0, 0, ',', '.') ?></td>

                        <td>
                            <?php
                                $status = $row['status'] ?? null;
                                if ($status):
                                    $status_class = strtolower(str_replace(' ', '-', $status));
                            ?>
                                <span class="status-badge status-<?= htmlspecialchars($status_class) ?>">
                                    <?= htmlspecialchars(ucwords(str_replace('_',' ',$status))) ?>
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-none">N/A</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <button class="btn blue btn-validasi" data-id="<?= htmlspecialchars($row['id_reservasi'] ?? '') ?>">
                                <i class="fa-solid fa-check-to-slot"></i> Validasi
                            </button>
                            <button class="btn red btn-hapus" data-id="<?= htmlspecialchars($row['id_reservasi'] ?? '') ?>">
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

    <div class="action-bar">
        <button class="btn green" id="btn-tambah-reservasi">
            <i class="fa-solid fa-plus"></i> Tambah Reservasi
        </button>
    </div>

</div> <div class="modal-overlay" id="modal-validasi">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Detail Reservasi</h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body"></div> </div>
</div>

<div class="modal-overlay" id="modal-tambah">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Tambah Reservasi Baru</h3>
            <button class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-tambah-reservasi">
                <div class="form-group">
                    <label>Nama Ketua (Pilih User)</label>
                    <select name="id_pengguna" required>
                        <option value="">Pilih Pengguna</option>
                        <?php
                            $profiles_endpoint = 'profiles?select=id,nama_lengkap&order=nama_lengkap.asc';
                            $profiles_data = supabase_request('GET', $profiles_endpoint);
                            if ($profiles_data && !isset($profiles_data['error'])) {
                                foreach ($profiles_data as $profile) {
                                    echo '<option value="' . htmlspecialchars($profile['id']) . '">' . htmlspecialchars($profile['nama_lengkap']) . '</option>';
                                }
                            }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="add_tanggal_pendakian">Tanggal Pendakian</label>
                    <input type="date" id="add_tanggal_pendakian" name="tanggal_pendakian" required>
                </div>
                <div class="form-group">
                    <label for="add_jumlah_pendaki">Jumlah Pendaki</label>
                    <input type="number" id="add_jumlah_pendaki" name="jumlah_pendaki" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label for="add_jumlah_tiket_parkir">Jumlah Tiket Parkir</label>
                    <input type="number" id="add_jumlah_tiket_parkir" name="jumlah_tiket_parkir" value="0" min="0" required>
                </div>
                <button type="submit" class="btn green form-submit-btn">
                     <i class="fa-solid fa-save"></i> Simpan Reservasi
                </button>
            </form>
        </div>
    </div>
</div>

<script src="/simaksi/assets/js/reservasi.js"></script>

</body>
</html>