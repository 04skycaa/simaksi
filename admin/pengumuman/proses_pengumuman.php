<?php
header('Content-Type: application/json');
include __DIR__ . '/../../../config/supabase.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    exit;
}

$action = $input['action'];
$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];
$id_admin_contoh = '2b367615-30d4-4ce0-b1d0-16d585e5055b'; 

switch ($action) {
    case 'create':
        $data = [
            'id_admin' => $id_admin_contoh,
            'judul' => $input['judul'],
            'konten' => $input['konten'],
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'telah_terbit' => $input['telah_terbit']
        ];
        $result = supabase_request('POST', 'pengumuman', json_encode($data));
        if (is_array($result) && isset($result[0]['id_pengumuman'])) {
            $response = ['success' => true, 'message' => 'Pengumuman berhasil dibuat.'];
        } else {
            $response['message'] = 'Gagal membuat pengumuman: ' . ($result['message'] ?? 'Error');
        }
        break;

    case 'update':
        $id = $input['id_pengumuman'];
        $data = [
            'judul' => $input['judul'],
            'konten' => $input['konten'],
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'telah_terbit' => $input['telah_terbit'],
            'diperbarui_pada' => date('c')
        ];
        $endpoint = 'pengumuman?id_pengumuman=eq.' . $id;
        $result = supabase_request('PATCH', $endpoint, json_encode($data));
        if (is_array($result) && empty($result)) {
            $response = ['success' => true, 'message' => 'Pengumuman berhasil diperbarui.'];
        } else {
            $response['message'] = 'Gagal memperbarui pengumuman: ' . ($result['message'] ?? 'Error');
        }
        break;

    case 'delete':
        $id = $input['id'];
        $endpoint = 'pengumuman?id_pengumuman=eq.' . $id;
        $result = supabase_request('DELETE', $endpoint);
        if (is_array($result) && empty($result)) {
            $response = ['success' => true, 'message' => 'Pengumuman berhasil dihapus.'];
        } else {
            $response['message'] = 'Gagal menghapus pengumuman: ' . ($result['message'] ?? 'Error');
        }
        break;
}

echo json_encode($response);
?>