<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
$_vendorPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!file_exists($_vendorPath)) {
    $_vendorPath = $_SERVER['DOCUMENT_ROOT'] . '/unify/vendor/autoload.php';
}
if (file_exists($_vendorPath)) {
    require_once $_vendorPath;
} else {
    echo json_encode(['success' => false, 'message' => 'Server config error: run composer install in UNIFY(db)/']);
    exit;
}

use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();
$action    = $_POST['action'] ?? '';
$userId    = $_SESSION['user_id'];

if ($action === 'generate') {
    $stmt = $pdo->prepare('SELECT email, two_fa_enabled FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user['two_fa_enabled']) {
        echo json_encode(['success' => false, 'message' => '2FA is already enabled.']);
        exit;
    }

    $secret = $google2fa->generateSecretKey();
    $_SESSION['2fa_setup_secret'] = $secret;

    // Build the QR code URL using Google Charts API — no image library needed
    $label    = urlencode('UNIFY:' . $user['email']);
    $issuer   = urlencode('UNIFY');
    $otpauth  = urlencode("otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}");
    $qrUrl    = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$otpauth}";

    echo json_encode([
        'success' => true,
        'secret'  => $secret,
        'qr_code' => $qrUrl,
    ]);
    exit;
}

if ($action === 'enable') {
    $otp = trim($_POST['otp_code'] ?? '');

    if (empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Please enter the 6-digit code.']);
        exit;
    }
    if (!isset($_SESSION['2fa_setup_secret'])) {
        echo json_encode(['success' => false, 'message' => 'Setup session expired. Please start again.']);
        exit;
    }

    $secret = $_SESSION['2fa_setup_secret'];

    if ($google2fa->verifyKey($secret, $otp)) {
        $stmt = $pdo->prepare('UPDATE users SET two_fa_secret = ?, two_fa_enabled = 1 WHERE id = ?');
        $stmt->execute([$secret, $userId]);
        unset($_SESSION['2fa_setup_secret']);
        echo json_encode(['success' => true, 'message' => '2FA enabled successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid code. Please try again.']);
    }
    exit;
}

if ($action === 'disable') {
    $stmt = $pdo->prepare('UPDATE users SET two_fa_secret = NULL, two_fa_enabled = 0 WHERE id = ?');
    $stmt->execute([$userId]);
    unset($_SESSION['2fa_setup_secret']);
    echo json_encode(['success' => true, 'message' => '2FA has been disabled.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);