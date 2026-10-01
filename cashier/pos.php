<?php
require_once '../db.php';
session_start();

$categories =$pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$search =$_GET['search'] ?? '';
$cat_id =$_GET['category_id'] ?? '';

$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock_qty > 0";
$params = [];

if (!empty($search)) {$sql .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}
if (!empty($cat_id)) {$sql .= " AND p.category_id = ?";
    $params[] =$cat_id;
}

$sql .= " ORDER BY p.name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products =$stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS ขายหน้าร้าน - Minimart POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

                /* ===== Stock Status ===== */
        @keyframes lowStockPulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.08);
            }
            50% {
                box-shadow: 0 0 0 5px rgba(239, 68, 68, 0.08);
            }
        }

        .low-stock-card {
            border-color: #fca5a5 !important;
            background: linear-gradient(to bottom right, #fff, #fff7f7);
            animation: lowStockPulse 2s ease-in-out infinite;
        }

        .low-stock-card:hover {
            border-color: #ef4444 !important;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.12) !important;
        }

        .low-stock-badge {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .normal-stock-badge {
            background: #ecfdf5;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .low-stock-warning {
            animation: warningBlink 1.5s ease-in-out infinite;
        }

        @keyframes warningBlink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.55;
            }
        }

        @media print {
            body * { visibility: hidden; }
            #receiptModal, #receiptModal * { visibility: visible; }
            #receiptModal { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; background: white; box-shadow: none; display: flex !important; z-index: 9999; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 h-screen overflow-hidden flex flex-col">

    <!-- Navbar ด้านบน -->
    <header class="bg-white border-b border-slate-200 px-6 py-3 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-md shadow-indigo-600/20">
                🛒
            </div>
            <div>
                <h1 class="font-bold text-slate-800 text-base">Minimart POS System</h1>
                <p class="text-xs text-slate-400">จุดขายหน้าร้าน (Cashier Terminal)</p>
            </div>
        </div>

        <div class="hidden md:flex items-center gap-2 bg-slate-50 border border-slate-200/80 px-4 py-2 rounded-2xl text-xs font-semibold text-slate-600">
            <span>📅</span>
            <span id="currentDateTime">กำลังโหลดเวลา...</span>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-xl border border-emerald-200">
                ● เปิดกะขายปกติ
            </span>
            <button onclick="openModal('logoutModal')" type="button" class="text-sm text-rose-600 hover:text-rose-700 font-medium transition bg-rose-50 hover:bg-rose-100 px-3.5 py-2 rounded-xl flex items-center gap-1">
                🚪 ออกจากระบบ
            </button>
        </div>
    </header>

    <!-- พื้นที่หลัก POS -->
    <div class="flex-1 flex overflow-hidden p-4 gap-4">
        
        <!-- ฝั่งซ้าย: ค้นหา & รายการสินค้า -->
        <div class="flex-1 flex flex-col bg-white rounded-3xl shadow-sm border border-slate-200/85 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap gap-3 items-center justify-between bg-slate-50/50">
                <form method="GET" class="flex-1 min-w-[240px] relative">
                    <?php if (!empty($cat_id)): ?>
                        <input type="hidden" name="category_id" value="<?= htmlspecialchars($cat_id) ?>">
                    <?php endif; ?>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="ค้นหาชื่อสินค้า หรือสแกนบาร์โค้ด..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-indigo-500 shadow-sm">
                    <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
                </form>
                
                <div class="flex items-center gap-1.5 overflow-x-auto max-w-full pb-1">
                    <a href="pos.php<?= !empty($search) ? '?search=' . urlencode($search) : '' ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition <?= empty($cat_id) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' ?>">
                        ทั้งหมด
                    </a>
                    <?php foreach ($categories as$cat): ?>
                        <a href="pos.php?category_id=<?= $cat['id'] ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition whitespace-nowrap <?= $cat_id ==$cat['id'] ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                <?php if (count($products) > 0): ?>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php foreach ($products as $p): ?>

                            <?php
                                $stockQty = (int) $p['stock_qty'];
                                $fixCost = (int) ($p['min_stock'] ?? 0);
                                $isLowStock = $fixCost > 0 && $stockQty < $fixCost;
                            ?>

                            <div
                                onclick="addToCart(<?= htmlspecialchars(json_encode($p)) ?>)"
                                class="<?= $isLowStock
                                    ? 'low-stock-card'
                                    : 'bg-white border border-slate-200/80 hover:border-indigo-500 hover:shadow-lg hover:shadow-indigo-500/5'
                                ?> rounded-2xl p-4 cursor-pointer transition flex flex-col justify-between group relative overflow-hidden"
                            >
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-semibold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                                        <?= htmlspecialchars($p['category_name']) ?>
                                    </span>

                                    <?php if ($isLowStock): ?>
                                        <span class="low-stock-warning text-[9px] font-extrabold bg-red-100 text-red-600 border border-red-200 px-2 py-1 rounded-lg whitespace-nowrap">
                                            ⚠ สั่งเพิ่ม
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="font-bold text-slate-800 text-sm mt-2 line-clamp-2 group-hover:text-indigo-600 transition">
                                    <?= htmlspecialchars($p['name']) ?>
                                </h3>

                                <?php if ($isLowStock): ?>
                                    <div class="mt-2 flex items-center gap-1.5 text-[10px] font-bold text-red-500">
                                        <span class="animate-pulse">🔴</span>
                                        <span>ต่ำกว่า Fix Cost (<?= $fixCost ?>)</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                                <div class="mt-4 flex items-end justify-between">
                                    <div>
                                        <span class="text-xs text-slate-400 block">ราคาขาย</span>
                                        <span class="text-base font-bold text-indigo-600">฿<?= number_format($p['price'], 2) ?></span>
                                    </div>

                                    <?php if ($isLowStock): ?>

                                        <span class="low-stock-badge text-xs font-extrabold px-2.5 py-1.5 rounded-lg whitespace-nowrap">
                                            🔴 คงเหลือ <?= $stockQty ?>
                                        </span>

                                        <?php else: ?>

                                        <span class="normal-stock-badge text-xs font-bold px-2.5 py-1.5 rounded-lg whitespace-nowrap">
                                            🟢 คงเหลือ <?= $stockQty ?>
                                        </span>

                                        <?php endif; ?>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 space-y-2">
                        <span class="text-4xl">📦</span>
                        <p class="text-sm">ไม่พบสินค้าในระบบ หรือสินค้าหมดสต็อก</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ฝั่งขวา: ตะกร้าสินค้า & ชำระเงิน -->
        <div class="w-[420px] bg-white rounded-3xl shadow-sm border border-slate-200/85 flex flex-col overflow-hidden shrink-0">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <span>🛍</span> ตะกร้าสินค้า
                </h2>
                <button onclick="clearCart()" type="button" class="text-xs text-rose-500 hover:text-rose-700 font-medium bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-xl transition">
                    ล้างตะกร้า
                </button>
            </div>

            <div id="cartItemsContainer" class="flex-1 overflow-y-auto divide-y divide-slate-100 p-4">
                <!-- ตะกร้าว่างจะถูกเรนเดอร์ผ่าน JS -->
            </div>

            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50 space-y-3">
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-slate-500">
                        <span>จำนวนรวม</span>
                        <span id="cartTotalQty">0 ชิ้น</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-slate-800 pt-1 border-t border-slate-200">
                        <span>ยอดชำระสุทธิ</span>
                        <span id="cartTotalPrice" class="text-indigo-600">฿0.00</span>
                    </div>
                </div>

                <button onclick="openCheckoutModal()" type="button" id="checkoutBtn" disabled class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:text-slate-400 text-white rounded-2xl font-bold text-sm transition shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2">
                    <span>💳</span> ชำระเงิน (F9)
                </button>
            </div>
        </div>
    </div>

    <!-- Modal ชำระเงิน -->
    <div id="checkoutModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 border border-slate-100 space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center shrink-0">
                <h3 class="text-lg font-bold text-slate-800">💰 ชำระเงินค่าสินค้า</h3>
                <button onclick="closeModal('checkoutModal')" type="button" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
            </div>
            
            <div class="border border-slate-200 rounded-2xl p-3 bg-slate-50/50 max-h-40 overflow-y-auto space-y-2 shrink-0">
                <span class="text-xs font-bold text-slate-500 block mb-1">รายการสินค้าในบิลนี้:</span>
                <div id="modalCartSummaryList" class="space-y-1.5 text-xs"></div>
            </div>

            <div class="bg-indigo-50 p-3 rounded-2xl border border-indigo-100 text-center shrink-0">
                <span class="text-xs text-indigo-600 font-semibold block">ยอดชำระสุทธิทั้งสิ้น</span>
                <div id="modalTotalPrice" class="text-2xl font-extrabold text-indigo-700">฿0.00</div>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto">
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="setPaymentMethod('cash')" id="btnMethodCash" class="py-2.5 px-4 rounded-xl text-xs font-bold border-2 transition flex items-center justify-center gap-2 border-indigo-600 bg-indigo-50 text-indigo-700">
                        <span>💵</span> เงินสด (Cash)
                    </button>
                    <button type="button" onclick="setPaymentMethod('transfer')" id="btnMethodTransfer" class="py-2.5 px-4 rounded-xl text-xs font-bold border-2 transition flex items-center justify-center gap-2 border-slate-200 bg-white text-slate-600">
                        <span>📱</span> เงินโอน / QR
                    </button>
                </div>

                <div id="cashPaymentSection" class="space-y-2">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1 font-medium">รับเงินสดจากลูกค้า (บาท)</label>
                        <input type="number" id="cashReceived" oninput="calculateChange()" placeholder="0.00" class="w-full px-4 py-2.5 border-2 border-slate-200 rounded-2xl text-base font-bold text-slate-800 focus:outline-none focus:border-indigo-500 text-right">
                    </div>
                    <div class="grid grid-cols-5 gap-1.5">
                        <button type="button" onclick="setCash(20)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700">20</button>
                        <button type="button" onclick="setCash(50)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700">50</button>
                        <button type="button" onclick="setCash(100)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700">100</button>
                        <button type="button" onclick="setCash(500)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700">500</button>
                        <button type="button" onclick="setCash(1000)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700">1000</button>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-200 p-3 rounded-2xl flex justify-between items-center">
                        <span class="text-xs font-bold text-emerald-800">เงินทอน</span>
                        <span id="changeAmount" class="text-lg font-extrabold text-emerald-600">฿0.00</span>
                    </div>
                </div>

                <div id="transferPaymentSection" class="hidden text-center space-y-2 p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div class="w-24 h-24 bg-white border border-slate-200 mx-auto rounded-xl flex items-center justify-center text-xl font-bold shadow-sm">
                        📱 QR
                    </div>
                    <p class="text-xs text-slate-500 font-medium">สแกน QR Code เพื่อชำระเงินผ่าน Mobile Banking</p>
                </div>
            </div>

            <div class="flex gap-2 pt-2 shrink-0">
                <button type="button" onclick="closeModal('checkoutModal')" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl text-xs font-semibold transition">ยกเลิก</button>
                <button type="button" onclick="processCheckout(event)" id="confirmPayBtn" disabled class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 text-white rounded-2xl text-xs font-bold transition shadow-lg shadow-emerald-600/25">ยืนยันการชำระเงิน</button>
            </div>
        </div>
    </div>

    <!-- Modal Preview ใบเสร็จ -->
    <div id="receiptModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden items-center justify-center z-[100] p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 border border-slate-100 space-y-4">
            <div class="text-center space-y-1 border-b border-dashed border-slate-200 pb-4">
                <h4 class="font-bold text-slate-800 text-base">MINIMART POS SYSTEM</h4>
                <p class="text-[11px] text-slate-500">ใบเสร็จรับเงิน / Tax Invoice</p>
                <p id="receiptDate" class="text-[11px] font-semibold text-indigo-600 mt-1"></p>
            </div>

            <div id="receiptItemsList" class="space-y-2 text-xs border-b border-dashed border-slate-200 pb-4 max-h-48 overflow-y-auto"></div>

            <div class="space-y-1.5 text-xs border-b border-dashed border-slate-200 pb-4">
                <div class="flex justify-between font-bold text-slate-800 text-sm">
                    <span>ยอดรวมสุทธิ</span>
                    <span id="receiptTotal" class="text-indigo-600">฿0.00</span>
                </div>
                <div id="receiptPaymentDetail" class="flex justify-between text-slate-600 text-xs"></div>
            </div>

            <div class="text-center pt-1 space-y-3">
                <p class="text-[11px] text-slate-400">*** ขอบคุณที่ใช้บริการ ***</p>
                <div class="flex gap-2 no-print">
                    <button onclick="finishReceipt()" type="button" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">ปิดหน้าต่าง</button>
                    <button onclick="printReceipt()" type="button" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-indigo-600/20">🖨️ พิมพ์บิล</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ออกจากระบบ -->
    <div id="logoutModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xs w-full p-5 text-center space-y-4 border border-slate-100">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl mx-auto flex items-center justify-center text-xl font-bold">🚪</div>
            <div>
                <h4 class="font-bold text-slate-800 text-sm">ยืนยันการออกจากระบบ</h4>
                <p class="text-xs text-slate-500 mt-1">คุณต้องการออกจากระบบแคชเชียร์ใช่หรือไม่?</p>
            </div>
            <div class="flex gap-2">
                <button onclick="closeModal('logoutModal')" type="button" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">ยกเลิก</button>
                <a href="../logout.php" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center shadow-md shadow-rose-600/20">ออกจากระบบ</a>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal -->
    <div id="alertModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-xs w-full p-5 text-center space-y-3 border border-slate-100">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl mx-auto flex items-center justify-center text-xl font-bold">ℹ️</div>
            <h4 id="alertTitle" class="font-bold text-slate-800 text-sm">แจ้งเตือน</h4>
            <p id="alertMessage" class="text-xs text-slate-500"></p>
            <button onclick="closeModal('alertModal')" type="button" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">ตกลง</button>
        </div>
    </div>

    <!-- JavaScript ระบบ POS -->
    <script>
        let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
        let currentPaymentMethod = 'cash'; 
        let lastCompletedOrder = null;

        document.addEventListener('DOMContentLoaded', () => {
            renderCart();
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        function updateDateTime() {
            const now = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('currentDateTime').innerText = now.toLocaleDateString('th-TH', options);
        }

        function showCustomAlert(title, message) {
            document.getElementById('alertTitle').innerText = title;
            document.getElementById('alertMessage').innerText = message;
            openModal('alertModal');
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.display = 'flex';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        function saveCart() {
            localStorage.setItem('pos_cart', JSON.stringify(cart));
        }

        function addToCart(product) {
            const existingItem = cart.find(item => item.id === product.id);
            if (existingItem) {
                if (existingItem.qty < product.stock_qty) {
                    existingItem.qty++;
                } else {
                    showCustomAlert('แจ้งเตือน', 'สินค้าในสต็อกหมดแล้ว!');
                    return;
                }
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    stock: parseInt(product.stock_qty),
                    qty: 1
                });
            }
            saveCart();
            renderCart();
        }

        function updateQty(id, change) {
            const item = cart.find(i => i.id === id);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) {
                    cart = cart.filter(i => i.id !== id);
                } else if (item.qty > item.stock) {
                    item.qty = item.stock;
                    showCustomAlert('แจ้งเตือน', 'เกินจำนวนสต็อกที่มี');
                }
            }
            saveCart();
            renderCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            saveCart();
            renderCart();
        }

        function clearCart() {
            cart = [];
            saveCart();
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartItemsContainer');
            const totalQtyEl = document.getElementById('cartTotalQty');
            const totalPriceEl = document.getElementById('cartTotalPrice');
            const checkoutBtn = document.getElementById('checkoutBtn');

            container.innerHTML = '';
            
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 space-y-2">
                        <span class="text-3xl">🛒</span>
                        <p class="text-xs">ยังไม่มีสินค้าในตะกร้า เลือกสินค้าทางซ้ายเพื่อเริ่มขาย</p>
                    </div>
                `;
                totalQtyEl.innerText = '0 ชิ้น';
                totalPriceEl.innerText = '฿0.00';
                checkoutBtn.disabled = true;
                return;
            }

            let totalQty = 0;
            let totalPrice = 0;

            cart.forEach(item => {
                totalQty += item.qty;
                totalPrice += item.price * item.qty;

                const div = document.createElement('div');
                div.className = 'py-3 flex items-center justify-between gap-2';
                div.innerHTML = `
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-slate-800 text-xs truncate">${item.name}</h4>
                        <span class="text-xs text-indigo-600 font-medium">฿${item.price.toFixed(2)}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <button onclick="updateQty(${item.id}, -1)" type="button" class="px-2 py-1 text-slate-500 hover:bg-slate-100 text-xs font-bold">-</button>
                            <span class="px-2.5 text-xs font-bold text-slate-800">${item.qty}</span>
                            <button onclick="updateQty(${item.id}, 1)" type="button" class="px-2 py-1 text-slate-500 hover:bg-slate-100 text-xs font-bold">+</button>
                        </div>
                        <button onclick="removeFromCart(${item.id})" type="button" class="text-rose-400 hover:text-rose-600 text-xs p-1">🗑️</button>
                    </div>
                `;
                container.appendChild(div);
            });

            totalQtyEl.innerText = totalQty + ' ชิ้น';
            totalPriceEl.innerText = '฿' + totalPrice.toFixed(2);
            checkoutBtn.disabled = false;
        }

        function getCartTotal() {
            return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        }

        function openCheckoutModal() {
            const total = getCartTotal();
            document.getElementById('modalTotalPrice').innerText = '฿' + total.toFixed(2);
            
            const summaryList = document.getElementById('modalCartSummaryList');
            summaryList.innerHTML = '';
            cart.forEach(item => {
                const row = document.createElement('div');
                row.className = 'flex justify-between items-center text-slate-700';
                row.innerHTML = `<span>${item.name} (x${item.qty})</span> <span class="font-semibold">฿${(item.price * item.qty).toFixed(2)}</span>`;
                summaryList.appendChild(row);
            });

            setPaymentMethod('cash');
            document.getElementById('cashReceived').value = '';
            document.getElementById('changeAmount').innerText = '฿0.00';
            document.getElementById('confirmPayBtn').disabled = true;
            
            openModal('checkoutModal');
            setTimeout(() => {
                if(currentPaymentMethod === 'cash') document.getElementById('cashReceived').focus();
            }, 100);
        }

        function setPaymentMethod(method) {
            currentPaymentMethod = method;
            const btnCash = document.getElementById('btnMethodCash');
            const btnTransfer = document.getElementById('btnMethodTransfer');
            const cashSection = document.getElementById('cashPaymentSection');
            const transferSection = document.getElementById('transferPaymentSection');
            const confirmBtn = document.getElementById('confirmPayBtn');

            if (method === 'cash') {
                btnCash.className = 'py-2.5 px-4 rounded-xl text-xs font-bold border-2 transition flex items-center justify-center gap-2 border-indigo-600 bg-indigo-50 text-indigo-700';
                btnTransfer.className = 'py-2.5 px-4 rounded-xl text-xs font-bold border-2 transition flex items-center justify-center gap-2 border-slate-200 bg-white text-slate-600';
                cashSection.classList.remove('hidden');
                transferSection.classList.add('hidden');
                calculateChange();
            } else {
                btnTransfer.className = 'py-2.5 px-4 rounded-xl text-xs font-bold border-2 transition flex items-center justify-center gap-2 border-indigo-600 bg-indigo-50 text-indigo-700';
                btnCash.className = 'py-2.5 px-4 rounded-xl text-xs font-bold border-2 transition flex items-center justify-center gap-2 border-slate-200 bg-white text-slate-600';
                transferSection.classList.remove('hidden');
                cashSection.classList.add('hidden');
                confirmBtn.disabled = false; 
            }
        }

        function setCash(amount) {
            document.getElementById('cashReceived').value = amount;
            calculateChange();
        }

        function calculateChange() {
            if (currentPaymentMethod !== 'cash') return;
            const total = getCartTotal();
            const cash = parseFloat(document.getElementById('cashReceived').value) || 0;
            const change = cash - total;
            const changeEl = document.getElementById('changeAmount');
            const confirmBtn = document.getElementById('confirmPayBtn');

            if (cash >= total && total > 0) {
                changeEl.innerText = '฿' + change.toFixed(2);
                changeEl.className = 'text-lg font-extrabold text-emerald-600';
                confirmBtn.disabled = false;
            } else {
                changeEl.innerText = 'เงินสดไม่พอชำระ';
                changeEl.className = 'text-xs font-bold text-rose-500';
                confirmBtn.disabled = true;
            }
        }

        async function processCheckout(e) {
            if (e) e.preventDefault();

            const total = getCartTotal();
            const cash = currentPaymentMethod === 'cash' ? (parseFloat(document.getElementById('cashReceived').value) || 0) : total;
            const change = cash - total;

            // แพ็กข้อมูลส่งไปตัดสต็อกที่ Backend
            try {
                const response = await fetch('checkout_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: cart, total: total, method: currentPaymentMethod, cash: cash })
                });
                const result = await response.json();

                if (!result.success) {
                    showCustomAlert('เกิดข้อผิดพลาด', result.message);
                    return;
                }
            } catch (err) {
                showCustomAlert('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์เพื่อตัดสต็อกได้');
                return;
            }

            lastCompletedOrder = {
                items: [...cart],
                total: total,
                method: currentPaymentMethod === 'cash' ? 'เงินสด' : 'เงินโอน (QR Code)',
                cash: cash,
                change: change,
                date: new Date().toLocaleString('th-TH', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' })
            };

            const checkoutModal = document.getElementById('checkoutModal');
            checkoutModal.classList.remove('flex');
            checkoutModal.classList.add('hidden');
            checkoutModal.style.display = 'none';

            cart = [];
            saveCart();
            renderCart();

            document.getElementById('receiptDate').innerText = 'วันที่: ' + lastCompletedOrder.date;
            document.getElementById('receiptTotal').innerText = '฿' + lastCompletedOrder.total.toFixed(2);
            
            const rList = document.getElementById('receiptItemsList');
            rList.innerHTML = '';
            lastCompletedOrder.items.forEach(i => {
                const row = document.createElement('div');
                row.className = 'flex justify-between text-slate-700 py-0.5';
                row.innerHTML = `<span>${i.name} (x${i.qty})</span> <span class="font-semibold">฿${(i.price * i.qty).toFixed(2)}</span>`;
                rList.appendChild(row);
            });

            const pDetail = document.getElementById('receiptPaymentDetail');
            if (lastCompletedOrder.method === 'เงินสด') {
                pDetail.innerHTML = `
                    <div class="space-y-1 w-full">
                        <div class="flex justify-between"><span>วิธีชำระ: เงินสด</span></div>
                        <div class="flex justify-between"><span>รับเงินสดมา:</span> <span>฿${lastCompletedOrder.cash.toFixed(2)}</span></div>
                        <div class="flex justify-between font-bold text-emerald-600"><span>เงินทอน:</span> <span>฿${lastCompletedOrder.change.toFixed(2)}</span></div>
                    </div>
                `;
            } else {
                pDetail.innerHTML = `
                    <div class="space-y-1 w-full">
                        <div class="flex justify-between"><span>วิธีชำระ: เงินโอน / QR</span></div>
                        <div class="flex justify-between text-emerald-600 font-bold"><span>สถานะ:</span> <span>ชำระเงินสำเร็จ</span></div>
                    </div>
                `;
            }

            setTimeout(() => {
                const receiptModal = document.getElementById('receiptModal');
                receiptModal.classList.remove('flex');
                receiptModal.classList.add('flex');
                receiptModal.style.display = 'flex';
            }, 50);
        }

        function printReceipt() {
            window.print();
        }

        function finishReceipt() {
            const receiptModal = document.getElementById('receiptModal');
            receiptModal.classList.remove('flex');
            receiptModal.classList.add('hidden');
            receiptModal.style.display = 'none';
            location.reload(); 
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'F9') {
                e.preventDefault();
                if (cart.length > 0) {
                    openCheckoutModal();
                }
            }
        });
    </script>
</body>
</html>