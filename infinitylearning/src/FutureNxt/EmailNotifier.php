<?php
declare(strict_types=1);

namespace App\FutureNxt;

use App\Mailer;
use PDO;

if (!class_exists('\\App\\Mailer')) {
    $mPath = (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2)) . '/src/Mailer.php';
    if (file_exists($mPath)) {
        require_once $mPath;
    }
}

final class EmailNotifier
{
    /**
     * Dedicated FutureNxt SMTP Configuration (fnxt.admin@gmail.com)
     */
    public static function getSmtpConfig(): array
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $config = require $base . '/config/config.php';
        return $config['futurenxt_smtp'] ?? [
            'host'      => 'smtp.gmail.com',
            'port'      => 587,
            'user'      => 'fnxt.admin@gmail.com',
            'pass'      => 'Futurenxt@99',
            'from'      => 'fnxt.admin@gmail.com',
            'from_name' => 'FutureNxt Operations',
        ];
    }

    /**
     * Send OTP email to employee when Admin initiates password reset.
     * Sent strictly from fnxt.admin@gmail.com
     */
    public static function sendPasswordResetOtp(int $employeeId, string $otp): bool
    {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT full_name, email, emp_code FROM fnx_employees WHERE id = ? LIMIT 1");
            $stmt->execute([$employeeId]);
            $emp = $stmt->fetch();
            if (!$emp || empty($emp['email'])) {
                return false;
            }

            $subject = "FutureNxt Account Verification: Your 6-Digit Password Reset OTP";

            $html = "
                <div style=\"font-family:'Plus Jakarta Sans',Arial,sans-serif; max-width:600px; margin:0 auto; background:#FFF9F4; padding:32px; border-radius:14px; border:1.5px solid #F3DECB;\">
                    <div style=\"display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;\">
                        <span style=\"background:#FF7A18; color:#FFF; font-size:12px; font-weight:800; padding:5px 12px; border-radius:6px; letter-spacing:0.04em; text-transform:uppercase;\">FutureNxt Security</span>
                        <span style=\"font-size:12px; color:#6B7280;\">" . date('d M Y, h:i A') . " IST</span>
                    </div>

                    <h2 style=\"margin:0 0 12px; color:#111827; font-size:22px; font-weight:800;\">Password Reset Verification</h2>
                    
                    <p style=\"font-size:14px; color:#374151; line-height:1.6; margin:0 0 14px;\">
                        Hello <strong>" . htmlspecialchars($emp['full_name']) . "</strong> (" . htmlspecialchars($emp['emp_code']) . "),
                    </p>
                    
                    <p style=\"font-size:14px; color:#374151; line-height:1.6; margin:0 0 20px;\">
                        An administrator has initiated a secure password reset for your FutureNxt Portal account. Please use the 6-digit verification code below to authorize this change:
                    </p>

                    <div style=\"text-align:center; margin:24px 0;\">
                        <div style=\"display:inline-block; background:#FFFFFF; border:2px dashed #FF7A18; border-radius:12px; padding:18px 36px; box-shadow:0 4px 12px rgba(255, 122, 24, 0.12);\">
                            <div style=\"font-size:11px; font-weight:800; color:#FF7A18; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:6px;\">One-Time Passcode (OTP)</div>
                            <div style=\"font-size:36px; font-weight:900; letter-spacing:8px; color:#111827; font-family:'JetBrains Mono',monospace;\">" . htmlspecialchars($otp) . "</div>
                            <div style=\"font-size:11px; color:#9CA3AF; margin-top:6px;\">Valid for 15 minutes</div>
                        </div>
                    </div>

                    <div style=\"background:#FFF; border:1px solid #E5E7EB; border-left:4px solid #FF7A18; border-radius:8px; padding:14px 18px; margin:20px 0;\">
                        <div style=\"font-size:12.5px; font-weight:700; color:#111827; margin-bottom:4px;\">🛡️ Security Information:</div>
                        <ul style=\"margin:4px 0 0; padding-left:18px; font-size:12.5px; color:#4B5563; line-height:1.5;\">
                            <li>Provide this verification code to your portal administrator to finalize your password update.</li>
                            <li>This code will expire automatically in 15 minutes for your protection.</li>
                            <li>If you did not expect a password update, please contact administration at <a href=\"mailto:fnxt.admin@gmail.com\" style=\"color:#FF7A18; font-weight:700;\">fnxt.admin@gmail.com</a> immediately.</li>
                        </ul>
                    </div>

                    <p style=\"font-size:12px; color:#9CA3AF; margin:24px 0 0; border-top:1px solid #F3DECB; padding-top:14px; text-align:center;\">
                        Sent via <strong>fnxt.admin@gmail.com</strong> · FutureNxt Technologies Operations System<br>
                        This is an automated system notification.
                    </p>
                </div>
            ";

            $sent = Mailer::send($emp['email'], $subject, $html, $emp['full_name'], self::getSmtpConfig());
            if (!$sent) {
                // Seamless fallback to primary server SMTP to guarantee immediate delivery
                $sent = Mailer::send($emp['email'], $subject, $html, $emp['full_name']);
            }
            return $sent;
        } catch (\Throwable $e) {
            error_log("[EmailNotifier] Error sending OTP email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send official onboarding welcome email to partner with their login email, partner ID, and password.
     */
    public static function sendPartnerOnboarding(int $partnerId, string $plainPassword): bool
    {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT p.*, mgr.full_name AS manager_name, mgr.emp_code AS manager_code
                FROM fnx_employees p
                LEFT JOIN fnx_employees mgr ON mgr.id = p.manager_id
                WHERE p.id = ?
                LIMIT 1
            ");
            $stmt->execute([$partnerId]);
            $partner = $stmt->fetch();
            if (!$partner || empty($partner['email'])) {
                return false;
            }

            $subject = "Welcome to FutureNxt Technologies — Official Portal Access & Credentials";

            $loginUrl = "https://infintylearning.net/futurenxt/login";
            $roleName = ucfirst(str_replace('_', ' ', (string)($partner['role'] ?? 'Partner')));

            $html = "
                <div style=\"font-family:'Plus Jakarta Sans',Arial,sans-serif; max-width:620px; margin:0 auto; background:#FFF9F4; padding:32px; border-radius:14px; border:1.5px solid #F3DECB;\">
                    <div style=\"display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;\">
                        <span style=\"background:#FF7A18; color:#FFF; font-size:12px; font-weight:800; padding:5px 12px; border-radius:6px; letter-spacing:0.04em; text-transform:uppercase;\">FutureNxt Technologies</span>
                        <span style=\"font-size:12px; color:#6B7280;\">" . date('d M Y') . "</span>
                    </div>

                    <h2 style=\"margin:0 0 10px; color:#111827; font-size:22px; font-weight:800;\">Welcome to FutureNxt Technologies!</h2>
                    
                    <p style=\"font-size:14px; color:#374151; line-height:1.6; margin:0 0 14px;\">
                        Dear <strong>" . htmlspecialchars($partner['full_name']) . "</strong>,
                    </p>
                    
                    <p style=\"font-size:14px; color:#374151; line-height:1.6; margin:0 0 20px;\">
                        We are thrilled to welcome you to <strong>FutureNxt Technologies</strong> as our <strong>" . htmlspecialchars($partner['designation'] ?: $roleName) . "</strong> (" . htmlspecialchars($partner['department'] ?? 'General') . " Department). Your official Corporate Operations Portal account is now active and ready for use.
                    </p>

                    <!-- Credentials Box -->
                    <div style=\"background:#FFFFFF; border:1.5px solid #F3DECB; border-radius:12px; padding:22px; margin:22px 0; box-shadow:0 4px 14px rgba(0,0,0,0.04);\">
                        <div style=\"font-size:12px; font-weight:800; color:#FF7A18; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:14px;\">
                            🔑 Your Portal Access Credentials
                        </div>
                        <table style=\"width:100%; border-collapse:collapse; font-size:13.5px;\">
                            <tr>
                                <td style=\"padding:7px 0; color:#6B7280; width:140px; font-weight:600;\">Portal URL:</td>
                                <td style=\"padding:7px 0;\">
                                    <a href=\"{$loginUrl}\" style=\"color:#FF7A18; font-weight:700; text-decoration:none;\">https://infintylearning.net/futurenxt/login</a>
                                </td>
                            </tr>
                            <tr>
                                <td style=\"padding:7px 0; color:#6B7280; font-weight:600;\">Partner ID:</td>
                                <td style=\"padding:7px 0; color:#111827; font-weight:800; font-family:'JetBrains Mono',monospace;\">" . htmlspecialchars($partner['emp_code']) . "</td>
                            </tr>
                            <tr>
                                <td style=\"padding:7px 0; color:#6B7280; font-weight:600;\">Login Email:</td>
                                <td style=\"padding:7px 0; color:#111827; font-weight:700;\">" . htmlspecialchars($partner['email']) . "</td>
                            </tr>
                            <tr>
                                <td style=\"padding:7px 0; color:#6B7280; font-weight:600;\">Access Password:</td>
                                <td style=\"padding:7px 0;\">
                                    <span style=\"background:#FFF4EC; border:1px solid #FF7A18; color:#B45309; font-weight:800; font-family:'JetBrains Mono',monospace; padding:3px 10px; border-radius:6px; font-size:14px;\">" . htmlspecialchars($plainPassword) . "</span>
                                </td>
                            </tr>
                            <tr>
                                <td style=\"padding:7px 0; color:#6B7280; font-weight:600;\">Designation:</td>
                                <td style=\"padding:7px 0; color:#111827;\">" . htmlspecialchars($partner['designation'] ?? '') . " (" . htmlspecialchars($roleName) . ")</td>
                            </tr>
                            " . (!empty($partner['manager_name']) ? "
                            <tr>
                                <td style=\"padding:7px 0; color:#6B7280; font-weight:600;\">Reporting To:</td>
                                <td style=\"padding:7px 0; color:#111827;\">" . htmlspecialchars($partner['manager_name']) . " (" . htmlspecialchars($partner['manager_code']) . ")</td>
                            </tr>" : "") . "
                        </table>
                    </div>

                    <div style=\"text-align:center; margin:24px 0;\">
                        <a href=\"{$loginUrl}\" style=\"display:inline-block; background:#FF7A18; color:#FFFFFF; text-decoration:none; padding:12px 32px; border-radius:8px; font-weight:800; font-size:14px; box-shadow:0 4px 12px rgba(255,122,24,0.25);\">
                            Sign In to FutureNxt Portal →
                        </a>
                    </div>

                    <!-- Operational Guidelines -->
                    <div style=\"background:#FFFFFF; border:1px solid #E5E7EB; border-left:4px solid #111827; border-radius:8px; padding:16px 18px; margin:20px 0;\">
                        <div style=\"font-size:13px; font-weight:800; color:#111827; margin-bottom:6px;\">📋 Important Partner Guidelines:</div>
                        <ul style=\"margin:0; padding-left:18px; font-size:12.5px; color:#4B5563; line-height:1.6;\">
                            <li><strong>Daily Updates:</strong> Submit your daily work update before end-of-day detailing your accomplishments and blockers.</li>
                            <li><strong>Task Management:</strong> Check your assigned tasks, review requirements, submit documentation, and track completion progress.</li>
                            <li><strong>Leaves &amp; WFA:</strong> Submit leave and Work From Anywhere (WFA) requests with advance notice for management review.</li>
                            <li><strong>Security:</strong> After your initial login, you can update your password anytime under <em>My Profile</em>.</li>
                        </ul>
                    </div>

                    <p style=\"font-size:12px; color:#9CA3AF; margin:24px 0 0; border-top:1px solid #F3DECB; padding-top:14px; text-align:center;\">
                        FutureNxt Technologies Corporate Operations · Sent via <strong>fnxt.admin@gmail.com</strong><br>
                        Have questions? Contact Administration at <a href=\"mailto:fnxt.admin@gmail.com\" style=\"color:#FF7A18; text-decoration:none; font-weight:700;\">fnxt.admin@gmail.com</a>.
                    </p>
                </div>
            ";

            $sent = Mailer::send($partner['email'], $subject, $html, $partner['full_name'], self::getSmtpConfig());
            if (!$sent) {
                $sent = Mailer::send($partner['email'], $subject, $html, $partner['full_name']);
            }

            Audit::log('onboarding_email_sent', 'employee', $partnerId, "Sent onboarding credentials email to {$partner['emp_code']} ({$partner['email']})");

            return $sent;
        } catch (\Throwable $e) {
            error_log("[EmailNotifier] Error sending onboarding email: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Send email notification to employee when their leave request is reviewed (approved or rejected).
     */
    public static function sendLeaveDecision(int $leaveId): bool
    {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT l.*, 
                       e.full_name AS employee_name, 
                       e.email AS employee_email, 
                       e.emp_code,
                       rev.full_name AS reviewer_name,
                       rev.role AS reviewer_role
                FROM fnx_leaves l
                JOIN fnx_employees e ON e.id = l.employee_id
                LEFT JOIN fnx_employees rev ON rev.id = l.reviewed_by
                WHERE l.id = ?
                LIMIT 1
            ");
            $stmt->execute([$leaveId]);
            $leave = $stmt->fetch();
            if (!$leave || empty($leave['employee_email'])) {
                return false;
            }

            $statusText = strtoupper($leave['status']);
            $isApproved = ($leave['status'] === 'approved');
            $badgeColor = $isApproved ? '#10B981' : '#EF4444';
            $subject = "FutureNxt Leave Request Update: {$statusText} ({$leave['start_date']} to {$leave['end_date']})";

            $html = "
                <div style=\"font-family:'Plus Jakarta Sans',Arial,sans-serif; max-width:600px; margin:0 auto; background:#FFF9F4; padding:28px; border-radius:14px; border:1.5px solid #F3DECB;\">
                    <div style=\"margin-bottom:20px;\">
                        <span style=\"background:#FF7A18; color:#FFF; font-size:12px; font-weight:800; padding:4px 10px; border-radius:6px; text-transform:uppercase;\">FutureNxt Operations</span>
                    </div>
                    <h2 style=\"margin:0 0 12px; color:#111827; font-size:22px;\">Leave Request Update</h2>
                    <p style=\"font-size:14px; color:#374151; line-height:1.6;\">
                        Hello <strong>" . htmlspecialchars($leave['employee_name']) . "</strong> (" . htmlspecialchars($leave['emp_code']) . "),
                    </p>
                    <p style=\"font-size:14px; color:#374151; line-height:1.6;\">
                        Your leave application has been reviewed by management.
                    </p>
                    <div style=\"background:#FFFFFF; border:1px solid #E5E7EB; border-radius:10px; padding:18px; margin:20px 0;\">
                        <table style=\"width:100%; border-collapse:collapse; font-size:13.5px;\">
                            <tr>
                                <td style=\"padding:6px 0; color:#6B7280; width:130px;\">Decision Status:</td>
                                <td style=\"padding:6px 0;\">
                                    <span style=\"background:{$badgeColor}; color:#FFF; font-weight:800; font-size:12px; padding:3px 8px; border-radius:4px;\">{$statusText}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style=\"padding:6px 0; color:#6B7280;\">Leave Type:</td>
                                <td style=\"padding:6px 0; color:#111827; font-weight:700;\">" . ucfirst(htmlspecialchars($leave['leave_type'])) . " Leave</td>
                            </tr>
                            <tr>
                                <td style=\"padding:6px 0; color:#6B7280;\">Duration:</td>
                                <td style=\"padding:6px 0; color:#111827;\">" . date('d M Y', strtotime($leave['start_date'])) . " to " . date('d M Y', strtotime($leave['end_date'])) . " (" . (int)$leave['total_days'] . " days)</td>
                            </tr>
                            <tr>
                                <td style=\"padding:6px 0; color:#6B7280;\">Reviewed By:</td>
                                <td style=\"padding:6px 0; color:#111827;\">" . htmlspecialchars($leave['reviewer_name'] ?? 'Executive Leadership') . "</td>
                            </tr>
                            " . (!empty($leave['reviewer_remarks']) ? "
                            <tr>
                                <td style=\"padding:6px 0; color:#6B7280; vertical-align:top;\">Remarks:</td>
                                <td style=\"padding:6px 0; color:#111827;\">" . nl2br(htmlspecialchars($leave['reviewer_remarks'])) . "</td>
                            </tr>" : "") . "
                        </table>
                    </div>
                    <p style=\"font-size:13px; color:#6B7280; margin:16px 0 0;\">
                        To view your leave ledger or submit further requests, please visit your <a href=\"https://infintylearning.net/futurenxt/leaves\" style=\"color:#FF7A18; font-weight:700; text-decoration:none;\">FutureNxt Portal</a>.
                    </p>
                </div>
            ";

            return Mailer::send($leave['employee_email'], $subject, $html, $leave['employee_name'], self::getSmtpConfig());
        } catch (\Throwable $e) {
            error_log('[FutureNxt EmailNotifier] sendLeaveDecision error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notifications for active tasks whose deadline is within 6 hours.
     */
    public static function checkAndSendTaskDeadlineReminders(): array
    {
        $db = Database::connect();
        $notifiedCount = 0;
        $tasksProcessed = [];

        // Find active tasks due within the next 6 hours (or overdue today) where reminder_sent_at IS NULL
        $sql = "
            SELECT t.*, 
                   TIMESTAMPDIFF(MINUTE, NOW(), TIMESTAMP(t.due_date, COALESCE(t.due_time, '18:00:00'))) AS minutes_left
            FROM fnx_tasks t
            WHERE t.status IN ('pending', 'in_progress')
              AND t.reminder_sent_at IS NULL
              AND TIMESTAMP(t.due_date, COALESCE(t.due_time, '18:00:00')) <= DATE_ADD(NOW(), INTERVAL 6 HOUR)
              AND TIMESTAMP(t.due_date, COALESCE(t.due_time, '18:00:00')) >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ";

        $stmt = $db->query($sql);
        $tasks = $stmt->fetchAll() ?: [];

        foreach ($tasks as $t) {
            $taskId = (int)$t['id'];
            $assignees = Task::getAssignees($taskId);
            if (empty($assignees)) {
                continue;
            }

            $minutesLeft = (int)$t['minutes_left'];
            $hoursLeft = round($minutesLeft / 60, 1);
            $timeNotice = $minutesLeft <= 0 
                ? "OVERDUE (Deadline has passed)" 
                : ($hoursLeft <= 1 ? "LESS THAN 1 HOUR REMAINING" : "LESS THAN {$hoursLeft} HOURS REMAINING");

            $subject = "🚨 URGENT TASK DEADLINE REMINDER [{$t['task_code']}]: Due in " . ($minutesLeft <= 0 ? 'less than 0 mins' : "{$hoursLeft} hrs");

            foreach ($assignees as $emp) {
                if (empty($emp['email'])) {
                    continue;
                }

                $html = "
                    <div style=\"font-family:'Plus Jakarta Sans',Arial,sans-serif; max-width:600px; margin:0 auto; background:#FFF9F4; padding:28px; border-radius:14px; border:1.5px solid #F3DECB;\">
                        <div style=\"margin-bottom:16px;\">
                            <span style=\"background:#EF4444; color:#FFF; font-size:12px; font-weight:800; padding:4px 10px; border-radius:6px; text-transform:uppercase;\">🚨 Task Deadline Warning</span>
                        </div>
                        <h2 style=\"margin:0 0 10px; color:#111827; font-size:20px;\">Task Due Within 6 Hours: {$t['task_code']}</h2>
                        <p style=\"font-size:14px; color:#374151; line-height:1.6;\">
                            Hello <strong>" . htmlspecialchars($emp['full_name']) . "</strong>,
                        </p>
                        <p style=\"font-size:14px; color:#374151; line-height:1.6;\">
                            This is an automated operational alert. You are assigned to the task below, and its deadline is approaching in <strong>{$timeNotice}</strong>:
                        </p>
                        <div style=\"background:#FFFFFF; border:1.5px solid #FCA5A5; border-radius:10px; padding:18px; margin:20px 0;\">
                            <h3 style=\"margin:0 0 8px; font-size:16px; color:#111827;\">" . htmlspecialchars($t['title']) . "</h3>
                            <div style=\"font-size:12.5px; color:#6B7280; font-family:monospace; margin-bottom:10px;\">
                                Code: " . htmlspecialchars($t['task_code']) . " · Priority: " . strtoupper($t['priority']) . "
                            </div>
                            <div style=\"font-size:13px; color:#374151; line-height:1.5; margin-bottom:12px;\">
                                " . nl2br(htmlspecialchars(mb_substr($t['description'] ?? '', 0, 300))) . "
                            </div>
                            <div style=\"font-size:13px; color:#DC2626; font-weight:700;\">
                                Deadline: " . date('d M Y', strtotime($t['due_date'])) . " at " . (!empty($t['due_time']) ? date('h:i A', strtotime($t['due_time'])) : '06:00 PM') . "
                            </div>
                        </div>
                        <p style=\"font-size:13.5px; color:#374151; line-height:1.6;\">
                            Please complete deliverables and upload your documentation PDF directly on the portal to submit for management review.
                        </p>
                        <div style=\"margin-top:20px;\">
                            <a href=\"https://infintylearning.net/futurenxt/tasks\" style=\"display:inline-block; background:#FF7A18; color:#FFFFFF; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:13.5px;\">
                                Open Task &amp; Submit PDF →
                            </a>
                        </div>
                    </div>
                ";

                Mailer::send($emp['email'], $subject, $html, $emp['full_name'], self::getSmtpConfig());
                $notifiedCount++;
            }

            // Mark task as reminded so it doesn't trigger repeatedly
            $upd = $db->prepare("UPDATE fnx_tasks SET reminder_sent_at = NOW() WHERE id = ?");
            $upd->execute([$taskId]);
            $tasksProcessed[] = $t['task_code'];
        }

        return [
            'tasks_processed' => $tasksProcessed,
            'emails_sent'     => $notifiedCount
        ];
    }

    /**
     * Broadcast an event, holiday notice, or company announcement to targeted personnel.
     */
    public static function broadcastNotice(int $noticeId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT n.*, e.full_name AS author_name 
            FROM fnx_notices n
            LEFT JOIN fnx_employees e ON e.id = n.created_by
            WHERE n.id = ?
            LIMIT 1
        ");
        $stmt->execute([$noticeId]);
        $notice = $stmt->fetch();
        if (!$notice) {
            return ['success' => false, 'error' => 'Notice not found'];
        }

        $empSql = "SELECT full_name, email FROM fnx_employees WHERE status = 'active'";
        if ($notice['target_audience'] === 'employees') {
            $empSql .= " AND role IN ('employee', 'intern')";
        } elseif ($notice['target_audience'] === 'interns') {
            $empSql .= " AND role = 'intern'";
        } elseif ($notice['target_audience'] === 'managers') {
            $empSql .= " AND role IN ('manager', 'office_manager')";
        }

        $employees = $db->query($empSql)->fetchAll() ?: [];
        $count = 0;

        $catBadge = match($notice['category']) {
            'holiday' => '🏖️ HOLIDAY NOTICE',
            'event'   => '🎉 IMPORTANT EVENT',
            'urgent'  => '🚨 URGENT ANNOUNCEMENT',
            'policy'  => '📜 POLICY UPDATE',
            default   => '📢 COMPANY NOTICE'
        };

        $subject = "FutureNxt Notice: [{$catBadge}] " . $notice['title'];

        foreach ($employees as $emp) {
            if (empty($emp['email'])) continue;

            $html = "
                <div style=\"font-family:'Plus Jakarta Sans',Arial,sans-serif; max-width:600px; margin:0 auto; background:#FFF9F4; padding:28px; border-radius:14px; border:1.5px solid #F3DECB;\">
                    <div style=\"margin-bottom:16px;\">
                        <span style=\"background:#FF7A18; color:#FFF; font-size:12px; font-weight:800; padding:4px 10px; border-radius:6px;\">{$catBadge}</span>
                    </div>
                    <h2 style=\"margin:0 0 10px; color:#111827; font-size:20px;\">" . htmlspecialchars($notice['title']) . "</h2>
                    <div style=\"font-size:12px; color:#6B7280; margin-bottom:16px;\">
                        Effective Date: <strong>" . date('d M Y', strtotime($notice['notice_date'])) . "</strong> · Published by " . htmlspecialchars($notice['author_name'] ?? 'Executive Leadership') . "
                    </div>
                    <div style=\"background:#FFFFFF; border:1px solid #E5E7EB; border-radius:10px; padding:18px; margin:16px 0; font-size:14px; color:#111827; line-height:1.6;\">
                        " . nl2br(htmlspecialchars($notice['content'])) . "
                    </div>
                    <div style=\"margin-top:20px;\">
                        <a href=\"https://infintylearning.net/futurenxt/notices\" style=\"display:inline-block; background:#111827; color:#FFFFFF; padding:8px 16px; border-radius:6px; text-decoration:none; font-weight:700; font-size:12.5px;\">
                            View Notice Board on Portal →
                        </a>
                    </div>
                </div>
            ";

            Mailer::send($emp['email'], $subject, $html, $emp['full_name'], self::getSmtpConfig());
            $count++;
        }

        return ['success' => true, 'emails_sent' => $count];
    }
}
