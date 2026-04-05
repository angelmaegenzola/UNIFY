<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Members</title>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
   <link rel="stylesheet" href="./assets/css/members.css" />
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
      <a href="index.php?page=members" class="nav-item active">
        <i class="fas fa-users"></i><span>Members</span>
      </a>
      <a href="index.php?page=clubpage" class="nav-item">
        <i class="fas fa-building-columns"></i><span>Clubs</span>
      </a>
      <a href="index.php?page=events" class="nav-item">
        <i class="fas fa-calendar-days"></i><span>Events</span>
      </a>
      <a href="index.php?page=finance" class="nav-item">
        <i class="fas fa-coins"></i><span>Finances</span>
      </a>
      <div class="nav-section-label">REPORTS</div>
      <a href="index.php?page=reports" class="nav-item">
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
        <a href="#" class="sidebar-logout" title="Logout">
          <i class="fas fa-arrow-right-from-bracket"></i>
        </a>
        <a href="#" class="sidebar-settings-btn" title="Settings">
          <i class="fas fa-gear"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Members</span>
        <span class="topbar-date">Tuesday, March 31, 2026</span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search members, clubs, events…" />
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="badge red">4</span>
        </button>
        <button class="icon-btn" title="Sync">
          <i class="fas fa-rotate"></i>
          <span class="badge green">2</span>
        </button>
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

    <!-- CONTENT -->
    <div class="content">

      <div class="stats-top">
        <div class="stats-filter">
          <i class="fas fa-calendar"></i>
          <span>Monthly</span>
          <i class="fas fa-chevron-down"></i>
        </div>
        <button class="stats-export">
          Export <i class="fas fa-arrow-up-right-from-square"></i>
        </button>
      </div>

      <!-- Stat Cards -->
      <div class="stat-cards-grid">
        <div class="stat-card-new sc-green">
          <div class="sc-top">
            <div class="sc-icon-wrap"><i class="fas fa-users"></i></div>
            <span class="sc-trend">↑ 12</span>
          </div>
          <div class="sc-value">1,250</div>
          <div class="sc-label">Total Members</div>
        </div>

        <div class="stat-card-new sc-yellow">
          <div class="sc-top">
            <div class="sc-icon-wrap"><i class="fas fa-user-check"></i></div>
            <span class="sc-trend">↑ 13</span>
          </div>
          <div class="sc-value">1,128</div>
          <div class="sc-label">Active Members</div>
        </div>

        <div class="stat-card-new sc-teal">
          <div class="sc-top">
            <div class="sc-icon-wrap"><i class="fas fa-user-plus"></i></div>
            <span class="sc-trend">↑ 12</span>
          </div>
          <div class="sc-value">36</div>
          <div class="sc-label">New Members</div>
        </div>

        <div class="stat-card-new sc-red">
          <div class="sc-top">
            <div class="sc-icon-wrap"><i class="fas fa-user"></i></div>
            <span class="sc-trend urgent">↑ 10</span>
          </div>
          <div class="sc-value">24</div>
          <div class="sc-label">New Applicants</div>
        </div>
      </div>

      <!-- Members Table -->
      <div class="table-container">

        <div class="table-toolbar">
          <span class="toolbar-title">All Members</span>
          <span class="member-count">6 members</span>
          <div class="search-box">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Search by name, course, club…" />
          </div>
          <select class="filter-select">
            <option value="">All Clubs</option>
            <option>Information Technology Society</option>
            <option>Future Technical Educators Society</option>
            <option>Information Systems Society</option>
            <option>Youth Movers Movement</option>
            <option>Artisan's Society</option>
            <option>Circle of Peer Facilitator</option>
          </select>
          <select class="filter-select">
            <option value="">All Roles</option>
            <option>Member</option>
            <option>Secretary</option>
            <option>Vice President</option>
          </select>
          <button class="btn-add">
            <i class="fas fa-plus"></i> Add Member
          </button>
        </div>

        <!-- Filter  -->
        <div class="filter">
          <button class="tab active">All</button>
          <button class="tab">Active</button>
          <button class="tab">Inactive</button>
          <button class="tab">Pending</button>
        </div>

        <!-- Table -->
        <div class="table-wrap">
          <table class="student-table">
            <thead>
              <tr>
                <th><input type="checkbox" /></th>
                <th>Full Name</th>
                <th>Course</th>
                <th>Year &amp; Section</th>
                <th>Club</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>

              <tr>
                <td><input type="checkbox" /></td>
                <td>
                  <div class="name-cell">
                    <div class="avatar">JD</div>
                    <span class="name-text">Juan Dela Cruz</span>
                  </div>
                </td>
                <td class="td-mid">BS Information Technology</td>
                <td class="td-mid td-bold">3A</td>
                <td class="td-mid td-ellipsis">Information Technology Society</td>
                <td><span class="role-badge">Member</span></td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>
                  <div class="actions-cell">
                    <button class="act-btn view" title="View"><i class="fas fa-eye"></i></button>
                    <button class="act-btn edit" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="act-btn del"  title="Delete"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td><input type="checkbox" /></td>
                <td>
                  <div class="name-cell">
                    <div class="avatar">MS</div>
                    <span class="name-text">Maria Santos</span>
                  </div>
                </td>
                <td class="td-mid">BS Education</td>
                <td class="td-mid td-bold">2B</td>
                <td class="td-mid td-ellipsis">Future Technical Educators Society</td>
                <td><span class="role-badge">Member</span></td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>
                  <div class="actions-cell">
                    <button class="act-btn view" title="View"><i class="fas fa-eye"></i></button>
                    <button class="act-btn edit" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="act-btn del"  title="Delete"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td><input type="checkbox" /></td>
                <td>
                  <div class="name-cell">
                    <div class="avatar">MR</div>
                    <span class="name-text">Mark Reyes</span>
                  </div>
                </td>
                <td class="td-mid">BS Information System</td>
                <td class="td-mid td-bold">4C</td>
                <td class="td-mid td-ellipsis">Information Systems Society</td>
                <td><span class="role-badge">Member</span></td>
                <td><span class="status-badge status-inactive">Inactive</span></td>
                <td>
                  <div class="actions-cell">
                    <button class="act-btn view" title="View"><i class="fas fa-eye"></i></button>
                    <button class="act-btn edit" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="act-btn del"  title="Delete"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td><input type="checkbox" /></td>
                <td>
                  <div class="name-cell">
                    <div class="avatar">AC</div>
                    <span class="name-text">Angela Cruz</span>
                  </div>
                </td>
                <td class="td-mid">BS Engineering</td>
                <td class="td-mid td-bold">1A</td>
                <td class="td-mid td-ellipsis">Youth Movers Movement</td>
                <td><span class="role-badge officer">Secretary</span></td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>
                  <div class="actions-cell">
                    <button class="act-btn view" title="View"><i class="fas fa-eye"></i></button>
                    <button class="act-btn edit" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="act-btn del"  title="Delete"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td><input type="checkbox" /></td>
                <td>
                  <div class="name-cell">
                    <div class="avatar">LR</div>
                    <span class="name-text">Leonor Rivera</span>
                  </div>
                </td>
                <td class="td-mid">BS Industrial Technology</td>
                <td class="td-mid td-bold">4C</td>
                <td class="td-mid td-ellipsis">Artisan's Society</td>
                <td><span class="role-badge exec">Vice President</span></td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>
                  <div class="actions-cell">
                    <button class="act-btn view" title="View"><i class="fas fa-eye"></i></button>
                    <button class="act-btn edit" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="act-btn del"  title="Delete"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td><input type="checkbox" /></td>
                <td>
                  <div class="name-cell">
                    <div class="avatar">NM</div>
                    <span class="name-text">Narcisa Mercado</span>
                  </div>
                </td>
                <td class="td-mid">BS Education</td>
                <td class="td-mid td-bold">1A</td>
                <td class="td-mid td-ellipsis">Circle of Peer Facilitator</td>
                <td><span class="role-badge">Member</span></td>
                <td><span class="status-badge status-pending">Pending</span></td>
                <td>
                  <div class="actions-cell">
                    <button class="act-btn view" title="View"><i class="fas fa-eye"></i></button>
                    <button class="act-btn edit" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="act-btn del"  title="Delete"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>

            </tbody>
          </table>
        </div>

     
        <div class="table-footer">
          <span class="showing-text">Showing 1–6 of 6 members</span>
          <div class="pagination">
            <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

</body>
</html>