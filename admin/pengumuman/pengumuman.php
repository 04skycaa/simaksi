<?php
include __DIR__ . '/../../config/supabase.php';

$endpoint = 'pengumuman?order=dibuat_pada.desc';
$data = supabase_request('GET', $endpoint);

if (!$data || isset($data['error'])) {
    $data = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pengumuman</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/simaksi/assets/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="menu-container">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Konten</th>
                    <th>Mulai Tayang</th>
                    <th>Selesai Tayang</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $index => $row): ?>
                    <tr style="--animation-order: <?= $index + 1 ?>;">
                        <td class="judul-pengumuman"><?= htmlspecialchars($row['judul']) ?></td>
                        <td class="isi-pengumuman"><?= htmlspecialchars(substr($row['konten'], 0, 80)) . '...' ?></td>
                        <td><?= date('d M Y, H:i', strtotime($row['start_date'])) ?></td>
                        <td><?= date('d M Y, H:i', strtotime($row['end_date'])) ?></td>
                        <td>
                            <?php if ($row['telah_terbit']): ?>
                                <span class="status-badge status-disetujui">Terbit</span>
                            <?php else: ?>
                                <span class="status-badge status-ditolak">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn blue btn-edit" data-id="<?= $row['id_pengumuman'] ?>">
                                <i class="fa-solid fa-pencil"></i> Edit
                            </button>
                            <button class="btn red btn-hapus" data-id="<?= $row['id_pengumuman'] ?>">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Belum ada pengumuman.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="action-bar">
        <button class="btn green" id="btn-tambah-pengumuman">
            <i class="fa-solid fa-plus"></i> Buat Pengumuman Baru
        </button>
    </div>
</div>

<div class="modal-overlay" id="pengumuman-modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="pengumuman-modal-title">Buat Pengumuman Baru</h3>
            <button class="modal-close-btn" id="pengumuman-modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="pengumuman-form">
                <input type="hidden" id="id_pengumuman" name="id_pengumuman">
                
                <div class="form-group">
                    <label for="judul">Judul Pengumuman</label>
                    <input type="text" id="judul" name="judul" required>
                </div>
                
                <div class="form-group">
                    <label for="konten">Konten</label>
                    <textarea id="konten" name="konten" rows="5" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Mulai Tayang</label>
                        <input type="datetime-local" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Selesai Tayang</label>
                        <input type="datetime-local" id="end_date" name="end_date" required>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="telah_terbit" name="telah_terbit">
                    <label for="telah_terbit">Langsung terbitkan pengumuman ini?</label>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn green" id="btn-simpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/simaksi/assets/js/pengumuman.js"></script>

</body>
</html>