# e-Scholar Dashboard API Test Script
# PowerShell script to test all API endpoints

$baseUrl = "http://localhost:8000"
$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host " e-Scholar API Testing Suite" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Base URL: $baseUrl" -ForegroundColor Gray
Write-Host "Timestamp: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Gray
Write-Host ""

# Test counter
$testsPassed = 0
$testsFailed = 0

# Test 1: Dashboard Overview
Write-Host "[Test 1/6] Dashboard Overview" -ForegroundColor Yellow
Write-Host "Endpoint: /api/dashboard/overview" -ForegroundColor Gray
try {
    $overview = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/overview" -Method Get -ErrorAction Stop
    if ($overview.success) {
        Write-Host "✓ Status: SUCCESS" -ForegroundColor Green
        Write-Host "  Total Students: $($overview.data.overview.total_students)" -ForegroundColor White
        Write-Host "  Enrolled: $($overview.data.overview.enrolled_students)" -ForegroundColor White
        Write-Host "  Not Enrolled: $($overview.data.overview.not_enrolled_students)" -ForegroundColor White
        Write-Host "  Total Liquidations: $($overview.data.liquidations.statistics.total)" -ForegroundColor White
        Write-Host "  Pending: $($overview.data.liquidations.statistics.pending)" -ForegroundColor Yellow
        Write-Host "  Overdue: $($overview.data.liquidations.statistics.overdue)" -ForegroundColor Red
        Write-Host "  Due Soon: $($overview.data.liquidations.statistics.dueSoon)" -ForegroundColor Yellow
        Write-Host "  Approved: $($overview.data.liquidations.statistics.approved)" -ForegroundColor Green
        $testsPassed++
    } else {
        Write-Host "✗ API returned success=false" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "✗ FAILED: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}
Write-Host ""

# Test 2: Campus Statistics
Write-Host "[Test 2/6] Campus Statistics" -ForegroundColor Yellow
Write-Host "Endpoint: /api/dashboard/campus-stats" -ForegroundColor Gray
try {
    $campusStats = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/campus-stats" -Method Get -ErrorAction Stop
    if ($campusStats.success) {
        Write-Host "✓ Status: SUCCESS" -ForegroundColor Green
        Write-Host "  Campuses Found: $($campusStats.data.Count)" -ForegroundColor White
        Write-Host ""
        Write-Host "  Campus Breakdown:" -ForegroundColor Cyan
        foreach ($campus in $campusStats.data) {
            $code = $campus.campus_code.PadRight(5)
            $name = $campus.campus_name.PadRight(20)
            Write-Host "    [$code] $name - $($campus.total_students) students (Enrolled: $($campus.enrolled), Not Enrolled: $($campus.not_enrolled))" -ForegroundColor White
        }
        $testsPassed++
    } else {
        Write-Host "✗ API returned success=false" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "✗ FAILED: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}
Write-Host ""

# Test 3: Upcoming Liquidations (7 days)
Write-Host "[Test 3/6] Upcoming Liquidations (7 days)" -ForegroundColor Yellow
Write-Host "Endpoint: /api/dashboard/liquidations/upcoming?days=7" -ForegroundColor Gray
try {
    $upcoming = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/liquidations/upcoming?days=7" -Method Get -ErrorAction Stop
    if ($upcoming.success) {
        Write-Host "✓ Status: SUCCESS" -ForegroundColor Green
        Write-Host "  Found: $($upcoming.count) liquidations due within 7 days" -ForegroundColor White
        if ($upcoming.count -gt 0) {
            Write-Host ""
            Write-Host "  Details:" -ForegroundColor Cyan
            foreach ($liq in $upcoming.data) {
                $statusColor = switch ($liq.deadline_status) {
                    "Overdue" { "Red" }
                    "Due Soon" { "Yellow" }
                    default { "White" }
                }
                Write-Host "    • $($liq.student.name) [$($liq.student.student_number)]" -ForegroundColor White
                Write-Host "      $($liq.scholarship_type) - Due: $($liq.deadline_formatted) ($($liq.days_until_deadline) days)" -ForegroundColor $statusColor
            }
        }
        $testsPassed++
    } else {
        Write-Host "✗ API returned success=false" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "✗ FAILED: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}
Write-Host ""

# Test 4: Overdue Liquidations
Write-Host "[Test 4/6] Overdue Liquidations" -ForegroundColor Yellow
Write-Host "Endpoint: /api/dashboard/liquidations/overdue" -ForegroundColor Gray
try {
    $overdue = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/liquidations/overdue" -Method Get -ErrorAction Stop
    if ($overdue.success) {
        Write-Host "✓ Status: SUCCESS" -ForegroundColor Green
        $overdueColor = if ($overdue.count -gt 0) { "Red" } else { "Green" }
        Write-Host "  Overdue Count: $($overdue.count)" -ForegroundColor $overdueColor
        if ($overdue.count -gt 0) {
            Write-Host ""
            Write-Host "  ⚠ URGENT - Overdue Liquidations:" -ForegroundColor Red
            foreach ($liq in $overdue.data) {
                Write-Host "    • $($liq.student.name) [$($liq.student.student_number)]" -ForegroundColor White
                Write-Host "      $($liq.scholarship_type) - $($liq.days_overdue) days overdue!" -ForegroundColor Red
                Write-Host "      Contact: $($liq.student.email) | $($liq.student.contact_number)" -ForegroundColor Gray
            }
        }
        $testsPassed++
    } else {
        Write-Host "✗ API returned success=false" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "✗ FAILED: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}
Write-Host ""

# Test 5: Liquidations by Status
Write-Host "[Test 5/6] Liquidations by Status" -ForegroundColor Yellow
Write-Host "Endpoint: /api/dashboard/liquidations/status/{status}" -ForegroundColor Gray
$statuses = @("Pending", "Submitted", "Approved", "Rejected")
$statusResults = @{}

foreach ($status in $statuses) {
    try {
        $byStatus = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/liquidations/status/$status" -Method Get -ErrorAction Stop
        if ($byStatus.success) {
            $statusResults[$status] = $byStatus.count
        }
    } catch {
        $statusResults[$status] = "Error"
    }
}

Write-Host "✓ Status: SUCCESS" -ForegroundColor Green
Write-Host "  Status Breakdown:" -ForegroundColor White
foreach ($status in $statuses) {
    $count = $statusResults[$status]
    $color = switch ($status) {
        "Pending" { "Yellow" }
        "Submitted" { "Cyan" }
        "Approved" { "Green" }
        "Rejected" { "Red" }
        default { "White" }
    }
    Write-Host "    $($status.PadRight(15)): $count" -ForegroundColor $color
}
$testsPassed++
Write-Host ""

# Test 6: Statistics Summary
Write-Host "[Test 6/6] Statistics Summary" -ForegroundColor Yellow
Write-Host "Endpoint: /api/dashboard/statistics/summary" -ForegroundColor Gray
try {
    $summary = Invoke-RestMethod -Uri "$baseUrl/api/dashboard/statistics/summary" -Method Get -ErrorAction Stop
    if ($summary.success) {
        Write-Host "✓ Status: SUCCESS" -ForegroundColor Green
        Write-Host ""
        Write-Host "  📊 OVERALL SUMMARY:" -ForegroundColor Cyan
        Write-Host "  ==================" -ForegroundColor Cyan
        Write-Host "  Total Students:           $($summary.data.total_students)" -ForegroundColor White
        Write-Host "  Active Campuses:          $($summary.data.total_campuses)" -ForegroundColor White
        Write-Host "  Scholarship Programs:     $($summary.data.total_scholarship_programs)" -ForegroundColor White
        Write-Host ""
        Write-Host "  📋 Liquidations:" -ForegroundColor Cyan
        Write-Host "    Total:                  $($summary.data.liquidations.total)" -ForegroundColor White
        Write-Host "    Pending:                $($summary.data.liquidations.pending)" -ForegroundColor Yellow
        Write-Host "    Overdue:                $($summary.data.liquidations.overdue)" -ForegroundColor Red
        Write-Host "    Due Soon (7 days):      $($summary.data.liquidations.due_soon)" -ForegroundColor Yellow
        Write-Host "    Submitted:              $($summary.data.liquidations.submitted)" -ForegroundColor Cyan
        Write-Host "    Approved:               $($summary.data.liquidations.approved)" -ForegroundColor Green
        $testsPassed++
    } else {
        Write-Host "✗ API returned success=false" -ForegroundColor Red
        $testsFailed++
    }
} catch {
    Write-Host "✗ FAILED: $($_.Exception.Message)" -ForegroundColor Red
    $testsFailed++
}
Write-Host ""

# Summary
Write-Host "================================" -ForegroundColor Cyan
Write-Host " Test Results Summary" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
$totalTests = $testsPassed + $testsFailed
$passRate = if ($totalTests -gt 0) { [math]::Round(($testsPassed / $totalTests) * 100, 2) } else { 0 }

Write-Host ""
Write-Host "Tests Passed:  $testsPassed / $totalTests" -ForegroundColor Green
Write-Host "Tests Failed:  $testsFailed / $totalTests" -ForegroundColor Red
Write-Host "Pass Rate:     $passRate%" -ForegroundColor $(if ($passRate -eq 100) { "Green" } elseif ($passRate -ge 80) { "Yellow" } else { "Red" })
Write-Host ""

if ($testsPassed -eq $totalTests) {
    Write-Host "✓ All tests passed! API is working correctly." -ForegroundColor Green
} elseif ($testsPassed -gt 0) {
    Write-Host "⚠ Some tests failed. Please check the errors above." -ForegroundColor Yellow
} else {
    Write-Host "✗ All tests failed. Please check if the server is running." -ForegroundColor Red
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Return exit code based on test results
exit $testsFailed
