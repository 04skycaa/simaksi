<?php
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
function insert_promosi($data) {
    global $serviceRoleKey;
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('POST', 'promosi_poster', $data, $headers);
}

// Update promosi poster
function update_promosi($id, $data) {
    global $serviceRoleKey;
    // Menggunakan 'id_poster'
    $endpoint = "promosi_poster?id_poster=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('PATCH', $endpoint, $data, $headers);
}

// Delete promosi poster
function delete_promosi($id) {
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

// --- Logika 'Handle AJAX Fetch' dipindahkan ke poster_ajax.php ---


// --- Handle Form Submission (Insert/Update/Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action'] ?? null;
    $id_promosi = isset($_POST['id_promosi']) ? (int)$_POST['id_promosi'] : null;

    // --- LOGIKA INSERT & UPDATE ---
    if (in_array($action_type, ['tambah_promosi', 'edit_promosi'])) {
        $data = [
            'judul_poster' => trim(htmlspecialchars($_POST['judul_promosi'] ?? '')),
            'deskripsi_poster' => trim(htmlspecialchars($_POST['deskripsi_promosi'] ?? '')),
            'url_tautan' => trim(htmlspecialchars($_POST['url_tautan'] ?? '')),
            'urutan' => (int)($_POST['urutan_tampil'] ?? 0),
            'is_aktif' => isset($_POST['status_promosi']) ? true : false,
        ];

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

        $data['url_gambar'] = $nama_file_gambar;
        
        if ($upload_ok) {
            if ($action_type === 'tambah_promosi') {
                if (empty($data['url_gambar'])) {
                    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Gambar promosi harus diunggah.</div>";
                } else {
                    $response = insert_promosi($data);
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
                $response = update_promosi($id_promosi, $data);
                if (isset($response['error'])) {
                    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase UPDATE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                } else {
                    $redirect_url = "index.php?page=poster&tab=daftar&msg=updated";
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
            if ($file_to_delete && file_exists($upload_dir . $file_to_delete) && strpos($file_to_delete, '://') === false) {
                unlink($upload_dir . $file_to_delete);
            }
            // PERBAIKAN: Ganti header() PHP dengan variabel untuk JavaScript
            $redirect_url = "index.php?page=poster&tab=daftar&msg=deleted";
            // header("Location: index.php?page=poster&tab=daftar&msg=deleted");
            // exit;
        }
    }
    
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
    'id_poster' => null,
    'judul_poster' => 'N/A',
    'url_tautan' => '',
    'is_aktif' => false,
    'urutan' => 0,
    'url_gambar' => null,
    'deskripsi_poster' => '',
];
foreach ($promosi_raw_list as $row) {
    $cleaned_row = array_filter((array)$row, function($value) { return $value !== null; });
    $promosi_data = array_merge($default_keys, $cleaned_row);
    $promosi = [
        'id_promosi_poster' => $promosi_data['id_poster'], // Baca dari 'id_poster'
        'judul_poster' => $promosi_data['judul_poster'],
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
?>


<div class="poster-container">
    <!-- Area Tab Header -->
    <div class="card mb-6">
        <div class="flex tab-header" style="border-radius: 12px 12px 0 0;">
            <a href="index.php?page=poster&tab=daftar" class="tab-item <?php echo $active_tab === 'daftar' ? 'tab-active' : ''; ?>">
                <iconify-icon icon="mdi:view-list" style="width: 20px; height: 20px; margin-right: 8px;"></iconify-icon>
                <span>Daftar Promosi</span>
            </a>
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
            <h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px;" class="flex items-center">
                <iconify-icon icon="mdi:monitor-dashboard" style="width: 24px; height: 24px; margin-right: 8px; color: #059669;"></iconify-icon>
                Daftar Promosi Poster (Total: <?php echo count($promosi_list); ?>)
            </h2>
            <div class="table-container" style="overflow-x: auto;">
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
                                    <?php echo htmlspecialchars($promosi['judul_poster'] ?? 'N/A'); ?>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php 
                                        $link = $promosi['link_tautan'] ?? '';
                                        echo !empty($link) ? '<a href="' . htmlspecialchars($link) . '" target="_blank" style="color:#3b82f6; text-decoration: none;">Lihat</a>' : '-'; 
                                    ?>
                                </td>
                                <td>
                                    <?php $status = ($promosi['status_promosi_db'] === true || $promosi['status_promosi_db'] === 't') ? 1 : 0; ?>
                                    <span class="<?php echo $status ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $status ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php echo $promosi['urutan_tampil'] ?? 0; ?>
                                </td>
                                <td style="text-align: center;">
                                    <button onclick="openEditModal(<?php echo $promosi['id_promosi_poster'] ?? 'null'; ?>)" class="btn btn-yellow" style="padding: 4px 12px; font-size: 12px; margin-right: 8px;">
                                        <iconify-icon icon="mdi:pencil-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Edit
                                    </button>
                                    <button onclick="openDeleteModal(<?php echo $promosi['id_promosi_poster'] ?? 'null'; ?>, '<?php echo htmlspecialchars($promosi['judul_poster'] ?? 'Promosi ini'); ?>')" class="btn btn-red" style="padding: 4px 12px; font-size: 12px;">
                                        <iconify-icon icon="mdi:trash-can-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        
        <?php else: // $active_tab === 'tambah' ?>
            <h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px;" class="flex items-center">
                <iconify-icon icon="mdi:plus-circle" style="width: 24px; height: 24px; margin-right: 8px; color: #059669;"></iconify-icon>
                Tambah Promosi Baru
            </h2>
            <form method="POST" enctype="multipart/form-data" action="index.php?page=poster&tab=tambah">
                <input type="hidden" name="action" value="tambah_promosi">
                <div class="grid-2-cols">
                    <!-- Kolom Kiri -->
                    <div>
                        <div class="form-group">
                            <label for="judul_promosi" class="form-label"><iconify-icon icon="mdi:format-title"></iconify-icon> Judul Promosi</label>
                            <input type="text" id="judul_promosi" name="judul_promosi" required value="" placeholder="Masukkan judul promosi">
                        </div>
                        <div class="form-group">
                            <label for="deskripsi_promosi" class="form-label"><iconify-icon icon="mdi:align-left"></iconify-icon> Deskripsi Promosi</label>
                            <textarea id="deskripsi_promosi" name="deskripsi_promosi" rows="3" placeholder="Deskripsi singkat promosi ini" style="resize: vertical;"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="gambar_promosi" class="form-label"><iconify-icon icon="mdi:image-outline"></iconify-icon> Upload Gambar Promosi</label>
                            <input type="file" id="gambar_promosi" name="gambar_promosi" required style="display: block; width: 100%; padding-top: 8px; padding-bottom: 8px;">
                            <p style="margin-top: 4px; font-size: 12px; color: #6b7280;">Format: JPG, PNG, GIF. Maksimal 5MB.</p>
                        </div>
                    </div>
                    <!-- Kolom Kanan -->
                    <div>
                        <div class="form-group">
                            <label for="url_tautan" class="form-label"><iconify-icon icon="mdi:link"></iconify-icon> URL Tautan (Opsional)</label>
                            <input type="url" id="url_tautan" name="url_tautan" value="" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label for="urutan_tampil" class="form-label"><iconify-icon icon="mdi:sort-numeric-ascending"></iconify-icon> Urutan Tampil</label>
                            <input type="number" id="urutan_tampil" name="urutan_tampil" required min="0" value="0">
                        </div>
                        <div class="form-group checkbox-group" style="padding-top: 8px;">
                            <input id="status_promosi" name="status_promosi" type="checkbox" value="1" checked>
                            <label for="status_promosi" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Poster akan ditampilkan)</label>
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
        <?php endif; ?>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT (HTML LENGKAP DIKEMBALIKAN) -->
<!-- ============================================== -->
<div class="modal-overlay" id="editModalOverlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="editModalTitle">Edit Promosi Poster</h3> 
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button> 
        </div>
        <div class="modal-body">
            <form method="POST" enctype="multipart/form-data" action="index.php?page=poster&tab=daftar" id="editForm">
                <input type="hidden" name="action" value="edit_promosi">
                <input type="hidden" name="id_promosi" id="modal_id_promosi"> 
                <input type="hidden" name="current_file_name" id="modal_current_file_name">

                <div class="grid-2-cols">
                    <!-- Kolom Kiri -->
                    <div>
                        <div class="form-group">
                            <label for="modal_judul_promosi" class="form-label"><iconify-icon icon="mdi:format-title"></iconify-icon> Judul Promosi</label>
                            <input type="text" id="modal_judul_promosi" name="judul_promosi" required>
                        </div>
                        <div class="form-group">
                            <label for="modal_deskripsi_promosi" class="form-label"><iconify-icon icon="mdi:align-left"></iconify-icon> Deskripsi Promosi</label>
                            <textarea id="modal_deskripsi_promosi" name="deskripsi_promosi" rows="3" style="resize: vertical;"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="modal_gambar_promosi" class="form-label"><iconify-icon icon="mdi:image-outline"></iconify-icon> Upload Gambar Promosi</label>
                            <input type="file" id="modal_gambar_promosi" name="gambar_promosi" style="display: block; width: 100%; padding-top: 8px; padding-bottom: 8px;">
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
                            <label for="modal_url_tautan" class="form-label"><iconify-icon icon="mdi:link"></iconify-icon> URL Tautan (Opsional)</label>
                            <input type="url" id="modal_url_tautan" name="url_tautan" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label for="modal_urutan_tampil" class="form-label"><iconify-icon icon="mdi:sort-numeric-ascending"></iconify-icon> Urutan Tampil</label>
                            <input type="number" id="modal_urutan_tampil" name="urutan_tampil" required min="0">
                        </div>
                        <div class="form-group checkbox-group" style="padding-top: 8px;">
                            <input id="modal_status_promosi" name="status_promosi" type="checkbox" value="1">
                            <label for="modal_status_promosi" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Poster akan ditampilkan)</label>
                        </div>
                    </div>
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
<!-- MODAL HAPUS (HTML LENGKAP DIKEMBALIKAN) -->
<!-- ============================================== -->
<div class="modal-overlay" id="deleteModalOverlay">
    <div class="modal-container" style="max-width: 480px; text-align: center;">
        <div class="modal-header">
            <h3>Konfirmasi Penghapusan</h3>
            <button class="modal-close-btn" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <iconify-icon icon="mdi:alert-decagram" style="width: 64px; height: 64px; color: #f59e0b; margin-bottom: 16px;"></iconify-icon>
            <p style="color: #6b7280; margin-bottom: 24px;">Apakah Anda yakin ingin menghapus promosi **<span id="delete_judul_target" style="font-weight: 600;"></span>**? Tindakan ini tidak dapat dibatalkan.</p>
            <form method="POST" style="display:inline;" id="deleteForm" action="index.php?page=poster&tab=daftar">
                <input type="hidden" name="action" value="delete_promosi">
                <input type="hidden" name="id_promosi_delete" id="delete_id_target">
                <button type="button" class="btn" onclick="closeDeleteModal()" style="background-color: #e5e7eb; color: #374151; margin-right: 12px;">Batal</button>
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

    // --- Fungsi Modal Edit ---
    function closeEditModal() {
        console.log("CLOSE EDIT: Menutup modal edit...");
        document.getElementById('editModalOverlay').classList.remove('show');
    }

    async function openEditModal(id) {
        console.log("OPEN EDIT: Tombol edit diklik.");
        if (!id) {
            console.error("OPEN EDIT ERROR: ID tidak valid (null atau 0).");
            return;
        }
        console.log("OPEN EDIT: Membuka modal untuk ID poster:", id);

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


        // =================================================================
        // BLOK JAVASCRIPT (SUDAH BENAR)
        // =================================================================
        try {
            // Path 'poster/poster_ajax.php' sudah benar *relatif* terhadap halaman admin
            const fetchUrl = `poster/poster_ajax.php?action=fetch_json&id=${id}&_cache=${new Date().getTime()}`;
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
                alert('Gagal memuat data promosi: ' + (data.error || 'Data tidak ditemukan.'));
                closeEditModal();
                return;
            }
            
            // === PENGISIAN FORM ===
            document.getElementById('modal_id_promosi').value = data.id_promosi_poster || '';
            document.getElementById('modal_judul_promosi').value = data.judul_poster || '';
            document.getElementById('modal_deskripsi_promosi').value = data.deskripsi_promosi || '';
            document.getElementById('modal_url_tautan').value = data.link_tautan || '';
            document.getElementById('modal_urutan_tampil').value = data.urutan_tampil || 0;
            document.getElementById('modal_current_file_name').value = data.url_gambar || '';
            document.getElementById('modal_status_promosi').checked = data.status_promosi === 1;

            const imgElement = document.getElementById('modal_current_image');
            const defaultImg = 'https://placehold.co/64x64/CCCCCC/000000?text=IMG';
            
            if (data.current_image_url) {
                imgElement.src = data.current_image_url;
            } else {
                imgElement.src = defaultImg;
            }
            imgElement.onerror = function() { this.src = defaultImg; };

            modalTitle.innerHTML = `<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Edit Promosi Poster: ${data.judul_poster || 'ID: ' + id}`;
            
            console.log("OPEN EDIT: Sukses. Modal seharusnya sudah terisi penuh.");

        } catch (error) {
            console.error('OPEN EDIT CATCH ERROR: Terjadi kesalahan besar saat fetch/proses data:', error);
            alert('Terjadi kesalahan saat mengambil data.');
            closeEditModal();
        }
        // =================================================================
        // AKHIR BLOK JAVASCRIPT
        // =================================================================
    }

    // --- Fungsi Modal Hapus ---
    function closeDeleteModal() {
        console.log("CLOSE DELETE: Menutup modal hapus...");
        const modal = document.getElementById('deleteModalOverlay');
        if(modal) modal.classList.remove('show');
    }

    function openDeleteModal(id, title) {
        console.log("OPEN DELETE: Tombol hapus diklik.");
        if (!id || !title) {
            console.error("OPEN DELETE ERROR: ID atau Judul tidak valid.");
            return;
        }
        console.log("OPEN DELETE: Membuka modal untuk ID:", id, "Judul:", title);

        document.getElementById('delete_id_target').value = id;
        document.getElementById('delete_judul_target').textContent = title;
        
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