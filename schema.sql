-- Relational Schema for Hopper MySQL Database

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    title VARCHAR(100) DEFAULT '',
    avatar LONGTEXT,
    email VARCHAR(255) DEFAULT '',
    phone VARCHAR(50) DEFAULT '',
    department VARCHAR(100) DEFAULT '',
    status VARCHAR(50) DEFAULT '',
    `group` VARCHAR(100) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Registration Requests Table
CREATE TABLE IF NOT EXISTS registration_requests (
    id BIGINT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    title VARCHAR(100) DEFAULT '',
    department VARCHAR(100) DEFAULT '',
    email VARCHAR(255) DEFAULT '',
    phone VARCHAR(50) DEFAULT '',
    avatar LONGTEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    request_date VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Changes Table
CREATE TABLE IF NOT EXISTS changes (
    id VARCHAR(50) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    requester VARCHAR(255) DEFAULT '',
    requesterTitle VARCHAR(100) DEFAULT '',
    owner VARCHAR(255) DEFAULT '',
    ownerTitle VARCHAR(100) DEFAULT '',
    category VARCHAR(100) DEFAULT '',
    risk VARCHAR(50) DEFAULT '',
    status VARCHAR(50) DEFAULT '',
    targetDate VARCHAR(50) DEFAULT '',
    impact TEXT,
    rollbackPlan TEXT,
    progress INT DEFAULT 0,
    assignedGroup VARCHAR(100) DEFAULT '',
    ownerUsername VARCHAR(100) DEFAULT '',
    requesterUsername VARCHAR(100) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Change Tasks Table
CREATE TABLE IF NOT EXISTS change_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    change_id VARCHAR(50) NOT NULL,
    task_number INT NOT NULL,
    text VARCHAR(255) NOT NULL,
    completed TINYINT DEFAULT 0,
    FOREIGN KEY (change_id) REFERENCES changes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Change Approvals Table
CREATE TABLE IF NOT EXISTS change_approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    change_id VARCHAR(50) NOT NULL,
    role VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    date VARCHAR(50) DEFAULT '',
    FOREIGN KEY (change_id) REFERENCES changes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Change Comments Table
CREATE TABLE IF NOT EXISTS change_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    change_id VARCHAR(50) NOT NULL,
    user VARCHAR(255) NOT NULL,
    userTitle VARCHAR(100) DEFAULT '',
    text TEXT NOT NULL,
    date VARCHAR(50) NOT NULL,
    FOREIGN KEY (change_id) REFERENCES changes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Change Revisions Table
CREATE TABLE IF NOT EXISTS change_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    change_id VARCHAR(50) NOT NULL,
    editor VARCHAR(255) NOT NULL,
    date VARCHAR(50) NOT NULL,
    changed_fields TEXT NOT NULL,
    FOREIGN KEY (change_id) REFERENCES changes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activities (Audit Logs) Table
CREATE TABLE IF NOT EXISTS activities (
    id BIGINT PRIMARY KEY,
    user VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    target VARCHAR(100) DEFAULT '',
    date VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Categories Table
CREATE TABLE IF NOT EXISTS categories (
    name VARCHAR(100) PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Departments Table
CREATE TABLE IF NOT EXISTS departments (
    name VARCHAR(100) PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Groups Table
CREATE TABLE IF NOT EXISTS groups (
    name VARCHAR(100) PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notification Settings Table
CREATE TABLE IF NOT EXISTS notification_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    webhookUrl VARCHAR(255) DEFAULT '',
    notifyOnCreate TINYINT DEFAULT 1,
    notifyOnStatusChange TINYINT DEFAULT 1,
    notifyOnHighRiskOnly TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Active Directory / LDAP Settings Table
CREATE TABLE IF NOT EXISTS ad_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adEnabled TINYINT DEFAULT 0,
    adServer VARCHAR(255) DEFAULT '',
    adPort INT DEFAULT 389,
    adBaseDn VARCHAR(255) DEFAULT '',
    adDomain VARCHAR(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Settings Table
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

