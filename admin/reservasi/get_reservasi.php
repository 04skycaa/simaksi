<?php
include __DIR__ . '/../../config/supabase.php';

if (!isset($_GET['id'])) {
    echo '<p>Error: ID Reservasi tidak ditemukan.</p>';
    exit;
}

$id_reservasi = $_GET['id'];
$reservasi_endpoint = 'reservasi?id_reservasi=eq.' . $id_reservasi . '&select=*,profiles(nama_lengkap,email)&limit=1';
$reservasi_data = supabase_request('GET', $reservasi_endpoint);
$barang_endpoint = 'barang_bawaan_sampah?id_reservasi=eq.' . $id_reservasi;
$barang_data = supabase_request('GET', $barang_endpoint);

if (!$reservasi_data || empty($reservasi_data)) {
    echo '<p>Error: Data reservasi tidak ditemukan.</p>';
    exit;
}

$reservasi = $reservasi_data[0];

?>

<div class="detail-reservasi">
    <h4>Informasi Pemesan</h4>
    <p><strong>Kode:</strong> <?= htmlspecialchars($reservasi['kode_reservasi']) ?></p>
    <p><strong>Nama Ketua:</strong> <?= htmlspecialchars($reservasi['profiles']['nama_lengkap'] ?? 'N/A') ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($reservasi['profiles']['email'] ?? 'N/A') ?></p>
    <p><strong>Tanggal Pendakian:</strong> <?= date('d F Y', strtotime($reservasi['tanggal_pendakian'])) ?></p>
    <p><strong>Jumlah Pendaki:</strong> <?= htmlspecialchars($reservasi['jumlah_pendaki']) ?> orang</p>
    <p><strong>Jumlah Tiket Parkir:</strong> <?= htmlspecialchars($reservasi['jumlah_tiket_parkir']) ?> tiket</p>
    <p><strong>Total Bayar:</strong> Rp <?= number_format($reservasi['total_harga'], 0, ',', '.') ?></p>
    <p><strong>Status Saat Ini:</strong> <?= htmlspecialchars($reservasi['status']) ?></p>

    <hr>
    <h4>Pengecekan Barang Bawaan & Potensi Sampah</h4>
    
    <?php if ($barang_data && !empty($barang_data)): ?>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Jenis Sampah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($barang_data as $barang): ?>
                    <tr>
                        <td><?= htmlspecialchars($barang['nama_barang']) ?></td>
                        <td><?= htmlspecialchars($barang['jumlah']) ?></td>
                        <td><?= htmlspecialchars($barang['jenis_sampah']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tidak ada data barang bawaan yang dilaporkan.</p>
    <?php endif; ?>

    <hr>
    
    <div class="modal-actions">
        <?php if ($reservasi['status'] == 'Menunggu Validasi'): ?>
            <button class="btn green btn-setujui" data-id="<?= $id_reservasi ?>">
                <i class="fa-solid fa-check"></i> Setujui Reservasi
            </button>
            <button class="btn orange btn-tolak" data-id="<?= $id_reservasi ?>">
                <i class="fa-solid fa-times"></i> Tolak Reservasi
            </button>
        <?php else: ?>
            <p>Aksi validasi tidak tersedia untuk status "<?= htmlspecialchars($reservasi['status']) ?>".</p>
        <?php endif; ?>
    </div>
</div>