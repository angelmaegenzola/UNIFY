<?php require_once __DIR__ . '/../../app/controllers/reports_controller.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>UNIFY — Reports</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/public/assets/css/reports.css"/>
  <link rel="stylesheet" href="/public/assets/css/transitions.css" />
</head>
<body>
<div class="app">

  <!-- ── Sidebar ── -->
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
  <aside class="sidebar" id="mainSidebar">
<div class="sidebar-brand">
  <img src="/public/assets/pictures/unifylogo.png" alt="UNIFY" class="brand-icon-img" />
  <div class="brand-text">
    <div class="brand-name">UNIFY</div>
    <div class="brand-tagline">Club Management System</div>
  </div>
</div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">MAIN MENU</div>
      <a href="index.php?page=dashboard" class="nav-item"><i class="fas fa-house"></i><span>Dashboard</span></a>
      <a href="index.php?page=members"   class="nav-item"><i class="fas fa-users"></i><span>Members</span></a>
      <a href="index.php?page=clubpage"  class="nav-item"><i class="fas fa-building-columns"></i><span>Clubs</span></a>
      <a href="index.php?page=events"    class="nav-item"><i class="fas fa-calendar-days"></i><span>Events</span></a>
      <div class="nav-section-label">REPORTS</div>
      <a href="index.php?page=reports"   class="nav-item active"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
    </nav>
    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <?php if(!empty($avatar_url)): ?><img src="<?= $avatar_url ?>" alt="Avatar" class="profile-avatar-img" /><?php else: ?><span class="profile-avatar-fallback"><?= $adminInitial ?></span><?php endif; ?>
          <span class="profile-online-dot"></span>
        </div>
        <a href="index.php?page=adminprofile" class="profile-link">
          <div class="profile-info">
            <span class="profile-name"><?= htmlspecialchars($adminName) ?></span>
            <span class="profile-role">Club Admin</span>
          </div>
        </a>
        <a href="index.php?page=logout" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
        <a href="#" class="sidebar-settings-btn" title="Settings"><i class="fas fa-gear"></i></a>
      </div>
    </div>
</aside>

  <!-- ── Main ── -->
  <main class="main">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <button class="topbar-hamburger" onclick="toggleSidebar()" title="Menu">
          <img src="/assets/pictures/unifylogo.png" alt="Menu" class="topbar-logo-btn" />
        </button>
        <div class="topbar-title-group">
          <span class="topbar-page-title">Reports &amp; Analytics</span>
          <span class="topbar-date"><?= date('l, F j, Y') ?></span>
        </div>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search clubs, members, events…"/>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn"><i class="fas fa-bell"></i><span class="notif-badge">4</span></button>
        <button class="icon-btn" onclick="window.print()"><i class="fas fa-rotate"></i></button>
        <div class="topbar-profile">
          <div class="topbar-avatar">
  <?php if (!empty($avatar_url)): ?>
    <img src="<?= $avatar_url ?>" alt="Avatar" class="topbar-avatar-img" />
  <?php else: ?>
    <?= $adminInitial ?>
  <?php endif; ?>
</div>
          <div class="topbar-profile-info">
            <span class="tp-name"><?= htmlspecialchars($adminName) ?></span>
            <span class="tp-role">Club Admin</span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </div>
      </div>
            <button class="hamburger-btn" onclick="toggleSidebar()" title="Menu"><i class="fas fa-bars"></i></button>
    </header>

    <div class="content">

      <!-- Period tabs + filters -->
      <div class="reports-toolbar">
        <div class="period-tabs">
          <button class="period-tab" onclick="setTab(this)">Weekly</button>
          <button class="period-tab active" onclick="setTab(this)">Monthly</button>
          <button class="period-tab" onclick="setTab(this)">Quarterly</button>
          <button class="period-tab" onclick="setTab(this)">Yearly</button>
        </div>
        <div class="toolbar-filters">
          <div class="custom-select-wrap">
            <button class="custom-select-btn" id="rptClubBtn" onclick="toggleDrop('rptClub')">All Clubs</button>
            <div class="custom-select-list" id="rptClubDropList">
              <div class="custom-select-option selected" onclick="selectDrop('rptClub','','All Clubs',this)">All Clubs</div>
              <?php foreach ($clubActivity as $c): ?>
                <div class="custom-select-option" onclick="selectDrop('rptClub','<?= htmlspecialchars($c['name']) ?>','<?= htmlspecialchars($c['name']) ?>',this)"><?= htmlspecialchars($c['name']) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
          <input type="hidden" id="rptClubFilter" value=""/>
          <div class="custom-select-wrap">
            <button class="custom-select-btn" id="rptMonthBtn" onclick="toggleDrop('rptMonth')"><?= date('F Y') ?></button>
            <div class="custom-select-list" id="rptMonthDropList">
              <div class="custom-select-option selected" onclick="selectDrop('rptMonth','<?= date('F Y') ?>','<?= date('F Y') ?>',this)"><?= date('F Y') ?></div>
              <div class="custom-select-option" onclick="selectDrop('rptMonth','<?= date('F Y', strtotime('-1 month')) ?>','<?= date('F Y', strtotime('-1 month')) ?>',this)"><?= date('F Y', strtotime('-1 month')) ?></div>
              <div class="custom-select-option" onclick="selectDrop('rptMonth','<?= date('F Y', strtotime('-2 months')) ?>','<?= date('F Y', strtotime('-2 months')) ?>',this)"><?= date('F Y', strtotime('-2 months')) ?></div>
            </div>
          </div>
          <input type="hidden" id="rptMonthFilter" value=""/>
        </div>
        <div class="toolbar-right">
          
          <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
      </div>

      <!-- Stat Cards Row -->
      <div class="stat-row">
        <div class="stat-card s-green">
          <div class="stat-top">
            <div class="stat-icon si-green"><i class="fas fa-building-columns"></i></div>
            <span class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 2</span>
          </div>
          <div class="stat-value"><?= $totalClubs ?></div>
          <div class="stat-label">Total Clubs</div>
        </div>
        <div class="stat-card s-gold">
          <div class="stat-top">
            <div class="stat-icon si-gold"><i class="fas fa-users"></i></div>
            <span class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 9%</span>
          </div>
          <div class="stat-value"><?= number_format($totalMembers) ?></div>
          <div class="stat-label">Total Members</div>
        </div>
        <div class="stat-card s-orange">
          <div class="stat-top">
            <div class="stat-icon si-orange"><i class="fas fa-calendar-days"></i></div>
            <span class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 18%</span>
          </div>
          <div class="stat-value"><?= $totalEvents ?></div>
          <div class="stat-label">Active Events</div>
        </div>
        <div class="stat-card s-red">
          <div class="stat-top">
            <div class="stat-icon si-red"><i class="fas fa-list-check"></i></div>
            <span class="stat-trend trend-flat">— 0%</span>
          </div>
          <div class="stat-value"><?= $completedTasks ?></div>
          <div class="stat-label">Completed Tasks</div>
        </div>
        <div class="stat-card s-purple">
          <div class="stat-top">
            <div class="stat-icon si-purple"><i class="fas fa-hourglass-half"></i></div>
            <span class="stat-trend trend-down"><i class="fas fa-arrow-down"></i> 5%</span>
          </div>
          <div class="stat-value"><?= $pendingApps ?></div>
          <div class="stat-label">Pending Tasks</div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="main-grid">

        <!-- LEFT: Club Activity Overview -->
        <div class="col-left">
          <div class="card table-card">
            <div class="card-header">
              <div>
                <div class="card-title">Club Activity Overview</div>
                <div class="card-sub">All clubs — <?= date('F Y') ?></div>
              </div>
              <span class="card-badge"><?= $totalClubs ?> Clubs</span>
            </div>

            <div class="activity-table-wrap">
              <div class="activity-table">
                <div class="table-head">
                  <span class="th">Club</span>
                  <span class="th center">Events</span>
                  <span class="th center">Members</span>
                  <span class="th">Tasks Done</span>
                  <span class="th center">Status</span>
                </div>

                <?php
                $maxEvents  = max(array_column($clubActivity, 'event_count') ?: [1]);
                $maxMembers = max(array_column($clubActivity, 'member_count') ?: [1]);
                foreach ($clubActivity as $i => $club):
                  $score = ($club['event_count'] / max($maxEvents,1) * 50)
                         + ($club['member_count'] / max($maxMembers,1) * 50);
                  $pct   = round($score);
                  $progClass = $pct >= 75 ? '' : ($pct >= 55 ? 'prog-orange' : 'prog-red');
                  $pctClass  = $pct >= 75 ? '' : ($pct >= 55 ? 'pct-orange'  : 'pct-red');
                  $statusClass = $pct >= 75 ? 's-active' : ($pct >= 55 ? 's-moderate' : 's-inactive');
                  $statusLabel = $pct >= 75 ? 'Active'   : ($pct >= 55 ? 'Moderate'   : 'Inactive');
                  $initials = strtoupper(substr($club['acronym'] ?: $club['name'], 0, 2));
                  $color    = $avatarColors[$i % count($avatarColors)];
                ?>
                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar <?= $color ?>"><?= $initials ?></div>
                    <span class="club-name-text"><?= htmlspecialchars($club['name']) ?></span>
                  </div>
                  <span class="td-num center"><?= $club['event_count'] ?></span>
                  <span class="td-num center"><?= $club['member_count'] ?></span>
                  <div class="td-progress">
                    <div class="mini-progress">
                      <div class="mini-progress-fill <?= $progClass ?>" style="width:<?= $pct ?>%" data-target="<?= $pct ?>"></div>
                    </div>
                    <span class="mini-pct <?= $pctClass ?>"><?= $pct ?>%</span>
                  </div>
                  <div style="display:flex;justify-content:center;">
                    <span class="status-badge <?= $statusClass ?>">
                      <span class="status-dot"></span><?= $statusLabel ?>
                    </span>
                  </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($clubActivity)): ?>
                <div style="text-align:center;padding:30px;color:var(--text-light);font-size:12px;">
                  No club data yet. Add clubs to see the overview.
                </div>
                <?php endif; ?>

              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: Task Overview + Top Clubs + Needs Attention -->
        <div class="col-right">

          <!-- Task Overview -->
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Task Overview</div>
                <div class="card-sub"><?= date('F Y') ?></div>
              </div>
              <span class="overall-pct"><?= $taskPct ?>%</span>
            </div>
            <div class="big-progress">
              <div class="big-progress-bar">
                <div class="big-progress-fill" style="width:0%" data-target="<?= $taskPct ?>"></div>
              </div>
              <div class="big-progress-meta">
                <span><?= $completedTasks ?> of <?= $totalTasks ?> tasks done</span>
                <span><?= max(0, $totalTasks - $completedTasks) ?> remaining</span>
              </div>
            </div>
            <div class="task-mini-grid">
              <div class="task-mini-box tmb-green">
                <div class="tmb-num"><?= $completedTasks ?></div>
                <div class="tmb-label">Completed</div>
              </div>
              <div class="task-mini-box tmb-gold">
                <div class="tmb-num"><?= $inProgressTasks ?></div>
                <div class="tmb-label">In Progress</div>
              </div>
              <div class="task-mini-box tmb-orange">
                <div class="tmb-num"><?= $overdueTasks ?></div>
                <div class="tmb-label">Overdue</div>
              </div>
              <div class="task-mini-box tmb-red">
                <div class="tmb-num"><?= $cancelledTasks ?></div>
                <div class="tmb-label">Cancelled</div>
              </div>
            </div>
          </div>

          <!-- Top Clubs -->
          <div class="card rank-card">
            <div class="card-header">
              <div>
                <div class="card-title">Top Clubs</div>
                <div class="card-sub">Most active this month</div>
              </div>
              <span class="card-badge">Top <?= min(5, count($topClubs)) ?></span>
            </div>
            <div class="rank-list">
              <?php foreach ($topClubs as $i => $club):
                $score = ($club['event_count'] * 2) + $club['member_count'];
                $maxScore = ($topClubs[0]['event_count'] * 2) + $topClubs[0]['member_count'];
                $barPct = $maxScore > 0 ? round($score / $maxScore * 100) : 0;
                $medalClass = $i === 0 ? 'medal-1' : ($i === 1 ? 'medal-2' : ($i === 2 ? 'medal-3' : 'medal-n'));
                $initials = strtoupper(substr($club['acronym'] ?: $club['name'], 0, 2));
                $color    = $avatarColors[$i % count($avatarColors)];
              ?>
              <div class="rank-item">
                <div class="rank-medal <?= $medalClass ?>"><?= $i + 1 ?></div>
                <div class="club-avatar <?= $color ?>" style="width:32px;height:32px;border-radius:9px;font-size:11px;"><?= $initials ?></div>
                <div class="rank-info">
                  <div class="rank-name"><?= htmlspecialchars($club['name']) ?></div>
                  <div class="rank-meta"><?= $club['event_count'] ?> events · <?= $club['member_count'] ?> members</div>
                </div>
                <div class="rank-score-wrap">
                  <div class="rank-bar-bg">
                    <div class="rank-bar-fill" style="width:0%" data-target="<?= $barPct ?>"></div>
                  </div>
                  <span class="rank-score <?= $i === 0 ? 'score-top' : '' ?>"><?= $score ?></span>
                </div>
              </div>
              <?php endforeach; ?>
              <?php if (empty($topClubs)): ?>
              <div style="text-align:center;padding:20px;color:var(--text-light);font-size:12px;">No clubs yet.</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Needs Attention -->
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Needs Attention</div>
                <div class="card-sub">Clubs with low activity</div>
              </div>
              <?php if (!empty($needsAttention)): ?>
              <span class="card-badge badge-red"><?= count($needsAttention) ?> Clubs</span>
              <?php endif; ?>
            </div>
            <div class="alert-list">
              <?php if (empty($needsAttention)): ?>
              <div style="text-align:center;padding:14px;color:var(--text-light);font-size:12px;">
                <i class="fas fa-check-circle" style="color:var(--green-accent);"></i> All clubs are active!
              </div>
              <?php else: ?>
              <?php foreach ($needsAttention as $i => $club):
                $score = ($club['event_count'] * 2) + $club['member_count'];
                $initials = strtoupper(substr($club['acronym'] ?: $club['name'], 0, 2));
                $color    = $avatarColors[$i % count($avatarColors)];
              ?>
              <div class="alert-item">
                <i class="fas fa-triangle-exclamation alert-icon"></i>
                <div class="club-avatar <?= $color ?>" style="width:30px;height:30px;border-radius:8px;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;flex-shrink:0;"><?= $initials ?></div>
                <div class="alert-info">
                  <div class="alert-name"><?= htmlspecialchars($club['name']) ?></div>
                  <div class="alert-meta"><?= $club['event_count'] ?> events · <?= $club['member_count'] ?> members</div>
                </div>
                <span class="status-badge s-inactive"><span class="status-dot"></span>Inactive</span>
              </div>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

        </div><!-- /col-right -->
      </div><!-- /main-grid -->
    </div><!-- /content -->
  </main>
</div>

<script src="/public/assets/javascripts/reports.js"></script>

<script>
function toggleDrop(type) {
  const btn = document.getElementById(type+'Btn');
  const list = document.getElementById(type+'DropList');
  const isOpen = list.classList.contains('open');
  document.querySelectorAll('.custom-select-list').forEach(l => l.classList.remove('open'));
  document.querySelectorAll('.custom-select-btn').forEach(b => b.classList.remove('open'));
  if (!isOpen) { list.classList.add('open'); btn.classList.add('open'); }
}
function selectDrop(type, value, label, el) {
  const hiddenInput = document.getElementById(type+'Filter');
  if (hiddenInput) hiddenInput.value = value;
  document.getElementById(type+'Btn').textContent = label;
  document.querySelectorAll('#'+type+'DropList .custom-select-option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById(type+'DropList').classList.remove('open');
  document.getElementById(type+'Btn').classList.remove('open');
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('.custom-select-wrap')) {
    document.querySelectorAll('.custom-select-list').forEach(l => l.classList.remove('open'));
    document.querySelectorAll('.custom-select-btn').forEach(b => b.classList.remove('open'));
  }
});
</script>

<script>
function preventScroll(e) { e.preventDefault(); }
function toggleSidebar() {
  const sidebar = document.getElementById('mainSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const isOpen = sidebar.classList.toggle('open');
  overlay.classList.toggle('open', isOpen);
  document.body.classList.toggle('sidebar-open', isOpen);
}
function closeSidebar() {
  const sidebar = document.getElementById('mainSidebar');
  sidebar.classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
  document.body.classList.remove('sidebar-open');
}
/* Swipe disabled */
</script>
<button class="fab-menu-btn" id="fabMenuBtn" onclick="toggleSidebar()" title="Menu">
  <i class="fas fa-bars"></i>
</button>
</body>
</html>