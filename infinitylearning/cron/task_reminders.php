<?php
declare(strict_types=1);

/**
 * FutureNxt Automated Task Deadline Reminders (Runs via cron)
 * Scans tasks due within 6 hours and sends email reminders to assignees.
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/autoload.php';

use App\FutureNxt\EmailNotifier;

echo "[" . date('Y-m-d H:i:s') . "] Starting FutureNxt task deadline reminder check...\n";
$res = EmailNotifier::checkAndSendTaskDeadlineReminders();
echo "[" . date('Y-m-d H:i:s') . "] Processed " . count($res['tasks_processed']) . " task(s), sent " . $res['emails_sent'] . " reminder email(s).\n";
if (!empty($res['tasks_processed'])) {
    echo "Tasks: " . implode(', ', $res['tasks_processed']) . "\n";
}
echo "Done.\n";
