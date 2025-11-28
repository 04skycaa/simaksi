<?php
// Pengaturan PHP untuk keamanan sesi
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();
include '../config/config.php'; 

// Inisialisasi variabel error message dan success flag
$error_message = '';
$success_redirect_url = '';

// Cek Login dan Pengalihan (Redirect)
// Jika pengguna sudah login, alihkan dia ke dashboard admin/main.
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    // Pengalihan default jika sudah login (tidak diubah)
    // Sesuaikan ini jika perlu
}

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

        // 1. Permintaan ke Supabase Auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($authDataPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Eksekusi cURL
        $authResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch); // Menutup cURL handle di sini

        
        if ($curlError) {
            throw new Exception("Kesalahan koneksi: " . $curlError);
        }
        
        // Decode response dari Supabase Auth
        $authData = json_decode($authResponse, true);
        if ($httpCode !== 200 || !isset($authData['user']) || !isset($authData['access_token'])) {
            $errorMessage = $authData['error_description'] ?? $authData['msg'] ?? "Login Gagal. Cek kredensial Anda.";
            throw new Exception($errorMessage);
        }

        // 2. Ambil data profile user dari tabel profiles
        $user = $authData['user'];
        $session = $authData;
        $profileEndpoint = 'profiles?select=nama_lengkap,peran&id=eq.' . $user['id'];
        
        // Memastikan fungsi makeSupabaseRequest() tersedia.
        // Fungsi ini DIDEFINISIKAN agar dapat diakses, dan sekarang menerima $accessToken
        if (!function_exists('makeSupabaseRequest')) {
            // Fungsi makeSupabaseRequest sekarang menerima $accessToken sebagai argumen WAJIB
            function makeSupabaseRequest($endpoint, $method, $accessToken, $data = []) {
                global $supabaseUrl, $supabaseKey;
                $url = rtrim($supabaseUrl, '/') . '/rest/v1/' . $endpoint;
                
                $headers = [
                    'apikey: ' . $supabaseKey, 
                    // Perbaikan: Menggunakan $accessToken yang dilewatkan ke fungsi
                    'Authorization: Bearer ' . $accessToken, 
                    'Content-Type: application/json',
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                if ($method === 'POST' || $method === 'PATCH') {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                // Perbaikan: Menghilangkan curl_close() yang didepresiasi dari sini 
                // karena kita sudah menutupnya di luar fungsi di blok try/catch sebelumnya (ini tidak perlu di sini)
                // Atau lebih baik, kita tetap menutupnya di sini karena ini adalah fungsi generik
                curl_close($ch); 

                $data = json_decode($response, true);
                
                if ($httpCode >= 400 || $curlError) {
                    return ['error' => $data['message'] ?? $curlError ?? 'Unknown Supabase error', 'code' => $httpCode];
                }
                
                return ['data' => $data];
            }
        }
        
        // Perbaikan: Mengirim $session['access_token'] ke makeSupabaseRequest
        $profileResult = makeSupabaseRequest($profileEndpoint, 'GET', $session['access_token']);
        
        if (isset($profileResult['error'])) {
            $errorMessage = $profileResult['error'] ?? "Gagal mengambil data profil. Cek RLS atau Kunci API.";
            throw new Exception($errorMessage);
        }
        
        // 3. Simpan data session dan set URL pengalihan sukses
        $profileData = $profileResult['data'];
        if (is_array($profileData) && count($profileData) > 0) {
            $profile = $profileData[0];
            
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['email'] = $user['email']; 
            $_SESSION['username'] = $profile['nama_lengkap']; 
            $_SESSION['user_peran'] = $profile['peran'];
            $_SESSION['access_token'] = $session['access_token']; 
            $_SESSION['is_logged_in'] = true; 
            
            // Tentukan URL pengalihan
            if (strtolower($profile['peran']) === 'admin') {
                $success_redirect_url = '/simaksi/admin/index.php';
            } else {
                $success_redirect_url = '/simaksi/index.php';
            }
            // HENTIKAN pengalihan PHP di sini, dan biarkan SweetAlert2 & JS yang melakukannya
            
        } else {
            $error_message = "Data profile Anda tidak ditemukan. Hubungi admin.";
        }
    
    // Notifikasi error
    } catch (Exception $e) {
        $raw_message = $e->getMessage();
        if (str_contains($raw_message, 'Invalid login credentials') || str_contains($raw_message, 'invalid_grant') || 
        str_contains($raw_message, 'Email or password are not valid') || str_contains($raw_message, 'Login Gagal') || 
        str_contains($raw_message, 'Email atau Password yang Anda masukkan salah')) {
             $error_message = "Email atau Password yang Anda masukkan salah.";
        } elseif (str_contains($raw_message, 'Email not confirmed') || str_contains($raw_message, 'email not confirmed')) {
             $error_message = "Email Anda belum terverifikasi. Silakan cek inbox email Anda.";
        } else {
             $error_message = "Terjadi Kesalahan Login. Silakan coba lagi. (ERR: " . htmlspecialchars($raw_message) . ")"; 
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
    <!-- Sertakan SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>SELAMAT DATANG DI <br> GUNUNG BUTAK</h1>
            <p>Selamat datang di halaman informasi Gunung Butak. Semua yang kamu butuhkan untuk mengenal gunung ini tersedia di sini seperti panduan jalur, lokasi gunung, cuaca, serta informasi penting lainnya. Silakan login untuk membuka detail lengkap yang dapat membantu perjalananmu menjadi lebih aman, terarah, dan penuh pengalaman.</p>
        </div>

        <div class="right-section">
            <div class="login-box">
                <div class="logo">
                    <img src="../assets/images/logo1.png" alt="E-SIMAKSI Logo">
                </div>
                <h2>LOGIN</h2>
                <p>Yuk login sekarang, biar cerita pendakianmu di Butak resmi dimulai</p>
                
                <!-- Hapus div error box lama. Sekarang menggunakan SweetAlert2 -->
                
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

<?php if (!empty($error_message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: '<?php echo htmlspecialchars($error_message); ?>',
                confirmButtonText: 'Tutup',
                customClass: {
                    container: 'swal-wide-container',
                    popup: 'swal-wide-popup'
                }
            });
        });
    </script>
<?php elseif (!empty($success_redirect_url)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                html: 'Selamat datang, **<?php echo htmlspecialchars($_SESSION['username'] ?? 'Pengguna'); ?>**. Anda akan dialihkan...',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            }).then((result) => {
                // Redirect setelah SweetAlert2 selesai atau timer habis
                window.location.href = '<?php echo $success_redirect_url; ?>';
            });
        });
    </script>
<?php endif; ?>

</body>
</html>