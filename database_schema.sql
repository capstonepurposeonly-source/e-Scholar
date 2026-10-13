-- ============================================
-- e-Scholar System Database Schema
-- MySQL Database Creation Script
-- ============================================

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS liquidations;
DROP TABLE IF EXISTS student_family;
DROP TABLE IF EXISTS student_addresses;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS campuses;

-- ============================================
-- Table: campuses
-- Description: Stores all NORSU campus locations
-- ============================================
CREATE TABLE campuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campus_name VARCHAR(100) NOT NULL UNIQUE,
    campus_code VARCHAR(20) NOT NULL UNIQUE,
    address VARCHAR(255),
    contact_number VARCHAR(20),
    email VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campus_code (campus_code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default campuses
INSERT INTO campuses (campus_name, campus_code, is_active) VALUES
('Main Campus 1', 'MC1', TRUE),
('Main Campus 2', 'MC2', TRUE),
('Guihulngan Campus', 'GHN', TRUE),
('Bais Campus', 'BAS', TRUE),
('Pamplona Campus', 'PAM', TRUE),
('Mabinay Campus', 'MAB', TRUE),
('Bayawan Campus', 'BAY', TRUE);

-- ============================================
-- Table: students
-- Description: Main student information table
-- ============================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(50) NOT NULL UNIQUE,
    lrn VARCHAR(12) UNIQUE COMMENT 'Learner Reference Number',
    scholarship_type VARCHAR(100) NOT NULL,
    
    -- Personal Information
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10) COMMENT 'Jr., Sr., III, etc.',
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    marital_status ENUM('Single', 'Married', 'Widowed', 'Separated', 'Divorced') DEFAULT 'Single',
    birthday DATE NOT NULL,
    
    -- Academic Information
    course VARCHAR(200) NOT NULL COMMENT 'Current enrolled course/program',
    elementary_school VARCHAR(200),
    elementary_year YEAR,
    high_school VARCHAR(200),
    high_school_year YEAR,
    
    -- Status and Contact
    registration_status ENUM('Enrolled', 'Not Enrolled') DEFAULT 'Not Enrolled',
    email VARCHAR(100) UNIQUE,
    
    -- Additional Information
    disability_type VARCHAR(100) COMMENT 'Type of disability if applicable, NULL if none',
    photo_url VARCHAR(255) COMMENT 'Path to student photo',
    
    -- Campus Assignment
    campus_id INT NOT NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Foreign Keys
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_student_number (student_number),
    INDEX idx_lrn (lrn),
    INDEX idx_scholarship_type (scholarship_type),
    INDEX idx_registration_status (registration_status),
    INDEX idx_campus_id (campus_id),
    INDEX idx_last_name (last_name),
    INDEX idx_email (email),
    INDEX idx_is_active (is_active),
    
    -- Check Constraints
    CONSTRAINT chk_email_format CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Z|a-z]{2,}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: student_addresses
-- Description: Stores student residential addresses
-- ============================================
CREATE TABLE student_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    
    -- Address Components
    street VARCHAR(255) COMMENT 'House number and street name',
    barangay VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    country VARCHAR(100) DEFAULT 'Philippines',
    postal_code VARCHAR(10),
    
    -- Address Type
    address_type ENUM('Permanent', 'Current', 'Other') DEFAULT 'Permanent',
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_student_id (student_id),
    INDEX idx_city (city),
    INDEX idx_province (province),
    INDEX idx_address_type (address_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: student_family
-- Description: Stores student family and guardian information
-- ============================================
CREATE TABLE student_family (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    
    -- Guardian Information
    guardian_name VARCHAR(200),
    guardian_contact VARCHAR(20),
    guardian_relationship VARCHAR(50) COMMENT 'Relationship to student',
    
    -- Father Information
    father_name VARCHAR(200),
    father_contact VARCHAR(20),
    father_occupation VARCHAR(100),
    
    -- Mother Information
    mother_name VARCHAR(200),
    mother_contact VARCHAR(20),
    mother_occupation VARCHAR(100),
    
    -- Spouse Information (if married)
    spouse_name VARCHAR(200),
    spouse_contact VARCHAR(20),
    spouse_occupation VARCHAR(100),
    
    -- Financial Information
    annual_income DECIMAL(12, 2) COMMENT 'Family annual income in PHP',
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_student_id (student_id),
    INDEX idx_annual_income (annual_income),
    
    -- Constraints
    CONSTRAINT chk_annual_income CHECK (annual_income >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: liquidations
-- Description: Tracks scholarship liquidation submissions and deadlines
-- ============================================
CREATE TABLE liquidations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    scholarship_type VARCHAR(100) NOT NULL,
    
    -- Academic Period
    academic_year VARCHAR(20) NOT NULL COMMENT 'e.g., 2023-2024',
    semester ENUM('1st Semester', '2nd Semester', 'Summer', 'Whole Year') NOT NULL,
    
    -- Liquidation Details
    deadline_date DATE NOT NULL,
    submission_date DATETIME COMMENT 'Actual date and time of submission',
    status ENUM('Pending', 'Submitted', 'Approved', 'Rejected', 'Overdue') DEFAULT 'Pending',
    
    -- Additional Information
    amount DECIMAL(10, 2) COMMENT 'Scholarship amount to be liquidated',
    remarks TEXT COMMENT 'Additional notes or comments',
    documents_url VARCHAR(255) COMMENT 'Path to uploaded liquidation documents',
    
    -- Approval Information
    approved_by INT COMMENT 'Admin user ID who approved',
    approved_at DATETIME,
    rejection_reason TEXT,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_student_id (student_id),
    INDEX idx_scholarship_type (scholarship_type),
    INDEX idx_status (status),
    INDEX idx_deadline_date (deadline_date),
    INDEX idx_submission_date (submission_date),
    INDEX idx_academic_year (academic_year),
    INDEX idx_semester (semester),
    
    -- Constraints
    CONSTRAINT chk_amount CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Views for Common Queries
-- ============================================

-- View: Complete Student Information
CREATE VIEW vw_student_complete AS
SELECT 
    s.id,
    s.student_number,
    s.lrn,
    CONCAT(s.first_name, ' ', COALESCE(s.middle_name, ''), ' ', s.last_name, ' ', COALESCE(s.suffix, '')) AS full_name,
    s.first_name,
    s.middle_name,
    s.last_name,
    s.suffix,
    s.gender,
    s.marital_status,
    s.birthday,
    TIMESTAMPDIFF(YEAR, s.birthday, CURDATE()) AS age,
    s.scholarship_type,
    s.course,
    s.registration_status,
    s.email,
    s.disability_type,
    s.photo_url,
    c.campus_name,
    c.campus_code,
    CONCAT(COALESCE(sa.street, ''), ', ', sa.barangay, ', ', sa.city, ', ', sa.province) AS full_address,
    sa.barangay,
    sa.city,
    sa.province,
    sf.guardian_name,
    sf.guardian_contact,
    sf.father_name,
    sf.mother_name,
    sf.annual_income,
    s.created_at,
    s.updated_at
FROM students s
LEFT JOIN campuses c ON s.campus_id = c.id
LEFT JOIN student_addresses sa ON s.id = sa.student_id AND sa.address_type = 'Permanent'
LEFT JOIN student_family sf ON s.id = sf.student_id
WHERE s.is_active = TRUE;

-- View: Liquidation Summary
CREATE VIEW vw_liquidation_summary AS
SELECT 
    l.id,
    s.student_number,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    l.scholarship_type,
    l.academic_year,
    l.semester,
    l.deadline_date,
    l.submission_date,
    l.status,
    l.amount,
    CASE 
        WHEN l.status = 'Pending' AND l.deadline_date < CURDATE() THEN 'Overdue'
        WHEN l.status = 'Pending' AND l.deadline_date >= CURDATE() THEN 'On Time'
        ELSE l.status
    END AS deadline_status,
    DATEDIFF(l.deadline_date, CURDATE()) AS days_until_deadline,
    c.campus_name
FROM liquidations l
INNER JOIN students s ON l.student_id = s.id
INNER JOIN campuses c ON s.campus_id = c.id;

-- ============================================
-- Stored Procedures
-- ============================================

DELIMITER $$

-- Procedure: Get Student Full Profile
CREATE PROCEDURE sp_get_student_profile(IN p_student_id INT)
BEGIN
    SELECT * FROM vw_student_complete WHERE id = p_student_id;
    
    SELECT * FROM student_addresses WHERE student_id = p_student_id;
    
    SELECT * FROM student_family WHERE student_id = p_student_id;
    
    SELECT * FROM liquidations WHERE student_id = p_student_id ORDER BY deadline_date DESC;
END$$

-- Procedure: Get Overdue Liquidations
CREATE PROCEDURE sp_get_overdue_liquidations()
BEGIN
    SELECT 
        l.*,
        s.student_number,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        s.email,
        c.campus_name
    FROM liquidations l
    INNER JOIN students s ON l.student_id = s.id
    INNER JOIN campuses c ON s.campus_id = c.id
    WHERE l.status = 'Pending' 
    AND l.deadline_date < CURDATE()
    ORDER BY l.deadline_date ASC;
END$$

-- Procedure: Get Students by Campus
CREATE PROCEDURE sp_get_students_by_campus(IN p_campus_id INT)
BEGIN
    SELECT * FROM vw_student_complete 
    WHERE campus_name = (SELECT campus_name FROM campuses WHERE id = p_campus_id)
    ORDER BY last_name, first_name;
END$$

DELIMITER ;

-- ============================================
-- Triggers
-- ============================================

DELIMITER $$

-- Trigger: Auto-update liquidation status to overdue
CREATE TRIGGER trg_update_overdue_liquidations
BEFORE UPDATE ON liquidations
FOR EACH ROW
BEGIN
    IF NEW.status = 'Pending' AND NEW.deadline_date < CURDATE() THEN
        SET NEW.status = 'Overdue';
    END IF;
END$$

-- Trigger: Validate student age
CREATE TRIGGER trg_validate_student_age
BEFORE INSERT ON students
FOR EACH ROW
BEGIN
    IF TIMESTAMPDIFF(YEAR, NEW.birthday, CURDATE()) < 15 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Student must be at least 15 years old';
    END IF;
END$$

DELIMITER ;

-- ============================================
-- Sample Data Queries (for testing)
-- ============================================

-- Query to check all tables
-- SELECT 'campuses' AS table_name, COUNT(*) AS record_count FROM campuses
-- UNION ALL
-- SELECT 'students', COUNT(*) FROM students
-- UNION ALL
-- SELECT 'student_addresses', COUNT(*) FROM student_addresses
-- UNION ALL
-- SELECT 'student_family', COUNT(*) FROM student_family
-- UNION ALL
-- SELECT 'liquidations', COUNT(*) FROM liquidations;

-- ============================================
-- Grant Permissions (Optional - adjust as needed)
-- ============================================

-- GRANT SELECT, INSERT, UPDATE, DELETE ON scholarship_db.* TO 'scholarship_user'@'%';
-- FLUSH PRIVILEGES;

-- ============================================
-- End of Schema
-- ============================================
