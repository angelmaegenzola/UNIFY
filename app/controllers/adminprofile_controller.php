<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../app/models/adminprofile_model.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: index.php?page=login');
  exit;
}

$userId = $_SESSION['user_id'];

// ── Fetch user from DB ────────────────────────────────────
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$dbUser = $userStmt->fetch(PDO::FETCH_ASSOC);
if (!$dbUser) { header('Location: index.php?page=logout'); exit; }

$success_msg = '';
$error_msg   = '';

// ── Handle POST actions ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'update_profile') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name']  ?? '');
    $email     = trim($_POST['email']      ?? '');
    $username  = trim($_POST['username']   ?? '');
    $phone     = trim($_POST['phone']      ?? '');

    if ($firstName && $lastName && $email) {
      $upd = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=?, phone=? WHERE id=?");
      $upd->execute([$firstName, $lastName, $email, $username, $phone, $userId]);
      $_SESSION['first_name'] = $firstName;
      $_SESSION['last_name']  = $lastName;
      $dbUser['first_name']   = $firstName;
      $dbUser['last_name']    = $lastName;
      $dbUser['email']        = $email;
      $dbUser['username']     = $username;
      $dbUser['phone']        = $phone;
      $success_msg = 'Profile updated successfully.';
    } else {
      $error_msg = 'First name, last name, and email are required.';
    }

  } elseif ($action === 'update_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
      $error_msg = 'All password fields are required.';
    } elseif (!password_verify($current, $dbUser['password_hash'])) {
      $error_msg = 'Current password is incorrect.';
    } elseif ($new !== $confirm) {
      $error_msg = 'New passwords do not match.';
    } elseif (strlen($new) < 8) {
      $error_msg = 'Password must be at least 8 characters.';
    } else {
      $hash = password_hash($new, PASSWORD_DEFAULT);
      $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $userId]);
      $success_msg = 'Password changed successfully.';
    }

  } elseif ($action === 'update_notifications') {
    $success_msg = 'Notification preferences saved.';

  } elseif ($action === 'delete_account') {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$userId]);
    session_destroy();
    header('Location: index.php?page=login');
    exit;
  }
}

// ── Build display variables ───────────────────────────────
$adminFirst   = $dbUser['first_name'];
$adminLast    = $dbUser['last_name'];
$adminName    = trim($adminFirst . ' ' . $adminLast);
$adminInitial = strtoupper(substr($adminFirst, 0, 1));

// ── Avatar URL ────────────────────────────────────────────
$profile_picture = $dbUser['profile_picture'] ?? '';
$avatar_url      = $profile_picture
    ? '/public/assets/pictures/profile_pictures/' . htmlspecialchars(basename($profile_picture))
    : '';

// Keep session in sync so other pages' sidebars can show the avatar
$_SESSION['profile_picture'] = $profile_picture;

// Stats from DB
$eventsManaged = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$clubsCount    = $pdo->query("SELECT COUNT(*) FROM clubs WHERE status='active'")->fetchColumn();
$totalAnns     = $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();

$user = [
  'first_name'     => $dbUser['first_name'],
  'last_name'      => $dbUser['last_name'],
  'username'       => $dbUser['username'],
  'email'          => $dbUser['email'],
  'phone'          => $dbUser['phone'] ?? '',
  'role'           => 'Club Admin',
  'status'         => 'Active',
  'joined'         => date('F Y', strtotime($dbUser['created_at'])),
  'events_managed' => $eventsManaged,
  'clubs_joined'   => $clubsCount,
  'announcements'  => $totalAnns,
];

// Clubs the admin oversees
$clubsStmt = $pdo->query("SELECT name, category FROM clubs WHERE status='active' ORDER BY name LIMIT 6");
$clubs = array_map(fn($c) => [
  'name'  => $c['name'],
  'role'  => 'Admin',
  'icon'  => 'fa-building-columns',
  'color' => '#2a7a48',
  'badge' => 'cbadge-admin',
], $clubsStmt->fetchAll(PDO::FETCH_ASSOC));

// Recent activity (static for now)
$activity = [
  ['icon' => 'fa-calendar-plus', 'dot' => 'dot-green',  'title' => 'Created event "Financial Literacy Seminar"',    'meta' => 'Business Management Club', 'time' => 'Today, 9:12 AM'],
  ['icon' => 'fa-circle-check',  'dot' => 'dot-green',  'title' => 'Approved "Campus Cleanup Drive"',               'meta' => 'Environmental Society',    'time' => 'Yesterday, 3:40 PM'],
  ['icon' => 'fa-bullhorn',      'dot' => 'dot-gold',   'title' => 'Posted announcement "Meeting Reminder"',        'meta' => 'All Clubs',                'time' => 'Mar 30, 11:00 AM'],
  ['icon' => 'fa-user-plus',     'dot' => 'dot-blue',   'title' => 'Approved membership for Neeru Abraham',         'meta' => 'CS Society',               'time' => 'Mar 28, 2:15 PM'],
  ['icon' => 'fa-xmark-circle',  'dot' => 'dot-red',    'title' => 'Rejected event "Unauthorized Off-Campus Trip"', 'meta' => 'Student Admin',            'time' => 'Mar 27, 10:05 AM'],
  ['icon' => 'fa-pen-to-square', 'dot' => 'dot-orange', 'title' => 'Updated profile information',                   'meta' => 'Account Settings',         'time' => 'Mar 25, 4:50 PM'],
];