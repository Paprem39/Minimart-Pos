<?php
require_once '../db.php';
session_start();
include '../includes/admin_header.php';

// 1. สินค้าขายดี Top (นับตามจำนวนชิ้นที่ขายได้)
$stmtTopQty = $pdo->query("SELECT p.name, SUM(sd.quantity) as total_qty, SUM(sd.total_price) as total_revenue 
    FROM sale_details sd 
    JOIN products p ON sd.product_id = p.id 
    GROUP BY sd.product_id 
    ORDER BY total_qty DESC LIMIT 5");
$topQtyProducts = $stmtTopQty->fetchAll();

// 2. สินค้าที่ทำกำไรสูงสุด Top
$stmtTopProfit = $pdo->query("SELECT p.name, SUM(sd.total_profit) as total_profit, SUM(sd.quantity) as total_qty 
    FROM sale_details sd 
    JOIN products p ON sd.product_id = p.id 
    GROUP BY sd.product_id 
    ORDER BY total_profit DESC LIMIT 5");
$topProfitProducts = $stmtTopProfit->fetchAll();

// 3. ยอดขายตามหมวดหมู่สินค้าสูงสุด
$stmtCategory = $pdo->query("SELECT c.name as category_name, SUM(sd.total_price) as total_sales, SUM(sd.total_profit) as total_profit 
    FROM sale_details sd 
    JOIN products p ON sd.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    GROUP BY p.category_id 
    ORDER BY total_sales DESC");
$categoriesSales = $stmtCategory->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานยอดขายและกำไร - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">
    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">📈 รายงานยอดขาย วิเคราะห์สินค้า & กำไร</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- สินค้าขายดี (Top Quantity) -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-700 mb-4">🔥 5 อันดับ สินค้าขายดี (ตามจำนวนชิ้น)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="p-3">สินค้า</th>
                                <th class="p-3 text-right">ขายได้ (ชิ้น)</th>
                                <th class="p-3 text-right">ยอดขายรวม</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($topQtyProducts as $p): ?>
                            <tr>
                                <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="p-3 text-right font-bold text-indigo-600"><?= number_format($p['total_qty']) ?></td>
                                <td class="p-3 text-right text-slate-600">฿<?= number_format($p['total_revenue'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- สินค้าทำกำไรสูงสุด (Top Profit) -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-700 mb-4">💰 5 อันดับ สินค้าทำกำไรสูงสุด</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="p-3">สินค้า</th>
                                <th class="p-3 text-right">จำนวนที่ขาย</th>
                                <th class="p-3 text-right">กำไรรวม</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($topProfitProducts as $p): ?>
                            <tr>
                                <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="p-3 text-right text-slate-600"><?= number_format($p['total_qty']) ?></td>
                                <td class="p-3 text-right font-bold text-emerald-600">฿<?= number_format($p['total_profit'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ยอดขายแยกตามหมวดหมู่ -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-slate-700 mb-4">📊 ยอดขายและกำไรแยกตามหมวดหมู่สินค้า</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="p-3">หมวดหมู่สินค้า</th>
                            <th class="p-3 text-right">ยอดขายรวม</th>
                            <th class="p-3 text-right">กำไรรวมสุทธิ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($categoriesSales as $cat): ?>
                        <tr>
                            <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($cat['category_name'] ?? 'ไม่ระบุหมวดหมู่') ?></td>
                            <td class="p-3 text-right font-semibold text-indigo-600">฿<?= number_format($cat['total_sales'], 2) ?></td>
                            <td class="p-3 text-right font-semibold text-emerald-600">฿<?= number_format($cat['total_profit'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>