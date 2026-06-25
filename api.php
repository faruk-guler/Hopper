<?php
// Hopper Backend API Router & Controller - Zero Dependency PHP Version
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Authorization');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Fallback for getallheaders() if not exists
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

// Load configurations
$configPath = __DIR__ . '/config.php';
$config = ['jwtSecret' => 'hopper-default-secret-key-123456', 'webhookUrl' => ''];
if (file_exists($configPath)) {
    $rawConfig = file_get_contents($configPath);
    $jsonStart = strpos($rawConfig, '?>');
    if ($jsonStart !== false) {
        $rawConfig = substr($rawConfig, $jsonStart + 2);
    }
    $decodedConfig = json_decode(trim($rawConfig), true);
    if ($decodedConfig) {
        $config = array_merge($config, $decodedConfig);
    }
}
define('DEFAULT_JWT_SECRET', $config['jwtSecret']);
define('WEBHOOK_URL', $config['webhookUrl']);

// Global lock file pointer
$globalLockFp = null;

function acquireDbLock() {
    global $globalLockFp;
    if ($globalLockFp === null) {
        $lockFile = __DIR__ . '/db.lock';
        $globalLockFp = fopen($lockFile, 'c+');
        if ($globalLockFp) {
            flock($globalLockFp, LOCK_EX);
            register_shutdown_function('releaseDbLock');
        }
    }
}

function releaseDbLock() {
    global $globalLockFp;
    if ($globalLockFp !== null) {
        flock($globalLockFp, LOCK_UN);
        fclose($globalLockFp);
        $globalLockFp = null;
    }
}

function getJwtSecret() {
    static $secret = null;
    if ($secret === null) {
        try {
            $conn = getDbConnection();
            $conn->exec("
                CREATE TABLE IF NOT EXISTS system_settings (
                    setting_key VARCHAR(100) PRIMARY KEY,
                    setting_value TEXT
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            
            $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'jwt_secret'");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            
            if ($val) {
                $secret = $val;
            } else {
                $secret = bin2hex(random_bytes(32));
                $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('jwt_secret', ?)");
                $stmt->execute([$secret]);
            }
        } catch (Exception $e) {
            $secret = defined('DEFAULT_JWT_SECRET') ? DEFAULT_JWT_SECRET : 'hopper-fallback-secret-key-123456';
        }
    }
    return $secret;
}

$dbHost = $config['dbHost'] ?? '127.0.0.1';
$dbName = $config['dbName'] ?? 'db_admin';
$dbUser = $config['dbUser'] ?? 'root';
$dbPass = $config['dbPass'] ?? '';

define('OLD_JSON_DB_PATH', __DIR__ . '/data.php');

function getDbConnection() {
    static $conn = null;
    if ($conn === null) {
        global $dbHost, $dbName, $dbUser, $dbPass;
        try {
            // First connect to MySQL without selecting db to ensure it exists or create it
            $conn = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if not exists
            $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Reconnect with dbname
            $conn = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Check if database tables are empty/uninitialized
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (empty($tables)) {
                initializeMysqlDatabase($conn);
            } else {
                // Ensure change_revisions schema is up-to-date with new columns
                try {
                    $check = $conn->query("SHOW COLUMNS FROM `change_revisions` LIKE 'editor'")->fetch();
                    if (!$check) {
                        $conn->exec("DROP TABLE IF EXISTS `change_revisions`");
                        $conn->exec("
                            CREATE TABLE IF NOT EXISTS change_revisions (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                change_id VARCHAR(50) NOT NULL,
                                editor VARCHAR(255) NOT NULL,
                                date VARCHAR(50) NOT NULL,
                                changed_fields TEXT NOT NULL,
                                FOREIGN KEY (change_id) REFERENCES changes(id) ON DELETE CASCADE
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                        ");
                    }
                } catch (PDOException $ex) {
                    // Fail silently to prevent crash if table does not exist
                }

                // Ensure ad_settings table exists and is seeded
                try {
                    $conn->exec("
                        CREATE TABLE IF NOT EXISTS ad_settings (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            adEnabled TINYINT DEFAULT 0,
                            adServer VARCHAR(255) DEFAULT '',
                            adPort INT DEFAULT 389,
                            adBaseDn VARCHAR(255) DEFAULT '',
                            adDomain VARCHAR(255) DEFAULT ''
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    ");
                    $count = $conn->query("SELECT COUNT(*) FROM ad_settings")->fetchColumn();
                    if ($count == 0) {
                        $conn->exec("INSERT INTO ad_settings (id, adEnabled, adServer, adPort, adBaseDn, adDomain) VALUES (1, 0, '', 389, '', '')");
                    }
                } catch (PDOException $ex) {
                    // Fail silently
                }
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'MySQL connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $conn;
}

function initializeMysqlDatabase($conn) {
    $schemaPath = __DIR__ . '/schema.sql';
    if (file_exists($schemaPath)) {
        $sql = file_get_contents($schemaPath);
        // Remove comments
        $sql = preg_replace('/--.*\n/', '', $sql);
        // Split queries by semicolon and execute
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $conn->exec($query);
            }
        }
    }
    migrateFromJsonToMysql($conn);
}

function seedDefaultMysqlDb($conn) {
    // Seed categories
    $categories = [
        "Software Development",
        "Database Management",
        "Network & Security",
        "System & Server",
        "Cloud Infrastructure",
        "Hardware & Infrastructure"
    ];
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    foreach ($categories as $cat) {
        $stmt->execute([$cat]);
    }
    
    // Seed departments
    $departments = [
        'Management', 'IT Operations', 'Human Resources', 'Accounting', 'Sales',
        'Marketing', 'R&D', 'Logistics', 'Warehouse', 'Security',
        'Technical Service', 'Quality Control', 'Customer Services', 'Training', 'Purchasing', 'Finance & Accounting'
    ];
    $stmt = $conn->prepare("INSERT IGNORE INTO departments (name) VALUES (?)");
    foreach ($departments as $dept) {
        $stmt->execute([$dept]);
    }
    
    // Seed groups
    $groups = [
        "Software Development",
        "Database Administration",
        "Network & Security",
        "System Administration",
        "DevOps"
    ];
    $stmt = $conn->prepare("INSERT IGNORE INTO groups (name) VALUES (?)");
    foreach ($groups as $grp) {
        $stmt->execute([$grp]);
    }
    
    // Seed users
    $users = [
        [
            'id' => 1,
            'username' => 'admin',
            'password_hash' => password_hash('admin', PASSWORD_BCRYPT),
            'name' => 'faruk guler',
            'role' => 'Administrator',
            'title' => 'SysAdmin',
            'avatar' => '',
            'email' => 'admin@hopper.local',
            'phone' => '',
            'department' => 'Management',
            'status' => 'Working',
            'group' => 'System Administration'
        ],
        [
            'id' => 2,
            'username' => 'approver',
            'password_hash' => password_hash('admin', PASSWORD_BCRYPT),
            'name' => 'CAB Approver',
            'role' => 'CAB Approver',
            'title' => 'Change Advisory Board',
            'avatar' => '',
            'email' => 'approver@hopper.local',
            'phone' => '',
            'department' => 'IT Operations',
            'status' => '',
            'group' => 'Network & Security'
        ],
        [
            'id' => 3,
            'username' => 'requester',
            'password_hash' => password_hash('admin', PASSWORD_BCRYPT),
            'name' => 'Developer Alice',
            'role' => 'Requester',
            'title' => 'Software Engineer',
            'avatar' => '',
            'email' => 'alice@hopper.local',
            'phone' => '',
            'department' => 'Technical Service',
            'status' => '',
            'group' => 'Software Development'
        ]
    ];
    $stmt = $conn->prepare("INSERT IGNORE INTO users (id, username, password_hash, name, role, title, avatar, email, phone, department, status, `group`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmt->execute([
            $u['id'],
            $u['username'],
            $u['password_hash'],
            $u['name'],
            $u['role'],
            $u['title'],
            $u['avatar'],
            $u['email'],
            $u['phone'],
            $u['department'],
            $u['status'],
            $u['group']
        ]);
    }
    
    // Seed notification settings
    $stmt = $conn->prepare("INSERT INTO notification_settings (webhookUrl, notifyOnCreate, notifyOnStatusChange, notifyOnHighRiskOnly) VALUES (?, ?, ?, ?)");
    $stmt->execute(['', 1, 1, 0]);

    // Seed ad settings
    $stmtAd = $conn->prepare("INSERT INTO ad_settings (id, adEnabled, adServer, adPort, adBaseDn, adDomain) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtAd->execute([1, 0, '', 389, '', '']);
}

function migrateFromJsonToMysql($conn) {
    if (!file_exists(OLD_JSON_DB_PATH)) {
        seedDefaultMysqlDb($conn);
        return;
    }
    
    $raw = @file_get_contents(OLD_JSON_DB_PATH);
    if ($raw === false) {
        seedDefaultMysqlDb($conn);
        return;
    }
    
    $jsonStart = strpos($raw, '?>');
    if ($jsonStart !== false) {
        $raw = substr($raw, $jsonStart + 2);
    }
    $db = json_decode(trim($raw), true);
    if (!$db) {
        seedDefaultMysqlDb($conn);
        return;
    }
    
    // 1. Migrate categories
    if (isset($db['categories'])) {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        foreach ($db['categories'] as $cat) {
            $stmt->execute([$cat]);
        }
    }
    
    // 2. Migrate departments
    if (isset($db['departments'])) {
        $stmt = $conn->prepare("INSERT IGNORE INTO departments (name) VALUES (?)");
        foreach ($db['departments'] as $dept) {
            $stmt->execute([$dept]);
        }
    }
    
    // 3. Migrate groups
    if (isset($db['groups'])) {
        $stmt = $conn->prepare("INSERT IGNORE INTO groups (name) VALUES (?)");
        foreach ($db['groups'] as $grp) {
            $stmt->execute([$grp]);
        }
    }
    
    // 4. Migrate users
    if (isset($db['users'])) {
        $stmt = $conn->prepare("INSERT IGNORE INTO users (id, username, password_hash, name, role, title, avatar, email, phone, department, status, `group`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($db['users'] as $u) {
            $stmt->execute([
                $u['id'],
                $u['username'],
                $u['password_hash'],
                $u['name'],
                $u['role'],
                $u['title'] ?? '',
                $u['avatar'] ?? '',
                $u['email'] ?? '',
                $u['phone'] ?? '',
                $u['department'] ?? '',
                $u['status'] ?? '',
                $u['group'] ?? ''
            ]);
        }
    }
    
    // 5. Migrate registration_requests
    if (isset($db['registration_requests'])) {
        $stmt = $conn->prepare("INSERT IGNORE INTO registration_requests (id, username, password_hash, name, role, title, department, email, phone, avatar, status, request_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($db['registration_requests'] as $req) {
            $stmt->execute([
                $req['id'],
                $req['username'],
                $req['password_hash'],
                $req['name'],
                $req['role'],
                $req['title'] ?? '',
                $req['department'] ?? '',
                $req['email'] ?? '',
                $req['phone'] ?? '',
                $req['avatar'] ?? '',
                $req['status'] ?? 'Pending',
                $req['request_date'] ?? date('Y-m-d H:i')
            ]);
        }
    }
    
    // 6. Migrate changes, tasks, approvals, comments, and revisions
    if (isset($db['changes'])) {
        $stmtChange = $conn->prepare("INSERT IGNORE INTO changes (id, title, description, requester, requesterTitle, owner, ownerTitle, category, risk, status, targetDate, impact, rollbackPlan, progress, assignedGroup, ownerUsername, requesterUsername) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtTask = $conn->prepare("INSERT INTO change_tasks (change_id, task_number, text, completed) VALUES (?, ?, ?, ?)");
        $stmtApproval = $conn->prepare("INSERT INTO change_approvals (change_id, role, status, date) VALUES (?, ?, ?, ?)");
        $stmtComment = $conn->prepare("INSERT INTO change_comments (change_id, user, userTitle, text, date) VALUES (?, ?, ?, ?, ?)");
        $stmtRevision = $conn->prepare("INSERT INTO change_revisions (change_id, rev_id, title, description, category, risk, targetDate, impact, rollbackPlan, assignedGroup, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($db['changes'] as $c) {
            $stmtChange->execute([
                $c['id'],
                $c['title'],
                $c['description'] ?? '',
                $c['requester'] ?? '',
                $c['requesterTitle'] ?? '',
                $c['owner'] ?? '',
                $c['ownerTitle'] ?? '',
                $c['category'] ?? '',
                $c['risk'] ?? '',
                $c['status'] ?? '',
                $c['targetDate'] ?? '',
                $c['impact'] ?? '',
                $c['rollbackPlan'] ?? '',
                $c['progress'] ?? 0,
                $c['assignedGroup'] ?? '',
                $c['ownerUsername'] ?? '',
                $c['requesterUsername'] ?? ''
            ]);
            
            if (isset($c['tasks']) && is_array($c['tasks'])) {
                foreach ($c['tasks'] as $idx => $t) {
                    $stmtTask->execute([
                        $c['id'],
                        $t['id'] ?? $idx,
                        $t['text'],
                        !empty($t['completed']) ? 1 : 0
                    ]);
                }
            }
            
            if (isset($c['approvals']) && is_array($c['approvals'])) {
                foreach ($c['approvals'] as $app) {
                    $stmtApproval->execute([
                        $c['id'],
                        $app['role'],
                        $app['status'] ?? 'Pending',
                        $app['date'] ?? ''
                    ]);
                }
            }
            
            if (isset($c['comments']) && is_array($c['comments'])) {
                foreach ($c['comments'] as $comm) {
                    $stmtComment->execute([
                        $c['id'],
                        $comm['user'],
                        $comm['userTitle'] ?? '',
                        $comm['text'],
                        $comm['date']
                    ]);
                }
            }
            
            if (isset($c['revisions']) && is_array($c['revisions'])) {
                foreach ($c['revisions'] as $rev) {
                    $stmtRevision->execute([
                        $c['id'],
                        $rev['rev_id'] ?? $rev['id'] ?? '',
                        $rev['title'] ?? '',
                        $rev['description'] ?? '',
                        $rev['category'] ?? '',
                        $rev['risk'] ?? '',
                        $rev['targetDate'] ?? '',
                        $rev['impact'] ?? '',
                        $rev['rollbackPlan'] ?? '',
                        $rev['assignedGroup'] ?? '',
                        $rev['date']
                    ]);
                }
            }
        }
    }
    
    // 7. Migrate activities
    if (isset($db['activities'])) {
        $stmt = $conn->prepare("INSERT IGNORE INTO activities (id, user, action, target, date) VALUES (?, ?, ?, ?, ?)");
        foreach ($db['activities'] as $act) {
            $stmt->execute([
                $act['id'],
                $act['user'],
                $act['action'],
                $act['target'] ?? '',
                $act['date']
            ]);
        }
    }
    
    // 8. Migrate notification settings
    if (isset($db['notification_settings'])) {
        $ns = $db['notification_settings'];
        $stmt = $conn->prepare("INSERT INTO notification_settings (webhookUrl, notifyOnCreate, notifyOnStatusChange, notifyOnHighRiskOnly) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $ns['webhookUrl'] ?? '',
            isset($ns['notifyOnCreate']) ? (!empty($ns['notifyOnCreate']) ? 1 : 0) : 1,
            isset($ns['notifyOnStatusChange']) ? (!empty($ns['notifyOnStatusChange']) ? 1 : 0) : 1,
            isset($ns['notifyOnHighRiskOnly']) ? (!empty($ns['notifyOnHighRiskOnly']) ? 1 : 0) : 0
        ]);
    }
}

// --- DATABASE HELPER FUNCTIONS ---
function readDb() {
    acquireDbLock();
    $conn = getDbConnection();
    $db = [];
    
    // 1. Users
    $stmt = $conn->query("SELECT * FROM users");
    $db['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    for ($i = 0; $i < count($db['users']); $i++) {
        $db['users'][$i]['id'] = (int)$db['users'][$i]['id'];
    }
    
    // 2. Registration Requests
    $stmt = $conn->query("SELECT * FROM registration_requests");
    $db['registration_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    for ($i = 0; $i < count($db['registration_requests']); $i++) {
        $db['registration_requests'][$i]['id'] = (float)$db['registration_requests'][$i]['id'];
    }
    
    // 3. Categories
    $stmt = $conn->query("SELECT name FROM categories");
    $db['categories'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 4. Departments
    $stmt = $conn->query("SELECT name FROM departments");
    $db['departments'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 5. Groups
    $stmt = $conn->query("SELECT name FROM groups");
    $db['groups'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 6. Notification Settings
    $stmt = $conn->query("SELECT * FROM notification_settings LIMIT 1");
    $ns = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($ns) {
        $db['notification_settings'] = [
            'webhookUrl' => $ns['webhookUrl'],
            'notifyOnCreate' => (bool)$ns['notifyOnCreate'],
            'notifyOnStatusChange' => (bool)$ns['notifyOnStatusChange'],
            'notifyOnHighRiskOnly' => (bool)$ns['notifyOnHighRiskOnly']
        ];
    } else {
        $db['notification_settings'] = [
            'webhookUrl' => '',
            'notifyOnCreate' => true,
            'notifyOnStatusChange' => true,
            'notifyOnHighRiskOnly' => false
        ];
    }

    // 6a. Active Directory Settings
    $stmt = $conn->query("SELECT * FROM ad_settings LIMIT 1");
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($ad) {
        $db['ad_settings'] = [
            'adEnabled' => (bool)$ad['adEnabled'],
            'adServer' => $ad['adServer'],
            'adPort' => (int)$ad['adPort'],
            'adBaseDn' => $ad['adBaseDn'],
            'adDomain' => $ad['adDomain']
        ];
    } else {
        $db['ad_settings'] = [
            'adEnabled' => false,
            'adServer' => '',
            'adPort' => 389,
            'adBaseDn' => '',
            'adDomain' => ''
        ];
    }
    
    // 7. Activities
    $stmt = $conn->query("SELECT * FROM activities ORDER BY id DESC");
    $db['activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    for ($i = 0; $i < count($db['activities']); $i++) {
        $db['activities'][$i]['id'] = (float)$db['activities'][$i]['id'];
    }
    
    // 8. Changes (with relational sub-arrays)
    $stmt = $conn->query("SELECT * FROM changes");
    $changes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmtTasks = $conn->prepare("SELECT id as db_id, task_number, text, completed FROM change_tasks WHERE change_id = ? ORDER BY task_number ASC");
    $stmtApprovals = $conn->prepare("SELECT id, role, status, date FROM change_approvals WHERE change_id = ?");
    $stmtComments = $conn->prepare("SELECT id, user, userTitle, text, date FROM change_comments WHERE change_id = ?");
    $stmtRevisions = $conn->prepare("SELECT id, editor, date, changed_fields as `changes` FROM change_revisions WHERE change_id = ? ORDER BY id DESC");
    
    for ($i = 0; $i < count($changes); $i++) {
        $chgId = $changes[$i]['id'];
        
        $stmtTasks->execute([$chgId]);
        $changes[$i]['tasks'] = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);
        for ($j = 0; $j < count($changes[$i]['tasks']); $j++) {
            $changes[$i]['tasks'][$j]['db_id'] = (int)$changes[$i]['tasks'][$j]['db_id'];
            $changes[$i]['tasks'][$j]['id'] = (int)$changes[$i]['tasks'][$j]['task_number'];
            $changes[$i]['tasks'][$j]['completed'] = (bool)$changes[$i]['tasks'][$j]['completed'];
        }
        
        $stmtApprovals->execute([$chgId]);
        $changes[$i]['approvals'] = $stmtApprovals->fetchAll(PDO::FETCH_ASSOC);
        for ($j = 0; $j < count($changes[$i]['approvals']); $j++) {
            $changes[$i]['approvals'][$j]['id'] = (int)$changes[$i]['approvals'][$j]['id'];
        }
        
        $stmtComments->execute([$chgId]);
        $changes[$i]['comments'] = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
        for ($j = 0; $j < count($changes[$i]['comments']); $j++) {
            $changes[$i]['comments'][$j]['id'] = (int)$changes[$i]['comments'][$j]['id'];
            $changes[$i]['comments'][$j]['author'] = $changes[$i]['comments'][$j]['user'];
        }
        
        $stmtRevisions->execute([$chgId]);
        $revs = $stmtRevisions->fetchAll(PDO::FETCH_ASSOC);
        for ($j = 0; $j < count($revs); $j++) {
            $revs[$j]['id'] = (int)$revs[$j]['id'];
            $revs[$j]['changes'] = !empty($revs[$j]['changes']) ? explode(',', $revs[$j]['changes']) : [];
        }
        $changes[$i]['revisions'] = $revs;
        
        $changes[$i]['progress'] = (int)$changes[$i]['progress'];
    }
    $db['changes'] = $changes;
    
    return $db;
}

function writeDb($db) {
    $conn = getDbConnection();
    
    try {
        $conn->beginTransaction();
        
        // 1. Sync Categories
        if (isset($db['categories'])) {
            $conn->exec("DELETE FROM categories");
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            foreach ($db['categories'] as $cat) {
                $stmt->execute([$cat]);
            }
        }
        
        // 2. Sync Departments
        if (isset($db['departments'])) {
            $conn->exec("DELETE FROM departments");
            $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
            foreach ($db['departments'] as $dept) {
                $stmt->execute([$dept]);
            }
        }
        
        // 3. Sync Groups
        if (isset($db['groups'])) {
            $conn->exec("DELETE FROM groups");
            $stmt = $conn->prepare("INSERT INTO groups (name) VALUES (?)");
            foreach ($db['groups'] as $grp) {
                $stmt->execute([$grp]);
            }
        }
        
        // 4. Sync Users
        if (isset($db['users'])) {
            $conn->exec("DELETE FROM users");
            $stmt = $conn->prepare("INSERT INTO users (id, username, password_hash, name, role, title, avatar, email, phone, department, status, `group`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($db['users'] as $u) {
                $stmt->execute([
                    $u['id'],
                    $u['username'],
                    $u['password_hash'],
                    $u['name'],
                    $u['role'],
                    $u['title'] ?? '',
                    $u['avatar'] ?? null,
                    $u['email'] ?? '',
                    $u['phone'] ?? '',
                    $u['department'] ?? '',
                    $u['status'] ?? '',
                    $u['group'] ?? ''
                ]);
            }
        }
        
        // 5. Sync Registration Requests
        if (isset($db['registration_requests'])) {
            $conn->exec("DELETE FROM registration_requests");
            $stmt = $conn->prepare("INSERT INTO registration_requests (id, username, password_hash, name, role, title, department, email, phone, avatar, status, request_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($db['registration_requests'] as $req) {
                $stmt->execute([
                    $req['id'],
                    $req['username'],
                    $req['password_hash'],
                    $req['name'],
                    $req['role'],
                    $req['title'] ?? '',
                    $req['department'] ?? '',
                    $req['email'] ?? '',
                    $req['phone'] ?? '',
                    $req['avatar'] ?? null,
                    $req['status'] ?? 'Pending',
                    $req['request_date'] ?? ''
                ]);
            }
        }
        
        // Activities sync removed to maintain a permanent audit trail in database.
        
        // 7. Sync Notification Settings
        if (isset($db['notification_settings'])) {
            $conn->exec("DELETE FROM notification_settings");
            $ns = $db['notification_settings'];
            $stmt = $conn->prepare("INSERT INTO notification_settings (webhookUrl, notifyOnCreate, notifyOnStatusChange, notifyOnHighRiskOnly) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $ns['webhookUrl'] ?? '',
                isset($ns['notifyOnCreate']) ? (!empty($ns['notifyOnCreate']) ? 1 : 0) : 1,
                isset($ns['notifyOnStatusChange']) ? (!empty($ns['notifyOnStatusChange']) ? 1 : 0) : 1,
                isset($ns['notifyOnHighRiskOnly']) ? (!empty($ns['notifyOnHighRiskOnly']) ? 1 : 0) : 0
            ]);
        }

        // 7a. Sync Active Directory Settings
        if (isset($db['ad_settings'])) {
            $conn->exec("DELETE FROM ad_settings");
            $ad = $db['ad_settings'];
            $stmt = $conn->prepare("INSERT INTO ad_settings (adEnabled, adServer, adPort, adBaseDn, adDomain) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                !empty($ad['adEnabled']) ? 1 : 0,
                $ad['adServer'] ?? '',
                intval($ad['adPort'] ?? 389),
                $ad['adBaseDn'] ?? '',
                $ad['adDomain'] ?? ''
            ]);
        }
        
        // 8. Sync Changes
        if (isset($db['changes'])) {
            $conn->exec("DELETE FROM changes");
            $conn->exec("DELETE FROM change_tasks");
            $conn->exec("DELETE FROM change_approvals");
            $conn->exec("DELETE FROM change_comments");
            $conn->exec("DELETE FROM change_revisions");
            
            $stmtChange = $conn->prepare("INSERT INTO changes (id, title, description, requester, requesterTitle, owner, ownerTitle, category, risk, status, targetDate, impact, rollbackPlan, progress, assignedGroup, ownerUsername, requesterUsername) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtTask = $conn->prepare("INSERT INTO change_tasks (id, change_id, task_number, text, completed) VALUES (?, ?, ?, ?, ?)");
            $stmtApproval = $conn->prepare("INSERT INTO change_approvals (id, change_id, role, status, date) VALUES (?, ?, ?, ?, ?)");
            $stmtComment = $conn->prepare("INSERT INTO change_comments (id, change_id, user, userTitle, text, date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtRevision = $conn->prepare("INSERT INTO change_revisions (id, change_id, editor, date, changed_fields) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($db['changes'] as $c) {
                $stmtChange->execute([
                    $c['id'],
                    $c['title'],
                    $c['description'] ?? '',
                    $c['requester'] ?? '',
                    $c['requesterTitle'] ?? '',
                    $c['owner'] ?? '',
                    $c['ownerTitle'] ?? '',
                    $c['category'] ?? '',
                    $c['risk'] ?? '',
                    $c['status'] ?? '',
                    $c['targetDate'] ?? '',
                    $c['impact'] ?? '',
                    $c['rollbackPlan'] ?? '',
                    $c['progress'] ?? 0,
                    $c['assignedGroup'] ?? '',
                    $c['ownerUsername'] ?? '',
                    $c['requesterUsername'] ?? ''
                ]);
                
                if (isset($c['tasks']) && is_array($c['tasks'])) {
                    foreach ($c['tasks'] as $idx => $t) {
                        $taskDbId = (!empty($t['db_id']) && $t['db_id'] > 0) ? (int)$t['db_id'] : null;
                        $stmtTask->execute([
                            $taskDbId,
                            $c['id'],
                            $t['id'] ?? $idx,
                            $t['text'],
                            !empty($t['completed']) ? 1 : 0
                        ]);
                    }
                }
                
                if (isset($c['approvals']) && is_array($c['approvals'])) {
                    foreach ($c['approvals'] as $app) {
                        $appDbId = (!empty($app['id']) && $app['id'] > 0) ? (int)$app['id'] : null;
                        $stmtApproval->execute([
                            $appDbId,
                            $c['id'],
                            $app['role'],
                            $app['status'] ?? 'Pending',
                            $app['date'] ?? ''
                        ]);
                    }
                }
                
                if (isset($c['comments']) && is_array($c['comments'])) {
                    foreach ($c['comments'] as $comm) {
                        $commDbId = (!empty($comm['id']) && $comm['id'] > 0) ? (int)$comm['id'] : null;
                        $commUser = $comm['user'] ?? $comm['author'] ?? '';
                        $stmtComment->execute([
                            $commDbId,
                            $c['id'],
                            $commUser,
                            $comm['userTitle'] ?? '',
                            $comm['text'],
                            $comm['date']
                        ]);
                    }
                }
                
                if (isset($c['revisions']) && is_array($c['revisions'])) {
                    foreach ($c['revisions'] as $rev) {
                        $revDbId = (!empty($rev['id']) && $rev['id'] > 0) ? (int)$rev['id'] : null;
                        $editor = $rev['editor'] ?? '';
                        $date = $rev['date'] ?? '';
                        $changed_fields = is_array($rev['changes']) ? implode(',', $rev['changes']) : ($rev['changes'] ?? '');
                        $stmtRevision->execute([
                            $revDbId,
                            $c['id'],
                            $editor,
                            $date,
                            $changed_fields
                        ]);
                    }
                }
            }
        }
        
        $conn->commit();
        releaseDbLock();
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        releaseDbLock();
        http_response_code(500);
        echo json_encode(['error' => 'Database write failed: ' . $e->getMessage()]);
        exit;
    }
}

// --- JWT ENCRYPTION HELPERS (HMAC-SHA256) ---
function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

function base64UrlDecode($data) {
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
}

function createJwt($payload) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode(json_encode($payload));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, getJwtSecret(), true);
    $base64UrlSignature = base64UrlEncode($signature);
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function decodeJwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
    
    // Verify signature
    $signature = base64UrlDecode($base64UrlSignature);
    $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, getJwtSecret(), true);
    if (!hash_equals($signature, $expectedSignature)) {
        return null;
    }
    
    return json_decode(base64UrlDecode($base64UrlPayload), true);
}

// --- AUTHENTICATION MIDDLEWARE ---
function getAuthenticatedUser() {
    $headers = getallheaders();
    $authHeader = '';
    
    // Normalize headers keys to lowercase to be case-insensitive
    $normalizedHeaders = [];
    foreach ($headers as $k => $v) {
        $normalizedHeaders[strtolower($k)] = $v;
    }
    
    // Check all potential candidate headers in order, using the first valid Bearer token
    $candidates = [
        $normalizedHeaders['authorization'] ?? '',
        $normalizedHeaders['x-authorization'] ?? '',
        $_SERVER['HTTP_AUTHORIZATION'] ?? '',
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '',
        $_SERVER['HTTP_X_AUTHORIZATION'] ?? '',
        $_SERVER['REDIRECT_HTTP_X_AUTHORIZATION'] ?? ''
    ];

    foreach ($candidates as $candidate) {
        if (!empty($candidate) && preg_match('/Bearer\s(\S+)/', $candidate, $matches)) {
            $authHeader = $candidate;
            break;
        }
    }

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $payload = decodeJwt($matches[1]);
        if ($payload && isset($payload['id'])) {
            // Fetch fresh details from DB to prevent stale JWT data (like changed roles)
            $db = readDb();
            foreach ($db['users'] as $u) {
                if ((int)$u['id'] === (int)$payload['id']) {
                    return [
                        'id' => $u['id'],
                        'username' => $u['username'],
                        'name' => $u['name'],
                        'role' => $u['role'],
                        'title' => $u['title'],
                        'department' => $u['department'] ?? ''
                    ];
                }
            }
        }
    }
    
    http_response_code(401);
    echo json_encode(['error' => 'Access denied. Invalid or missing authentication token.']);
    exit;
}

function logActivity($user, $action, $targetId) {
    $now = new DateTime();
    $dateStr = $now->format('Y-m-d H:i');
    $id = round(microtime(true) * 1000);
    
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO activities (id, user, action, target, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $user,
            $action,
            $targetId,
            $dateStr
        ]);
        
        // Keep max 2000 activities to prevent infinite database growth
        $count = $conn->query("SELECT COUNT(*) FROM activities")->fetchColumn();
        if ($count > 2000) {
            $limit = $count - 2000;
            $conn->exec("DELETE FROM activities WHERE id IN (SELECT id FROM (SELECT id FROM activities ORDER BY id ASC LIMIT $limit) as tmp)");
        }
    } catch (PDOException $e) {
        // Fail silently to prevent user actions from failing due to logging errors
    }
}

// --- WEBHOOK NOTIFICATION TRIGGER ---
function sendWebhookNotification($change, $actionText, $actionType = 'status_change') {
    $db = readDb();
    $settings = $db['notification_settings'] ?? null;
    if (!$settings) return;
    
    $webhookUrl = $settings['webhookUrl'] ?? '';
    if (empty($webhookUrl)) return;
    
    // Check high risk filter
    $notifyOnHighRiskOnly = !empty($settings['notifyOnHighRiskOnly']);
    if ($notifyOnHighRiskOnly && $change['risk'] !== 'High') {
        return;
    }
    
    // Check action type filter
    if ($actionType === 'create') {
        $notifyOnCreate = isset($settings['notifyOnCreate']) ? !empty($settings['notifyOnCreate']) : true;
        if (!$notifyOnCreate) return;
    } else {
        $notifyOnStatusChange = isset($settings['notifyOnStatusChange']) ? !empty($settings['notifyOnStatusChange']) : true;
        if (!$notifyOnStatusChange) return;
    }
    
    $color = '#a78bfa'; // Purple default
    if ($change['risk'] === 'High' || $change['status'] === 'Rolled Back' || $change['status'] === 'Rejected') {
        $color = '#ef4444'; // Red
    } else if ($change['status'] === 'Approved' || $change['status'] === 'Completed') {
        $color = '#10b981'; // Green
    } else if ($change['status'] === 'Pending Approval') {
        $color = '#f59e0b'; // Amber
    }
    
    $payload = [
        'text' => "🔔 *Hopper:* Change *{$change['id']}* updated: _{$actionText}_",
        'attachments' => [
            [
                'title' => "{$change['id']}: {$change['title']}",
                'color' => $color,
                'fields' => [
                    ['title' => 'Status', 'value' => $change['status'], 'short' => true],
                    ['title' => 'Risk Level', 'value' => "{$change['risk']} Risk", 'short' => true],
                    ['title' => 'Category', 'value' => $change['category'], 'short' => true],
                    ['title' => 'Target Date', 'value' => $change['targetDate'], 'short' => true],
                    ['title' => 'Owner', 'value' => "{$change['owner']} ({$change['ownerTitle']})", 'short' => true],
                    ['title' => 'Requester', 'value' => "{$change['requester']} ({$change['requesterTitle']})", 'short' => true]
                ],
                'footer' => 'Hopper Change Management System'
            ]
        ]
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($payload),
            'ignore_errors' => true,
            'timeout' => 5
        ]
    ];
    $context = stream_context_create($options);
    @file_get_contents($webhookUrl, false, $context);
}

// --- API ACTIONS HANDLER ---
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$rawInput = $input; // Maintain compatibility for $rawInput usages

switch ($action) {
    // 1b. Get Public Login Configuration (Unauthenticated)
    case 'get_login_config':
        $db = readDb();
        $ad = $db['ad_settings'] ?? null;
        echo json_encode([
            'adEnabled' => $ad ? !empty($ad['adEnabled']) : false,
            'adDomain' => $ad ? ($ad['adDomain'] ?? '') : ''
        ]);
        break;

    // 1. User Login
    case 'login':
        $username = trim($input['username'] ?? '');
        $password = $rawInput['password'] ?? '';
        $authType = $input['authType'] ?? 'local';
        $selectedDomain = trim($input['adDomain'] ?? '');
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password required.']);
            break;
        }

        // Handle DOMAIN\username or DOMAIN/username formats (strip domain prefix)
        $cleanUsername = $username;
        if (strpos($cleanUsername, '\\') !== false) {
            $parts = explode('\\', $cleanUsername, 2);
            $cleanUsername = $parts[1];
        } elseif (strpos($cleanUsername, '/') !== false) {
            $parts = explode('/', $cleanUsername, 2);
            $cleanUsername = $parts[1];
        }
        // Handle UPN format: alice@domain.com -> alice (for local login lookup)
        // Note: for LDAP login the full UPN is used as bindUser, so we preserve the original
        $localUsername = $cleanUsername;
        if ($authType === 'local' && strpos($localUsername, '@') !== false) {
            $atParts = explode('@', $localUsername, 2);
            $localUsername = $atParts[0];
        }

        $db = readDb();
        
        $ad = $db['ad_settings'] ?? null;
        $adAuthenticated = false;
        $adUserAttrs = [];

        // Check if LDAP login is requested but disabled or not configured
        if ($authType === 'ldap') {
            if (!$ad || empty($ad['adEnabled'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Active Directory authentication is disabled on this server.']);
                break;
            }
            if (!function_exists('ldap_connect')) {
                http_response_code(500);
                echo json_encode(['error' => 'PHP LDAP extension is not enabled on this server. Check your php.ini configurations.']);
                break;
            }
        }

        // Try Active Directory authentication if requested, enabled and extension exists
        if ($authType === 'ldap' && $ad && !empty($ad['adEnabled']) && function_exists('ldap_connect')) {
            $ldapconn = @ldap_connect($ad['adServer'], intval($ad['adPort'] ?? 389));
            if (!$ldapconn) {
                http_response_code(503);
                echo json_encode(['error' => 'Could not establish connection to the LDAP server. Check the server address and port in Settings.']);
                break;
            }

            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 5);

            // Prepare bind DN / UPN
            $bindUser = $cleanUsername;
            $domainToUse = !empty($selectedDomain) ? $selectedDomain : ($ad['adDomain'] ?? '');
            
            // If there's multiple comma separated domains, and none selected, default to the first one
            if (!empty($domainToUse)) {
                $domains = array_map('trim', explode(',', $domainToUse));
                $domainToUse = $domains[0];
            }

            if (!empty($domainToUse)) {
                $domainSuffix = (strpos($domainToUse, '@') === 0) ? $domainToUse : '@' . $domainToUse;
                if (strpos($cleanUsername, '@') === false) {
                    $bindUser = $cleanUsername . $domainSuffix;
                }
            }

            $ldapbind = @ldap_bind($ldapconn, $bindUser, $password);
            if ($ldapbind) {
                $adAuthenticated = true;
                
                // Search user details in Active Directory
                $baseDn = $ad['adBaseDn'] ?: '';
                if (!empty($baseDn)) {
                    // Escape special characters to prevent LDAP injection
                    $escapedUsername = ldap_escape($cleanUsername, '', LDAP_ESCAPE_FILTER);
                    $escapedBindUser = ldap_escape($bindUser, '', LDAP_ESCAPE_FILTER);
                    $filter = "(|(sAMAccountName={$escapedUsername})(userPrincipalName={$escapedBindUser})(mail={$escapedUsername}))";
                    $search = @ldap_search($ldapconn, $baseDn, $filter, ['displayname', 'mail', 'department', 'title']);
                    if ($search) {
                        $entries = @ldap_get_entries($ldapconn, $search);
                        if ($entries && $entries['count'] > 0) {
                            $entry = $entries[0];
                            $adUserAttrs = [
                                'name' => $entry['displayname'][0] ?? $cleanUsername,
                                'email' => $entry['mail'][0] ?? '',
                                'department' => $entry['department'][0] ?? 'IT Operations',
                                'title' => $entry['title'][0] ?? 'Systems Engineer'
                            ];
                        }
                    }
                }

                if (empty($adUserAttrs)) {
                    $adUserAttrs = [
                        'name' => $cleanUsername,
                        'email' => '',
                        'department' => 'IT Operations',
                        'title' => 'Systems Engineer'
                    ];
                }
            } else {
                // Get detailed LDAP error for better debugging
                $ldapErrNo = ldap_errno($ldapconn);
                $ldapErrStr = ldap_error($ldapconn);
                // Error 49 = Invalid credentials (wrong username/password)
                // Other errors = connection/server issues
                if ($ldapErrNo === 49) {
                    $adAuthenticated = false;
                } else {
                    http_response_code(503);
                    echo json_encode(['error' => "LDAP server error ({$ldapErrNo}): {$ldapErrStr}"]);
                    @ldap_close($ldapconn);
                    break;
                }
            }
            @ldap_close($ldapconn);
        }

        $user = null;
        if ($authType === 'ldap') {
            if ($adAuthenticated) {
                // Find or Provision User in local DB (JIT Provisioning)
                $userIdx = -1;
                for ($i = 0; $i < count($db['users']); $i++) {
                    if (strcasecmp($db['users'][$i]['username'], $cleanUsername) === 0) {
                        $userIdx = $i;
                        break;
                    }
                }

                if ($userIdx !== -1) {
                    // Update existing user with AD attributes
                    $db['users'][$userIdx]['name'] = $adUserAttrs['name'];
                    if (!empty($adUserAttrs['email'])) $db['users'][$userIdx]['email'] = $adUserAttrs['email'];
                    if (!empty($adUserAttrs['department'])) $db['users'][$userIdx]['department'] = $adUserAttrs['department'];
                    if (!empty($adUserAttrs['title'])) $db['users'][$userIdx]['title'] = $adUserAttrs['title'];
                    $user = $db['users'][$userIdx];
                    // Update in DB directly to avoid full writeDb overhead
                    try {
                        $conn = getDbConnection();
                        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, department=?, title=? WHERE id=?");
                        $stmt->execute([
                            $adUserAttrs['name'],
                            !empty($adUserAttrs['email']) ? $adUserAttrs['email'] : $db['users'][$userIdx]['email'],
                            !empty($adUserAttrs['department']) ? $adUserAttrs['department'] : $db['users'][$userIdx]['department'],
                            !empty($adUserAttrs['title']) ? $adUserAttrs['title'] : $db['users'][$userIdx]['title'],
                            $db['users'][$userIdx]['id']
                        ]);
                    } catch (PDOException $ex) { /* fail silently */ }
                } else {
                    // JIT Provision new user - insert directly into DB
                    $validDepts = $db['departments'] ?? [];
                    if (!in_array($adUserAttrs['department'], $validDepts)) {
                        try {
                            $conn = getDbConnection();
                            $stmt = $conn->prepare("INSERT IGNORE INTO departments (name) VALUES (?)");
                            $stmt->execute([$adUserAttrs['department']]);
                        } catch (PDOException $ex) { /* fail silently */ }
                    }

                    try {
                        $conn = getDbConnection();
                        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, name, role, title, department, email, phone, avatar, status, `group`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            strtolower($cleanUsername),
                            password_hash($password, PASSWORD_BCRYPT),
                            $adUserAttrs['name'],
                            'Requester',
                            $adUserAttrs['title'],
                            $adUserAttrs['department'],
                            $adUserAttrs['email'],
                            '',
                            '',
                            'Working',
                            'IT Operations'
                        ]);
                        $newId = (int)$conn->lastInsertId();
                        $user = [
                            'id'           => $newId,
                            'username'     => strtolower($cleanUsername),
                            'name'         => $adUserAttrs['name'],
                            'role'         => 'Requester',
                            'title'        => $adUserAttrs['title'],
                            'department'   => $adUserAttrs['department'],
                            'email'        => $adUserAttrs['email'],
                            'phone'        => '',
                            'avatar'       => '',
                            'status'       => 'Working',
                            'group'        => 'IT Operations',
                            'password_hash'=> ''
                        ];
                    } catch (PDOException $ex) {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to provision AD user in local database: ' . $ex->getMessage()]);
                        break;
                    }
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid Active Directory username or password.']);
                break;
            }
        } else {
            // Standard validation (Local DB Fallback)
            foreach ($db['users'] as $u) {
                if (strtolower($u['username']) === strtolower($localUsername)) {
                    $user = $u;
                    break;
                }
            }

            if ($user) {
                if (!password_verify($password, $user['password_hash'])) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid username or password.']);
                    break;
                }
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid username or password.']);
                break;
            }
        }

        if ($user) {
            $tokenPayload = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role'],
                'title' => $user['title'],
                'department' => $user['department'] ?? ''
            ];
            $token = createJwt($tokenPayload);

            echo json_encode([
                'token' => $token, 
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'title' => $user['title'],
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'avatar' => $user['avatar'] ?? '',
                    'department' => $user['department'] ?? '',
                    'status' => $user['status'] ?? '',
                    'group' => $user['group'] ?? ''
                ]
            ]);
        } else {
            // Check registration requests backwards (most recent first)
            $pendingOrRejected = null;
            if (isset($db['registration_requests'])) {
                for ($i = count($db['registration_requests']) - 1; $i >= 0; $i--) {
                    $req = $db['registration_requests'][$i];
                    if (strtolower($req['username']) === strtolower($username)) {
                        $pendingOrRejected = $req;
                        break;
                    }
                }
            }

            if ($pendingOrRejected) {
                if (password_verify($password, $pendingOrRejected['password_hash'])) {
                    if ($pendingOrRejected['status'] === 'Pending') {
                        http_response_code(403);
                        echo json_encode(['error' => 'Your registration request has not been approved yet. Please wait for administrator approval.']);
                        break;
                    } elseif ($pendingOrRejected['status'] === 'Rejected') {
                        http_response_code(403);
                        echo json_encode(['error' => 'Your registration request has been rejected by the administrator.']);
                        break;
                    }
                }
            }

            // Default to invalid credentials if no active user and no pending/rejected request matches
            http_response_code(401);
            echo json_encode(['error' => 'Invalid username or password.']);
        }
        break;

    // 2. User Register
    case 'register':
        $name = trim($input['name'] ?? '');
        $username = trim($input['username'] ?? '');
        $password = $rawInput['password'] ?? '';
        $title = trim($input['title'] ?? 'IT Operations');
        $role = $input['role'] ?? 'Requester';
        $department = trim($input['department'] ?? 'IT Operations');

        if (empty($name) || empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields.']);
            break;
        }

        if (strlen($name) < 2 || strlen($name) > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'Full Name must be between 2 and 100 characters.']);
            break;
        }

        if (!preg_match('/^[a-zA-Z0-9_\-]{3,30}$/', $username)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username must be 3-30 characters long and contain only letters, numbers, underscores, or dashes.']);
            break;
        }

        if (strlen($password) < 4 || strlen($password) > 72) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be between 4 and 72 characters.']);
            break;
        }

        if (strlen($title) > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'Job title must be under 100 characters.']);
            break;
        }

        $db = readDb();
        $validDepartments = $db['departments'] ?? [];
        if (!in_array($department, $validDepartments)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid department selected.']);
            break;
        }
        
        // Check active users
        foreach ($db['users'] as $u) {
            if (strtolower($u['username']) === strtolower($username)) {
                http_response_code(409);
                echo json_encode(['error' => 'This username is already taken or pending approval.']);
                break 2;
            }
        }

        // Check pending/approved registration requests
        if (isset($db['registration_requests'])) {
            foreach ($db['registration_requests'] as $req) {
                if (strtolower($req['username']) === strtolower($username) && $req['status'] !== 'Rejected') {
                    http_response_code(409);
                    echo json_encode(['error' => 'This username is already taken or pending approval.']);
                    break 2;
                }
            }
        }

        $allowedRoles = ['Requester', 'CAB Approver', 'Administrator'];
        $userRole = in_array($role, $allowedRoles) ? $role : 'Requester';

        $newRequest = [
            'id' => round(microtime(true) * 1000),
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'name' => $name,
            'role' => $userRole,
            'title' => empty($title) ? 'IT Operations' : $title,
            'department' => $department,
            'email' => '',
            'phone' => '',
            'avatar' => '',
            'status' => 'Pending',
            'request_date' => date('Y-m-d H:i')
        ];

        $db['registration_requests'][] = $newRequest;
        writeDb($db);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Your registration request has been submitted to the administrator. You can log in once it is approved.'
        ]);
        break;

    // 3. Get Current User Info
    case 'me':
        $userPayload = getAuthenticatedUser();
        
        $db = readDb();
        $user = null;
        foreach ($db['users'] as $u) {
            if ((int)$u['id'] === (int)$userPayload['id']) {
                $user = $u;
                break;
            }
        }
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            break;
        }

        echo json_encode([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role'],
                'title' => $user['title'],
                'email' => $user['email'] ?? '',
                'phone' => $user['phone'] ?? '',
                'avatar' => $user['avatar'] ?? '',
                'department' => $user['department'] ?? '',
                'status' => $user['status'] ?? '',
                'group' => $user['group'] ?? ''
            ]
        ]);
        break;

    // 4. Update Profile Settings (All Users)
    case 'update_profile':
        $userPayload = getAuthenticatedUser();
        
        $name = trim($input['name'] ?? '');
        $title = trim($input['title'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $avatar = $input['avatar'] ?? null;
        $newPassword = $rawInput['newPassword'] ?? '';
        $department = trim($input['department'] ?? '');

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Display name is required.']);
            break;
        }

        if (strlen($name) < 2 || strlen($name) > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'Display Name must be between 2 and 100 characters.']);
            break;
        }

        if (strlen($title) > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'Job Title must be under 100 characters.']);
            break;
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address format.']);
            break;
        }

        if (strlen($phone) > 30) {
            http_response_code(400);
            echo json_encode(['error' => 'Phone number must be under 30 characters.']);
            break;
        }

        $db = readDb();
        $validDepartments = $db['departments'] ?? [];
        if (empty($department) || !in_array($department, $validDepartments)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid department selected.']);
            break;
        }



        if (!empty($newPassword)) {
            if (strlen($newPassword) < 4 || strlen($newPassword) > 72) {
                http_response_code(400);
                echo json_encode(['error' => 'New password must be between 4 and 72 characters.']);
                break;
            }
        }

        $userIdx = -1;
        for ($i = 0; $i < count($db['users']); $i++) {
            if ((int)$db['users'][$i]['id'] === (int)$userPayload['id']) {
                $userIdx = $i;
                break;
            }
        }

        if ($userIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            break;
        }

        if ($avatar !== null) {
            if (empty($avatar)) {
                $oldAvatar = $db['users'][$userIdx]['avatar'] ?? '';
                if (!empty($oldAvatar) && strpos($oldAvatar, 'avatars/') === 0 && file_exists(__DIR__ . '/' . $oldAvatar)) {
                    unlink(__DIR__ . '/' . $oldAvatar);
                }
                $avatar = '';
            } elseif (preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $avatar, $matches)) {
                if (strlen($avatar) > 1500000) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Avatar image size exceeds the allowed 1MB limit.']);
                    break;
                }
                
                $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $avatar);
                $imageData = base64_decode($base64Data);
                
                if ($imageData === false) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to decode base64 image data.']);
                    break;
                }
                
                if (!is_dir(__DIR__ . '/avatars')) {
                    mkdir(__DIR__ . '/avatars', 0755, true);
                }
                
                $filename = 'avatars/user_' . $userPayload['id'] . '_' . time() . '.' . $extension;
                $filepath = __DIR__ . '/' . $filename;
                
                $oldAvatar = $db['users'][$userIdx]['avatar'] ?? '';
                if (!empty($oldAvatar) && strpos($oldAvatar, 'avatars/') === 0 && file_exists(__DIR__ . '/' . $oldAvatar)) {
                    unlink(__DIR__ . '/' . $oldAvatar);
                }

                if (file_put_contents($filepath, $imageData) === false) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to save avatar image to server.']);
                    break;
                }
                
                $avatar = $filename;
            } elseif (strpos($avatar, 'avatars/') === 0) {
                // Keep the existing avatar file path
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid avatar format. Only PNG, JPG, JPEG, and WEBP base64 images are allowed.']);
                break;
            }
        }

        // Apply edits
        $db['users'][$userIdx]['name'] = $name;
        $db['users'][$userIdx]['title'] = $title;
        $db['users'][$userIdx]['email'] = $email;
        $db['users'][$userIdx]['phone'] = $phone;
        $db['users'][$userIdx]['department'] = $department;
        if ($avatar !== null) {
            $db['users'][$userIdx]['avatar'] = $avatar;
        }

        if (!empty($newPassword)) {
            $db['users'][$userIdx]['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        writeDb($db);

        // Regenerate JWT token with updated name/title/role/department
        $newTokenPayload = [
            'id' => $db['users'][$userIdx]['id'],
            'username' => $db['users'][$userIdx]['username'],
            'name' => $db['users'][$userIdx]['name'],
            'role' => $db['users'][$userIdx]['role'],
            'title' => $db['users'][$userIdx]['title'],
            'department' => $db['users'][$userIdx]['department']
        ];
        $token = createJwt($newTokenPayload);

        logActivity($db['users'][$userIdx]['name'], "updated their profile settings", "USR-{$userPayload['id']}");

        echo json_encode([
            'token' => $token,
            'user' => [
                'id' => $db['users'][$userIdx]['id'],
                'username' => $db['users'][$userIdx]['username'],
                'name' => $db['users'][$userIdx]['name'],
                'role' => $db['users'][$userIdx]['role'],
                'title' => $db['users'][$userIdx]['title'],
                'email' => $db['users'][$userIdx]['email'],
                'phone' => $db['users'][$userIdx]['phone'],
                'avatar' => $db['users'][$userIdx]['avatar'],
                'department' => $db['users'][$userIdx]['department'],
                'status' => $db['users'][$userIdx]['status'] ?? ''
            ]
        ]);
        break;



    // 5. Get Users List (Admin Only)
    case 'get_users':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }

        $db = readDb();
        $filteredUsers = [];
        foreach ($db['users'] as $u) {
            $filteredUsers[] = [
                'id' => $u['id'],
                'username' => $u['username'],
                'name' => $u['name'],
                'role' => $u['role'],
                'title' => $u['title'],
                'email' => $u['email'] ?? '',
                'phone' => $u['phone'] ?? '',
                'avatar' => $u['avatar'] ?? '',
                'department' => $u['department'] ?? '',
                'group' => $u['group'] ?? ''
            ];
        }

        echo json_encode(['users' => $filteredUsers]);
        break;

    // 5a. Get Registration Requests (Admin Only)
    case 'get_registration_requests':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }

        $db = readDb();
        $requests = $db['registration_requests'] ?? [];
        echo json_encode(['requests' => $requests]);
        break;

    // 5b. Approve Registration Request (Admin Only)
    case 'approve_registration':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }

        $requestId = floatval($input['requestId'] ?? 0);
        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['error' => 'Request ID is required.']);
            break;
        }

        $db = readDb();
        $reqIdx = -1;
        if (isset($db['registration_requests'])) {
            for ($i = 0; $i < count($db['registration_requests']); $i++) {
                if (floatval($db['registration_requests'][$i]['id']) === $requestId) {
                    $reqIdx = $i;
                    break;
                }
            }
        }

        if ($reqIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Registration request not found.']);
            break;
        }

        $req = $db['registration_requests'][$reqIdx];
        if ($req['status'] !== 'Pending') {
            http_response_code(400);
            echo json_encode(['error' => 'This request is already ' . strtolower($req['status']) . '.']);
            break;
        }

        // Calculate next user ID
        $nextId = 1;
        foreach ($db['users'] as $u) {
            if ($u['id'] >= $nextId) {
                $nextId = $u['id'] + 1;
            }
        }

        // Add to active users
        $newUser = [
            'id' => $nextId,
            'username' => $req['username'],
            'password_hash' => $req['password_hash'],
            'name' => $req['name'],
            'role' => $req['role'],
            'title' => $req['title'],
            'department' => $req['department'],
            'email' => $req['email'] ?? '',
            'phone' => $req['phone'] ?? '',
            'avatar' => $req['avatar'] ?? '',
            'group' => $req['group'] ?? ''
        ];

        $db['users'][] = $newUser;
        
        // Update request status to Approved
        $db['registration_requests'][$reqIdx]['status'] = 'Approved';
        
        writeDb($db);

        logActivity($admin['name'], "approved registration request for '{$req['username']}'", "USR-{$nextId}");

        echo json_encode(['success' => true]);
        break;

    // 5c. Reject Registration Request (Admin Only)
    case 'reject_registration':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }

        $requestId = floatval($input['requestId'] ?? 0);
        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['error' => 'Request ID is required.']);
            break;
        }

        $db = readDb();
        $reqIdx = -1;
        if (isset($db['registration_requests'])) {
            for ($i = 0; $i < count($db['registration_requests']); $i++) {
                if (floatval($db['registration_requests'][$i]['id']) === $requestId) {
                    $reqIdx = $i;
                    break;
                }
            }
        }

        if ($reqIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Registration request not found.']);
            break;
        }

        $req = $db['registration_requests'][$reqIdx];
        if ($req['status'] !== 'Pending') {
            http_response_code(400);
            echo json_encode(['error' => 'This request is already ' . strtolower($req['status']) . '.']);
            break;
        }

        // Update status to Rejected
        $db['registration_requests'][$reqIdx]['status'] = 'Rejected';
        
        writeDb($db);

        logActivity($admin['name'], "rejected registration request for '{$req['username']}'", "REQ-{$requestId}");

        echo json_encode(['success' => true]);
        break;

    // 6. Change User Role (Admin Only)
    case 'change_user_role':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }

        $targetUserId = intval($input['userId'] ?? 0);
        $newRole = $input['role'] ?? '';

        $allowedRoles = ['Requester', 'CAB Approver', 'Administrator'];
        if (!in_array($newRole, $allowedRoles)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid role specified.']);
            break;
        }

        if ((int)$targetUserId === (int)$admin['id']) {
            http_response_code(400);
            echo json_encode(['error' => 'You cannot modify your own administrator role.']);
            break;
        }

        $db = readDb();
        $userIdx = -1;
        for ($i = 0; $i < count($db['users']); $i++) {
            if ((int)$db['users'][$i]['id'] === (int)$targetUserId) {
                $userIdx = $i;
                break;
            }
        }

        if ($userIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Target user not found.']);
            break;
        }

        $db['users'][$userIdx]['role'] = $newRole;
        writeDb($db);

        logActivity($admin['name'], "changed system role for user '{$db['users'][$userIdx]['username']}' to '{$newRole}'", "USR-{$targetUserId}");

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $db['users'][$userIdx]['id'],
                'username' => $db['users'][$userIdx]['username'],
                'role' => $db['users'][$userIdx]['role']
            ]
        ]);
        break;

    // 6a. Admin Update User Details (Admin Only)
    case 'admin_update_user':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }

        $targetUserId = intval($input['userId'] ?? 0);
        $name = trim($input['name'] ?? '');
        $title = trim($input['title'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $department = trim($input['department'] ?? '');
        $group = trim($input['group'] ?? '');
        $role = trim($input['role'] ?? '');
        $newPassword = $rawInput['newPassword'] ?? '';

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Full name is required.']);
            break;
        }

        if (strlen($name) < 2 || strlen($name) > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'Full name must be between 2 and 100 characters.']);
            break;
        }

        if (strlen($title) > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'Job title must be under 100 characters.']);
            break;
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address format.']);
            break;
        }

        if (strlen($phone) > 30) {
            http_response_code(400);
            echo json_encode(['error' => 'Phone number must be under 30 characters.']);
            break;
        }

        $db = readDb();
        $validDepartments = $db['departments'] ?? [];
        if (empty($department) || !in_array($department, $validDepartments)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid department selected.']);
            break;
        }

        $validGroups = $db['groups'] ?? [];
        if (!empty($group) && !in_array($group, $validGroups)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid group selected.']);
            break;
        }

        $allowedRoles = ['Requester', 'CAB Approver', 'Administrator'];
        if (!in_array($role, $allowedRoles)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid role specified.']);
            break;
        }

        if ((int)$targetUserId === (int)$admin['id'] && $role !== 'Administrator') {
            http_response_code(400);
            echo json_encode(['error' => 'You cannot modify your own administrator role.']);
            break;
        }

        if (!empty($newPassword)) {
            if (strlen($newPassword) < 4 || strlen($newPassword) > 72) {
                http_response_code(400);
                echo json_encode(['error' => 'Password must be between 4 and 72 characters.']);
                break;
            }
        }

        $userIdx = -1;
        for ($i = 0; $i < count($db['users']); $i++) {
            if ((int)$db['users'][$i]['id'] === (int)$targetUserId) {
                $userIdx = $i;
                break;
            }
        }

        if ($userIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            break;
        }

        // Apply edits
        $db['users'][$userIdx]['name'] = $name;
        $db['users'][$userIdx]['title'] = $title;
        $db['users'][$userIdx]['email'] = $email;
        $db['users'][$userIdx]['phone'] = $phone;
        $db['users'][$userIdx]['department'] = $department;
        $db['users'][$userIdx]['group'] = $group;
        $db['users'][$userIdx]['role'] = $role;

        if (!empty($newPassword)) {
            $db['users'][$userIdx]['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        writeDb($db);

        logActivity($admin['name'], "updated user details for '{$db['users'][$userIdx]['username']}'", "USR-{$targetUserId}");

        $response = [
            'success' => true,
            'user' => [
                'id' => $db['users'][$userIdx]['id'],
                'username' => $db['users'][$userIdx]['username'],
                'name' => $db['users'][$userIdx]['name'],
                'role' => $db['users'][$userIdx]['role'],
                'title' => $db['users'][$userIdx]['title'],
                'email' => $db['users'][$userIdx]['email'],
                'phone' => $db['users'][$userIdx]['phone'],
                'department' => $db['users'][$userIdx]['department'],
                'group' => $db['users'][$userIdx]['group']
            ]
        ];

        // If the admin updated themselves, regenerate token
        if ((int)$targetUserId === (int)$admin['id']) {
            $newTokenPayload = [
                'id' => $db['users'][$userIdx]['id'],
                'username' => $db['users'][$userIdx]['username'],
                'name' => $db['users'][$userIdx]['name'],
                'role' => $db['users'][$userIdx]['role'],
                'title' => $db['users'][$userIdx]['title'],
                'department' => $db['users'][$userIdx]['department'],
                'group' => $db['users'][$userIdx]['group']
            ];
            $response['token'] = createJwt($newTokenPayload);
        }

        echo json_encode($response);
        break;

    // 7. Get System Categories
    case 'categories':
        $db = readDb();
        echo json_encode(['categories' => $db['categories']]);
        break;

    // 8. Get Change Requests List (with Filters)
    case 'get_changes':
        getAuthenticatedUser(); // Verify token
        $db = readDb();
        $list = $db['changes'];

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $risk = $_GET['risk'] ?? '';

        if (!empty($search)) {
            $q = strtolower($search);
            $list = array_filter($list, function($c) use ($q) {
                return (strpos(strtolower($c['id']), $q) !== false) ||
                       (strpos(strtolower($c['title']), $q) !== false) ||
                       (strpos(strtolower($c['owner']), $q) !== false) ||
                       (strpos(strtolower($c['requester']), $q) !== false);
            });
        }

        if (!empty($status)) {
            $list = array_filter($list, function($c) use ($status) {
                return $c['status'] === $status;
            });
        }

        if (!empty($risk)) {
            $list = array_filter($list, function($c) use ($risk) {
                return $c['risk'] === $risk;
            });
        }

        // Return array re-indexed values
        echo json_encode(['changes' => array_values($list)]);
        break;

    // 9. Create New Change Request
    case 'create_change':
        $user = getAuthenticatedUser();
        
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $requester = trim($input['requester'] ?? $user['name']);
        $requesterTitle = trim($input['requesterTitle'] ?? $user['title']);
        $owner = trim($input['owner'] ?? $user['name']);
        $ownerTitle = trim($input['ownerTitle'] ?? $user['title']);
        $category = $input['category'] ?? '';
        $risk = $input['risk'] ?? '';
        $targetDate = $input['targetDate'] ?? '';
        $impact = trim($input['impact'] ?? '');
        $rollbackPlan = trim($input['rollbackPlan'] ?? '');
        $assignedGroup = trim($input['assignedGroup'] ?? '');
        $tasksInput = $input['tasks'] ?? [];

        if (empty($title) || empty($description) || empty($category) || empty($risk) || empty($targetDate) || empty($impact) || empty($rollbackPlan) || empty($tasksInput)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields.']);
            break;
        }

        if (strlen($title) > 150) {
            http_response_code(400);
            echo json_encode(['error' => 'Title exceeds maximum length of 150 characters.']);
            break;
        }

        if (strlen($description) > 5000 || strlen($impact) > 5000 || strlen($rollbackPlan) > 5000) {
            http_response_code(400);
            echo json_encode(['error' => 'Text fields (description, impact, rollback) exceed maximum length of 5000 characters.']);
            break;
        }

        $dateObj = DateTime::createFromFormat('Y-m-d', $targetDate);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $targetDate) {
            http_response_code(400);
            echo json_encode(['error' => 'Target date must be a valid date in YYYY-MM-DD format.']);
            break;
        }

        $allowedRisks = ['Low', 'Medium', 'High'];
        if (!in_array($risk, $allowedRisks)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid risk level.']);
            break;
        }

        $db = readDb();
        
        $ownerUsername = $user['username'];
        if ($owner !== $user['name']) {
            foreach ($db['users'] as $u) {
                if (strcasecmp($u['name'], $owner) === 0) {
                    $ownerUsername = $u['username'];
                    break;
                }
            }
        }
        
        $validGroups = $db['groups'] ?? [];
        if (!empty($assignedGroup) && !in_array($assignedGroup, $validGroups)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid assigned group selected.']);
            break;
        }

        $nextNumber = 1001;
        foreach ($db['changes'] as $c) {
            $parts = explode('-', $c['id']);
            $num = intval($parts[1]);
            if ($num >= $nextNumber) {
                $nextNumber = $num + 1;
            }
        }
        $newId = "CHG-{$nextNumber}";

        $parsedTasks = [];
        $taskIdCounter = 1;
        foreach ($tasksInput as $t) {
            $taskText = is_array($t) ? trim($t['text'] ?? '') : trim($t);
            if (empty($taskText)) {
                continue;
            }
            if (strlen($taskText) > 250) {
                http_response_code(400);
                echo json_encode(['error' => 'Task text exceeds maximum length of 250 characters.']);
                break 2; // Break out of switch and loop
            }
            $parsedTasks[] = [
                'id' => $taskIdCounter++,
                'text' => $taskText,
                'completed' => is_array($t) ? (!empty($t['completed'])) : false
            ];
        }

        if (empty($parsedTasks)) {
            http_response_code(400);
            echo json_encode(['error' => 'At least one valid implementation task is required.']);
            break;
        }

        $newChange = [
            'id' => $newId,
            'title' => $title,
            'description' => $description,
            'requester' => empty($requester) ? $user['name'] : $requester,
            'requesterUsername' => $user['username'],
            'requesterTitle' => empty($requesterTitle) ? $user['title'] : $requesterTitle,
            'owner' => empty($owner) ? $user['name'] : $owner,
            'ownerUsername' => $ownerUsername,
            'ownerTitle' => empty($ownerTitle) ? $user['title'] : $ownerTitle,
            'category' => $category,
            'risk' => $risk,
            'status' => 'Draft',
            'targetDate' => $targetDate,
            'impact' => $impact,
            'rollbackPlan' => $rollbackPlan,
            'tasks' => $parsedTasks,
            'progress' => 0,
            'approvals' => [
                ['role' => 'CAB (Change Advisory Board)', 'status' => 'Pending', 'date' => '']
            ],
            'comments' => [],
            'assignedGroup' => $assignedGroup,
            'revisions' => []
        ];

        array_unshift($db['changes'], $newChange);
        writeDb($db);

        logActivity($user['name'], "created a new change request: \"{$title}\"", $newId);
        sendWebhookNotification($newChange, "created by {$user['name']}", "create");

        http_response_code(201);
        echo json_encode(['change' => $newChange]);
        break;

    // 10. Get Single Change Request Details
    case 'get_change_detail':
        getAuthenticatedUser();
        $id = $_GET['id'] ?? '';
        
        if (empty($id) || !preg_match('/^CHG-[0-9]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid change request ID format.']);
            break;
        }
        
        $db = readDb();
        $change = null;
        foreach ($db['changes'] as $c) {
            if ($c['id'] === $id) {
                $change = $c;
                break;
            }
        }

        if (!$change) {
            http_response_code(404);
            echo json_encode(['error' => 'Change request not found.']);
            break;
        }

        echo json_encode(['change' => $change]);
        break;

    // 11. Update Change Request Status (Workflow Transition)
    case 'update_status':
        $user = getAuthenticatedUser();
        $id = $_GET['id'] ?? '';
        $status = $input['status'] ?? '';
        $actionText = $input['actionText'] ?? '';

        if (empty($id) || !preg_match('/^CHG-[0-9]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid change request ID format.']);
            break;
        }

        $allowedStatuses = ['Draft', 'Under Review', 'Pending Approval', 'Approved', 'Implementing', 'Completed', 'Rolled Back', 'Rejected'];
        if (!in_array($status, $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status transition.']);
            break;
        }

        if (empty($status)) {
            http_response_code(400);
            echo json_encode(['error' => 'New status is required.']);
            break;
        }

        $db = readDb();
        $changeIdx = -1;
        for ($i = 0; $i < count($db['changes']); $i++) {
            if ($db['changes'][$i]['id'] === $id) {
                $changeIdx = $i;
                break;
            }
        }

        if ($changeIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Change request not found.']);
            break;
        }

        $change = $db['changes'][$changeIdx];

        // --- ROLE-BASED ACCESS CONTROL (RBAC) ---
        $isApproving = $status === 'Approved' || $status === 'Rejected';
        if ($isApproving) {
            if ($user['role'] !== 'CAB Approver' && $user['role'] !== 'Administrator') {
                http_response_code(403);
                echo json_encode(['error' => 'Only CAB Approvers or Administrators can approve or reject changes.']);
                break;
            }

            // Update CAB Approvals record list
            for ($j = 0; $j < count($change['approvals']); $j++) {
                if ($change['approvals'][$j]['role'] === 'CAB (Change Advisory Board)') {
                    $now = new DateTime();
                    $change['approvals'][$j]['status'] = $status;
                    $change['approvals'][$j]['date'] = $now->format('Y-m-d H:i');
                }
            }
        } else {
            // For non-approval states, only Admins or Owners/Requesters can transition
            if ($user['role'] !== 'Administrator') {
                if (($change['ownerUsername'] ?? '') !== $user['username'] && ($change['requesterUsername'] ?? '') !== $user['username']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'You can only update workflows for change requests you own.']);
                    break;
                }
            }
        }

        $change['status'] = $status;
        $db['changes'][$changeIdx] = $change;
        writeDb($db);

        // Log activity
        logActivity($user['name'], $actionText ?: "updated status to {$status}", $change['id']);
        sendWebhookNotification($change, ($actionText ?: "updated status to {$status}") . " by {$user['name']}");

        echo json_encode(['change' => $change]);
        break;

    // 12. Update Checklist Item Task Status
    case 'toggle_task':
        $user = getAuthenticatedUser();
        $id = $_GET['id'] ?? '';
        $taskId = intval($_GET['task_id'] ?? 0);
        $completed = !empty($input['completed']);

        if (empty($id) || !preg_match('/^CHG-[0-9]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid change request ID format.']);
            break;
        }

        $db = readDb();
        $changeIdx = -1;
        for ($i = 0; $i < count($db['changes']); $i++) {
            if ($db['changes'][$i]['id'] === $id) {
                $changeIdx = $i;
                break;
            }
        }

        if ($changeIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Change request not found.']);
            break;
        }

        $change = $db['changes'][$changeIdx];
        
        $taskIdx = -1;
        for ($k = 0; $k < count($change['tasks']); $k++) {
            if ($change['tasks'][$k]['id'] === $taskId) {
                $taskIdx = $k;
                break;
            }
        }

        if ($taskIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found.']);
            break;
        }

        // Enable checks for change owner, requester, or admins
        if ($user['role'] !== 'Administrator' && ($change['ownerUsername'] ?? '') !== $user['username'] && ($change['requesterUsername'] ?? '') !== $user['username']) {
            http_response_code(403);
            echo json_encode(['error' => 'You can only update task checklists for requests you own.']);
            break;
        }

        $change['tasks'][$taskIdx]['completed'] = $completed;

        // Calculate progress percentage
        $total = count($change['tasks']);
        $completedCount = 0;
        foreach ($change['tasks'] as $t) {
            if ($t['completed']) {
                $completedCount++;
            }
        }
        $change['progress'] = $total > 0 ? round(($completedCount / $total) * 100) : 0;

        $db['changes'][$changeIdx] = $change;
        writeDb($db);

        logActivity($user['name'], "updated task status: \"" . $change['tasks'][$taskIdx]['text'] . "\" (" . ($completed ? 'Completed' : 'Pending') . ")", $change['id']);

        echo json_encode(['change' => $change]);
        break;

    // 13. Add Comment to Change Request
    case 'add_comment':
        $user = getAuthenticatedUser();
        $id = $_GET['id'] ?? '';
        $text = trim($input['text'] ?? '');

        if (empty($id) || !preg_match('/^CHG-[0-9]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid change request ID format.']);
            break;
        }

        if (strlen($text) > 2000) {
            http_response_code(400);
            echo json_encode(['error' => 'Comment text exceeds maximum length of 2000 characters.']);
            break;
        }

        if (empty($text)) {
            http_response_code(400);
            echo json_encode(['error' => 'Comment text is required.']);
            break;
        }

        $db = readDb();
        $changeIdx = -1;
        for ($i = 0; $i < count($db['changes']); $i++) {
            if ($db['changes'][$i]['id'] === $id) {
                $changeIdx = $i;
                break;
            }
        }

        if ($changeIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Change request not found.']);
            break;
        }

        $now = new DateTime();
        $dateStr = $now->format('Y-m-d H:i');

        $newComment = [
            'author' => $user['name'],
            'user' => $user['name'],
            'userTitle' => $user['title'] ?? '',
            'text' => $text,
            'date' => $dateStr
        ];

        if (!isset($db['changes'][$changeIdx]['comments'])) {
            $db['changes'][$changeIdx]['comments'] = [];
        }
        $db['changes'][$changeIdx]['comments'][] = $newComment;
        writeDb($db);

        logActivity($user['name'], "added a comment: \"" . substr($text, 0, 30) . "...\"", $id);

        echo json_encode(['comment' => $newComment]);
        break;

    // 14. Delete Change Request (Drafts only)
    case 'delete_change':
        $user = getAuthenticatedUser();
        $id = $_GET['id'] ?? '';

        if (empty($id) || !preg_match('/^CHG-[0-9]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid change request ID format.']);
            break;
        }

        $db = readDb();
        $change = null;
        $changeIdx = -1;
        for ($i = 0; $i < count($db['changes']); $i++) {
            if ($db['changes'][$i]['id'] === $id) {
                $change = $db['changes'][$i];
                $changeIdx = $i;
                break;
            }
        }

        if (!$change) {
            http_response_code(404);
            echo json_encode(['error' => 'Change request not found.']);
            break;
        }

        if ($change['status'] !== 'Draft') {
            http_response_code(400);
            echo json_encode(['error' => 'Only draft change requests can be deleted.']);
            break;
        }

        if ($user['role'] !== 'Administrator' && ($change['ownerUsername'] ?? '') !== $user['username'] && ($change['requesterUsername'] ?? '') !== $user['username']) {
            http_response_code(403);
            echo json_encode(['error' => 'You can only delete draft change requests that you own.']);
            break;
        }

        array_splice($db['changes'], $changeIdx, 1);
        writeDb($db);

        logActivity($user['name'], "deleted the change request \"{$change['title']}\".", $id);

        echo json_encode(['success' => true]);
        break;

    // 15. Get Activities Feed List
    case 'activities':
        $user = getAuthenticatedUser();
        if ($user['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $db = readDb();
        echo json_encode(['activities' => $db['activities']]);
        break;

    // 15a. Check Change Conflicts
    case 'check_conflicts':
        getAuthenticatedUser();
        $date = trim($input['date'] ?? '');
        $category = trim($input['category'] ?? '');
        $assignedGroup = trim($input['assignedGroup'] ?? '');
        $excludeId = trim($input['excludeId'] ?? '');
        
        if (empty($date)) {
            echo json_encode(['conflicts' => []]);
            break;
        }
        
        $conn = getDbConnection();
        $stmt = $conn->prepare("
            SELECT id, title, targetDate, category, assignedGroup, risk, requester 
            FROM changes 
            WHERE LOWER(status) NOT IN ('completed', 'rejected', 'rolled back', 'rolled-back', 'rollback')
              AND targetDate = ?
              AND id != ?
              AND (
                (? != '' AND category = ?) OR 
                (? != '' AND assignedGroup = ?)
              )
        ");
        $stmt->execute([$date, $excludeId, $category, $category, $assignedGroup, $assignedGroup]);
        $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['conflicts' => $conflicts]);
        break;

    // 15b. Get Change Metrics & Analytics
    case 'get_analytics':
        getAuthenticatedUser();
        $conn = getDbConnection();
        
        // 1. Status Distribution
        $stmt = $conn->query("SELECT status, COUNT(*) as count FROM changes GROUP BY status");
        $statusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Risk Distribution
        $stmt = $conn->query("SELECT risk, COUNT(*) as count FROM changes GROUP BY risk");
        $riskDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Category Distribution
        $stmt = $conn->query("SELECT category, COUNT(*) as count FROM changes GROUP BY category");
        $categoryDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. Department Distribution
        $stmt = $conn->query("
            SELECT u.department, COUNT(*) as count 
            FROM changes c 
            JOIN users u ON c.requesterUsername = u.username 
            WHERE u.department IS NOT NULL AND u.department != ''
            GROUP BY u.department
        ");
        $departmentDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 5. Success rate and metrics
        $totalChanges = intval($conn->query("SELECT COUNT(*) FROM changes")->fetchColumn());
        $completedChanges = intval($conn->query("SELECT COUNT(*) FROM changes WHERE LOWER(status) = 'completed'")->fetchColumn());
        $rejectedChanges = intval($conn->query("SELECT COUNT(*) FROM changes WHERE LOWER(status) = 'rejected'")->fetchColumn());
        $rolledBackChanges = intval($conn->query("SELECT COUNT(*) FROM changes WHERE LOWER(status) IN ('rolled back', 'rolled-back', 'rollback')")->fetchColumn());
        $pendingApprovals = intval($conn->query("SELECT COUNT(*) FROM changes WHERE LOWER(status) IN ('pending approval', 'pending-approval')")->fetchColumn());
        
        $successRate = 100.0;
        $totalEvaluated = $completedChanges + $rejectedChanges + $rolledBackChanges;
        if ($totalEvaluated > 0) {
            $successRate = round(($completedChanges / $totalEvaluated) * 100, 1);
        }
        
        echo json_encode([
            'statusDistribution' => $statusDistribution,
            'riskDistribution' => $riskDistribution,
            'categoryDistribution' => $categoryDistribution,
            'departmentDistribution' => $departmentDistribution,
            'kpis' => [
                'total' => $totalChanges,
                'completed' => $completedChanges,
                'rejected' => $rejectedChanges,
                'rolledBack' => $rolledBackChanges,
                'pendingApprovals' => $pendingApprovals,
                'successRate' => $successRate
            ]
        ]);
        break;

    // 16. Get Departments List
    case 'get_departments':
        $db = readDb();
        echo json_encode(['departments' => $db['departments'] ?? []]);
        break;

    // 17. Add Department (Admin Only)
    case 'add_department':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name is required.']);
            break;
        }
        $db = readDb();
        if (!isset($db['departments'])) {
            $db['departments'] = [];
        }
        if (in_array($name, $db['departments'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Department already exists.']);
            break;
        }
        $db['departments'][] = $name;
        writeDb($db);
        logActivity($admin['name'], "added a new department: \"{$name}\"", "DEPT-NEW");
        echo json_encode(['success' => true, 'departments' => $db['departments']]);
        break;

    // 18. Delete Department (Admin Only)
    case 'delete_department':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name is required.']);
            break;
        }
        $db = readDb();
        if (!isset($db['departments']) || !in_array($name, $db['departments'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Department not found.']);
            break;
        }
        $db['departments'] = array_values(array_diff($db['departments'], [$name]));
        
        // Re-map users in deleted department to 'IT Operations' to avoid orphans
        if (!in_array('IT Operations', $db['departments'])) {
            $db['departments'][] = 'IT Operations';
        }
        if (isset($db['users'])) {
            for ($i = 0; $i < count($db['users']); $i++) {
                if (isset($db['users'][$i]['department']) && $db['users'][$i]['department'] === $name) {
                    $db['users'][$i]['department'] = 'IT Operations';
                }
            }
        }

        writeDb($db);
        logActivity($admin['name'], "deleted department: \"{$name}\"", "DEPT-DEL");
        echo json_encode(['success' => true, 'departments' => $db['departments']]);
        break;

    // 19. Add Category (Admin Only)
    case 'add_category':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name is required.']);
            break;
        }
        $db = readDb();
        if (in_array($name, $db['categories'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category already exists.']);
            break;
        }
        $db['categories'][] = $name;
        writeDb($db);
        logActivity($admin['name'], "added a new change category: \"{$name}\"", "CAT-NEW");
        echo json_encode(['success' => true, 'categories' => $db['categories']]);
        break;

    // 20. Delete Category (Admin Only)
    case 'delete_category':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name is required.']);
            break;
        }
        $db = readDb();
        if (!in_array($name, $db['categories'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found.']);
            break;
        }
        $db['categories'] = array_values(array_diff($db['categories'], [$name]));
        
        // Re-map changes in deleted category to 'General' to avoid orphans
        if (!in_array('General', $db['categories'])) {
            $db['categories'][] = 'General';
        }
        if (isset($db['changes'])) {
            for ($i = 0; $i < count($db['changes']); $i++) {
                if (isset($db['changes'][$i]['category']) && $db['changes'][$i]['category'] === $name) {
                    $db['changes'][$i]['category'] = 'General';
                }
            }
        }

        writeDb($db);
        logActivity($admin['name'], "deleted change category: \"{$name}\"", "CAT-DEL");
        echo json_encode(['success' => true, 'categories' => $db['categories']]);
        break;

    // 21. Upload Attachment to Change Request
    case 'upload_attachment':
        $user = getAuthenticatedUser();
        $id = $_GET['id'] ?? '';
        
        if (empty($id) || !preg_match('/^CHG-[0-9]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid change request ID format.']);
            break;
        }

        $fileName = trim($input['fileName'] ?? '');
        $fileType = trim($input['fileType'] ?? '');
        $fileData = $input['fileData'] ?? ''; // base64

        if (empty($fileName) || empty($fileData)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing file details.']);
            break;
        }

        $db = readDb();
        $changeIdx = -1;
        for ($i = 0; $i < count($db['changes']); $i++) {
            if ($db['changes'][$i]['id'] === $id) {
                $changeIdx = $i;
                break;
            }
        }

        if ($changeIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Change request not found.']);
            break;
        }

        $change = $db['changes'][$changeIdx];

        if ($user['role'] !== 'Administrator' && ($change['ownerUsername'] ?? '') !== $user['username'] && ($change['requesterUsername'] ?? '') !== $user['username']) {
            http_response_code(403);
            echo json_encode(['error' => 'You can only upload attachments to change requests you own.']);
            break;
        }

        $allowedExtensions = ['pdf', 'txt', 'docx', 'xlsx', 'png', 'jpg', 'jpeg', 'zip'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid file extension. Allowed types: PDF, TXT, DOCX, XLSX, PNG, JPG, JPEG, ZIP.']);
            break;
        }

        // Check file size (approximate for base64: 1.37 * size)
        $approxSize = strlen($fileData) * 3 / 4;
        if ($approxSize > 2 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['error' => 'File size exceeds the 2MB limit.']);
            break;
        }

        $base64Data = preg_replace('/^data:[^;]+;base64,/', '', $fileData);
        $decodedData = base64_decode($base64Data);

        if ($decodedData === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to decode base64 file data.']);
            break;
        }

        if (!is_dir(__DIR__ . '/attachments')) {
            mkdir(__DIR__ . '/attachments', 0755, true);
        }

        // Clean file name to prevent directory traversal
        $safeFileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
        $filename = 'attachments/chg_' . $id . '_' . time() . '_' . $safeFileName;
        $filepath = __DIR__ . '/' . $filename;

        // Delete old attachment if exists
        $oldPath = $change['attachment_path'] ?? '';
        if (!empty($oldPath) && strpos($oldPath, 'attachments/') === 0 && file_exists(__DIR__ . '/' . $oldPath)) {
            unlink(__DIR__ . '/' . $oldPath);
        }

        if (file_put_contents($filepath, $decodedData) === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save attachment to server.']);
            break;
        }

        $db['changes'][$changeIdx]['attachment_path'] = $filename;
        $db['changes'][$changeIdx]['attachment_name'] = $fileName;
        writeDb($db);

        logActivity($user['name'], "uploaded attachment '{$fileName}'", $id);

        echo json_encode([
            'success' => true,
            'attachment_path' => $filename,
            'attachment_name' => $fileName
        ]);
        break;

    // 22. Delete Attachment from Change Request
    case 'delete_attachment':
        $user = getAuthenticatedUser();
        $id = $_GET['id'] ?? '';

        if (empty($id) || !preg_match('/^CHG-[0-9]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid change request ID format.']);
            break;
        }

        $db = readDb();
        $changeIdx = -1;
        for ($i = 0; $i < count($db['changes']); $i++) {
            if ($db['changes'][$i]['id'] === $id) {
                $changeIdx = $i;
                break;
            }
        }

        if ($changeIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Change request not found.']);
            break;
        }

        $change = $db['changes'][$changeIdx];

        if ($user['role'] !== 'Administrator' && ($change['ownerUsername'] ?? '') !== $user['username'] && ($change['requesterUsername'] ?? '') !== $user['username']) {
            http_response_code(403);
            echo json_encode(['error' => 'You can only delete attachments from change requests you own.']);
            break;
        }

        $oldPath = $change['attachment_path'] ?? '';
        $oldName = $change['attachment_name'] ?? '';
        if (!empty($oldPath) && strpos($oldPath, 'attachments/') === 0 && file_exists(__DIR__ . '/' . $oldPath)) {
            unlink(__DIR__ . '/' . $oldPath);
        }

        $db['changes'][$changeIdx]['attachment_path'] = '';
        $db['changes'][$changeIdx]['attachment_name'] = '';
        writeDb($db);

        logActivity($user['name'], "deleted attachment '{$oldName}'", $id);

        echo json_encode(['success' => true]);
        break;

    // 23. Get User Groups
    case 'get_groups':
        getAuthenticatedUser(); // Verify token
        $db = readDb();
        echo json_encode(['groups' => $db['groups'] ?? []]);
        break;

    // 24. Add Group (Admin Only)
    case 'add_group':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Group name is required.']);
            break;
        }
        $db = readDb();
        if (!isset($db['groups'])) {
            $db['groups'] = [];
        }
        if (in_array($name, $db['groups'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Group already exists.']);
            break;
        }
        $db['groups'][] = $name;
        writeDb($db);
        logActivity($admin['name'], "added a new group: \"{$name}\"", "GRP-NEW");
        echo json_encode(['success' => true, 'groups' => $db['groups']]);
        break;

    // 25. Delete Group (Admin Only)
    case 'delete_group':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $name = trim($input['name'] ?? '');
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Group name is required.']);
            break;
        }
        $db = readDb();
        if (!isset($db['groups']) || !in_array($name, $db['groups'])) {
            http_response_code(404);
            echo json_encode(['error' => 'Group not found.']);
            break;
        }
        $db['groups'] = array_values(array_diff($db['groups'], [$name]));
        
        // Re-map users in deleted group to 'IT Operations' to avoid orphans
        if (!in_array('IT Operations', $db['groups'])) {
            $db['groups'][] = 'IT Operations';
        }
        if (isset($db['users'])) {
            for ($i = 0; $i < count($db['users']); $i++) {
                if (isset($db['users'][$i]['group']) && $db['users'][$i]['group'] === $name) {
                    $db['users'][$i]['group'] = 'IT Operations';
                }
            }
        }

        writeDb($db);
        logActivity($admin['name'], "deleted group: \"{$name}\"", "GRP-DEL");
        echo json_encode(['success' => true, 'groups' => $db['groups']]);
        break;

    // 26. Get Webhook Notification Settings
    case 'get_notification_settings':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $db = readDb();
        echo json_encode(['notification_settings' => $db['notification_settings'] ?? [
            "webhookUrl" => "",
            "notifyOnCreate" => true,
            "notifyOnStatusChange" => true,
            "notifyOnHighRiskOnly" => false
        ]]);
        break;

    // 27. Update Webhook Notification Settings (Admin Only)
    case 'update_notification_settings':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $webhookUrl = trim($input['webhookUrl'] ?? '');
        $notifyOnCreate = !empty($input['notifyOnCreate']);
        $notifyOnStatusChange = !empty($input['notifyOnStatusChange']);
        $notifyOnHighRiskOnly = !empty($input['notifyOnHighRiskOnly']);

        $db = readDb();
        $db['notification_settings'] = [
            "webhookUrl" => $webhookUrl,
            "notifyOnCreate" => $notifyOnCreate,
            "notifyOnStatusChange" => $notifyOnStatusChange,
            "notifyOnHighRiskOnly" => $notifyOnHighRiskOnly
        ];
        writeDb($db);
        logActivity($admin['name'], "updated webhook notification settings", "NOTIFICATION-SETTINGS");
        echo json_encode(['success' => true, 'notification_settings' => $db['notification_settings']]);
        break;

    // 27a. Get Active Directory Settings (Admin Only)
    case 'get_ad_settings':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $db = readDb();
        echo json_encode(['ad_settings' => $db['ad_settings'] ?? [
            "adEnabled" => false,
            "adServer" => "",
            "adPort" => 389,
            "adBaseDn" => "",
            "adDomain" => ""
        ]]);
        break;

    // 27b. Update Active Directory Settings (Admin Only)
    case 'update_ad_settings':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        $adEnabled = !empty($input['adEnabled']);
        $adServer = trim($input['adServer'] ?? '');
        $adPort = intval($input['adPort'] ?? 389);
        $adBaseDn = trim($input['adBaseDn'] ?? '');
        $adDomain = trim($input['adDomain'] ?? '');

        $db = readDb();
        $db['ad_settings'] = [
            "adEnabled" => $adEnabled,
            "adServer" => $adServer,
            "adPort" => $adPort,
            "adBaseDn" => $adBaseDn,
            "adDomain" => $adDomain
        ];
        writeDb($db);
        logActivity($admin['name'], "updated Active Directory settings", "AD-SETTINGS");
        echo json_encode(['success' => true, 'ad_settings' => $db['ad_settings']]);
        break;

    // 27c. Test Active Directory Connection (Admin Only)
    case 'test_ad_connection':
        $admin = getAuthenticatedUser();
        if ($admin['role'] !== 'Administrator') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden. Administrator privileges required.']);
            break;
        }
        
        $adServer = trim($input['adServer'] ?? '');
        $adPort = intval($input['adPort'] ?? 389);
        
        if (empty($adServer)) {
            http_response_code(400);
            echo json_encode(['error' => 'Active Directory Server address is required.']);
            break;
        }

        if (!function_exists('ldap_connect')) {
            http_response_code(500);
            echo json_encode(['error' => 'The PHP LDAP extension is not enabled on this server. Check your php.ini configurations.']);
            break;
        }

        // Establish connection
        $ldapconn = @ldap_connect($adServer, $adPort);
        if ($ldapconn) {
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 3); // 3 seconds timeout

            // Perform anonymous bind test
            $bind = @ldap_bind($ldapconn);
            if ($bind) {
                echo json_encode(['success' => true, 'message' => 'Connection established successfully. Anonymous bind succeeded.']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Connection established successfully. Server is reachable (Note: Anonymous bind is disabled, which is standard for Active Directory).']);
            }
            @ldap_close($ldapconn);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to connect to LDAP server at ' . $adServer . ':' . $adPort]);
        }
        break;

    // 28. Update/Edit Change Request (Owner/Admin, Draft/Under Review only)
    case 'update_change':
        $user = getAuthenticatedUser();
        $id = $_GET['id'] ?? '';

        if (empty($id) || !preg_match('/^CHG-[0-9]+$/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid change request ID format.']);
            break;
        }

        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $category = $input['category'] ?? '';
        $risk = $input['risk'] ?? '';
        $targetDate = $input['targetDate'] ?? '';
        $impact = trim($input['impact'] ?? '');
        $rollbackPlan = trim($input['rollbackPlan'] ?? '');
        $assignedGroup = trim($input['assignedGroup'] ?? '');
        $tasksInput = $input['tasks'] ?? [];
        $owner = trim($input['owner'] ?? '');
        $ownerTitle = trim($input['ownerTitle'] ?? '');

        if (empty($title) || empty($description) || empty($category) || empty($risk) || empty($targetDate) || empty($impact) || empty($rollbackPlan) || empty($tasksInput)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields.']);
            break;
        }

        if (strlen($title) > 150) {
            http_response_code(400);
            echo json_encode(['error' => 'Title exceeds maximum length of 150 characters.']);
            break;
        }

        if (strlen($description) > 5000 || strlen($impact) > 5000 || strlen($rollbackPlan) > 5000) {
            http_response_code(400);
            echo json_encode(['error' => 'Text fields exceed maximum length of 5000 characters.']);
            break;
        }

        $dateObj = DateTime::createFromFormat('Y-m-d', $targetDate);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $targetDate) {
            http_response_code(400);
            echo json_encode(['error' => 'Target date must be a valid date in YYYY-MM-DD format.']);
            break;
        }

        $allowedRisks = ['Low', 'Medium', 'High'];
        if (!in_array($risk, $allowedRisks)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid risk level.']);
            break;
        }

        $db = readDb();
        
        $validGroups = $db['groups'] ?? [];
        if (!empty($assignedGroup) && !in_array($assignedGroup, $validGroups)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid assigned group selected.']);
            break;
        }

        $changeIdx = -1;
        for ($i = 0; $i < count($db['changes']); $i++) {
            if ($db['changes'][$i]['id'] === $id) {
                $changeIdx = $i;
                break;
            }
        }

        if ($changeIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'Change request not found.']);
            break;
        }

        $oldChange = $db['changes'][$changeIdx];

        // Check if change status is Draft or Under Review
        if ($oldChange['status'] !== 'Draft' && $oldChange['status'] !== 'Under Review') {
            http_response_code(400);
            echo json_encode(['error' => 'Change request can only be edited when in Draft or Under Review status.']);
            break;
        }

        // Check permissions: Admin or Owner/Requester
        if ($user['role'] !== 'Administrator' && ($oldChange['ownerUsername'] ?? '') !== $user['username'] && ($oldChange['requesterUsername'] ?? '') !== $user['username']) {
            http_response_code(403);
            echo json_encode(['error' => 'You can only edit change requests that you own.']);
            break;
        }

        // Parse tasks
        $parsedTasks = [];
        $taskIdCounter = 1;
        foreach ($tasksInput as $t) {
            $taskText = is_array($t) ? trim($t['text'] ?? '') : trim($t);
            if (empty($taskText)) {
                continue;
            }
            if (strlen($taskText) > 250) {
                http_response_code(400);
                echo json_encode(['error' => 'Task text exceeds maximum length of 250 characters.']);
                break 2;
            }
            $parsedTasks[] = [
                'id' => $taskIdCounter++,
                'text' => $taskText,
                'completed' => is_array($t) ? (!empty($t['completed'])) : false
            ];
        }

        if (empty($parsedTasks)) {
            http_response_code(400);
            echo json_encode(['error' => 'At least one valid implementation task is required.']);
            break;
        }

        // Resolve owner username if changed
        $ownerUsername = $oldChange['ownerUsername'] ?? '';
        if (!empty($owner) && $owner !== $oldChange['owner']) {
            foreach ($db['users'] as $u) {
                if (strcasecmp($u['name'], $owner) === 0) {
                    $ownerUsername = $u['username'];
                    break;
                }
            }
        }

        // Detect modifications for revision history
        $changedFields = [];
        if ($oldChange['title'] !== $title) $changedFields[] = "Title";
        if ($oldChange['description'] !== $description) $changedFields[] = "Description";
        if ($oldChange['category'] !== $category) $changedFields[] = "Category";
        if ($oldChange['risk'] !== $risk) $changedFields[] = "Risk Level";
        if ($oldChange['targetDate'] !== $targetDate) $changedFields[] = "Target Date";
        if ($oldChange['impact'] !== $impact) $changedFields[] = "Impact Analysis";
        if ($oldChange['rollbackPlan'] !== $rollbackPlan) $changedFields[] = "Rollback Plan";
        if (($oldChange['assignedGroup'] ?? '') !== $assignedGroup) $changedFields[] = "Assigned Group";
        if (!empty($owner) && $oldChange['owner'] !== $owner) $changedFields[] = "Owner";

        // Simple task list comparison
        $oldTasksText = array_map(function($tk) { return $tk['text']; }, $oldChange['tasks']);
        $newTasksText = array_map(function($tk) { return $tk['text']; }, $parsedTasks);
        if ($oldTasksText !== $newTasksText) {
            $changedFields[] = "Task Checklist";
        }

        // Save revisions if anything changed
        if (!empty($changedFields)) {
            $now = new DateTime();
            $revisionRecord = [
                'editor' => $user['name'],
                'date' => $now->format('Y-m-d H:i'),
                'changes' => $changedFields
            ];
            
            if (!isset($oldChange['revisions'])) {
                $oldChange['revisions'] = [];
            }
            array_unshift($oldChange['revisions'], $revisionRecord);
        }

        // Apply new values
        $oldChange['title'] = $title;
        $oldChange['description'] = $description;
        $oldChange['category'] = $category;
        $oldChange['risk'] = $risk;
        $oldChange['targetDate'] = $targetDate;
        $oldChange['impact'] = $impact;
        $oldChange['rollbackPlan'] = $rollbackPlan;
        $oldChange['assignedGroup'] = $assignedGroup;
        $oldChange['tasks'] = $parsedTasks;
        if (!empty($owner)) {
            $oldChange['owner'] = $owner;
            $oldChange['ownerUsername'] = $ownerUsername;
        }
        if (!empty($ownerTitle)) {
            $oldChange['ownerTitle'] = $ownerTitle;
        }

        // Recompute progress based on tasks
        $total = count($parsedTasks);
        $completedCount = 0;
        foreach ($parsedTasks as $t) {
            if ($t['completed']) {
                $completedCount++;
            }
        }
        $oldChange['progress'] = $total > 0 ? round(($completedCount / $total) * 100) : 0;

        $db['changes'][$changeIdx] = $oldChange;
        writeDb($db);

        logActivity($user['name'], "edited change request fields: " . implode(', ', $changedFields), $id);
        sendWebhookNotification($oldChange, "edited by {$user['name']} (" . implode(', ', $changedFields) . ")", "status_change");

        echo json_encode(['change' => $oldChange]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found.']);
        break;
}
