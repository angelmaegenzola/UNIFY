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
        $clubId = (int)$memberRow['club_id'];

        switch ($action) {

            case 'evt_create':
                $stmt = $pdo->prepare("
                    INSERT INTO events (name,description,event_date,start_time,end_time,location,club_id,status)
                    VALUES (:name,:desc,:date,:start,:end,:location,:club_id,'upcoming')
                ");
                $stmt->execute([
                    ':name'     => $body['name']     ?? '',
                    ':desc'     => $body['desc']     ?? '',
                    ':date'     => $body['date']     ?? '',
                    ':start'    => $body['start']    ?: null,
                    ':end'      => $body['end']      ?: null,
                    ':location' => $body['location'] ?? '',
                    ':club_id'  => $clubId,
                ]);
                $newId = $pdo->lastInsertId();
                // Notify all club members
                $members = $pdo->prepare("SELECT user_id FROM members WHERE club_id=? AND status='active' AND user_id!=?");
                $members->execute([$clubId, $userId]);
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1"); $cnameStmt->execute([$clubId]); $cname = $cnameStmt->fetchColumn();
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id,type,title,message,link) VALUES (?,'event_new','New Event: ".addslashes($body['name']??"")."',?,'index.php?page=officer_events')");
                foreach ($members->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                    $notifStmt->execute([$uid, $cname . ' has a new event: ' . ($body['name'] ?? '') . ' on ' . ($body['date'] ?? '')]);
                }
                echo json_encode(['success' => true, 'id' => $newId]);
                break;

            case 'evt_update':
                $evtId   = (int)($body['id'] ?? 0);
                $evtName = $body['name'] ?? '';
                $evtDate = $body['date'] ?? '';

                // Fetch old event to detect meaningful changes
                $oldEvt = $pdo->prepare("SELECT name, event_date, location, status FROM events WHERE id=? AND club_id=? LIMIT 1");
                $oldEvt->execute([$evtId, $clubId]);
                $oldRow = $oldEvt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("
                    UPDATE events SET name=:name,description=:desc,event_date=:date,
                    start_time=:start,end_time=:end,location=:location
                    WHERE id=:id AND club_id=:club_id
                ");
                $stmt->execute([
                    ':id'       => $evtId,
                    ':name'     => $evtName,
                    ':desc'     => $body['desc']     ?? '',
                    ':date'     => $evtDate,
                    ':start'    => $body['start']    ?: null,
                    ':end'      => $body['end']      ?: null,
                    ':location' => $body['location'] ?? '',
                    ':club_id'  => $clubId,
                ]);

                // Notify club members only if something meaningful changed
                if ($oldRow && ($oldRow['name'] !== $evtName || $oldRow['event_date'] !== $evtDate)) {
                    $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1"); $cnameStmt->execute([$clubId]); $cname = $cnameStmt->fetchColumn();
                    $members = $pdo->prepare("SELECT user_id FROM members WHERE club_id=? AND status='active' AND user_id!=?");
                    $members->execute([$clubId, $userId]);
                    $notifStmt = $pdo->prepare("
                        INSERT INTO notifications (user_id, type, title, message, link)
                        VALUES (?, 'event_updated', ?, ?, 'index.php?page=officer_events')
                    ");
                    $msg = $cname . ': Event "' . $evtName . '" has been updated' .
                           ($evtDate !== ($oldRow['event_date'] ?? '') ? ' (new date: ' . date('M j, Y', strtotime($evtDate)) . ')' : '') . '.';
                    foreach ($members->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                        $notifStmt->execute([$uid, 'Event Updated: ' . $evtName, $msg]);
                    }
                }
                echo json_encode(['success' => true]);
                break;

            case 'evt_delete':
                $evtId = (int)($body['id'] ?? 0);
                // Fetch name before deleting so we can notify members
                $delEvt = $pdo->prepare("SELECT name, event_date FROM events WHERE id=? AND club_id=? LIMIT 1");
                $delEvt->execute([$evtId, $clubId]);
                $delRow = $delEvt->fetch(PDO::FETCH_ASSOC);

                $pdo->prepare("DELETE FROM events WHERE id=:id AND club_id=:club_id")
                    ->execute([':id' => $evtId, ':club_id' => $clubId]);

                // Notify club members that the event was cancelled
                if ($delRow) {
                    $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1"); $cnameStmt->execute([$clubId]); $cname = $cnameStmt->fetchColumn();
                    $members = $pdo->prepare("SELECT user_id FROM members WHERE club_id=? AND status='active' AND user_id!=?");
                    $members->execute([$clubId, $userId]);
                    $notifStmt = $pdo->prepare("
                        INSERT INTO notifications (user_id, type, title, message, link)
                        VALUES (?, 'event_cancelled', ?, ?, 'index.php?page=officer_events')
                    ");
                    $dateLabel = $delRow['event_date'] ? ' scheduled for ' . date('M j, Y', strtotime($delRow['event_date'])) : '';
                    foreach ($members->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                        $notifStmt->execute([
                            $uid,
                            'Event Cancelled: ' . $delRow['name'],
                            $cname . ': The event "' . $delRow['name'] . '"' . $dateLabel . ' has been cancelled.'
                        ]);
                    }
                }
                echo json_encode(['success' => true]);
                break;

            // ── Quick-create announcement ────────────────────────
            case 'ann_quick_create':
                $stmt = $pdo->prepare("
                    INSERT INTO announcements (title, description, category, status, club_id, posted_at)
                    VALUES (:title, :desc, :category, :status, :club_id, NOW())
                ");
                $stmt->execute([
                    ':title'    => substr(trim($body['title']    ?? ''), 0, 191),
                    ':desc'     => trim($body['desc']     ?? ''),
                    ':category' => $body['category'] ?? 'General',
                    ':status'   => in_array($body['status'] ?? '', ['info','warning','urgent','event','general'])
                                   ? $body['status'] : 'info',
                    ':club_id'  => $clubId,
                ]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                break;

            // ── Quick-create event ───────────────────────────────
            case 'evt_quick_create':
                $evtName = trim($body['name'] ?? '');
                $evtDate = trim($body['date'] ?? '');
                if (!$evtName || !$evtDate) {
                    echo json_encode(['error' => 'Name and date are required.']); break;
                }
                $stmt = $pdo->prepare("
                    INSERT INTO events (name, description, event_date, start_time, end_time, location, club_id, status)
                    VALUES (:name, :desc, :date, :start, :end, :location, :club_id, 'upcoming')
                ");
                $stmt->execute([
                    ':name'     => $evtName,
                    ':desc'     => $body['desc']     ?? '',
                    ':date'     => $evtDate,
                    ':start'    => $body['start']    ?: null,
                    ':end'      => $body['end']      ?: null,
                    ':location' => $body['location'] ?? '',
                    ':club_id'  => $clubId,
                ]);
                $newId = $pdo->lastInsertId();
                // Notify all active club members
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                $cnameStmt->execute([$clubId]);
                $cname = $cnameStmt->fetchColumn();
                $members = $pdo->prepare("SELECT user_id FROM members WHERE club_id=? AND status='active' AND user_id!=?");
                $members->execute([$clubId, $userId]);
                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'event_new', ?, ?, 'index.php?page=officer_events')
                ");
                foreach ($members->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                    $notifStmt->execute([$uid, 'New Event: ' . $evtName, $cname . ' has a new event: ' . $evtName . ' on ' . $evtDate]);
                }
                echo json_encode(['success' => true, 'id' => $newId]);
                break;

            // ── Approve student application ──────────────────────
            case 'app_approve':
                $pdo->beginTransaction();
                $appId   = (int)($body['id'] ?? 0);
                $appStmt = $pdo->prepare("
                    SELECT * FROM applications
                    WHERE id=:id AND club_id=:cid AND status='pending' AND reviewer_type='officer'
                    LIMIT 1
                ");
                $appStmt->execute([':id' => $appId, ':cid' => $clubId]);
                $app = $appStmt->fetch(PDO::FETCH_ASSOC);
                if (!$app) { $pdo->rollBack(); echo json_encode(['error' => 'Application not found.']); break; }

                $pdo->prepare("UPDATE applications SET status='approved', reviewed_at=NOW() WHERE id=?")
                    ->execute([$appId]);
                $pdo->prepare("
                    INSERT IGNORE INTO members (user_id, club_id, course, year, section, role, status, joined_at)
                    VALUES (?, ?, ?, ?, ?, 'member', 'active', NOW())
                ")->execute([$app['user_id'], $app['club_id'], $app['course'] ?? null, $app['year'] ?? null, $app['section'] ?? null]);

                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                $cnameStmt->execute([$clubId]);
                $cname = $cnameStmt->fetchColumn();
                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'app_approved', 'Application Approved! 🎉', ?, 'index.php?page=studenthome')
                ")->execute([$app['user_id'], 'Congratulations! Your application to join ' . $cname . ' has been approved. Welcome to the club!']);

                // Update stats badge
                $statsStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE club_id=? AND status='pending' AND reviewer_type='officer'");
                $statsStmt->execute([$clubId]);
                $pendingCount = (int)$statsStmt->fetchColumn();

                $pdo->commit();
                echo json_encode(['success' => true, 'pending_count' => $pendingCount]);
                break;

            // ── Reject student application ───────────────────────
            case 'app_reject':
                $appId  = (int)($body['id'] ?? 0);
                $reason = trim($body['reason'] ?? '');
                $appStmt = $pdo->prepare("
                    SELECT * FROM applications
                    WHERE id=:id AND club_id=:cid AND status='pending'
                    LIMIT 1
                ");
                $appStmt->execute([':id' => $appId, ':cid' => $clubId]);
                $app = $appStmt->fetch(PDO::FETCH_ASSOC);
                if (!$app) { echo json_encode(['error' => 'Application not found.']); break; }

                $pdo->prepare("UPDATE applications SET status='rejected', reviewed_at=NOW() WHERE id=?")
                    ->execute([$appId]);
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                $cnameStmt->execute([$clubId]);
                $cname = $cnameStmt->fetchColumn();
                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'app_rejected', 'Application Update', ?, 'index.php?page=explore')
                ")->execute([$app['user_id'], 'Your application to join ' . $cname . ' was not approved.' . ($reason ? ' Reason: ' . $reason : '')]);

                echo json_encode(['success' => true]);
                break;

            // ── Accept event assignment (student confirms role) ──
            case 'assignment_accept':
                $assignId = (int)($body['assign_id'] ?? 0);
                $aStmt = $pdo->prepare("
                    SELECT ea.*, e.name AS event_name, e.club_id AS evt_club_id
                    FROM event_assignees ea
                    JOIN events e ON e.id = ea.event_id
                    WHERE ea.id = :aid AND ea.user_id = :uid AND ea.status = 'pending'
                    LIMIT 1
                ");
                $aStmt->execute([':aid' => $assignId, ':uid' => $userId]);
                $aRow = $aStmt->fetch(PDO::FETCH_ASSOC);
                if (!$aRow) { echo json_encode(['error' => 'Assignment not found or already responded.']); break; }

                $pdo->prepare("UPDATE event_assignees SET status='accepted' WHERE id=?")
                    ->execute([$assignId]);

                // Notify the club officers
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                $cnameStmt->execute([$aRow['evt_club_id']]);
                $cname = $cnameStmt->fetchColumn();
                $officerList = $pdo->prepare("
                    SELECT user_id FROM members
                    WHERE club_id=? AND status='active' AND role IN ('president','vice president','officer','lead')
                ");
                $officerList->execute([$aRow['evt_club_id']]);
                $uNameStmt = $pdo->prepare("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=? LIMIT 1");
                $uNameStmt->execute([$userId]);
                $uName = $uNameStmt->fetchColumn();
                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'assignment_accepted', 'Assignment Accepted ✅', ?, 'index.php?page=officer_events')
                ");
                foreach ($officerList->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                    $notifStmt->execute([$uid, $uName . ' accepted their role in "' . $aRow['event_name'] . '".']);
                }
                echo json_encode(['success' => true]);
                break;

            // ── Decline event assignment ─────────────────────────
            case 'assignment_decline':
                $assignId = (int)($body['assign_id'] ?? 0);
                $aStmt = $pdo->prepare("
                    SELECT ea.*, e.name AS event_name, e.club_id AS evt_club_id
                    FROM event_assignees ea
                    JOIN events e ON e.id = ea.event_id
                    WHERE ea.id = :aid AND ea.user_id = :uid AND ea.status = 'pending'
                    LIMIT 1
                ");
                $aStmt->execute([':aid' => $assignId, ':uid' => $userId]);
                $aRow = $aStmt->fetch(PDO::FETCH_ASSOC);
                if (!$aRow) { echo json_encode(['error' => 'Assignment not found or already responded.']); break; }

                $pdo->prepare("UPDATE event_assignees SET status='declined' WHERE id=?")
                    ->execute([$assignId]);

                // Notify the club officers
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1");
                $cnameStmt->execute([$aRow['evt_club_id']]);
                $cname = $cnameStmt->fetchColumn();
                $officerList = $pdo->prepare("
                    SELECT user_id FROM members
                    WHERE club_id=? AND status='active' AND role IN ('president','vice president','officer','lead')
                ");
                $officerList->execute([$aRow['evt_club_id']]);
                $uNameStmt = $pdo->prepare("SELECT CONCAT(first_name,' ',last_name) FROM users WHERE id=? LIMIT 1");
                $uNameStmt->execute([$userId]);
                $uName = $uNameStmt->fetchColumn();
                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'assignment_declined', 'Assignment Declined ❌', ?, 'index.php?page=officer_events')
                ");
                foreach ($officerList->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                    $notifStmt->execute([$uid, $uName . ' declined their role in "' . $aRow['event_name'] . '".']);
                }
                echo json_encode(['success' => true]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Unknown action']);
        }
    } catch (Exception $e) {
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
$userId      = (int)$_SESSION['user_id'];
$userFirst   = $_SESSION['first_name'] ?? 'Officer';
$userLast    = $_SESSION['last_name']  ?? '';
$userName    = trim($userFirst . ' ' . $userLast);
$userInit    = strtoupper(substr($userFirst, 0, 1));

// Profile picture
$picStmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$picStmt->execute([$userId]);
$picFile = $picStmt->fetchColumn();
$avatar_url = $picFile
    ? '/UNIFY(db)/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';



$memberStmt = $pdo->prepare("
    SELECT m.club_id, m.role,
           c.name AS club_name, c.acronym, c.category,
           c.description AS club_desc, c.logo_path,
           c.room, c.founded
    FROM members m
    JOIN clubs c ON c.id = m.club_id
    WHERE m.user_id = :uid
      AND m.role IN ('officer','lead','president','vice president')
      AND m.status = 'active'
    LIMIT 1
");
$memberStmt->execute([':uid' => $userId]);
$officerClub = $memberStmt->fetch(PDO::FETCH_ASSOC);
if (!$officerClub) { header('Location: index.php?page=home'); exit; }

$clubId      = (int)$officerClub['club_id'];
$officerRole = $officerClub['role'];
$clubName    = $officerClub['club_name'];
$clubInitial = strtoupper(substr($clubName, 0, 1));

// Build $stats array required by the view
$s1 = $pdo->prepare("SELECT COUNT(*) FROM members WHERE club_id=? AND status='active'");
$s1->execute([$clubId]);
$s2 = $pdo->prepare("SELECT COUNT(*) FROM events WHERE club_id=? AND event_date >= CURDATE() AND status='upcoming'");
$s2->execute([$clubId]);
$s3 = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE club_id=? AND status='pending' AND reviewer_type='officer'");
$s3->execute([$clubId]);
$stats = [
    'total_members'   => (int)$s1->fetchColumn(),
    'upcoming_events' => (int)$s2->fetchColumn(),
    'pending_apps'    => (int)$s3->fetchColumn(),
];

// All club events (all time)
$stmt = $pdo->prepare("
    SELECT * FROM events WHERE club_id=:cid
    ORDER BY event_date DESC, start_time ASC
");
$stmt->execute([':cid' => $clubId]);
$dbEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today         = date('Y-m-d');
$upcomingCount = count(array_filter($dbEvents, fn($e) => $e['event_date'] >= $today && $e['status'] === 'upcoming'));
$pastCount     = count(array_filter($dbEvents, fn($e) => $e['event_date'] < $today));
$totalEvents   = count($dbEvents);

$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadStmt->execute([$userId]);
$unreadNotifs = (int)$unreadStmt->fetchColumn();

$chatStmt = $pdo->prepare("SELECT COUNT(*) FROM club_messages WHERE club_id=? AND sender_id!=? AND is_deleted=0 AND sent_at > COALESCE((SELECT last_read FROM club_message_reads WHERE user_id=? AND club_id=?), '2000-01-01')");
$chatStmt->execute([$clubId, $userId, $userId, $clubId]);
$unreadChat = (int)$chatStmt->fetchColumn();

// ── Members preview (right sidebar) ─────────────────────────
$stmt = $pdo->prepare("
    SELECT m.id, m.role, m.joined_at, m.course, m.year, m.section,
           u.first_name, u.last_name, u.email
    FROM members m
    JOIN users u ON u.id = m.user_id
    WHERE m.club_id = :cid AND m.status = 'active'
    ORDER BY FIELD(m.role,'president','vice president','officer','lead','member'), u.first_name ASC
    LIMIT 6
");
$stmt->execute([':cid' => $clubId]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Upcoming events for timeline ─────────────────────────────
$events = array_values(array_filter($dbEvents, fn($e) =>
    $e['event_date'] >= $today && $e['status'] === 'upcoming'
));

// ── Announcements (latest 5) ─────────────────────────────────
$stmt = $pdo->prepare("
    SELECT * FROM announcements WHERE club_id = :cid
    ORDER BY posted_at DESC LIMIT 5
");
$stmt->execute([':cid' => $clubId]);
$anns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Pending applications ─────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT ap.id, ap.status, ap.applied_at, ap.course AS app_course,
           ap.year, ap.section, ap.extras, ap.student_id_no,
           ap.phone AS app_phone,
           u.first_name, u.last_name, u.email
    FROM applications ap
    JOIN users u ON u.id = ap.user_id
    WHERE ap.club_id = :cid AND ap.status = 'pending' AND ap.reviewer_type = 'officer'
    ORDER BY ap.applied_at DESC LIMIT 10
");
$stmt->execute([':cid' => $clubId]);
$pendingApps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Recent activity (events + announcements) ──────────────────
$stmt = $pdo->prepare("
    (SELECT 'event' AS type, name AS label, created_at AS activity_at
     FROM events WHERE club_id = :cid1)
    UNION ALL
    (SELECT 'announcement' AS type, title AS label, posted_at AS activity_at
     FROM announcements WHERE club_id = :cid2)
    ORDER BY activity_at DESC LIMIT 8
");
$stmt->execute([':cid1' => $clubId, ':cid2' => $clubId]);
$activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Incoming collaboration requests ─────────────────────────
$collabRequests = [];
try {
    $stmt = $pdo->prepare("
        SELECT ccr.id, ccr.event_name, ccr.proposed_date, ccr.message,
               ccr.created_at,
               fc.name AS from_club_name, fc.logo_path AS from_club_logo,
               fc.acronym AS from_club_acronym,
               CONCAT(u.first_name,' ',u.last_name) AS proposed_by_name
        FROM club_collaboration_requests ccr
        JOIN clubs fc ON fc.id = ccr.from_club_id
        JOIN users u  ON u.id  = ccr.proposed_by
        WHERE ccr.to_club_id = :cid AND ccr.status = 'pending'
        ORDER BY ccr.created_at DESC
    ");
    $stmt->execute([':cid' => $clubId]);
    $collabRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $collabRequests = []; }

$pendingCollabCount = count($collabRequests);

// ── Display helpers ───────────────────────────────────────────
$av_colors = ['av-green', 'av-teal', 'av-gold', 'av-blue', 'av-red', 'av-purple'];
$role_labels = [
    'president'      => ['label' => 'President', 'class' => 'president'],
    'vice president' => ['label' => 'VP',        'class' => 'vp'],
    'officer'        => ['label' => 'Officer',   'class' => 'officer'],
    'lead'           => ['label' => 'Lead',      'class' => 'officer'],
    'member'         => ['label' => 'Member',    'class' => 'member'],
];
$ann_icons = [
    'info'    => ['dot' => 'dot-blue'],
    'warning' => ['dot' => 'dot-yellow'],
    'urgent'  => ['dot' => 'dot-red'],
    'event'   => ['dot' => 'dot-green'],
    'general' => ['dot' => 'dot-gray'],
];

if (!function_exists('ohRelTime')) {
    function ohRelTime(?string $dt): string {
        if (!$dt) return '—';
        $diff = time() - strtotime($dt);
        if ($diff < 60)     return 'just now';
        if ($diff < 3600)   return (int)($diff / 60) . 'm ago';
        if ($diff < 86400)  return (int)($diff / 3600) . 'h ago';
        if ($diff < 604800) return (int)($diff / 86400) . 'd ago';
        return date('M j', strtotime($dt));
    }
}
