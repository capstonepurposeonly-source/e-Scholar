# e-Scholar Database Setup Guide

## Overview
This guide explains how to set up the e-Scholar System database using the provided SQL scripts.

## Database Files

1. **database_schema.sql** - MySQL/MariaDB version
2. **database_schema_postgresql.sql** - PostgreSQL version

## Table Structure

### 1. `campuses`
Stores information about all NORSU campus locations.

**Pre-populated campuses:**
- Main Campus 1 (MC1)
- Main Campus 2 (MC2)
- Guihulngan Campus (GHN)
- Bais Campus (BAS)
- Pamplona Campus (PAM)
- Mabinay Campus (MAB)
- Bayawan Campus (BAY)

### 2. `students`
Main table storing student personal and academic information.

**Key Fields:**
- `student_number` - Unique student identifier
- `lrn` - Learner Reference Number
- `scholarship_type` - Type of scholarship received
- Personal info: names, gender, marital status, birthday
- Academic info: course, previous schools
- `registration_status` - Enrolled/Not Enrolled
- `campus_id` - Foreign key to campuses table

### 3. `student_addresses`
Stores residential addresses for students.

**Fields:**
- `street` - House number and street name
- `barangay`, `city`, `province`, `country`
- `address_type` - Permanent, Current, or Other

### 4. `student_family`
Contains family and guardian contact information.

**Fields:**
- Guardian information (name, contact, relationship)
- Father information (name, contact, occupation)
- Mother information (name, contact, occupation)
- Spouse information (name, contact, occupation)
- `annual_income` - Family annual income in PHP

### 5. `liquidations`
Tracks scholarship liquidation submissions and deadlines.

**Fields:**
- `scholarship_type` - Type of scholarship
- `academic_year` - e.g., "2023-2024"
- `semester` - 1st Semester, 2nd Semester, Summer, Whole Year
- `deadline_date` - Submission deadline
- `submission_date` - Actual submission timestamp
- `status` - Pending, Submitted, Approved, Rejected, Overdue
- `amount` - Scholarship amount to liquidate
- `documents_url` - Path to uploaded documents

## Installation Instructions

### For MySQL/MariaDB (Current Setup)

1. **Using phpMyAdmin:**
   - Navigate to http://localhost:8082
   - Login with username: `root`, password: `scholarship_root_password`
   - Select the `scholarship_db` database
   - Click "SQL" tab
   - Copy the entire contents of `database_schema.sql`
   - Paste and click "Go"

2. **Using Command Line:**
   ```bash
   # Navigate to project directory
   cd c:\Users\kyle\e-Scholar
   
   # Import the schema
   docker exec -i scholarship-mysql-1 mysql -uroot -pscholarship_root_password scholarship_db < database_schema.sql
   ```

3. **Using MySQL Workbench:**
   - Connect to: `127.0.0.1:3307`
   - Username: `root`
   - Password: `scholarship_root_password`
   - Database: `scholarship_db`
   - File > Run SQL Script > Select `database_schema.sql`

### For PostgreSQL

1. **Using pgAdmin:**
   - Connect to your PostgreSQL server
   - Select the target database
   - Tools > Query Tool
   - Open `database_schema_postgresql.sql`
   - Execute

2. **Using Command Line:**
   ```bash
   psql -U postgres -d scholarship_db -f database_schema_postgresql.sql
   ```

## Features Included

### Views
1. **vw_student_complete** - Complete student profile with all related data
2. **vw_liquidation_summary** - Summary of liquidations with deadline status

### Stored Procedures/Functions

#### MySQL:
- `sp_get_student_profile(student_id)` - Get complete student profile
- `sp_get_overdue_liquidations()` - List all overdue liquidations
- `sp_get_students_by_campus(campus_id)` - Get students by campus

#### PostgreSQL:
- `sp_get_student_profile(student_id)` - Returns JSON data
- `sp_get_overdue_liquidations()` - Returns table of overdue items
- `sp_get_students_by_campus(campus_id)` - Returns students by campus

### Triggers

1. **Auto-update timestamps** - Automatically updates `updated_at` field
2. **Overdue liquidations** - Auto-marks liquidations as overdue past deadline
3. **Age validation** - Ensures students are at least 15 years old

### Data Integrity Features

- Foreign key constraints with CASCADE/RESTRICT
- Email format validation
- Phone number format validation
- Date validation (birthdays, submission dates)
- Amount validation (no negative values)
- Comprehensive indexing for performance

## Usage Examples

### Query Examples

```sql
-- Get all students from Main Campus 1
SELECT * FROM vw_student_complete 
WHERE campus_code = 'MC1';

-- Get all overdue liquidations
CALL sp_get_overdue_liquidations();

-- Get student profile
CALL sp_get_student_profile(1);

-- Find students by scholarship type
SELECT student_number, full_name, course, campus_name
FROM vw_student_complete
WHERE scholarship_type = 'TES';

-- Count students per campus
SELECT 
    c.campus_name,
    COUNT(s.id) as student_count
FROM campuses c
LEFT JOIN students s ON c.id = s.campus_id
GROUP BY c.campus_name
ORDER BY student_count DESC;

-- Liquidations due this month
SELECT * FROM vw_liquidation_summary
WHERE MONTH(deadline_date) = MONTH(CURRENT_DATE())
AND YEAR(deadline_date) = YEAR(CURRENT_DATE())
ORDER BY deadline_date;
```

### Insert Examples

```sql
-- Insert a new student
INSERT INTO students (
    student_number, lrn, scholarship_type,
    first_name, middle_name, last_name,
    gender, birthday, course, campus_id,
    registration_status, email
) VALUES (
    '2024-00001', '123456789012', 'TES',
    'Juan', 'Santos', 'Dela Cruz',
    'Male', '2005-05-15', 'BS Computer Science', 1,
    'Enrolled', 'juan.delacruz@norsu.edu.ph'
);

-- Insert student address
INSERT INTO student_addresses (
    student_id, street, barangay, city, province
) VALUES (
    1, '123 Main Street', 'Poblacion', 'Dumaguete City', 'Negros Oriental'
);

-- Insert family information
INSERT INTO student_family (
    student_id, father_name, father_contact,
    mother_name, mother_contact, annual_income
) VALUES (
    1, 'Pedro Dela Cruz', '09171234567',
    'Maria Dela Cruz', '09181234567', 150000.00
);

-- Insert liquidation record
INSERT INTO liquidations (
    student_id, scholarship_type, academic_year, semester,
    deadline_date, status, amount
) VALUES (
    1, 'TES', '2024-2025', '1st Semester',
    '2024-12-15', 'Pending', 10000.00
);
```

## Symfony Integration

The database is already configured in your `.env` file:

```env
DATABASE_URL="mysql://scholarship_user:scholarship_password@127.0.0.1:3307/scholarship_db?serverVersion=8.0.44&charset=utf8mb4"
```

### Doctrine Entity Mapping

To generate Doctrine entities from the database:

```bash
# Generate entities from existing database
php bin/console doctrine:mapping:import "App\Entity" annotation --path=src/Entity

# Or use make:entity to create/update entities manually
php bin/console make:entity
```

## Verification

After running the schema, verify the installation:

```sql
-- Check all tables exist
SHOW TABLES;

-- Check record counts
SELECT 'campuses' AS table_name, COUNT(*) AS record_count FROM campuses
UNION ALL
SELECT 'students', COUNT(*) FROM students
UNION ALL
SELECT 'student_addresses', COUNT(*) FROM student_addresses
UNION ALL
SELECT 'student_family', COUNT(*) FROM student_family
UNION ALL
SELECT 'liquidations', COUNT(*) FROM liquidations;

-- Should show 7 campuses
SELECT * FROM campuses;
```

## Backup and Restore

### Backup
```bash
# MySQL backup
docker exec scholarship-mysql-1 mysqldump -uroot -pscholarship_root_password scholarship_db > backup.sql

# PostgreSQL backup
pg_dump -U postgres scholarship_db > backup.sql
```

### Restore
```bash
# MySQL restore
docker exec -i scholarship-mysql-1 mysql -uroot -pscholarship_root_password scholarship_db < backup.sql

# PostgreSQL restore
psql -U postgres scholarship_db < backup.sql
```

## Security Notes

1. **Change default passwords** in production
2. **Use environment variables** for sensitive data
3. **Grant minimal permissions** to application users
4. **Regular backups** are essential
5. **Validate input** at application level
6. **Use prepared statements** to prevent SQL injection

## Performance Tips

1. Indexes are already created on frequently queried columns
2. Use views for complex queries
3. Consider partitioning for large datasets
4. Monitor slow queries and optimize as needed
5. Regular ANALYZE/OPTIMIZE table maintenance

## Troubleshooting

### Common Issues

1. **Foreign key constraint errors:**
   - Ensure parent records exist before inserting child records
   - Check cascade rules are appropriate

2. **Date format errors:**
   - Use YYYY-MM-DD format for dates
   - Validate dates before insertion

3. **Duplicate key errors:**
   - Check unique constraints (student_number, lrn, email)
   - Ensure values are truly unique

4. **Connection errors:**
   - Verify Docker containers are running
   - Check port mappings (3307 for MySQL)
   - Confirm credentials match .env file

## Support

For issues or questions:
1. Check Docker container logs: `docker logs scholarship-mysql-1`
2. Verify database connection in Symfony: `php bin/console doctrine:schema:validate`
3. Review error logs in `var/log/`

---

**Last Updated:** October 13, 2026
**Database Version:** 1.0
**Compatible with:** MySQL 8.0+, MariaDB 10.4+, PostgreSQL 12+
