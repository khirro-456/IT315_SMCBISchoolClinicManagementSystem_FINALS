-- Reference only: the system creates these tables automatically on first run.
-- Create an empty database named clinicdb in phpMyAdmin, then open the website.

CREATE TABLE IF NOT EXISTS app_meta (
        k VARCHAR(50) PRIMARY KEY,
        v VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        username VARCHAR(80) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','nurse') NOT NULL DEFAULT 'nurse',
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        must_change_password TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id VARCHAR(50) NOT NULL UNIQUE,
        patient_type ENUM('student','personnel') NOT NULL,
        first_name VARCHAR(80) NOT NULL,
        middle_name VARCHAR(80) DEFAULT NULL,
        last_name VARCHAR(80) NOT NULL,
        sex ENUM('Male','Female') DEFAULT NULL,
        birth_date DATE DEFAULT NULL,
        grade_department VARCHAR(120) DEFAULT NULL,
        contact_number VARCHAR(30) DEFAULT NULL,
        guardian_name VARCHAR(150) DEFAULT NULL,
        guardian_contact VARCHAR(30) DEFAULT NULL,
        allergies TEXT DEFAULT NULL,
        medical_conditions TEXT DEFAULT NULL,
        emergency_contact_name VARCHAR(150) DEFAULT NULL,
        emergency_contact_number VARCHAR(30) DEFAULT NULL,
        remarks TEXT DEFAULT NULL,
        portal_code CHAR(6) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS portal_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id VARCHAR(50) NOT NULL,
        ip VARCHAR(45) NOT NULL,
        attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (school_id, attempted_at),
        INDEX (ip, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_code VARCHAR(50) NOT NULL UNIQUE,
        item_name VARCHAR(150) NOT NULL,
        category ENUM('Medicine','First Aid Supply','Medical Equipment','Clinic Supply') NOT NULL,
        unit VARCHAR(50) NOT NULL,
        current_quantity INT NOT NULL DEFAULT 0,
        minimum_stock INT NOT NULL DEFAULT 10,
        expiration_date DATE DEFAULT NULL,
        supplier VARCHAR(150) DEFAULT NULL,
        remarks TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clinic_visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        attended_by INT NOT NULL,
        visit_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        chief_complaint TEXT NOT NULL,
        temperature VARCHAR(20) DEFAULT NULL,
        blood_pressure VARCHAR(30) DEFAULT NULL,
        pulse_rate VARCHAR(20) DEFAULT NULL,
        assessment TEXT DEFAULT NULL,
        treatment_given TEXT DEFAULT NULL,
        recommendation TEXT DEFAULT NULL,
        referred_to_hospital ENUM('Yes','No') NOT NULL DEFAULT 'No',
        guardian_notified ENUM('Yes','No') NOT NULL DEFAULT 'No',
        notes TEXT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visit_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clinic_visit_id INT NOT NULL,
        inventory_item_id INT NOT NULL,
        quantity_used INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        inventory_item_id INT NOT NULL,
        transaction_type ENUM('Stock In','Stock Out','Visit Usage','Adjustment') NOT NULL,
        transaction_reason VARCHAR(100) DEFAULT NULL,
        quantity INT NOT NULL,
        description TEXT DEFAULT NULL,
        processed_by INT DEFAULT NULL,
        transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        notification_key VARCHAR(191) NOT NULL UNIQUE,
        notification_type ENUM('out_of_stock','critical_stock','low_stock','near_expiry','expired') NOT NULL,
        priority ENUM('critical','warning','info') NOT NULL DEFAULT 'info',
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        inventory_item_id INT DEFAULT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_at DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(80) NOT NULL,
        ip VARCHAR(45) NOT NULL,
        attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (username, attempted_at),
        INDEX (ip, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    user_name VARCHAR(150) DEFAULT NULL,
    event VARCHAR(60) NOT NULL,
    outcome VARCHAR(20) NOT NULL DEFAULT 'success',
    details VARCHAR(255) NOT NULL DEFAULT '',
    ip VARCHAR(45) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
