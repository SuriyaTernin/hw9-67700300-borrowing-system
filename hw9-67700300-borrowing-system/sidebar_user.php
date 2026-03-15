<?php
// ตรวจสอบชื่อไฟล์ปัจจุบันเพื่อทำสถานะ Active
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar" class="sidebar-gradient text-white w-64 flex-shrink-0 fixed md:static h-full z-40 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out flex flex-col">
    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle hidden md:flex">
        <i class="bi bi-chevron-left text-xs"></i>
    </div>
    
    <div class="p-6 text-center border-b border-gray-700/30">
        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl mx-auto flex items-center justify-center mb-3 animate__animated animate__bounceIn shadow-lg">
            <i class="bi bi-box-seam text-3xl text-white"></i>
        </div>
        <h2 class="sidebar-header text-xl font-bold tracking-wide">
            <i class="logo-icon bi bi-box-seam mr-2"></i>
            <span class="sidebar-text">Equipment<br>Borrowing</span>
        </h2>
    </div>
    
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <a href="user_dashboard" class="sidebar-item flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= ($current_page == 'user_dashboard.php') ? 'active' : '' ?>">
            <i class="sidebar-icon bi bi-grid-fill mr-3 text-lg"></i> 
            <span class="sidebar-text font-medium">ยืมอุปกรณ์</span>
        </a>

        <a href="user_history" class="sidebar-item flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= ($current_page == 'user_history.php') ? 'active' : '' ?>">
            <i class="sidebar-icon bi bi-clock-history mr-3 text-lg"></i> 
            <span class="sidebar-text font-medium">ประวัติการยืม</span>
        </a>
        
        <a href="borrowing_dashboard" class="sidebar-item flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= ($current_page == 'borrowing_dashboard.php') ? 'active' : '' ?>">
            <i class="sidebar-icon bi bi-graph-up mr-3 text-lg"></i> 
            <span class="sidebar-text font-medium">สถิติการยืม</span>
        </a>
        
        <a href="profile" class="sidebar-item flex items-center px-4 py-3 transition-all duration-200 rounded-xl <?= ($current_page == 'profile.php') ? 'active' : '' ?>">
            <i class="sidebar-icon bi bi-person-circle mr-3 text-lg"></i> 
            <span class="sidebar-text font-medium">โปรไฟล์ของฉัน</span>
        </a>
    </nav>

    <div class="p-4 border-t border-gray-700/30 mt-auto">
        <a href="logout" class="flex items-center px-4 py-2 text-gray-300 hover:text-white transition-colors hover:bg-white/5 rounded-lg">
            <i class="sidebar-icon bi bi-box-arrow-right mr-3"></i> 
            <span class="sidebar-text">ออกจากระบบ</span>
        </a>
    </div>
</aside>