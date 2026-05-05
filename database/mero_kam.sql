-- Mero Kam - Full database schema for XAMPP / phpMyAdmin
-- Charset: utf8mb4 for Nepali + English

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS mero_kam;
CREATE DATABASE mero_kam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mero_kam;

-- Roles: seeker | employer | admin
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('seeker','employer','admin') NOT NULL DEFAULT 'seeker',
  phone VARCHAR(40) DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  language_pref VARCHAR(8) DEFAULT 'en',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_role (role),
  INDEX idx_email (email)
) ENGINE=InnoDB;

CREATE TABLE companies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  company_name VARCHAR(200) NOT NULL,
  logo VARCHAR(255) DEFAULT NULL,
  description TEXT,
  website VARCHAR(255) DEFAULT NULL,
  location VARCHAR(200) DEFAULT NULL,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  description MEDIUMTEXT NOT NULL,
  salary_min INT UNSIGNED DEFAULT NULL,
  salary_max INT UNSIGNED DEFAULT NULL,
  location VARCHAR(200) DEFAULT NULL,
  type ENUM('full-time','part-time','contract','internship') NOT NULL DEFAULT 'full-time',
  experience_level ENUM('entry','mid','senior','lead') NOT NULL DEFAULT 'mid',
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  views_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  INDEX idx_company (company_id),
  INDEX idx_status (status),
  INDEX idx_featured (is_featured),
  FULLTEXT KEY ft_jobs (title, description)
) ENGINE=InnoDB;

CREATE TABLE applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  cv_path VARCHAR(255) DEFAULT NULL,
  cover_letter TEXT,
  status ENUM('pending','shortlisted','rejected','hired') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_job_user (job_id, user_id),
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_job (job_id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE saved_jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_saved (user_id, job_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE job_views (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  job_id INT UNSIGNED NOT NULL,
  viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  session_id VARCHAR(64) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  INDEX idx_user_job (user_id, job_id),
  INDEX idx_job (job_id)
) ENGINE=InnoDB;

CREATE TABLE user_skills (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  skill VARCHAR(100) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE job_alerts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  keywords VARCHAR(255) NOT NULL,
  location VARCHAR(200) DEFAULT NULL,
  salary_min INT UNSIGNED DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE resume_data (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  summary TEXT,
  experience TEXT,
  education TEXT,
  skills TEXT,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  currency VARCHAR(8) NOT NULL DEFAULT 'NPR',
  job_credits INT UNSIGNED NOT NULL DEFAULT 0,
  featured_jobs INT UNSIGNED NOT NULL DEFAULT 0,
  duration_days INT UNSIGNED NOT NULL DEFAULT 30,
  features TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'NPR',
  status ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  reference VARCHAR(120) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT,
  INDEX idx_user (user_id),
  INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE employer_credits (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  job_credits INT UNSIGNED NOT NULL DEFAULT 0,
  featured_credits INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  from_user_id INT UNSIGNED NOT NULL,
  to_user_id INT UNSIGNED NOT NULL,
  job_id INT UNSIGNED DEFAULT NULL,
  body TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL,
  INDEX idx_thread (from_user_id, to_user_id),
  INDEX idx_to (to_user_id, is_read)
) ENGINE=InnoDB;

-- Seed admin — default password: password (change immediately in production)
INSERT INTO users (name, email, password, role) VALUES
('Mero Kam Admin', 'admin@merokam.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO plans (name, slug, price, job_credits, featured_jobs, duration_days, features) VALUES
('Basic', 'basic', 0.00, 3, 0, 30, '3 free job posts per month'),
('Premium', 'premium', 999.00, 20, 2, 30, '20 posts + 2 featured slots'),
('Pay Per Job', 'pay-per-job', 199.00, 1, 0, 0, 'Single job post');

-- Demo accounts (password for all: password)
INSERT INTO users (name, email, password, role) VALUES
('Demo Employer', 'employer@merokam.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer');
SET @emp_uid = LAST_INSERT_ID();
INSERT INTO employer_credits (user_id, job_credits, featured_credits) VALUES (@emp_uid, 10, 2);
INSERT INTO companies (user_id, company_name, description, location, verified) VALUES
(@emp_uid, 'Himal Tech Pvt. Ltd.', 'Leading IT and software services in Nepal.', 'Kathmandu', 1);
SET @comp_id = LAST_INSERT_ID();
INSERT INTO jobs (company_id, title, description, salary_min, salary_max, location, type, experience_level, status, is_featured) VALUES
(@comp_id, 'Senior PHP Developer', 'Build REST APIs, MVC backend, MySQL. Collaborate with React frontend team.', 80000, 150000, 'Kathmandu', 'full-time', 'senior', 'approved', 1),
(@comp_id, 'Digital Marketing Officer', 'SEO, social media, and campaign analytics.', 35000, 55000, 'Pokhara', 'full-time', 'mid', 'approved', 0),
(@comp_id, 'Banking Operations Trainee', 'Retail banking, customer service, compliance basics.', 15000, 22000, 'Biratnagar', 'internship', 'entry', 'approved', 0),
(@comp_id, 'React Frontend Developer', 'React, Tailwind, REST integration.', 60000, 110000, 'Lalitpur', 'full-time', 'mid', 'approved', 0);

INSERT INTO users (name, email, password, role) VALUES
('Demo Seeker', 'seeker@merokam.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker');
SET @seeker_id = LAST_INSERT_ID();
INSERT INTO user_skills (user_id, skill) VALUES
(@seeker_id, 'PHP'), (@seeker_id, 'JavaScript'), (@seeker_id, 'React');

SET FOREIGN_KEY_CHECKS = 1;
