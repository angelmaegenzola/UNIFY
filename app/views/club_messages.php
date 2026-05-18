<?php
// ============================================================
//  UNIFY — club_messages.php
//  Path: /UNIFY(db)/app/api/club_messages.php
// ============================================================

ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

$conn = new mysqli('127.0.0.1', 'root', '', 'unify_db');
if ($conn->connect_error) die(json_encode(['success' => false, 'error' => 'DB error']));
$conn->set_charset('utf8mb4');

ob_clean();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']); exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ── Resolve club_id (support multi-club switcher) ──────────
$requestedClubId = (int)($_GET['club_id'] ?? $_POST['club_id'] ?? 0);

if ($requestedClubId) {
    $mStmt = $conn->prepare("SELECT club_id FROM members WHERE user_id = ? AND club_id = ? AND status = 'active' LIMIT 1");
    $mStmt->bind_param('ii', $userId, $requestedClubId);
} else {
    $mStmt = $conn->prepare("SELECT club_id FROM members WHERE user_id = ? AND status = 'active' LIMIT 1");
    $mStmt->bind_param('i', $userId);
}
$mStmt->execute();
$mRow = $mStmt->get_result()->fetch_assoc();
$mStmt->close();

if (!$mRow) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not a club member']); exit;
}
$clubId = (int)$mRow['club_id'];

// ── GET: fetch messages ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if ($action === 'fetch' || $action === 'poll') {
        $after = (int)($_GET['after'] ?? $_GET['since'] ?? 0);

        $stmt = $conn->prepare("
            SELECT cm.id, cm.sender_id, cm.message, cm.sent_at,
                   u.first_name, u.last_name, m.role AS sender_role
            FROM club_messages cm
            JOIN users u ON u.id = cm.sender_id
            JOIN members m ON m.user_id = cm.sender_id AND m.club_id = ? AND m.status = 'active'
            WHERE cm.club_id = ? AND cm.is_deleted = 0 AND cm.id > ?
            ORDER BY cm.sent_at ASC
            LIMIT 100
        ");
        $stmt->bind_param('iii', $clubId, $clubId, $after);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $messages = array_map(fn($r) => [
            'id'         => (int)$r['id'],
            'user_id'    => (int)$r['sender_id'],
            'first_name' => $r['first_name'],
            'last_name'  => $r['last_name'],
            'role'       => $r['sender_role'],
            'body'       => $r['message'],
            'created_at' => $r['sent_at'],
        ], $rows);

        echo json_encode(['success' => true, 'messages' => $messages]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']); exit;
}

// ── POST: send or delete ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'send') {
        $text = trim($_POST['body'] ?? '');
        if ($text === '') { echo json_encode(['success' => false, 'error' => 'Empty message']); exit; }
        if (mb_strlen($text) > 1000) { echo json_encode(['success' => false, 'error' => 'Too long']); exit; }

        $stmt = $conn->prepare("INSERT INTO club_messages (club_id, sender_id, message, sent_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iis', $clubId, $userId, $text);
        $stmt->execute();
        $newId = (int)$conn->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("
            SELECT cm.id, cm.sender_id, cm.message, cm.sent_at,
                   u.first_name, u.last_name, m.role AS sender_role
            FROM club_messages cm
            JOIN users u ON u.id = cm.sender_id
            JOIN members m ON m.user_id = cm.sender_id AND m.club_id = ? AND m.status = 'active'
            WHERE cm.id = ?
        ");
        $stmt->bind_param('ii', $clubId, $newId);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        echo json_encode(['success' => true, 'messages' => [[
            'id'         => (int)$r['id'],
            'user_id'    => (int)$r['sender_id'],
            'first_name' => $r['first_name'],
            'last_name'  => $r['last_name'],
            'role'       => $r['sender_role'],
            'body'       => $r['message'],
            'created_at' => $r['sent_at'],
        ]]]);
        exit;
    }

    if ($action === 'delete') {
        $msgId = (int)($_POST['message_id'] ?? 0);
        $role  = $_SESSION['role'] ?? '';
        $isMod = in_array($role, ['officer', 'lead', 'president', 'vice president']);

        if ($isMod) {
            $stmt = $conn->prepare("UPDATE club_messages SET is_deleted = 1 WHERE id = ? AND club_id = ?");
            $stmt->bind_param('ii', $msgId, $clubId);
        } else {
            $stmt = $conn->prepare("UPDATE club_messages SET is_deleted = 1 WHERE id = ? AND sender_id = ? AND club_id = ?");
            $stmt->bind_param('iii', $msgId, $userId, $clubId);
        }
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true]); exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']); exit;
}

echo json_encode(['success' => false, 'error' => 'Bad request']);
$conn->close();