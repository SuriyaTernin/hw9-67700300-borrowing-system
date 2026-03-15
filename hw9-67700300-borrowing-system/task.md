# ระบบอนุมัติการยืมอุปกรณ์ - Task List

## ภาพรวมระบบ
ระบบการยืมอุปกรณ์แบบมีการอนุมัติจากผู้ดูแลระบบ (Admin) ก่อนที่ผู้ใช้สามารถนำอุปกรณ์ไปใช้งานได้จริง

## ขั้นตอนการทำงาน

### 1. ผู้ใช้ (User) ส่งคำร้องยืมอุปกรณ์
- เข้าสู่ระบบผ่านหน้า `user_dashboard.php`
- เลือกอุปกรณ์ที่ต้องการยืมและระบุจำนวน
- ระบุวันที่ต้องการยืม
- กด "ยืนยันการยืม" → สร้างคำร้องในฐานข้อมูล
- **สถานะ**: `pending` (รออนุมัติ)
- **สต็อก**: ยังไม่ถูกตัด (รอการอนุมัติก่อน)
- **แจ้งเตือน**: "ส่งคำร้องยืมอุปกรณ์เรียบร้อยแล้ว กรุณารอการอนุมัติจากผู้ดูแลระบบ"

### 2. ผู้ดูแลระบบ (Admin) ตรวจสอบและอนุมัติคำร้อง
- เข้าสู่ระบบผ่านหน้า `approval_dashboard.php`
- ดูรายการคำร้องที่รออนุมัติในส่วน "คำร้องที่รออนุมัติ"
- ตรวจสอบรายละเอียด:
  - ชื่ออุปกรณ์และรูปภาพ
  - ชื่อผู้ขอยืม
  - จำนวนที่ต้องการ
  - วันที่ต้องการยืม
  - เวลาที่ส่งคำร้อง

#### ตัวเลือกการดำเนินการ:
- **อนุมัติ (Approve)**:
  - ตรวจสอบว่ามีสต็อกเพียงพอ
  - อัปเดตสถานะเป็น `approved`
  - ตัดสต็อกอุปกรณ์ตามจำนวนที่อนุมัติ
  - บันทึกผู้อนุมัติและเวลาที่อนุมัติ
  - ผู้ใช้สามารถนำอุปกรณ์ไปใช้งานได้ทันที

- **ปฏิเสธ (Reject)**:
  - ระบุเหตุผลในการปฏิเสธ
  - อัปเดตสถานะเป็น `rejected`
  - บันทึกเหตุผลการปฏิเสธ
  - สต็อกยังคงเหมือนเดิม
  - ผู้ใช้ไม่สามารถนำอุปกรณ์ไปใช้ได้

### 3. ผู้ใช้ตรวจสอบสถานะคำร้อง
- ดูสถานะในหน้า `user_dashboard.php`:
  - **รออนุมัติ**: แสดงไอคอนนาฬิกา ⏳
  - **กำลังยืม**: แสดงไอคอนกล่อง 📦 (สามารถคืนได้)
  - **ถูกปฏิเสธ**: แสดงไอคอน X ❌
  - **คืนแล้ว**: แสดงไอคอนเช็ค ✅

### 4. การคืนอุปกรณ์
- เฉพาะคำร้องที่ได้รับการอนุมัติเท่านั้น (`approved`)
- ผู้ใช้กดปุ่มคืนอุปกรณ์
- อัปเดตสถานะเป็น `returned`
- คืนสต็อกอุปกรณ์กลับเข้าคลัง

## ฐานข้อมูล (Database Schema)

### ตาราง `borrowings` (เพิ่มฟิลด์ใหม่)
```sql
ALTER TABLE `borrowings` 
ADD COLUMN `approval_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER `status`,
ADD COLUMN `approved_by` INT(11) NULL AFTER `approval_status`,
ADD COLUMN `approved_at` TIMESTAMP NULL AFTER `approved_by`,
ADD COLUMN `rejection_reason` TEXT NULL AFTER `approved_at`;
```

### คำอธิบายฟิลด์ใหม่:
- `approval_status`: สถานะการอนุมัติ (pending/approved/rejected)
- `approved_by`: ID ของผู้ที่อนุมัติ (FK ไปที่ users.id)
- `approved_at`: เวลาที่อนุมัติ
- `rejection_reason`: เหตุผลในการปฏิเสธ

## ไฟล์ที่เกี่ยวข้อง

### 1. `user_dashboard.php`
- **หน้าหลักผู้ใช้**
- แสดงสถิติ: กำลังยืม, รออนุมัติ, คืนแล้ว
- ฟอร์มส่งคำร้องยืมอุปกรณ์
- แสดงประวัติการยืมพร้อมสถานะอนุมัติ
- ปุ่มคืนอุปกรณ์ (เฉพาะที่ได้รับการอนุมัติ)

### 2. `approval_dashboard.php`
- **หน้าหลักผู้ดูแลระบบ**
- แสดงคำร้องที่รออนุมัติ
- ปุ่มอนุมัติ/ปฏิเสธคำร้อง
- ประวัติคำร้องทั้งหมด
- ฟอร์มระบุเหตุผลการปฏิเสธ

### 3. `add_approval_columns.sql`
- **คำสั่ง SQL** สำหรับอัปเดตโครงสร้างฐานข้อมูล
- เพิ่มฟิลด์ที่จำเป็นสำหรับระบบอนุมัติ

## ข้อควรมควบคุมและความปลอดภัย

### สิทธิ์การเข้าถึง:
- **ผู้ใช้ทั่วไป**: สามารถดูและส่งคำร้องยืมได้เท่านั้น
- **ผู้ดูแลระบบ**: สามารถอนุมัติ/ปฏิเสธคำร้องได้

### การตรวจสอบ:
- ตรวจสอบจำนวนสต็อกก่อนอนุมัติ
- ป้องกันการยืมเกินจำนวนที่มีในคลัง
- บันทึกประวัติการอนุมัติทุกรายการ

### การแจ้งเตือน:
- แจ้งเตือนเมื่อส่งคำร้องสำเร็จ
- แจ้งเตือนเมื่อ Admin อนุมัติ/ปฏิเสธ
- แจ้งเตือนกรณีมีข้อผิดพลาด

## ประโยชน์ของระบบ

1. **ความปลอดภัย**: Admin สามารถตรวจสอบและควบคุมการยืมอุปกรณ์
2. **การจัดการสต็อก**: ป้องกันการยืมเกินความสามารถของระบบ
3. **การติดตาม**: มีประวัติการอนุมัติและเหตุผลการปฏิเสธครบถ้วน
4. **ความรับผิดชอบ**: ทราบได้ว่าใครเป็นผู้อนุมัติคำร้องแต่ละรายการ

## การติดตั้งและใช้งาน

1. รันคำสั่ง SQL ในไฟล์ `add_approval_columns.sql` เพื่ออัปเดตฐานข้อมูล
2. อัปโหลดไฟล์ที่แก้ไขไปยังเซิร์ฟเวอร์
3. ทดสอบระบบด้วยบัญชีผู้ใช้และแอดมิน
4. ตรวจสอบการทำงานของขั้นตอนอนุมัติ

## ข้อสังเกต
- คำร้องที่ยังไม่ได้รับการอนุมัติจะไม่สามารถนำอุปกรณ์ไปใช้ได้
- สต็อกจะถูกตัดเฉพาะเมื่อได้รับการอนุมัติแล้วเท่านั้น
- ระบบบันทึกประวัติการอนุมัติทุกรายการเพื่อการตรวจสอบย้อนหลัง

## โครงสร้างไฟล์

```
borrowing-system/
├── Database/
│   ├── db.sql                 # สคริปตารางฐานข้อมูลหลัก
│   ├── add_approval_columns.sql # คอลัมน์สำหรับระบบอนุมัติ
│   └── add_student_info.sql      # คอลัมน์ข้อมูลนักศึกษา
├── Uploads/
│   ├── equipment/             # รูปภาพอุปกรณ์
│   └── profiles/              # รูปโปรไฟล์ผู้ใช้
├── components/
│   ├── header.php
│   ├── sidebar.php
│   └── modals/
│       ├── confirm_modal.php
│       └── success_modal.php
├── pages/
│   ├── login.php
│   ├── logout.php
│   ├── admin_dashboard.php
│   ├── user_dashboard.php
│   ├── approval_dashboard.php
│   ├── equipment.php
│   ├── admins.php
│   ├── user_history.php
│   ├── borrowing_dashboard.php
│   └── profile.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config.php
└── user_manual.md
```

## การทดสอบและทดสอบ

### 1. Unit Testing
```php
// tests/UserTest.php
class UserTest extends PHPUnit\Framework\TestCase {
    public function testUserLogin() {
        // Test login functionality
    }
    
    public function testBorrowEquipment() {
        // Test borrowing functionality
    }
    
    public function testReturnEquipment() {
        // Test returning functionality
    }
}
```

### 2. Integration Testing
```php
// tests/IntegrationTest.php
class IntegrationTest extends PHPUnit\Framework\TestCase {
    public function testCompleteBorrowingWorkflow() {
        // Test complete borrowing workflow
    }
    
    public function testApprovalWorkflow() {
        // Test approval workflow
    }
}
```

## การ Deploy

### Docker Configuration
```dockerfile
FROM php:8.0-apache
COPY . /var/www/html/
RUN docker-php-ext-install pdo_mysql
RUN a2enmod -x +x /var/www/html/Uploads/
```

### Apache Configuration
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/borrowing
    ServerName borrowing.local
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## การปรับปรุงและพัฒนา

### 1. การเพิ่มฟีเจอร์ใหม่
- สร้าง Migration Scripts
- ใช้ Database Version Control (Git)
- ทำการ Rollback ถ้ามีปัญหา

### 2. การปรับปรุงขนาน
- ใช้ Object Relational Mapping (ORM)
- ใช้ Caching (Redis/Memcached)
- ใช้ Message Queue (RabbitMQ/Redis)

### 3. การปรับปรุง UI/UX
- ใช้ Modern CSS Framework (Tailwind CSS, Bootstrap)
- ใช้ JavaScript Framework (React, Vue.js)
- ใช้ PWA (Progressive Web App)

---

**แผนที่งานนี้เป็นเพียงแระบบที่สมบูรณม์สำหรับการพัฒนาต่อไปของระบบยืมอุปกรณ์** 🚀

**เป้านที่เป็นได้:**
- ✅ ระบบ Login/Logout ที่ปลอดภัยและปลอดภัย
- ✅ ระบบจัดการอุปกรณ์ CRUD
- ✅ ระบบยืม-คืนพร้อมระบบอนุมัติ
- ✅ ระบบโปรไฟล์ผู้ใช้
- ✅ ระบบสถิติและรายงานกราฟอร์
- ✅ ระบบที่รองรับทุกรณอย่างง่าย

**เป้านที่ต้องพัฒนา:**
- 📝 ระบบรายงานกราฟอร์
- 📊 ระบบการจัดการข้อมูลขนาน
- 📱 ระบบรายงานกราฟอร์
- 🔐 ระบบความปลอดภัย
- 📱 ระบบการแจ้งเตือน
- 🔔 ระบบการ Export ข้อมูล

**เทคโนโลยีที่ใช้:**
- PHP 8+ พร้อม PDO
- MySQL 8.0+
- Tailwind CSS
- jQuery & jQuery Confirm
- Bootstrap Icons
- Animate.css
- Highcharts (สำหรับสถิติ)

**Architectural Pattern:**
- MVC Pattern
- Repository Pattern
- Service Layer
- Dependency Injection
