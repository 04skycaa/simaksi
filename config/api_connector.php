<?php
// simaksi/config/api_connector.php
// Konfigurasi koneksi dan fungsi untuk berinteraksi dengan API Supabase.

// GANTI INI: Ambil dari Settings -> API -> Project API Keys -> URL
define('SUPABASE_URL', 'https://kitxtcpfnccblznbagzx.supabase.co');

// GANTI INI: Ambil dari Settings -> API -> Project API Keys -> anon public
// Ini digunakan untuk Auth API Supabase.
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTk1ODIxMzEsImV4cCI6MjA3NTE1ODEzMX0.OySigpw4AWI3G7JW_8r8yXu7re0Mr9CYv8u3d9Fr548');


/**
 * Fungsi untuk melakukan permintaan ke Supabase Auth API
 * @param string $endpoint Contoh: 'signup', 'verify' (untuk OTP)
 * @param array $payload Data yang dikirimkan ke API
 * @return array Hasil dari API atau pesan error
 */
function makeSupabaseAuthRequest(string $endpoint, array $payload): array {
    $url = SUPABASE_URL . '/auth/v1/' . $endpoint;

    $headers = [
        'Content-Type: application/json',
        // Menggunakan Anon Key untuk otentikasi
        'apikey: ' . SUPABASE_ANON_KEY,
    ];

    // Inisialisasi cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    // Konfigurasi SSL untuk koneksi lebih stabil
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Nonaktifkan verifikasi SSL sementara
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Konfigurasi timeout untuk menghindari koneksi terputus
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x46) AppleWebKit/537.36'); // Set a user agent
    curl_setopt($ch, CURLOPT_ENCODING, '');

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    // Baris curl_close($ch); telah dihapus karena dianggap deprecated

    if ($error) {
        error_log("CURL Error in Supabase Auth Request: " . $error . " (Total time: {$totalTime}s)");
        return ['error' => true, 'detailed_error' => 'cURL Error: ' . $error, 'http_status' => 0];
    }

    $data = json_decode($response, true);

    if ($http_status >= 400) {
        // Menangkap deskripsi error dari Supabase
        $error_detail = $data['error_description'] ?? $data['msg'] ?? 'Kesalahan tidak diketahui dari Supabase.';
        return ['error' => true, 'detailed_error' => $error_detail, 'http_status' => $http_status];
    }

    return $data;
}