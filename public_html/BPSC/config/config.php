<?php
/**
 * Infinity Learning - BPSC TRE 4.0 Ecosystem & Proctored CBT Engine
 * Core Application Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

date_default_timezone_set('Asia/Kolkata');

class AppConfig {
    public const APP_NAME = 'Infinity Learning — BPSC TRE 4.0 Ecosystem & Proctored CBT Engine';
    public const MAIN_WEBSITE_URL = 'https://infinitylearning.net';
    public const TARGET_EXAM_DATE = '2026-12-15 09:30:00';
}
