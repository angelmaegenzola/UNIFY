<?php
// ============================================================
//  UNIFY — club_messages.php
//  Place at: /../app/controllers/club_messages.php
//  Handles both GET (poll) and POST (send / delete) via AJAX
// ============================================================

ob_start();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']); exit;
}

ob_clean();
header('Content-Type: application/json');

$userId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// ── Resolve the club this user belongs to (any active membership) ──
$memberStmt = $pdo->prepare("
    SELECT club_id FROM members
    WHERE user_id = :uid AND status = 'active'
    LIMIT 1
");
$memberStmt->execute([':uid' => $userId]);
$memberRow = $memberStmt->fetch(PDO::FETCH_ASSOC);
if (!$memberRow) {
    http_response_code(403);
    echo json_encode(['error' => 'Not a club member']); exit;
}
$clubId = (int)$memberRow['club_id'];

// ── GET: poll for new messages ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'poll') {
        $since = $_GET['since'] ?? '0';
        $stmt = $pdo->prepare("
            SELECT cm.id, cm.sender_id, cm.message, cm.sent_at,
                   u.first_name, u.last_name,
                   m.role AS sender_role
            FROM club_messages cm
            JOIN users   u ON u.id = cm.sender_id
            JOIN members m ON m.user_id = cm.sender_id AND m.club_id = :cid AND m.status = 'active'
            WHERE cm.club_id = :cid2
              AND cm.is_deleted = 0
              AND cm.id > :since
            ORDER BY cm.sent_at ASC
            LIMIT 60
        ");
        $stmt->execute([':cid' => $clubId, ':cid2' => $clubId, ':since' => (int)$since]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'messages' => array_map(fn($r) => [
                'id'          => (int)$r['id'],
                'sender_id'   => (int)$r['sender_id'],
                'name'        => trim($r['first_name'] . ' ' . $r['last_name']),
                'initial'     => strtoupper(substr($r['first_name'], 0, 1)),
                'role'        => $r['sender_role'],
                'message'     => $r['message'],
                'sent_at'     => $r['sent_at'],
                'mine'        => (int)$r['sender_id'] === $userId,
            ], $rows),
            'club_id' => $clubId,
        ]);
    } elseif ($action === 'history') {
        // Load last 50 messages on open
        $stmt = $pdo->prepare("
            SELECT cm.id, cm.sender_id, cm.message, cm.sent_at,
                   u.first_name, u.last_name,
                   m.role AS sender_role
            FROM club_messages cm
            JOIN users   u ON u.id = cm.sender_id
            JOIN members m ON m.user_id = cm.sender_id AND m.club_id = :cid AND m.status = 'active'
            WHERE cm.club_id = :cid2 AND cm.is_deleted = 0
            ORDER BY cm.sent_at DESC
            LIMIT 50
        ");
        $stmt->execute([':cid' => $clubId, ':cid2' => $clubId]);
        $rows = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo json_encode([
            'messages' => array_map(fn($r) => [
                'id'        => (int)$r['id'],
                'sender_id' => (int)$r['sender_id'],
                'name'      => trim($r['first_name'] . ' ' . $r['last_name']),
                'initial'   => strtoupper(substr($r['first_name'], 0, 1)),
                'role'      => $r['sender_role'],
                'message'   => $r['message'],
                'sent_at'   => $r['sent_at'],
                'mine'      => (int)$r['sender_id'] === $userId,
            ], $rows),
        ]);
    } else {
        echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}

// ── POST: send or delete ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    if ($action === 'send') {
        $text = trim($body['message'] ?? '');
        if ($text === '') { echo json_encode(['error' => 'Empty message']); exit; }
        if (mb_strlen($text) > 1000) { echo json_encode(['error' => 'Message too long']); exit; }

        $stmt = $pdo->prepare("
            INSERT INTO club_messages (club_id, sender_id, message, sent_at)
            VALUES (:club_id, :sender_id, :message, NOW())
        ");
        $stmt->execute([':club_id' => $clubId, ':sender_id' => $userId, ':message' => $text]);
        $newId = (int)$pdo->lastInsertId();

        // Fetch the inserted row to return full object
        $row = $pdo->prepare("
            SELECT cm.id, cm.sender_id, cm.message, cm.sent_at,
                   u.first_name, u.last_name, m.role AS sender_role
            FROM club_messages cm
            JOIN users   u ON u.id = cm.sender_id
            JOIN members m ON m.user_id = cm.sender_id AND m.club_id = :cid AND m.status = 'active'
            WHERE cm.id = :id
        ");
        $row->execute([':cid' => $clubId, ':id' => $newId]);
        $r = $row->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'message' => [
            'id'        => (int)$r['id'],
            'sender_id' => (int)$r['sender_id'],
            'name'      => trim($r['first_name'] . ' ' . $r['last_name']),
            'initial'   => strtoupper(substr($r['first_name'], 0, 1)),
            'role'      => $r['sender_role'],
            'message'   => $r['message'],
            'sent_at'   => $r['sent_at'],
            'mine'      => true,
        ]]);
        exit;
    }

    if ($action === 'delete') {
        $msgId = (int)($body['id'] ?? 0);
        // Only allow deleting own messages (or officers can delete any)
        $isMod = in_array($_SESSION['role'] ?? '', ['officer','lead','president','vice president']);
        if ($isMod) {
            $pdo->prepare("UPDATE club_messages SET is_deleted=1 WHERE id=? AND club_id=?")
                ->execute([$msgId, $clubId]);
        } else {
            $pdo->prepare("UPDATE club_messages SET is_deleted=1 WHERE id=? AND sender_id=? AND club_id=?")
                ->execute([$msgId, $userId, $clubId]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['error' => 'Unknown action']);
    exit;
}

echo json_encode(['error' => 'Bad request']);