<?php
// ============================================================
//  UNIFY — student_messages_controller.php  (REWRITTEN)
//  Handles Club Chat + Direct Messages for students.
//  Matches officer_messages_controller.php format exactly.
//  DB columns: sender_id, message, sent_at, is_deleted
// ============================================================

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!empty($_GET['action'])) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => $err['message'], 'messages' => []]);
        }
    }
});

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/config/db.php';

if (empty($_SESSION['user_id'])) {
    if (!empty($_GET['action'])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not authenticated', 'messages' => []]);
        exit;
    }
    header('Location: index.php?page=login');
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$userFirst = $_SESSION['first_name'] ?? 'Student';
$userLast  = $_SESSION['last_name']  ?? '';
$userName  = trim($userFirst . ' ' . $userLast);
$userInit  = strtoupper(substr($userFirst, 0, 1));

// ── Profile picture ────────────────────────────────────────
$picStmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$picStmt->execute([$userId]);
$picFile    = $picStmt->fetchColumn();
$avatar_url = $picFile
    ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';

// ── All active clubs this student belongs to ───────────────
$allStmt = $pdo->prepare("
    SELECT m.role, c.id AS club_id, c.name AS club_name,
           c.acronym, c.logo_path
    FROM members m
    JOIN clubs c ON c.id = m.club_id
    WHERE m.user_id = ? AND m.status = 'active'
    ORDER BY FIELD(m.role,'president','vice president','officer','lead','member'),
             m.joined_at ASC
");
$allStmt->execute([$userId]);
$allClubs = $allStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($allClubs)) {
    if (!empty($_GET['action'])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No club membership found.', 'messages' => []]);
        exit;
    }
    header('Location: index.php?page=explore');
    exit;
}

// ── Pick active club ───────────────────────────────────────
$requestedClubId = (int) ($_GET['club_id'] ?? $_POST['club_id'] ?? 0);
$studentClub = null;
if ($requestedClubId > 0) {
    foreach ($allClubs as $c) {
        if ((int) $c['club_id'] === $requestedClubId) { $studentClub = $c; break; }
    }
}
if (!$studentClub) $studentClub = $allClubs[0];

$clubId      = (int) $studentClub['club_id'];
$clubName    = $studentClub['club_name'];
$clubInitial = strtoupper(substr($clubName, 0, 1));
$studentRole = $studentClub['role'];
$isMod       = in_array($studentRole, ['officer', 'lead', 'president', 'vice president']);

// ── DM: resolve target user (for direct messages) ─────────
$dmUserId = (int) ($_GET['dm_user'] ?? $_POST['dm_user'] ?? 0);
$isDM     = $dmUserId > 0;
$dmUser   = null;
if ($isDM) {
    $dmStmt = $pdo->prepare("
        SELECT u.id, u.first_name, u.last_name, u.profile_picture, m.role
        FROM users u
        JOIN members m ON m.user_id = u.id AND m.club_id = ? AND m.status = 'active'
        WHERE u.id = ?
        LIMIT 1
    ");
    $dmStmt->execute([$clubId, $dmUserId]);
    $dmUser = $dmStmt->fetch(PDO::FETCH_ASSOC);
    if (!$dmUser) $isDM = false;
}

// ── Members list with profile pictures ────────────────────
$mbrStmt = $pdo->prepare("
    SELECT u.id AS user_id, u.first_name, u.last_name, u.profile_picture, m.role
    FROM members m
    JOIN users u ON u.id = m.user_id
    WHERE m.club_id = ? AND m.status = 'active'
    ORDER BY FIELD(m.role,'president','vice president','officer','lead','member'),
             m.joined_at ASC
    LIMIT 50
");
$mbrStmt->execute([$clubId]);
$dbMembers = $mbrStmt->fetchAll(PDO::FETCH_ASSOC);

// ── Totals ─────────────────────────────────────────────────
$totalMembers = count($dbMembers);
$unreadNotifs = 0;

// ============================================================
//  AJAX HANDLERS
// ============================================================
$action = $_GET['action'] ?? '';

// ── helper: build message row ──────────────────────────────
function buildMessageRow($r, $myUserId) {
    $pic = !empty($r['profile_picture'])
        ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($r['profile_picture']))
        : '';
    return [
        'id'        => (int) $r['id'],
        'sender_id' => (int) $r['sender_id'],
        'mine'      => (int) $r['sender_id'] === $myUserId,
        'name'      => trim($r['first_name'] . ' ' . $r['last_name']),
        'initial'   => strtoupper(substr($r['first_name'], 0, 1)),
        'role'      => $r['role'],
        'message'   => $r['message'],
        'sent_at'   => $r['sent_at'],
        'avatar'    => $pic,
    ];
}

// ── history ────────────────────────────────────────────────
if ($action === 'history') {
    ob_end_clean();
    header('Content-Type: application/json');
    try {
        if ($isDM) {
            // Direct message history between two users
            $stmt = $pdo->prepare("
                SELECT dm.id, dm.sender_id, dm.message, dm.sent_at,
                       u.first_name, u.last_name, u.profile_picture, mem.role
                FROM direct_messages dm
                JOIN users u     ON u.id = dm.sender_id
                JOIN members mem ON mem.user_id = dm.sender_id
                                 AND mem.club_id = :cid
                                 AND mem.status  = 'active'
                WHERE dm.club_id = :cid2
                  AND (dm.is_deleted = 0 OR dm.is_deleted IS NULL)
                  AND (
                    (dm.sender_id = :me  AND dm.receiver_id = :them)
                    OR
                    (dm.sender_id = :them2 AND dm.receiver_id = :me2)
                  )
                ORDER BY dm.sent_at ASC
                LIMIT 200
            ");
            $stmt->execute([
                ':cid'   => $clubId,
                ':cid2'  => $clubId,
                ':me'    => $userId,
                ':them'  => $dmUserId,
                ':them2' => $dmUserId,
                ':me2'   => $userId,
            ]);
        } else {
            // Group chat history
            $stmt = $pdo->prepare("
                SELECT m.id, m.sender_id, m.message, m.sent_at,
                       u.first_name, u.last_name, u.profile_picture, mem.role
                FROM club_messages m
                JOIN users u     ON u.id = m.sender_id
                JOIN members mem ON mem.user_id = m.sender_id
                                 AND mem.club_id = m.club_id
                                 AND mem.status  = 'active'
                WHERE m.club_id = ?
                  AND (m.is_deleted = 0 OR m.is_deleted IS NULL)
                ORDER BY m.sent_at ASC
                LIMIT 200
            ");
            $stmt->execute([$clubId]);
        }

        $rows     = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $messages = array_map(fn($r) => buildMessageRow($r, $userId), $rows);
        echo json_encode(['success' => true, 'messages' => $messages]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage(), 'messages' => []]);
    }
    exit;
}

// ── poll ───────────────────────────────────────────────────
if ($action === 'poll') {
    ob_end_clean();
    header('Content-Type: application/json');
    $since = (int) ($_GET['since'] ?? 0);
    try {
        if ($isDM) {
            $stmt = $pdo->prepare("
                SELECT dm.id, dm.sender_id, dm.message, dm.sent_at,
                       u.first_name, u.last_name, u.profile_picture, mem.role
                FROM direct_messages dm
                JOIN users u     ON u.id = dm.sender_id
                JOIN members mem ON mem.user_id = dm.sender_id
                                 AND mem.club_id = :cid
                                 AND mem.status  = 'active'
                WHERE dm.club_id = :cid2
                  AND dm.id > :since
                  AND (dm.is_deleted = 0 OR dm.is_deleted IS NULL)
                  AND (
                    (dm.sender_id = :me  AND dm.receiver_id = :them)
                    OR
                    (dm.sender_id = :them2 AND dm.receiver_id = :me2)
                  )
                ORDER BY dm.sent_at ASC
            ");
            $stmt->execute([
                ':cid'   => $clubId, ':cid2'  => $clubId,
                ':since' => $since,
                ':me'    => $userId, ':them'  => $dmUserId,
                ':them2' => $dmUserId, ':me2' => $userId,
            ]);
        } else {
            $stmt = $pdo->prepare("
                SELECT m.id, m.sender_id, m.message, m.sent_at,
                       u.first_name, u.last_name, u.profile_picture, mem.role
                FROM club_messages m
                JOIN users u     ON u.id = m.sender_id
                JOIN members mem ON mem.user_id = m.sender_id
                                 AND mem.club_id = m.club_id
                                 AND mem.status  = 'active'
                WHERE m.club_id = ? AND m.id > ?
                  AND (m.is_deleted = 0 OR m.is_deleted IS NULL)
                ORDER BY m.sent_at ASC
            ");
            $stmt->execute([$clubId, $since]);
        }

        $rows     = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $messages = array_map(fn($r) => buildMessageRow($r, $userId), $rows);
        echo json_encode(['success' => true, 'messages' => $messages]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage(), 'messages' => []]);
    }
    exit;
}

// ── send ───────────────────────────────────────────────────
if ($action === 'send') {
    ob_end_clean();
    header('Content-Type: application/json');
    try {
        $body    = json_decode(file_get_contents('php://input'), true);
        $message = trim($body['message'] ?? '');
        $toDM    = (int) ($body['dm_user'] ?? 0);

        if (!$message || mb_strlen($message) > 1000) {
            echo json_encode(['error' => 'Invalid message']); exit;
        }

        if ($toDM > 0) {
            // Send direct message
            $stmt = $pdo->prepare("
                INSERT INTO direct_messages (club_id, sender_id, receiver_id, message, sent_at, is_deleted)
                VALUES (?, ?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$clubId, $userId, $toDM, $message]);
        } else {
            // Rate limit for group chat
            $rl = $pdo->prepare("
                SELECT COUNT(*) FROM club_messages
                WHERE sender_id = ? AND club_id = ?
                  AND sent_at >= DATE_SUB(NOW(), INTERVAL 5 SECOND)
            ");
            $rl->execute([$userId, $clubId]);
            if ((int) $rl->fetchColumn() >= 5) {
                echo json_encode(['error' => 'Slow down!']); exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO club_messages (club_id, sender_id, message, sent_at, is_deleted)
                VALUES (?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$clubId, $userId, $message]);
        }

        echo json_encode([
            'success' => true,
            'message' => [
                'id'        => (int) $pdo->lastInsertId(),
                'sender_id' => $userId,
                'mine'      => true,
                'name'      => $userName,
                'initial'   => $userInit,
                'role'      => $studentRole,
                'message'   => $message,
                'sent_at'   => date('Y-m-d H:i:s'),
                'avatar'    => $avatar_url,
            ],
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ── delete ─────────────────────────────────────────────────
if ($action === 'delete') {
    ob_end_clean();
    header('Content-Type: application/json');
    try {
        $body  = json_decode(file_get_contents('php://input'), true);
        $id    = (int) ($body['id'] ?? 0);
        $isDMd = (bool) ($body['is_dm'] ?? false);

        $table = $isDMd ? 'direct_messages' : 'club_messages';

        if ($isMod) {
            $stmt = $pdo->prepare("UPDATE $table SET is_deleted = 1 WHERE id = ? AND club_id = ?");
            $stmt->execute([$id, $clubId]);
        } else {
            $stmt = $pdo->prepare("UPDATE $table SET is_deleted = 1 WHERE id = ? AND club_id = ? AND sender_id = ?");
            $stmt->execute([$id, $clubId, $userId]);
        }

        echo json_encode(['success' => $stmt->rowCount() > 0]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ── dm_unread: count unread DMs per member ─────────────────
if ($action === 'dm_unread') {
    ob_end_clean();
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare("
            SELECT sender_id, COUNT(*) as cnt
            FROM direct_messages
            WHERE receiver_id = ? AND club_id = ?
              AND is_read = 0 AND (is_deleted = 0 OR is_deleted IS NULL)
            GROUP BY sender_id
        ");
        $stmt->execute([$userId, $clubId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) $map[(int)$r['sender_id']] = (int)$r['cnt'];
        echo json_encode(['success' => true, 'unread' => $map]);
    } catch (Exception $e) {
        echo json_encode(['success' => false]);
    }
    exit;
}

// ── dm_mark_read ───────────────────────────────────────────
if ($action === 'dm_mark_read') {
    ob_end_clean();
    header('Content-Type: application/json');
    $from = (int) ($_GET['from'] ?? 0);
    if ($from > 0) {
        $pdo->prepare("
            UPDATE direct_messages SET is_read = 1
            WHERE receiver_id = ? AND sender_id = ? AND club_id = ?
        ")->execute([$userId, $from, $clubId]);
    }
    echo json_encode(['success' => true]);
    exit;
}

// ── End of AJAX — page HTML render below ───────────────────