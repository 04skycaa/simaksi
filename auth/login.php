<?php
error_reporting(E_ALL & ~E_DEPRECATED); 

session_start();
include '../config/config.php'; 
$error_message = '';

// ini untuk fungsi login 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = $_POST['username'];
    $password = $_POST['password'];
    $auth_url = rtrim($supabaseUrl, '/') . '/auth/v1/token?grant_type=password'; 
    try {
        $authHeaders = [
            'Content-Type: application/json',
            'apikey: ' . $supabaseKey, 
        ];
        
        $authDataPayload = [
            'email' => $email,
            'password' => $password
        ];

        // ini untuk request ke supabase auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($authDataPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // ini untuk eksekusi curl
        $authResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch); 
        if ($curlError) {
            throw new Exception("Kesalahan koneksi: " . $curlError);
        }
        
        // ini untuk decode response dari supabase auth
        $authData = json_decode($authResponse, true);
        if ($httpCode !== 200 || !isset($authData['user']) || !isset($authData['access_token'])) {
            $errorMessage = $authData['error_description'] ?? $authData['msg'] ?? "Login Gagal. Cek kredensial Anda.";
            throw new Exception($errorMessage);
        }

        // ambil data profile user dari tabel profiles
        $user = $authData['user'];
        $session = $authData;
        $profileEndpoint = 'profiles?select=nama_lengkap,peran&id=eq.' . $user['id'];
        $profileResult = makeSupabaseRequest($profileEndpoint, 'GET');
        if (isset($profileResult['error'])) {
            $errorMessage = $profileResult['error'] ?? "Gagal mengambil data profil. Cek RLS atau Kunci API.";
            throw new Exception($errorMessage);
        }
        
        // ini untuk menyimpan data session
        $profileData = $profileResult['data'];
        if (is_array($profileData) && count($profileData) > 0) {
            $profile = $profileData[0];
            $_SESSION['user_id']    = $user['id']; 
            $_SESSION['email']      = $user['email']; 
            $_SESSION['username']   = $profile['nama_lengkap']; 
            $_SESSION['user_peran'] = $profile['peran'];
            $_SESSION['access_token'] = $session['access_token']; 
            
            if (strtolower($profile['peran']) === 'admin') {
                header('Location: /simaksi/admin/index.php');
                exit;
            } else {
                header('Location: /simaksi/index.php');
                exit;
            }
        } else {
            $error_message = "Data profile Anda tidak ditemukan. Hubungi admin.";
        }
    
    // ini untuk notifikasi error
    } catch (Exception $e) {
        $raw_message = $e->getMessage();
        if (str_contains($raw_message, 'Invalid login credentials') || str_contains($raw_message, 'invalid_grant') || 
        str_contains($raw_message, 'Email or password are not valid') || str_contains($raw_message, 'Login Gagal') || 
        str_contains($raw_message, 'Email atau Password yang Anda masukkan salah')) {
             $error_message = "Email atau Password yang Anda masukkan salah.";
        } elseif (str_contains($raw_message, 'Email not confirmed') || str_contains($raw_message, 'email not confirmed')) {
             $error_message = "Email Anda belum terverifikasi. Silakan cek inbox email Anda.";
        } else {
             $error_message = "Terjadi Kesalahan Login. Silakan coba lagi. (DEBUG: " . $raw_message . ")"; 
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-SIMAKSI - Login</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>SELAMAT DATANG DI <br> GUNUNG BUTAK</h1>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestiae eum optio debitis fugiat ad, suscipit tenetur totam labore possimus beatae itaque accusantium soluta libero quos recusandae obcaecati voluptatum temporibus enim?</p>
        </div>

        <div class="right-section">
            <div class="login-box">
                <div class="logo">
                    <img src="../assets/images/logo1.png" alt="E-SIMAKSI Logo">
                </div>
                <h2>LOGIN</h2>
                <p>Yuk login sekarang, biar cerita pendakianmu di Butak resmi dimulai</p>
                
                <?php if (!empty($error_message)): ?>
                <div class="error-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <div class="input-group floating-label">
                        <input type="text" name="username" id="username" required placeholder=" ">
                        <label for="username">Email</label>
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><circle cx="12" cy="6" r="4" fill="currentColor"/><path fill="currentColor" d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5"/>
                        </svg>
                    </div>

                    <div class="input-group floating-label">
                        <input type="password" id="password" name="password" placeholder=" " autocomplete="new-password" required>
                        <label for="password">Password</label>
                        <span class="input-icon toggle-password" tabindex="0" role="button" aria-label="Toggle password visibility">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 9a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0-4.5c5 0 9.27 3.11 11 7.5c-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12c1.73-4.39 6-7.5 11-7.5M3.18 12a9.821 9.821 0 0 0 17.64 0a9.821 9.821 0 0 0-17.64 0"/></svg>
                            <svg id="eye-close" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M2 5.27L3.28 4L20 20.72L18.73 22l-3.08-3.08c-1.15.38-2.37.58-3.65.58c-5 0-9.27-3.11-11-7.5c.69-1.76 1.79-3.31 3.19-4.54zM12 9a3 3 0 0 1 3 3a3 3 0 0 1-.17 1L11 9.17A3 3 0 0 1 12 9m0-4.5c5 0 9.27 3.11 11 7.5a11.8 11.8 0 0 1-4 5.19l-1.42-1.43A9.86 9.86 0 0 0 20.82 12A9.82 9.82 0 0 0 12 6.5c-1.09 0-2.16.18-3.16.5L7.3 5.47c1.44-.62 3.03-.97 4.7-.97M3.18 12A9.82 9.82 0 0 0 12 17.5c.69 0 1.37-.07 2-.21L11.72 15A3.064 3.064 0 0 1 9 12.28L5.6 8.87c-.99.85-1.82 1.91-2.42 3.13"/></svg>     
                        </span>
                    </div>

                    <div class="remember-forgot">
                        <label><input type="checkbox" name="remember"> Ingat saya</label>
                        <a href="forgot_password.php">Lupa password?</a>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <p class="register-link">Belum punya akun? <a href="../auth/register.php">Register</a></p>
            </div>
        </div>
    </div>

<script src="../assets/js/auth.js"></script>  
</body>
</html>