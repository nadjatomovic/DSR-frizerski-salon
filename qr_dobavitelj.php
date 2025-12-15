<?php
error_reporting(E_ERROR | E_PARSE); // sakrije deprecated/warnings
ini_set('display_errors', 0);

require_once __DIR__ . '/lib/phpqrcode/qrlib.php';

$url = $_GET['url'] ?? '';
$url = trim($url);

if ($url === '' || !preg_match('~^https?://~i', $url)) {
    http_response_code(400);
    exit;
}

header('Content-Type: image/png');
QRcode::png($url, false, QR_ECLEVEL_M, 6, 2);
