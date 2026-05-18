<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }

require_once __DIR__ . '/../../config/db.php';
// $pdo is available from db.php

$user_id    = (int) $_SESSION['user_id'];
$first_name = htmlspecialchars($_SESSION['first_name'] ?? 'Student');
$last_name  = htmlspecialchars($_SESSION['last_name']  ?? '');
$full_name  = trim($first_name . ' ' . $last_name);
$avatar     = strtoupper(substr($first_name, 0, 1));

$clubs_result = $conn->query("
    SELECT c.id, c.name, c.acronym, c.category, c.description,
           c.logo_path, c.room, c.founded, c.status,
           COUNT(DISTINCT m.id) AS member_count,
           COUNT(DISTINCT e.id) AS event_count
    FROM clubs c
    LEFT JOIN members m ON m.club_id = c.id AND m.status = 'active'
    LEFT JOIN events  e ON e.club_id = c.id
    WHERE c.status = 'active'
    GROUP BY c.id ORDER BY c.name ASC
");
$clubs = [];
while ($row = $clubs_result->fetch_assoc()) { $clubs[] = $row; }

$cat_result = $conn->query("SELECT DISTINCT category FROM clubs WHERE status='active' AND category IS NOT NULL ORDER BY category ASC");
$categories = [];
while ($row = $cat_result->fetch_assoc()) { $categories[] = $row['category']; }

$mem_check = $conn->prepare("SELECT id FROM members WHERE user_id = ? AND status = 'active' LIMIT 1");
$mem_check->bind_param('i', $user_id); $mem_check->execute(); $mem_check->store_result();
$has_club = $mem_check->num_rows > 0; $mem_check->close();
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['president','vice president','officer','lead'])) {
    header('Location: index.php?page=officer_dashboard');
    exit;
}

$applied_ids = [];
$app_stmt = $conn->prepare("SELECT club_id FROM applications WHERE user_id = ? AND status = 'pending'");
$app_stmt->bind_param('i', $user_id); $app_stmt->execute();
$app_result = $app_stmt->get_result();
while ($row = $app_result->fetch_assoc()) { $applied_ids[] = (int) $row['club_id']; }
$app_stmt->close();

// ── Clubs the user is already an APPROVED member of ──────
$member_club_ids = [];
$mc_stmt = $conn->prepare("SELECT club_id FROM members WHERE user_id = ? AND status = 'active'");
$mc_stmt->bind_param('i', $user_id); $mc_stmt->execute();
$mc_result = $mc_stmt->get_result();
while ($row = $mc_result->fetch_assoc()) { $member_club_ids[] = (int) $row['club_id']; }
$mc_stmt->close();

// ── Pre-fill user profile data (student_profiles overrides users) ──
$user_data = $conn->prepare("
    SELECT
        COALESCE(sp.course,     u.course)     AS course,
        COALESCE(sp.year_level, u.year_level) AS year_level,
        COALESCE(sp.section,    u.section)    AS section,
        COALESCE(sp.phone,      u.phone)      AS phone,
        COALESCE(sp.student_id, u.student_id) AS student_id
    FROM users u
    LEFT JOIN student_profiles sp ON sp.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
");
$user_data->bind_param('i', $user_id); $user_data->execute();
$user_profile = $user_data->get_result()->fetch_assoc();
$user_data->close();

$conn->close();

// Profile picture
$picStmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$picStmt->execute([$user_id]);
$picFile    = $picStmt->fetchColumn();
$avatar_url = $picFile
    ? '/UNIFY(db)/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';

// Officer check (for sidebar MANAGEMENT link)
$is_officer = isset($_SESSION['role']) && in_array($_SESSION['role'], ['president', 'vice president', 'officer', 'lead']);

$prefill_course     = htmlspecialchars($user_profile['course']      ?? '');
$prefill_year       = htmlspecialchars($user_profile['year_level']  ?? '');
$prefill_section    = htmlspecialchars($user_profile['section']     ?? '');
$prefill_phone      = htmlspecialchars($user_profile['phone']       ?? '');
$prefill_student_id = htmlspecialchars($user_profile['student_id']  ?? '');

$cat_icons = [
    'Tech'=>'fa-microchip','Arts'=>'fa-palette','Sports'=>'fa-trophy','Science'=>'fa-flask',
    'Engineering'=>'fa-gear','Academic'=>'fa-book','Business'=>'fa-briefcase','Advocacy'=>'fa-hand-fist',
];
?>
