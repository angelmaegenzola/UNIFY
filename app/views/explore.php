<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/UNIFY(db)/app/controllers/explore_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Explore Clubs</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
    rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/UNIFY(db)/public/assets/css/explore.css" />
</head>

<body>
  <div class="app">

    <aside class="sidebar">
      <div class="sidebar-brand">
        <img src="/UNIFY(db)/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
        <div class="brand-text">
          <div class="brand-name">UNIFY</div>
          <div class="brand-tagline">Club Management System</div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">STUDENT MENU</div>
        <a href="index.php?page=explore" class="nav-item active"><i class="fas fa-compass"></i><span>Explore
            Clubs</span></a>
        <div class="nav-section-label">MY SPACE</div>
        <?php if ($has_club): ?>
          <a href="index.php?page=studenthome" class="nav-item"><i class="fas fa-house"></i><span>Home</span></a>
          <a href="index.php?page=myclubs" class="nav-item"><i class="fas fa-users"></i><span>My Clubs</span></a>
          <a href="index.php?page=studentevents" class="nav-item"><i
              class="fas fa-calendar-days"></i><span>Events</span></a>
          <a href="index.php?page=student_messages" class="nav-item"><i class="fas fa-comments"></i><span>Club
              Chat</span></a>
        <?php else: ?>
          <a href="#" class="nav-item locked" onclick="return false;"><i class="fas fa-house"></i><span>Home</span><i
              class="fas fa-lock nav-lock-icon"></i></a>
          <a href="#" class="nav-item locked" onclick="return false;"><i class="fas fa-users"></i><span>My Clubs</span><i
              class="fas fa-lock nav-lock-icon"></i></a>
          <a href="#" class="nav-item locked" onclick="return false;"><i
              class="fas fa-calendar-days"></i><span>Events</span><i class="fas fa-lock nav-lock-icon"></i></a>
          <a href="#" class="nav-item locked" onclick="return false;"><i class="fas fa-comments"></i><span>Club
              Chat</span><i class="fas fa-lock nav-lock-icon"></i></a>
        <?php endif; ?>
      </nav>

      

      <div class="sidebar-bottom">
        <div class="sidebar-profile">
          <div class="profile-avatar-wrap">
  <?php if (!empty($avatar_url)): ?>
    <img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" />
  <?php else: ?>
    <span class="profile-avatar-fallback"><?= $avatar ?></span>
  <?php endif; ?>
  <span class="profile-online-dot"></span>
</div>
          <a href="index.php?page=studentprofile" class="profile-link">
            <div class="profile-info"><span class="profile-name"><?= $full_name ?></span><span
                class="profile-role">Student</span></div>
          </a>
          <a href="index.php?page=logout" class="sidebar-logout" title="Logout"><i
              class="fas fa-arrow-right-from-bracket"></i></a>
        </div>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <div class="topbar-left">
          <span class="topbar-page-title">Explore Clubs</span>
          <span class="topbar-date" id="topbarDate"></span>
        </div>
        <div class="topbar-center">
          <div class="topbar-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search clubs by name or category…"
              oninput="filterClubs()" />
          </div>
        </div>
        <div class="topbar-actions">
          <button class="icon-btn" id="notifBtn" title="Notifications" onclick="toggleNotif(event)">
            <i class="fas fa-bell"></i><span class="badge red hidden" id="notifBadge">0</span>
          </button>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header"><span class="notif-header-title"><i class="fas fa-bell"></i>
                Notifications</span><button class="notif-mark-btn" onclick="clearNotifs()">Mark all read</button></div>
            <div class="notif-list" id="notifList">
              <div class="notif-item">
                <div class="notif-content"><span class="notif-text">No new notifications.</span></div>
              </div>
            </div>
            <div class="notif-footer">Only showing recent notifications</div>
          </div>
          <a href="index.php?page=studentprofile" class="topbar-profile" title="View Profile">
            <div class="topbar-avatar">
              <?php if (!empty($avatar_url)): ?>
                <img src="<?= $avatar_url ?>" alt="Avatar"
                  style="width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;" />
              <?php else: ?>
                <?= $avatar ?>
              <?php endif; ?>
            </div>
            <div class="topbar-profile-info"><span class="tp-name"><?= $full_name ?></span><span
                class="tp-role">Student</span></div>
            <i class="fas fa-chevron-down tp-caret"></i>
          </a>
        </div>
      </header>

      <?php if (isset($_GET['welcome']) && $_GET['welcome'] === '1'): ?>
        <div id="welcomeToast" style="
        position:fixed;top:24px;right:24px;z-index:9999;
        background:linear-gradient(135deg,#1a5c38,#2d8a57);
        color:#fff;padding:16px 22px;border-radius:16px;
        box-shadow:0 8px 32px rgba(13,43,26,.25);
        display:flex;align-items:center;gap:12px;
        font-family:inherit;font-size:14px;font-weight:600;
        animation:slideInToast .4s cubic-bezier(.34,1.56,.64,1);
        max-width:320px;">
          <i class="fas fa-party-horn" style="font-size:1.4rem;"></i>
          <div>
            <div style="font-size:15px;font-weight:800;margin-bottom:2px;">Welcome to UNIFY! 🎉</div>
            <div style="font-weight:400;opacity:.88;font-size:13px;">Your account is ready. Explore clubs or propose your
              own!</div>
          </div>
          <button onclick="document.getElementById('welcomeToast').remove()"
            style="background:none;border:none;color:#fff;cursor:pointer;padding:0 0 0 8px;font-size:16px;opacity:.7;">&times;</button>
        </div>

      <?php endif; ?>

      <?php if (!$has_club): ?>
        <div class="onboarding-banner">
          <div class="ob-text">
            <div class="ob-title">Welcome to UNIFY, <?= $first_name ?>! 👋</div>
            <div class="ob-sub">You're not in a club yet. Apply to an existing club below, or propose your own — once
              accepted, you unlock full access.</div>
          </div>
          <div class="ob-steps">
            <div class="ob-step done"><i class="fas fa-check"></i> Sign Up</div>
            <div class="ob-step-arrow"><i class="fas fa-chevron-right"></i></div>
            <div class="ob-step active"><i class="fas fa-compass"></i> Explore</div>
            <div class="ob-step-arrow"><i class="fas fa-chevron-right"></i></div>
            <div class="ob-step"><i class="fas fa-paper-plane"></i> Apply / Propose</div>
            <div class="ob-step-arrow"><i class="fas fa-chevron-right"></i></div>
            <div class="ob-step"><i class="fas fa-door-open"></i> Access</div>
          </div>
        </div>
      <?php endif; ?>

      <div class="filter-bar">
        <div style="display:flex;align-items:center;gap:12px;">
          <div class="filter-label"><i class="fas fa-filter"></i> Filter by:</div>
          <div class="filter-pills" id="filterPills">
            <button class="pill active" data-cat="all" onclick="setCat(this,'all')">All</button>
            <?php foreach ($categories as $cat):
              $icon = $cat_icons[$cat] ?? 'fa-tag'; ?>
              <button class="pill" data-cat="<?= htmlspecialchars($cat) ?>"
                onclick="setCat(this,'<?= htmlspecialchars($cat) ?>')">
                <i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($cat) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <a href="index.php?page=club_request"
          style="display:inline-flex;align-items:center;gap:6px;color:#1a5c38;font-size:13px;font-weight:700;text-decoration:none;">
          <i class="fas fa-plus-circle"></i> Propose a Club
        </a>
      </div>

      <div class="clubs-grid" id="clubsGrid">
        <?php foreach ($clubs as $club):
          $c_id = $club['id'];
          $name = htmlspecialchars($club['name']);
          $acronym = htmlspecialchars($club['acronym'] ?? $club['name']);
          $category = htmlspecialchars($club['category'] ?? 'General');
          $desc = htmlspecialchars($club['description'] ?? '');
          $logo = $club['logo_path'] ? htmlspecialchars($club['logo_path']) : '';
          $room = htmlspecialchars($club['room'] ?? 'TBA');
          $founded = htmlspecialchars($club['founded'] ?? 'N/A');
          $members = (int) $club['member_count'];
          $events = (int) $club['event_count'];
          $cat_lower = strtolower($category);
          $icon = $cat_icons[$club['category']] ?? 'fa-tag';
          $pending = in_array((int) $c_id, $applied_ids);
          ?>
          <article class="club-card cat-<?= $cat_lower ?>" data-cat="<?= $category ?>" data-name="<?= $name ?>"
            data-logo="<?= $logo ?>" data-desc="<?= $desc ?>" data-members="<?= $members ?>" data-events="<?= $events ?>"
            data-founded="<?= $founded ?>" data-room="<?= $room ?>" data-acronym="<?= $acronym ?>"
            data-id="<?= (int) $c_id ?>">
            <div class="cc-hero">
              <div class="cc-hero-bg"></div>
              <div class="cc-logo-wrap">
                <?php if ($logo): ?>
                  <img class="cc-logo" src="<?= $logo ?>" alt="<?= $acronym ?>"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                  <div class="cc-logo-fallback" style="display:none"><i class="fas <?= $icon ?>"></i></div>
                <?php else: ?>
                  <div class="cc-logo-fallback" style="display:flex"><i class="fas <?= $icon ?>"></i></div>
                <?php endif; ?>
              </div>
              <div class="cc-member-badge"><i class="fas fa-users"></i><span
                  class="cc-member-count"><?= $members ?></span><span
                  class="cc-member-label">member<?= $members === 1 ? '' : 's' ?></span></div>
              <span class="cc-category <?= $cat_lower ?>"><i class="fas <?= $icon ?>"></i> <?= $category ?></span>
            </div>
            <div class="cc-body">
              <div class="cc-name-row">
                <h3 class="cc-name"><?= $name ?></h3><span class="cc-acronym"><?= $acronym ?></span>
              </div>
              <p class="cc-desc"><?= $desc ?: 'No description available.' ?></p>
              <div class="cc-stats">
                <div class="cc-stat"><span class="cc-stat-num"><?= $members ?></span><span
                    class="cc-stat-label">Members</span></div>
                <div class="cc-stat-sep"></div>
                <div class="cc-stat"><span class="cc-stat-num"><?= $events ?></span><span
                    class="cc-stat-label">Events</span></div>
                <div class="cc-stat-sep"></div>
                <div class="cc-stat"><span class="cc-stat-num"><?= $founded ?></span><span
                    class="cc-stat-label">Founded</span></div>
              </div>
              <div class="cc-room"><i class="fas fa-location-dot"></i> <?= $room ?></div>
            </div>
            <div class="cc-footer">
              <span class="cc-status open"><i class="fas fa-circle-dot"></i> Open</span>
              <?php if (in_array((int) $c_id, $member_club_ids)): ?>
                <a class="cc-apply-btn visit-btn" href="index.php?page=myclubs&club_id=<?= (int) $c_id ?>">
                  <i class="fas fa-door-open"></i> Visit Club
                </a>
              <?php elseif ($pending): ?>
                <button class="cc-apply-btn applied" disabled><i class="fas fa-hourglass-half"></i> Pending</button>
              <?php else: ?>
                <button class="cc-apply-btn" onclick="openApply(this)">
                  <i class="fas fa-paper-plane"></i> Apply
                </button>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="empty-state" id="emptyState" style="display:none">
        <i class="fas fa-face-meh-blank"></i>
        <p>No clubs match your search.</p><span>Try a different keyword or category.</span>
      </div>
    </main>
  </div>

  <!-- ═══ APPLY MODAL ═══ -->
  <div class="modal-overlay" id="applyOverlay" onclick="closeApply(event)">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-header-left">
          <img class="modal-club-logo" id="modalLogo" src="" alt="">
          <div>
            <div class="modal-title" id="modalClubName">Club Name</div>
            <div class="modal-subtitle">Membership Application</div>
          </div>
        </div>
        <button class="modal-close" onclick="closeApply()"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="modal-info-strip" id="modalInfoStrip">
          <span><i class="fas fa-users"></i> <span id="mInfoMembers"></span> members</span>
          <span><i class="fas fa-calendar-days"></i> <span id="mInfoEvents"></span> events</span>
          <span><i class="fas fa-location-dot"></i> <span id="mInfoRoom"></span></span>
          <span><i class="fas fa-seedling"></i> Founded <span id="mInfoFounded"></span></span>
        </div>
        <div class="modal-about">
          <div class="field-label">About this Club</div>
          <p id="modalDesc"></p>
        </div>
        <div class="modal-divider"></div>
        <div class="field-label" style="margin-bottom:12px;">Your Application</div>
        <input type="hidden" id="fClubId" value="" />
        <div class="modal-fields">
          <div class="field-group">
            <label class="field-label">Full Name <span class="field-required">*</span></label>
            <input class="field-input" id="fName" type="text" value="<?= $full_name ?>" readonly />
          </div>
          <div class="field-group">
            <label class="field-label">Student ID <span class="field-required">*</span></label>
            <input class="field-input" id="fStudentId" type="text" placeholder="e.g. 2023-00123"
              value="<?= $prefill_student_id ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Course <span class="field-required">*</span></label>
            <input class="field-input" id="fCourse" type="text" placeholder="e.g. BSIT"
              value="<?= $prefill_course ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Contact No. <span class="field-required">*</span></label>
            <input class="field-input" id="fPhone" type="text" placeholder="e.g. 09171234567"
              value="<?= $prefill_phone ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Year Level <span class="field-required">*</span></label>
            <input class="field-input" id="fYear" type="text" placeholder="e.g. 2nd Year"
              value="<?= $prefill_year ?>" />
          </div>
          <div class="field-group">
            <label class="field-label">Section <span class="field-required">*</span></label>
            <input class="field-input" id="fSection" type="text" placeholder="e.g. C" value="<?= $prefill_section ?>" />
          </div>
          <div class="field-group field-full">
            <label class="field-label">Why do you want to join? <span class="field-required">*</span></label>
            <textarea class="field-input field-textarea" id="fReason"
              placeholder="Tell the club why you'd like to become a member…"></textarea>
          </div>
          <div class="field-group field-full">
            <label class="field-label">Any relevant skills or experience? <span
                class="field-optional">(optional)</span></label>
            <textarea class="field-input field-textarea" id="fSkills"
              placeholder="e.g. graphic design, coding, event organization…"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <span class="already-link"
          onclick="closeApply(); openAlreadyMember(document.getElementById('fClubId').value, document.getElementById('modalClubName').textContent)">
          Already a member of this club?
        </span>
        <div style="display:flex; gap:10px;">
          <button class="modal-btn-cancel" onclick="closeApply()">Cancel</button>
          <button class="modal-btn-submit" onclick="submitApplication()"><i class="fas fa-paper-plane"></i> Submit
            Application</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══ ALREADY A MEMBER MODAL ═══ -->
  <div class="modal-overlay" id="alreadyMemberOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-header-left">
          <div>
            <div class="modal-title" id="amClubName"></div>
            <div class="modal-subtitle">Already a Member Registration</div>
          </div>
        </div>
        <button class="modal-close" onclick="closeAlreadyMember()"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="amClubId" />
        <div class="modal-fields">
          <div class="field-group">
            <label class="field-label">First Name <span class="field-required">*</span></label>
            <input class="field-input" id="amFirstName" type="text" placeholder="Your first name" />
          </div>
          <div class="field-group">
            <label class="field-label">Last Name <span class="field-required">*</span></label>
            <input class="field-input" id="amLastName" type="text" placeholder="Your last name" />
          </div>
          <div class="field-group">
            <label class="field-label">Course <span class="field-required">*</span></label>
            <input class="field-input" id="amCourse" type="text" placeholder="e.g. BSIT" />
          </div>
          <div class="field-group">
            <label class="field-label">Year Level <span class="field-required">*</span></label>
            <input class="field-input" id="amYear" type="text" placeholder="e.g. 2nd Year" />
          </div>
          <div class="field-group field-full">
            <label class="field-label">Role in Club <span class="field-required">*</span></label>
            <select class="field-input" id="amRole">
              <option value="member">Member</option>
              <option value="officer">Officer</option>
              <option value="lead">Lead</option>
              <option value="vice president">Vice President</option>
              <option value="president">President</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="modal-btn-cancel" onclick="closeAlreadyMember()">Cancel</button>
        <button class="modal-btn-submit" onclick="submitAlreadyMember()"><i class="fas fa-user-check"></i> Register as
          Member</button>
      </div>
    </div>
  </div>

  <!-- ═══ SUCCESS MODAL ═══ -->
  <div class="modal-overlay" id="successOverlay">
    <div class="modal-box success-box">
      <div class="success-icon"><i class="fas fa-circle-check"></i></div>
      <div class="success-title">Application Sent!</div>
      <div class="success-msg">Your application to <strong id="successClubName"></strong> has been submitted. You'll be
        notified once the club admin reviews it.</div>
      <div class="success-steps">
        <div class="ss-step done"><i class="fas fa-check"></i> Application Submitted</div>
        <div class="ss-arrow"><i class="fas fa-chevron-down"></i></div>
        <div class="ss-step pending"><i class="fas fa-hourglass-half"></i> Under Review</div>
        <div class="ss-arrow"><i class="fas fa-chevron-down"></i></div>
        <div class="ss-step"><i class="fas fa-door-open"></i> Access Granted</div>
      </div>
      <button class="modal-btn-submit" style="width:100%;justify-content:center;"
        onclick="document.getElementById('successOverlay').classList.remove('modal-open')">Got it!</button>
    </div>
  </div>

  <div class="crud-toast" id="crudToast"></div>
  <script src="/UNIFY(db)/public/assets/javascripts/explore.js"></script>

</body>

</html>