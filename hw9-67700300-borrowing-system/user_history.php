<?php
session_start();
require_once 'config.php';

// ตรวจสอบสิทธิ์การเข้าถึง (ต้องเป็น User เท่านั้น)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

// ---------------------------------------------------------
// 1. ระบบจัดการการยกเลิกคำร้อง (ต้องเป็น Pending เท่านั้น)
// ---------------------------------------------------------
if (isset($_POST['cancel_request']) && isset($_POST['cancel_id'])) {
    $cancel_id = $_POST['cancel_id'];
    
    try {
        // ลบรายการออกจากฐานข้อมูล
        $stmt = $pdo->prepare("DELETE FROM borrowings WHERE id = ? AND user_id = ? AND approval_status = 'pending'");
        $stmt->execute([$cancel_id, $_SESSION['user_id']]);
        
        // รีเฟรชหน้าเพื่ออัปเดตข้อมูล พร้อมส่งพารามิเตอร์แจ้งว่ายกเลิกสำเร็จ
        header("Location: user_history.php?cancel_success=1");
        exit;
    } catch (PDOException $e) {
        $error_msg = "เกิดข้อผิดพลาดในการยกเลิก: " . $e->getMessage();
    }
}

// ดึงข้อมูลโปรไฟล์ผู้ใช้สำหรับแสดงบน Header
$stmt = $pdo->prepare("SELECT student_id, first_name, last_name, profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userProfile = $stmt->fetch();

// ดึงข้อมูลประวัติการยืมของผู้ใช้ปัจจุบัน
$stmt = $pdo->prepare("
    SELECT b.*, e.name as equipment_name, e.image as equipment_image
    FROM borrowings b 
    JOIN equipment e ON b.equipment_id = e.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$borrowing_history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการยืม-คืน - ระบบยืม-คืนอุปกรณ์</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- jQuery Confirm CSS สำหรับ Popup สวยๆ -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css">

    <style>
        body { 
            font-family: 'Prompt', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }
        .sidebar-gradient { background: linear-gradient(180deg, #1e293b 0%, #334155 100%); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-gradient.collapsed { width: 80px; }
        .sidebar-gradient.collapsed .sidebar-text { opacity: 0; visibility: hidden; }
        .sidebar-gradient.collapsed .sidebar-icon { margin-right: 0; }
        .sidebar-gradient.collapsed .sidebar-header h2 { font-size: 0; }
        .sidebar-gradient.collapsed .sidebar-header .logo-icon { font-size: 1.5rem; }
        
        .premium-header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .modern-shadow { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        
        /* Custom Status Badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .badge-pending { background: linear-gradient(135deg, #fde68a 0%, #f59e0b 100%); color: #78350f; }
        .badge-approved { background: linear-gradient(135deg, #a7f3d0 0%, #10b981 100%); color: #064e3b; }
        .badge-rejected { background: linear-gradient(135deg, #fecaca 0%, #ef4444 100%); color: #7f1d1d; }
        .badge-returned { background: linear-gradient(135deg, #bfdbfe 0%, #3b82f6 100%); color: #1e3a8a; }
    </style>
</head>
<body class="text-gray-800 h-screen flex overflow-hidden">

    <!-- Mobile Header -->
    <div class="md:hidden fixed w-full bg-[#722ff9] text-white z-50 flex justify-between items-center p-4 shadow-md">
        <span class="font-bold text-lg">ประวัติการยืม</span>
        <button id="mobile-menu-btn" class="focus:outline-none">
            <i class="bi bi-list text-2xl"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <?php include 'sidebar_user.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full relative overflow-hidden pt-16 md:pt-0">
        <!-- Top Bar -->
        <header class="premium-header z-10 px-8 py-4 flex justify-between items-center border-b border-gray-200/50">
            <div class="flex items-center space-x-6">
                <div class="sidebar-toggle hidden md:flex cursor-pointer" id="sidebar-toggle">
                    <i class="bi bi-list text-2xl text-gray-600 hover:text-purple-600 transition-colors"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 animate__animated animate__fadeInDown">ประวัติการทำรายการ</h1>
                    <p class="text-gray-500 text-xs mt-1">ดูประวัติการยืมและคืนอุปกรณ์ทั้งหมดของคุณ</p>
                </div>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-purple-200">
                        <?php if ($userProfile['profile_image'] && $userProfile['profile_image'] !== 'default.jpg'): ?>
                            <img src="Uploads/profiles/<?php echo htmlspecialchars($userProfile['profile_image']); ?>" 
                                 class="w-full h-full object-cover" alt="Profile">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                <?php echo strtoupper(substr($userProfile['first_name'] ?: $_SESSION['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($userProfile['first_name'] . ' ' . $userProfile['last_name'] ?: $_SESSION['username']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($userProfile['student_id'] ?: 'นักศึกษา'); ?></p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-10">
            <div class="glass-effect rounded-3xl p-8 animate__animated animate__fadeInUp modern-shadow">
                <h3 class="text-xl font-bold gradient-text mb-6">
                    <i class="bi bi-clock-history text-purple-600 mr-2"></i> ประวัติของคุณ
                </h3>

                <?php if (isset($error_msg)): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 flex items-center">
                        <i class="bi bi-exclamation-triangle-fill mr-3"></i>
                        <?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">อุปกรณ์</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">จำนวน</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">วันที่ทำรายการ</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">สถานะ</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($borrowing_history) > 0): ?>
                                <?php foreach ($borrowing_history as $history): ?>
                                    <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100">
                                        <td class="px-5 py-4 bg-white text-sm">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-12 h-12">
                                                    <img class="w-full h-full rounded-lg object-cover border border-gray-200" 
                                                         src="Uploads/<?php echo htmlspecialchars($history['equipment_image'] ?: 'default.jpg'); ?>" 
                                                         alt="<?php echo htmlspecialchars($history['equipment_name']); ?>" />
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-gray-900 font-semibold whitespace-no-wrap">
                                                        <?php echo htmlspecialchars($history['equipment_name']); ?>
                                                    </p>
                                                    <p class="text-xs text-gray-500">รหัสทำรายการ: #<?php echo str_pad($history['id'], 5, '0', STR_PAD_LEFT); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 bg-white text-sm text-center font-medium text-gray-700">
                                            <?php echo $history['quantity']; ?>
                                        </td>
                                        <td class="px-5 py-4 bg-white text-sm text-center">
                                            <div class="flex flex-col gap-1 text-xs">
                                                <span><i class="bi bi-calendar-event text-blue-500"></i> ยืม: <?php echo date('d/m/Y', strtotime($history['borrow_date'])); ?></span>
                                                <?php if ($history['status'] === 'returned' && $history['return_date']): ?>
                                                    <span><i class="bi bi-calendar-check text-green-500"></i> คืน: <?php echo date('d/m/Y', strtotime($history['return_date'])); ?></span>
                                                <?php else: ?>
                                                    <span class="text-gray-400"><i class="bi bi-calendar text-gray-300"></i> คืน: -</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 bg-white text-sm text-center">
                                            <?php if ($history['approval_status'] === 'pending'): ?>
                                                <span class="badge badge-pending"><i class="bi bi-hourglass-split"></i> รออนุมัติ</span>
                                            <?php elseif ($history['approval_status'] === 'rejected'): ?>
                                                <span class="badge badge-rejected"><i class="bi bi-x-circle"></i> ปฏิเสธ</span>
                                                <?php if (!empty($history['rejection_reason'])): ?>
                                                    <p class="text-xs text-red-500 mt-1 max-w-[150px] mx-auto truncate" title="<?php echo htmlspecialchars($history['rejection_reason']); ?>">
                                                        (<?php echo htmlspecialchars($history['rejection_reason']); ?>)
                                                    </p>
                                                <?php endif; ?>
                                            <?php elseif ($history['approval_status'] === 'approved'): ?>
                                                <?php if ($history['status'] === 'returned'): ?>
                                                    <span class="badge badge-returned"><i class="bi bi-check-circle"></i> คืนแล้ว</span>
                                                <?php elseif ($history['pickup_confirmed']): ?>
                                                    <span class="badge badge-pending" style="background: linear-gradient(135deg, #d8b4fe 0%, #a855f7 100%); color: #4c1d95;"><i class="bi bi-box-seam"></i> กำลังใช้งาน</span>
                                                <?php else: ?>
                                                    <span class="badge badge-approved" style="background: linear-gradient(135deg, #e0e7ff 0%, #93c5fd 100%); color: #1e40af;"><i class="bi bi-box-arrow-down"></i> รอรับของ</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-5 py-4 bg-white text-sm text-center">
                                            <?php if ($history['approval_status'] === 'pending'): ?>
                                                <!-- ปุ่มยกเลิกคำขอ (แสดงเฉพาะตอนที่ยังไม่อนุมัติ) -->
                                                <button type="button" onclick="confirmCancel(<?php echo $history['id']; ?>)" class="text-xs text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg font-medium flex items-center justify-center w-fit mx-auto gap-1 transition-all duration-200 border border-red-200 hover:border-red-300">
                                                    <i class="bi bi-trash3"></i> ยกเลิกคำขอ
                                                </button>
                                            <?php elseif ($history['approval_status'] === 'approved' && $history['status'] === 'borrowed' && !$history['pickup_confirmed']): ?>
                                                <a href="qr_pickup.php?generate_qr=1&borrowing_id=<?php echo $history['id']; ?>" 
                                                   class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center w-fit mx-auto gap-1">
                                                    <i class="bi bi-qr-code text-sm"></i> รับอุปกรณ์ (QR)
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-5 py-12 bg-white text-sm text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                <i class="bi bi-inbox text-3xl text-gray-400"></i>
                                            </div>
                                            <p class="text-gray-500 font-medium">คุณยังไม่มีประวัติการทำรายการ</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- โหลดไลบรารี jQuery และ jQuery Confirm -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>

    <script>
        // Mobile Menu Toggle
        $('#mobile-menu-btn').click(function() {
            $('#sidebar').toggleClass('-translate-x-full');
        });

        // Sidebar Toggle Functionality
        function toggleSidebar() {
            $('#sidebar').toggleClass('collapsed');
        }
        $(document).on('click', '#sidebar-toggle', toggleSidebar);

        // ฟังก์ชันแสดง Popup แจ้งเตือนเมื่อกดยกเลิก
        function confirmCancel(cancelId) {
            $.confirm({
                title: 'ยืนยันการยกเลิก',
                content: 'คุณแน่ใจหรือไม่ว่าต้องการยกเลิกคำร้องขอยืมอุปกรณ์นี้?<br><span class="text-xs text-gray-500">การดำเนินการนี้ไม่สามารถย้อนกลับได้</span>',
                type: 'red',
                theme: 'modern',
                icon: 'bi bi-exclamation-triangle-fill',
                backgroundDismiss: true,
                animation: 'scale',
                closeAnimation: 'scale',
                buttons: {
                    confirm: {
                        text: 'ยืนยันการยกเลิก',
                        btnClass: 'btn-red',
                        action: function () {
                            // สร้าง Form ซ่อนเพื่อส่งค่า POST ไปลบข้อมูล
                            $('<form>', {
                                method: 'POST',
                                html: '<input type="hidden" name="cancel_id" value="' + cancelId + '">' +
                                      '<input type="hidden" name="cancel_request" value="1">'
                            }).appendTo('body').submit();
                        }
                    },
                    cancel: {
                        text: 'ปิดหน้าต่าง',
                        btnClass: 'btn-default'
                    }
                }
            });
        }

        // แจ้งเตือนเมื่อลบสำเร็จ (รับค่าผ่าน URL Parameter)
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('cancel_success') === '1') {
                $.alert({
                    title: 'สำเร็จ!',
                    content: 'ยกเลิกคำร้องเรียบร้อยแล้ว',
                    type: 'green',
                    theme: 'modern',
                    icon: 'bi bi-check-circle-fill',
                    autoClose: 'ok|3000' // ปิดอัตโนมัติใน 3 วินาที
                });
                
                // เคลียร์ค่า URL parameter ออกเพื่อไม่ให้แจ้งเตือนซ้ำตอนรีเฟรช
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>