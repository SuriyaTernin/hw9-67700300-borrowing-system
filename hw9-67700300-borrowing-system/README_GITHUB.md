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
📁 Borrowing Management System/
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
   git clone https://github.com/YOUR_USERNAME/borrowing-system.git
   cd borrowing-system
   ```

2. **🗄️ ตั้งค่าฐานข้อมูล**
   ```sql
   CREATE DATABASE borrowing;
   -- Import SQL files from Database/ folder
   ```

3. **⚙️ ตั้งค่าการเชื่อมต่อ**
   ```php
   // config.php
   <?php
   $host = 'localhost';
   $dbname = 'borrowing';
   $username = 'your_db_username';
   $password = 'your_db_password';
   
   try {
       $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch(PDOException $e) {
       die("Connection failed: " . $e->getMessage());
   }
   ?>
   ```

4. **📁 ตั้งค่าสิทธิ์โฟลเดอร์**
   ```bash
   chmod 755 Uploads/
   chmod 755 Uploads/profiles/
   ```

5. **🌐 เข้าถึงระบบ**
   ```
   http://localhost/borrowing/
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

## 🎨 UI/UX Showcase

### 🌟 Premium Design Elements
- **Glass Morphism** - พื้นหลังโปร่งแสง
- **Gradient Buttons** - ปุ่มไล่สีสวยงาม
- **Smooth Animations** - เคลื่อนไหวลื่นไหว
- **Responsive Grid** - รองรับทุกขนาด
- **Modern Shadows** - เงาสวยงาม

### 📱 Mobile Responsive
- **Touch Friendly** - เหมาะกับสัมผัส
- **Collapsible Sidebar** - เมนูย่อ-ขยาย
- **Mobile Header** - หัวข้อสำหรับมือถือ
- **Optimized Tables** - ตาราง responsive

## 🔄 การอัปเดตล่าสุด

### 🆕 v2.0 - Premium Update
- ✨ **Premium UI** - ออกแบบใหม่ทั้งหมด
- 🗑️ **Trash System** - ถังขยะ & คืนข้อมูล
- 📱 **QR Scanner** - ระบบสแกน QR
- 👤 **Profile Pictures** - รูปโปรไฟล์ผู้ใช้
- 📊 **Enhanced Reports** - รายงานดีขึ้น

### 🔧 v1.5 - Core Features
- ✅ ระบบจัดการพื้นฐาน
- ✅ ระบบอนุมัติคำร้อง
- ✅ รายงานพื้นฐาน

## 🤝 การมีส่วนร่วม

### 📋 วิธีมีส่วนร่วม
1. **Fork** Repository
2. **Create Branch** - `git checkout -b feature/AmazingFeature`
3. **Commit** - `git commit -m 'Add some AmazingFeature'`
4. **Push** - `git push origin feature/AmazingFeature`
5. **Pull Request**

### 📝 แนวทานการพัฒนา
- 🎨 รักษาความสวยงามของ UI
- 📱 ให้ความสำคัญกับ responsive
- 🔐 ให้ความสำคัญกับความปลอดภัย
- 📝 เขียนโค้ดที่อ่านง่าย
- 🧪 ทดสอบก่อน push

## 📄 License

นี่คือโปรเจคที่สร้างขึ้นสำหรับการศึกษาและพัฒนา

## 👨‍💻 ผู้พัฒนา

พัฒนาด้วย ❤️ สำหรับสถาบันการศึกษา

---

## 🌟 ขอบคุณ!

**ขอบคุณที่สนใจโปรเจคนี้!** 🙏

**🚀 พร้อมใช้งานแล้ว - ติดตั้งง่าย - ฟีเจอร์ครบถ้วน!**
