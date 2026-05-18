<?php
// ============================================================
//  UNIFY — 2FA Verification Controller
//  app/controllers/verify_2fa_controller.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// No pending 2FA session → kick back to login
if (!isset($_SESSION['2fa_pending_user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Already fully logged in → redirect home
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=studenthome');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';
$_vendorPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!file_exists($_vendorPath)) {
    // Fallback: try DOCUMENT_ROOT path
    $_vendorPath = $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/vendor/autoload.php';
}
if (!file_exists($_vendorPath)) {
    die('<div style="font-family:sans-serif;padding:40px;color:#c0291b;background:#fdecea;border-radius:8px;margin:40px auto;max-width:600px;">
        <h2>⚠️ Composer dependencies missing</h2>
        <p>The vendor folder was not found. Please run <code>composer install</code> in the project root (<code>UNIFY(db)/</code>) to install dependencies.</p>
        <p><strong>Steps:</strong></p>
        <ol><li>Open terminal / command prompt</li><li>Navigate to <code>C:\xampp\htdocs\UNIFY(db)\</code></li><li>Run: <code>composer install</code></li><li>Refresh this page</li></ol>
        </div>');
}
require_once $_vendorPath;

use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();
$error     = '';

// ── Handle POST (OTP submission) ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp = trim($_POST['otp_code'] ?? '');

    if (empty($otp)) {
        $error = 'Please enter the 6-digit code from your authenticator app.';
    } else {
        $stmt = $pdo->prepare('SELECT two_fa_secret FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['2fa_pending_user_id']]);
        $row = $stmt->fetch();

        if ($row && $google2fa->verifyKey($row['two_fa_secret'], $otp)) {
            // ✅ OTP valid — complete the login
            $_SESSION['user_id']    = $_SESSION['2fa_pending_user_id'];
            $_SESSION['username']   = $_SESSION['2fa_pending_username'];
            $_SESSION['first_name'] = $_SESSION['2fa_pending_first_name'];
            $_SESSION['last_name']  = $_SESSION['2fa_pending_last_name'];
            $_SESSION['role']       = $_SESSION['2fa_pending_role'];

            unset(
                $_SESSION['2fa_pending_user_id'],
                $_SESSION['2fa_pending_username'],
                $_SESSION['2fa_pending_first_name'],
                $_SESSION['2fa_pending_last_name'],
                $_SESSION['2fa_pending_role']
            );

            $role = $_SESSION['role'];
            if ($role === 'admin') {
                header('Location: index.php?page=dashboard');
            } elseif (in_array($role, ['officer', 'lead', 'president', 'vice president'])) {
                header('Location: index.php?page=officer_dashboard');
            } else {
                header('Location: index.php?page=studenthome');
            }
            exit;

        } else {
            $error = 'Invalid code. Please try again with a fresh code from your app.';
        }
    }
}
?>
