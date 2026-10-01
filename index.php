<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

// จัดการการ Login ด้วย PIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $pin = trim($_POST['pin']);

    if (strlen($pin) === 4) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE pin = ?");
        $stmt->execute([$pin]);
        $user = $stmt->fetch();

        if ($user) {
            // เช็คสถานะ (ถ้าเป็น cashier ต้อง approved แล้วเท่านั้น)
            if ($user['role'] === 'cashier' && $user['status'] !== 'approved') {
                $error = 'บัญชีของคุณกำลังรอผู้ดูแลระบบ (Admin) อนุมัติ';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                    exit();
                } else {
                    header('Location: cashier/pos.php');
                    exit();
                }
            }
        } else {
            $error = 'รหัส PIN ไม่ถูกต้อง';
        }
    } else {
        $error = 'กรุณากรอกรหัส PIN 4 หลัก';
    }
}

// จัดการการลงทะเบียนพนักงาน (Cashier Register)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $fullname = trim($_POST['fullname']);
    $pin = trim($_POST['pin']);

    if (!empty($fullname) && strlen($pin) === 4) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (fullname, pin, role, status) VALUES (?, ?, 'cashier', 'pending')");
            $stmt->execute([$fullname, $pin]);
            $success = 'ลงทะเบียนสำเร็จ! กรุณารอ Admin ออนุมัติก่อนเข้าใช้งาน';
        } catch (PDOException $e) {
            $error = 'รหัส PIN นี้ถูกใช้งานแล้ว กรุณาใช้รหัสอื่น';
        }
    } else {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน (PIN ต้องมี 4 หลัก)';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimart POS - Login</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen">

    <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Minimart POS</h1>
            <p class="text-sm text-slate-500">ระบบขายหน้าร้านและจัดการสต็อก</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded-lg text-center"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 text-sm rounded-lg text-center"><?= $success ?></div>
        <?php endif; ?>

        <!-- Form Login -->
        <form method="POST" id="loginForm">
            <input type="hidden" name="action" value="login">
            <div class="mb-6">
                <label class="block text-slate-600 text-sm font-medium mb-2 text-center">กรุณากรอกรหัส PIN 4 หลัก</label>
                <div class="flex justify-center gap-3 mb-4">
                    <input type="password" name="pin" id="pinInput" maxlength="4" readonly 
                           class="w-48 text-center text-3xl tracking-widest py-2 border-2 border-slate-300 rounded-xl focus:outline-none focus:border-indigo-500 bg-slate-50" placeholder="••••">
                </div>
            </div>

            <!-- Keypad -->
            <div class="grid grid-cols-3 gap-3 mb-6 max-w-xs mx-auto">
                <button type="button" onclick="pressNum('1')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">1</button>
                <button type="button" onclick="pressNum('2')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">2</button>
                <button type="button" onclick="pressNum('3')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">3</button>
                <button type="button" onclick="pressNum('4')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">4</button>
                <button type="button" onclick="pressNum('5')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">5</button>
                <button type="button" onclick="pressNum('6')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">6</button>
                <button type="button" onclick="pressNum('7')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">7</button>
                <button type="button" onclick="pressNum('8')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">8</button>
                <button type="button" onclick="pressNum('9')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">9</button>
                <button type="button" onclick="clearPin()" class="bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-4 rounded-xl transition text-sm">ล้าง</button>
                <button type="button" onclick="pressNum('0')" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xl font-bold py-4 rounded-xl transition">0</button>
                <button type="button" onclick="backspacePin()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-4 rounded-xl transition text-sm">ลบ</button>
            </div>
        </form>

        <!-- Toggle Register Button -->
        <div class="text-center border-t pt-4">
            <button onclick="toggleModal()" class="text-sm text-indigo-600 hover:underline font-medium">
                พนักงานใหม่? ขอสิทธิ์ลงทะเบียน (Cashier Register)
            </button>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm p-6 rounded-2xl shadow-xl">
            <h2 class="text-lg font-bold text-slate-800 mb-4 text-center">ขอสิทธิ์ลงทะเบียนพนักงาน</h2>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="mb-4">
                    <label class="block text-slate-600 text-sm mb-1">ชื่อ-นามสกุล</label>
                    <input type="text" name="fullname" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-indigo-500">
                </div>
                <div class="mb-6">
                    <label class="block text-slate-600 text-sm mb-1">กำหนดรหัส PIN 4 หลัก</label>
                    <input type="password" name="pin" maxlength="4" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-indigo-500 tracking-widest text-center text-lg" placeholder="••••">
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="toggleModal()" class="w-1/2 bg-slate-200 hover:bg-slate-300 py-2 rounded-lg text-slate-700 font-medium">ยกเลิก</button>
                    <button type="submit" class="w-1/2 bg-indigo-600 hover:bg-indigo-700 py-2 rounded-lg text-white font-medium">ส่งคำขอ</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const pinInput = document.getElementById('pinInput');

        function pressNum(num) {
            if (pinInput.value.length < 4) {
                pinInput.value += num;
                if (pinInput.value.length === 4) {
                    // ส่งฟอร์มทันทีเมื่อครบ 4 หลัก
                    document.getElementById('loginForm').submit();
                }
            }
        }

        function clearPin() {
            pinInput.value = '';
        }

        function backspacePin() {
            pinInput.value = pinInput.value.slice(0, -1);
        }

        function toggleModal() {
            const modal = document.getElementById('registerModal');
            modal.classList.toggle('hidden');
        }
    </script>
</body>
</html>