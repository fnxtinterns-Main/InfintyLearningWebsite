<?php
declare(strict_types=1);

namespace App;

final class Importer
{
    /** Allowed enum values reused across imports. */
    public static function allowedGenders(): array
    {
        return ['male','female','other','prefer_not_to_say'];
    }

    public static function allowedBoards(): array
    {
        return ['CBSE','ICSE','State Board','IB','IGCSE','Other'];
    }

    /** Normalize a CSV column header to snake_case: "Full Name" → "full_name" */
    public static function normalizeHeader(string $h): string
    {
        $h = strtolower(trim($h));
        $h = preg_replace('/[^a-z0-9]+/', '_', $h) ?? '';
        return trim($h, '_');
    }

        /** Normalize a phone number to canonical +91XXXXXXXXXX form. */
    public static function normalizePhone(string $phone): string
    {
        return \App\Helper::normalizePhone($phone);
    }

    /** Generate a random password that always has letters + numbers (passes Validator). */
    public static function generatePassword(int $length = 10): string
    {
        $letters = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $digits  = '23456789';
        $all     = $letters . $digits;

        $pw  = $letters[random_int(0, strlen($letters) - 1)];
        $pw .= $digits[random_int(0, strlen($digits) - 1)];
        for ($i = 0; $i < $length - 2; $i++) {
            $pw .= $all[random_int(0, strlen($all) - 1)];
        }
        return str_shuffle($pw);
    }

    /**
     * Parse a CSV file and validate every row against the DB.
     *
     * @return array{rows: array<int, array>, summary: array{total:int,new:int,existing:int,errors:int}}
     */
    public static function parseAndValidateCsv(string $path, int $batchId): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return ['rows' => [], 'summary' => ['total'=>0,'new'=>0,'existing'=>0,'errors'=>0]];
        }

        // Read header (strip UTF-8 BOM if present)
        $header = fgetcsv($handle);
        if ($header && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]);
        }
        $header = array_map([self::class, 'normalizeHeader'], $header ?: []);

        $requiredCols = ['full_name','email','phone','current_class'];

        $rows     = [];
        $summary  = ['total'=>0, 'new'=>0, 'existing'=>0, 'errors'=>0];
        $db       = Database::connect();
        $rowNum   = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip completely empty lines
            if (count($line) === 0 || (count($line) === 1 && trim((string)$line[0]) === '')) {
                continue;
            }

            $raw = [];
            foreach ($header as $i => $col) {
                $raw[$col] = isset($line[$i]) ? trim((string)$line[$i]) : '';
            }

            $data = [
                'full_name'             => $raw['full_name'] ?? '',
                'email'                 => strtolower($raw['email'] ?? ''),
                'phone'                 => self::normalizePhone($raw['phone'] ?? ''),
                'password'              => $raw['password'] ?? '',
                'gender'                => strtolower($raw['gender'] ?? ''),
                'age'                   => $raw['age'] ?? '',
                'school_name'           => $raw['school_name'] ?? '',
                'school_board'          => $raw['school_board'] ?? '',
                'current_class'         => $raw['current_class'] ?? '',
                'academic_year'         => $raw['academic_year'] ?? '',
                'city'                  => $raw['city'] ?? '',
                'state'                 => $raw['state'] ?? '',
                'interests'             => $raw['interests'] ?? '',
                'guardian_name'         => $raw['guardian_name'] ?? '',
                'guardian_relationship' => strtolower($raw['guardian_relationship'] ?? '') ?: 'guardian',
                'guardian_phone'        => self::normalizePhone($raw['guardian_phone'] ?? ''),
                'guardian_email'        => strtolower($raw['guardian_email'] ?? ''),
                'guardian_occupation'   => $raw['guardian_occupation'] ?? '',
                'guardian_city'         => $raw['guardian_city'] ?? '',
            ];

            $summary['total']++;

            // --- Validation ---
            $errors = [];
            foreach ($requiredCols as $c) {
                if (($data[$c] ?? '') === '') $errors[] = "Missing $c";
            }
            if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email";
            }
            if ($data['phone'] !== '' && !preg_match('/^\+?[0-9]{7,15}$/', $data['phone'])) {
                $errors[] = "Invalid phone";
            }
            if ($data['gender'] !== '' && !in_array($data['gender'], self::allowedGenders(), true)) {
                $data['gender'] = ''; // silently drop invalid gender
            }
            if ($data['guardian_phone'] !== '' && !preg_match('/^\+?[0-9]{7,15}$/', $data['guardian_phone'])) {
                $errors[] = "Invalid guardian phone";
            }

            if ($errors) {
                $summary['errors']++;
                $rows[] = [
                    'row'    => $rowNum,
                    'data'   => $data,
                    'status' => 'error',
                    'error'  => implode('; ', $errors),
                ];
                continue;
            }

            // --- Duplicate / existing-user detection ---
            $existing = null;
            if ($data['email'] !== '') {
                $q = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $q->execute([$data['email']]);
                $existing = $q->fetch() ?: null;
            }
            if (!$existing && $data['phone'] !== '') {
                $q = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
                $q->execute([$data['phone']]);
                $existing = $q->fetch() ?: null;
            }

            if ($existing) {
                // Already in this batch?
                $q = $db->prepare("SELECT 1 FROM batch_students WHERE batch_id = ? AND student_user_id = ? LIMIT 1");
                $q->execute([$batchId, (int)$existing['id']]);
                if ($q->fetch()) {
                    $rows[] = [
                        'row'              => $rowNum,
                        'data'             => $data,
                        'status'           => 'already_in_batch',
                        'existing_user_id' => (int)$existing['id'],
                    ];
                    continue;
                }
                $summary['existing']++;
                $rows[] = [
                    'row'              => $rowNum,
                    'data'             => $data,
                    'status'           => 'existing',
                    'existing_user_id' => (int)$existing['id'],
                ];
            } else {
                $summary['new']++;
                $rows[] = [
                    'row'    => $rowNum,
                    'data'   => $data,
                    'status' => 'new',
                ];
            }
        }

        fclose($handle);
        return ['rows' => $rows, 'summary' => $summary];
    }
}