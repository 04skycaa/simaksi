<?php
// Lokasi: /simaksi/api/update_pendakian.php
// Menangani permintaan POST dari JS dan mengirimkan PATCH ke Supabase

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

// Ambil data yang diperlukan
$idReservasi = $data['id_reservasi'] ?? null;
$namaLengkap = $data['nama_lengkap'] ?? null;
$nik = $data['nik'] ?? null;
$alamat = $data['alamat'] ?? null;
$nomorTelepon = $data['nomor_telepon'] ?? null;
$kontakDarurat = $data['kontak_darurat'] ?? null;

// Validasi ID Reservasi
if (empty($idReservasi)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'ID Reservasi wajib diisi untuk pembaruan data.']);
    exit;
}

// Data yang akan diupdate di Supabase
$updatePayload = [
    'nama_lengkap' => $namaLengkap,
    'nik' => $nik,
    'alamat' => $alamat,
    'nomor_telepon' => $nomorTelepon,
    'kontak_darurat' => $kontakDarurat,
];

$tableName = 'pendaki_rombongan';
// Endpoint untuk PATCH (Update) dengan filter Primary Key
$endpoint = $tableName . '?id_reservasi=eq.' . urlencode($idReservasi);

// Lakukan request PATCH ke Supabase
$response = supabase_request('PATCH', $endpoint, $updatePayload);

// Cek respons
if (!$response || isset($response['error'])) {
    http_response_code(500);
    $errorMessage = $response['message'] ?? 'Gagal memperbarui data. (Supabase Error)';
    echo json_encode(['error' => true, 'message' => $errorMessage]);
    exit;
}

// Sukses
echo json_encode(['error' => false, 'message' => 'Data pendaki rombongan berhasil diperbarui.']);
?>