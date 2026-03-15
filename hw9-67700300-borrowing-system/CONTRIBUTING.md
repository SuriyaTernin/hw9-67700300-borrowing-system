# แผนที่งาน: การสร้างระบบยืม-คืนอุปกรณ์

## ภาพรวมโครงสร้าง

นี่คือแผนที่งานสำหรับการสร้างระบบยืม-คืนอุปกรณ์ แบ่งขึ้นด้วย PHP 8, MySQL, Tailwind CSS และ jQuery โดยใช้วัตถุประสงค์ค์ พัฒนา

## วัตถุประสงค์

1. **ระบบ Login/Logout** - ระบบยืนออกจากระบบ
2. **ระบบจัดการผู้ใช้** - Admin สามารถจัดการผู้ใช้ทั้งหมด
3. **ระบบจัดการอุปกรณ์** - Admin สามารถเพิ่ม/แก้ไข/ลบอุปกรณ์
4. **ระบบยืมอุปกรณ์** - User สามารถเลือกอุปกรณ์และส่งคำร้องยืม
5. **ระบบอนุมัติ** - Admin สามารถอนุมัติหรือปฏิเสธคำร้องยืม
6. **ระบบคืนอุปกรณ์** - User สามารถคืนอุปกรณ์หลังจากที่ยืม
7. **ระบบสถิติ** - ดูสถิติการใช้งานทั้งหมด
8. **ระบบโปรไฟล์** - User สามารถจัดการข้อมูลส่วนตัว

## Phase 1: พื้นฐาน (Foundation)

### 1.1 ออกแบบตารางฐานข้อมูล
```sql
-- สร้างฐานข้อมูล
CREATE DATABASE equipment_borrowing;

-- สร้างตารางผู้ใช้
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- สร้างตารางหมวดหมู่
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- สร้างตารางอุปกรณ์
CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    quantity INT DEFAULT 0,
    category_id INT NOT NULL,
    image VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- สร้างตารางการยืม
CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT NOT NULL,
    quantity INT NOT NULL,
    borrow_date DATE NOT NULL,
    return_date DATE,
    status ENUM('borrowed', 'returned') DEFAULT 'borrowed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id)
);
```

### 1.2 สร้างไฟล์พื้นฐาน
```php
// config.php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'equipment_borrowing');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

### 1.3 สร้างระบบ Login
```php
// login.php
<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: user_dashboard.php');
        }
        exit;
    }
}
?>
```

## Phase 2: พัฒนาฟังก์ชัน

### 2.1 สร้าง Sidebar Navigation
```php
// sidebar.php (Admin)
<nav class="flex-1 px-4 py-6 space-y-2">
    <a href="admin_dashboard.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white/10">
        <i class="bi bi-grid"></i>
        <span>แดชบอร์ด</span>
    </a>
    <a href="equipment.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-white/10">
        <i class="bi bi-box"></i>
        <span>จัดการอุปกรณ์</span>
    </a>
    <!-- เพิ่มเมนูอื่นๆ -->
</nav>
```

### 2.2 สร้าง Dashboard Layout
```php
// admin_dashboard.php
<main class="flex-1 flex flex-col bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
    </header>
    
    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6">
        <!-- Dashboard Content -->
    </div>
</main>
```

### 2.3 สร้าง Component Structure
```
components/
├── header.php
├── sidebar.php
├── footer.php
├── modals/
│   ├── confirm_modal.php
│   └── success_modal.php
├── cards/
│   ├── stats_card.php
│   ├── table_card.php
│   └── form_card.php
└── helpers/
    ├── database.php
    ├── session.php
    └── validation.php
```

## Phase 3: พัฒนาฟีเจอร์ CRUD

### 3.1 จัดการอุปกรณ์ (CRUD)
```php
// equipment.php - Create
if (isset($_POST['add_equipment'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['category_id'];
    
    $stmt = $pdo->prepare("INSERT INTO equipment (name, description, quantity, category_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $quantity, $category_id]);
}

// equipment.php - Read
$equipment = $pdo->query("SELECT e.*, c.name as category_name FROM equipment e JOIN categories c ON e.category_id = c.id");

// equipment.php - Update
if (isset($_POST['update_equipment'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    
    $stmt = $pdo->prepare("UPDATE equipment SET name = ?, description = ?, quantity = ?, category_id = ? WHERE id = ?");
    $stmt->execute([$name, $description, $quantity, $category_id, $id]);
}

// equipment.php - Delete
if (isset($_POST['delete_equipment'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
}
```

### 3.2 จัดการผู้ใช้ (CRUD)
```php
// admins.php - Create
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $role]);
}

// admins.php - Read
$stmt = $pdo->query("SELECT id, username, role, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");

// admins.php - Update
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    
    $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
    $stmt->execute([$username, $role, $id]);
}

// admins.php - Delete
if (isset($_POST['delete_user'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}
```

## Phase 4: พัฒนาฟีเจอร์การยืม-คืน

### 4.1 การส่งคำร้องยืม
```php
// user_dashboard.php - ส่งคำร้อง
if (isset($_POST['borrow_equipment'])) {
    $items = $_POST['items'] ?? [];
    $borrow_date = $_POST['borrow_date'];
    
    $pdo->beginTransaction();
    try {
        foreach ($items as $item) {
            $equipment_id = $item['equipment_id'];
            $quantity = $item['quantity'];
            
            // ตรวจสอบจำนวนที่คงเหลือ
            $stmt = $pdo->prepare("SELECT quantity FROM equipment WHERE id = ?");
            $stmt->execute([$equipment_id]);
            $available = $stmt->fetchColumn();
            
            if ($quantity > $available) {
                throw new Exception("สินค้าไม่พอ");
            }
            
            // บันทึกข้อมูลการยืม
            $stmt = $pdo->prepare("INSERT INTO borrowings (user_id, equipment_id, quantity, borrow_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
            $stmt->execute([$_SESSION['user_id'], $equipment_id, $quantity, $borrow_date]);
            
            // ตัดสต็อกอุปกรณ์
            $stmt = $pdo->prepare("UPDATE equipment SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $equipment_id]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}
```

### 4.2 การคืนอุปกรณ์
```php
// user_dashboard.php - คืนอุปกรณ์
if (isset($_GET['return_borrowing'])) {
    $borrowing_id = $_GET['return_borrowing'];
    
    $pdo->beginTransaction();
    try {
        // อัปเดตสถานะเป็น 'returned'
        $stmt = $pdo->prepare("UPDATE borrowings SET status = 'returned', return_date = CURDATE() WHERE id = ? AND user_id = ?");
        $stmt->execute([$borrowing_id, $_SESSION['user_id']]);
        
        // คืนสต็อกอุปกรณ์
        $stmt = $pdo->prepare("SELECT equipment_id, quantity FROM borrowings WHERE id = ?");
        $stmt->execute([$borrowing_id]);
        $borrowing = $stmt->fetch();
        
        if ($borrowing) {
            $stmt = $pdo->prepare("UPDATE equipment SET quantity = quantity + ? WHERE id = ?");
            $stmt->execute([$borrowing['quantity'], $borrowing['equipment_id']]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}
```

## Phase 5: พัฒนาฟีเจอร์อนุมัติ

### 5.1 อัปเดตตาราง borrowings
```sql
ALTER TABLE borrowings 
ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' AFTER status,
ADD COLUMN approved_by INT(11) NULL AFTER quantity,
ADD COLUMN approved_at TIMESTAMP NULL AFTER approval_status,
ADD COLUMN rejection_reason TEXT NULL AFTER approved_at;
```

### 5.2 การอนุมัติคำร้อง
```php
// approval_dashboard.php
if (isset($_POST['action'])) {
    $borrowing_id = $_POST['borrowing_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("SELECT equipment_id, quantity FROM borrowings WHERE id = ?");
        $stmt->execute([$borrowing_id]);
        $borrowing = $stmt->fetch();
        
        // อนุมัติและตัดสต็อก
        $stmt = $pdo->prepare("UPDATE borrowings SET approval_status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $borrowing_id]);
        
        // ตัดสต็อกอุปกรณ์
        $stmt = $pdo->prepare("UPDATE equipment SET quantity = quantity - ? WHERE id = ?");
        $stmt->execute([$borrowing['quantity'], $borrowing['equipment_id']]);
        
    } elseif ($action === 'reject') {
        $rejection_reason = $_POST['rejection_reason'];
        $stmt = $pdo->prepare("UPDATE borrowings SET approval_status = 'rejected', rejection_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->execute([$rejection_reason, $_SESSION['user_id'], $borrowing_id]);
    }
}
```

## Phase 6: พัฒนาฟีเจอร์โปรไฟล์

### 6.1 เพิ่มฟิลด์ข้อมูลนักศึกษา
```sql
ALTER TABLE users 
ADD COLUMN student_id VARCHAR(20) NULL AFTER username,
ADD COLUMN first_name VARCHAR(100) NULL AFTER student_id,
ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name,
ADD COLUMN profile_image VARCHAR(255) NULL DEFAULT 'default.jpg' AFTER last_name;
```

### 6.2 การอัปโหลดรูปภาพ
```php
// profile.php - อัปโหลดรูปภาพ
if (isset($_POST['upload_image'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_info = pathinfo($_FILES['profile_image']['name']);
        $file_extension = strtolower($file_info['extension']);
        
        if (in_array($file_extension, $allowed_types)) {
            $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = 'Uploads/profiles/' . $new_filename;
            
            if (!is_dir('Uploads/profiles')) {
                mkdir('Uploads/profiles', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->execute([$new_filename, $_SESSION['user_id']]);
            }
        }
    }
}
```

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
