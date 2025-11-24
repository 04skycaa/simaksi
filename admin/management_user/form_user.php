<?php
// --- Blok PHP untuk $is_edit, dll. ---
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
    } else {
        $is_edit = false;
        $form_action = 'tambah';
    }
}
?>

<style>
/* ... (Gaya .form-group-static, .form-static-text, .form-container) ... */
.form-group-static { margin-bottom: 1.5rem; }
.form-group-static label { display: block; font-size: 0.9rem; color: #555; margin-bottom: 5px; font-weight: 600; }
.form-static-text { display: block; width: 100%; padding: 10px 14px; background-color: #f0f0f0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; color: #333; font-size: 0.95rem; min-height: 40px; }
/* Baris .form-container { ... } yang kosong dihapus (ini adalah baris 31) */

/* 1. Pastikan input-group bisa menampung ikon (position: relative) */
.input-group { 
    position: relative; 
    margin-bottom: 1.5rem; /* Samakan jarak */
}

/* 2. Gaya untuk semua input (termasuk 'date') */
.input-group input[type="text"],
.input-group input[type="email"],
.input-group input[type="password"],
.input-group input[type="date"] {
    width: 100%;
    padding: 16px 14px; 
    border: 1px solid #056634; 
    border-radius: 100px; 
    box-sizing: border-box;
    font-size: 0.95rem;
    outline: none;
    background-color: transparent;
    padding-right: 45px; 
}

/* Khusus untuk input 'date', kita perlu penyesuaian */
.input-group input[type="date"] {
    color: #555; /* Beri warna agar terlihat */
    padding-right: 14px; /* Tidak perlu padding ikon mata */
}

/* 3. Gaya untuk semua label (termasuk 'date') */
.input-group input[type="text"] + label,
.input-group input[type="email"] + label,
.input-group input[type="password"] + label,
.input-group input[type="date"] + label {
    position: absolute;
    left: 15px;
    top: 16px; 
    color: #555;
    pointer-events: none;
    transition: all 0.2s ease;
}

/* 4. Gaya label saat focus/terisi (termasuk 'date') */
.input-group input[type="text"]:focus + label,
.input-group input[type="text"]:not(:placeholder-shown) + label,
.input-group input[type="email"]:focus + label,
.input-group input[type="email"]:not(:placeholder-shown) + label,
.input-group input[type="password"]:focus + label,
.input-group input[type="password"]:not(:placeholder-shown) + label,
.input-group input[type="date"]:focus + label,
.input-group input[type="date"]:not(:placeholder-shown) + label {
    top: 5px; 
    left: 15px;
    font-size: 0.8rem;
    color: #056634; 
    background-color: #fff; 
    padding: 0 5px;
}

/* Selalu 'naikkan' label untuk input tanggal */
.input-group input[type="date"] + label {
    top: 5px; 
    left: 15px;
    font-size: 0.8rem;
    color: #056634; 
    background-color: #fff; 
    padding: 0 5px;
}


/* 5. GAYA BARU UNTUK IKON MATA */
.toggle-password {
    position: absolute;
    top: 50%;
    right: 18px;
    transform: translateY(-50%);
    cursor: pointer;
    color: #555;
    z-index: 10; 
}
</style>


<div class="form-container">
    <form id="userForm" action="/simaksi/admin/management_user/proses_user.php" method="POST">
        <input type="hidden" name="form_action" value="<?= $form_action ?>">
        
        <?php if ($is_edit): ?>
            <!-- Mode Lihat Detail (Read-only) -->
            <!-- ... (semua input hidden dan div .form-group-static Anda) ... -->
            <input type="hidden" name="id" value="<?= htmlspecialchars($user_data['id'] ?? '') ?>">
            <input type="hidden" name="nama_lengkap" value="<?= htmlspecialchars($user_data['nama_lengkap'] ?? '') ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>">
            <input type="hidden" name="nomor_telepon" value="<?= htmlspecialchars($user_data['nomor_telepon'] ?? '') ?>">
            <input type="hidden" name="alamat" value="<?= htmlspecialchars($user_data['alamat'] ?? '') ?>">
            <!-- Pastikan ini 'peran' (sudah benar) -->
            <input type="hidden" name="peran" value="<?= htmlspecialchars($user_data['peran'] ?? '') ?>">
            
            <div class="form-group-static">
                <label>ID User</label>
                <p class="form-static-text"><?= htmlspecialchars($user_data['id'] ?? '') ?></p>
            </div>
            <div class="form-group-static">
                <label>Nama Lengkap</label>
                <p class="form-static-text"><?= htmlspecialchars($user_data['nama_lengkap'] ?? '') ?></p>
            </div>
            <div class="form-group-static">
                <label>Email</label>
                <p class="form-static-text"><?= htmlspecialchars($user_data['email'] ?? '') ?></p>
            </div>
            
            <!-- Tampilkan NIK dan Tgl Lahir jika ada -->
            <div class="form-group-static">
                <label>NIK</label>
                <p class="form-static-text"><?= htmlspecialchars($user_data['nik'] ?? ' - ') ?></p>
            </div>
            <div class="form-group-static">
                <label>Tanggal Lahir</label>
                <p class="form-static-text"><?= htmlspecialchars($user_data['tanggal_lahir'] ?? ' - ') ?></p>
            </div>
            
            <div class="form-group-static">
                <label>No. Telepon</label>
                <p class="form-static-text"><?= htmlspecialchars($user_data['nomor_telepon'] ?? ' - ') ?></p>
            </div>
            <div class="form-group-static">
                <label>Alamat</label>
                <p class="form-static-text"><?= htmlspecialchars($user_data['alamat'] ?? ' - ') ?></p>
            </div>
            <div class="form-group-static">
                <label>Peran</label>
                <!-- Pastikan ini 'peran' (sudah benar) -->
                <p class="form-static-text"><?= htmlspecialchars(ucfirst($user_data['peran'] ?? '')) ?></p>
            </div>

        <?php else: ?>
            <!-- Mode 'Tambah Admin' (Bisa edit) -->
            <div class="input-group">
                <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder=" " required>
                <label for="nama_lengkap">Nama Lengkap</label>
            </div>
            <div class="input-group">
                <input type="email" id="email" name="email" placeholder=" " required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " required>
                <label for="password">Password Baru</label>
                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
            </div>
            
            <!-- --- INPUT BARU DITAMBAHKAN DI SINI --- -->
            <div class="input-group">
                <input type="text" id="nik" name="nik" placeholder=" ">
                <label for="nik">NIK (Opsional)</label>
            </div>
            <div class="input-group">
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" placeholder=" ">
                <label for="tanggal_lahir">Tanggal Lahir (Opsional)</label>
            </div>
            <!-- --- SELESAI PENAMBAHAN INPUT --- -->

            <div class="input-group">
                <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder=" ">
                <label for="nomor_telepon">No. Telepon (Opsional)</label>
            </div>
            <div class="input-group">
                <input type="text" id="alamat" name="alamat" placeholder=" ">
                <label for="alamat">Alamat (Opsional)</label>
            </div>
            
            <div class="form-group-static">
                <label>Peran</label>
                <p class="form-static-text">Admin</p>
            </div>
            <input type="hidden" name="peran" value="admin">

        <?php endif; ?>
        
        <!-- Tombol dinamis -->
        <?php if ($form_action == 'tambah'): ?>
            <button type="submit" class="form-submit-btn">
                <i class="fa-solid fa-save"></i> Simpan
            </button>
        <?php else: ?>
            <button type="button" class="form-submit-btn" onclick="window.location.href='http://localhost/simaksi/admin/index.php?page=user'">
                <i class="fa-solid fa-times"></i> Tutup
            </button>
        <?php endif; ?>
    </form>
</div>