<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';

$me = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$rows = $pdo->query(
    'SELECT m.id, m.course, m.year, m.section, m.role, m.status, m.joined_at,
            u.id AS user_id, u.first_name, u.last_name, u.email,
            c.name AS club_name, c.id AS club_id
     FROM members m
     JOIN users u ON u.id = m.user_id
     JOIN clubs c ON c.id = m.club_id
     ORDER BY u.last_name, u.first_name, m.joined_at DESC'
)->fetchAll();
$clubs = $pdo->query('SELECT id, name FROM clubs WHERE status="active" ORDER BY name')->fetchAll();
$users = $pdo->query('SELECT id, first_name, last_name, username FROM users ORDER BY first_name')->fetchAll();
