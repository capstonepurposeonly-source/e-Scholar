-- ============================================
-- e-Scholar System Sample Data
-- Insert sample students and liquidations for testing
-- ============================================

USE scholarship_db;

START TRANSACTION;

-- ============================================
-- Sample Students (20 students across all campuses)
-- ============================================

INSERT INTO students (
    student_number, lrn, scholarship_type,
    first_name, middle_name, last_name,
    gender, marital_status, birthday,
    course, campus_id, registration_status, email,
    is_active, created_at, updated_at
) VALUES
-- Main Campus 1 Students
('2024-00001', '123456789001', 'TES', 'Juan', 'Santos', 'Dela Cruz', 'Male', 'Single', '2004-05-15', 'BS Computer Science', 1, 'Enrolled', 'juan.delacruz@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00002', '123456789002', 'CHED-UNIFAST', 'Maria', 'Garcia', 'Santos', 'Female', 'Single', '2003-08-22', 'BS Nursing', 1, 'Enrolled', 'maria.santos@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00003', '123456789003', 'TDP', 'Pedro', 'Reyes', 'Gonzales', 'Male', 'Single', '2005-03-10', 'BS Civil Engineering', 1, 'Enrolled', 'pedro.gonzales@norsu.edu.ph', 1, NOW(), NOW()),

-- Main Campus 2 Students
('2024-00004', '123456789004', 'SM Foundation', 'Ana', 'Lopez', 'Rivera', 'Female', 'Single', '2004-11-30', 'BS Education', 2, 'Enrolled', 'ana.rivera@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00005', '123456789005', 'TES', 'Carlos', 'Martinez', 'Torres', 'Male', 'Single', '2003-07-18', 'BS Business Administration', 2, 'Enrolled', 'carlos.torres@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00006', '123456789006', 'Landbank Scholarship', 'Sofia', 'Hernandez', 'Morales', 'Female', 'Single', '2004-09-25', 'BS Accountancy', 2, 'Enrolled', 'sofia.morales@norsu.edu.ph', 1, NOW(), NOW()),

-- Guihulngan Campus Students
('2024-00007', '123456789007', 'TES', 'Miguel', 'Cruz', 'Ramos', 'Male', 'Single', '2005-01-14', 'BS Agriculture', 3, 'Enrolled', 'miguel.ramos@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00008', '123456789008', 'CHED-UNIFAST', 'Isabel', 'Flores', 'Diaz', 'Female', 'Single', '2004-06-20', 'BS Information Technology', 3, 'Enrolled', 'isabel.diaz@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00009', '123456789009', 'TDP', 'Roberto', 'Mendez', 'Castro', 'Male', 'Single', '2003-12-08', 'BS Marine Biology', 3, 'Enrolled', 'roberto.castro@norsu.edu.ph', 1, NOW(), NOW()),

-- Bais Campus Students
('2024-00010', '123456789010', 'TES', 'Carmen', 'Navarro', 'Ortiz', 'Female', 'Single', '2004-04-17', 'BS Psychology', 4, 'Enrolled', 'carmen.ortiz@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00011', '123456789011', 'SM Foundation', 'Diego', 'Vargas', 'Ruiz', 'Male', 'Single', '2005-02-28', 'BS Criminology', 4, 'Enrolled', 'diego.ruiz@norsu.edu.ph', 1, NOW(), NOW()),

-- Pamplona Campus Students
('2024-00012', '123456789012', 'TES', 'Lucia', 'Jimenez', 'Perez', 'Female', 'Single', '2003-10-11', 'BS Environmental Science', 5, 'Enrolled', 'lucia.perez@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00013', '123456789013', 'CHED-UNIFAST', 'Fernando', 'Gutierrez', 'Sanchez', 'Male', 'Single', '2004-07-05', 'BS Hotel Management', 5, 'Enrolled', 'fernando.sanchez@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00014', '123456789014', 'TDP', 'Rosa', 'Silva', 'Ramirez', 'Female', 'Single', '2005-05-23', 'BS Tourism', 5, 'Not Enrolled', 'rosa.ramirez@norsu.edu.ph', 1, NOW(), NOW()),

-- Mabinay Campus Students
('2024-00015', '123456789015', 'TES', 'Antonio', 'Fernandez', 'Garcia', 'Male', 'Single', '2004-03-19', 'BS Geology', 6, 'Enrolled', 'antonio.garcia@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00016', '123456789016', 'Landbank Scholarship', 'Elena', 'Moreno', 'Lopez', 'Female', 'Single', '2003-11-27', 'BS Mathematics', 6, 'Enrolled', 'elena.lopez@norsu.edu.ph', 1, NOW(), NOW()),

-- Bayawan Campus Students
('2024-00017', '123456789017', 'TES', 'Jose', 'Alvarez', 'Martin', 'Male', 'Single', '2005-08-31', 'BS Fisheries', 7, 'Enrolled', 'jose.martin@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00018', '123456789018', 'CHED-UNIFAST', 'Patricia', 'Romero', 'Gonzalez', 'Female', 'Single', '2004-01-09', 'BS Social Work', 7, 'Enrolled', 'patricia.gonzalez@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00019', '123456789019', 'SM Foundation', 'Manuel', 'Torres', 'Hernandez', 'Male', 'Single', '2003-09-16', 'BS Agricultural Technology', 7, 'Enrolled', 'manuel.hernandez@norsu.edu.ph', 1, NOW(), NOW()),
('2024-00020', '123456789020', 'TES', 'Gabriela', 'Dominguez', 'Martinez', 'Female', 'Single', '2004-12-03', 'BS Development Communication', 7, 'Not Enrolled', 'gabriela.martinez@norsu.edu.ph', 1, NOW(), NOW());

-- ============================================
-- Sample Liquidations (Various statuses and deadlines)
-- ============================================

INSERT INTO liquidations (
    student_id, scholarship_type, academic_year, semester,
    deadline_date, submission_date, status, amount, remarks,
    created_at, updated_at
) VALUES
-- Overdue liquidations (past deadline)
(1, 'TES', '2024-2025', '1st Semester', '2026-10-05', NULL, 'Overdue', 10000.00, 'First semester liquidation', NOW(), NOW()),
(5, 'TES', '2024-2025', '1st Semester', '2026-10-08', NULL, 'Overdue', 10000.00, NULL, NOW(), NOW()),
(10, 'TES', '2024-2025', '1st Semester', '2026-10-10', NULL, 'Overdue', 10000.00, NULL, NOW(), NOW()),

-- Due Soon (within 3 days)
(2, 'CHED-UNIFAST', '2024-2025', '1st Semester', '2026-10-15', NULL, 'Pending', 20000.00, 'UNIFAST liquidation deadline', NOW(), NOW()),
(8, 'CHED-UNIFAST', '2024-2025', '1st Semester', '2026-10-16', NULL, 'Pending', 20000.00, NULL, NOW(), NOW()),

-- Upcoming (within 30 days)
(3, 'TDP', '2024-2025', '1st Semester', '2026-10-25', NULL, 'Pending', 15000.00, 'Tulong Dunong Program', NOW(), NOW()),
(9, 'TDP', '2024-2025', '1st Semester', '2026-10-28', NULL, 'Pending', 15000.00, NULL, NOW(), NOW()),
(13, 'CHED-UNIFAST', '2024-2025', '1st Semester', '2026-11-01', NULL, 'Pending', 20000.00, NULL, NOW(), NOW()),
(17, 'TES', '2024-2025', '1st Semester', '2026-11-05', NULL, 'Pending', 10000.00, NULL, NOW(), NOW()),
(20, 'TES', '2024-2025', '1st Semester', '2026-11-10', NULL, 'Pending', 10000.00, NULL, NOW(), NOW()),

-- Submitted (awaiting approval)
(4, 'SM Foundation', '2024-2025', '1st Semester', '2026-10-30', '2026-10-12 14:30:00', 'Submitted', 12000.00, 'Documents submitted for review', NOW(), NOW()),
(6, 'Landbank Scholarship', '2024-2025', '1st Semester', '2026-11-15', '2026-10-11 09:15:00', 'Submitted', 18000.00, 'Complete requirements submitted', NOW(), NOW()),
(16, 'Landbank Scholarship', '2024-2025', '1st Semester', '2026-11-15', '2026-10-10 16:45:00', 'Submitted', 18000.00, NULL, NOW(), NOW()),

-- Approved (completed)
(11, 'SM Foundation', '2024-2025', '1st Semester', '2026-09-30', '2026-09-25 10:00:00', 'Approved', 12000.00, 'Approved by admin on Oct 1', NOW(), NOW()),
(12, 'TES', '2024-2025', '1st Semester', '2026-09-30', '2026-09-28 15:30:00', 'Approved', 10000.00, 'All documents verified', NOW(), NOW()),
(15, 'TES', '2024-2025', '1st Semester', '2026-09-30', '2026-09-29 11:20:00', 'Approved', 10000.00, NULL, NOW(), NOW()),

-- Future deadlines (2nd semester)
(1, 'TES', '2024-2025', '2nd Semester', '2027-03-15', NULL, 'Pending', 10000.00, 'Second semester liquidation', NOW(), NOW()),
(2, 'CHED-UNIFAST', '2024-2025', '2nd Semester', '2027-03-20', NULL, 'Pending', 20000.00, NULL, NOW(), NOW()),
(3, 'TDP', '2024-2025', '2nd Semester', '2027-03-25', NULL, 'Pending', 15000.00, NULL, NOW(), NOW());

-- ============================================
-- Sample Student Addresses
-- ============================================

INSERT INTO student_addresses (
    student_id, street, barangay, city, province, address_type, created_at, updated_at
) VALUES
(1, '123 Main Street', 'Poblacion', 'Dumaguete City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(2, '456 Rizal Avenue', 'Bantayan', 'Dumaguete City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(3, '789 University Road', 'Candau-ay', 'Dumaguete City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(4, '321 Flores Street', 'Poblacion', 'Sibulan', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(5, '654 San Jose Street', 'Looc', 'Dumaguete City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(6, '987 Hibbard Avenue', 'Taclobo', 'Dumaguete City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(7, 'Purok 1', 'Poblacion', 'Guihulngan City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(8, 'Purok 3', 'Humayhumay', 'Guihulngan City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(9, 'Purok 5', 'Trinidad', 'Guihulngan City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(10, 'Sitio Tunga', 'Poblacion', 'Bais City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(11, 'Barangay Road', 'Okiot', 'Bais City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(12, 'Main Road', 'Poblacion', 'Pamplona', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(13, 'Purok 2', 'Magsusunog', 'Pamplona', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(14, 'Sitio Proper', 'Lungsodaan', 'Pamplona', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(15, 'Purok 1', 'Poblacion', 'Mabinay', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(16, 'Barangay Center', 'Arebasore', 'Mabinay', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(17, 'Main Street', 'Poblacion', 'Bayawan City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(18, 'Purok 4', 'Bugay', 'Bayawan City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(19, 'Sitio Lower', 'Villareal', 'Bayawan City', 'Negros Oriental', 'Permanent', NOW(), NOW()),
(20, 'Purok Centro', 'San Jose', 'Bayawan City', 'Negros Oriental', 'Permanent', NOW(), NOW());

-- ============================================
-- Sample Student Family Information
-- ============================================

INSERT INTO student_family (
    student_id, guardian_name, guardian_contact, father_name, father_contact,
    mother_name, mother_contact, annual_income, created_at, updated_at
) VALUES
(1, 'Jose Dela Cruz', '09171234567', 'Jose Dela Cruz', '09171234567', 'Rosa Dela Cruz', '09181234568', 150000.00, NOW(), NOW()),
(2, 'Roberto Santos', '09191234569', 'Roberto Santos', '09191234569', 'Linda Santos', '09201234570', 180000.00, NOW(), NOW()),
(3, 'Alfredo Gonzales', '09211234571', 'Alfredo Gonzales', '09211234571', 'Teresa Gonzales', '09221234572', 120000.00, NOW(), NOW()),
(4, 'Manuel Rivera', '09231234573', 'Manuel Rivera', '09231234573', 'Gloria Rivera', '09241234574', 200000.00, NOW(), NOW()),
(5, 'Fernando Torres', '09251234575', 'Fernando Torres', '09251234575', 'Carmen Torres', '09261234576', 170000.00, NOW(), NOW()),
(6, 'Ricardo Morales', '09271234577', 'Ricardo Morales', '09271234577', 'Luz Morales', '09281234578', 160000.00, NOW(), NOW()),
(7, 'Eduardo Ramos', '09291234579', 'Eduardo Ramos', '09291234579', 'Consuelo Ramos', '09301234580', 140000.00, NOW(), NOW()),
(8, 'Antonio Diaz', '09311234581', 'Antonio Diaz', '09311234581', 'Bella Diaz', '09321234582', 130000.00, NOW(), NOW()),
(9, 'Salvador Castro', '09331234583', 'Salvador Castro', '09331234583', 'Rosario Castro', '09341234584', 110000.00, NOW(), NOW()),
(10, 'Emilio Ortiz', '09351234585', 'Emilio Ortiz', '09351234585', 'Dolores Ortiz', '09361234586', 190000.00, NOW(), NOW()),
(11, 'Vicente Ruiz', '09371234587', 'Vicente Ruiz', '09371234587', 'Pilar Ruiz', '09381234588', 125000.00, NOW(), NOW()),
(12, 'Domingo Perez', '09391234589', 'Domingo Perez', '09391234589', 'Socorro Perez', '09401234590', 155000.00, NOW(), NOW()),
(13, 'Rodrigo Sanchez', '09411234591', 'Rodrigo Sanchez', '09411234591', 'Amparo Sanchez', '09421234592', 175000.00, NOW(), NOW()),
(14, 'Felipe Ramirez', '09431234593', 'Felipe Ramirez', '09431234593', 'Remedios Ramirez', '09441234594', 145000.00, NOW(), NOW()),
(15, 'Guillermo Garcia', '09451234595', 'Guillermo Garcia', '09451234595', 'Esperanza Garcia', '09461234596', 135000.00, NOW(), NOW()),
(16, 'Arturo Lopez', '09471234597', 'Arturo Lopez', '09471234597', 'Felicidad Lopez', '09481234598', 165000.00, NOW(), NOW()),
(17, 'Armando Martin', '09491234599', 'Armando Martin', '09491234599', 'Paz Martin', '09501234600', 115000.00, NOW(), NOW()),
(18, 'Enrique Gonzalez', '09511234601', 'Enrique Gonzalez', '09511234601', 'Asuncion Gonzalez', '09521234602', 185000.00, NOW(), NOW()),
(19, 'Raul Hernandez', '09531234603', 'Raul Hernandez', '09531234603', 'Milagros Hernandez', '09541234604', 195000.00, NOW(), NOW()),
(20, 'Cesar Martinez', '09551234605', 'Cesar Martinez', '09551234605', 'Soledad Martinez', '09561234606', 105000.00, NOW(), NOW());

COMMIT;

-- ============================================
-- Verification Queries
-- ============================================

-- Check inserted data
SELECT 'Students Inserted' AS info, COUNT(*) AS count FROM students;
SELECT 'Liquidations Inserted' AS info, COUNT(*) AS count FROM liquidations;
SELECT 'Addresses Inserted' AS info, COUNT(*) AS count FROM student_addresses;
SELECT 'Family Records Inserted' AS info, COUNT(*) AS count FROM student_family;

-- Campus distribution
SELECT c.campus_name, COUNT(s.id) AS student_count
FROM campuses c
LEFT JOIN students s ON c.id = s.campus_id
GROUP BY c.id, c.campus_name
ORDER BY c.campus_name;

-- Liquidation status summary
SELECT status, COUNT(*) AS count
FROM liquidations
GROUP BY status
ORDER BY 
    CASE status
        WHEN 'Overdue' THEN 1
        WHEN 'Pending' THEN 2
        WHEN 'Submitted' THEN 3
        WHEN 'Approved' THEN 4
        ELSE 5
    END;

-- Upcoming deadlines
SELECT 
    l.id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    l.scholarship_type,
    l.deadline_date,
    l.status,
    DATEDIFF(l.deadline_date, CURDATE()) AS days_until_deadline
FROM liquidations l
JOIN students s ON l.student_id = s.id
WHERE l.deadline_date >= CURDATE()
AND l.status IN ('Pending', 'Submitted')
ORDER BY l.deadline_date;
