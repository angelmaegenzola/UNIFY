<?php
// ============================================================
//  UNIFY — Student Profile Page
//  app/views/studentprofile.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Admins should not see the student profile page
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: index.php?page=adminprofile');
    exit;
}

$conn = new mysqli('localhost', 'u970217706_EGG', 'EGGPassword_Unify2C', 'u970217706_unify_db');
if ($conn->connect_error) die('Database connection failed.');
$conn->set_charset('utf8mb4');

$user_id = (int) $_SESSION['user_id'];

// ── Fetch user ───────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT id, first_name, last_name, email, username, role, created_at,
           two_fa_enabled, profile_picture
    FROM users WHERE id = ?
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

// ── Fetch student_profiles ───────────────────────────────────
$sp_stmt = $conn->prepare("
    SELECT phone, date_of_birth, gender, nationality, address,
           course, year_level, section, academic_year, department, campus, student_id
    FROM student_profiles WHERE user_id = ?
");
$sp_stmt->bind_param('i', $user_id);
$sp_stmt->execute();
$profile = $sp_stmt->get_result()->fetch_assoc();
$sp_stmt->close();

// ── Stats ────────────────────────────────────────────────────
$stats_stmt = $conn->prepare("
    SELECT
        (SELECT COUNT(*) FROM applications    WHERE user_id = ?)                       AS app_count,
        (SELECT COUNT(*) FROM members         WHERE user_id = ? AND status = 'active') AS club_count,
        (SELECT COUNT(*) FROM event_attendees WHERE user_id = ?)                       AS event_count
");
$stats_stmt->bind_param('iii', $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

// ── Nav checks ───────────────────────────────────────────────
$mem = $conn->prepare("SELECT id FROM members WHERE user_id = ? AND status = 'active' LIMIT 1");
$mem->bind_param('i', $user_id);
$mem->execute();
$mem->store_result();
$has_club = $mem->num_rows > 0;
$mem->close();

$pend = $conn->prepare("SELECT id FROM applications WHERE user_id = ? AND status = 'pending' LIMIT 1");
$pend->bind_param('i', $user_id);
$pend->execute();
$pend->store_result();
$has_pending = $pend->num_rows > 0;
$pend->close();

// ── Recent activity ──────────────────────────────────────────
$act_stmt = $conn->prepare("
    SELECT a.status, a.applied_at, c.name AS club_name
    FROM applications a
    JOIN clubs c ON c.id = a.club_id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$act_stmt->bind_param('i', $user_id);
$act_stmt->execute();
$activities = $act_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$act_stmt->close();

$conn->close();

// ── Display helpers ──────────────────────────────────────────
$first_name  = htmlspecialchars($user['first_name'] ?? '');
$last_name   = htmlspecialchars($user['last_name']  ?? '');
$full_name   = trim($first_name . ' ' . $last_name);
$initials    = strtoupper(
    substr($user['first_name'] ?? '', 0, 1) .
    substr($user['last_name']  ?? '', 0, 1)
);
$email       = htmlspecialchars($user['email']      ?? '');
$username    = htmlspecialchars($user['username']   ?? '');
$created_at  = $user['created_at'] ? date('F j, Y', strtotime($user['created_at'])) : '—';
$twoFaEnabled    = !empty($user['two_fa_enabled']);
$profile_picture = $user['profile_picture'] ?? '';
$avatar_url = '';
if ($profile_picture) {
    $filename  = basename($profile_picture);
    if ($filename) {
        $disk_path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/pictures/profile_pictures/' . $filename;
        $avatar_url = file_exists($disk_path)
            ? '/assets/pictures/profile_pictures/' . htmlspecialchars($filename)
            : '';
    }
}

$phone       = htmlspecialchars($profile['phone']         ?? '—');
$dob         = htmlspecialchars($profile['date_of_birth'] ?? '—');
$gender      = htmlspecialchars($profile['gender']        ?? '—');
$nationality = htmlspecialchars($profile['nationality']   ?? 'Filipino');
$address     = htmlspecialchars($profile['address']       ?? '—');
$course      = htmlspecialchars($profile['course']        ?? '—');
$year_level  = htmlspecialchars($profile['year_level']    ?? '—');
$section     = htmlspecialchars($profile['section']       ?? '—');
$acad_year   = htmlspecialchars($profile['academic_year'] ?? '—');
$department  = htmlspecialchars($profile['department']    ?? '—');
$campus      = htmlspecialchars($profile['campus']        ?? '—');
$student_id  = htmlspecialchars($profile['student_id']    ?? '—');

$app_count   = (int) ($stats['app_count']   ?? 0);
$club_count  = (int) ($stats['club_count']  ?? 0);
$event_count = (int) ($stats['event_count'] ?? 0);

if ($club_count > 0) {
    $acct_status  = '<span class="status-pill active"><i class="fas fa-circle-check"></i> Active Member</span>';
    $access_level = 'Full Member';
} elseif ($app_count > 0) {
    $acct_status  = '<span class="status-pill pending"><i class="fas fa-hourglass-half"></i> Pending</span>';
    $access_level = 'Basic (Pre-member)';
} else {
    $acct_status  = '<span class="status-pill inactive"><i class="fas fa-circle-xmark"></i> No Application</span>';
    $access_level = 'Basic (Pre-member)';
}

// ── Raw values for modal pre-fill (unescaped for JS) ─────────
$raw_dob         = $profile['date_of_birth'] ?? '';
$raw_gender      = $profile['gender']        ?? '';
$raw_nationality = $profile['nationality']   ?? 'Filipino';
$raw_phone       = $profile['phone']         ?? '';
$raw_address     = $profile['address']       ?? '';
$raw_student_id  = ($profile['student_id']   ?? '') ?: '';
$raw_course      = ($profile['course']       ?? '') ?: '';
$raw_year        = ($profile['year_level']   ?? '') ?: '';
$raw_section     = ($profile['section']      ?? '') ?: '';
$raw_dept        = ($profile['department']   ?? '') ?: '';
$raw_acad_year   = ($profile['academic_year']?? '') ?: '';
$raw_campus      = ($profile['campus']       ?? '') ?: '';
?>
