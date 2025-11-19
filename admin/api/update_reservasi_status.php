<?php

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'PATCH' && $method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$SUPABASE_URL = 'https://kitxtcpfnccblznbagzx.supabase.co'; 
$SUPABASE_SERVICE_KEY = 'GANTI_DENGAN_SERVICE_ROLE_KEY_ANDA';
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['id_reservasi'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID Reservasi tidak ditemukan.']);
    exit;
}

$id_reservasi = $data['id_reservasi'];

// untuk memanggil Supabase API
function callSupabaseAPI($url, $method, $payload, $serviceKey) {
    $headers = [
        'Content-Type: application/json',
        "apikey: {$serviceKey}", 
        "Authorization: Bearer {$serviceKey}",
        'Prefer: return=minimal'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($payload) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['http_code' => $http_code, 'response' => $response];
}

$success = true;
$message = 'Data berhasil diperbarui.';

// untuk memproses update data reservasi utama
try {
    $reservasi_update_data = [
        'tanggal_pendakian' => $data['tanggal_pendakian'] ?? null,
        'jumlah_pendaki' => (int)($data['jumlah_pendaki'] ?? 0),
        'jumlah_tiket_parkir' => (int)($data['jumlah_tiket_parkir'] ?? 0),
        'status' => $data['status'] ?? null,
        'status_sampah' => $data['status_sampah'] ?? null,
    ];
    $reservasi_update_data = array_filter($reservasi_update_data, fn($value) => !is_null($value));

    $url = $SUPABASE_URL . "/rest/v1/reservasi?id_reservasi=eq.{$id_reservasi}";
    $result = callSupabaseAPI($url, 'PATCH', $reservasi_update_data, $SUPABASE_SERVICE_KEY);
    
    if ($result['http_code'] !== 204) {
        $success = false;
        $message .= " Gagal update reservasi utama (Code: {$result['http_code']}).";
    }

} catch (Exception $e) {
    $success = false;
    $message .= " Error update reservasi utama: " . $e->getMessage();
}


// untuk memproses data rombongan dan barang bawaan
if (isset($data['rombongan']) && $success) {
    
    foreach ($data['rombongan'] as $item) {
        $pendaki_id = $item['id'];
        
        if (empty($item['nama_lengkap']) || empty($item['nik'])) continue; 
        
        $pendaki_payload = [
            'nama_lengkap' => $item['nama_lengkap'],
            'nik' => $item['nik'],
            'alamat' => $item['alamat'],
            'kontak_darurat' => $item['kontak_darurat'],
        ];

        if (str_starts_with($pendaki_id, 'new_')) {
            $pendaki_payload['id_reservasi'] = $id_reservasi; 
            $url = $SUPABASE_URL . "/rest/v1/pendaki_rombongan";
            $result = callSupabaseAPI($url, 'POST', $pendaki_payload, $SUPABASE_SERVICE_KEY);
            if ($result['http_code'] !== 201) { $success = false; $message .= " Gagal tambah pendaki baru (Code: {$result['http_code']})."; }
            
        } else {
            $url = $SUPABASE_URL . "/rest/v1/pendaki_rombongan?id_pendaki=eq.{$pendaki_id}";
            $result = callSupabaseAPI($url, 'PATCH', $pendaki_payload, $SUPABASE_SERVICE_KEY);
            if ($result['http_code'] !== 204) { $success = false; $message .= " Gagal update pendaki ID {$pendaki_id} (Code: {$result['http_code']})."; }
        }
    }
}


// untuk memproses data barang bawaan
if (isset($data['barang']) && $success) {
    
    foreach ($data['barang'] as $item) {
        $barang_id = $item['id']; 
        
        if (empty($item['nama_barang']) || empty($item['jumlah']) || (int)$item['jumlah'] < 0) continue; 
        
        $barang_payload = [
            'nama_barang' => $item['nama_barang'],
            'jenis_sampah' => $item['jenis_sampah'],
            'jumlah' => (int)$item['jumlah'],
        ];

        if (str_starts_with($barang_id, 'new_barang_')) {
            $barang_payload['id_reservasi'] = $id_reservasi; 
            $url = $SUPABASE_URL . "/rest/v1/barang_sampah_bawaan";
            $result = callSupabaseAPI($url, 'POST', $barang_payload, $SUPABASE_SERVICE_KEY);
            if ($result['http_code'] !== 201) { $success = false; $message .= " Gagal tambah barang baru (Code: {$result['http_code']})."; }
            
        } else {
            $url = $SUPABASE_URL . "/rest/v1/barang_sampah_bawaan?id=eq.{$barang_id}";
            $result = callSupabaseAPI($url, 'PATCH', $barang_payload, $SUPABASE_SERVICE_KEY);
            if ($result['http_code'] !== 204) { $success = false; $message .= " Gagal update barang ID {$barang_id} (Code: {$result['http_code']})."; }
        }
    }
}

// untuk response akhir
if ($success) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $message]);
}
?>