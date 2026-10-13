# e-Scholar Dashboard - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Prerequisites Check
- ✅ Docker containers running (MySQL + phpMyAdmin)
- ✅ Symfony project set up
- ✅ PHP 8.1+ installed
- ✅ Composer installed

---

## Step 1: Verify Database (Already Done ✅)

The database schema and sample data have already been inserted:
- ✅ 5 tables created (campuses, students, student_addresses, student_family, liquidations)
- ✅ 7 campuses populated
- ✅ 20 sample students inserted
- ✅ 19 liquidation records added

**Verify in phpMyAdmin**: http://localhost:8082

---

## Step 2: Update Symfony to Use New Dashboard

### Option A: Replace Current Dashboard (Recommended)

```bash
# Backup old dashboard
cp templates/admin/dashboard.html.twig templates/admin/dashboard_backup.html.twig

# Replace with new dashboard
cp templates/admin/dashboard_new.html.twig templates/admin/dashboard.html.twig
```

### Option B: Update Controller

Edit `src/Controller/AdminController.php`:

```php
return $this->render('admin/dashboard_new.html.twig', [
    // ... existing parameters
]);
```

---

## Step 3: Clear Symfony Cache

```bash
php bin/console cache:clear
```

---

## Step 4: Start Symfony Server

### Using Symfony CLI (Recommended):
```bash
symfony serve
```

### Or using PHP built-in server:
```bash
php -S localhost:8000 -t public
```

---

## Step 5: Access the Dashboard

1. **Open your browser**: http://localhost:8000/admin/login
2. **Log in** with your admin credentials
3. **View the dashboard**: You should see:
   - 20 total students
   - Campus distribution across 7 campuses
   - 19 liquidations (3 overdue, 10 pending)
   - Interactive charts
   - Upcoming deadlines table

---

## Step 6: Test the API (Optional)

### Quick API Test:

**In PowerShell**:
```powershell
# Test overview endpoint
Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/overview" -Method Get | ConvertTo-Json -Depth 5

# Run full test suite
.\test-api.ps1
```

**In Browser**:
- Overview: http://localhost:8000/api/dashboard/overview
- Campus Stats: http://localhost:8000/api/dashboard/campus-stats
- Overdue: http://localhost:8000/api/dashboard/liquidations/overdue

---

## 📊 What You Should See

### Dashboard Metrics:
- **Total Students**: 20
- **Pending Liquidations**: 10
- **Overdue Liquidations**: 3 (🔴 Red alert)
- **Due Within 7 Days**: 2 (🟡 Yellow warning)
- **Approved**: 3
- **Active Campuses**: 7

### Campus Distribution:
- Main Campus 1: 3 students
- Main Campus 2: 3 students
- Guihulngan: 3 students
- Bais: 2 students
- Pamplona: 3 students
- Mabinay: 2 students
- Bayawan: 4 students

### Liquidation Status:
- 3 Overdue (past deadline)
- 2 Due Soon (within 3 days)
- 8 Upcoming (within 30 days)
- 3 Submitted (awaiting approval)
- 3 Approved (completed)

---

## 🎨 Dashboard Features

### Metric Cards
Six color-coded cards showing key statistics with icons

### Campus Distribution
Visual cards for each campus with student counts

### Interactive Charts
- Scholarship Distribution (Pie Chart)
- Registration Status (Bar Chart)

### Liquidation Tracker
Table showing:
- Next 30 days of deadlines
- Color-coded status (Green/Yellow/Red)
- Student information
- Days until/overdue

### Overdue Alert Section
Special red-bordered section for urgent items

---

## 🔧 Common Issues & Solutions

### Issue: 404 Not Found on API
**Solution**: Make sure Symfony server is running
```bash
symfony serve
# or
php -S localhost:8000 -t public
```

### Issue: Dashboard shows no data
**Solution**: Verify sample data is inserted
```bash
docker exec scholarship-mysql-1 mysql -uroot -pscholarship_root_password scholarship_db -e "SELECT COUNT(*) FROM students;"
```
Should return: 20

### Issue: Charts not displaying
**Solution**: Check browser console for errors. Ensure internet connection for CDN resources (Chart.js, Font Awesome)

### Issue: Admin login not working
**Solution**: Create admin user if needed
```bash
php bin/console app:create-admin
```

### Issue: Database connection error
**Solution**: Check `.env` file:
```env
DATABASE_URL="mysql://scholarship_user:scholarship_password@127.0.0.1:3307/scholarship_db?serverVersion=8.0.44&charset=utf8mb4"
```

---

## 📱 Mobile Access

The dashboard is fully responsive:
- Desktop (>1200px): Multi-column layout
- Tablet (768-1200px): 2-column layout  
- Mobile (<768px): Single column, horizontal scroll for tables

Test on mobile by accessing: http://localhost:8000/admin/dashboard

---

## 🎯 Next Actions

### Add Real Data:
1. Register students through the registration form
2. Add liquidation deadlines for each student
3. Monitor deadlines and approve submissions

### Customize:
1. Change colors in `dashboard_new.html.twig`
2. Adjust deadline thresholds in `Liquidation` entity
3. Add more metrics as needed

### Integrate:
1. Set up email notifications for overdue items
2. Add document upload for liquidations
3. Create student portal for submissions

---

## 📚 Documentation Reference

- **Full Implementation Guide**: `DASHBOARD_IMPLEMENTATION_GUIDE.md`
- **Database Setup**: `DATABASE_SETUP_GUIDE.md`
- **API Documentation**: `API_TESTING.md`
- **Project README**: `README_ESCHOLAR.md`

---

## 🎓 Sample Data Details

### Students by Scholarship Type:
- TES: 7 students
- CHED-UNIFAST: 5 students
- TDP: 3 students
- SM Foundation: 3 students
- Landbank Scholarship: 2 students

### Students by Campus:
- All 7 campuses have students
- Even distribution for testing
- Complete contact information
- Family details included

### Liquidations:
- Various deadlines (past, current, future)
- Different statuses for testing
- Amounts range from ₱10,000 to ₱20,000

---

## ✅ Success Checklist

- [ ] Database tables created
- [ ] Sample data inserted
- [ ] Symfony server running
- [ ] Dashboard accessible at /admin/dashboard
- [ ] All 6 metric cards showing data
- [ ] Campus distribution displaying
- [ ] Charts rendering correctly
- [ ] Liquidation table populated
- [ ] Overdue section showing 3 items
- [ ] API endpoints responding (optional)

---

## 🆘 Need Help?

1. **Check Docker**: `docker ps` - Ensure MySQL is running
2. **Check Database**: Login to phpMyAdmin at http://localhost:8082
3. **Check Logs**: `tail -f var/log/dev.log`
4. **Clear Cache**: `php bin/console cache:clear`
5. **Restart Server**: Stop and start Symfony server

---

## 🎉 You're All Set!

Your e-Scholar Admin Dashboard is now ready to use. You have:
- ✅ Complete database with sample data
- ✅ Functional dashboard with analytics
- ✅ Liquidation tracking system
- ✅ RESTful API endpoints
- ✅ Responsive UI design
- ✅ Comprehensive documentation

**Start monitoring scholarships and liquidations today!**

---

**Questions?** Check the documentation files or review the code comments.

**Last Updated**: October 13, 2026  
**Version**: 1.0  
**Status**: Production Ready ✅
