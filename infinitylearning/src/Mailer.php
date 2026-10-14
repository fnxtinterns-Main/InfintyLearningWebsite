<?php
declare(strict_types=1);

namespace App;

final class Mailer
{
    /**
     * Send a transactional email via SMTP.
     */
    public static function send(string $to, string $subject, string $htmlBody, string $toName = ''): bool
    {
        $config = require BASE_PATH . '/config/config.php';
        $smtp   = $config['smtp'];

        // In development, log and skip actual send
        if (($config['app']['env'] ?? 'production') === 'development') {
            self::logToFile($to, $subject, $htmlBody);
            return true;
        }

        // If SMTP is not configured, log and bail gracefully
        if (empty($smtp['host']) || empty($smtp['user'])) {
            error_log('[Mailer] SMTP not configured; skipping actual send for: ' . $to);
            self::logToFile($to, $subject, $htmlBody);
            return false;
        }

        // Use PHPMailer
        if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
            return self::sendWithPHPMailer($to, $toName, $subject, $htmlBody, $smtp);
        }

        // Fallback: native mail()
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $smtp['from'],
            'Reply-To: ' . $smtp['from'],
        ];
        return @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    }

    /**
     * Send an AI-generated email by calling an external AI agent endpoint.
     */
    public static function sendViaAgent(string $to, string $intent, array $context, string $subject = ''): bool
    {
        $config = require BASE_PATH . '/config/config.php';
        $agentUrl = $config['ai_agent']['url'] ?? '';
        $apiKey   = $config['ai_agent']['api_key'] ?? '';

        if (empty($agentUrl)) {
            return self::send($to, $subject ?: 'Notification from Infinity Learning',
                '<p>' . htmlspecialchars(json_encode($context)) . '</p>');
        }

        $payload = json_encode([
            'to'      => $to,
            'intent'  => $intent,
            'context' => $context,
            'subject' => $subject,
            'from'    => $config['smtp']['from'],
        ]);

        $headers = ['Content-Type: application/json'];
        if ($apiKey) $headers[] = 'Authorization: Bearer ' . $apiKey;

        $ch = curl_init($agentUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($code >= 200 && $code < 300) return true;

        error_log('[Mailer] AI agent failed (' . $code . '): ' . $err);
        return self::send($to, $subject ?: 'Notification from Infinity Learning',
            '<p>' . htmlspecialchars(json_encode($context)) . '</p>');
    }

    // =========================================================
    // INTERNAL
    // =========================================================

   private static function sendWithPHPMailer(string $to, string $toName, string $subject, string $htmlBody, array $smtp): bool
{
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->Port       = (int)$smtp['port'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['user'];
        $mail->Password   = $smtp['pass'];
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

        // ---- FORCE UTF-8 EVERYWHERE (fixes emoji + em-dash corruption) ----
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setLanguage('en');

        $mail->setFrom($smtp['from'], 'Infinity Learning');
        $mail->addAddress($to, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        return $mail->send();
    } catch (\Throwable $e) {
        error_log('[Mailer] PHPMailer failed: ' . $e->getMessage());
        error_log('[Mailer] ErrorInfo: ' . ($mail->ErrorInfo ?? 'N/A'));
        self::logToFile($to, $subject, $htmlBody);
        return false;
    }
}

    private static function logToFile(string $to, string $subject, string $htmlBody): void
    {
        $dir = BASE_PATH . '/logs';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $line = sprintf(
            "[%s] TO=%s\nSUBJECT=%s\n%s\n---\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $htmlBody
        );
        @file_put_contents($dir . '/mail.log', $line, FILE_APPEND);
        error_log('[MAIL] To=' . $to . ' | Subject=' . $subject);
    }

    // =========================================================
    // TEMPLATES
    // =========================================================

    /**
     * OTP email — used for registration and password resets.
     */
    public static function otpEmail(string $name, string $otp, string $purpose = 'registration'): string
    {
        $title = match ($purpose) {
            'registration'   => 'Verify your phone number',
            'password_reset' => 'Reset your password',
            'phone_change'   => 'Verify your new phone number',
            default          => 'Your one-time password',
        };
        return "
        <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#FFF9F4;padding:24px;border-radius:12px'>
            <div style='text-align:center;margin-bottom:24px'>
                <h1 style='color:#FF7A18;font-size:24px;margin:0'>Infinity Learning</h1>
                <p style='color:#7A5C4A;font-size:14px;margin:4px 0 0'>Learn. Grow. Achieve — Infinitely.</p>
            </div>
            <div style='background:#fff;border:1px solid #F3DECB;border-radius:12px;padding:24px'>
                <h2 style='color:#2B1B12;font-size:18px;margin:0 0 12px'>Hi " . htmlspecialchars($name) . ",</h2>
                <p style='color:#7A5C4A;font-size:14px;line-height:1.6;margin:0 0 16px'>$title. Enter the code below to continue:</p>
                <div style='text-align:center;background:#FFF3E7;border-radius:12px;padding:20px;margin:16px 0'>
                    <div style='font-family:Courier,monospace;font-size:36px;font-weight:800;letter-spacing:.3em;color:#FF7A18'>" . htmlspecialchars($otp) . "</div>
                </div>
                <p style='color:#7A5C4A;font-size:13px;margin:16px 0 0'>This code expires in 10 minutes. If you didn't request it, you can safely ignore this email.</p>
            </div>
            <p style='text-align:center;color:#7A5C4A;font-size:12px;margin-top:24px'>© " . date('Y') . " Infinity Learning</p>
        </div>";
    }

    /**
     * Approval email — sent when a student or teacher is approved by a school/super admin.
     */
    public static function approvalEmail(
        string $name,
        string $email,
        string $role,
        ?string $schoolName = null,
        string $loginUrl = 'https://infintylearning.net/login'
    ): string {
        $roleLabel = ucfirst($role);
        $intro = $role === 'student'
            ? 'Your student account is now active. You can log in to access lessons, activities, competitions and download your certificates.'
            : 'Your teacher account is now active. You can log in to create batches, upload lessons, mark attendance and assign activities.';

        $schoolLine = $schoolName
            ? "<p style='color:#7A5C4A;font-size:14px;line-height:1.6;margin:0 0 16px'><strong>School:</strong> " . htmlspecialchars($schoolName) . "</p>"
            : '';

        return "
        <div style='font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#FFF9F4;padding:24px;border-radius:12px'>
            <div style='text-align:center;margin-bottom:24px'>
                <h1 style='color:#FF7A18;font-size:24px;margin:0'>Infinity Learning</h1>
                <p style='color:#7A5C4A;font-size:13px;margin:4px 0 0'>Learn. Grow. Achieve — Infinitely.</p>
            </div>

            <div style='background:#fff;border:1px solid #F3DECB;border-radius:12px;padding:28px'>
                <h2 style='color:#2B1B12;font-size:20px;margin:0 0 12px'>🎉 Welcome aboard, " . htmlspecialchars($name) . "!</h2>

                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 16px'>
                    Great news — your <strong>$roleLabel</strong> account has been approved.
                </p>

                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 16px'>
                    $intro
                </p>

                $schoolLine

                <div style='background:#FFF3E7;border-radius:10px;padding:16px;margin:20px 0'>
                    <div style='color:#7A5C4A;font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:6px'>Your login email</div>
                    <div style='color:#2B1B12;font-family:monospace;font-size:15px;font-weight:700;word-break:break-all'>" . htmlspecialchars($email) . "</div>
                </div>

                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 20px'>
                    Use your password from when you registered to log in. If you've forgotten it, click <em>Forgot password</em> on the login page.
                </p>

                <div style='text-align:center;margin:24px 0 8px'>
                    <a href='" . htmlspecialchars($loginUrl) . "'
                       style='display:inline-block;background:linear-gradient(135deg,#FF7A18,#FFB871);color:#fff;
                              padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px'>
                        Log in to your account →
                    </a>
                </div>
            </div>

            <p style='text-align:center;color:#7A5C4A;font-size:12px;margin-top:20px'>
                © " . date('Y') . " Infinity Learning · <a href='https://infintylearning.net' style='color:#FF7A18'>infintylearning.net</a>
            </p>
        </div>";
    }

    /**
     * Welcome email for a newly-onboarded school admin.
     */
    public static function schoolAdminWelcomeEmail(
        string $name,
        string $email,
        string $schoolName,
        string $schoolCode,
        string $loginUrl = 'https://infintylearning.net/login'
    ): string {
        return "
        <div style='font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#FFF9F4;padding:24px;border-radius:12px'>
            <div style='text-align:center;margin-bottom:24px'>
                <h1 style='color:#FF7A18;font-size:24px;margin:0'>Infinity Learning</h1>
                <p style='color:#7A5C4A;font-size:13px;margin:4px 0 0'>Learn. Grow. Achieve — Infinitely.</p>
            </div>

            <div style='background:#fff;border:1px solid #F3DECB;border-radius:12px;padding:28px'>
                <h2 style='color:#2B1B12;font-size:20px;margin:0 0 12px'>🏫 Welcome, " . htmlspecialchars($name) . "!</h2>

                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 16px'>
                    <strong>" . htmlspecialchars($schoolName) . "</strong> has been successfully onboarded to Infinity Learning.
                    You're now the school admin — you can approve teachers, approve students, and manage your school's
                    lessons, activities and certificates.
                </p>

                <div style='background:#FFF3E7;border-radius:10px;padding:16px;margin:20px 0'>
                    <div style='color:#7A5C4A;font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:6px'>School code</div>
                    <div style='color:#2B1B12;font-family:monospace;font-size:18px;font-weight:800;letter-spacing:.05em'>" . htmlspecialchars($schoolCode) . "</div>
                </div>

                <div style='background:#FFF3E7;border-radius:10px;padding:16px;margin:20px 0'>
                    <div style='color:#7A5C4A;font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:6px'>Your admin login</div>
                    <div style='color:#2B1B12;font-family:monospace;font-size:15px;font-weight:700;word-break:break-all'>" . htmlspecialchars($email) . "</div>
                </div>

                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 20px'>
                    Use the password generated when your school was created. If you've lost it, click <em>Forgot password</em> on the login page to reset.
                </p>

                <div style='text-align:center;margin:24px 0 8px'>
                    <a href='" . htmlspecialchars($loginUrl) . "'
                       style='display:inline-block;background:linear-gradient(135deg,#7C2D5C,#A0386B);color:#fff;
                              padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px'>
                        Log in to your school panel →
                    </a>
                </div>
            </div>

            <p style='text-align:center;color:#7A5C4A;font-size:12px;margin-top:20px'>
                © " . date('Y') . " Infinity Learning · <a href='https://infintylearning.net' style='color:#FF7A18'>infintylearning.net</a>
            </p>
        </div>";
    }

    /**
     * Welcome email for a new super admin.
     */
    public static function superAdminWelcomeEmail(
        string $email,
        string $tempPassword,
        string $loginUrl = 'https://infintylearning.net/login'
    ): string {
        return "
        <div style='font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#FFF9F4;padding:24px;border-radius:12px'>
            <div style='text-align:center;margin-bottom:24px'>
                <h1 style='color:#FF7A18;font-size:24px;margin:0'>Infinity Learning</h1>
                <p style='color:#7A5C4A;font-size:13px;margin:4px 0 0'>Learn. Grow. Achieve — Infinitely.</p>
            </div>

            <div style='background:#fff;border:1px solid #F3DECB;border-radius:12px;padding:28px'>
                <h2 style='color:#2B1B12;font-size:20px;margin:0 0 12px'>👑 Super Admin access granted</h2>

                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 16px'>
                    You now have full platform access — onboarding schools, managing users, publishing
                    global activities, and overseeing the whole system.
                </p>

                <div style='background:#FFF3E7;border-radius:10px;padding:16px;margin:20px 0'>
                    <div style='color:#7A5C4A;font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:6px'>Login email</div>
                    <div style='color:#2B1B12;font-family:monospace;font-size:15px;font-weight:700;word-break:break-all;margin-bottom:10px'>" . htmlspecialchars($email) . "</div>

                    <div style='color:#7A5C4A;font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:6px'>Temporary password</div>
                    <div style='color:#2B1B12;font-family:monospace;font-size:15px;font-weight:700;word-break:break-all'>" . htmlspecialchars($tempPassword) . "</div>
                </div>

                <p style='color:#DC2626;font-size:13px;line-height:1.6;margin:0 0 20px'>
                    ⚠️ <strong>Change this password immediately after logging in</strong> — via Profile → Change Password.
                </p>

                <div style='text-align:center;margin:24px 0 8px'>
                    <a href='" . htmlspecialchars($loginUrl) . "'
                       style='display:inline-block;background:linear-gradient(135deg,#7C2D5C,#A0386B);color:#fff;
                              padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px'>
                        Log in now →
                    </a>
                </div>
            </div>

            <p style='text-align:center;color:#7A5C4A;font-size:12px;margin-top:20px'>
                © " . date('Y') . " Infinity Learning · <a href='https://infintylearning.net' style='color:#FF7A18'>infintylearning.net</a>
            </p>
        </div>";
    }
        /**
     * Password reset email — sent when a user requests a reset link.
     */
    public static function passwordResetEmail(
        string $email,
        string $resetUrl,
        string $expiryText = '1 hour'
    ): string {
        return "
        <div style='font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;background:#FFF9F4;padding:24px;border-radius:12px'>
            <div style='text-align:center;margin-bottom:24px'>
                <h1 style='color:#FF7A18;font-size:24px;margin:0'>Infinity Learning</h1>
                <p style='color:#7A5C4A;font-size:13px;margin:4px 0 0'>Learn. Grow. Achieve — Infinitely.</p>
            </div>

            <div style='background:#fff;border:1px solid #F3DECB;border-radius:12px;padding:28px'>
                <h2 style='color:#2B1B12;font-size:20px;margin:0 0 12px'>🔑 Reset your password</h2>

                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 16px'>
                    We received a request to reset the password for your Infinity Learning account
                    (<strong>" . htmlspecialchars($email) . "</strong>).
                </p>

                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 20px'>
                    Click the button below to choose a new password. This link expires in $expiryText.
                </p>

                <div style='text-align:center;margin:24px 0 8px'>
                    <a href='" . htmlspecialchars($resetUrl) . "'
                       style='display:inline-block;background:linear-gradient(135deg,#FF7A18,#FFB871);color:#fff;
                              padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px'>
                        Reset my password →
                    </a>
                </div>

                <p style='color:#7A5C4A;font-size:13px;line-height:1.6;margin:20px 0 0'>
                    If the button doesn't work, copy and paste this link into your browser:
                </p>
                <p style='color:#7A5C4A;font-size:12px;line-height:1.5;margin:6px 0 0;word-break:break-all;font-family:monospace'>
                    " . htmlspecialchars($resetUrl) . "
                </p>

                <div style='margin-top:20px;padding:12px 14px;background:#FFF3E7;border-radius:8px'>
                    <p style='color:#7A5C4A;font-size:13px;line-height:1.55;margin:0'>
                        <strong>Didn't request this?</strong> You can safely ignore this email —
                        your password will not change unless you click the link above.
                    </p>
                </div>
            </div>

            <p style='text-align:center;color:#7A5C4A;font-size:12px;margin-top:20px'>
                © " . date('Y') . " Infinity Learning · <a href='https://infintylearning.net' style='color:#FF7A18'>infintylearning.net</a>
            </p>
        </div>";
    }
        /**
     * Weekly parent summary email.
     * $summaries is an array of ParentSummary::buildForStudent() results.
     */
    public static function parentSummaryEmail(
        string $parentName,
        array $summaries,
        string $weekStart,
        string $dashboardUrl = 'https://infintylearning.net/login'
    ): string {
        $weekLabel = date('d M', strtotime($weekStart)) . ' – ' . date('d M Y', strtotime($weekStart . ' +6 days'));

        $childrenHtml = '';
        foreach ($summaries as $s) {
            $childrenHtml .= self::renderChildBlock($s, $dashboardUrl);
        }

        return "
        <div style='font-family:Arial,Helvetica,sans-serif;max-width:640px;margin:0 auto;background:#FFF9F4;padding:24px;border-radius:12px'>
            <div style='text-align:center;margin-bottom:24px'>
                <h1 style='color:#FF7A18;font-size:24px;margin:0'>Infinity Learning</h1>
                <p style='color:#7A5C4A;font-size:13px;margin:4px 0 0'>Weekly Progress Summary</p>
            </div>

            <div style='background:#fff;border:1px solid #F3DECB;border-radius:12px;padding:28px'>
                <h2 style='color:#2B1B12;font-size:20px;margin:0 0 6px'>Hi " . htmlspecialchars($parentName) . ",</h2>
                <p style='color:#7A5C4A;font-size:14px;line-height:1.65;margin:0 0 20px'>
                    Here's a summary of your child's week on Infinity Learning
                    (<strong>" . htmlspecialchars($weekLabel) . "</strong>).
                </p>

                $childrenHtml

                <div style='text-align:center;margin:28px 0 8px'>
                    <a href='" . htmlspecialchars($dashboardUrl) . "'
                       style='display:inline-block;background:linear-gradient(135deg,#FF7A18,#FFB871);color:#fff;
                              padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px'>
                        View full progress →
                    </a>
                </div>
            </div>

            <p style='text-align:center;color:#7A5C4A;font-size:12px;margin-top:20px;line-height:1.6'>
                You're receiving this because you're listed as a parent/guardian on Infinity Learning.<br>
                Questions? Contact your school admin or email
                <a href='mailto:info@infintylearning.net' style='color:#FF7A18'>info@infintylearning.net</a>.
            </p>
            <p style='text-align:center;color:#7A5C4A;font-size:12px;margin-top:8px'>
                © " . date('Y') . " Infinity Learning · <a href='https://infintylearning.net' style='color:#FF7A18'>infintylearning.net</a>
            </p>
        </div>";
    }

    /**
     * Render a single student's block within the parent summary email.
     */
    private static function renderChildBlock(array $s, string $dashboardUrl): string
    {
        $st = $s['student'];
        $name = htmlspecialchars($st['full_name'] ?: $st['email']);
        $classLine = array_filter([
            $st['current_class'] ?? '',
            $st['school_display_name'] ?? $st['school_name'] ?? '',
        ]);
        $classLine = htmlspecialchars(implode(' · ', $classLine));

        // ----- Attendance section -----
        $att = $s['attendance'];
        $total = $s['attendance_total'];
        $attHtml = '';
        if ($total > 0) {
            $pct = $s['attendance_pct'];
            $pctColor = $pct >= 80 ? '#16A34A' : ($pct >= 60 ? '#F59E0B' : '#DC2626');
            $attHtml = "
                <div style='background:#FFF3E7;border-radius:10px;padding:14px 16px;margin:14px 0'>
                    <div style='font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#7A5C4A;margin-bottom:8px'>📊 Attendance</div>
                    <div style='font-size:22px;font-weight:800;color:$pctColor;line-height:1'>$pct% <span style='font-size:13px;color:#7A5C4A;font-weight:600'>present</span></div>
                    <div style='font-size:12px;color:#7A5C4A;margin-top:6px'>
                        {$att['present']} present · {$att['absent']} absent · {$att['late']} late · {$att['excused']} excused
                    </div>
                </div>";
        } else {
            $attHtml = "
                <div style='background:#FFF3E7;border-radius:10px;padding:14px 16px;margin:14px 0'>
                    <div style='font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#7A5C4A;margin-bottom:6px'>📊 Attendance</div>
                    <div style='font-size:13px;color:#7A5C4A'>No attendance recorded this week.</div>
                </div>";
        }

        // ----- Certificates section -----
        $certHtml = '';
        if ($s['certificates']) {
            $rows = '';
            foreach ($s['certificates'] as $c) {
                $type = $c['certificate_type'] ?? 'participant';
                $badgeColor = $type === 'winner' ? '#16A34A' : ($type === 'runner_up' ? '#2563EB' : '#FF7A18');
                $typeLabel = $type === 'winner' ? '🏆 Winner' : ($type === 'runner_up' ? '🥈 Runner-up' : '🎓 Participant');
                $rows .= "
                    <div style='padding:8px 0;border-bottom:1px dashed #F3DECB'>
                        <div style='font-weight:700;color:#2B1B12;font-size:14px'>" . htmlspecialchars($c['title']) . "</div>
                        <div style='font-size:12px;color:$badgeColor;font-weight:700;margin-top:2px'>$typeLabel</div>
                    </div>";
            }
            $certHtml = "
                <div style='margin:14px 0'>
                    <div style='font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#7A5C4A;margin-bottom:8px'>🏅 New Certificates</div>
                    $rows
                </div>";
        }

        // ----- Results section -----
        $resultHtml = '';
        if ($s['results']) {
            $rows = '';
            foreach ($s['results'] as $r) {
                $rank = $r['position'] ? ' · Rank ' . (int)$r['position'] : '';
                $score = $r['score'] !== null ? ' · Score ' . rtrim(rtrim((string)$r['score'], '0'), '.') : '';
                $rows .= "
                    <div style='padding:8px 0;border-bottom:1px dashed #F3DECB'>
                        <div style='font-weight:700;color:#2B1B12;font-size:14px'>" . htmlspecialchars($r['activity_title']) . "</div>
                        <div style='font-size:12px;color:#7A5C4A;margin-top:2px'>" . htmlspecialchars(ucfirst($r['activity_category'])) . $rank . $score . "</div>
                    </div>";
            }
            $resultHtml = "
                <div style='margin:14px 0'>
                    <div style='font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#7A5C4A;margin-bottom:8px'>🏆 Activity Results</div>
                    $rows
                </div>";
        }

        // ----- Upcoming assignments -----
        $assignHtml = '';
        if ($s['assignments']) {
            $rows = '';
            foreach ($s['assignments'] as $a) {
                $when = $a['scheduled_date'] ? date('d M', strtotime($a['scheduled_date'])) : 'Soon';
                if ($a['scheduled_time']) $when .= ' · ' . substr($a['scheduled_time'], 0, 5);
                $rows .= "
                    <div style='padding:8px 0;border-bottom:1px dashed #F3DECB'>
                        <div style='font-weight:700;color:#2B1B12;font-size:14px'>" . htmlspecialchars($a['activity_title']) . "</div>
                        <div style='font-size:12px;color:#7A5C4A;margin-top:2px'>$when · " . htmlspecialchars($a['batch_name']) . "</div>
                    </div>";
            }
            $assignHtml = "
                <div style='margin:14px 0'>
                    <div style='font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#7A5C4A;margin-bottom:8px'>🎯 Upcoming Activities (from teacher)</div>
                    $rows
                </div>";
        }

        // ----- Upcoming open activities -----
        $upcomingHtml = '';
        if ($s['upcoming_activities']) {
            $rows = '';
            foreach ($s['upcoming_activities'] as $a) {
                $when = $a['event_date'] ? date('d M', strtotime($a['event_date'])) : 'TBD';
                $rows .= "
                    <div style='padding:8px 0;border-bottom:1px dashed #F3DECB'>
                        <div style='font-weight:700;color:#2B1B12;font-size:14px'>" . htmlspecialchars($a['title']) . "</div>
                        <div style='font-size:12px;color:#7A5C4A;margin-top:2px'>$when" . ($a['venue'] ? ' · ' . htmlspecialchars($a['venue']) : '') . "</div>
                    </div>";
            }
            $upcomingHtml = "
                <div style='margin:14px 0'>
                    <div style='font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:800;color:#7A5C4A;margin-bottom:8px'>📅 Open Events</div>
                    $rows
                </div>";
        }

        // If nothing happened, show a friendly empty note
        $anyContent = $total > 0 || $s['certificates'] || $s['results'] || $s['assignments'] || $s['upcoming_activities'];
        $emptyNote = !$anyContent
            ? "<p style='color:#7A5C4A;font-size:14px;line-height:1.6;margin:14px 0'>No activity recorded this week. A quieter week is fine — we'll have more to share next week.</p>"
            : '';

        return "
        <div style='border-top:3px solid #FF7A18;padding-top:16px;margin-top:24px'>
            <div style='font-size:11px;text-transform:uppercase;letter-spacing:.1em;font-weight:800;color:#FF7A18;margin-bottom:4px'>Student</div>
            <div style='font-family:Georgia,serif;font-size:22px;font-weight:700;color:#2B1B12;line-height:1.15'>$name</div>
            " . ($classLine ? "<div style='font-size:13px;color:#7A5C4A;margin-top:2px'>$classLine</div>" : "") . "
            $attHtml
            $certHtml
            $resultHtml
            $assignHtml
            $upcomingHtml
            $emptyNote
        </div>";
    }
}