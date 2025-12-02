<?php
// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'fetch_json') {
    // Determine the correct path to the config file - we're in admin/poster/
    // so we need to go up one level to access admin/config/
    $config_path = '../config/supabase.php';

    if (!file_exists($config_path)) {
        http_response_code(500);
        echo json_encode(['error' => 'Config file not found']);
        exit;
    }

    // Get serviceRoleKey from the main script or define a default
    global $serviceRoleKey;
    if (empty($serviceRoleKey)) {
        $serviceRoleKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE';
    }

    header('Content-Type: application/json');

    $id_to_fetch = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $item_type = $_GET['type'] ?? 'poster'; // Default to poster

    if (!$id_to_fetch) {
        echo json_encode(['error' => 'ID tidak valid dari _GET.']);
        exit;
    }

    if ($item_type === 'promo' || $item_type === 'promosi') {
        // Fetch from promosi table
        $filter = urlencode("id_promosi=eq.{$id_to_fetch}");
        $headers = ['X-Override-Key' => $serviceRoleKey];
        $data_raw_list = supabase_request('GET', "promosi?{$filter}", null, $headers);

        $data_raw = null;
        if (isset($data_raw_list['error']) || empty($data_raw_list) || !is_array($data_raw_list)) {
            $data_raw = null;
        } else {
            $data_raw = $data_raw_list[0] ?? null;
        }

        if (!$data_raw) {
            echo json_encode(['error' => 'Data promosi tidak ditemukan untuk ID: ' . $id_to_fetch]);
            exit;
        }

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
    } else {
        // Fetch from promosi_poster table (default)
        $filter = urlencode("id_poster=eq.{$id_to_fetch}");
        $headers = ['X-Override-Key' => $serviceRoleKey];
        $data_raw_list = supabase_request('GET', "promosi_poster?{$filter}", null, $headers);

        $data_raw = null;
        if (isset($data_raw_list['error']) || empty($data_raw_list) || !is_array($data_raw_list)) {
            $data_raw = null;
        } else {
            $data_raw = $data_raw_list[0] ?? null;
        }

        if (!$data_raw) {
            echo json_encode(['error' => 'Data poster tidak ditemukan untuk ID: ' . $id_to_fetch]);
            exit;
        }

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
            'judul_poster' => $data_raw['judul_poster'],
            'deskripsi_poster' => $data_raw['deskripsi_poster'],
            'url_tautan' => $data_raw['url_tautan'],
            'urutan' => $data_raw['urutan'],
            'url_gambar' => $data_raw['url_gambar'],
            'is_aktif' => ($data_raw['is_aktif'] === true || $data_raw['is_aktif'] === 't') ? 1 : 0,
            'current_image_url' => $gambar_url_full
        ];
    }

    echo json_encode($data_mapped);
    exit;
}

require_once '../config/supabase.php';
$serviceRoleKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE';

$upload_dir = '../../uploads/poster/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

function fetch_promosi_list() {
    global $serviceRoleKey;
    $filter = urlencode("order=urutan.asc,id_poster.desc");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', "promosi_poster?{$filter}", null, $headers);
    if (isset($response['error'])) {
        $errorMessage = $response['error']['message'] ?? 'Unknown Error';
        return ['error' => true, 'message' => $errorMessage];
    }
    return $response;
}

function fetch_promosi_by_id($id) {
    global $serviceRoleKey;
    $filter = urlencode("id_poster=eq.{$id}");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', "promosi_poster?{$filter}", null, $headers);
    if (isset($response['error']) || empty($response) || !is_array($response)) {
        return null;
    }
    return $response[0] ?? null;
}

// Insert promosi poster
function insert_promosi_poster($data) {
    global $serviceRoleKey;
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('POST', 'promosi_poster', $data, $headers);
}

// Update promosi poster
function update_promosi_poster($id, $data) {
    global $serviceRoleKey;
    // Menggunakan 'id_poster'
    $endpoint = "promosi_poster?id_poster=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('PATCH', $endpoint, $data, $headers);
}

// Delete promosi poster
function delete_promosi_poster($id) {
    global $serviceRoleKey;
    // Menggunakan 'id_poster'
    $endpoint = "promosi_poster?id_poster=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('DELETE', $endpoint, null, $headers);
}


// ----------------------------------------------------------------------
// LOGIKA BACK-END (DB ASLI Supabase)
// ----------------------------------------------------------------------
$message = '';
$active_tab = isset($_GET['tab']) && in_array($_GET['tab'], ['tambah', 'daftar']) ? $_GET['tab'] : 'daftar';

// Functions for managing promotions (for promosi table)
function fetch_promo_list() {
    global $serviceRoleKey;
    $filter = urlencode("order=dibuat_pada.desc");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', "promosi?{$filter}", null, $headers);
    if (isset($response['error'])) {
        $errorMessage = $response['error']['message'] ?? 'Unknown Error';
        return ['error' => true, 'message' => $errorMessage];
    }
    return $response;
}

function fetch_promo_by_id($id) {
    global $serviceRoleKey;
    $filter = urlencode("id_promosi=eq.{$id}");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', "promosi?{$filter}", null, $headers);
    if (isset($response['error']) || empty($response) || !is_array($response)) {
        return null;
    }
    return $response[0] ?? null;
}

function fetch_promosi_poster_by_id($id) {
    global $serviceRoleKey;
    $filter = urlencode("id_poster=eq.{$id}");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', "promosi_poster?{$filter}", null, $headers);
    if (isset($response['error']) || empty($response) || !is_array($response)) {
        return null;
    }
    return $response[0] ?? null;
}

// Insert promosi
function insert_promo($data) {
    global $serviceRoleKey;
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('POST', 'promosi', $data, $headers);
}

// Update promosi
function update_promo($id, $data) {
    global $serviceRoleKey;
    $endpoint = "promosi?id_promosi=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('PATCH', $endpoint, $data, $headers);
}

// Delete promosi
function delete_promo($id) {
    global $serviceRoleKey;
    $endpoint = "promosi?id_promosi=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('DELETE', $endpoint, null, $headers);
}

// --- Logika 'Handle AJAX Fetch' dipindahkan ke poster_ajax.php ---


// --- Handle Form Submission (Insert/Update/Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action'] ?? null;
    $id_promosi = isset($_POST['id_promosi']) ? (int)$_POST['id_promosi'] : null;

    // --- LOGIKA INSERT & UPDATE ---
    if (in_array($action_type, ['tambah_promosi', 'edit_promosi'])) {
        // Determine if this is a poster or promo based on the presence of specific fields
        // For updates, we need to be more specific since form fields might be present but hidden
        if ($action_type === 'edit_promosi' && isset($_POST['id_promosi'])) {
            // For edit operations, we can also check if specific promo fields have values
            $is_promo = !empty($_POST['tipe_promosi']) && in_array($_POST['tipe_promosi'], ['PERSENTASE', 'POTONGAN_TETAP', 'HARGA_KHUSUS']);
        } else {
            // For insert operations, use tipe_promosi field
            $is_promo = !empty($_POST['tipe_promosi']);
        }

        if ($is_promo) {
            // This is a promo
            $data = [
                'nama_promosi' => trim(htmlspecialchars($_POST['nama_promosi'] ?? '')),
                'deskripsi_promosi' => trim(htmlspecialchars($_POST['deskripsi_promosi'] ?? '')),
                'tipe_promosi' => trim(htmlspecialchars($_POST['tipe_promosi'] ?? '')),
                'nilai_promosi' => (float)($_POST['nilai_promosi'] ?? 0),
                'kondisi_min_pendaki' => (int)($_POST['kondisi_min_pendaki'] ?? 1),
                'kondisi_max_pendaki' => !empty($_POST['kondisi_max_pendaki']) ? (int)$_POST['kondisi_max_pendaki'] : null,
                'tanggal_mulai' => trim($_POST['tanggal_mulai'] ?? ''),
                'tanggal_akhir' => trim($_POST['tanggal_akhir'] ?? ''),
                'is_aktif' => isset($_POST['status_promosi']) ? true : false,
                'kode_promo' => trim(htmlspecialchars($_POST['kode_promo'] ?? ''))
            ];

            if (empty($data['nama_promosi'])) {
                $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Nama promosi harus diisi.</div>";
            } else {
                if ($action_type === 'tambah_promosi') {
                    $response = insert_promo($data);
                    if (isset($response['error'])) {
                        $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase INSERT: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                    } else {
                        $redirect_url = "index.php?page=poster&tab=daftar&msg=promo_added";
                    }
                } elseif ($action_type === 'edit_promosi' && $id_promosi) {
                    $response = update_promo($id_promosi, $data);
                    if (isset($response['error'])) {
                        $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase UPDATE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                    } else {
                        $redirect_url = "index.php?page=poster&tab=daftar&msg=promo_updated";
                    }
                }
            }
        } else {
            // This is a poster
            $data = [
                'judul_poster' => trim(htmlspecialchars($_POST['judul_nama'] ?? '')),  // Changed from 'judul_promosi' to 'judul_nama' which is used in the edit form
                'deskripsi_poster' => trim(htmlspecialchars($_POST['deskripsi_promosi'] ?? '')),
                'urutan' => (int)($_POST['urutan_tampil'] ?? 0),
                'is_aktif' => isset($_POST['status_promosi']) ? true : false,  // This matches the checkbox name in edit modal: status_promosi
            ];

            // Add image information if exists
            $nama_file_gambar = $_POST['current_file_name'] ?? '';
            $upload_ok = true;

            if (isset($_FILES['gambar_promosi']) && $_FILES['gambar_promosi']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['gambar_promosi']['tmp_name'];
                $file_ext = strtolower(pathinfo($_FILES['gambar_promosi']['name'], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($file_ext, $allowed_ext)) {
                    $nama_file_gambar = uniqid('poster_') . '.' . $file_ext;
                    $target_file = $upload_dir . $nama_file_gambar;
                    if (!move_uploaded_file($file_tmp, $target_file)) {
                        $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Gagal memindahkan file lokal.</div>";
                        $upload_ok = false;
                    }
                } else {
                     $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Format file tidak didukung.</div>";
                     $upload_ok = false;
                }
            }

            if ($upload_ok) {
                $data['url_gambar'] = $nama_file_gambar;
            }


            if ($upload_ok) {
                if ($action_type === 'tambah_promosi') {
                    if (empty($data['url_gambar'])) {
                        $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Gambar poster harus diunggah.</div>";
                    } else {
                        $response = insert_promosi_poster($data);
                        if (isset($response['error'])) {
                            $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase INSERT: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                        } else {
                            $redirect_url = "index.php?page=poster&tab=daftar&msg=added";
                        }
                    }
                } elseif ($action_type === 'edit_promosi' && $id_promosi) {
                    if (empty($nama_file_gambar) && empty($_FILES['gambar_promosi']['name'])) {
                        unset($data['url_gambar']);
                    }

                    // For poster updates, we need to use the separate update logic that was handled in the earlier section
                    // The current $data was prepared based on $is_promo check earlier
                    $is_promo_submitted = isset($_POST['tipe_promosi']);  // Re-check type based on submitted fields
                    if ($is_promo_submitted) {
                        $response = update_promo($id_promosi, $data);
                        $msg_suffix = 'promo_';
                    } else {
                        $response = update_promosi_poster($id_promosi, $data);
                        $msg_suffix = '';
                    }

                    if (isset($response['error'])) {
                        $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase UPDATE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                    } else {
                        $redirect_url = "index.php?page=poster&tab=daftar&msg={$msg_suffix}updated";
                    }
                }
            }
        }
    }

    // --- LOGIKA DELETE ---
    if ($action_type === 'delete_promosi' && isset($_POST['id_promosi_delete'])) {
        $id_delete = (int)$_POST['id_promosi_delete'];
        $item_type = $_POST['item_type'] ?? ''; // Determine if it's a poster or promo

        if ($item_type === 'promo' || $item_type === 'promosi') {
            // Delete promo
            $response = delete_promo($id_delete);
            if (isset($response['error'])) {
                $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase DELETE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
            } else {
                $redirect_url = "index.php?page=poster&tab=daftar&msg=promo_deleted";
            }
        } else {
            // Delete poster
            $poster_to_delete = fetch_promosi_poster_by_id($id_delete); // Use correct function to get poster data
            $file_to_delete = $poster_to_delete['url_gambar'] ?? null;
            $response = delete_promosi_poster($id_delete);
            if (isset($response['error'])) {
                $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase DELETE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
            } else {
                if ($file_to_delete && file_exists($upload_dir . $file_to_delete) && strpos($file_to_delete, '://') === false) {
                    unlink($upload_dir . $file_to_delete);
                }
                $redirect_url = "index.php?page=poster&tab=daftar&msg=deleted";
            }
        }
    } elseif ($action_type === 'delete_poster' && isset($_POST['id_poster_delete'])) {
        // Also handle the old action type for poster deletion to ensure compatibility
        $id_delete = (int)$_POST['id_poster_delete'];
        $poster_to_delete = fetch_promosi_poster_by_id($id_delete); // Use correct function to get poster data
        $file_to_delete = $poster_to_delete['url_gambar'] ?? null;
        $response = delete_promosi_poster($id_delete);
        if (isset($response['error'])) {
            $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase DELETE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
        } else {
            if ($file_to_delete && file_exists($upload_dir . $file_to_delete) && strpos($file_to_delete, '://') === false) {
                unlink($upload_dir . $file_to_delete);
            }
            $redirect_url = "index.php?page=poster&tab=daftar&msg=deleted";
        }
    }
}


// 2. Fetch All Data (both posters and promos)
$promosi_poster_raw_list = fetch_promosi_list();
$promosi_poster_list = [];
if (isset($promosi_poster_raw_list['error'])) {
    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase FETCH: " . ($promosi_poster_raw_list['message'] ?? 'Terjadi kesalahan saat mengambil data poster.') . "</div>";
    $promosi_poster_raw_list = [];
}
$default_keys_poster = [
    'id_poster' => null,
    'judul_poster' => 'N/A',
    'url_tautan' => '',
    'is_aktif' => false,
    'urutan' => 0,
    'url_gambar' => null,
    'deskripsi_poster' => '',
];
foreach ($promosi_poster_raw_list as $row) {
    $cleaned_row = array_filter((array)$row, function($value) { return $value !== null; });
    $poster_data = array_merge($default_keys_poster, $cleaned_row);
    $poster = [
        'id' => $poster_data['id_poster'], // Baca dari 'id_poster'
        'judul' => $poster_data['judul_poster'],
        'deskripsi' => $poster_data['deskripsi_poster'],
        'status' => $poster_data['is_aktif'],
        'urutan' => $poster_data['urutan'],
        'url_gambar' => $poster_data['url_gambar'],
        'url_tautan' => $poster_data['url_tautan'],
        'type' => 'poster' // Mark as poster
    ];
    $gambar_file = $poster['url_gambar'];
    $is_supabase_url = $gambar_file && strpos($gambar_file, '://') !== false;
    if ($is_supabase_url) {
        $poster['gambar_url'] = $gambar_file;
    } else {
        $poster['gambar_url'] = '../../uploads/poster/' . ($gambar_file ?? 'placeholder.png');
    }
    $promosi_poster_list[] = $poster;
}

$promosi_raw_list = fetch_promo_list();
$promosi_list = [];
if (isset($promosi_raw_list['error'])) {
    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase FETCH: " . ($promosi_raw_list['message'] ?? 'Terjadi kesalahan saat mengambil data promosi.') . "</div>";
    $promosi_raw_list = [];
}
$default_keys_promo = [
    'id_promosi' => null,
    'nama_promosi' => 'N/A',
    'deskripsi_promosi' => '',
    'tipe_promosi' => '',
    'nilai_promosi' => 0,
    'kondisi_min_pendaki' => 1,
    'kondisi_max_pendaki' => null,
    'tanggal_mulai' => '',
    'tanggal_akhir' => '',
    'is_aktif' => false,
    'dibuat_pada' => '',
    'kode_promo' => ''
];
foreach ($promosi_raw_list as $row) {
    $cleaned_row = array_filter((array)$row, function($value) { return $value !== null; });
    $promosi_data = array_merge($default_keys_promo, $cleaned_row);
    $promo = [
        'id' => $promosi_data['id_promosi'],
        'judul' => $promosi_data['nama_promosi'], // Use 'judul' for consistency in display
        'deskripsi' => $promosi_data['deskripsi_promosi'],
        'tipe' => $promosi_data['tipe_promosi'],
        'nilai' => $promosi_data['nilai_promosi'],
        'tanggal_mulai' => $promosi_data['tanggal_mulai'],
        'tanggal_akhir' => $promosi_data['tanggal_akhir'],
        'status' => $promosi_data['is_aktif'],
        'kode' => $promosi_data['kode_promo'],
        'type' => 'promo' // Mark as promo
    ];
    $promosi_list[] = $promo;
}

// Combine both lists into one main list
$all_items = array_merge($promosi_poster_list, $promosi_list);
// Sort by creation date (newest first) - we'll sort by ID as a proxy for now
usort($all_items, function($a, $b) {
    return $b['id'] - $a['id'];
});

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Poster baru berhasil ditambahkan!</div>";
    } elseif ($_GET['msg'] === 'updated') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Poster berhasil diubah!</div>";
    } elseif ($_GET['msg'] === 'deleted') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Poster berhasil dihapus.</div>";
    } elseif ($_GET['msg'] === 'promo_added') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Promosi baru berhasil ditambahkan!</div>";
    } elseif ($_GET['msg'] === 'promo_updated') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Promosi berhasil diubah!</div>";
    } elseif ($_GET['msg'] === 'promo_deleted') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Promosi berhasil dihapus.</div>";
    }
}
?>


<style>
    .tabs-container {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 10px;
    }

    .tab-btn {
        padding: 8px 16px;
        border: 1px solid #d1d5db;
        background-color: #f3f4f6;
        cursor: pointer;
        border-radius: 6px 6px 0 0;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
    }

    .tab-btn.active {
        background-color: #ffffff;
        border-bottom: 1px solid transparent;
        color: #059669;
        border-color: #d1d5db #d1d5db #ffffff #d1d5db;
    }

    .form-section {
        display: none;
    }

    .form-section.active {
        display: block;
    }

    /* Consistent form styling */
    input[type="text"],
    input[type="url"],
    input[type="number"],
    input[type="datetime-local"],
    input[type="file"],
    select,
    textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.15s, box-shadow 0.15s;
        box-sizing: border-box;
    }

    input[type="text"]:focus,
    input[type="url"]:focus,
    input[type="number"]:focus,
    input[type="datetime-local"]:focus,
    input[type="file"]:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .form-label iconify-icon {
        margin-right: 8px;
        vertical-align: middle;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: background-color 0.15s;
    }

    .btn-blue {
        background-color: #3b82f6;
        color: white;
    }

    .btn-green {
        background-color: #10b981;
        color: white;
    }

    .btn-yellow {
        background-color: #f59e0b;
        color: white;
    }

    .btn-red {
        background-color: #ef4444;
        color: white;
    }

    .status-active {
        background-color: #dcfce7;
        color: #166534;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-inactive {
        background-color: #fee2e2;
        color: #991b1b;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
</style>
<div class="poster-container">
    <!-- Area Tab Header -->
    <div class="card mb-6">
        <div class="flex tab-header" style="border-radius: 12px 12px 0 0;">
            <a href="index.php?page=poster&tab=daftar" class="tab-item <?php echo $active_tab === 'daftar' ? 'tab-active' : ''; ?>">
                <iconify-icon icon="mdi:view-list" style="width: 20px; height: 20px; margin-right: 8px;"></iconify-icon>
                <span>Daftar Promosi & Poster</span>
            </a>
            <a href="index.php?page=poster&tab=tambah" class="tab-item <?php echo $active_tab === 'tambah' ? 'tab-active' : ''; ?>">
                <iconify-icon icon="mdi:plus-circle" style="width: 20px; height: 20px; margin-right: 8px;"></iconify-icon>
                <span>Tambah Baru</span>
            </a>
        </div>
    </div>

    <?php echo $message; // Menampilkan pesan sukses/error ?>

    <!-- Konten Tab -->
    <div class="p-6 card card-shadow">
        <?php if ($active_tab === 'daftar'): ?>
            <h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px;" class="flex items-center">
                <iconify-icon icon="mdi:monitor-dashboard" style="width: 24px; height: 24px; margin-right: 8px; color: #059669;"></iconify-icon>
                Daftar Promosi & Poster
            </h2>

            <!-- Tabel Promosi -->
            <div style="margin-bottom: 40px;">
                <h3 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 16px; border-bottom: 2px solid #059669; padding-bottom: 8px; display: inline-block;">
                    <iconify-icon icon="mdi:ticket-percent-outline" style="width: 20px; height: 20px; margin-right: 8px;"></iconify-icon>
                    Tabel Promosi (Total: <?php echo count($promosi_list); ?>)
                </h3>
                <div class="table-container" style="overflow-x: auto;">
                    <table class="data-table">
                        <thead class="table-head">
                            <tr>
                                <th>NAMA PROMOSI</th>
                                <th>KODE PROMO</th>
                                <th>TIPE</th>
                                <th>NILAI</th>
                                <th>TANGGAL MULAI</th>
                                <th>TANGGAL AKHIR</th>
                                <th>STATUS</th>
                                <th style="text-align: center;">AKSI</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php if (empty($promosi_list)): ?>
                            <tr>
                                <td colspan="8" style="padding: 16px; text-align: center; color: #6b7280;">Tidak ada data promosi. Silakan tambahkan promosi baru.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($promosi_list as $promosi): ?>
                            <tr class="data-row">
                                <td style="font-weight: 500; color: #1f2937;">
                                    <?php echo htmlspecialchars($promosi['judul'] ?? 'N/A'); ?>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php echo htmlspecialchars($promosi['kode'] ?? '-'); ?>
                                </td>
                                <td style="color: #6b7280; text-transform: capitalize;">
                                    <?php echo htmlspecialchars($promosi['tipe'] ?? 'N/A'); ?>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php
                                        if ($promosi['tipe'] === 'PERSENTASE') {
                                            echo htmlspecialchars($promosi['nilai']) . '%';
                                        } elseif ($promosi['tipe'] === 'POTONGAN_TETAP') {
                                            echo 'Rp ' . number_format($promosi['nilai'] ?? 0, 0, ',', '.');
                                        } else {
                                            echo 'Rp ' . number_format($promosi['nilai'] ?? 0, 0, ',', '.');
                                        }
                                    ?>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php echo $promosi['tanggal_mulai'] ? date('d M Y', strtotime($promosi['tanggal_mulai'])) : '-'; ?>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php echo $promosi['tanggal_akhir'] ? date('d M Y', strtotime($promosi['tanggal_akhir'])) : '-'; ?>
                                </td>
                                <td>
                                    <?php $status = ($promosi['status'] === true || $promosi['status'] === 't') ? 1 : 0; ?>
                                    <span class="<?php echo $status ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $status ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <button onclick="openEditModal(<?php echo json_encode($promosi['id']); ?>, 'promo')" class="btn btn-yellow" style="padding: 4px 12px; font-size: 12px; margin-right: 8px;">
                                        <iconify-icon icon="mdi:pencil-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Edit
                                    </button>
                                    <button onclick="openDeleteModal(<?php echo json_encode($promosi['id']); ?>, <?php echo json_encode(htmlspecialchars($promosi['judul'] ?? 'Promosi ini')); ?>, 'promo')" class="btn btn-red" style="padding: 4px 12px; font-size: 12px;">
                                        <iconify-icon icon="mdi:trash-can-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabel Poster -->
            <div>
                <h3 style="font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 16px; border-bottom: 2px solid #3b82f6; padding-bottom: 8px; display: inline-block;">
                    <iconify-icon icon="mdi:image-outline" style="width: 20px; height: 20px; margin-right: 8px;"></iconify-icon>
                    Tabel Poster (Total: <?php echo count($promosi_poster_list); ?>)
                </h3>
                <div class="table-container" style="overflow-x: auto;">
                    <table class="data-table">
                        <thead class="table-head">
                            <tr>
                                <th>GAMBAR</th>
                                <th>JUDUL POSTER</th>
                                <th>DESKRIPSI</th>
                                <th>TAUTAN</th>
                                <th>URUTAN</th>
                                <th>STATUS</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($promosi_poster_list)): ?>
                                <tr>
                                    <td colspan="7" style="padding: 16px; text-align: center; color: #6b7280;">Tidak ada data poster. Silakan tambahkan poster baru.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($promosi_poster_list as $poster): ?>
                                <tr class="data-row">
                                    <td>
                                        <?php if (!empty($poster['url_gambar'])): ?>
                                            <img
                                                src="<?php echo $poster['gambar_url']; ?>"
                                                alt="<?php echo htmlspecialchars($poster['judul'] ?? ''); ?>"
                                                style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;"
                                                onerror="this.onerror=null;this.src='https://placehold.co/50x50/CCCCCC/000000?text=IMG';">
                                        <?php else: ?>
                                            <img
                                                src="https://placehold.co/48x48/CCCCCC/000000?text=-"
                                                alt="No Image"
                                                style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight: 500; color: #1f2937;">
                                        <?php echo htmlspecialchars($poster['judul'] ?? 'N/A'); ?>
                                    </td>
                                    <td style="color: #6b7280;">
                                        <?php
                                            $description = $poster['deskripsi'] ?? '';
                                            echo strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
                                        ?>
                                    </td>
                                    <td style="color: #6b7280;">
                                        <?php echo htmlspecialchars($poster['url_tautan'] ?? '-'); ?>
                                    </td>
                                    <td style="color: #6b7280; font-weight: 500;">
                                        <?php echo (int)($poster['urutan'] ?? 0); ?>
                                    </td>
                                    <td>
                                        <?php $status = ($poster['status'] === true || $poster['status'] === 't') ? 1 : 0; ?>
                                        <span class="<?php echo $status ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $status ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button onclick="openEditModal(<?php echo json_encode($poster['id']); ?>, 'poster')" class="btn btn-yellow" style="padding: 4px 12px; font-size: 12px; margin-right: 8px;">
                                            <iconify-icon icon="mdi:pencil-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Edit
                                        </button>
                                        <button onclick="openDeleteModal(<?php echo json_encode($poster['id']); ?>, <?php echo json_encode(htmlspecialchars($poster['judul'] ?? 'Poster ini')); ?>, 'poster')" class="btn btn-red" style="padding: 4px 12px; font-size: 12px;">
                                            <iconify-icon icon="mdi:trash-can-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Hapus
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: // $active_tab === 'tambah' ?>
            <h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px;" class="flex items-center">
                <iconify-icon icon="mdi:plus-circle" style="width: 24px; height: 24px; margin-right: 8px; color: #059669;"></iconify-icon>
                Tambah Baru
            </h2>

            <!-- Unified form with tabs for Poster or Promo -->
            <div class="tabs-container" style="margin-bottom: 20px;">
                <button id="poster-tab-btn" class="tab-btn active" onclick="switchTab('poster')">Tambah Poster</button>
                <button id="promo-tab-btn" class="tab-btn" onclick="switchTab('promo')">Tambah Promosi</button>
            </div>

            <!-- Poster Form -->
            <div id="poster-form" class="form-section active">
                <form method="POST" enctype="multipart/form-data" action="index.php?page=poster&tab=daftar">
                    <input type="hidden" name="action" value="tambah_promosi">
                    <div class="grid-2-cols">
                        <!-- Kolom Kiri -->
                        <div>
                            <div class="form-group">
                                <label for="judul_poster" class="form-label"><iconify-icon icon="mdi:format-title"></iconify-icon> Judul Poster</label>
                                <input type="text" id="judul_poster" name="judul_promosi" required value="" placeholder="Masukkan judul poster">
                            </div>
                            <div class="form-group">
                                <label for="deskripsi_poster" class="form-label"><iconify-icon icon="mdi:align-left"></iconify-icon> Deskripsi Poster</label>
                                <textarea id="deskripsi_poster" name="deskripsi_promosi" rows="3" placeholder="Deskripsi singkat poster ini"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="gambar_poster" class="form-label"><iconify-icon icon="mdi:image-outline"></iconify-icon> Upload Gambar Poster</label>
                                <input type="file" id="gambar_poster" name="gambar_promosi" required>
                                <p style="margin-top: 4px; font-size: 12px; color: #6b7280;">Format: JPG, PNG, GIF. Maksimal 5MB.</p>
                                <div id="gambar_poster_preview_container" style="margin-top: 10px;">
                                    <img id="gambar_poster_preview" src="" alt="Pratinjau Gambar" style="max-width: 200px; max-height: 200px; display: none; border-radius: 6px; border: 1px solid #d1d5db;">
                                </div>
                            </div>
                        </div>
                        <!-- Kolom Kanan -->
                        <div>
                            <div class="form-group">
                                <label for="urutan_poster" class="form-label"><iconify-icon icon="mdi:sort-numeric-ascending"></iconify-icon> Urutan Tampil</label>
                                <input type="number" id="urutan_poster" name="urutan_tampil" required min="0" value="0">
                            </div>
                            <div class="form-group checkbox-group" style="padding-top: 8px;">
                                <input id="status_poster" name="status_promosi" type="checkbox" value="1" checked>
                                <label for="status_poster" class="form-label-checkbox" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Poster akan ditampilkan)</label>
                            </div>
                        </div>
                    </div>
                    <!-- Tombol Aksi -->
                    <div class="flex-end">
                        <button type="button" onclick="alert('Fungsi Pratinjau belum diimplementasikan.')" class="btn btn-blue" style="margin-right: 12px;">
                            <iconify-icon icon="mdi:eye-outline" style="width: 16px; height: 16px; margin-right: 8px;"></iconify-icon> Pratinjau
                        </button>
                        <button type="submit" class="btn btn-green btn-pulse">
                            <iconify-icon icon="mdi:content-save-outline" style="width: 16px; height: 16px; margin-right: 8px;"></iconify-icon> Simpan Poster
                        </button>
                    </div>
                </form>
            </div>

            <!-- Promosi Form (hidden by default) -->
            <div id="promo-form" class="form-section">
                <form method="POST" action="index.php?page=poster&tab=daftar">
                    <input type="hidden" name="action" value="tambah_promosi">
                    <div class="grid-2-cols">
                        <!-- Kolom Kiri -->
                        <div>
                            <div class="form-group">
                                <label for="nama_promosi" class="form-label"><iconify-icon icon="mdi:format-title"></iconify-icon> Nama Promosi</label>
                                <input type="text" id="nama_promosi" name="nama_promosi" required value="" placeholder="Masukkan nama promosi">
                            </div>
                            <div class="form-group">
                                <label for="deskripsi_promosi" class="form-label"><iconify-icon icon="mdi:align-left"></iconify-icon> Deskripsi Promosi</label>
                                <textarea id="deskripsi_promosi" name="deskripsi_promosi" rows="3" placeholder="Deskripsi singkat promosi ini"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="tipe_promosi" class="form-label"><iconify-icon icon="mdi:tag-multiple"></iconify-icon> Tipe Promosi</label>
                                <select id="tipe_promosi" name="tipe_promosi" required>
                                    <option value="">Pilih Tipe Promosi</option>
                                    <option value="PERSENTASE">Persentase</option>
                                    <option value="POTONGAN_TETAP">Potongan Tetap</option>
                                    <option value="HARGA_KHUSUS">Harga Khusus</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nilai_promosi" class="form-label"><iconify-icon icon="mdi:percent"></iconify-icon> Nilai Promosi</label>
                                <input type="number" id="nilai_promosi" name="nilai_promosi" step="0.01" required min="0" value="0" placeholder="Masukkan nilai promosi">
                            </div>
                        </div>
                        <!-- Kolom Kanan -->
                        <div>
                            <div class="form-group">
                                <label for="kode_promosi" class="form-label"><iconify-icon icon="mdi:ticket-percent-outline"></iconify-icon> Kode Promosi</label>
                                <input type="text" id="kode_promosi" name="kode_promo" value="" placeholder="Masukkan kode promosi (opsional)">
                            </div>
                            <div class="form-group">
                                <label for="tanggal_mulai" class="form-label"><iconify-icon icon="mdi:calendar-start"></iconify-icon> Tanggal Mulai</label>
                                <input type="datetime-local" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_akhir" class="form-label"><iconify-icon icon="mdi:calendar-end"></iconify-icon> Tanggal Akhir</label>
                                <input type="datetime-local" id="tanggal_akhir" name="tanggal_akhir" required>
                            </div>
                            <div class="form-group">
                                <label for="kondisi_min_pendaki" class="form-label"><iconify-icon icon="mdi:account-group"></iconify-icon> Min. Pendaki</label>
                                <input type="number" id="kondisi_min_pendaki" name="kondisi_min_pendaki" required min="1" value="1">
                            </div>
                            <div class="form-group">
                                <label for="kondisi_max_pendaki" class="form-label"><iconify-icon icon="mdi:account-group"></iconify-icon> Max. Pendaki (Opsional)</label>
                                <input type="number" id="kondisi_max_pendaki" name="kondisi_max_pendaki" min="1" placeholder="Kosongkan jika tidak ada batas">
                            </div>
                            <div class="form-group checkbox-group" style="padding-top: 8px;">
                                <input id="is_promosi_aktif" name="is_promosi_aktif" type="checkbox" value="1" checked>
                                <label for="is_promosi_aktif" class="form-label-checkbox" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Promosi akan digunakan)</label>
                            </div>
                        </div>
                    </div>
                    <!-- Tombol Aksi -->
                    <div class="flex-end">
                        <button type="button" onclick="alert('Fungsi Pratinjau belum diimplementasikan.')" class="btn btn-blue" style="margin-right: 12px;">
                            <iconify-icon icon="mdi:eye-outline" style="width: 16px; height: 16px; margin-right: 8px;"></iconify-icon> Pratinjau
                        </button>
                        <button type="submit" class="btn btn-green btn-pulse">
                            <iconify-icon icon="mdi:content-save-outline" style="width: 16px; height: 16px; margin-right: 8px;"></iconify-icon> Simpan Promosi
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT (Unified) -->
<!-- ============================================== -->
<div class="modal-overlay" id="editModalOverlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="editModalTitle">Edit Item</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="editForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="editAction" value="edit_promosi">
                <input type="hidden" name="id_promosi" id="modal_id_promosi">
                <input type="hidden" name="current_file_name" id="modal_current_file_name">

                <!-- Unified fields that are common -->
                <div class="form-group">
                    <label for="modal_judul" class="form-label"><iconify-icon icon="mdi:format-title"></iconify-icon> Judul/Nama</label>
                    <input type="text" id="modal_judul" name="judul_nama" required value="" placeholder="Masukkan judul atau nama">
                </div>

                <div class="form-group">
                    <label for="modal_deskripsi" class="form-label"><iconify-icon icon="mdi:align-left"></iconify-icon> Deskripsi</label>
                    <textarea id="modal_deskripsi" name="deskripsi_promosi" rows="3" style="resize: vertical;" placeholder="Deskripsi item"></textarea>
                </div>

                <!-- Fields specific to poster -->
                <div id="poster_fields">
                    <div class="form-group">
                        <label for="modal_gambar" class="form-label"><iconify-icon icon="mdi:image-outline"></iconify-icon> Upload Gambar</label>
                        <input type="file" id="modal_gambar" name="gambar_promosi" style="display: block; width: 100%; padding-top: 8px; padding-bottom: 8px;">
                        <p style="margin-top: 4px; font-size: 12px; color: #6b7280;">Kosongkan jika tidak ingin mengganti gambar.</p>
                        <div id="modal_current_image_container" style="margin-top: 8px; font-size: 14px; color: #4b5563;" class="flex items-center">
                            <span style="margin-right: 8px;">Gambar Saat Ini:</span>
                            <img id="modal_current_image" alt="Current Item" style="width: 64px; height: 64px; object-fit: cover; border-radius: 6px; border: 1px solid #d1d5db;">
                        </div>
                        <div id="gambar_preview_container" style="margin-top: 10px;">
                            <img id="gambar_preview" src="" alt="Pratinjau Gambar" style="max-width: 200px; max-height: 200px; display: none; border-radius: 6px; border: 1px solid #d1d5db;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="modal_urutan" class="form-label"><iconify-icon icon="mdi:sort-numeric-ascending"></iconify-icon> Urutan Tampil</label>
                        <input type="number" id="modal_urutan" name="urutan_tampil" required min="0" value="0">
                    </div>
                </div>

                <!-- Fields specific to promo -->
                <div id="promo_fields" style="display:none;">
                    <div class="form-group">
                        <label for="modal_tipe" class="form-label"><iconify-icon icon="mdi:tag-multiple"></iconify-icon> Tipe Promosi</label>
                        <select id="modal_tipe" name="tipe_promosi">
                            <option value="">Pilih Tipe Promosi</option>
                            <option value="PERSENTASE">Persentase</option>
                            <option value="POTONGAN_TETAP">Potongan Tetap</option>
                            <option value="HARGA_KHUSUS">Harga Khusus</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal_nilai" class="form-label"><iconify-icon icon="mdi:percent"></iconify-icon> Nilai Promosi</label>
                        <input type="number" id="modal_nilai" name="nilai_promosi" step="0.01" min="0" value="0" placeholder="Masukkan nilai promosi">
                    </div>
                    <div class="form-group">
                        <label for="modal_kode" class="form-label"><iconify-icon icon="mdi:ticket-percent-outline"></iconify-icon> Kode Promosi</label>
                        <input type="text" id="modal_kode" name="kode_promo" value="" placeholder="Masukkan kode promosi (opsional)">
                    </div>
                    <div class="form-group">
                        <label for="modal_tanggal_mulai" class="form-label"><iconify-icon icon="mdi:calendar-start"></iconify-icon> Tanggal Mulai</label>
                        <input type="datetime-local" id="modal_tanggal_mulai" name="tanggal_mulai">
                    </div>
                    <div class="form-group">
                        <label for="modal_tanggal_akhir" class="form-label"><iconify-icon icon="mdi:calendar-end"></iconify-icon> Tanggal Akhir</label>
                        <input type="datetime-local" id="modal_tanggal_akhir" name="tanggal_akhir">
                    </div>
                    <div class="form-group">
                        <label for="modal_min_pendaki" class="form-label"><iconify-icon icon="mdi:account-group"></iconify-icon> Min. Pendaki</label>
                        <input type="number" id="modal_min_pendaki" name="kondisi_min_pendaki" min="1" value="1">
                    </div>
                    <div class="form-group">
                        <label for="modal_max_pendaki" class="form-label"><iconify-icon icon="mdi:account-group"></iconify-icon> Max. Pendaki (Opsional)</label>
                        <input type="number" id="modal_max_pendaki" name="kondisi_max_pendaki" min="1" placeholder="Kosongkan jika tidak ada batas">
                    </div>
                </div>

                <div class="form-group checkbox-group" style="padding-top: 8px;">
                    <input id="modal_status" name="status_promosi" type="checkbox" value="1">
                    <label for="modal_status" class="form-label-checkbox" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Item akan ditampilkan/digunakan)</label>
                </div>

                <div class="flex-end">
                    <button type="submit" class="form-submit-btn">
                        <iconify-icon icon="mdi:content-save-outline" style="width: 16px; height: 16px; margin-right: 8px;"></iconify-icon> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL HAPUS (Unified) -->
<!-- ============================================== -->
<div class="modal-overlay" id="deleteModalOverlay">
    <div class="modal-container" style="max-width: 480px; text-align: center;">
        <div class="modal-header">
            <h3>Konfirmasi Penghapusan</h3>
            <button class="modal-close-btn" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <iconify-icon icon="mdi:alert-decagram" style="width: 64px; height: 64px; color: #f59e0b; margin-bottom: 16px;"></iconify-icon>
            <p style="color: #6b7280; margin-bottom: 24px;">Apakah Anda yakin ingin menghapus <span id="item_type_delete">item</span> **<span id="delete_judul_target" style="font-weight: 600;"></span>**? Tindakan ini tidak dapat dibatalkan.</p>
            <form method="POST" style="display:inline;" id="deleteForm">
                <input type="hidden" name="action" id="delete_action" value="">
                <input type="hidden" name="id_promosi_delete" id="delete_id_target">
                <input type="hidden" name="item_type" id="delete_item_type" value="">
                <button type="button" class="btn" onclick="closeDeleteModal()">&times; Batal</button>
                <button type="submit" class="btn btn-red">
                    <iconify-icon icon="mdi:trash-can-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Hapus Permanen
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- SCRIPT LOKAL (Modal, dll) - HARUS DI ATAS ICONIFY -->
<!-- ============================================== -->
<script>
    console.log("SCRIPT POSTER.PHP: Berhasil dimuat.");

    // --- Fungsi Modal Edit Poster ---
    function closeEditPosterModal() {
        console.log("CLOSE EDIT POSTER: Menutup modal edit poster...");
        document.getElementById('editPosterModalOverlay').classList.remove('show');
    }

    async function openEditPosterModal(id) {
        console.log("OPEN EDIT POSTER: Tombol edit poster diklik.");
        if (!id) {
            console.error("OPEN EDIT POSTER ERROR: ID tidak valid (null atau 0).");
            return;
        }
        console.log("OPEN EDIT POSTER: Membuka modal untuk ID poster:", id);

        const modalTitle = document.getElementById('editPosterModalTitle');
        const modal = document.getElementById('editPosterModalOverlay');

        // Cek jika elemen ada sebelum menggunakannya
        if (modalTitle) {
            modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Memuat Data Poster...`;
        } else {
            console.error("OPEN EDIT POSTER ERROR: Element 'editPosterModalTitle' tidak ditemukan.");
            return; // Hentikan jika HTML modal tidak ada
        }

        if (modal) {
            modal.classList.add('show');
            console.log("OPEN EDIT POSTER: Menjalankan 'modal.classList.add('show')'.");
        } else {
            console.error("OPEN EDIT POSTER ERROR: Element 'editPosterModalOverlay' tidak ditemukan.");
            return; // Hentikan jika HTML modal tidak ada
        }


        // =================================================================
        // BLOK JAVASCRIPT (SUDAH BENAR)
        // =================================================================
        try {
            // Path 'poster/poster_ajax.php' sudah benar *relatif* terhadap halaman admin
            const fetchUrl = `poster/poster_ajax.php?action=fetch_json&id=${id}&_cache=${new Date().getTime()}`;
            console.log("OPEN EDIT POSTER: Mengambil data dari URL:", fetchUrl);

            const response = await fetch(fetchUrl);

            console.log("OPEN EDIT POSTER: Response status dari server:", response.status);
            if (!response.ok) {
                console.error("OPEN EDIT POSTER FETCH ERROR: Network response was not ok.", response);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                console.error("OPEN EDIT POSTER JSON PARSE ERROR:", jsonError);
                console.error("Server Response (yang bukan JSON):", responseText);
                throw new Error("Respons bukan JSON yang valid.");
            }

            console.log("OPEN EDIT POSTER: Data JSON diterima:", data);

            if (!data || data.error) {
                alert('Gagal memuat data poster: ' + (data.error || 'Data tidak ditemukan.'));
                closeEditPosterModal();
                return;
            }

            // === PENGISIAN FORM ===
            document.getElementById('modal_poster_id_promosi').value = data.id_promosi_poster || '';
            document.getElementById('modal_poster_judul').value = data.judul_poster || '';
            document.getElementById('modal_poster_deskripsi').value = data.deskripsi_promosi || '';
            document.getElementById('modal_poster_urutan').value = data.urutan_tampil || 0;
            document.getElementById('modal_poster_current_file_name').value = data.url_gambar || '';
            document.getElementById('modal_poster_status').checked = data.status_promosi === 1;

            const imgElement = document.getElementById('modal_poster_current_image');
            const defaultImg = 'https://placehold.co/64x64/CCCCCC/000000?text=IMG';

            if (data.current_image_url) {
                imgElement.src = data.current_image_url;
            } else {
                imgElement.src = defaultImg;
            }
            imgElement.onerror = function() { this.src = defaultImg; };

            modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Edit Poster: ${data.judul_poster || 'ID: ' + id}`;

            console.log("OPEN EDIT POSTER: Sukses. Modal seharusnya sudah terisi penuh.");

        } catch (error) {
            console.error('OPEN EDIT POSTER CATCH ERROR: Terjadi kesalahan besar saat fetch/proses data:', error);
            alert('Terjadi kesalahan saat mengambil data poster.');
            closeEditPosterModal();
        }
        // =================================================================
        // AKHIR BLOK JAVASCRIPT
        // =================================================================
    }

    // --- Fungsi Modal Edit Promosi ---
    function closeEditPromoModal() {
        console.log("CLOSE EDIT PROMO: Menutup modal edit promosi...");
        document.getElementById('editPromoModalOverlay').classList.remove('show');
    }

    async function openEditPromoModal(id) {
        console.log("OPEN EDIT PROMO: Tombol edit promosi diklik.");
        if (!id) {
            console.error("OPEN EDIT PROMO ERROR: ID tidak valid (null atau 0).");
            return;
        }
        console.log("OPEN EDIT PROMO: Membuka modal untuk ID promosi:", id);

        const modalTitle = document.getElementById('editPromoModalTitle');
        const modal = document.getElementById('editPromoModalOverlay');

        // Cek jika elemen ada sebelum menggunakannya
        if (modalTitle) {
            modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Memuat Data Promosi...`;
        } else {
            console.error("OPEN EDIT PROMO ERROR: Element 'editPromoModalTitle' tidak ditemukan.");
            return; // Hentikan jika HTML modal tidak ada
        }

        if (modal) {
            modal.classList.add('show');
            console.log("OPEN EDIT PROMO: Menjalankan 'modal.classList.add('show')'.");
        } else {
            console.error("OPEN EDIT PROMO ERROR: Element 'editPromoModalOverlay' tidak ditemukan.");
            return; // Hentikan jika HTML modal tidak ada
        }


        try {
            // Fetch data from the promo AJAX endpoint
            const fetchUrl = `promo_ajax.php?action=fetch_json&id=${id}&_cache=${new Date().getTime()}`;
            console.log("OPEN EDIT PROMO: Mengambil data dari URL:", fetchUrl);

            const response = await fetch(fetchUrl);

            console.log("OPEN EDIT PROMO: Response status dari server:", response.status);
            if (!response.ok) {
                console.error("OPEN EDIT PROMO FETCH ERROR: Network response was not ok.", response);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                console.error("OPEN EDIT PROMO JSON PARSE ERROR:", jsonError);
                console.error("Server Response (yang bukan JSON):", responseText);
                throw new Error("Respons bukan JSON yang valid.");
            }

            console.log("OPEN EDIT PROMO: Data JSON diterima:", data);

            if (!data || data.error) {
                alert('Gagal memuat data promosi: ' + (data.error || 'Data tidak ditemukan.'));
                closeEditPromoModal();
                return;
            }

            // === PENGISIAN FORM PROMOSI ===
            document.getElementById('modal_promo_id_promosi').value = data.id_promosi || '';
            document.getElementById('modal_promo_nama').value = data.nama_promosi || '';
            document.getElementById('modal_promo_deskripsi').value = data.deskripsi_promosi || '';
            document.getElementById('modal_promo_tipe').value = data.tipe_promosi || '';
            document.getElementById('modal_promo_nilai').value = data.nilai_promosi || 0;
            document.getElementById('modal_promo_kode').value = data.kode_promo || '';

            // Format date for datetime-local input (YYYY-MM-DDTHH:MM)
            if (data.tanggal_mulai) {
                const startDate = new Date(data.tanggal_mulai);
                document.getElementById('modal_promo_tanggal_mulai').value = startDate.toISOString().slice(0, 16);
            }
            if (data.tanggal_akhir) {
                const endDate = new Date(data.tanggal_akhir);
                document.getElementById('modal_promo_tanggal_akhir').value = endDate.toISOString().slice(0, 16);
            }

            document.getElementById('modal_promo_min_pendaki').value = data.kondisi_min_pendaki || 1;
            document.getElementById('modal_promo_max_pendaki').value = data.kondisi_max_pendaki || '';
            document.getElementById('modal_promo_status').checked = data.is_aktif === 1 || data.is_aktif === true;

            modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Edit Promosi: ${data.nama_promosi || 'ID: ' + id}`;

            console.log("OPEN EDIT PROMO: Sukses. Modal promosi seharusnya sudah terisi penuh.");

        } catch (error) {
            console.error('OPEN EDIT PROMO CATCH ERROR: Terjadi kesalahan besar saat fetch/proses data:', error);
            alert('Terjadi kesalahan saat mengambil data promosi.');
            closeEditPromoModal();
        }
    }

    // --- Fungsi Modal Hapus Poster ---
    function closeDeletePosterModal() {
        console.log("CLOSE DELETE POSTER: Menutup modal hapus poster...");
        const modal = document.getElementById('deletePosterModalOverlay');
        if(modal) modal.classList.remove('show');
    }

    function openDeletePosterModal(id, title) {
        console.log("OPEN DELETE POSTER: Tombol hapus poster diklik.");
        if (!id || !title) {
            console.error("OPEN DELETE POSTER ERROR: ID atau Judul tidak valid.");
            return;
        }
        console.log("OPEN DELETE POSTER: Membuka modal untuk ID:", id, "Judul:", title);

        document.getElementById('delete_poster_id_target').value = id;
        document.getElementById('delete_poster_judul_target').textContent = title;

        const modal = document.getElementById('deletePosterModalOverlay');
        if(modal) {
            modal.classList.add('show');
            console.log("OPEN DELETE POSTER: Sukses. Modal hapus seharusnya terlihat.");
        } else {
            console.error("OPEN DELETE POSTER ERROR: Element 'deletePosterModalOverlay' tidak ditemukan.");
        }
    }

    // Function to switch between poster and promo forms
    function switchTab(tabName) {
        // Hide all forms
        document.getElementById('poster-form').style.display = 'none';
        document.getElementById('promo-form').style.display = 'none';

        // Remove active class from all buttons
        document.getElementById('poster-tab-btn').classList.remove('active');
        document.getElementById('promo-tab-btn').classList.remove('active');

        // Show selected form and activate button
        if (tabName === 'promo') {
            document.getElementById('promo-form').style.display = 'block';
            document.getElementById('promo-tab-btn').classList.add('active');
        } else {
            document.getElementById('poster-form').style.display = 'block';
            document.getElementById('poster-tab-btn').classList.add('active');
        }
    }

    // Function to preview image before upload
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }

            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }

    // Add event listeners to file inputs for image preview
    document.addEventListener('DOMContentLoaded', function() {
        // For main form
        const posterGambarInput = document.getElementById('gambar_poster');
        if (posterGambarInput) {
            posterGambarInput.addEventListener('change', function() {
                previewImage(this, 'gambar_poster_preview');
            });
        }

        const modalGambarInput = document.getElementById('modal_gambar');
        if (modalGambarInput) {
            modalGambarInput.addEventListener('change', function() {
                previewImage(this, 'gambar_preview');
            });
        }
    });

    // --- Fungsi Modal Edit (Unified) ---
    function closeEditModal() {
        console.log("CLOSE EDIT: Menutup modal edit...");
        document.getElementById('editModalOverlay').classList.remove('show');
    }

    async function openEditModal(id, itemType) {
        console.log("OPEN EDIT: Tombol edit diklik untuk type:", itemType, "dengan ID:", id);
        if (!id) {
            console.error("OPEN EDIT ERROR: ID tidak valid (null atau 0).");
            return;
        }

        const modalTitle = document.getElementById('editModalTitle');
        const modal = document.getElementById('editModalOverlay');

        // Cek jika elemen ada sebelum menggunakannya
        if (modalTitle) {
            modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Memuat Data...`;
        } else {
            console.error("OPEN EDIT ERROR: Element 'editModalTitle' tidak ditemukan.");
            return; // Hentikan jika HTML modal tidak ada
        }

        if (modal) {
            modal.classList.add('show');
            console.log("OPEN EDIT: Menjalankan 'modal.classList.add('show')'.");
        } else {
            console.error("OPEN EDIT ERROR: Element 'editModalOverlay' tidak ditemukan.");
            return; // Hentikan jika HTML modal tidak ada
        }

        // Show appropriate fields based on item type
        if (itemType === 'promo' || itemType === 'promosi') {
            document.getElementById('poster_fields').style.display = 'none';
            document.getElementById('promo_fields').style.display = 'block';
            document.getElementById('editAction').value = 'edit_promosi';
        } else {
            document.getElementById('poster_fields').style.display = 'block';
            document.getElementById('promo_fields').style.display = 'none';
            document.getElementById('editAction').value = 'edit_poster';
        }

        // Set form action to current page
        document.getElementById('editForm').action = window.location.pathname + window.location.search;

        try {
            // Fetch data from unified AJAX endpoint with item type parameter
            const fetchUrl = `/simaksi/admin/poster/poster_ajax.php?action=fetch_json&type=${itemType}&id=${id}&_cache=${new Date().getTime()}`;
            console.log("OPEN EDIT: Mengambil data dari URL:", fetchUrl);

            const response = await fetch(fetchUrl);

            console.log("OPEN EDIT: Response status dari server:", response.status);
            if (!response.ok) {
                console.error("OPEN EDIT FETCH ERROR: Network response was not ok.", response);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                console.error("OPEN EDIT JSON PARSE ERROR:", jsonError);
                console.error("Server Response (yang bukan JSON):", responseText);
                throw new Error("Respons bukan JSON yang valid.");
            }

            console.log("OPEN EDIT: Data JSON diterima:", data);

            if (!data || data.error) {
                alert('Gagal memuat data: ' + (data.error || 'Data tidak ditemukan.'));
                closeEditModal();
                return;
            }

            // === PENGISIAN FORM BERDASARKAN TIPE ITEM ===
            if (itemType === 'promo' || itemType === 'promosi') {
                // Fill promo data
                document.getElementById('modal_id_promosi').value = data.id_promosi || '';
                document.getElementById('modal_judul').value = data.nama_promosi || '';
                document.getElementById('modal_deskripsi').value = data.deskripsi_promosi || '';
                document.getElementById('modal_tipe').value = data.tipe_promosi || '';
                document.getElementById('modal_nilai').value = data.nilai_promosi || 0;
                document.getElementById('modal_kode').value = data.kode_promo || '';

                // Format date for datetime-local input (YYYY-MM-DDTHH:MM)
                if (data.tanggal_mulai) {
                    const startDate = new Date(data.tanggal_mulai);
                    document.getElementById('modal_tanggal_mulai').value = startDate.toISOString().slice(0, 16);
                }
                if (data.tanggal_akhir) {
                    const endDate = new Date(data.tanggal_akhir);
                    document.getElementById('modal_tanggal_akhir').value = endDate.toISOString().slice(0, 16);
                }

                document.getElementById('modal_min_pendaki').value = data.kondisi_min_pendaki || 1;
                document.getElementById('modal_max_pendaki').value = data.kondisi_max_pendaki || '';
                document.getElementById('modal_status').checked = data.is_aktif === 1 || data.is_aktif === true;

                modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Edit Promosi: ${data.nama_promosi || 'ID: ' + id}`;
            } else {
                // Fill poster data
                document.getElementById('modal_id_promosi').value = data.id_promosi_poster || '';
                document.getElementById('modal_judul').value = data.judul_poster || '';
                document.getElementById('modal_deskripsi').value = data.deskripsi_promosi || '';
                document.getElementById('modal_urutan').value = data.urutan_tampil || 0;
                document.getElementById('modal_current_file_name').value = data.url_gambar || '';
                document.getElementById('modal_status').checked = data.status_promosi === 1;

                const imgElement = document.getElementById('modal_current_image');
                const defaultImg = 'https://placehold.co/64x64/CCCCCC/000000?text=IMG';

                if (data.current_image_url) {
                    imgElement.src = data.current_image_url;
                } else {
                    imgElement.src = defaultImg;
                }
                imgElement.onerror = function() { this.src = defaultImg; };

                modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Edit Poster: ${data.judul_poster || 'ID: ' + id}`;
            }

            console.log("OPEN EDIT: Sukses. Modal seharusnya sudah terisi penuh.");

        } catch (error) {
            console.error('OPEN EDIT CATCH ERROR: Terjadi kesalahan besar saat fetch/proses data:', error);
            alert('Terjadi kesalahan saat mengambil data.');
            closeEditModal();
        }
    }

    // --- Fungsi Modal Hapus (Unified) ---
    function closeDeleteModal() {
        console.log("CLOSE DELETE: Menutup modal hapus...");
        const modal = document.getElementById('deleteModalOverlay');
        if(modal) modal.classList.remove('show');
    }

    function openDeleteModal(id, title, itemType) {
        console.log("OPEN DELETE: Tombol hapus diklik untuk type:", itemType, "dengan ID:", id, "dan Judul:", title);
        if (!id || !title) {
            console.error("OPEN DELETE ERROR: ID atau Judul tidak valid.");
            return;
        }

        // Set the hidden fields
        document.getElementById('delete_id_target').value = id;
        document.getElementById('delete_judul_target').textContent = title;
        document.getElementById('delete_item_type').value = itemType;

        // Set the action based on item type
        const actionType = itemType === 'promo' ? 'delete_promosi' : 'delete_poster';
        document.getElementById('delete_action').value = actionType;

        // Update the type text in the modal
        const typeText = itemType === 'promo' ? 'promosi' : 'poster';
        document.getElementById('item_type_delete').textContent = typeText;

        // Set form action to current page
        document.getElementById('deleteForm').action = window.location.pathname + window.location.search;

        const modal = document.getElementById('deleteModalOverlay');
        if(modal) {
            modal.classList.add('show');
            console.log("OPEN DELETE: Sukses. Modal hapus seharusnya terlihat.");
        } else {
            console.error("OPEN DELETE ERROR: Element 'deleteModalOverlay' tidak ditemukan.");
        }
    }
</script>

<!-- ============================================== -->
<!-- PERBAIKAN BARU: Blok JavaScript untuk Redirect -->
<!-- ============================================== -->
<?php
if (isset($redirect_url)) {
    echo "<script type='text/javascript'>
            console.log('Redirecting via JavaScript to: " . $redirect_url . "');
            window.location.href = '" . $redirect_url . "';
          </script>";
}
?>

<!-- ============================================== -->
<!-- SCRIPT ICONIFY (DIMUAT TERAKHIR) -->
<!-- ============================================== -->
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>