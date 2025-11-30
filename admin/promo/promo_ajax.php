<?php
// Lokasi file: /admin/promo/promo_ajax.php

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
    global $serviceRoleKey;
    if (empty($serviceRoleKey)) {
        $serviceRoleKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE';
    }

    // Pastikan $id_to_fetch digunakan di sini
    $filter = urlencode("id_promosi=eq.{$id_to_fetch}");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $data_raw_list = supabase_request('GET', "promosi?{$filter}", null, $headers);

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
    $data_mapped = [
        'id_promosi' => $data_raw['id_promosi'],
        'nama_promosi' => $data_raw['nama_promosi'],
        'deskripsi_promosi' => $data_raw['deskripsi_promosi'],
        'tipe_promosi' => $data_raw['tipe_promosi'],
        'nilai_promosi' => $data_raw['nilai_promosi'],
        'kondisi_min_pendaki' => $data_raw['kondisi_min_pendaki'],
        'kondisi_max_pendaki' => $data_raw['kondisi_max_pendaki'],
        'tanggal_mulai' => $data_raw['tanggal_mulai'],
        'tanggal_akhir' => $data_raw['tanggal_akhir'],
        'is_aktif' => ($data_raw['is_aktif'] === true || $data_raw['is_aktif'] === 't') ? 1 : 0,
        'kode_promo' => $data_raw['kode_promo']
    ];

    echo json_encode($data_mapped);
    exit;
}

echo json_encode(['error' => 'Action tidak diketahui.']);
exit;
?>