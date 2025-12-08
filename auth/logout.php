<?php
session_start();
session_unset();    // hapus semua isi session
session_destroy();  // hancurkan session

// Check if there's a redirect parameter, otherwise default to login
$redirect = $_GET['redirect'] ?? 'login';
if ($redirect === 'index') {
    $location = '../index.php';
} else {
    $location = '../auth/login.php';
}

header("Location: $location");
exit;
