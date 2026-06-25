// Hopper App Engine - PHP Full-Stack Version with i18n Multi-Language Support

document.addEventListener("DOMContentLoaded", () => {
  // --- THEME LOAD & INITIALIZATION (DARK/LIGHT MODE) ---
  let currentTheme = localStorage.getItem("hopper_theme") || "light";
  const btnThemeToggle = document.getElementById("btn-theme-toggle");

  function applyTheme(theme) {
    if (theme === "dark") {
      document.body.classList.add("dark-theme");
    } else {
      document.body.classList.remove("dark-theme");
    }
  }

  // Apply saved theme instantly
  applyTheme(currentTheme);

  if (btnThemeToggle) {
    btnThemeToggle.addEventListener("click", (e) => {
      e.preventDefault();
      currentTheme = currentTheme === "light" ? "dark" : "light";
      localStorage.setItem("hopper_theme", currentTheme);
      applyTheme(currentTheme);
      if (activeTab === "analytics") {
        renderAnalyticsTab();
      }
      refreshIcons();
    });
  }

  // --- TRANSLATIONS (EN / TR) ---
  let currentLang = localStorage.getItem("hopper_lang") || "en";

  const translations = {
    en: {
      app_title: "Hopper",
      my_profile: "My Profile",
      settings: "Settings",
      sign_out: "Sign out",
      dashboard: "Dashboard",
      dashboard_subtitle: "Change requests and overall operational status",
      change_requests: "Change Requests",
      changes_subtitle: "Track all planned, ongoing, and completed changes",
      approval_center: "Approval Center",
      approvals_subtitle: "Critical change requests awaiting board approval",
      change_calendar: "Change Calendar",
      calendar_subtitle: "Calendar planning to prevent schedule conflicts",
      user_directory: "User Directory",
      users_subtitle: "Manage system users, contact details, and RBAC roles",
      about: "About",
      about_subtitle: "Development details and project information",
      new_change_request: "New Change Request",
      total_changes: "Total Changes",
      awaiting_approval: "Awaiting Approval",
      implementing: "Implementing",
      success_rate: "Success Rate",
      active_change_tracking: "Active Change Tracking",
      view_all: "View All",
      recent_activities: "Recent Activities",
      search_placeholder: "Search by request title, ID, or owner...",
      all_statuses: "All Statuses",
      status_draft: "Draft",
      status_under_review: "Under Review",
      status_pending_approval: "Pending Approval",
      status_approved: "Approved",
      status_implementing: "Implementing",
      status_completed: "Completed",
      status_rolled_back: "Rolled Back",
      status_rejected: "Rejected",
      all_risks: "All Risk Levels",
      risk_high: "High Risk",
      risk_medium: "Medium Risk",
      risk_low: "Low Risk",
      export_cab_report: "Export CAB Report",
      approvals_intro: "List of change requests awaiting Change Advisory Board (CAB) and stakeholder approval. Evaluate approval steps based on business continuity and risk analysis.",
      my_profile_settings: "My Profile Settings",
      preferred_language: "Preferred Language",
      save_profile_changes: "Save Profile Changes",
      avatar_help: "Supports PNG, JPG. Max 1MB. Image is saved inside the database.",
      full_name: "Full Name",
      username_readonly: "Username (Read-only)",
      job_title: "Job Title",
      department: "Department",
      email_address: "Email Address",
      phone_number: "Phone Number",
      new_password: "New Password",
      leave_empty: "Leave empty to keep current",
      confirm_password: "Confirm Password",
      confirm_new_password: "Confirm new password",
      pending_registration_requests: "Pending Registration Requests",
      review_registrations: "Review and approve new user account registrations.",
      applicant: "Applicant",
      job_title_dept: "Job Title & Dept",
      requested_role: "Requested Role",
      date: "Date",
      actions: "Actions",
      user_directory_title: "User Directory & Role Management (Admin Only)",
      system_access_control: "System Access Control",
      user: "User",
      contact_info: "Contact Info",
      system_role: "System Role",
      general_system_settings: "General System Settings",
      system_administration: "System Administration",
      manage_departments: "Manage Departments",
      departments_intro: "Add or remove departments available for user registrations and profiles.",
      dept_placeholder: "e.g. Finance",
      add: "Add",
      manage_change_categories: "Manage Change Categories",
      categories_intro: "Configure options for change request categorization.",
      category_placeholder: "e.g. Infrastructure",
      about_title_large: "About Hopper",
      about_description: "Hopper is a premium, lightweight Change Management System designed to track, coordinate, and review infrastructure and software change requests with complete audit control and workflow transparency.",
      lead_developer: "Lead Developer",
      create_title: "Create New Change Request",
      req_title_label: "Request Title *",
      change_title_placeholder: "Change title",
      desc_label: "Description & Business Case *",
      desc_placeholder: "Explain why this change is needed and what will be done...",
      requester_name_label: "Requester (Full Name) *",
      requester_title_label: "Requester Title",
      owner_name_label: "Implementation Owner *",
      owner_title_label: "Owner Title",
      category_label: "Category *",
      risk_level_label: "Risk Level *",
      calculate_risk: "Calculate Risk",
      risk_assessment_guide: "Risk Assessment Guide",
      close: "Close",
      risk_q_prod: "Affects Production Environment",
      risk_q_downtime: "Requires System Downtime / Maintenance Window",
      risk_q_untested: "Has NOT been tested in Pre-Prod/Staging Environment",
      risk_q_no_rollback: "Rollback Plan is untested/complex",
      calculated_level: "Calculated Level:",
      target_date_label: "Target Date *",
      impact_label: "Impact Analysis *",
      impact_placeholder: "Impact and outage details...",
      rollback_label: "Rollback Plan *",
      rollback_placeholder: "Rollback steps in case of failure...",
      tasks_label: "Implementation Steps / Tasks (One per line) *",
      tasks_placeholder: "1. Task\n2. Task\n3. Task",
      cancel: "Cancel",
      save_draft: "Save Request (Draft)",
      description_case: "Description & Business Case",
      impact_analysis: "Impact Analysis",
      rollback_plan: "Rollback Plan",
      progress: "Implementation Steps & Progress",
      workflow_controls: "Workflow Controls",
      request_details: "Request Details",
      requester: "Requester",
      owner: "Owner",
      target_date: "Target Date",
      approval_states: "Approval States",
      runbooks_documentation: "Runbooks & Documentation",
      upload_file_attachment: "Upload File Attachment",
      upload_help: "Max size: 2MB. Formats: PDF, TXT, DOCX, XLSX, PNG, JPG, ZIP.",
      discussion_comments: "Discussion & Comments",
      write_comment: "Write a comment...",
      edit_user_title: "Edit User Details",
      username_label: "Username",
      full_name_label: "Full Name *",
      job_title_label: "Job Title",
      department_label: "Department *",
      system_role_label: "System Role *",
      role_requester: "Requester",
      role_cab_approver: "CAB Approver",
      role_administrator: "Administrator",
      role_req: "Requester",
      role_cab: "CAB Approver",
      role_admin: "Administrator",
      role_req_desc: "Requester (Developer / Owner)",
      role_cab_desc: "CAB Approver (Change Advisory Board)",
      role_admin_desc: "Administrator",
      email_label: "Email",
      phone_label: "Phone",
      new_password_label: "New Password",
      keep_password_placeholder: "Leave blank to keep current",
      save_changes: "Save Changes",
      sign_in_title: "Sign In to System",
      username: "Username",
      enter_username: "Enter username (e.g. admin)",
      password: "Password",
      enter_password: "Enter password (e.g. admin123)",
      sign_in: "Sign In",
      no_account: "Don't have an account?",
      register_here: "Register here",
      full_name_reg: "Full Name *",
      enter_fullname: "e.g. Alice Smith",
      username_reg: "Username *",
      choose_username: "Choose username",
      password_reg: "Password *",
      min_chars_password: "Password (min 4 chars)",
      job_title_reg: "Job Title",
      enter_jobtitle: "e.g. Systems Engineer",
      department_reg: "Department *",
      system_role_reg: "System Role *",
      create_account_and_sign_in: "Create Account & Sign In",
      has_account: "Already have an account?",
      sign_in_here: "Sign in here",
      
      // Dynamic JS messages
      alert_image_size: "Profile image size must be less than 1MB.",
      alert_user_updated: "User updated successfully!",
      confirm_delete_attachment: "Are you sure you want to delete this attachment?",
      confirm_delete_change: "Are you sure you want to delete this change request?",
      confirm_approve_reg: "Are you sure you want to approve this registration request?",
      confirm_reject_reg: "Are you sure you want to reject this registration request?",
      confirm_delete_dept: "Are you sure you want to delete the department",
      confirm_delete_cat: "Are you sure you want to delete the category",
      registration_success: "Your registration request has been submitted. Please wait for approval.",
      no_changes: "No active or critical change requests.",
      no_matching_changes: "No change requests match the criteria.",
      no_pending_approvals: "No Pending Approvals",
      no_pending_approvals_desc: "Great! All pending change requests in the system have been approved or reviewed.",
      no_activities: "No recent activities.",
      no_departments: "No departments found.",
      no_categories: "No categories found.",
      no_comments: "No comments posted yet.",
      no_attachments: "No attachments uploaded.",
      no_changes_to_export: "No change requests to export.",
      alert_error_creating: "Error creating request: ",
      alert_error_submitting: "Failed to submit request to server.",
      alert_error_updating: "Failed to update profile.",
      alert_error_communicating: "Failed to communicate with server.",
      alert_error_deleting_dept: "Error deleting department: ",
      alert_error_deleting_cat: "Error deleting category: ",
      alert_error_adding_dept: "Error adding department: ",
      alert_error_adding_cat: "Error adding category: ",
      alert_error_updating_user: "Failed to update user.",
      alert_upload_reading: "Reading file...",
      alert_upload_uploading: "Uploading file...",
      alert_upload_failed: "Upload failed. Try again.",
      alert_upload_error: "Error reading file.",
      alert_auth_failed: "Unable to connect to PHP backend. Verify local server configuration.",
      alert_invalid_passwords: "New passwords do not match.",
      alert_profile_updated: "Profile updated successfully!",
      alert_file_size_exceeded: "File size exceeds 2MB limit.",
      assigned_group_label: "Assigned Group",
      assigned_group: "Assigned Group",
      manage_groups: "Manage User Groups",
      groups_intro: "Add or remove user groups/teams for assignment and routing.",
      group_placeholder: "e.g. DevOps",
      webhook_settings: "Webhook Settings",
      webhook_intro: "Configure system notifications to be sent to external team channels.",
      webhook_url: "Webhook URL",
      notify_on_create: "Notify on change creation",
      notify_on_status: "Notify on workflow status changes",
      notify_on_highrisk: "Only notify for High Risk changes",
      save_settings: "Save Settings",
      revision_history: "Revision History",
      print_cab_report: "Print CAB Report (PDF)",
      edit_change_request: "Edit Request",
      save_request_changes: "Save Request Changes",
      overlaps_found: "overlaps found",
      no_overlaps_found: "No overlaps found",
      overlap_alert_label: "Outage Overlap Warning:",
      notifications: "Notifications",
      new_notif: "New",
      clear_all: "Clear All",
      no_notifications: "No pending notifications.",
      new_activity: "New Activity",
      approval_pending_change: "Approval Pending Change",
      action_profile_updated: "updated their profile settings",
      action_webhook_updated: "updated webhook notification settings",
      action_approved_registration: "approved registration request for '{username}'",
      action_rejected_registration: "rejected registration request for '{username}'",
      action_changed_role: "changed system role for user '{username}' to '{role}'",
      action_updated_user_details: "updated user details for '{username}'",
      action_created_change: "created a new change request: \"{title}\"",
      action_updated_status: "updated status to {status}",
      action_updated_task: "updated task status: \"{task}\" ({status})",
      action_added_comment: "added a comment: \"{comment}\"",
      action_deleted_change: "deleted the change request \"{title}\"",
      action_added_dept: "added a new department: \"{dept}\"",
      action_deleted_dept: "deleted department: \"{dept}\"",
      action_added_cat: "added a new change category: \"{cat}\"",
      action_deleted_cat: "deleted change category: \"{cat}\"",
      action_uploaded_file: "uploaded attachment '{file}'",
      action_deleted_file: "deleted attachment '{file}'",
      action_added_group: "added a new group: \"{grp}\"",
      action_deleted_group: "deleted group: \"{grp}\"",
      action_edited_change_fields: "edited change request fields: {fields}",
      action_submitted_review: "submitted the change request for review.",
      action_pulled_draft: "pulled the change request back to draft.",
      action_submitted_approval: "submitted the change request for approval.",
      action_approved_change: "approved the change request.",
      action_rejected_change: "rejected the change request.",
      action_started_maintenance: "started the change implementation in production.",
      action_completed_successfully: "marked the change request as completed successfully.",
      action_failed_rollback: "reported the change as failed and rolled back systems.",
      action_reset_draft: "reset the change request to draft for testing.",
      completed: "Completed",
      pending: "Pending",
      view_photo: "View Photo",
      delete_photo: "Delete Photo",
      confirm_delete_photo: "Are you sure you want to delete your profile photo?",
      no_groups: "No groups available.",
      confirm_delete_group: "Are you sure you want to delete group",
      alert_error_deleting_group: "Error deleting group: ",
      alert_error_adding_group: "Error adding group: ",
      alert_settings_saved: "Webhook settings saved successfully.",
      alert_error_saving_settings: "Error saving settings: ",
      change_id: "Change ID",
      change_title: "Title",
      status: "Status",
      msg_owner_admin_draft: "Only the owner or an administrator can manage this draft request.",
      msg_owner_admin_manage: "Only the owner or an administrator can manage this request.",
      msg_cab_admin_approve: "Only CAB Approvers or Administrators can approve or reject this request.",
      msg_request_closed: "This change request is closed. No actions can be taken.",
      analytics: "Analytics",
      analytics_title: "System Analytics",
      analytics_desc: "Change success rates, operational KPIs, and distribution charts.",
      analytics_subtitle: "Change success rates and distribution charts",
      audit_logs: "Audit Logs",
      audit_logs_title: "System Audit Trail",
      audit_logs_desc: "Permanent log records of all security and workflow activities.",
      audit_logs_subtitle: "Permanent log records of security and workflow activities",
      ad_settings: "Active Directory / LDAP Settings",
      ad_intro: "Configure system integration with corporate Active Directory / LDAP servers for unified authentication.",
      ad_enable: "Enable Active Directory Authentication",
      ad_server: "LDAP Server Address",
      ad_port: "LDAP Server Port",
      ad_domain: "Domain Suffix (UPN)",
      ad_basedn: "Base DN (Search Directory)",
      save_ad_settings: "Save AD Settings",
      alert_ad_saved: "Active Directory settings saved successfully.",
      alert_ad_testing: "Testing Active Directory connection...",
      ad_view_guide: "View Integration Guide",
      ad_hide_guide: "Hide Integration Guide"
    }
  };

  function translatePage() {
    const elements = document.querySelectorAll("[data-i18n]");
    elements.forEach(el => {
      const key = el.getAttribute("data-i18n");
      if (translations[currentLang] && translations[currentLang][key]) {
        el.textContent = translations[currentLang][key];
      }
    });

    const placeholders = document.querySelectorAll("[data-i18n-placeholder]");
    placeholders.forEach(el => {
      const key = el.getAttribute("data-i18n-placeholder");
      if (translations[currentLang] && translations[currentLang][key]) {
        el.setAttribute("placeholder", translations[currentLang][key]);
      }
    });
    
    // Set preferred language dropdown selection
    const profileLang = document.getElementById("profile-language");
    if (profileLang) {
      profileLang.value = currentLang;
    }
  }

  function t(key) {
    return (translations[currentLang] && translations[currentLang][key]) || key;
  }

  // Language select dropdown value is saved upon form submission

  refreshIcons();

  // --- UTILITIES ---
  function escapeHTML(str) {
    if (str === null || str === undefined) return "";
    return str
      .toString()
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function getTranslatedStatus(status) {
    if (!status) return "";
    const key = "status_" + status.toLowerCase().replace(/\s+/g, "_");
    return t(key) || status;
  }

  function getTranslatedRole(role) {
    if (!role) return "";
    const key = "role_" + role.toLowerCase().replace(/\s+/g, "_");
    return t(key) || role;
  }

  function getTranslatedAction(action) {
    if (!action) return "";

    const mappings = [
      {
        regex: /^updated their profile settings$/,
        key: 'action_profile_updated',
        vars: []
      },
      {
        regex: /^updated webhook notification settings$/,
        key: 'action_webhook_updated',
        vars: []
      },
      {
        regex: /^approved registration request for '([^']+)'$/,
        key: 'action_approved_registration',
        vars: ['username']
      },
      {
        regex: /^rejected registration request for '([^']+)'$/,
        key: 'action_rejected_registration',
        vars: ['username']
      },
      {
        regex: /^changed system role for user '([^']+)' to '([^']+)'$/,
        key: 'action_changed_role',
        vars: ['username', 'role'],
        translateVars: { role: getTranslatedRole }
      },
      {
        regex: /^updated user details for '([^']+)'$/,
        key: 'action_updated_user_details',
        vars: ['username']
      },
      {
        regex: /^created a new change request: "([^"]+)"$/,
        key: 'action_created_change',
        vars: ['title']
      },
      {
        regex: /^updated status to (.*)$/,
        key: 'action_updated_status',
        vars: ['status'],
        translateVars: { status: getTranslatedStatus }
      },
      {
        regex: /^updated task status: "([^"]+)" \((Completed|Pending)\)$/,
        key: 'action_updated_task',
        vars: ['task', 'status'],
        translateVars: { status: (s) => t(s.toLowerCase()) }
      },
      {
        regex: /^added a comment: "([^"]+)"$/,
        key: 'action_added_comment',
        vars: ['comment']
      },
      {
        regex: /^deleted the change request "([^"]+)"$/,
        key: 'action_deleted_change',
        vars: ['title']
      },
      {
        regex: /^added a new department: "([^"]+)"$/,
        key: 'action_added_dept',
        vars: ['dept']
      },
      {
        regex: /^deleted department: "([^"]+)"$/,
        key: 'action_deleted_dept',
        vars: ['dept']
      },
      {
        regex: /^added a new change category: "([^"]+)"$/,
        key: 'action_added_cat',
        vars: ['cat']
      },
      {
        regex: /^deleted change category: "([^"]+)"$/,
        key: 'action_deleted_cat',
        vars: ['cat']
      },
      {
        regex: /^uploaded attachment '([^']+)'$/,
        key: 'action_uploaded_file',
        vars: ['file']
      },
      {
        regex: /^deleted attachment '([^']+)'$/,
        key: 'action_deleted_file',
        vars: ['file']
      },
      {
        regex: /^added a new group: "([^"]+)"$/,
        key: 'action_added_group',
        vars: ['grp']
      },
      {
        regex: /^deleted group: "([^"]+)"$/,
        key: 'action_deleted_group',
        vars: ['grp']
      },
      {
        regex: /^edited change request fields: (.*)$/,
        key: 'action_edited_change_fields',
        vars: ['fields'],
        translateVars: {
          fields: (fieldsStr) => {
            return fieldsStr.split(', ').map(f => {
              const cleaned = f.trim().toLowerCase();
              if (cleaned === 'title') return t('change_title');
              if (cleaned === 'description') return t('desc_label').replace(' *', '');
              if (cleaned === 'category') return t('category_label').replace(' *', '');
              if (cleaned === 'risk level') return t('risk_level_label').replace(' *', '');
              if (cleaned === 'target date') return t('target_date');
              if (cleaned === 'impact analysis') return t('impact_analysis');
              if (cleaned === 'rollback plan') return t('rollback_plan');
              if (cleaned === 'assigned group') return t('assigned_group_label');
              if (cleaned === 'task checklist') return t('progress');
              return f;
            }).join(', ');
          }
        }
      },
      {
        regex: /^submitted the change request for review\.$/,
        key: 'action_submitted_review',
        vars: []
      },
      {
        regex: /^pulled the change request back to draft\.$/,
        key: 'action_pulled_draft',
        vars: []
      },
      {
        regex: /^submitted the change request for approval\.$/,
        key: 'action_submitted_approval',
        vars: []
      },
      {
        regex: /^approved the change request\.$/,
        key: 'action_approved_change',
        vars: []
      },
      {
        regex: /^rejected the change request\.$/,
        key: 'action_rejected_change',
        vars: []
      },
      {
        regex: /^started the change implementation in production\.$/,
        key: 'action_started_maintenance',
        vars: []
      },
      {
        regex: /^marked the change request as completed successfully\.$/,
        key: 'action_completed_successfully',
        vars: []
      },
      {
        regex: /^reported the change as failed and rolled back systems\.$/,
        key: 'action_failed_rollback',
        vars: []
      },
      {
        regex: /^reset the change request to draft for testing\.$/,
        key: 'action_reset_draft',
        vars: []
      }
    ];

    for (let mapping of mappings) {
      const match = action.match(mapping.regex);
      if (match) {
        let template = t(mapping.key);
        mapping.vars.forEach((varName, idx) => {
          let value = match[idx + 1];
          if (mapping.translateVars && mapping.translateVars[varName]) {
            value = mapping.translateVars[varName](value);
          }
          template = template.replace(`{${varName}}`, value);
        });
        return template;
      }
    }

    return action;
  }

  // --- STATE MANAGEMENT ---
  let changes = [];
  let activities = [];
  let categories = [];
  let departments = [];
  let groups = [];
  let activeTab = "dashboard";
  let activeChangeId = null;
  let editingChangeId = null;
  let currentUser = null;

  // Calendar State
  const _now = new Date();
  let currentYear = _now.getFullYear();
  let currentMonth = _now.getMonth(); // 0-indexed
  const monthNames = [
    "January", "February", "March", "April", "May", "June", 
    "July", "August", "September", "October", "November", "December"
  ];


  // --- AUTHENTICATION HELPERS ---
  function getToken() {
    return localStorage.getItem("hopper_token");
  }

  function getAuthHeaders() {
    const token = getToken();
    return {
      "Content-Type": "application/json",
      "Authorization": token ? `Bearer ${token}` : "",
      "X-Authorization": token ? `Bearer ${token}` : ""
    };
  }

  // Selected Auth Type (default: local)
  let currentAuthType = "local";

  // --- FETCH PUBLIC LOGIN CONFIGURATION ---
  async function fetchLoginConfig() {
    try {
      const config = await apiCall("get_login_config");
      const authTabs = document.getElementById("auth-method-tabs");
      const tabLdap = document.getElementById("tab-login-ldap");
      const domainSelect = document.getElementById("login-domain-select");
      
      if (config.adEnabled) {
        if (authTabs) authTabs.style.display = "flex";
        if (tabLdap) tabLdap.style.display = "block";
        
        // Populate select list
        if (domainSelect) {
          domainSelect.innerHTML = "";
          const domains = config.adDomain ? config.adDomain.split(',').map(d => d.trim()).filter(d => d.length > 0) : [];
          domains.forEach(d => {
            const opt = document.createElement("option");
            opt.value = d;
            opt.textContent = `@${d.replace(/^@/, '')}`;
            domainSelect.appendChild(opt);
          });
        }
        
        // Default to local, reset view
        setAuthType("local");
      } else {
        if (authTabs) authTabs.style.display = "none";
        if (tabLdap) tabLdap.style.display = "none";
        setAuthType("local");
      }
    } catch (err) {
      console.error("Failed to fetch public login configuration:", err);
    }
  }

  // --- TOGGLE AUTHENTICATION TYPE (LOCAL vs LDAP) ---
  function setAuthType(type) {
    currentAuthType = type;
    const tabLocal = document.getElementById("tab-login-local");
    const tabLdap = document.getElementById("tab-login-ldap");
    const domainSelect = document.getElementById("login-domain-select");
    const usernameInput = document.getElementById("login-username");
    
    if (type === "ldap") {
      if (tabLocal) tabLocal.classList.remove("active");
      if (tabLdap) tabLdap.classList.add("active");
      
      if (domainSelect && domainSelect.options.length > 0) {
        domainSelect.style.display = "block";
        if (usernameInput) {
          usernameInput.classList.add("form-control-input");
          usernameInput.placeholder = "Enter username (e.g. murat)";
          usernameInput.removeAttribute("data-i18n-placeholder");
        }
      } else {
        if (domainSelect) domainSelect.style.display = "none";
        if (usernameInput) {
          usernameInput.classList.remove("form-control-input");
          usernameInput.placeholder = "Enter AD username (e.g. murat@guler.com)";
          usernameInput.removeAttribute("data-i18n-placeholder");
        }
      }
    } else {
      if (tabLocal) tabLocal.classList.add("active");
      if (tabLdap) tabLdap.classList.remove("active");
      if (domainSelect) domainSelect.style.display = "none";
      if (usernameInput) {
        usernameInput.classList.remove("form-control-input");
        usernameInput.placeholder = "Enter username (e.g. admin)";
        usernameInput.setAttribute("data-i18n-placeholder", "enter_username");
      }
    }
  }

  // --- INITIAL CHECK AUTH ---
  async function checkAuth() {
    const token = getToken();
    const loginScreen = document.getElementById("login-screen");
    
    if (!token) {
      loginScreen.style.display = "flex";
      fetchLoginConfig();
      translatePage();
      refreshIcons();
      return;
    }

    // 1. First verify auth token itself
    try {
      const data = await apiCall("me");
      currentUser = data.user;
    } catch (err) {
      console.error("Auth token verification failed:", err);
      localStorage.removeItem("hopper_token");
      loginScreen.style.display = "flex";
      fetchLoginConfig();
      
      const isConnectionError = err.message === t("alert_error_communicating");
      if (isConnectionError) {
        const errorEl = document.getElementById("login-error");
        if (errorEl) {
          errorEl.textContent = t("alert_auth_failed");
          errorEl.style.display = "block";
        }
      }
      
      translatePage();
      refreshIcons();
      return;
    }

    // 2. Load system data and render page (do not clear session token if data/rendering fails)
    try {
      loginScreen.style.display = "none";
      updateUserProfileSidebar();
      
      // Load initial system data
      await fetchCategories();
      await fetchGroups();
      await refreshData();
      await switchTab(activeTab);
    } catch (err) {
      console.error("Initial data load/render failed:", err);
      // Keep session active, simply log the UI or retrieval error
    }
  }

  // Update profile sidebar/dropdown with current user info
  function updateUserProfileSidebar() {
    if (!currentUser) return;
    
    // Show/hide Admin User Directory, Audit Logs & Settings menu items dynamically
    const usersLi = document.getElementById("nav-users-li");
    const dropdownAuditLogsLink = document.getElementById("dropdown-audit-logs-link");
    const dropdownSettingsLink = document.getElementById("dropdown-settings-link");
    if (currentUser.role === "Administrator") {
      if (usersLi) usersLi.style.display = "block";
      if (dropdownAuditLogsLink) dropdownAuditLogsLink.style.display = "flex";
      if (dropdownSettingsLink) dropdownSettingsLink.style.display = "flex";
    } else {
      if (usersLi) usersLi.style.display = "none";
      if (dropdownAuditLogsLink) dropdownAuditLogsLink.style.display = "none";
      if (dropdownSettingsLink) dropdownSettingsLink.style.display = "none";
    }

    // Sync names
    const navUserName = document.getElementById("nav-user-name");
    if (navUserName) navUserName.textContent = currentUser.name;

    // Sync roles & titles
    const displayRoleTitle = `${getTranslatedRole(currentUser.role)} • ${currentUser.title || 'Owner'}`;
    const navUserRole = document.getElementById("nav-user-role");
    if (navUserRole) navUserRole.textContent = displayRoleTitle;
    
    // Compute initials
    const initials = currentUser.name
      .trim()
      .split(/\s+/)
      .map(n => n ? n[0] : "")
      .join("")
      .substring(0, 3)
      .toUpperCase();
      
    // Update header avatar
    const avatarEl = document.getElementById("nav-user-avatar");
    if (avatarEl) {
      if (currentUser.avatar) {
        avatarEl.innerHTML = `<img src="${currentUser.avatar}" style="width:100%; height:100%; border-radius:50%; object-fit:cover; display:block;">`;
        avatarEl.style.padding = "0";
        avatarEl.style.overflow = "hidden";
      } else {
        avatarEl.textContent = initials;
        avatarEl.style.padding = ""; // Reset
        avatarEl.style.overflow = "";
      }
    }
  }

  // --- API HELPER ---
  async function apiCall(action, method = "GET", body = null, extraHeaders = {}) {
    let url = action.startsWith("api.php") ? action : `api.php?action=${action}`;
    const options = {
      method,
      cache: "no-store",
      headers: { ...getAuthHeaders(), ...extraHeaders }
    };
    if (body) {
      if (body instanceof FormData) {
        options.body = body;
        delete options.headers["Content-Type"]; // let browser set it with boundary
      } else {
        options.headers["Content-Type"] = "application/json";
        options.body = JSON.stringify(body);
      }
    }
    const res = await fetch(url, options);
    let data;
    try {
      data = await res.json();
    } catch(e) {
      throw new Error(t("alert_error_communicating"));
    }
    if (!res.ok) {
      throw new Error(data.error || t("alert_error_communicating"));
    }
    return data;
  }

  // --- DATA REFRESHERS ---
  async function fetchCategories() {
    try {
      const data = await apiCall("categories");
      categories = data.categories;
    } catch (err) {
      console.error("Error fetching categories:", err);
    }
  }

  async function fetchDepartments() {
    try {
      const data = await apiCall("get_departments");
      departments = data.departments;
      populateDepartmentDropdowns();
    } catch (err) {
      console.error("Error fetching departments:", err);
    }
  }

  async function fetchGroups() {
    try {
      const data = await apiCall("get_groups");
      groups = data.groups;
      populateGroupDropdowns();
    } catch (err) {
      console.error("Error fetching groups:", err);
    }
  }

  function populateGroupDropdowns() {
    const changeSelect = document.getElementById("change-assigned-group");
    const userSelect = document.getElementById("edit-user-group");
    
    const optionsHtml = `<option value="">-- None --</option>` + groups.map(g => `<option value="${escapeHTML(g)}">${escapeHTML(g)}</option>`).join("");
    
    if (changeSelect) {
      const currentVal = changeSelect.value;
      changeSelect.innerHTML = optionsHtml;
      if (currentVal && groups.includes(currentVal)) {
        changeSelect.value = currentVal;
      }
    }
    
    if (userSelect) {
      const currentVal = userSelect.value;
      userSelect.innerHTML = optionsHtml;
      if (currentVal && groups.includes(currentVal)) {
        userSelect.value = currentVal;
      }
    }
  }

  function populateDepartmentDropdowns() {
    const regSelect = document.getElementById("reg-department");
    const profileSelect = document.getElementById("profile-department");
    const optionsHtml = departments.map(d => `<option value="${escapeHTML(d)}">${escapeHTML(d)}</option>`).join("");

    if (regSelect) {
      const currentVal = regSelect.value;
      regSelect.innerHTML = optionsHtml;
      if (currentVal && departments.includes(currentVal)) {
        regSelect.value = currentVal;
      } else {
        if (departments.length > 0) {
          regSelect.value = departments[0];
        }
      }
    }

    if (profileSelect) {
      const currentVal = profileSelect.value;
      profileSelect.innerHTML = optionsHtml;
      if (currentVal && departments.includes(currentVal)) {
        profileSelect.value = currentVal;
      }
    }
  }

  async function refreshData() {
    if (!getToken()) return;
    try {
      // Get changes
      const changesData = await apiCall("get_changes");
      changes = changesData.changes;

      // Get activities
      const actData = await apiCall("activities");
      activities = actData.activities;
      
      updateApprovalBadge();
      updateNotifications();
    } catch (err) {
      console.error("Error refreshing data from server:", err);
    }
  }

  // --- LOGIN & REGISTER HANDLERS ---
  const formLogin = document.getElementById("form-login");
  const formRegister = document.getElementById("form-register");
  const linkShowRegister = document.getElementById("link-show-register");
  const linkShowLogin = document.getElementById("link-show-login");
  const btnLogout = document.getElementById("btn-logout");
  const authTitle = document.getElementById("auth-title");
  
  const headerUserProfile = document.getElementById("header-user-profile");
  const dropdownMenu = document.getElementById("profile-dropdown-menu");
  
  const btnNotifications = document.getElementById("btn-notifications");
  const notificationsMenu = document.getElementById("notifications-menu");

  if (headerUserProfile && dropdownMenu) {
    headerUserProfile.addEventListener("click", (e) => {
      e.stopPropagation();
      const isVisible = dropdownMenu.style.display === "flex";
      dropdownMenu.style.display = isVisible ? "none" : "flex";
      if (notificationsMenu) notificationsMenu.style.display = "none";
    });
  }
  
  if (btnNotifications && notificationsMenu) {
    btnNotifications.addEventListener("click", (e) => {
      e.stopPropagation();
      const isVisible = notificationsMenu.style.display === "block";
      notificationsMenu.style.display = isVisible ? "none" : "block";
      if (dropdownMenu) dropdownMenu.style.display = "none";
      
      if (!isVisible) {
        // Mark all currently rendered notifications as seen
        let seenNotifs = [];
        try {
          seenNotifs = JSON.parse(localStorage.getItem("hopper_seen_notifs") || "[]");
        } catch(e) {}
        
        document.querySelectorAll("#notifications-list .notification-item").forEach(item => {
          const id = item.getAttribute("data-id");
          if (id && !seenNotifs.includes(id)) {
            seenNotifs.push(id);
          }
        });
        
        localStorage.setItem("hopper_seen_notifs", JSON.stringify(seenNotifs));
        
        // Hide badge
        const badgeEl = document.getElementById("notification-badge");
        if (badgeEl) badgeEl.style.display = "none";
      }
    });
  }

  // Close dropdown menus when clicking outside
  document.addEventListener("click", (e) => {
    if (dropdownMenu && dropdownMenu.style.display === "flex") {
      if (!headerUserProfile.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.style.display = "none";
      }
    }
    if (notificationsMenu && notificationsMenu.style.display === "block") {
      if (!btnNotifications.contains(e.target) && !notificationsMenu.contains(e.target)) {
        notificationsMenu.style.display = "none";
      }
    }
  });

  // Dropdown Tab Navigation Event Listeners
  const dropdownTabLinks = document.querySelectorAll("#profile-dropdown-menu [data-dropdown-tab]");
  dropdownTabLinks.forEach(link => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const tab = link.getAttribute("data-dropdown-tab");
      switchTab(tab);
      if (dropdownMenu) dropdownMenu.style.display = "none";
    });
  });

  linkShowRegister.addEventListener("click", (e) => {
    e.preventDefault();
    formLogin.style.display = "none";
    formRegister.style.display = "block";
    authTitle.setAttribute("data-i18n", "create_account_and_sign_in");
    const authTabs = document.getElementById("auth-method-tabs");
    if (authTabs) authTabs.style.display = "none";
    translatePage();
    refreshIcons();
  });

  linkShowLogin.addEventListener("click", (e) => {
    e.preventDefault();
    formRegister.style.display = "none";
    formLogin.style.display = "block";
    authTitle.setAttribute("data-i18n", "sign_in_title");
    fetchLoginConfig();
    translatePage();
    refreshIcons();
  });

  // Bind Auth Tab click events
  const tabLoginLocal = document.getElementById("tab-login-local");
  const tabLoginLdap = document.getElementById("tab-login-ldap");
  if (tabLoginLocal) {
    tabLoginLocal.addEventListener("click", () => setAuthType("local"));
  }
  if (tabLoginLdap) {
    tabLoginLdap.addEventListener("click", () => setAuthType("ldap"));
  }

  // Login Submit
  formLogin.addEventListener("submit", async (e) => {
    e.preventDefault();
    const username = document.getElementById("login-username").value.trim();
    const password = document.getElementById("login-password").value;
    const adDomain = document.getElementById("login-domain-select")?.value || "";
    const errorEl = document.getElementById("login-error");
    
    errorEl.style.display = "none";

    try {
      const data = await apiCall("login", "POST", { 
        username, 
        password,
        authType: currentAuthType,
        adDomain
      }, { "Content-Type": "application/json" });
      localStorage.setItem("hopper_token", data.token);
      currentUser = data.user;
      document.getElementById("login-screen").style.display = "none";
      formLogin.reset();
      
      updateUserProfileSidebar();
      await fetchCategories();
      await refreshData();
      switchTab("dashboard");
    } catch (err) {
      errorEl.textContent = err.message || t("alert_error_communicating");
      errorEl.style.display = "block";
    }
  });

  // Register Submit
  formRegister.addEventListener("submit", async (e) => {
    e.preventDefault();
    const name = document.getElementById("reg-name").value.trim();
    const username = document.getElementById("reg-username").value.trim();
    const password = document.getElementById("reg-password").value;
    const title = document.getElementById("reg-title").value.trim();
    const department = document.getElementById("reg-department").value;
    const role = document.getElementById("reg-role").value;
    const errorEl = document.getElementById("register-error");

    errorEl.style.display = "none";

    if (password.length < 4) {
      errorEl.textContent = t("min_chars_password");
      errorEl.style.display = "block";
      return;
    }

    try {
      const data = await apiCall("register", "POST", { name, username, password, title, department, role }, { "Content-Type": "application/json" });
      formRegister.reset();
      
      // Switch back to Login form
      formRegister.style.display = "none";
      formLogin.style.display = "block";
      authTitle.setAttribute("data-i18n", "sign_in_title");
      
      const loginErrorEl = document.getElementById("login-error");
      loginErrorEl.textContent = t("registration_success");
      loginErrorEl.style.color = "#34d399"; // success green
      loginErrorEl.style.display = "block";
      
      // Reset color back to red after 4 seconds for next potential error
      setTimeout(() => {
        loginErrorEl.style.color = "#f87171";
      }, 4000);
      
      translatePage();
      refreshIcons();
    } catch (err) {
      errorEl.textContent = err.message || t("alert_error_communicating");
      errorEl.style.display = "block";
    }
  });

  // Logout Click
  btnLogout.addEventListener("click", () => {
    localStorage.removeItem("hopper_token");
    currentUser = null;
    document.getElementById("login-screen").style.display = "flex";
    fetchLoginConfig();
    formLogin.style.display = "block";
    formRegister.style.display = "none";
    authTitle.setAttribute("data-i18n", "sign_in_title");
    document.getElementById("login-error").style.display = "none";
    document.getElementById("register-error").style.display = "none";
    translatePage();
    refreshIcons();
  });

  // --- HELPER: ICON REFRESHER ---
  function refreshIcons() {
    if (window.lucide && typeof window.lucide.createIcons === "function") {
      if (document.querySelector("i[data-lucide]")) {
        window.lucide.createIcons();
      }
    }
  }

  // --- SIDEBAR BADGE UPDATE ---
  function updateApprovalBadge() {
    const pendingCount = changes.filter(c => c.status === "Pending Approval").length;
    const badgeEl = document.getElementById("nav-approval-badge");
    if (pendingCount > 0) {
      badgeEl.textContent = pendingCount;
      badgeEl.style.display = "inline-flex";
    } else {
      badgeEl.style.display = "none";
    }
  }

  // --- NOTIFICATIONS UPDATE ---
  function updateNotifications() {
    const badgeEl = document.getElementById("notification-badge");
    const countText = document.getElementById("notification-count-text");
    const listEl = document.getElementById("notifications-list");
    if (!badgeEl || !countText || !listEl) return;

    let notifs = [];
    
    // Add pending approvals
    if (currentUser && (currentUser.role === "CAB Approver" || currentUser.role === "Administrator")) {
      const pendingChanges = changes.filter(c => c.status === "Pending Approval");
      pendingChanges.forEach(c => {
        notifs.push({
          id: `approval_${c.id}`,
          changeId: c.id,
          title: t("approval_pending_change"),
          desc: `${c.id}: ${c.title}`,
          icon: "check-square"
        });
      });
    }

    // Add recent activities as notifications (max 10)
    let recentActs = activities.slice(0, 10);
    recentActs.forEach(act => {
      notifs.push({
        id: `activity_${act.id}`,
        title: t("new_activity"),
        desc: `${act.user}: ${getTranslatedAction(act.action)}`,
        icon: "activity"
      });
    });

    // Filter out cleared notifications
    let clearedNotifs = [];
    try {
      clearedNotifs = JSON.parse(localStorage.getItem("hopper_cleared_notifs") || "[]");
    } catch(e) {}

    notifs = notifs.filter(n => !clearedNotifs.includes(n.id));

    if (notifs.length > 0) {
      // Compute unseen count for badge
      let seenNotifs = [];
      try {
        seenNotifs = JSON.parse(localStorage.getItem("hopper_seen_notifs") || "[]");
      } catch(e) {}
      
      const unseenCount = notifs.filter(n => !seenNotifs.includes(n.id)).length;
      if (unseenCount > 0) {
        badgeEl.textContent = unseenCount;
        badgeEl.style.display = "block";
      } else {
        badgeEl.style.display = "none";
      }
      
      countText.textContent = `${notifs.length} ${t("new_notif")}`;
      
      let html = "";
      notifs.forEach(n => {
        html += `
          <div class="notification-item" data-id="${n.id}" data-change-id="${n.changeId || ''}" style="position: relative; display: flex; align-items: flex-start; gap: 10px; padding: 10px 36px 10px 15px; border-bottom: 1px solid var(--glass-border); transition: background 0.2s; cursor: ${n.changeId ? 'pointer' : 'default'};">
            <div class="notification-icon" style="background: rgba(124, 58, 237, 0.1); color: var(--accent-color); padding: 6px; border-radius: 50%; display: flex; flex-shrink: 0;"><i data-lucide="${n.icon}" style="width: 14px; height: 14px;"></i></div>
            <div class="notification-content" style="flex: 1;">
              <div class="notification-title" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 2px;">${escapeHTML(n.title)}</div>
              <div class="notification-desc" style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.3;">${escapeHTML(n.desc)}</div>
            </div>
            <button class="btn-clear-single-notif" data-id="${n.id}" style="position: absolute; right: 10px; top: 12px; background: none; border: none; color: var(--text-sub); cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; outline: none; border-radius: 50%;" title="${t("clear_all")}">
              <i data-lucide="x" style="width: 12px; height: 12px;"></i>
            </button>
          </div>
        `;
      });
      listEl.innerHTML = html;
      
      // Bind click events on notification items
      listEl.querySelectorAll(".notification-item").forEach(item => {
        item.addEventListener("click", (e) => {
          if (e.target.closest(".btn-clear-single-notif")) return;
          const changeId = item.getAttribute("data-change-id");
          if (changeId) {
            openDetailModal(changeId);
            notificationsMenu.style.display = "none";
          }
        });
      });

      // Bind click on clear single button
      listEl.querySelectorAll(".btn-clear-single-notif").forEach(btn => {
        btn.addEventListener("click", (e) => {
          e.stopPropagation();
          const notifId = btn.getAttribute("data-id");
          clearNotification(notifId);
        });
      });

      refreshIcons();
    } else {
      badgeEl.style.display = "none";
      countText.textContent = `0 ${t("new_notif")}`;
      listEl.innerHTML = `<div style="padding: 15px; text-align: center; color: var(--text-sub); font-size: 0.9rem;" data-i18n="no_notifications">${t("no_notifications")}</div>`;
    }
  }

  function clearNotification(id) {
    let clearedNotifs = [];
    try {
      clearedNotifs = JSON.parse(localStorage.getItem("hopper_cleared_notifs") || "[]");
    } catch(e) {}
    
    if (!clearedNotifs.includes(id)) {
      clearedNotifs.push(id);
      localStorage.setItem("hopper_cleared_notifs", JSON.stringify(clearedNotifs));
    }
    
    updateNotifications();
  }

  // Clear All Notifications Handler
  const btnClearAllNotifs = document.getElementById("btn-clear-notifications");
  if (btnClearAllNotifs) {
    btnClearAllNotifs.addEventListener("click", (e) => {
      e.stopPropagation();
      let displayedIds = [];
      document.querySelectorAll("#notifications-list .notification-item").forEach(item => {
        const id = item.getAttribute("data-id");
        if (id) displayedIds.push(id);
      });
      
      let clearedNotifs = [];
      try {
        clearedNotifs = JSON.parse(localStorage.getItem("hopper_cleared_notifs") || "[]");
      } catch(e) {}
      
      displayedIds.forEach(id => {
        if (!clearedNotifs.includes(id)) {
          clearedNotifs.push(id);
        }
      });
      
      localStorage.setItem("hopper_cleared_notifs", JSON.stringify(clearedNotifs));
      updateNotifications();
    });
  }

  // --- TAB ROUTER ---
  const menuItems = document.querySelectorAll(".menu-item");
  menuItems.forEach(item => {
    item.addEventListener("click", (e) => {
      e.preventDefault();
      const tab = item.getAttribute("data-tab");
      switchTab(tab);
    });
  });

  async function switchTab(tabName) {
    activeTab = tabName;
    
    // Update active class on sidebar
    menuItems.forEach(item => {
      if (item.getAttribute("data-tab") === tabName) {
        item.classList.add("active");
      } else {
        item.classList.remove("active");
      }
    });

    // Update main titles
    const titleEl = document.getElementById("main-title");
    const subtitleEl = document.getElementById("main-subtitle");
    
    if (tabName === "dashboard") {
      titleEl.textContent = t("dashboard");
      subtitleEl.textContent = t("dashboard_subtitle");
    } else if (tabName === "changes") {
      titleEl.textContent = t("change_requests");
      subtitleEl.textContent = t("changes_subtitle");
    } else if (tabName === "approvals") {
      titleEl.textContent = t("approval_center");
      subtitleEl.textContent = t("approvals_subtitle");
    } else if (tabName === "calendar") {
      titleEl.textContent = t("change_calendar");
      subtitleEl.textContent = t("calendar_subtitle");
    } else if (tabName === "profile") {
      titleEl.textContent = t("my_profile");
      subtitleEl.textContent = t("my_profile_settings");
    } else if (tabName === "users") {
      titleEl.textContent = t("user_directory");
      subtitleEl.textContent = t("users_subtitle");
    } else if (tabName === "settings") {
      titleEl.textContent = t("general_system_settings");
      subtitleEl.textContent = t("system_administration");
    } else if (tabName === "analytics") {
      titleEl.textContent = t("analytics") || "System Analytics";
      subtitleEl.textContent = t("analytics_subtitle") || "Change success rates and distribution charts";
    } else if (tabName === "audit-logs") {
      titleEl.textContent = t("audit_logs") || "System Audit Trail";
      subtitleEl.textContent = t("audit_logs_subtitle") || "Permanent log records of security and workflow activities";
    } else if (tabName === "about") {
      titleEl.textContent = t("about");
      subtitleEl.textContent = t("about_subtitle");
    }

    // Toggle tab sections
    document.querySelectorAll(".tab-content").forEach(content => {
      content.classList.remove("active");
    });
    document.getElementById(`tab-${tabName}`).classList.add("active");

    // Pull fresh data from database when navigating tabs
    await refreshData();

    // Render tab-specific views
    if (tabName === "dashboard") {
      renderDashboard();
    } else if (tabName === "changes") {
      renderChangesList();
    } else if (tabName === "approvals") {
      renderApprovalsList();
    } else if (tabName === "calendar") {
      renderCalendar();
    } else if (tabName === "profile") {
      renderProfileTab();
    } else if (tabName === "users") {
      fetchAdminUserDirectory();
    } else if (tabName === "analytics") {
      renderAnalyticsTab();
    } else if (tabName === "audit-logs") {
      renderAuditLogsTab();
    } else if (tabName === "settings") {
      await loadWebhookSettings();
      await loadAdSettings();
      renderSettingsTab();
    }

    translatePage();
    refreshIcons();
  }

  // --- RENDERER: DASHBOARD ---
  function renderDashboard() {
    // Compute KPIs
    const total = changes.length;
    const pending = changes.filter(c => c.status === "Pending Approval").length;
    const implementing = changes.filter(c => c.status === "Implementing").length;
    
    const completed = changes.filter(c => c.status === "Completed").length;
    const rolledBack = changes.filter(c => c.status === "Rolled Back").length;
    const closed = completed + rolledBack;
    const successRate = closed > 0 ? Math.round((completed / closed) * 100) : 100;

    document.getElementById("kpi-total").textContent = total;
    document.getElementById("kpi-pending").textContent = pending;
    document.getElementById("kpi-implementing").textContent = implementing;
    document.getElementById("kpi-success-rate").textContent = `${successRate}%`;

    // Render Active Changes List (Non-closed changes)
    const activeChanges = changes.filter(c => 
      c.status !== "Completed" && 
      c.status !== "Rolled Back" && 
      c.status !== "Rejected"
    );

    const activeListContainer = document.getElementById("dashboard-active-changes");
    activeListContainer.innerHTML = "";

    if (activeChanges.length === 0) {
      activeListContainer.innerHTML = `
        <div style="text-align: center; padding: 32px; color: var(--text-muted);">
          <i data-lucide="check-circle" style="width: 48px; height: 48px; color: var(--color-low); margin-bottom: 12px; display: inline-block;"></i>
          <p>${t("no_changes")}</p>
        </div>
      `;
    } else {
      activeChanges.forEach(change => {
        const escId = escapeHTML(change.id);
        const escTitle = escapeHTML(change.title);
        const escCategory = escapeHTML(change.category);
        const escOwner = escapeHTML(change.owner);
        const escRisk = escapeHTML(change.risk);
        const escStatus = escapeHTML(change.status);
        const escDate = escapeHTML(change.targetDate);
        const displayRisk = `${t("risk_" + escRisk.toLowerCase())}`;
        const displayStatus = getTranslatedStatus(change.status);
        const itemHtml = `
          <div class="change-item" data-id="${escId}">
            <span class="change-id">${escId}</span>
            <div class="change-main-info">
              <span class="change-title" title="${escTitle}">${escTitle}</span>
              <span class="change-meta">${escCategory} • ${t("owner")}: ${escOwner}</span>
            </div>
            <div>
              <span class="badge badge-risk ${escRisk.toLowerCase()}">${displayRisk}</span>
            </div>
            <div>
              <span class="badge badge-status ${escStatus.toLowerCase().replace(/ /g, '-')}">${displayStatus}</span>
            </div>
            <div style="text-align: right; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
              ${escDate}
            </div>
          </div>
        `;
        activeListContainer.insertAdjacentHTML("beforeend", itemHtml);
      });

      // Hook click events on items
      activeListContainer.querySelectorAll(".change-item").forEach(item => {
        item.addEventListener("click", () => {
          const id = item.getAttribute("data-id");
          openDetailModal(id);
        });
      });
    }
  }

  // Direct tab redirection from Dashboard Button
  document.getElementById("btn-view-all-changes").addEventListener("click", () => {
    switchTab("changes");
  });

  // --- RENDERER: CHANGES LIST (WITH FILTERS VIA API) ---
  async function renderChangesList() {
    const query = document.getElementById("search-query").value.trim();
    const statusFilter = document.getElementById("filter-status").value;
    const riskFilter = document.getElementById("filter-risk").value;

    let url = `api.php?action=get_changes&`;
    if (query) url += `search=${encodeURIComponent(query)}&`;
    if (statusFilter) url += `status=${encodeURIComponent(statusFilter)}&`;
    if (riskFilter) url += `risk=${encodeURIComponent(riskFilter)}&`;

    try {
      const data = await apiCall(url);
      const filteredChanges = data.changes;
        
        const listContainer = document.getElementById("all-changes-list");
        listContainer.innerHTML = "";

        if (filteredChanges.length === 0) {
          listContainer.innerHTML = `
            <div style="text-align: center; padding: 48px; color: var(--text-muted);">
              <i data-lucide="info" style="width: 48px; height: 48px; color: var(--text-sub); margin-bottom: 12px; display: inline-block;"></i>
              <p>${t("no_matching_changes")}</p>
            </div>
          `;
        } else {
          filteredChanges.forEach(change => {
            const escId = escapeHTML(change.id);
            const escTitle = escapeHTML(change.title);
            const escCategory = escapeHTML(change.category);
            const escOwner = escapeHTML(change.owner);
            const escRisk = escapeHTML(change.risk);
            const escStatus = escapeHTML(change.status);
            const escDate = escapeHTML(change.targetDate);
            const displayRisk = `${t("risk_" + escRisk.toLowerCase())}`;
            const displayStatus = getTranslatedStatus(change.status);
            const itemHtml = `
              <div class="change-item" data-id="${escId}">
                <span class="change-id">${escId}</span>
                <div class="change-main-info">
                  <span class="change-title" title="${escTitle}">${escTitle}</span>
                  <span class="change-meta">${escCategory} • ${t("owner")}: ${escOwner}</span>
                </div>
                <div>
                  <span class="badge badge-risk ${escRisk.toLowerCase()}">${displayRisk}</span>
                </div>
                <div>
                  <span class="badge badge-status ${escStatus.toLowerCase().replace(/ /g, '-')}">${displayStatus}</span>
                </div>
                <div style="text-align: right; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
                  ${escDate}
                </div>
              </div>
            `;
            listContainer.insertAdjacentHTML("beforeend", itemHtml);
          });

          // Click events
          listContainer.querySelectorAll(".change-item").forEach(item => {
            item.addEventListener("click", () => {
              const id = item.getAttribute("data-id");
              openDetailModal(id);
            });
          });
        }
    } catch (err) {
      console.error("Error loading filtered changes:", err);
    }
  }

  // Hook filters to trigger API-based re-rendering
  document.getElementById("search-query").addEventListener("input", renderChangesList);
  document.getElementById("filter-status").addEventListener("change", renderChangesList);
  document.getElementById("filter-risk").addEventListener("change", renderChangesList);

  // --- RENDERER: APPROVALS LIST ---
  function renderApprovalsList() {
    const pending = changes.filter(c => c.status === "Pending Approval");
    const container = document.getElementById("approvals-list");
    container.innerHTML = "";

    if (pending.length === 0) {
      container.innerHTML = `
        <div style="text-align: center; padding: 64px; color: var(--text-muted);">
          <i data-lucide="shield-check" style="width: 64px; height: 64px; color: var(--color-low); margin-bottom: 16px; display: inline-block;"></i>
          <h3>${t("no_pending_approvals")}</h3>
          <p style="margin-top: 8px;">${t("no_pending_approvals_desc")}</p>
        </div>
      `;
    } else {
      pending.forEach(change => {
        const escId = escapeHTML(change.id);
        const escTitle = escapeHTML(change.title);
        const escRequester = escapeHTML(change.requester);
        const escReqTitle = escapeHTML(change.requesterTitle);
        const escCategory = escapeHTML(change.category);
        const escRisk = escapeHTML(change.risk);
        const displayRisk = `${t("risk_" + escRisk.toLowerCase())}`;
        const displayStatus = getTranslatedStatus(change.status);
        const itemHtml = `
          <div class="change-item" data-id="${escId}">
            <span class="change-id">${escId}</span>
            <div class="change-main-info">
              <span class="change-title" title="${escTitle}">${escTitle}</span>
              <span class="change-meta">${t("requester")}: ${escRequester} (${escReqTitle}) • ${t("category_label").replace(' *', '')}: ${escCategory}</span>
            </div>
            <div>
              <span class="badge badge-risk ${escRisk.toLowerCase()}">${displayRisk}</span>
            </div>
            <div>
              <span class="badge badge-status pending-approval">${displayStatus}</span>
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
              <button class="btn btn-secondary btn-sm btn-quick-view" data-id="${escId}">${t("view_all")}</button>
            </div>
          </div>
        `;
        container.insertAdjacentHTML("beforeend", itemHtml);
      });

      // Quick view click
      container.querySelectorAll(".btn-quick-view").forEach(btn => {
        btn.addEventListener("click", (e) => {
          e.stopPropagation();
          const id = btn.getAttribute("data-id");
          openDetailModal(id);
        });
      });
      // Card click
      container.querySelectorAll(".change-item").forEach(card => {
        card.addEventListener("click", () => {
          const id = card.getAttribute("data-id");
          openDetailModal(id);
        });
      });
    }
  }

  // --- RENDERER: CALENDAR ---
  function renderCalendar() {
    const monthLabel = document.getElementById("calendar-month-label");
    const monthName = monthNames[currentMonth];
    monthLabel.textContent = `${monthName} ${currentYear}`;

    const calendarGrid = document.getElementById("calendar-grid-container");
    calendarGrid.innerHTML = "";

    // Day headers
    const daysOfWeek = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    daysOfWeek.forEach(day => {
      calendarGrid.insertAdjacentHTML("beforeend", `<div class="calendar-day-header">${day}</div>`);
    });

    // Compute calendar dates
    const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay(); // Sun = 0, Mon = 1
    const startDay = firstDayIndex === 0 ? 6 : firstDayIndex - 1;

    const totalDays = new Date(currentYear, currentMonth + 1, 0).getDate();
    const prevMonthTotalDays = new Date(currentYear, currentMonth, 0).getDate();

    // Render Previous Month's trailing days
    for (let i = startDay - 1; i >= 0; i--) {
      const dayNum = prevMonthTotalDays - i;
      calendarGrid.insertAdjacentHTML("beforeend", `
        <div class="calendar-day other-month">
          <span class="calendar-day-number">${dayNum}</span>
        </div>
      `);
    }

    // Render Current Month's days
    const today = new Date();
    for (let day = 1; day <= totalDays; day++) {
      const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      
      // Filter changes scheduled for this specific day
      const dayChanges = changes.filter(c => c.targetDate === dateStr);
      
      const isToday = today.getFullYear() === currentYear && today.getMonth() === currentMonth && today.getDate() === day;
      const todayClass = isToday ? "today" : "";

      let changePillsHtml = "";
      dayChanges.forEach(chg => {
        const escChgId = escapeHTML(chg.id);
        const escChgTitle = escapeHTML(chg.title);
        const escChgRisk = escapeHTML(chg.risk);
        changePillsHtml += `
          <div class="calendar-event ${escChgRisk.toLowerCase()}" data-id="${escChgId}" title="${escChgId}: ${escChgTitle}">
            ${escChgId}: ${escChgTitle}
          </div>
        `;
      });

      // Check if there is any conflict on this day (same category OR same assignedGroup)
      const ignoredStatuses = ["draft", "rejected", "rolled back"];
      const activeDayChanges = dayChanges.filter(c => !ignoredStatuses.includes(c.status.toLowerCase()));
      let hasDayConflict = activeDayChanges.some(c => checkConflicts(c).length > 0);

      const conflictIndicator = hasDayConflict ? `<span class="calendar-conflict-dot" title="Schedule Conflict Detected (Schedule or assignment overlap on this date)" style="width: 8px; height: 8px; background: var(--color-high); border-radius: 50%; display: inline-block; margin-left: 6px; box-shadow: 0 0 8px var(--color-high); animation: pulse 1.5s infinite;"></span>` : "";

      calendarGrid.insertAdjacentHTML("beforeend", `
        <div class="calendar-day ${todayClass}">
          <span class="calendar-day-number">${day}${conflictIndicator}</span>
          <div class="calendar-events">
            ${changePillsHtml}
          </div>
        </div>
      `);
    }

    // Hook click events on calendar pills
    calendarGrid.querySelectorAll(".calendar-event").forEach(pill => {
      pill.addEventListener("click", (e) => {
        e.stopPropagation();
        const id = pill.getAttribute("data-id");
        openDetailModal(id);
      });
    });

    // Render next month's leading days
    const currentGridCells = startDay + totalDays;
    const remainingCells = 42 - currentGridCells;
    if (remainingCells > 0) {
      for (let day = 1; day <= remainingCells; day++) {
        calendarGrid.insertAdjacentHTML("beforeend", `
          <div class="calendar-day other-month">
            <span class="calendar-day-number">${day}</span>
          </div>
        `);
      }
    }
  }

  // Calendar Controls
  document.getElementById("calendar-prev-month").addEventListener("click", () => {
    currentMonth--;
    if (currentMonth < 0) {
      currentMonth = 11;
      currentYear--;
    }
    renderCalendar();
    refreshIcons();
  });

  document.getElementById("calendar-next-month").addEventListener("click", () => {
    currentMonth++;
    if (currentMonth > 11) {
      currentMonth = 0;
      currentYear++;
    }
    renderCalendar();
    refreshIcons();
  });

  // --- CREATE NEW CHANGE REQUEST MODAL ---
  const modalCreate = document.getElementById("modal-create-change");
  const btnNewChange = document.getElementById("btn-new-change");
  const btnCreateCancel = document.getElementById("btn-create-cancel");
  const btnCreateClose = document.getElementById("modal-create-close");
  const formCreate = document.getElementById("form-create-change");

  // Open Modal
  btnNewChange.addEventListener("click", () => {
    // Populate Categories selector
    const categorySelect = document.getElementById("change-category");
    categorySelect.innerHTML = "";
    categories.forEach(cat => {
      categorySelect.insertAdjacentHTML("beforeend", `<option value="${cat}">${cat}</option>`);
    });

    // Reset Form
    formCreate.reset();
    
    // Reset Risk Calculator Panel
    const riskCalcPanel = document.getElementById("risk-calc-panel");
    if (riskCalcPanel) riskCalcPanel.style.display = "none";
    const calculatedRiskBadge = document.getElementById("calculated-risk-badge");
    if (calculatedRiskBadge) {
      calculatedRiskBadge.textContent = t("risk_low");
      calculatedRiskBadge.className = "badge badge-risk low";
    }
    const qProd = document.getElementById("risk-q-prod");
    if (qProd) qProd.checked = false;
    const qDowntime = document.getElementById("risk-q-downtime");
    if (qDowntime) qDowntime.checked = false;
    const qUntested = document.getElementById("risk-q-untested");
    if (qUntested) qUntested.checked = false;
    const qNoRollback = document.getElementById("risk-q-no-rollback");
    if (qNoRollback) qNoRollback.checked = false;
    
    // Set current user as default requester and owner
    if (currentUser) {
      document.getElementById("change-requester").value = currentUser.name;
      document.getElementById("change-requester-title").value = currentUser.title;
      document.getElementById("change-owner").value = currentUser.name;
      document.getElementById("change-owner-title").value = currentUser.title;
    }

    // Default today date
    const today = new Date();
    document.getElementById("change-target-date").value = today.toISOString().split('T')[0];

    // Reset editingChangeId state
    editingChangeId = null;
    const modalTitleEl = document.querySelector("#modal-create-change .modal-header h3");
    if (modalTitleEl) {
      modalTitleEl.textContent = t("create_title");
    }
    const submitBtnEl = document.querySelector("#form-create-change button[type='submit']");
    if (submitBtnEl) {
      submitBtnEl.textContent = t("save_draft");
    }

    populateGroupDropdowns();

    document.getElementById("create-conflict-warning-container").style.display = "none";
    modalCreate.classList.add("active");
    checkConflictsForForm();
    translatePage();
    refreshIcons();
  });

  // Close Modals
  function closeCreateModal() {
    modalCreate.classList.remove("active");
  }
  btnCreateCancel.addEventListener("click", closeCreateModal);
  btnCreateClose.addEventListener("click", closeCreateModal);

  // Form Submit (API POST)
  formCreate.addEventListener("submit", async (e) => {
    e.preventDefault();

    const title = document.getElementById("change-title").value.trim();
    const description = document.getElementById("change-description").value.trim();
    const requester = document.getElementById("change-requester").value.trim();
    const requesterTitle = document.getElementById("change-requester-title").value.trim() || "Requester";
    const owner = document.getElementById("change-owner").value.trim();
    const ownerTitle = document.getElementById("change-owner-title").value.trim() || "Owner";
    const category = document.getElementById("change-category").value;
    const risk = document.getElementById("change-risk").value;
    const targetDate = document.getElementById("change-target-date").value;
    const impact = document.getElementById("change-impact").value.trim();
    const rollbackPlan = document.getElementById("change-rollback").value.trim();
    const assignedGroup = document.getElementById("change-assigned-group")?.value || "";
    
    // Parse tasks text
    const tasksRaw = document.getElementById("change-tasks").value.split("\n");
    const tasks = tasksRaw
      .map(line => line.trim())
      .filter(line => line.length > 0)
      .map(line => ({ text: line, completed: false }));

    const url = editingChangeId ? `api.php?action=update_change&id=${editingChangeId}` : "api.php?action=create_change";

    try {
      const data = await apiCall(url, "POST", {
        title, description, requester, requesterTitle, owner, ownerTitle,
        category, risk, targetDate, impact, rollbackPlan, tasks, assignedGroup
      });

      closeCreateModal();
      await refreshData();
      if (editingChangeId) {
        await openDetailModal(editingChangeId);
      } else {
        switchTab("changes");
      }
    } catch (err) {
      alert(err.message || t("alert_error_submitting"));
    }
  });

  // --- RISK CALCULATOR BINDINGS ---
  const btnCalcRisk = document.getElementById("btn-calc-risk");
  const btnCloseRiskCalc = document.getElementById("btn-close-risk-calc");
  const riskCalcPanel = document.getElementById("risk-calc-panel");
  const calculatedRiskBadge = document.getElementById("calculated-risk-badge");
  const selectRisk = document.getElementById("change-risk");

  const qProd = document.getElementById("risk-q-prod");
  const qDowntime = document.getElementById("risk-q-downtime");
  const qUntested = document.getElementById("risk-q-untested");
  const qNoRollback = document.getElementById("risk-q-no-rollback");

  if (btnCalcRisk && riskCalcPanel) {
    btnCalcRisk.addEventListener("click", () => {
      const isVisible = riskCalcPanel.style.display === "block";
      riskCalcPanel.style.display = isVisible ? "none" : "block";
    });
  }

  if (btnCloseRiskCalc && riskCalcPanel) {
    btnCloseRiskCalc.addEventListener("click", () => {
      riskCalcPanel.style.display = "none";
    });
  }

  function recalculateRisk() {
    let score = 0;
    if (qProd && qProd.checked) score++;
    if (qDowntime && qDowntime.checked) score++;
    if (qUntested && qUntested.checked) score++;
    if (qNoRollback && qNoRollback.checked) score++;

    let level = "Low";
    let badgeClass = "badge-risk low";
    if (score === 1 || score === 2) {
      level = "Medium";
      badgeClass = "badge-risk medium";
    } else if (score >= 3) {
      level = "High";
      badgeClass = "badge-risk high";
    }

    if (calculatedRiskBadge) {
      calculatedRiskBadge.textContent = `${t("risk_" + level.toLowerCase())}`;
      calculatedRiskBadge.className = `badge ${badgeClass}`;
      calculatedRiskBadge.style.fontWeight = "700";
    }

    if (selectRisk) {
      selectRisk.value = level;
    }
  }

  [qProd, qDowntime, qUntested, qNoRollback].forEach(q => {
    if (q) {
      q.addEventListener("change", recalculateRisk);
    }
  });

  // --- CONFLICT DETECTION HELPER ---
  function checkConflicts(change) {
    if (!change || !change.targetDate) return [];
    
    // Ignore draft, rejected, or rolled back changes from conflicts
    const ignoredStatuses = ["draft", "rejected", "rolled back"];
    if (ignoredStatuses.includes(change.status.toLowerCase())) {
      return [];
    }

    return changes.filter(other => {
      if (other.id === change.id) return false;
      if (other.targetDate !== change.targetDate) return false;
      if (ignoredStatuses.includes(other.status.toLowerCase())) return false;
      
      const categoryMatch = change.category && other.category && change.category === other.category;
      const groupMatch = change.assignedGroup && other.assignedGroup && change.assignedGroup === other.assignedGroup;
      
      return categoryMatch || groupMatch;
    });
  }

  // --- REPORTING & ANALYTICS TAB ---
  let chartInstances = {};

  async function renderAnalyticsTab() {
    if (typeof Chart === 'undefined') {
      console.warn("Chart.js library is not loaded.");
      // Still load and render KPIs even if Chart.js is not loaded
      try {
        const data = await apiCall("get_analytics");
        if (data) {
          document.getElementById("kpi-total-changes").textContent = data.kpis.total;
          document.getElementById("kpi-completed-changes").textContent = data.kpis.completed;
          document.getElementById("kpi-pending-approvals").textContent = data.kpis.pendingApprovals;
          const successRate = data.kpis.successRate;
          document.getElementById("kpi-success-rate").textContent = `${successRate}%`;
          document.getElementById("kpi-success-rate-fill").style.width = `${successRate}%`;
        }
      } catch (err) {
        console.error("Failed to load analytics KPIs:", err);
      }
      return;
    }

    try {
      const data = await apiCall("get_analytics");
      if (!data) return;
      
      document.getElementById("kpi-total-changes").textContent = data.kpis.total;
      document.getElementById("kpi-completed-changes").textContent = data.kpis.completed;
      document.getElementById("kpi-pending-approvals").textContent = data.kpis.pendingApprovals;
      
      const successRate = data.kpis.successRate;
      document.getElementById("kpi-success-rate").textContent = `${successRate}%`;
      document.getElementById("kpi-success-rate-fill").style.width = `${successRate}%`;
      
      // Destroy old chart instances to avoid overlap/titling bugs on hover
      Object.keys(chartInstances).forEach(key => {
        if (chartInstances[key]) {
          chartInstances[key].destroy();
        }
      });
      
      const getChartData = (dist, labelKey = 'status') => {
        return {
          labels: dist.map(d => d[labelKey] || 'N/A'),
          data: dist.map(d => parseInt(d.count))
        };
      };
      
      const statusData = getChartData(data.statusDistribution, 'status');
      const riskData = getChartData(data.riskDistribution, 'risk');
      const catData = getChartData(data.categoryDistribution, 'category');
      const deptData = getChartData(data.departmentDistribution, 'department');
      
      const chartColors = [
        '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', 
        '#6c757d', '#a78bfa', '#ec4899', '#f97316'
      ];
      
      // 1. Status Chart
      const ctxStatus = document.getElementById("chart-status").getContext("2d");
      chartInstances["chart-status"] = new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
          labels: statusData.labels,
          datasets: [{
            data: statusData.data,
            backgroundColor: chartColors.slice(0, statusData.labels.length)
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'right', labels: { color: getComputedStyle(document.body).getPropertyValue('--text-main').trim() || '#212529' } }
          }
        }
      });
      
      // 2. Risk Chart
      const ctxRisk = document.getElementById("chart-risk").getContext("2d");
      chartInstances["chart-risk"] = new Chart(ctxRisk, {
        type: 'bar',
        data: {
          labels: riskData.labels,
          datasets: [{
            data: riskData.data,
            backgroundColor: '#007bff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0, color: getComputedStyle(document.body).getPropertyValue('--text-sub').trim() || '#495057' } },
            x: { ticks: { color: getComputedStyle(document.body).getPropertyValue('--text-sub').trim() || '#495057' } }
          }
        }
      });
      
      // 3. Category Chart
      const ctxCat = document.getElementById("chart-category").getContext("2d");
      chartInstances["chart-category"] = new Chart(ctxCat, {
        type: 'bar',
        data: {
          labels: catData.labels,
          datasets: [{
            data: catData.data,
            backgroundColor: '#28a745'
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { beginAtZero: true, ticks: { precision: 0, color: getComputedStyle(document.body).getPropertyValue('--text-sub').trim() || '#495057' } },
            y: { ticks: { color: getComputedStyle(document.body).getPropertyValue('--text-sub').trim() || '#495057' } }
          }
        }
      });
      
      // 4. Department Chart
      const ctxDept = document.getElementById("chart-department").getContext("2d");
      chartInstances["chart-department"] = new Chart(ctxDept, {
        type: 'pie',
        data: {
          labels: deptData.labels,
          datasets: [{
            data: deptData.data,
            backgroundColor: chartColors.slice(4, 4 + deptData.labels.length)
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'right', labels: { color: getComputedStyle(document.body).getPropertyValue('--text-main').trim() || '#212529' } }
          }
        }
      });
      
    } catch (err) {
      console.error("Failed rendering analytics tab:", err);
    }
  }

  // --- SECURITY & AUDIT LOGS TAB ---
  async function renderAuditLogsTab() {
    try {
      const data = await apiCall("activities");
      if (!data) return;
      
      const tbody = document.getElementById("audit-logs-tbody");
      tbody.innerHTML = "";
      
      if (!data.activities || data.activities.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 20px; color: var(--text-sub);">No audit logs found.</td></tr>`;
        return;
      }
      
      data.activities.forEach(act => {
        const tr = document.createElement("tr");
        
        const tdDate = document.createElement("td");
        tdDate.style.padding = "12px 16px";
        tdDate.style.color = "var(--text-sub)";
        tdDate.textContent = act.date;
        tr.appendChild(tdDate);
        
        const tdUser = document.createElement("td");
        tdUser.style.padding = "12px 16px";
        tdUser.style.fontWeight = "600";
        tdUser.textContent = act.user;
        tr.appendChild(tdUser);
        
        const tdAction = document.createElement("td");
        tdAction.style.padding = "12px 16px";
        tdAction.textContent = act.action;
        tr.appendChild(tdAction);
        
        const tdTarget = document.createElement("td");
        tdTarget.style.padding = "12px 16px";
        
        if (act.target && act.target.startsWith("CHG-")) {
          const a = document.createElement("a");
          a.href = "#";
          a.style.color = "var(--accent-color)";
          a.style.fontWeight = "600";
          a.style.textDecoration = "none";
          a.textContent = act.target;
          a.addEventListener("click", (e) => {
            e.preventDefault();
            openDetailModal(act.target);
          });
          tdTarget.appendChild(a);
        } else {
          tdTarget.textContent = act.target || "-";
          tdTarget.style.color = "var(--text-sub)";
        }
        tr.appendChild(tdTarget);
        
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error("Failed rendering audit logs tab:", err);
    }
  }

  // --- FORM REALTIME CONFLICT CHECKER ---
  async function checkConflictsForForm() {
    const targetDateEl = document.getElementById("change-target-date");
    const categoryEl = document.getElementById("change-category");
    const assignedGroupEl = document.getElementById("change-assigned-group");
    const warningContainer = document.getElementById("create-conflict-warning-container");
    
    if (!targetDateEl || !warningContainer) return;
    
    const date = targetDateEl.value;
    const category = categoryEl ? categoryEl.value : "";
    const assignedGroup = assignedGroupEl ? assignedGroupEl.value : "";
    
    if (!date) {
      warningContainer.style.display = "none";
      warningContainer.innerHTML = "";
      return;
    }
    
    try {
      const data = await apiCall("check_conflicts", "POST", {
        date: date,
        category: category,
        assignedGroup: assignedGroup,
        excludeId: editingChangeId || ""
      }, { "Content-Type": "application/json" });
      
      if (data && data.conflicts && data.conflicts.length > 0) {
        warningContainer.style.display = "block";
        
        let conflictListHtml = "";
        data.conflicts.forEach(c => {
          conflictListHtml += `
            <li>
              <strong>${c.id}</strong>: "${escapeHTML(c.title)}" (Requester: ${escapeHTML(c.requester)}) 
              <span class="badge badge-risk ${c.risk.toLowerCase()}">${c.risk}</span>
            </li>
          `;
        });
        
        warningContainer.innerHTML = `
          <div class="conflict-alert-box">
            <div class="conflict-alert-title">
              <i data-lucide="shield-alert" style="width: 18px; height: 18px;"></i>
              <span>Conflict Warning: Scheduling Conflict Detected</span>
            </div>
            <p style="font-size: 0.85rem; margin-top: 4px; line-height: 1.4;">
              There are other active changes scheduled for this date matching the selected category or group:
            </p>
            <ul class="conflict-alert-list" style="margin-top: 6px;">
              ${conflictListHtml}
            </ul>
          </div>
        `;
        refreshIcons();
      } else {
        warningContainer.style.display = "none";
        warningContainer.innerHTML = "";
      }
    } catch (err) {
      console.error("Failed to check conflicts:", err);
    }
  }

  // --- ATTACHMENT RENDERING HELPER ---
  function renderAttachments(change) {
    const attachmentContainer = document.getElementById("detail-attachment-container");
    const uploadWrapper = document.getElementById("attachment-upload-wrapper");
    if (!attachmentContainer) return;
    
    attachmentContainer.innerHTML = "";
    
    if (change.attachment_path) {
      const escName = escapeHTML(change.attachment_name);
      const escPath = escapeHTML(change.attachment_path);
      
      const isOwnerOrAdmin = currentUser && (currentUser.role === 'Administrator' || change.ownerUsername === currentUser.username || change.requesterUsername === currentUser.username);
      
      attachmentContainer.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); width: 100%;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="file-text" style="width: 16px; height: 16px; color: #a78bfa; flex-shrink: 0;"></i>
            <a href="${escPath}" target="_blank" style="color: var(--text-main); font-weight: 500; text-decoration: none; word-break: break-all; font-size: 0.85rem;">${escName}</a>
          </div>
          <div style="display: flex; gap: 8px; flex-shrink: 0;">
            <a href="${escPath}" download="${escName}" class="btn btn-secondary btn-sm" style="padding: 4px 8px; min-height: auto; cursor: pointer; display: flex; align-items: center; justify-content: center;" title="Download">
              <i data-lucide="download" style="width: 12px; height: 12px;"></i>
            </a>
            ${isOwnerOrAdmin ? `
              <button class="btn btn-danger btn-sm" id="btn-delete-attachment" style="padding: 4px 8px; min-height: auto; cursor: pointer; display: flex; align-items: center; justify-content: center; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #ef4444;" title="Delete">
                <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
              </button>
            ` : ""}
          </div>
        </div>
      `;
      
      const btnDeleteAttach = document.getElementById("btn-delete-attachment");
      if (btnDeleteAttach) {
        btnDeleteAttach.addEventListener("click", async () => {
          if (confirm(t("confirm_delete_attachment"))) {
            try {
              const data = await apiCall(`delete_attachment&id=${change.id}`, "POST");
              await refreshData();
              openDetailModal(change.id);
            } catch (e) {
              alert(e.message || t("alert_error_communicating"));
            }
          }
        });
      }
    } else {
      attachmentContainer.innerHTML = `<p style="color: var(--text-sub); font-size: 0.8rem; font-style: italic; margin: 0;">${t("no_attachments")}</p>`;
    }

    // Show/hide upload section based on roles
    const isAllowedToUpload = currentUser && (currentUser.role === 'Administrator' || change.ownerUsername === currentUser.username || change.requesterUsername === currentUser.username);
    if (uploadWrapper) {
      if (isAllowedToUpload && !change.attachment_path) {
        uploadWrapper.style.display = "block";
      } else {
        uploadWrapper.style.display = "none";
      }
    }
    refreshIcons();
  }

  // --- DETAIL MODAL & WORKFLOW MANAGEMENT ---
  const modalDetail = document.getElementById("modal-detail");
  const btnDetailClose = document.getElementById("modal-detail-close");

  function closeDetailModal() {
    modalDetail.classList.remove("active");
    activeChangeId = null;
  }
  btnDetailClose.addEventListener("click", closeDetailModal);

  async function openDetailModal(changeId) {
    activeChangeId = changeId;
    
    try {
      const data = await apiCall(`get_change_detail&id=${changeId}`);
      const change = data.change;

      // Check Conflicts
      const conflicts = checkConflicts(change);
      const conflictBanner = document.getElementById("detail-conflict-banner");
      const conflictMessage = document.getElementById("detail-conflict-message");
      if (conflicts.length > 0) {
        if (conflictBanner) {
          conflictBanner.style.display = "flex";
          conflictBanner.style.flexDirection = "column";
          conflictBanner.style.alignItems = "flex-start";
        }
        if (conflictMessage) {
          let conflictListHtml = `<div><strong>${t("overlap_alert_label") || "Outage Overlap Warning:"}</strong> ${conflicts.length} ${t("overlaps_found") || "overlaps found"} on ${change.targetDate}:</div>`;
          conflictListHtml += `<ul style="margin: 6px 0 0 16px; padding: 0; list-style: disc; display: flex; flex-direction: column; gap: 4px;">`;
          conflicts.forEach(c => {
            conflictListHtml += `<li><a href="#" class="conflict-link" data-id="${escapeHTML(c.id)}" style="color: inherit; text-decoration: underline; font-weight: 600;">${escapeHTML(c.id)}: ${escapeHTML(c.title)}</a> (${escapeHTML(c.owner)})</li>`;
          });
          conflictListHtml += `</ul>`;
          conflictMessage.innerHTML = conflictListHtml;
          
          // Add click listener for conflict links
          conflictMessage.querySelectorAll(".conflict-link").forEach(lnk => {
            lnk.addEventListener("click", (ev) => {
              ev.preventDefault();
              const cid = lnk.getAttribute("data-id");
              openDetailModal(cid);
            });
          });
        }
      } else {
        if (conflictBanner) conflictBanner.style.display = "none";
      }

      // Set static text content
      document.getElementById("detail-id").textContent = change.id;
      document.getElementById("detail-title").textContent = change.title;
      document.getElementById("detail-description").textContent = change.description;
      document.getElementById("detail-impact").textContent = change.impact;
      document.getElementById("detail-rollback").textContent = change.rollbackPlan;
      document.getElementById("detail-requester").textContent = `${change.requester} (${change.requesterTitle})`;
      document.getElementById("detail-owner").textContent = `${change.owner} (${change.ownerTitle})`;
      document.getElementById("detail-date").textContent = change.targetDate;

      // Display Assigned Group
      const assignedGroupEl = document.getElementById("detail-assigned-group");
      if (assignedGroupEl) {
        assignedGroupEl.textContent = change.assignedGroup || "-- None --";
      }

      // Badges Styling
      const statusEl = document.getElementById("detail-status");
      statusEl.className = `badge badge-status ${change.status.toLowerCase().replace(/ /g, '-')}`;
      statusEl.textContent = getTranslatedStatus(change.status);

      const riskEl = document.getElementById("detail-risk");
      riskEl.className = `badge badge-risk ${change.risk.toLowerCase()}`;
      riskEl.textContent = `${t("risk_" + change.risk.toLowerCase())}`;

      const catEl = document.getElementById("detail-category");
      catEl.textContent = change.category;

      // Render Checklist
      renderChecklist(change);

      // Render Approvals Log
      renderApprovalsLog(change);

      // Render Comments Section
      renderComments(change);

      // Render Attachments Section
      renderAttachments(change);

      // Render Workflow Actions Panel
      renderActionsPanel(change);

      // Render Revision History
      renderRevisionHistory(change);

      // Show modal
      modalDetail.classList.add("active");
      translatePage();
      refreshIcons();
    } catch (err) {
      console.error("Error loading change details:", err);
    }
  }

  function renderRevisionHistory(change) {
    const section = document.getElementById("detail-revisions-section");
    const timeline = document.getElementById("detail-revisions-timeline");
    if (!section || !timeline) return;

    if (!change.revisions || change.revisions.length === 0) {
      section.style.display = "none";
      timeline.innerHTML = "";
      return;
    }

    section.style.display = "block";
    timeline.innerHTML = "";

    change.revisions.forEach(rev => {
      const escEditor = escapeHTML(rev.editor);
      const escDate = escapeHTML(rev.date);
      const changesList = rev.changes.map(ch => `<span class="badge" style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2); color: #c084fc; font-size: 0.75rem; padding: 2px 8px;">${escapeHTML(ch)}</span>`).join(" ");

      const itemHtml = `
        <div class="revision-item" style="display: flex; gap: 12px; padding: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); position: relative;">
          <div style="display: flex; flex-direction: column; align-items: center; position: relative;">
            <div style="width: 24px; height: 24px; border-radius: 50%; background: #a78bfa; display: flex; align-items: center; justify-content: center; color: white;">
              <i data-lucide="history" style="width: 12px; height: 12px;"></i>
            </div>
          </div>
          <div style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--text-sub);">
              <strong>${escEditor}</strong>
              <span>${escDate}</span>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
              <span>Updated fields:</span>
              ${changesList}
            </div>
          </div>
        </div>
      `;
      timeline.insertAdjacentHTML("beforeend", itemHtml);
    });
    
    refreshIcons();
  }

  let webhookSettingsLoaded = false;
  async function loadWebhookSettings() {
    try {
      const data = await apiCall("get_notification_settings");
      const settings = data.notification_settings;
      document.getElementById("webhook-url").value = settings.webhookUrl || "";
      document.getElementById("webhook-notify-create").checked = !!settings.notifyOnCreate;
      document.getElementById("webhook-notify-status").checked = !!settings.notifyOnStatusChange;
      document.getElementById("webhook-notify-highrisk").checked = !!settings.notifyOnHighRiskOnly;
    } catch (err) {
      console.error("Error loading webhook settings:", err);
    }
  }

  let adDomainsList = [];

  function renderAdDomainsList() {
    const listEl = document.getElementById("ad-domains-list");
    if (!listEl) return;
    listEl.innerHTML = "";
    adDomainsList.forEach((domain, idx) => {
      const tag = document.createElement("div");
      tag.style.cssText = "display: inline-flex; align-items: center; gap: 6px; background: rgba(167, 139, 250, 0.15); border: 1px solid rgba(167, 139, 250, 0.3); color: #c084fc; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;";
      
      const text = document.createElement("span");
      text.textContent = domain;
      tag.appendChild(text);
      
      const removeBtn = document.createElement("span");
      removeBtn.innerHTML = "&times;";
      removeBtn.style.cssText = "cursor: pointer; font-size: 1rem; line-height: 1; font-weight: 700; color: #ef4444; margin-left: 2px;";
      removeBtn.addEventListener("click", () => {
        adDomainsList.splice(idx, 1);
        renderAdDomainsList();
      });
      tag.appendChild(removeBtn);
      
      listEl.appendChild(tag);
    });
  }

  async function loadAdSettings() {
    try {
      const data = await apiCall("get_ad_settings");
      const settings = data.ad_settings;
      if (settings) {
        document.getElementById("ad-enabled").checked = !!settings.adEnabled;
        document.getElementById("ad-server").value = settings.adServer || "";
        document.getElementById("ad-port").value = settings.adPort || 389;
        document.getElementById("ad-basedn").value = settings.adBaseDn || "";
        
        adDomainsList = settings.adDomain ? settings.adDomain.split(',').map(d => d.trim()).filter(d => d.length > 0) : [];
        renderAdDomainsList();
      }
    } catch (err) {
      console.error("Error loading Active Directory Active Directory settings:", err);
    }
  }

  // RENDER CHECKLIST & PROGRESS
  function renderChecklist(change) {
    const listContainer = document.getElementById("detail-tasks-checklist");
    listContainer.innerHTML = "";

    const totalTasks = change.tasks.length;
    const completedTasks = change.tasks.filter(t => t.completed).length;
    const percentage = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;

    // Update progress elements
    document.getElementById("detail-progress-label").textContent = `${percentage}%`;
    document.getElementById("detail-progress-bar").style.width = `${percentage}%`;

    // Disable checklist checks if the user is not the owner/requester or an admin
    const canEditChecklist = currentUser && 
      (currentUser.role === "Administrator" || 
       change.ownerUsername === currentUser.username || 
       change.requesterUsername === currentUser.username);

    change.tasks.forEach(task => {
      const completedClass = task.completed ? "completed" : "";
      const escTaskId = escapeHTML(String(task.id));
      const escTaskText = escapeHTML(task.text);
      const taskHtml = `
        <div class="checklist-item ${completedClass}" data-task-id="${escTaskId}" style="${!canEditChecklist ? 'cursor: default; opacity: 0.85;' : ''}">
          <div class="checklist-checkbox">
            <i data-lucide="check"></i>
          </div>
          <span class="checklist-text">${escTaskText}</span>
        </div>
      `;
      listContainer.insertAdjacentHTML("beforeend", taskHtml);
    });

    if (canEditChecklist) {
      // Add checkbox toggle listener
      listContainer.querySelectorAll(".checklist-item").forEach(item => {
        item.addEventListener("click", async () => {
          const taskId = parseInt(item.getAttribute("data-task-id"), 10);
          const taskObj = change.tasks.find(t => t.id === taskId);
          if (taskObj) {
            const nextCompletedState = !taskObj.completed;
            
            try {
              const data = await apiCall(`toggle_task&id=${change.id}&task_id=${taskId}`, "PUT", { completed: nextCompletedState });
              
              // Update local list index
              const idx = changes.findIndex(c => c.id === change.id);
              if (idx !== -1) changes[idx] = data.change;
              
              renderChecklist(data.change);
              refreshIcons();

              // Trigger background list updates
              if (activeTab === "dashboard") renderDashboard();
              if (activeTab === "changes") renderChangesList();
            } catch (err) {
              console.error("Failed to toggle checklist task:", err);
            }
          }
        });
      });
    }
  }

  // RENDER APPROVAL LOG IN SIDEBAR
  function renderApprovalsLog(change) {
    const container = document.getElementById("detail-approval-list");
    container.innerHTML = "";

    change.approvals.forEach(app => {
      let iconColor = "#94a3b8"; // Gray pending
      let iconName = "clock";
      if (app.status === "Approved") {
        iconColor = "var(--status-completed)";
        iconName = "check-circle";
      } else if (app.status === "Rejected") {
        iconColor = "var(--status-rejected)";
        iconName = "x-circle";
      }

      const escAppRole = escapeHTML(app.role);
      const displayStatus = getTranslatedStatus(app.status);
      const appHtml = `
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm);">
          <span style="font-weight: 500; font-size: 0.85rem;">${escAppRole}</span>
          <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: ${iconColor}; font-weight: 600;">
            <i data-lucide="${iconName}" style="width: 14px; height: 14px;"></i>
            <span>${escapeHTML(displayStatus)}</span>
          </div>
        </div>
      `;
      container.insertAdjacentHTML("beforeend", appHtml);
    });
  }

  // RENDER ACTIONS PANEL FOR STAGE TRANSITIONS
  function renderActionsPanel(change) {
    const container = document.getElementById("detail-actions-panel");
    container.innerHTML = "";

    const isOwnerOrRequester = currentUser && (change.ownerUsername === currentUser.username || change.requesterUsername === currentUser.username);
    const isAdmin = currentUser && currentUser.role === "Administrator";
    const isCabApprover = currentUser && currentUser.role === "CAB Approver";

    const canManageLifecycle = isAdmin || isOwnerOrRequester;
    const canApproveReject = isAdmin || isCabApprover;

    let buttonsHtml = "";

    switch (change.status) {
      case "Draft":
        if (canManageLifecycle) {
          buttonsHtml = `
            <button class="btn btn-primary w-full" id="btn-wf-review">
              <i data-lucide="send"></i> Submit for Review
            </button>
            <button class="btn btn-secondary w-full" id="btn-wf-edit">
              <i data-lucide="edit"></i> Edit Request
            </button>
            <button class="btn btn-danger w-full" id="btn-wf-delete">
              <i data-lucide="trash-2"></i> Delete Request
            </button>
          `;
        } else {
          buttonsHtml = `
            <div style="padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
              ${t("msg_owner_admin_draft")}
            </div>
          `;
        }
        break;
      case "Under Review":
        if (canManageLifecycle) {
          buttonsHtml = `
            <button class="btn btn-primary w-full" id="btn-wf-request-approval">
              <i data-lucide="check-square"></i> Submit for Approval
            </button>
            <button class="btn btn-secondary w-full" id="btn-wf-edit">
              <i data-lucide="edit"></i> Edit Request
            </button>
            <button class="btn btn-secondary w-full" id="btn-wf-draft">
              <i data-lucide="rotate-ccw"></i> Pull to Draft
            </button>
          `;
        } else {
          buttonsHtml = `
            <div style="padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
              ${t("msg_owner_admin_manage")}
            </div>
          `;
        }
        break;
      case "Pending Approval":
        if (canApproveReject) {
          buttonsHtml = `
            <button class="btn btn-primary w-full" id="btn-wf-approve" style="background: linear-gradient(135deg, #10b981, #059669); border-color: #059669; box-shadow: 0 4px 15px rgba(16,185,129,0.35);">
              <i data-lucide="check"></i> Approve Change
            </button>
            <button class="btn btn-danger w-full" id="btn-wf-reject">
              <i data-lucide="x"></i> Reject Request
            </button>
          `;
        } else {
          buttonsHtml = `
            <div style="padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
              ${t("msg_cab_admin_approve")}
            </div>
          `;
        }
        break;
      case "Approved":
        if (canManageLifecycle) {
          buttonsHtml = `
            <button class="btn btn-primary w-full" id="btn-wf-implement">
              <i data-lucide="play"></i> Start Maintenance (Prod)
            </button>
          `;
        } else {
          buttonsHtml = `
            <div style="padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
              ${t("msg_owner_admin_manage")}
            </div>
          `;
        }
        break;
      case "Implementing":
        if (canManageLifecycle) {
          buttonsHtml = `
            <button class="btn btn-primary w-full" id="btn-wf-complete" style="background: linear-gradient(135deg, #10b981, #059669); border-color: #059669; box-shadow: 0 4px 15px rgba(16,185,129,0.35);">
              <i data-lucide="check-circle-2"></i> Completed Successfully
            </button>
            <button class="btn btn-danger w-full" id="btn-wf-rollback">
              <i data-lucide="shield-alert"></i> Failed / Rollback
            </button>
          `;
        } else {
          buttonsHtml = `
            <div style="padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
              ${t("msg_owner_admin_manage")}
            </div>
          `;
        }
        break;
      case "Completed":
      case "Rejected":
      case "Rolled Back":
        buttonsHtml = `
          <div style="padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
            ${t("msg_request_closed")}
          </div>
        `;
        if (canManageLifecycle) {
          buttonsHtml += `
            <button class="btn btn-secondary w-full" id="btn-wf-reset" style="margin-top: 8px;">
              <i data-lucide="refresh-cw"></i> Reset to Draft for Testing
            </button>
          `;
        }
        break;
    }

    container.innerHTML = buttonsHtml;

    // Attach workflow actions event listeners
    const addWfListener = (btnId, newStatus, actionText) => {
      const btn = document.getElementById(btnId);
      if (btn) {
        btn.addEventListener("click", async () => {
          try {
            const data = await apiCall(`update_status&id=${change.id}`, "PUT", { status: newStatus, actionText: actionText });
            
            // Update local array
            const idx = changes.findIndex(c => c.id === change.id);
            if (idx !== -1) changes[idx] = data.change;
            
            await openDetailModal(change.id); // Re-render detail view
            
            // Update background lists
            if (activeTab === "dashboard") renderDashboard();
            if (activeTab === "changes") renderChangesList();
            if (activeTab === "approvals") renderApprovalsList();
            if (activeTab === "calendar") renderCalendar();
          } catch (e) {
            alert(`Workflow Action Failed: ${e.message}`);
          }
        });
      }
    };

    addWfListener("btn-wf-review", "Under Review", "submitted the change request for review.");
    addWfListener("btn-wf-draft", "Draft", "pulled the change request back to draft.");
    addWfListener("btn-wf-request-approval", "Pending Approval", "submitted the change request for approval.");
    addWfListener("btn-wf-approve", "Approved", "approved the change request.");
    addWfListener("btn-wf-reject", "Rejected", "rejected the change request.");
    addWfListener("btn-wf-implement", "Implementing", "started the change implementation in production.");
    addWfListener("btn-wf-complete", "Completed", "marked the change request as completed successfully.");
    addWfListener("btn-wf-rollback", "Rolled Back", "reported the change as failed and rolled back systems.");
    addWfListener("btn-wf-reset", "Draft", "reset the change request to draft for testing.");

    // Edit handler
    const btnEdit = document.getElementById("btn-wf-edit");
    if (btnEdit) {
      btnEdit.addEventListener("click", () => {
        editingChangeId = change.id;
        
        // Populate Categories
        const categorySelect = document.getElementById("change-category");
        categorySelect.innerHTML = "";
        categories.forEach(cat => {
          categorySelect.insertAdjacentHTML("beforeend", `<option value="${cat}">${cat}</option>`);
        });

        populateGroupDropdowns();

        // Populate values
        document.getElementById("change-title").value = change.title;
        document.getElementById("change-description").value = change.description;
        document.getElementById("change-requester").value = change.requester;
        document.getElementById("change-requester-title").value = change.requesterTitle;
        document.getElementById("change-owner").value = change.owner;
        document.getElementById("change-owner-title").value = change.ownerTitle;
        document.getElementById("change-category").value = change.category;
        document.getElementById("change-risk").value = change.risk;
        document.getElementById("change-target-date").value = change.targetDate;
        document.getElementById("change-impact").value = change.impact;
        document.getElementById("change-rollback").value = change.rollbackPlan;
        
        const groupSelect = document.getElementById("change-assigned-group");
        if (groupSelect) {
          groupSelect.value = change.assignedGroup || "";
        }

        const tasksText = change.tasks.map(t => t.text).join("\n");
        document.getElementById("change-tasks").value = tasksText;

        // Change modal labels
        const modalTitleEl = document.querySelector("#modal-create-change .modal-header h3");
        if (modalTitleEl) {
          modalTitleEl.textContent = t("edit_change_request") || "Edit Change Request";
        }
        const submitBtnEl = document.querySelector("#form-create-change button[type='submit']");
        if (submitBtnEl) {
          submitBtnEl.textContent = t("save_request_changes") || "Save Request Changes";
        }

        // Close details modal and open edit modal
        closeDetailModal();
        modalCreate.classList.add("active");
        checkConflictsForForm();
        translatePage();
        refreshIcons();
      });
    }

    // Delete handler
    const btnDelete = document.getElementById("btn-wf-delete");
    if (btnDelete) {
      btnDelete.addEventListener("click", async () => {
        if (confirm(t("confirm_delete_change"))) {
          try {
            const data = await apiCall(`delete_change&id=${change.id}`, "DELETE");
            closeDetailModal();
            await refreshData();
            if (activeTab === "dashboard") renderDashboard();
            if (activeTab === "changes") renderChangesList();
          } catch (e) {
            alert(`Deletion failed: ${e.message}`);
          }
        }
      });
    }
  }

  // --- COMMENTS MANAGEMENT ---
  function renderComments(change) {
    const list = document.getElementById("detail-comments-list");
    list.innerHTML = "";

    if (!change.comments || change.comments.length === 0) {
      list.innerHTML = `<p style="font-size: 0.8rem; color: var(--text-sub); text-align: center; padding: 12px;">${t("no_comments")}</p>`;
      return;
    }

    change.comments.forEach(com => {
      const escComAuthor = escapeHTML(com.author);
      const escComDate = escapeHTML(com.date);
      const escComText = escapeHTML(com.text);
      const cardHtml = `
        <div class="comment-card">
          <div class="comment-header">
            <span class="comment-author">${escComAuthor}</span>
            <span>${escComDate}</span>
          </div>
          <p class="comment-text">${escComText}</p>
        </div>
      `;
      list.insertAdjacentHTML("beforeend", cardHtml);
    });
    // Scroll comments list to bottom
    list.scrollTop = list.scrollHeight;
  }

  // Add Comment Event
  const btnAddComment = document.getElementById("btn-add-comment");
  const commentInput = document.getElementById("comment-input");

  async function submitComment() {
    const text = commentInput.value.trim();
    if (text === "" || !activeChangeId) return;

    try {
      const data = await apiCall(`add_comment&id=${activeChangeId}`, "POST", { text });
      commentInput.value = "";
      
      // Refresh detail view
      await openDetailModal(activeChangeId);
      
      // Update background lists
      await refreshData();
      if (activeTab === "dashboard") renderDashboard();
    } catch (err) {
      alert(`Failed to add comment: ${err.message}`);
    }
  }

  btnAddComment.addEventListener("click", submitComment);
  commentInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      submitComment();
    }
  });

  // --- RENDERER: PROFILE & USER DIRECTORY ---
  const formProfile = document.getElementById("form-profile");
  const avatarContainer = document.getElementById("avatar-container");
  const avatarInput = document.getElementById("profile-avatar-input");
  const avatarImg = document.getElementById("profile-avatar-img");
  const avatarPlaceholder = document.getElementById("profile-avatar-placeholder");
  const avatarHoverOverlay = document.getElementById("avatar-hover-overlay");
  const btnViewAvatar = document.getElementById("btn-view-avatar");
  const btnDeleteAvatar = document.getElementById("btn-delete-avatar");
  const avatarActionsContainer = document.getElementById("avatar-actions-container");
  const modalImageViewer = document.getElementById("modal-image-viewer");
  const btnImageViewerClose = document.getElementById("modal-image-viewer-close");
  const viewerModalImg = document.getElementById("viewer-modal-img");
  
  // Set up avatar triggers once
  if (avatarContainer) {
    avatarContainer.addEventListener("click", () => {
      if (avatarInput) avatarInput.click();
    });
    
    avatarContainer.addEventListener("mouseenter", () => {
      if (avatarHoverOverlay) avatarHoverOverlay.style.opacity = "1";
    });
    avatarContainer.addEventListener("mouseleave", () => {
      if (avatarHoverOverlay) avatarHoverOverlay.style.opacity = "0";
    });
  }

  // View avatar in full screen modal
  if (btnViewAvatar) {
    btnViewAvatar.addEventListener("click", (e) => {
      e.stopPropagation();
      if (base64AvatarData) {
        viewerModalImg.src = base64AvatarData;
        modalImageViewer.classList.add("active");
      }
    });
  }

  // Close viewer modal
  if (btnImageViewerClose) {
    btnImageViewerClose.addEventListener("click", () => {
      modalImageViewer.classList.remove("active");
    });
  }
  if (modalImageViewer) {
    modalImageViewer.addEventListener("click", (e) => {
      if (e.target === modalImageViewer) {
        modalImageViewer.classList.remove("active");
      }
    });
  }

  // Delete avatar
  if (btnDeleteAvatar) {
    btnDeleteAvatar.addEventListener("click", (e) => {
      e.stopPropagation();
      if (confirm(t("confirm_delete_photo"))) {
        base64AvatarData = ""; // Empty string tells the backend to delete the file
        avatarImg.style.display = "none";
        avatarImg.src = "";
        avatarPlaceholder.style.display = "flex";
        const initials = currentUser.name.trim().split(/\s+/).map(n => n ? n[0] : "").join("").substring(0, 3).toUpperCase();
        avatarPlaceholder.textContent = initials;
        if (avatarActionsContainer) {
          avatarActionsContainer.style.display = "none";
        }
      }
    });
  }

  let base64AvatarData = null;
  avatarInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 1048576) { // 1MB limit
      alert(t("alert_image_size"));
      return;
    }
    const reader = new FileReader();
    reader.onload = (event) => {
      base64AvatarData = event.target.result;
      avatarImg.src = base64AvatarData;
      avatarImg.style.display = "block";
      avatarPlaceholder.style.display = "none";
      if (avatarActionsContainer) {
        avatarActionsContainer.style.display = "flex";
      }
    };
    reader.readAsDataURL(file);
  });

  function renderProfileTab() {
    if (!currentUser) return;
    
    // Reset forms password
    document.getElementById("profile-new-password").value = "";
    document.getElementById("profile-confirm-password").value = "";
    
    // Fill text inputs
    document.getElementById("profile-name").value = currentUser.name;
    document.getElementById("profile-username").value = currentUser.username;
    document.getElementById("profile-title").value = currentUser.title;
    document.getElementById("profile-department").value = currentUser.department || "IT Operations";
    document.getElementById("profile-email").value = currentUser.email || "";
    document.getElementById("profile-phone").value = currentUser.phone || "";
    const profileLangSelect = document.getElementById("profile-language");
    if (profileLangSelect) {
      profileLangSelect.value = currentLang;
    }
    
    // Role badge
    document.getElementById("profile-role-badge").textContent = getTranslatedRole(currentUser.role);
    
    // Avatar image setup
    if (currentUser.avatar) {
      avatarImg.src = currentUser.avatar;
      avatarImg.style.display = "block";
      avatarPlaceholder.style.display = "none";
      base64AvatarData = currentUser.avatar;
      if (avatarActionsContainer) {
        avatarActionsContainer.style.display = "flex";
      }
    } else {
      avatarImg.style.display = "none";
      avatarPlaceholder.style.display = "flex";
      const initials = currentUser.name.trim().split(/\s+/).map(n => n ? n[0] : "").join("").substring(0, 3).toUpperCase();
      avatarPlaceholder.textContent = initials;
      base64AvatarData = null;
      if (avatarActionsContainer) {
        avatarActionsContainer.style.display = "none";
      }
    }
  }

  // Fetch and draw directory
  async function fetchAdminUserDirectory() {
    try {
      const data = await apiCall("get_users");
      renderAdminUserDirectoryTable(data.users);
    } catch (err) {
      console.error("Failed to load user directory:", err);
    }

    try {
      const data = await apiCall("get_registration_requests");
      renderPendingRegistrationRequests(data.requests);
    } catch (err) {
      console.error("Failed to load registration requests:", err);
    }
  }

  function renderAdminUserDirectoryTable(users) {
    const tbody = document.getElementById("admin-users-tbody");
    tbody.innerHTML = "";
    
    users.forEach(u => {
      const escName = escapeHTML(u.name);
      const escUsername = escapeHTML(u.username);
      const escEmail = escapeHTML(u.email);
      const escPhone = escapeHTML(u.phone);
      const escTitle = escapeHTML(u.title);
      const escDepartment = escapeHTML(u.department || '');
      const escRole = escapeHTML(u.role);
      const escId = escapeHTML(String(u.id));

      const userInitials = escName.trim().split(/\s+/).map(n => n ? n[0] : "").join("").substring(0, 2).toUpperCase();
      const isSelf = u.id === currentUser.id;
      const displayYou = isSelf ? ' (You)' : '';
      const displayNoEmail = `<em style="color: var(--text-sub);">No email</em>`;
      const displayNoPhone = 'No phone';

      const localizedRole = getTranslatedRole(escRole);
      
      const rowHtml = `
        <tr>
          <td style="padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background: var(--glass-bg-hover); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #c084fc; font-size: 0.8rem; flex-shrink: 0;">
              ${u.avatar ? `<img src="${u.avatar}" style="width:100%; height:100%; object-fit:cover;">` : userInitials}
            </div>
            <div>
              <strong style="color: var(--text-main); font-size: 0.9rem;">${escName}</strong><br>
              <span style="font-size: 0.75rem; color: var(--text-sub);">@${escUsername}${displayYou}</span>
            </div>
          </td>
          <td style="padding: 12px 16px; font-size: 0.85rem;">
            <span style="color: var(--text-muted);">${escEmail || displayNoEmail}</span><br>
            <span style="color: var(--text-sub); font-size: 0.75rem;">${escPhone || displayNoPhone}</span>
          </td>
          <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--text-muted);">${escTitle}</td>
          <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--text-muted);">${escDepartment}</td>
          <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
            ${localizedRole}
          </td>
          <td style="padding: 12px 16px; text-align: right;">
            <button class="btn btn-secondary btn-edit-user" data-user-id="${escId}" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 6px;">
              <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
              <span>Edit</span>
            </button>
          </td>
        </tr>
      `;
      tbody.insertAdjacentHTML("beforeend", rowHtml);
    });

    // Bind edit user triggers
    tbody.querySelectorAll(".btn-edit-user").forEach(btn => {
      btn.addEventListener("click", () => {
        const userId = parseInt(btn.getAttribute("data-user-id"), 10);
        const targetUser = users.find(x => x.id === userId);
        if (targetUser) {
          openEditUserModal(targetUser);
        }
      });
    });

    refreshIcons();
  }

  function renderPendingRegistrationRequests(requests) {
    const card = document.getElementById("admin-pending-registrations-card");
    const tbody = document.getElementById("admin-pending-users-tbody");
    const countBadge = document.getElementById("pending-reg-count");
    
    tbody.innerHTML = "";
    
    const pendingRequests = requests.filter(r => r.status === 'Pending');
    
    if (pendingRequests.length === 0) {
      card.style.display = "none";
      return;
    }
    
    card.style.display = "block";
    countBadge.textContent = `${pendingRequests.length} ${t('pending_approvals') || 'Pending Approval'}`;
    
    pendingRequests.forEach(req => {
      const escName = escapeHTML(req.name);
      const escUsername = escapeHTML(req.username);
      const escTitle = escapeHTML(req.title);
      const escDept = escapeHTML(req.department || 'IT Operations');
      const escRole = escapeHTML(req.role);
      const escDate = escapeHTML(req.request_date || '');
      const escId = escapeHTML(String(req.id));
      
      const userInitials = escName.trim().split(/\s+/).map(n => n ? n[0] : "").join("").substring(0, 2).toUpperCase();
      
      const rowHtml = `
        <tr>
          <td style="padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(192, 132, 252, 0.1); border: 1px solid rgba(192, 132, 252, 0.3); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #c084fc; font-size: 0.8rem; flex-shrink: 0;">
              ${userInitials}
            </div>
            <div>
              <strong style="color: var(--text-main); font-size: 0.9rem;">${escName}</strong><br>
              <span style="font-size: 0.75rem; color: var(--text-sub);">@${escUsername}</span>
            </div>
          </td>
          <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--text-muted); text-align: left;">
            <strong>${escTitle}</strong><br>
            <span style="font-size: 0.75rem; color: var(--text-sub);">${escDept}</span>
          </td>
          <td style="padding: 12px 16px; font-size: 0.85rem;">
            <span class="badge" style="background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); color: #c084fc; font-size: 0.75rem; padding: 2px 8px;">${getTranslatedRole(escRole)}</span>
          </td>
          <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--text-sub);">${escDate}</td>
          <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
            <button class="btn btn-primary btn-sm btn-approve-reg" data-request-id="${escId}" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px; background: #10b981; border-color: #10b981; cursor: pointer; color: white;">
              <i data-lucide="check" style="width:14px; height:14px;"></i> Approve
            </button>
            <button class="btn btn-secondary btn-sm btn-reject-reg" data-request-id="${escId}" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #ef4444; margin-left: 6px; cursor: pointer;">
              <i data-lucide="x" style="width:14px; height:14px;"></i> Reject
            </button>
          </td>
        </tr>
      `;
      
      tbody.insertAdjacentHTML("beforeend", rowHtml);
    });
    
    // Bind approve/reject triggers
    tbody.querySelectorAll(".btn-approve-reg").forEach(btn => {
      btn.addEventListener("click", async () => {
        const requestId = parseInt(btn.getAttribute("data-request-id"), 10);
        if (confirm(t("confirm_approve_reg"))) {
          try {
            await apiCall("approve_registration", "POST", { requestId });
            await fetchAdminUserDirectory();
          } catch (e) {
            alert(e.message || t("alert_error_communicating"));
          }
        }
      });
    });
    
    tbody.querySelectorAll(".btn-reject-reg").forEach(btn => {
      btn.addEventListener("click", async () => {
        const requestId = parseInt(btn.getAttribute("data-request-id"), 10);
        if (confirm(t("confirm_reject_reg"))) {
          try {
            await apiCall("reject_registration", "POST", { requestId });
            await fetchAdminUserDirectory();
          } catch (e) {
            alert(e.message || t("alert_error_communicating"));
          }
        }
      });
    });
    
    refreshIcons();
  }

  // Submit Profile Form
  formProfile.addEventListener("submit", async (e) => {
    e.preventDefault();

    const name = document.getElementById("profile-name").value.trim();
    const title = document.getElementById("profile-title").value.trim();
    const department = document.getElementById("profile-department").value;
    const email = document.getElementById("profile-email").value.trim();
    const phone = document.getElementById("profile-phone").value.trim();
    const language = "en";
    const newPassword = document.getElementById("profile-new-password").value;
    const confirmPassword = document.getElementById("profile-confirm-password").value;
    const statusMsg = document.getElementById("profile-status-message");

    if (statusMsg) statusMsg.style.display = "none";

    // Validate Passwords match if typed
    if (newPassword && newPassword !== confirmPassword) {
      if (statusMsg) {
        statusMsg.textContent = t("alert_invalid_passwords");
        statusMsg.style.color = "#f87171";
        statusMsg.style.display = "block";
      }
      return;
    }

    try {
      const data = await apiCall("update_profile", "POST", {
        name, title, department, email, phone,
        avatar: base64AvatarData,
        newPassword
      });

      localStorage.setItem("hopper_token", data.token);
      currentUser = data.user;
      
      // Update language state and trigger translation
      currentLang = language;
      localStorage.setItem("hopper_lang", currentLang);
      translatePage();
      switchTab(activeTab); // Re-run tab rendering for title/subtitle updates
      
      // Show success msg
      if (statusMsg) {
        statusMsg.textContent = t("alert_profile_updated");
        statusMsg.style.color = "#10b981";
        statusMsg.style.display = "block";
      }
      
      // Re-render
      updateUserProfileSidebar();
      renderProfileTab();
      
      // Fade out success message after 3 seconds
      setTimeout(() => {
        if (statusMsg) statusMsg.style.display = "none";
      }, 3000);
    } catch (err) {
      if (statusMsg) {
        statusMsg.textContent = err.message || t("alert_error_communicating");
        statusMsg.style.color = "#f87171";
        statusMsg.style.display = "block";
      }
    }
  });

  // --- RENDERER: SYSTEM SETTINGS ---
  function renderSettingsTab() {
    const listDepts = document.getElementById("settings-depts-list");
    const listCats = document.getElementById("settings-cats-list");
    
    // Render Departments
    listDepts.innerHTML = "";
    if (departments.length === 0) {
      listDepts.innerHTML = `<p style="font-size: 0.8rem; color: var(--text-sub); text-align: center; padding: 12px;">${t("no_departments")}</p>`;
    } else {
      departments.forEach(dept => {
        const escDept = escapeHTML(dept);
        const itemHtml = `
          <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid var(--glass-border); font-size: 0.85rem;">
            <span>${escDept}</span>
            ${currentUser && currentUser.role === 'Administrator' ? `
              <button class="btn btn-danger btn-sm btn-delete-dept" data-dept="${escDept}" style="padding: 4px 8px; font-size: 0.75rem; min-height: auto; cursor: pointer; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #ef4444;">
                <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
              </button>
            ` : ''}
          </div>
        `;
        listDepts.insertAdjacentHTML("beforeend", itemHtml);
      });
      
      // Bind Delete triggers
      listDepts.querySelectorAll(".btn-delete-dept").forEach(btn => {
        btn.addEventListener("click", async () => {
          const dept = btn.getAttribute("data-dept");
          if (confirm(`${t("confirm_delete_dept")} "${dept}"?`)) {
            try {
              await apiCall("delete_department", "POST", { name: dept });
              await fetchDepartments();
              renderSettingsTab();
            } catch (e) {
              alert(`${t("alert_error_deleting_dept")}${e.message}`);
            }
          }
        });
      });
    }

    // Render Categories
    listCats.innerHTML = "";
    if (categories.length === 0) {
      listCats.innerHTML = `<p style="font-size: 0.8rem; color: var(--text-sub); text-align: center; padding: 12px;">${t("no_categories")}</p>`;
    } else {
      categories.forEach(cat => {
        const escCat = escapeHTML(cat);
        const itemHtml = `
          <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid var(--glass-border); font-size: 0.85rem;">
            <span>${escCat}</span>
            ${currentUser && currentUser.role === 'Administrator' ? `
              <button class="btn btn-danger btn-sm btn-delete-cat" data-cat="${escCat}" style="padding: 4px 8px; font-size: 0.75rem; min-height: auto; cursor: pointer; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #ef4444;">
                <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
              </button>
            ` : ''}
          </div>
        `;
        listCats.insertAdjacentHTML("beforeend", itemHtml);
      });

      // Bind Delete triggers
      listCats.querySelectorAll(".btn-delete-cat").forEach(btn => {
        btn.addEventListener("click", async () => {
          const cat = btn.getAttribute("data-cat");
          if (confirm(`${t("confirm_delete_cat")} "${cat}"?`)) {
            try {
              await apiCall("delete_category", "POST", { name: cat });
              await fetchCategories();
              renderSettingsTab();
            } catch (e) {
              alert(`${t("alert_error_deleting_cat")}${e.message}`);
            }
          }
        });
      });
    }

    // Render Groups
    const listGroups = document.getElementById("settings-groups-list");
    if (listGroups) {
      listGroups.innerHTML = "";
      if (groups.length === 0) {
        listGroups.innerHTML = `<p style="font-size: 0.8rem; color: var(--text-sub); text-align: center; padding: 12px;">${t("no_groups") || "No groups available"}</p>`;
      } else {
        groups.forEach(grp => {
          const escGrp = escapeHTML(grp);
          const itemHtml = `
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid var(--glass-border); font-size: 0.85rem;">
              <span>${escGrp}</span>
              ${currentUser && currentUser.role === 'Administrator' ? `
                <button class="btn btn-danger btn-sm btn-delete-group" data-group="${escGrp}" style="padding: 4px 8px; font-size: 0.75rem; min-height: auto; cursor: pointer; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #ef4444;">
                  <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                </button>
              ` : ''}
            </div>
          `;
          listGroups.insertAdjacentHTML("beforeend", itemHtml);
        });

        // Bind Delete Group triggers
        listGroups.querySelectorAll(".btn-delete-group").forEach(btn => {
          btn.addEventListener("click", async () => {
            const grp = btn.getAttribute("data-group");
            if (confirm(`${t("confirm_delete_group") || "Are you sure you want to delete group"} "${grp}"?`)) {
              try {
                await apiCall("delete_group", "POST", { name: grp });
                await fetchGroups();
                renderSettingsTab();
              } catch (e) {
                alert(`${t("alert_error_deleting_group") || "Error deleting group: "}${e.message}`);
              }
            }
          });
        });
      }
    }

    // Show/hide Admin management forms
    const addDeptWrapper = document.getElementById("add-dept-wrapper");
    const addCatWrapper = document.getElementById("add-cat-wrapper");
    const addGroupWrapper = document.getElementById("add-group-wrapper");
    const webhookForm = document.getElementById("form-webhook-settings");
    const settingsAdminBadge = document.getElementById("settings-admin-badge");

    if (currentUser && currentUser.role === 'Administrator') {
      if (addDeptWrapper) addDeptWrapper.style.display = "flex";
      if (addCatWrapper) addCatWrapper.style.display = "flex";
      if (addGroupWrapper) addGroupWrapper.style.display = "flex";
      if (webhookForm) {
        webhookForm.querySelectorAll("input, button").forEach(el => el.disabled = false);
      }
      if (settingsAdminBadge) {
        settingsAdminBadge.textContent = t("system_administration");
        settingsAdminBadge.className = "badge badge-risk high";
      }
    } else {
      if (addDeptWrapper) addDeptWrapper.style.display = "none";
      if (addCatWrapper) addCatWrapper.style.display = "none";
      if (addGroupWrapper) addGroupWrapper.style.display = "none";
      if (webhookForm) {
        webhookForm.querySelectorAll("input, button").forEach(el => el.disabled = true);
      }
      if (settingsAdminBadge) {
        settingsAdminBadge.textContent = t("view_all");
        settingsAdminBadge.className = "badge badge-risk low";
      }
    }
    
    refreshIcons();
  }

  // Bind Add Buttons Once
  const btnAddDept = document.getElementById("btn-add-dept");
  if (btnAddDept) {
    btnAddDept.addEventListener("click", async () => {
      const input = document.getElementById("new-dept-name");
      const name = input.value.trim();
      if (!name) return;
      
      try {
        await apiCall("add_department", "POST", { name });
        input.value = "";
        await fetchDepartments();
        renderSettingsTab();
      } catch (e) {
        alert(`${t("alert_error_adding_dept")}${e.message}`);
      }
    });
  }

  const btnAddCat = document.getElementById("btn-add-cat");
  if (btnAddCat) {
    btnAddCat.addEventListener("click", async () => {
      const input = document.getElementById("new-cat-name");
      const name = input.value.trim();
      if (!name) return;

      try {
        await apiCall("add_category", "POST", { name });
        input.value = "";
        await fetchCategories();
        renderSettingsTab();
      } catch (e) {
        alert(`${t("alert_error_adding_cat")}${e.message}`);
      }
    });
  }

  const btnAddGroup = document.getElementById("btn-add-group");
  if (btnAddGroup) {
    btnAddGroup.addEventListener("click", async () => {
      const input = document.getElementById("new-group-name");
      const name = input.value.trim();
      if (!name) return;

      try {
        await apiCall("add_group", "POST", { name });
        input.value = "";
        await fetchGroups();
        renderSettingsTab();
      } catch (e) {
        alert(`${t("alert_error_adding_group") || "Error adding group: "}${e.message}`);
      }
    });
  }

  const formWebhookSettings = document.getElementById("form-webhook-settings");
  if (formWebhookSettings) {
    formWebhookSettings.addEventListener("submit", async (e) => {
      e.preventDefault();
      const webhookUrl = document.getElementById("webhook-url").value.trim();
      const notifyOnCreate = document.getElementById("webhook-notify-create").checked;
      const notifyOnStatusChange = document.getElementById("webhook-notify-status").checked;
      const notifyOnHighRiskOnly = document.getElementById("webhook-notify-highrisk").checked;

      try {
        await apiCall("update_notification_settings", "POST", {
          webhookUrl,
          notifyOnCreate,
          notifyOnStatusChange,
          notifyOnHighRiskOnly
        });
        alert(t("alert_settings_saved") || "Webhook settings saved successfully.");
        await loadWebhookSettings();
      } catch (e) {
        alert(`${t("alert_error_saving_settings") || "Error saving settings: "}${e.message}`);
      }
    });
  }

  const formAdSettings = document.getElementById("form-ad-settings");
  
  // Bind Add Domain button event listeners
  const btnAddAdDomain = document.getElementById("btn-add-ad-domain");
  const adDomainInput = document.getElementById("ad-domain-input");
  if (btnAddAdDomain && adDomainInput) {
    btnAddAdDomain.addEventListener("click", () => {
      const val = adDomainInput.value.trim().toLowerCase();
      if (val) {
        const cleanVal = val.replace(/^@/, '');
        if (cleanVal && !adDomainsList.includes(cleanVal)) {
          adDomainsList.push(cleanVal);
          adDomainInput.value = "";
          renderAdDomainsList();
        }
      }
    });
    adDomainInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        btnAddAdDomain.click();
      }
    });
  }

  if (formAdSettings) {
    formAdSettings.addEventListener("submit", async (e) => {
      e.preventDefault();
      const adEnabled = document.getElementById("ad-enabled").checked;
      const adServer = document.getElementById("ad-server").value.trim();
      const adPort = parseInt(document.getElementById("ad-port").value || "389");
      const adDomain = adDomainsList.join(', ');
      const adBaseDn = document.getElementById("ad-basedn").value.trim();

      try {
        await apiCall("update_ad_settings", "POST", {
          adEnabled,
          adServer,
          adPort,
          adDomain,
          adBaseDn
        });
        alert(t("alert_ad_saved") || "Active Directory settings saved successfully.");
        await loadAdSettings();
      } catch (e) {
        alert("Error saving Active Directory settings: " + e.message);
      }
    });
  }

  const btnTestAd = document.getElementById("btn-test-ad");
  if (btnTestAd) {
    btnTestAd.addEventListener("click", async () => {
      const adServer = document.getElementById("ad-server").value.trim();
      const adPort = parseInt(document.getElementById("ad-port").value || "389");
      
      if (!adServer) {
        alert("LDAP Server Address is required to perform connection test.");
        return;
      }

      const originalText = btnTestAd.innerHTML;
      btnTestAd.disabled = true;
      btnTestAd.innerHTML = `<i data-lucide="loader-2" class="spin" style="width: 14px; height: 14px; animation: spin 1s linear infinite;"></i> Testing...`;
      refreshIcons();

      try {
        const data = await apiCall("test_ad_connection", "POST", {
          adServer,
          adPort
        });
        alert(data.message || "Connection succeeded.");
      } catch (e) {
        alert("Connection test failed: " + e.message);
      } finally {
        btnTestAd.disabled = false;
        btnTestAd.innerHTML = originalText;
        refreshIcons();
      }
    });
  }

  const linkAdGuide = document.getElementById("link-ad-guide");
  const adGuideBox = document.getElementById("ad-guide-box");
  const textAdGuideToggle = document.getElementById("text-ad-guide-toggle");
  if (linkAdGuide && adGuideBox) {
    linkAdGuide.addEventListener("click", (e) => {
      e.preventDefault();
      if (adGuideBox.style.display === "none" || adGuideBox.style.display === "") {
        adGuideBox.style.display = "flex";
        textAdGuideToggle.setAttribute("data-i18n", "ad_hide_guide");
        textAdGuideToggle.textContent = t("ad_hide_guide");
      } else {
        adGuideBox.style.display = "none";
        textAdGuideToggle.setAttribute("data-i18n", "ad_view_guide");
        textAdGuideToggle.textContent = t("ad_view_guide");
      }
    });
  }

  const btnExportCsv = document.getElementById("btn-export-cab");
  if (btnExportCsv) {
    btnExportCsv.addEventListener("click", () => {
      const query = (document.getElementById("search-query")?.value || "").toLowerCase();
      const status = document.getElementById("filter-status")?.value || "";
      const risk = document.getElementById("filter-risk")?.value || "";

      const filtered = changes.filter(change => {
        const matchesQuery = change.id.toLowerCase().includes(query) ||
                             change.title.toLowerCase().includes(query) ||
                             change.owner.toLowerCase().includes(query);
        const matchesStatus = status === "" || change.status === status;
        const matchesRisk = risk === "" || change.risk === risk;
        return matchesQuery && matchesStatus && matchesRisk;
      });

      if (filtered.length === 0) {
        alert(t("no_changes_to_export"));
        return;
      }

      const headers = [t("change_id"), t("change_title"), t("requester"), t("owner"), t("category_label").replace(' *', ''), t("risk_level_label").replace(' *', ''), t("status") || "Status", t("target_date")];
      const csvRows = [headers.join(",")];

      filtered.forEach(change => {
        const row = [
          change.id,
          `"${change.title.replace(/"/g, '""')}"`,
          `"${change.requester.replace(/"/g, '""')}"`,
          `"${change.owner.replace(/"/g, '""')}"`,
          `"${change.category.replace(/"/g, '""')}"`,
          change.risk,
          change.status,
          change.targetDate
        ];
        csvRows.push(row.join(","));
      });

      const csvContent = "\uFEFF" + csvRows.join("\n");
      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.setAttribute("href", url);
      link.setAttribute("download", `CAB_Change_Report_${new Date().toISOString().slice(0,10)}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    });
  }

  const btnPrintCab = document.getElementById("btn-print-cab");
  if (btnPrintCab) {
    btnPrintCab.addEventListener("click", () => {
      window.print();
    });
  }

  // --- EDIT USER DETAILS MODAL (Admin Only) ---
  const modalEditUser = document.getElementById("modal-edit-user");
  const btnEditUserCancel = document.getElementById("btn-edit-user-cancel");
  const btnEditUserClose = document.getElementById("modal-edit-user-close");
  const formEditUser = document.getElementById("form-edit-user");

  function closeEditUserModal() {
    modalEditUser.classList.remove("active");
    document.getElementById("edit-user-error").style.display = "none";
    document.getElementById("edit-user-error").textContent = "";
  }
  if (btnEditUserCancel) btnEditUserCancel.addEventListener("click", closeEditUserModal);
  if (btnEditUserClose) btnEditUserClose.addEventListener("click", closeEditUserModal);

  function openEditUserModal(user) {
    // Populate departments select dropdown
    const deptSelect = document.getElementById("edit-user-department");
    deptSelect.innerHTML = "";
    departments.forEach(dept => {
      const option = document.createElement("option");
      option.value = dept;
      option.textContent = dept;
      if (dept === user.department) {
        option.selected = true;
      }
      deptSelect.appendChild(option);
    });

    // Populate user values
    document.getElementById("edit-user-id").value = user.id;
    document.getElementById("edit-user-username").value = user.username;
    document.getElementById("edit-user-name").value = user.name;
    document.getElementById("edit-user-title").value = user.title || "";
    document.getElementById("edit-user-email").value = user.email || "";
    document.getElementById("edit-user-phone").value = user.phone || "";
    document.getElementById("edit-user-password").value = ""; // clear password field

    // Populate role selector and handle self-demotion logic
    const roleSelect = document.getElementById("edit-user-role");
    roleSelect.value = user.role;
    
    // Pre-select group
    const groupSelect = document.getElementById("edit-user-group");
    if (groupSelect) {
      groupSelect.value = user.group || "";
    }
    
    // Disable role selector if editing own profile to prevent self-demotion
    if (user.id === currentUser.id) {
      roleSelect.disabled = true;
    } else {
      roleSelect.disabled = false;
    }

    // Open modal
    modalEditUser.classList.add("active");
    translatePage();
  }

  if (formEditUser) {
    formEditUser.addEventListener("submit", async (e) => {
      e.preventDefault();
      
      const userId = parseInt(document.getElementById("edit-user-id").value, 10);
      const name = document.getElementById("edit-user-name").value.trim();
      const title = document.getElementById("edit-user-title").value.trim();
      const department = document.getElementById("edit-user-department").value;
      const group = document.getElementById("edit-user-group").value;
      const role = document.getElementById("edit-user-role").value;
      const email = document.getElementById("edit-user-email").value.trim();
      const phone = document.getElementById("edit-user-phone").value.trim();
      const newPassword = document.getElementById("edit-user-password").value;
      
      const errorEl = document.getElementById("edit-user-error");
      errorEl.style.display = "none";
      errorEl.textContent = "";

      if (!name) {
        errorEl.textContent = "Full Name is required.";
        errorEl.style.display = "block";
        return;
      }

      try {
        const data = await apiCall("admin_update_user", "POST", {
          userId,
          name,
          title,
          department,
          group,
          role,
          email,
          phone,
          newPassword
        });

        // Show success alert
        alert(t("alert_user_updated"));
        
        // If we updated ourselves, reload the current session session token & state
        if (data.token) {
          localStorage.setItem("hopper_token", data.token);
          // Recheck authentication to refresh currentUser session state
          await checkAuth();
        }

        // Close modal
        closeEditUserModal();

        // Refresh user list directory table
        await fetchAdminUserDirectory();
        await refreshData();
      } catch (err) {
        errorEl.textContent = err.message || t("alert_error_updating_user");
        errorEl.style.display = "block";
      }
    });
  }


  // --- ATTACHMENT UPLOAD LISTENER ---
  const attachInput = document.getElementById("change-attachment-input");
  const uploadStatus = document.getElementById("attachment-upload-status");
  
  if (attachInput) {
    attachInput.addEventListener("change", async (e) => {
      const file = e.target.files[0];
      if (!file) return;
      
      if (file.size > 2 * 1024 * 1024) {
        alert(t("alert_file_size_exceeded"));
        attachInput.value = "";
        return;
      }
      
      if (uploadStatus) uploadStatus.textContent = t("alert_upload_reading");
      
      const reader = new FileReader();
      reader.onload = async () => {
        const fileData = reader.result;
        if (uploadStatus) uploadStatus.textContent = t("alert_upload_uploading");
        
        try {
          const data = await apiCall(`upload_attachment&id=${activeChangeId}`, "POST", {
            fileName: file.name,
            fileType: file.type,
            fileData: fileData
          });
          
          if (uploadStatus) uploadStatus.textContent = t("upload_help");
          attachInput.value = "";
          await refreshData();
          openDetailModal(activeChangeId);
        } catch (err) {
          alert(`Upload failed: ${err.message}`);
          if (uploadStatus) uploadStatus.textContent = t("alert_upload_failed");
          attachInput.value = "";
        }
      };
      reader.onerror = () => {
        alert(t("alert_upload_error"));
        if (uploadStatus) uploadStatus.textContent = t("alert_upload_error");
        attachInput.value = "";
      };
      reader.readAsDataURL(file);
    });
  }

  // --- PASSWORD VISIBILITY TOGGLE ---
  document.querySelectorAll(".password-toggle-btn").forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const targetId = btn.getAttribute("toggle-target");
      const input = document.getElementById(targetId);
      if (!input) return;

      if (input.type === "password") {
        input.type = "text";
        btn.innerHTML = `<i data-lucide="eye-off" style="width: 18px; height: 18px; display: block;"></i>`;
      } else {
        input.type = "password";
        btn.innerHTML = `<i data-lucide="eye" style="width: 18px; height: 18px; display: block;"></i>`;
      }
      refreshIcons();
    });
  });

  // Bind conflict check listeners for real-time form checking
  const targetDateInput = document.getElementById("change-target-date");
  if (targetDateInput) targetDateInput.addEventListener("change", checkConflictsForForm);
  
  const categorySelectInput = document.getElementById("change-category");
  if (categorySelectInput) categorySelectInput.addEventListener("change", checkConflictsForForm);
  
  const assignedGroupSelectInput = document.getElementById("change-assigned-group");
  if (assignedGroupSelectInput) assignedGroupSelectInput.addEventListener("change", checkConflictsForForm);

  // --- INITIAL APPLICATION START ---
  fetchCategories();
  fetchDepartments();
  checkAuth();
  document.title = "Hopper";
  
  // Double-guard to ensure icons render even if Lucide script loads with latency
  setTimeout(refreshIcons, 100);
  setTimeout(refreshIcons, 500);
  setTimeout(refreshIcons, 1500);
});
