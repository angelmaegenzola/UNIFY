<?php
// Model: page-load queries for officer_dashboard

require_once __DIR__ . '/../../config/db.php';

function getOfficerClub($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT m.club_id, m.role, c.name AS club_name, c.description AS club_desc,
               c.category, c.status AS club_status, c.logo_path
        FROM members m
        JOIN clubs c ON c.id = m.club_id
        WHERE m.user_id = :uid AND m.role IN ('officer','lead','president','vice president') AND m.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTotalMembers($pdo, $clubId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE club_id = :cid AND status = 'active'");
    $stmt->execute([':cid' => $clubId]);
    return (int) $stmt->fetchColumn();
}

function getUpcomingEventsCount($pdo, $clubId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE club_id = :cid AND event_date >= CURDATE() AND status = 'upcoming'");
    $stmt->execute([':cid' => $clubId]);
    return (int) $stmt->fetchColumn();
}

function getPendingAppsCount($pdo, $clubId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE club_id = :cid AND status = 'pending' AND reviewer_type = 'officer'");
    $stmt->execute([':cid' => $clubId]);
    return (int) $stmt->fetchColumn();
}

function getTotalAnnouncements($pdo, $clubId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM announcements WHERE club_id = :cid");
    $stmt->execute([':cid' => $clubId]);
    return (int) $stmt->fetchColumn();
}

function getUnreadNotifCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
    $stmt->execute([':uid' => $userId]);
    return (int) $stmt->fetchColumn();
}

function getUnreadChatCount($pdo, $userId, $clubId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM club_messages
        WHERE club_id = :cid
          AND sender_id != :uid
          AND is_deleted = 0
          AND sent_at > COALESCE(
            (SELECT last_read FROM club_message_reads WHERE user_id = :uid2 AND club_id = :cid2),
            '2000-01-01'
          )
    ");
    $stmt->execute([':cid' => $clubId, ':uid' => $userId, ':uid2' => $userId, ':cid2' => $clubId]);
    return (int) $stmt->fetchColumn();
}

function getAnnouncements($pdo, $clubId) {
    $stmt = $pdo->prepare("
        SELECT * FROM announcements WHERE club_id = :cid ORDER BY posted_at DESC LIMIT 10
    ");
    $stmt->execute([':cid' => $clubId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUpcomingEvents($pdo, $clubId) {
    $stmt = $pdo->prepare("
        SELECT * FROM events
        WHERE club_id = :cid AND event_date >= CURDATE() AND status = 'upcoming'
        ORDER BY event_date ASC, start_time ASC LIMIT 6
    ");
    $stmt->execute([':cid' => $clubId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClubMembers($pdo, $clubId) {
    $stmt = $pdo->prepare("
        SELECT m.id, m.role, m.joined_at, m.course, m.year, m.section,
               u.first_name, u.last_name, u.email
        FROM members m
        JOIN users u ON u.id = m.user_id
        WHERE m.club_id = :cid AND m.status = 'active'
        ORDER BY FIELD(m.role,'president','vice president','officer','lead','member'), m.joined_at ASC
        LIMIT 8
    ");
    $stmt->execute([':cid' => $clubId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPendingApplicants($pdo, $clubId) {
    $stmt = $pdo->prepare("
        SELECT ap.id, ap.status, ap.applied_at, ap.reviewer_type,
               ap.course AS app_course, ap.year, ap.section,
               ap.extras, ap.student_id_no, ap.phone AS app_phone,
               u.first_name, u.last_name, u.email
        FROM applications ap
        JOIN users u ON u.id = ap.user_id
        WHERE ap.club_id = :cid AND ap.status = 'pending' AND ap.reviewer_type = 'officer'
        ORDER BY ap.applied_at DESC LIMIT 20
    ");
    $stmt->execute([':cid' => $clubId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}