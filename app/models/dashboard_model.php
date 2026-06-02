<?php
// ============================================================
//  UNIFY — Dashboard Model
//  Provides all page-load data for the admin dashboard.
//  Queries are aligned with dashboard_controller.php.
// ============================================================

require_once __DIR__ . '/../../config/db.php';

// ── Stat counters ────────────────────────────────────────────
$totalMembers   = $pdo->query("SELECT COUNT(*) FROM members WHERE status='active'")->fetchColumn();
$activeClubs    = $pdo->query("SELECT COUNT(*) FROM clubs WHERE status='active'")->fetchColumn();
$upcomingEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND status='upcoming'")->fetchColumn();

// Leader applications: all pending admin-routed applications
$leaderApps = $pdo->query("
    SELECT COUNT(*) FROM applications
    WHERE status = 'pending'
      AND reviewer_type = 'admin'
")->fetchColumn();

// Pending club requests
$pendingClubRequests = $pdo->query("SELECT COUNT(*) FROM club_requests WHERE status='pending'")->fetchColumn();

// Pending event approvals
$pendingEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE status='pending_approval'")->fetchColumn();

// Total pending for bell badge and stat card
$totalAdminPending = (int)$leaderApps + (int)$pendingClubRequests + (int)$pendingEvents;

// ── Announcements ────────────────────────────────────────────
$dbAnnouncements = $pdo->query("
    SELECT a.*, c.name AS club_name
    FROM announcements a
    LEFT JOIN clubs c ON c.id = a.club_id
    ORDER BY a.posted_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ── Upcoming Events ──────────────────────────────────────────
$dbEvents = $pdo->query("
    SELECT e.*, c.name AS club_name
    FROM events e
    JOIN clubs c ON c.id = e.club_id
    WHERE e.event_date >= CURDATE() AND e.status = 'upcoming'
    ORDER BY e.event_date ASC, e.start_time ASC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ── Regular applicants (officer-reviewed, shown in applicants card) ──
$dbApplicants = $pdo->query("
    SELECT ap.id, ap.status, ap.applied_at, ap.reviewer_type,
           ap.course AS app_course,
           ap.year   AS app_year,
           ap.section AS app_section,
           ap.extras, ap.student_id_no, ap.phone AS app_phone,
           u.first_name, u.last_name, u.email,
           c.name AS club_name
    FROM applications ap
    JOIN users u ON u.id = ap.user_id
    JOIN clubs c ON c.id = ap.club_id
    WHERE ap.status = 'pending' AND ap.reviewer_type = 'officer'
    ORDER BY ap.applied_at DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// ── Leader applicants: all admin-routed pending applications ──
$dbLeaderApps = $pdo->query("
    SELECT ap.id, ap.status, ap.applied_at, ap.reviewer_type,
           ap.course AS app_course,
           ap.year   AS app_year,
           ap.section AS app_section,
           ap.extras, ap.student_id_no, ap.phone AS app_phone,
           u.first_name, u.last_name, u.email,
           c.name AS club_name,
           (
               SELECT GROUP_CONCAT(c2.name SEPARATOR ', ')
               FROM members m2
               JOIN clubs c2 ON c2.id = m2.club_id
               WHERE m2.user_id = ap.user_id
                 AND m2.role IN ('president','vice president','officer','lead')
                 AND m2.status = 'active'
           ) AS leader_in_clubs
    FROM applications ap
    JOIN users u ON u.id = ap.user_id
    JOIN clubs c ON c.id = ap.club_id
    WHERE ap.status = 'pending'
      AND ap.reviewer_type = 'admin'
    ORDER BY ap.applied_at DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// ── Pending club requests ────────────────────────────────────
$dbClubRequests = $pdo->query("
    SELECT cr.*, u.first_name, u.last_name, u.email
    FROM club_requests cr
    JOIN users u ON u.id = cr.user_id
    WHERE cr.status = 'pending'
    ORDER BY cr.created_at DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// ── Pending event approvals ──────────────────────────────────
$dbPendingEvents = $pdo->query("
    SELECT e.*, c.name AS club_name
    FROM events e
    JOIN clubs c ON c.id = e.club_id
    WHERE e.status = 'pending_approval'
    ORDER BY e.created_at DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// ── Helper functions ─────────────────────────────────────────
function relativeDate($ts) {
    $diff = time() - strtotime($ts);
    if ($diff < 86400)  return 'Today';
    if ($diff < 172800) return '1d ago';
    return floor($diff / 86400) . 'd ago';
}

function dotFromStatus($status) {
    return match($status) {
        'urgent'   => 'red',
        'approved' => 'green',
        'info'     => 'yellow',
        default    => 'blue',
    };
}