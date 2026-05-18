<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }
require_once __DIR__ . '/../../config/db.php';

$user_id    = (int) $_SESSION['user_id'];
$first_name = htmlspecialchars($_SESSION['first_name'] ?? 'Student');
$last_name  = htmlspecialchars($_SESSION['last_name']  ?? '');
$full_name  = trim($first_name . ' ' . $last_name);
$avatar     = strtoupper(substr($first_name, 0, 1));

// ── All applications ───────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT a.id, a.status, a.applied_at, a.reviewed_at,
           a.extras, a.course,
           c.id AS club_id, c.name AS club_name, c.category, c.logo_path, c.room,
           COUNT(DISTINCT m.id) AS member_count
    FROM applications a
    JOIN clubs c ON c.id = a.club_id
    LEFT JOIN members m ON m.club_id = c.id AND m.status = 'active'
    WHERE a.user_id = ?
    GROUP BY a.id
    ORDER BY a.applied_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$all_apps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$app = $all_apps[0] ?? null; // latest

// ── Nav ────────────────────────────────────────────────────────────────────
$mem = $conn->prepare("SELECT id FROM members WHERE user_id=? AND status='active' LIMIT 1");
$mem->bind_param('i', $user_id); $mem->execute(); $mem->store_result();
$has_club = $mem->num_rows > 0; $mem->close();

$has_pending_app = false;
foreach ($all_apps as $a) { if ($a['status'] === 'pending') { $has_pending_app = true; break; } }

// ── User created_at ────────────────────────────────────────────────────────
$u = $conn->prepare("SELECT created_at FROM users WHERE id=?");
$u->bind_param('i', $user_id); $u->execute();
$u_row = $u->get_result()->fetch_assoc(); $u->close();
$created_at = $u_row['created_at'] ?? null;

// ── Student profile ────────────────────────────────────────────────────────
$sp = $conn->prepare("SELECT student_id, course, year_level, phone FROM student_profiles WHERE user_id=?");
$sp->bind_param('i', $user_id); $sp->execute();
$profile = $sp->get_result()->fetch_assoc(); $sp->close();

// Also check users table
$uu = $conn->prepare("SELECT student_id, course, year_level FROM users WHERE id=?");
$uu->bind_param('i', $user_id); $uu->execute();
$u_profile = $uu->get_result()->fetch_assoc(); $uu->close();

$conn->close();

$student_id = htmlspecialchars($profile['student_id'] ?? $u_profile['student_id'] ?? '—');
$course_val = htmlspecialchars($profile['course']     ?? $u_profile['course']     ?? '—');
$year_level = htmlspecialchars($profile['year_level'] ?? $u_profile['year_level'] ?? '—');
$phone_val  = htmlspecialchars($profile['phone']      ?? '—');

if ($app) {
    $a_status        = $app['status'];
    $a_club          = htmlspecialchars($app['club_name']);
    $a_cat           = htmlspecialchars($app['category'] ?? '');
    $a_logo          = $app['logo_path'] ? htmlspecialchars($app['logo_path']) : '';
    $a_members       = (int)$app['member_count'];
    $a_applied       = $app['applied_at'] ? date('F j, Y \a\t g:i A', strtotime($app['applied_at'])) : '—';
    $a_applied_short = $app['applied_at'] ? date('F j, Y', strtotime($app['applied_at'])) : '—';
    $a_ref           = 'APP-' . str_pad($app['id'], 5, '0', STR_PAD_LEFT);
    $a_extras        = htmlspecialchars($app['extras'] ?? '');
    $a_course        = htmlspecialchars($app['course']  ?? $course_val);
    $a_gwa           = '—';

    $tl_submitted  = true;
    $tl_reviewing  = in_array($a_status, ['pending','approved','rejected']);
    $tl_decided    = in_array($a_status, ['approved','rejected']);
    $tl_access     = $a_status === 'approved';
}
?>