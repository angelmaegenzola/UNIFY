<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIFY — Finances</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/finance.css" />
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
      <a href="index.php?page=finance" class="nav-item active">
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
        <div class="profile-info">
          <span class="profile-name">Alex Santos</span>
          <span class="profile-role">Club Admin</span>
        </div>
        <a href="#" class="sidebar-logout" title="Logout">
          <i class="fas fa-arrow-right-from-bracket"></i>
        </a>
        <a href="index.php?page=settings" class="sidebar-settings-btn" title="Settings">
          <i class="fas fa-gear"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <header class="topbar">
      <div class="topbar-left">
        <span class="topbar-page-title">Finances</span>
        <span class="topbar-date">Wednesday, April 01, 2026</span>
      </div>
      <div class="topbar-center">
        <div class="topbar-search">
          <i class="fas fa-magnifying-glass"></i>
          <input type="text" placeholder="Search transactions, clubs…" />
        </div>
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" title="Notifications">
          <i class="fas fa-bell"></i>
          <span class="badge red">4</span>
        </button>
        <button class="icon-btn" title="Sync"><i class="fas fa-rotate"></i></button>
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

    <div class="content">

      <!-- Toolbar -->
      <div class="finance-toolbar">
        <div class="filter-tabs">
          <button class="filter-tab active" onclick="setTab(this)">All</button>
          <button class="filter-tab" onclick="setTab(this)">Income</button>
          <button class="filter-tab" onclick="setTab(this)">Expenses</button>
          <button class="filter-tab" onclick="setTab(this)">Pending</button>
        </div>
        <div class="toolbar-right">
          <button class="add-record-btn" onclick="openModal('addRecordModal')">
            <i class="fas fa-plus"></i> Add Record
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="finance-body">

        <!-- LEFT COLUMN -->
        <div class="finance-main-col">

          <!-- Stat Cards -->
          <div class="stat-cards-grid">
            <div class="stat-card sc-green">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-wallet"></i></div>
                <span class="sc-trend">Total</span>
              </div>
              <div class="sc-value">₱182,500</div>
              <div class="sc-label">Total Budget</div>
            </div>
            <div class="stat-card sc-teal">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-arrow-trend-up"></i></div>
                <span class="sc-trend">↑ This Month</span>
              </div>
              <div class="sc-value">₱64,200</div>
              <div class="sc-label">Total Income</div>
            </div>
            <div class="stat-card sc-gold">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-arrow-trend-down"></i></div>
                <span class="sc-trend">↓ This Month</span>
              </div>
              <div class="sc-value">₱38,750</div>
              <div class="sc-label">Total Expenses</div>
            </div>
            <div class="stat-card sc-red">
              <div class="sc-top">
                <div class="sc-icon-wrap"><i class="fas fa-clock"></i></div>
                <span class="sc-trend">3 Requests</span>
              </div>
              <div class="sc-value">₱12,000</div>
              <div class="sc-label">Pending Requests</div>
            </div>
          </div>

          <!-- Recent Transactions -->
          <div class="card">
            <div class="card-header">
              <h2>Recent Transactions</h2>
              <a href="#" class="see-all-link">View All <i class="fas fa-chevron-right"></i></a>
            </div>

            <div class="table-header-row">
              <span class="th-col">Description</span>
              <span class="th-col">Club</span>
              <span class="th-col">Category</span>
              <span class="th-col th-right">Amount</span>
              <span class="th-col th-right">Status</span>
            </div>

            <div class="table-body">

              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-icon icon-income"><i class="fas fa-arrow-down"></i></div>
                  <div class="tr-info">
                    <span class="tr-title">Membership Dues Collection</span>
                    <span class="tr-sub">Mar 31, 2026</span>
                  </div>
                </div>
                <span class="tr-club">CS Society</span>
                <span class="tr-category">Income</span>
                <span class="tr-amount income">+₱8,500</span>
                <span class="tr-status-badge badge-approved">Approved</span>
              </div>

              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-icon icon-expense"><i class="fas fa-arrow-up"></i></div>
                  <div class="tr-info">
                    <span class="tr-title">Event Venue Rental</span>
                    <span class="tr-sub">Mar 30, 2026</span>
                  </div>
                </div>
                <span class="tr-club">Debate Society</span>
                <span class="tr-category">Expense</span>
                <span class="tr-amount expense">−₱5,000</span>
                <span class="tr-status-badge badge-approved">Approved</span>
              </div>

              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-icon icon-income"><i class="fas fa-arrow-down"></i></div>
                  <div class="tr-info">
                    <span class="tr-title">Sports Fest Registration Fees</span>
                    <span class="tr-sub">Mar 29, 2026</span>
                  </div>
                </div>
                <span class="tr-club">Athletics</span>
                <span class="tr-category">Income</span>
                <span class="tr-amount income">+₱12,000</span>
                <span class="tr-status-badge badge-approved">Approved</span>
              </div>

              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-icon icon-expense"><i class="fas fa-arrow-up"></i></div>
                  <div class="tr-info">
                    <span class="tr-title">Printing & Supplies</span>
                    <span class="tr-sub">Mar 28, 2026</span>
                  </div>
                </div>
                <span class="tr-club">Fine Arts Club</span>
                <span class="tr-category">Expense</span>
                <span class="tr-amount expense">−₱2,300</span>
                <span class="tr-status-badge badge-approved">Approved</span>
              </div>

              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-icon icon-pending"><i class="fas fa-clock"></i></div>
                  <div class="tr-info">
                    <span class="tr-title">Hackathon Prize Money</span>
                    <span class="tr-sub">Mar 27, 2026</span>
                  </div>
                </div>
                <span class="tr-club">CS Society</span>
                <span class="tr-category">Expense</span>
                <span class="tr-amount expense">−₱6,000</span>
                <span class="tr-status-badge badge-pending">Pending</span>
              </div>

              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-icon icon-income"><i class="fas fa-arrow-down"></i></div>
                  <div class="tr-info">
                    <span class="tr-title">Fundraising — Bake Sale</span>
                    <span class="tr-sub">Mar 26, 2026</span>
                  </div>
                </div>
                <span class="tr-club">Env. Society</span>
                <span class="tr-category">Income</span>
                <span class="tr-amount income">+₱3,450</span>
                <span class="tr-status-badge badge-approved">Approved</span>
              </div>

              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-icon icon-expense"><i class="fas fa-arrow-up"></i></div>
                  <div class="tr-info">
                    <span class="tr-title">Sound System Equipment Rental</span>
                    <span class="tr-sub">Mar 25, 2026</span>
                  </div>
                </div>
                <span class="tr-club">Music Club</span>
                <span class="tr-category">Expense</span>
                <span class="tr-amount expense">−₱4,500</span>
                <span class="tr-status-badge badge-approved">Approved</span>
              </div>

              <div class="table-row">
                <div class="tr-title-col">
                  <div class="tr-icon icon-pending"><i class="fas fa-clock"></i></div>
                  <div class="tr-info">
                    <span class="tr-title">Leadership Training Catering</span>
                    <span class="tr-sub">Mar 24, 2026</span>
                  </div>
                </div>
                <span class="tr-club">Student Admin</span>
                <span class="tr-category">Expense</span>
                <span class="tr-amount expense">−₱3,800</span>
                <span class="tr-status-badge badge-pending">Pending</span>
              </div>

            </div>
          </div>

          <!-- Club Budget Breakdown -->
          <div class="card">
            <div class="card-header">
              <h2>Club Budget Overview</h2>
              <a href="#" class="see-all-link">View All <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="budget-list">

              <div class="budget-item">
                <div class="budget-item-top">
                  <span class="budget-club-name">CS Society</span>
                  <span class="budget-amounts">Spent: <span>₱18,200</span> / ₱30,000</span>
                </div>
                <div class="progress-bar-wrap">
                  <div class="progress-bar bar-green" style="width: 61%;"></div>
                </div>
                <div class="budget-footer">
                  <span class="budget-pct">61% used</span>
                  <span class="budget-remaining">₱11,800 remaining</span>
                </div>
              </div>

              <div class="budget-item">
                <div class="budget-item-top">
                  <span class="budget-club-name">Athletics Department</span>
                  <span class="budget-amounts">Spent: <span>₱22,500</span> / ₱25,000</span>
                </div>
                <div class="progress-bar-wrap">
                  <div class="progress-bar bar-orange" style="width: 90%;"></div>
                </div>
                <div class="budget-footer">
                  <span class="budget-pct">90% used</span>
                  <span class="budget-remaining">₱2,500 remaining</span>
                </div>
              </div>

              <div class="budget-item">
                <div class="budget-item-top">
                  <span class="budget-club-name">Fine Arts Club</span>
                  <span class="budget-amounts">Spent: <span>₱6,800</span> / ₱20,000</span>
                </div>
                <div class="progress-bar-wrap">
                  <div class="progress-bar bar-gold" style="width: 34%;"></div>
                </div>
                <div class="budget-footer">
                  <span class="budget-pct">34% used</span>
                  <span class="budget-remaining">₱13,200 remaining</span>
                </div>
              </div>

              <div class="budget-item">
                <div class="budget-item-top">
                  <span class="budget-club-name">Debate Society</span>
                  <span class="budget-amounts">Spent: <span>₱15,900</span> / ₱15,000</span>
                </div>
                <div class="progress-bar-wrap">
                  <div class="progress-bar bar-red" style="width: 100%;"></div>
                </div>
                <div class="budget-footer">
                  <span class="budget-pct">Over budget</span>
                  <span class="budget-remaining" style="color: var(--red-accent);">₱900 over</span>
                </div>
              </div>

            </div>
          </div>

        </div>
 

        <!--RIGHT COLUMN -->
        <div class="finance-side-col">


          <div class="card">
            <div class="card-header">
              <h2>Fund Requests</h2>
              <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:var(--orange-bg);color:var(--orange);">3 pending</span>
            </div>
            <div class="request-list">

              <div class="request-item">
                <div class="request-item-top">
                  <div class="request-icon icon-red"><i class="fas fa-microphone-lines"></i></div>
                  <div class="request-title">Debate Competition Materials</div>
                  <span class="request-urgency urgency-high">High</span>
                </div>
                <div class="request-meta">
                  <span><i class="fas fa-building-columns"></i> Debate Society</span>
                  <span><i class="fas fa-calendar"></i> Apr 5</span>
                </div>
                <div class="request-amount">₱4,500</div>
                <div class="request-actions">
                  <button class="btn-approve" onclick="quickApprove(this)">Approve</button>
                  <button class="btn-reject" onclick="quickReject(this)">Reject</button>
                </div>
              </div>

              <div class="request-item">
                <div class="request-item-top">
                  <div class="request-icon icon-orange"><i class="fas fa-palette"></i></div>
                  <div class="request-title">Art Exhibition Supplies</div>
                  <span class="request-urgency urgency-medium">Medium</span>
                </div>
                <div class="request-meta">
                  <span><i class="fas fa-building-columns"></i> Fine Arts Club</span>
                  <span><i class="fas fa-calendar"></i> Apr 10</span>
                </div>
                <div class="request-amount">₱3,800</div>
                <div class="request-actions">
                  <button class="btn-approve" onclick="quickApprove(this)">Approve</button>
                  <button class="btn-reject" onclick="quickReject(this)">Reject</button>
                </div>
              </div>

              <div class="request-item">
                <div class="request-item-top">
                  <div class="request-icon icon-green"><i class="fas fa-leaf"></i></div>
                  <div class="request-title">Cleanup Drive Equipment</div>
                  <span class="request-urgency urgency-low">Low</span>
                </div>
                <div class="request-meta">
                  <span><i class="fas fa-building-columns"></i> Env. Society</span>
                  <span><i class="fas fa-calendar"></i> Apr 18</span>
                </div>
                <div class="request-amount">₱1,200</div>
                <div class="request-actions">
                  <button class="btn-approve" onclick="quickApprove(this)">Approve</button>
                  <button class="btn-reject" onclick="quickReject(this)">Reject</button>
                </div>
              </div>

            </div>
          </div>

          <!-- Quick Summary -->
          <div class="card">
            <div class="card-header">
              <h2>Monthly Summary</h2>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;">

              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--main-bg);border-radius:var(--radius-sm);">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="width:8px;height:8px;border-radius:50%;background:var(--green-accent);"></div>
                  <span style="font-size:12px;font-weight:600;color:var(--text-dark);">Total Income</span>
                </div>
                <span style="font-size:13px;font-weight:800;color:var(--green-accent);">₱64,200</span>
              </div>

              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--main-bg);border-radius:var(--radius-sm);">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="width:8px;height:8px;border-radius:50%;background:var(--red-accent);"></div>
                  <span style="font-size:12px;font-weight:600;color:var(--text-dark);">Total Expenses</span>
                </div>
                <span style="font-size:13px;font-weight:800;color:var(--red-accent);">₱38,750</span>
              </div>

              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:linear-gradient(135deg,#1e5c38,#0d3320);border-radius:var(--radius-sm);">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="width:8px;height:8px;border-radius:50%;background:#4ade80;"></div>
                  <span style="font-size:12px;font-weight:700;color:rgba(255,255,255,0.85);">Net Balance</span>
                </div>
                <span style="font-size:14px;font-weight:800;color:#fff;">₱25,450</span>
              </div>

              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--main-bg);border-radius:var(--radius-sm);">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="width:8px;height:8px;border-radius:50%;background:var(--gold);"></div>
                  <span style="font-size:12px;font-weight:600;color:var(--text-dark);">Pending Amount</span>
                </div>
                <span style="font-size:13px;font-weight:800;color:var(--gold);">₱12,000</span>
              </div>

            </div>
          </div>

        </div>

      </div>
    </div>
  </main>
</div>

<!-- ADD RECORD MODAL -->
<div class="modal-overlay" id="addRecordModal" onclick="handleOverlayClick(event,'addRecordModal')">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Finance Record</span>
      <button class="modal-close" onclick="closeModal('addRecordModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <input type="text" class="form-input" placeholder="e.g. Membership Dues Collection" />
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Type</label>
        <select class="form-select">
          <option>Income</option>
          <option>Expense</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Category</label>
        <select class="form-select">
          <option>Dues</option>
          <option>Fundraising</option>
          <option>Event Expense</option>
          <option>Supplies</option>
          <option>Rental</option>
          <option>Other</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Amount (₱)</label>
        <input type="number" class="form-input" placeholder="0.00" />
      </div>
      <div class="form-group">
        <label class="form-label">Date</label>
        <input type="date" class="form-input" value="2026-04-01" />
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Club</label>
      <select class="form-select">
        <option>CS Society</option>
        <option>Athletics Department</option>
        <option>Fine Arts Club</option>
        <option>Debate Society</option>
        <option>Environmental Society</option>
        <option>Music Club</option>
        <option>Student Administration</option>
        <option>Business Management Club</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Notes (optional)</label>
      <textarea class="form-textarea" placeholder="Add any relevant notes…"></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeModal('addRecordModal')">Cancel</button>
      <button class="btn-primary">Save Record</button>
    </div>
  </div>
</div>

<script>
  function setTab(el) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
  }

  function openModal(id) { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  function handleOverlayClick(e, id) { if (e.target === document.getElementById(id)) closeModal(id); }

  function quickApprove(btn) {
    const item = btn.closest('.request-item');
    item.style.opacity = '0';
    item.style.transform = 'translateX(10px)';
    item.style.transition = 'all 0.3s';
    setTimeout(() => item.remove(), 300);
  }

  function quickReject(btn) {
    const item = btn.closest('.request-item');
    item.style.opacity = '0';
    item.style.transform = 'translateX(-10px)';
    item.style.transition = 'all 0.3s';
    setTimeout(() => item.remove(), 300);
  }

  document.querySelectorAll('.card').forEach(card => {
  card.addEventListener('wheel', function(e) {
    const scrollable = this.scrollHeight > this.clientHeight;
    if (scrollable) {
      e.stopPropagation();
    }
  });
});
</script>
</body>
</html>