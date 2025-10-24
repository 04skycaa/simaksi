<?php
$supabaseUrl = 'https://kitxtcpfnccblznbagzx.supabase.co'; 
$supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTk1ODIxMzEsImV4cCI6MjA3NTE1ODEzMX0.OySigpw4AWI3G7JW_8r8yXu7re0Mr9CYv8u3d9Fr548'; // anon key
$serviceRoleKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE'; // service role key

$headers = [
    'Content-Type: application/json',
    'apikey: ' . $supabaseKey,
    'Authorization: Bearer ' . $serviceRoleKey, 
    'Prefer: return=representation'
];

function makeSupabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $headers;
    
    $baseUrl = rtrim($supabaseUrl, '/') . '/rest/v1'; 
    
    $url_parts = explode('?', $endpoint, 2);
    $path = $url_parts[0];
    $query = isset($url_parts[1]) ? '?' . $url_parts[1] : '';
    
    $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/') . $query;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    if ($curlError) {
        error_log("CURL Error: " . $curlError);
        return ['error' => 'Curl error: ' . $curlError];
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode >= 400 || json_last_error() !== JSON_ERROR_NONE) {
        $errorMessage = $result['message'] ?? $result['msg'] ?? 'Kesalahan API Supabase: ' . $response;
        error_log("HTTP error: " . $httpCode . " - " . $errorMessage);
        return ['error' => $errorMessage, 'http_code' => $httpCode];
    }
    
    return [
        'status_code' => $httpCode,
        'data' => $result
    ];
}
?>