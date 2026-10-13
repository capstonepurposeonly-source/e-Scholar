# e-Scholar Dashboard Implementation Guide

## Overview
This guide explains the new Admin Dashboard with analytics, liquidation tracking, and API endpoints.

## 🎯 What's Been Created

### 1. Backend Components

#### New Entities
- **Campus** (`src/Entity/Campus.php`) - Campus information
- **Liquidation** (`src/Entity/Liquidation.php`) - Scholarship liquidation tracking

#### New Repositories
- **CampusRepository** (`src/Repository/CampusRepository.php`)
- **LiquidationRepository** (`src/Repository/LiquidationRepository.php`)
  - Methods for tracking upcoming, overdue, and status-based liquidations

#### API Controller
- **DashboardApiController** (`src/Controller/Api/DashboardApiController.php`)
  - `/api/dashboard/overview` - Complete dashboard overview
  - `/api/dashboard/campus-stats` - Campus-wise student statistics
  - `/api/dashboard/liquidations/upcoming?days=30` - Upcoming liquidations
  - `/api/dashboard/liquidations/overdue` - Overdue liquidations
  - `/api/dashboard/liquidations/status/{status}` - Filter by status
  - `/api/dashboard/statistics/summary` - Quick summary stats

### 2. Frontend Components

#### Enhanced Dashboard
- **New Template**: `templates/admin/dashboard_new.html.twig`
- **Features**:
  - Metric cards showing key statistics
  - Campus distribution cards
  - Scholarship distribution pie chart
  - Registration status bar chart
  - Upcoming liquidations table (30-day view)
  - Overdue liquidations alert section
  - Responsive design with mobile support

## 📊 Dashboard Features

### Key Metrics Display
1. **Total Students** - University-wide count
2. **Pending Liquidations** - Awaiting submission
3. **Overdue Liquidations** - Past deadline
4. **Due Within 7 Days** - Urgent deadlines
5. **Approved This Month** - Completed liquidations
6. **Active Campuses** - Number of campuses

### Campus Distribution
Displays student count for each campus:
- Main Campus 1 (MC1)
- Main Campus 2 (MC2)
- Guihulngan Campus (GHN)
- Bais Campus (BAS)
- Pamplona Campus (PAM)
- Mabinay Campus (MAB)
- Bayawan Campus (BAY)

### Liquidation Tracker
Shows upcoming liquidations with:
- Student information
- Scholarship type
- Academic year and semester
- Deadline date
- Days until deadline
- Status indicators:
  - **On Time** (green) - More than 3 days remaining
  - **Due Soon** (yellow) - 3 days or less
  - **Overdue** (red) - Past deadline
- Amount to be liquidated

### Status Indicators
- **Pending** - Awaiting submission
- **Submitted** - Submitted, awaiting approval
- **Approved** - Approved and processed
- **Rejected** - Rejected with reason
- **Overdue** - Past deadline, not submitted

## 🚀 Implementation Steps

### Step 1: Update Database Schema

The database schema was already created in the previous step. Ensure you've run:

```bash
Get-Content database_schema.sql | docker exec -i scholarship-mysql-1 mysql -uroot -pscholarship_root_password scholarship_db
```

### Step 2: Create Doctrine Entities (if not using direct SQL)

Since we're using the SQL schema, Symfony needs to recognize the new tables:

```bash
# Generate entities from database (optional)
php bin/console doctrine:mapping:import "App\Entity" attribute --path=src/Entity

# Update schema (sync with database)
php bin/console doctrine:schema:update --force
```

### Step 3: Update Routes

Add to `config/routes.yaml` if needed (controllers use attributes, so this is automatic):

```yaml
# API routes are auto-loaded via controller attributes
```

### Step 4: Switch to New Dashboard

Option A: Replace the old dashboard
```bash
# Backup old dashboard
mv templates/admin/dashboard.html.twig templates/admin/dashboard_old.html.twig

# Use new dashboard
mv templates/admin/dashboard_new.html.twig templates/admin/dashboard.html.twig
```

Option B: Update route to use new dashboard
In `src/Controller/AdminController.php`, change the render call to use `dashboard_new.html.twig`.

## 🧪 Testing the Implementation

### 1. Test Database Connection

```bash
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM campuses"
php bin/console doctrine:query:sql "SELECT * FROM campuses"
```

### 2. Test API Endpoints

#### Get Dashboard Overview
```bash
curl http://localhost:8000/api/dashboard/overview
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "overview": {
      "total_students": 0,
      "enrolled_students": 0,
      "not_enrolled_students": 0
    },
    "campus_distribution": [
      {"campus_name": "Main Campus 1", "campus_code": "MC1", "student_count": 0},
      ...
    ],
    "liquidations": {
      "statistics": {
        "total": 0,
        "pending": 0,
        "overdue": 0,
        "dueSoon": 0,
        "submitted": 0,
        "approved": 0
      },
      "upcoming": []
    }
  }
}
```

#### Get Campus Statistics
```bash
curl http://localhost:8000/api/dashboard/campus-stats
```

#### Get Upcoming Liquidations
```bash
curl http://localhost:8000/api/dashboard/liquidations/upcoming?days=30
```

#### Get Overdue Liquidations
```bash
curl http://localhost:8000/api/dashboard/liquidations/overdue
```

#### Get Summary Statistics
```bash
curl http://localhost:8000/api/dashboard/statistics/summary
```

### 3. Test Dashboard UI

1. Navigate to: `http://localhost:8000/admin/dashboard`
2. Verify:
   - ✅ Metric cards display correctly
   - ✅ Campus distribution shows all campuses
   - ✅ Charts render (if data exists)
   - ✅ Liquidation tables display
   - ✅ Responsive design works on mobile

## 📝 Adding Sample Data

### Insert Sample Students

```sql
-- Insert sample students
INSERT INTO students (
    student_number, lrn, scholarship_type,
    first_name, middle_name, last_name,
    gender, marital_status, birthday,
    course, campus_id, registration_status, email,
    created_at, updated_at, is_active
) VALUES
('2024-00001', '123456789001', 'TES', 'Juan', 'Santos', 'Dela Cruz', 'Male', 'Single', '2004-05-15', 'BS Computer Science', 1, 'Enrolled', 'juan.delacruz@norsu.edu.ph', NOW(), NOW(), 1),
('2024-00002', '123456789002', 'CHED-UNIFAST', 'Maria', 'Garcia', 'Santos', 'Female', 'Single', '2003-08-22', 'BS Nursing', 2, 'Enrolled', 'maria.santos@norsu.edu.ph', NOW(), NOW(), 1),
('2024-00003', '123456789003', 'TDP', 'Pedro', 'Reyes', 'Gonzales', 'Male', 'Single', '2005-03-10', 'BS Engineering', 3, 'Enrolled', 'pedro.gonzales@norsu.edu.ph', NOW(), NOW(), 1),
('2024-00004', '123456789004', 'SM Foundation', 'Ana', 'Lopez', 'Rivera', 'Female', 'Single', '2004-11-30', 'BS Education', 4, 'Enrolled', 'ana.rivera@norsu.edu.ph', NOW(), NOW(), 1),
('2024-00005', '123456789005', 'TES', 'Carlos', 'Martinez', 'Torres', 'Male', 'Single', '2003-07-18', 'BS Business Admin', 5, 'Enrolled', 'carlos.torres@norsu.edu.ph', NOW(), NOW(), 1);
```

### Insert Sample Liquidations

```sql
-- Insert sample liquidations
INSERT INTO liquidations (
    student_id, scholarship_type, academic_year, semester,
    deadline_date, status, amount, created_at, updated_at
) VALUES
(1, 'TES', '2024-2025', '1st Semester', '2026-10-25', 'Pending', 10000.00, NOW(), NOW()),
(1, 'TES', '2024-2025', '2nd Semester', '2026-11-15', 'Pending', 10000.00, NOW(), NOW()),
(2, 'CHED-UNIFAST', '2024-2025', '1st Semester', '2026-10-20', 'Pending', 20000.00, NOW(), NOW()),
(3, 'TDP', '2024-2025', '1st Semester', '2026-10-18', 'Due Soon', 15000.00, NOW(), NOW()),
(4, 'SM Foundation', '2024-2025', '1st Semester', '2026-09-30', 'Overdue', 12000.00, NOW(), NOW()),
(5, 'TES', '2024-2025', '1st Semester', '2026-12-01', 'Pending', 10000.00, NOW(), NOW());
```

### SQL Script for Quick Sample Data

```sql
-- Quick sample data insertion
START TRANSACTION;

-- Ensure campuses exist (already inserted from schema)

-- Insert 20 sample students across different campuses
INSERT INTO students (student_number, lrn, scholarship_type, first_name, middle_name, last_name, gender, marital_status, birthday, course, campus_id, registration_status, email, is_active, created_at, updated_at)
SELECT 
    CONCAT('2024-', LPAD(seq, 5, '0')),
    CONCAT('1234567890', LPAD(seq, 2, '0')),
    CASE (seq % 4) 
        WHEN 0 THEN 'TES'
        WHEN 1 THEN 'CHED-UNIFAST'
        WHEN 2 THEN 'TDP'
        ELSE 'SM Foundation'
    END,
    CONCAT('Student', seq),
    'Middle',
    CONCAT('Last', seq),
    CASE (seq % 2) WHEN 0 THEN 'Male' ELSE 'Female' END,
    'Single',
    DATE_SUB(CURDATE(), INTERVAL (18 + (seq % 5)) YEAR),
    CASE (seq % 5)
        WHEN 0 THEN 'BS Computer Science'
        WHEN 1 THEN 'BS Nursing'
        WHEN 2 THEN 'BS Engineering'
        WHEN 3 THEN 'BS Education'
        ELSE 'BS Business Administration'
    END,
    ((seq % 7) + 1),
    'Enrolled',
    CONCAT('student', seq, '@norsu.edu.ph'),
    1,
    NOW(),
    NOW()
FROM (
    SELECT 1 AS seq UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 
    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
    UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15
    UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20
) nums;

-- Insert sample liquidations with various deadlines
INSERT INTO liquidations (student_id, scholarship_type, academic_year, semester, deadline_date, status, amount, created_at, updated_at)
SELECT 
    s.id,
    s.scholarship_type,
    '2024-2025',
    '1st Semester',
    CASE 
        WHEN s.id % 5 = 0 THEN DATE_SUB(CURDATE(), INTERVAL 5 DAY) -- Overdue
        WHEN s.id % 4 = 0 THEN DATE_ADD(CURDATE(), INTERVAL 2 DAY) -- Due soon
        WHEN s.id % 3 = 0 THEN DATE_ADD(CURDATE(), INTERVAL 15 DAY) -- Upcoming
        ELSE DATE_ADD(CURDATE(), INTERVAL 25 DAY) -- On time
    END,
    CASE 
        WHEN s.id % 5 = 0 THEN 'Overdue'
        WHEN s.id % 7 = 0 THEN 'Submitted'
        ELSE 'Pending'
    END,
    CASE (s.id % 3)
        WHEN 0 THEN 10000.00
        WHEN 1 THEN 15000.00
        ELSE 20000.00
    END,
    NOW(),
    NOW()
FROM students s
WHERE s.is_active = 1
LIMIT 15;

COMMIT;
```

## 🎨 Customization

### Changing Colors

Edit the CSS in `dashboard_new.html.twig`:

```css
/* Primary brand color */
.metric-icon.blue { background: #your-color; }

/* Status colors */
.status-badge.on-time { background: #your-green; }
.status-badge.due-soon { background: #your-yellow; }
.status-badge.overdue { background: #your-red; }
```

### Adding New Metrics

1. Add metric card in template:
```twig
<div class="metric-card">
    <div class="metric-icon your-color">
        <i class="fa-solid fa-your-icon"></i>
    </div>
    <div class="metric-info">
        <h3>{{ yourMetric }}</h3>
        <p>Your Label</p>
    </div>
</div>
```

2. Pass data from controller:
```php
return $this->render('admin/dashboard.html.twig', [
    'yourMetric' => $yourValue,
    // ... other data
]);
```

### Adding More Charts

```html
<div class="chart-container">
    <canvas id="yourChart"></canvas>
</div>

<script>
const ctx = document.getElementById('yourChart').getContext('2d');
new Chart(ctx, {
    type: 'line', // or 'bar', 'pie', 'doughnut'
    data: { /* your data */ },
    options: { /* your options */ }
});
</script>
```

## 🔧 Troubleshooting

### Issue: Charts not displaying
**Solution**: Ensure Chart.js is loaded
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
```

### Issue: API returns 404
**Solution**: Clear Symfony cache
```bash
php bin/console cache:clear
```

### Issue: No data in dashboard
**Solution**: Insert sample data using SQL scripts above

### Issue: Campus entity not found
**Solution**: Ensure entities are recognized
```bash
php bin/console doctrine:mapping:info
```

## 📱 Mobile Responsiveness

The dashboard is fully responsive:
- **Desktop (>1200px)**: 2-column layout
- **Tablet (768-1200px)**: 1-column layout
- **Mobile (<768px)**: Stacked cards, horizontal scroll for tables

## 🔐 Security Considerations

1. **Authentication**: Dashboard requires admin login
2. **API Authentication**: Add authentication to API endpoints in production
3. **CSRF Protection**: Already enabled in Symfony
4. **Input Validation**: Always validate user inputs
5. **SQL Injection**: Use Doctrine ORM or prepared statements

## 📚 Additional Resources

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Chart.js Documentation](https://www.chartjs.org/docs/latest/)
- [Font Awesome Icons](https://fontawesome.com/icons)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)

## ✅ Completion Checklist

- [ ] Database schema created
- [ ] Entities and repositories created
- [ ] API controller implemented
- [ ] Dashboard template created
- [ ] Sample data inserted
- [ ] API endpoints tested
- [ ] Dashboard UI tested
- [ ] Mobile responsiveness verified
- [ ] Charts displaying correctly
- [ ] Liquidation tracking working

---

**Last Updated**: October 13, 2026  
**Version**: 1.0  
**Compatible with**: Symfony 6.x+, PHP 8.1+, MySQL 8.0+
