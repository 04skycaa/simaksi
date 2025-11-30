<?php
// FILE: simaksi/admin/api/fetch_stats.php

// Izinkan akses dari domain manapun (CORS)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// --- PENGATURAN KONEKSI SUPABASE ---
$SUPABASE_URL = "https://kitxtcpfnccblznbagzx.supabase.co"; 
// GANTI DENGAN SERVICE ROLE KEY ANDA (Ini melewati RLS dan BERBAHAYA untuk publik!)
$SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE"; 

// Objek untuk menyimpan hasil akhir
$response = [
    'total_pendaki' => 0, 
    'average_rating' => 0.0,
    'rating_display' => 'N/A',
    'status' => 'success',
    'message' => 'Data berhasil diambil.'
];

/**
 * Fungsi untuk melakukan panggilan ke Supabase melalui cURL
 */
function fetch_supabase_data($url, $key) {
    // Pengecekan cURL
    if (!function_exists('curl_init')) {
        error_log("PHP cURL extension is not installed atau enabled.");
        return ['error' => true, 'http_status' => 500, 'response' => 'PHP cURL tidak aktif.'];
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        // Supabase REST API menerima Service Role Key di header ini.
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key,
        'Accept: application/json'
    ));
    $result = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    if ($curl_error) {
        error_log("cURL Error: " . $curl_error);
        return ['error' => true, 'http_status' => 503, 'response' => 'cURL connection error: ' . $curl_error];
    }
    
    if ($http_status !== 200) {
        // Log status HTTP dan respons dari Supabase
        error_log("Gagal mengambil data dari Supabase. Status HTTP: " . $http_status . " Response: " . $result);
        return ['error' => true, 'http_status' => $http_status, 'response' => $result];
    }
    
    // Jika sukses, kembalikan data
    return json_decode($result, true);
}

// =========================================================================================
// 1. MENGAMBIL RATA-RATA RATING (Tabel: komentar, Kolom: rating)
// =========================================================================================

$rating_endpoint = $SUPABASE_URL . "/rest/v1/komentar?select=avg(rating):avg_rating_all_time,count(id_komentar)";
$rating_data = fetch_supabase_data($rating_endpoint, $SUPABASE_KEY);


if (isset($rating_data['error'])) {
    $response['status'] = 'error_rating_fetch';
    $response['message'] = 'Gagal rating. Status Supabase: ' . $rating_data['http_status'] . '. Respon: ' . substr($rating_data['response'], 0, 100) . '...';
    echo json_encode($response);
    exit;
} elseif ($rating_data && is_array($rating_data) && !empty($rating_data)) {
    $ratings = $rating_data[0];
    $avg_rating_raw = $ratings['avg_rating_all_time'] ?? 0;
    $total_ratings = (int)($ratings['count'] ?? 0);
    
    $avg_rating = round((float)$avg_rating_raw, 1);
    
    $response['average_rating'] = $avg_rating;
    
    if ($total_ratings > 0) {
        $response['rating_display'] = $avg_rating . '/5'; 
    } else {
        $response['rating_display'] = 'Belum Ada Rating';
    }
} else {
    $response['status'] = 'error_rating_process';
    $response['message'] = 'Respon rating kosong atau tidak valid dari Supabase.';
    error_log("Gagal memproses data rating Supabase.");
}

// =========================================================================================
// 2. MENGAMBIL TOTAL ROMBONGAN TAHUN INI (Tabel: reservasi, Kolom: id_reservasi)
// =========================================================================================

$current_year_start = date("Y") . "-01-01"; 
$next_year_start = (date("Y") + 1) . "-01-01"; 

$rombongan_endpoint = $SUPABASE_URL . "/rest/v1/reservasi?select=count(id_reservasi):total_rombongan_count&status_reservasi=neq.dibatalkan&tanggal_reservasi=gte." . $current_year_start . "&tanggal_reservasi=lt." . $next_year_start;

$rombongan_data = fetch_supabase_data($rombongan_endpoint, $SUPABASE_KEY);

if (isset($rombongan_data['error'])) {
    $response['status'] = 'error_rombongan_fetch';
    $response['message'] = 'Gagal rombongan. Status Supabase: ' . $rombongan_data['http_status'] . '. Respon: ' . substr($rombongan_data['response'], 0, 100) . '...';
    echo json_encode($response);
    exit;
} elseif ($rombongan_data && is_array($rombongan_data) && !empty($rombongan_data)) {
    $total_rombongan = (int)($rombongan_data[0]['total_rombongan_count'] ?? 0);
    
    // Mapping ke kunci lama agar JS tetap berfungsi
    $response['total_pendaki'] = $total_rombongan; 
    
} else {
    $response['status'] = 'error_rombongan_process';
    $response['message'] = 'Respon rombongan kosong atau tidak valid dari Supabase.';
    error_log("Gagal memproses data rombongan Supabase.");
}

// Keluarkan data dalam format JSON
echo json_encode($response);
?>