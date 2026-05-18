<?php
// ============================================================
//  UNIFY — Student Events Controller
//  app/controllers/studentevents_controller.php
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: index.php?page=login'); exit; }

require_once __DIR__ . '/../../config/db.php';

date_default_timezone_set('Asia/Manila');

$user_id    = (int) $_SESSION['user_id'];
$first_name = htmlspecialchars($_SESSION['first_name'] ?? 'Student');
$last_name  = htmlspecialchars($_SESSION['last_name']  ?? '');
$full_name  = trim($first_name . ' ' . $last_name);
$avatar     = strtoupper(substr($first_name, 0, 1));

// ── Membership guard — fetch ALL active clubs ──────────────
$mem_stmt = $conn->prepare("
    SELECT m.role, c.id AS club_id, c.name AS club_name,
           c.acronym, c.category, c.description AS club_desc,
           c.logo_path, c.room, c.founded
    FROM members m
    JOIN clubs c ON c.id = m.club_id
    WHERE m.user_id = ? AND m.status = 'active'
    ORDER BY FIELD(m.role,'president','vice president','officer','member'), m.joined_at ASC
");
$mem_stmt->bind_param('i', $user_id);
$mem_stmt->execute();
$my_clubs_all = $mem_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$mem_stmt->close();

if (empty($my_clubs_all)) {
    $conn->close();
    header('Location: index.php?page=explore');
    exit;
}

// Default (first) club
$my_club  = $my_clubs_all[0];
$my_role  = $my_club['role'];
$club_id  = (int) $my_club['club_id'];
$club_name= $my_club['club_name'];
$club_acro= $my_club['acronym'];
$club_cat = $my_club['category'];
$club_desc= $my_club['club_desc'];
$club_logo= $my_club['logo_path'];
$club_room= $my_club['room'];

$today_ts    = strtotime(date('Y-m-d'));
$week_end_ts = strtotime('+7 days', $today_ts);

// ── Auto-update event statuses ─────────────────────────────
$_date = date('Y-m-d');
$_time = date('H:i:s');

$_club_ids = array_map(fn($mc) => (int)$mc['club_id'], $my_clubs_all);
$_ph = implode(',', array_fill(0, count($_club_ids), '?'));
$_tp = str_repeat('i', count($_club_ids));

// upcoming/pending_approval → ongoing
$_s = $conn->prepare("
    UPDATE events SET status = 'ongoing'
    WHERE club_id IN ($_ph)
      AND status IN ('upcoming','pending_approval')
      AND event_date = ?
      AND start_time <= ?
      AND (end_time IS NULL OR end_time > ?)
");
$_s->bind_param($_tp . 'sss', ...[...$_club_ids, $_date, $_time, $_time]);
$_s->execute(); $_s->close();

// upcoming/pending_approval → completed (same day, window passed)
$_s4 = $conn->prepare("
    UPDATE events SET status = 'completed'
    WHERE club_id IN ($_ph)
      AND status IN ('upcoming','pending_approval')
      AND event_date = ?
      AND end_time IS NOT NULL
      AND end_time <= ?
");
$_s4->bind_param($_tp . 'ss', ...[...$_club_ids, $_date, $_time]);
$_s4->execute(); $_s4->close();

// ongoing → completed
$_s2 = $conn->prepare("
    UPDATE events SET status = 'completed'
    WHERE club_id IN ($_ph)
      AND status = 'ongoing'
      AND (event_date < ? OR (event_date = ? AND end_time IS NOT NULL AND end_time <= ?))
");
$_s2->bind_param($_tp . 'sss', ...[...$_club_ids, $_date, $_date, $_time]);
$_s2->execute(); $_s2->close();

// upcoming/pending_approval → completed (past date)
$_s3 = $conn->prepare("
    UPDATE events SET status = 'completed'
    WHERE club_id IN ($_ph)
      AND status IN ('upcoming','pending_approval')
      AND event_date < ?
");
$_s3->bind_param($_tp . 's', ...[...$_club_ids, $_date]);
$_s3->execute(); $_s3->close();

// ── Per-club data collection ───────────────────────────────
$all_events_data = [];

foreach ($my_clubs_all as $mc) {
    $cid = (int) $mc['club_id'];

    // Total active members
    $mc_stmt = $conn->prepare("SELECT COUNT(*) AS total_members FROM members WHERE club_id = ? AND status = 'active'");
    $mc_stmt->bind_param('i', $cid);
    $mc_stmt->execute();
    $total_members_club = (int) ($mc_stmt->get_result()->fetch_assoc()['total_members'] ?? 0);
    $mc_stmt->close();

    // Events
    $ev_stmt = $conn->prepare("
        SELECT e.id, e.name, e.description, e.event_date, e.start_time, e.end_time,
               e.location, e.status, e.club_id, e.created_at,
               c.name      AS club_name,
               c.acronym   AS club_acronym,
               c.category  AS club_cat,
               c.logo_path AS club_logo,
               SUM(CASE WHEN ea.rsvp = 'confirmed' THEN 1 ELSE 0 END) AS going_count,
               SUM(CASE WHEN ea.rsvp = 'pending'   THEN 1 ELSE 0 END) AS pending_count,
               SUM(CASE WHEN ea.rsvp = 'declined'  THEN 1 ELSE 0 END) AS declined_count,
               COUNT(DISTINCT ea.id) AS attendees_total
        FROM events e
        JOIN clubs c ON c.id = e.club_id
        LEFT JOIN event_attendees ea ON ea.event_id = e.id
        WHERE e.club_id = ?
          AND e.event_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY e.id
        ORDER BY e.event_date ASC, e.start_time ASC
    ");
    $ev_stmt->bind_param('i', $cid);
    $ev_stmt->execute();
    $club_events_raw = $ev_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $ev_stmt->close();

    // Organizer
    $organizer = null;
    if (!empty($club_events_raw)) {
        $org_stmt = $conn->prepare("
            SELECT u.first_name, u.last_name, m.role
            FROM members m JOIN users u ON u.id = m.user_id
            WHERE m.club_id = ? AND m.status = 'active'
              AND m.role IN ('president','vice president','officer')
            ORDER BY FIELD(m.role,'president','vice president','officer'), m.joined_at ASC
            LIMIT 1
        ");
        $org_stmt->bind_param('i', $cid);
        $org_stmt->execute();
        $organizer = $org_stmt->get_result()->fetch_assoc();
        $org_stmt->close();
    }

    // Attendee preview
    $attendee_preview = [];
    if (!empty($club_events_raw)) {
        $event_ids    = array_column($club_events_raw, 'id');
        $placeholders = implode(',', array_fill(0, count($event_ids), '?'));
        $types        = str_repeat('i', count($event_ids));
        $ap_stmt = $conn->prepare("
            SELECT ea.event_id, u.first_name, u.last_name
            FROM event_attendees ea JOIN users u ON u.id = ea.user_id
            WHERE ea.event_id IN ($placeholders) AND ea.rsvp = 'confirmed'
            ORDER BY ea.id ASC
        ");
        $ap_stmt->bind_param($types, ...$event_ids);
        $ap_stmt->execute();
        foreach ($ap_stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $eid = (int) $r['event_id'];
            if (!isset($attendee_preview[$eid])) $attendee_preview[$eid] = [];
            if (count($attendee_preview[$eid]) >= 5) continue;
            $attendee_preview[$eid][] = [
                'name'   => trim($r['first_name'] . ' ' . $r['last_name']),
                'avatar' => strtoupper(substr($r['first_name'],0,1) . substr($r['last_name'],0,1)),
            ];
        }
        $ap_stmt->close();
    }

    // RSVPs for this club
    $rsvp_stmt = $conn->prepare("
        SELECT ea.event_id FROM event_attendees ea
        JOIN events e ON e.id = ea.event_id
        WHERE ea.user_id = ? AND e.club_id = ? AND ea.rsvp = 'confirmed'
    ");
    $rsvp_stmt->bind_param('ii', $user_id, $cid);
    $rsvp_stmt->execute();
    $rsvped_ids_club = array_map('intval', array_column($rsvp_stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'event_id'));
    $rsvp_stmt->close();

    // Reminders for this club
    $rem_stmt = $conn->prepare("
        SELECT er.event_id FROM event_reminders er
        JOIN events e ON e.id = er.event_id
        WHERE er.user_id = ? AND e.club_id = ?
    ");
    $rem_stmt->bind_param('ii', $user_id, $cid);
    $rem_stmt->execute();
    $reminded_ids_club = array_map('intval', array_column($rem_stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'event_id'));
    $rem_stmt->close();

    // Feedback already submitted by this user for events in this club
    $fb_stmt = $conn->prepare("
        SELECT ef.event_id, ef.rating, ef.review FROM event_feedback ef
        JOIN events e ON e.id = ef.event_id
        WHERE ef.user_id = ? AND e.club_id = ?
    ");
    $fb_stmt->bind_param('ii', $user_id, $cid);
    $fb_stmt->execute();
    $my_feedback_map = [];
    foreach ($fb_stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $fb) {
        $my_feedback_map[(int)$fb['event_id']] = ['rating' => (int)$fb['rating'], 'review' => $fb['review']];
    }
    $fb_stmt->close();

    // Avg ratings per event for this club
    foreach ($club_events_raw as &$e) {
        $e['avg_rating']    = 0;
        $e['total_ratings'] = 0;
    }
    unset($e);
    if (!empty($club_events_raw)) {
        $eids = array_column($club_events_raw, 'id');
        $ph_fb = implode(',', array_fill(0, count($eids), '?'));
        $tp_fb = str_repeat('i', count($eids));
        $avg_stmt = $conn->prepare("
            SELECT event_id, ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total_ratings
            FROM event_feedback WHERE event_id IN ($ph_fb) GROUP BY event_id
        ");
        $avg_stmt->bind_param($tp_fb, ...$eids);
        $avg_stmt->execute();
        $avg_map = [];
        foreach ($avg_stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $avg_map[(int)$r['event_id']] = ['avg' => (float)$r['avg_rating'], 'total' => (int)$r['total_ratings']];
        }
        $avg_stmt->close();
        foreach ($club_events_raw as &$e) {
            $eid = (int)$e['id'];
            $e['avg_rating']    = $avg_map[$eid]['avg']   ?? 0;
            $e['total_ratings'] = $avg_map[$eid]['total'] ?? 0;
        }
        unset($e);
    }

    // Enrich events
    foreach ($club_events_raw as &$e) {
        $ts = strtotime($e['event_date']);
        $e['going_count']     = (int)($e['going_count']     ?? 0);
        $e['pending_count']   = (int)($e['pending_count']   ?? 0);
        $e['declined_count']  = (int)($e['declined_count']  ?? 0);
        $e['attendees_total'] = (int)($e['attendees_total'] ?? 0);
        $e['day_num']   = date('j',      $ts);
        $e['month']     = date('M',      $ts);
        $e['weekday']   = date('l',      $ts);
        $e['date_full'] = date('F j, Y', $ts);
        $st = $e['start_time'] ? date('g:i A', strtotime($e['start_time'])) : null;
        $en = $e['end_time']   ? date('g:i A', strtotime($e['end_time']))   : null;
        $e['time_range'] = $st ? ($en ? "$st – $en" : $st) : 'TBA';
        if ($e['start_time'] && $e['end_time']) {
            $dur_min = (strtotime($e['end_time']) - strtotime($e['start_time'])) / 60;
            if ($dur_min > 0) {
                $h = floor($dur_min / 60); $m = $dur_min % 60;
                $e['duration'] = trim(($h ? "{$h}h" : '') . ($m ? " {$m}m" : ''));
            } else $e['duration'] = '';
        } else $e['duration'] = '';
        $diff_days = (int) round(($ts - $today_ts) / 86400);
        if ($diff_days === 0)      $e['when_label'] = 'Today';
        elseif ($diff_days === 1)  $e['when_label'] = 'Tomorrow';
        elseif ($diff_days > 1)    $e['when_label'] = "In $diff_days days";
        elseif ($diff_days === -1) $e['when_label'] = 'Yesterday';
        else                       $e['when_label'] = abs($diff_days) . ' days ago';
        $e['days_diff']    = $diff_days;
        $e['is_this_week'] = ($ts >= $today_ts && $ts <= $week_end_ts);
        $e['attendees']    = $attendee_preview[(int)$e['id']] ?? [];
    }
    unset($e);

    // Stats
    $stat_total    = count($club_events_raw);
    $stat_upcoming = $stat_thisweek = $stat_completed = 0;
    $status_set    = [];
    foreach ($club_events_raw as $e) {
        if (in_array($e['status'], ['upcoming','ongoing'])) $stat_upcoming++;
        if ($e['status'] === 'completed') $stat_completed++;
        if ($e['is_this_week']) $stat_thisweek++;
        $status_set[$e['status']] = true;
    }
    $stat_rsvped = count($rsvped_ids_club);
    $next_event  = null;
    foreach ($club_events_raw as $e) {
        if (in_array($e['status'], ['upcoming','ongoing'], true)) { $next_event = $e; break; }
    }

    // Club announcements
    $club_ann_local = [];
    $ann_stmt = $conn->prepare("SELECT title, description, status, posted_at FROM announcements WHERE club_id = ? ORDER BY posted_at DESC LIMIT 5");
    $ann_stmt->bind_param('i', $cid);
    $ann_stmt->execute();
    $club_ann_local = $ann_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $ann_stmt->close();

    $organizer_name = $organizer ? trim($organizer['first_name'] . ' ' . $organizer['last_name']) : null;
    $organizer_role = $organizer ? ucwords($organizer['role']) : null;

    $all_events_data[$cid] = [
        'meta'           => $mc,
        'events'         => $club_events_raw,
        'rsvped_ids'     => $rsvped_ids_club,
        'reminded_ids'   => $reminded_ids_club,
        'my_feedback'    => $my_feedback_map,
        'total_members'  => $total_members_club,
        'organizer_name' => $organizer_name,
        'organizer_role' => $organizer_role,
        'stat_total'     => $stat_total,
        'stat_upcoming'  => $stat_upcoming,
        'stat_thisweek'  => $stat_thisweek,
        'stat_completed' => $stat_completed,
        'stat_rsvped'    => $stat_rsvped,
        'status_set'     => $status_set,
        'next_event'     => $next_event,
        'club_ann'       => $club_ann_local,
    ];
}

$conn->close();

// ── Fetch my assignments across all clubs ──────────────
$conn2 = new mysqli('127.0.0.1', 'root', '', 'unify_db');
$conn2->set_charset('utf8mb4');
$conn2->query("SET time_zone = '+08:00'");

$asgn_stmt = $conn2->prepare("
    SELECT ea.event_id, ea.role_label, ea.status AS assignment_status,
           e.name AS event_name, e.event_date, e.start_time, e.end_time,
           e.location, e.status AS event_status,
           c.name AS club_name, c.id AS club_id, c.logo_path AS club_logo, c.acronym
    FROM event_assignees ea
    JOIN events e ON e.id = ea.event_id
    JOIN clubs  c ON c.id = e.club_id
    WHERE ea.user_id = ?
      AND e.status IN ('upcoming','ongoing','completed')
    ORDER BY FIELD(ea.status,'pending','accepted','declined'), e.event_date ASC, e.start_time ASC
");
$asgn_stmt->bind_param('i', $user_id);
$asgn_stmt->execute();
$my_assignments = $asgn_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$asgn_stmt->close();

// Enrich assignment date formatting
foreach ($my_assignments as &$asgn) {
    $ts = strtotime($asgn['event_date']);
    $asgn['day_num']   = date('j',      $ts);
    $asgn['month']     = date('M',      $ts);
    $asgn['weekday']   = date('l',      $ts);
    $asgn['date_full'] = date('F j, Y', $ts);
    $st = $asgn['start_time'] ? date('g:i A', strtotime($asgn['start_time'])) : null;
    $en = $asgn['end_time']   ? date('g:i A', strtotime($asgn['end_time']))   : null;
    $asgn['time_range'] = $st ? ($en ? "$st – $en" : $st) : 'TBA';
}
unset($asgn);

$conn2->close();

$pending_assignments_count = count(array_filter($my_assignments, fn($a) => $a['assignment_status'] === 'pending'));

// Profile picture
$picStmt = $pdo->prepare('SELECT profile_picture FROM users WHERE id = ? LIMIT 1');
$picStmt->execute([$user_id]);
$picFile    = $picStmt->fetchColumn();
$avatar_url = $picFile
    ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($picFile))
    : '';

// Sidebar vars
$has_club   = !empty($my_clubs_all);
$is_officer = in_array($my_role, ['president', 'vice president', 'officer', 'lead']);

// First club shorthand
$first_cid      = (int)$my_clubs_all[0]['club_id'];
$first_data     = $all_events_data[$first_cid];
$club_ann       = $first_data['club_ann'];
$total_members  = $first_data['total_members'];

$club_events    = $first_data['events'];
$rsvped_ids     = $first_data['rsvped_ids'];
$organizer_name = $first_data['organizer_name'];
$organizer_role = $first_data['organizer_role'];
$stat_total     = $first_data['stat_total'];
$stat_upcoming  = $first_data['stat_upcoming'];
$stat_thisweek  = $first_data['stat_thisweek'];
$stat_completed = $first_data['stat_completed'];
$stat_rsvped    = $first_data['stat_rsvped'];
$status_set     = $first_data['status_set'];
$next_event     = $first_data['next_event'];

// ── Helpers ────────────────────────────────────────────────
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