<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// เช็คสิทธิ์ว่าเป็น Admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Minimart POS</title>
    <!-- Tailwind CSS & Font Prompt -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between shadow-xl">
        <div>
            <div class="p-5 text-xl font-bold text-white tracking-wider flex items-center gap-2 border-b border-slate-800">
                <span class="bg-indigo-600 text-white p-1.5 rounded-lg text-sm">🛒</span> Minimart POS
            </div>
            <nav class="p-4 space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition hover:bg-slate-800 hover:text-white">
                    📊 แดชบอร์ด & รายงาน
                </a>
                <a href="products.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition hover:bg-slate-800 hover:text-white">
                    📦 จัดการสินค้า & สต็อก
                </a>
                <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition hover:bg-slate-800 hover:text-white">
                    🏷️ จัดการหมวดหมู่สินค้า
                </a>
                <a href="sales_report.php" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition hover:bg-slate-800 hover:text-white">
                    📈 รายงานการขาย & กำไร
                </a>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-800">
            <div class="text-xs text-slate-500 mb-2">ผู้ใช้งานระบบ</div>
            <div class="text-sm font-semibold text-white mb-3"><?= htmlspecialchars($_SESSION['fullname']) ?></div>
            <a href="../logout.php" class="block w-full text-center bg-red-600/20 text-red-400 hover:bg-red-600 hover:text-white py-2 rounded-xl text-sm font-medium transition">
                ออกจากระบบ
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navbar -->
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-8 z-10">
            <h1 class="text-lg font-semibold text-slate-800">ระบบจัดการหลังร้าน (Admin Dashboard)</h1>
            <div class="text-sm font-medium text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200 flex items-center gap-2">
                <span>🕒</span>
                <span id="live-clock">กำลังโหลดเวลา...</span>
            </div>
        </header>

        <!-- Page Content Scroll Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 bg-slate-50">

        <!-- Script สำหรับแสดงเวลาปัจจุบันแบบ Real-time -->
    <script>
        function updateClock() {
            const now = new Date();
            // กำหนดรูปแบบวันที่และเวลาเป็นภาษาไทย/สากล
            const options = { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false 
            };
            const timeString = now.toLocaleDateString('en-GB', options).replace(',', ' |');
            document.getElementById('live-clock').innerText = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock(); // รันทันทีเมื่อโหลดหน้าเว็บ
    </script>
</body>
</html>