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

/**
 * Fungsi untuk mengupload file Base64 ke Supabase Storage.
 * Anda HARUS mengimplementasikan fungsi ini menggunakan Service Key Supabase Anda
 * dan library HTTP (seperti cURL) untuk berkomunikasi dengan Supabase Storage API.
 * * @param string $base64_string Data file dalam format Base64 mentah.
 * @param string $mime_type MIME type dari file (misalnya image/jpeg, application/pdf).
 * @param string $file_name Nama file yang diusulkan.
 * @param string $bucket_name Nama bucket target (default: surat-sehat).
 * @return array ['url' => public_url] jika sukses, atau ['error' => message] jika gagal.
 */
if (!function_exists('upload_base64_to_supabase_storage')) {
    function upload_base64_to_supabase_storage($base64_string, $mime_type, $file_name, $bucket_name = 'surat-sehat') {
        $timestamp = time();
        $unique_name = $bucket_name . '_' . $timestamp . '_' . uniqid() . '.' . explode('/', $mime_type)[1];
        
        $public_url = "https://your-supabase-storage-url.supabase.co/storage/v1/object/public/{$bucket_name}/{$unique_name}";
        return ['error' => 'Fungsi upload Base64 ke Supabase Storage belum diimplementasikan di backend. Mohon hubungi developer.']; 
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

        // untuk validasi data masuk
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

        // untuk pengecekan kuota
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

        // --- Pengecekan User ---
        $user_response = makeSupabaseRequest('profiles?select=id,nama_lengkap&' . 'id=eq.' . urlencode($id_pengguna), 'GET');
        if (empty($user_response['data'])) {
             http_response_code(400);
             echo json_encode(['error' => 'User (Ketua Rombongan) tidak ditemukan.']);
             exit;
        }
        $user_id = $user_response['data'][0]['id'];
        $kode_reservasi = 'R' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // untuk insert data reservasi utama
        $reservation_data = [
            'id_pengguna' => $user_id,
            'kode_reservasi' => $kode_reservasi,
            'tanggal_pendakian' => $tanggal_pendakian,
            'jumlah_pendaki' => $jumlah_pendaki,
            'jumlah_tiket_parkir' => $jumlah_tiket_parkir,
            'total_harga' => $total_harga,
            'jumlah_potensi_sampah' => $jumlah_potensi_sampah,
            'status' => 'pending', 
        ];
        
        $reservation_response = makeSupabaseRequest('reservasi', 'POST', $reservation_data);

        if (isset($reservation_response['error'])) {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal membuat reservasi: ' . $reservation_response['error']]);
            exit;
        }
        $id_reservasi = $reservation_response['data'][0]['id_reservasi'];

        // untuk insert anggota rombongan
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

            // Insert pendaki rombongan
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

        // untuk insert barang bawaan sampah
        foreach ($barang_bawaan as $barang) {
            $barang_data = [
                'id_reservasi' => $id_reservasi,
                'nama_barang' => $barang['nama_barang'],
                'jenis_sampah' => $barang['jenis_sampah'],
                'jumlah' => $barang['jumlah'] ?? 1 
            ];
            
            $barang_response = makeSupabaseRequest('barang_bawaan_sampah', 'POST', $barang_data);

            if (isset($barang_response['error'])) {
                http_response_code(500);
                echo json_encode(['error' => 'Gagal menambahkan barang bawaan: ' . $barang_response['error']]);
                exit;
            }
        }

        // untuk update kuota terpesan
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
