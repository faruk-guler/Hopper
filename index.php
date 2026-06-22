<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hopper 🦗</title>
  <link rel="stylesheet" href="styles.css?v=6">
  <style>
    /* Authentication Overlay */
    .login-screen-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(7, 8, 14, 0.88);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition-smooth);
    }
    .login-card-content {
      width: 100%;
      max-width: 450px;
      border-radius: var(--border-radius-md);
      padding: 36px;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      box-shadow: var(--shadow-main);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }
    .w-full {
      width: 100% !important;
    }

    /* User Directory Table Styles */
    .user-directory-table tbody tr {
      border-bottom: 1px solid var(--glass-border);
      transition: var(--transition-smooth);
    }
    .user-directory-table tbody tr:hover {
      background: rgba(255, 255, 255, 0.02);
    }
    .user-directory-table td, .user-directory-table th {
      padding: 14px 16px;
      vertical-align: middle;
      font-size: 0.9rem;
    }
    .user-directory-table th {
      font-weight: 700;
      border-bottom: 2px solid var(--glass-border);
    }

    /* Password visibility toggle styles */
    .password-wrapper {
      position: relative;
      display: block;
      width: 100%;
    }
    .password-wrapper input {
      padding-right: 44px !important;
      width: 100%;
      box-sizing: border-box;
    }
    .password-toggle-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none !important;
      border: none !important;
      color: #94a3b8 !important;
      cursor: pointer !important;
      padding: 0 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      transition: var(--transition-smooth);
      z-index: 100 !important;
      min-height: auto !important;
      width: 24px !important;
      height: 24px !important;
    }
    .password-toggle-btn:hover {
      color: #c084fc !important;
    }
    .password-toggle-btn svg {
      width: 18px !important;
      height: 18px !important;
      display: block !important;
      pointer-events: none !important;
    }
  </style>
  <!-- Lucide Icons for beautiful modern vector icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <div class="app-container">
    
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
      <div class="logo-container">
        <div class="logo-icon" style="overflow: hidden; border-radius: 10px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: none; border: 1px solid var(--glass-border);">
          <img src="images/hopper.png" alt="Hopper Logo" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <span class="logo-text">Hopper</span>
      </div>
      
      <nav style="flex: 1;">
        <ul class="sidebar-menu">
          <li>
            <a href="#" class="menu-item active" data-tab="dashboard">
              <i data-lucide="layout-dashboard"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li>
            <a href="#" class="menu-item" data-tab="changes">
              <i data-lucide="clipboard-list"></i>
              <span>Change Requests</span>
            </a>
          </li>
          <li>
            <a href="#" class="menu-item" data-tab="approvals">
              <i data-lucide="check-square"></i>
              <span>Approval Center</span>
              <span id="nav-approval-badge" class="badge" style="background: rgba(245,158,11,0.2); color: var(--status-pending); margin-left: auto; display: none; padding: 2px 8px;">0</span>
            </a>
          </li>
          <li>
            <a href="#" class="menu-item" data-tab="calendar">
              <i data-lucide="calendar"></i>
              <span>Change Calendar</span>
            </a>
          </li>
          <li id="nav-users-li" style="display: none;">
            <a href="#" class="menu-item" data-tab="users">
              <i data-lucide="users"></i>
              <span>User Directory</span>
            </a>
          </li>
          <li>
            <a href="#" class="menu-item" data-tab="about">
              <i data-lucide="info"></i>
              <span>About</span>
            </a>
          </li>
        </ul>
      </nav>
      
    </aside>

    <!-- Main Content Grid -->
    <main class="main-content">
      
      <!-- Top Header Bar -->
      <header class="header-bar">
        <div class="page-title">
          <h1 id="main-title">Dashboard</h1>
          <p id="main-subtitle">Change requests and overall operational status</p>
        </div>
        <div class="header-actions" style="display: flex; align-items: center; gap: 16px;">
          <button class="btn btn-primary" id="btn-new-change">
            <i data-lucide="plus"></i>
            New Change Request
          </button>

          <!-- Profile Dropdown Container -->
          <div class="profile-dropdown-wrapper" style="position: relative; display: inline-block;">
            <!-- Trigger Card -->
            <div class="user-profile" id="header-user-profile" style="border: 1px solid var(--glass-border); padding: 6px 14px; border-radius: var(--border-radius-sm); background: var(--glass-bg); display: flex; align-items: center; gap: 10px; cursor: pointer; transition: var(--transition-smooth);" onmouseover="this.style.background='var(--glass-bg-hover)'; this.style.borderColor='var(--glass-border-focus)';" onmouseout="this.style.background='var(--glass-bg)'; this.style.borderColor='var(--glass-border)';" title="Profile Menu">
              <div class="user-avatar" id="nav-user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: 700; color: #c084fc; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--glass-bg-hover); border: 1px solid var(--glass-border);">ADM</div>
              <div class="user-info" style="display: flex; flex-direction: column; text-align: left;">
                <span class="user-name" id="nav-user-name" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); line-height: 1.2;">administrator</span>
                <span class="user-role" id="nav-user-role" style="font-size: 0.7rem; color: var(--text-muted); line-height: 1.2;">IT Operations</span>
              </div>
              <i data-lucide="chevron-down" style="width: 14px; height: 14px; color: var(--text-muted); margin-left: 2px;"></i>
            </div>

            <!-- GitHub Style Floating Menu -->
            <div class="github-dropdown" id="profile-dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 220px; border-radius: 12px; z-index: 1000; flex-direction: column; padding: 8px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;">
              <!-- Menu Navigation Links -->
              <div style="display: flex; flex-direction: column;">
                <a href="#" class="dropdown-item" data-dropdown-tab="profile">
                  <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                  <span data-i18n="my_profile">My Profile</span>
                </a>
              </div>

              <div style="border-top: 1px solid var(--glass-border); margin: 4px 0;"></div>

              <!-- Settings & Theme switch -->
              <div style="display: flex; flex-direction: column;">
                <a href="#" class="dropdown-item" data-dropdown-tab="settings">
                  <i data-lucide="settings" style="width: 16px; height: 16px;"></i>
                  <span data-i18n="settings">Settings</span>
                </a>
                <!-- Custom Theme Toggle Switch inside dropdown -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; color: var(--text-main); font-size: 0.85rem;">
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="palette" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
                    <span>Theme Mode</span>
                  </div>
                  <!-- Theme Toggle Switch -->
                  <label class="switch-toggle" style="margin-bottom: 0;">
                    <input type="checkbox" id="theme-mode-checkbox">
                    <span class="switch-slider"></span>
                  </label>
                </div>
              </div>

              <div style="border-top: 1px solid var(--glass-border); margin: 4px 0;"></div>

              <!-- Sign out -->
              <a href="#" class="dropdown-item text-danger" id="btn-logout">
                <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                <span data-i18n="sign_out">Sign out</span>
              </a>
            </div>
          </div>
        </div>
      </header>

      <!-- TAB 1: DASHBOARD -->
      <section id="tab-dashboard" class="tab-content active">
        <!-- KPI Cards Grid -->
        <div class="kpi-grid">
          <div class="glass-card kpi-card">
            <div class="kpi-info">
              <span class="kpi-label">Total Changes</span>
              <span class="kpi-value" id="kpi-total">0</span>
            </div>
            <div class="kpi-icon-wrapper">
              <i data-lucide="layers"></i>
            </div>
          </div>
          <div class="glass-card kpi-card">
            <div class="kpi-info">
              <span class="kpi-label">Awaiting Approval</span>
              <span class="kpi-value" id="kpi-pending">0</span>
            </div>
            <div class="kpi-icon-wrapper">
              <i data-lucide="clock"></i>
            </div>
          </div>
          <div class="glass-card kpi-card">
            <div class="kpi-info">
              <span class="kpi-label">Implementing</span>
              <span class="kpi-value" id="kpi-implementing">0</span>
            </div>
            <div class="kpi-icon-wrapper">
              <i data-lucide="play-circle"></i>
            </div>
          </div>
          <div class="glass-card kpi-card">
            <div class="kpi-info">
              <span class="kpi-label">Success Rate</span>
              <span class="kpi-value" id="kpi-success-rate">100%</span>
            </div>
            <div class="kpi-icon-wrapper">
              <i data-lucide="trending-up"></i>
            </div>
          </div>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="dashboard-grid">
          <!-- Active / Urgent Changes List -->
          <div class="glass-card">
            <div class="section-header">
              <h3>Active Change Tracking</h3>
              <button class="btn btn-secondary btn-sm" id="btn-view-all-changes">View All</button>
            </div>
            <div class="list-container" id="dashboard-active-changes">
              <!-- Rendered via JS -->
            </div>
          </div>

          <!-- Recent Activity Log -->
          <div class="glass-card">
            <div class="section-header">
              <h3>Recent Activities</h3>
            </div>
            <div class="activity-feed" id="dashboard-activity-feed">
              <!-- Rendered via JS -->
            </div>
          </div>
        </div>
      </section>

      <!-- TAB 2: CHANGES LIST -->
      <section id="tab-changes" class="tab-content">
        <!-- Search & Filter Controls -->
        <div class="glass-card filters-bar">
          <div class="search-wrapper">
            <i data-lucide="search"></i>
            <input type="text" id="search-query" class="form-control search-input" placeholder="Search by request title, ID, or owner...">
          </div>
          <select id="filter-status" class="form-control filter-select">
            <option value="">All Statuses</option>
            <option value="Draft">Draft</option>
            <option value="Under Review">Under Review</option>
            <option value="Pending Approval">Pending Approval</option>
            <option value="Approved">Approved</option>
            <option value="Implementing">Implementing</option>
            <option value="Completed">Completed</option>
            <option value="Rolled Back">Rolled Back</option>
            <option value="Rejected">Rejected</option>
          </select>
          <select id="filter-risk" class="form-control filter-select">
            <option value="">All Risk Levels</option>
            <option value="High">High Risk</option>
            <option value="Medium">Medium Risk</option>
            <option value="Low">Low Risk</option>
          </select>
          <button class="btn btn-secondary" id="btn-export-csv" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; white-space: nowrap;">
            <i data-lucide="download" style="width: 16px; height: 16px;"></i> Export CAB Report
          </button>
        </div>

        <!-- Main Table/List -->
        <div class="glass-card">
          <div class="list-container" id="all-changes-list">
            <!-- Rendered via JS -->
          </div>
        </div>
      </section>

      <!-- TAB 3: APPROVALS -->
      <section id="tab-approvals" class="tab-content">
        <div class="glass-card" style="margin-bottom: 24px;">
          <p style="color: var(--text-muted);">List of change requests awaiting Change Advisory Board (CAB) and stakeholder approval. Evaluate approval steps based on business continuity and risk analysis.</p>
        </div>
        <div class="glass-card">
          <div class="list-container" id="approvals-list">
            <!-- Rendered via JS -->
          </div>
        </div>
      </section>

      <!-- TAB 4: CALENDAR -->
      <section id="tab-calendar" class="tab-content">
        <div class="glass-card calendar-wrapper">
          <div class="calendar-header">
            <button class="btn btn-secondary" id="calendar-prev-month">
              <i data-lucide="chevron-left"></i>
            </button>
            <span class="calendar-month-name" id="calendar-month-label">June 2026</span>
            <button class="btn btn-secondary" id="calendar-next-month">
              <i data-lucide="chevron-right"></i>
            </button>
          </div>
          
          <div class="calendar-grid" id="calendar-grid-container">
            <!-- Rendered via JS -->
          </div>
        </div>
      </section>

      <!-- TAB 6: PROFILE -->
      <section id="tab-profile" class="tab-content">
        <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
          <div class="section-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 16px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px;">
            <h3 style="margin: 0;">My Profile Settings</h3>
            <div style="display: flex; align-items: center; gap: 16px;">
              <div id="profile-status-message" style="font-size: 0.9rem; font-weight: 600; display: none;"></div>
              <button type="submit" form="form-profile" class="btn btn-primary" style="margin: 0;">
                <i data-lucide="save"></i> Save Profile Changes
              </button>
            </div>
          </div>
          
          <form id="form-profile" style="display: flex; gap: 32px; flex-wrap: wrap;">
            <!-- Left Column: Avatar upload -->
            <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column; align-items: center; gap: 16px;">
              <div style="position: relative; width: 140px; height: 140px; border-radius: 50%; overflow: hidden; background: var(--glass-bg-hover); border: 2px solid var(--glass-border-focus); display: flex; align-items: center; justify-content: center; cursor: pointer;" id="avatar-container" title="Click to upload profile image">
                <img id="profile-avatar-img" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;" alt="Avatar">
                <span id="profile-avatar-placeholder" style="font-size: 3rem; font-weight: 800; color: #c084fc;">ADM</span>
                
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 35%; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; opacity: 0; transition: var(--transition-smooth);" id="avatar-hover-overlay">
                  <i data-lucide="camera" style="width: 20px; height: 20px; color: #fff;"></i>
                </div>
              </div>
              <input type="file" id="profile-avatar-input" accept="image/*" style="display: none;">
              <span class="badge" id="profile-role-badge" style="background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); color: #c084fc; font-size: 0.8rem; padding: 4px 16px;">Administrator</span>
              <p style="font-size: 0.75rem; color: var(--text-sub); text-align: center;">Supports PNG, JPG. Max 1MB. Image is saved inside the database.</p>
            </div>

            <!-- Right Column: Profile fields -->
            <div style="flex: 2; min-width: 300px; display: flex; flex-direction: column; gap: 16px;">
              <div class="form-grid" style="margin-bottom: 0;">
                <div class="form-group">
                  <label for="profile-name">Full Name *</label>
                  <input type="text" id="profile-name" class="form-control" required>
                </div>
                <div class="form-group">
                  <label for="profile-username">Username (Read-only)</label>
                  <input type="text" id="profile-username" class="form-control" disabled>
                </div>
                <div class="form-group">
                  <label for="profile-title">Job Title</label>
                  <input type="text" id="profile-title" class="form-control">
                </div>
                <div class="form-group">
                  <label for="profile-department">Department *</label>
                  <select id="profile-department" class="form-control" required>
                    <option value="Management">Management</option>
                    <option value="IT Operations">IT Operations</option>
                    <option value="Human Resources">Human Resources</option>
                    <option value="Accounting">Accounting</option>
                    <option value="Sales">Sales</option>
                    <option value="Marketing">Marketing</option>
                    <option value="R&D">R&D</option>
                    <option value="Logistics">Logistics</option>
                    <option value="Warehouse">Warehouse</option>
                    <option value="Security">Security</option>
                    <option value="Technical Service">Technical Service</option>
                    <option value="Quality Control">Quality Control</option>
                    <option value="Training">Training</option>
                    <option value="Purchasing">Purchasing</option>
                    <option value="Finance & Accounting">Finance & Accounting</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="profile-email">Email Address</label>
                  <input type="email" id="profile-email" class="form-control" placeholder="e.g. name@company.com">
                </div>
                <div class="form-group">
                  <label for="profile-phone">Phone Number</label>
                  <input type="text" id="profile-phone" class="form-control" placeholder="e.g. +90 (555) 123 4567">
                </div>
                <div class="form-group">
                  <label for="profile-new-password">New Password</label>
                  <div class="password-wrapper">
                    <input type="password" id="profile-new-password" class="form-control" placeholder="Leave empty to keep current">
                    <button type="button" class="password-toggle-btn" toggle-target="profile-new-password" title="Toggle password visibility">
                      <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
                    </button>
                  </div>
                </div>
                <div class="form-group">
                  <label for="profile-confirm-password">Confirm Password</label>
                  <div class="password-wrapper">
                    <input type="password" id="profile-confirm-password" class="form-control" placeholder="Confirm new password">
                    <button type="button" class="password-toggle-btn" toggle-target="profile-confirm-password" title="Toggle password visibility">
                      <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
                    </button>
                  </div>
                </div>
              </div>
              

            </div>
          </form>
        </div>
      </section>

      <!-- TAB 7: USER DIRECTORY (Admin Only) -->
      <section id="tab-users" class="tab-content">
        <!-- Pending Registration Requests -->
        <div class="glass-card" id="admin-pending-registrations-card" style="max-width: 900px; margin: 0 auto 24px auto; display: none;">
          <div class="section-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div>
              <h3>Pending Registration Requests</h3>
              <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Review and approve new user account registrations.</p>
            </div>
            <span class="badge badge-risk medium" id="pending-reg-count" style="background: rgba(245,158,11,0.2); color: var(--status-pending); font-weight: 700;">0 Pending</span>
          </div>
          
          <div class="list-container" style="overflow-x: auto;">
            <table class="user-directory-table" style="width: 100%; border-collapse: collapse; text-align: left;">
              <thead>
                <tr style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-family: var(--font-heading);">
                  <th style="padding: 12px 16px;">Applicant</th>
                  <th style="padding: 12px 16px;">Job Title & Dept</th>
                  <th style="padding: 12px 16px;">Requested Role</th>
                  <th style="padding: 12px 16px;">Date</th>
                  <th style="padding: 12px 16px; text-align: right; width: 180px;">Actions</th>
                </tr>
              </thead>
              <tbody id="admin-pending-users-tbody">
                <!-- Rendered dynamically by JS -->
              </tbody>
            </table>
          </div>
        </div>

        <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
          <div class="section-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 16px; margin-bottom: 20px;">
            <h3>User Directory & Role Management (Admin Only)</h3>
            <span class="badge badge-risk high">System Access Control</span>
          </div>
          
          <div class="list-container" style="overflow-x: auto;">
            <table class="user-directory-table" style="width: 100%; border-collapse: collapse; text-align: left;">
              <thead>
                <tr style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-family: var(--font-heading);">
                  <th style="padding: 12px 16px;">User</th>
                  <th style="padding: 12px 16px;">Contact Info</th>
                  <th style="padding: 12px 16px;">Job Title</th>
                  <th style="padding: 12px 16px;">Department</th>
                  <th style="padding: 12px 16px; width: 180px;">System Role</th>
                  <th style="padding: 12px 16px; text-align: right; width: 100px;">Actions</th>
                </tr>
              </thead>
              <tbody id="admin-users-tbody">
                <!-- Rendered dynamically by JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- TAB 8: GENERAL SETTINGS -->
      <section id="tab-settings" class="tab-content">
        <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
          <div class="section-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 16px; margin-bottom: 24px;">
            <h3>General System Settings</h3>
            <span class="badge badge-risk high" id="settings-admin-badge">System Administration</span>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; flex-wrap: wrap;" id="settings-container">
            <!-- Left Column: Department Management -->
            <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.01);">
              <h4 style="margin-bottom: 12px; font-size: 1.15rem; color: var(--text-main); font-weight: 600;">Manage Departments</h4>
              <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px;">Add or remove departments available for user registrations and profiles.</p>
              
              <!-- Add Department Form (Admin Only) -->
              <div id="add-dept-wrapper" style="display: flex; gap: 8px; margin-bottom: 16px;">
                <input type="text" id="new-dept-name" class="form-control" placeholder="e.g. Muhasebe" style="font-size: 0.85rem; padding: 8px 12px; flex: 1;">
                <button class="btn btn-primary" id="btn-add-dept" style="padding: 8px 16px; font-size: 0.85rem;"><i data-lucide="plus"></i> Add</button>
              </div>

              <!-- Departments List -->
              <div style="max-height: 300px; overflow-y: auto; border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); padding: 8px;" id="settings-depts-list">
                <!-- Rendered dynamically -->
              </div>
            </div>

            <!-- Right Column: Category Management -->
            <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.01);">
              <h4 style="margin-bottom: 12px; font-size: 1.15rem; color: var(--text-main); font-weight: 600;">Manage Change Categories</h4>
              <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px;">Configure options for change request categorization.</p>
              
              <!-- Add Category Form (Admin Only) -->
              <div id="add-cat-wrapper" style="display: flex; gap: 8px; margin-bottom: 16px;">
                <input type="text" id="new-cat-name" class="form-control" placeholder="e.g. Infrastructure" style="font-size: 0.85rem; padding: 8px 12px; flex: 1;">
                <button class="btn btn-primary" id="btn-add-cat" style="padding: 8px 16px; font-size: 0.85rem;"><i data-lucide="plus"></i> Add</button>
              </div>

              <!-- Categories List -->
              <div style="max-height: 300px; overflow-y: auto; border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); padding: 8px;" id="settings-cats-list">
                <!-- Rendered dynamically -->
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- TAB 5: ABOUT -->
      <section id="tab-about" class="tab-content">
        <div class="glass-card" style="max-width: 800px; margin: 40px auto 0 auto; display: flex; flex-direction: column; gap: 24px; text-align: center; padding: 48px 32px;">
          <div style="width: 140px; height: 140px; border-radius: 36px; overflow: hidden; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 2px solid var(--glass-border-focus); box-shadow: 0 8px 30px rgba(139, 92, 246, 0.35);">
            <img src="images/hopper.png" alt="Hopper Logo" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div>
            <h2 class="about-title" style="font-size: 2rem; font-weight: 800; margin: 0 auto;">About Hopper</h2>
            <p style="color: var(--text-muted); margin-top: 12px; font-size: 1.05rem;">Hopper is a premium, lightweight Change Management System designed to track, coordinate, and review infrastructure and software change requests with complete audit control and workflow transparency.</p>
          </div>
          
          <div style="border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); padding: 24px 0; display: flex; flex-direction: column; gap: 12px; align-items: center;">
            <p style="color: var(--text-main); font-weight: 600; font-size: 1.1rem;">Lead Developer</p>
            <p style="font-size: 1.35rem; font-weight: 800; color: #c084fc; font-family: var(--font-heading);">Faruk Güler</p>
          </div>

          <div style="display: flex; justify-content: center; gap: 24px;">
            <a href="https://github.com/faruk-guler" target="_blank" class="btn btn-secondary" style="text-decoration: none;">
              <i data-lucide="github"></i> GitHub Profile
            </a>
            <a href="http://www.farukguler.com" target="_blank" class="btn btn-primary" style="text-decoration: none;">
              <i data-lucide="globe"></i> Personal Website
            </a>
          </div>
        </div>
      </section>

    </main>
  </div>

  <!-- MODAL: CREATE NEW CHANGE REQUEST -->
  <div class="modal-overlay" id="modal-create-change">
    <div class="glass-card modal-content">
      <div class="modal-header">
        <h3 data-i18n="create_title">Create New Change Request</h3>
        <button class="modal-close" id="modal-create-close">
          <i data-lucide="x"></i>
        </button>
      </div>
      
      <form id="form-create-change">
        <div class="form-grid">
          <div class="form-group form-full">
            <label for="change-title" data-i18n="req_title_label">Request Title *</label>
            <input type="text" id="change-title" class="form-control" placeholder="Change title" data-i18n-placeholder="change_title_placeholder" required>
          </div>

          <div class="form-group form-full">
            <label for="change-description" data-i18n="desc_label">Description & Business Case *</label>
            <textarea id="change-description" class="form-control" placeholder="Explain why this change is needed and what will be done..." data-i18n-placeholder="desc_placeholder" required></textarea>
          </div>

          <div class="form-group">
            <label for="change-requester" data-i18n="requester_name_label">Requester (Full Name) *</label>
            <input type="text" id="change-requester" class="form-control" value="administrator" required>
          </div>

          <div class="form-group">
            <label for="change-requester-title" data-i18n="requester_title_label">Requester Title</label>
            <input type="text" id="change-requester-title" class="form-control" value="IT Operations">
          </div>

          <div class="form-group">
            <label for="change-owner" data-i18n="owner_name_label">Implementation Owner *</label>
            <input type="text" id="change-owner" class="form-control" value="administrator" required>
          </div>

          <div class="form-group">
            <label for="change-owner-title" data-i18n="owner_title_label">Owner Title</label>
            <input type="text" id="change-owner-title" class="form-control" value="IT Operations">
          </div>

          <div class="form-group">
            <label for="change-category" data-i18n="category_label">Category *</label>
            <select id="change-category" class="form-control" required>
              <!-- Populated by JS -->
            </select>
          </div>

          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <label for="change-risk" style="margin-bottom: 0;">Risk Level *</label>
              <button type="button" id="btn-calc-risk" style="background: none; border: none; color: #a78bfa; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0;">
                <i data-lucide="sparkles" style="width: 12px; height: 12px;"></i> Calculate Risk
              </button>
            </div>
            <select id="change-risk" class="form-control" required>
              <option value="Low">Low</option>
              <option value="Medium">Medium</option>
              <option value="High">High</option>
            </select>
          </div>

          <!-- Risk Calculator Interactive Panel -->
          <div class="form-group form-full" id="risk-calc-panel" style="display: none; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); padding: 16px; margin-top: 10px; transition: var(--transition-smooth); width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--glass-border); padding-bottom: 8px;">
              <h5 style="margin: 0; font-size: 0.85rem; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <i data-lucide="calculator" style="width: 14px; height: 14px; color: #a78bfa;"></i> Risk Assessment Guide
              </h5>
              <button type="button" id="btn-close-risk-calc" style="background: none; border: none; color: var(--text-muted); font-size: 0.75rem; cursor: pointer; padding: 0;">Close</button>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.8rem;">
              <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-muted);">
                <input type="checkbox" id="risk-q-prod" style="width: 14px; height: 14px;"> Affects Production Environment
              </label>
              <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-muted);">
                <input type="checkbox" id="risk-q-downtime" style="width: 14px; height: 14px;"> Requires System Downtime / Maintenance Window
              </label>
              <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-muted);">
                <input type="checkbox" id="risk-q-untested" style="width: 14px; height: 14px;"> Has NOT been tested in Pre-Prod/Staging Environment
              </label>
              <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-muted);">
                <input type="checkbox" id="risk-q-no-rollback" style="width: 14px; height: 14px;"> Rollback Plan is untested/complex
              </label>
              <div style="border-top: 1px solid var(--glass-border); padding-top: 8px; margin-top: 4px; display: flex; justify-content: space-between; align-items: center;">
                <span>Calculated Level:</span>
                <span id="calculated-risk-badge" class="badge badge-risk low" style="font-weight: 700;">Low Risk</span>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="change-target-date" data-i18n="target_date_label">Target Date *</label>
            <input type="date" id="change-target-date" class="form-control" required>
          </div>

          <div class="form-group form-full">
            <label for="change-impact" data-i18n="impact_label">Impact Analysis *</label>
            <textarea id="change-impact" class="form-control" placeholder="Impact and outage details..." data-i18n-placeholder="impact_placeholder" required></textarea>
          </div>

          <div class="form-group form-full">
            <label for="change-rollback" data-i18n="rollback_label">Rollback Plan *</label>
            <textarea id="change-rollback" class="form-control" placeholder="Rollback steps in case of failure..." data-i18n-placeholder="rollback_placeholder" required></textarea>
          </div>

          <div class="form-group form-full">
            <label for="change-tasks" data-i18n="tasks_label">Implementation Steps / Tasks (One per line) *</label>
            <textarea id="change-tasks" class="form-control" placeholder="1. Task&#10;2. Task&#10;3. Task" data-i18n-placeholder="tasks_placeholder" required></textarea>
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
          <button type="button" class="btn btn-secondary" id="btn-create-cancel" data-i18n="cancel">Cancel</button>
          <button type="submit" class="btn btn-primary" data-i18n="save_draft">Save Request (Draft)</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: CHANGE DETAILS & WORKFLOW -->
  <div class="modal-overlay" id="modal-detail">
    <div class="glass-card modal-content" style="max-width: 950px;">
      <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 12px;">
          <span id="detail-id" class="change-id" style="font-size: 1.25rem;">CHG-XXXX</span>
          <h3 id="detail-title" style="margin: 0;">Change Title</h3>
        </div>
        <button class="modal-close" id="modal-detail-close">
          <i data-lucide="x"></i>
        </button>
      </div>

      <!-- Conflict Warning Banner -->
      <div id="detail-conflict-banner" style="display: none; align-items: center; gap: 10px; padding: 12px 16px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--border-radius-sm); margin-bottom: 20px; color: var(--color-high); font-size: 0.9rem; font-weight: 500; width: 100%;">
        <i data-lucide="shield-alert" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
        <span id="detail-conflict-message">Warning: This change overlaps with other schedule windows of the same category.</span>
      </div>

      <div class="detail-layout">
        
        <!-- Left Side: Core Info, Impact, Tasks -->
        <div>
          <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
            <span id="detail-status" class="badge badge-status">Status</span>
            <span id="detail-risk" class="badge badge-risk">Risk</span>
            <span id="detail-category" class="badge" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);">Category</span>
          </div>

          <div class="detail-section">
            <h4 data-i18n="description_case">Description & Business Case</h4>
            <p id="detail-description" class="detail-text">Description will go here.</p>
          </div>

          <div class="detail-section">
            <h4 data-i18n="impact_analysis">Impact Analysis</h4>
            <p id="detail-impact" class="detail-text">Impact details will go here.</p>
          </div>

          <div class="detail-section">
            <h4 data-i18n="rollback_plan">Rollback Plan</h4>
            <p id="detail-rollback" class="detail-text">Rollback plan will go here.</p>
          </div>

          <div class="detail-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <h4 data-i18n="progress">Implementation Steps & Progress</h4>
              <span id="detail-progress-label" style="font-size: 0.85rem; font-weight: 700; color: #a78bfa;">0%</span>
            </div>
            <div class="progress-container">
              <div id="detail-progress-bar" class="progress-bar"></div>
            </div>
            <div class="checklist-container" id="detail-tasks-checklist">
              <!-- Checklist rendered via JS -->
            </div>
          </div>
        </div>

        <!-- Right Side: Workflow, Approvals, Action, Comments -->
        <div>
          
          <!-- Actions Panel -->
          <div class="detail-section">
            <h4 data-i18n="workflow_controls">Workflow Controls</h4>
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;" id="detail-actions-panel">
              <!-- Contextual action buttons will be injected here by app.js -->
            </div>
          </div>

          <!-- Metadata Info -->
          <div class="detail-section">
            <h4 data-i18n="request_details">Request Details</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.85rem; margin-top: 10px;">
              <div><span style="color: var(--text-sub);" data-i18n="requester">Requester</span>: <strong id="detail-requester">...</strong></div>
              <div><span style="color: var(--text-sub);" data-i18n="owner">Owner</span>: <strong id="detail-owner">...</strong></div>
              <div><span style="color: var(--text-sub);" data-i18n="target_date">Target Date</span>: <strong id="detail-date">...</strong></div>
            </div>
          </div>

          <!-- Approval status list -->
          <div class="detail-section">
            <h4 data-i18n="approval_states">Approval States</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;" id="detail-approval-list">
              <!-- Approval log rendered via JS -->
            </div>
          </div>

          <!-- Attachments Section -->
          <div class="detail-section">
            <h4>Runbooks & Documentation</h4>
            <div id="detail-attachment-container" style="margin-top: 10px; font-size: 0.85rem;">
              <!-- Will be populated dynamically by JS -->
            </div>
            <!-- Upload form, visible to owners/requesters/admins -->
            <div id="attachment-upload-wrapper" style="display: none; margin-top: 12px;">
              <label class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.75rem; width: 100%; justify-content: center; min-height: auto; padding: 6px 12px;" id="lbl-attachment-input">
                <i data-lucide="paperclip" style="width: 14px; height: 14px;"></i> Upload File Attachment
                <input type="file" id="change-attachment-input" style="display: none;" accept=".pdf,.txt,.docx,.xlsx,.png,.jpg,.jpeg,.zip">
              </label>
              <div id="attachment-upload-status" style="margin-top: 6px; font-size: 0.75rem; color: var(--text-muted); text-align: center;">Max size: 2MB. Formats: PDF, TXT, DOCX, XLSX, PNG, JPG, ZIP.</div>
            </div>
          </div>

          <!-- Comments Section -->
          <div class="detail-section">
            <h4 data-i18n="discussion_comments">Discussion & Comments</h4>
            <div class="comments-list" id="detail-comments-list" style="margin-top: 10px; margin-bottom: 12px;">
              <!-- Comments rendered via JS -->
            </div>
            <div style="display: flex; gap: 8px;">
              <input type="text" id="comment-input" class="form-control" style="font-size: 0.8rem; padding: 8px;" placeholder="Write a comment..." data-i18n-placeholder="write_comment">
              <button class="btn btn-primary" id="btn-add-comment" style="padding: 8px 12px;"><i data-lucide="send" style="width:16px; height:16px;"></i></button>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>

  <!-- MODAL: EDIT USER DETAILS (Admin Only) -->
  <div class="modal-overlay" id="modal-edit-user">
    <div class="glass-card modal-content" style="max-width: 600px;">
      <div class="modal-header">
        <h3 id="edit-user-modal-title" data-i18n="edit_user_title">Edit User Details</h3>
        <button class="modal-close" id="modal-edit-user-close">
          <i data-lucide="x"></i>
        </button>
      </div>
      
      <form id="form-edit-user">
        <input type="hidden" id="edit-user-id">
        <div class="form-grid">
          <div class="form-group">
            <label for="edit-user-username" data-i18n="username_label">Username</label>
            <input type="text" id="edit-user-username" class="form-control" readonly disabled style="opacity: 0.6; cursor: not-allowed;">
          </div>

          <div class="form-group">
            <label for="edit-user-name" data-i18n="full_name_label">Full Name *</label>
            <input type="text" id="edit-user-name" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="edit-user-title" data-i18n="job_title_label">Job Title</label>
            <input type="text" id="edit-user-title" class="form-control">
          </div>

          <div class="form-group">
            <label for="edit-user-department" data-i18n="department_label">Department *</label>
            <select id="edit-user-department" class="form-control" required>
              <!-- Will be populated dynamically by JS -->
            </select>
          </div>

          <div class="form-group">
            <label for="edit-user-role" data-i18n="system_role_label">System Role *</label>
            <select id="edit-user-role" class="form-control" required>
              <option value="Requester" data-i18n="role_req">Requester</option>
              <option value="CAB Approver" data-i18n="role_cab">CAB Approver</option>
              <option value="Administrator" data-i18n="role_admin">Administrator</option>
            </select>
          </div>

          <div class="form-group">
            <label for="edit-user-email" data-i18n="email_label">Email</label>
            <input type="email" id="edit-user-email" class="form-control">
          </div>

          <div class="form-group">
            <label for="edit-user-phone" data-i18n="phone_label">Phone</label>
            <input type="text" id="edit-user-phone" class="form-control">
          </div>

          <div class="form-group">
            <label for="edit-user-password" data-i18n="new_password_label">New Password</label>
            <div class="password-wrapper">
              <input type="password" id="edit-user-password" class="form-control" placeholder="Leave blank to keep current" data-i18n-placeholder="keep_password_placeholder">
              <button type="button" class="password-toggle-btn" toggle-target="edit-user-password" title="Toggle password visibility">
                <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
              </button>
            </div>
          </div>
        </div>

        <div id="edit-user-error" style="color: #f87171; font-size: 0.85rem; margin-top: 12px; margin-bottom: 12px; text-align: center; display: none;"></div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--glass-border); padding-top: 20px; margin-top: 20px;">
          <button type="button" class="btn btn-secondary" id="btn-edit-user-cancel" data-i18n="cancel">Cancel</button>
          <button type="submit" class="btn btn-primary" data-i18n="save_changes">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Authentication Screen Overlay -->
  <div id="login-screen" class="login-screen-overlay">
    <div class="glass-card login-card-content">
      <div class="logo-container" style="justify-content: center; border-bottom: none; margin-bottom: 16px; padding-bottom: 0; align-items: center;">
        <div class="logo-icon" style="overflow: hidden; border-radius: 14px; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; background: none; border: 1px solid var(--glass-border); margin-right: 12px; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);">
          <img src="images/hopper.png" alt="Hopper Logo" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <span class="logo-text" style="font-size: 2.25rem; font-weight: 800;">Hopper</span>
      </div>
      <h2 id="auth-title" class="auth-title" style="text-align: center; margin-bottom: 24px; font-size: 1.5rem;">Sign In to System</h2>
      
      <!-- Login Form -->
      <form id="form-login">
        <div class="form-group" style="margin-bottom: 16px;">
          <label for="login-username">Username</label>
          <input type="text" id="login-username" class="form-control" placeholder="Enter username (e.g. admin)" required>
        </div>
        <div class="form-group" style="margin-bottom: 24px;">
          <label for="login-password">Password</label>
          <div class="password-wrapper">
            <input type="password" id="login-password" class="form-control" placeholder="Enter password (e.g. admin123)" required>
            <button type="button" class="password-toggle-btn" toggle-target="login-password" title="Toggle password visibility">
              <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
            </button>
          </div>
        </div>
        <div id="login-error" style="color: #f87171; font-size: 0.85rem; margin-bottom: 16px; text-align: center; display: none;"></div>
        <button type="submit" class="btn btn-primary w-full" style="justify-content: center; width: 100%;">
          Sign In <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
        </button>
        <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 16px;">
          <span data-i18n="no_account">Don't have an account?</span> <a href="#" id="link-show-register" data-i18n="register_here" style="color: #c084fc; text-decoration: none; font-weight: 600;">Register here</a>
        </p>
      </form>

      <!-- Register Form -->
      <form id="form-register" style="display: none;">
        <div class="form-group" style="margin-bottom: 12px;">
          <label for="reg-name">Full Name *</label>
          <input type="text" id="reg-name" class="form-control" placeholder="e.g. Alice Smith" required>
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
          <label for="reg-username">Username *</label>
          <input type="text" id="reg-username" class="form-control" placeholder="Choose username" required>
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
          <label for="reg-password">Password *</label>
          <div class="password-wrapper">
            <input type="password" id="reg-password" class="form-control" placeholder="Password (min 4 chars)" required>
            <button type="button" class="password-toggle-btn" toggle-target="reg-password" title="Toggle password visibility">
              <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
            </button>
          </div>
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
          <label for="reg-title">Job Title</label>
          <input type="text" id="reg-title" class="form-control" placeholder="e.g. Systems Engineer" value="IT Operations">
        </div>
        <div class="form-group" style="margin-bottom: 12px;">
          <label for="reg-department">Department *</label>
          <select id="reg-department" class="form-control" required>
            <option value="Management">Management</option>
            <option value="IT Operations" selected>IT Operations</option>
            <option value="Human Resources">Human Resources</option>
            <option value="Accounting">Accounting</option>
            <option value="Sales">Sales</option>
            <option value="Marketing">Marketing</option>
            <option value="R&D">R&D</option>
            <option value="Logistics">Logistics</option>
            <option value="Warehouse">Warehouse</option>
            <option value="Security">Security</option>
            <option value="Technical Service">Technical Service</option>
            <option value="Quality Control">Quality Control</option>
            <option value="Training">Training</option>
            <option value="Purchasing">Purchasing</option>
            <option value="Finance & Accounting">Finance & Accounting</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
          <label for="reg-role">System Role *</label>
          <select id="reg-role" class="form-control" required>
            <option value="Requester">Requester (Developer / Owner)</option>
            <option value="CAB Approver">CAB Approver (Change Advisory Board)</option>
            <option value="Administrator">Administrator</option>
          </select>
        </div>
        <div id="register-error" style="color: #f87171; font-size: 0.85rem; margin-bottom: 16px; text-align: center; display: none;"></div>
        <button type="submit" class="btn btn-primary w-full" style="justify-content: center; width: 100%;">
          Create Account & Sign In <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
        </button>
        <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 16px;">
          <span data-i18n="has_account">Already have an account?</span> <a href="#" id="link-show-login" data-i18n="sign_in_here" style="color: #c084fc; text-decoration: none; font-weight: 600;">Sign in here</a>
        </p>
      </form>
    </div>
  </div>

  <!-- Inject preloaded mock data first -->
  <script src="mockData.js?v=5"></script>
  <!-- Load the main application logic -->
  <script src="app.js?v=5"></script>
</body>
</html>
