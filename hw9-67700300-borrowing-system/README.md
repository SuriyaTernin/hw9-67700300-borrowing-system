# 🎓 Borrowing Management System

> 🌟 **ระบบจัดการการยืม-คืนอุปกรณ์สำหรับสถาบันการศึกษา**  
> 🚀 **Premium UI Design พร้อมฟีเจอร์ครบถ้วน**

## ✨ คุณสมบัติเด่น

### 🎯 หลัก (Core Features)
- 👥 **จัดการผู้ใช้** - ระบบสมาชิกพร้อมรูปโปรไฟล์
- 📦 **จัดการอุปกรณ์** - ครบจบครบถ้วน
- 📋 **จัดการหมวดหมู่** - แยกประเภทอุปกรณ์
- 🔄 **จัดการการยืม-คืน** - **🆕 ครบถ้วนที่สุด**
- 📊 **รายงาน** - สถิติและการส่งออก

### 🚀 ฟีเจอร์พิเศษ
- 📱 **QR Code Scanner** - ระบบรับอุปกรณ์ด้วย QR
- ✅ **ระบบอนุมัติ** - Workflow การอนุมัติคำร้อง
- 🗑️ **ถังขยะ & คืนข้อมูล** - **🆕 คืนข้อมูลที่ลบได้**
- 📅 **ปฏิทิน** - แสดงการยืมในรูปแบบปฏิทิน
- 📈 **สถิติ Real-time** - อัปเดตข้อมูลทันที

### 🎨 UI/UX พรีเมียม
- 🌟 **Glass Effect** - พื้นหลังโปร่งแสง
- 🎨 **Gradient Design** - ไล่สีสวยงาม
- 📱 **Responsive** - รองรับทุกอุปกรณ์
- ⚡ **Animations** - เคลื่อนไหวลื่นไหล
- 🌙 **Dark Mode Ready** - รองรับโหมดมืด

## 🏗️ สถาปัตยกรรมระบบ

```
� Borrowing Management System/
├── 🎯 Core Application/
│   ├── admin_dashboard.php      # แดชบอร์ด admin
│   ├── manage_borrowings.php   # 🆕 จัดการการยืม-คืน
│   ├── approval_dashboard.php  # อนุมัติคำร้อง
│   ├── equipment.php           # จัดการอุปกรณ์
│   ├── admins.php              # จัดการสมาชิก
│   └── report_borrowing.php    # รายงาน
├── 👤 User Interface/
│   ├── user_dashboard.php      # แดชบอร์ดผู้ใช้
│   ├── profile.php             # โปรไฟล์ผู้ใช้
│   └── sidebar_user.php        # เมนูผู้ใช้
├── 📱 Advanced Features/
│   ├── qr_scan.php             # สแกน QR Code
│   ├── qr_pickup.php           # QR สำหรับรับ
│   └── calendar.php            # ปฏิทิน
├── 🗃️ Database/
│   ├── create_deleted_borrowings_table_fixed.sql
│   ├── add_approval_columns.sql
│   └── add_pickup_columns.sql
└── 📚 Documentation/
    ├── README.md               # 📖 เอกสารนี้
    ├── user_manual.md          # คู่มือผู้ใช้
    └── CONTRIBUTING.md         # แนวทางการมีส่วนร่วม
```

## 🛠️ เทคโนโลยีที่ใช้

### 🎨 Frontend
- **Tailwind CSS** - Modern CSS Framework
- **Bootstrap Icons** - Icon Library
- **Animate.css** - Animation Library
- **Chart.js** - Data Visualization
- **FullCalendar** - Calendar Component
- **SweetAlert2** - Beautiful Alerts
- **jQuery** - JavaScript Library

### 📱 QR Code & Scanner
- **html5-qrcode** - QR Code Scanner
- **Custom QR Generation** - PHP QR Code

### 🗄️ Backend
- **PHP 8+** - Server-side Language
- **MySQL/MariaDB** - Database
- **PDO** - Secure Database Access
- **Sessions** - User Authentication

## 🚀 การติดตั้ง

### 📋 ข้อกำหนดเบื้องต้น
- PHP 8.0 ขึ้นไป
- MySQL/MariaDB 5.7 ขึ้นไป
- Web Server (Apache/Nginx)
- GD Library (สำหรับรูปภาพ)

### 🛠️ ขั้นตอนการติดตั้ง

1. **📥 Clone Repository**
   ```bash
   git clone https://github.com/sirmommam/borrowing-system.git
   cd borrowing-system
   ```

2. **🗄️ ตั้งค่าฐานข้อมูล**
   ```sql
   CREATE DATABASE borrowings_db;
   -- Import SQL files from Database/ folder
   ```

3. **⚙️ ตั้งค่าการเชื่อมต่อ**
   (ระบบใช้ Docker Compose สำหรับรันเซิร์ฟเวอร์และฐานข้อมูล)

4. **🌐 เข้าถึงระบบ**
   ```
   http://localhost:8080/ (หรือพอร์ตที่ตั้งค่าไว้)
   ```

## 👤 บทบาทผู้ใช้

### 🔑 Admin
- 📊 จัดการทุกอย่าง
- ✅ อนุมัติคำร้อง
- 📈 ดูรายงาน
- 👥 จัดการผู้ใช้
- 🗑️ ถังขยะ & คืนข้อมูล

### 👤 Member
- 📦 ยืมอุปกรณ์
- 📋 ดูประวัติ
- 👤 จัดการโปรไฟล์
- 📱 สแกน QR รับอุปกรณ์

## 🎯 ฟีเจอร์หลัก

### 📋 จัดการการยืม-คืน (🆕)
- ✏️ **แก้ไขข้อมูล** - แก้ไขวันที่ สถานะ
- 🔄 **คืนอุปกรณ์** - คืนอัตโนมัติ
- 🗑️ **ลบคำร้อง** - ลบข้อมูลที่ไม่อนุมัติ
- 📦 **ถังขยะ** - เก็บข้อมูลที่ถูกลบ
- ♻️ **คืนข้อมูล** - คืนข้อมูลกลับมาได้

### 📱 QR Code System
- 📸 **Generate QR** - สร้าง QR สำหรับรับ
- 📷 **QR Scanner** - สแกน QR ยืนยันรับ
- ⏰ **Time-based** - QR หมดอายุ 5 นาที
- 🔐 **Security** - Checksum validation

### 📊 รายงาน & สถิติ
- 📈 **Real-time** - อัปเดตทันที
- 📊 **Statistics** - สถิติทั้งหมด
- 📤 **Export CSV** - ส่งออกข้อมูล
- 📅 **Date Range** - กรองตามวันที่

## 👨‍💻 ผู้พัฒนา

พัฒนาด้วย ❤️ สำหรับสถาบันการศึกษา

---

**🚀 พร้อมใช้งานแล้ว - ติดตั้งง่าย - ฟีเจอร์ครบถ้วน!**
