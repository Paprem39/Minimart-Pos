<?php
require_once '../db.php';
include '../includes/admin_header.php';

$success = '';
$error = '';

// จัดการอนุมัติพนักงานแคชเชียร์
if (isset($_GET['approve_id'])) {
    $approve_id = $_GET['approve_id'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->execute([$approve_id]);
        $success = 'อนุมัติพนักงานเรียบร้อยแล้ว';
    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาดในการอนุมัติ';
    }
}

// จัดการลบพนักงานที่รออนุมัติ (ปฏิเสธ)
if (isset($_GET['reject_id'])) {
    $reject_id = $_GET['reject_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'cashier' AND status = 'pending'");
        $stmt->execute([$reject_id]);
        $success = 'ปฏิเสธและลบคำขอเรียบร้อยแล้ว';
    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาด';
    }
}

// --- ดึงข้อมูลสถิติภาพรวม ---
// 1. ยอดขายรวม & กำไร
$salesQuery = $pdo->query("
    SELECT 
        COALESCE(SUM(o.total_price), 0) as total_sales,
        COALESCE(SUM(od.quantity * p.cost), 0) as total_cost
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    JOIN products p ON od.product_id = p.id
");
$salesData = $salesQuery->fetch();
$totalSales = $salesData['total_sales'];
$totalCost = $salesData['total_cost'];
$totalProfit = $totalSales - $totalCost;

// 2. จำนวนบิลทั้งหมด
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// 3. จำนวนสินค้าทั้งหมดในสต็อก
$totalProducts = $pdo->query("SELECT SUM(stock_qty) FROM products")->fetchColumn();

// --- ดึงข้อมูลพนักงานที่รออนุมัติ (Pending Cashiers) ---
$pendingUsers = $pdo->query("SELECT * FROM users WHERE role = 'cashier' AND status = 'pending' ORDER BY id DESC")->fetchAll();

// --- ดึงข้อมูลสินค้าสต็อกใกล้หมด (Low Stock: stock_qty <= min_stock) ---
$lowStocks = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock_qty <= p.min_stock ORDER BY p.stock_qty ASC")->fetchAll();

// --- ดึงข้อมูลสินค้าใกล้หมดอายุ (ภายใน 30 วัน หรือหมดอายุแล้ว) ---
$expiryAlerts = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.expire_date IS NOT NULL AND p.expire_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) ORDER BY p.expire_date ASC")->fetchAll();

$stmtSummary = $pdo->query("SELECT 
    COALESCE(SUM(total_amount), 0) AS total_sales, 
    COALESCE(SUM(total_profit), 0) AS total_profit, 
    COUNT(id) AS total_bills 
    FROM sales");
$summary = $stmtSummary->fetch();

?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-slate-800">📊 แดชบอร์ด & ภาพรวมระบบ</h2>
    </div>

    <?php if ($success): ?>
        <div class="p-3 bg-green-100 text-green-700 rounded-xl text-sm"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="p-3 bg-red-100 text-red-700 rounded-xl text-sm"><?= $error ?></div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500">ยอดขายรวมทั้งหมด</p>
            <h3 class="text-2xl font-bold text-indigo-600 mt-1">฿<?= number_format($summary['total_sales'], 2) ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500">กำไรสุทธิโดยประมาณ</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">฿<?= number_format($summary['total_profit'], 2) ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500">จำนวนบิลขายทั้งหมด</p>
            <h3 class="text-2xl font-bold text-slate-700 mt-1"><?= number_format($summary['total_bills']) ?> บิล</h3>
        </div>
        <!-- กล่องเดิม: สินค้าคงเหลือ -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
            <p class="text-sm text-slate-500">สินค้าคงเหลือรวมในสต็อก</p>
            <h3 class="text-2xl font-bold text-amber-500 mt-1"><?= number_format($totalProducts) ?> ชิ้น</h3>
        </div>
    </div>

    <!-- ส่วนที่ 1: อนุมัติสิทธิ์พนักงานแคชเชียร์ (Pending Approvals) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span>👤 คำขอลงทะเบียนพนักงานใหม่ (Cashier Approval)</span>
            <?php if(count($pendingUsers) > 0): ?>
                <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($pendingUsers) ?></span>
            <?php endif; ?>
        </h3>
        
        <?php if (count($pendingUsers) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                            <th class="p-3">ชื่อ-นามสกุล</th>
                            <th class="p-3">รหัส PIN</th>
                            <th class="p-3">วันที่ขอลงทะเบียน</th>
                            <th class="p-3 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach ($pendingUsers as $pUser): ?>
                            <tr>
                                <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($pUser['fullname']) ?></td>
                                <td class="p-3 tracking-widest text-slate-600">••••</td>
                                <td class="p-3 text-slate-500 text-xs"><?= $pUser['created_at'] ?></td>
                                <td class="p-3 text-center space-x-2">
                                    <a href="dashboard.php?approve_id=<?= $pUser['id'] ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs font-medium transition">อนุมัติ</a>
                                    <a href="dashboard.php?reject_id=<?= $pUser['id'] ?>" onclick="return confirm('ปฏิเสธคำขอนี้?')" class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-1.5 rounded-xl text-xs font-medium transition">ปฏิเสธ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-slate-400 text-sm">ไม่มีคำขอลงทะเบียนพนักงานใหม่ในขณะนี้</p>
        <?php endif; ?>
    </div>

    <!-- ส่วนที่ 2: แจ้งเตือนสต็อกต่ำกว่ากำหนด & สินค้าใกล้หมดอายุ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Low Stock Alert -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 text-amber-600">⚠️ สินค้าสต็อกใกล้หมด (Low Stock Alert)</h3>
            <?php if (count($lowStocks) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                                <th class="p-3">สินค้า</th>
                                <th class="p-3">หมวดหมู่</th>
                                <th class="p-3 text-center">เหลือ</th>
                                <th class="p-3 text-center">ขั้นต่ำ (Min)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php foreach ($lowStocks as $item): ?>
                                <tr class="hover:bg-amber-50/30">
                                    <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($item['name']) ?></td>
                                    <td class="p-3 text-slate-500 text-xs"><?= htmlspecialchars($item['category_name']) ?></td>
                                    <td class="p-3 text-center font-bold text-red-600"><?= $item['stock_qty'] ?></td>
                                    <td class="p-3 text-center text-slate-500"><?= $item['min_stock'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-slate-400 text-sm">ยอดเยี่ยม! ไม่มีสินค้าที่ต่ำกว่าเกณฑ์สต็อก</p>
            <?php endif; ?>
        </div>

        <!-- Expiry Alert -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 text-red-600">⌛ สินค้าใกล้หมดอายุ (Expiry Alert)</h3>
            <?php if (count($expiryAlerts) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                                <th class="p-3">สินค้า</th>
                                <th class="p-3">หมวดหมู่</th>
                                <th class="p-3 text-center">วันหมดอายุ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php foreach ($expiryAlerts as $exp): ?>
                                <tr class="hover:bg-red-50/30">
                                    <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($exp['name']) ?></td>
                                    <td class="p-3 text-slate-500 text-xs"><?= htmlspecialchars($exp['category_name']) ?></td>
                                    <td class="p-3 text-center font-semibold text-red-500"><?= $exp['expire_date'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-slate-400 text-sm">ไม่มีสินค้าที่ใกล้หมดอายุในระยะเวลาอันใกล้</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
echo "</main></div></body></html>";
?>