<?php
// ============================================================
//  UNIFY — Dashboard Controller
//  GET  (no action) → loads page data via model
//  POST ?action=...  → AJAX handlers (JSON responses)
// ============================================================
ob_start();
session_start();

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?page=login'); exit;
}

// ── AJAX handler ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean();
    header('Content-Type: application/json');
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $_GET['action'] ?? '';

    try {
        switch ($action) {

            // ── Announcements CRUD ─────────────────────────────
            case 'ann_create':
                $stmt = $pdo->prepare("
                    INSERT INTO announcements (title, description, category, status, posted_at)
                    VALUES (:title, :desc, :category, :status, NOW())
                ");
                $stmt->execute([
                    ':title'    => $body['title']    ?? '',
                    ':desc'     => $body['desc']     ?? '',
                    ':category' => $body['category'] ?? 'General',
                    ':status'   => $body['status']   ?? 'info',
                ]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                break;

            case 'ann_update':
                $pdo->prepare("
                    UPDATE announcements
                    SET title=:title, description=:desc, category=:category, status=:status
                    WHERE id=:id
                ")->execute([
                    ':id'       => (int)($body['id'] ?? 0),
                    ':title'    => $body['title']    ?? '',
                    ':desc'     => $body['desc']     ?? '',
                    ':category' => $body['category'] ?? 'General',
                    ':status'   => $body['status']   ?? 'info',
                ]);
                echo json_encode(['success' => true]);
                break;

            case 'ann_delete':
                $pdo->prepare("DELETE FROM announcements WHERE id=:id")
                    ->execute([':id' => (int)($body['id'] ?? 0)]);
                echo json_encode(['success' => true]);
                break;

            // ── Application approve (admin queue only) ─────────
            case 'app_approve':
                $pdo->beginTransaction();
                $appId = (int)($body['id'] ?? 0);

                // GUARD: only approve if pending AND routed to admin
                $app = $pdo->prepare("
                    SELECT * FROM applications
                    WHERE id = ? AND status = 'pending' AND reviewer_type = 'admin'
                    LIMIT 1
                ");
                $app->execute([$appId]);
                $app = $app->fetch(PDO::FETCH_ASSOC);

                if (!$app) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Application not found or not in admin queue.']);
                    break;
                }

                $pdo->prepare("UPDATE applications SET status='approved', reviewed_at=NOW() WHERE id=?")
                    ->execute([$appId]);

                $pdo->prepare("
                    INSERT IGNORE INTO members
                        (user_id, club_id, course, year, section, role, status, joined_at)
                    VALUES (:uid, :cid, :course, :year, :section, 'member', 'active', NOW())
                ")->execute([
                    ':uid'     => $app['user_id'],
                    ':cid'     => $app['club_id'],
                    ':course'  => $app['course']  ?? null,
                    ':year'    => $app['year']    ?? null,
                    ':section' => $app['section'] ?? null,
                ]);

                $cname = $pdo->prepare("SELECT name FROM clubs WHERE id=?");
                $cname->execute([$app['club_id']]);
                $cname = $cname->fetchColumn();

                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'app_approved', 'Application Approved! 🎉', ?, 'index.php?page=studenthome')
                ")->execute([
                    $app['user_id'],
                    'Your application to join ' . $cname . ' has been approved. Welcome!',
                ]);

                $pdo->commit();
                echo json_encode(['success' => true]);
                break;

            // ── Application reject (admin queue only) ──────────
            case 'app_reject':
                $appId  = (int)($body['id'] ?? 0);
                $reason = trim($body['reason'] ?? '');

                // GUARD: only reject if pending AND routed to admin
                $app = $pdo->prepare("
                    SELECT user_id, club_id FROM applications
                    WHERE id = ? AND status = 'pending' AND reviewer_type = 'admin'
                    LIMIT 1
                ");
                $app->execute([$appId]);
                $app = $app->fetch(PDO::FETCH_ASSOC);

                if (!$app) {
                    echo json_encode(['success' => false, 'message' => 'Application not found or not in admin queue.']);
                    break;
                }

                $pdo->prepare("UPDATE applications SET status='rejected', reviewed_at=NOW() WHERE id=?")
                    ->execute([$appId]);

                $cname = $pdo->prepare("SELECT name FROM clubs WHERE id=?");
                $cname->execute([$app['club_id']]);
                $cname = $cname->fetchColumn();

                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'app_rejected', 'Application Update', ?, 'index.php?page=explore')
                ")->execute([
                    $app['user_id'],
                    'Your application to join ' . $cname . ' was not approved.'
                        . ($reason ? ' Reason: ' . $reason : ''),
                ]);

                echo json_encode(['success' => true]);
                break;

            // ── Club request approve ───────────────────────────
            case 'club_approve':
                $req_id = (int)($body['req_id'] ?? 0);
                if (!$req_id) {
                    echo json_encode(['success' => false, 'message' => 'Invalid request ID.']); break;
                }

                $pdo->beginTransaction();
                $req = $pdo->prepare("SELECT * FROM club_requests WHERE id=? AND status='pending' LIMIT 1");
                $req->execute([$req_id]);
                $r = $req->fetch(PDO::FETCH_ASSOC);

                if (!$r) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Request not found or already reviewed.']); break;
                }

                // Create the club
                $pdo->prepare("
                    INSERT INTO clubs (name, acronym, category, description, room, founded, status, budget)
                    VALUES (?, ?, ?, ?, ?, ?, 'active', 0.00)
                ")->execute([$r['name'], $r['acronym'], $r['category'], $r['description'], $r['room'], $r['founded']]);
                $new_club_id = (int)$pdo->lastInsertId();

                // Make requester the president
                $pdo->prepare("
                    INSERT IGNORE INTO members (user_id, club_id, role, status, joined_at)
                    VALUES (?, ?, 'president', 'active', NOW())
                ")->execute([$r['user_id'], $new_club_id]);

                // Mark request approved
                $pdo->prepare("
                    UPDATE club_requests SET status='approved', reviewed_by=?, reviewed_at=NOW() WHERE id=?
                ")->execute([$_SESSION['user_id'], $req_id]);

                // Notify the student
                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'club_approved', 'Your Club Request Was Approved! 🎉', ?, 'index.php?page=officer_dashboard')
                ")->execute([
                    $r['user_id'],
                    'Congratulations! Your club "' . $r['name'] . '" has been approved. You are now the club president. Head to your Officer Dashboard to get started.',
                ]);

                $pdo->commit();
                echo json_encode(['success' => true, 'club_id' => $new_club_id]);
                break;

            // ── Club request reject ────────────────────────────
            case 'club_reject':
                $req_id     = (int)($body['req_id'] ?? 0);
                $admin_note = trim($body['admin_note'] ?? '');

                $req = $pdo->prepare("SELECT * FROM club_requests WHERE id=? AND status='pending' LIMIT 1");
                $req->execute([$req_id]);
                $r = $req->fetch(PDO::FETCH_ASSOC);

                if (!$r) {
                    echo json_encode(['success' => false, 'message' => 'Request not found.']); break;
                }

                $pdo->prepare("
                    UPDATE club_requests SET status='rejected', admin_note=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?
                ")->execute([$admin_note, $_SESSION['user_id'], $req_id]);

                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'club_rejected', 'Club Request Update', ?, 'index.php?page=studenthome')
                ")->execute([
                    $r['user_id'],
                    'Your club request for "' . $r['name'] . '" was not approved.'
                        . ($admin_note ? ' Reason: ' . $admin_note : ''),
                ]);

                echo json_encode(['success' => true]);
                break;

            // ── Event approve ──────────────────────────────────
            case 'evt_approve':
                $eventId = (int)($body['id'] ?? 0);
                if (!$eventId) { echo json_encode(['success' => false, 'message' => 'Invalid event ID.']); break; }

                $pdo->beginTransaction();
                $ev = $pdo->prepare("SELECT * FROM events WHERE id=? AND status='pending_approval' LIMIT 1");
                $ev->execute([$eventId]);
                $ev = $ev->fetch(PDO::FETCH_ASSOC);

                if (!$ev) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Event not found or already reviewed.']); break;
                }

                $pdo->prepare("UPDATE events SET status='upcoming' WHERE id=?")->execute([$eventId]);

                // Now notify all active club members
                $members = $pdo->prepare("SELECT user_id FROM members WHERE club_id=? AND status='active'");
                $members->execute([$ev['club_id']]);
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1"); $cnameStmt->execute([$ev['club_id']]); $cname = $cnameStmt->fetchColumn();
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id,type,title,message,link) VALUES (?,'event_new',?,?,'index.php?page=officer_events')");
                foreach ($members->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                    $notifStmt->execute([$uid, 'New Event: ' . $ev['name'], $cname . ' has a new event: ' . $ev['name'] . ' on ' . $ev['event_date']]);
                }

                $pdo->commit();
                echo json_encode(['success' => true]);
                break;

            // ── Event reject ───────────────────────────────────
            case 'evt_reject':
                $eventId    = (int)($body['id'] ?? 0);
                $admin_note = trim($body['admin_note'] ?? '');
                if (!$eventId) { echo json_encode(['success' => false, 'message' => 'Invalid event ID.']); break; }

                $ev = $pdo->prepare("SELECT * FROM events WHERE id=? AND status='pending_approval' LIMIT 1");
                $ev->execute([$eventId]);
                $ev = $ev->fetch(PDO::FETCH_ASSOC);

                if (!$ev) {
                    echo json_encode(['success' => false, 'message' => 'Event not found or already reviewed.']); break;
                }

                $pdo->prepare("UPDATE events SET status='rejected' WHERE id=?")->execute([$eventId]);

                // Notify club officers/president
                $officers = $pdo->prepare("SELECT user_id FROM members WHERE club_id=? AND role IN ('president','vice president','officer','lead') AND status='active'");
                $officers->execute([$ev['club_id']]);
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id,type,title,message,link) VALUES (?,'event_rejected','Event Not Approved',?,'index.php?page=officer_events')");
                foreach ($officers->fetchAll(PDO::FETCH_COLUMN) as $uid) {
                    $notifStmt->execute([$uid, 'Your event "' . $ev['name'] . '" was not approved.' . ($admin_note ? ' Reason: ' . $admin_note : '')]);
                }

                echo json_encode(['success' => true]);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Unknown action.']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ── Page data (GET) ───────────────────────────────────────────
$adminStmt = $pdo->prepare('SELECT first_name, last_name FROM users WHERE id = ?');
$adminStmt->execute([$_SESSION['user_id']]);
$adminRow = $adminStmt->fetch(PDO::FETCH_ASSOC);
$adminFirst   = $adminRow['first_name'] ?? 'Admin';
$adminLast    = $adminRow['last_name']  ?? '';
$adminName    = trim($adminFirst . ' ' . $adminLast);
$adminInitial = strtoupper(substr($adminFirst, 0, 1));

// Build avatar URL from session (set by adminprofile or upload_avatar controllers)
$_sessionPic  = $_SESSION['profile_picture'] ?? '';
$avatar_url   = $_sessionPic
    ? '/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($_sessionPic))
    : '';

// Load all dashboard data from the model
require_once __DIR__ . '/../../app/models/dashboard_model.php';
// Unread notifications count for admin
$adminUnreadNotifs = 0;
try {
    $nStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND is_read=0");
    $nStmt->execute([':uid' => $_SESSION['user_id']]);
    $adminUnreadNotifs = (int) $nStmt->fetchColumn();
} catch (Exception $e) { $adminUnreadNotifs = 0; }