<?php
// ==========================================================
// FILE: api/kuota.php
// Mengambil data kuota dari tabel 'kuota_harian'
// ==========================================================
require_once 'config.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $tanggal = $_GET['tanggal'] ?? null;
    
    if (!$tanggal) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Parameter tanggal wajib diisi.']);
        exit;
    }
    
    // Query ke tabel kuota_harian
    $endpoint = 'kuota_harian?select=kuota_maksimal,kuota_terpesan&tanggal_kuota=eq.' . urlencode($tanggal);
    $response = makeSupabaseRequest($endpoint, 'GET');
    
    if (isset($response['error'])) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $response['error']]);
        exit;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $response['data'] ?? [] // Kembalikan array kosong jika tidak ada kuota
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
