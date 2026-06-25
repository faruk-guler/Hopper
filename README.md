# Hopper (v1.2.2)
![Hopper Logo](images/hopper.png)

**Hopper** is a premium, lightweight, and modern Single Page Application (SPA) designed to coordinate, track, and review software and infrastructure change requests. It implements industry-standard change management best practices (ITIL/COBIT frameworks) within a clean, flat, and professional classic Bootstrap layout.

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
* **User Directory:** An administrative table displaying users, roles, titles, and contact information.

---

## 🎨 Design System & Aesthetics

* **Classic Bootstrap Theme:** Solid, flat card layouts, clear boundary borders, and clear semantic accents (Primary Blue, Success Green, Warning Yellow, Danger Red).
* **Typography:** Clean Google Font—**Nunito** is configured globally for readable data tables, dashboard headers, and forms.
* **Vector Icons:** Dynamic loading of icons via **Lucide Icons**.

---

## 🛠️ Technologies Used

* **Frontend:** HTML5, CSS3 (Vanilla CSS variables), Vanilla ES6 JavaScript (zero dependency client-side SPA routing).
* **Backend:** PHP (PDO database connection layer, session management, JWT authentication).
* **Database:** MySQL.

---

## 💻 How to Run Locally (XAMPP Environment)

Since Hopper runs on a PHP and MySQL backend, we recommend setting it up on a local XAMPP web server stack:

### Step 1: Place files in the Web Root
Copy or extract this project folder inside your XAMPP installation directory's web root:
- On Windows, this is typically: `C:\xampp\htdocs\`

### Step 2: Start the Web Server & Database
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 3: Run the Application
Open your web browser and navigate to:
- `http://localhost/`

### Step 4: Automatic Database Setup
On the first launch:
1. `api.php` will automatically connect to your local MySQL instance (using the default root configuration with no password).
2. It will run `CREATE DATABASE IF NOT EXISTS db_admin` for you.
3. It will construct the tables automatically using `schema.sql`.
4. It will import the default seeding data and user records, allowing you to sign in immediately using:
   - **Username:** `admin`
   - **Password:** `admin`
