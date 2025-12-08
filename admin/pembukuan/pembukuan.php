<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include __DIR__ . '/../../config/supabase.php';

// Determine if this page is being included as content or accessed directly
$is_included = (strpos($_SERVER['SCRIPT_NAME'], '/admin/index.php') !== false && isset($_GET['page']) && $_GET['page'] === 'pembukuan');

// Define asset path based on context
$asset_path = $is_included ? '../assets' : '../../assets';

$pemasukan_data = supabase_request('GET', 'pemasukan?select=*,tanggal_pemasukan,keterangan,jumlah');
$pengeluaran_data = supabase_request(
    'GET', 
    'pengeluaran?select=id_pengeluaran,id_kategori,keterangan,jumlah,tanggal_pengeluaran,deleted_at,kategori_pengeluaran(nama_kategori)&deleted_at=is.null'
);
$all_transactions = [];

if ($pemasukan_data && !isset($pemasukan_data['error'])) {
    foreach ($pemasukan_data as $item) {
        $all_transactions[] = [ 
            'id' => null, 
            'table' => 'pemasukan',
            'id_kategori' => null,
            'tanggal' => $item['tanggal_pemasukan'],
            'jenis' => 'Pemasukan',
            'deskripsi' => $item['keterangan'],
            'kategori' => 'Pemasukan',  
            'jumlah' => $item['jumlah']
        ];
    }
}
 
if ($pengeluaran_data && !isset($pengeluaran_data['error'])) {
    foreach ($pengeluaran_data as $item) { 
            $all_transactions[] = [
                'id' => $item['id_pengeluaran'],
                'table' => 'pengeluaran',
                'id_kategori' => $item['id_kategori'],
                'tanggal' => $item['tanggal_pengeluaran'],
                'jenis' => 'Pengeluaran',
                'deskripsi' => $item['keterangan'], 
                'kategori' => $item['kategori_pengeluaran']['nama_kategori'] ?? 'Tanpa Kategori', 
                'jumlah' => $item['jumlah']
            ];
        // }
    }
}
 
usort($all_transactions, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});
 
$current_tab = $_GET['tab'] ?? 'laporan';

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

$kategori_data = supabase_request('GET', 'kategori_pengeluaran?select=id_kategori,nama_kategori');

$kategori_options = $kategori_data && !isset($kategori_data['error']) ? $kategori_data : [];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Keuangan</title>
    <link rel="stylesheet" href="<?php echo $asset_path; ?>/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        
        .table-responsive {
            overflow-x: auto; 
            width: 100%;
            padding: 15px;
        }
        .btn.red-pdf {
            background-color: #dc3545;
            color: white;
            border: none;
        }
    
        .filter-group-action {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-form.horizontal > div {
            margin-right: 15px; 
        }

        @media print {
            .sidebar, .top-nav {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            } 
            .secondary-nav {
                display: none !important;
            } 
            .status-bar {
                display: none !important;
            } 
            .filter-section {
                display: none !important;
            } 
            .data-table th:last-child, .data-table td:last-child {
                display: none !important;
            } 
            .two-column-details {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .details-card {
                width: 100% !important;
                float: none !important;
                page-break-inside: avoid; 
                margin-bottom: 20px !important;
                box-shadow: none !important; 
                border: 1px solid #ccc;  
            }
            .details-card h3 {
                background-color: #f0f0f0;  
                padding: 10px;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="menu-container">

    <div class="secondary-nav">
        <a href="index.php?page=pembukuan&tab=laporan" 
            class="<?= $current_tab == 'laporan' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-line"></i> Laporan Keuangan
        </a>
        <a href="index.php?page=pembukuan&tab=tambah" 
            class="<?= $current_tab == 'tambah' ? 'active' : '' ?>">
            <i class="fa-solid fa-plus-circle"></i> Catat Pengeluaran Baru
        </a>
    </div>
 
    <div id="laporan-content" class="tab-content <?= $current_tab == 'laporan' ? 'active animate__animated animate__fadeIn' : '' ?>">
        
        <div class="status-bar">
            <div class="card green">
                <span class="icon"><i class="fa-solid fa-arrow-down"></i></span>
                Keuntungan
                <span class="value"><?= format_rupiah($total_keuntungan) ?></span>
            </div>
            <div class="card red">
                <span class="icon"><i class="fa-solid fa-arrow-up"></i></span>
                Pengeluaran
                <span class="value"><?= format_rupiah($total_pengeluaran) ?></span>
            </div>
            <div class="card blue">
                <span class="icon"><i class="fa-solid fa-wallet"></i></span>
                Saldo Akhir
                <span class="value"><?= format_rupiah($saldo_akhir) ?></span>
            </div>
            <div class="card soft-green"><span class="icon"><i class="fa-solid fa-list-ol"></i></span>Total Transaksi<span class="value"><?= $jumlah_transaksi ?></span></div>
        </div>

        <div class="filter-section">
            <form action="index.php" method="GET" class="filter-form horizontal">
                <input type="hidden" name="page" value="pembukuan">
                <input type="hidden" name="tab" value="laporan">
                <div class="filter-group"><label for="start_date">Dari Tanggal</label><input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($startDate) ?>"></div>
                <div class="filter-group"><label for="end_date">Sampai Tanggal</label><input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($endDate) ?>"></div>
                <div class="filter-group"><label for="jenis_transaksi">Jenis</label><select name="jenis_transaksi" id="jenis_transaksi"><option value="semua" <?= $jenisTransaksi == 'semua' ? 'selected' : '' ?>>Semua</option><option value="Pemasukan" <?= $jenisTransaksi == 'Pemasukan' ? 'selected' : '' ?>>Pemasukan</option><option value="Pengeluaran" <?= $jenisTransaksi == 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option></select></div>
                <div class="filter-group-action">  
                    <button type="submit" class="btn blue" title="Terapkan Filter"><i class="fa-solid fa-filter"></i> Terapkan</button>
                    <a href="index.php?page=pembukuan&tab=laporan" class="btn gray reset-btn" title="Reset Filter">Reset</a>
                    <button type="button" class="btn red-pdf" id="cetakPdfBtn" title="Cetak Laporan Keuangan">
                        <i class="fa-solid fa-file-pdf"></i> Cetak PDF
                    </button>
                </div>
            </form>
        </div>
 
        <div class="two-column-details">
             
            <div class="details-card">
                <div class="card-header green">
                    <i class="fa-solid fa-arrow-down"></i>
                    <h3>Rincian Pemasukan</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;"><i class="fa-solid fa-calendar-alt"></i> Tanggal</th>
                                <th style="width: 50%;"><i class="fa-solid fa-info-circle"></i> Keterangan</th>
                                <th style="width: 25%; text-align: right;"><i class="fa-solid fa-dollar-sign"></i> Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $pemasukan_found = false;
                            $pemasukan_index = 0;
                            foreach ($filtered_transactions as $trans): 
                                if ($trans['jenis'] === 'Pemasukan'): 
                                    $pemasukan_found = true;
                                    $pemasukan_index++;
                            ?>
                                <tr style="--animation-order: <?= $pemasukan_index ?>;"> <!-- Digunakan untuk animation-delay di CSS -->
                                    <td><?= date('d M Y', strtotime($trans['tanggal'])) ?></td>
                                                                                                                                                                                                                                                                                                 <td><?= htmlspecialchars($trans['deskripsi']) ?></td>
                                    <td class="amount green-text" style="text-align: right;"><?= format_rupiah($trans['jumlah']) ?></td>
                                </tr>
                            <?php 
                                endif;
                            endforeach; 
                            if (!$pemasukan_found): 
                            ?>
                                <tr><td colspan="3" style="text-align:center;">Tidak ada data pemasukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>  
            </div>  
 
            <div class="details-card">
                <div class="card-header red">
                    <i class="fa-solid fa-arrow-up"></i>
                    <h3>Rincian Pengeluaran</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;"><i class="fa-solid fa-calendar-alt"></i> Tanggal</th>
                                <th style="width: 35%;"><i class="fa-solid fa-info-circle"></i> Keterangan</th>
                                <th style="width: 20%;"><i class="fa-solid fa-tags"></i> Kategori</th>
                                <th style="width: 20%; text-align: right;"><i class="fa-solid fa-dollar-sign"></i> Jumlah</th>
                                <th style="width: 5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $pengeluaran_found = false;
                            $pengeluaran_index = 0;
                            foreach ($filtered_transactions as $trans): 
                                if ($trans['jenis'] === 'Pengeluaran'): 
                                    $pengeluaran_found = true;
                                    $pengeluaran_index++;
                            ?>
                                <tr style="--animation-order: <?= $pengeluaran_index ?>;"> <!-- Digunakan untuk animation-delay di CSS -->
                                    <td><?= date('d M Y', strtotime($trans['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($trans['deskripsi']) ?></td> 
                                    <td><?= htmlspecialchars($trans['kategori']) ?></td>
                                    <td class="amount red-text" style="text-align: right;"><?= format_rupiah($trans['jumlah']) ?></td>
                                    <td> 
                                        <?php if ($trans['id']): ?>
                                            <button class="btn btn-icon btn-edit" data-id="<?= $trans['id'] ?>" title="Edit">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                             
                                            <button class="btn btn-icon btn-delete" 
                                                                 data-id="<?= $trans['id'] ?>" 
                                                                 data-table="<?= $trans['table'] ?? 'pengeluaran' ?>"
                                                                 title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                                endif;
                            endforeach; 
                            if (!$pengeluaran_found): 
                            ?>
                                <tr><td colspan="5" style="text-align:center;">Tidak ada data pengeluaran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>  
            </div>  
        </div>  

    </div> 
 
    <div id="tambah-content" class="tab-content <?= $current_tab == 'tambah' ? 'active animate__animated animate__fadeIn' : '' ?>">
        <div class="card-modern" style="padding: 24px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);"> 
            
            <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-plus-circle" style="color: #28a745;"></i> Catat Pengeluaran Baru
            </h3>
            <form id="tambahPengeluaranForm" action="pembukuan/proses_pembukuan.php" method="POST">
                <input type="hidden" name="form_action" value="tambah">
                
                <div class="form-grid">
                    <div class="form-group-modern">
                        <label for="tambah_jumlah" class="form-label-modern">
                            <i class="fa-solid fa-dollar-sign"></i> Jumlah Pengeluaran (Rp)
                        </label>
                        <input type="number" id="tambah_jumlah" name="jumlah" class="input-modern" placeholder="Masukkan jumlah pengeluaran" required>
                    </div>
 
                    <div class="form-group-modern">
                        <label for="tambah_tanggal_pengeluaran" class="form-label-modern">
                            <i class="fa-solid fa-calendar-alt"></i> Tanggal
                        </label>
                        <input type="date" id="tambah_tanggal_pengeluaran" name="tanggal_pengeluaran" class="input-modern" value="<?= date('Y-m-d') ?>" required>
                    </div>
 
                    <div class="form-group-modern grid-col-span-2">
                        <label for="tambah_keterangan" class="form-label-modern">
                            <i class="fa-solid fa-info-circle"></i> Keterangan
                        </label>
                        <textarea id="tambah_keterangan" name="keterangan" class="input-modern" placeholder="Masukkan keterangan pengeluaran" rows="4" required></textarea>
                    </div>

                    
                    <div class="form-group-modern">
                        <label for="tambah_kategori" class="form-label-modern">
                            <i class="fa-solid fa-tags"></i> Kategori
                        </label>
                        <div class="input-group-modern">
                            <select id="tambah_kategori" name="id_kategori" class="input-modern" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori_options as $kat): ?>
                                    <option value="<?= htmlspecialchars($kat['id_kategori']) ?>">
                                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if (empty($kategori_options)): ?>
                                    <option value="" disabled>Gagal memuat kategori</option>
                                <?php endif; ?>
                            </select>
                            
                            <button type="button" class="btn-blue-action" id="buka-modal-kategori-btn">
                                <i class="fa-solid fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>

                </div>
                
                <button type="submit" class="btn-green-submit" style="margin-top: 20px;">
                    <i class="fa-solid fa-save"></i> Simpan Pengeluaran
                </button>
            </form>
        </div>
    </div> 
</div>
 
<div class="modal-overlay" id="pembukuan-modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="pembukuan-modal-title">Edit Pengeluaran</h3>
            <button class="modal-close-btn" id="pembukuan-modal-close">&times;</button>
            </div>
        <div class="modal-body" id="pembukuan-modal-body">
            <div class="loading-spinner" style="text-align: center; padding: 40px 0; font-size: 24px; color: #007bff;">
                <i class="fa-solid fa-spinner fa-spin"></i>
            </div>
        </div>
    </div>
</div>
 
<div class="modal-overlay" id="kategori-modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="kategori-modal-title">Tambah Kategori Baru</h3>
            <button class="modal-close-btn" id="kategori-modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-tambah-kategori" action="pembukuan/proses_kategori.php" method="POST">
                <div class="form-group">
                    <label for="nama_kategori_baru">Nama Kategori Baru</label>
                    <input type="text" id="nama_kategori_baru" name="nama_kategori" placeholder="Misal: Transportasi" required>
                </div>
                <button type="submit" class="form-submit-btn" style="width: 100%;">
                    <i class="fa-solid fa-save"></i> Simpan Kategori
                </button>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo $asset_path; ?>/js/pembukuan.js"></script>
 
<script>
document.addEventListener('DOMContentLoaded', function() { 
    document.getElementById('cetakPdfBtn').addEventListener('click', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const jenisTransaksi = document.getElementById('jenis_transaksi').value;
         
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Memproses laporan PDF...',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
 
        let url = 'pembukuan/cetak_pembukuan_pdf.php?';
         
        if (startDate) url += '&start_date=' + encodeURIComponent(startDate);
        if (endDate) url += '&end_date=' + encodeURIComponent(endDate);
        url += '&jenis_transaksi=' + encodeURIComponent(jenisTransaksi);
         
        window.open(url, '_blank');
    });

    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete')) {
            e.preventDefault(); 
            const button = e.target.closest('.btn-delete');
            const id = button.getAttribute('data-id');
            const table = button.getAttribute('data-table') || 'pengeluaran'; 
            Swal.fire({
                title: 'Konfirmasi Hapus Data',
                text: "Anda yakin ingin menghapus data pengeluaran ini? Tindakan ini tidak dapat dibatalkan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true 
            }).then((result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    
                    window.location.href = 'pembukuan/proses_hapus.php?id=' + id + '&table=' + table;
                }
            });
        }
    });
});
</script>

<?php

if (isset($_SESSION['pdf_success'])) {
    $pdf_message = htmlspecialchars($_SESSION['pdf_success']);
    unset($_SESSION['pdf_success']); 
?>
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success', 
            title: '<?php echo $pdf_message; ?>',
            showConfirmButton: false,
            timer: 5000, 
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    });
</script>
<?php
}
?>

<?php
if (isset($_SESSION['toast_status']) && isset($_SESSION['toast_message'])) {
    $status = htmlspecialchars($_SESSION['toast_status']);
    $message = htmlspecialchars($_SESSION['toast_message']);
    unset($_SESSION['toast_status']);
    unset($_SESSION['toast_message']);
?>
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: '<?php echo $status; ?>', 
        title: 'Pemberitahuan',
        text: '<?php echo $message; ?>', 
        showConfirmButton: true, 
    });
});
</script>
<?php
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit')) {
        }
    });
});
</script>

</body>
</html>