<?php
// FILE: simaksi/admin/poster/poster.php (Content Snippet yang Di-Include oleh index.php)

// 1. Panggil file koneksi database Supabase
require_once __DIR__ . '/../../config/supabase.php'; 

// --- KONFIGURASI SERVICE ROLE KEY (Hanya digunakan di file ini) ---
// Service Role Key: Digunakan untuk mengesampingkan RLS saat fetch data
$serviceRoleKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE'; 
// --- AKHIR KONFIGURASI KEY ---

// Asumsi jalur untuk upload lokal (Hanya untuk keperluan *path* gambar tampil)
$upload_dir = '../../uploads/poster/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ----------------------------------------------------------------------
// FUNGSI CRUD Supabase (Menggunakan Fungsi dari supabase.php)
// ----------------------------------------------------------------------

// Fetch semua promosi poster
function fetch_promosi_list() {
    global $serviceRoleKey;

    // KOREKSI KOLOM SELECT SESUAI DATABASE ANDA
    $filter = urlencode("select=id_promosi_poster,judul_promosi,url_tautan,is_aktif,urutan,url_gambar,deskripsi_poster&order=urutan.asc,id_promosi_poster.desc");
    
    // Override key dengan Service Role Key untuk melewati RLS
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', "promosi_poster?{$filter}", null, $headers);
    
    if (isset($response['error'])) {
        $errorMessage = $response['error']['message'] ?? 'Unknown Error';
        return ['error' => true, 'message' => $errorMessage];
    }
    
    return $response; 
}

// Fetch promosi poster berdasarkan ID
function fetch_promosi_by_id($id) {
    global $serviceRoleKey;
    $filter = urlencode("id_promosi_poster=eq.{$id}&select=id_promosi_poster,judul_promosi,deskripsi_poster,url_tautan,is_aktif,urutan,url_gambar");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', "promosi_poster?{$filter}", null, $headers); 
    
    if (isset($response['error']) || empty($response) || !is_array($response)) {
        return null;
    }
    
    return $response[0] ?? null; // Supabase mengembalikan array of one, ambil elemen pertama
}

// Insert promosi poster
function insert_promosi($data) {
    global $serviceRoleKey;
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('POST', 'promosi_poster', $data, $headers);
}

// Update promosi poster
function update_promosi($id, $data) {
    global $serviceRoleKey;
    $endpoint = "promosi_poster?id_promosi_poster=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('PATCH', $endpoint, $data, $headers);
}

// Delete promosi poster
function delete_promosi($id) {
    global $serviceRoleKey;
    $endpoint = "promosi_poster?id_promosi_poster=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('DELETE', $endpoint, null, $headers);
}


// ----------------------------------------------------------------------
// LOGIKA BACK-END (DB ASLI Supabase)
// ----------------------------------------------------------------------
$message = '';
// FIX: Pastikan tab disetel dengan benar, default ke 'daftar'
$active_tab = isset($_GET['tab']) && in_array($_GET['tab'], ['tambah', 'daftar']) ? $_GET['tab'] : 'daftar';

// --- Handle AJAX Fetch for Modal ---
if (isset($_GET['action']) && $_GET['action'] === 'fetch_json' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_raw = fetch_promosi_by_id($id);
    
    if ($data_raw) {
        // Mapping kolom database ke nama yang digunakan di form JS
        $data = [
            'id_promosi_poster' => $data_raw['id_promosi_poster'],
            'judul_promosi' => $data_raw['judul_promosi'],
            'deskripsi_promosi' => $data_raw['deskripsi_poster'], 
            'link_tautan' => $data_raw['url_tautan'],
            'urutan_tampil' => $data_raw['urutan'],
            'url_gambar' => $data_raw['url_gambar'],
            'status_promosi' => ($data_raw['is_aktif'] === true || $data_raw['is_aktif'] === 't') ? 1 : 0,
            'current_image_url' => $data_raw['url_gambar'],
        ];
    } else {
        $data = null;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// --- Handle Form Submission (Insert/Update/Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action'] ?? null;
    $id_promosi = isset($_POST['id_promosi']) ? (int)$_POST['id_promosi'] : null;

    // --- LOGIKA INSERT & UPDATE ---
    if (in_array($action_type, ['tambah_promosi', 'edit_promosi'])) {
        // Data yang akan dikirim ke Supabase harus menggunakan nama kolom DB yang benar
        $data = [
            'judul_promosi' => trim(htmlspecialchars($_POST['judul_promosi'] ?? '')), 
            'deskripsi_poster' => trim(htmlspecialchars($_POST['deskripsi_promosi'] ?? '')), // DB: deskripsi_poster
            'url_tautan' => trim(htmlspecialchars($_POST['url_tautan'] ?? '')), // DB: url_tautan
            'urutan' => (int)($_POST['urutan_tampil'] ?? 0), // DB: urutan
            'is_aktif' => isset($_POST['status_promosi']) ? true : false, // DB: is_aktif
        ];

        $nama_file_gambar = $_POST['current_file_name'] ?? ''; 
        $upload_ok = true;
        
        // Logika File Upload (SIMULASI/LOKAL)
        if (isset($_FILES['gambar_promosi']) && $_FILES['gambar_promosi']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar_promosi']['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES['gambar_promosi']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                $nama_file_gambar = uniqid('poster_') . '.' . $file_ext; 
                $target_file = $upload_dir . $nama_file_gambar;
                // PENTING: Jika Anda menggunakan Supabase Storage, GANTI move_uploaded_file ini dengan API call ke Storage
                if (!move_uploaded_file($file_tmp, $target_file)) {
                    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Gagal memindahkan file lokal.</div>";
                    $upload_ok = false;
                }
            } else {
                 $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Format file tidak didukung.</div>";
                 $upload_ok = false;
            }
        }

        $data['url_gambar'] = $nama_file_gambar; // DB: url_gambar
        
        if ($upload_ok) {
            if ($action_type === 'tambah_promosi') {
                if (empty($data['url_gambar'])) {
                    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Gambar promosi harus diunggah.</div>";
                } else {
                    $response = insert_promosi($data);
                    if (isset($response['error'])) {
                        $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase INSERT: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                    } else {
                        header("Location: poster.php?tab=daftar&msg=added");
                        exit;
                    }
                }
            } elseif ($action_type === 'edit_promosi' && $id_promosi) {
                if (empty($nama_file_gambar) && empty($_FILES['gambar_promosi']['name'])) {
                    unset($data['url_gambar']); 
                }
                
                $response = update_promosi($id_promosi, $data);
                
                if (isset($response['error'])) {
                    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase UPDATE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                } else {
                    header("Location: poster.php?tab=daftar&msg=updated");
                    exit;
                }
            }
        }
    }
    
    // --- LOGIKA DELETE ---
    if ($action_type === 'delete_promosi' && isset($_POST['id_promosi_delete'])) {
        $id_delete = (int)$_POST['id_promosi_delete'];
        
        $promosi_to_delete = fetch_promosi_by_id($id_delete);
        $file_to_delete = $promosi_to_delete['url_gambar'] ?? null; 

        $response = delete_promosi($id_delete);
        
        if (isset($response['error'])) {
            $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase DELETE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
        } else {
            // Hapus file lokal (jika ada) - GANTI JIKA MENGGUNAKAN SUPABASE STORAGE
            if ($file_to_delete && file_exists($upload_dir . $file_to_delete) && strpos($file_to_delete, '://') === false) {
                unlink($upload_dir . $file_to_delete);
            }
            header("Location: poster.php?tab=daftar&msg=deleted");
            exit;
        }
    }
    
    // FIX PENTING: Jika terjadi error pada POST, kita harus memastikan tab tetap di 'tambah'
    if ($action_type === 'tambah_promosi' || $action_type === 'edit_promosi') {
        $active_tab = 'tambah';
    }
}


// 2. Fetch Promosi List
$promosi_raw_list = fetch_promosi_list();
$promosi_list = [];

if (isset($promosi_raw_list['error'])) {
    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase FETCH: " . ($promosi_raw_list['message'] ?? 'Terjadi kesalahan saat mengambil data.') . "</div>";
    $promosi_raw_list = [];
}

$default_keys = [
    'id_promosi_poster' => null,
    'judul_promosi' => 'N/A', 
    'url_tautan' => '', 
    'is_aktif' => false, 
    'urutan' => 0, 
    'url_gambar' => null,
    'deskripsi_poster' => '', 
];


foreach ($promosi_raw_list as $row) {
    $cleaned_row = array_filter((array)$row, function($value) { 
        return $value !== null; 
    });

    $promosi_data = array_merge($default_keys, $cleaned_row);
    $promosi = [
        'id_promosi_poster' => $promosi_data['id_promosi_poster'],
        'judul_promosi' => $promosi_data['judul_promosi'],
        'link_tautan' => $promosi_data['url_tautan'],
        'status_promosi_db' => $promosi_data['is_aktif'], 
        'urutan_tampil' => $promosi_data['urutan'],
        'deskripsi_promosi' => $promosi_data['deskripsi_poster'],
        'url_gambar_db' => $promosi_data['url_gambar'],
    ];

    $gambar_file = $promosi['url_gambar_db'];
    $is_supabase_url = $gambar_file && strpos($gambar_file, '://') !== false;

    if ($is_supabase_url) {
        $promosi['gambar_url'] = $gambar_file;
    } else {
        $promosi['gambar_url'] = '../../uploads/poster/' . ($gambar_file ?? 'placeholder.png'); 
    }

    $promosi_list[] = $promosi;
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Promosi baru berhasil ditambahkan!</div>";
    } elseif ($_GET['msg'] === 'updated') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Promosi berhasil diubah!</div>";
    } elseif ($_GET['msg'] === 'deleted') {
        $message = "<div style='padding:12px; background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; margin-bottom:16px;'>Promosi berhasil dihapus.</div>";
    }
}

// ----------------------------------------------------------------------
?>
<style>
    /* ---------------------------------------------------------------------- */
    /* CSS MANDIRI UNTUK FILE INI */
    /* ---------------------------------------------------------------------- */
    /* Font dan Warna Dasar */
    .poster-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    .card { background-color: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06); }
    .card-shadow { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    .mb-6 { margin-bottom: 24px; }
    .p-6 { padding: 24px; }
    .flex { display: flex; }
    .items-center { align-items: center; }
    
    /* Tab Styling */
    .tab-header { border-bottom: 1px solid #e5e7eb; }
    .tab-item {
        padding: 16px;
        color: #6b7280;
        text-decoration: none;
        transition: all 0.3s ease-in-out;
        border-bottom: 3px solid transparent;
        cursor: pointer;
    }
    .tab-item:hover:not(.tab-active) {
        background-color: #f9fafb;
        color: #10B981;
    }
    .tab-active {
        border-bottom: 3px solid #10B981;
        color: #10B981;
        font-weight: 600;
    }

    /* Tombol Dasar */
    .btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border: 1px solid transparent;
        border-radius: 9999px; 
        font-size: 14px;
        font-weight: 500;
        line-height: 20px;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }
    .btn-green { background-color: #10B981; color: white; }
    .btn-red { background-color: #dc2626; color: white; }
    .btn-yellow { background-color: #fde68a; color: #92400e; }
    
    /* Tabel Styling */
    .data-table { width: 100%; border-collapse: collapse; }
    .table-head { background-color: #f9fafb; }
    .table-head th { padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
    .data-row td { padding: 16px 24px; font-size: 14px; color: #1f2937; border-bottom: 1px solid #f3f4f6; }
    .data-row:hover { background-color: #f9fafb; transition: background-color 0.2s; }
    
    .status-active { background-color: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
    .status-inactive { background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600; }

    /* Form Styling */
    .form-group { margin-bottom: 16px; }
    .form-label { display: flex; align-items: center; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px; }
    .form-input, .form-textarea { width: 100%; padding: 8px 16px; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
    .grid-2-cols { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
    .flex-end { display: flex; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e5e7eb; margin-top: 16px; }

    /* Modal Styling */
    .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); display: none; justify-content: center; align-items: center; z-index: 1000; }
    .modal-content { animation: slideInDown 0.4s; }
    /* ---------------------------------------------------------------------- */
</style>

<div class="poster-container">
    <!-- Area Tab Header -->
    <div class="card mb-6">
        <div class="flex tab-header" style="border-radius: 12px 12px 0 0;">
            <!-- Tab Daftar Promosi -->
            <a href="index.php?page=poster&tab=daftar" class="tab-item <?php echo $active_tab === 'daftar' ? 'tab-active' : ''; ?>">
                <iconify-icon icon="mdi:view-list" style="width: 20px; height: 20px; margin-right: 8px;"></iconify-icon>
                <span>Daftar Promosi</span>
            </a>
            
            <!-- Tab Tambah Promosi Baru -->
            <a href="index.php?page=poster&tab=tambah" class="tab-item <?php echo $active_tab === 'tambah' ? 'tab-active' : ''; ?>">
                <iconify-icon icon="mdi:plus-circle" style="width: 20px; height: 20px; margin-right: 8px;"></iconify-icon>
                <span>Tambah Promosi Baru</span>
            </a>
        </div>
    </div>

    <?php echo $message; // Menampilkan pesan sukses/error ?>

    <!-- Konten Tab -->
    <div class="p-6 card card-shadow">
        <?php if ($active_tab === 'daftar'): ?>
            <!-- ============================================== -->
            <!-- Tampilan Daftar Promosi -->
            <!-- ============================================== -->
            <h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px;" class="flex items-center">
                <iconify-icon icon="mdi:monitor-dashboard" style="width: 24px; height: 24px; margin-right: 8px; color: #059669;"></iconify-icon>
                Daftar Promosi Poster (Total: <?php echo count($promosi_list); ?>)
            </h2>

            <div class="table-container">
                <table class="data-table">
                    <thead class="table-head">
                        <tr>
                            <th>GAMBAR</th>
                            <th>JUDUL</th>
                            <th>TAUTAN</th>
                            <th>STATUS</th>
                            <th>URUTAN</th>
                            <th style="text-align: center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($promosi_list)): ?>
                            <tr>
                                <td colspan="6" style="padding: 16px; text-align: center; color: #6b7280;">Tidak ada data promosi. Silakan tambahkan promosi baru.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($promosi_list as $promosi): ?>
                            <tr class="data-row">
                                <td>
                                    <img 
                                        src="<?php echo $promosi['gambar_url']; ?>" 
                                        alt="Poster" 
                                        style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;" 
                                        onerror="this.onerror=null;this.src='https://placehold.co/50x50/CCCCCC/000000?text=IMG';">
                                </td>
                                <td style="font-weight: 500; color: #1f2937;">
                                    <?php 
                                        // FIX: Judul diambil dari array $promosi yang sudah didefinisikan secara defensif
                                        echo htmlspecialchars($promosi['judul_promosi'] ?? 'N/A'); 
                                    ?>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php 
                                        $link = $promosi['link_tautan'] ?? '';
                                        echo !empty($link) ? '<a href="' . htmlspecialchars($link) . '" target="_blank" style="color:#3b82f6; text-decoration: none;">Lihat</a>' : '-'; 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        // **FIX STATUS PENTING**
                                        // Status diambil dari array $promosi
                                        $status = ($promosi['status_promosi_db'] === true || $promosi['status_promosi_db'] === 't') ? 1 : 0; 
                                    ?>
                                    <span class="<?php echo $status ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $status ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php 
                                        // Urutan diambil dari array $promosi
                                        echo $promosi['urutan_tampil'] ?? 0; 
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <!-- Tombol Edit diubah menjadi pemicu Modal -->
                                    <button onclick="openEditModal(<?php echo $promosi['id_promosi_poster'] ?? 'null'; ?>)" class="btn btn-yellow" style="padding: 4px 12px; font-size: 12px; margin-right: 8px;">
                                        <iconify-icon icon="mdi:pencil-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Edit
                                    </button>
                                    <!-- Tombol Hapus diubah menjadi pemicu Modal Konfirmasi -->
                                    <button onclick="openDeleteModal(<?php echo $promosi['id_promosi_poster'] ?? 'null'; ?>, '<?php echo htmlspecialchars($promosi['judul_promosi'] ?? 'Promosi ini'); ?>')" class="btn btn-red" style="padding: 4px 12px; font-size: 12px;">
                                        <iconify-icon icon="mdi:trash-can-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        
        <?php else: 
            // $active_tab === 'tambah'
        ?>
            <!-- ============================================== -->
            <!-- Tampilan Tambah Promosi BARU (Di Tab) -->
            <!-- ============================================== -->
            <h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px;" class="flex items-center">
                <iconify-icon icon="mdi:plus-circle" style="width: 24px; height: 24px; margin-right: 8px; color: #059669;"></iconify-icon>
                Tambah Promosi Baru
            </h2>

            <form method="POST" enctype="multipart/form-data" action="poster.php?tab=tambah">
                <input type="hidden" name="action" value="tambah_promosi">

                <div class="grid-2-cols">
                    <!-- Kolom Kiri -->
                    <div>
                        <div class="form-group">
                            <label for="judul_promosi" class="form-label"><iconify-icon icon="mdi:format-title" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Judul Promosi</label>
                            <input type="text" id="judul_promosi" name="judul_promosi" required value="" class="form-input" placeholder="Masukkan judul promosi">
                        </div>

                        <div class="form-group">
                            <label for="deskripsi_promosi" class="form-label"><iconify-icon icon="mdi:align-left" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Deskripsi Promosi</label>
                            <textarea id="deskripsi_promosi" name="deskripsi_promosi" rows="3" class="form-textarea" placeholder="Deskripsi singkat tentang promosi ini" style="resize: vertical;"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="gambar_promosi" class="form-label"><iconify-icon icon="mdi:image-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Upload Gambar Promosi</label>
                            <input type="file" id="gambar_promosi" name="gambar_promosi" required style="display: block; width: 100%; padding-top: 8px; padding-bottom: 8px;" class="form-input">
                            <p style="margin-top: 4px; font-size: 12px; color: #6b7280;">Format: JPG, PNG, GIF. Maksimal 5MB.</p>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div>
                        <div class="form-group">
                            <label for="url_tautan" class="form-label"><iconify-icon icon="mdi:link" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> URL Tautan (Opsional)</label>
                            <input type="url" id="url_tautan" name="url_tautan" value="" class="form-input" placeholder="https://example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="urutan_tampil" class="form-label"><iconify-icon icon="mdi:sort-numeric-ascending" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Urutan Tampil</label>
                            <input type="number" id="urutan_tampil" name="urutan_tampil" required min="0" value="0" class="form-input">
                        </div>

                        <div class="form-group" style="padding-top: 8px;">
                            <div class="flex items-center">
                                <input id="status_promosi" name="status_promosi" type="checkbox" value="1" checked
                                    style="height: 16px; width: 16px; color: #10B981; border: 1px solid #d1d5db; border-radius: 4px; margin-right: 12px;">
                                <label for="status_promosi" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Poster akan ditampilkan)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex-end">
                    <button type="button" onclick="alert('Fungsi Pratinjau belum diimplementasikan.')"
                        class="btn btn-blue" style="margin-right: 12px;">
                        <iconify-icon icon="mdi:eye-outline" style="width: 16px; height: 16px; margin-right: 8px;"></iconify-icon> Pratinjau
                    </button>

                    <button type="submit" class="btn btn-green btn-pulse">
                        <iconify-icon icon="mdi:content-save-outline" style="width: 16px; height: 16px; margin-right: 8px;"></iconify-icon> Simpan Promosi
                    </button>
                </div>
            </form>

        <?php endif; ?>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT (BARU) -->
<!-- ============================================== -->
<div id="editModal" class="modal" onclick="if(event.target.id === 'editModal') closeEditModal();" style="display: none;">
    <div class="modal-content">
        <button type="button" class="modal-close-btn" onclick="closeEditModal()"><iconify-icon icon="mdi:close"></iconify-icon></button>
        <h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px;" class="flex items-center">
            <iconify-icon icon="mdi:pencil-outline" style="width: 24px; height: 24px; margin-right: 8px; color: #f9d854;"></iconify-icon>
            Edit Promosi Poster
        </h2>

        <form method="POST" enctype="multipart/form-data" action="poster.php?tab=daftar" id="editForm">
            <input type="hidden" name="action" value="edit_promosi">
            <input type="hidden" name="id_promosi" id="modal_id_promosi">
            <input type="hidden" name="current_file_name" id="modal_current_file_name">

            <div class="grid-2-cols">
                <!-- Kolom Kiri -->
                <div>
                    <div class="form-group">
                        <label for="modal_judul_promosi" class="form-label"><iconify-icon icon="mdi:format-title" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Judul Promosi</label>
                        <input type="text" id="modal_judul_promosi" name="judul_promosi" required class="form-input" placeholder="Masukkan judul promosi">
                    </div>

                    <div class="form-group">
                        <label for="modal_deskripsi_promosi" class="form-label"><iconify-icon icon="mdi:align-left" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Deskripsi Promosi</label>
                        <textarea id="modal_deskripsi_promosi" name="deskripsi_promosi" rows="3" class="form-textarea" style="resize: vertical;"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="modal_gambar_promosi" class="form-label"><iconify-icon icon="mdi:image-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Upload Gambar Promosi</label>
                        <input type="file" id="modal_gambar_promosi" name="gambar_promosi" style="display: block; width: 100%; padding-top: 8px; padding-bottom: 8px;" class="form-input">
                        <p style="margin-top: 4px; font-size: 12px; color: #6b7280;">Kosongkan jika tidak ingin mengganti gambar.</p>
                        
                        <div id="modal_current_image_container" style="margin-top: 8px; font-size: 14px; color: #4b5563;" class="flex items-center">
                            <span style="margin-right: 8px;">Gambar Saat Ini:</span>
                            <img id="modal_current_image" alt="Current Poster" style="width: 64px; height: 64px; object-fit: cover; border-radius: 6px; border: 1px solid #d1d5db;">
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div>
                    <div class="form-group">
                        <label for="modal_url_tautan" class="form-label"><iconify-icon icon="mdi:link" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> URL Tautan (Opsional)</label>
                        <input type="url" id="modal_url_tautan" name="url_tautan" class="form-input" placeholder="https://example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal_urutan_tampil" class="form-label"><iconify-icon icon="mdi:sort-numeric-ascending" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Urutan Tampil</label>
                        <input type="number" id="modal_urutan_tampil" name="urutan_tampil" required min="0" class="form-input">
                    </div>

                    <div class="form-group" style="padding-top: 8px;">
                        <div class="flex items-center">
                            <input id="modal_status_promosi" name="status_promosi" type="checkbox" value="1"
                                style="height: 16px; width: 16px; color: #10B981; border: 1px solid #d1d5db; border-radius: 4px; margin-right: 12px;">
                            <label for="modal_status_promosi" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Poster akan ditampilkan)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-end">
                <button type="submit" class="btn btn-green">
                    <iconify-icon icon="mdi:content-save-outline" style="width: 16px; height: 16px; margin-right: 8px;"></iconify-icon> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL HAPUS KONFIRMASI (BARU) -->
<!-- ============================================== -->
<div id="deleteModal" class="modal modal-confirm" onclick="if(event.target.id === 'deleteModal') closeDeleteModal();" style="display: none;">
    <div class="modal-content">
        <iconify-icon icon="mdi:alert-decagram" style="width: 64px; height: 64px; color: #f59e0b; margin-bottom: 16px;"></iconify-icon>
        <h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 8px;">Konfirmasi Penghapusan</h3>
        <p style="color: #6b7280; margin-bottom: 24px;">Apakah Anda yakin ingin menghapus promosi **<span id="delete_judul_target" style="font-weight: 600;"></span>**? Tindakan ini tidak dapat dibatalkan.</p>

        <form method="POST" style="display:inline;" id="deleteForm">
            <input type="hidden" name="action" value="delete_promosi">
            <input type="hidden" name="id_promosi_delete" id="delete_id_target">
            
            <button type="button" class="btn btn-gray" onclick="closeDeleteModal()" style="background-color: #e5e7eb; color: #374151; margin-right: 12px;">Batal</button>
            <button type="submit" class="btn btn-red">
                <iconify-icon icon="mdi:trash-can-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Hapus Permanen
            </button>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- SCRIPT JAVASCRIPT -->
<!-- ============================================== -->
<script>
    // --- Fungsi Modal Edit ---
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    async function openEditModal(id) {
        if (!id) return;

        const modalTitle = document.querySelector('#editModal h2');
        const modal = document.getElementById('editModal');
        
        modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline" style="width: 24px; height: 24px; margin-right: 8px; color: #f9d854;"></iconify-icon> Memuat Data...`;
        modal.style.display = 'flex';

        try {
            // Fetch data dari endpoint JSON
            const response = await fetch(`poster.php?action=fetch_json&id=${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();

            if (!data || data.error) { 
                alert('Gagal memuat data promosi. Data mungkin tidak lengkap. Cek log server.');
                closeEditModal();
                return;
            }
            
            // Isi Form Modal
            document.getElementById('modal_id_promosi').value = data.id_promosi_poster || '';
            document.getElementById('modal_judul_promosi').value = data.judul_promosi || '';
            document.getElementById('modal_deskripsi_promosi').value = data.deskripsi_promosi || ''; // Menggunakan deskripsi_promosi (display)
            document.getElementById('modal_url_tautan').value = data.link_tautan || ''; // Menggunakan link_tautan (display)
            document.getElementById('modal_urutan_tampil').value = data.urutan_tampil || 0; // Menggunakan urutan_tampil (display)
            document.getElementById('modal_current_file_name').value = data.url_gambar || '';
            
            // Set Status Promosi (Checkbox)
            const statusCheckbox = document.getElementById('modal_status_promosi');
            statusCheckbox.checked = data.status_promosi === 1; 

            // Set Gambar Saat Ini
            const imgElement = document.getElementById('modal_current_image');
            const defaultImg = 'https://placehold.co/64x64/CCCCCC/000000?text=IMG';
            
            if (data.current_image_url && data.current_image_url.includes('://')) {
                imgElement.src = data.current_image_url;
            } else if (data.url_gambar) {
                imgElement.src = `../../uploads/poster/${data.url_gambar}`; 
            } else {
                imgElement.src = defaultImg;
            }
            
            imgElement.onerror = function() { this.src = defaultImg; };

            // Perbarui Judul Modal
            modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline" style="width: 24px; height: 24px; margin-right: 8px; color: #f9d854;"></iconify-icon> Edit Promosi Poster: ${data.judul_promosi || 'ID: ' + id}`;
            
        } catch (error) {
            console.error('Error fetching data for modal:', error);
            alert('Terjadi kesalahan saat mengambil data.');
            closeEditModal();
        }
    }

    // --- Fungsi Modal Hapus ---
    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    function openDeleteModal(id, title) {
        if (!id || !title) return;

        document.getElementById('delete_id_target').value = id;
        document.getElementById('delete_judul_target').textContent = title;
        document.getElementById('deleteModal').style.display = 'flex';
    }
</script>