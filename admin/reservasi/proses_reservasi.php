<?php
header('Content-Type: application/json');
include __DIR__ . '/../../config/supabase.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    exit;
}

$action = $input['action'];
$id_reservasi = $input['id'] ?? null;

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

switch ($action) {
    case 'update_status':
        if ($id_reservasi && isset($input['status'])) {
            $new_status = $input['status'];
            $endpoint = 'reservasi?id_reservasi=eq.' . $id_reservasi;
            $data = ['status' => $new_status];
            
            $result = supabase_request('PATCH', $endpoint, json_encode($data));
            
            if (is_array($result) && empty($result)) {
                $response = ['success' => true, 'message' => 'Status reservasi berhasil diperbarui.'];
            } else {
                $response['message'] = 'Gagal memperbarui status: ' . ($result['message'] ?? 'Error tidak diketahui');
            }
        } else {
            $response['message'] = 'ID atau status tidak lengkap.';
        }
        break;

    case 'delete':
        if ($id_reservasi) {
            $endpoint_barang = 'barang_bawaan_sampah?id_reservasi=eq.' . $id_reservasi;
            supabase_request('DELETE', $endpoint_barang);

            $endpoint_reservasi = 'reservasi?id_reservasi=eq.' . $id_reservasi;
            $result = supabase_request('DELETE', $endpoint_reservasi);

            if (is_array($result) && empty($result)) {
                $response = ['success' => true, 'message' => 'Reservasi berhasil dihapus.'];
            } else {
                $response['message'] = 'Gagal menghapus reservasi: ' . ($result['message'] ?? 'Error tidak diketahui');
            }
        } else {
            $response['message'] = 'ID reservasi tidak ditemukan.';
        }
        break;

    default:
        $response['message'] = 'Aksi tidak dikenali.';
        break;
}

echo json_encode($response);
?>