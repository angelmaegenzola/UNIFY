<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/events_model.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: index.php?page=login');
  exit;
}

/* ══════════════════════════════════════════════════════════
   AJAX handlers
══════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  header('Content-Type: application/json');
  $action = $_POST['action'];
  try {

    if ($action === 'event_create') {
      $club_id     = intval($_POST['club_id']     ?? 0);
      $name        = trim($_POST['name']          ?? '');
      $description = trim($_POST['description']   ?? '');
      $event_date  = trim($_POST['event_date']    ?? '');
      $start_time  = trim($_POST['start_time']    ?? '') ?: null;
      $end_time    = trim($_POST['end_time']      ?? '') ?: null;
      $location    = trim($_POST['location']      ?? '');
      $status      = trim($_POST['status']        ?? 'upcoming');

      if (!$club_id || !$name || !$event_date) {
        echo json_encode(['success'=>false,'message'=>'Club, name and date are required.']); exit;
      }
      $stmt = $pdo->prepare("INSERT INTO events (club_id,name,description,event_date,start_time,end_time,location,status)
        VALUES (:cid,:name,:desc,:date,:start,:end,:loc,:status)");
      $stmt->execute([':cid'=>$club_id,':name'=>$name,':desc'=>$description,
        ':date'=>$event_date,':start'=>$start_time,':end'=>$end_time,':loc'=>$location,':status'=>$status]);
      echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); exit;
    }

    if ($action === 'event_update') {
      $id          = intval($_POST['id']          ?? 0);
      $club_id     = intval($_POST['club_id']     ?? 0);
      $name        = trim($_POST['name']          ?? '');
      $description = trim($_POST['description']   ?? '');
      $event_date  = trim($_POST['event_date']    ?? '');
      $start_time  = trim($_POST['start_time']    ?? '') ?: null;
      $end_time    = trim($_POST['end_time']      ?? '') ?: null;
      $location    = trim($_POST['location']      ?? '');
      $status      = trim($_POST['status']        ?? 'upcoming');

      if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
      $stmt = $pdo->prepare("UPDATE events SET club_id=:cid,name=:name,description=:desc,
        event_date=:date,start_time=:start,end_time=:end,location=:loc,status=:status WHERE id=:id");
      $stmt->execute([':cid'=>$club_id,':name'=>$name,':desc'=>$description,
        ':date'=>$event_date,':start'=>$start_time,':end'=>$end_time,':loc'=>$location,':status'=>$status,':id'=>$id]);
      echo json_encode(['success'=>true]); exit;
    }

    if ($action === 'event_delete') {
      $id = intval($_POST['id'] ?? 0);
      if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
      $pdo->prepare("DELETE FROM events WHERE id=:id")->execute([':id'=>$id]);
      echo json_encode(['success'=>true]); exit;
    }

    // ── Approve pending event → upcoming ──────────────────
    if ($action === 'event_approve') {
      $id = intval($_POST['id'] ?? 0);
      if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
      $pdo->prepare("UPDATE events SET status='upcoming' WHERE id=:id AND status='pending_approval'")
          ->execute([':id' => $id]);
      // Notify officers of that club
      $evStmt = $pdo->prepare("SELECT e.name, e.club_id, c.name AS club_name FROM events e JOIN clubs c ON c.id=e.club_id WHERE e.id=:id LIMIT 1");
      $evStmt->execute([':id' => $id]);
      $ev = $evStmt->fetch(PDO::FETCH_ASSOC);
      if ($ev) {
        $officers = $pdo->prepare("SELECT user_id FROM members WHERE club_id=:cid AND status='active' AND role IN ('officer','lead','president','vice president','secretary','treasurer')");
        $officers->execute([':cid' => $ev['club_id']]);
        $notif = $pdo->prepare("INSERT INTO notifications (user_id,type,title,message,link) VALUES (:uid,'event_approved','✅ Event Approved',:msg,'index.php?page=officer_events')");
        foreach ($officers->fetchAll(PDO::FETCH_COLUMN) as $uid) {
          $notif->execute([':uid' => $uid, ':msg' => '"' . $ev['name'] . '" has been approved by admin and is now listed as upcoming.']);
        }
      }
      echo json_encode(['success'=>true]); exit;
    }

    // ── Reject pending event → rejected ───────────────────
    if ($action === 'event_reject') {
      $id   = intval($_POST['id'] ?? 0);
      $note = trim($_POST['note'] ?? '');
      if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
      $pdo->prepare("UPDATE events SET status='rejected' WHERE id=:id AND status='pending_approval'")
          ->execute([':id' => $id]);
      // Notify officers
      $evStmt = $pdo->prepare("SELECT e.name, e.club_id FROM events e WHERE e.id=:id LIMIT 1");
      $evStmt->execute([':id' => $id]);
      $ev = $evStmt->fetch(PDO::FETCH_ASSOC);
      if ($ev) {
        $officers = $pdo->prepare("SELECT user_id FROM members WHERE club_id=:cid AND status='active' AND role IN ('officer','lead','president','vice president','secretary','treasurer')");
        $officers->execute([':cid' => $ev['club_id']]);
        $notif = $pdo->prepare("INSERT INTO notifications (user_id,type,title,message,link) VALUES (:uid,'event_rejected','❌ Event Rejected',:msg,'index.php?page=officer_events')");
        $msg = '"' . $ev['name'] . '" was rejected by admin.' . ($note ? ' Reason: ' . $note : '');
        foreach ($officers->fetchAll(PDO::FETCH_COLUMN) as $uid) {
          $notif->execute([':uid' => $uid, ':msg' => $msg]);
        }
      }
      echo json_encode(['success'=>true]); exit;
    }

    echo json_encode(['success'=>false,'message'=>'Unknown action.']); exit;
  } catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]); exit;
  }
}

/* ══════════════════════════════════════════════════════════
   Page load — fetch data
══════════════════════════════════════════════════════════ */
$adminStmt = $pdo->prepare('SELECT first_name, last_name FROM users WHERE id = ?');
$adminStmt->execute([$_SESSION['user_id']]);
$adminRow = $adminStmt->fetch(PDO::FETCH_ASSOC);
$adminFirst   = $adminRow['first_name'] ?? 'Admin';
$adminLast    = $adminRow['last_name']  ?? '';
$adminName    = trim($adminFirst . ' ' . $adminLast);
$adminInitial = strtoupper(substr($adminFirst, 0, 1));
$_sessionPic  = $_SESSION['profile_picture'] ?? '';
$avatar_url   = $_sessionPic
    ? '/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($_sessionPic))
    : '';

$eventsStmt = $pdo->query("
  SELECT e.*, c.name AS club_name, c.acronym AS club_acronym
  FROM events e
  JOIN clubs c ON c.id = e.club_id
  ORDER BY e.event_date ASC, e.start_time ASC
");
$dbEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

$totalEvents     = count($dbEvents);
$upcomingEvents  = count(array_filter($dbEvents, fn($e) => $e['status'] === 'upcoming'));
$ongoingEvents   = count(array_filter($dbEvents, fn($e) => $e['status'] === 'ongoing'));
$completedEvents = count(array_filter($dbEvents, fn($e) => $e['status'] === 'completed'));
$pendingEvents   = count(array_filter($dbEvents, fn($e) => $e['status'] === 'pending_approval'));

$clubs = $pdo->query("SELECT id, name, acronym FROM clubs WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
