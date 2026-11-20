-- MySQL 8.0+ / MariaDB 10.4+, InnoDB, UTC; run once in an empty database.
CREATE TABLE admins (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('owner','editor') NOT NULL DEFAULT 'editor',
 active TINYINT(1) NOT NULL DEFAULT 1,
 is_test TINYINT(1) NOT NULL DEFAULT 0,
 session_version INT NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE jobs (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 legacy_key VARCHAR(32) UNIQUE,
 category ENUM('professional','ssw','technical-intern') NOT NULL,
 status ENUM('draft','open','closed','archived') NOT NULL DEFAULT 'draft',
 version INT NOT NULL DEFAULT 1,
 updated_by INT UNSIGNED NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX jobs_status_category (status, category),
 FOREIGN KEY (updated_by) REFERENCES admins(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE job_translations (
 job_id INT UNSIGNED NOT NULL,
 locale ENUM('en','ja') NOT NULL,
 title VARCHAR(255) NOT NULL,
 summary TEXT NOT NULL,
 location VARCHAR(255) NOT NULL,
 salary VARCHAR(255) NOT NULL,
 contract VARCHAR(255) NOT NULL,
 japanese VARCHAR(255) NOT NULL,
 description MEDIUMTEXT NOT NULL,
 requirements MEDIUMTEXT NOT NULL,
 PRIMARY KEY (job_id,locale),
 FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE login_limits (
 bucket CHAR(64) CHARACTER SET ascii PRIMARY KEY,
 attempts INT UNSIGNED NOT NULL,
 window_start DATETIME NOT NULL,
 INDEX (window_start)
) ENGINE=InnoDB;
CREATE TABLE audit_log (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 admin_id INT UNSIGNED NOT NULL,
 action VARCHAR(64) NOT NULL,
 target_id INT UNSIGNED NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX (created_at),
 FOREIGN KEY (admin_id) REFERENCES admins(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
