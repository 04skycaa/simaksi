<?php
session_start();
// Path __DIR__ menunjuk ke /admin/pembukuan/, jadi ../../ menunjuk ke /simaksi/
include __DIR__ . '/../../config/supabase.php';

// Mengatur header sebagai JSON
header('Content-Type: application/json');

// Pastikan admin login (sesuaikan dengan session Anda)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk menghapus data.']);
    exit;
}

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID data tidak ditemukan.']);
    exit;
}

$id_pengeluaran = $_GET['id'];

// Kirim request DELETE ke Supabase
$result = supabase_request('DELETE', 'pengeluaran?id_pengeluaran=eq.' . $id_pengeluaran);

// Cek hasil request
if ($result === null || (is_array($result) && !isset($result['error']))) {
    // Berhasil
    echo json_encode(['success' => true, 'message' => 'Data pengeluaran berhasil dihapus.']);
} else {
    // Gagal
    $api_error = $result['message'] ?? 'Gagal menghapus data.';
    echo json_encode(['success' => false, 'message' => $api_error]);
}
exit;
?>