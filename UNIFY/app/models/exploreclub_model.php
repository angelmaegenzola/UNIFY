<?php
/* ============================================================
   UNIFY — ExploreClubModel.php
   Path: app/models/ExploreClubModel.php
   Handles all DB queries for the Explore Clubs page.
============================================================ */

class ExploreClubModel {

    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /* ── All active clubs with member count & upcoming events ── */
    public function getAllClubs(): array {
        $sql = "
            SELECT
                c.id,
                c.name,
                c.acronym,
                c.category,
                c.description,
                c.logo_path,
                c.room,
                c.founded,
                c.status,
                COUNT(DISTINCT CASE WHEN m.status = 'active' THEN m.id END) AS member_count,
                COUNT(DISTINCT CASE
                    WHEN e.event_date >= CURDATE()
                    AND  e.status NOT IN ('completed','cancelled')
                    THEN e.id END) AS upcoming_events
            FROM clubs c
            LEFT JOIN members m ON m.club_id = c.id
            LEFT JOIN events  e ON e.club_id = c.id
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY c.name ASC
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /* ── Single club detail (for the modal) ─────────────────── */
    public function getClubById(int $clubId): ?array {
        $stmt = $this->db->prepare("
            SELECT
                c.id, c.name, c.acronym, c.category,
                c.description, c.logo_path, c.room, c.founded, c.status,
                COUNT(DISTINCT CASE WHEN m.status='active' THEN m.id END) AS member_count
            FROM clubs c
            LEFT JOIN members m ON m.club_id = c.id
            WHERE c.id = ?
            GROUP BY c.id
        ");
        $stmt->bind_param('i', $clubId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /* ── Officers of a specific club (for modal) ────────────── */
    public function getClubOfficers(int $clubId): array {
        $stmt = $this->db->prepare("
            SELECT
                u.first_name, u.last_name,
                m.role
            FROM members m
            JOIN users u ON u.id = m.user_id
            WHERE m.club_id = ?
              AND m.status  = 'active'
              AND m.role NOT IN ('member','lead')
            ORDER BY FIELD(m.role,'president','vice president','officer') ASC
            LIMIT 6
        ");
        $stmt->bind_param('i', $clubId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /* ── Upcoming events for a specific club (for modal) ─────── */
    public function getClubUpcomingEvents(int $clubId): array {
        $stmt = $this->db->prepare("
            SELECT name, event_date, start_time, location
            FROM events
            WHERE club_id   = ?
              AND event_date >= CURDATE()
              AND status NOT IN ('completed','cancelled')
            ORDER BY event_date ASC
            LIMIT 3
        ");
        $stmt->bind_param('i', $clubId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /* ── All distinct categories (for filter pills) ──────────── */
    public function getCategories(): array {
        $result = $this->db->query("
            SELECT DISTINCT category
            FROM clubs
            WHERE status = 'active' AND category IS NOT NULL
            ORDER BY category ASC
        ");
        $cats = [];
        while ($row = $result->fetch_assoc()) {
            $cats[] = $row['category'];
        }
        return $cats;
    }

    /* ── Check if a user already has an active membership ──────
       Officers always return TRUE (they can never apply)        */
    public function getUserActiveClubId(int $userId): ?int {
        $stmt = $this->db->prepare("
            SELECT club_id FROM members
            WHERE user_id = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? (int)$row['club_id'] : null;
    }
}