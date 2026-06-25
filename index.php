<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hopper</title>
  <!-- Favicon / Logo -->
  <link rel="icon" type="image/png" href="images/hopper.png">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap" rel="stylesheet">
  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="styles.css?v=23">
  <!-- Chart.js for Analytics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <div class="app-container">
    
    <!-- Top Header Bar -->
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#">
            <img src="images/hopper.png" alt="Hopper Logo" style="width: 28px; height: 28px; object-fit: contain;">
            <span>Hopper</span>
            <span style="font-size: 0.65rem; background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.3); color: var(--accent-color); padding: 2px 6px; border-radius: 10px; margin-left: 6px; font-weight: 600; line-height: 1; vertical-align: middle; height: fit-content; font-family: var(--font-body);">v1.2.2</span>
          </a>
          
          <ul class="nav pull-right" style="margin: 0; list-style: none; display: flex; align-items: center; height: 40px; padding: 0; gap: 15px;">
            <!-- Theme Toggle Button -->
            <li style="display: flex; align-items: center;">
              <button id="btn-theme-toggle" title="Toggle dark/light mode" style="background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border); cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; padding: 8px; transition: var(--transition-smooth); border-radius: 50%; width: 34px; height: 34px; outline: none;">
                <i data-lucide="sun" class="theme-icon-sun"></i>
                <i data-lucide="moon" class="theme-icon-moon"></i>
              </button>
            </li>

            <!-- Notification Bell Container -->
            <li class="dropdown" style="position: relative; display: flex; align-items: center;">
              <button class="dropdown-toggle" id="btn-notifications" style="background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border); cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; padding: 8px; transition: var(--transition-smooth); border-radius: 50%; width: 34px; height: 34px; outline: none; position: relative;">
                <i data-lucide="bell" style="width: 16px; height: 16px;"></i>
                <span id="notification-badge" class="notification-badge" style="display: none;">0</span>
              </button>
              
              <!-- Notifications Dropdown Menu -->
              <div class="github-dropdown" id="notifications-menu" style="display: none; width: 300px; right: -10px; left: auto; padding: 0;">
                <div style="padding: 10px 15px; border-bottom: 1px solid var(--glass-border); font-weight: 600; color: var(--text-main); display: flex; justify-content: space-between; align-items: center;">
                  <span data-i18n="notifications">Notifications</span>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span id="notification-count-text" style="font-size: 0.8rem; background: var(--accent-color); color: #fff; padding: 2px 6px; border-radius: 10px;">0 New</span>
                    <button id="btn-clear-notifications" style="background: none; border: none; color: var(--text-sub); font-size: 0.75rem; cursor: pointer; text-decoration: underline; padding: 0; outline: none;" title="Clear All Notifications" data-i18n="clear_all">Clear All</button>
                  </div>
                </div>
                <div id="notifications-list" style="max-height: 300px; overflow-y: auto; padding: 5px 0;">
                  <!-- Notification items will be injected here via JS -->
                  <div style="padding: 15px; text-align: center; color: var(--text-sub); font-size: 0.9rem;" data-i18n="no_notifications">No pending notifications.</div>
                </div>
              </div>
            </li>

            <!-- Profile Dropdown Container -->
            <li class="dropdown" style="position: relative; display: inline-block;">
              <a href="#" class="dropdown-toggle" id="header-user-profile" style="cursor: pointer; display: flex; align-items: center; gap: 10px; text-decoration: none; padding: 5px 15px;">
                <div class="user-avatar" id="nav-user-avatar">ADM</div>
                <div class="user-info" style="display: flex; flex-direction: column; text-align: left; line-height: 1.2;">
                  <span class="user-name" id="nav-user-name" style="color: var(--text-main);">administrator</span>
                  <span class="user-role" id="nav-user-role" style="color: var(--text-muted);">IT Operations</span>
                </div>
                <i data-lucide="chevron-down" style="color: var(--text-muted); width: 14px; height: 14px;"></i>
              </a>

              <!-- Floating Menu -->
              <div class="github-dropdown" id="profile-dropdown-menu">
                <a href="#" class="dropdown-item" data-dropdown-tab="profile">
                  <i data-lucide="user" style="width: 14px; height: 14px;"></i>
                  <span data-i18n="my_profile">My Profile</span>
                </a>
                <a href="#" class="dropdown-item" data-dropdown-tab="settings" id="dropdown-settings-link">
                  <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                  <span data-i18n="settings">Settings</span>
                </a>
                <a href="#" class="dropdown-item" data-dropdown-tab="analytics">
                  <i data-lucide="bar-chart-2" style="width: 14px; height: 14px;"></i>
                  <span data-i18n="analytics">Analytics</span>
                </a>
                <a href="#" class="dropdown-item" data-dropdown-tab="audit-logs" id="dropdown-audit-logs-link" style="display: none;">
                  <i data-lucide="shield-alert" style="width: 14px; height: 14px;"></i>
                  <span data-i18n="audit_logs">Audit Logs</span>
                </a>
                <div style="border-top: 1px solid var(--glass-border); margin: 4px 0;"></div>
                <a href="#" class="dropdown-item text-danger" id="btn-logout">
                  <i data-lucide="log-out" style="width: 14px; height: 14px;"></i>
                  <span data-i18n="sign_out">Sign out</span>
                </a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Subnavbar navigation -->
    <div class="subnavbar">
      <div class="subnavbar-inner">
        <div class="container">
          <ul class="mainnav">
            <li>
              <a href="#" class="menu-item active" data-tab="dashboard">
                <i data-lucide="layout-dashboard"></i>
                <span data-i18n="dashboard">Dashboard</span>
              </a>
            </li>
            <li>
              <a href="#" class="menu-item" data-tab="changes">
                <i data-lucide="list"></i>
                <span data-i18n="change_requests">Change Requests</span>
              </a>
            </li>
            <li>
              <a href="#" class="menu-item" data-tab="approvals" style="position: relative;">
                <i data-lucide="check-square"></i>
                <span data-i18n="approval_center">Approval Center</span>
                <span id="nav-approval-badge" class="badge" style="position: absolute; top: 10px; right: 10px; background: #ff7f74; color: #ffffff; padding: 2px 6px; font-size: 0.7rem; border-radius: 10px; display: none;">0</span>
              </a>
            </li>
            <li>
              <a href="#" class="menu-item" data-tab="calendar">
                <i data-lucide="calendar"></i>
                <span data-i18n="change_calendar">Change Calendar</span>
              </a>
            </li>

            <li id="nav-users-li" style="display: none;">
              <a href="#" class="menu-item" data-tab="users">
                <i data-lucide="users"></i>
                <span data-i18n="user_directory">User Directory</span>
              </a>
            </li>

            <li>
              <a href="#" class="menu-item" data-tab="about">
                <i data-lucide="info"></i>
                <span data-i18n="about">About</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Main Content Container Grid -->
    <div class="main">
      <div class="main-inner">
        <div class="container">
          <main class="main-content" style="padding-top: 0; margin-left: 0; width: 100%;">
            
            <!-- Page Heading Actions -->
            <div class="header-bar">
              <div class="page-title">
                <h1 id="main-title" data-i18n="dashboard">Dashboard</h1>
                <p id="main-subtitle" data-i18n="dashboard_subtitle">Change requests and overall operational status</p>
              </div>
              <div class="header-actions">
                <button class="btn btn-primary" id="btn-new-change">
                  <i data-lucide="plus"></i> <span data-i18n="new_change_request">New Change Request</span>
                </button>
              </div>
            </div>

            <!-- TAB 1: DASHBOARD -->
            <section id="tab-dashboard" class="tab-content active">
              <!-- KPI Cards Grid -->
              <div class="kpi-grid">
                <div class="glass-card kpi-card">
                  <div class="kpi-info">
                    <span class="kpi-label" data-i18n="total_changes">Total Changes</span>
                    <span class="kpi-value" id="kpi-total">0</span>
                  </div>
                  <div class="kpi-icon-wrapper">
                    <i data-lucide="layers"></i>
                  </div>
                </div>
                <div class="glass-card kpi-card">
                  <div class="kpi-info">
                    <span class="kpi-label" data-i18n="awaiting_approval">Awaiting Approval</span>
                    <span class="kpi-value" id="kpi-pending">0</span>
                  </div>
                  <div class="kpi-icon-wrapper">
                    <i data-lucide="clock"></i>
                  </div>
                </div>
                <div class="glass-card kpi-card">
                  <div class="kpi-info">
                    <span class="kpi-label" data-i18n="implementing">Implementing</span>
                    <span class="kpi-value" id="kpi-implementing">0</span>
                  </div>
                  <div class="kpi-icon-wrapper">
                    <i data-lucide="play-circle"></i>
                  </div>
                </div>
                <div class="glass-card kpi-card">
                  <div class="kpi-info">
                    <span class="kpi-label" data-i18n="success_rate">Success Rate</span>
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
                    <h3 data-i18n="active_change_tracking">Active Change Tracking</h3>
                    <button class="btn btn-secondary btn-sm" id="btn-view-all-changes" data-i18n="view_all">View All</button>
                  </div>
                  <div class="list-container" id="dashboard-active-changes">
                    <!-- Rendered via JS -->
                  </div>
                </div>
              </div>
            </section>

            <!-- TAB 2: CHANGES LIST -->
            <section id="tab-changes" class="tab-content">
              <!-- Print-only header -->
              <div class="print-cab-header" style="display: none;">
                <h1>Hopper Change Advisory Board (CAB) Report</h1>
                <p>Generated on: <?php echo date('Y-m-d H:i'); ?> | Planned Infrastructure & Software Changes</p>
              </div>
              <!-- Search & Filter Controls -->
              <div class="glass-card filters-bar">
                <div class="search-wrapper">
                  <i data-lucide="search"></i>
                  <input type="text" id="search-query" class="form-control search-input" placeholder="Search by request title, ID, or owner..." data-i18n-placeholder="search_placeholder">
                </div>
                <select id="filter-status" class="form-control filter-select">
                  <option value="" data-i18n="all_statuses">All Statuses</option>
                  <option value="Draft" data-i18n="status_draft">Draft</option>
                  <option value="Under Review" data-i18n="status_under_review">Under Review</option>
                  <option value="Pending Approval" data-i18n="status_pending_approval">Pending Approval</option>
                  <option value="Approved" data-i18n="status_approved">Approved</option>
                  <option value="Implementing" data-i18n="status_implementing">Implementing</option>
                  <option value="Completed" data-i18n="status_completed">Completed</option>
                  <option value="Rolled Back" data-i18n="status_rolled_back">Rolled Back</option>
                  <option value="Rejected" data-i18n="status_rejected">Rejected</option>
                </select>
                
                <select id="filter-risk" class="form-control" style="font-size: 0.85rem; padding: 6px 12px; min-height: 32px; height: 32px;">
                  <option value="" data-i18n="all_risks">All Risk Levels</option>
                  <option value="High" data-i18n="risk_high">High Risk</option>
                  <option value="Medium" data-i18n="risk_medium">Medium Risk</option>
                  <option value="Low" data-i18n="risk_low">Low Risk</option>
                </select>
              </div>
              <div class="header-actions" style="margin-bottom: 0; display: flex; gap: 8px;">
                <button class="btn btn-secondary btn-sm" id="btn-export-cab" style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; min-height: 32px;">
                  <i data-lucide="download" style="width: 16px; height: 16px;"></i> <span data-i18n="export_cab_report">Export CAB Report</span>
                </button>
                <button class="btn btn-secondary btn-sm" id="btn-print-cab" style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; min-height: 32px;">
                  <i data-lucide="printer" style="width: 16px; height: 16px;"></i> <span data-i18n="print_cab_report">Print CAB Report (PDF)</span>
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
                <p style="color: var(--text-muted);" data-i18n="approvals_intro">List of change requests awaiting Change Advisory Board (CAB) and stakeholder approval. Evaluate approval steps based on business continuity and risk analysis.</p>
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">
                  <h3 style="margin: 0;" data-i18n="my_profile_settings">My Profile Settings</h3>
                  
                  <div style="display: flex; gap: 10px;">
                    <button type="submit" form="form-profile" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; min-height: 28px; font-size: 0.75rem;">
                      <i data-lucide="save"></i> <span data-i18n="save_profile_changes">Save Profile Changes</span>
                    </button>
                  </div>
                </div>
                
                <form id="form-profile">
                  <div id="profile-status-message" style="width: 100%; font-size: 0.85rem; margin-bottom: 16px; text-align: center; display: none; font-weight: 600;"></div>
                  <div class="profile-layout" style="display: flex; gap: 32px; flex-wrap: wrap;">
                    <!-- Left Column: Avatar upload -->
                    <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column; align-items: center; gap: 16px; border-right: 1px solid var(--glass-border); padding-right: 24px;">
                      <div class="profile-avatar-large" id="avatar-container" style="width: 140px; height: 140px; border-radius: 50%; overflow: hidden; border: 2px solid var(--glass-border-focus); display: flex; align-items: center; justify-content: center; background: var(--glass-bg-hover); position: relative; cursor: pointer;">
                        <img id="profile-avatar-img" src="" alt="Profile Photo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: none;">
                        <div id="profile-avatar-placeholder" class="avatar-placeholder" style="width: 100%; height: 100%; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; background: var(--glass-bg); color: var(--accent-color);">ADM</div>
                        <div id="avatar-hover-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s ease; border-radius: 50%; color: white;">
                          <i data-lucide="camera" style="width: 24px; height: 24px;"></i>
                        </div>
                      </div>
                      
                      <div style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
                        <label class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer; font-size: 0.75rem; justify-content: center; padding: 4px 12px; min-height: 28px;">
                          <i data-lucide="upload" style="width: 12px; height: 12px;"></i> Upload
                          <input type="file" id="profile-avatar-input" style="display: none;" accept="image/png, image/jpeg">
                        </label>
                        
                        <div id="avatar-actions-container" style="display: none; justify-content: center; gap: 8px;">
                          <button type="button" class="btn btn-secondary btn-sm" id="btn-view-avatar" title="View Photo" style="padding: 4px 12px; min-height: 28px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;">
                            <i data-lucide="eye" style="width: 12px; height: 12px;"></i> <span data-i18n="view_photo">View Photo</span>
                          </button>
                          <button type="button" class="btn btn-danger btn-sm" id="btn-delete-avatar" title="Delete Photo" style="padding: 4px 12px; min-height: 28px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.3); color: #ef4444; box-shadow: none;">
                            <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i> <span data-i18n="delete_photo">Delete Photo</span>
                          </button>
                        </div>
                      </div>
                      <p style="font-size: 0.75rem; color: var(--text-sub); text-align: center;" data-i18n="avatar_help">Supports PNG, JPG. Max 1MB. Image is saved inside the database.</p>
                    </div>
                    
                    <!-- Right Column: Profile details -->
                    <div style="flex: 2; min-width: 300px; display: flex; flex-direction: column; gap: 16px;">
                      <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                          <label for="profile-username" data-i18n="username_readonly">Username (Read-only)</label>
                          <input type="text" id="profile-username" class="form-control" readonly disabled style="opacity: 0.6; cursor: not-allowed;">
                        </div>
                        <div class="form-group">
                          <label style="display: block; margin-bottom: 6px;">Role</label>
                          <span id="profile-role-badge" class="badge" style="background: rgba(167, 139, 250, 0.15); border: 1px solid rgba(167, 139, 250, 0.3); color: #c084fc; font-size: 0.8rem; padding: 6px 12px; display: inline-block; width: fit-content; font-weight: 600;">Administrator</span>
                        </div>
                      </div>
                      
                      <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                          <label for="profile-name" data-i18n="full_name_label">Full Name *</label>
                          <input type="text" id="profile-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                          <label for="profile-title" data-i18n="job_title_label">Job Title</label>
                          <input type="text" id="profile-title" class="form-control">
                        </div>
                      </div>
                      
                      <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                          <label for="profile-department" data-i18n="department_label">Department *</label>
                          <select id="profile-department" class="form-control" required>
                            <!-- Populated by JS -->
                          </select>
                        </div>
                        <div class="form-group">
                          <label for="profile-email" data-i18n="email_label">Email</label>
                          <input type="email" id="profile-email" class="form-control">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="profile-phone" data-i18n="phone_number">Phone Number</label>
                        <input type="text" id="profile-phone" class="form-control">
                      </div>
                      
                      <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                          <label for="profile-new-password" data-i18n="new_password">New Password</label>
                          <div class="password-wrapper">
                            <input type="password" id="profile-new-password" class="form-control" placeholder="Leave empty to keep current" data-i18n-placeholder="leave_empty">
                            <button type="button" class="password-toggle-btn" toggle-target="profile-new-password" title="Toggle password visibility">
                              <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
                            </button>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="profile-confirm-password" data-i18n="confirm_password">Confirm Password</label>
                          <div class="password-wrapper">
                            <input type="password" id="profile-confirm-password" class="form-control" placeholder="Confirm new password" data-i18n-placeholder="confirm_new_password">
                            <button type="button" class="password-toggle-btn" toggle-target="profile-confirm-password" title="Toggle password visibility">
                              <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
                            </button>
                          </div>
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
                    <h3 data-i18n="pending_registration_requests">Pending Registration Requests</h3>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;" data-i18n="review_registrations">Review and approve new user account registrations.</p>
                  </div>
                  <span class="badge badge-risk medium" id="pending-reg-count" style="background: rgba(245,158,11,0.2); color: var(--status-pending); font-weight: 700;">0 Pending</span>
                </div>
                
                <div class="list-container" style="overflow-x: auto;">
                  <table class="user-directory-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                      <tr style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-family: var(--font-heading);">
                        <th style="padding: 12px 16px;" data-i18n="applicant">Applicant</th>
                        <th style="padding: 12px 16px;" data-i18n="job_title_dept">Job Title & Dept</th>
                        <th style="padding: 12px 16px;" data-i18n="requested_role">Requested Role</th>
                        <th style="padding: 12px 16px;" data-i18n="date">Date</th>
                        <th style="padding: 12px 16px; text-align: right; width: 180px;" data-i18n="actions">Actions</th>
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
                  <h3 data-i18n="user_directory_title">User Directory & Role Management (Admin Only)</h3>
                  <span class="badge badge-risk high" data-i18n="system_access_control">System Access Control</span>
                </div>
                
                <div class="list-container" style="overflow-x: auto;">
                  <table class="user-directory-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                      <tr style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-family: var(--font-heading);">
                        <th style="padding: 12px 16px;" data-i18n="user">User</th>
                        <th style="padding: 12px 16px;" data-i18n="contact_info">Contact Info</th>
                        <th style="padding: 12px 16px;" data-i18n="job_title">Job Title</th>
                        <th style="padding: 12px 16px;" data-i18n="department">Department</th>
                        <th style="padding: 12px 16px; width: 180px;" data-i18n="system_role">System Role</th>
                        <th style="padding: 12px 16px; text-align: right; width: 100px;" data-i18n="actions">Actions</th>
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
              <div class="glass-card" style="max-width: 1200px; margin: 0 auto; padding: 24px;">
                <div class="section-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 16px; margin-bottom: 24px;">
                  <h3 data-i18n="general_system_settings">General System Settings</h3>
                  <span class="badge badge-risk high" id="settings-admin-badge" data-i18n="system_administration">System Administration</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 32px;">
                  
                  <!-- Section 1: System Directory Lists -->
                  <div>
                    <h5 style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                      <i data-lucide="list" style="width: 14px; height: 14px; color: #a78bfa;"></i>
                      <span>System Directory Lists</span>
                    </h5>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                      
                      <!-- Department Management -->
                      <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.01); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                          <h4 style="margin-bottom: 12px; font-size: 1.1rem; color: var(--text-main); font-weight: 600;" data-i18n="manage_departments">Manage Departments</h4>
                          <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px; min-height: 38px;" data-i18n="departments_intro">Add or remove departments available for user registrations and profiles.</p>
                          
                          <!-- Add Department Form (Admin Only) -->
                          <div id="add-dept-wrapper" style="display: flex; gap: 8px; margin-bottom: 16px;">
                            <input type="text" id="new-dept-name" class="form-control" placeholder="e.g. Finance" data-i18n-placeholder="dept_placeholder" style="font-size: 0.85rem; padding: 8px 12px; flex: 1;">
                            <button class="btn btn-primary" id="btn-add-dept" style="padding: 8px 16px; font-size: 0.85rem;"><i data-lucide="plus"></i> <span data-i18n="add">Add</span></button>
                          </div>
                        </div>

                        <!-- Departments List -->
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); padding: 8px;" id="settings-depts-list">
                          <!-- Rendered dynamically -->
                        </div>
                      </div>

                      <!-- Category Management -->
                      <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.01); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                          <h4 style="margin-bottom: 12px; font-size: 1.1rem; color: var(--text-main); font-weight: 600;" data-i18n="manage_change_categories">Manage Change Categories</h4>
                          <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px; min-height: 38px;" data-i18n="categories_intro">Configure options for change request categorization.</p>
                          
                          <!-- Add Category Form (Admin Only) -->
                          <div id="add-cat-wrapper" style="display: flex; gap: 8px; margin-bottom: 16px;">
                            <input type="text" id="new-cat-name" class="form-control" placeholder="e.g. Infrastructure" data-i18n-placeholder="category_placeholder" style="font-size: 0.85rem; padding: 8px 12px; flex: 1;">
                            <button class="btn btn-primary" id="btn-add-cat" style="padding: 8px 16px; font-size: 0.85rem;"><i data-lucide="plus"></i> <span data-i18n="add">Add</span></button>
                          </div>
                        </div>

                        <!-- Categories List -->
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); padding: 8px;" id="settings-cats-list">
                          <!-- Rendered dynamically -->
                        </div>
                      </div>

                      <!-- Group Management -->
                      <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.01); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                          <h4 style="margin-bottom: 12px; font-size: 1.1rem; color: var(--text-main); font-weight: 600;" data-i18n="manage_groups">Manage User Groups</h4>
                          <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px; min-height: 38px;" data-i18n="groups_intro">Add or remove user groups/teams for assignment and routing.</p>
                          
                          <!-- Add Group Form (Admin Only) -->
                          <div id="add-group-wrapper" style="display: flex; gap: 8px; margin-bottom: 16px;">
                            <input type="text" id="new-group-name" class="form-control" placeholder="e.g. DevOps" data-i18n-placeholder="group_placeholder" style="font-size: 0.85rem; padding: 8px 12px; flex: 1;">
                            <button class="btn btn-primary" id="btn-add-group" style="padding: 8px 16px; font-size: 0.85rem;"><i data-lucide="plus"></i> <span data-i18n="add">Add</span></button>
                          </div>
                        </div>

                        <!-- Groups List -->
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); padding: 8px;" id="settings-groups-list">
                          <!-- Rendered dynamically -->
                        </div>
                      </div>

                    </div>
                  </div>

                  <!-- Section 2: Integrations & External Services -->
                  <div>
                    <h5 style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                      <i data-lucide="cpu" style="width: 14px; height: 14px; color: #a78bfa;"></i>
                      <span>Integrations & Connectors</span>
                    </h5>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
                      
                      <!-- Webhook Settings -->
                      <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.01);">
                        <h4 style="margin-bottom: 12px; font-size: 1.1rem; color: var(--text-main); font-weight: 600;" data-i18n="webhook_settings">Webhook Settings</h4>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px;" data-i18n="webhook_intro">Configure system notifications to be sent to external team channels.</p>
                        
                        <!-- Webhook Config Form (Admin Only) -->
                        <form id="form-webhook-settings" style="display: flex; flex-direction: column; gap: 12px;">
                          <div class="form-group" style="margin-bottom: 0;">
                            <label for="webhook-url" data-i18n="webhook_url" style="font-size: 0.8rem; font-weight: 600; margin-bottom: 6px;">Webhook URL</label>
                            <input type="url" id="webhook-url" class="form-control" placeholder="https://outlook.office.com/webhook/..." style="font-size: 0.85rem; padding: 8px 12px;">
                          </div>
                          
                          <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 4px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-main); font-size: 0.85rem;">
                              <input type="checkbox" id="webhook-notify-create" style="width: 14px; height: 14px;"> <span data-i18n="notify_on_create">Notify on change creation</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-main); font-size: 0.85rem;">
                              <input type="checkbox" id="webhook-notify-status" style="width: 14px; height: 14px;"> <span data-i18n="notify_on_status">Notify on workflow status changes</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-main); font-size: 0.85rem;">
                              <input type="checkbox" id="webhook-notify-highrisk" style="width: 14px; height: 14px;"> <span data-i18n="notify_on_highrisk">Only notify for High Risk changes</span>
                            </label>
                          </div>
                          
                          <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem; margin-top: 8px; align-self: flex-start;">
                            <i data-lucide="save"></i> <span data-i18n="save_settings">Save Settings</span>
                          </button>
                        </form>
                      </div>

                      <!-- Active Directory / LDAP Settings (Admin Only) -->
                      <div class="glass-card" style="padding: 20px; background: rgba(255,255,255,0.01);">
                        <h4 style="margin-bottom: 12px; font-size: 1.1rem; color: var(--text-main); font-weight: 600;" data-i18n="ad_settings">Active Directory / LDAP Settings</h4>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px;" data-i18n="ad_intro">Configure system integration with corporate Active Directory / LDAP servers for unified authentication.</p>
                        
                        <!-- AD Setup Guide -->
                        <div style="margin-bottom: 16px;">
                          <a href="#" id="link-ad-guide" style="font-size: 0.8rem; color: #a78bfa; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 500;">
                            <i data-lucide="help-circle" style="width: 14px; height: 14px;"></i>
                            <span data-i18n="ad_view_guide" id="text-ad-guide-toggle">View Integration Guide</span>
                          </a>
                          <div id="ad-guide-box" style="display: none; margin-top: 12px; padding: 14px; background: rgba(139, 92, 246, 0.03); border: 1px solid rgba(139, 92, 246, 0.15); border-radius: var(--border-radius-sm); font-size: 0.75rem; color: var(--text-sub); line-height: 1.5; text-align: left; flex-direction: column; gap: 8px;">
                            <div><strong>LDAP vs LDAPS:</strong> Use <code>ldap://hostname</code> with port <code>389</code> for standard connections. For secure SSL connections, use <code>ldaps://hostname</code> with port <code>636</code>.</div>
                            <div><strong>Domain Suffix:</strong> The UPN domain suffix (e.g., <code>company.local</code>). If set, users can log in using just their short username (e.g., <code>alice</code>) instead of the full UPN (e.g., <code>alice@company.local</code>).</div>
                            <div><strong>Base DN:</strong> The search root directory for locating user objects in AD hierarchy (e.g., <code>dc=company,dc=local</code> or <code>ou=Staff,dc=company,dc=local</code>).</div>
                            <div><strong>JIT Provisioning:</strong> Once authenticated, users who don't exist in local database are automatically created with default system permissions.</div>
                          </div>
                        </div>

                        <form id="form-ad-settings" style="display: flex; flex-direction: column; gap: 12px;">
                          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-main); font-size: 0.85rem; margin-bottom: 4px;">
                            <input type="checkbox" id="ad-enabled" style="width: 14px; height: 14px;"> <span data-i18n="ad_enable">Enable Active Directory Authentication</span>
                          </label>

                          <div class="form-group" style="margin-bottom: 0;">
                            <label for="ad-server" data-i18n="ad_server" style="font-size: 0.8rem; font-weight: 600; margin-bottom: 6px;">LDAP Server Address</label>
                            <input type="text" id="ad-server" class="form-control" placeholder="ldap://ad.company.local" style="font-size: 0.85rem; padding: 8px 12px;">
                          </div>

                          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                              <label for="ad-port" data-i18n="ad_port" style="font-size: 0.8rem; font-weight: 600; margin-bottom: 6px;">LDAP Server Port</label>
                              <input type="number" id="ad-port" class="form-control" placeholder="389" value="389" style="font-size: 0.85rem; padding: 8px 12px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                              <label data-i18n="ad_domain" style="font-size: 0.8rem; font-weight: 600; margin-bottom: 6px;">Domain Suffixes (UPN)</label>
                              <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="text" id="ad-domain-input" class="form-control" placeholder="e.g. company.local" style="font-size: 0.85rem; padding: 8px 12px; flex: 1;">
                                <button type="button" id="btn-add-ad-domain" class="btn btn-secondary btn-sm" style="padding: 0 16px; min-height: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main);"><i data-lucide="plus" style="width: 16px; height: 16px;"></i></button>
                              </div>
                              <div id="ad-domains-list" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; min-height: 20px;">
                                <!-- Tag pills will be injected here via JS -->
                              </div>
                            </div>
                          </div>

                          <div class="form-group" style="margin-bottom: 0;">
                            <label for="ad-basedn" data-i18n="ad_basedn" style="font-size: 0.8rem; font-weight: 600; margin-bottom: 6px;">Base DN (Search Directory)</label>
                            <input type="text" id="ad-basedn" class="form-control" placeholder="dc=company,dc=local" style="font-size: 0.85rem; padding: 8px 12px;">
                          </div>

                          <div style="display: flex; gap: 12px; margin-top: 8px;">
                            <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem;">
                              <i data-lucide="save"></i> <span data-i18n="save_ad_settings">Save AD Settings</span>
                            </button>
                            <button type="button" class="btn btn-secondary" id="btn-test-ad" style="padding: 8px 16px; font-size: 0.85rem; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); color: var(--text-main);">
                              <i data-lucide="wifi"></i> <span data-i18n="test_connection">Test Connection</span>
                            </button>
                          </div>
                        </form>
                      </div>

                    </div>
                  </div>

                </div>
              </div>
            </section>

            <!-- TAB 5: ABOUT -->
            <section id="tab-about" class="tab-content">
              <div class="glass-card" style="max-width: 800px; margin: 40px auto 0 auto; display: flex; flex-direction: column; gap: 24px; text-align: center; padding: 48px 32px;">
                <div style="width: 110px; height: 110px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                  <img src="images/hopper.png" alt="Hopper Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div>
                  <h2 class="about-title" style="font-size: 2.25rem; font-weight: 800; margin: 0 auto;" data-i18n="about_title_large">About Hopper</h2>
                  <p style="color: var(--text-muted); margin-top: 12px; font-size: 1.05rem;" data-i18n="about_description">Hopper is a premium, lightweight Change Management System designed to track, coordinate, and review infrastructure and software change requests with complete audit control and workflow transparency.</p>
                </div>
                
                <div style="border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); padding: 24px 0; display: flex; flex-direction: column; gap: 12px; align-items: center;">
                  <p style="color: var(--text-main); font-weight: 600; font-size: 1.1rem;" data-i18n="lead_developer">Lead Developer</p>
                  <p style="font-size: 1.35rem; font-weight: 800; color: #c084fc; font-family: var(--font-heading); margin-bottom: 4px;">Faruk Güler</p>
                  <span class="badge" style="background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.3); color: var(--accent-color); font-size: 0.8rem; padding: 4px 12px;">Version v1.2.2</span>
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

            <!-- TAB: ANALYTICS -->
            <section id="tab-analytics" class="tab-content">
              <div class="header-bar">
                <div class="page-title">
                  <h1 data-i18n="analytics_title">System Analytics</h1>
                  <p data-i18n="analytics_desc">Change success rates, operational KPIs, and distribution charts.</p>
                </div>
              </div>
              
              <!-- KPI Grid -->
              <div class="analytics-kpi-grid">
                <div class="kpi-card" style="border-left: 4px solid var(--accent-color);">
                  <div class="kpi-info">
                    <span class="kpi-label" data-i18n="total_changes">Total Changes</span>
                    <span class="kpi-value" id="kpi-total-changes">0</span>
                  </div>
                  <div class="kpi-icon-wrapper">
                    <i data-lucide="bar-chart-2" style="color: var(--accent-color);"></i>
                  </div>
                </div>
                <div class="kpi-card" style="border-left: 4px solid var(--status-completed);">
                  <div class="kpi-info">
                    <span class="kpi-label" data-i18n="completed">Completed</span>
                    <span class="kpi-value" id="kpi-completed-changes">0</span>
                  </div>
                  <div class="kpi-icon-wrapper">
                    <i data-lucide="check-circle" style="color: var(--status-completed);"></i>
                  </div>
                </div>
                <div class="kpi-card" style="border-left: 4px solid var(--status-pending);">
                  <div class="kpi-info">
                    <span class="kpi-label" data-i18n="pending_approvals">Pending Approvals</span>
                    <span class="kpi-value" id="kpi-pending-approvals">0</span>
                  </div>
                  <div class="kpi-icon-wrapper">
                    <i data-lucide="clock" style="color: var(--status-pending);"></i>
                  </div>
                </div>
                <div class="kpi-card" style="border-left: 4px solid var(--color-high);">
                  <div class="kpi-info">
                    <span class="kpi-label" data-i18n="success_rate">Success Rate</span>
                    <span class="kpi-value" id="kpi-success-rate">100%</span>
                    <div class="success-rate-bar-bg">
                      <div class="success-rate-bar-fill" id="kpi-success-rate-fill" style="width: 100%;"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Analytics Charts Grid -->
              <div class="analytics-grid">
                <div class="analytics-card">
                  <h4 data-i18n="status_distribution">Change Status Distribution</h4>
                  <div class="analytics-chart-container">
                    <canvas id="chart-status"></canvas>
                  </div>
                </div>
                <div class="analytics-card">
                  <h4 data-i18n="risk_distribution">Risk Level Distribution</h4>
                  <div class="analytics-chart-container">
                    <canvas id="chart-risk"></canvas>
                  </div>
                </div>
                <div class="analytics-card">
                  <h4 data-i18n="category_distribution">Category Distribution</h4>
                  <div class="analytics-chart-container">
                    <canvas id="chart-category"></canvas>
                  </div>
                </div>
                <div class="analytics-card">
                  <h4 data-i18n="department_distribution">Requests by Department</h4>
                  <div class="analytics-chart-container">
                    <canvas id="chart-department"></canvas>
                  </div>
                </div>
              </div>
            </section>

            <!-- TAB: AUDIT LOGS -->
            <section id="tab-audit-logs" class="tab-content">
              <div class="header-bar">
                <div class="page-title">
                  <h1 data-i18n="audit_logs_title">System Audit Trail</h1>
                  <p data-i18n="audit_logs_desc">Permanent log records of all security and workflow activities.</p>
                </div>
              </div>

              <div class="audit-logs-table-container">
                <table class="user-directory-table">
                  <thead>
                    <tr>
                      <th style="padding: 12px 16px; font-weight: 700; width: 180px;" data-i18n="date">Date</th>
                      <th style="padding: 12px 16px; font-weight: 700; width: 200px;" data-i18n="user">User</th>
                      <th style="padding: 12px 16px; font-weight: 700;" data-i18n="action">Action</th>
                      <th style="padding: 12px 16px; font-weight: 700; width: 150px;" data-i18n="target">Target</th>
                    </tr>
                  </thead>
                  <tbody id="audit-logs-tbody">
                    <!-- Logs injected dynamically -->
                  </tbody>
                </table>
              </div>
            </section>

          </main>
        </div>
      </div>
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
              <label for="change-assigned-group" data-i18n="assigned_group_label">Assigned Group</label>
              <select id="change-assigned-group" class="form-control">
                <!-- Populated by JS -->
              </select>
            </div>

            <div class="form-group">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label for="change-risk" style="margin-bottom: 0;" data-i18n="risk_level_label">Risk Level *</label>
                <button type="button" id="btn-calc-risk" style="background: none; border: none; color: #a78bfa; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0;">
                  <i data-lucide="sparkles" style="width: 12px; height: 12px;"></i> <span data-i18n="calculate_risk">Calculate Risk</span>
                </button>
              </div>
              <select id="change-risk" class="form-control" required>
                <option value="Low" data-i18n="risk_low">Low Risk</option>
                <option value="Medium" data-i18n="risk_medium">Medium Risk</option>
                <option value="High" data-i18n="risk_high">High Risk</option>
              </select>
            </div>

            <!-- Risk Calculator Interactive Panel -->
            <div class="form-group form-full" id="risk-calc-panel" style="display: none; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); padding: 16px; margin-top: 10px; transition: var(--transition-smooth); width: 100%;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--glass-border); padding-bottom: 8px;">
                <h5 style="margin: 0; font-size: 0.85rem; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="calculator" style="width: 14px; height: 14px; color: #a78bfa;"></i> <span data-i18n="risk_assessment_guide">Risk Assessment Guide</span>
                </h5>
                <button type="button" id="btn-close-risk-calc" style="background: none; border: none; color: var(--text-muted); font-size: 0.75rem; cursor: pointer; padding: 0;" data-i18n="close">Close</button>
              </div>
              <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.8rem;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-muted);">
                  <input type="checkbox" id="risk-q-prod" style="width: 14px; height: 14px;"> <span data-i18n="risk_q_prod">Affects Production Environment</span>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-muted);">
                  <input type="checkbox" id="risk-q-downtime" style="width: 14px; height: 14px;"> <span data-i18n="risk_q_downtime">Requires System Downtime / Maintenance Window</span>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-muted);">
                  <input type="checkbox" id="risk-q-untested" style="width: 14px; height: 14px;"> <span data-i18n="risk_q_untested">Has NOT been tested in Pre-Prod/Staging Environment</span>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-muted);">
                  <input type="checkbox" id="risk-q-no-rollback" style="width: 14px; height: 14px;"> <span data-i18n="risk_q_no_rollback">Rollback Plan is untested/complex</span>
                </label>
                <div style="border-top: 1px solid var(--glass-border); padding-top: 8px; margin-top: 4px; display: flex; justify-content: space-between; align-items: center;">
                  <span data-i18n="calculated_level">Calculated Level:</span>
                  <span id="calculated-risk-badge" class="badge badge-risk low" style="font-weight: 700;" data-i18n="risk_low">Low Risk</span>
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

          <!-- Conflict Warning Banner -->
          <div id="create-conflict-warning-container" style="display: none; margin-bottom: 20px;"></div>

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

            <div class="detail-section" id="detail-revisions-section" style="display: none; margin-top: 20px;">
              <h4 data-i18n="revision_history">Revision History</h4>
              <div class="revision-timeline" id="detail-revisions-timeline" style="margin-top: 12px; display: flex; flex-direction: column; gap: 12px;">
                <!-- Populated dynamically by JS -->
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
                <div><span style="color: var(--text-sub);" data-i18n="assigned_group">Assigned Group</span>: <strong id="detail-assigned-group">...</strong></div>
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
              <h4 data-i18n="runbooks_documentation">Runbooks & Documentation</h4>
              <div id="detail-attachment-container" style="margin-top: 10px; font-size: 0.85rem;">
                <!-- Will be populated dynamically by JS -->
              </div>
              <!-- Upload form, visible to owners/requesters/admins -->
              <div id="attachment-upload-wrapper" style="display: none; margin-top: 12px;">
                <label class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.75rem; width: 100%; justify-content: center; min-height: auto; padding: 6px 12px;" id="lbl-attachment-input">
                  <i data-lucide="paperclip" style="width: 14px; height: 14px;"></i> <span data-i18n="upload_file_attachment">Upload File Attachment</span>
                  <input type="file" id="change-attachment-input" style="display: none;" accept=".pdf,.txt,.docx,.xlsx,.png,.jpg,.jpeg,.zip">
                </label>
                <div id="attachment-upload-status" style="margin-top: 6px; font-size: 0.75rem; color: var(--text-muted); text-align: center;" data-i18n="upload_help">Max size: 2MB. Formats: PDF, TXT, DOCX, XLSX, PNG, JPG, ZIP.</div>
              </div>
            </div>

            <!-- Comments Section -->
            <div class="detail-section">
              <h4 data-i18n="discussion_comments">Discussion & Comments</h4>
              <div class="comments-list" id="detail-comments-list" style="margin-top: 10px; margin-bottom: 12px;">
                <!-- Comments rendered via JS -->
              </div>
              <div style="display: flex; gap: 8px;">
                <input type="text" id="comment-input" class="form-control" style="font-size: 0.8rem; padding: 8px; height: 36px !important;" placeholder="Write a comment..." data-i18n-placeholder="write_comment">
                <button class="btn btn-primary" id="btn-add-comment" style="padding: 8px 12px; min-height: 36px;"><i data-lucide="send" style="width:16px; height:16px;"></i></button>
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
              <label for="edit-user-group" data-i18n="assigned_group_label">Assigned Group</label>
              <select id="edit-user-group" class="form-control">
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

    <!-- MODAL: IMAGE VIEWER / LIGHTBOX -->
    <div class="modal-overlay" id="modal-image-viewer">
      <div class="glass-card modal-content" style="max-width: 500px; text-align: center; padding: 20px;">
        <div class="modal-header" style="margin-bottom: 16px;">
          <h3 data-i18n="view_photo">Profile Photo</h3>
          <button class="modal-close" id="modal-image-viewer-close">
            <i data-lucide="x"></i>
          </button>
        </div>
        <div style="width: 100%; max-height: 400px; overflow: hidden; display: flex; align-items: center; justify-content: center; border-radius: var(--border-radius-md); border: 1px solid var(--glass-border); background: var(--bg-primary);">
          <img id="viewer-modal-img" src="" style="max-width: 100%; max-height: 400px; object-fit: contain;">
        </div>
      </div>
    </div>

    <!-- Authentication Screen Overlay -->
    <div id="login-screen" class="login-screen-overlay">
      <div class="glass-card login-card-content">
        <div class="logo-container" style="justify-content: center; border-bottom: none; margin-bottom: 16px; padding-bottom: 0; align-items: center;">
          <div style="width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
            <img src="images/hopper.png" alt="Hopper Logo" style="width: 100%; height: 100%; object-fit: contain;">
          </div>
          <span class="logo-text" style="font-size: 2.25rem; font-weight: 800;">Hopper</span>
        </div>
        <h2 id="auth-title" class="auth-title" style="text-align: center; margin-bottom: 24px; font-size: 1.5rem;" data-i18n="sign_in_title">Sign In to System</h2>
        
        <!-- Auth Method Selector (Tabs) -->
        <div id="auth-method-tabs" class="auth-tabs" style="display: none;">
          <button type="button" id="tab-login-local" class="auth-tab active" data-auth-type="local">Local Account</button>
          <button type="button" id="tab-login-ldap" class="auth-tab" data-auth-type="ldap">AD / LDAP</button>
        </div>

        <!-- Login Form -->
        <form id="form-login">
          <div class="form-group" style="margin-bottom: 16px;">
            <label for="login-username" data-i18n="username">Username</label>
            <div id="login-username-wrapper" class="username-input-group">
              <input type="text" id="login-username" class="form-control" placeholder="Enter username (e.g. admin)" data-i18n-placeholder="enter_username" required>
              <select id="login-domain-select" class="form-control domain-select-addon" style="display: none;">
                <!-- Populated dynamically -->
              </select>
            </div>
          </div>
          <div class="form-group" style="margin-bottom: 24px;">
            <label for="login-password" data-i18n="password">Password</label>
            <div class="password-wrapper">
              <input type="password" id="login-password" class="form-control" placeholder="Enter password (e.g. admin123)" data-i18n-placeholder="enter_password" required>
              <button type="button" class="password-toggle-btn" toggle-target="login-password" title="Toggle password visibility">
                <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
              </button>
            </div>
          </div>
          <div id="login-error" style="color: #f87171; font-size: 0.85rem; margin-bottom: 16px; text-align: center; display: none;"></div>
          <button type="submit" class="btn btn-primary w-full" style="justify-content: center; width: 100%;">
            <span data-i18n="sign_in">Sign In</span> <i data-lucide="log-in" style="width: 18px; height: 18px; margin-left: 6px;"></i>
          </button>
          <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 16px;">
            <span data-i18n="no_account">Don't have an account?</span> <a href="#" id="link-show-register" data-i18n="register_here" style="color: #c084fc; text-decoration: none; font-weight: 600;">Register here</a>
          </p>
        </form>

        <!-- Register Form -->
        <form id="form-register" style="display: none;">
          <div class="form-group" style="margin-bottom: 12px;">
            <label for="reg-name" data-i18n="full_name_reg">Full Name *</label>
            <input type="text" id="reg-name" class="form-control" placeholder="e.g. Alice Smith" data-i18n-placeholder="enter_fullname" required>
          </div>
          <div class="form-group" style="margin-bottom: 12px;">
            <label for="reg-username" data-i18n="username_reg">Username *</label>
            <input type="text" id="reg-username" class="form-control" placeholder="Choose username" data-i18n-placeholder="choose_username" required>
          </div>
          <div class="form-group" style="margin-bottom: 12px;">
            <label for="reg-password" data-i18n="password_reg">Password *</label>
            <div class="password-wrapper">
              <input type="password" id="reg-password" class="form-control" placeholder="Password (min 4 chars)" data-i18n-placeholder="min_chars_password" required>
              <button type="button" class="password-toggle-btn" toggle-target="reg-password" title="Toggle password visibility">
                <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
              </button>
            </div>
          </div>
          <div class="form-group" style="margin-bottom: 12px;">
            <label for="reg-title" data-i18n="job_title_reg">Job Title</label>
            <input type="text" id="reg-title" class="form-control" placeholder="e.g. Systems Engineer" data-i18n-placeholder="enter_jobtitle" value="IT Operations">
          </div>
          <div class="form-group" style="margin-bottom: 12px;">
            <label for="reg-department" data-i18n="department_reg">Department *</label>
            <select id="reg-department" class="form-control" required>
              <!-- Populated by JS -->
            </select>
          </div>
          <div class="form-group" style="margin-bottom: 20px;">
            <label for="reg-role" data-i18n="system_role_reg">System Role *</label>
            <select id="reg-role" class="form-control" required>
              <option value="Requester" data-i18n="role_req_desc">Requester (Developer / Owner)</option>
              <option value="CAB Approver" data-i18n="role_cab_desc">CAB Approver (Change Advisory Board)</option>
              <option value="Administrator" data-i18n="role_admin_desc">Administrator</option>
            </select>
          </div>
          <div id="register-error" style="color: #f87171; font-size: 0.85rem; margin-bottom: 16px; text-align: center; display: none;"></div>
          <button type="submit" class="btn btn-primary w-full" style="justify-content: center; width: 100%;">
            <span data-i18n="create_account_and_sign_in">Create Account & Sign In</span> <i data-lucide="user-plus" style="width: 18px; height: 18px; margin-left: 6px;"></i>
          </button>
          <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 16px;">
            <span data-i18n="has_account">Already have an account?</span> <a href="#" id="link-show-login" data-i18n="sign_in_here" style="color: #c084fc; text-decoration: none; font-weight: 600;">Sign in here</a>
          </p>
        </form>
      </div>
    </div>

  </div>

  <!-- Load the main application logic -->
  <script src="app.js?v=21"></script>
</body>
</html>
