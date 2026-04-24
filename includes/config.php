<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'findit_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'FindIt');
define('UNIVERSITY', 'Your University Name');
define('UNI_EMAIL', '@university.edu');
define('BASE_URL', 'http://localhost/findit/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

define('CAMPUS_LOCATIONS', [
    'Central Library', 'Main Canteen', 'Hostel Block A', 'Hostel Block B',
    'Hostel Block C', 'Hostel Block D', 'Lab Block - CS Lab 1',
    'Lab Block - CS Lab 2', 'Lab Block - CS Lab 3', 'Lecture Hall 1',
    'Lecture Hall 2', 'Lecture Hall 3', 'Lecture Hall 4', 'Seminar Hall',
    'Admin Block', 'Sports Ground', 'Parking Lot - Gate 1',
    'Parking Lot - Gate 2', 'Main Gate', 'Medical Centre',
    'Boys Common Room', 'Girls Common Room', 'Department Block'
]);

define('REPORT_CATEGORIES', [
    'id_card' => 'ID Card',
    'library_card' => 'Library Card',
    'laptop' => 'Laptop',
    'phone' => 'Phone',
    'wallet' => 'Wallet',
    'keys' => 'Keys',
    'stationery' => 'Stationery',
    'clothing' => 'Clothing',
    'book' => 'Book',
    'calculator' => 'Calculator',
    'usb_drive' => 'USB Drive',
    'earbuds' => 'Earbuds',
    'other' => 'Other'
]);

define('DEPARTMENTS', [
    'Computer Science', 'Electronics', 'Mechanical', 'Civil', 'MBA',
    'Administration', 'Security Dept', 'Physics', 'Chemistry',
    'Mathematics', 'Biotechnology', 'Economics', 'English', 'Law'
]);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function is_admin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function human_datetime(?string $datetime): string
{
    if (!$datetime) {
        return '-';
    }

    return date('d M Y, h:i A', strtotime($datetime));
}

function human_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('d M Y', strtotime($date));
}

function human_time(?string $time): string
{
    if (!$time) {
        return '-';
    }

    return date('h:i A', strtotime($time));
}

function valid_uni_email(string $email): bool
{
    return str_ends_with(strtolower(trim($email)), strtolower(UNI_EMAIL));
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'active' => 'badge-active',
        'resolved' => 'badge-resolved',
        'claimed', 'verified' => 'badge-claimed',
        'disputed' => 'badge-disputed',
        default => 'badge-active'
    };
}

function report_type_badge(string $type): string
{
    return $type === 'lost' ? 'badge-lost' : 'badge-found';
}

function report_location(array $report): string
{
    if (isset($report['campus_location']) && trim((string) $report['campus_location']) !== '') {
        return (string) $report['campus_location'];
    }
    if (isset($report['location']) && trim((string) $report['location']) !== '') {
        return (string) $report['location'];
    }

    return 'Campus';
}

function generate_code(int $length = 8): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return strtoupper($code);
}

if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}
?>
