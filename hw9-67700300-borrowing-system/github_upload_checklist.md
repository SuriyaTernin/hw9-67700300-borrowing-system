# Borrowing System - Files for GitHub Upload

## 📁 Files to Exclude (DO NOT UPLOAD)

### 🔒 Sensitive Files
- `config.php` - Contains database credentials
- `.htaccess` - Server configuration
- `Uploads/` - User uploaded files (images, profiles)

### 🗑️ Old/Unused Files
- `admins_old.php` - Old version
- `categorie_old.php` - Old version
- `equipment_old.php` - Old version
- `report_borrowing_old.php` - Old version
- `borrowing_dashboard.php` - Duplicate
- `create_user.php` - Empty file
- `user_history.php` - Duplicate functionality

### 🗃️ Database Files (Keep SQL only)
- `Database/127_0_0_1 (3).sql` - Local backup
- `Database/db.sql` - Local backup

## 📁 Files to Upload

### 🎯 Core Application Files
- ✅ `login.php` - Login page
- ✅ `logout.php` - Logout function
- ✅ `admin_dashboard.php` - Admin dashboard
- ✅ `approval_dashboard.php` - Approval system
- ✅ `manage_borrowings.php` - **NEW** - Borrowing management
- ✅ `equipment.php` - Equipment management
- ✅ `categorie.php` - Category management
- ✅ `admins.php` - User management
- ✅ `report_borrowing.php` - Reports
- ✅ `user_dashboard.php` - User dashboard
- ✅ `profile.php` - User profile
- ✅ `calendar.php` - Calendar view

### 📱 User Interface Files
- ✅ `sidebar.php` - Admin sidebar
- ✅ `sidebar_user.php` - User sidebar
- ✅ `qr_scan.php` - QR code scanner
- ✅ `qr_pickup.php` - QR code pickup

### 📄 Documentation Files
- ✅ `README.md` - Project documentation
- ✅ `CONTRIBUTING.md` - Contributing guidelines
- ✅ `user_manual.md` - User manual
- ✅ `task.md` - Project tasks
- ✅ `borrowing_system_processes.md` - System processes

### 🗃️ Database Schema Files
- ✅ `Database/add_approval_columns.sql` - Approval system
- ✅ `Database/add_pickup_columns.sql` - QR pickup system
- ✅ `Database/add_student_info.sql` - User info
- ✅ `Database/create_deleted_borrowings_table_fixed.sql` - **NEW** - Trash system
- ✅ `Database/architecture.md` - Database architecture

### 🛠️ Utility Files
- ✅ `fetch_borrowings.php` - AJAX endpoint

## 📋 .gitignore File

Create `.gitignore` file with:

```
# Database Configuration
config.php

# Server Configuration
.htaccess

# Upload Directories
Uploads/
Uploads/profiles/

# User Generated Files
*.log
*.tmp

# IDE Files
.vscode/
.idea/

# Local Database Files
Database/127_0_0_1*.sql
Database/db.sql

# Old Files
*_old.php
borrowing_dashboard.php
create_user.php
user_history.php

# Empty Files
create_user.php
```

## 🚀 GitHub Upload Steps

### 1. Create Repository
```bash
git init
git add .
git commit -m "Initial commit - Borrowing Management System"
git branch -M main
git remote add origin https://github.com/username/borrowing-system.git
git push -u origin main
```

### 2. Create README.md Update
```markdown
# Borrowing Management System

## 🌟 Features
- ✅ User Management with Profile Pictures
- ✅ Equipment Management
- ✅ Borrowing/Returning System
- ✅ QR Code Scanner for Pickup
- ✅ Approval Workflow
- ✅ **NEW** - Trash & Restore System
- ✅ Reports & Statistics
- ✅ Premium UI/UX Design

## 📁 Structure
```
Borrowing/
├── 📄 Core PHP Files
├── 🗃️ Database/ (SQL schemas)
├── 📚 Documentation/
└── 🖼️ Uploads/ (excluded)
```

## 🛠️ Installation
1. Clone repository
2. Create `config.php` with database settings
3. Import SQL files from `Database/` folder
4. Set up `Uploads/` directory with proper permissions
```

## 📊 Project Statistics
- **Total Files:** ~30 core files
- **New Features:** Trash system, Premium UI, QR scanning
- **Documentation:** Complete user manual & API docs
- **Database:** Optimized with indexes & foreign keys
```

## 🔍 Final Check

### ✅ Ready for GitHub:
- ✅ All sensitive data excluded
- ✅ Clean file structure
- ✅ Complete documentation
- ✅ Latest features included
- ✅ No old/duplicate files
- ✅ Proper .gitignore created

### 🎯 Key Features to Highlight:
1. **Premium UI Design** - Modern glass effects & animations
2. **Complete CRUD Operations** - Full management capabilities
3. **QR Code Integration** - Modern pickup system
4. **Trash & Restore** - Data recovery system
5. **Responsive Design** - Mobile-friendly interface
6. **Role-based Access** - Admin/User permissions
7. **Real-time Statistics** - Live data updates
8. **Comprehensive Reports** - Export capabilities

---

**🎉 พร้อมอัปโหลดขึ้น GitHub แล้ว!**

**ขั้นตอนถัดไป:**
1. สร้าง `.gitignore` ตามรายการข้างบน
2. สร้าง repository บน GitHub
3. อัปโหลดไฟล์ที่เลือก
4. เขียน README.md ที่สวยงาม

**โปรเจคของคุณพร้อมแชร์กับโลก! 🚀✨**
