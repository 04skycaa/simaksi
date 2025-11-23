<?php
require_once __DIR__ . '/config.php'; 

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PATCH, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method !== 'PATCH' && $method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

if (!function_exists('makeSupabaseRequest')) {
     http_response_code(500);
     echo json_encode(['success' => false, 'message' => 'Fungsi makeSupabaseRequest tidak ditemukan di config.php']);
     exit;
}


$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['id_reservasi'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID Reservasi tidak ditemukan.']);
    exit;
}

$id_reservasi = $data['id_reservasi'];
$success = true;
$message = '';

try {
    $reservasi_update_data = [
        'tanggal_pendakian' => $data['tanggal_pendakian'] ?? null,
        'jumlah_pendaki' => isset($data['jumlah_pendaki']) ? (int)$data['jumlah_pendaki'] : null,
        'jumlah_tiket_parkir' => isset($data['jumlah_tiket_parkir']) ? (int)$data['jumlah_tiket_parkir'] : null,
        'status' => $data['status'] ?? null,
        'status_sampah' => $data['status_sampah'] ?? null,
        'total_harga' => $data['total_harga'] ?? null, 
    ];
    $reservasi_update_data = array_filter($reservasi_update_data, function($value) {
        return !is_null($value);
    });

    if (!empty($reservasi_update_data)) {
        $endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi);
        $result = makeSupabaseRequest($endpoint, 'PATCH', $reservasi_update_data); 
        
        if (isset($result['error'])) {
            $success = false;
            $message = " Gagal update reservasi utama: " . $result['error'];
        } else {
            $message = "Data berhasil diperbarui.";
        }
    } else {
        $message = "Tidak ada data untuk diperbarui.";
    }

} catch (Exception $e) {
    $success = false;
    $message .= " Error update reservasi utama: " . $e->getMessage();
}
if ($success) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => $message]);
}
?>