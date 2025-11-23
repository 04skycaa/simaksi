<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!function_exists('upload_base64_to_supabase_storage')) {
    function upload_base64_to_supabase_storage($base64_data, $mime_type, $file_name_prefix, $bucket_name = 'surat-sehat') {
        if (!function_exists('uploadToSupabaseStorage') || !function_exists('getSupabaseStoragePublicUrl')) {
            return ['error' => 'Fungsi inti uploadToSupabaseStorage() atau getSupabaseStoragePublicUrl() tidak ditemukan di config.php.'];
        }

        $extension_map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
        ];
        
        $extension = $extension_map[$mime_type] ?? explode('/', $mime_type)[1] ?? 'dat'; 
        $timestamp = time();
        $unique_name = $file_name_prefix . '_' . $timestamp . '_' . uniqid() . '.' . $extension;
        $file_path_in_bucket = $unique_name; 
        $file_content = base64_decode($base64_data);
        if ($file_content === false) {
            return ['error' => 'Gagal mendekode data Base64.'];
        }

        $upload_result = uploadToSupabaseStorage($file_path_in_bucket, $file_content, $bucket_name);

        if (!$upload_result['success']) {
            error_log("Gagal upload Supabase Storage: " . $upload_result['error']);
            return ['error' => 'Gagal mengunggah file ke storage. ' . $upload_result['error']];
        }

        $public_url = getSupabaseStoragePublicUrl($file_path_in_bucket, $bucket_name);

        return ['url' => $public_url];
    }
}


try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response = makeSupabaseRequest('pengaturan_biaya?select=nama_item,harga', 'GET');
        
        if (isset($response['error'])) {
            http_response_code(500);
            echo json_encode(['error' => $response['error']]);
            exit;
        }
        
        $pricing_map = [];
        foreach ($response['data'] as $item) {
            $pricing_map[$item['nama_item']] = (int)$item['harga'];
        }
        
        echo json_encode([
            'status' => 'success', 
            'data' => $pricing_map 
        ]);

    } 
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['tanggal_pendakian']) || !isset($data['jumlah_pendaki']) || 
            !isset($data['jumlah_tiket_parkir']) || !isset($data['total_harga']) || 
            !isset($data['id_pengguna']) || !isset($data['anggota_rombongan'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Data tidak lengkap.']);
            exit;
        }

        $tanggal_pendakian = $data['tanggal_pendakian'];
        $jumlah_pendaki = $data['jumlah_pendaki'];
        $jumlah_tiket_parkir = $data['jumlah_tiket_parkir'];
        $total_harga = $data['total_harga'];
        $jumlah_potensi_sampah = $data['jumlah_potensi_sampah'] ?? 0;
        $id_pengguna = $data['id_pengguna']; 
        $anggota_rombongan = $data['anggota_rombongan'];
        $barang_bawaan = $data['barang_bawaan'] ?? [];
        $kuota_response = makeSupabaseRequest(
            'kuota_harian?select=kuota_maksimal,kuota_terpesan&tanggal_kuota=eq.' . urlencode($tanggal_pendakian), 
            'GET'
        );
        
        $kuota_data = $kuota_response['data'] ?? [];
        $kuotaTerpesan = $kuota_data[0]['kuota_terpesan'] ?? 0;
        $kuotaMaksimal = $kuota_data[0]['kuota_maksimal'] ?? 50; 
        $available_quota = $kuotaMaksimal - $kuotaTerpesan;
        
        if ($available_quota < $jumlah_pendaki) {
            http_response_code(400);
            echo json_encode(['error' => 'Kuota tidak mencukupi untuk tanggal tersebut. Tersedia: ' . $available_quota . ', Dibutuhkan: ' . $jumlah_pendaki]);
            exit;
        }

        $user_response = makeSupabaseRequest('profiles?select=id,nama_lengkap&' . 'id=eq.' . urlencode($id_pengguna), 'GET');
        if (empty($user_response['data'])) {
             http_response_code(400);
             echo json_encode(['error' => 'User (Ketua Rombongan) tidak ditemukan.']);
             exit;
        }
        $user_id = $user_response['data'][0]['id'];
        $kode_reservasi = 'R' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $reservation_data = [
            'id_pengguna' => $user_id,
            'kode_reservasi' => $kode_reservasi,
            'tanggal_pendakian' => $tanggal_pendakian,
            'jumlah_pendaki' => $jumlah_pendaki,
            'jumlah_tiket_parkir' => $jumlah_tiket_parkir,
            'total_harga' => $total_harga,
            'jumlah_potensi_sampah' => $jumlah_potensi_sampah,
            'status' => 'menunggu_pembayaran', 
        ];
        
        $reservation_response = makeSupabaseRequest('reservasi', 'POST', $reservation_data);

        if (isset($reservation_response['error'])) {
            http_response_code(400); 
            echo json_encode(['error' => 'Gagal membuat reservasi: ' . $reservation_response['error']]);
            exit;
        }
        $id_reservasi = $reservation_response['data'][0]['id_reservasi'];

        foreach ($anggota_rombongan as $pendaki) {
            $url_surat_sehat = $pendaki['url_surat_sehat'] ?? null;
            
            if (strpos($url_surat_sehat, 'data:') === 0) {
                list($meta, $base64_string) = explode(';', $url_surat_sehat);
                list($base64_indicator, $base64_data) = explode(',', $base64_string);
                $mime_type = str_replace('data:', '', $meta);
                $file_name_for_storage = $pendaki['nama_lengkap'] . '_' . $id_reservasi . '_' . time();
                $upload_result = upload_base64_to_supabase_storage($base64_data, $mime_type, $file_name_for_storage);
                
                if (isset($upload_result['error'])) {
                     http_response_code(500);
                     echo json_encode(['error' => 'Gagal upload surat sehat untuk ' . $pendaki['nama_lengkap'] . ': ' . $upload_result['error']]);
                     exit;
                }
                
                $pendaki['url_surat_sehat'] = $upload_result['url']; 

            } elseif ($url_surat_sehat !== null && $url_surat_sehat !== "") {
                $pendaki['url_surat_sehat'] = $url_surat_sehat;
            } else {
                $pendaki['url_surat_sehat'] = null;
            }

            $pendaki_data = [
                'id_reservasi' => $id_reservasi,
                'nama_lengkap' => $pendaki['nama_lengkap'],
                'nik' => $pendaki['nik'],
                'alamat' => $pendaki['alamat'],
                'nomor_telepon' => $pendaki['nomor_telepon'],
                'kontak_darurat' => $pendaki['kontak_darurat'],
                'url_surat_sehat' => $pendaki['url_surat_sehat']
            ];
            
            $pendaki_response = makeSupabaseRequest('pendaki_rombongan', 'POST', $pendaki_data);

            if (isset($pendaki_response['error'])) {
                http_response_code(500);
                echo json_encode(['error' => 'Gagal menambahkan anggota rombongan: ' . $pendaki_response['error']]);
                exit;
            }
        }

        foreach ($barang_bawaan as $barang) {
            $jenis_sampah_db = $barang['jenis_sampah'];
            if ($jenis_sampah_db === 'anorganik') {
                $jenis_sampah_db = 'non-organik';
            }

            $barang_data = [
                'id_reservasi' => $id_reservasi,
                'nama_barang' => $barang['nama_barang'],
                'jenis_sampah' => $jenis_sampah_db, 
                'jumlah' => $barang['jumlah'] ?? 1 
            ];
            
            $barang_response = makeSupabaseRequest('barang_bawaan_sampah', 'POST', $barang_data);

            if (isset($barang_response['error'])) {
                http_response_code(500);
                echo json_encode(['error' => 'Gagal menambahkan barang bawaan: ' . $barang_response['error']]);
                exit;
            }
        }

        $update_kuota_endpoint = 'kuota_harian?tanggal_kuota=eq.' . urlencode($tanggal_pendakian);
        $kuota_update_data = ['kuota_terpesan' => (int)$kuotaTerpesan + (int)$jumlah_pendaki];
        makeSupabaseRequest($update_kuota_endpoint, 'PATCH', $kuota_update_data);
        
        echo json_encode([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat',
            'kode_reservasi' => $kode_reservasi
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>