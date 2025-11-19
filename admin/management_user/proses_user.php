<?php
// Mengaktifkan laporan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../config/supabase.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$action = $_POST['form_action'] ?? null;

switch ($action) {
    case 'tambah':
        tambahUser();
        break;
    case 'edit':
        editUser();
        break;
    case 'hapus':
        hapusUser();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
        break;
}

function tambahUser() {
    // Ambil semua data dari form
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $nomor_telepon = $_POST['nomor_telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $peran = $_POST['peran'] ?? '';

    if (empty($nama_lengkap) || empty($email) || empty($peran)) {
        echo json_encode(['success' => false, 'message' => 'Nama Lengkap, Email, dan Peran wajib diisi.']);
        return;
    }
    
    // Cek apakah email sudah ada
    $check = supabase_request('GET', 'profiles?email=eq.' . urlencode($email));
    if (!empty($check)) {
        echo json_encode(['success' => false, 'message' => 'Email ' . $email . ' sudah terdaftar.']);
        return;
    }

    $data = [
        'nama_lengkap' => $nama_lengkap,
        'email' => $email,
        'nomor_telepon' => $nomor_telepon,
        'alamat' => $alamat,
        'peran' => $peran, // <-- UBAH 'peran' menjadi 'peran_pengguna'
    ];

    // ... (kode lain di dalam fungsi tambahUser)

    $result = supabase_request('POST', 'profiles', $data);

    // UBAH MENJADI KODE INI UNTUK DEBUGGING
    if (isset($result['error']) || !$result) {
        // Siapkan pesan error default
        $errorMessage = 'Gagal menambahkan pengguna ke database.';
        
        // Jika Supabase memberikan pesan error yang lebih spesifik, kita akan menampilkannya
        if (!empty($result['message'])) {
            $errorMessage = 'Error dari Supabase: ' . $result['message'];
        }
        
        // Kirim respons error yang lebih detail
        echo json_encode(['success' => false, 'message' => $errorMessage, 'debug_info' => $result]);

    } else {
        echo json_encode(['success' => true, 'message' => 'Pengguna baru berhasil ditambahkan.']);
    }
}

function editUser() {
    $id = $_POST['id'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $nomor_telepon = $_POST['nomor_telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    if (empty($id) || empty($nama_lengkap) || empty($email) || empty($peran)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
        return;
    }

    $data = [
        'nama_lengkap' => $nama_lengkap,
        'email' => $email,
        'nomor_telepon' => $nomor_telepon,
        'alamat' => $alamat,
    ];

    $result = supabase_request('PATCH', 'profiles?id=eq.' . $id, $data);

    if (!$result || isset($result['error'])) {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data pengguna.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Data pengguna berhasil diperbarui.']);
    }
}

function hapusUser() {
    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID Pengguna tidak ditemukan.']);
        return;
    }
    
    // Catatan: Ini hanya menghapus dari tabel 'profiles', tidak dari Supabase Auth.
    $result = supabase_request('DELETE', 'profiles?id=eq.' . $id);

    if (isset($result['error'])) {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus pengguna.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Pengguna berhasil dihapus.']);
    }
}
?>