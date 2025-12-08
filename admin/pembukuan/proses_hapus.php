<?php
session_start();

include __DIR__ . '/../../config/supabase.php'; 
 
$redirect_url = '../index.php?page=pembukuan';
 
function redirectWithStatus($status, $message, $url) { 
    $final_url = $url . '&status=' . urlencode($status) . '&message=' . urlencode($message);
    header("Location: " . $final_url);
    exit; 
}

if (!isset($_SESSION['user_id'])) {
    redirectWithStatus('error', 'Sesi Anda tidak valid. Silakan login kembali.', $redirect_url);
}

$id_to_delete = $_GET['id'] ?? null;
$table_name = $_GET['table'] ?? null;

if (empty($id_to_delete) || empty($table_name)) {
    redirectWithStatus('error', 'ID atau nama tabel tidak ditemukan.', $redirect_url);
}

$id_column = '';
if ($table_name === 'pengeluaran') {
    $id_column = 'id_pengeluaran';
} elseif ($table_name === 'pemasukan') {
    $id_column = 'id_pemasukan';
} else {
    redirectWithStatus('error', 'Tabel yang diminta tidak valid.', $redirect_url);
}

$soft_delete_data = [
    'deleted_at' => date('Y-m-d H:i:sP') 
];

$endpoint = "{$table_name}?{$id_column}=eq." . (int)$id_to_delete; 
$result = supabase_request('PATCH', $endpoint, $soft_delete_data); 
if ($result !== false && !isset($result['error'])) {
    $message = "Data " . ucfirst($table_name) . " dengan ID " . (int)$id_to_delete . " berhasil dihapus!";
    redirectWithStatus('success', $message, $redirect_url);
} else {
    $api_error = $result['error']['message'] ?? 'Gagal melakukan soft delete data. Cek logs Supabase.';
    error_log("Supabase Error: " . print_r($result, true));
    redirectWithStatus('error', $api_error, $redirect_url);
}
?>