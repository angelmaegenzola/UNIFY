<?php
// ============================================================
//  UNIFY — Club Request Handler
//  Handles:
//    POST action=submit  → student submits new club request
//    POST action=approve → admin approves a club request
//    POST action=reject  → admin rejects a club request
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';

$user_id = (int) $_SESSION['user_id'];
$action  = trim($_POST['action'] ?? '');

// ── SUBMIT (student creates club request) ──────────────────
if ($action === 'submit') {
    $name        = trim($_POST['name']        ?? '');
    $acronym     = trim($_POST['acronym']     ?? '');
    $category    = trim($_POST['category']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $room        = trim($_POST['room']         ?? '');
    $founded     = trim($_POST['founded']      ?? '');

    if (!$name || !$category || !$description) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']); exit;
    }

    // Check duplicate name
    $chk = $pdo->prepare("SELECT id FROM club_requests WHERE name = ? AND status = 'pending' LIMIT 1");
    $chk->execute([$name]);
    if ($chk->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A club with that name already has a pending request.']); exit;
    }
    // Check if club name already exists
    $chk2 = $pdo->prepare("SELECT id FROM clubs WHERE name = ? LIMIT 1");
    $chk2->execute([$name]);
    if ($chk2->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A club with that name already exists.']); exit;
    }
    // Check if user already has a pending club request
    $chk3 = $pdo->prepare("SELECT id FROM club_requests WHERE user_id = ? AND status = 'pending' LIMIT 1");
    $chk3->execute([$user_id]);
    if ($chk3->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You already have a pending club request.']); exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO club_requests (user_id, name, acronym, category, description, room, founded)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $name, $acronym ?: null, $category, $description, $room ?: null, $founded ?: null]);

    // Notify admin
    $admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, 'club_request', ?, ?, 'index.php?page=dashboard')
    ");
    foreach ($admins as $adminId) {
        $notifStmt->execute([
            $adminId,
            'New Club Request: ' . $name,
            'A student has submitted a new club request for "' . $name . '". Please review it.'
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Club request submitted! Admin will review it shortly.']);
    exit;
}

// ── APPROVE (admin approves club request) ──────────────────
if ($action === 'approve') {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit;
    }
    $req_id = (int)($_POST['req_id'] ?? 0);
    if (!$req_id) { echo json_encode(['success' => false, 'message' => 'Invalid request ID.']); exit; }

    $pdo->beginTransaction();
    try {
        $req = $pdo->prepare("SELECT * FROM club_requests WHERE id = ? AND status = 'pending' LIMIT 1");
        $req->execute([$req_id]);
        $r = $req->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Request not found or already reviewed.']); exit;
        }

        // Insert into clubs
        $ins = $pdo->prepare("
            INSERT INTO clubs (name, acronym, category, description, room, founded, status, budget)
            VALUES (?, ?, ?, ?, ?, ?, 'active', 0.00)
        ");
        $ins->execute([$r['name'], $r['acronym'], $r['category'], $r['description'], $r['room'], $r['founded']]);
        $new_club_id = (int)$pdo->lastInsertId();

        // Insert creator as president
        $memStmt = $pdo->prepare("
            INSERT IGNORE INTO members (user_id, club_id, role, status, joined_at)
            VALUES (?, ?, 'president', 'active', NOW())
        ");
        $memStmt->execute([$r['user_id'], $new_club_id]);

        // Mark request approved
        $pdo->prepare("
            UPDATE club_requests SET status='approved', reviewed_by=?, reviewed_at=NOW() WHERE id=?
        ")->execute([$user_id, $req_id]);

        // Notify the student
        $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, link)
            VALUES (?, 'club_approved', ?, ?, 'index.php?page=officer_dashboard')
        ")->execute([
            $r['user_id'],
            'Your Club Request Was Approved! 🎉',
            'Congratulations! Your club "' . $r['name'] . '" has been approved. You are now the club president. Head to your Officer Dashboard to get started.',
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'club_id' => $new_club_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── REJECT (admin rejects club request) ───────────────────
if ($action === 'reject') {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']); exit;
    }
    $req_id    = (int)($_POST['req_id']    ?? 0);
    $admin_note = trim($_POST['admin_note'] ?? '');

    $req = $pdo->prepare("SELECT * FROM club_requests WHERE id = ? AND status='pending' LIMIT 1");
    $req->execute([$req_id]);
    $r = $req->fetch(PDO::FETCH_ASSOC);
    if (!$r) { echo json_encode(['success' => false, 'message' => 'Request not found.']); exit; }

    $pdo->prepare("
        UPDATE club_requests SET status='rejected', admin_note=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?
    ")->execute([$admin_note, $user_id, $req_id]);

    // Notify the student
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, 'club_rejected', ?, ?, 'index.php?page=studenthome')
    ")->execute([
        $r['user_id'],
        'Club Request Update',
        'Your club request for "' . $r['name'] . '" was not approved.' . ($admin_note ? ' Reason: ' . $admin_note : ''),
    ]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
