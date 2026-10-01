<?php
require_once '../db.php';
include '../includes/admin_header.php';

$success = '';
$error = '';

// จัดการเพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = 'เพิ่มหมวดหมู่สำเร็จ!';
        } catch (PDOException $e) {
            $error = 'ชื่อหมวดหมู่นี้มีอยู่แล้ว';
        }
    } else {
        $error = 'กรุณากรอกชื่อหมวดหมู่';
    }
}

// จัดการลบหมวดหมู่
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: categories.php');
        exit();
    } catch (PDOException $e) {
        $error = 'ไม่สามารถลบหมวดหมู่นี้ได้เนื่องจากมีสินค้าใช้งานอยู่';
    }
}

// ดึงรายการหมวดหมู่ทั้งหมด
$stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count FROM categories c ORDER BY c.id DESC");
$categories = $stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-800">🏷️ จัดการหมวดหมู่สินค้า</h2>
    </div>

    <?php if ($success): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-xl text-sm"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-xl text-sm"><?= $error ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- ฟอร์มเพิ่มหมวดหมู่ -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">เพิ่มหมวดหมู่ใหม่</h3>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm text-slate-600 mb-1 font-medium">ชื่อหมวดหมู่</label>
                    <input type="text" name="name" required placeholder="เช่น เครื่องดื่ม, ขนม" 
                           class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:border-indigo-500 text-sm">
                </div>
                <button type="submit" name="add_category" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition text-sm shadow-lg shadow-indigo-600/20">
                    + บันทึกหมวดหมู่
                </button>
            </form>
        </div>

        <!-- ตารางแสดงรายการหมวดหมู่ -->
        <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase font-semibold">
                        <th class="p-4">#</th>
                        <th class="p-4">ชื่อหมวดหมู่</th>
                        <th class="p-4 text-center">จำนวนสินค้า</th>
                        <th class="p-4 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $index => $cat): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-4 text-slate-500"><?= $index + 1 ?></td>
                                <td class="p-4 font-medium text-slate-800"><?= htmlspecialchars($cat['name']) ?></td>
                                <td class="p-4 text-center">
                                    <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <?= $cat['product_count'] ?> รายการ
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="categories.php?delete=<?= $cat['id'] ?>" onclick="return confirm('ยืนยันการลบหมวดหมู่นี้?')" class="text-red-500 hover:text-red-700 font-medium text-xs bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                                        ลบ
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="p-6 text-center text-slate-400">ยังไม่มีข้อมูลหมวดหมู่สินค้า</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// ปิดแท็ก main และ body จาก admin_header.php
echo "</main></div></body></html>";
?>