-- Create loan_settings table
CREATE TABLE IF NOT EXISTS loan_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    loan_duration INT NOT NULL DEFAULT 14,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default loan duration (14 days)
INSERT INTO loan_settings (loan_duration) 
VALUES (14)
ON DUPLICATE KEY UPDATE loan_duration = VALUES(loan_duration); 