<?php
// ==========================================================
// FILE: api/pengguna.php
// Mengambil daftar ID dan Nama dari tabel 'profiles'
// ==========================================================
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Ambil hanya ID dan nama_lengkap dari tabel profiles
    $response = makeSupabaseRequest('profiles?select=id,nama_lengkap', 'GET');
    
    if (isset($response['error'])) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $response['error']]);
        exit;
    }
    
    // Kembalikan data dalam format yang diharapkan oleh frontend
    echo json_encode(['status' => 'success', 'data' => $response['data']]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
