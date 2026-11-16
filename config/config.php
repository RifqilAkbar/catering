<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Konfigurasi Aplikasi
|--------------------------------------------------------------------------
| Ubah nilai di bawah ini sesuai server Anda. Default mengikuti XAMPP.
*/

ini_set('display_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

define('APP_NAME', 'Dapur Catering');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'dapur_catering');
define('DB_USER', 'root');
define('DB_PASS', '');

/*
| Base URL otomatis: mendukung aplikasi di document root maupun di subfolder
| (mis. http://localhost/dapur-mbak-khotim).
*/
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
$appRoot = realpath(__DIR__ . '/..');
$basePath = '';
if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
    $basePath = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
}
define('BASE_URL', rtrim($basePath, '/'));

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
