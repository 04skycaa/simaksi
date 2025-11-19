<?php
// Path __DIR__ menunjuk ke /admin/pembukuan/, jadi ../../ menunjuk ke /simaksi/
require __DIR__ . '/../../config/supabase.php';

$is_edit = isset($_GET['id']);
$pengeluaran_data = null;
$form_action = 'edit'; // Modal ini selalu untuk edit

if ($is_edit) {
    $id = $_GET['id'];
    // Ambil juga 'id_kategori'
    $endpoint = 'pengeluaran?id_pengeluaran=eq.' . $id . '&select=*,id_kategori&limit=1';
    $data = supabase_request('GET', $endpoint); 
    
    if ($data && !isset($data['error']) && count($data) > 0) {
        $pengeluaran_data = $data[0];
    } else {
        echo "<p>Error: Data pengeluaran tidak ditemukan.</p>";
        exit;
    }
} else {
     echo "<p>Error: ID tidak disediakan.</p>";
     exit;
}

// Ambil daftar kategori untuk dropdown
$kategori_data = supabase_request('GET', 'kategori_pengeluaran?select=id_kategori,nama_kategori');
$kategori_options = $kategori_data && !isset($kategori_data['error']) ? $kategori_data : [];

?>
<!-- 
=============================================
PERBAIKAN: 
Form ini sekarang menggunakan kelas CSS dari style.css Anda
(class="form-group", <label>, <input>, class="form-submit-btn")
agar tampilannya sesuai.
=============================================
-->
<form id="pengeluaranForm" action="pembukuan/proses_pembukuan.php" method="POST">
    <input type="hidden" name="form_action" value="<?= $form_action ?>">
    <input type="hidden" name="id_pengeluaran" value="<?= htmlspecialchars($pengeluaran_data['id_pengeluaran'] ?? '') ?>">

    <!-- Tanggal -->
    <div class="form-group">
        <label for="edit_tanggal">Tanggal</label>
        <input type="date" id="edit_tanggal" name="tanggal_pengeluaran" value="<?= htmlspecialchars(date('Y-m-d', strtotime($pengeluaran_data['tanggal_pengeluaran']))) ?>" required>
    </div>

    <!-- Jumlah Pengeluaran -->
    <div class="form-group">
        <label for="edit_jumlah">Jumlah Pengeluaran (Rp)</label>
        <input type="number" id="edit_jumlah" name="jumlah" value="<?= htmlspecialchars($pengeluaran_data['jumlah'] ?? '') ?>" required>
    </div>

    <!-- Keterangan -->
    <div class="form-group">
        <label for="edit_keterangan">Keterangan</label>
        <!-- Asumsi style.css Anda juga punya style untuk textarea di modal -->
        <textarea id="edit_keterangan" name="keterangan" rows="4" required><?= htmlspecialchars($pengeluaran_data['keterangan'] ?? '') ?></textarea>
    </div>

    <!-- Kategori -->
    <div class="form-group">
        <label for="edit_kategori">Kategori</label>
         <!-- Asumsi style.css Anda juga punya style untuk select di modal -->
        <select id="edit_kategori" name="id_kategori" required>
            <option value="">Pilih Kategori</option>
                <?php foreach ($kategori_options as $kat): ?>
                    <option value="<?= htmlspecialchars($kat['id_kategori']) ?>" 
                        <?php 
                        // Pilih kategori yang sesuai dengan data yang diedit
                        if ($is_edit && $kat['id_kategori'] == $pengeluaran_data['id_kategori']) {
                            echo 'selected';
                        } 
                        ?>
                    >
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
                <?php if (empty($kategori_options)): ?>
                    <option value="" disabled>Gagal memuat kategori</option>
                <?php endif; ?>
        </select>
    </div>
    
    <!-- PERBAIKAN: Menggunakan kelas .form-submit-btn -->
    <button type="submit" class="form-submit-btn">
        <i class="fa-solid fa-save"></i> Simpan Perubahan
    </button>
</form>