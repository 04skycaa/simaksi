<?php
session_start(); // WAJIB di baris paling atas
header('Content-Type: application/json');

// --- TAMBAHAN KEAMANAN ---
// Cek apakah ada session admin yang aktif.
if (!isset($_SESSION['user_id'])) {
    // Jika tidak ada, kirim error dan hentikan skrip
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Sesi Anda mungkin telah berakhir. Silakan login kembali.']);
    exit;
}
// --- SELESAI KEAMANAN ---


include __DIR__ . '/../../config/supabase.php';

// Ambil data dari request body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    exit;
}

$action = $input['action'];
$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

// === INI DIA PERUBAHANNYA ===
// Ambil ID admin dari session yang sudah login, BUKAN hardcode
$id_admin_dari_session = $_SESSION['user_id']; 
// === SELESAI PERUBAHAN ===


// Cek boolean 'telah_terbit'
if (isset($input['telah_terbit']) && $input['telah_terbit']) {
    $telah_terbit_value = "true";
} else {
    $telah_terbit_value = "false";
}


switch ($action) {
    case 'create':
        $data = [
            // Gunakan variabel dari session
            'id_admin' => $id_admin_dari_session, 
            'judul' => $input['judul'],
            'konten' => $input['konten'],
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'telah_terbit' => $telah_terbit_value 
        ];

        $result = supabase_request('POST', 'pengumuman', $data); 
        
        if (is_array($result) && isset($result[0]['id_pengumuman'])) {
            $response = ['success' => true, 'message' => 'Pengumuman berhasil dibuat.'];
        } else {
            // Debugging response
            $raw_response = json_encode($result);
            $response['message'] = 'Gagal membuat pengumuman. Raw response: ' . $raw_response;
        }
        break;

    case 'update':
        // ini untuk memperbarui pengumuman
        $id = $input['id_pengumuman'];
        $data = [
            'judul' => $input['judul'],
            'konten' => $input['konten'],
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'diperbarui_pada' => date('c'),
            'telah_terbit' => $telah_terbit_value // Gunakan logika baru yang robust
        ];
        $endpoint = 'pengumuman?id_pengumuman=eq.' . $id;
        // ini untuk request patch
        $result = supabase_request('PATCH', $endpoint, $data);
        
        if (is_array($result) && isset($result[0]['id_pengumuman'])) {
            $response = ['success' => true, 'message' => 'Pengumuman berhasil diperbarui.'];
        } else {
            $error_msg = 'Gagal memperbarui pengumuman.';
            if(isset($result['error']['message'])) {
                $error_msg .= ' Pesan: ' . $result['error']['message'];
            } elseif(isset($result['message'])) {
                $error_msg .= ' Pesan: ' . $result['message'];
            }
            $response['message'] = $error_msg;
        }
        break;

    case 'delete':
        $id = $input['id'];
        $endpoint = 'pengumuman?id_pengumuman=eq.' . $id;
        
        $result = supabase_request('DELETE', $endpoint); 
        
        // Cek hasil delete (biasanya $result kosong saat sukses)
        if (is_array($result) && empty($result)) {
            $response = ['success' => true, 'message' => 'Pengumuman berhasil dihapus.'];
        } else {
            $response['message'] = 'Gagal menghapus pengumuman: ' . ($result['message'] ?? 'Error');
        }
        break;
}

echo json_encode($response);
?>