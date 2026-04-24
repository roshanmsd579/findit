<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="padding:40px;font-family:Arial,sans-serif;color:#ef4444">'
        . '<h2>Database Connection Failed</h2>'
        . '<p>' . h($e->getMessage()) . '</p>'
        . '<p>Make sure XAMPP MySQL is running and findit_db exists.</p>'
        . '</div>');
}

// Keep the app resilient on partially-initialized local databases.
// This creates missing tables/columns used by the portal without requiring a manual SQL import first.
function bootstrap_schema(PDO $pdo): void
{
    $createStatements = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(20) NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NULL,
            role ENUM('student','faculty','staff','security','admin') DEFAULT 'student',
            department VARCHAR(100) NULL,
            avatar VARCHAR(255) NULL,
            rating DECIMAL(3,2) DEFAULT 0.00,
            rating_count INT DEFAULT 0,
            is_verified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS reports (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            type ENUM('lost','found') NOT NULL,
            category ENUM('id_card','library_card','laptop','phone','wallet','keys','stationery','clothing','book','calculator','usb_drive','earbuds','other') NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            photo VARCHAR(255) NULL,
            campus_location VARCHAR(100) NULL,
            latitude DECIMAL(10,7) NULL,
            longitude DECIMAL(10,7) NULL,
            date_occurred DATE NULL,
            time_occurred TIME NULL,
            contact_phone VARCHAR(20) NULL,
            secret_question VARCHAR(255) NULL,
            secret_answer VARCHAR(255) NULL,
            reward_offered TINYINT(1) DEFAULT 0,
            reward_amount DECIMAL(10,2) NULL,
            status ENUM('active','claimed','verified','resolved','disputed','closed') DEFAULT 'active',
            views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_reports_user_id (user_id),
            CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS claims (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            report_id INT UNSIGNED NOT NULL,
            claimant_id INT UNSIGNED NOT NULL,
            secret_answer VARCHAR(255) NULL,
            answer_correct TINYINT(1) DEFAULT 0,
            verification_code VARCHAR(8) NULL,
            code_entered VARCHAR(8) NULL,
            reporter_confirmed TINYINT(1) DEFAULT 0,
            claimant_confirmed TINYINT(1) DEFAULT 0,
            proof_photo VARCHAR(255) NULL,
            status ENUM('pending','code_sent','verified','rejected','disputed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_claims_report_id (report_id),
            INDEX idx_claims_claimant_id (claimant_id),
            CONSTRAINT fk_claims_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
            CONSTRAINT fk_claims_claimant FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS chat_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            report_id INT UNSIGNED NOT NULL,
            sender_id INT UNSIGNED NOT NULL,
            receiver_id INT UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_chat_report_id (report_id),
            INDEX idx_chat_sender_id (sender_id),
            INDEX idx_chat_receiver_id (receiver_id),
            CONSTRAINT fk_chat_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
            CONSTRAINT fk_chat_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_chat_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS notifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            report_id INT UNSIGNED NULL,
            type VARCHAR(50) NULL,
            message TEXT NOT NULL,
            link VARCHAR(255) NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notifications_user_id (user_id),
            CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS comments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            report_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_comments_report_id (report_id),
            INDEX idx_comments_user_id (user_id),
            CONSTRAINT fk_comments_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
            CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS reviews (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            claim_id INT UNSIGNED NOT NULL,
            reviewer_id INT UNSIGNED NOT NULL,
            reviewed_id INT UNSIGNED NOT NULL,
            rating TINYINT NOT NULL,
            comment TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_reviews_claim_id (claim_id),
            INDEX idx_reviews_reviewer_id (reviewer_id),
            INDEX idx_reviews_reviewed_id (reviewed_id),
            CONSTRAINT fk_reviews_claim FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
            CONSTRAINT fk_reviews_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_reviews_reviewed FOREIGN KEY (reviewed_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS disputes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            claim_id INT UNSIGNED NOT NULL,
            raised_by INT UNSIGNED NOT NULL,
            reason TEXT NOT NULL,
            status ENUM('open','under_review','resolved') DEFAULT 'open',
            admin_note TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_disputes_claim_id (claim_id),
            INDEX idx_disputes_raised_by (raised_by),
            CONSTRAINT fk_disputes_claim FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
            CONSTRAINT fk_disputes_raised_by FOREIGN KEY (raised_by) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS matches (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            lost_report_id INT UNSIGNED NOT NULL,
            found_report_id INT UNSIGNED NOT NULL,
            score INT DEFAULT 0,
            status ENUM('pending','confirmed','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_matches_lost_report_id (lost_report_id),
            INDEX idx_matches_found_report_id (found_report_id),
            CONSTRAINT fk_matches_lost FOREIGN KEY (lost_report_id) REFERENCES reports(id) ON DELETE CASCADE,
            CONSTRAINT fk_matches_found FOREIGN KEY (found_report_id) REFERENCES reports(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    $alterStatements = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS student_id VARCHAR(20) NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('student','faculty','staff','security','admin') DEFAULT 'student'",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS department VARCHAR(100) NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS rating DECIMAL(3,2) DEFAULT 0.00",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS rating_count INT DEFAULT 0",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0",

        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS photo VARCHAR(255) NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS campus_location VARCHAR(100) NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS date_occurred DATE NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS time_occurred TIME NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS contact_phone VARCHAR(20) NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS secret_question VARCHAR(255) NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS secret_answer VARCHAR(255) NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS reward_offered TINYINT(1) DEFAULT 0",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS reward_amount DECIMAL(10,2) NULL",
        "ALTER TABLE reports ADD COLUMN IF NOT EXISTS views INT DEFAULT 0",

        "ALTER TABLE claims ADD COLUMN IF NOT EXISTS secret_answer VARCHAR(255) NULL",
        "ALTER TABLE claims ADD COLUMN IF NOT EXISTS answer_correct TINYINT(1) DEFAULT 0",
        "ALTER TABLE claims ADD COLUMN IF NOT EXISTS verification_code VARCHAR(8) NULL",
        "ALTER TABLE claims ADD COLUMN IF NOT EXISTS code_entered VARCHAR(8) NULL",
        "ALTER TABLE claims ADD COLUMN IF NOT EXISTS reporter_confirmed TINYINT(1) DEFAULT 0",
        "ALTER TABLE claims ADD COLUMN IF NOT EXISTS claimant_confirmed TINYINT(1) DEFAULT 0",
        "ALTER TABLE claims ADD COLUMN IF NOT EXISTS proof_photo VARCHAR(255) NULL",
        "ALTER TABLE claims ADD COLUMN IF NOT EXISTS status ENUM('pending','code_sent','verified','rejected','disputed') DEFAULT 'pending'",

        "ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS is_read TINYINT(1) DEFAULT 0",

        "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS report_id INT UNSIGNED NULL",
        "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS type VARCHAR(50) NULL",
        "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS link VARCHAR(255) NULL",
        "ALTER TABLE notifications ADD COLUMN IF NOT EXISTS is_read TINYINT(1) DEFAULT 0",

        "ALTER TABLE reviews ADD COLUMN IF NOT EXISTS claim_id INT UNSIGNED NULL",
        "ALTER TABLE reviews ADD COLUMN IF NOT EXISTS reviewer_id INT UNSIGNED NULL",
        "ALTER TABLE reviews ADD COLUMN IF NOT EXISTS reviewed_id INT UNSIGNED NULL",
        "ALTER TABLE reviews ADD COLUMN IF NOT EXISTS rating TINYINT NULL",
        "ALTER TABLE reviews ADD COLUMN IF NOT EXISTS comment TEXT NULL",

        "ALTER TABLE disputes ADD COLUMN IF NOT EXISTS status ENUM('open','under_review','resolved') DEFAULT 'open'",
        "ALTER TABLE disputes ADD COLUMN IF NOT EXISTS admin_note TEXT NULL",

        "ALTER TABLE matches ADD COLUMN IF NOT EXISTS score INT DEFAULT 0",
        "ALTER TABLE matches ADD COLUMN IF NOT EXISTS status ENUM('pending','confirmed','rejected') DEFAULT 'pending'",
    ];

    foreach ($createStatements as $sql) {
        $pdo->exec($sql);
    }

    foreach ($alterStatements as $sql) {
        $pdo->exec($sql);
    }

    // Retire legacy human/animal categories and old demo rows so listings stay college-item focused.
    $pdo->exec("UPDATE reports
                SET category = 'other', status = 'closed'
                WHERE category IN ('pet', 'person')
                   OR LOWER(title) REGEXP 'golden retriever|elderly woman|tabby cat|rahul jr|missing:'
                   OR LOWER(description) REGEXP 'friendly dog|elderly lady|missing since friday'");
    $pdo->exec("ALTER TABLE reports MODIFY COLUMN category ENUM('id_card','library_card','laptop','phone','wallet','keys','stationery','clothing','book','calculator','usb_drive','earbuds','other') NOT NULL");

    // Backfill campus_location for legacy rows that only had `location` data in older schemas.
    $pdo->exec("UPDATE reports SET campus_location = COALESCE(campus_location, 'Campus') WHERE campus_location IS NULL OR campus_location = ''");
}

try {
    bootstrap_schema($pdo);
} catch (PDOException $_bootstrapError) {
    // Keep runtime alive; feature-level pages can still function partially if schema migration fails.
}
?>
