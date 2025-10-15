<?php
require __DIR__ . '/../../config/supabase.php';

$is_edit = isset($_GET['id']);
$user_data = null;
$form_action = 'tambah';
$form_title = 'Tambah Pengguna Baru';

if ($is_edit) {
    $id = $_GET['id'];
    $endpoint = 'profiles?id=eq.' . $id . '&select=*&limit=1';
    $data = supabase_request('GET', $endpoint);
    
    if ($data && !isset($data['error']) && count($data) > 0) {
        $user_data = $data[0];
        $form_action = 'edit';
        $form_title = 'Edit Data Pengguna';
    }
}
?>

<div class="form-container">
    <form id="userForm" action="/simaksi/admin/management_user/proses_user.php" method="POST">
        <input type="hidden" name="form_action" value="<?= $form_action ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($user_data['id'] ?? '') ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="id">ID User</label>
            <input type="text" id="id" value="<?= htmlspecialchars($user_data['id'] ?? 'Akan dibuat otomatis') ?>" readonly>
        </div>

        <div class="input-group">
            <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder=" " value="<?= htmlspecialchars($user_data['nama_lengkap'] ?? '') ?>" required>
            <label for="nama_lengkap">Nama Lengkap</label>
        </div>

        <div class="input-group">
            <input type="email" id="email" name="email" placeholder=" " value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required>
            <label for="email">Email</label>
        </div>

        <div class="input-group">
            <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder=" " value="<?= htmlspecialchars($user_data['nomor_telepon'] ?? '') ?>">
            <label for="nomor_telepon">No. Telepon</label>
        </div>
        
        <div class="input-group">
            <input type="text" id="alamat" name="alamat" placeholder=" " value="<?= htmlspecialchars($user_data['alamat'] ?? '') ?>">
            <label for="alamat">Alamat</label>
        </div>

        <div class="form-group">
            <label for="peran">Peran</label>
            <?php if ($is_edit): ?>
                <input type="text" value="<?= htmlspecialchars(ucfirst($user_data['peran'] ?? '')) ?>" readonly>
                <input type="hidden" name="peran" value="<?= htmlspecialchars($user_data['peran'] ?? '') ?>">
            <?php else: ?>
                <select id="peran" name="peran" required>
                    <option value="" disabled selected>Pilih Peran...</option>
                    <option value="pendaki">Pendaki</option>
                    <option value="admin">Admin</option>
                </select>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="form-submit-btn">
            <i class="fa-solid fa-save"></i> Simpan
        </button>
    </form>
</div>