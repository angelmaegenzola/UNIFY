<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Reports & Analytics</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/reports.css" />
</head>
<body>
<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon"><i class="fas fa-layer-group"></i></div>
      <div class="brand-text">
        <div class="brand-name">UNIFY</div>
        <div class="brand-tagline">Club Management System</div>
      </div>
    </div>
<nav class="sidebar-nav">
      <div class="nav-section-label">MAIN MENU</div>
      <a href="index.php?page=dashboard" class="nav-item">
        <i class="fas fa-house"></i><span>Dashboard</span>
      </a>
      <a href="index.php?page=members" class="nav-item">
        <i class="fas fa-users"></i><span>Members</span>
      </a>
      <a href="index.php?page=clubpage" class="nav-item">
        <i class="fas fa-building-columns"></i><span>Clubs</span>
      </a>
      <a href="index.php?page=events" class="nav-item">
        <i class="fas fa-calendar-days"></i><span>Events</span>
      </a>
      <a href="index.php?page=finance" class="nav-item s">
        <i class="fas fa-coins"></i><span>Finances</span>
      </a>
      <div class="nav-section-label">REPORTS</div>
      <a href="index.php?page=reports" class="nav-item active">
        <i class="fas fa-chart-bar"></i><span>Reports</span>
      </a>
    </nav>


    <div class="sidebar-bottom">
      <div class="sidebar-profile">
        <div class="profile-avatar-wrap">
          <span class="profile-avatar-fallback">A</span>
          <span class="profile-online-dot"></span>
        </div>
       <a href="index.php?page=profile" class="profile-link">
  <div class="profile-info">
    <span class="profile-name">Alex Santos</span>
    <span class="profile-role">Club Admin</span>
  </div>
</a>
        <a href="#" class="sidebar-logout" title="Logout"><i class="fas fa-arrow-right-from-bracket"></i></a>
        <a href="#" class="sidebar-settings-btn" title="Settings"><i class="fas fa-gear"></i></a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Reports & Analytics</span>
        <span class="topbar-date">Tuesday, March 31, 2026</span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search clubs, members, events…" />
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="notif-badge">4</span>
        </button>
        <button class="icon-btn"><i class="fas fa-rotate"></i></button>
        <div class="topbar-profile">
          <div class="topbar-avatar">A</div>
          <div class="topbar-profile-info">
            <span class="tp-name">Alex Santos</span>
            <span class="tp-role">Club Admin</span>
          </div>
          <i class="fas fa-chevron-down tp-caret"></i>
        </div>
      </div>
    </header>

    <!-- Content -->
    <div class="content">

      <!--TOOLBAR -->
      <div class="reports-toolbar">
        <div class="period-tabs">
          <button class="period-tab">Weekly</button>
          <button class="period-tab active">Monthly</button>
          <button class="period-tab">Quarterly</button>
          <button class="period-tab">Yearly</button>
        </div>
        <div class="toolbar-filters">
          <select class="filter-select">
            <option>All Clubs</option>
            <option>CS Society</option>
            <option>Athletics Dept.</option>
            <option>Debate Society</option>
            <option>Fine Arts Club</option>
          </select>
          <select class="filter-select">
            <option>March 2026</option>
            <option>February 2026</option>
            <option>January 2026</option>
          </select>
        </div>
      </div>

      <!-- MAIN GRID -->
      <div class="main-grid">

        <div class="col-left">

          <!-- Summary Cards -->
          <div class="stat-row">
            <div class="stat-card s-green">
              <div class="stat-top">
                <div class="stat-icon si-green"><i class="fas fa-building-columns"></i></div>
                <span class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 2</span>
              </div>
              <div class="stat-value">18</div>
              <div class="stat-label">Total Clubs</div>
            </div>

            <div class="stat-card s-gold">
              <div class="stat-top">
                <div class="stat-icon si-gold"><i class="fas fa-users"></i></div>
                <span class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 9%</span>
              </div>
              <div class="stat-value">1,250</div>
              <div class="stat-label">Total Members</div>
            </div>

            <div class="stat-card s-orange">
              <div class="stat-top">
                <div class="stat-icon si-orange"><i class="fas fa-calendar-check"></i></div>
                <span class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> 18%</span>
              </div>
              <div class="stat-value">142</div>
              <div class="stat-label">Active Events</div>
            </div>

            <div class="stat-card s-red">
              <div class="stat-top">
                <div class="stat-icon si-red"><i class="fas fa-list-check"></i></div>
                <span class="stat-trend trend-flat"><i class="fas fa-minus"></i> 0%</span>
              </div>
              <div class="stat-value">348</div>
              <div class="stat-label">Completed Tasks</div>
            </div>

            <div class="stat-card s-purple">
              <div class="stat-top">
                <div class="stat-icon si-purple"><i class="fas fa-hourglass-half"></i></div>
                <span class="stat-trend trend-down"><i class="fas fa-arrow-down"></i> 5%</span>
              </div>
              <div class="stat-value">116</div>
              <div class="stat-label">Pending Tasks</div>
            </div>
          </div>

          <!-- Activity Table -->
          <div class="card table-card">
            <div class="card-header">
              <div>
                <div class="card-title">Club Activity Overview</div>
                <div class="card-sub">All clubs — March 2026</div>
              </div>
              <span class="card-badge">18 Clubs</span>
            </div>

            <div class="activity-table-wrap">
              <div class="activity-table">
                <div class="table-head">
                  <span class="th">Club</span>
                  <span class="th center">Events</span>
                  <span class="th center">Members</span>
                  <span class="th center">Tasks Done</span>
                  <span class="th center">Status</span>
                </div>

                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar ca-green">CS</div>
                    <span class="club-name-text">CS Society</span>
                  </div>
                  <span class="td-num center">24</span>
                  <span class="td-num center">186</span>
                  <div class="td-progress">
                    <div class="mini-progress"><div class="mini-progress-fill" style="width:92%"></div></div>
                    <span class="mini-pct">92%</span>
                  </div>
                  <span class="status-badge s-active"><span class="status-dot"></span>Active</span>
                </div>

                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar ca-teal">AT</div>
                    <span class="club-name-text">Athletics Dept.</span>
                  </div>
                  <span class="td-num center">31</span>
                  <span class="td-num center">210</span>
                  <div class="td-progress">
                    <div class="mini-progress"><div class="mini-progress-fill" style="width:88%"></div></div>
                    <span class="mini-pct">88%</span>
                  </div>
                  <span class="status-badge s-active"><span class="status-dot"></span>Active</span>
                </div>

                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar ca-gold">DB</div>
                    <span class="club-name-text">Debate Society</span>
                  </div>
                  <span class="td-num center">18</span>
                  <span class="td-num center">142</span>
                  <div class="td-progress">
                    <div class="mini-progress"><div class="mini-progress-fill" style="width:85%"></div></div>
                    <span class="mini-pct">85%</span>
                  </div>
                  <span class="status-badge s-active"><span class="status-dot"></span>Active</span>
                </div>

                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar ca-blue">BM</div>
                    <span class="club-name-text">Biz Management</span>
                  </div>
                  <span class="td-num center">15</span>
                  <span class="td-num center">98</span>
                  <div class="td-progress">
                    <div class="mini-progress"><div class="mini-progress-fill" style="width:80%"></div></div>
                    <span class="mini-pct">80%</span>
                  </div>
                  <span class="status-badge s-active"><span class="status-dot"></span>Active</span>
                </div>

                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar" style="background:#7c3aed;color:#fff">FA</div>
                    <span class="club-name-text">Fine Arts Club</span>
                  </div>
                  <span class="td-num center">12</span>
                  <span class="td-num center">75</span>
                  <div class="td-progress">
                    <div class="mini-progress"><div class="mini-progress-fill" style="width:74%"></div></div>
                    <span class="mini-pct">74%</span>
                  </div>
                  <span class="status-badge s-moderate"><span class="status-dot"></span>Moderate</span>
                </div>

                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar" style="background:#0e7c6e;color:#fff">ST</div>
                    <span class="club-name-text">Science & Tech</span>
                  </div>
                  <span class="td-num center">10</span>
                  <span class="td-num center">64</span>
                  <div class="td-progress">
                    <div class="mini-progress"><div class="mini-progress-fill" style="width:70%"></div></div>
                    <span class="mini-pct">70%</span>
                  </div>
                  <span class="status-badge s-moderate"><span class="status-dot"></span>Moderate</span>
                </div>

                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar ca-orange">LT</div>
                    <span class="club-name-text">Literature Club</span>
                  </div>
                  <span class="td-num center">4</span>
                  <span class="td-num center">38</span>
                  <div class="td-progress">
                    <div class="mini-progress"><div class="mini-progress-fill prog-red" style="width:48%"></div></div>
                    <span class="mini-pct pct-red">48%</span>
                  </div>
                  <span class="status-badge s-inactive"><span class="status-dot"></span>Inactive</span>
                </div>

                <div class="table-row">
                  <div class="club-name-col">
                    <div class="club-avatar ca-red">EV</div>
                    <span class="club-name-text">Env. Society</span>
                  </div>
                  <span class="td-num center">5</span>
                  <span class="td-num center">44</span>
                  <div class="td-progress">
                    <div class="mini-progress"><div class="mini-progress-fill prog-orange" style="width:52%"></div></div>
                    <span class="mini-pct pct-orange">52%</span>
                  </div>
                  <span class="status-badge s-inactive"><span class="status-dot"></span>Inactive</span>
                </div>

              </div>
            </div>
          </div>

        </div>

        <div class="col-right">

          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Task Overview</div>
                <div class="card-sub">March 2026</div>
              </div>
              <span class="overall-pct">83%</span>
            </div>

            <div class="big-progress">
              <div class="big-progress-bar">
                <div class="big-progress-fill" style="width:83%"></div>
              </div>
              <div class="big-progress-meta">
                <span>348 of 420 tasks done</span>
                <span>72 remaining</span>
              </div>
            </div>

            <div class="task-mini-grid">
              <div class="task-mini-box tmb-green">
                <div class="tmb-num">348</div>
                <div class="tmb-label">Completed</div>
              </div>
              <div class="task-mini-box tmb-gold">
                <div class="tmb-num">84</div>
                <div class="tmb-label">In Progress</div>
              </div>
              <div class="task-mini-box tmb-orange">
                <div class="tmb-num">32</div>
                <div class="tmb-label">Overdue</div>
              </div>
              <div class="task-mini-box tmb-red">
                <div class="tmb-num">18</div>
                <div class="tmb-label">Cancelled</div>
              </div>
            </div>
          </div>


          <div class="card rank-card">
            <div class="card-header">
              <div>
                <div class="card-title">Top Clubs</div>
                <div class="card-sub">Most active this month</div>
              </div>
              <span class="card-badge">Top 5</span>
            </div>

            <div class="rank-list">
              <div class="rank-item">
                <div class="rank-medal medal-1">1</div>
                <div class="club-avatar ca-green">CS</div>
                <div class="rank-info">
                  <div class="rank-name">CS Society</div>
                  <div class="rank-meta">24 events · 186 members</div>
                </div>
                <div class="rank-score-wrap">
                  <div class="rank-bar-bg">
                    <div class="rank-bar-fill" style="width:98%"></div>
                  </div>
                  <span class="rank-score score-top">98</span>
                </div>
              </div>

              <div class="rank-item">
                <div class="rank-medal medal-2">2</div>
                <div class="club-avatar ca-teal">AT</div>
                <div class="rank-info">
                  <div class="rank-name">Athletics Dept.</div>
                  <div class="rank-meta">31 events · 210 members</div>
                </div>
                <div class="rank-score-wrap">
                  <div class="rank-bar-bg">
                    <div class="rank-bar-fill" style="width:94%"></div>
                  </div>
                  <span class="rank-score score-top">94</span>
                </div>
              </div>

              <div class="rank-item">
                <div class="rank-medal medal-3">3</div>
                <div class="club-avatar ca-gold">DB</div>
                <div class="rank-info">
                  <div class="rank-name">Debate Society</div>
                  <div class="rank-meta">18 events · 142 members</div>
                </div>
                <div class="rank-score-wrap">
                  <div class="rank-bar-bg">
                    <div class="rank-bar-fill" style="width:88%"></div>
                  </div>
                  <span class="rank-score score-top">88</span>
                </div>
              </div>

              <div class="rank-item">
                <div class="rank-medal medal-n">4</div>
                <div class="club-avatar ca-blue">BM</div>
                <div class="rank-info">
                  <div class="rank-name">Biz Management</div>
                  <div class="rank-meta">15 events · 98 members</div>
                </div>
                <div class="rank-score-wrap">
                  <div class="rank-bar-bg">
                    <div class="rank-bar-fill" style="width:81%"></div>
                  </div>
                  <span class="rank-score">81</span>
                </div>
              </div>

              <div class="rank-item">
                <div class="rank-medal medal-n">5</div>
                <div class="club-avatar" style="background:#7c3aed;color:#fff">FA</div>
                <div class="rank-info">
                  <div class="rank-name">Fine Arts Club</div>
                  <div class="rank-meta">12 events · 75 members</div>
                </div>
                <div class="rank-score-wrap">
                  <div class="rank-bar-bg">
                    <div class="rank-bar-fill" style="width:72%"></div>
                  </div>
                  <span class="rank-score">72</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Least Active -->
          <div class="card">
            <div class="card-header">
              <div>
                <div class="card-title">Needs Attention</div>
                <div class="card-sub">Clubs with low activity</div>
              </div>
              <span class="card-badge badge-red">2 Clubs</span>
            </div>

            <div class="alert-list">
              <div class="alert-item">
                <div class="alert-icon"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="club-avatar ca-orange">LT</div>
                <div class="alert-info">
                  <div class="alert-name">Literature Club</div>
                  <div class="alert-meta">4 events · 48% task rate</div>
                </div>
                <span class="status-badge s-inactive"><span class="status-dot"></span>Inactive</span>
              </div>
              <div class="alert-item">
                <div class="alert-icon"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="club-avatar ca-red">EV</div>
                <div class="alert-info">
                  <div class="alert-name">Env. Society</div>
                  <div class="alert-meta">5 events · 52% task rate</div>
                </div>
                <span class="status-badge s-inactive"><span class="status-dot"></span>Inactive</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>

  </main>
</div>
</body>
</html>