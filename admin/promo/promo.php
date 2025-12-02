<?php
require_once '../config/supabase.php';
$serviceRoleKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE';

function fetch_promosi_list() {
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

function fetch_promosi_by_id($id) {
    global $serviceRoleKey;
    $filter = urlencode("id_promosi=eq.{$id}");
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', "promosi?{$filter}", null, $headers);
    if (isset($response['error']) || empty($response) || !is_array($response)) {
        return null;
    }
    return $response[0] ?? null;
}

// Insert promosi
function insert_promosi($data) {
    global $serviceRoleKey;
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('POST', 'promosi', $data, $headers);
}

// Update promosi
function update_promosi($id, $data) {
    global $serviceRoleKey;
    $endpoint = "promosi?id_promosi=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('PATCH', $endpoint, $data, $headers);
}

// Delete promosi
function delete_promosi($id) {
    global $serviceRoleKey;
    $endpoint = "promosi?id_promosi=eq.{$id}";
    $headers = ['X-Override-Key' => $serviceRoleKey];
    return supabase_request('DELETE', $endpoint, null, $headers);
}


// ----------------------------------------------------------------------
// LOGIKA BACK-END (DB ASLI Supabase)
// ----------------------------------------------------------------------
$message = '';
$active_tab = isset($_GET['tab']) && in_array($_GET['tab'], ['tambah', 'daftar']) ? $_GET['tab'] : 'daftar';

// --- Handle Form Submission (Insert/Update/Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action'] ?? null;
    $id_promosi = isset($_POST['id_promosi']) ? (int)$_POST['id_promosi'] : null;

    // --- LOGIKA INSERT & UPDATE ---
    if (in_array($action_type, ['tambah_promosi', 'edit_promosi'])) {
        $data = [
            'nama_promosi' => trim(htmlspecialchars($_POST['nama_promosi'] ?? '')),
            'deskripsi_promosi' => trim(htmlspecialchars($_POST['deskripsi_promosi'] ?? '')),
            'tipe_promosi' => trim(htmlspecialchars($_POST['tipe_promosi'] ?? '')),
            'nilai_promosi' => (float)($_POST['nilai_promosi'] ?? 0),
            'kondisi_min_pendaki' => (int)($_POST['kondisi_min_pendaki'] ?? 1),
            'kondisi_max_pendaki' => !empty($_POST['kondisi_max_pendaki']) ? (int)$_POST['kondisi_max_pendaki'] : null,
            'tanggal_mulai' => trim($_POST['tanggal_mulai'] ?? ''),
            'tanggal_akhir' => trim($_POST['tanggal_akhir'] ?? ''),
            'is_aktif' => isset($_POST['is_aktif']) ? true : false,
            'kode_promo' => trim(htmlspecialchars($_POST['kode_promo'] ?? ''))
        ];

        if (empty($data['nama_promosi'])) {
            $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error: Nama promosi harus diisi.</div>";
        } else {
            if ($action_type === 'tambah_promosi') {
                $response = insert_promosi($data);
                if (isset($response['error'])) {
                    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase INSERT: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                } else {
                    $redirect_url = "index.php?page=promo&tab=daftar&msg=added";
                }
            } elseif ($action_type === 'edit_promosi' && $id_promosi) {
                $response = update_promosi($id_promosi, $data);
                if (isset($response['error'])) {
                    $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase UPDATE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
                } else {
                    $redirect_url = "index.php?page=promo&tab=daftar&msg=updated";
                }
            }
        }
    }
    
    // --- LOGIKA DELETE ---
    if ($action_type === 'delete_promosi' && isset($_POST['id_promosi_delete'])) {
        $id_delete = (int)$_POST['id_promosi_delete'];
        $response = delete_promosi($id_delete);
        if (isset($response['error'])) {
            $message = "<div style='padding:12px; background-color:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; border-radius:6px; margin-bottom:16px;'>Error Supabase DELETE: " . ($response['error']['message'] ?? 'Unknown error') . "</div>";
        } else {
            $redirect_url = "index.php?page=promo&tab=daftar&msg=deleted";
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
    $promosi_data = array_merge($default_keys, $cleaned_row);
    $promosi = [
        'id_promosi' => $promosi_data['id_promosi'],
        'nama_promosi' => $promosi_data['nama_promosi'],
        'deskripsi_promosi' => $promosi_data['deskripsi_promosi'],
        'tipe_promosi' => $promosi_data['tipe_promosi'],
        'nilai_promosi' => $promosi_data['nilai_promosi'],
        'kondisi_min_pendaki' => $promosi_data['kondisi_min_pendaki'],
        'kondisi_max_pendaki' => $promosi_data['kondisi_max_pendaki'],
        'tanggal_mulai' => $promosi_data['tanggal_mulai'],
        'tanggal_akhir' => $promosi_data['tanggal_akhir'],
        'is_aktif' => $promosi_data['is_aktif'],
        'dibuat_pada' => $promosi_data['dibuat_pada'],
        'kode_promo' => $promosi_data['kode_promo']
    ];
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


<div class="promo-container">
    <!-- Area Tab Header -->
    <div class="card mb-6">
        <div class="flex tab-header" style="border-radius: 12px 12px 0 0;">
            <a href="index.php?page=promo&tab=daftar" class="tab-item <?php echo $active_tab === 'daftar' ? 'tab-active' : ''; ?>">
                <iconify-icon icon="mdi:view-list" style="width: 20px; height: 20px; margin-right: 8px;"></iconify-icon>
                <span>Daftar Promosi</span>
            </a>
            <a href="index.php?page=promo&tab=tambah" class="tab-item <?php echo $active_tab === 'tambah' ? 'tab-active' : ''; ?>">
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
                Daftar Promosi (Total: <?php echo count($promosi_list); ?>)
            </h2>
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
                                    <?php echo htmlspecialchars($promosi['nama_promosi'] ?? 'N/A'); ?>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php echo htmlspecialchars($promosi['kode_promo'] ?? '-'); ?>
                                </td>
                                <td style="color: #6b7280; text-transform: capitalize;">
                                    <?php echo htmlspecialchars($promosi['tipe_promosi'] ?? 'N/A'); ?>
                                </td>
                                <td style="color: #6b7280;">
                                    <?php 
                                        if ($promosi['tipe_promosi'] === 'PERSENTASE') {
                                            echo htmlspecialchars($promosi['nilai_promosi']) . '%';
                                        } elseif ($promosi['tipe_promosi'] === 'POTONGAN_TETAP') {
                                            echo 'Rp ' . number_format($promosi['nilai_promosi'], 0, ',', '.');
                                        } else {
                                            echo 'Rp ' . number_format($promosi['nilai_promosi'], 0, ',', '.');
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
                                    <?php $status = ($promosi['is_aktif'] === true || $promosi['is_aktif'] === 't') ? 1 : 0; ?>
                                    <span class="<?php echo $status ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $status ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <button onclick="openEditModal(<?php echo $promosi['id_promosi'] ?? 'null'; ?>)" class="btn btn-yellow" style="padding: 4px 12px; font-size: 12px; margin-right: 8px;">
                                        <iconify-icon icon="mdi:pencil-outline" style="width: 16px; height: 16px; margin-right: 4px;"></iconify-icon> Edit
                                    </button>
                                    <button onclick="openDeleteModal(<?php echo $promosi['id_promosi'] ?? 'null'; ?>, '<?php echo htmlspecialchars($promosi['nama_promosi'] ?? 'Promosi ini'); ?>')" class="btn btn-red" style="padding: 4px 12px; font-size: 12px;">
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
            <form method="POST" action="index.php?page=promo&tab=tambah">
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
                            <textarea id="deskripsi_promosi" name="deskripsi_promosi" rows="3" placeholder="Deskripsi singkat promosi ini" style="resize: vertical;"></textarea>
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
                            <label for="kode_promo" class="form-label"><iconify-icon icon="mdi:ticket-percent-outline"></iconify-icon> Kode Promosi</label>
                            <input type="text" id="kode_promo" name="kode_promo" value="" placeholder="Masukkan kode promosi (opsional)">
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
                            <input id="is_aktif" name="is_aktif" type="checkbox" value="1" checked>
                            <label for="is_aktif" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Promosi akan digunakan)</label>
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
            <h3 id="editModalTitle">Edit Promosi</h3> 
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button> 
        </div>
        <div class="modal-body">
            <form method="POST" action="index.php?page=promo&tab=daftar" id="editForm">
                <input type="hidden" name="action" value="edit_promosi">
                <input type="hidden" name="id_promosi" id="modal_id_promosi"> 

                <div class="grid-2-cols">
                    <!-- Kolom Kiri -->
                    <div>
                        <div class="form-group">
                            <label for="modal_nama_promosi" class="form-label"><iconify-icon icon="mdi:format-title"></iconify-icon> Nama Promosi</label>
                            <input type="text" id="modal_nama_promosi" name="nama_promosi" required>
                        </div>
                        <div class="form-group">
                            <label for="modal_deskripsi_promosi" class="form-label"><iconify-icon icon="mdi:align-left"></iconify-icon> Deskripsi Promosi</label>
                            <textarea id="modal_deskripsi_promosi" name="deskripsi_promosi" rows="3" style="resize: vertical;"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="modal_tipe_promosi" class="form-label"><iconify-icon icon="mdi:tag-multiple"></iconify-icon> Tipe Promosi</label>
                            <select id="modal_tipe_promosi" name="tipe_promosi" required>
                                <option value="">Pilih Tipe Promosi</option>
                                <option value="PERSENTASE">Persentase</option>
                                <option value="POTONGAN_TETAP">Potongan Tetap</option>
                                <option value="HARGA_KHUSUS">Harga Khusus</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modal_nilai_promosi" class="form-label"><iconify-icon icon="mdi:percent"></iconify-icon> Nilai Promosi</label>
                            <input type="number" id="modal_nilai_promosi" name="nilai_promosi" step="0.01" required min="0" value="0">
                        </div>
                    </div>
                    <!-- Kolom Kanan -->
                    <div>
                        <div class="form-group">
                            <label for="modal_kode_promo" class="form-label"><iconify-icon icon="mdi:ticket-percent-outline"></iconify-icon> Kode Promosi</label>
                            <input type="text" id="modal_kode_promo" name="kode_promo" value="">
                        </div>
                        <div class="form-group">
                            <label for="modal_tanggal_mulai" class="form-label"><iconify-icon icon="mdi:calendar-start"></iconify-icon> Tanggal Mulai</label>
                            <input type="datetime-local" id="modal_tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                        <div class="form-group">
                            <label for="modal_tanggal_akhir" class="form-label"><iconify-icon icon="mdi:calendar-end"></iconify-icon> Tanggal Akhir</label>
                            <input type="datetime-local" id="modal_tanggal_akhir" name="tanggal_akhir" required>
                        </div>
                        <div class="form-group">
                            <label for="modal_kondisi_min_pendaki" class="form-label"><iconify-icon icon="mdi:account-group"></iconify-icon> Min. Pendaki</label>
                            <input type="number" id="modal_kondisi_min_pendaki" name="kondisi_min_pendaki" required min="1" value="1">
                        </div>
                        <div class="form-group">
                            <label for="modal_kondisi_max_pendaki" class="form-label"><iconify-icon icon="mdi:account-group"></iconify-icon> Max. Pendaki (Opsional)</label>
                            <input type="number" id="modal_kondisi_max_pendaki" name="kondisi_max_pendaki" min="1" placeholder="Kosongkan jika tidak ada batas">
                        </div>
                        <div class="form-group checkbox-group" style="padding-top: 8px;">
                            <input id="modal_is_aktif" name="is_aktif" type="checkbox" value="1">
                            <label for="modal_is_aktif" style="font-size: 14px; font-weight: 500; color: #374151;">Aktif (Promosi akan digunakan)</label>
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
            <p style="color: #6b7280; margin-bottom: 24px;">Apakah Anda yakin ingin menghapus promosi **<span id="delete_nama_target" style="font-weight: 600;"></span>**? Tindakan ini tidak dapat dibatalkan.</p>
            <form method="POST" style="display:inline;" id="deleteForm" action="index.php?page=promo&tab=daftar">
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
    console.log("SCRIPT PROMO.PHP: Berhasil dimuat.");

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
        console.log("OPEN EDIT: Membuka modal untuk ID promosi:", id);

        const modalTitle = document.getElementById('editModalTitle');
        const modal = document.getElementById('editModalOverlay');
        
        // Cek jika elemen ada sebelum menggunakannya
        if (modalTitle) {
            modalTitle.innerHTML = '<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Memuat Data...';
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

        try {
            // Fetch data from an API endpoint
            const fetchUrl = 'promo_ajax.php?action=fetch_json&id=' + id + '&_cache=' + new Date().getTime();
            console.log("OPEN EDIT: Mengambil data dari URL:", fetchUrl);

            const response = await fetch(fetchUrl);

            console.log("OPEN EDIT: Response status dari server:", response.status);
            if (!response.ok) {
                console.error("OPEN EDIT FETCH ERROR: Network response was not ok.", response);
                throw new Error('HTTP error! status: ' + response.status);
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
            document.getElementById('modal_id_promosi').value = data.id_promosi || '';
            document.getElementById('modal_nama_promosi').value = data.nama_promosi || '';
            document.getElementById('modal_deskripsi_promosi').value = data.deskripsi_promosi || '';
            document.getElementById('modal_tipe_promosi').value = data.tipe_promosi || '';
            document.getElementById('modal_nilai_promosi').value = data.nilai_promosi || 0;
            document.getElementById('modal_kode_promo').value = data.kode_promo || '';
            
            // Format date for datetime-local input (YYYY-MM-DDTHH:MM)
            if (data.tanggal_mulai) {
                const startDate = new Date(data.tanggal_mulai);
                document.getElementById('modal_tanggal_mulai').value = startDate.toISOString().slice(0, 16);
            }
            if (data.tanggal_akhir) {
                const endDate = new Date(data.tanggal_akhir);
                document.getElementById('modal_tanggal_akhir').value = endDate.toISOString().slice(0, 16);
            }
            
            document.getElementById('modal_kondisi_min_pendaki').value = data.kondisi_min_pendaki || 1;
            document.getElementById('modal_kondisi_max_pendaki').value = data.kondisi_max_pendaki || '';
            document.getElementById('modal_is_aktif').checked = data.is_aktif === 1 || data.is_aktif === true;

            modalTitle.innerHTML = '<iconify-icon icon="mdi:pencil-outline"></iconify-icon> Edit Promosi: ' + (data.nama_promosi || 'ID: ' + id);

            console.log("OPEN EDIT: Sukses. Modal seharusnya sudah terisi penuh.");

        } catch (error) {
            console.error('OPEN EDIT CATCH ERROR: Terjadi kesalahan besar saat fetch/proses data:', error);
            alert('Terjadi kesalahan saat mengambil data.');
            closeEditModal();
        }
    }

    // --- Fungsi Modal Hapus ---
    function closeDeleteModal() {
        console.log("CLOSE DELETE: Menutup modal hapus...");
        const modal = document.getElementById('deleteModalOverlay');
        if(modal) modal.classList.remove('show');
    }

    function openDeleteModal(id, name) {
        console.log("OPEN DELETE: Tombol hapus diklik.");
        if (!id || !name) {
            console.error("OPEN DELETE ERROR: ID atau Nama tidak valid.");
            return;
        }
        console.log("OPEN DELETE: Membuka modal untuk ID:", id, "Nama:", name);

        document.getElementById('delete_id_target').value = id;
        document.getElementById('delete_nama_target').textContent = name;
        
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