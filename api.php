<?php
// Hopper Backend API Router & Controller - Zero Dependency PHP Version
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
$configPath = __DIR__ . '/config.json';
$config = ['jwtSecret' => 'hopper-default-secret-key-123456', 'webhookUrl' => ''];
if (file_exists($configPath)) {
    $decodedConfig = json_decode(file_get_contents($configPath), true);
    if ($decodedConfig) {
        $config = array_merge($config, $decodedConfig);
    }
}
define('JWT_SECRET', $config['jwtSecret']);
define('WEBHOOK_URL', $config['webhookUrl']);
define('DB_PATH', __DIR__ . '/data.json');

// --- DATABASE HELPER FUNCTIONS ---
function readDb() {
    if (!file_exists(DB_PATH)) {
        // Initial Database Seeding
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
                'department' => 'Management'
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
                'department' => 'IT Operations'
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
                'department' => 'Technical Service'
            ]
        ];
        $defaultDb = [
            'users' => $users,
            'changes' => [],
            'activities' => [],
            'registration_requests' => [],
            'categories' => [
                "Software Development",
                "Database Management",
                "Network & Security",
                "System & Server",
                "Cloud Infrastructure",
                "Hardware & Infrastructure"
            ],
            'departments' => [
                'Management', 'IT Operations', 'Human Resources', 'Accounting', 'Sales',
                'Marketing', 'R&D', 'Logistics', 'Warehouse', 'Security',
                'Technical Service', 'Quality Control', 'Customer Services', 'Training', 'Purchasing', 'Finance & Accounting'
            ]
        ];
        writeDb($defaultDb);
        return $defaultDb;
    }
    
    $fp = @fopen(DB_PATH, 'rb');
    if (!$fp) {
        http_response_code(500);
        echo json_encode(['error' => 'Database read error. Please retry.']);
        exit;
    }
    @flock($fp, LOCK_SH);
    $raw = @stream_get_contents($fp);
    @flock($fp, LOCK_UN);
    @fclose($fp);
    
    if ($raw === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Database read error. Please retry.']);
        exit;
    }
    
    $db = json_decode($raw, true);
    if ($db === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Database parse error. Data may be locked or corrupted.']);
        exit;
    }
    
    $migrated = false;
    
    // --- DATABASE SCHEMA MIGRATION ---
    if (isset($db['users'])) {
        for ($i = 0; $i < count($db['users']); $i++) {
            if (!isset($db['users'][$i]['avatar'])) {
                $db['users'][$i]['avatar'] = '';
                $migrated = true;
            }
            if (!isset($db['users'][$i]['email'])) {
                $db['users'][$i]['email'] = '';
                $migrated = true;
            }
            if (!isset($db['users'][$i]['phone'])) {
                $db['users'][$i]['phone'] = '';
                $migrated = true;
            }
            if (!isset($db['users'][$i]['department'])) {
                $role = $db['users'][$i]['role'] ?? '';
                if ($role === 'Administrator') {
                    $db['users'][$i]['department'] = 'Management';
                } elseif ($role === 'CAB Approver') {
                    $db['users'][$i]['department'] = 'IT Operations';
                } else {
                    $db['users'][$i]['department'] = 'Technical Service';
                }
                $migrated = true;
            }
            if (!isset($db['users'][$i]['status'])) {
                $db['users'][$i]['status'] = '';
                $migrated = true;
            }
        }
    }
    if (!isset($db['registration_requests'])) {
        $db['registration_requests'] = [];
        $migrated = true;
    }
    if (!isset($db['departments'])) {
        $db['departments'] = [
            'Management', 'IT Operations', 'Human Resources', 'Accounting', 'Sales',
            'Marketing', 'R&D', 'Logistics', 'Warehouse', 'Security',
            'Technical Service', 'Quality Control', 'Customer Services', 'Training', 'Purchasing', 'Finance & Accounting'
        ];
        $migrated = true;
    }
    
    if ($migrated) {
        writeDb($db);
    }
    
    return $db;
}

function writeDb($data) {
    $fp = @fopen(DB_PATH, 'c+b');
    if ($fp) {
        @flock($fp, LOCK_EX);
        @ftruncate($fp, 0);
        @fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        @fflush($fp);
        @flock($fp, LOCK_UN);
        @fclose($fp);
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
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
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
    $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    if (!hash_equals($signature, $expectedSignature)) {
        return null;
    }
    
    return json_decode(base64UrlDecode($base64UrlPayload), true);
}

// --- AUTHENTICATION MIDDLEWARE ---
function getAuthenticatedUser() {
    $headers = getallheaders();
    $authHeader = '';
    
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $payload = decodeJwt($matches[1]);
        if ($payload && isset($payload['id'])) {
            // Fetch fresh details from DB to prevent stale JWT data (like changed roles)
            $db = readDb();
            foreach ($db['users'] as $u) {
                if ($u['id'] === $payload['id']) {
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

// --- LOG ACTIVITY HELPER ---
function logActivity($user, $action, $targetId) {
    $db = readDb();
    $now = new DateTime();
    $dateStr = $now->format('Y-m-d H:i');
    
    array_unshift($db['activities'], [
        'id' => round(microtime(true) * 1000),
        'user' => $user,
        'action' => $action,
        'target' => $targetId,
        'date' => $dateStr
    ]);

    // Keep max 30 activities
    if (count($db['activities']) > 30) {
        array_pop($db['activities']);
    }
    
    writeDb($db);
}

// --- WEBHOOK NOTIFICATION TRIGGER ---
function sendWebhookNotification($change, $actionText) {
    $webhookUrl = WEBHOOK_URL;
    if (empty($webhookUrl)) return;
    
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

switch ($action) {
    // 1. User Login
    case 'login':
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password required.']);
            break;
        }

        $db = readDb();
        
        $user = null;
        foreach ($db['users'] as $u) {
            if (strtolower($u['username']) === strtolower($username)) {
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
                    'status' => $user['status'] ?? ''
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
        $password = $input['password'] ?? '';
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
                exit;
            }
        }

        // Check pending/approved registration requests
        if (isset($db['registration_requests'])) {
            foreach ($db['registration_requests'] as $req) {
                if (strtolower($req['username']) === strtolower($username) && $req['status'] !== 'Rejected') {
                    http_response_code(409);
                    echo json_encode(['error' => 'This username is already taken or pending approval.']);
                    exit;
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
            if ($u['id'] === $userPayload['id']) {
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
                'status' => $user['status'] ?? ''
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
        $newPassword = $input['newPassword'] ?? '';
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

        $db = readDb();
        $userIdx = -1;
        for ($i = 0; $i < count($db['users']); $i++) {
            if ($db['users'][$i]['id'] === $userPayload['id']) {
                $userIdx = $i;
                break;
            }
        }

        if ($userIdx === -1) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            break;
        }

        if ($avatar !== null && !empty($avatar)) {
            if (preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $avatar, $matches)) {
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
                'department' => $u['department'] ?? ''
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
            'avatar' => $req['avatar'] ?? ''
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

        if ($targetUserId === $admin['id']) {
            http_response_code(400);
            echo json_encode(['error' => 'You cannot modify your own administrator role.']);
            break;
        }

        $db = readDb();
        $userIdx = -1;
        for ($i = 0; $i < count($db['users']); $i++) {
            if ($db['users'][$i]['id'] === $targetUserId) {
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
        $role = trim($input['role'] ?? '');
        $newPassword = $input['newPassword'] ?? '';

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

        $allowedRoles = ['Requester', 'CAB Approver', 'Administrator'];
        if (!in_array($role, $allowedRoles)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid role specified.']);
            break;
        }

        if ($targetUserId === $admin['id'] && $role !== 'Administrator') {
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
            if ($db['users'][$i]['id'] === $targetUserId) {
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
                'department' => $db['users'][$userIdx]['department']
            ]
        ];

        // If the admin updated themselves, regenerate token
        if ($targetUserId === $admin['id']) {
            $newTokenPayload = [
                'id' => $db['users'][$userIdx]['id'],
                'username' => $db['users'][$userIdx]['username'],
                'name' => $db['users'][$userIdx]['name'],
                'role' => $db['users'][$userIdx]['role'],
                'title' => $db['users'][$userIdx]['title'],
                'department' => $db['users'][$userIdx]['department']
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
            'requesterTitle' => empty($requesterTitle) ? $user['title'] : $requesterTitle,
            'owner' => empty($owner) ? $user['name'] : $owner,
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
            'comments' => []
        ];

        array_unshift($db['changes'], $newChange);
        writeDb($db);

        logActivity($user['name'], "created a new change request: \"{$title}\"", $newId);
        sendWebhookNotification($newChange, "created by {$user['name']}");

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
                if ($change['owner'] !== $user['name'] && $change['requester'] !== $user['name']) {
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
        if ($user['role'] !== 'Administrator' && $change['owner'] !== $user['name'] && $change['requester'] !== $user['name']) {
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

        if ($user['role'] !== 'Administrator' && $change['owner'] !== $user['name'] && $change['requester'] !== $user['name']) {
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
        getAuthenticatedUser();
        $db = readDb();
        echo json_encode(['activities' => $db['activities']]);
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

        if ($user['role'] !== 'Administrator' && $change['owner'] !== $user['name'] && $change['requester'] !== $user['name']) {
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

        if ($user['role'] !== 'Administrator' && $change['owner'] !== $user['name'] && $change['requester'] !== $user['name']) {
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

    default:
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found.']);
        break;
}
