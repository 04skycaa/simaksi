<?php
require __DIR__ . '/../../config/supabase.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan.']);
    exit;
}

$endpoint = 'kuota_harian?id_kuota=eq.' . $id . '&select=*&limit=1';
$data = supabase_request('GET', $endpoint);

if ($data && !isset($data['error']) && count($data) > 0) {
    echo json_encode(['success' => true, 'data' => $data[0]]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan.']);
}