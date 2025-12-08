<?php
/**
 * Base URL Configuration for E-Simaksi Application
 * This file defines the base URL and paths for the application
 * to ensure proper linking when hosted in different environments.
 */

// Determine the base URL based on server variables
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Determine the base script path to create correct relative paths
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
if ($script_dir === DIRECTORY_SEPARATOR || $script_dir === '.') {
    $script_dir = '';
}
$base_dir = str_replace('\\', '/', $script_dir);

// Set the base URL for the application
define('BASE_URL', $protocol . '://' . $host . $base_dir);
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . $base_dir);

// Define relative paths from document root
define('ASSETS_PATH', rtrim(dirname($base_dir), '/') . '/assets');
define('CONFIG_PATH', rtrim(dirname($base_dir), '/') . '/config');
define('AUTH_PATH', rtrim(dirname($base_dir), '/') . '/auth');
define('ADMIN_PATH', rtrim(dirname($base_dir), '/') . '/admin');

// For file includes, we use relative paths from current directory
// Define these as functions to be used in includes
function asset_path($path) {
    return ASSETS_PATH . '/' . ltrim($path, '/');
}

function config_path($path) {
    return CONFIG_PATH . '/' . ltrim($path, '/');
}

function auth_path($path) {
    return AUTH_PATH . '/' . ltrim($path, '/');
}

function admin_path($path) {
    return ADMIN_PATH . '/' . ltrim($path, '/');
}

// Function to get relative path from current location to assets directory
function get_asset_relative_path() {
    // Determine the current script path and calculate how many levels up we need to go
    $script_path = $_SERVER['SCRIPT_NAME'];
    $path_parts = explode('/', trim($script_path, '/'));

    // If this is through a query parameter (e.g. admin/index.php?page=kuota_pendakian)
    // we need to determine the depth differently
    if (isset($_GET['page']) && strpos($script_path, '/admin/index.php') !== false) {
        // The actual page is specified by ?page parameter, which loads files like /admin/subfolder/page.php
        $page = $_GET['page'];

        // Count directory levels in the script path
        $depth = count($path_parts) - 1; // Exclude the filename (index.php)

        // We need to go up from admin/ (1 level) to root, then down to assets
        $relative_path = '../assets';
    } else {
        // Regular file access - count the directory depth
        $depth = count(array_filter($path_parts)); // Count non-empty path segments

        // Calculate how many levels up to go back to document root
        $relative_path = str_repeat('../', $depth - 1) . 'assets';
    }

    return $relative_path;
}
?>