<?php
require __DIR__ . '/../../config/supabase.php';

$is_edit = isset($_GET['id']);
$pengeluaran_data = null;
$form_action = 'tambah';

if ($is_edit) {
    $id = $_GET['id'];
    // PERUBAHAN: Menggunakan 'id_pengeluaran' untuk query
    $endpoint = 'pengeluaran?id_pengeluaran=eq.' . $id . '&select=*&limit=1';
    $data = supabase_request('GET', $endpoint); 
    
    if ($data && !isset($data['error']) && count($data) > 0) {
        $pengeluaran_data = $data[0];
        $form_action = 'edit';
    }
}
?>
<div class="form-container">
    <form id="pengeluaranForm" action="/simaksi/admin/pembukuan/proses_pembukuan.php" method="POST">
        <input type="hidden" name="form_action" value="<?= $form_action ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id_pengeluaran" value="<?= htmlspecialchars($pengeluaran_data['id_pengeluaran'] ?? '') ?>">
        <?php endif; ?>

        <div class="input-group">
            <input type="date" id="tanggal_pengeluaran" name="tanggal_pengeluaran" placeholder=" " value="<?= htmlspecialchars($pengeluaran_data['tanggal_pengeluaran'] ?? date('Y-m-d')) ?>" required>
            <label for="tanggal_pengeluaran">Tanggal Pengeluaran</label>
        </div>

        <div class="input-group">
            <input type="number" id="jumlah" name="jumlah" placeholder=" " value="<?= htmlspecialchars($pengeluaran_data['jumlah'] ?? '') ?>" required>
            <label for="jumlah">Jumlah (Rp)</label>
        </div>
        
        <div class="input-group">
            <textarea id="keterangan" name="keterangan" placeholder=" " required><?= htmlspecialchars($pengeluaran_data['keterangan'] ?? '') ?></textarea>
            <label for="keterangan">Keterangan</label>
        </div>
        
        <button type="submit" class="form-submit-btn">
            <i class="fa-solid fa-save"></i> Simpan
        </button>
    </form>
</div>