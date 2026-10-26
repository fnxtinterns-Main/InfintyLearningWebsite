<?php
http_response_code(503);
header('Retry-After: 300');
$mFile = dirname(__DIR__, 2) . '/public_html/maintenance.html';
if (file_exists($mFile)) {
    readfile($mFile);
} elseif (file_exists(__DIR__ . '/../../public_html/maintenance.html')) {
    readfile(__DIR__ . '/../../public_html/maintenance.html');
} else {
    echo '<!DOCTYPE html><html><head><title>Maintenance Break — Infinity Learning</title></head><body style="font-family:sans-serif;text-align:center;padding:50px;"><h1>Maintenance Break</h1><p>We are currently updating our systems. Please check back shortly.</p></body></html>';
}
exit;
