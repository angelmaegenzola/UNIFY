<?php
// ============================================================
//  UNIFY — Already Member Handler
//  Routes through the applications table for admin approval.
//  No direct insert into members — all roles require review.
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/config/db.php';

$user_id    = (int) $_SESSION['user_id'];
$club_id    = (int) ($_POST['club_id']    ?? 0);
$first_name = trim($_POST['first_name']   ?? '');
$last_name  = trim($_POST['last_name']    ?? '');
$course     = trim($_POST['course']       ?? '');
$year       = trim($_POST['year']         ?? '');
$role       = trim($_POST['role']         ?? 'member');
$student_id = trim($_POST['student_id']   ?? '');
$phone      = trim($_POST['phone']        ?? '');

if (!$club_id || !$first_name || !$last_name || !$course || !$year) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']); exit;
}

// Only allow member role — officer/president claims always go to admin
$allowed_roles = ['member', 'officer', 'lead', 'president', 'vice president'];
if (!in_array($role, $allowed_roles)) $role = 'member';

// Any role above member must be verified by admin
$reviewer_type = ($role === 'member') ? 'officer' : 'admin';

// Check if already an active member
$check = $pdo->prepare("SELECT id FROM members WHERE user_id = ? AND club_id = ? AND status = 'active' LIMIT 1");
$check->execute([$user_id, $club_id]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You are already registered as a member of this club.']); exit;
}

// Check if already has a pending application
$appCheck = $pdo->prepare("SELECT id FROM applications WHERE user_id = ? AND club_id = ? AND status = 'pending' LIMIT 1");
$appCheck->execute([$user_id, $club_id]);
if ($appCheck->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending application for this club.']); exit;
}

// Verify club exists and is active
$clubRow = $pdo->prepare("SELECT name FROM clubs WHERE id = ? AND status = 'active' LIMIT 1");
$clubRow->execute([$club_id]);
$club = $clubRow->fetch(PDO::FETCH_ASSOC);
if (!$club) {
    echo json_encode(['success' => false, 'message' => 'Club not found.']); exit;
}

try {
    // Insert into applications — never directly into members
    $stmt = $pdo->prepare("
        INSERT INTO applications
            (user_id, club_id, course, year, section, extras, student_id_no, phone, status, reviewer_type)
        VALUES (?, ?, ?, ?, '', ?, ?, ?, 'pending', ?)
    ");
    $extras = 'Previously a member claim. Stated role: ' . $role . '. Name provided: ' . $first_name . ' ' . $last_name . '.';
    $stmt->execute([$user_id, $club_id, $course, $year, $extras, $student_id, $phone, $reviewer_type]);

    // Also update student_profiles so the info is saved to their profile
    $pdo->prepare("
        INSERT INTO student_profiles (user_id, student_id, phone, course, year_level)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            student_id = COALESCE(NULLIF(student_id, ''), VALUES(student_id)),
            phone      = COALESCE(NULLIF(phone, ''),      VALUES(phone)),
            course     = COALESCE(NULLIF(course, ''),     VALUES(course)),
            year_level = COALESCE(NULLIF(year_level, ''), VALUES(year_level))
    ")->execute([$user_id, $student_id, $phone, $course, $year]);

    // Notify the right reviewer
    $applicantName = trim(($_SESSION['first_name'] ?? $first_name) . ' ' . ($_SESSION['last_name'] ?? $last_name));
    $clubName      = $club['name'];

    if ($reviewer_type === 'admin') {
        // Elevated role claim — goes straight to admin
        $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_COLUMN);
        $ns = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, link)
            VALUES (?, 'leader_application', ?, ?, 'index.php?page=dashboard')
        ");
        foreach ($admins as $adminId) {
            $ns->execute([
                $adminId,
                'Role Claim Requires Review: ' . $applicantName,
                $applicantName . ' has claimed a ' . $role . ' role in ' . $clubName . ' and requires admin verification.',
            ]);
        }
    } else {
        // Regular member claim — goes to club officers
        $officers = $pdo->prepare("
            SELECT m.user_id FROM members m
            WHERE m.club_id = ? AND m.role IN ('president','vice president') AND m.status = 'active'
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
                'Membership Claim for ' . $clubName,
                $applicantName . ' has claimed prior membership in your club and is awaiting verification.',
            ]);
        }
    }

    $msg = ($reviewer_type === 'admin')
        ? 'Your claim has been sent to the admin for review since you indicated a leadership role.'
        : 'Your membership claim has been submitted and is pending officer verification.';

    echo json_encode(['success' => true, 'message' => $msg]);

} catch (PDOException $e) {
    error_log('[UNIFY] already_member_handler error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
exit;