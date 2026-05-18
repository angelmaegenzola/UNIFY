<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }

require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';
$me = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$me->execute([$_SESSION['user_id']]);
$me = $me->fetch(PDO::FETCH_ASSOC);
$adminFirst   = $me['first_name'] ?? 'Admin';
$adminLast    = $me['last_name']  ?? '';
$adminName    = trim($adminFirst . ' ' . $adminLast);
$adminInitial = strtoupper(substr($adminFirst, 0, 1));
$_sessionPic  = $_SESSION['profile_picture'] ?? '';
$avatar_url   = $_sessionPic
    ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($_sessionPic))
    : '';
function handleLogoUpload(string $field): ?string {
    if (empty($_FILES[$field]['tmp_name'])) return null;

    $file    = $_FILES[$field];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];

    if (!in_array($file['type'], $allowed, true)) return null;
    if ($file['size'] > 2 * 1024 * 1024) return null; // 2 MB cap

    $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fname   = 'club_' . uniqid() . '.' . $ext;
    $dir     = $_SERVER['DOCUMENT_ROOT'] . '/assets/pictures/clubs/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $dest    = $dir . $fname;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
    return '/assets/pictures/clubs/' . $fname;
}
$toast = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add') {
        $name     = trim($_POST['name']        ?? '');
        $acronym  = trim($_POST['acronym']     ?? '') ?: null;
        $category = trim($_POST['category']    ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $room     = trim($_POST['room']        ?? '') ?: null;
        $founded  = trim($_POST['founded']     ?? '') ?: null;
        $status   = $_POST['status']           ?? 'active';

        if (empty($name) || empty($category) || empty($desc)) {
            $toast = 'error:Name, category, and description are required.';
        } else {
            $chk = $pdo->prepare('SELECT id FROM clubs WHERE name = ? LIMIT 1');
            $chk->execute([$name]);
            if ($chk->fetch()) {
                $toast = 'error:A club with that name already exists.';
            } else {
                $logo = handleLogoUpload('logo');
                $pdo->prepare(
                    'INSERT INTO clubs (name, acronym, category, description, room, founded, status, logo_path)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                )->execute([$name, $acronym, $category, $desc, $room, $founded, $status, $logo]);
                $toast = 'success:Club created successfully.';
            }
        }
    } elseif ($act === 'edit') {
        $id       = (int)($_POST['id']         ?? 0);
        $name     = trim($_POST['name']        ?? '');
        $acronym  = trim($_POST['acronym']     ?? '') ?: null;
        $category = trim($_POST['category']    ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $room     = trim($_POST['room']        ?? '') ?: null;
        $founded  = trim($_POST['founded']     ?? '') ?: null;
        $status   = $_POST['status']           ?? 'active';
        $budget   = (float)($_POST['budget']   ?? 0);

        $newLogo = handleLogoUpload('logo');

        if ($newLogo) {
            $old = $pdo->prepare('SELECT logo_path FROM clubs WHERE id = ?');
            $old->execute([$id]);
            $oldPath = $old->fetchColumn();
            if ($oldPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldPath)) {
                @unlink($_SERVER['DOCUMENT_ROOT'] . $oldPath);
            }
            $pdo->prepare(
                'UPDATE clubs SET name=?, acronym=?, category=?, description=?, room=?, founded=?, status=?, budget=?, logo_path=?
                 WHERE id=?'
            )->execute([$name, $acronym, $category, $desc, $room, $founded, $status, $budget, $newLogo, $id]);
        } else {
            $pdo->prepare(
                'UPDATE clubs SET name=?, acronym=?, category=?, description=?, room=?, founded=?, status=?, budget=?
                 WHERE id=?'
            )->execute([$name, $acronym, $category, $desc, $room, $founded, $status, $budget, $id]);
        }
        $toast = 'success:Club updated successfully.';
    } elseif ($act === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $old = $pdo->prepare('SELECT logo_path FROM clubs WHERE id = ?');
        $old->execute([$id]);
        $oldPath = $old->fetchColumn();
        if ($oldPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldPath)) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . $oldPath);
        }
        $pdo->prepare('DELETE FROM clubs WHERE id = ?')->execute([$id]);
        $toast = 'success:Club deleted.';
    }

    header('Location: index.php?page=clubpage' . ($toast ? '&toast=' . urlencode($toast) : ''));
    exit;
}

$toast = $_GET['toast'] ?? '';
$pdo->exec("UPDATE events SET status='completed' WHERE status='upcoming' AND event_date < CURDATE()");
require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/app/models/clubpage_model.php';
$avatarColors = ['oa-blue','oa-teal','oa-green','oa-yellow','oa-orange','oa-purple'];
$eventColors  = ['cev-blue','cev-teal','cev-green','cev-yellow','cev-orange'];

function logoOrInitial(array $club): string {
    if (!empty($club['logo_path'])) {
        return '<img class="club-item-logo" src="' . htmlspecialchars($club['logo_path']) . '" alt="' . htmlspecialchars($club['name']) . ' logo" style="width:44px;height:44px;border-radius:10px;object-fit:cover;">';
    }
    return '<div class="club-item-logo club-item-logo-initial">' . strtoupper(substr($club['name'], 0, 1)) . '</div>';
}

function buildOfficersJson(array $officers): string {
    $colors = ['oa-blue','oa-teal','oa-green','oa-yellow'];
    $out = [];
    foreach ($officers as $i => $o) {
        $out[] = [
            'name'  => $o['first_name'] . ' ' . $o['last_name'],
            'pos'   => ucwords($o['role']),
            'color' => $colors[$i % count($colors)],
            'lead'  => $o['role'] === 'president',
        ];
    }
    return json_encode($out);
}

function buildUpcomingJson(array $events): string {
    $colors = ['cev-blue','cev-teal','cev-green','cev-yellow'];
    $out = [];
    foreach ($events as $i => $e) {
        $ts  = strtotime($e['event_date']);
        $out[] = [
            'day'   => date('d', $ts),
            'mon'   => strtoupper(date('M', $ts)),
            'title' => $e['name'],
            'time'  => substr($e['start_time'], 0, 5) . ' – ' . substr($e['end_time'], 0, 5),
            'color' => $colors[$i % count($colors)],
        ];
    }
    return json_encode($out);
}
// Unread notifications count for admin
$adminUnreadNotifs = 0;
try {
    $nStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND is_read=0");
    $nStmt->execute([':uid' => $_SESSION['user_id']]);
    $adminUnreadNotifs = (int) $nStmt->fetchColumn();
} catch (Exception $e) { $adminUnreadNotifs = 0; }