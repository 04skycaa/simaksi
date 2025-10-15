<?php
header('Content-Type: application/json');
include __DIR__ . '/../../../config/supabase.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan.']);
    exit;
}

$id = $_GET['id'];
$endpoint = 'pengumuman?id_pengumuman=eq.' . $id . '&limit=1';
$data = supabase_request('GET', $endpoint);

if ($data && !empty($data)) {
    echo json_encode(['success' => true, 'data' => $data[0]]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data pengumuman tidak ditemukan.']);
}
?>