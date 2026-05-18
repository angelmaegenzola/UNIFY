<?php
// ============================================================
//  UNIFY — officer_home_model.php
//  app/models/officer_home_model.php
//  All DB queries for the officer home dashboard.
//  Uses PDO (same as all other officer controllers).
// ============================================================

function getOfficerHomeClub(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare("
        SELECT m.role, m.joined_at,
               c.id AS club_id, c.name AS club_name, c.acronym,
               c.category, c.description AS club_desc,
               c.logo_path, c.room, c.founded,
               COUNT(DISTINCT m2.id) AS member_count
        FROM members m
        JOIN clubs c ON c.id = m.club_id
        LEFT JOIN members m2 ON m2.club_id = c.id AND m2.status = 'active'
        WHERE m.user_id = :uid AND m.status = 'active'
          AND m.role IN ('officer','lead','president','vice president')
        GROUP BY m.id, c.id
        LIMIT 1
    ");
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getOfficerHomeStats(PDO $pdo, int $clubId): array {
    // Total active members
    $s1 = $pdo->prepare("SELECT COUNT(*) FROM members WHERE club_id=? AND status='active'");
    $s1->execute([$clubId]);
    $totalMembers = (int)$s1->fetchColumn();

    // Upcoming events (from today)
    $s2 = $pdo->prepare("SELECT COUNT(*) FROM events WHERE club_id=? AND event_date >= CURDATE() AND status='upcoming'");
    $s2->execute([$clubId]);
    $upcomingEvents = (int)$s2->fetchColumn();

    // Pending applications (officer queue)
    $s3 = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE club_id=? AND status='pending' AND reviewer_type='officer'");
    $s3->execute([$clubId]);
    $pendingApps = (int)$s3->fetchColumn();

    // Total announcements this club has posted
    $s4 = $pdo->prepare("SELECT COUNT(*) FROM announcements WHERE club_id=?");
    $s4->execute([$clubId]);
    $totalAnn = (int)$s4->fetchColumn();

    return [
        'total_members'   => $totalMembers,
        'upcoming_events' => $upcomingEvents,
        'pending_apps'    => $pendingApps,
        'total_ann'       => $totalAnn,
    ];
}

function getOfficerHomeAnnouncements(PDO $pdo, int $clubId, int $limit = 4): array {
    $stmt = $pdo->prepare("
        SELECT id, title, description, category, status, posted_at
        FROM announcements
        WHERE club_id = :cid
        ORDER BY posted_at DESC
        LIMIT :lim
    ");
    $stmt->bindValue(':cid', $clubId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOfficerHomeEvents(PDO $pdo, int $clubId, int $limit = 5): array {
    $stmt = $pdo->prepare("
        SELECT id, name, description, event_date, start_time, end_time, location, status
        FROM events
        WHERE club_id = :cid
          AND event_date >= CURDATE()
          AND status NOT IN ('cancelled', 'rejected', 'deleted', 'completed')
        ORDER BY event_date ASC, start_time ASC
        LIMIT :lim
    ");
    $stmt->bindValue(':cid', $clubId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOfficerHomeMembers(PDO $pdo, int $clubId, int $limit = 6): array {
    $stmt = $pdo->prepare("
        SELECT m.id, m.role, m.joined_at, m.course, m.year,
               u.id AS user_id, u.first_name, u.last_name, u.email
        FROM members m
        JOIN users u ON u.id = m.user_id
        WHERE m.club_id = :cid AND m.status = 'active'
        ORDER BY
            FIELD(m.role,'president','vice president','officer','lead','member') ASC,
            u.first_name ASC
        LIMIT :lim
    ");
    $stmt->bindValue(':cid', $clubId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOfficerHomePendingApps(PDO $pdo, int $clubId, int $limit = 4): array {
    $stmt = $pdo->prepare("
        SELECT ap.id, ap.applied_at, ap.course AS app_course, ap.extras,
               ap.student_id_no, ap.phone AS app_phone,
               u.id AS user_id, u.first_name, u.last_name, u.email
        FROM applications ap
        JOIN users u ON u.id = ap.user_id
        WHERE ap.club_id = :cid AND ap.status = 'pending' AND ap.reviewer_type = 'officer'
        ORDER BY ap.applied_at ASC
        LIMIT :lim
    ");
    $stmt->bindValue(':cid', $clubId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOfficerHomeNotifCount(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function getOfficerHomeRecentActivity(PDO $pdo, int $clubId, int $limit = 5): array {
    // Merge announcements + events into a unified activity feed
    $stmt = $pdo->prepare("
        (
            SELECT 'announcement' AS type, id, title AS label,
                   posted_at AS activity_at
            FROM announcements WHERE club_id = :cid
        )
        UNION ALL
        (
            SELECT 'event' AS type, id, name AS label,
                   created_at AS activity_at
            FROM events WHERE club_id = :cid2
              AND status NOT IN ('cancelled', 'rejected', 'deleted')
        )
        ORDER BY activity_at DESC
        LIMIT :lim
    ");
    $stmt->bindValue(':cid',  $clubId, PDO::PARAM_INT);
    $stmt->bindValue(':cid2', $clubId, PDO::PARAM_INT);
    $stmt->bindValue(':lim',  $limit,  PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}