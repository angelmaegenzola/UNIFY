<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$user_id = $_SESSION['user_id'];

// ── Fetch user from DB ──────────────────────────────────────────────────────
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$profile_picture = $user['profile_picture'] ?? '';
$avatar_url      = $profile_picture
    ? '/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($profile_picture))
    : '';
if (!$user) {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

$twoFaEnabled = !empty($user['two_fa_enabled']);

// ── Fetch clubs this user belongs to ───────────────────────────────────────
$stmt = $pdo->prepare(
    'SELECT c.id, c.name, c.category, c.logo_path, m.role
     FROM members m
     JOIN clubs c ON c.id = m.club_id
     WHERE m.user_id = ? AND m.status = "active"
     ORDER BY m.role ASC'
);
$stmt->execute([$user_id]);
$clubs = $stmt->fetchAll();

// ── Sidebar club context (first/primary club) ───────────────────────────────
// Pick the club where the user has the highest officer role, or just the first.
$officerClub = null;
$officerRole = 'member';

$rolePriority = ['president' => 1, 'vice president' => 2, 'officer' => 3, 'member' => 4];

foreach ($clubs as $c) {
    $priority = $rolePriority[strtolower($c['role'])] ?? 99;
    if ($officerClub === null || $priority < ($rolePriority[strtolower($officerRole)] ?? 99)) {
        $officerClub = $c;
        $officerRole = $c['role'];
    }
}

// Fallback if the user has no clubs at all
if ($officerClub === null) {
    $officerClub = [];
    $officerRole = $user['role'] ?? 'member';
}

$clubName    = $officerClub['name']      ?? 'No Club';
$clubInitial = strtoupper(substr($clubName, 0, 1));

// ── Sidebar / topbar user identity ─────────────────────────────────────────
$userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$userInit = strtoupper(substr($user['first_name'] ?? 'U', 0, 1));

// ── Count stats ─────────────────────────────────────────────────────────────
$stmt = $pdo->prepare('SELECT COUNT(*) FROM members WHERE user_id = ? AND status = "active"');
$stmt->execute([$user_id]);
$clubs_count = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM event_attendees WHERE user_id = ?');
$stmt->execute([$user_id]);
$events_count = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM announcements
     WHERE club_id IN (SELECT club_id FROM members WHERE user_id = ?)'
);
$stmt->execute([$user_id]);
$announcements_count = (int) $stmt->fetchColumn();

// ── Fetch student_id (LRN) for QR code ─────────────────────────────────────
$stmt = $pdo->prepare('SELECT student_id FROM student_profiles WHERE user_id = ? LIMIT 1');
$stmt->execute([$user_id]);
$student_id_raw = $stmt->fetchColumn() ?: '';
$qr_url = $student_id_raw
    ? 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($student_id_raw) . '&bgcolor=ffffff&color=0d2b1a&qzone=2'
    : '';

// ── Handle POST ─────────────────────────────────────────────────────────────
$success_msg = '';
$error_msg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Update profile ──────────────────────────────────────────────────────
    if ($action === 'update_profile') {
        $first_name = trim($_POST['first_name']  ?? '');
        $last_name  = trim($_POST['last_name']   ?? '');
        $username   = trim($_POST['username']    ?? '');
        $email      = trim($_POST['email']       ?? '');
        $phone      = trim($_POST['phone']       ?? '');
        $bio        = trim($_POST['bio']         ?? '');
        $department = trim($_POST['department']  ?? '');
        $course     = trim($_POST['course']      ?? '');
        $year_level = trim($_POST['year_level']  ?? '');

        if (empty($first_name) || empty($last_name) || empty($email)) {
            $error_msg = 'First name, last name, and email are required.';
        } else {
            // Check if username or email already taken by someone else
            $stmt = $pdo->prepare(
                'SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1'
            );
            $stmt->execute([$username, $email, $user_id]);

            if ($stmt->fetch()) {
                $error_msg = 'That username or email is already in use.';
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE users
                     SET first_name=?, last_name=?, username=?, email=?,
                         phone=?, bio=?, department=?, course=?, year_level=?
                     WHERE id=?'
                );
                $stmt->execute([
                    $first_name, $last_name, $username, $email,
                    $phone, $bio, $department, $course, $year_level,
                    $user_id,
                ]);

                // Keep session in sync
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name']  = $last_name;
                $_SESSION['username']   = $username;

                // Re-fetch updated user row
                $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                // Refresh derived display variables
                $userName = trim($first_name . ' ' . $last_name);
                $userInit = strtoupper(substr($first_name, 0, 1));

                $success_msg = 'Profile updated successfully.';
            }
        }

    // ── Update password ─────────────────────────────────────────────────────
    } elseif ($action === 'update_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $error_msg = 'All password fields are required.';
        } elseif ($new !== $confirm) {
            $error_msg = 'New passwords do not match.';
        } elseif (strlen($new) < 8) {
            $error_msg = 'Password must be at least 8 characters.';
        } elseif (!password_verify($current, $user['password_hash'])) {
            $error_msg = 'Current password is incorrect.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?');
            $stmt->execute([password_hash($new, PASSWORD_BCRYPT), $user_id]);
            $success_msg = 'Password changed successfully.';
        }

    // ── Update notifications ────────────────────────────────────────────────
    } elseif ($action === 'update_notifications') {
        // Extend this block when you persist notification prefs to the DB.
        $success_msg = 'Notification preferences saved.';
    }
}

// ── Helper functions ────────────────────────────────────────────────────────
if (!function_exists('clubIcon')) {
    function clubIcon(string $category): string {
        return match (strtolower($category)) {
            'tech'        => 'fa-laptop-code',
            'business'    => 'fa-briefcase',
            'science'     => 'fa-flask',
            'arts'        => 'fa-palette',
            'academic'    => 'fa-book',
            'advocacy'    => 'fa-bullhorn',
            'engineering' => 'fa-gears',
            'sports'      => 'fa-trophy',
            default       => 'fa-building-columns',
        };
    }
}

if (!function_exists('clubColor')) {
    function clubColor(string $category): string {
        return match (strtolower($category)) {
            'tech'        => '#2563eb',
            'business'    => '#d4a017',
            'science'     => '#0e7c6e',
            'arts'        => '#9333ea',
            'academic'    => '#0284c7',
            'advocacy'    => '#dc2626',
            'engineering' => '#ea580c',
            'sports'      => '#16a34a',
            default       => '#1a5c38',
        };
    }
}

if (!function_exists('roleBadge')) {
    function roleBadge(string $role): string {
        return match (strtolower($role)) {
            'president', 'admin'            => 'cbadge-admin',
            'vice president', 'officer'     => 'cbadge-officer',
            default                         => 'cbadge-member',
        };
    }
}