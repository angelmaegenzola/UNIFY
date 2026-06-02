<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: index.php?page=login');
  exit;
}

// ── Admin sidebar variables ───────────────────────────────
$adminStmt = $pdo->prepare('SELECT first_name, last_name FROM users WHERE id = ?');
$adminStmt->execute([$_SESSION['user_id']]);
$adminRow = $adminStmt->fetch(PDO::FETCH_ASSOC);
$adminFirst   = $adminRow['first_name'] ?? 'Admin';
$adminLast    = $adminRow['last_name']  ?? '';
$adminName    = trim($adminFirst . ' ' . $adminLast);
$adminInitial = strtoupper(substr($adminFirst, 0, 1));
$_sessionPic  = $_SESSION['profile_picture'] ?? '';
$avatar_url   = $_sessionPic
    ? '/assets/pictures/profile_pictures/' . htmlspecialchars(basename($_sessionPic))
    : '';

// ── All data comes from the model (with safe fallbacks) ───
require_once __DIR__ . '/../../app/models/reports_model.php';

// ── Derived variables ─────────────────────────────────────
$topClubs       = array_slice($clubActivity, 0, 5);
$needsAttention = array_slice(array_values(array_filter(
    $clubActivity, fn($c) => $c['event_count'] == 0 || $c['member_count'] < 5
)), 0, 3);

$completedTasks  = $completedEvt;
$inProgressTasks = $upcomingEvt;
$overdueTasks    = max(0, (int)($totalEvents * 0.1));
$totalTasks      = $completedTasks + $inProgressTasks + $overdueTasks + $cancelledTasks;
$taskPct         = $totalTasks > 0 ? round($completedTasks / $totalTasks * 100) : 0;


$avatarColors = ['ca-green','ca-gold','ca-orange','ca-red','ca-teal','ca-blue','ca-green','ca-gold'];

// Unread notifications count for admin
$adminUnreadNotifs = 0;
try {
    $nStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND is_read=0");
    $nStmt->execute([':uid' => $_SESSION['user_id']]);
    $adminUnreadNotifs = (int) $nStmt->fetchColumn();
} catch (Exception $e) { $adminUnreadNotifs = 0; }