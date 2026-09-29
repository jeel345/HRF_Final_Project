CREATE DATABASE IF NOT EXISTS human_rights_federation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE human_rights_federation;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    phone VARCHAR(40) NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    subject VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    admin_reply TEXT NULL,
    replied_at TIMESTAMP NULL,
    replied_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE contact_messages
    ADD COLUMN IF NOT EXISTS user_id INT NULL,
    ADD COLUMN IF NOT EXISTS admin_reply TEXT NULL,
    ADD COLUMN IF NOT EXISTS replied_at TIMESTAMP NULL,
    ADD COLUMN IF NOT EXISTS replied_by INT NULL;

CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(220) NOT NULL,
    image_path VARCHAR(255) NULL,
    description TEXT NOT NULL,
    admin_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS president_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(140) NOT NULL,
    designation VARCHAR(160) NOT NULL,
    bio TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (name, email, password)
VALUES (
    'Main Administrator',
    'admin@hrf.com',
    '$2y$10$I4qqVLrK4CzGT7cc5reGBujz3ewaDw5ZGL.21e4Aw3ivsN4U341Cm'
)
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO president_profiles (name, designation, bio, image_path)
VALUES
('National President', 'President, National Bureau', 'Leads federation programs, public rights awareness initiatives, bureau coordination, and community representation.', NULL),
('State President', 'President, State Bureau', 'Coordinates state-level committees, local outreach, complaint documentation, and awareness campaigns.', NULL)
ON DUPLICATE KEY UPDATE name = name;
