<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }

require_once __DIR__ . '/../../config/db.php';
$me = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$me->execute([$_SESSION['user_id']]);
$me = $me->fetch();
$adminFirst   = $me['first_name'] ?? 'Admin';
$adminLast    = $me['last_name']  ?? '';
$adminName    = trim($adminFirst . ' ' . $adminLast);
$adminInitial = strtoupper(substr($adminFirst, 0, 1));
$_sessionPic  = $_SESSION['profile_picture'] ?? '';
$avatar_url   = $_sessionPic
    ? '/UNIFY(db)/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($_sessionPic))
    : '';
$toast = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add') {
        $uid     = (int)($_POST['user_id']  ?? 0);
        $cid     = (int)($_POST['club_id']  ?? 0);
        $course  = trim($_POST['course']    ?? '');
        $year    = trim($_POST['year']      ?? '');
        $section = trim($_POST['section']   ?? '');
        $role    = $_POST['role']           ?? 'member';
        $status  = $_POST['status']         ?? 'active';
        $chk = $pdo->prepare('SELECT id FROM members WHERE user_id=? AND club_id=? LIMIT 1');
        $chk->execute([$uid, $cid]);
        if ($chk->fetch()) {
            $toast = 'error:That user is already a member of that club.';
        } else {
            $pdo->prepare('INSERT INTO members (user_id, club_id, course, year, section, role, status) VALUES (?,?,?,?,?,?,?)')
                ->execute([$uid, $cid, $course, $year, $section, $role, $status]);
            $toast = 'success:Member added successfully.';
        }
    } elseif ($act === 'edit') {
        $id      = (int)($_POST['id']       ?? 0);
        $course  = trim($_POST['course']    ?? '');
        $year    = trim($_POST['year']      ?? '');
        $section = trim($_POST['section']   ?? '');
        $role    = $_POST['role']           ?? 'member';
        $status  = $_POST['status']         ?? 'active';
        $pdo->prepare('UPDATE members SET course=?, year=?, section=?, role=?, status=? WHERE id=?')
            ->execute([$course, $year, $section, $role, $status, $id]);
        $toast = 'success:Member updated successfully.';
    } elseif ($act === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM members WHERE id=?')->execute([$id]);
        $toast = 'success:Member removed.';
    }
$_SESSION['toast'] = $toast;
header('Location: index.php?page=members');
exit;
}
$toast = $_SESSION['toast'] ?? '';
unset($_SESSION['toast']);

$toast = $_GET['toast'] ?? '';
$rows = $pdo->query(
    'SELECT m.id, m.course, m.year, m.section, m.role, m.status, m.joined_at,
            u.id AS user_id, u.first_name, u.last_name, u.email,
            c.name AS club_name, c.id AS club_id
     FROM members m
     JOIN users u ON u.id = m.user_id
     JOIN clubs c ON c.id = m.club_id
     ORDER BY u.last_name, u.first_name, m.joined_at DESC'
)->fetchAll();
$grouped = [];
foreach ($rows as $r) {

    $uid = $r['user_id'];
    if (!isset($grouped[$uid])) {
        $grouped[$uid] = [
            'user_id'    => $uid,
            'first_name' => $r['first_name'],
            'last_name'  => $r['last_name'],
            'email'      => $r['email'],
            'course'     => $r['course'],
            'year'       => $r['year'],
            'section'    => $r['section'],
            'status'     => $r['status'],   // use first/primary membership status
            'memberships'=> [],
        ];
    }
    $grouped[$uid]['memberships'][] = [
        'id'        => $r['id'],
        'club_name' => $r['club_name'],
        'club_id'   => $r['club_id'],
        'role'      => $r['role'],
        'status'    => $r['status'],
        'joined_at' => $r['joined_at'],
    ];
}
$members = array_values($grouped);
$total    = count($members);
$active   = count(array_filter($members, fn($r) => $r['status'] === 'active'));
$pending  = count(array_filter($members, fn($r) => $r['status'] === 'pending'));
$inactive = count(array_filter($members, fn($r) => $r['status'] === 'inactive'));
$clubs = $pdo->query('SELECT id, name FROM clubs WHERE status="active" ORDER BY name')->fetchAll();
$users = $pdo->query('SELECT id, first_name, last_name, username FROM users ORDER BY first_name')->fetchAll();

function initials(string $f, string $l): string {
    return strtoupper(substr($f,0,1) . substr($l,0,1));
}
function roleBadgeClass(string $role): string {
    return match($role) {
        'president'      => 'role-badge exec',
        'vice president' => 'role-badge exec',
        'officer'        => 'role-badge officer',
        default          => 'role-badge',
    };
}
// Unread notifications count for admin
$adminUnreadNotifs = 0;
try {
    $nStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND is_read=0");
    $nStmt->execute([':uid' => $_SESSION['user_id']]);
    $adminUnreadNotifs = (int) $nStmt->fetchColumn();
} catch (Exception $e) { $adminUnreadNotifs = 0; }