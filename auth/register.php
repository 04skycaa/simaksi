<?php
error_reporting(E_ALL & ~E_DEPRECATED); 
session_start();
// Ubah dari database.php ke config.php untuk Supabase
include '../config/config.php'; 

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil Data dari Form
    $nama_lengkap = $_POST['username']; // Di form Anda menggunakan 'username' untuk nama lengkap
    $email = $_POST['email'];
    $password = $_POST['password'];
    $peran = 'pendaki'; // Peran default

    // Endpoint untuk pendaftaran (Sign Up) di Supabase Auth
    $auth_url = rtrim($supabaseUrl, '/') . '/auth/v1/signup'; 
    
    try {
        // Headers untuk Auth API (Hanya memerlukan apikey, Content-Type, dan Authorization Bearer)
        // Kita gunakan service role key untuk sign up agar tidak perlu verifikasi email,
        // namun biasanya 'anon key' dan 'Authorization: Bearer [anon key]' sudah cukup.
        // Karena config.php hanya menyediakan $supabaseKey (anon) dan $serviceRoleKey (service role), 
        // kita akan menggunakan $supabaseKey untuk Auth jika ingin verifikasi email.
        // Jika Anda ingin mengabaikan verifikasi email, gunakan $serviceRoleKey dengan 'Authorization: Bearer [serviceRoleKey]'.
        
        // Asumsi: Kita menggunakan anon key ($supabaseKey) untuk pendaftaran standar dengan konfirmasi email.
        $authHeaders = [
            'Content-Type: application/json',
            'apikey: ' . $supabaseKey, 
            'Authorization: Bearer ' . $supabaseKey, 
        ];
        
        $authDataPayload = [
            'email' => $email,
            'password' => $password,
            // Opsional: Tambahkan metadata pengguna jika diperlukan
            'data' => [
                'nama_lengkap_temp' => $nama_lengkap
            ]
        ];

        // 2. Kirim Permintaan Pendaftaran ke Supabase Auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($authDataPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ganti ke true di produksi!
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $authResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("Kesalahan koneksi: " . $curlError);
        }
        
        $authData = json_decode($authResponse, true);

        // 3. Cek Respons Auth
        if ($httpCode >= 400 || !isset($authData['user'])) {
            $errorMessage = $authData['msg'] ?? $authData['message'] ?? "Pendaftaran Gagal. Silakan coba lagi.";
            if (str_contains($errorMessage, 'User already registered')) {
                $errorMessage = "Email sudah terdaftar! Silakan gunakan email lain.";
            }
            throw new Exception($errorMessage);
        }
        
        $user = $authData['user'];
        $user_id = $user['id'];

        // 4. Masukkan Data Profil ke Tabel 'profiles' (Gunakan makeSupabaseRequest)
        // Pastikan Anda memiliki tabel 'profiles' di Supabase yang terhubung ke 'auth.users' melalui kolom 'id'
        // dan memiliki RLS yang mengizinkan INSERT.
        
        // Data yang akan dimasukkan ke tabel 'profiles'
        $profileData = [
            'id' => $user_id, // ID Auth pengguna
            'nama_lengkap' => $nama_lengkap,
            'email' => $email,
            'peran' => $peran,
            // 'dibuat_pada' tidak perlu jika ada default value/timestamp di tabel
        ];

        $profileEndpoint = 'profiles'; // Nama tabel Anda di Supabase
        // PENTING: Gunakan Service Role Key di makeSupabaseRequest untuk melewati RLS
        // Jika Anda ingin menggunakan Anon key, pastikan RLS diaktifkan dengan benar.
        $profileResult = makeSupabaseRequest($profileEndpoint, 'POST', $profileData);
        
        if (isset($profileResult['error'])) {
             // Jika gagal buat profil, hapus akun Auth (opsional tapi disarankan)
             // ... Tambahkan logika penghapusan Auth jika diperlukan (memerlukan admin key) ...
             
             $errorMessage = $profileResult['error'] ?? "Pendaftaran berhasil, tetapi gagal menyimpan data profil. Hubungi admin.";
             throw new Exception($errorMessage);
        }
        
        // 5. Sukses
        $_SESSION['register_status'] = 'success';
        $_SESSION['register_message'] = "Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi (jika diaktifkan) dan login.";
        header('Location: ../auth/login.php');
        exit;

    } catch (Exception $e) {
        // 6. Gagal
        $_SESSION['register_status'] = 'error';
        $_SESSION['register_message'] = $e->getMessage();
        header('Location: ../auth/register.php'); 
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-SIMAKSI - Register</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>SELAMAT DATANG DI <br> GUNUNG BUTAK</h1>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestiae eum optio debitis fugiat ad, suscipit tenetur totam labore possimus beatae itaque accusantium soluta libero quos recusandae obcaecati voluptatum temporibus enim?</p>

        </div>

        <div class="right-section">
            <div class="register-box">
                <div class="logo">
                    <img src="../assets/images/logo1.png" alt="E-SIMAKSI Logo">
                </div>
                <h2>REGISTER</h2>
                <p>Buat akun baru untuk melanjutkan cerita pendakianmu</p>
                
                <?php if (isset($_SESSION['register_status']) && $_SESSION['register_status'] === 'error'): ?>
                <div class="error-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    <span><?php echo htmlspecialchars($_SESSION['register_message']); ?></span>
                </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="input-group floating-label">
                        <input type="text" name="username" id="username" required placeholder=" ">
                        <label for="username">Nama Lengkap</label> <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><circle cx="12" cy="6" r="4" fill="currentColor"/><path fill="currentColor" d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5"/></svg>
                    </div>

                    <div class="input-group floating-label">
                        <input type="email" name="email" id="email" required placeholder=" ">
                        <label for="email">Email</label>
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2m0 4l-8 5l-8-5V6l8 5l8-5v2z"/></svg>
                    </div>

                    <div class="input-group floating-label">
                        <input type="password" id="password" name="password" placeholder=" " autocomplete="new-password" required>
                        <label for="password">Password</label>
                        <span class="input-icon toggle-password" tabindex="0" role="button" aria-label="Toggle password visibility">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 9a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0-4.5c5 0 9.27 3.11 11 7.5c-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12c1.73-4.39 6-7.5 11-7.5M3.18 12a9.821 9.821 0 0 0 17.64 0a9.821 9.821 0 0 0-17.64 0"/></svg>
                            <svg id="eye-close" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M2 5.27L3.28 4L20 20.72L18.73 22l-3.08-3.08c-1.15.38-2.37.58-3.65.58c-5 0-9.27-3.11-11-7.5c.69-1.76 1.79-3.31 3.19-4.54zM12 9a3 3 0 0 1 3 3a3 3 0 0 1-.17 1L11 9.17A3 3 0 0 1 12 9m0-4.5c5 0 9.27 3.11 11 7.5a11.8 11.8 0 0 1-4 5.19l-1.42-1.43A9.86 9.86 0 0 0 20.82 12A9.82 9.82 0 0 0 12 6.5c-1.09 0-2.16.18-3.16.5L7.3 5.47c1.44-.62 3.03-.97 4.7-.97M3.18 12A9.82 9.82 0 0 0 12 17.5c.69 0 1.37-.07 2-.21L11.72 15A3.064 3.064 0 0 1 9 12.28L5.6 8.87c-.99.85-1.82 1.91-2.42 3.13"/></svg>     
                        </span>
                    </div>

                    <button type="submit" class="register-btn">Register</button>
                </form>

                <p class="login-link">Sudah punya akun? <a href="../auth/login.php">login</a></p>
            </div>
        </div>
    </div>
    
    <div id="status-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modal-title"></h2>
        <p id="modal-message"></p>
    </div>
    </div>

<script>
    // Hanya menampilkan alert untuk status success/error, karena elemen error-box sudah ditambahkan
    const registerStatus = "<?php echo isset($_SESSION['register_status']) ? $_SESSION['register_status'] : ''; ?>";
    const registerMessage = "<?php echo isset($_SESSION['register_message']) ? $_SESSION['register_message'] : ''; ?>";

    // Hapus alert jika statusnya error, karena sudah ada error-box
    if (registerStatus === 'success' && registerMessage) {
        alert(registerMessage); 
    }
</script>
<?php
// Hapus status session setelah ditampilkan
unset($_SESSION['register_status']);
unset($_SESSION['register_message']);
?>

<script src="../assets/js/auth.js"></script>

</body>
</html>