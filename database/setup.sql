CREATE DATABASE IF NOT EXISTS findit_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE findit_db;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id    VARCHAR(20) UNIQUE,
  name          VARCHAR(255) NOT NULL,
  email         VARCHAR(255) UNIQUE NOT NULL,
  password      VARCHAR(255) NOT NULL,
  phone         VARCHAR(20),
  role          ENUM('student','faculty','staff','security','admin') DEFAULT 'student',
  department    VARCHAR(100),
  avatar        VARCHAR(255),
  rating        DECIMAL(3,2) DEFAULT 0.00,
  rating_count  INT DEFAULT 0,
  is_verified   TINYINT(1) DEFAULT 0,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reports (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NOT NULL,
  type            ENUM('lost','found') NOT NULL,
  category        ENUM('id_card','library_card','laptop','phone','wallet','keys','stationery','clothing','pet','person','other') NOT NULL,
  title           VARCHAR(255) NOT NULL,
  description     TEXT NOT NULL,
  photo           VARCHAR(255),
  campus_location VARCHAR(100) NOT NULL,
  latitude        DECIMAL(10,7),
  longitude       DECIMAL(10,7),
  date_occurred   DATE NOT NULL,
  time_occurred   TIME,
  contact_phone   VARCHAR(20),
  secret_question VARCHAR(255),
  secret_answer   VARCHAR(255),
  reward_offered  TINYINT(1) DEFAULT 0,
  reward_amount   DECIMAL(10,2),
  status          ENUM('active','claimed','verified','resolved','disputed','closed') DEFAULT 'active',
  views           INT DEFAULT 0,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE claims (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id          INT UNSIGNED NOT NULL,
  claimant_id        INT UNSIGNED NOT NULL,
  secret_answer      VARCHAR(255),
  answer_correct     TINYINT(1) DEFAULT 0,
  verification_code  VARCHAR(8),
  code_entered       VARCHAR(8),
  reporter_confirmed TINYINT(1) DEFAULT 0,
  claimant_confirmed TINYINT(1) DEFAULT 0,
  proof_photo        VARCHAR(255),
  status             ENUM('pending','code_sent','verified','rejected','disputed') DEFAULT 'pending',
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
  FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE chat_messages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id   INT UNSIGNED NOT NULL,
  sender_id   INT UNSIGNED NOT NULL,
  receiver_id INT UNSIGNED NOT NULL,
  message     TEXT NOT NULL,
  is_read     TINYINT(1) DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  report_id  INT UNSIGNED,
  type       VARCHAR(50),
  message    TEXT NOT NULL,
  link       VARCHAR(255),
  is_read    TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE comments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id  INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  message    TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  claim_id    INT UNSIGNED NOT NULL,
  reviewer_id INT UNSIGNED NOT NULL,
  reviewed_id INT UNSIGNED NOT NULL,
  rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment     TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE disputes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  claim_id    INT UNSIGNED NOT NULL,
  raised_by   INT UNSIGNED NOT NULL,
  reason      TEXT NOT NULL,
  status      ENUM('open','under_review','resolved') DEFAULT 'open',
  admin_note  TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
  FOREIGN KEY (raised_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE matches (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lost_report_id  INT UNSIGNED NOT NULL,
  found_report_id INT UNSIGNED NOT NULL,
  score           INT DEFAULT 0,
  status          ENUM('pending','confirmed','rejected') DEFAULT 'pending',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (lost_report_id) REFERENCES reports(id) ON DELETE CASCADE,
  FOREIGN KEY (found_report_id) REFERENCES reports(id) ON DELETE CASCADE
);
