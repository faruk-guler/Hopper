# Hopper - Change Management System
<img src="images/hopper.png" alt="alt text" width="430" height="330">

**Hopper** is a premium, lightweight, and modern Single Page Application (SPA) designed to coordinate, track, and review software and infrastructure change requests. It implements industry-standard change management best practices (ITIL/COBIT frameworks) within a gorgeous dark-mode Glassmorphism interface.

Developed by **Faruk Güler**.

---

## 🚀 Key Features

* **Interactive Dashboard:** High-level KPIs (Total changes, pending approvals, active implementations, and project success rates), active change request lists, and an activity audit log.
* **Granular Change Creation:** Submit new changes detailing the request title, description, impact analysis, rollback (recovery) steps, and step-by-step implementation tasks.
* **Dynamic Workflows:** Navigate change requests through a complete status lifecycle:
  * `Draft` ➔ `Under Review` ➔ `Pending Approval` ➔ `Approved` ➔ `Implementing` ➔ `Completed` / `Rolled Back` / `Rejected`
* **Workflow & CAB Approvals:** Managers can approve or reject incoming requests. Action history logs who did what in real-time.
* **Interactive Checklist:** Implementation owners can check tasks off in the details modal, updating the progress bar dynamically.
* **Change Calendar:** A monthly grid calendar displaying scheduled changes color-coded by their risk levels to avoid schedule collisions.
* **About developer panel:** Direct links to the developer's GitHub and personal website.

---

## 🎨 Design System & Aesthetics

* **Glassmorphism Theme:** Rich translucent cards using HSL-based color tokens, fine borders, and blurred background filters.
* **Typography:** Premium Google Fonts—**Outfit** for headings and **Inter** for readability in body text.
* **Micro-Animations:** Seamless tab transitions, hover glows, pulsing notification badges, and progress bar animations.
* **Vector Icons:** Dynamic loading of icons via **Lucide Icons**.

---

## 🛠️ Technologies Used

* **Core:** HTML5, CSS3 (Vanilla Custom Properties), Vanilla ES6 JavaScript.
* **Icons:** Lucide Icons.
* **Fonts:** Google Fonts (Inter & Outfit).
* **Storage:** Client-side persistence using browser `localStorage` (with automatic schema versioning to avoid cache issues).

---

## 💻 How to Run Locally

Since Hopper relies on a PHP backend (`api.php` and `index.php`), you need to run it with a PHP-capable web server.

### Method 1: Using PHP's Built-in Development Server (Recommended)
Serve the application locally to run the database backend and allow icons to load correctly:
```bash
# Start the PHP development server in the project folder
php -S localhost:8000
```
Then navigate to `http://localhost:8000/index.php` in your web browser.

---
