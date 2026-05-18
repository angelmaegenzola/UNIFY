<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';
$clubs = $pdo->query(
    'SELECT c.*,
        (SELECT COUNT(*) FROM members m WHERE m.club_id = c.id AND m.status = "active") AS member_count,
        (SELECT COUNT(*) FROM events  e WHERE e.club_id = c.id AND e.status = "upcoming" AND e.event_date >= CURDATE()) AS event_count
     FROM clubs c
     ORDER BY c.name ASC'
)->fetchAll(PDO::FETCH_ASSOC);
$officersMap  = [];
$upcomingMap  = [];

foreach ($clubs as $club) {
    $cid = $club['id'];

    $stmt = $pdo->prepare(
        'SELECT u.first_name, u.last_name, m.role
         FROM members m
         JOIN users u ON u.id = m.user_id
         WHERE m.club_id = ? AND m.role IN ("president","vice president","officer") AND m.status = "active"
         ORDER BY FIELD(m.role,"president","vice president","officer")
         LIMIT 4'
    );
    $stmt->execute([$cid]);
    $officersMap[$cid] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        'SELECT name, event_date, start_time, end_time
         FROM events
         WHERE club_id = ? AND status = "upcoming" AND event_date >= CURDATE()
         ORDER BY event_date ASC
         LIMIT 3'
    );
    $stmt->execute([$cid]);
    $upcomingMap[$cid] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$total   = count($clubs);
$active  = count(array_filter($clubs, fn($c) => $c['status'] === 'active'));
$pending = count(array_filter($clubs, fn($c) => $c['status'] === 'pending'));