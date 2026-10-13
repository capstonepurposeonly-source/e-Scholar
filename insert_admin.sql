-- Create Admin User for e-Scholar System
-- Default credentials (CHANGE AFTER FIRST LOGIN!)
-- Email: admin@norsu.edu.ph
-- Password: admin123

USE scholarship_db;

-- Delete existing admin if any (optional)
DELETE FROM admin WHERE email = 'admin@norsu.edu.ph';

-- Insert admin user
-- Password hash is for 'admin123' using bcrypt
INSERT INTO admin (email, roles, password, full_name) VALUES
(
    'admin@norsu.edu.ph',
    '["ROLE_ADMIN"]',
    '$2y$12$A5LuMMTW67fGBFfiONUAouh42tur1liSsevWzTGHXGQglc1kti736',
    'NORSU Administrator'
);

-- Verify insertion
SELECT 
    id,
    email,
    full_name,
    roles,
    'Password: admin123' as note
FROM admin 
WHERE email = 'admin@norsu.edu.ph';
