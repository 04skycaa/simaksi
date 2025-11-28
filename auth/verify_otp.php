<?php
// simaksi/auth/verify_otp.php
// Halaman untuk memverifikasi kode OTP yang dikirim Supabase

session_start();

// PASTIKAN PATH INI BENAR:
require_once '../config/api_connector.php';

$error_message = '';
$success_message = '';

// Ambil email dari URL (dari register.php) atau input tersembunyi
$email = trim($_GET['email'] ?? $_POST['email'] ?? '');

if (empty($email)) {
    // Jika tidak ada email, alihkan kembali ke register
    header('Location: register.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_otp = trim($_POST['token_otp'] ?? '');
    
    // 1. Validasi dasar
    if (empty($token_otp)) {
        $error_message = "Kode verifikasi (OTP) wajib diisi.";
    } elseif (!preg_match('/^\d{6}$/', $token_otp)) {
        $error_message = "Kode verifikasi harus 6 digit angka.";
    } else {
        // 2. Panggil Supabase Auth API untuk VERIFIKASI OTP
        
        // *** PERBAIKAN PAYLOAD KRUSIAL (KODE 400) ***
        // Mengembalikan 'email' dan 'type: email' ke body JSON 
        // karena Supabase secara eksplisit meminta 'verification type' di body.
        $payload = [
            'email' => $email, // Tambahkan kembali email
            'token' => $token_otp,
            'type' => 'email', // Tambahkan kembali verification type
        ];

        // *** PANGGILAN API KRUSIAL ***
        // Menggunakan endpoint 'verify' saja, karena type sudah ada di payload
        $authResponse = makeSupabaseAuthRequest('verify', $payload);

        if (isset($authResponse['error'])) {
            // Gagal verifikasi
            $http_status = $authResponse['http_status'] ?? 'Tidak Diketahui';
            // Detail error Supabase harusnya sudah lebih jelas
            $detailed_error = $authResponse['detailed_error'] ?? 'Kode tidak valid atau sudah kedaluwarsa.';
            
            // Tampilkan pesan error yang lebih jelas dari Supabase
            $error_message = "Verifikasi gagal! Kode Status: " . htmlspecialchars($http_status) . ". Detail Error Supabase: " . htmlspecialchars($detailed_error) . ".";
            
            error_log("Supabase Auth Error [verify_otp]: HTTP Status: " . $http_status . " - Detail: " . $detailed_error);

        } else {
            // Sukses! Pengguna sekarang terverifikasi.
            
            $success_message = "Verifikasi berhasil! Anda akan diarahkan ke halaman masuk.";
            
            // Arahkan ke halaman login
            header('Location: login.php?verified=success');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun - SIMAKSI</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>Selamat Datang di SIMAKSI</h1>
            <p>Sistem Informasi Pendaftaran dan Pelaporan Pendakian Gunung.</p>
        </div>
        <div class="right-section">
            <div class="register-box">
                <div class="logo">
                    <img src="../assets/images/logo1.png" alt="E-SIMAKSI Logo"> 
                </div>
                <h2>Verifikasi Akun</h2>
                <p>Kami telah mengirimkan **kode verifikasi 6 digit** ke alamat email:</p>
                <p class="email-display">**<?= htmlspecialchars($email) ?>**</p>
                
                <?php if ($error_message): ?>
                    <div class="error-message"> 
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php elseif ($success_message): ?>
                    <div class="success-message"> 
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="verify_otp.php">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    
                    <!-- Input untuk Kode Verifikasi (OTP) -->
                    <div class="input-group floating-label">
                        <input type="text" id="token_otp" name="token_otp" placeholder=" " required
                            minlength="6" maxlength="6" pattern="\d{6}" inputmode="numeric">
                        <label for="token_otp">Kode Verifikasi (6 Digit)</label>
                    </div>
                    
                    <button type="submit" class="register-btn">Verifikasi Akun</button>
                </form>

                <div class="login-link">
                    Tidak menerima kode? Pastikan email Anda benar atau <a href="register.php">coba daftar lagi</a>.
                </div>
            </div>
        </div>
    </div>
</body>
</html>