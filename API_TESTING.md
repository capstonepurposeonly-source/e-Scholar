# e-Scholar Dashboard API Testing Guide

## Base URL
```
http://localhost:8000
```

## API Endpoints

### 1. Dashboard Overview
**Endpoint**: `/api/dashboard/overview`  
**Method**: GET  
**Description**: Get complete dashboard overview with all statistics

**cURL Example**:
```bash
curl http://localhost:8000/api/dashboard/overview
```

**PowerShell Example**:
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/overview" -Method Get | ConvertTo-Json -Depth 10
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "overview": {
      "total_students": 20,
      "enrolled_students": 18,
      "not_enrolled_students": 2
    },
    "campus_distribution": [
      {
        "campus_name": "Main Campus 1",
        "campus_code": "MC1",
        "student_count": "3"
      },
      ...
    ],
    "liquidations": {
      "statistics": {
        "total": 19,
        "pending": 10,
        "overdue": 3,
        "dueSoon": 2,
        "submitted": 3,
        "approved": 3
      },
      "upcoming": [...]
    },
    "scholarship_distribution": {
      "TES": 7,
      "CHED-UNIFAST": 5,
      "TDP": 3,
      ...
    }
  },
  "timestamp": "2026-10-13 10:30:00"
}
```

---

### 2. Campus Statistics
**Endpoint**: `/api/dashboard/campus-stats`  
**Method**: GET  
**Description**: Get detailed student statistics per campus

**cURL Example**:
```bash
curl http://localhost:8000/api/dashboard/campus-stats
```

**PowerShell Example**:
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/campus-stats" -Method Get | ConvertTo-Json -Depth 5
```

**Expected Response**:
```json
{
  "success": true,
  "data": [
    {
      "campus_name": "Main Campus 1",
      "campus_code": "MC1",
      "total_students": "3",
      "enrolled": "3",
      "not_enrolled": "0"
    },
    {
      "campus_name": "Main Campus 2",
      "campus_code": "MC2",
      "total_students": "3",
      "enrolled": "3",
      "not_enrolled": "0"
    },
    ...
  ]
}
```

---

### 3. Upcoming Liquidations
**Endpoint**: `/api/dashboard/liquidations/upcoming`  
**Method**: GET  
**Parameters**: 
- `days` (optional, default: 30) - Number of days to look ahead

**cURL Example**:
```bash
# Next 30 days
curl http://localhost:8000/api/dashboard/liquidations/upcoming

# Next 7 days
curl http://localhost:8000/api/dashboard/liquidations/upcoming?days=7
```

**PowerShell Example**:
```powershell
# Next 30 days
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/liquidations/upcoming" -Method Get | ConvertTo-Json -Depth 10

# Next 7 days
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/liquidations/upcoming?days=7" -Method Get | ConvertTo-Json -Depth 10
```

**Expected Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 4,
      "student": {
        "id": 2,
        "name": "Maria Santos",
        "student_number": "2024-00002",
        "campus": "Main Campus 1",
        "email": "maria.santos@norsu.edu.ph"
      },
      "scholarship_type": "CHED-UNIFAST",
      "academic_year": "2024-2025",
      "semester": "1st Semester",
      "deadline_date": "2026-10-15",
      "deadline_formatted": "October 15, 2026",
      "days_until_deadline": 2,
      "status": "Pending",
      "deadline_status": "Due Soon",
      "amount": "20000.00"
    },
    ...
  ],
  "count": 10
}
```

---

### 4. Overdue Liquidations
**Endpoint**: `/api/dashboard/liquidations/overdue`  
**Method**: GET  
**Description**: Get all overdue liquidations

**cURL Example**:
```bash
curl http://localhost:8000/api/dashboard/liquidations/overdue
```

**PowerShell Example**:
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/liquidations/overdue" -Method Get | ConvertTo-Json -Depth 10
```

**Expected Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student": {
        "id": 1,
        "name": "Juan Dela Cruz",
        "student_number": "2024-00001",
        "campus": "Main Campus 1",
        "email": "juan.delacruz@norsu.edu.ph",
        "contact_number": "09171234567"
      },
      "scholarship_type": "TES",
      "academic_year": "2024-2025",
      "semester": "1st Semester",
      "deadline_date": "2026-10-05",
      "days_overdue": 8,
      "amount": "10000.00"
    },
    ...
  ],
  "count": 3
}
```

---

### 5. Liquidations by Status
**Endpoint**: `/api/dashboard/liquidations/status/{status}`  
**Method**: GET  
**Parameters**: 
- `status` (required) - Valid values: Pending, Submitted, Approved, Rejected, Overdue

**cURL Examples**:
```bash
# Get pending liquidations
curl http://localhost:8000/api/dashboard/liquidations/status/Pending

# Get submitted liquidations
curl http://localhost:8000/api/dashboard/liquidations/status/Submitted

# Get approved liquidations
curl http://localhost:8000/api/dashboard/liquidations/status/Approved
```

**PowerShell Examples**:
```powershell
# Get pending liquidations
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/liquidations/status/Pending" -Method Get | ConvertTo-Json -Depth 10

# Get submitted liquidations
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/liquidations/status/Submitted" -Method Get | ConvertTo-Json -Depth 10
```

**Expected Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 4,
      "student_name": "Maria Santos",
      "student_number": "2024-00002",
      "scholarship_type": "CHED-UNIFAST",
      "deadline_date": "2026-10-15",
      "status": "Pending",
      "amount": "20000.00"
    },
    ...
  ],
  "count": 10
}
```

---

### 6. Statistics Summary
**Endpoint**: `/api/dashboard/statistics/summary`  
**Method**: GET  
**Description**: Get quick summary of all key statistics

**cURL Example**:
```bash
curl http://localhost:8000/api/dashboard/statistics/summary
```

**PowerShell Example**:
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/statistics/summary" -Method Get | ConvertTo-Json -Depth 5
```

**Expected Response**:
```json
{
  "success": true,
  "data": {
    "total_students": 20,
    "total_campuses": 7,
    "total_scholarship_programs": 6,
    "liquidations": {
      "total": 19,
      "pending": 10,
      "overdue": 3,
      "due_soon": 2,
      "submitted": 3,
      "approved": 3
    }
  }
}
```

---

## Testing Script (PowerShell)

Save as `test-api.ps1`:

```powershell
# e-Scholar API Test Script
$baseUrl = "http://localhost:8000"

Write-Host "================================" -ForegroundColor Cyan
Write-Host "e-Scholar API Testing" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Test 1: Dashboard Overview
Write-Host "Test 1: Dashboard Overview" -ForegroundColor Yellow
try {
    $overview = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/overview" -Method Get
    Write-Host "✓ Total Students: $($overview.data.overview.total_students)" -ForegroundColor Green
    Write-Host "✓ Total Liquidations: $($overview.data.liquidations.statistics.total)" -ForegroundColor Green
    Write-Host "✓ Overdue: $($overview.data.liquidations.statistics.overdue)" -ForegroundColor Red
} catch {
    Write-Host "✗ Failed: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 2: Campus Statistics
Write-Host "Test 2: Campus Statistics" -ForegroundColor Yellow
try {
    $campusStats = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/campus-stats" -Method Get
    Write-Host "✓ Campuses Found: $($campusStats.data.Count)" -ForegroundColor Green
    foreach ($campus in $campusStats.data) {
        Write-Host "  - $($campus.campus_name): $($campus.total_students) students" -ForegroundColor White
    }
} catch {
    Write-Host "✗ Failed: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 3: Upcoming Liquidations
Write-Host "Test 3: Upcoming Liquidations (7 days)" -ForegroundColor Yellow
try {
    $upcoming = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/liquidations/upcoming?days=7" -Method Get
    Write-Host "✓ Found: $($upcoming.count) liquidations" -ForegroundColor Green
    foreach ($liq in $upcoming.data) {
        Write-Host "  - $($liq.student.name): $($liq.deadline_date) ($($liq.deadline_status))" -ForegroundColor White
    }
} catch {
    Write-Host "✗ Failed: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 4: Overdue Liquidations
Write-Host "Test 4: Overdue Liquidations" -ForegroundColor Yellow
try {
    $overdue = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/liquidations/overdue" -Method Get
    Write-Host "✓ Overdue Count: $($overdue.count)" -ForegroundColor $(if ($overdue.count -gt 0) { "Red" } else { "Green" })
    foreach ($liq in $overdue.data) {
        Write-Host "  - $($liq.student.name): $($liq.days_overdue) days overdue" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Failed: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 5: Liquidations by Status
Write-Host "Test 5: Liquidations by Status" -ForegroundColor Yellow
$statuses = @("Pending", "Submitted", "Approved")
foreach ($status in $statuses) {
    try {
        $byStatus = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/liquidations/status/$status" -Method Get
        Write-Host "✓ $status`: $($byStatus.count)" -ForegroundColor Green
    } catch {
        Write-Host "✗ $status`: Failed" -ForegroundColor Red
    }
}
Write-Host ""

# Test 6: Statistics Summary
Write-Host "Test 6: Statistics Summary" -ForegroundColor Yellow
try {
    $summary = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/statistics/summary" -Method Get
    Write-Host "✓ Students: $($summary.data.total_students)" -ForegroundColor Green
    Write-Host "✓ Campuses: $($summary.data.total_campuses)" -ForegroundColor Green
    Write-Host "✓ Scholarship Programs: $($summary.data.total_scholarship_programs)" -ForegroundColor Green
    Write-Host "✓ Liquidations Summary:" -ForegroundColor Green
    Write-Host "  - Total: $($summary.data.liquidations.total)" -ForegroundColor White
    Write-Host "  - Pending: $($summary.data.liquidations.pending)" -ForegroundColor White
    Write-Host "  - Overdue: $($summary.data.liquidations.overdue)" -ForegroundColor Red
    Write-Host "  - Due Soon: $($summary.data.liquidations.due_soon)" -ForegroundColor Yellow
} catch {
    Write-Host "✗ Failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Testing Complete!" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
```

Run the script:
```powershell
.\test-api.ps1
```

---

## Testing with Postman

### Import Collection
1. Open Postman
2. Click "Import"
3. Copy and paste this collection JSON:

```json
{
  "info": {
    "name": "e-Scholar Dashboard API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Dashboard Overview",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/dashboard/overview"
      }
    },
    {
      "name": "Campus Statistics",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/dashboard/campus-stats"
      }
    },
    {
      "name": "Upcoming Liquidations",
      "request": {
        "method": "GET",
        "url": {
          "raw": "http://localhost:8000/api/dashboard/liquidations/upcoming?days=30",
          "query": [
            {
              "key": "days",
              "value": "30"
            }
          ]
        }
      }
    },
    {
      "name": "Overdue Liquidations",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/dashboard/liquidations/overdue"
      }
    },
    {
      "name": "Liquidations by Status - Pending",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/dashboard/liquidations/status/Pending"
      }
    },
    {
      "name": "Statistics Summary",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/dashboard/statistics/summary"
      }
    }
  ]
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "error": "Invalid status. Valid statuses: Pending, Submitted, Approved, Rejected, Overdue"
}
```

### 404 Not Found
```json
{
  "error": "Route not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal server error",
  "message": "Database connection failed"
}
```

---

## Browser Testing

Simply open these URLs in your browser:

1. **Dashboard Overview**:  
   http://localhost:8000/api/dashboard/overview

2. **Campus Stats**:  
   http://localhost:8000/api/dashboard/campus-stats

3. **Upcoming Liquidations**:  
   http://localhost:8000/api/dashboard/liquidations/upcoming

4. **Overdue Liquidations**:  
   http://localhost:8000/api/dashboard/liquidations/overdue

5. **Statistics Summary**:  
   http://localhost:8000/api/dashboard/statistics/summary

---

## Response Times

Expected response times for each endpoint:

| Endpoint | Expected Time |
|----------|---------------|
| Overview | < 200ms |
| Campus Stats | < 100ms |
| Upcoming Liquidations | < 150ms |
| Overdue Liquidations | < 150ms |
| Liquidations by Status | < 100ms |
| Statistics Summary | < 150ms |

---

## Common Issues

### Issue: Connection Refused
**Solution**: Ensure Symfony dev server is running
```bash
php bin/console server:run
# or
symfony serve
```

### Issue: 404 Not Found
**Solution**: Clear Symfony cache
```bash
php bin/console cache:clear
```

### Issue: Empty Data
**Solution**: Ensure sample data is inserted
```bash
Get-Content sample_data.sql | docker exec -i scholarship-mysql-1 mysql -uroot -pscholarship_root_password scholarship_db
```

---

**Last Updated**: October 13, 2026
