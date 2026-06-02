<?php
// ============================================================
//  UNIFY — Apply Handler
//  app/controllers/apply_handler_controller.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

require_once __DIR__ . '/../../config/db.php';

$user_id = (int) $_SESSION['user_id'];
$action  = trim($_POST['action'] ?? '');

// ── WITHDRAW ──────────────────────────────────────────────
if ($action === 'withdraw') {
    $app_id = (int)($_POST['app_id'] ?? 0);
    if (!$app_id) { echo json_encode(['success' => false, 'message' => 'Invalid application.']); exit; }
    $del = $pdo->prepare("DELETE FROM applications WHERE id=? AND user_id=? AND status='pending'");
    $del->execute([$app_id, $user_id]);
    echo $del->rowCount() > 0
        ? json_encode(['success' => true])
        : json_encode(['success' => false, 'message' => 'Could not withdraw. Application may already be reviewed.']);
    exit;
}

// ── SUBMIT APPLICATION ────────────────────────────────────
$club_id    = (int) ($_POST['club_id']    ?? 0);
$course     = trim($_POST['course']       ?? '');
$year       = trim($_POST['year']         ?? '');
$section    = trim($_POST['section']      ?? '');
$extras     = trim($_POST['extras']       ?? '');
$student_id = trim($_POST['student_id']   ?? '');
$phone      = trim($_POST['phone']        ?? '');

if (!$club_id || !$course || !$year || !$section || !$student_id || !$phone) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']); exit;
}

// Check duplicate pending
$check = $pdo->prepare("SELECT id FROM applications WHERE user_id=? AND club_id=? AND status='pending' LIMIT 1");
$check->execute([$user_id, $club_id]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending application for this club.']); exit;
}

// Check already a member
$mem = $pdo->prepare("SELECT id FROM members WHERE user_id=? AND club_id=? AND status='active' LIMIT 1");
$mem->execute([$user_id, $club_id]);
if ($mem->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You are already a member of this club.']); exit;
}

// ── LEADER DETECTION ──────────────────────────────────────
$leaderCheck = $pdo->prepare("
    SELECT m.role, c.name AS club_name
    FROM members m
    JOIN clubs c ON c.id = m.club_id
    WHERE m.user_id = ?
      AND m.role IN ('president','vice president','officer','lead')
      AND m.status = 'active'
    LIMIT 1
");
$leaderCheck->execute([$user_id]);
$leaderRow     = $leaderCheck->fetch(PDO::FETCH_ASSOC);
$is_leader     = (bool)$leaderRow;
$reviewer_type = $is_leader ? 'admin' : 'officer';

// ── Insert application ────────────────────────────────────
$stmt = $pdo->prepare("
    INSERT INTO applications
        (user_id, club_id, course, year, section, extras, student_id_no, phone, status, reviewer_type)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
");
$stmt->execute([$user_id, $club_id, $course, $year, $section, $extras, $student_id, $phone, $reviewer_type]);
$app_id = (int)$pdo->lastInsertId();

// ── Update student_profiles (correct table) ───────────────
// Uses INSERT ... ON DUPLICATE KEY so it works whether or not a profile row exists yet
$pdo->prepare("
    INSERT INTO student_profiles (user_id, student_id, phone, course, year_level, section)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        student_id = COALESCE(NULLIF(student_id, ''), VALUES(student_id)),
        phone      = COALESCE(NULLIF(phone, ''),      VALUES(phone)),
        course     = COALESCE(NULLIF(course, ''),     VALUES(course)),
        year_level = COALESCE(NULLIF(year_level, ''), VALUES(year_level)),
        section    = COALESCE(NULLIF(section, ''),    VALUES(section))
")->execute([$user_id, $student_id, $phone, $course, $year, $section]);

// ── Send Notifications ────────────────────────────────────
$applicantName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
$clubRow = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
$clubRow->execute([$club_id]);
$clubName = $clubRow->fetchColumn() ?: 'the club';

if ($is_leader) {
    // Notify admins — requires admin review
    $admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
    $ns = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, 'leader_application', ?, ?, 'index.php?page=dashboard')
    ");
    foreach ($admins as $adminId) {
        $ns->execute([
            $adminId,
            'Leader Application: ' . $applicantName,
            $applicantName . ' — who holds a leadership role in ' . $leaderRow['club_name'] . ' — has applied to join ' . $clubName . '. This requires your review.',
        ]);
    }
} else {
    // Notify club officers — goes to officer queue
    $officers = $pdo->prepare("
        SELECT m.user_id FROM members m
        WHERE m.club_id=? AND m.role IN ('president','vice president') AND m.status='active'
    ");
    $officers->execute([$club_id]);
    $officerIds = $officers->fetchAll(PDO::FETCH_COLUMN);
    $ns = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, 'new_application', ?, ?, 'index.php?page=officer_dashboard')
    ");
    foreach ($officerIds as $oid) {
        $ns->execute([
            $oid,
            'New Application for ' . $clubName,
            $applicantName . ' has applied to join your club. Review it on your Officer Dashboard.',
        ]);
    }
}

echo json_encode([
    'success'       => true,
    'is_leader'     => $is_leader,
    'reviewer_type' => $reviewer_type,
    'leader_note'   => $is_leader
        ? 'Your application has been forwarded to the Admin for review because you hold a leadership role in ' . htmlspecialchars($leaderRow['club_name']) . '. You will be notified once it has been reviewed.'
        : null,
]);