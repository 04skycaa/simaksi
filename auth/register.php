<?php
session_start();
require_once '../config/api_connector.php';

$error_message = '';
$success_redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    
    if (empty($email) || empty($nama_lengkap) || empty($password)) {
        $error_message = "Semua kolom wajib diisi: Email, Nama Lengkap, dan Kata Sandi.";
    } elseif (strlen($password) < 6) {
        $error_message = "Kata Sandi minimal 6 karakter.";
    } else {
        $payload = [
            'email' => $email,
            'password' => $password, 
            'data' => [ 
                'nama_lengkap' => $nama_lengkap,
                'role' => 'pendaki',
            ]
        ];

        $authResponse = makeSupabaseAuthRequest('signup', $payload);

        if (isset($authResponse['error'])) {
            $http_status = $authResponse['http_status'] ?? 'Tidak Diketahui';
            $detailed_error = $authResponse['detailed_error'] ?? 'Tidak ada detail error yang dikirim oleh Supabase.';
            
            error_log("Supabase Auth Error [signup]: HTTP Status: " . $http_status . " - Detail: " . $detailed_error . " - Email: " . $email);

            $error_message = "Gagal mendaftar. Kode Status: " . htmlspecialchars($http_status) . ". Detail: " . htmlspecialchars($detailed_error);

        } else {
            $success_redirect = true;
            $redirect_url = 'verify_otp.php?email=' . urlencode($email);
            $_SESSION['temp_email'] = $email;
        }
    }
    
    if ($error_message) {
        $_SESSION['reg_data'] = [
            'email' => $email,
            'nama_lengkap' => $nama_lengkap,
        ];
    }
}

$reg_data = $_SESSION['reg_data'] ?? [];
unset($_SESSION['reg_data']); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - SIMAKSI</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/auth.js" defer></script> 
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>SELAMAT DATANG DI GUNUNG BUTAK</h1>
            <p>Mulai langkahmu dengan membuat akun, dengan registrasi ini, kamu akan mendapatkan akses lengkap ke informasi jalur, aturan pendakian, dan update terbaru Gunung Butak.
Dan yang paling penting—akun yang kamu buat di website bisa langsung digunakan di aplikasi mobile tanpa harus daftar ulang.
Satu akun, akses di mana saja.</p>
        </div>
        <div class="right-section">
            <div class="register-box">
                <div class="logo">
                    <img src="../assets/images/logo1.png" alt="E-SIMAKSI Logo"> 
                </div>
                <h2>Buat Akun</h2>
                <p>Masukkan data Anda dan buat Kata Sandi. Konfirmasi akun akan dikirim melalui email.</p>

                <form method="POST" action="register.php">
                    <div class="input-group floating-label">
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder=" " required 
                               value="<?= htmlspecialchars($reg_data['nama_lengkap'] ?? '') ?>">
                        <label for="nama_lengkap">Nama Lengkap</label>
                    </div>

                    <div class="input-group floating-label">
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <input type="email" id="email" name="email" placeholder=" " required
                               value="<?= htmlspecialchars($reg_data['email'] ?? '') ?>">
                        <label for="email">Email</label>
                    </div>
                    
                    <div class="input-group floating-label password-container">
                        <input type="password" id="password" name="password" placeholder=" " autocomplete="new-password" required>
                        <label for="password">Kata Sandi</label>
                        <span class="input-icon toggle-password" tabindex="0" role="button" aria-label="Toggle password visibility">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 9a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0-4.5c5 0 9.27 3.11 11 7.5c-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12c1.73-4.39 6-7.5 11-7.5M3.18 12a9.821 9.821 0 0 0 17.64 0a9.821 9.821 0 0 0-17.64 0"/></svg>
                            <svg id="eye-close" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M2 5.27L3.28 4L20 20.72L18.73 22l-3.08-3.08c-1.15.38-2.37.58-3.65.58c-5 0-9.27-3.11-11-7.5c.69-1.76 1.79-3.31 3.19-4.54zM12 9a3 3 0 0 1 3 3a3 3 0 0 1-.17 1L11 9.17A3 3 0 0 1 12 9m0-4.5c5 0 9.27 3.11 11 7.5a11.8 11.8 0 0 1-4 5.19l-1.42-1.43A9.86 9.86 0 0 0 20.82 12A9.82 9.82 0 0 0 12 6.5c-1.09 0-2.16.18-3.16.5L7.3 5.47c1.44-.62 3.03-.97 4.7-.97M3.18 12A9.82 9.82 0 0 0 12 17.5c.69 0 1.37-.07 2-.21L11.72 15A3.064 3.064 0 0 1 9 12.28L5.6 8.87c-.99.85-1.82 1.91-2.42 3.13"/></svg>     
                        </span>
                    </div>
                    
                    <button type="submit" class="register-btn">Daftar Akun</button>
                </form>

                <div class="login-link">
                    Sudah punya akun? <a href="login.php">Masuk di sini</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const errorMessage = "<?= $error_message ?>";
        const successRedirect = <?= $success_redirect ? 'true' : 'false' ?>;
        const redirectUrl = "<?= isset($redirect_url) ? $redirect_url : '' ?>";
        const tempEmail = "<?= $_SESSION['temp_email'] ?? '' ?>";
        <?php unset($_SESSION['temp_email']); ?>


        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Pendaftaran Gagal!',
                text: errorMessage,
                confirmButtonText: 'Coba Lagi'
            });
        }

        if (successRedirect) {
            Swal.fire({
                icon: 'info',
                title: 'Langkah Verifikasi',
                html: `Pendaftaran berhasil! Kami telah mengirimkan kode verifikasi 6 digit ke email <b>${tempEmail}</b>. Anda akan diarahkan ke halaman verifikasi.`, // Pesan diperjelas
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            });
        }
    </script>
</body>
</html>