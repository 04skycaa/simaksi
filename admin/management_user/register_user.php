<?php
// Hanya mulai session jika belum aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validasi otorisasi dilakukan di index.php sebelum menyertakan file ini
// Tapi kita tetap lakukan pengecekan tambahan sebagai layer keamanan
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_peran'] !== 'admin') {
    // Redirect ke login jika akses diperoleh secara langsung
    if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
        header('Location: ../../auth/login.php');
        exit;
    } else {
        // Jika diakses melalui include (seharusnya tidak terjadi karena dicek di index.php)
        echo '<div class="error-container" style="padding: 20px; text-align: center; color: #dc2626; background-color: #fee2e2; border-radius: 6px; margin: 20px;">';
        echo '<h3>Akses Ditolak</h3>';
        echo '<p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>';
        echo '<a href="../index.php" style="color: #3b82f6;">Kembali ke Dashboard</a>';
        echo '</div>';
        exit;
    }
}

require_once __DIR__ . '/../../config/api_connector.php';
include_once __DIR__ . '/../../admin/api/config.php';

// Ambil fungsi yang sudah ada
if (!function_exists('makeSupabaseRequest')) {
    die("Error: Fungsi makeSupabaseRequest tidak ditemukan.");
}

// serviceRoleKey sudah didefinisikan di file config.php yang di-include sebelumnya
global $serviceRoleKey;

$error_message = '';
$success_message = '';

// Fungsi untuk membuat pengguna baru melalui Admin API Supabase
function createSupabaseUser($email, $password, $nama_lengkap, $peran = 'pendaki', $nomor_telepon = '', $alamat = '', $nik = '', $tanggal_lahir = null) {
    global $serviceRoleKey;

    // 1. Buat pengguna lewat Admin API Auth
    $authUrl = 'https://kitxtcpfnccblznbagzx.supabase.co/auth/v1/admin/users';
    $authHeaders = [
        'Content-Type: application/json',
        'apikey: ' . $serviceRoleKey,
        'Authorization: Bearer ' . $serviceRoleKey,
    ];

    $userPayload = [
        'email' => $email,
        'password' => $password,
        'email_confirm' => true,
        'user_metadata' => [
            'nama_lengkap' => $nama_lengkap,
            'peran' => $peran,
            'nomor_telepon' => $nomor_telepon,
            'alamat' => $alamat,
            'nik' => $nik,
            'tanggal_lahir' => $tanggal_lahir,
        ]
    ];

    $ch = curl_init($authUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeaders);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userPayload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => true, 'detailed_error' => 'cURL Error: ' . $error, 'http_status' => 0];
    }

    $userData = json_decode($response, true);

    error_log("Auth API Response: " . $response . " | Status: " . $http_status);

    if ($http_status >= 400) {
        $error_detail = $userData['error'] ?? $userData['message'] ?? ($response ?? 'Unknown error');

        // Tangani error khusus untuk email yang sudah terdaftar
        if (isset($userData['error_code']) && $userData['error_code'] === 'email_exists') {
            $error_detail = 'Email sudah terdaftar. Silakan gunakan email lain.';
        }

        error_log("Auth API Error: " . $error_detail);
        return ['error' => true, 'detailed_error' => $error_detail, 'http_status' => $http_status];
    }

    // Tangani kasus respon tanpa data (response kosong)
    if (empty($userData)) {
        error_log("Empty response from Auth API: " . $response);
        return ['error' => true, 'detailed_error' => 'Supabase tidak mengembalikan data pengguna yang valid.', 'http_status' => $http_status];
    }

    // Kita tidak akan update tabel profiles karena menyebabkan konflik dengan fungsi handle_new_user
    // Profil seharusnya sudah dibuat oleh fungsi handle_new_user secara otomatis
    // Proses pembuatan profil oleh handle_new_user akan otomatis mencocokkan struktur yang benar
    // Jika update profil diperlukan, bisa dilakukan secara terpisah

    return ['error' => false, 'data' => $userData, 'http_status' => $http_status];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $peran = $_POST['peran'] ?? 'pendaki'; // Default role
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $nik = trim($_POST['nik'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;

    // Validasi input
    if (empty($email) || empty($nama_lengkap) || empty($password)) {
        $error_message = "Email, Nama Lengkap, dan Kata Sandi wajib diisi.";
    } elseif (strlen($password) < 6) {
        $error_message = "Kata Sandi minimal 6 karakter.";
    } else {
        // Cek apakah role valid
        $valid_roles = ['pendaki', 'admin'];
        if (!in_array($peran, $valid_roles)) {
            $peran = 'pendaki'; // Set default jika role tidak valid
        }

        // Buat pengguna menggunakan Admin API
        $authResponse = createSupabaseUser($email, $password, $nama_lengkap, $peran, $nomor_telepon, $alamat, $nik, $tanggal_lahir);

        if (isset($authResponse['error']) && $authResponse['error'] === true) {
            $http_status = $authResponse['http_status'] ?? 'Tidak Diketahui';
            $detailed_error = $authResponse['detailed_error'] ?? 'Tidak ada detail error yang dikirim oleh Supabase.';

            error_log("Supabase Admin Auth Error: HTTP Status: " . $http_status . " - Detail: " . $detailed_error . " - Email: " . $email);

            $error_message = "Gagal mendaftar pengguna. Kode Status: " . htmlspecialchars($http_status) . ". Detail: " . htmlspecialchars($detailed_error);
        } else {
            $success_message = "Pengguna berhasil didaftarkan: " . htmlspecialchars($email);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna Baru - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .content-wrapper {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #f8f9fa;
            min-height: 80vh;
        }

        .main-content-area {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-header {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #1f2937;
            text-align: center;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group label {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            color: #888;
            background-color: #fff;
            padding: 0 5px;
            margin: 0 5px;
            transition: all 0.2s ease-out;
            pointer-events: none;
            font-weight: 600;
        }

        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
            padding: 14px 20px;
            border: 2px solid #35542E;
            border-radius: 50px;
            font-size: 1rem;
            color: #333;
            background-color: #fff;
            transition: border-color 0.2s;
            box-sizing: border-box;
            font-family: inherit;
        }

        .input-group input:focus,
        .input-group select:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: #75B368;
            box-shadow: 0 0 0 3px rgba(117, 179, 104, 0.2);
        }

        .input-group select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2335542E' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 20px center;
            background-repeat: no-repeat;
            background-size: 16px 16px;
            padding-right: 50px;
        }

        .input-group textarea {
            resize: vertical;
            min-height: 100px;
            border-radius: 20px;
        }

        /* Floating label effect */
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label,
        .input-group select:focus + label,
        .input-group select:not([value=""]) + label,
        .input-group textarea:focus + label,
        .input-group textarea:not(:placeholder-shown) + label {
            top: 0;
            font-size: 0.85rem;
            color: #35542E;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #35542E;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #2a4325;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: #6b7280;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background-color: #4b5563;
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="main-content-area">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pengguna
            </a>

            <div class="form-container">
                <h2 class="form-header">Daftar Pengguna Baru</h2>

                <?php if ($error_message): ?>
                    <div class="message error">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="message success">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder=" " required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <label for="email">Email *</label>
                    </div>

                    <div class="input-group">
                        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder=" " required value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">
                        <label for="nama_lengkap">Nama Lengkap *</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder=" " required>
                        <label for="password">Kata Sandi *</label>
                    </div>

                    <div class="input-group">
                        <select id="peran" name="peran">
                            <option value="" <?= (!isset($_POST['peran']) || $_POST['peran'] === '') ? 'selected' : '' ?>>Pilih Peran</option>
                            <option value="pendaki" <?= (isset($_POST['peran']) && $_POST['peran'] === 'pendaki') ? 'selected' : '' ?>>Pendaki</option>
                            <option value="admin" <?= (isset($_POST['peran']) && $_POST['peran'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <label for="peran">Peran</label>
                    </div>

                    <div class="input-group">
                        <input type="tel" id="nomor_telepon" name="nomor_telepon" placeholder=" " value="<?= htmlspecialchars($_POST['nomor_telepon'] ?? '') ?>">
                        <label for="nomor_telepon">Nomor Telepon</label>
                    </div>

                    <div class="input-group">
                        <textarea id="alamat" name="alamat" placeholder=" " rows="3"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                        <label for="alamat">Alamat</label>
                    </div>

                    <div class="input-group">
                        <input type="text" id="nik" name="nik" placeholder=" " value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>">
                        <label for="nik">NIK</label>
                    </div>

                    <div class="input-group">
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" placeholder=" " value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>">
                        <label for="tanggal_lahir">Tanggal Lahir</label>
                    </div>

                    <button type="submit" class="btn-submit">Daftar Pengguna</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>