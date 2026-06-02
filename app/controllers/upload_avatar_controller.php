<?php
// ============================================================
//  UNIFY — Avatar Upload Controller
//  app/controllers/upload_avatar_controller.php
//  Endpoint: index.php?page=upload_avatar
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode([
        'success' => false,
        'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"
    ]);
    exit;
});

if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success'      => false,
        'message'      => 'Not authenticated.',
        'session_keys' => array_keys($_SESSION),
        'session_id'   => session_id(),
        'cookie_sent'  => isset($_COOKIE[session_name()]) ? 'yes' : 'no',
        'cookie_name'  => session_name(),
    ]);
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$user_id   = (int) $_SESSION['user_id'];
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/pictures/profile_pictures/';

// ── Validation ───────────────────────────────────────────────
if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file received or upload error.']);
    exit;
}

$file     = $_FILES['avatar'];
$maxBytes = 3 * 1024 * 1024; // 3 MB

if ($file['size'] > $maxBytes) {
    echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 3 MB.']);
    exit;
}

// Check real MIME type (not just extension)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!in_array($mimeType, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, and WEBP images are allowed.']);
    exit;
}

$ext = match($mimeType) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
};

// ── Delete old picture if it exists ─────────────────────────
$stmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$user_id]);
$oldPic = $stmt->fetchColumn();

if ($oldPic) {
    $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/pictures/profile_pictures/' . basename($oldPic);
    if (file_exists($oldPath)) {
        @unlink($oldPath);
    }
}

// ── Save new file ────────────────────────────────────────────
$filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
$destPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the file. Check folder permissions.']);
    exit;
}

// ── Update DB ────────────────────────────────────────────────
try {
    $upd = $pdo->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
    $upd->execute([$filename, $user_id]);

    // Keep session in sync so sidebar avatar updates immediately on next page load
    $_SESSION['profile_picture'] = $filename;

    echo json_encode([
        'success'  => true,
        'message'  => 'Profile picture updated!',
        'filename' => $filename,
        'url'      => '/assets/pictures/profile_pictures/' . $filename,
    ]);
} catch (Exception $e) {
    error_log('[UNIFY] upload_avatar error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}