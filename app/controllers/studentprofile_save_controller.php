<?php
// ============================================================
//  UNIFY — Student Profile Save Controller
//  app/controllers/studentprofile_save_controller.php
//
//  Responds to AJAX POST requests from studentprofile.js
//  Endpoint: index.php?page=studentprofile_save
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Auth guard
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/config/db.php';

$user_id = (int) $_SESSION['user_id'];
$action  = trim($_POST['action'] ?? '');

/* ─────────────────────────────────────────────────────────────
   Helper: sanitize string input
───────────────────────────────────────────────────────────── */
function sp_clean(string $key, int $maxLen = 255): string {
    $val = trim($_POST[$key] ?? '');
    return mb_substr(htmlspecialchars($val, ENT_QUOTES, 'UTF-8'), 0, $maxLen);
}

function sp_raw(string $key, int $maxLen = 255): string {
    return mb_substr(trim($_POST[$key] ?? ''), 0, $maxLen);
}

/* ═══════════════════════════════════════════════════════════════
   ACTION: save_personal
═══════════════════════════════════════════════════════════════ */
if ($action === 'save_personal') {

    $first_name  = sp_raw('first_name', 80);
    $last_name   = sp_raw('last_name', 80);
    $email       = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $username    = sp_raw('username', 60);
    $phone       = sp_raw('phone', 30);
    $dob         = sp_raw('dob', 10);
    $gender      = sp_raw('gender', 30);
    $nationality = sp_raw('nationality', 60);
    $address     = sp_raw('address', 255);

    // Validation
    if (!$first_name || !$last_name) {
        echo json_encode(['success' => false, 'message' => 'First and last name are required.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    // Check email uniqueness (ignore current user)
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
    $check->execute([$email, $user_id]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'That email is already in use by another account.']);
        exit;
    }

    // Check username uniqueness
    if ($username) {
        $uchk = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
        $uchk->execute([$username, $user_id]);
        if ($uchk->fetch()) {
            echo json_encode(['success' => false, 'message' => 'That username is already taken.']);
            exit;
        }
    }

    $pdo->beginTransaction();
    try {
        // Update users table — now includes phone
        $stmt = $pdo->prepare('
            UPDATE users SET first_name = ?, last_name = ?, email = ?, username = ?, phone = ?
            WHERE id = ?
        ');
        $stmt->execute([$first_name, $last_name, $email, $username ?: null, $phone ?: null, $user_id]);

        // Upsert student_profiles
        $dobVal = ($dob && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) ? $dob : null;

        $upsert = $pdo->prepare('
            INSERT INTO student_profiles (user_id, phone, date_of_birth, gender, nationality, address)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                phone         = VALUES(phone),
                date_of_birth = VALUES(date_of_birth),
                gender        = VALUES(gender),
                nationality   = VALUES(nationality),
                address       = VALUES(address)
        ');
        $upsert->execute([
            $user_id,
            $phone       ?: null,
            $dobVal,
            $gender      ?: null,
            $nationality ?: null,
            $address     ?: null,
        ]);

        $pdo->commit();

        // Update session
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name']  = $last_name;

        echo json_encode(['success' => true, 'message' => 'Personal information updated successfully!']);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('[UNIFY] save_personal error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    exit;
}

/* ═══════════════════════════════════════════════════════════════
   ACTION: save_academic
═══════════════════════════════════════════════════════════════ */
if ($action === 'save_academic') {

    $student_id    = sp_raw('student_id', 30);
    $department    = sp_raw('department', 120);
    $course        = sp_raw('course', 80);
    $year_level    = sp_raw('year_level', 20);
    $section       = sp_raw('section', 30);
    $academic_year = sp_raw('academic_year', 20);
    $campus        = sp_raw('campus', 80);

    $pdo->beginTransaction();
    try {
        // Update student_profiles
        $upsert = $pdo->prepare('
            INSERT INTO student_profiles
                (user_id, student_id, department, course, year_level, section, academic_year, campus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                student_id    = VALUES(student_id),
                department    = VALUES(department),
                course        = VALUES(course),
                year_level    = VALUES(year_level),
                section       = VALUES(section),
                academic_year = VALUES(academic_year),
                campus        = VALUES(campus)
        ');
        $upsert->execute([
            $user_id,
            $student_id    ?: null,
            $department    ?: null,
            $course        ?: null,
            $year_level    ?: null,
            $section       ?: null,
            $academic_year ?: null,
            $campus        ?: null,
        ]);

        // Also update users table
        $pdo->prepare('
            UPDATE users
            SET student_id = ?, course = ?, year_level = ?, section = ?
            WHERE id = ?
        ')->execute([
            $student_id ?: null,
            $course     ?: null,
            $year_level ?: null,
            $section    ?: null,
            $user_id,
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Academic information updated successfully!']);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('[UNIFY] save_academic error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    exit;
}

/* ═══════════════════════════════════════════════════════════════
   ACTION: save_password
═══════════════════════════════════════════════════════════════ */
if ($action === 'save_password') {

    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required.']);
        exit;
    }
    if (strlen($new) < 8) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']);
        exit;
    }
    if ($new !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
        exit;
    }

    // Fetch current hash
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit;
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);

    try {
        $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $upd->execute([$hash, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
    } catch (Exception $e) {
        error_log('[UNIFY] save_password error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    exit;
}

/* ─── Unknown action ──────────────────────────────────────── */
echo json_encode(['success' => false, 'message' => 'Unknown action: ' . htmlspecialchars($action)]);