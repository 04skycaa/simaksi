<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!function_exists('makeSupabaseRequest')) {
     http_response_code(500);
     echo json_encode(['success' => false, 'message' => 'Fungsi makeSupabaseRequest tidak ditemukan di config.php']);
     exit;
}

$id_reservasi = $_GET['id_reservasi'] ?? null;
if (!$id_reservasi) {
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'Parameter id_reservasi tidak ditemukan.']);
    exit;
}

$detail = [];
$success = true;
$message = 'Detail reservasi berhasil diambil.';

try {
    $select_reservasi = '*,profiles(nama_lengkap)';
    $endpoint_reservasi = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi) . '&select=' . urlencode($select_reservasi) . '&limit=1';
    $response = makeSupabaseRequest($endpoint_reservasi, 'GET');
    if (isset($response['error'])) {
        throw new Exception("Error API Reservasi: " . $response['error']);
    }
    $reservasi_data = $response['data'];


    if (empty($reservasi_data)) {
        throw new Exception("Data reservasi dengan ID {$id_reservasi} tidak ditemukan.");
    }
    
    $data_utama = $reservasi_data[0]; 
    $detail['reservasi'] = $data_utama;
    $detail['profiles'] = $data_utama['profiles']; 
    unset($detail['reservasi']['profiles']); 
    $select_rombongan = 'id_pendaki,nama_lengkap,nik,nomor_telepon,alamat,kontak_darurat,url_surat_sehat';
    $endpoint_rombongan = 'pendaki_rombongan?id_reservasi=eq.' . urlencode($id_reservasi) . '&select=' . urlencode($select_rombongan) . '&limit=100';
    $response_rombongan = makeSupabaseRequest($endpoint_rombongan, 'GET');
    $rombongan_data = isset($response_rombongan['error']) ? [] : $response_rombongan['data'];
    $detail['pendaki_rombongan'] = $rombongan_data;
    $select_barang = 'id_barang,nama_barang,jenis_sampah,jumlah';
    $endpoint_barang = 'barang_bawaan_sampah?id_reservasi=eq.' . urlencode($id_reservasi) . '&select=' . urlencode($select_barang) . '&limit=100';
    $response_barang = makeSupabaseRequest($endpoint_barang, 'GET');
    $barang_data = isset($response_barang['error']) ? [] : $response_barang['data'];
    $detail['barang_sampah_bawaan'] = $barang_data;

} catch (Exception $e) {
    $success = false;
    $message = $e->getMessage();
    $detail = null; 
}

echo json_encode(['success' => $success, 'message' => $message, 'detail' => $detail]);
?>