<?php
// ============================================================
//  UNIFY — Student Home Controller
//  app/views/studenthome.php (controller portion)
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// ── Define user_id FIRST before any DB calls ───────────────
$user_id    = (int) $_SESSION['user_id'];
$first_name = htmlspecialchars($_SESSION['first_name'] ?? 'Student');
$last_name  = htmlspecialchars($_SESSION['last_name']  ?? '');
$full_name  = trim($first_name . ' ' . $last_name);
$avatar     = strtoupper(substr($first_name, 0, 1));

// ── Single DB connection ────────────────────────────────────
$conn = new mysqli('localhost', 'u970217706_EGG', 'EGGPassword_Unify2C', 'u970217706_unify_db');
if ($conn->connect_error) die('Database connection failed.');
$conn->set_charset('utf8mb4');

// Profile picture (must come after $conn is created)
$pic_stmt = $conn->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$pic_stmt->bind_param('i', $user_id);
$pic_stmt->execute();
$pic_stmt->bind_result($picFile);
$pic_stmt->fetch();
$pic_stmt->close();
$avatar_url = $picFile
    ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';

// ── Role guard: always check DB, never trust stale session ──
$role_check = $conn->prepare("
    SELECT role FROM members
    WHERE user_id = ? AND role IN ('officer','lead','president','vice president') AND status = 'active'
    LIMIT 1
");
$role_check->bind_param('i', $user_id);
$role_check->execute();
$live_role = $role_check->get_result()->fetch_assoc();
$role_check->close();

if ($live_role) {
    // Sync session and redirect to officer dashboard
    $_SESSION['role'] = $live_role['role'];
    header('Location: index.php?page=officer_dashboard');
    exit;
}

// Confirmed not an officer by DB — no second query needed
$is_officer = false;

// ── All active memberships ──────────────────────────────────
$mem_stmt = $conn->prepare("
    SELECT m.role, m.status AS mem_status, m.joined_at,
           c.id AS club_id, c.name AS club_name, c.acronym,
           c.category, c.description AS club_desc,
           c.logo_path, c.room, c.founded, c.budget,
           COUNT(DISTINCT m2.id) AS member_count
    FROM members m
    JOIN clubs c ON c.id = m.club_id
    LEFT JOIN members m2 ON m2.club_id = c.id AND m2.status = 'active'
    WHERE m.user_id = ? AND m.status = 'active'
    GROUP BY m.id
    ORDER BY FIELD(m.role,'president','vice president','officer','member'), m.joined_at ASC
");
$mem_stmt->bind_param('i', $user_id);
$mem_stmt->execute();
$my_clubs_all = $mem_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$mem_stmt->close();

$has_club = !empty($my_clubs_all);
$my_club  = $has_club ? $my_clubs_all[0] : null;
$my_role  = $has_club ? $my_club['role'] : null;

// ── Pending application check ───────────────────────────────
$pend_stmt = $conn->prepare("
    SELECT id FROM applications
    WHERE user_id = ? AND status = 'pending'
    LIMIT 1
");
$pend_stmt->bind_param('i', $user_id);
$pend_stmt->execute();
$pend_stmt->store_result();
$has_pending = $pend_stmt->num_rows > 0;
$pend_stmt->close();

if (!$has_club && $has_pending) {
    header('Location: index.php?page=status');
    exit;
}

// ── Campus-wide data ────────────────────────────────────────
$campus_events = $conn->query("
    SELECT e.name, e.event_date, e.start_time, e.location, e.status,
           c.name AS club_name, c.acronym
    FROM events e
    JOIN clubs c ON c.id = e.club_id
    WHERE e.event_date >= CURDATE() AND e.status IN ('upcoming','ongoing')
    ORDER BY e.event_date ASC
    LIMIT 4
")->fetch_all(MYSQLI_ASSOC);

$campus_ann = $conn->query("
    SELECT a.title, a.description, a.status, a.posted_at,
           c.name AS club_name
    FROM announcements a
    LEFT JOIN clubs c ON c.id = a.club_id
    ORDER BY a.posted_at DESC
    LIMIT 4
")->fetch_all(MYSQLI_ASSOC);

$club_total = (int)$conn->query("
    SELECT COUNT(*) AS cnt FROM clubs WHERE status='active'
")->fetch_assoc()['cnt'];

// ── Per-club data (for all joined clubs) ────────────────────
$all_club_data = [];
$event_count   = 0;

if ($has_club) {
    $ea_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM event_attendees WHERE user_id = ?");
    $ea_stmt->bind_param('i', $user_id);
    $ea_stmt->execute();
    $event_count = (int)$ea_stmt->get_result()->fetch_assoc()['cnt'];
    $ea_stmt->close();

    foreach ($my_clubs_all as $mc) {
        $cid = (int)$mc['club_id'];

        $mbr_stmt = $conn->prepare("
            SELECT u.first_name, u.last_name, m.role, m.joined_at
            FROM members m
            JOIN users u ON u.id = m.user_id
            WHERE m.club_id = ? AND m.status = 'active'
            ORDER BY FIELD(m.role,'president','vice president','officer','member'), m.joined_at ASC
            LIMIT 5
        ");
        $mbr_stmt->bind_param('i', $cid);
        $mbr_stmt->execute();
        $members = $mbr_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $mbr_stmt->close();

        $cev_stmt = $conn->prepare("
            SELECT name, event_date, start_time, location, status
            FROM events
            WHERE club_id = ? AND event_date >= CURDATE()
            ORDER BY event_date ASC
            LIMIT 3
        ");
        $cev_stmt->bind_param('i', $cid);
        $cev_stmt->execute();
        $events = $cev_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $cev_stmt->close();

        $can_stmt = $conn->prepare("
            SELECT title, description, status, posted_at
            FROM announcements
            WHERE club_id = ?
            ORDER BY posted_at DESC
            LIMIT 3
        ");
        $can_stmt->bind_param('i', $cid);
        $can_stmt->execute();
        $ann = $can_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $can_stmt->close();

        $all_club_data[$cid] = [
            'meta'    => $mc,
            'members' => $members,
            'events'  => $events,
            'ann'     => $ann,
        ];
    }
}

$conn->close();

// ── Helpers ─────────────────────────────────────────────────
function human_time_diff(int $ts): string {
    $diff = time() - $ts;
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return round($diff / 60) . ' min ago';
    if ($diff < 86400)  return round($diff / 3600) . ' hr ago';
    if ($diff < 604800) return round($diff / 86400) . ' day' . (round($diff / 86400) > 1 ? 's' : '') . ' ago';
    return date('M j, Y', $ts);
}

$ann_icon = [
    'urgent'   => 'fa-circle-exclamation',
    'approved' => 'fa-calendar-check',
    'info'     => 'fa-circle-info',
    'pending'  => 'fa-hourglass-half',
];
$ann_color = [
    'urgent'   => 'ann-red',
    'approved' => 'ann-green',
    'info'     => 'ann-teal',
    'pending'  => 'ann-yellow',
];
$role_badge_map = [
    'president'      => ['label' => 'President',      'class' => 'role-president'],
    'vice president' => ['label' => 'Vice President', 'class' => 'role-vp'],
    'officer'        => ['label' => 'Officer',        'class' => 'role-officer'],
    'member'         => ['label' => 'Member',         'class' => 'role-member'],
];

$first_club_id = $has_club ? (int)$my_clubs_all[0]['club_id'] : 0;