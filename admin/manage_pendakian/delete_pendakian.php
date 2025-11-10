<?php
// Lokasi: /simaksi/api/delete_pendakian.php
// Menangani permintaan POST dari JS dan mengirimkan DELETE ke Supabase

header('Content-Type: application/json');

// Asumsi: Path ini benar relatif terhadap lokasi file API
include __DIR__ . '/../../config/supabase.php'; 

// Cek apakah request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Metode request tidak diizinkan.']);
    exit;
}

// Baca data JSON dari body request
$data = json_decode(file_get_contents("php://input"), true);

$idReservasi = $data['id_reservasi'] ?? null;

// Validasi ID Reservasi
if (empty($idReservasi)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'ID Reservasi wajib diisi untuk penghapusan data.']);
    exit;
}

$tableName = 'pendaki_rombongan';
// Endpoint untuk DELETE dengan filter Primary Key
$endpoint = $tableName . '?id_reservasi=eq.' . urlencode($idReservasi);

// Lakukan request DELETE ke Supabase
$response = supabase_request('DELETE', $endpoint);

// Cek respons
if (!$response || isset($response['error'])) {
    http_response_code(500);
    $errorMessage = $response['message'] ?? 'Gagal menghapus data. (Supabase Error)';
    echo json_encode(['error' => true, 'message' => $errorMessage]);
    exit;
}

// Sukses (Supabase DELETE yang berhasil biasanya mengembalikan array kosong [])
echo json_encode(['error' => false, 'message' => 'Data pendaki rombongan berhasil dihapus.']);
?>