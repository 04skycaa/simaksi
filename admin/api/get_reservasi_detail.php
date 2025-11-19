<?php

header('Content-Type: application/json');
$SUPABASE_URL = 'https://kitxtcpfnccblznbagzx.supabase.co'; 
$SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTk1ODIxMzEsImV4cCI6MjA3NTE1ODEzMX0.OySigpw4AWI3G7JW_8r8yXu7re0Mr9CYv8u3d9Fr548'; 

// untuk mengambil data dari Supabase
function fetchSupabaseData($tableName, $select, $filterColumn, $filterValue, $limit = 1) {
    global $SUPABASE_URL, $SUPABASE_ANON_KEY;
    
    $url = $SUPABASE_URL . "/rest/v1/{$tableName}?";
    $url .= "select={$select}&{$filterColumn}=eq.{$filterValue}";
    $url .= "&limit={$limit}"; 

    $headers = [
        'Content-Type: application/json',
        "apikey: {$SUPABASE_ANON_KEY}", 
        "Authorization: Bearer {$SUPABASE_ANON_KEY}" 
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        return ['error' => true, 'http_code' => $http_code, 'response' => $response];
    }
    
    return json_decode($response, true);
}

// --- LOGIKA UTAMA ---
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
    $reservasi_data = fetchSupabaseData('reservasi', '*,profiles(nama_lengkap)', 'id_reservasi', $id_reservasi);

    if (isset($reservasi_data['error']) || empty($reservasi_data)) {
        throw new Exception("Data reservasi dengan ID {$id_reservasi} tidak ditemukan atau error API.");
    }
    
    $data_utama = $reservasi_data[0]; 
    $detail['reservasi'] = $data_utama;
    $detail['profiles'] = $data_utama['profiles']; 
    unset($detail['reservasi']['profiles']); 

    $rombongan_data = fetchSupabaseData('pendaki_rombongan', 'id_pendaki,nama_lengkap,nik,alamat,kontak_darurat', 'id_reservasi', $id_reservasi, 100);
    $detail['pendaki_rombongan'] = isset($rombongan_data['error']) ? [] : $rombongan_data;

    $barang_data = fetchSupabaseData('barang_sampah_bawaan', 'id,nama_barang,jenis_sampah,jumlah', 'id_reservasi', $id_reservasi, 100);
    $detail['barang_sampah_bawaan'] = isset($barang_data['error']) ? [] : $barang_data;

} catch (Exception $e) {
    $success = false;
    $message = $e->getMessage();
    $detail = null; 
}

echo json_encode(['success' => $success, 'message' => $message, 'detail' => $detail]);
?>