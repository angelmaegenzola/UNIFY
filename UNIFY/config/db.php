<?php
// ============================================================
//  UNIFY — Database Connection
//  config/db.php
// ============================================================
define('DB_HOST',    'localhost');
define('DB_NAME',    'u970217706_unify_db');
define('DB_USER',    'u970217706_EGG');
define('DB_PASS',    'EGGPassword_Unify2C');
define('DB_CHARSET', 'utf8mb4');

// ── PDO ───────────────────────────────────────────────────
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET),
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    } else {
        die('Database connection failed. Please try again later.');
    }
    exit;
}

// ── mysqli ────────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    } else {
        die('Database connection failed. Please try again later.');
    }
    exit;
}
$conn->set_charset('utf8mb4');
$conn->query("SET time_zone = '+08:00'");


// ============================================================
//  MY CLUBS — Query Helpers
// ============================================================

function get_my_clubs(mysqli $conn, int $user_id): array
{
    $sql = "
        SELECT
            c.id                                        AS club_id,
            c.name,
            c.logo_path                                 AS logo,
            c.category                                  AS cat,
            c.description                               AS `desc`,
            c.room,
            c.founded,
            (SELECT COUNT(*)
             FROM   members m2
             WHERE  m2.club_id = c.id
               AND  m2.status  = 'active')              AS members,
            (SELECT COUNT(*)
             FROM   events e
             WHERE  e.club_id = c.id
               AND  e.status IN ('upcoming','ongoing')) AS events,
            m.role,
            DATE_FORMAT(m.joined_at, '%b %d, %Y')       AS joined
        FROM   members m
        INNER  JOIN clubs c ON c.id = m.club_id
        WHERE  m.user_id = ?
          AND  m.status  = 'active'
          AND  c.status  = 'active'
        ORDER  BY m.joined_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $clubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $cat_map = [
        'Tech'        => ['catClass' => 'tech',        'catIcon' => 'fa-microchip'],
        'Arts'        => ['catClass' => 'arts',        'catIcon' => 'fa-palette'],
        'Sports'      => ['catClass' => 'sports',      'catIcon' => 'fa-trophy'],
        'Science'     => ['catClass' => 'science',     'catIcon' => 'fa-flask'],
        'Academic'    => ['catClass' => 'science',     'catIcon' => 'fa-graduation-cap'],
        'Engineering' => ['catClass' => 'engineering', 'catIcon' => 'fa-gear'],
    ];

    foreach ($clubs as &$club) {
        $map              = $cat_map[$club['cat']] ?? ['catClass' => 'tech', 'catIcon' => 'fa-users'];
        $club['catClass'] = $map['catClass'];
        $club['catIcon']  = $map['catIcon'];
        $club['members']  = (int) $club['members'];
        $club['events']   = (int) $club['events'];
        $club['role']     = ucwords($club['role']);
    }

    return $clubs;
}

function get_club_officers(mysqli $conn, int $club_id): array
{
    $sql = "
        SELECT
            CONCAT(u.first_name, ' ', u.last_name) AS name,
            UPPER(CONCAT(
                SUBSTRING(u.first_name, 1, 1),
                SUBSTRING(u.last_name,  1, 1)
            ))                                     AS initials,
            m.role
        FROM   members m
        INNER  JOIN users u ON u.id = m.user_id
        WHERE  m.club_id = ?
          AND  m.status  = 'active'
          AND  m.role   IN ('president','vice president','officer')
        ORDER  BY FIELD(m.role, 'president','vice president','officer')
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $club_id);
    $stmt->execute();
    $officers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $colours = ['#0d2b1a', '#2a7a48', '#1a4d2e', '#0e7c6e', '#1d4ed8', '#be185d'];
    foreach ($officers as $i => &$off) {
        $off['role']  = ucwords($off['role']);
        $off['color'] = $colours[$i % count($colours)];
    }

    return $officers;
}

function get_club_events(mysqli $conn, int $club_id, int $user_id, int $limit = 5): array
{
    $sql = "
        SELECT
            e.id,
            UPPER(DATE_FORMAT(e.event_date, '%b')) AS month,
            DATE_FORMAT(e.event_date, '%e')        AS day,
            e.name                                 AS title,
            CONCAT(
                TIME_FORMAT(e.start_time, '%h:%i %p'),
                ' – ',
                TIME_FORMAT(e.end_time,   '%h:%i %p')
            )                                      AS time,
            e.location,
            (SELECT COUNT(*)
             FROM   event_attendees ea2
             WHERE  ea2.event_id = e.id
               AND  ea2.rsvp     = 'confirmed')    AS going,
            IF(ea.rsvp = 'confirmed', 1, 0)        AS rsvped
        FROM   events e
        LEFT   JOIN event_attendees ea ON ea.event_id = e.id AND ea.user_id = ?
        WHERE  e.club_id = ?
          AND  e.status  IN ('upcoming','ongoing')
        ORDER  BY e.event_date ASC, e.start_time ASC
        LIMIT  ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $user_id, $club_id, $limit);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($events as &$ev) {
        $ev['going']  = (int)  $ev['going'];
        $ev['rsvped'] = (bool) $ev['rsvped'];
    }

    return $events;
}

function leave_club(mysqli $conn, int $user_id, int $club_id): bool
{
    $stmt = $conn->prepare(
        "UPDATE members SET status = 'inactive'
         WHERE user_id = ? AND club_id = ? AND status = 'active'"
    );
    $stmt->bind_param('ii', $user_id, $club_id);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

function toggle_rsvp(mysqli $conn, int $user_id, int $event_id): array
{
    $chk = $conn->prepare(
        "SELECT id, rsvp FROM event_attendees WHERE user_id = ? AND event_id = ? LIMIT 1"
    );
    $chk->bind_param('ii', $user_id, $event_id);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();
    $chk->close();

    if (!$existing) {
        $ins = $conn->prepare("INSERT INTO event_attendees (event_id, user_id, rsvp) VALUES (?, ?, 'confirmed')");
        $ins->bind_param('ii', $event_id, $user_id);
        $ins->execute();
        $ins->close();
        $rsvped = true;
    } elseif ($existing['rsvp'] === 'confirmed') {
        $upd = $conn->prepare("UPDATE event_attendees SET rsvp = 'declined' WHERE user_id = ? AND event_id = ?");
        $upd->bind_param('ii', $user_id, $event_id);
        $upd->execute();
        $upd->close();
        $rsvped = false;
    } else {
        $upd = $conn->prepare("UPDATE event_attendees SET rsvp = 'confirmed' WHERE user_id = ? AND event_id = ?");
        $upd->bind_param('ii', $user_id, $event_id);
        $upd->execute();
        $upd->close();
        $rsvped = true;
    }

    $row = $conn->query(
        "SELECT COUNT(*) AS going FROM event_attendees WHERE event_id = $event_id AND rsvp = 'confirmed'"
    )->fetch_assoc();

    return [
        'rsvped' => $rsvped,
        'going'  => (int) ($row['going'] ?? 0),
    ];
}