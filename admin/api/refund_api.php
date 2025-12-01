<?php
// Hanya untuk menangani permintaan AJAX refund
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

include __DIR__ . '/config.php';

if (!function_exists('makeSupabaseRequest')) {
    echo json_encode(['success' => false, 'message' => "Error: Fungsi makeSupabaseRequest tidak ditemukan."]);
    exit;
}

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Aksi tidak valid.'];

if ($action === 'get_refund_detail' && isset($_GET['id_reservasi'])) {
    $id_reservasi = $_GET['id_reservasi'];

    // Include the necessary joins to get user data and refund details - using correct column names
    $profile_join_hint = 'id_pengguna:profiles(nama_lengkap,nomor_telepon)';
    $select_detail = 'id_reservasi,kode_reservasi,nominal_refund,bank_refund,no_rek_refund,atas_nama_refund,status,' .
                     'tanggal_pendakian,jumlah_pendaki,total_harga,' .
                     $profile_join_hint;

    $detail_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi) . '&select=' . urlencode($select_detail);

    try {
        $api_response = makeSupabaseRequest($detail_endpoint, 'GET');
    } catch (Exception $e) {
        error_log("Error making Supabase request: " . $e->getMessage());
        $api_response = ['error' => 'Error making request: ' . $e->getMessage()];
    }

    $detailRefund = $api_response['data'] ?? null;

    if (!empty($detailRefund) && !isset($api_response['error'])) {
        if (isset($detailRefund[0])) {
            // Ensure profile data is properly structured for frontend
            $refundData = $detailRefund[0];

            // The profile data is in id_pengguna as confirmed by our debug,
            // but we'll make sure it's accessible in both formats for compatibility
            if (isset($refundData['id_pengguna'])) {
                $refundData['profiles'] = $refundData['id_pengguna'];
            }

            $response = ['success' => true, 'data' => $refundData];
        } else {
            $response = ['success' => false, 'message' => 'Data refund dengan ID tersebut tidak ditemukan.'];
        }
    } else {
        $errorMessage = 'Gagal mengambil detail refund.';
        if (isset($api_response['error'])) {
            $errorMessage = "Supabase Error: " . (is_array($api_response['error']) ? json_encode($api_response['error']) : $api_response['error']);
        } elseif (empty($detailRefund)) {
            $errorMessage = "Supabase mengembalikan data kosong. Cek id_reservasi atau RLS.";
        }
        $response = ['success' => false, 'message' => $errorMessage];
    }
} elseif ($action === 'process_refund' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_reservasi = $_POST['id_reservasi'] ?? null;

    // Check if a file was uploaded
    if (!isset($_FILES['bukti_refund']) || $_FILES['bukti_refund']['error'] !== UPLOAD_ERR_OK) {
        $response = ['success' => false, 'message' => 'File bukti refund tidak ditemukan atau terjadi kesalahan upload.'];
    } else {
        $file = $_FILES['bukti_refund'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $response = ['success' => false, 'message' => 'Format file tidak didukung. Hanya JPG, JPEG, PNG, GIF, dan PDF yang diperbolehkan.'];
        } else {
            // Upload file to Supabase Storage
            $file_content = file_get_contents($file['tmp_name']);
            $file_path = 'bukti_refund_' . $id_reservasi . '_' . time() . '.' . $file_extension;

            $upload_result = uploadToSupabaseStorage($file_path, $file_content, 'bukti-refund');

            if (!$upload_result['success']) {
                $response = ['success' => false, 'message' => 'Gagal mengupload bukti refund: ' . $upload_result['error']];
            } else {
                // Get public URL
                $public_url = getSupabaseStoragePublicUrl($file_path, 'bukti-refund');

                // Update reservation status and bukti_refund
                $update_data = [
                    'status' => 'refund_selesai',
                    'bukti_refund' => $public_url
                ];

                $update_endpoint = 'reservasi?id_reservasi=eq.' . urlencode($id_reservasi);
                $result = makeSupabaseRequest($update_endpoint, 'PATCH', $update_data);

                if (empty($result) || !isset($result['error'])) {
                    $response = ['success' => true, 'message' => 'Refund berhasil diproses!'];
                } else {
                    $response = ['success' => false, 'message' => 'Gagal update status refund di Supabase. ' . ($result['error']['message'] ?? 'Error tidak diketahui.')];
                }
            }
        }
    }
}

// Ensure no extra output before JSON
if (ob_get_level()) {
    ob_clean();
}
echo json_encode($response);
exit;
?>