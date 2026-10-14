<?php
declare(strict_types=1);

use App\Env;

return [
    'app' => [
        'name'      => Env::get('APP_NAME', 'Infinity Learning'),
        'tagline'   => Env::get('APP_TAGLINE', 'Learn. Grow. Achieve — Infinitely.'),
        'url'       => Env::get('APP_URL', 'https://infintylearning.net'),
        'env'       => Env::get('APP_ENV', 'production'),
        'debug'     => Env::get('APP_DEBUG', false),
        'key'       => Env::get('APP_KEY', 'default-random-secret-key-32ch'),
        'timezone'  => Env::get('APP_TIMEZONE', 'Asia/Kolkata'),
    ],
    // Database (P1: Supports ProxySQL on port 6033 and Read/Write replica splitting)
    'db' => [
        'host'         => Env::get('DB_HOST', 'localhost'),
        'port'         => (string)Env::get('DB_PORT', '3306'),
        'name'         => Env::get('DB_NAME', 'u999011396_inflearning'),
        'user'         => Env::get('DB_USER', 'u999011396_Inflearning'),
        'pass'         => Env::get('DB_PASS', ''),
        'charset'      => Env::get('DB_CHARSET', 'utf8mb4'),
        'lock_timeout' => (int)Env::get('DB_LOCK_TIMEOUT', 5),
        // Optional Read Replicas (for P1 database splitting):
        // 'read' => [
        //     ['host' => Env::get('DB_READ_HOST'), 'port' => '3306'],
        // ],
    ],
    // Redis (P0: In-Memory Object Cache & Multi-User Stateless Sessions)
    'redis' => [
        'enabled'    => (bool)Env::get('REDIS_ENABLED', false),
        'sessions'   => (bool)Env::get('REDIS_SESSIONS', false),
        'host'       => Env::get('REDIS_HOST', '127.0.0.1'),
        'port'       => (int)Env::get('REDIS_PORT', 6379),
        'password'   => Env::get('REDIS_PASSWORD', ''),
        'rest_url'   => Env::get('UPSTASH_REDIS_REST_URL', ''),
        'rest_token' => Env::get('UPSTASH_REDIS_REST_TOKEN', ''),
        'timeout'    => (float)Env::get('REDIS_TIMEOUT', 1.5),
        'prefix'     => Env::get('REDIS_PREFIX', 'il:'),
        'db'         => 0,
    ],
    // CDN (P0: Cloudflare & Hostinger Edge Caching)
    'cdn' => [
        'enabled' => (bool)Env::get('CDN_ENABLED', true),
        'url'     => Env::get('CDN_URL', ''),
    ],
    'otp' => [
        'length' => 6, 'ttl_minutes' => 10, 'max_attempts' => 5, 'resend_wait' => 60,
    ],
    'session' => [ 'name' => 'il_session', 'lifetime' => 7200 ],
    'security' => [
        'password_min_length' => 8,
        'login_max_attempts'  => 5,
        'login_lockout_min'   => 15,
    ],
    'smtp' => [
        'host'      => Env::get('SMTP_HOST', 'smtp.hostinger.com'),
        'port'      => (int)Env::get('SMTP_PORT', 587),
        'user'      => Env::get('SMTP_USER', ''),
        'pass'      => Env::get('SMTP_PASS', ''),
        'from'      => Env::get('SMTP_FROM', 'info@infintylearning.net'),
        'from_name' => Env::get('SMTP_FROM_NAME', 'Infinity Learning'),
    ],
    // AI agent endpoint for smart questions & tutoring
    'ai_agent' => [
        'url'     => Env::get('AI_AGENT_URL', ''),
        'api_key' => Env::get('AI_AGENT_KEY', ''),
    ],
    'uploads' => [
        'max_image_mb'    => 4,
        'max_pdf_mb'      => 10,
        'max_word_mb'     => 10,
        'max_excel_mb'    => 10,
        'max_ppt_mb'      => 15,
        'max_document_mb' => 15,
        'max_lesson_total_mb' => 40,
        'max_lesson_files'    => 10,
        'allowed_mimes' => [
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        ],
        'mime_bucket' => [
            'jpg' => 'image','jpeg' => 'image','png' => 'image','webp' => 'image','gif' => 'image',
            'pdf' => 'pdf',
            'doc' => 'word','docx' => 'word',
            'xls' => 'excel','xlsx' => 'excel',
            'ppt' => 'ppt','pptx' => 'ppt',
        ],
        'image_extensions'  => ['jpg','jpeg','png','webp','gif'],
        'inline_extensions' => ['jpg','jpeg','png','webp','gif','pdf'],
    ],
];