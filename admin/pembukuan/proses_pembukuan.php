<?php
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
        tambahPengeluaran();
        break;
    case 'edit':
        editPengeluaran();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
        break;
}

function tambahPengeluaran() {
    // PERUBAHAN: Menggunakan 'tanggal_pengeluaran' dari form
    $tanggal = $_POST['tanggal_pengeluaran'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    // Ganti dengan ID admin dari sesi login Anda
    $id_admin = '3b3e7f63-30dd-4ce0-bd31-95d9...'; 
    $id_kat = 1; // Default kategori sesuai database Anda

    if (empty($tanggal) || empty($jumlah) || empty($keterangan)) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
        return;
    }
    
    $data = [
        // PERUBAHAN: Menyesuaikan nama kolom DB
        'tanggal_pengeluaran' => $tanggal,
        'jumlah' => $jumlah,
        'keterangan' => $keterangan,
        'id_admin' => $id_admin,
        'id_kat' => $id_kat
    ];

    $result = supabase_request('POST', 'pengeluaran', $data);

    if (isset($result['error']) || !$result) {
        $errorMessage = $result['message'] ?? 'Gagal menambahkan data.';
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Data pengeluaran berhasil ditambahkan.']);
    }
}

function editPengeluaran() {
    // PERUBAHAN: Menggunakan 'id_pengeluaran' dan 'tanggal_pengeluaran'
    $id = $_POST['id_pengeluaran'] ?? '';
    $tanggal = $_POST['tanggal_pengeluaran'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';

    if (empty($id) || empty($tanggal) || empty($jumlah) || empty($keterangan)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap untuk diedit.']);
        return;
    }

    $data = [
        // PERUBAHAN: Menyesuaikan nama kolom DB
        'tanggal_pengeluaran' => $tanggal,
        'jumlah' => $jumlah,
        'keterangan' => $keterangan,
    ];

    // PERUBAHAN: Menggunakan 'id_pengeluaran' untuk query
    $result = supabase_request('PATCH', 'pengeluaran?id_pengeluaran=eq.' . $id, $data);

    if (!$result || isset($result['error'])) {
        $errorMessage = $result['message'] ?? 'Gagal memperbarui data.';
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Data pengeluaran berhasil diperbarui.']);
    }
}
?>