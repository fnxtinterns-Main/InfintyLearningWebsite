<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

define('APP_ROOT', $appRoot);
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Timetable, Helper};

Session::start();

$user = Auth::user();
$schoolId = 0;
if (Auth::isSchoolAdmin()) {
    $schoolId = (int)Auth::requireSchoolAdmin();
} elseif ($user && Auth::role() === 'teacher' && !empty($user['school_id'])) {
    $schoolId = (int)$user['school_id'];
} else {
    $schoolId = (int)Auth::requireSchoolAdmin();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/school/timetable');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid token.');
    Helper::redirect('/school/timetable');
}

$action = $_POST['action'] ?? 'save';
$class = urlencode($_POST['class'] ?? 'Grade 8');
$section = urlencode($_POST['section'] ?? 'A');

try {
    if ($action === 'save') {
        $res = Timetable::saveSlot($_POST, $schoolId);
        if (!$res['success']) {
            Session::flash('error', $res['error']);
        } else {
            Session::flash('success', 'Routine period slot updated successfully.');
        }
    } elseif ($action === 'delete') {
        $slotId = (int)($_POST['slot_id'] ?? 0);
        Timetable::deleteSlot($slotId, $schoolId);
        Session::flash('success', 'Period slot cleared.');
    } elseif ($action === 'add_class') {
        $newClass = trim($_POST['class_name'] ?? '');
        if ($newClass !== '') {
            Timetable::addCustomClass($schoolId, $newClass);
            Session::flash('success', "Class '{$newClass}' added successfully.");
            $class = urlencode($newClass);
        }
    } elseif ($action === 'add_section') {
        $newSection = trim($_POST['section_name'] ?? '');
        if ($newSection !== '') {
            Timetable::addCustomSection($schoolId, $newSection);
            Session::flash('success', "Section '{$newSection}' added successfully.");
            $section = urlencode($newSection);
        }
    } elseif ($action === 'save_bell_schedule') {
        $periodsRaw = $_POST['periods'] ?? [];
        $periods = [];
        if (is_array($periodsRaw)) {
            foreach ($periodsRaw as $pNum => $pData) {
                $start = trim($pData['start'] ?? '');
                $end = trim($pData['end'] ?? '');
                if (strlen($start) === 5) $start .= ':00';
                if (strlen($end) === 5) $end .= ':00';

                $periods[] = [
                    'period_number' => (int)$pNum,
                    'label'         => trim($pData['label'] ?? "Period {$pNum}"),
                    'start'         => $start,
                    'end'           => $end,
                    'is_break'      => !empty($pData['is_break']) ? 1 : 0
                ];
            }
        }
        if (!empty($periods)) {
            Timetable::saveBellSchedule($schoolId, $periods);
            Session::flash('success', 'Bell schedule and timing slots updated successfully.');
        }
    }
} catch (\Throwable $e) {
    error_log('Timetable save error: ' . $e->getMessage());
    Session::flash('error', 'Error: ' . $e->getMessage());
}

$redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : "/school/timetable?class={$class}&section={$section}";
Helper::redirect($redirect);
