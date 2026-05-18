<?php
// ============================================================
//  UNIFY — Notifications Handler
//  GET  action=list   → returns notifications for current user
//  POST action=read   → marks notification(s) as read
//  POST action=read_all → marks all as read
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']); exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/config/db.php';

$user_id = (int) $_SESSION['user_id'];
$action  = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    $stmt = $pdo->prepare("
        SELECT id, type, title, message, link, is_read,
               DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') AS created_fmt
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id]);
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $unread = (int)$pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0")
                        ->execute([$user_id]) ? 0 : 0;
    $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $unreadStmt->execute([$user_id]);
    $unread = (int)$unreadStmt->fetchColumn();

    echo json_encode(['success' => true, 'notifications' => $notifs, 'unread' => $unread]);
    exit;
}

if ($action === 'read') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$id, $user_id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'read_all') {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$user_id]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
