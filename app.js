// Hopper App Engine - PHP Full-Stack Version
document.addEventListener("DOMContentLoaded", () => {
  // --- LANGUAGE STATE ---
  let currentLang = localStorage.getItem("hopper_lang") || "en";

  // --- THEME STATE & TOGGLE ---
  const themeCheckbox = document.getElementById("theme-mode-checkbox");
  const themeSunIcon = document.getElementById("theme-toggle-sun");

  // Load saved theme
  const currentTheme = localStorage.getItem("hopper_theme") || "dark";
  if (currentTheme === "light") {
    document.body.classList.add("light-mode");
    if (themeCheckbox) themeCheckbox.checked = true;
    if (themeSunIcon) {
      themeSunIcon.setAttribute("data-lucide", "sun");
    }
  } else {
    document.body.classList.remove("light-mode");
    if (themeCheckbox) themeCheckbox.checked = false;
    if (themeSunIcon) {
      themeSunIcon.setAttribute("data-lucide", "moon");
    }
  }
  refreshIcons();

  if (themeCheckbox) {
    themeCheckbox.addEventListener("change", () => {
      if (themeCheckbox.checked) {
        document.body.classList.add("light-mode");
        localStorage.setItem("hopper_theme", "light");
        if (themeSunIcon) {
          themeSunIcon.setAttribute("data-lucide", "sun");
        }
      } else {
        document.body.classList.remove("light-mode");
        localStorage.setItem("hopper_theme", "dark");
        if (themeSunIcon) {
          themeSunIcon.setAttribute("data-lucide", "moon");
        }
      }
      refreshIcons();
    });
  }

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

  // --- STATE MANAGEMENT ---
  let changes = [];
  let activities = [];
  let categories = [];
  let departments = [];
  let activeTab = "dashboard";
  let activeChangeId = null;
  let currentUser = null;

  // Calendar State
  let currentYear = 2026;
  let currentMonth = 5; // June (0-indexed)
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
      "Authorization": token ? `Bearer ${token}` : ""
    };
  }

  // --- INITIAL CHECK AUTH ---
  async function checkAuth() {
    const token = getToken();
    const loginScreen = document.getElementById("login-screen");
    
    if (!token) {
      loginScreen.style.display = "flex";
      return;
    }

    try {
      const res = await fetch("api.php?action=me", {
        headers: getAuthHeaders()
      });
      
      if (res.ok) {
        const data = await res.json();
        currentUser = data.user;
        loginScreen.style.display = "none";
        updateUserProfileSidebar();
        
        // Load initial system data
        await fetchCategories();
        await refreshData();
        switchTab(activeTab);
      } else {
        // Token invalid
        localStorage.removeItem("hopper_token");
        loginScreen.style.display = "flex";
      }
    } catch (err) {
      console.error("Auth check failed:", err);
      loginScreen.style.display = "flex";
      document.getElementById("login-error").textContent = "Unable to connect to PHP backend. Verify local server configuration.";
      document.getElementById("login-error").style.display = "block";
    }
  }

  // Update profile sidebar/dropdown with current user info
  function updateUserProfileSidebar() {
    if (!currentUser) return;
    
    // Show/hide Admin User Directory menu items dynamically
    const usersLi = document.getElementById("nav-users-li");
    if (currentUser.role === "Administrator") {
      if (usersLi) usersLi.style.display = "block";
    } else {
      if (usersLi) usersLi.style.display = "none";
    }

    // Sync names
    const navUserName = document.getElementById("nav-user-name");
    const dropdownUserName = document.getElementById("dropdown-user-name");
    if (navUserName) navUserName.textContent = currentUser.name;
    if (dropdownUserName) dropdownUserName.textContent = currentUser.name;

    // Sync roles & titles
    const displayRoleTitle = `${currentUser.role} • ${currentUser.title}`;
    const navUserRole = document.getElementById("nav-user-role");
    const dropdownUserRole = document.getElementById("dropdown-user-role");
    if (navUserRole) navUserRole.textContent = displayRoleTitle;
    if (dropdownUserRole) dropdownUserRole.textContent = displayRoleTitle;
    
    // Compute initials
    const initials = currentUser.name
      .split(" ")
      .map(n => n[0])
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

    // Update dropdown avatar
    const dropdownAvatarEl = document.getElementById("dropdown-user-avatar");
    if (dropdownAvatarEl) {
      if (currentUser.avatar) {
        dropdownAvatarEl.innerHTML = `<img src="${currentUser.avatar}" style="width:100%; height:100%; border-radius:50%; object-fit:cover; display:block;">`;
        dropdownAvatarEl.style.padding = "0";
        dropdownAvatarEl.style.overflow = "hidden";
      } else {
        dropdownAvatarEl.textContent = initials;
        dropdownAvatarEl.style.padding = ""; // Reset
        dropdownAvatarEl.style.overflow = "";
      }
    }
  }

  // --- DATA REFRESHERS ---
  async function fetchCategories() {
    try {
      const res = await fetch("api.php?action=categories");
      if (res.ok) {
        const data = await res.json();
        categories = data.categories;
      }
    } catch (err) {
      console.error("Error fetching categories:", err);
    }
  }

  async function fetchDepartments() {
    try {
      const res = await fetch("api.php?action=get_departments");
      if (res.ok) {
        const data = await res.json();
        departments = data.departments;
        populateDepartmentDropdowns();
      }
    } catch (err) {
      console.error("Error fetching departments:", err);
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
        if (departments.includes("BT / IT")) {
          regSelect.value = "BT / IT";
        } else if (departments.length > 0) {
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
      const changesRes = await fetch("api.php?action=get_changes", { headers: getAuthHeaders() });
      if (changesRes.ok) {
        const data = await changesRes.json();
        changes = data.changes;
      }

      // Get activities
      const actRes = await fetch("api.php?action=activities", { headers: getAuthHeaders() });
      if (actRes.ok) {
        const data = await actRes.json();
        activities = data.activities;
      }
      
      updateApprovalBadge();
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

  if (headerUserProfile && dropdownMenu) {
    headerUserProfile.addEventListener("click", (e) => {
      e.stopPropagation();
      const isVisible = dropdownMenu.style.display === "flex";
      dropdownMenu.style.display = isVisible ? "none" : "flex";
    });
  }

  // Close dropdown menu when clicking outside
  document.addEventListener("click", (e) => {
    if (dropdownMenu && dropdownMenu.style.display === "flex") {
      if (!headerUserProfile.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.style.display = "none";
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
    authTitle.textContent = "Create New Account";
    refreshIcons();
  });

  linkShowLogin.addEventListener("click", (e) => {
    e.preventDefault();
    formRegister.style.display = "none";
    formLogin.style.display = "block";
    authTitle.textContent = "Sign In to System";
    refreshIcons();
  });

  // Login Submit
  formLogin.addEventListener("submit", async (e) => {
    e.preventDefault();
    const username = document.getElementById("login-username").value.trim();
    const password = document.getElementById("login-password").value;
    const errorEl = document.getElementById("login-error");
    
    errorEl.style.display = "none";
    errorEl.style.color = "#f87171";

    try {
      const res = await fetch("api.php?action=login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password })
      });
      
      const data = await res.json();
      if (res.ok) {
        localStorage.setItem("hopper_token", data.token);
        currentUser = data.user;
        document.getElementById("login-screen").style.display = "none";
        formLogin.reset();
        
        updateUserProfileSidebar();
        await fetchCategories();
        await refreshData();
        switchTab("dashboard");
      } else {
        errorEl.textContent = data.error || "Login failed.";
        errorEl.style.display = "block";
      }
    } catch (err) {
      errorEl.textContent = "Unable to connect to backend api.";
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
      errorEl.textContent = "Password must be at least 4 characters.";
      errorEl.style.display = "block";
      return;
    }

    try {
      const res = await fetch("api.php?action=register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, username, password, title, department, role })
      });

      const data = await res.json();
      if (res.ok) {
        formRegister.reset();
        
        // Switch back to Login form
        formRegister.style.display = "none";
        formLogin.style.display = "block";
        document.getElementById("auth-title").textContent = "Sign In to System";
        
        const loginErrorEl = document.getElementById("login-error");
        loginErrorEl.textContent = data.message || "Kayıt talebiniz iletildi. Lütfen onay bekleyin.";
        loginErrorEl.style.color = "#10b981"; // success green
        loginErrorEl.style.display = "block";
        
        refreshIcons();
      } else {
        errorEl.textContent = data.error || "Registration failed.";
        errorEl.style.display = "block";
      }
    } catch (err) {
      errorEl.textContent = "Unable to connect to server.";
      errorEl.style.display = "block";
    }
  });

  // Logout Click
  btnLogout.addEventListener("click", () => {
    localStorage.removeItem("hopper_token");
    currentUser = null;
    document.getElementById("login-screen").style.display = "flex";
    formLogin.style.display = "block";
    formRegister.style.display = "none";
    authTitle.textContent = "Sign In to System";
    document.getElementById("login-error").style.display = "none";
    document.getElementById("register-error").style.display = "none";
    refreshIcons();
  });

  // --- HELPER: ICON REFRESHER ---
  function refreshIcons() {
    if (window.lucide && typeof window.lucide.createIcons === "function") {
      window.lucide.createIcons();
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
      titleEl.textContent = translations[currentLang]['dashboard'];
      subtitleEl.textContent = translations[currentLang]['dashboard_subtitle'];
    } else if (tabName === "changes") {
      titleEl.textContent = translations[currentLang]['change_requests'];
      subtitleEl.textContent = translations[currentLang]['changes_subtitle'];
    } else if (tabName === "approvals") {
      titleEl.textContent = translations[currentLang]['approval_center'];
      subtitleEl.textContent = translations[currentLang]['approvals_subtitle'];
    } else if (tabName === "calendar") {
      titleEl.textContent = translations[currentLang]['change_calendar'];
      subtitleEl.textContent = translations[currentLang]['calendar_subtitle'];
    } else if (tabName === "profile") {
      titleEl.textContent = translations[currentLang]['my_profile'];
      subtitleEl.textContent = translations[currentLang]['profile_subtitle'];
    } else if (tabName === "users") {
      titleEl.textContent = translations[currentLang]['user_directory'];
      subtitleEl.textContent = translations[currentLang]['users_subtitle'];
    } else if (tabName === "settings") {
      titleEl.textContent = translations[currentLang]['system_settings'];
      subtitleEl.textContent = translations[currentLang]['settings_subtitle'];
    } else if (tabName === "about") {
      titleEl.textContent = translations[currentLang]['about_hopper'];
      subtitleEl.textContent = translations[currentLang]['about_subtitle'];
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
    } else if (tabName === "settings") {
      renderSettingsTab();
    }

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
          <p>No active or critical change requests.</p>
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
        const itemHtml = `
          <div class="change-item" data-id="${escId}">
            <span class="change-id">${escId}</span>
            <div class="change-main-info">
              <span class="change-title" title="${escTitle}">${escTitle}</span>
              <span class="change-meta">${escCategory} • Owner: ${escOwner}</span>
            </div>
            <div>
              <span class="badge badge-risk ${escRisk.toLowerCase()}">${escRisk} Risk</span>
            </div>
            <div>
              <span class="badge badge-status ${escStatus.toLowerCase().replace(' ', '-')}">${escStatus}</span>
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

    renderActivityFeed();
  }

  // Render Activity Feed helper
  function renderActivityFeed() {
    const feedContainer = document.getElementById("dashboard-activity-feed");
    feedContainer.innerHTML = "";

    if (activities.length === 0) {
      feedContainer.innerHTML = `<p style="color: var(--text-sub); text-align: center; padding: 16px;">No recent activities.</p>`;
      return;
    }

    activities.slice(0, 5).forEach(act => {
      const escUser = escapeHTML(act.user);
      const escAction = escapeHTML(act.action);
      const escTarget = escapeHTML(act.target);
      const escDate = escapeHTML(act.date);
      const itemHtml = `
        <div class="activity-item">
          <div class="activity-marker">
            <i data-lucide="activity"></i>
          </div>
          <div class="activity-content">
            <p class="activity-text"><strong>${escUser}</strong>, ${escAction} (${escTarget})</p>
            <div class="activity-time">${escDate}</div>
          </div>
        </div>
      `;
      feedContainer.insertAdjacentHTML("beforeend", itemHtml);
    });
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
      const res = await fetch(url, { headers: getAuthHeaders() });
      if (res.ok) {
        const data = await res.json();
        const filteredChanges = data.changes;
        
        const listContainer = document.getElementById("all-changes-list");
        listContainer.innerHTML = "";

        if (filteredChanges.length === 0) {
          listContainer.innerHTML = `
            <div style="text-align: center; padding: 48px; color: var(--text-muted);">
              <i data-lucide="info" style="width: 48px; height: 48px; color: var(--text-sub); margin-bottom: 12px; display: inline-block;"></i>
              <p>No change requests match the criteria.</p>
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
            const itemHtml = `
              <div class="change-item" data-id="${escId}">
                <span class="change-id">${escId}</span>
                <div class="change-main-info">
                  <span class="change-title" title="${escTitle}">${escTitle}</span>
                  <span class="change-meta">${escCategory} • Owner: ${escOwner}</span>
                </div>
                <div>
                  <span class="badge badge-risk ${escRisk.toLowerCase()}">${escRisk} Risk</span>
                </div>
                <div>
                  <span class="badge badge-status ${escStatus.toLowerCase().replace(' ', '-')}">${escStatus}</span>
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
          <h3>No Pending Approvals</h3>
          <p style="margin-top: 8px;">Great! All pending change requests in the system have been approved or reviewed.</p>
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
        const itemHtml = `
          <div class="change-item" data-id="${escId}">
            <span class="change-id">${escId}</span>
            <div class="change-main-info">
              <span class="change-title" title="${escTitle}">${escTitle}</span>
              <span class="change-meta">Requester: ${escRequester} (${escReqTitle}) • Category: ${escCategory}</span>
            </div>
            <div>
              <span class="badge badge-risk ${escRisk.toLowerCase()}">${escRisk} Risk</span>
            </div>
            <div>
              <span class="badge badge-status pending-approval">Pending Approval</span>
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
              <button class="btn btn-secondary btn-sm btn-quick-view" data-id="${escId}">View Details / Approve</button>
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
    monthLabel.textContent = `${monthNames[currentMonth]} ${currentYear}`;

    const calendarGrid = document.getElementById("calendar-grid-container");
    calendarGrid.innerHTML = "";

    // Day headers
    const daysOfWeek = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    daysOfWeek.forEach(day => {
      calendarGrid.insertAdjacentHTML("beforeend", `<div class="calendar-day-header">${day}</div>`);
    });

    // Compute calendar dates
    const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay(); // Sun = 0, Mon = 1, etc.
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

      calendarGrid.insertAdjacentHTML("beforeend", `
        <div class="calendar-day ${todayClass}">
          <span class="calendar-day-number">${day}</span>
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
    if (remainingCells > 0 && remainingCells < 7) {
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

    modalCreate.classList.add("active");
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
    
    // Parse tasks text
    const tasksRaw = document.getElementById("change-tasks").value.split("\n");
    const tasks = tasksRaw
      .map(line => line.trim())
      .filter(line => line.length > 0)
      .map(line => ({ text: line, completed: false }));

    try {
      const res = await fetch("api.php?action=create_change", {
        method: "POST",
        headers: getAuthHeaders(),
        body: JSON.stringify({
          title, description, requester, requesterTitle, owner, ownerTitle,
          category, risk, targetDate, impact, rollbackPlan, tasks
        })
      });

      if (res.ok) {
        closeCreateModal();
        await refreshData();
        switchTab("changes");
      } else {
        const errData = await res.json();
        alert(`Error creating request: ${errData.error}`);
      }
    } catch (err) {
      alert("Failed to submit request to server.");
    }
  });

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
      const res = await fetch(`api.php?action=get_change_detail&id=${changeId}`, { headers: getAuthHeaders() });
      if (!res.ok) return;

      const data = await res.json();
      const change = data.change;

      // Set static text content
      document.getElementById("detail-id").textContent = change.id;
      document.getElementById("detail-title").textContent = change.title;
      document.getElementById("detail-description").textContent = change.description;
      document.getElementById("detail-impact").textContent = change.impact;
      document.getElementById("detail-rollback").textContent = change.rollbackPlan;
      document.getElementById("detail-requester").textContent = `${change.requester} (${change.requesterTitle})`;
      document.getElementById("detail-owner").textContent = `${change.owner} (${change.ownerTitle})`;
      document.getElementById("detail-date").textContent = change.targetDate;

      // Badges Styling
      const statusEl = document.getElementById("detail-status");
      statusEl.className = `badge badge-status ${change.status.toLowerCase().replace(' ', '-')}`;
      statusEl.textContent = change.status;

      const riskEl = document.getElementById("detail-risk");
      riskEl.className = `badge badge-risk ${change.risk.toLowerCase()}`;
      riskEl.textContent = `${change.risk} Risk`;

      const catEl = document.getElementById("detail-category");
      catEl.textContent = change.category;

      // Render Checklist
      renderChecklist(change);

      // Render Approvals Log
      renderApprovalsLog(change);

      // Render Comments Section
      renderComments(change);

      // Render Workflow Actions Panel
      renderActionsPanel(change);

      // Show modal
      modalDetail.classList.add("active");
      refreshIcons();
    } catch (err) {
      console.error("Error loading change details:", err);
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
       change.owner === currentUser.name || 
       change.requester === currentUser.name);

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
              const res = await fetch(`api.php?action=toggle_task&id=${change.id}&task_id=${taskId}`, {
                method: "PUT",
                headers: getAuthHeaders(),
                body: JSON.stringify({ completed: nextCompletedState })
              });

              if (res.ok) {
                const data = await res.json();
                
                // Update local list index
                const idx = changes.findIndex(c => c.id === change.id);
                if (idx !== -1) changes[idx] = data.change;
                
                renderChecklist(data.change);
                refreshIcons();

                // Trigger background list updates
                if (activeTab === "dashboard") renderDashboard();
                if (activeTab === "changes") renderChangesList();
              }
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
      const escAppStatus = escapeHTML(app.status);
      const appHtml = `
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px; background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm);">
          <span style="font-weight: 500; font-size: 0.85rem;">${escAppRole}</span>
          <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: ${iconColor}; font-weight: 600;">
            <i data-lucide="${iconName}" style="width: 14px; height: 14px;"></i>
            <span>${escAppStatus}</span>
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

    let buttonsHtml = "";

    switch (change.status) {
      case "Draft":
        buttonsHtml = `
          <button class="btn btn-primary w-full" id="btn-wf-review">
            <i data-lucide="send"></i> Submit for Review
          </button>
          <button class="btn btn-danger w-full" id="btn-wf-delete">
            <i data-lucide="trash-2"></i> Delete Request
          </button>
        `;
        break;
      case "Under Review":
        buttonsHtml = `
          <button class="btn btn-primary w-full" id="btn-wf-request-approval">
            <i data-lucide="check-square"></i> Submit for Approval
          </button>
          <button class="btn btn-secondary w-full" id="btn-wf-draft">
            <i data-lucide="rotate-ccw"></i> Pull to Draft
          </button>
        `;
        break;
      case "Pending Approval":
        buttonsHtml = `
          <button class="btn btn-primary w-full" id="btn-wf-approve" style="background: var(--grad-accent-green); box-shadow: 0 4px 15px rgba(16,185,129,0.35);">
            <i data-lucide="check"></i> Approve Change
          </button>
          <button class="btn btn-danger w-full" id="btn-wf-reject">
            <i data-lucide="x"></i> Reject Request
          </button>
        `;
        break;
      case "Approved":
        buttonsHtml = `
          <button class="btn btn-primary w-full" id="btn-wf-implement">
            <i data-lucide="play"></i> Start Maintenance (Prod)
          </button>
        `;
        break;
      case "Implementing":
        buttonsHtml = `
          <button class="btn btn-primary w-full" id="btn-wf-complete" style="background: var(--grad-accent-green); box-shadow: 0 4px 15px rgba(16,185,129,0.35);">
            <i data-lucide="check-circle-2"></i> Completed Successfully
          </button>
          <button class="btn btn-danger w-full" id="btn-wf-rollback">
            <i data-lucide="shield-alert"></i> Failed / Rollback
          </button>
        `;
        break;
      case "Completed":
      case "Rejected":
      case "Rolled Back":
        buttonsHtml = `
          <div style="padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--border-radius-sm); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
            This change request is closed. No actions can be taken.
          </div>
          <button class="btn btn-secondary w-full" id="btn-wf-reset" style="margin-top: 8px;">
            <i data-lucide="refresh-cw"></i> Reset to Draft for Testing
          </button>
        `;
        break;
    }

    container.innerHTML = buttonsHtml;

    // Attach workflow actions event listeners
    const addWfListener = (btnId, newStatus, actionText) => {
      const btn = document.getElementById(btnId);
      if (btn) {
        btn.addEventListener("click", async () => {
          try {
            const res = await fetch(`api.php?action=update_status&id=${change.id}`, {
              method: "PUT",
              headers: getAuthHeaders(),
              body: JSON.stringify({ status: newStatus, actionText: actionText })
            });

            if (res.ok) {
              const data = await res.json();
              
              // Update local array
              const idx = changes.findIndex(c => c.id === change.id);
              if (idx !== -1) changes[idx] = data.change;
              
              await openDetailModal(change.id); // Re-render detail view
              
              // Update background lists
              if (activeTab === "dashboard") renderDashboard();
              if (activeTab === "changes") renderChangesList();
              if (activeTab === "approvals") renderApprovalsList();
              if (activeTab === "calendar") renderCalendar();
            } else {
              const err = await res.json();
              alert(`Workflow Action Failed: ${err.error}`);
            }
          } catch (e) {
            alert("Error sending workflow transition request to backend.");
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

    // Delete handler
    const btnDelete = document.getElementById("btn-wf-delete");
    if (btnDelete) {
      btnDelete.addEventListener("click", async () => {
        if (confirm("Are you sure you want to delete this change request?")) {
          try {
            const res = await fetch(`api.php?action=delete_change&id=${change.id}`, {
              method: "DELETE",
              headers: getAuthHeaders()
            });

            if (res.ok) {
              closeDetailModal();
              await refreshData();
              if (activeTab === "dashboard") renderDashboard();
              if (activeTab === "changes") renderChangesList();
            } else {
              const err = await res.json();
              alert(`Deletion failed: ${err.error}`);
            }
          } catch (e) {
            alert("Failed to send delete request.");
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
      list.innerHTML = `<p style="font-size: 0.8rem; color: var(--text-sub); text-align: center; padding: 12px;">No comments posted yet.</p>`;
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
      const res = await fetch(`api.php?action=add_comment&id=${activeChangeId}`, {
        method: "POST",
        headers: getAuthHeaders(),
        body: JSON.stringify({ text })
      });

      if (res.ok) {
        commentInput.value = "";
        
        // Refresh detail view
        await openDetailModal(activeChangeId);
        
        // Update background lists
        await refreshData();
        if (activeTab === "dashboard") renderDashboard();
      } else {
        const err = await res.json();
        alert(`Failed to add comment: ${err.error}`);
      }
    } catch (err) {
      alert("Failed to submit comment.");
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
  
  // Set up avatar triggers once
  avatarContainer.addEventListener("click", () => {
    avatarInput.click();
  });
  
  avatarContainer.addEventListener("mouseenter", () => {
    avatarHoverOverlay.style.opacity = "1";
  });
  avatarContainer.addEventListener("mouseleave", () => {
    avatarHoverOverlay.style.opacity = "0";
  });

  let base64AvatarData = null;
  avatarInput.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 1048576) { // 1MB limit
      alert("Profile image size must be less than 1MB.");
      return;
    }
    const reader = new FileReader();
    reader.onload = (event) => {
      base64AvatarData = event.target.result;
      avatarImg.src = base64AvatarData;
      avatarImg.style.display = "block";
      avatarPlaceholder.style.display = "none";
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
    document.getElementById("profile-department").value = currentUser.department || "BT / IT";
    document.getElementById("profile-email").value = currentUser.email || "";
    document.getElementById("profile-phone").value = currentUser.phone || "";
    
    // Role badge
    document.getElementById("profile-role-badge").textContent = currentUser.role;
    
    // Avatar image setup
    if (currentUser.avatar) {
      avatarImg.src = currentUser.avatar;
      avatarImg.style.display = "block";
      avatarPlaceholder.style.display = "none";
      base64AvatarData = currentUser.avatar;
    } else {
      avatarImg.style.display = "none";
      avatarPlaceholder.style.display = "flex";
      const initials = currentUser.name.split(" ").map(n => n[0]).join("").substring(0, 3).toUpperCase();
      avatarPlaceholder.textContent = initials;
      base64AvatarData = null;
    }
  }

  // Fetch and draw directory
  async function fetchAdminUserDirectory() {
    try {
      const res = await fetch("api.php?action=get_users", { headers: getAuthHeaders() });
      if (res.ok) {
        const data = await res.json();
        renderAdminUserDirectoryTable(data.users);
      }
    } catch (err) {
      console.error("Failed to load user directory:", err);
    }

    try {
      const res = await fetch("api.php?action=get_registration_requests", { headers: getAuthHeaders() });
      if (res.ok) {
        const data = await res.json();
        renderPendingRegistrationRequests(data.requests);
      }
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

      const userInitials = escName.split(" ").map(n => n[0]).join("").substring(0, 2).toUpperCase();
      const isSelf = u.id === currentUser.id;
      
      const rowHtml = `
        <tr>
          <td style="padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background: var(--glass-bg-hover); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #c084fc; font-size: 0.8rem; flex-shrink: 0;">
              ${u.avatar ? `<img src="${u.avatar}" style="width:100%; height:100%; object-fit:cover;">` : userInitials}
            </div>
            <div>
              <strong style="color: var(--text-main); font-size: 0.9rem;">${escName}</strong><br>
              <span style="font-size: 0.75rem; color: var(--text-sub);">@${escUsername} ${isSelf ? '(You)' : ''}</span>
            </div>
          </td>
          <td style="padding: 12px 16px; font-size: 0.85rem;">
            <span style="color: var(--text-muted);">${escEmail || '<em style="color: var(--text-sub);">No email</em>'}</span><br>
            <span style="color: var(--text-sub); font-size: 0.75rem;">${escPhone || 'No phone'}</span>
          </td>
          <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--text-muted);">${escTitle}</td>
          <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--text-muted);">${escDepartment}</td>
          <td style="padding: 12px 16px;">
            <select class="form-control admin-user-role-select" data-user-id="${escId}" style="font-size: 0.8rem; padding: 6px 12px; height: auto;" ${isSelf ? 'disabled' : ''}>
              <option value="Requester" ${escRole === 'Requester' ? 'selected' : ''}>Requester</option>
              <option value="CAB Approver" ${escRole === 'CAB Approver' ? 'selected' : ''}>CAB Approver</option>
              <option value="Administrator" ${escRole === 'Administrator' ? 'selected' : ''}>Administrator</option>
            </select>
          </td>
        </tr>
      `;
      tbody.insertAdjacentHTML("beforeend", rowHtml);
    });

    // Bind role change dropdown triggers
    tbody.querySelectorAll(".admin-user-role-select").forEach(select => {
      select.addEventListener("change", async () => {
        const userId = parseInt(select.getAttribute("data-user-id"), 10);
        const newRole = select.value;
        
        try {
          const res = await fetch("api.php?action=change_user_role", {
            method: "POST",
            headers: getAuthHeaders(),
            body: JSON.stringify({ userId, role: newRole })
          });

          if (res.ok) {
            await fetchAdminUserDirectory();
            await refreshData();
            updateApprovalBadge();
          } else {
            const err = await res.json();
            alert(`Failed to update role: ${err.error}`);
            // Reload database to reset select value
            await fetchAdminUserDirectory();
          }
        } catch (e) {
          alert("Error sending request to server.");
          await fetchAdminUserDirectory();
        }
      });
    });
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
    countBadge.textContent = `${pendingRequests.length} ${translations[currentLang]['status_pending']}`;
    
    pendingRequests.forEach(req => {
      const escName = escapeHTML(req.name);
      const escUsername = escapeHTML(req.username);
      const escTitle = escapeHTML(req.title);
      const escDept = escapeHTML(req.department || 'BT / IT');
      const escRole = escapeHTML(req.role);
      const escDate = escapeHTML(req.request_date || '');
      const escId = escapeHTML(String(req.id));
      
      const userInitials = escName.split(" ").map(n => n[0]).join("").substring(0, 2).toUpperCase();
      
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
            <span class="badge" style="background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); color: #c084fc; font-size: 0.75rem; padding: 2px 8px;">${escRole}</span>
          </td>
          <td style="padding: 12px 16px; font-size: 0.85rem; color: var(--text-sub);">${escDate}</td>
          <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
            <button class="btn btn-primary btn-sm btn-approve-reg" data-request-id="${escId}" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px; background: #10b981; border-color: #10b981; cursor: pointer; color: white;">
              <i data-lucide="check" style="width:14px; height:14px;"></i> ${translations[currentLang]['action_approve']}
            </button>
            <button class="btn btn-secondary btn-sm btn-reject-reg" data-request-id="${escId}" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #ef4444; margin-left: 6px; cursor: pointer;">
              <i data-lucide="x" style="width:14px; height:14px;"></i> ${translations[currentLang]['action_reject']}
            </button>
          </td>
        </tr>
      `;
      
      tbody.insertAdjacentHTML("beforeend", rowHtml);
    });
    
    // Bind approve/reject triggers
    tbody.querySelectorAll(".btn-approve-reg").forEach(btn => {
      btn.addEventListener("click", async () => {
        const requestId = parseFloat(btn.getAttribute("data-request-id"));
        if (confirm(translations[currentLang]['confirm_approve_reg'])) {
          try {
            const res = await fetch("api.php?action=approve_registration", {
              method: "POST",
              headers: getAuthHeaders(),
              body: JSON.stringify({ requestId })
            });
            if (res.ok) {
              await fetchAdminUserDirectory();
            } else {
              const err = await res.json();
              alert(err.error);
            }
          } catch (e) {
            alert(translations[currentLang]['network_error']);
          }
        }
      });
    });
    
    tbody.querySelectorAll(".btn-reject-reg").forEach(btn => {
      btn.addEventListener("click", async () => {
        const requestId = parseFloat(btn.getAttribute("data-request-id"));
        if (confirm(translations[currentLang]['confirm_reject_reg'])) {
          try {
            const res = await fetch("api.php?action=reject_registration", {
              method: "POST",
              headers: getAuthHeaders(),
              body: JSON.stringify({ requestId })
            });
            if (res.ok) {
              await fetchAdminUserDirectory();
            } else {
              const err = await res.json();
              alert(err.error);
            }
          } catch (e) {
            alert(translations[currentLang]['network_error']);
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
    const newPassword = document.getElementById("profile-new-password").value;
    const confirmPassword = document.getElementById("profile-confirm-password").value;
    const statusMsg = document.getElementById("profile-status-message");

    statusMsg.style.display = "none";

    // Validate Passwords match if typed
    if (newPassword && newPassword !== confirmPassword) {
      statusMsg.textContent = "New passwords do not match.";
      statusMsg.style.color = "#f87171";
      statusMsg.style.display = "block";
      return;
    }

    try {
      const res = await fetch("api.php?action=update_profile", {
        method: "POST",
        headers: getAuthHeaders(),
        body: JSON.stringify({
          name, title, department, email, phone,
          avatar: base64AvatarData,
          newPassword
        })
      });

      const data = await res.json();
      if (res.ok) {
        localStorage.setItem("hopper_token", data.token);
        currentUser = data.user;
        
        // Show success msg
        statusMsg.textContent = "Profile updated successfully!";
        statusMsg.style.color = "#10b981";
        statusMsg.style.display = "block";
        
        // Re-render
        updateUserProfileSidebar();
        renderProfileTab();
        
        // Fade out success message after 3 seconds
        setTimeout(() => {
          statusMsg.style.display = "none";
        }, 3000);
      } else {
        statusMsg.textContent = data.error || "Failed to update profile.";
        statusMsg.style.color = "#f87171";
        statusMsg.style.display = "block";
      }
    } catch (err) {
      statusMsg.textContent = "Failed to communicate with server.";
      statusMsg.style.color = "#f87171";
      statusMsg.style.display = "block";
    }
  });

  // --- RENDERER: SYSTEM SETTINGS ---
  function renderSettingsTab() {
    const listDepts = document.getElementById("settings-depts-list");
    const listCats = document.getElementById("settings-cats-list");
    
    // Render Departments
    listDepts.innerHTML = "";
    if (departments.length === 0) {
      listDepts.innerHTML = `<p style="font-size: 0.8rem; color: var(--text-sub); text-align: center; padding: 12px;">No departments found.</p>`;
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
          if (confirm(`Are you sure you want to delete the department "${dept}"?`)) {
            try {
              const res = await fetch("api.php?action=delete_department", {
                method: "POST",
                headers: getAuthHeaders(),
                body: JSON.stringify({ name: dept })
              });
              if (res.ok) {
                await fetchDepartments();
                renderSettingsTab();
              } else {
                const err = await res.json();
                alert(`Error deleting department: ${err.error}`);
              }
            } catch (e) {
              alert("Network error.");
            }
          }
        });
      });
    }

    // Render Categories
    listCats.innerHTML = "";
    if (categories.length === 0) {
      listCats.innerHTML = `<p style="font-size: 0.8rem; color: var(--text-sub); text-align: center; padding: 12px;">No categories found.</p>`;
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
          if (confirm(`Are you sure you want to delete the category "${cat}"?`)) {
            try {
              const res = await fetch("api.php?action=delete_category", {
                method: "POST",
                headers: getAuthHeaders(),
                body: JSON.stringify({ name: cat })
              });
              if (res.ok) {
                await fetchCategories();
                renderSettingsTab();
              } else {
                const err = await res.json();
                alert(`Error deleting category: ${err.error}`);
              }
            } catch (e) {
              alert("Network error.");
            }
          }
        });
      });
    }

    // Show/hide Admin management forms
    const addDeptWrapper = document.getElementById("add-dept-wrapper");
    const addCatWrapper = document.getElementById("add-cat-wrapper");
    const settingsAdminBadge = document.getElementById("settings-admin-badge");

    if (currentUser && currentUser.role === 'Administrator') {
      if (addDeptWrapper) addDeptWrapper.style.display = "flex";
      if (addCatWrapper) addCatWrapper.style.display = "flex";
      if (settingsAdminBadge) {
        settingsAdminBadge.textContent = "System Administration";
        settingsAdminBadge.className = "badge badge-risk high";
      }
    } else {
      if (addDeptWrapper) addDeptWrapper.style.display = "none";
      if (addCatWrapper) addCatWrapper.style.display = "none";
      if (settingsAdminBadge) {
        settingsAdminBadge.textContent = "View Only";
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
        const res = await fetch("api.php?action=add_department", {
          method: "POST",
          headers: getAuthHeaders(),
          body: JSON.stringify({ name })
        });
        if (res.ok) {
          input.value = "";
          await fetchDepartments();
          renderSettingsTab();
        } else {
          const err = await res.json();
          alert(`Error adding department: ${err.error}`);
        }
      } catch (e) {
        alert("Network error.");
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
        const res = await fetch("api.php?action=add_category", {
          method: "POST",
          headers: getAuthHeaders(),
          body: JSON.stringify({ name })
        });
        if (res.ok) {
          input.value = "";
          await fetchCategories();
          renderSettingsTab();
        } else {
          const err = await res.json();
          alert(`Error adding category: ${err.error}`);
        }
      } catch (e) {
        alert("Network error.");
      }
    });
  }

  // --- TRANSLATION ENGINE ---
  function applyLanguage(lang) {
    currentLang = lang;
    localStorage.setItem("hopper_lang", lang);

    // 1. Static translation selectors
    for (const selector in translationSelectors) {
      const elements = document.querySelectorAll(selector);
      const rule = translationSelectors[selector];
      
      elements.forEach(el => {
        if (typeof rule === "string") {
          const text = translations[lang][rule];
          if (text) {
            if (el.children.length === 0) {
              el.textContent = text;
            } else {
              let textNodeFound = false;
              el.childNodes.forEach(child => {
                if (child.nodeType === Node.TEXT_NODE && child.nodeValue.trim().length > 0) {
                  child.nodeValue = text;
                  textNodeFound = true;
                }
              });
              if (!textNodeFound) {
                // If text node wasn't found directly, prepend text content to preserve badges
                el.innerHTML = text + el.innerHTML;
              }
            }
          }
        } else if (typeof rule === "object") {
          const text = translations[lang][rule.key];
          if (text) {
            el.setAttribute(rule.attr, text);
          }
        }
      });
    }

    // 2. Extra data-i18n elements
    const dataI18nEls = document.querySelectorAll("[data-i18n]");
    dataI18nEls.forEach(el => {
      const key = el.getAttribute("data-i18n");
      const text = translations[lang][key];
      if (text) el.textContent = text;
    });

    // 3. Highlight language buttons
    const btnEn = document.getElementById("lang-btn-en");
    const btnTr = document.getElementById("lang-btn-tr");
    if (btnEn && btnTr) {
      if (lang === "en") {
        btnEn.style.opacity = "1";
        btnEn.style.color = "#c084fc";
        btnTr.style.opacity = "0.5";
        btnTr.style.color = "";
      } else {
        btnEn.style.opacity = "0.5";
        btnEn.style.color = "";
        btnTr.style.opacity = "1";
        btnTr.style.color = "#c084fc";
      }
    }

    // 4. Reload page title inside browser tab
    document.title = lang === "tr" ? "Hopper - Değişiklik Yönetim Sistemi" : "Hopper - Change Management System";

    // 5. Re-render dynamic components if tab active
    if (activeTab === "dashboard") renderDashboard();
    if (activeTab === "changes") renderChangesList();
    if (activeTab === "approvals") renderApprovalsList();
    if (activeTab === "calendar") renderCalendar();
    if (activeTab === "profile") renderProfileTab();
    if (activeTab === "users") fetchAdminUserDirectory();
    if (activeTab === "settings") renderSettingsTab();

    refreshIcons();
  }

  // Bind Language switch triggers
  const btnEn = document.getElementById("lang-btn-en");
  const btnTr = document.getElementById("lang-btn-tr");
  if (btnEn && btnTr) {
    btnEn.addEventListener("click", (e) => {
      e.stopPropagation();
      applyLanguage("en");
    });
    btnTr.addEventListener("click", (e) => {
      e.stopPropagation();
      applyLanguage("tr");
    });
  }

  // --- INITIAL APPLICATION START ---
  fetchCategories();
  fetchDepartments();
  checkAuth();
  applyLanguage(currentLang);
});
