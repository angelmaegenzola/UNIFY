<?php
/* ============================================================
   UNIFY — officer_explore_controller.php
   Path: app/controllers/officer_explore_controller.php

   Loaded by:  index.php?page=explore  (officer context)
   Requires:   session with user_id set, officer role verified
============================================================ */

if (session_status() === PHP_SESSION_NONE) session_start();

/* ── Auth guard ─────────────────────────────────────────────── */
if (empty($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

/* ── DB connection ──────────────────────────────────────────── */
$db = new mysqli('127.0.0.1', 'root', '', 'unify_db');
if ($db->connect_error) {
    die('Database connection failed: ' . $db->connect_error);
}
$db->set_charset('utf8mb4');

/* ── Load model ─────────────────────────────────────────────── */
require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/app/models/exploreclub_model.php';
$model = new ExploreClubModel($db);

/* ── Helper: format time (defined early — used in AJAX block) ── */
function fmtTime(string $t): string {
    return date('g:i A', strtotime($t));
}

/* ── Session user data ──────────────────────────────────────── */
$userId    = (int) $_SESSION['user_id'];
$userFirst = htmlspecialchars($_SESSION['first_name'] ?? 'Officer');
$userLast  = htmlspecialchars($_SESSION['last_name']  ?? '');
$userName  = trim($userFirst . ' ' . $userLast);
$userInit  = strtoupper(substr($userFirst, 0, 1));

// Profile picture
$picStmt = $db->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$picStmt->bind_param('i', $userId);
$picStmt->execute();
$picStmt->bind_result($picFile);
$picStmt->fetch();
$picStmt->close();
$avatar_url = $picFile
    ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';



/* ── Officer club context ───────────────────────────────────── */
$officerRole = htmlspecialchars($_SESSION['club_role'] ?? 'officer');

/* ── Officer's own club id ──────────────────────────────────── */
$myClubId = $model->getUserActiveClubId($userId);

/* ── Fetch officer's club for sidebar + real club name ─────── */
$officerClub = [];
$clubName    = 'My Club';
$clubInitial = 'C';

if ($myClubId) {
    $officerClub = $model->getClubById($myClubId) ?? [];
    if (!empty($officerClub['name'])) {
        $clubName    = htmlspecialchars($officerClub['name']);
        $clubInitial = strtoupper(substr($officerClub['name'], 0, 1));
    }
} elseif (!empty($_SESSION['club_name'])) {
    $clubName    = htmlspecialchars($_SESSION['club_name']);
    $clubInitial = strtoupper(substr($clubName, 0, 1));
}

/* ══════════════════════════════════════════════════════════════
   AJAX HANDLERS
   JS fetches: index.php?page=explore&ajax=club_officers&club_id=X
   The ?page=explore ensures this controller is loaded first,
   then the ajax param triggers one of the handlers below.
   Returns JSON then exits — page HTML is never rendered.
══════════════════════════════════════════════════════════════ */
if (!empty($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['ajax'];

    /* ── Club Officers ─────────────────────────────────────── */
    if ($action === 'club_officers') {
        $clubId = isset($_GET['club_id']) ? (int) $_GET['club_id'] : 0;
        if ($clubId <= 0) { echo json_encode([]); $db->close(); exit; }

        $rows = $model->getClubOfficers($clubId);

        $officers = array_map(function($r) {
            return [
                'name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                'role' => $r['role'] ?? '',
            ];
        }, $rows);

        echo json_encode($officers);
        $db->close();
        exit;
    }

    /* ── Club Upcoming Events ──────────────────────────────── */
    if ($action === 'club_events') {
        $clubId = isset($_GET['club_id']) ? (int) $_GET['club_id'] : 0;
        if ($clubId <= 0) { echo json_encode([]); $db->close(); exit; }

        $rows = $model->getClubUpcomingEvents($clubId);

        $events = array_map(function($r) {
            return [
                'name'       => $r['name']       ?? '',
                'event_date' => $r['event_date']  ?? '',
                'start_time' => !empty($r['start_time']) ? fmtTime($r['start_time']) : '',
                'location'   => $r['location']   ?? '',
            ];
        }, $rows);

        echo json_encode($events);
        $db->close();
        exit;
    }

    /* ── Notifications ────────────────────────────────────── */
    if ($action === 'notifications') {
        $stmt = $db->prepare("
            SELECT id, title, message, is_read,
                   DATE_FORMAT(created_at, '%b %d, %Y') AS created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $notifs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode($notifs);
        $db->close();
        exit;
    }

    /* ── Mark single notification read ───────────────────── */
    if ($action === 'mark_notif_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nid = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($nid > 0) {
            $stmt = $db->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
            $stmt->bind_param('ii', $nid, $userId);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['ok' => true]);
        $db->close();
        exit;
    }

    /* ── Mark all notifications read ─────────────────────── */
    if ($action === 'mark_all_notifs_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => true]);
        $db->close();
        exit;
    }

    /* ── Send Collaboration Proposal ─────────────────────── */
    if ($action === 'send_collab' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Accept both JSON body (application/json) and FormData
        $jsonBody = json_decode(file_get_contents('php://input'), true) ?? [];
        $targetClubId = (int)   ($jsonBody['target_club_id']  ?? $_POST['target_club_id']  ?? 0);
        $eventName    = trim(    $jsonBody['event_name']       ?? $_POST['event_name']       ?? '');
        $proposedDate = trim(    $jsonBody['proposed_date']    ?? $_POST['proposed_date']    ?? '') ?: null;
        $message      = trim(    $jsonBody['message']          ?? $_POST['message']          ?? '');

        if (!$myClubId) {
            echo json_encode(['success' => false, 'message' => 'You are not an officer of any club.']);
            $db->close(); exit;
        }
        if (!$targetClubId || $targetClubId === $myClubId) {
            echo json_encode(['success' => false, 'message' => 'Invalid target club.']);
            $db->close(); exit;
        }
        if (!$eventName) {
            echo json_encode(['success' => false, 'message' => 'Please enter a proposed event name.']);
            $db->close(); exit;
        }

        // Check for already-pending request between same clubs
        $chkStmt = $db->prepare("
            SELECT id FROM club_collaboration_requests
            WHERE from_club_id = ? AND to_club_id = ? AND status = 'pending'
            LIMIT 1
        ");
        $chkStmt->bind_param('ii', $myClubId, $targetClubId);
        $chkStmt->execute();
        $existing = $chkStmt->get_result()->fetch_assoc();
        $chkStmt->close();

        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'You already have a pending proposal to this club.']);
            $db->close(); exit;
        }

        // Insert the collaboration request
        $insStmt = $db->prepare("
            INSERT INTO club_collaboration_requests
                (from_club_id, to_club_id, proposed_by, event_name, proposed_date, message)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insStmt->bind_param('iiisss', $myClubId, $targetClubId, $userId, $eventName, $proposedDate, $message);
        $insStmt->execute();
        $requestId = $db->insert_id;
        $insStmt->close();

        // Get my club name for notification
        $myClubRow = $db->query("SELECT name FROM clubs WHERE id = $myClubId LIMIT 1")->fetch_assoc();
        $fromClubName = $myClubRow['name'] ?? 'A club';

        // Find the president/vice president of the target club to notify
        $officerStmt = $db->prepare("
            SELECT m.user_id FROM members m
            WHERE m.club_id = ? AND m.status = 'active'
              AND m.role IN ('president','vice president','officer','lead')
            ORDER BY FIELD(m.role,'president','vice president','officer','lead')
            LIMIT 5
        ");
        $officerStmt->bind_param('i', $targetClubId);
        $officerStmt->execute();
        $targetOfficers = $officerStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $officerStmt->close();

        // Send notification to each officer of the target club
        $notifLink  = 'index.php?page=officer_home&tab=collab';
        $notifTitle = '🤝 Collaboration Proposal Received';
        $notifMsg   = $fromClubName . ' is proposing a collaboration: "' . $eventName . '". Review it on your Home page.';
        $notifStmt  = $db->prepare("
            INSERT INTO notifications (user_id, type, title, message, link)
            VALUES (?, 'collab_request', ?, ?, ?)
        ");
        foreach ($targetOfficers as $off) {
            $uid = (int)$off['user_id'];
            $notifStmt->bind_param('isss', $uid, $notifTitle, $notifMsg, $notifLink);
            $notifStmt->execute();
        }
        $notifStmt->close();

        echo json_encode(['success' => true, 'request_id' => $requestId]);
        $db->close(); exit;
    }

    /* ── Respond to Collaboration Proposal (accept / decline) ─ */
    if ($action === 'collab_respond' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $response  = trim($_POST['response'] ?? ''); // 'accepted' or 'declined'

        if (!in_array($response, ['accepted', 'declined'], true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid response.']);
            $db->close(); exit;
        }
        if (!$myClubId) {
            echo json_encode(['success' => false, 'message' => 'You are not an officer of any club.']);
            $db->close(); exit;
        }

        // Fetch the request and verify it targets our club
        $fetchStmt = $db->prepare("
            SELECT ccr.*, fc.name AS from_club_name
            FROM club_collaboration_requests ccr
            JOIN clubs fc ON fc.id = ccr.from_club_id
            WHERE ccr.id = ? AND ccr.to_club_id = ? AND ccr.status = 'pending'
            LIMIT 1
        ");
        $fetchStmt->bind_param('ii', $requestId, $myClubId);
        $fetchStmt->execute();
        $req = $fetchStmt->get_result()->fetch_assoc();
        $fetchStmt->close();

        if (!$req) {
            echo json_encode(['success' => false, 'message' => 'Request not found or already responded.']);
            $db->close(); exit;
        }

        // Update status
        $now = date('Y-m-d H:i:s');
        $updStmt = $db->prepare("
            UPDATE club_collaboration_requests
            SET status = ?, responded_by = ?, responded_at = ?
            WHERE id = ?
        ");
        $updStmt->bind_param('siis', $response, $userId, $now, $requestId);
        $updStmt->execute();
        $updStmt->close();

        // Get my club name
        $myClubRow2 = $db->query("SELECT name FROM clubs WHERE id = $myClubId LIMIT 1")->fetch_assoc();
        $myClubNameStr = $myClubRow2['name'] ?? 'The club';

        // Notify the proposing officer
        $notifType  = ($response === 'accepted') ? 'collab_accepted' : 'collab_declined';
        $notifTitle = ($response === 'accepted')
            ? '✅ Collaboration Proposal Accepted!'
            : '❌ Collaboration Proposal Declined';
        $notifMsg   = $myClubNameStr . ($response === 'accepted'
            ? ' accepted your collaboration proposal for "' . $req['event_name'] . '"! Coordinate via Club Chat.'
            : ' declined your collaboration proposal for "' . $req['event_name'] . '".');
        $notifLink  = 'index.php?page=officer_home';

        $proposedBy = (int) $req['proposed_by'];
        $notifIns = $db->prepare("
            INSERT INTO notifications (user_id, type, title, message, link)
            VALUES (?, ?, ?, ?, ?)
        ");
        $notifIns->bind_param('issss', $proposedBy, $notifType, $notifTitle, $notifMsg, $notifLink);
        $notifIns->execute();
        $notifIns->close();

        echo json_encode([
            'success'  => true,
            'response' => $response,
            'message'  => ($response === 'accepted')
                ? 'Collaboration accepted! The proposing club has been notified.'
                : 'Proposal declined. The proposing club has been notified.',
        ]);
        $db->close(); exit;
    }

    /* ── Get pending collab requests FOR my club ──────────── */
    if ($action === 'collab_requests') {
        if (!$myClubId) {
            echo json_encode([]);
            $db->close(); exit;
        }
        $stmt = $db->prepare("
            SELECT ccr.id, ccr.event_name, ccr.proposed_date, ccr.message, ccr.created_at,
                   fc.name AS from_club_name, fc.logo_path AS from_club_logo,
                   fc.acronym AS from_club_acronym,
                   CONCAT(u.first_name,' ',u.last_name) AS proposed_by_name, u.id AS proposed_by_id
            FROM club_collaboration_requests ccr
            JOIN clubs fc ON fc.id = ccr.from_club_id
            JOIN users u  ON u.id  = ccr.proposed_by
            WHERE ccr.to_club_id = ? AND ccr.status = 'pending'
            ORDER BY ccr.created_at DESC
        ");
        $stmt->bind_param('i', $myClubId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(['success' => true, 'requests' => $rows]);
        $db->close(); exit;
    }

    /* ── Unknown AJAX action ─────────────────────────────── */
    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
    $db->close();
    exit;
}

/* ══════════════════════════════════════════════════════════════
   PAGE DATA  (only reached when rendering the full HTML page)
══════════════════════════════════════════════════════════════ */

/* ── Unread notifications count ────────────────────────────── */
$notifStmt = $db->prepare("
    SELECT COUNT(*) AS cnt FROM notifications
    WHERE user_id = ? AND is_read = 0
");
$notifStmt->bind_param('i', $userId);
$notifStmt->execute();
$unreadNotifs = (int) $notifStmt->get_result()->fetch_assoc()['cnt'];
$notifStmt->close();

$unreadChat = 0;
try {
    $chatStmt = $db->prepare("SELECT COUNT(*) FROM club_messages WHERE club_id=? AND sender_id!=? AND is_deleted=0 AND sent_at > COALESCE((SELECT last_read FROM club_message_reads WHERE user_id=? AND club_id=?), '2000-01-01')");
    $chatStmt->bind_param('iiii', $myClubId, $userId, $userId, $myClubId);
    $chatStmt->execute();
    $unreadChat = (int)$chatStmt->get_result()->fetch_row()[0];
    $chatStmt->close();
} catch (Exception $e) { $unreadChat = 0; }

/* ── All clubs — excluding the officer's own club ───────────── */
$allClubs = $model->getAllClubs();

if ($myClubId) {
    $allClubs = array_values(array_filter($allClubs, function($c) use ($myClubId) {
        return (int)$c['id'] !== $myClubId;
    }));
}

$categories = $model->getCategories();
$totalClubs = count($allClubs);

/* ── Cast numeric fields for JSON safety ───────────────────── */
$allClubs = array_map(function($c) {
    $c['id']              = (int) $c['id'];
    $c['member_count']    = (int) $c['member_count'];
    $c['upcoming_events'] = (int) $c['upcoming_events'];
    return $c;
}, $allClubs);

$db->close();