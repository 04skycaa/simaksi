<?php
require __DIR__ . '/../../config/supabase.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $response_data = ['success' => false, 'message' => 'Aksi tidak valid atau data kurang.'];
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'tambah') {
            $data_to_insert = [
                'tanggal_kuota' => $_POST['tanggal_kuota'] ?? null,
                'kuota_maksimal' => (int)($_POST['kuota_maksimal'] ?? 0),
                'kuota_terpesan' => 0 // Default 0 untuk kuota baru
            ];
            
            $result = supabase_request('POST', 'kuota_harian', $data_to_insert);

            // Logika untuk 'tambah' (POST) biasanya mengembalikan data, jadi '$result'
            // seharusnya berisi data. Biarkan pengecekan ini.
            if ($result && !isset($result['error'])) {
                $response_data = ['success' => true, 'message' => 'Kuota berhasil ditambahkan.'];
            } else {
                $error_message = $result['error']['message'] ?? 'Gagal menambahkan kuota. Cek log Supabase/RLS.';
                $response_data = ['success' => false, 'message' => $error_message];
            }

        } elseif ($action === 'edit') {
            $id_kuota = $_POST['id_kuota'] ?? null;
            $data_to_update = [
                'tanggal_kuota' => $_POST['tanggal_kuota'] ?? null,
                'kuota_maksimal' => (int)($_POST['kuota_maksimal'] ?? 0)
            ];
            
            if (!$id_kuota) {
                 throw new Exception('ID Kuota tidak ditemukan.');
            }

            $endpoint = 'kuota_harian?id_kuota=eq.' . $id_kuota;
            $result = supabase_request('PATCH', $endpoint, $data_to_update); 
            
            // PERBAIKAN: Operasi 'PATCH' (edit) mungkin tidak mengembalikan data.
            // Kita HANYA perlu memeriksa jika ada 'error'.
            if (!isset($result['error'])) {
                $response_data = ['success' => true, 'message' => 'Kuota berhasil diupdate.'];
            } else {
                $error_message = $result['error']['message'] ?? 'Gagal mengupdate kuota. Kuota tidak ditemukan atau RLS bermasalah.';
                $response_data = ['success' => false, 'message' => $error_message];
            }
            
        } elseif ($action === 'hapus') {
            $id_kuota = $_POST['id_kuota'] ?? null;
            
            if (!$id_kuota) {
                throw new Exception('ID Kuota tidak ditemukan.');
            }

            $endpoint = 'kuota_harian?id_kuota=eq.' . $id_kuota;
            $result = supabase_request('DELETE', $endpoint); 

            // PERBAIKAN FINAL: Operasi 'DELETE' (hapus) hampir pasti tidak mengembalikan data.
            // Kita HANYA perlu memeriksa jika ada 'error'.
            // Jika $result['error'] tidak ada (isset = false), berarti sukses.
            if (!isset($result['error'])) {
                $response_data = ['success' => true, 'message' => 'Kuota berhasil dihapus.'];
            } else {
                // Jika $result['error'] ada, barulah kita tampilkan error.
                $error_message = $result['error']['message'] ?? 'Gagal menghapus kuota atau kuota tidak ditemukan. RLS?';
                $response_data = ['success' => false, 'message' => $error_message];
            }
        }

    } catch (Exception $e) {
        $response_data = ['success' => false, 'message' => 'Kesalahan sistem PHP: ' . $e->getMessage()];
    }

    // Selalu kirim respons JSON dan HENTIKAN eksekusi
    echo json_encode($response_data);
    exit;
}

// Bagian HTML di bawah ini tidak akan pernah dieksekusi jika request-nya POST,
// karena 'exit' dipanggil di atas. Ini sepertinya sisa kode dari form.
?>

<div class="form-container">
    <form id="kuotaForm" action="proses_kuota.php" method="POST">
        <input type="hidden" name="action" value="<?= $form_action ?>">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id_kuota" value="<?= htmlspecialchars($kuota_data['id_kuota'] ?? '') ?>">
        <?php endif; ?>

        <?php if ($is_edit): ?>
            <div class="form-group">
                <label for="tanggal_kuota">Tanggal Kuota</label>
                <input type="date" id="tanggal_kuota" name="tanggal_kuota" value="<?= htmlspecialchars($kuota_data['tanggal_kuota'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="kuota_maksimal">Kuota Maksimal</label>
                <input type="number" id="kuota_maksimal" name="kuota_maksimal" value="<?= htmlspecialchars($kuota_data['kuota_maksimal'] ?? '') ?>" required>
            </div>
        <?php else: ?>
            <div class="input-group">
                <input type="date" id="tanggal_kuota" name="tanggal_kuota" placeholder=" " required>
                <label for="tanggal_kuota">Tanggal Kuota</label>
            </div>
            <div class="input-group">
                <input type="number" id="kuota_maksimal" name="kuota_maksimal" placeholder=" " required>
                <label for="kuota_maksimal">Kuota Maksimal</label>
            </div>
        <?php endif; ?>
        
        <button type="submit" class="form-submit-btn">
            <i class="fa-solid fa-save"></i> Simpan
        </button>
    </form>
</div>