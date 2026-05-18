<?php
ob_start();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['officer','lead','student','president','vice president'])) {
    header('Location: index.php?page=login'); exit;
}

// ── AJAX handler ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean();
    header('Content-Type: application/json');
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $userId = (int)$_SESSION['user_id'];

    try {
        $memberStmt = $pdo->prepare("
            SELECT m.club_id, m.role FROM members m
            WHERE m.user_id=:uid AND m.role IN ('officer','lead','president','vice president') AND m.status='active'
            LIMIT 1
        ");
        $memberStmt->execute([':uid' => $userId]);
        $memberRow = $memberStmt->fetch(PDO::FETCH_ASSOC);
        if (!$memberRow) {
            http_response_code(403);
            echo json_encode(['error' => 'Not an officer']); exit;
        }
        $clubId      = (int)$memberRow['club_id'];
        $officerRole = $memberRow['role'];
        $canManage   = in_array($officerRole, ['president', 'vice president']);

        switch ($action) {

            case 'app_approve':
                $pdo->beginTransaction();
                $appId   = (int)($body['id'] ?? 0);
                $appStmt = $pdo->prepare("
                    SELECT * FROM applications
                    WHERE id=:id AND club_id=:cid AND status='pending' AND reviewer_type='officer' LIMIT 1
                ");
                $appStmt->execute([':id' => $appId, ':cid' => $clubId]);
                $app = $appStmt->fetch(PDO::FETCH_ASSOC);
                if (!$app) { $pdo->rollBack(); echo json_encode(['error' => 'Application not found.']); break; }
                $pdo->prepare("UPDATE applications SET status='approved', reviewed_at=NOW() WHERE id=?")->execute([$appId]);
                $pdo->prepare("
                    INSERT IGNORE INTO members (user_id,club_id,course,year,section,role,status,joined_at)
                    VALUES (?,?,?,?,?,'member','active',NOW())
                ")->execute([$app['user_id'], $app['club_id'], $app['course'] ?? null, $app['year'] ?? null, $app['section'] ?? null]);
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                $cnameStmt->execute([$clubId]);
                $cname = $cnameStmt->fetchColumn();
                $pdo->prepare("
                    INSERT INTO notifications (user_id,type,title,message,link)
                    VALUES (?,'app_approved','Application Approved! 🎉',?,'index.php?page=studenthome')
                ")->execute([$app['user_id'], 'Congratulations! Your application to join ' . $cname . ' has been approved. Welcome to the club!']);
                $pdo->commit();
                echo json_encode(['success' => true]);
                break;

            case 'app_reject':
                $appId  = (int)($body['id'] ?? 0);
                $reason = trim($body['reason'] ?? '');
                $appStmt = $pdo->prepare("SELECT * FROM applications WHERE id=:id AND club_id=:cid AND status='pending' LIMIT 1");
                $appStmt->execute([':id' => $appId, ':cid' => $clubId]);
                $app = $appStmt->fetch(PDO::FETCH_ASSOC);
                if (!$app) { echo json_encode(['error' => 'Application not found.']); break; }
                $pdo->prepare("UPDATE applications SET status='rejected', reviewed_at=NOW() WHERE id=?")->execute([$appId]);
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                $cnameStmt->execute([$clubId]);
                $cname = $cnameStmt->fetchColumn();
                $pdo->prepare("
                    INSERT INTO notifications (user_id,type,title,message,link)
                    VALUES (?,'app_rejected','Application Update',?,'index.php?page=explore')
                ")->execute([$app['user_id'], 'Your application to join ' . $cname . ' was not approved.' . ($reason ? ' Reason: ' . $reason : '')]);
                echo json_encode(['success' => true]);
                break;

            case 'member_remove':
                if (!$canManage) { echo json_encode(['error' => 'Insufficient permissions.']); break; }
                $memberId = (int)($body['member_id'] ?? 0);
                $check = $pdo->prepare("SELECT user_id FROM members WHERE id=? AND club_id=?");
                $check->execute([$memberId, $clubId]);
                $row = $check->fetch(PDO::FETCH_ASSOC);
                if (!$row) { echo json_encode(['error' => 'Member not found.']); break; }
                if ($row['user_id'] == $userId) { echo json_encode(['error' => 'You cannot remove yourself.']); break; }
                $pdo->prepare("UPDATE members SET status='inactive' WHERE id=? AND club_id=?")->execute([$memberId, $clubId]);
                echo json_encode(['success' => true]);
                break;

            case 'member_role':
                if (!$canManage) { echo json_encode(['error' => 'Insufficient permissions.']); break; }
                $memberId = (int)($body['member_id'] ?? 0);
                $newRole  = trim($body['role'] ?? '');
                $allowed  = ['member','lead','officer','vice president','president'];
                if (!in_array($newRole, $allowed)) { echo json_encode(['error' => 'Invalid role.']); break; }
                $pdo->prepare("UPDATE members SET role=? WHERE id=? AND club_id=?")->execute([$newRole, $memberId, $clubId]);
                echo json_encode(['success' => true]);
                break;

            case 'member_set_position':
                if (!$canManage) { echo json_encode(['error' => 'Insufficient permissions.']); break; }
                $memberId    = (int)($body['member_id'] ?? 0);
                $position    = substr(trim($body['position'] ?? ''), 0, 100);
                $memberCheck = $pdo->prepare("SELECT user_id FROM members WHERE id=? AND club_id=?");
                $memberCheck->execute([$memberId, $clubId]);
                $mrow = $memberCheck->fetch(PDO::FETCH_ASSOC);
                if (!$mrow) { echo json_encode(['error' => 'Member not found.']); break; }
                $pdo->prepare("UPDATE members SET club_position=? WHERE id=? AND club_id=?")->execute([$position ?: null, $memberId, $clubId]);
                if ($position) {
                    $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                    $cnameStmt->execute([$clubId]);
                    $cname = $cnameStmt->fetchColumn();
                    $pdo->prepare("
                        INSERT INTO notifications (user_id,type,title,message,link)
                        VALUES (?,'club_position','Club Position Assigned 🏅',?,'index.php?page=studenthome')
                    ")->execute([$mrow['user_id'], 'You have been assigned the position of ' . $position . ' in ' . $cname . '.']);
                }
                echo json_encode(['success' => true]);
                break;

            case 'student_search':
                $q = '%' . trim($body['q'] ?? '') . '%';
                $stmt = $pdo->prepare("
                    SELECT u.id, u.first_name, u.last_name, u.email
                    FROM users u
                    WHERE (u.email LIKE :q)
                      AND u.id NOT IN (SELECT user_id FROM members WHERE club_id=:cid AND status='active')
                    LIMIT 6
                ");
                $stmt->execute([':q' => $q, ':cid' => $clubId]);
                echo json_encode(['results' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                break;

            case 'member_add_direct':
                if (!$canManage) { echo json_encode(['error' => 'Insufficient permissions.']); break; }
                $targetUserId = (int)($body['user_id'] ?? 0);
                $role         = trim($body['role'] ?? 'member');
                $allowed      = ['member','lead','officer','vice president'];
                if (!in_array($role, $allowed)) $role = 'member';
                $exists = $pdo->prepare("SELECT id FROM members WHERE user_id=? AND club_id=? AND status='active'");
                $exists->execute([$targetUserId, $clubId]);
                if ($exists->fetch()) { echo json_encode(['error' => 'Already a member.']); break; }
                $pdo->prepare("
                    INSERT INTO members (user_id,club_id,role,status,joined_at) VALUES (?,?,?,'active',NOW())
                    ON DUPLICATE KEY UPDATE status='active', role=?, joined_at=NOW()
                ")->execute([$targetUserId, $clubId, $role, $role]);
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                $cnameStmt->execute([$clubId]);
                $cname = $cnameStmt->fetchColumn();
                $pdo->prepare("
                    INSERT INTO notifications (user_id,type,title,message,link)
                    VALUES (?,'app_approved','Added to Club 🎉',?,'index.php?page=studenthome')
                ")->execute([$targetUserId, 'You have been directly added to ' . $cname . ' as a ' . $role . '.']);
                echo json_encode(['success' => true]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Unknown action']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ── GET: Notifications ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    ob_clean();
    header('Content-Type: application/json');
    $userId = (int)$_SESSION['user_id'];
    $action = $_GET['action'];
    if ($action === 'notif_list') {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$userId]);
        $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($notifs as &$n) $n['created_fmt'] = date('M j, g:i a', strtotime($n['created_at']));
        $unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
        $unread->execute([$userId]);
        echo json_encode(['notifications' => $notifs, 'unread' => (int)$unread->fetchColumn()]);
        exit;
    }
    if ($action === 'notif_read') {
        $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$_GET['id'] ?? 0, $userId]);
        echo json_encode(['success' => true]); exit;
    }
    if ($action === 'notif_read_all') {
        $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$userId]);
        echo json_encode(['success' => true]); exit;
    }
    exit;
}

// ── Page Data ──────────────────────────────────────────────────
$userId    = (int)$_SESSION['user_id'];
$userFirst = $_SESSION['first_name'] ?? 'Officer';
$userLast  = $_SESSION['last_name']  ?? '';
$userName  = trim($userFirst . ' ' . $userLast);
$userInit  = strtoupper(substr($userFirst, 0, 1));

$picStmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$picStmt->execute([$userId]);
$picFile    = $picStmt->fetchColumn();
$avatar_url = $picFile
    ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';

$memberStmt = $pdo->prepare("
    SELECT m.club_id, m.role, c.name AS club_name, c.logo_path
    FROM members m JOIN clubs c ON c.id=m.club_id
    WHERE m.user_id=:uid AND m.role IN ('officer','lead','president','vice president') AND m.status='active'
    LIMIT 1
");
$memberStmt->execute([':uid' => $userId]);
$officerClub = $memberStmt->fetch(PDO::FETCH_ASSOC);
if (!$officerClub) { header('Location: index.php?page=home'); exit; }

$clubId      = (int)$officerClub['club_id'];
$officerRole = $officerClub['role'];
$clubName    = $officerClub['club_name'];
$clubInitial = strtoupper(substr($clubName, 0, 1));

// Fetch members — includes club_position
$stmt = $pdo->prepare("
    SELECT m.id, m.role, m.club_position, m.joined_at, m.course, m.year, m.section,
           u.id AS user_id, u.first_name, u.last_name, u.email, u.student_id
    FROM members m
    JOIN users u ON u.id = m.user_id
    WHERE m.club_id=:cid AND m.status='active'
    ORDER BY FIELD(m.role,'president','vice president','officer','lead','member'), u.first_name ASC
");
$stmt->execute([':cid' => $clubId]);
$dbMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Attendance score per member
$scoreMap  = [];
$scoreStmt = $pdo->prepare("
    SELECT
        m.user_id,
        COUNT(DISTINCT a.id)  AS attended,
        COUNT(DISTINCT ea.id) AS rsvped,
        LEAST(COUNT(DISTINCT cm.id), 10) AS chats,
        CASE WHEN m.joined_at >= DATE_FORMAT(NOW(),'%Y-%m-01') THEN 1 ELSE 0 END AS new_member
    FROM members m
    LEFT JOIN attendance      a  ON a.user_id  = m.user_id
    LEFT JOIN event_attendees ea ON ea.user_id = m.user_id AND ea.rsvp = 'confirmed'
    LEFT JOIN club_messages   cm ON cm.sender_id = m.user_id AND cm.club_id = :cid2 AND cm.is_deleted = 0
    WHERE m.club_id = :cid AND m.status = 'active'
    GROUP BY m.user_id, m.joined_at
");
$scoreStmt->execute([':cid' => $clubId, ':cid2' => $clubId]);
foreach ($scoreStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $attended = (int)$row['attended'];
    $rsvped   = (int)$row['rsvped'];
    $chats    = (int)$row['chats'];
    $isNew    = (int)$row['new_member'];
    $scoreMap[$row['user_id']] = [
        'score'     => min(100, ($attended * 30) + ($rsvped * 10) + ($chats * 5) + ($isNew * 20)),
        'breakdown' => "Events attended: {$attended} · RSVPs: {$rsvped} · Messages: {$chats}",
    ];
}

// Pending applications
$stmt = $pdo->prepare("
    SELECT ap.id, ap.status, ap.applied_at, ap.course AS app_course, ap.year, ap.section,
           ap.extras, ap.student_id_no, ap.phone AS app_phone,
           u.id AS user_id, u.first_name, u.last_name, u.email
    FROM applications ap
    JOIN users u ON u.id = ap.user_id
    WHERE ap.club_id=:cid AND ap.status='pending' AND ap.reviewer_type='officer'
    ORDER BY ap.applied_at DESC LIMIT 30
");
$stmt->execute([':cid' => $clubId]);
$dbApplicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalMembers = count($dbMembers);
$officerCount = count(array_filter($dbMembers, fn($m) => in_array($m['role'], ['officer','lead','president','vice president'])));
$pendingCount = count($dbApplicants);

$thisMonth    = date('Y-m-01');
$newThisMonth = count(array_filter($dbMembers, fn($m) => $m['joined_at'] && $m['joined_at'] >= $thisMonth));

$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadStmt->execute([$userId]);
$unreadNotifs = (int)$unreadStmt->fetchColumn();

$chatStmt = $pdo->prepare("SELECT COUNT(*) FROM club_messages WHERE club_id=? AND sender_id!=? AND is_deleted=0 AND sent_at > COALESCE((SELECT last_read FROM club_message_reads WHERE user_id=? AND club_id=?), '2000-01-01')");
$chatStmt->execute([$clubId, $userId, $userId, $clubId]);
$unreadChat = (int)$chatStmt->fetchColumn();