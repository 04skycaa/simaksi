<?php
require_once '../config/supabase.php';
$serviceRoleKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtpdHh0Y3BmbmNjYmx6bmJhZ3p4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1OTU4MjEzMSwiZXhwIjoyMDc1MTU4MTMxfQ.eSggC5imTRztxGNQyW9exZTQo3CU-8QmZ54BhfUDTcE';

$message = '';
$page = $_GET['page'] ?? 'promosi';
$active_sub_tab = isset($_GET['sub']) && in_array($_GET['sub'], ['promosi', 'poster']) ? $_GET['sub'] : 'promosi';
$active_tab = isset($_GET['tab']) && in_array($_GET['tab'], ['tambah', 'daftar']) ? $_GET['tab'] : 'daftar';

// Functions for promo
function fetch_promo_list() {
    global $serviceRoleKey;
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', 'promosi?order=dibuat_pada.desc', null, $headers);
    return $response;
}

// Functions for poster
function fetch_poster_list() {
    global $serviceRoleKey;
    $headers = ['X-Override-Key' => $serviceRoleKey];
    $response = supabase_request('GET', 'promosi_poster?order=urutan.asc,id_poster.desc', null, $headers);
    return $response;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $itemType = $_POST['itemType'] ?? '';
    
    if ($action === 'tambah' && $itemType === 'promo') {
        $data = [
            'nama_promosi' => $_POST['nama_promosi'],
            'deskripsi_promosi' => $_POST['deskripsi_promosi'],
            'tipe_promosi' => $_POST['tipe_promosi'],
            'nilai_promosi' => floatval($_POST['nilai_promosi']),
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_akhir' => $_POST['tanggal_akhir'],
            'is_aktif' => isset($_POST['is_aktif']) ? true : false,
            'kode_promo' => $_POST['kode_promo'] ?? '',
            'kondisi_min_pendaki' => intval($_POST['kondisi_min_pendaki'] ?? 1)
        ];
        
        $headers = ['X-Override-Key' => $serviceRoleKey];
        $result = supabase_request('POST', 'promosi', $data, $headers);
        
        if (isset($result['error'])) {
            $message = "<div>Error: " . ($result['error']['message'] ?? 'Unknown error') . "</div>";
        } else {
            $message = "<div>Promosi berhasil ditambahkan!</div>";
            header("Location: ?page=$page&sub=promosi&tab=daftar");
            exit;
        }
    }
    
    if ($action === 'tambah' && $itemType === 'poster') {
        $upload_dir = '../../uploads/poster/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $gambar_nama = '';
        if (isset($_FILES['gambar_poster']) && $_FILES['gambar_poster']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['gambar_poster']['name'], PATHINFO_EXTENSION));
            $gambar_nama = time() . '_' . uniqid() . '.' . $ext;
            $target = $upload_dir . $gambar_nama;
            if (!move_uploaded_file($_FILES['gambar_poster']['tmp_name'], $target)) {
                $message = "<div>Error upload gambar</div>";
            }
        }
        
        if (empty($gambar_nama)) {
            $message = "<div>Gambar harus diupload</div>";
        } else {
            $data = [
                'judul_poster' => $_POST['judul_poster'],
                'deskripsi_poster' => $_POST['deskripsi_poster'],
                'url_gambar' => $gambar_nama,
                'urutan' => intval($_POST['urutan'] ?? 0),
                'is_aktif' => isset($_POST['is_aktif']) ? true : false
            ];
            
            $headers = ['X-Override-Key' => $serviceRoleKey];
            $result = supabase_request('POST', 'promosi_poster', $data, $headers);
            
            if (isset($result['error'])) {
                $message = "<div>Error: " . ($result['error']['message'] ?? 'Unknown error') . "</div>";
            } else {
                $message = "<div>Poster berhasil ditambahkan!</div>";
                header("Location: ?page=$page&sub=poster&tab=daftar");
                exit;
            }
        }
    }
}

// Fetch data based on active sub tab
$items = [];
if ($active_sub_tab === 'promosi') {
    $items = fetch_promo_list();
} else {
    $items = fetch_poster_list();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Promosi & Poster</title>
    <style>
        .tabs-container { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 10px 15px; cursor: pointer; border: 1px solid #ccc; }
        .tab-btn.active { background: #007cba; color: white; }
        .btn-tambah { background: #28a745; color: white; padding: 8px 16px; text-decoration: none; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <?php echo $message; ?>
        
        <!-- Tabs -->
        <div class="tabs-container">
            <a href="?page=<?php echo $page; ?>&sub=promosi&tab=daftar" class="tab-btn <?php echo $active_sub_tab === 'promosi' ? 'active' : ''; ?>">Promosi</a>
            <a href="?page=<?php echo $page; ?>&sub=poster&tab=daftar" class="tab-btn <?php echo $active_sub_tab === 'poster' ? 'active' : ''; ?>">Poster</a>
            <a href="?page=<?php echo $page; ?>&sub=<?php echo $active_sub_tab; ?>&tab=tambah" class="btn-tambah">Tambah <?php echo ucfirst($active_sub_tab); ?></a>
        </div>
        
        <h2>Daftar <?php echo ucfirst($active_sub_tab); ?></h2>
        
        <?php if ($active_tab === 'daftar'): ?>
            <table>
                <tr>
                    <th>Nama/Judul</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item[$active_sub_tab === 'promosi' ? 'nama_promosi' : 'judul_poster'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($item[$active_sub_tab === 'promosi' ? 'deskripsi_promosi' : 'deskripsi_poster'] ?? ''); ?></td>
                    <td><?php echo $item['is_aktif'] ? 'Aktif' : 'Nonaktif'; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <h3>Tambah <?php echo ucfirst($active_sub_tab); ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="tambah">
                <input type="hidden" name="itemType" value="<?php echo $active_sub_tab; ?>">
                
                <?php if ($active_sub_tab === 'promosi'): ?>
                    <div class="form-group">
                        <label>Nama Promosi</label>
                        <input type="text" name="nama_promosi" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Promosi</label>
                        <textarea name="deskripsi_promosi"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tipe Promosi</label>
                        <select name="tipe_promosi" required>
                            <option value="PERSENTASE">Persentase</option>
                            <option value="POTONGAN_TETAP">Potongan Tetap</option>
                            <option value="HARGA_KHUSUS">Harga Khusus</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nilai Promosi</label>
                        <input type="number" step="0.01" name="nilai_promosi" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="datetime-local" name="tanggal_mulai" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Akhir</label>
                        <input type="datetime-local" name="tanggal_akhir" required>
                    </div>
                    <div class="form-group">
                        <label>Kode Promosi</label>
                        <input type="text" name="kode_promo">
                    </div>
                    <div class="form-group">
                        <label>Min Pendaki</label>
                        <input type="number" name="kondisi_min_pendaki" value="1">
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label>Judul Poster</label>
                        <input type="text" name="judul_poster" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Poster</label>
                        <textarea name="deskripsi_poster"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Gambar Poster</label>
                        <input type="file" name="gambar_poster" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label>Urutan Tampil</label>
                        <input type="number" name="urutan" value="0">
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Aktif</label>
                    <input type="checkbox" name="is_aktif" value="1" checked>
                </div>
                
                <button type="submit">Simpan</button>
                <a href="?page=<?php echo $page; ?>&sub=<?php echo $active_sub_tab; ?>&tab=daftar">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>