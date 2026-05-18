<?php
// Model: page-load queries for profile

require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt = $pdo->prepare(
    'SELECT c.name, c.category, m.role
     FROM members m
     JOIN clubs c ON c.id = m.club_id
     WHERE m.user_id = ? AND m.status = "active"'
);
$stmt = $pdo->prepare('SELECT COUNT(*) FROM members WHERE user_id = ? AND status = "active"');
$stmt = $pdo->prepare('SELECT COUNT(*) FROM event_attendees WHERE user_id = ?');
$stmt = $pdo->prepare('SELECT COUNT(*) FROM announcements WHERE club_id IN (SELECT club_id FROM members WHERE user_id = ?)');
