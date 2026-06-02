<?php
ob_start();
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/officer_dashboard_model.php';

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
        // Verify this user is actually an officer of a club
        $memberStmt = $pdo->prepare("
            SELECT m.club_id, m.role FROM members m
            WHERE m.user_id = :uid
              AND m.role IN ('officer','lead','president','vice president')
              AND m.status = 'active'
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

            // ── Announcements ──────────────────────────────
            case 'ann_create':
                $stmt = $pdo->prepare("
                    INSERT INTO announcements (title, description, category, status, club_id, posted_at)
                    VALUES (:title, :desc, :category, :status, :club_id, NOW())
                ");
                $stmt->execute([
                    ':title'    => $body['title']    ?? '',
                    ':desc'     => $body['desc']     ?? '',
                    ':category' => $body['category'] ?? 'General',
                    ':status'   => $body['status']   ?? 'info',
                    ':club_id'  => $clubId,
                ]);
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
                break;

            case 'ann_update':
                $stmt = $pdo->prepare("
                    UPDATE announcements
                    SET title = :title, description = :desc, category = :category, status = :status
                    WHERE id = :id AND club_id = :club_id
                ");
                $stmt->execute([
                    ':id'       => (int)($body['id'] ?? 0),
                    ':title'    => $body['title']    ?? '',
                    ':desc'     => $body['desc']     ?? '',
                    ':category' => $body['category'] ?? 'General',
                    ':status'   => $body['status']   ?? 'info',
                    ':club_id'  => $clubId,
                ]);
                echo json_encode(['success' => true]);
                break;

            case 'ann_delete':
                $pdo->prepare("DELETE FROM announcements WHERE id = :id AND club_id = :club_id")
                    ->execute([':id' => (int)($body['id'] ?? 0), ':club_id' => $clubId]);
                echo json_encode(['success' => true]);
                break;

            // ── Events ────────────────────────────────────
            case 'evt_create':
                $stmt = $pdo->prepare("
                    INSERT INTO events (name, description, event_date, start_time, end_time, location, club_id, status)
                    VALUES (:name, :desc, :date, :start, :end, :location, :club_id, 'pending_approval')
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
                // Notify admins — members will be notified once admin approves
                $cnameStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=? LIMIT 1"); $cnameStmt->execute([$clubId]); $cname = $cnameStmt->fetchColumn();
                $admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id,type,title,message,link) VALUES (?,'event_pending','Event Pending Approval',?,'index.php?page=dashboard')");
                foreach ($admins as $adminId) {
                    $notifStmt->execute([$adminId, $cname . ' submitted a new event for approval: ' . ($body['name'] ?? '') . ' on ' . ($body['date'] ?? '')]);
                }
                echo json_encode(['success' => true, 'id' => $newId]);
                break;

            case 'evt_delete':
                $pdo->prepare("DELETE FROM events WHERE id = :id AND club_id = :club_id")
                    ->execute([':id' => (int)($body['id'] ?? 0), ':club_id' => $clubId]);
                echo json_encode(['success' => true]);
                break;

            // ── Applications ───────────────────────────────
            case 'app_approve':
                $pdo->beginTransaction();
                $appId = (int)($body['id'] ?? 0);

                // GUARD: only approve if still pending AND routed to officer queue
                $appStmt = $pdo->prepare("
                    SELECT * FROM applications
                    WHERE id = :id
                      AND club_id = :cid
                      AND status = 'pending'
                      AND reviewer_type = 'officer'
                    LIMIT 1
                ");
                $appStmt->execute([':id' => $appId, ':cid' => $clubId]);
                $app = $appStmt->fetch(PDO::FETCH_ASSOC);

                if (!$app) {
                    $pdo->rollBack();
                    echo json_encode(['error' => 'Application not found, already reviewed, or requires admin approval.']);
                    break;
                }

                // Mark approved
                $pdo->prepare("
                    UPDATE applications SET status = 'approved', reviewed_at = NOW() WHERE id = ?
                ")->execute([$appId]);

                // Add to members (IGNORE prevents duplicate if somehow called twice)
                $pdo->prepare("
                    INSERT IGNORE INTO members (user_id, club_id, course, year, section, role, status, joined_at)
                    VALUES (?, ?, ?, ?, ?, 'member', 'active', NOW())
                ")->execute([
                    $app['user_id'],
                    $app['club_id'],
                    $app['course']  ?? null,
                    $app['year']    ?? null,
                    $app['section'] ?? null,
                ]);

                // Notify the applicant
                $clubNameRow = $pdo->prepare("SELECT name FROM clubs WHERE id = ? LIMIT 1");
                $clubNameRow->execute([$clubId]);
                $cname = $clubNameRow->fetchColumn();

                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'app_approved', 'Application Approved! 🎉', ?, 'index.php?page=studenthome')
                ")->execute([
                    $app['user_id'],
                    'Congratulations! Your application to join ' . $cname . ' has been approved. Welcome to the club!',
                ]);

                $pdo->commit();
                echo json_encode(['success' => true]);
                break;

            case 'app_reject':
                $appId  = (int)($body['id'] ?? 0);
                $reason = trim($body['reason'] ?? '');

                // GUARD: only reject if still pending AND routed to officer queue
                $appStmt = $pdo->prepare("
                    SELECT * FROM applications
                    WHERE id = :id
                      AND club_id = :cid
                      AND status = 'pending'
                      AND reviewer_type = 'officer'
                    LIMIT 1
                ");
                $appStmt->execute([':id' => $appId, ':cid' => $clubId]);
                $app = $appStmt->fetch(PDO::FETCH_ASSOC);

                if (!$app) {
                    echo json_encode(['error' => 'Application not found, already reviewed, or requires admin approval.']);
                    break;
                }

                $pdo->prepare("
                    UPDATE applications SET status = 'rejected', reviewed_at = NOW() WHERE id = ?
                ")->execute([$appId]);

                $clubNameRow = $pdo->prepare("SELECT name FROM clubs WHERE id = ? LIMIT 1");
                $clubNameRow->execute([$clubId]);
                $cname = $clubNameRow->fetchColumn();

                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (?, 'app_rejected', 'Application Update', ?, 'index.php?page=explore')
                ")->execute([
                    $app['user_id'],
                    'Your application to join ' . $cname . ' was not approved.' . ($reason ? ' Reason: ' . $reason : ''),
                ]);

                echo json_encode(['success' => true]);
                break;

            // ── Club Info ──────────────────────────────────
            case 'club_update':
                $name    = trim($body['name']        ?? '');
                $desc    = trim($body['description'] ?? '');
                $room    = trim($body['room']        ?? '');
                $founded = trim($body['founded']     ?? '');
                if (!$name) { echo json_encode(['error' => 'Club name is required.']); break; }
                $pdo->prepare("UPDATE clubs SET name = ?, description = ?, room = ?, founded = ? WHERE id = ?")
                    ->execute([$name, $desc, $room, $founded, $clubId]);
                echo json_encode(['success' => true]);
                break;

            case 'club_logo':
                if (empty($_FILES['logo']['tmp_name'])) { echo json_encode(['error' => 'No file uploaded.']); break; }
                $file    = $_FILES['logo'];
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file['type'], $allowed)) { echo json_encode(['error' => 'Invalid file type.']); break; }
                if ($file['size'] > 2 * 1024 * 1024)   { echo json_encode(['error' => 'File too large (max 2MB).']); break; }
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fname    = 'club_' . uniqid() . '.' . $ext;
                $dir      = $_SERVER['DOCUMENT_ROOT'] . '/assets/pictures/profile_pictures/clubs/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($file['tmp_name'], $dir . $fname);
                $logoPath = '/assets/pictures/profile_pictures/clubs/' . $fname;
                $pdo->prepare("UPDATE clubs SET logo_path = ? WHERE id = ?")->execute([$logoPath, $clubId]);
                echo json_encode(['success' => true, 'logo' => $logoPath]);
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

// ── Page data ──────────────────────────────────────────────────
$userId             = (int)$_SESSION['user_id'];
$userFirst          = $_SESSION['first_name'] ?? 'Officer';
$userLast           = $_SESSION['last_name']  ?? '';
$userName           = trim($userFirst . ' ' . $userLast);
$userInit           = strtoupper(substr($userFirst, 0, 1));

// Profile picture
$picStmt  = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$picStmt->execute([$userId]);
$picFile  = $picStmt->fetchColumn();
$avatar_url = $picFile
    ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';

$officerClub        = getOfficerClub($pdo, $userId);
if (!$officerClub) { header('Location: index.php?page=home'); exit; }

$clubId             = (int)$officerClub['club_id'];
$officerRole        = $officerClub['role'];
$clubName           = $officerClub['club_name'];
$clubInitial        = strtoupper(substr($clubName, 0, 1));

$totalMembers       = getTotalMembers($pdo, $clubId);
$upcomingEvents     = getUpcomingEventsCount($pdo, $clubId);
$pendingApps        = getPendingAppsCount($pdo, $clubId);
$totalAnnouncements = getTotalAnnouncements($pdo, $clubId);
$unreadNotifs       = getUnreadNotifCount($pdo, $userId);
$unreadChat         = getUnreadChatCount($pdo, $userId, $clubId);
$dbAnnouncements    = getAnnouncements($pdo, $clubId);
$dbEvents           = getUpcomingEvents($pdo, $clubId);
$dbMembers          = getClubMembers($pdo, $clubId);
$dbApplicants       = getPendingApplicants($pdo, $clubId);   // model must filter reviewer_type='officer'

function relativeDate($ts) {
    $diff = time() - strtotime($ts);
    if ($diff < 86400)  return 'Today';
    if ($diff < 172800) return '1d ago';
    return floor($diff / 86400) . 'd ago';
}
function dotFromStatus($s) {
    return match($s) {
        'urgent'   => 'red',
        'approved' => 'green',
        'info'     => 'yellow',
        default    => 'blue',
    };
}