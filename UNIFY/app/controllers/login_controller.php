<?php
// ============================================================
//  UNIFY — Login Page
//  app/views/login.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in? Redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: index.php?page=dashboard');
    } elseif (in_array($_SESSION['role'], ['officer', 'lead', 'president', 'vice president'])) {
        header('Location: index.php?page=officer_dashboard');
    } else {
        header('Location: index.php?page=studenthome');
    }
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$error    = '';
$username = '';

// Show success message if coming from signup
$registered = isset($_GET['registered']) && $_GET['registered'] == '1';

// ── Handle POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Fetch user by username — also check members table for officer role
        $stmt = $pdo->prepare(
            'SELECT u.id, u.first_name, u.last_name, u.username, u.password_hash, u.role,
                    u.two_fa_enabled, u.two_fa_secret, u.profile_picture,
                    COALESCE(
                        (SELECT m.role FROM members m
                         WHERE m.user_id = u.id AND m.status = "active"
                         AND m.role IN ("officer","lead","president","vice president")
                         ORDER BY FIELD(m.role,"president","vice president","lead","officer")
                         LIMIT 1),
                        u.role
                    ) AS effective_role
             FROM users u WHERE u.username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            // ── 2FA enabled? Hold login, redirect to verify page ──
            if (!empty($user['two_fa_enabled']) && !empty($user['two_fa_secret'])) {
                $_SESSION['2fa_pending_user_id']    = $user['id'];
                $_SESSION['2fa_pending_username']   = $user['username'];
                $_SESSION['2fa_pending_first_name'] = $user['first_name'];
                $_SESSION['2fa_pending_last_name']  = $user['last_name'];
                $_SESSION['2fa_pending_role']       = $user['effective_role'];
                header('Location: index.php?page=verify_2fa');
                exit;
            }

            // ── No 2FA — set session and redirect normally ────────
            $_SESSION['user_id']         = $user['id'];
            $_SESSION['username']        = $user['username'];
            $_SESSION['first_name']      = $user['first_name'];
            $_SESSION['last_name']       = $user['last_name'];
            $_SESSION['role']            = $user['effective_role'];
            $_SESSION['profile_picture'] = $user['profile_picture'] ?? '';

            if ($user['effective_role'] === 'admin') {
                header('Location: index.php?page=dashboard');
            } elseif (in_array($user['effective_role'], ['officer', 'lead', 'president', 'vice president'])) {
                header('Location: index.php?page=officer_dashboard');
            } else {
                header('Location: index.php?page=studenthome');
            }
            exit;
        } else {
            $error = 'Incorrect username or password. Please try again.';
        }
    }
}
?>
