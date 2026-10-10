# e-Scholar: NORSU Integrated Scholarship Data Management System

A comprehensive scholarship management and student monitoring system for Negros Oriental State University (NORSU).

## System Features

### Student Module
- **Comprehensive Registration Form**: Captures all student information including:
  - Personal information (name, birth date, civil status, etc.)
  - Academic details (campus, course, year level, GWA, etc.)
  - Contact information (email, phone numbers)
  - Complete address details
  - Family information (parents, guardians, siblings, income)
  - Profile picture upload (2x2 ID photo)
  
- **7 NORSU Campuses Supported**:
  - Main Campus 1
  - Main Campus 2
  - Guihulngan
  - Bais
  - Pamplona
  - Mabinay
  - Bayawan

- **9 Scholarship Programs**:
  - ACEF-GIAHEP
  - CMSP
  - COSCHO
  - Landbank
  - NGCP
  - SIDA-SGP
  - TDP
  - TES
  - PLDT

### Admin Module
- **Dashboard with Statistics**:
  - Total students count
  - Campus distribution
  - Scholarship type distribution
  - Year level distribution
  - Registration status tracking (Pending, Approved, Rejected, On Hold)

- **Student Management**:
  - Search and filter students by:
    - Name or student number
    - Campus
    - Year level
    - Scholarship type
    - Registration status
  - View complete student profiles
  - Update registration status
  - Track all student information

- **Real-time Data**:
  - Campus-wide statistics
  - Scholarship program monitoring
  - Application status tracking

## Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL/MariaDB
- Symfony CLI (optional but recommended)

### Step 1: Database Configuration

Edit your `.env` file and configure your database connection:

```env
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/norsu_scholarship?serverVersion=8.0"
```

### Step 2: Create the Database

```bash
php bin/console doctrine:database:create
```

### Step 3: Run Migrations

Run the migrations to create all necessary tables:

```bash
php bin/console doctrine:migrations:migrate
```

This will create:
- `admin` table - for admin user accounts
- `student` table - for scholarship student records

### Step 4: Create Admin User

Create an admin account to access the admin panel:

```bash
php bin/console app:create-admin
```

Follow the prompts to enter:
- Full Name
- Email
- Password

### Step 5: Create Upload Directory

Create the directory for profile pictures:

```bash
mkdir public/uploads
mkdir public/uploads/profile_pictures
```

On Windows PowerShell:
```powershell
New-Item -ItemType Directory -Path public\uploads\profile_pictures -Force
```

### Step 6: Start the Development Server

Using Symfony CLI (recommended):
```bash
symfony server:start
```

Or using PHP built-in server:
```bash
php -S localhost:8000 -t public
```

## Usage

### For Students

1. **Access the Landing Page**: Visit `http://localhost:8000` (or your server URL)

2. **Register for Scholarship**:
   - Click "Student Portal" in navigation OR
   - Click "APPLY NOW" button in hero section
   - Fill out the comprehensive registration form
   - Upload your 2x2 ID photo
   - Submit application

3. **Track Application**: Use the track application feature (coming soon)

### For Administrators

1. **Login**: 
   - Visit `http://localhost:8000/admin/login`
   - Enter your admin credentials

2. **Dashboard**:
   - View real-time statistics
   - See campus distribution
   - Monitor scholarship programs
   - Check application statuses

3. **Manage Students**:
   - Click "Manage Students" from dashboard
   - Use filters to search students:
     - By name or student number
     - By campus
     - By year level
     - By scholarship type
     - By registration status
   - Click "View" to see complete student profile

4. **Review Applications**:
   - View student details
   - Update registration status:
     - Pending (initial status)
     - Approved
     - Rejected
     - On Hold

## File Structure

```
c:\Users\kyle\Scholarship\
├── src/
│   ├── Controller/
│   │   ├── AdminController.php      # Admin dashboard & management
│   │   ├── LandingController.php    # Public landing page
│   │   ├── ScholarshipController.php # Scholarship details
│   │   └── StudentController.php    # Student registration
│   ├── Entity/
│   │   ├── Admin.php                # Admin user entity
│   │   └── Student.php              # Student entity (40+ fields)
│   ├── Form/
│   │   └── StudentRegistrationType.php # Registration form
│   ├── Repository/
│   │   ├── AdminRepository.php      # Admin queries
│   │   └── StudentRepository.php    # Student queries with statistics
│   └── Command/
│       └── CreateAdminCommand.php   # CLI command to create admin
├── templates/
│   ├── admin/
│   │   ├── login.html.twig         # Admin login page
│   │   ├── dashboard.html.twig     # Admin dashboard with stats
│   │   ├── students.html.twig      # Student list with filters
│   │   └── student_view.html.twig  # Individual student details
│   ├── student/
│   │   └── register.html.twig      # Student registration form
│   ├── landing/
│   │   └── index.html.twig         # Public landing page
│   └── scholarship/
│       └── detail.html.twig        # Scholarship program details
├── public/
│   ├── images/                     # Logo and images
│   └── uploads/
│       └── profile_pictures/       # Student profile pictures
└── migrations/
    ├── Version20261006_Admin.php   # Admin table migration
    └── Version20261007134514.php   # Student table migration
```

## Routes

### Public Routes
- `/` - Landing page
- `/scholarship/{slug}` - Scholarship program details
- `/student/register` - Student registration form

### Admin Routes (Protected - Requires Login)
- `/admin/login` - Admin login
- `/admin/dashboard` - Admin dashboard
- `/admin/students` - Manage students (with filters)
- `/admin/students/{id}` - View student details
- `/admin/students/{id}/update-status` - Update student status
- `/admin/logout` - Logout

## Database Schema

### Student Table
Comprehensive student information with 40+ fields including:
- Scholarship details
- Personal information
- Academic records
- Contact details
- Complete address
- Family information
- Profile picture path
- Registration status & timestamps

### Admin Table
- id
- full_name
- email (unique)
- password (hashed)
- roles (JSON)

## Security Features
- Password hashing with bcrypt
- CSRF protection on forms
- Form validation
- Secure file uploads
- Access control (admin routes protected)
- SQL injection prevention (Doctrine ORM)

## Future Enhancements
- Student tracking system by student number
- Email notifications for status changes
- Document upload for scholarship requirements
- Liquidation deadline tracking
- Export student data to Excel/PDF
- SMS notifications
- Student portal for tracking own application
- Bulk operations for admin
- Advanced reporting and analytics

## Support
For issues or questions, contact the NORSU Scholarship Office.

## Color Scheme
- Primary Blue: #1e5ba8
- Secondary Blue: #2c7ac7
- Accent Yellow: #ffc107
- Font: Poppins

## Version
1.0.0 - Initial Release

## Credits
Developed for Negros Oriental State University (NORSU)
e-Scholar: Integrated Scholarship Data Management and Student Monitoring System
