-- ============================================
-- e-Scholar System Database Schema
-- PostgreSQL Database Creation Script
-- ============================================

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS liquidations CASCADE;
DROP TABLE IF EXISTS student_family CASCADE;
DROP TABLE IF EXISTS student_addresses CASCADE;
DROP TABLE IF EXISTS students CASCADE;
DROP TABLE IF EXISTS campuses CASCADE;

-- Drop existing types
DROP TYPE IF EXISTS gender_type CASCADE;
DROP TYPE IF EXISTS marital_status_type CASCADE;
DROP TYPE IF EXISTS registration_status_type CASCADE;
DROP TYPE IF EXISTS address_type CASCADE;
DROP TYPE IF EXISTS semester_type CASCADE;
DROP TYPE IF EXISTS liquidation_status_type CASCADE;

-- ============================================
-- Custom Types
-- ============================================

CREATE TYPE gender_type AS ENUM ('Male', 'Female', 'Other');
CREATE TYPE marital_status_type AS ENUM ('Single', 'Married', 'Widowed', 'Separated', 'Divorced');
CREATE TYPE registration_status_type AS ENUM ('Enrolled', 'Not Enrolled');
CREATE TYPE address_type AS ENUM ('Permanent', 'Current', 'Other');
CREATE TYPE semester_type AS ENUM ('1st Semester', '2nd Semester', 'Summer', 'Whole Year');
CREATE TYPE liquidation_status_type AS ENUM ('Pending', 'Submitted', 'Approved', 'Rejected', 'Overdue');

-- ============================================
-- Table: campuses
-- Description: Stores all NORSU campus locations
-- ============================================
CREATE TABLE campuses (
    id SERIAL PRIMARY KEY,
    campus_name VARCHAR(100) NOT NULL UNIQUE,
    campus_code VARCHAR(20) NOT NULL UNIQUE,
    address VARCHAR(255),
    contact_number VARCHAR(20),
    email VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_campuses_campus_code ON campuses(campus_code);
CREATE INDEX idx_campuses_is_active ON campuses(is_active);

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
    id SERIAL PRIMARY KEY,
    student_number VARCHAR(50) NOT NULL UNIQUE,
    lrn VARCHAR(12) UNIQUE,
    scholarship_type VARCHAR(100) NOT NULL,
    
    -- Personal Information
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10),
    gender gender_type NOT NULL,
    marital_status marital_status_type DEFAULT 'Single',
    birthday DATE NOT NULL,
    
    -- Academic Information
    course VARCHAR(200) NOT NULL,
    elementary_school VARCHAR(200),
    elementary_year INTEGER,
    high_school VARCHAR(200),
    high_school_year INTEGER,
    
    -- Status and Contact
    registration_status registration_status_type DEFAULT 'Not Enrolled',
    email VARCHAR(100) UNIQUE,
    
    -- Additional Information
    disability_type VARCHAR(100),
    photo_url VARCHAR(255),
    
    -- Campus Assignment
    campus_id INTEGER NOT NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Foreign Keys
    FOREIGN KEY (campus_id) REFERENCES campuses(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Check Constraints
    CONSTRAINT chk_birthday CHECK (birthday <= CURRENT_DATE),
    CONSTRAINT chk_email_format CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$'),
    CONSTRAINT chk_elementary_year CHECK (elementary_year IS NULL OR (elementary_year >= 1900 AND elementary_year <= EXTRACT(YEAR FROM CURRENT_DATE))),
    CONSTRAINT chk_high_school_year CHECK (high_school_year IS NULL OR (high_school_year >= 1900 AND high_school_year <= EXTRACT(YEAR FROM CURRENT_DATE)))
);

-- Indexes
CREATE INDEX idx_students_student_number ON students(student_number);
CREATE INDEX idx_students_lrn ON students(lrn);
CREATE INDEX idx_students_scholarship_type ON students(scholarship_type);
CREATE INDEX idx_students_registration_status ON students(registration_status);
CREATE INDEX idx_students_campus_id ON students(campus_id);
CREATE INDEX idx_students_last_name ON students(last_name);
CREATE INDEX idx_students_email ON students(email);
CREATE INDEX idx_students_is_active ON students(is_active);

-- Comments
COMMENT ON COLUMN students.lrn IS 'Learner Reference Number';
COMMENT ON COLUMN students.suffix IS 'Jr., Sr., III, etc.';
COMMENT ON COLUMN students.course IS 'Current enrolled course/program';
COMMENT ON COLUMN students.disability_type IS 'Type of disability if applicable, NULL if none';
COMMENT ON COLUMN students.photo_url IS 'Path to student photo';

-- ============================================
-- Table: student_addresses
-- Description: Stores student residential addresses
-- ============================================
CREATE TABLE student_addresses (
    id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    
    -- Address Components
    street VARCHAR(255),
    barangay VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    country VARCHAR(100) DEFAULT 'Philippines',
    postal_code VARCHAR(10),
    
    -- Address Type
    address_type address_type DEFAULT 'Permanent',
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Indexes
CREATE INDEX idx_student_addresses_student_id ON student_addresses(student_id);
CREATE INDEX idx_student_addresses_city ON student_addresses(city);
CREATE INDEX idx_student_addresses_province ON student_addresses(province);
CREATE INDEX idx_student_addresses_address_type ON student_addresses(address_type);

-- Comments
COMMENT ON COLUMN student_addresses.street IS 'House number and street name';

-- ============================================
-- Table: student_family
-- Description: Stores student family and guardian information
-- ============================================
CREATE TABLE student_family (
    id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    
    -- Guardian Information
    guardian_name VARCHAR(200),
    guardian_contact VARCHAR(20),
    guardian_relationship VARCHAR(50),
    
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
    annual_income NUMERIC(12, 2),
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_annual_income CHECK (annual_income IS NULL OR annual_income >= 0),
    CONSTRAINT chk_contact_format CHECK (
        (guardian_contact IS NULL OR guardian_contact ~ '^[0-9+()-]{7,20}$') AND
        (father_contact IS NULL OR father_contact ~ '^[0-9+()-]{7,20}$') AND
        (mother_contact IS NULL OR mother_contact ~ '^[0-9+()-]{7,20}$') AND
        (spouse_contact IS NULL OR spouse_contact ~ '^[0-9+()-]{7,20}$')
    )
);

-- Indexes
CREATE INDEX idx_student_family_student_id ON student_family(student_id);
CREATE INDEX idx_student_family_annual_income ON student_family(annual_income);

-- Comments
COMMENT ON COLUMN student_family.guardian_relationship IS 'Relationship to student';
COMMENT ON COLUMN student_family.annual_income IS 'Family annual income in PHP';

-- ============================================
-- Table: liquidations
-- Description: Tracks scholarship liquidation submissions and deadlines
-- ============================================
CREATE TABLE liquidations (
    id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    scholarship_type VARCHAR(100) NOT NULL,
    
    -- Academic Period
    academic_year VARCHAR(20) NOT NULL,
    semester semester_type NOT NULL,
    
    -- Liquidation Details
    deadline_date DATE NOT NULL,
    submission_date TIMESTAMP,
    status liquidation_status_type DEFAULT 'Pending',
    
    -- Additional Information
    amount NUMERIC(10, 2),
    remarks TEXT,
    documents_url VARCHAR(255),
    
    -- Approval Information
    approved_by INTEGER,
    approved_at TIMESTAMP,
    rejection_reason TEXT,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_amount CHECK (amount IS NULL OR amount >= 0),
    CONSTRAINT chk_submission_date CHECK (submission_date IS NULL OR submission_date >= created_at),
    CONSTRAINT chk_approved_date CHECK (approved_at IS NULL OR approved_at >= created_at)
);

-- Indexes
CREATE INDEX idx_liquidations_student_id ON liquidations(student_id);
CREATE INDEX idx_liquidations_scholarship_type ON liquidations(scholarship_type);
CREATE INDEX idx_liquidations_status ON liquidations(status);
CREATE INDEX idx_liquidations_deadline_date ON liquidations(deadline_date);
CREATE INDEX idx_liquidations_submission_date ON liquidations(submission_date);
CREATE INDEX idx_liquidations_academic_year ON liquidations(academic_year);
CREATE INDEX idx_liquidations_semester ON liquidations(semester);

-- Comments
COMMENT ON COLUMN liquidations.academic_year IS 'e.g., 2023-2024';
COMMENT ON COLUMN liquidations.submission_date IS 'Actual date and time of submission';
COMMENT ON COLUMN liquidations.amount IS 'Scholarship amount to be liquidated';
COMMENT ON COLUMN liquidations.remarks IS 'Additional notes or comments';
COMMENT ON COLUMN liquidations.documents_url IS 'Path to uploaded liquidation documents';
COMMENT ON COLUMN liquidations.approved_by IS 'Admin user ID who approved';

-- ============================================
-- Functions for Triggers
-- ============================================

-- Function: Update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Function: Auto-update overdue liquidations
CREATE OR REPLACE FUNCTION update_overdue_liquidations()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'Pending' AND NEW.deadline_date < CURRENT_DATE THEN
        NEW.status = 'Overdue';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Function: Validate student age
CREATE OR REPLACE FUNCTION validate_student_age()
RETURNS TRIGGER AS $$
BEGIN
    IF EXTRACT(YEAR FROM AGE(CURRENT_DATE, NEW.birthday)) < 15 THEN
        RAISE EXCEPTION 'Student must be at least 15 years old';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- Triggers
-- ============================================

-- Trigger: Update updated_at on campuses
CREATE TRIGGER trg_campuses_updated_at
    BEFORE UPDATE ON campuses
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger: Update updated_at on students
CREATE TRIGGER trg_students_updated_at
    BEFORE UPDATE ON students
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger: Update updated_at on student_addresses
CREATE TRIGGER trg_student_addresses_updated_at
    BEFORE UPDATE ON student_addresses
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger: Update updated_at on student_family
CREATE TRIGGER trg_student_family_updated_at
    BEFORE UPDATE ON student_family
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger: Update updated_at on liquidations
CREATE TRIGGER trg_liquidations_updated_at
    BEFORE UPDATE ON liquidations
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger: Auto-update liquidation status to overdue
CREATE TRIGGER trg_update_overdue_liquidations
    BEFORE UPDATE ON liquidations
    FOR EACH ROW
    EXECUTE FUNCTION update_overdue_liquidations();

-- Trigger: Validate student age on insert
CREATE TRIGGER trg_validate_student_age
    BEFORE INSERT ON students
    FOR EACH ROW
    EXECUTE FUNCTION validate_student_age();

-- ============================================
-- Views for Common Queries
-- ============================================

-- View: Complete Student Information
CREATE OR REPLACE VIEW vw_student_complete AS
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
    EXTRACT(YEAR FROM AGE(CURRENT_DATE, s.birthday))::INTEGER AS age,
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
CREATE OR REPLACE VIEW vw_liquidation_summary AS
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
        WHEN l.status = 'Pending' AND l.deadline_date < CURRENT_DATE THEN 'Overdue'
        WHEN l.status = 'Pending' AND l.deadline_date >= CURRENT_DATE THEN 'On Time'
        ELSE l.status::TEXT
    END AS deadline_status,
    l.deadline_date - CURRENT_DATE AS days_until_deadline,
    c.campus_name
FROM liquidations l
INNER JOIN students s ON l.student_id = s.id
INNER JOIN campuses c ON s.campus_id = c.id;

-- ============================================
-- Stored Procedures (Functions in PostgreSQL)
-- ============================================

-- Function: Get Student Full Profile
CREATE OR REPLACE FUNCTION sp_get_student_profile(p_student_id INTEGER)
RETURNS TABLE (
    profile_data JSON,
    addresses JSON,
    family JSON,
    liquidations JSON
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        (SELECT row_to_json(t) FROM (SELECT * FROM vw_student_complete WHERE id = p_student_id) t) AS profile_data,
        (SELECT json_agg(row_to_json(t)) FROM (SELECT * FROM student_addresses WHERE student_id = p_student_id) t) AS addresses,
        (SELECT row_to_json(t) FROM (SELECT * FROM student_family WHERE student_id = p_student_id) t) AS family,
        (SELECT json_agg(row_to_json(t)) FROM (SELECT * FROM liquidations WHERE student_id = p_student_id ORDER BY deadline_date DESC) t) AS liquidations;
END;
$$ LANGUAGE plpgsql;

-- Function: Get Overdue Liquidations
CREATE OR REPLACE FUNCTION sp_get_overdue_liquidations()
RETURNS TABLE (
    id INTEGER,
    student_id INTEGER,
    student_number VARCHAR(50),
    student_name TEXT,
    scholarship_type VARCHAR(100),
    academic_year VARCHAR(20),
    semester semester_type,
    deadline_date DATE,
    status liquidation_status_type,
    email VARCHAR(100),
    campus_name VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        l.id,
        l.student_id,
        s.student_number,
        CONCAT(s.first_name, ' ', s.last_name)::TEXT AS student_name,
        l.scholarship_type,
        l.academic_year,
        l.semester,
        l.deadline_date,
        l.status,
        s.email,
        c.campus_name
    FROM liquidations l
    INNER JOIN students s ON l.student_id = s.id
    INNER JOIN campuses c ON s.campus_id = c.id
    WHERE l.status = 'Pending' 
    AND l.deadline_date < CURRENT_DATE
    ORDER BY l.deadline_date ASC;
END;
$$ LANGUAGE plpgsql;

-- Function: Get Students by Campus
CREATE OR REPLACE FUNCTION sp_get_students_by_campus(p_campus_id INTEGER)
RETURNS TABLE (
    id INTEGER,
    student_number VARCHAR(50),
    full_name TEXT,
    scholarship_type VARCHAR(100),
    course VARCHAR(200),
    registration_status registration_status_type,
    campus_name VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        sc.id,
        sc.student_number,
        sc.full_name::TEXT,
        sc.scholarship_type,
        sc.course,
        sc.registration_status,
        sc.campus_name
    FROM vw_student_complete sc
    WHERE sc.campus_name = (SELECT campus_name FROM campuses WHERE id = p_campus_id)
    ORDER BY sc.last_name, sc.first_name;
END;
$$ LANGUAGE plpgsql;

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

-- GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO scholarship_user;
-- GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO scholarship_user;

-- ============================================
-- End of Schema
-- ============================================
