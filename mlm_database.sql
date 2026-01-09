-- MLM System Database Schema

CREATE DATABASE IF NOT EXISTS mlm_system;
USE mlm_system;

-- Admin Table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    mobile VARCHAR(15) NOT NULL,
    bank_account_name VARCHAR(100),
    bank_account_no VARCHAR(30),
    ifsc_code VARCHAR(15),
    bank_name VARCHAR(100),
    upi_id VARCHAR(50),
    referral_code VARCHAR(20) UNIQUE NOT NULL,
    referred_by INT DEFAULT NULL,
    wallet_balance DECIMAL(10,2) DEFAULT 0.00,
    joining_amount DECIMAL(10,2) DEFAULT 1000.00,
    amount_received ENUM('yes','no') DEFAULT 'no',
    status ENUM('active','inactive') DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Company Wallet Table
CREATE TABLE IF NOT EXISTS company_wallet (
    id INT PRIMARY KEY AUTO_INCREMENT,
    total_balance DECIMAL(15,2) DEFAULT 0.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Transactions Table
CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    transaction_type ENUM('joining_bonus','referral_bonus','withdrawal','company_share') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Company Transactions Table
CREATE TABLE IF NOT EXISTS company_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_type ENUM('joining_share','system_credit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Withdrawal Requests Table
CREATE TABLE IF NOT EXISTS withdrawal_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin (password: admin123)
INSERT INTO admins (username, email, password) 
VALUES ('admin', 'admin@mlm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Initialize company wallet
INSERT INTO company_wallet (total_balance) VALUES (0.00);