<?php
/**
 * Infinity Learning - BPSC TRE 4.0 Portal
 * Documentation (PDF) Download Handler
 */
$filePath = __DIR__ . '/BPSC_TRE_PORTAL_DOCUMENTATION.pdf';
$fileName = 'BPSC_TRE_PORTAL_DOCUMENTATION.pdf';

if (!file_exists($filePath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Documentation PDF file not found on server.";
    exit;
}

$fileSize = filesize($filePath);

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $fileSize);

if (ob_get_level()) {
    ob_end_clean();
}
readfile($filePath);
exit;
