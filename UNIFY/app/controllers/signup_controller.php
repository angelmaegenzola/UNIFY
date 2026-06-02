<?php
// ============================================================
//  UNIFY — Sign Up Controller (UPDATED)
//  - year_level + section are now separate fields
//  - auto-creates student_profiles row on signup
//  - auto-logs student in after signup
//  - redirects straight to Explore Clubs
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    header($_SESSION['role'] === 'admin'
        ? 'Location: index.php?page=dashboard'
        : 'Location: index.php?page=explore');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$errors = [];
$fields = [
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
    'username'   => '',
    'course'     => '',
    'year_level' => '',
    'section'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fields['first_name'] = trim($_POST['first_name'] ?? '');
    $fields['last_name']  = trim($_POST['last_name']  ?? '');
    $fields['email']      = trim($_POST['email']      ?? '');
    $fields['username']   = strtolower(trim($_POST['username'] ?? ''));
    $fields['course']     = trim($_POST['course']     ?? '');
    $fields['year_level'] = trim($_POST['year_level'] ?? '');
    $fields['section']    = strtoupper(trim($_POST['section'] ?? ''));
    $password             = $_POST['password']         ?? '';
    $confirm              = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($fields['first_name']))  $errors[] = 'First name is required.';
    if (empty($fields['last_name']))   $errors[] = 'Last name is required.';
    if (empty($fields['email']))       $errors[] = 'Email address is required.';
    elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL))
                                       $errors[] = 'Please enter a valid email address.';
    if (empty($fields['username']))    $errors[] = 'Username is required.';
    elseif (!preg_match('/^[a-z0-9_]{3,30}$/', $fields['username']))
                                       $errors[] = 'Username: 3–30 chars, letters/numbers/underscores only.';
    if (empty($fields['course']))      $errors[] = 'Course is required.';
    if (empty($fields['year_level']))  $errors[] = 'Year level is required.';
    if (empty($fields['section']))     $errors[] = 'Section is required.';
    if (empty($password))              $errors[] = 'Password is required.';
    elseif (strlen($password) < 8)    $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)        $errors[] = 'Passwords do not match.';
    if (!isset($_POST['terms']))       $errors[] = 'You must agree to the Terms of Service.';

    if (empty($errors)) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE username=? OR email=? LIMIT 1');
        $chk->execute([$fields['username'], $fields['email']]);

        if ($chk->fetch()) {
            $errors[] = 'Username or email is already taken. Please choose another.';
        } else {
            $pdo->beginTransaction();
            try {
                // 1. Insert user
                $pdo->prepare('
                    INSERT INTO users
                        (first_name, last_name, email, username, password_hash, role, course, year_level, section)
                    VALUES (?, ?, ?, ?, ?, "student", ?, ?, ?)
                ')->execute([
                    $fields['first_name'],
                    $fields['last_name'],
                    $fields['email'],
                    $fields['username'],
                    password_hash($password, PASSWORD_BCRYPT),
                    $fields['course'],
                    $fields['year_level'],
                    $fields['section'],
                ]);
                $new_user_id = (int) $pdo->lastInsertId();

                // 2. Auto-create student_profiles row
                $pdo->prepare('
                    INSERT INTO student_profiles
                        (user_id, course, year_level, section, academic_year, campus)
                    VALUES (?, ?, ?, ?, "2025-2026", "CHMSU-Alijis")
                ')->execute([
                    $new_user_id,
                    $fields['course'],
                    $fields['year_level'],
                    $fields['section'],
                ]);

                $pdo->commit();

                // 3. Auto-login — set session so student skips login page
                $_SESSION['user_id']    = $new_user_id;
                $_SESSION['username']   = $fields['username'];
                $_SESSION['first_name'] = $fields['first_name'];
                $_SESSION['last_name']  = $fields['last_name'];
                $_SESSION['role']       = 'student';

                // 4. Straight to Explore Clubs with welcome flag
                header('Location: index.php?page=explore&welcome=1');
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
