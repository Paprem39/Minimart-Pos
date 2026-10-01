<?php
require_once '../db.php';

// จัดการลบสินค้า (ย้ายขึ้นมาไว้ด้านบนสุด ก่อนจะ include header เพื่อป้องกัน Header already sent error)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: products.php?msg=deleted');
        exit();
    } catch (PDOException $e) {
        header('Location: products.php?error=delete_failed');
        exit();
    }
}

include '../includes/admin_header.php';

$success = '';
$error = '';

// เช็คข้อความแจ้งเตือนจาก URL Query String
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $success = 'ลบสินค้าสำเร็จ!';
}
if (isset($_GET['error']) && $_GET['error'] === 'delete_failed') {
    $error = 'ไม่สามารถลบสินค้านี้ได้เนื่องจากมีประวัติการขายในระบบ';
}

// 1. จัดการเพิ่มสินค้าใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name         = trim($_POST['name']);
    $category_id  = $_POST['category_id'];
    $price        = $_POST['price'];
    $cost         = $_POST['cost'];
    $stock_qty    = $_POST['stock_qty'];
    $min_stock    = $_POST['min_stock'] ?? 5;
    $received_date = $_POST['received_date'] ?: date('Y-m-d');
    $expire_date  = !empty($_POST['expire_date']) ? $_POST['expire_date'] : null;

    if (!empty($name) && !empty($category_id) && $price >= 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, cost, stock_qty, min_stock, received_date, expire_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category_id, $price, $cost, $stock_qty, $min_stock, $received_date, $expire_date]);
            $success = 'เพิ่มสินค้าสำเร็จ!';
        } catch (PDOException $e) {
            $error = 'เกิดข้อผิดพลาดในการบันทึกสินค้า: ' . $e->getMessage();
        }
    } else {
        $error = 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน';
    }
}

// 2. จัดการแก้ไขสินค้า (Update Product)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id           = $_POST['product_id'];
    $name         = trim($_POST['name']);
    $category_id  = $_POST['category_id'];
    $price        = $_POST['price'];
    $cost         = $_POST['cost'];
    $stock_qty    = $_POST['stock_qty'];
    $min_stock    = $_POST['min_stock'] ?? 5;
    $received_date = $_POST['received_date'] ?: date('Y-m-d');
    $expire_date  = !empty($_POST['expire_date']) ? $_POST['expire_date'] : null;

    if (!empty($id) && !empty($name) && !empty($category_id) && $price >= 0) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, cost = ?, stock_qty = ?, min_stock = ?, received_date = ?, expire_date = ? WHERE id = ?");
            $stmt->execute([$name, $category_id, $price, $cost, $stock_qty, $min_stock, $received_date, $expire_date, $id]);
            $success = 'แก้ไขข้อมูลสินค้าสำเร็จ!';
        } catch (PDOException $e) {
            $error = 'เกิดข้อผิดพลาดในการแก้ไขสินค้า: ' . $e->getMessage();
        }
    } else {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
}

// ดึงรายการหมวดหมู่สำหรับใส่ใน Dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// ดึงรายการสินค้าทั้งหมด พร้อมชื่อหมวดหมู่
$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
")->fetchAll();
?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-slate-800">📦 จัดการสินค้า & สต็อก</h2>
        <button onclick="toggleModal('addProductModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm shadow-lg shadow-indigo-600/25 flex items-center gap-2">
            <span>+</span> เพิ่มสินค้าใหม่
        </button>
    </div>

    <?php if ($success): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm flex items-center gap-3">
            <span class="text-emerald-500 font-bold">✓</span> <?= $success ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-sm flex items-center gap-3">
            <span class="text-rose-500 font-bold">✕</span> <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- ตารางแสดงรายการสินค้า -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-500 text-xs uppercase font-semibold tracking-wider">
                        <th class="p-4 pl-6">#</th>
                        <th class="p-4">ชื่อสินค้า</th>
                        <th class="p-4">หมวดหมู่</th>
                        <th class="p-4 text-right">ราคาขาย</th>
                        <th class="p-4 text-right">ต้นทุน</th>
                        <th class="p-4 text-center">คงเหลือ (สต็อก)</th>
                        <th class="p-4 text-center">วันที่รับเข้า</th>
                        <th class="p-4 text-center">วันหมดอายุ</th>
                        <th class="p-4 text-center pr-6">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $index => $p): ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-4 pl-6 text-slate-400 font-medium"><?= $index + 1 ?></td>
                                <td class="p-4 font-semibold text-slate-800"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="p-4 text-slate-600">
                                    <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-xl text-xs font-medium">
                                        <?= htmlspecialchars($p['category_name']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right font-semibold text-indigo-600">฿<?= number_format($p['price'], 2) ?></td>
                                <td class="p-4 text-right text-slate-500">฿<?= number_format($p['cost'], 2) ?></td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm <?= $p['stock_qty'] <= $p['min_stock'] ? 'bg-rose-100 text-rose-600 border border-rose-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' ?>">
                                        <?= $p['stock_qty'] ?> ชิ้น
                                    </span>
                                </td>
                                <td class="p-4 text-center text-slate-500 text-xs"><?= $p['received_date'] ?></td>
                                <td class="p-4 text-center text-xs">
                                    <?php if ($p['expire_date']): ?>
                                        <span class="<?= $p['expire_date'] <= date('Y-m-d') ? 'text-rose-600 font-bold bg-rose-50 px-2 py-1 rounded-lg' : 'text-slate-600' ?>">
                                            <?= $p['expire_date'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center pr-6 space-x-1">
                                    <button onclick='openEditModal(<?= json_encode($p) ?>)' class="text-indigo-600 hover:text-indigo-800 font-medium text-xs bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-xl transition">
                                        แก้ไข
                                    </button>
                                    <button onclick="openDeleteModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>')" class="text-rose-500 hover:text-rose-700 font-medium text-xs bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-xl transition">
                                        ลบ
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="p-12 text-center text-slate-400">ยังไม่มีรายการสินค้าในระบบ กดปุ่ม "เพิ่มสินค้าใหม่" ด้านบนได้เลย</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal เพิ่มสินค้า -->
<div id="addProductModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 border border-slate-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-800">📦 เพิ่มสินค้าใหม่เข้าสต็อก</h3>
            <button onclick="toggleModal('addProductModal')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm text-slate-600 mb-1 font-medium">ชื่อสินค้า</label>
                <input type="text" name="name" required placeholder="เช่น มาม่า รสต้มยำกุ้ง" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">หมวดหมู่สินค้า</label>
                    <select name="category_id" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm bg-white">
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">จำนวนสต็อกเริ่มต้น</label>
                    <input type="number" name="stock_qty" required value="10" min="0" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">ราคาขาย (บาท)</label>
                    <input type="number" step="0.01" name="price" required placeholder="0.00" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">ต้นทุน (บาท)</label>
                    <input type="number" step="0.01" name="cost" required placeholder="0.00" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">วันที่รับเข้าสต็อก</label>
                    <input type="date" name="received_date" value="<?= date('Y-m-d') ?>" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">วันหมดอายุ (ถ้ามี)</label>
                    <input type="date" name="expire_date" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-600 mb-1 font-medium">แจ้งเตือนสต็อกขั้นต่ำ (Min Stock)</label>
                <input type="number" name="min_stock" value="5" min="1" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="toggleModal('addProductModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition">ยกเลิก</button>
                <button type="submit" name="add_product" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition shadow-lg shadow-indigo-600/25">บันทึกสินค้า</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal แก้ไขสินค้า -->
<div id="editProductModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 border border-slate-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-800">✏️ แก้ไขข้อมูลสินค้า</h3>
            <button onclick="toggleModal('editProductModal')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="product_id" id="edit_id">
            <div>
                <label class="block text-sm text-slate-600 mb-1 font-medium">ชื่อสินค้า</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">หมวดหมู่สินค้า</label>
                    <select name="category_id" id="edit_category_id" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm bg-white">
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">จำนวนสต็อกคงเหลือ</label>
                    <input type="number" name="stock_qty" id="edit_stock_qty" required min="0" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">ราคาขาย (บาท)</label>
                    <input type="number" step="0.01" name="price" id="edit_price" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">ต้นทุน (บาท)</label>
                    <input type="number" step="0.01" name="cost" id="edit_cost" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">วันที่รับเข้าสต็อก</label>
                    <input type="date" name="received_date" id="edit_received_date" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1 font-medium">วันหมดอายุ (ถ้ามี)</label>
                    <input type="date" name="expire_date" id="edit_expire_date" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-600 mb-1 font-medium">แจ้งเตือนสต็อกขั้นต่ำ (Min Stock)</label>
                <input type="number" name="min_stock" id="edit_min_stock" value="5" min="1" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="toggleModal('editProductModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition">ยกเลิก</button>
                <button type="submit" name="edit_product" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition shadow-lg shadow-indigo-600/25">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal ยืนยันการลบสินค้า (ดีไซน์สวยงามแทนหน้าจอบราว์เซอร์เดิม) -->
<div id="deleteProductModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 border border-slate-100 text-center space-y-4">
        <div class="w-12 h-12 bg-rose-100 text-rose-500 rounded-2xl mx-auto flex items-center justify-center text-xl font-bold">
            🗑️
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">ยืนยันการลบสินค้า</h3>
            <p class="text-slate-500 text-sm mt-1">คุณต้องการลบสินค้า <span id="delete_product_name" class="font-semibold text-slate-700"></span> ใช่หรือไม่?</p>
        </div>
        <div class="flex gap-2 pt-2">
            <button type="button" onclick="toggleModal('deleteProductModal')" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition">ยกเลิก</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition shadow-lg shadow-rose-600/25 flex items-center justify-center">ลบข้อมูล</a>
        </div>
    </div>
</div>

<script>
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    } else {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
}

function openEditModal(product) {
    document.getElementById('edit_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_cost').value = product.cost;
    document.getElementById('edit_stock_qty').value = product.stock_qty;
    document.getElementById('edit_min_stock').value = product.min_stock;
    document.getElementById('edit_received_date').value = product.received_date;
    document.getElementById('edit_expire_date').value = product.expire_date || '';
    
    toggleModal('editProductModal');
}

function openDeleteModal(id, name) {
    document.getElementById('delete_product_name').innerText = '"' + name + '"';
    document.getElementById('confirmDeleteBtn').href = 'products.php?delete=' + id;
    toggleModal('deleteProductModal');
}
</script>

<?php 
echo "</main></div></body></html>";
?>