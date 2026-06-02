<?php
// Model: page-load queries for adminprofile

require_once __DIR__ . '/../../config/db.php';

$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$eventsManaged = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$clubsCount    = $pdo->query("SELECT COUNT(*) FROM clubs WHERE status='active'")->fetchColumn();
$totalAnns     = $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
$clubsStmt = $pdo->query("SELECT name, category FROM clubs WHERE status='active' ORDER BY name LIMIT 6");
