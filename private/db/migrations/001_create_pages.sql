-- project-root/private/db/migrations/001_create_pages.sql --

-- subjects registry (optional if you already have one)
CREATE TABLE IF NOT EXISTS subjects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  nav_order INT NOT NULL DEFAULT 0,
  meta_description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- generic pages table (subject_scoped)
CREATE TABLE IF NOT EXISTS pages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subject_slug VARCHAR(64) NOT NULL,             -- e.g., 'history'
  title VARCHAR(180) NOT NULL,
  slug VARCHAR(180) NOT NULL,                     -- e.g., 'intro'
  summary TEXT NULL,
  body MEDIUMTEXT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_subject_slug (subject_slug, slug),
  INDEX idx_subject (subject_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- optional media table (can be wired later)
CREATE TABLE IF NOT EXISTS media (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subject_slug VARCHAR(64) NOT NULL,
  kind ENUM('image','file','video','audio') DEFAULT 'image',
  path VARCHAR(255) NOT NULL,                     -- /lib/uploads/...
  title VARCHAR(180) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_media_subject (subject_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
