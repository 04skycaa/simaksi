<?php
include __DIR__ . '/../../config/supabase.php';

$pemasukan_data = supabase_request('GET', 'pemasukan?select=*');
$pengeluaran_data = supabase_request('GET', 'pengeluaran?select=*,id_pengeluaran');

$all_transactions = [];

if ($pemasukan_data && !isset($pemasukan_data['error'])) {
    foreach ($pemasukan_data as $item) {
        $all_transactions[] = [
            'id'        => null, 
            'tanggal'   => $item['tanggal_pemasukan'],
            'jenis'     => 'Pemasukan',
            'deskripsi' => $item['keterangan'],
            'jumlah'    => $item['jumlah']
        ];
    }
}

// Proses data pengeluaran
if ($pengeluaran_data && !isset($pengeluaran_data['error'])) {
    foreach ($pengeluaran_data as $item) {
        $all_transactions[] = [
            'id'        => $item['id_pengeluaran'],
            'tanggal'   => $item['tanggal_pengeluaran'],
            'jenis'     => 'Pengeluaran',
            'deskripsi' => $item['keterangan'],
            'jumlah'    => $item['jumlah']
        ];
    }
}

// Urutkan semua transaksi berdasarkan tanggal
usort($all_transactions, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

// 2. LOGIKA FILTER (Tidak ada perubahan di sini)
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$jenisTransaksi = $_GET['jenis_transaksi'] ?? 'semua';

$filtered_transactions = array_filter($all_transactions, function($trans) use ($startDate, $endDate, $jenisTransaksi) {
    $tanggal_transaksi = strtotime($trans['tanggal']);
    
    if ($startDate && $tanggal_transaksi < strtotime($startDate)) return false;
    if ($endDate && $tanggal_transaksi > strtotime($endDate . ' 23:59:59')) return false;
    if ($jenisTransaksi !== 'semua' && $trans['jenis'] !== $jenisTransaksi) return false;
    
    return true;
});

// 3. KALKULASI TOTAL (Tidak ada perubahan di sini)
$total_keuntungan = 0;
$total_pengeluaran = 0;
foreach ($filtered_transactions as $trans) {
    if ($trans['jenis'] === 'Pemasukan') {
        $total_keuntungan += $trans['jumlah'];
    } else {
        $total_pengeluaran += $trans['jumlah'];
    }
}
$saldo_akhir = $total_keuntungan - $total_pengeluaran;
$jumlah_transaksi = count($filtered_transactions);

function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Keuangan</title>
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
                <i class="fa-solid fa-arrow-down"></i>
            </span>
            Keuntungan
            <span class="value"><?= format_rupiah($total_keuntungan) ?></span>
        </div>
        <div class="card red">
            <span class="icon">
                <i class="fa-solid fa-arrow-up"></i>
            </span>
            Pengeluaran
            <span class="value"><?= format_rupiah($total_pengeluaran) ?></span>
        </div>
        <div class="card blue">
            <span class="icon"><i class="fa-solid fa-wallet">
                </i>
            </span>
            Saldo Akhir
            <span class="value"><?= format_rupiah($saldo_akhir) ?></span>
        </div>

        <div class="card soft-green"><span class="icon"><i class="fa-solid fa-list-ol"></i></span>Total Transaksi<span class="value"><?= $jumlah_transaksi ?></span></div>
    </div>

    <div class="filter-section">
        <form action="" method="GET" class="filter-form horizontal">
            <div class="filter-group"><label for="start_date">Dari Tanggal</label><input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($startDate) ?>"></div>
            <div class="filter-group"><label for="end_date">Sampai Tanggal</label><input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($endDate) ?>"></div>
            <div class="filter-group"><label for="jenis_transaksi">Jenis</label><select name="jenis_transaksi" id="jenis_transaksi"><option value="semua" <?= $jenisTransaksi == 'semua' ? 'selected' : '' ?>>Semua</option><option value="Pemasukan" <?= $jenisTransaksi == 'Pemasukan' ? 'selected' : '' ?>>Pemasukan</option><option value="Pengeluaran" <?= $jenisTransaksi == 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option></select></div>
            <div class="filter-group-action"><button type="submit" class="btn blue" title="Terapkan Filter"><i class="fa-solid fa-filter"></i> Terapkan</button><a href="?" class="btn gray reset-btn" title="Reset Filter">Reset</a></div>
        </form>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Deskripsi</th>
                    <th style="text-align: right;">Jumlah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($filtered_transactions)): ?>
                <?php foreach ($filtered_transactions as $index => $trans): ?>
                    <tr style="--animation-order: <?= $index + 1 ?>;">
                        <td><?= date('d M Y', strtotime($trans['tanggal'])) ?></td>
                        <td><span class="jenis-<?= strtolower($trans['jenis']) ?>"><?= htmlspecialchars($trans['jenis']) ?></span></td>
                        <td><?= htmlspecialchars($trans['deskripsi']) ?></td>
                        <td style="text-align: right;"><?= format_rupiah($trans['jumlah']) ?></td>
                        <td>
                            <?php if ($trans['jenis'] === 'Pengeluaran'): ?>
                                <button class="btn blue btn-edit" data-id="<?= $trans['id'] ?>"><i class="fa-solid fa-pencil"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">Tidak ada data yang cocok dengan filter.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="action-bar">
        <button class="btn green"><i class="fas fa-download"></i> Unduh Excel</button>
        <button class="btn blue" id="tambahPengeluaran"><i class="fas fa-plus"></i> Tambah Pengeluaran</button>
    </div>
</div>

<div class="modal-overlay" id="pembukuan-modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="pembukuan-modal-title">Tambah Pengeluaran Baru</h3>
            <button class="modal-close-btn" id="pembukuan-modal-close">&times;</button>
        </div>
        <div class="modal-body" id="pembukuan-modal-body"></div>
    </div>
</div>

<script src="/simaksi/assets/js/pembukuan.js"></script>
</body>
</html>