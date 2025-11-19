<?php
// FILE BARU: /simaksi/admin/pembukuan/proses_kategori.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Path __DIR__ menunjuk ke /admin/pembukuan/, jadi ../../ menunjuk ke /simaksi/
require __DIR__ . '/../../config/supabase.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

// Ambil nama kategori baru dari form
$nama_kategori = $_POST['nama_kategori'] ?? '';

if (empty($nama_kategori)) {
    echo json_encode(['success' => false, 'message' => 'Nama kategori tidak boleh kosong.']);
    return;
}

// Siapkan data untuk disimpan
// (Nama kolom di database Anda adalah 'nama_kategori')
$data = [
    'nama_kategori' => $nama_kategori
];

// Kirim ke tabel 'kategori_pengeluaran'
// Fungsi supabase_request kita sudah diatur untuk 'Prefer: return=representation'
// yang akan mengembalikan data yang baru dibuat
$result = supabase_request('POST', 'kategori_pengeluaran', $data);

// Cek jika ada error
if (isset($result['error']) || !$result || !isset($result[0])) {
    $errorMessage = $result['error']['message'] ?? 'Gagal menambahkan data ke database.';
    // Cek jika error karena duplikat
    if (isset($result['error']['details']) && strpos($result['error']['details'], 'duplicate key') !== false) {
        $errorMessage = "Nama kategori '{$nama_kategori}' sudah ada.";
    }
    echo json_encode(['success' => false, 'message' => $errorMessage]);
} else {
    // Sukses! Kirim kembali data kategori baru ke JavaScript
    $newCategory = $result[0];
    echo json_encode([
        'success' => true, 
        'message' => 'Kategori baru berhasil ditambahkan.',
        'newCategory' => [
            // Kirim kembali 'id_kategori' dan 'nama_kategori'
            'id' => $newCategory['id_kategori'],
            'nama' => $newCategory['nama_kategori']
        ]
    ]);
}
?>