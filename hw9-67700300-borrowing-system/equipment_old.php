<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login');
    exit;
}

$alertScript = '';

// --- LOGIC: ADVANCED AJAX SEARCH & FILTER ---
if (isset($_GET['ajax'])) {
    $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%%";
    $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : "";
    $status = isset($_GET['status']) ? $_GET['status'] : "";

    // สร้าง Query พื้นฐาน
    $sql = "SELECT e.*, c.name as category_name 
            FROM equipment e 
            LEFT JOIN categories c ON e.category_id = c.id 
            WHERE (e.name LIKE :search OR e.description LIKE :search)";
    
    // กรองตามหมวดหมู่
    if ($category_id != "") {
        $sql .= " AND e.category_id = :category_id";
    }

    // กรองตามสถานะ (เช็กจาก Quantity)
    if ($status == "available") {
        $sql .= " AND e.quantity > 0";
    } elseif ($status == "out_of_stock") {
        $sql .= " AND e.quantity <= 0";
    }

    $sql .= " ORDER BY e.id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', $search);
    if ($category_id != "") $stmt->bindValue(':category_id', $category_id);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($results) > 0) {
        foreach ($results as $item) {
            $statusBadge = ($item['quantity'] > 0) 
                ? "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'><i class='bi bi-check-circle-fill mr-1'></i> พร้อมใช้งาน ({$item['quantity']})</span>" 
                : "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'><i class='bi bi-exclamation-triangle-fill mr-1'></i> ของหมด</span>";
            
            echo "
            <tr class='hover:bg-gray-50/50 transition-colors'>
                <td class='px-6 py-4'>
                    <div class='flex items-center'>
                        <div class='flex-shrink-0 h-12 w-12 border rounded-lg overflow-hidden bg-gray-100 shadow-sm'>
                            <img src='Uploads/".($item['image'] ?: 'default.jpg')."' class='h-full w-full object-cover'>
                        </div>
                        <div class='ml-4'>
                            <div class='text-sm font-bold text-gray-900'>".htmlspecialchars($item['name'])."</div>
                            <div class='text-xs text-gray-500'>ID: #".str_pad($item['id'], 5, '0', STR_PAD_LEFT)."</div>
                        </div>
                    </div>
                </td>
                <td class='px-6 py-4 text-center text-sm text-gray-600'>".htmlspecialchars($item['category_name'])."</td>
                <td class='px-6 py-4 text-center'>$statusBadge</td>
                <td class='px-6 py-4 text-right text-sm font-medium'>
                    <button onclick=\"toggleModal('editEquipModal{$item['id']}')\" class='text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-lg mr-1 transition-all'>
                        <i class='bi bi-pencil-square'></i>
                    </button>
                    <button onclick=\"confirmDelete({$item['id']})\" class='text-red-600 hover:text-red-900 bg-red-50 p-2 rounded-lg transition-all'>
                        <i class='bi bi-trash'></i>
                    </button>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='4' class='px-6 py-12 text-center'><div class='text-gray-400'><i class='bi bi-inbox text-5xl mb-2 block'></i> ไม่พบข้อมูลอุปกรณ์ที่ต้องการ</div></td></tr>";
    }
    exit;
}

// --- LOGIC: ADD EQUIPMENT ---
// --- LOGIC: ADD EQUIPMENT (ปรับปรุงใหม่ให้ตรงกับ DB ของคุณ) ---
if (isset($_POST['add_equipment'])) {
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $quantity = $_POST['quantity'];
    $description = ""; // เพิ่มตัวแปรเผื่อไว้สำหรับคอลัมน์ description ใน DB

    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '.' . $extension;
        move_uploaded_file($_FILES['image']['tmp_name'], 'Uploads/' . $image_name);
    }

    // แก้ไข: ตัด 'status' ออกจากคำสั่ง SQL เพราะในตารางไม่มีคอลัมน์นี้
    $stmt = $pdo->prepare("INSERT INTO equipment (name, category_id, quantity, image, description) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $category_id, $quantity, $image_name, $description])) {
        $alertScript = "Swal.fire('สำเร็จ', 'เพิ่มอุปกรณ์เรียบร้อยแล้ว', 'success');";
    }
}
// --- LOGIC: UPDATE EQUIPMENT ---
if (isset($_POST['update_equipment'])) {
    $id = $_POST['equipment_id'];
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $quantity = $_POST['quantity'];
    $description = trim($_POST['description']);

    // จัดการรูปภาพ (ถ้ามีการอัปโหลดใหม่)
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '.' . $extension;
        move_uploaded_file($_FILES['image']['tmp_name'], 'Uploads/' . $image_name);
        
        $stmt = $pdo->prepare("UPDATE equipment SET name = ?, category_id = ?, quantity = ?, image = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $category_id, $quantity, $image_name, $description, $id]);
    } else {
        // กรณีไม่เปลี่ยนรูป
        $stmt = $pdo->prepare("UPDATE equipment SET name = ?, category_id = ?, quantity = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $category_id, $quantity, $description, $id]);
    }
    $alertScript = "Swal.fire('สำเร็จ', 'แก้ไขข้อมูลเรียบร้อยแล้ว', 'success');";
}

// --- LOGIC: DELETE EQUIPMENT ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    try {
        // เช็กก่อนว่ามีการยืมค้างอยู่หรือไม่ (ถ้ามีตาราง borrowings)
        $check = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE equipment_id = ? AND status = 'borrowed'");
        $check->execute([$id]);
        
        if ($check->fetchColumn() > 0) {
            $alertScript = "Swal.fire('ข้อผิดพลาด', 'ไม่สามารถลบได้เนื่องจากอุปกรณ์นี้ถูกยืมอยู่', 'error');";
        } else {
            $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: equipment.php"); // Refresh หน้าหลังลบ
            exit;
        }
    } catch (PDOException $e) {
        $alertScript = "Swal.fire('ข้อผิดพลาด', 'ไม่สามารถลบข้อมูลได้', 'error');";
    }
}
// ดึงข้อมูลหมวดหมู่มาโชว์ใน Select
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
// ดึงข้อมูลอุปกรณ์ทั้งหมด (สำหรับ Modal)
$all_equipment = $pdo->query("SELECT * FROM equipment")->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลโปรไฟล์ Admin
$stmt = $pdo->prepare("SELECT username, first_name, last_name, profile_image FROM users WHERE id = ? AND role = 'admin'");
$stmt->execute([$_SESSION['user_id']]);
$adminProfile = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการอุปกรณ์ - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Prompt', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white border-b px-8 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">คลังอุปกรณ์</h1>
            <p class="text-sm text-gray-500">จัดการและตรวจสอบรายการอุปกรณ์ทั้งหมดในระบบ</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="toggleModal('addEquipModal')" class="bg-[#722ff9] hover:bg-[#5b21b6] text-white px-5 py-2.5 rounded-xl transition-all flex items-center shadow-lg shadow-purple-100 font-medium">
                <i class="bi bi-plus-lg mr-2"></i> เพิ่มอุปกรณ์ใหม่
            </button>
        </div>
    </header>

    <div class="px-8 py-4 bg-gray-50/50 border-b flex flex-wrap items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <i class="bi bi-search absolute left-4 top-3 text-gray-400"></i>
            <input type="text" id="equipSearch" placeholder="ค้นหาชื่ออุปกรณ์..." 
                class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all shadow-sm">
        </div>
        
        <select id="filterCategory" onchange="loadEquipment()" class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-purple-500/20 shadow-sm text-gray-600">
            <option value="">ทุกหมวดหมู่</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="filterStatus" onchange="loadEquipment()" class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-purple-500/20 shadow-sm text-gray-600">
            <option value="">ทุกสถานะ</option>
            <option value="available">พร้อมใช้งาน</option>
            <option value="out_of_stock">ของหมด</option>
        </select>
    </div>

    <div class="flex-1 overflow-y-auto p-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">ข้อมูลอุปกรณ์</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">หมวดหมู่</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">สถานะการคลัง</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="equipTableBody" class="divide-y divide-gray-100">
                    </tbody>
            </table>
        </div>
    </div>
</main>

    <script>
        function loadEquipment() {
            const query = $('#equipSearch').val();
            const category = $('#filterCategory').val();
            const status = $('#filterStatus').val();

            $.ajax({
                url: 'equipment.php',
                type: 'GET',
                data: { 
                    search: query,
                    category_id: category,
                    status: status,
                    ajax: 1 // บอก PHP ว่านี่คือการเรียกแบบ AJAX
                },
                success: function(data) {
                    $('#equipTableBody').html(data);
                }
            });
        }

        // ผูกเหตุการณ์การพิมพ์
        $('#equipSearch').on('input', function() {
            loadEquipment();
        });



        $(document).ready(function() {
            loadEquipment();
            <?= $alertScript ?>
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "ข้อมูลนี้จะถูกลบออกจากระบบอย่างถาวร",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#722ff9',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `equipment.php?delete_id=${id}`;
                }
            })
        }
    </script>
    <?php foreach ($all_equipment as $item): ?>
<div id="editEquipModal<?= $item['id'] ?>" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800">แก้ไขอุปกรณ์</h3>
            <button onclick="toggleModal('editEquipModal<?= $item['id'] ?>')" class="text-gray-400 hover:text-gray-600"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="equipment_id" value="<?= $item['id'] ?>">
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4 col-span-2">
                    <label class="block text-sm font-medium mb-1">ชื่ออุปกรณ์</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required class="w-full p-2.5 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">หมวดหมู่</label>
                    <select name="category_id" class="w-full p-2.5 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-purple-500">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $item['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">จำนวน</label>
                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" class="w-full p-2.5 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="mb-4 col-span-2">
                    <label class="block text-sm font-medium mb-1">คำอธิบาย</label>
                    <textarea name="description" class="w-full p-2.5 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars($item['description']) ?></textarea>
                </div>
                <div class="mb-6 col-span-2 text-center">
                    <img src="Uploads/<?= $item['image'] ?>" class="w-20 h-20 mx-auto rounded-lg object-cover mb-2 border">
                    <label class="block text-sm font-medium mb-1 text-left">เปลี่ยนรูปภาพ (ถ้ามี)</label>
                    <input type="file" name="image" accept="image/*" class="text-sm w-full">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="toggleModal('editEquipModal<?= $item['id'] ?>')" class="flex-1 py-3 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition-all">ยกเลิก</button>
                <button type="submit" name="update_equipment" class="flex-1 py-3 bg-[#722ff9] text-white font-bold rounded-xl shadow-lg hover:bg-[#5b21b6] transition-all">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
<div id="addEquipModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate__animated animate__zoomIn animate__faster">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-xl font-bold text-gray-800">เพิ่มอุปกรณ์เข้าคลัง</h3>
            <button onclick="toggleModal('addEquipModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        <form action="" method="POST" enctype="multipart/form-data" class="p-8">
            <div class="grid grid-cols-2 gap-5">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ชื่ออุปกรณ์</label>
                    <input type="text" name="name" required placeholder="เช่น Projector Epson EB-X06" 
                        class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">หมวดหมู่</label>
                    <select name="category_id" required class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                        <option value="">เลือกหมวดหมู่</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">จำนวนตั้งต้น</label>
                    <input type="number" name="quantity" value="1" min="1" required 
                        class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">รายละเอียด / หมายเหตุ</label>
                    <textarea name="description" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติม..." 
                        class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"></textarea>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">รูปภาพอุปกรณ์</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-purple-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <i class="bi bi-image text-3xl text-gray-400"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none">
                                    <span>อัปโหลดไฟล์รูปภาพ</span>
                                    <input id="file-upload" name="image" type="file" class="sr-only" accept="image/*">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, JPEG ไม่เกิน 2MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="toggleModal('addEquipModal')" 
                    class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition-all">ยกเลิก</button>
                <button type="submit" name="add_equipment" 
                    class="flex-1 py-3 bg-[#722ff9] text-white font-bold rounded-xl shadow-lg shadow-purple-200 hover:bg-[#5b21b6] transform hover:-translate-y-0.5 transition-all">
                    บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    // เพิ่มฟังก์ชันเปิด-ปิด Modal
    function toggleModal(id) {
        document.getElementById(id).classList.toggle('hidden');
    }
    function exportToExcel() {
    let table = document.querySelector("#equipTableBody").parentElement;
    let html = table.outerHTML;
    
    // กำหนดรายละเอียดไฟล์
    let url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    let link = document.createElement('a');
    link.download = 'รายงานอุปกรณ์_' + new Date().toLocaleDateString() + '.xls';
    link.href = url;
    link.click();
}
</script>
</body>
</html>