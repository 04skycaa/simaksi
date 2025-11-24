<?php
// Selalu mulai dengan header JSON
header('Content-Type: application/json');

// Memuat konfigurasi Supabase
require __DIR__ . '/../../config/supabase.php';

// Fungsi helper untuk mengirim respons JSON
function send_json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

// Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Metode request tidak valid.');
}

$action = $_POST['form_action'] ?? '';

// --- LOGIKA UTAMA UNTUK MENAMBAH PENGGUNA BARU (VERSI 1 LANGKAH DENGAN METADATA) ---
if ($action === 'tambah') {
    
    // Deklarasikan variabel global
    global $supabaseUrl, $supabaseKey;

    // Pengecekan error konfigurasi
    if (empty($supabaseUrl) || empty($supabaseKey)) {
        send_json_response(false, 'Error Konfigurasi: $supabaseUrl atau $supabaseKey tidak ditemukan.');
    }

    // 1. Ambil data dari form
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $peran = $_POST['peran'] ?? 'admin'; // Di form, ini dikirim sebagai 'admin'

    // Validasi dasar
    if (empty($email) || empty($password) || empty($nama_lengkap)) {
        send_json_response(false, 'Data tidak lengkap (Email, Password, dan Nama Lengkap wajib diisi).');
    }

    //
    // --- BUAT 'user_metadata' (INI YANG AKAN DIBACA OLEH TRIGGER) ---
    //
    
    $user_metadata = [
        'nama_lengkap' => $nama_lengkap,
        'peran' => $peran, // Trigger akan membaca ini
        'email' => $email 
    ];

    // Tambahkan data opsional (Kirim 'null' jika kosong)
    $user_metadata['nomor_telepon'] = !empty($_POST['nomor_telepon']) ? $_POST['nomor_telepon'] : null;
    $user_metadata['alamat'] = !empty($_POST['alamat']) ? $_POST['alamat'] : null;
    $user_metadata['nik'] = !empty($_POST['nik']) ? $_POST['nik'] : null;
    $user_metadata['tanggal_lahir'] = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
    
    //
    // --- KIRIM SEMUA DATA KE AUTH UNTUK DITANGANI TRIGGER ---
    //

    $auth_url = $supabaseUrl . '/auth/v1/admin/users';
    
    $post_data = [
        'email' => $email,
        'password' => $password,
        'email_confirm' => true,
        'user_metadata' => $user_metadata // Kirim semua data profil di sini
    ];

    $auth_data = json_encode($post_data);

    $ch_auth = curl_init($auth_url);
    curl_setopt($ch_auth, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_auth, CURLOPT_POST, true);
    curl_setopt($ch_auth, CURLOPT_POSTFIELDS, $auth_data);
    curl_setopt($ch_auth, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $supabaseKey,
        'Authorization: ' . 'Bearer ' . $supabaseKey
    ]);

    $auth_response_body = curl_exec($ch_auth);
    $auth_http_code = curl_getinfo($ch_auth, CURLINFO_HTTP_CODE);
    
    $auth_response_data = json_decode($auth_response_body, true);

    // Cek jika Gagal (Trigger akan mengirim error jika gagal)
    if ($auth_http_code < 200 || $auth_http_code >= 300) {
        $error_message = $auth_response_data['msg'] ?? $auth_response_data['message'] ?? 'Gagal membuat pengguna.';
        // Ini adalah error yang Anda lihat
        send_json_response(false, 'Gagal: ' . $error_message); 
    }

    // --- SEMUA BERHASIL ---
    send_json_response(true, 'Admin baru berhasil ditambahkan!');

} else {
    send_json_response(false, 'Aksi tidak diketahui.');
}
?>