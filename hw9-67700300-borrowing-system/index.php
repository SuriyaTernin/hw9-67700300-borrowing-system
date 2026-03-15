<?php
session_start();

// หากมีการล็อกอินอยู่แล้ว ให้ Redirect ไปยังหน้า Dashboard ที่เหมาะสมทันที
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบยืม-คืนอุปกรณ์กีฬา (SportBorrow)</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body { 
            font-family: 'Prompt', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            z-index: -1;
            opacity: 0.5;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="text-gray-800 overflow-x-hidden relative">

    <!-- Background Shapes -->
    <div class="hero-shape bg-purple-400 w-96 h-96 top-[-10%] left-[-10%]"></div>
    <div class="hero-shape bg-blue-400 w-96 h-96 bottom-[-10%] right-[-10%]"></div>

    <!-- Navbar -->
    <nav class="w-full p-4 md:p-6 absolute top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center glass-effect rounded-2xl px-6 py-4 animate__animated animate__fadeInDown">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <span class="text-xl font-bold gradient-text hidden sm:block">SportBorrow</span>
            </div>
            <div>
                <a href="login.php" class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-xl shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 inline-flex items-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-1 flex items-center justify-center pt-32 pb-20 px-6 z-10 relative">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <!-- Left Content -->
            <div class="animate__animated animate__fadeInLeft">
                <div class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md border border-white/30 rounded-full text-white text-sm font-medium mb-6">
                    🚀 ระบบจัดการยืม-คืนอุปกรณ์ 2024
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6 drop-shadow-lg">
                    ยืม-คืน อุปกรณ์กีฬา<br>ได้อย่างง่ายดาย
                </h1>
                <p class="text-lg text-white/90 mb-8 max-w-xl font-light">
                    ยกระดับการจัดการอุปกรณ์กีฬาด้วยระบบที่ทันสมัย ใช้งานง่าย พร้อมรองรับการอนุมัติคำร้อง ระบบสแกน QR Code สำหรับรับของ และการติดตามสถานะแบบเรียลไทม์
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="login.php" class="px-8 py-4 bg-white text-purple-700 font-bold rounded-xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 flex items-center gap-2">
                        เริ่มต้นใช้งาน <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Right Content (Image / Mockup) -->
            <div class="animate__animated animate__fadeInRight flex justify-center lg:justify-end floating">
                <div class="glass-effect rounded-[2rem] p-6 w-full max-w-md shadow-2xl relative border-t border-l border-white/40">
                    <!-- Decorative elements -->
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-yellow-400 rounded-full mix-blend-overlay filter blur-xl opacity-70 animate-pulse"></div>
                    <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-blue-400 rounded-full mix-blend-overlay filter blur-xl opacity-70 animate-pulse" style="animation-delay: 1.5s;"></div>
                    
                    <!-- Hero Image placeholder -->
                    <div class="bg-gray-100 rounded-2xl w-full h-64 object-cover shadow-inner mb-6 overflow-hidden relative group">
                        <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Sports Equipment" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 text-white">
                            <p class="font-bold">จัดการอุปกรณ์อย่างเป็นระบบ</p>
                        </div>
                    </div>
                    
                    <!-- Features list -->
                    <div class="space-y-4 relative z-10">
                        <div class="flex items-center gap-4 bg-white/70 backdrop-blur p-3 rounded-xl border border-white/50 shadow-sm">
                            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center text-xl"><i class="bi bi-qr-code-scan"></i></div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">สแกนรับอุปกรณ์</h4>
                                <p class="text-xs text-gray-500 font-medium">รวดเร็วและปลอดภัยผ่าน QR Code</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 bg-white/70 backdrop-blur p-3 rounded-xl border border-white/50 shadow-sm">
                            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-xl"><i class="bi bi-check-circle-fill"></i></div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">อนุมัติคำร้องอัตโนมัติ</h4>
                                <p class="text-xs text-gray-500 font-medium">มีระบบแจ้งเตือนและติดตามสถานะ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>