<?php
// simaksi/auth/verify_otp.php
// Halaman untuk memverifikasi kode OTP yang dikirim Supabase
session_start();

// PASTIKAN PATH INI BENAR:
require_once '../config/api_connector.php';

$error_message = '';
$success_message = '';
// *** MENGARAHKAN KE LOGIN.PHP SETELAH VERIFIKASI SUKSES ***
$redirect_to = ''; 

// Ambil email dari URL (dari register.php) atau input tersembunyi
$email = trim($_GET['email'] ?? $_POST['email'] ?? '');

if (empty($email)) {
    // Jika tidak ada email, atur pesan error
    $error_message = "Email pendaftaran tidak ditemukan.";
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
        $payload = [
            'email' => $email, // Tambahkan kembali email
            'token' => $token_otp,
            'type' => 'email', // Tambahkan kembali verification type
        ];

        $authResponse = makeSupabaseAuthRequest('verify', $payload);

        if (isset($authResponse['error'])) {
            // Gagal verifikasi
            $http_status = $authResponse['http_status'] ?? 'Tidak Diketahui';
            $detailed_error = $authResponse['detailed_error'] ?? 'Kode tidak valid atau sudah kedaluwarsa.';
            
            // Simpan detail error lengkap
            $error_message = "Verifikasi gagal! Kode Status: " . htmlspecialchars($http_status) . ". Detail Error Supabase: " . htmlspecialchars($detailed_error) . ".";
            
            error_log("Supabase Auth Error [verify_otp]: HTTP Status: " . $http_status . " - Detail: " . $detailed_error);

        } else {
            // Sukses! Pengguna sekarang terverifikasi.
            $success_message = "Verifikasi berhasil! Akun Anda sudah aktif. Silakan masuk.";
            // *** REDIRECT KE LOGIN.PHP DENGAN PESAN SUKSES ***
            $redirect_to = 'login.php?verified=success'; 
            // PENTING: Jangan gunakan header('Location: ...') di sini. Kita akan pakai JavaScript.
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
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/auth.js" defer></script> 
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
                
                <!-- Penghapusan div error/success PHP yang lama -->
                <!-- SweetAlert2 akan menangani tampilan pesan -->

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

    <script>
        // Skrip SweetAlert2 untuk menangani hasil POST
        const errorMessage = "<?= $error_message ?>";
        const successMessage = "<?= $success_message ?>";
        const redirectTo = "<?= $redirect_to ?>";

        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Verifikasi Gagal!',
                text: errorMessage,
                confirmButtonText: 'Coba Lagi'
            });
        }

        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: successMessage,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            }).then(() => {
                // Arahkan setelah SweetAlert selesai
                if (redirectTo) {
                    window.location.href = redirectTo;
                }
            });
        }
    </script>
</body>
</html>