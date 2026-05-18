<?php
// ============================================================
//  UNIFY — Officer Events Controller
//  app/controllers/officer_events_controller.php
// ============================================================

ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
error_reporting(0);
ini_set('display_errors', 0);
require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/config/db.php';

date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login'); exit;
}

$userId = (int) $_SESSION['user_id'];

// ── Shared: verify officer & get club_id ───────────────────
function getOfficerClubId(PDO $pdo, int $userId): ?int {
    $s = $pdo->prepare("
        SELECT club_id FROM members
        WHERE user_id = :uid
          AND role IN ('officer','lead','president','vice president','secretary','treasurer')
          AND status = 'active'
        LIMIT 1
    ");
    $s->execute([':uid' => $userId]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    return $row ? (int) $row['club_id'] : null;
}

// ── AJAX handler ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean();
    header('Content-Type: application/json');

    $action  = trim($_POST['action'] ?? '');
    $clubId  = getOfficerClubId($pdo, $userId);

    if (!$clubId) {
        echo json_encode(['success' => false, 'message' => 'Not an officer']); exit;
    }

    try {
        switch ($action) {

            // ── Create event ────────────────────────────────
            case 'evt_create': {
                $name      = trim($_POST['name']         ?? '');
                $date      = trim($_POST['event_date']   ?? '');
                $location  = trim($_POST['location']     ?? '');
                $start     = trim($_POST['start_time']   ?? '') ?: null;
                $end       = trim($_POST['end_time']     ?? '') ?: null;
                $desc      = trim($_POST['description']  ?? '');
                $status    = trim($_POST['status']       ?? 'upcoming');
                $mandatory = (int) ($_POST['is_mandatory'] ?? 0);

                if (!$name || !$date) {
                    echo json_encode(['success'=>false,'message'=>'Name and date are required']); break;
                }

                $allowed = ['upcoming','ongoing','completed','cancelled','pending_approval'];
                if (!in_array($status, $allowed)) $status = 'upcoming';

                $s = $pdo->prepare("
                    INSERT INTO events (club_id, name, description, location, event_date, start_time, end_time, status, is_mandatory)
                    VALUES (:club_id, :name, :desc, :location, :date, :start, :end, :status, :mandatory)
                ");
                $s->execute([
                    ':club_id'   => $clubId,
                    ':name'      => $name,
                    ':desc'      => $desc,
                    ':location'  => $location,
                    ':date'      => $date,
                    ':start'     => $start,
                    ':end'       => $end,
                    ':status'    => $status,
                    ':mandatory' => $mandatory,
                ]);
                echo json_encode(['success' => true, 'event_id' => $pdo->lastInsertId()]);
                break;
            }

            // ── Update event ────────────────────────────────
            case 'evt_update': {
                $eventId   = (int) ($_POST['event_id']   ?? 0);
                $name      = trim($_POST['name']         ?? '');
                $date      = trim($_POST['event_date']   ?? '');
                $location  = trim($_POST['location']     ?? '');
                $start     = trim($_POST['start_time']   ?? '') ?: null;
                $end       = trim($_POST['end_time']     ?? '') ?: null;
                $desc      = trim($_POST['description']  ?? '');
                $status    = trim($_POST['status']       ?? 'upcoming');
                $mandatory = (int) ($_POST['is_mandatory'] ?? 0);

                if (!$eventId || !$name || !$date) {
                    echo json_encode(['success'=>false,'message'=>'Missing required fields']); break;
                }

                $allowed = ['upcoming','ongoing','completed','cancelled','pending_approval'];
                if (!in_array($status, $allowed)) $status = 'upcoming';

                $s = $pdo->prepare("
                    UPDATE events
                    SET name=:name, description=:desc, location=:location,
                        event_date=:date, start_time=:start, end_time=:end,
                        status=:status, is_mandatory=:mandatory
                    WHERE id=:id AND club_id=:club_id
                ");
                $s->execute([
                    ':name'      => $name,
                    ':desc'      => $desc,
                    ':location'  => $location,
                    ':date'      => $date,
                    ':start'     => $start,
                    ':end'       => $end,
                    ':status'    => $status,
                    ':mandatory' => $mandatory,
                    ':id'        => $eventId,
                    ':club_id'   => $clubId,
                ]);
                echo json_encode(['success' => true]);
                break;
            }

            // ── Toggle mandatory ────────────────────────────
            case 'evt_toggle_mandatory': {
                $eventId = (int) ($_POST['event_id'] ?? 0);
                if (!$eventId) { echo json_encode(['success'=>false,'message'=>'No event_id']); break; }

                $pdo->prepare("
                    UPDATE events SET is_mandatory = 1 - is_mandatory
                    WHERE id = :id AND club_id = :club_id
                ")->execute([':id' => $eventId, ':club_id' => $clubId]);

                echo json_encode(['success' => true]);
                break;
            }

            // ── Delete event ────────────────────────────────
            case 'evt_delete': {
                $eventId = (int) ($_POST['event_id'] ?? 0);
                if (!$eventId) { echo json_encode(['success'=>false,'message'=>'No event_id']); break; }

                $pdo->prepare("DELETE FROM events WHERE id = :id AND club_id = :club_id")
                    ->execute([':id' => $eventId, ':club_id' => $clubId]);

                echo json_encode(['success' => true]);
                break;
            }

            // ── Scan QR / LRN ───────────────────────────────
            case 'att_scan': {
                $eventId = (int) ($_POST['event_id'] ?? 0);
                $lrn     = trim($_POST['lrn'] ?? '');

                if (!$eventId || !$lrn) {
                    echo json_encode(['success'=>false,'message'=>'Missing event_id or lrn']); break;
                }

                // Verify event belongs to this club
                $ev = $pdo->prepare("SELECT id FROM events WHERE id=:id AND club_id=:cid LIMIT 1");
                $ev->execute([':id' => $eventId, ':cid' => $clubId]);
                if (!$ev->fetch()) {
                    echo json_encode(['success'=>false,'message'=>'Event not found']); break;
                }

                // Find active club member by LRN (student_id)
                $ms = $pdo->prepare("
                    SELECT u.id AS user_id, u.first_name, u.last_name
                    FROM student_profiles sp
                    JOIN users u ON u.id = sp.user_id
                    JOIN members m ON m.user_id = u.id
                        AND m.club_id = :cid AND m.status = 'active'
                    WHERE sp.student_id = :lrn
                    LIMIT 1
                ");
                $ms->execute([':cid' => $clubId, ':lrn' => $lrn]);
                $member = $ms->fetch(PDO::FETCH_ASSOC);

                if (!$member) {
                    echo json_encode(['success'=>false,'message'=>'Member not found or not active in this club']); break;
                }

                // Insert attendance — ignore duplicate
                $ins = $pdo->prepare("
                    INSERT IGNORE INTO attendance (event_id, user_id, scanned_by)
                    VALUES (:event_id, :user_id, :scanned_by)
                ");
                $ins->execute([
                    ':event_id'   => $eventId,
                    ':user_id'    => (int) $member['user_id'],
                    ':scanned_by' => $userId,
                ]);

                echo json_encode([
                    'success' => true,
                    'name'    => trim($member['first_name'] . ' ' . $member['last_name']),
                ]);
                break;
            }

            // ── Attendance list ─────────────────────────────
            case 'att_list': {
                $eventId = (int) ($_POST['event_id'] ?? 0);
                if (!$eventId) { echo json_encode(['success'=>false,'message'=>'No event_id']); break; }

                // Verify event belongs to club
                $ev = $pdo->prepare("SELECT id FROM events WHERE id=:id AND club_id=:cid LIMIT 1");
                $ev->execute([':id' => $eventId, ':cid' => $clubId]);
                if (!$ev->fetch()) {
                    echo json_encode(['success'=>false,'message'=>'Event not found']); break;
                }

                // All active members
                $ms = $pdo->prepare("
                    SELECT u.id AS user_id, u.first_name, u.last_name,
                           COALESCE(sp.student_id, '') AS student_id
                    FROM members m
                    JOIN users u ON u.id = m.user_id
                    LEFT JOIN student_profiles sp ON sp.user_id = u.id
                    WHERE m.club_id = :cid AND m.status = 'active'
                    ORDER BY u.last_name, u.first_name
                ");
                $ms->execute([':cid' => $clubId]);
                $allMembers = $ms->fetchAll(PDO::FETCH_ASSOC);

                // Scanned IDs
                $sc = $pdo->prepare("SELECT user_id FROM attendance WHERE event_id = :eid");
                $sc->execute([':eid' => $eventId]);
                $scannedIds = array_map('intval', $sc->fetchAll(PDO::FETCH_COLUMN));

                $present = [];
                $absent  = [];
                foreach ($allMembers as $m) {
                    if (in_array((int)$m['user_id'], $scannedIds)) {
                        $present[] = $m;
                    } else {
                        $absent[] = $m;
                    }
                }

                echo json_encode(['success' => true, 'present' => $present, 'absent' => $absent]);
                break;
            }

            // ── Save assignees for an event ──────────────────
            case 'assignees_save': {
                $eventId    = (int) ($_POST['event_id'] ?? 0);
                $assigneesRaw = trim($_POST['assignees'] ?? '');

                if (!$eventId) { echo json_encode(['success'=>false,'message'=>'No event_id']); break; }

                // Verify event belongs to this club
                $ev = $pdo->prepare("SELECT id FROM events WHERE id=:id AND club_id=:cid LIMIT 1");
                $ev->execute([':id' => $eventId, ':cid' => $clubId]);
                if (!$ev->fetch()) { echo json_encode(['success'=>false,'message'=>'Event not found']); break; }

                $assignees = json_decode($assigneesRaw, true);
                if (!is_array($assignees)) $assignees = [];

                // Wipe existing assignees for this event
                $pdo->prepare("DELETE FROM event_assignees WHERE event_id = :eid")
                    ->execute([':eid' => $eventId]);

                // Re-insert
                // INSERT with status=pending (upsert on duplicate to reset to pending if re-assigned)
                $ins = $pdo->prepare("
                    INSERT INTO event_assignees (event_id, user_id, role_label, assigned_by, status)
                    VALUES (:eid, :uid, :role, :by, 'pending')
                    ON DUPLICATE KEY UPDATE role_label=:role2, assigned_by=:by2, status='pending'
                ");
                // Get event name and club name once for notifications
                $evInfo = $pdo->prepare("SELECT name FROM events WHERE id=:eid LIMIT 1");
                $evInfo->execute([':eid' => $eventId]);
                $evName = $evInfo->fetchColumn();
                $cnStmt = $pdo->prepare("SELECT name FROM clubs WHERE id=:cid LIMIT 1");
                $cnStmt->execute([':cid' => $clubId]);
                $cnName = $cnStmt->fetchColumn();

                $notifIns = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (:uid, 'event_assigned', :title, :msg, 'index.php?page=studentevents&tab=assignments')
                ");

                foreach ($assignees as $a) {
                    $uid  = (int) ($a['user_id'] ?? 0);
                    $role = substr(trim($a['role_label'] ?? ''), 0, 120);
                    if (!$uid) continue;
                    // Verify member belongs to this club
                    $chk = $pdo->prepare("SELECT id FROM members WHERE user_id=:uid AND club_id=:cid AND status='active' LIMIT 1");
                    $chk->execute([':uid' => $uid, ':cid' => $clubId]);
                    if (!$chk->fetch()) continue;
                    $ins->execute([':eid' => $eventId, ':uid' => $uid, ':role' => $role, ':by' => $userId, ':role2' => $role, ':by2' => $userId]);
                    // Notify the assigned member — they must approve/decline on student events page
                    $notifTitle = '📋 You\'ve been assigned to an event!';
                    $notifMsg   = 'You have been assigned as ' . ($role ?: 'a volunteer') . ' for "' . $evName . '" by ' . $cnName . '. Please accept or decline on the Events page.';
                    $notifIns->execute([':uid' => $uid, ':title' => $notifTitle, ':msg' => $notifMsg]);
                }

                // Return saved list with names + status
                $saved = $pdo->prepare("
                    SELECT ea.user_id, ea.role_label, ea.status,
                           u.first_name, u.last_name,
                           u.profile_picture
                    FROM event_assignees ea
                    JOIN users u ON u.id = ea.user_id
                    WHERE ea.event_id = :eid
                    ORDER BY u.last_name, u.first_name
                ");
                $saved->execute([':eid' => $eventId]);

                echo json_encode(['success' => true, 'assignees' => $saved->fetchAll(PDO::FETCH_ASSOC)]);
                break;
            }

            // ── Get assignees for an event ───────────────────
            case 'assignees_get': {
                $eventId = (int) ($_POST['event_id'] ?? 0);
                if (!$eventId) { echo json_encode(['success'=>false,'message'=>'No event_id']); break; }

                $stmt = $pdo->prepare("
                    SELECT ea.user_id, ea.role_label, ea.status,
                           u.first_name, u.last_name,
                           u.profile_picture
                    FROM event_assignees ea
                    JOIN users u ON u.id = ea.user_id
                    WHERE ea.event_id = :eid
                    ORDER BY u.last_name, u.first_name
                ");
                $stmt->execute([':eid' => $eventId]);
                echo json_encode(['success' => true, 'assignees' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                break;
            }

            // ── Get other clubs for collaboration ───────────────
            case 'collab_clubs_list': {
                $stmt = $pdo->prepare("
                    SELECT id, name, logo_path
                    FROM clubs
                    WHERE status = 'active' AND id != :cid
                    ORDER BY name
                ");
                $stmt->execute([':cid' => $clubId]);
                echo json_encode(['success' => true, 'clubs' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                break;
            }

            // ── Send a collaboration request ─────────────────────
            case 'collab_request': {
                $eventId    = (int) ($_POST['event_id']    ?? 0);
                $targetClub = (int) ($_POST['target_club'] ?? 0);
                $message    = trim($_POST['message']       ?? '');

                if (!$eventId || !$targetClub) {
                    echo json_encode(['success'=>false,'message'=>'Missing event_id or target_club']); break;
                }
                // Verify event belongs to this club
                $ev = $pdo->prepare("SELECT id, name FROM events WHERE id=:id AND club_id=:cid LIMIT 1");
                $ev->execute([':id'=>$eventId,':cid'=>$clubId]);
                $evRow = $ev->fetch(PDO::FETCH_ASSOC);
                if (!$evRow) { echo json_encode(['success'=>false,'message'=>'Event not found']); break; }

                // Upsert collab request
                $ins = $pdo->prepare("
                    INSERT INTO event_collab_requests (event_id, requesting_club, target_club, message, status)
                    VALUES (:eid, :req, :tgt, :msg, 'pending')
                    ON DUPLICATE KEY UPDATE message=:msg2, status='pending', updated_at=NOW()
                ");
                $ins->execute([':eid'=>$eventId,':req'=>$clubId,':tgt'=>$targetClub,':msg'=>$message,':msg2'=>$message]);
                $collabId = $pdo->lastInsertId() ?: null;
                if (!$collabId) {
                    $sel = $pdo->prepare("SELECT id FROM event_collab_requests WHERE event_id=:eid AND requesting_club=:req AND target_club=:tgt LIMIT 1");
                    $sel->execute([':eid'=>$eventId,':req'=>$clubId,':tgt'=>$targetClub]);
                    $collabId = (int)$sel->fetchColumn();
                }

                // Notify all officers of the target club
                $tgClubName = $pdo->prepare("SELECT name FROM clubs WHERE id=:id LIMIT 1");
                $tgClubName->execute([':id'=>$targetClub]);
                $targetName = $tgClubName->fetchColumn();

                $reqClubName = $pdo->prepare("SELECT name FROM clubs WHERE id=:id LIMIT 1");
                $reqClubName->execute([':id'=>$clubId]);
                $reqName = $reqClubName->fetchColumn();

                $officersStmt = $pdo->prepare("
                    SELECT user_id FROM members
                    WHERE club_id=:cid AND status='active'
                      AND role IN ('officer','lead','president','vice president','secretary','treasurer')
                ");
                $officersStmt->execute([':cid'=>$targetClub]);
                $officers = $officersStmt->fetchAll(PDO::FETCH_COLUMN);

                $notifIns = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (:uid, 'collab_request', :title, :msg, 'index.php?page=officer_events&tab=collabs')
                ");
                foreach ($officers as $ouid) {
                    $notifIns->execute([
                        ':uid'   => $ouid,
                        ':title' => '🤝 Club Collaboration Request',
                        ':msg'   => $reqName . ' is requesting collaboration for the event "' . $evRow['name'] . '". Please review and respond.',
                    ]);
                }

                echo json_encode(['success'=>true,'collab_id'=>$collabId]);
                break;
            }

            // ── Get collabs for an event (as requesting club) ────
            case 'collab_get': {
                $eventId = (int)($_POST['event_id'] ?? 0);
                if (!$eventId) { echo json_encode(['success'=>false,'message'=>'No event_id']); break; }

                $stmt = $pdo->prepare("
                    SELECT ecr.id, ecr.target_club, ecr.status, ecr.message, ecr.response_message, ecr.updated_at,
                           c.name AS target_club_name, c.logo_path AS target_club_logo
                    FROM event_collab_requests ecr
                    JOIN clubs c ON c.id = ecr.target_club
                    WHERE ecr.event_id=:eid AND ecr.requesting_club=:cid
                    ORDER BY ecr.created_at DESC
                ");
                $stmt->execute([':eid'=>$eventId,':cid'=>$clubId]);
                $collabs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Attach members for accepted collabs
                foreach ($collabs as &$col) {
                    if ($col['status'] === 'accepted') {
                        $ms = $pdo->prepare("
                            SELECT ecm.user_id, ecm.role_label, u.first_name, u.last_name, u.profile_picture
                            FROM event_collab_members ecm
                            JOIN users u ON u.id = ecm.user_id
                            WHERE ecm.collab_id=:cid
                        ");
                        $ms->execute([':cid'=>$col['id']]);
                        $col['members'] = $ms->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $col['members'] = [];
                    }
                }
                unset($col);

                echo json_encode(['success'=>true,'collabs'=>$collabs]);
                break;
            }

            // ── Get incoming collab requests (as target club) ────
            case 'collab_incoming': {
                $stmt = $pdo->prepare("
                    SELECT ecr.id, ecr.event_id, ecr.requesting_club, ecr.status, ecr.message, ecr.created_at,
                           c.name AS requesting_club_name, c.logo_path AS requesting_club_logo,
                           e.name AS event_name, e.event_date, e.location, e.start_time, e.end_time,
                           ec.name AS event_club_name
                    FROM event_collab_requests ecr
                    JOIN clubs c ON c.id = ecr.requesting_club
                    JOIN events e ON e.id = ecr.event_id
                    JOIN clubs ec ON ec.id = e.club_id
                    WHERE ecr.target_club=:cid
                    ORDER BY ecr.created_at DESC
                ");
                $stmt->execute([':cid'=>$clubId]);
                $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($requests as &$req) {
                    // Attach target club members (for already-accepted)
                    $ms = $pdo->prepare("
                        SELECT ecm.user_id, ecm.role_label, u.first_name, u.last_name, u.profile_picture
                        FROM event_collab_members ecm
                        JOIN users u ON u.id = ecm.user_id
                        WHERE ecm.collab_id=:cid
                    ");
                    $ms->execute([':cid'=>$req['id']]);
                    $req['chosen_members'] = $ms->fetchAll(PDO::FETCH_ASSOC);
                }
                unset($req);

                echo json_encode(['success'=>true,'requests'=>$requests]);
                break;
            }

            // ── Respond to a collab request (accept/reject) ──────
            case 'collab_respond': {
                $collabId       = (int) ($_POST['collab_id']        ?? 0);
                $response       = trim($_POST['response']           ?? ''); // 'accepted' | 'rejected'
                $responseMsg    = trim($_POST['response_message']   ?? '');
                $chosenMembers  = json_decode($_POST['chosen_members'] ?? '[]', true);

                if (!$collabId || !in_array($response, ['accepted','rejected'])) {
                    echo json_encode(['success'=>false,'message'=>'Invalid request']); break;
                }

                // Verify this request targets our club
                $chk = $pdo->prepare("SELECT * FROM event_collab_requests WHERE id=:id AND target_club=:cid LIMIT 1");
                $chk->execute([':id'=>$collabId,':cid'=>$clubId]);
                $collab = $chk->fetch(PDO::FETCH_ASSOC);
                if (!$collab) { echo json_encode(['success'=>false,'message'=>'Not found']); break; }

                // Update status
                $upd = $pdo->prepare("
                    UPDATE event_collab_requests
                    SET status=:status, response_message=:rmsg, updated_at=NOW()
                    WHERE id=:id
                ");
                $upd->execute([':status'=>$response,':rmsg'=>$responseMsg,':id'=>$collabId]);

                if ($response === 'accepted' && !empty($chosenMembers)) {
                    // Clear old and re-insert chosen members
                    $pdo->prepare("DELETE FROM event_collab_members WHERE collab_id=:cid")->execute([':cid'=>$collabId]);
                    $mIns = $pdo->prepare("
                        INSERT IGNORE INTO event_collab_members (collab_id, user_id, role_label)
                        VALUES (:cid, :uid, :role)
                    ");
                    foreach ($chosenMembers as $cm) {
                        $uid  = (int)($cm['user_id'] ?? 0);
                        $role = substr(trim($cm['role_label'] ?? ''), 0, 120);
                        if (!$uid) continue;
                        // Verify member belongs to our club
                        $chkM = $pdo->prepare("SELECT id FROM members WHERE user_id=:uid AND club_id=:cid AND status='active' LIMIT 1");
                        $chkM->execute([':uid'=>$uid,':cid'=>$clubId]);
                        if (!$chkM->fetch()) continue;
                        $mIns->execute([':cid'=>$collabId,':uid'=>$uid,':role'=>$role]);
                    }
                }

                // Build member names list for notification
                $memberNames = '';
                if ($response === 'accepted' && !empty($chosenMembers)) {
                    $mNamesStmt = $pdo->prepare("
                        SELECT CONCAT(u.first_name,' ',u.last_name) AS full_name
                        FROM event_collab_members ecm
                        JOIN users u ON u.id=ecm.user_id
                        WHERE ecm.collab_id=:cid
                    ");
                    $mNamesStmt->execute([':cid'=>$collabId]);
                    $names = $mNamesStmt->fetchAll(PDO::FETCH_COLUMN);
                    $memberNames = implode(', ', $names);
                }

                // Get event + club info for notification
                $evStmt = $pdo->prepare("SELECT e.name AS ename, c.name AS cname FROM events e JOIN clubs c ON c.id=e.club_id WHERE e.id=:eid LIMIT 1");
                $evStmt->execute([':eid'=>$collab['event_id']]);
                $evInfo = $evStmt->fetch(PDO::FETCH_ASSOC);

                $tgName = $pdo->prepare("SELECT name FROM clubs WHERE id=:id LIMIT 1");
                $tgName->execute([':id'=>$clubId]);
                $targetClubName = $tgName->fetchColumn();

                // Notify officers of the requesting club
                $reqOfficers = $pdo->prepare("
                    SELECT user_id FROM members
                    WHERE club_id=:cid AND status='active'
                      AND role IN ('officer','lead','president','vice president','secretary','treasurer')
                ");
                $reqOfficers->execute([':cid'=>$collab['requesting_club']]);
                $notifIns2 = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, link)
                    VALUES (:uid, 'collab_response', :title, :msg, 'index.php?page=officer_events')
                ");
                foreach ($reqOfficers->fetchAll(PDO::FETCH_COLUMN) as $ouid) {
                    if ($response === 'accepted') {
                        $msg  = $targetClubName . ' has ACCEPTED your collaboration request for "' . ($evInfo['ename'] ?? '') . '".';
                        if ($memberNames) $msg .= ' Assigned members: ' . $memberNames . '.';
                        if ($responseMsg) $msg .= ' Message: ' . $responseMsg;
                        $title = '✅ Collaboration Accepted!';
                    } else {
                        $msg   = $targetClubName . ' has DECLINED your collaboration request for "' . ($evInfo['ename'] ?? '') . '".';
                        if ($responseMsg) $msg .= ' Reason: ' . $responseMsg;
                        $title = '❌ Collaboration Declined';
                    }
                    $notifIns2->execute([':uid'=>$ouid,':title'=>$title,':msg'=>$msg]);
                }

                echo json_encode(['success'=>true]);
                break;
            }

            // ── Event overview (full detail view) ───────────────
            case 'event_overview': {
                $eventId = (int)($_POST['event_id'] ?? 0);
                if (!$eventId) { echo json_encode(['success'=>false,'message'=>'No event_id']); break; }

                // Get event (must belong to this club OR this club is a collaborator)
                $evq = $pdo->prepare("
                    SELECT e.*, c.name AS club_name, c.logo_path AS club_logo
                    FROM events e JOIN clubs c ON c.id=e.club_id
                    WHERE e.id=:eid AND (
                        e.club_id=:cid
                        OR EXISTS(SELECT 1 FROM event_collab_requests WHERE event_id=e.id AND target_club=:cid2 AND status='accepted')
                    )
                    LIMIT 1
                ");
                $evq->execute([':eid'=>$eventId,':cid'=>$clubId,':cid2'=>$clubId]);
                $event = $evq->fetch(PDO::FETCH_ASSOC);
                if (!$event) { echo json_encode(['success'=>false,'message'=>'Event not found']); break; }

                // Assignees
                $asq = $pdo->prepare("
                    SELECT ea.user_id, ea.role_label, ea.status,
                           u.first_name, u.last_name, u.profile_picture,
                           m.role AS club_role
                    FROM event_assignees ea
                    JOIN users u ON u.id=ea.user_id
                    LEFT JOIN members m ON m.user_id=ea.user_id AND m.club_id=:cid
                    WHERE ea.event_id=:eid
                    ORDER BY u.last_name, u.first_name
                ");
                $asq->execute([':eid'=>$eventId,':cid'=>$event['club_id']]);
                $event['assignees'] = $asq->fetchAll(PDO::FETCH_ASSOC);

                // Collaborating clubs
                $colq = $pdo->prepare("
                    SELECT ecr.id AS collab_id, ecr.status, ecr.message, ecr.response_message,
                           c.id AS club_id, c.name AS club_name, c.logo_path AS club_logo
                    FROM event_collab_requests ecr
                    JOIN clubs c ON c.id=ecr.target_club
                    WHERE ecr.event_id=:eid
                    ORDER BY ecr.created_at
                ");
                $colq->execute([':eid'=>$eventId]);
                $collaborations = $colq->fetchAll(PDO::FETCH_ASSOC);

                foreach ($collaborations as &$col) {
                    $ms = $pdo->prepare("
                        SELECT ecm.user_id, ecm.role_label, u.first_name, u.last_name, u.profile_picture
                        FROM event_collab_members ecm
                        JOIN users u ON u.id=ecm.user_id
                        WHERE ecm.collab_id=:cid
                    ");
                    $ms->execute([':cid'=>$col['collab_id']]);
                    $col['members'] = $ms->fetchAll(PDO::FETCH_ASSOC);
                }
                unset($col);
                $event['collaborations'] = $collaborations;

                // Attendance summary
                $attq = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE event_id=:eid");
                $attq->execute([':eid'=>$eventId]);
                $event['attendance_count'] = (int)$attq->fetchColumn();

                echo json_encode(['success'=>true,'event'=>$event]);
                break;
            }

            // ── Approve / Reject pending event ──────────────
            case 'evt_approve': {
                $eventId  = (int) ($_POST['event_id'] ?? 0);
                $decision = trim($_POST['decision'] ?? ''); // 'upcoming' or 'cancelled'
                if (!$eventId || !in_array($decision, ['upcoming','cancelled'])) {
                    echo json_encode(['success'=>false,'message'=>'Invalid request']); break;
                }
                // Only president/officer of this club can approve
                $roleCheck = $pdo->prepare("SELECT role FROM members WHERE user_id=:uid AND club_id=:cid AND status='active' LIMIT 1");
                $roleCheck->execute([':uid'=>$userId,':cid'=>$clubId]);
                $myRole = $roleCheck->fetchColumn();
                if (!in_array($myRole, ['president','vice president','officer','lead'])) {
                    echo json_encode(['success'=>false,'message'=>'Not authorized']); break;
                }
                $pdo->prepare("UPDATE events SET status=:status WHERE id=:id AND club_id=:cid AND status='pending_approval'")
                    ->execute([':status'=>$decision,':id'=>$eventId,':cid'=>$clubId]);
                // Notify all club officers
                $evName = $pdo->prepare("SELECT name FROM events WHERE id=:id LIMIT 1");
                $evName->execute([':id'=>$eventId]);
                $eName = $evName->fetchColumn();
                $allOfficers = $pdo->prepare("SELECT user_id FROM members WHERE club_id=:cid AND status='active' AND user_id != :me");
                $allOfficers->execute([':cid'=>$clubId,':me'=>$userId]);
                $notifIns = $pdo->prepare("INSERT INTO notifications (user_id,type,title,message,link) VALUES (:uid,'event_approved',:title,:msg,'index.php?page=officer_events')");
                $title = $decision === 'upcoming' ? '✅ Event Approved' : '❌ Event Rejected';
                $msg   = $decision === 'upcoming'
                    ? '"'.$eName.'" has been approved and is now listed as upcoming.'
                    : '"'.$eName.'" was rejected and will not proceed.';
                foreach ($allOfficers->fetchAll(PDO::FETCH_COLUMN) as $ouid) {
                    $notifIns->execute([':uid'=>$ouid,':title'=>$title,':msg'=>$msg]);
                }
                echo json_encode(['success'=>true]);
                break;
            }

            default:
                echo json_encode(['success' => false, 'message' => 'Unknown action']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── GET: Notification handlers ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    ob_clean();
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'notif_list') {
        $clubId = getOfficerClubId($pdo, $userId);
        $stmt = $pdo->prepare("
            SELECT id, type, title, message, link, is_read,
                   DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') AS created_fmt
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC LIMIT 20
        ");
        $stmt->execute([$userId]);
        $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
        $unreadStmt->execute([$userId]);
        $unread = (int)$unreadStmt->fetchColumn();
        echo json_encode(['success' => true, 'data' => $notifs, 'unread' => $unread]);
        exit;
    }
    if ($action === 'notif_read') {
        $nid = (int)($_GET['id'] ?? 0);
        $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$nid, $userId]);
        echo json_encode(['success' => true]); exit;
    }
    if ($action === 'notif_read_all') {
        $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$userId]);
        echo json_encode(['success' => true]); exit;
    }
    exit;
}

// ── POST: Mark notif read (also accepts via POST for JS fetch) ─
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'notif_read') {
    ob_clean();
    header('Content-Type: application/json');
    $nid = (int)($_GET['id'] ?? 0);
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$nid, $userId]);
    echo json_encode(['success' => true]); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'notif_read_all') {
    ob_clean();
    header('Content-Type: application/json');
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$userId]);
    echo json_encode(['success' => true]); exit;
}

// ── Page data ──────────────────────────────────────────────
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




$ocStmt = $pdo->prepare("
    SELECT m.club_id, m.role, c.name AS club_name, c.logo_path
    FROM members m
    JOIN clubs c ON c.id = m.club_id
    WHERE m.user_id = :uid
      AND m.role IN ('officer','lead','president','vice president','secretary','treasurer')
      AND m.status = 'active'
    LIMIT 1
");
$ocStmt->execute([':uid' => $userId]);
$officerClub = $ocStmt->fetch(PDO::FETCH_ASSOC);
if (!$officerClub) { header('Location: index.php?page=studenthome'); exit; }

$clubId      = (int) $officerClub['club_id'];
// Auto-update event statuses on every page load
date_default_timezone_set('Asia/Manila');
$pdo->exec("SET time_zone = '+08:00'");
$now  = date('Y-m-d H:i:s');
$date = date('Y-m-d');
$time = date('H:i:s');

// upcoming → ongoing
$pdo->prepare("
    UPDATE events
    SET status = 'ongoing'
    WHERE club_id = :cid
      AND status = 'upcoming'
      AND event_date = :date
      AND start_time IS NOT NULL
      AND start_time <= :time1
      AND (end_time IS NULL OR end_time > :time2)
")->execute([':cid' => $clubId, ':date' => $date, ':time1' => $time, ':time2' => $time]);

// also catch events with no start_time set — mark ongoing if date is today
$pdo->prepare("
    UPDATE events
    SET status = 'ongoing'
    WHERE club_id = :cid
      AND status = 'upcoming'
      AND event_date = :date
      AND start_time IS NULL
      AND (end_time IS NULL OR end_time > :time)
")->execute([':cid' => $clubId, ':date' => $date, ':time' => $time]);
// ongoing → completed
$pdo->prepare("
    UPDATE events
    SET status = 'completed'
    WHERE club_id = :cid
      AND status = 'ongoing'
      AND (
        event_date < :date1
        OR (event_date = :date2 AND end_time IS NOT NULL AND end_time <= :time)
      )
")->execute([':cid' => $clubId, ':date1' => $date, ':date2' => $date, ':time' => $time]);
// upcoming → completed (date fully passed)
$pdo->prepare("
    UPDATE events
    SET status = 'completed'
    WHERE club_id = :cid
      AND status = 'upcoming'
      AND event_date < :date
")->execute([':cid' => $clubId, ':date' => $date]);

// upcoming → completed (same day but end_time already passed)
$pdo->prepare("
    UPDATE events
    SET status = 'completed'
    WHERE club_id = :cid
      AND status = 'upcoming'
      AND event_date = :date
      AND end_time IS NOT NULL
      AND end_time <= :time
")->execute([':cid' => $clubId, ':date' => $date, ':time' => $time]);

// upcoming → ongoing (today, started, not yet ended)
$pdo->prepare("
    UPDATE events
    SET status = 'ongoing'
    WHERE club_id = :cid
      AND status = 'upcoming'
      AND event_date = :date
      AND start_time <= :time1
      AND (end_time IS NULL OR end_time > :time2)
")->execute([':cid' => $clubId, ':date' => $date, ':time1' => $time, ':time2' => $time]);
$clubName    = $officerClub['club_name'];
$officerRole = $officerClub['role'];
$clubInitial = strtoupper(substr($clubName, 0, 1));

// Build abbreviation from words
$_words    = preg_split('/\s+/', $clubName);
$clubAbbr  = implode('', array_map(fn($w) => strtoupper(substr($w,0,1)), array_slice($_words,0,4)));
if (!$clubAbbr) $clubAbbr = strtoupper(substr($clubName,0,3));

// Fetch all events for this club
$evStmt = $pdo->prepare("
    SELECT id, name AS title, description, location AS venue,
           event_date, start_time, end_time, status, is_mandatory
    FROM events
    WHERE club_id = :cid
    ORDER BY event_date DESC, start_time DESC
");
$evStmt->execute([':cid' => $clubId]);
$allEvents = $evStmt->fetchAll(PDO::FETCH_ASSOC);

// Active member count
$mcStmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE club_id=:cid AND status='active'");
$mcStmt->execute([':cid' => $clubId]);
$memberCount = (int) $mcStmt->fetchColumn();

// Attendance counts for completed events
$attCounts = [];
$acStmt = $pdo->prepare("
    SELECT event_id, COUNT(*) AS cnt
    FROM attendance
    WHERE event_id IN (SELECT id FROM events WHERE club_id=:cid AND status='completed')
    GROUP BY event_id
");
$acStmt->execute([':cid' => $clubId]);
foreach ($acStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $attCounts[(int)$row['event_id']] = (int)$row['cnt'];
}

// Partition events
$upcomingOngoing = [];
$completedEvents = [];
foreach ($allEvents as $e) {
    if ($e['status'] === 'completed') {
        $e['att_count'] = $attCounts[(int)$e['id']] ?? 0;
        $completedEvents[] = $e;
    } else {
        $upcomingOngoing[] = $e;
    }
}

// ── Summary counts ────────────────────────────────────────
$totalEvents    = count($allEvents);
$upcomingCount  = count(array_filter($allEvents, fn($e) => $e['status'] === 'upcoming'));
$ongoingCount   = count(array_filter($allEvents, fn($e) => $e['status'] === 'ongoing'));
$completedCount = count(array_filter($allEvents, fn($e) => $e['status'] === 'completed'));
$cancelledCount = count(array_filter($allEvents, fn($e) => $e['status'] === 'cancelled'));
$pendingCount   = count(array_filter($allEvents, fn($e) => $e['status'] === 'pending_approval'));

// Total attendance across all completed events
$taStmt = $pdo->prepare("
    SELECT COUNT(*) FROM attendance a
    JOIN events e ON e.id = a.event_id
    WHERE e.club_id = :cid AND e.status = 'completed'
");
$taStmt->execute([':cid' => $clubId]);
$totalAttendance = (int) $taStmt->fetchColumn();

// Members list for Members tab
$mlStmt = $pdo->prepare("
    SELECT u.id AS user_id, u.first_name, u.last_name, u.email, u.profile_picture,
           m.role, m.joined_at,
           sp.course, sp.year_level AS year, sp.section
    FROM members m
    JOIN users u ON u.id = m.user_id
    LEFT JOIN student_profiles sp ON sp.user_id = u.id
    WHERE m.club_id = :cid AND m.status = 'active'
    ORDER BY m.role, u.last_name, u.first_name
");
$mlStmt->execute([':cid' => $clubId]);
$membersList = $mlStmt->fetchAll(PDO::FETCH_ASSOC);

// Unread notifications count
$unreadNotifs = 0;
try {
    $nStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND is_read=0");
    $nStmt->execute([':uid' => $userId]);
    $unreadNotifs = (int) $nStmt->fetchColumn();
} catch (Exception $e) { $unreadNotifs = 0; }

$unreadChat = 0;
try {
    $chatStmt = $pdo->prepare("SELECT COUNT(*) FROM club_messages WHERE club_id=? AND sender_id!=? AND is_deleted=0 AND sent_at > COALESCE((SELECT last_read FROM club_message_reads WHERE user_id=? AND club_id=?), '2000-01-01')");
    $chatStmt->execute([$clubId, $userId, $userId, $clubId]);
    $unreadChat = (int)$chatStmt->fetchColumn();
} catch (Exception $e) { $unreadChat = 0; }

// Assignees per event
$assigneeMap = [];
if (!empty($allEvents)) {
    $eventIds = array_column($allEvents, 'id');
    $phA = implode(',', array_fill(0, count($eventIds), '?'));
    $asStmt = $pdo->prepare("
        SELECT ea.event_id, ea.user_id, ea.role_label,
               u.first_name, u.last_name, u.profile_picture
        FROM event_assignees ea
        JOIN users u ON u.id = ea.user_id
        WHERE ea.event_id IN ($phA)
        ORDER BY u.last_name, u.first_name
    ");
    $asStmt->execute($eventIds);
    foreach ($asStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $assigneeMap[(int)$row['event_id']][] = $row;
    }
}

$jsEvents = json_encode(array_map(fn($e) => [
    'db_id'        => (int)$e['id'],
    'title'        => $e['title'] ?? '',
    'venue'        => $e['venue'] ?? '',
    'event_date'   => $e['event_date'] ?? '',
    'start_time'   => $e['start_time'] ?? null,
    'end_time'     => $e['end_time'] ?? null,
    'status'       => $e['status'] ?? '',
    'is_mandatory' => (bool)($e['is_mandatory'] ?? false),
    'description'  => $e['description'] ?? '',
    'assignees'    => $assigneeMap[(int)$e['id']] ?? [],
], $allEvents), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
if ($jsEvents === false) $jsEvents = '[]';

$jsMembers = json_encode(array_map(fn($m) => [
    'user_id'         => (int)$m['user_id'],
    'first_name'      => $m['first_name'],
    'last_name'       => $m['last_name'],
    'role'            => $m['role'],
    'profile_picture' => $m['profile_picture'] ?? null,
], $membersList), JSON_HEX_TAG | JSON_HEX_APOS);
// Other clubs for collaboration picker
$otherClubsStmt = $pdo->prepare("SELECT id, name, logo_path FROM clubs WHERE status='active' AND id != :cid ORDER BY name");
$otherClubsStmt->execute([':cid' => $clubId]);
$otherClubs = $otherClubsStmt->fetchAll(PDO::FETCH_ASSOC);
$jsOtherClubs = json_encode($otherClubs, JSON_HEX_TAG | JSON_HEX_APOS);

// Incoming collab requests for this club
$incomingCollabs = [];
$incomingPendingCount = 0;
try {
    $incomingStmt = $pdo->prepare("
        SELECT ecr.id, ecr.event_id, ecr.requesting_club, ecr.status, 
               COALESCE(ecr.message, '') AS message, ecr.created_at,
               c.name AS requesting_club_name, COALESCE(c.logo_path, '') AS requesting_club_logo,
               e.name AS event_name, e.event_date, COALESCE(e.location, '') AS location, 
               e.start_time, e.end_time
        FROM event_collab_requests ecr
        JOIN clubs c ON c.id = ecr.requesting_club
        JOIN events e ON e.id = ecr.event_id
        WHERE ecr.target_club = :cid
        ORDER BY ecr.created_at DESC
    ");
    $incomingStmt->execute([':cid' => $clubId]);
    $incomingCollabs = $incomingStmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach chosen_members safely
    $cmStmt = $pdo->prepare("
        SELECT ecm.user_id, ecm.role_label, u.first_name, u.last_name, u.profile_picture
        FROM event_collab_members ecm
        JOIN users u ON u.id = ecm.user_id
        WHERE ecm.collab_id = :cid
    ");
    foreach ($incomingCollabs as &$inc) {
        try {
            $cmStmt->execute([':cid' => $inc['id']]);
            $inc['chosen_members'] = $cmStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            $inc['chosen_members'] = [];
        }
    }
    unset($inc);

    $incomingPendingCount = count(array_filter($incomingCollabs, fn($r) => $r['status'] === 'pending'));
} catch (Exception $e) {
    $incomingCollabs = [];
    $incomingPendingCount = 0;
}

$jsIncoming = json_encode($incomingCollabs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
if ($jsIncoming === false) $jsIncoming = '[]';

// Partnered events — events where this club collaborated (either as host inviting others, or as helper being invited)
try {
    // Direction 1: Events YOUR club organized — include pending and rejected too
    $partneredStmt1 = $pdo->prepare("
        SELECT ecr.id AS collab_id, ecr.event_id, ecr.message, ecr.response_message,
               ecr.status AS collab_status,
               'outgoing' AS direction,
               e.name AS event_name, e.event_date, e.start_time, e.end_time, e.location AS venue, e.status AS event_status,
               c.name AS partner_club_name, c.logo_path AS partner_club_logo
        FROM event_collab_requests ecr
        JOIN events e ON e.id = ecr.event_id
        JOIN clubs c ON c.id = ecr.target_club
        WHERE ecr.requesting_club = :cid
          AND ecr.status IN ('accepted', 'pending', 'rejected')
        ORDER BY e.event_date DESC
    ");
    $partneredStmt1->execute([':cid' => $clubId]);
    $partnered1 = $partneredStmt1->fetchAll(PDO::FETCH_ASSOC);

    // Direction 2: Events OTHER clubs organized, where they invited YOUR club and you accepted
    $partneredStmt2 = $pdo->prepare("
        SELECT ecr.id AS collab_id, ecr.event_id, ecr.message, ecr.response_message,
               ecr.status AS collab_status,
               'incoming' AS direction,
               e.name AS event_name, e.event_date, e.start_time, e.end_time, e.location AS venue, e.status AS event_status,
               c.name AS partner_club_name, c.logo_path AS partner_club_logo
        FROM event_collab_requests ecr
        JOIN events e ON e.id = ecr.event_id
        JOIN clubs c ON c.id = ecr.requesting_club
        WHERE ecr.target_club = :cid
          AND ecr.status IN ('accepted', 'pending', 'rejected')
        ORDER BY e.event_date DESC
    ");
    $partneredStmt2->execute([':cid' => $clubId]);
    $partnered2 = $partneredStmt2->fetchAll(PDO::FETCH_ASSOC);

    $partneredEvents = array_merge($partnered1, $partnered2);
    // Sort combined by date desc
    usort($partneredEvents, fn($a, $b) => strcmp($b['event_date'], $a['event_date']));

    // Attach our assigned members for each (only relevant for incoming direction)
    $pmStmt = $pdo->prepare("
        SELECT ecm.user_id, ecm.role_label, u.first_name, u.last_name, u.profile_picture
        FROM event_collab_members ecm
        JOIN users u ON u.id = ecm.user_id
        WHERE ecm.collab_id = :cid
    ");
    foreach ($partneredEvents as &$pe) {
        $pmStmt->execute([':cid' => $pe['collab_id']]);
        $pe['our_members'] = $pmStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($pe);
} catch (Exception $e) {
    $partneredEvents = [];
}
$jsPartnered = json_encode($partneredEvents, JSON_HEX_TAG | JSON_HEX_APOS);