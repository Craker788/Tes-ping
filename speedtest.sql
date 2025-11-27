CREATE DATABASE IF NOT EXISTS speedtest;
USE speedtest;

CREATE TABLE IF NOT EXISTS speedtest_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    country VARCHAR(100),
    region VARCHAR(100),
    city VARCHAR(100),
    isp VARCHAR(255),
    download_speed DECIMAL(8,2),
    upload_speed DECIMAL(8,2),
    ping INT,
    test_time DATETIME,
    user_agent TEXT,
    ai_score INT,
    INDEX idx_ip (ip_address),
    INDEX idx_time (test_time),
    INDEX idx_ai_score (ai_score)
);