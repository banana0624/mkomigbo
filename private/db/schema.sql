-- =========================================
-- SCHEMA for Mkomigbo Website
-- Location: project-root/private/db/schema.sql
-- =========================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS mkomigbo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mkomigbo;

-- =========================
-- Drop Tables (in reverse order of dependencies)
-- =========================
DROP TABLE IF EXISTS contributor_reactions;
DROP TABLE IF EXISTS content_tags;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS forum_replies;
DROP TABLE IF EXISTS forum_threads;
DROP TABLE IF EXISTS forums;
DROP TABLE IF EXISTS blog_comments;
DROP TABLE IF EXISTS blogs;
DROP TABLE IF EXISTS reels;
DROP TABLE IF EXISTS threads;
DROP TABLE IF EXISTS contributor_profiles;
DROP TABLE IF EXISTS contributors;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS subjects;

-- =========================
-- 1. Subjects & Pages
-- =========================
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    meta_description TEXT,
    meta_keywords TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    meta_description TEXT,
    meta_keywords TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- 2. Contributors
-- =========================
CREATE TABLE contributors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('contributor','moderator','admin') DEFAULT 'contributor',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE contributor_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contributor_id INT NOT NULL,
    full_name VARCHAR(255),
    bio TEXT,
    avatar VARCHAR(255),
    website VARCHAR(255),
    location VARCHAR(255),
    FOREIGN KEY (contributor_id) REFERENCES contributors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- 3. Platforms (Blogs, Forums, Reels, Threads)
-- =========================
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contributor_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contributor_id) REFERENCES contributors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    contributor_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (contributor_id) REFERENCES contributors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE forums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE forum_threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    forum_id INT NOT NULL,
    contributor_id INT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
    FOREIGN KEY (contributor_id) REFERENCES contributors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE forum_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    contributor_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES forum_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (contributor_id) REFERENCES contributors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE reels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contributor_id INT,
    title VARCHAR(255),
    video_url VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contributor_id) REFERENCES contributors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contributor_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contributor_id) REFERENCES contributors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- 4. Tags
-- =========================
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE content_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_id INT NOT NULL,
    blog_id INT,
    thread_id INT,
    reel_id INT,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
    FOREIGN KEY (reel_id) REFERENCES reels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- 5. Contributor Reactions
-- =========================
CREATE TABLE contributor_reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contributor_id INT NOT NULL,
    page_id INT,
    blog_id INT,
    thread_id INT,
    type ENUM('like','comment','rating') NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contributor_id) REFERENCES contributors(id) ON DELETE CASCADE,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (thread_id) REFERENCES forum_threads(id) ON DELETE CASCADE
) ENGINE=InnoDB;
