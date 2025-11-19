<?php
session_start();
include __DIR__ . '/../../config/supabase.php';

// Cek apakah method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=pembukuan&tab=laporan&status=error&msg=Invalid request');
    exit;
}

// Ambil data dari form
$form_action = $_POST['form_action'] ?? '';
$jumlah = $_POST['jumlah'] ?? 0;
$tanggal = $_POST['tanggal_pengeluaran'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';
$id_kategori = $_POST['id_kategori'] ?? null;
$id_pengeluaran = $_POST['id_pengeluaran'] ?? null; // Hanya ada saat edit

// Validasi dasar
if (empty($jumlah) || empty($tanggal) || empty($keterangan) || empty($id_kategori)) {
    $tab = ($form_action == 'tambah') ? 'tambah' : 'laporan';
    // Set pesan error di session
    $_SESSION['toast_status'] = 'error';
    $_SESSION['toast_message'] = 'Semua field wajib diisi.';
    header('Location: ../index.php?page=pembukuan&tab=' . $tab);
    exit;
}

// === PERBAIKAN DIMULAI DI SINI ===

// 1. Validasi Sesi: Pastikan admin sudah login
// (Asumsi UUID admin disimpan di 'user_id' saat login)
if (!isset($_SESSION['user_id'])) {
    $tab = ($form_action == 'tambah') ? 'tambah' : 'laporan';
    $_SESSION['toast_status'] = 'error';
    $_SESSION['toast_message'] = 'Sesi Anda tidak valid. Silakan login kembali.';
    header('Location: ../index.php?page=pembukuan&tab=' . $tab);
    exit;
}

// 2. Siapkan data untuk dikirim ke Supabase
$data_to_send = [
    'jumlah' => $jumlah,
    'tanggal_pengeluaran' => $tanggal,
    'keterangan' => $keterangan,
    'id_kategori' => $id_kategori,
    
    // PERBAIKAN: Menambahkan 'id_admin' dari Sesi.
    // Nama kolom di database Anda adalah 'id_admin'.
    'id_admin' => $_SESSION['user_id'] 
];

// === PERBAIKAN SELESAI ===


$result = null;

if ($form_action == 'tambah') {
    // ==================
    // LOGIKA TAMBAH DATA
    // ==================
    $result = supabase_request('POST', 'pengeluaran', $data_to_send);
    // PERBAIKAN: Redirect ke tab Laporan agar data baru terlihat
    $tab_redirect = 'laporan'; 
    $success_msg = 'Pengeluaran baru berhasil dicatat';
    $error_msg = 'Gagal mencatat pengeluaran';

} elseif ($form_action == 'edit' && !empty($id_pengeluaran)) {
    // ==================
    // LOGIKA EDIT DATA
    // ==================
    // Gunakan method PATCH dan filter ID
    $result = supabase_request('PATCH', 'pengeluaran?id_pengeluaran=eq.' . $id_pengeluaran, $data_to_send);
    $tab_redirect = 'laporan'; // Kembali ke tab laporan
    $success_msg = 'Data pengeluaran berhasil diperbarui';
    $error_msg = 'Gagal memperbarui data pengeluaran';

} else {
    // Action tidak valid
    $_SESSION['toast_status'] = 'error';
    $_SESSION['toast_message'] = 'Aksi tidak valid';
    header('Location: ../index.php?page=pembukuan&tab=laporan');
    exit;
}

// Cek hasil request
if ($result && !isset($result['error'])) {
    // Berhasil
    $_SESSION['toast_status'] = 'success';
    $_SESSION['toast_message'] = $success_msg;
    header('Location: ../index.php?page=pembukuan&tab=' . $tab_redirect);
    exit;
} else {
    // Gagal
    $api_error = $result['message'] ?? $error_msg;
    $_SESSION['toast_status'] = 'error';
    $_SESSION['toast_message'] = $api_error;
    header('Location: ../index.php?page=pembukuan&tab=' . $tab_redirect);
    exit;
}
?>