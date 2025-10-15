<?php
require __DIR__ . '/../../config/supabase.php';

$is_edit = isset($_GET['id']);
$kuota_data = null;
$form_action = 'tambah';

if ($is_edit) {
    $id = $_GET['id'];
    $endpoint = 'kuota_harian?id_kuota=eq.' . $id . '&select=*&limit=1';
    $data = supabase_request('GET', $endpoint);
    
    if ($data && !isset($data['error']) && count($data) > 0) {
        $kuota_data = $data[0];
        $form_action = 'edit';
    }
}
?>

<div class="form-container">
    <form id="kuotaForm" action="/simaksi/admin/kuota_pendakian/proses_kuota.php" method="POST">
        <input type="hidden" name="action" value="<?= $form_action ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id_kuota" value="<?= htmlspecialchars($kuota_data['id_kuota'] ?? '') ?>">
        <?php endif; ?>

        <?php if ($is_edit): ?>
            <div class="form-group">
                <label for="tanggal_kuota">Tanggal Kuota</label>
                <input type="date" id="tanggal_kuota" name="tanggal_kuota" value="<?= htmlspecialchars($kuota_data['tanggal_kuota'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="kuota_maksimal">Kuota Maksimal</label>
                <input type="number" id="kuota_maksimal" name="kuota_maksimal" value="<?= htmlspecialchars($kuota_data['kuota_maksimal'] ?? '') ?>" required>
            </div>
        <?php else: ?>
            <div class="input-group">
                <input type="date" id="tanggal_kuota" name="tanggal_kuota" placeholder=" " required>
                <label for="tanggal_kuota">Tanggal Kuota</label>
            </div>
            <div class="input-group">
                <input type="number" id="kuota_maksimal" name="kuota_maksimal" placeholder=" " required>
                <label for="kuota_maksimal">Kuota Maksimal</label>
            </div>
        <?php endif; ?>
        
        <button type="submit" class="form-submit-btn">
            <i class="fa-solid fa-save"></i> Simpan
        </button>
    </form>
</div>