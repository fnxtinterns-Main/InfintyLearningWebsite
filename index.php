<?php
declare(strict_types=1);

/**
 * Infinity Learning - Hostinger Deployment Bridge
 *
 * When Hostinger Git deploys the repository into public_html/, this entrypoint
 * seamlessly delegates execution to public_html/index.php, preventing 403 Forbidden
 * errors caused by nested directory structures.
 */

$target = __DIR__ . '/public_html/index.php';

if (file_exists($target)) {
    require $target;
} else {
    http_response_code(503);
    echo '<!DOCTYPE html><html><head><title>System Initializing</title></head><body style="font-family:sans-serif;text-align:center;padding:50px;"><h2>Infinity Learning</h2><p>Application is initializing. Please refresh in a moment.</p></body></html>';
}
