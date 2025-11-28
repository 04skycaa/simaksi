<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();

include '../config/config.php'; 

$message_type = ''; 
$message = '';
$ch = null; 

if (isset($_SESSION['message_type']) && isset($_SESSION['message'])) {
    $message_type = $_SESSION['message_type'];
    $message = $_SESSION['message'];
    unset($_SESSION['message_type']);
    unset($_SESSION['message']);
}

$stage = $_POST['stage'] ?? (isset($_SESSION['reset_email']) ? 'verify_and_reset' : 'send_email');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($stage === 'send_email') {
        $email = $_POST['email'] ?? '';
        $action = $_POST['action'] ?? ''; 
         
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message_type = 'error';
            $message = 'Mohon masukkan alamat email yang valid.';
        } else { 
            $reset_url = rtrim($supabaseUrl, '/') . '/auth/v1/recover'; 
            
            try {
                $headers = [
                    'Content-Type: application/json',
                    'apikey: ' . $supabaseKey, 
                ];
                
                $payload = [
                    'email' => $email
                ];

                $ch = curl_init(); 
                
                $curl_headers = [];
                foreach ($headers as $header) {
                    $curl_headers[] = $header;
                }

                curl_setopt($ch, CURLOPT_URL, $reset_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                
                if ($curlError) {
                    throw new Exception("Kesalahan koneksi cURL: " . $curlError);
                }
                 
                if ($httpCode === 200 || $httpCode === 204) { 
                    $_SESSION['reset_email'] = $email; 
                    $_SESSION['message_type'] = 'success';
                    
                    if ($action === 'resend') {
                       $_SESSION['message'] = 'Kode baru telah berhasil dikirimkan ke **' . htmlspecialchars($email) . '**.';
                    } else {
                       $_SESSION['message'] = 'Kode 6-digit telah dikirimkan ke **' . htmlspecialchars($email) . '**. Silakan masukkan kode dan password baru Anda di bawah.';
                    }
                     
                    header("Location: forgot_password.php");
                    exit();
                } else {
                    $errorData = json_decode($response, true);
                    $errorMessage = $errorData['msg'] ?? $errorData['error_description'] ?? 'Gagal memproses permintaan OTP. (Kode HTTP: ' . $httpCode . ')';
                    throw new Exception($errorMessage);
                }
            
            } catch (Exception $e) { 
                $message_type = 'error';
                $message = 'Terjadi kesalahan: ' . $e->getMessage(); 
                $stage = 'send_email';
            } finally {
                $ch = null; 
            }
        }
    } 
     
    elseif ($stage === 'verify_and_reset') {
        $email = $_SESSION['reset_email'] ?? $_POST['email_hidden'] ?? ''; 
        $token = $_POST['token'] ?? ''; 
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $ch_verify = null; 
        $ch_update = null; 

        if (empty($email) || empty($token) || empty($new_password) || $new_password !== $confirm_password || strlen($new_password) < 6) {
             $message_type = 'error';
             $message = 'Pastikan semua kolom terisi, password baru minimal 6 karakter, dan konfirmasi password cocok.';
             $stage = 'verify_and_reset'; 
        } else {
            try { 
                $verify_url = rtrim($supabaseUrl, '/') . '/auth/v1/verify';
                $payload_verify = [
                    'email' => $email,
                    'token' => $token, 
                    'type' => 'recovery' 
                ];

                $ch_verify = curl_init();
                curl_setopt($ch_verify, CURLOPT_URL, $verify_url);
                curl_setopt($ch_verify, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch_verify, CURLOPT_POSTFIELDS, json_encode($payload_verify));
                curl_setopt($ch_verify, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'apikey: ' . $supabaseKey,
                ]);
                curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, true);
                $verify_response = curl_exec($ch_verify);
                $verify_httpCode = curl_getinfo($ch_verify, CURLINFO_HTTP_CODE);

                if ($verify_httpCode !== 200) {
                    $errorData = json_decode($verify_response, true);
                    $errorMessage = $errorData['msg'] ?? 'Kode pemulihan salah, telah kedaluwarsa, atau terjadi kesalahan.';
                    throw new Exception($errorMessage);
                }

                $verify_data = json_decode($verify_response, true);
                $access_token = $verify_data['access_token'] ?? null;

                if (!$access_token) {
                      throw new Exception("Verifikasi berhasil, tetapi gagal mendapatkan sesi. Silakan coba kirim ulang kode.");
                }
 
                $update_url = rtrim($supabaseUrl, '/') . '/auth/v1/user';
                $update_payload = ['password' => $new_password];
                
                $ch_update = curl_init();
                curl_setopt($ch_update, CURLOPT_URL, $update_url);
                curl_setopt($ch_update, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch_update, CURLOPT_POSTFIELDS, json_encode($update_payload));
                curl_setopt($ch_update, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'apikey: ' . $supabaseKey, 
                    'Authorization: Bearer ' . $access_token 
                ]);
                curl_setopt($ch_update, CURLOPT_RETURNTRANSFER, true);
                $update_response = curl_exec($ch_update);
                $update_httpCode = curl_getinfo($ch_update, CURLINFO_HTTP_CODE);

                if ($update_httpCode !== 200) {
                    $errorData = json_decode($update_response, true);
                    $errorMessage = $errorData['msg'] ?? 'Gagal mengatur ulang password. Silakan coba lagi.';
                    throw new Exception($errorMessage);
                }
                 
                $message_type = 'success';
                $message = 'Password Anda berhasil diubah! Anda akan dialihkan ke halaman Login.';
                 
                unset($_SESSION['reset_email']); 
                
            } catch (Exception $e) {
                $message_type = 'error';
                $message = 'Terjadi kesalahan: ' . $e->getMessage(); 
                $stage = 'verify_and_reset'; 
            } finally {
                $ch_verify = null;
                $ch_update = null;
            }
        }
    }
} else { 
    if (isset($_GET['clear_session']) && $_GET['clear_session'] === 'true') {
        unset($_SESSION['reset_email']);
    }
    
    if (!isset($_SESSION['reset_email'])) {
        $stage = 'send_email';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-SIMAKSI - Lupa Password</title>
    <link rel="stylesheet" href="../assets/css/auth.css"> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style> 
        .password-toggle-group {
            position: relative; 
        } 
        .password-toggle-group .toggle-password {
            position: absolute;
            right: 15px; 
            top: 50%; 
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        } 
        .password-toggle-group .toggle-password .eye-icon {
            position: absolute;
            transition: opacity 0.2s;
        } 
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="container"> 
        <div class="left-section">
            <h1>SELAMAT DATANG DI <br> GUNUNG BUTAK</h1>
            <p>Selamat datang di halaman login Gunung Butak. Semua yang kamu butuhkan untuk mengenal gunung ini tersedia di sini seperti panduan jalur, lokasi gunung, cuaca, serta informasi penting lainnya. Silakan login untuk membuka detail lengkap yang dapat membantu perjalananmu menjadi lebih aman, terarah, dan penuh pengalaman.</p>
        </div>
 
        <div class="right-section">
            <div class="login-box">
                <div class="logo"> 
                    <img src="../assets/images/logo1.png" alt="E-SIMAKSI Logo">
                </div>
                <h2>ATUR ULANG PASSWORD</h2>
                
                <?php if ($stage === 'send_email'): ?> 
                    <p>Masukkan email Anda untuk menerima kode 6-digit pemulihan:</p>
                    <form action="forgot_password.php" method="POST">
                        <input type="hidden" name="stage" value="send_email">
                        <div class="input-group floating-label">
                            <input type="email" name="email" id="email" required placeholder=" ">
                            <label for="email">Email Terdaftar</label>
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z"/></svg>
                        </div>
                        <button type="submit" class="login-btn">Kirim Kode Pemulihan</button>
                    </form>

                <?php elseif ($stage === 'verify_and_reset'): ?> 
                    <p>Kode telah dikirim ke **<?php echo htmlspecialchars($_SESSION['reset_email'] ?? 'email Anda'); ?>**. Masukkan kode dan password baru:</p>
                    <form action="forgot_password.php" method="POST">
                        <input type="hidden" name="stage" value="verify_and_reset">
                        <input type="hidden" name="email_hidden" value="<?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?>">
 
                        <div class="input-group floating-label">
                            <input type="text" name="token" id="token" required placeholder=" " maxlength="6" pattern="\d{6}">
                            <label for="token">Kode 6-Digit (OTP)</label>
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 17c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm10-5c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2s10 4.48 10 10zm-2 0c0-4.42-3.58-8-8-8S4 7.58 4 12s3.58 8 8 8 8-3.58 8-8zm-6-3h-4c-.55 0-1 .45-1 1s.45 1 1 1h4c.55 0 1-.45 1-1s-.45-1-1-1z"/></svg>
                        </div>
 
                        <div class="input-group floating-label password-toggle-group">
                            <input type="password" name="new_password" id="new_password" required placeholder=" " minlength="6">
                            <label for="new_password">Password Baru (min 6 karakter)</label>
                            <span class="input-icon toggle-password" data-target="new_password" tabindex="0" role="button" aria-label="Toggle password visibility">
                                <svg class="eye-icon open-eye hidden" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 9a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0-4.5c5 0 9.27 3.11 11 7.5c-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12c1.73-4.39 6-7.5 11-7.5M3.18 12a9.821 9.821 0 0 0 17.64 0a9.821 9.821 0 0 0-17.64 0"/></svg>
                                <svg class="eye-icon closed-eye" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M2 5.27L3.28 4L20 20.72L18.73 22l-3.08-3.08c-1.15.38-2.37.58-3.65.58c-5 0-9.27-3.11-11-7.5c.69-1.76 1.79-3.31 3.19-4.54zM12 9a3 3 0 0 1 3 3a3 3 0 0 1-.17 1L11 9.17A3 3 0 0 1 12 9m0-4.5c5 0 9.27 3.11 11 7.5a11.8 11.8 0 0 1-4 5.19l-1.42-1.43A9.86 9.86 0 0 0 20.82 12A9.82 9.82 0 0 0 12 6.5c-1.09 0-2.16.18-3.16.5L7.3 5.47c1.44-.62 3.03-.97 4.7-.97M3.18 12A9.82 9.82 0 0 0 12 17.5c.69 0 1.37-.07 2-.21L11.72 15A3.064 3.064 0 0 1 9 12.28L5.6 8.87c-.99.85-1.82 1.91-2.42 3.13"/></svg>
                            </span>
                        </div>
                         
                        <div class="input-group floating-label password-toggle-group">
                            <input type="password" name="confirm_password" id="confirm_password" required placeholder=" " minlength="6">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <span class="input-icon toggle-password" data-target="confirm_password" tabindex="0" role="button" aria-label="Toggle password visibility">
                                <svg class="eye-icon open-eye hidden" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 9a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0-4.5c5 0 9.27 3.11 11 7.5c-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12c1.73-4.39 6-7.5 11-7.5M3.18 12a9.821 9.821 0 0 0 17.64 0a9.821 9.821 0 0 0-17.64 0"/></svg>
                                <svg class="eye-icon closed-eye" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M2 5.27L3.28 4L20 20.72L18.73 22l-3.08-3.08c-1.15.38-2.37.58-3.65.58c-5 0-9.27-3.11-11-7.5c.69-1.76 1.79-3.31 3.19-4.54zM12 9a3 3 0 0 1 3 3a3 3 0 0 1-.17 1L11 9.17A3 3 0 0 1 12 9m0-4.5c5 0 9.27 3.11 11 7.5a11.8 11.8 0 0 1-4 5.19l-1.42-1.43A9.86 9.86 0 0 0 20.82 12A9.82 9.82 0 0 0 12 6.5c-1.09 0-2.16.18-3.16.5L7.3 5.47c1.44-.62 3.03-.97 4.7-.97M3.18 12A9.82 9.82 0 0 0 12 17.5c.69 0 1.37-.07 2-.21L11.72 15A3.064 3.064 0 0 1 9 12.28L5.6 8.87c-.99.85-1.82 1.91-2.42 3.13"/></svg>
                            </span>
                        </div>

                        <button type="submit" class="login-btn">Atur Ulang Password</button>
                    </form>

                    <p class="resend-link">
                        Tidak menerima kode? 
                        <a href="#" onclick="document.getElementById('resend-form').submit(); return false;">Kirim Ulang Kode</a>
                    </p>
                    <form id="resend-form" action="forgot_password.php" method="POST" style="display: none;">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?>">
                        <input type="hidden" name="stage" value="send_email">
                        <input type="hidden" name="action" value="resend">
                    </form>

                <?php endif; ?>

                <p class="register-link"><a href="login.php">Kembali ke Halaman Login</a></p>
            </div>
        </div>
    </div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- LOGIKA TOGGLE PASSWORD ---
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const openEye = this.querySelector('.open-eye');
                const closedEye = this.querySelector('.closed-eye');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    openEye.classList.remove('hidden'); 
                    closedEye.classList.add('hidden');    
                } else {
                    passwordInput.type = 'password';
                    openEye.classList.add('hidden');    
                    closedEye.classList.remove('hidden'); 
                }
            });
        });

        <?php 
        if (!empty($message)): 
            $swal_message = nl2br(htmlspecialchars($message)); 
            $is_stage2_success = ($message_type === 'success' && str_contains($message, 'Password Anda berhasil diubah!')); 
            
            $swal_config = [
                'icon' => $message_type,
                'title' => ($message_type === 'success') ? 'Permintaan Berhasil!' : 'Terjadi Kesalahan',
                'confirmButtonText' => 'Tutup',
                'html' => $swal_message, 
                'customClass' => [
                    'container' => 'swal-wide-container',
                    'popup' => 'swal-wide-popup'
                ]
            ];
            
            if ($is_stage2_success) { 
                $swal_config['timer'] = 3000;
                $swal_config['timerProgressBar'] = true;
                $swal_config['showConfirmButton'] = false;
            }
        ?> 
            Swal.fire(<?php echo json_encode($swal_config); ?>)
            <?php if ($is_stage2_success): ?>
            .then(() => { 
                window.location.href = 'login.php';
            });
            <?php endif; ?>

        <?php endif; ?>
    });
</script>
</body>
</html>