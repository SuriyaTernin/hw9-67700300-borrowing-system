<?php
session_start();
require_once 'config.php';

// ตรวจสอบสิทธิ์การเข้าถึง (สำหรับ User)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

// ดึงข้อมูลโปรไฟล์ผู้ใช้สำหรับแสดงบน Header
$stmt = $pdo->prepare("SELECT student_id, first_name, last_name, profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userProfile = $stmt->fetch();

// --- สถิติส่วนตัวของผู้ใช้ ---
// 1. อุปกรณ์ที่กำลังยืมอยู่
$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status = 'borrowed' AND approval_status = 'approved'");
$stmt->execute([$_SESSION['user_id']]);
$currentlyBorrowed = $stmt->fetchColumn();

// 2. คำร้องที่รออนุมัติ
$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND approval_status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$pendingApproval = $stmt->fetchColumn();

// 3. คืนอุปกรณ์เรียบร้อยแล้วทั้งหมด
$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status = 'returned'");
$stmt->execute([$_SESSION['user_id']]);
$totalReturned = $stmt->fetchColumn();

// ข้อมูลสำหรับกราฟวงกลม (สัดส่วนการยืมตามหมวดหมู่ของผู้ใช้นี้)
$stmt = $pdo->prepare("
    SELECT c.name, COUNT(b.id) as total 
    FROM borrowings b
    JOIN equipment e ON b.equipment_id = e.id
    JOIN categories c ON e.category_id = c.id
    WHERE b.user_id = ?
    GROUP BY c.id
");
$stmt->execute([$_SESSION['user_id']]);
$categoryStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ข้อมูลสำหรับกราฟเส้น (สถิติการยืม 7 วันล่าสุดของผู้ใช้นี้)
$stmt = $pdo->prepare("
    SELECT DATE(borrow_date) as date, COUNT(*) as count 
    FROM borrowings 
    WHERE user_id = ? AND borrow_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(borrow_date)
    ORDER BY date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// หากไม่มีข้อมูลกราฟ ให้สร้างข้อมูลจำลองเพื่อให้กราฟไม่ว่างเปล่า
$categoryLabels = !empty($categoryStats) ? array_column($categoryStats, 'name') : ['ยังไม่มีข้อมูล'];
$categoryData = !empty($categoryStats) ? array_column($categoryStats, 'total') : [1];

// เตรียมข้อมูลวันที่ย้อนหลัง 7 วัน สำหรับกราฟเส้น
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

$dailyCounts = array_fill_keys($dates, 0);
foreach ($dailyStats as $stat) {
    $dailyCounts[$stat['date']] = $stat['count'];
}

$dailyLabels = array_map(function($d) { return date('d/m', strtotime($d)); }, array_keys($dailyCounts));
$dailyData = array_values($dailyCounts);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติการยืม - ระบบยืม-คืนอุปกรณ์</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        
        .sidebar-item { position: relative; overflow: hidden; }
        .sidebar-item::before {
            content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: translateX(-100%); transition: transform 0.3s ease;
        }
        .sidebar-item:hover::before, .sidebar-item.active::before { transform: translateX(0); }
        .sidebar-item.active { background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%); border-left: 3px solid #667eea; }
        .sidebar-item:hover { background: rgba(255, 255, 255, 0.05); }
        
        .premium-header {
            background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .modern-shadow { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    </style>
</head>
<body class="text-gray-800 h-screen flex overflow-hidden">

    <!-- Mobile Header -->
    <div class="md:hidden fixed w-full bg-[#722ff9] text-white z-50 flex justify-between items-center p-4 shadow-md">
        <span class="font-bold text-lg">สถิติการยืม</span>
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
                    <h1 class="text-2xl font-bold text-gray-800 animate__animated animate__fadeInDown">สถิติการยืมของคุณ</h1>
                    <p class="text-gray-500 text-xs mt-1">ภาพรวมการทำรายการยืม-คืนอุปกรณ์ทั้งหมด</p>
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
            <div class="max-w-7xl mx-auto">
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="glass-effect rounded-3xl p-6 card-hover animate__animated animate__fadeInUp modern-shadow border-l-4 border-yellow-400">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 font-semibold mb-1">คำร้องรออนุมัติ</p>
                                <h3 class="text-3xl font-bold text-gray-800"><?= $pendingApproval ?> <span class="text-sm font-normal text-gray-500">รายการ</span></h3>
                            </div>
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-yellow-300 to-yellow-500 flex items-center justify-center text-white shadow-lg">
                                <i class="bi bi-hourglass-split text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-effect rounded-3xl p-6 card-hover animate__animated animate__fadeInUp modern-shadow border-l-4 border-blue-500" style="animation-delay: 0.1s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 font-semibold mb-1">อุปกรณ์ที่กำลังยืม</p>
                                <h3 class="text-3xl font-bold text-gray-800"><?= $currentlyBorrowed ?> <span class="text-sm font-normal text-gray-500">รายการ</span></h3>
                            </div>
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white shadow-lg">
                                <i class="bi bi-box-seam text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-effect rounded-3xl p-6 card-hover animate__animated animate__fadeInUp modern-shadow border-l-4 border-green-500" style="animation-delay: 0.2s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 font-semibold mb-1">คืนเรียบร้อยแล้ว</p>
                                <h3 class="text-3xl font-bold text-gray-800"><?= $totalReturned ?> <span class="text-sm font-normal text-gray-500">รายการ</span></h3>
                            </div>
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white shadow-lg">
                                <i class="bi bi-check2-circle text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="glass-effect rounded-3xl p-8 animate__animated animate__fadeInLeft modern-shadow">
                        <h3 class="text-xl font-bold gradient-text mb-6">
                            <i class="bi bi-graph-up-arrow text-purple-600 mr-2"></i> สถิติการขอยืม 7 วันล่าสุด
                        </h3>
                        <canvas id="lineChart" height="150"></canvas>
                    </div>

                    <div class="glass-effect rounded-3xl p-8 animate__animated animate__fadeInRight modern-shadow">
                        <h3 class="text-xl font-bold gradient-text mb-6">
                            <i class="bi bi-pie-chart text-purple-600 mr-2"></i> หมวดหมู่ที่ยืมบ่อยที่สุด
                        </h3>
                        <div class="max-w-[250px] mx-auto">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        // Sidebar Toggle
        $('#mobile-menu-btn').click(function() { $('#sidebar').toggleClass('-translate-x-full'); });
        function toggleSidebar() { $('#sidebar').toggleClass('collapsed'); }
        $(document).on('click', '#sidebar-toggle', toggleSidebar);

        // ดึงข้อมูลจาก PHP ไปสร้างกราฟ
        const categoryLabels = <?= json_encode($categoryLabels) ?>;
        const categoryData = <?= json_encode($categoryData) ?>;
        const dailyLabels = <?= json_encode($dailyLabels) ?>;
        const dailyData = <?= json_encode($dailyData) ?>;

        // สร้างกราฟเส้น (Line Chart)
        new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: { labels: dailyLabels, datasets: [{ label: 'จำนวนครั้งที่ยืม', data: dailyData, borderColor: '#722ff9', backgroundColor: 'rgba(114, 47, 249, 0.1)', fill: true, tension: 0.4 }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        // สร้างกราฟวงกลม (Pie Chart)
        new Chart(document.getElementById('pieChart').getContext('2d'), {
            type: 'doughnut',
            data: { labels: categoryLabels, datasets: [{ data: categoryData, backgroundColor: ['#722ff9', '#B8A2F9', '#DDD6FE', '#A78BFA', '#8B5CF6', '#4C1D95'] }] },
            options: { cutout: '65%', plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>