<?php
// ============================================================
//  UNIFY — Profile Handler
//  app/views/profile_handler.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$conn = new mysqli('localhost', 'u970217706_EGG', 'EGGPassword_Unify2C', 'u970217706_unify_db');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}
$conn->set_charset('utf8mb4');

$user_id = (int) $_SESSION['user_id'];
$action  = trim($_POST['action'] ?? '');

// ── Helper: send JSON and exit ────────────────────────────
function respond(bool $ok, string $msg = ''): void {
    echo json_encode(['success' => $ok, 'message' => $msg]);
    exit;
}

// ============================================================
//  ACTION: personal  (Personal Info + Contact)
// ============================================================
if ($action === 'personal') {

    $first_name  = trim($_POST['first_name']  ?? '');
    $last_name   = trim($_POST['last_name']   ?? '');
    $dob         = trim($_POST['date_of_birth'] ?? '');
    $gender      = trim($_POST['gender']      ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $phone       = trim($_POST['phone']       ?? '');
    $address     = trim($_POST['address']     ?? '');

    if ($first_name === '' || $last_name === '') {
        respond(false, 'First name and last name are required.');
    }

    $u = $conn->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
    $u->bind_param('ssi', $first_name, $last_name, $user_id);
    if (!$u->execute()) {
        respond(false, 'Failed to update name.');
    }
    $u->close();

    $sp = $conn->prepare("
        INSERT INTO student_profiles
            (user_id, date_of_birth, gender, nationality, phone, address, updated_at)
        VALUES
            (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            date_of_birth = VALUES(date_of_birth),
            gender        = VALUES(gender),
            nationality   = VALUES(nationality),
            phone         = VALUES(phone),
            address       = VALUES(address),
            updated_at    = NOW()
    ");
    $sp->bind_param('isssss', $user_id, $dob, $gender, $nationality, $phone, $address);
    if (!$sp->execute()) {
        respond(false, 'Failed to update profile details.');
    }
    $sp->close();

    respond(true, 'Profile updated successfully.');
}

// ============================================================
//  ACTION: academic
// ============================================================
if ($action === 'academic') {

    $student_id  = trim($_POST['student_id']    ?? '');
    $course      = trim($_POST['course']        ?? '');
    $year_level  = trim($_POST['year_level']    ?? '');
    $section     = trim($_POST['section']       ?? '');
    $department  = trim($_POST['department']    ?? '');
    $acad_year   = trim($_POST['academic_year'] ?? '');
    $campus      = trim($_POST['campus']        ?? '');

    $sp = $conn->prepare("
        INSERT INTO student_profiles
            (user_id, student_id, course, year_level, section, department, academic_year, campus, updated_at)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            student_id    = VALUES(student_id),
            course        = VALUES(course),
            year_level    = VALUES(year_level),
            section       = VALUES(section),
            department    = VALUES(department),
            academic_year = VALUES(academic_year),
            campus        = VALUES(campus),
            updated_at    = NOW()
    ");
    $sp->bind_param('isssssss',
        $user_id, $student_id, $course, $year_level,
        $section, $department, $acad_year, $campus
    );
    if (!$sp->execute()) {
        respond(false, 'Failed to update academic info. Error: ' . $sp->error);
    }
    $sp->close();

    respond(true, 'Academic info updated successfully.');
}

// ============================================================
//  ACTION: password
// ============================================================
if ($action === 'password') {

    $current_pw = $_POST['current_password'] ?? '';
    $new_pw     = $_POST['new_password']     ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';

    if ($current_pw === '' || $new_pw === '' || $confirm_pw === '') {
        respond(false, 'All password fields are required.');
    }

    if (strlen($new_pw) < 8) {
        respond(false, 'New password must be at least 8 characters.');
    }

    if ($new_pw !== $confirm_pw) {
        respond(false, 'New passwords do not match.');
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($current_pw, $row['password'])) {
        respond(false, 'Current password is incorrect.');
    }

    $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
    $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $upd->bind_param('si', $new_hash, $user_id);
    if (!$upd->execute()) {
        respond(false, 'Failed to update password.');
    }
    $upd->close();

    respond(true, 'Password changed successfully.');
}

respond(false, 'Unknown action.');