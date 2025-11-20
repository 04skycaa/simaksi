<?php
// Lokasi file: /admin/poster/poster_ajax.php

// 1. SET HEADER JSON
header('Content-Type: application/json');

// 2. REQUIRE CONFIG (UNTUK FUNGSI supabase_request)
require_once '../../config/supabase.php';

// 3. LOGIKA AJAX (TANPA FUNGSI TAMBAHAN)
$action = $_GET['action'] ?? null;

if ($action === 'fetch_json') {
    $id_to_fetch = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$id_to_fetch) {
        echo json_encode(['error' => 'ID tidak valid dari _GET.']);
        exit;
    }

    // --- LOGIKA PENGAMBILAN DATA LANGSUNG ---
    // (Tidak memanggil fungsi internal, langsung eksekusi)
    
    global $serviceRoleKey;
    if (empty($serviceRoleKey)) {
        $serviceRoleKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE';
    }

    // Pastikan $id_to_fetch digunakan di sini
    $filter = urlencode("id_poster=eq.{$id_to_fetch}");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $data_raw_list = supabase_request('GET', "promosi_poster?{$filter}", null, $headers); 
    
    $data_raw = null;
    if (isset($data_raw_list['error']) || empty($data_raw_list) || !is_array($data_raw_list)) {
        $data_raw = null;
    } else {
        $data_raw = $data_raw_list[0] ?? null;
    }
    // --- AKHIR LOGIKA PENGAMBILAN DATA ---


    if (!$data_raw) {
        echo json_encode(['error' => 'Data tidak ditemukan untuk ID: ' . $id_to_fetch]);
        exit;
    }

    // 5. MAPPING DATA
    $gambar_url_full = 'https://placehold.co/64x64/CCCCCC/000000?text=IMG';
    if (!empty($data_raw['url_gambar'])) {
        $is_supabase_url = strpos($data_raw['url_gambar'], '://') !== false;
        if ($is_supabase_url) {
            $gambar_url_full = $data_raw['url_gambar'];
        } else {
            $gambar_url_full = '../../uploads/poster/' . $data_raw['url_gambar'];
        }
    }

    $data_mapped = [
        'id_promosi_poster' => $data_raw['id_poster'],
        'judul_poster'      => $data_raw['judul_poster'],
        'deskripsi_promosi' => $data_raw['deskripsi_poster'],
        'link_tautan'       => $data_raw['url_tautan'],
        'urutan_tampil'     => $data_raw['urutan'],
        'url_gambar'        => $data_raw['url_gambar'], 
        'status_promosi'    => ($data_raw['is_aktif'] === true || $data_raw['is_aktif'] === 't') ? 1 : 0,
        'current_image_url' => $gambar_url_full
    ];

    echo json_encode($data_mapped);
    exit;
}

echo json_encode(['error' => 'Action tidak diketahui.']);
exit;
?>