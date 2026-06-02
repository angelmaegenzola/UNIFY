<?php
// Model: page-load queries for reports
// All queries wrapped safely — a missing table returns 0 instead of crashing.

require_once __DIR__ . '/../../config/db.php';

function rq(PDO $pdo, string $sql, int $default = 0): int {
    try { return (int) $pdo->query($sql)->fetchColumn(); }
    catch (Throwable $e) { return $default; }
}

function rqa(PDO $pdo, string $sql): array {
    try { return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); }
    catch (Throwable $e) { return []; }
}

$totalClubs   = rq($pdo, "SELECT COUNT(*) FROM clubs WHERE status='active'");
$totalMembers = rq($pdo, "SELECT COUNT(*) FROM members WHERE status='active'");
$totalEvents  = rq($pdo, "SELECT COUNT(*) FROM events");
$completedEvt = rq($pdo, "SELECT COUNT(*) FROM events WHERE status='completed'");
$upcomingEvt  = rq($pdo, "SELECT COUNT(*) FROM events WHERE status='upcoming'");
$cancelledEvt = rq($pdo, "SELECT COUNT(*) FROM events WHERE status='cancelled'");

// applications table may not exist — safely defaults to 0
$pendingApps  = rq($pdo, "SELECT COUNT(*) FROM applications WHERE status='pending'");

$clubActivity = rqa($pdo, "
  SELECT c.id, c.name, c.acronym,
    COUNT(DISTINCT e.id)           AS event_count,
    COUNT(DISTINCT m.id)           AS member_count,
    SUM(e.status='completed')      AS completed_events,
    SUM(e.status='upcoming')       AS upcoming_events
  FROM clubs c
  LEFT JOIN events  e ON e.club_id = c.id
  LEFT JOIN members m ON m.club_id = c.id AND m.status = 'active'
  WHERE c.status = 'active'
  GROUP BY c.id, c.name, c.acronym
  ORDER BY event_count DESC, member_count DESC
");

$recentApps = rqa($pdo, "
  SELECT a.status, a.applied_at, u.first_name, u.last_name, c.name AS club_name
  FROM applications a
  JOIN users u ON u.id = a.user_id
  JOIN clubs c ON c.id = a.club_id
  ORDER BY a.applied_at DESC LIMIT 8
");

$totalIncome    = 0;
$totalExpense   = 0;
$cancelledTasks = $cancelledEvt;