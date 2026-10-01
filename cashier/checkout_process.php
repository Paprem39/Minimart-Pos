<?php
require_once '../db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data) && isset($data['items'])) {
    try {
        $pdo->beginTransaction();

        $totalAmount = 0;
        $totalCost = 0;
        $processedItems = [];

        // ตรวจสอบสต็อกและคำนวณต้นทุน/ราคาขายรวม
        foreach ($data['items'] as $item) {
            $productId = $item['id'];
            $buyQty = intval($item['qty']);

            $stmtCheck = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmtCheck->execute([$productId]);
            $product = $stmtCheck->fetch();

            if (!$product || intval($product['stock_qty']) < $buyQty) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'สินค้า ' . ($product['name'] ?? '') . ' สต็อกไม่พอ']);
                exit;
            }

            $price = floatval($product['price']);
            $cost = floatval($product['cost']); 
            
            $itemTotal = $price * $buyQty;
            $itemCostTotal = $cost * $buyQty;

            $totalAmount += $itemTotal;
            $totalCost += $itemCostTotal;

            $processedItems[] = [
                'id' => $productId,
                'qty' => $buyQty,
                'price' => $price,
                'cost' => $cost,
                'total' => $itemTotal,
                'profit' => $itemTotal - $itemCostTotal
            ];
        }

        $totalProfit = $totalAmount - $totalCost;
        $paymentMethod = $data['method'] ?? 'cash';
        $cashReceived = isset($data['cash']) ? floatval($data['cash']) : $totalAmount;
        $changeAmount = $cashReceived - $totalAmount;

        // 1. บันทึกลงตาราง sales (หัวบิล)
        $stmtSale = $pdo->prepare("INSERT INTO sales (total_amount, total_cost, total_profit, payment_method, cash_received, change_amount) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtSale->execute([$totalAmount, $totalCost, $totalProfit, $paymentMethod, $cashReceived, $changeAmount]);
        $saleId = $pdo->lastInsertId();

        // 2. บันทึกรายละเอียดสินค้า และตัดสต็อก
        $stmtDetail = $pdo->prepare("INSERT INTO sale_details (sale_id, product_id, quantity, unit_price, unit_cost, total_price, total_profit) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");

        foreach ($processedItems as $item) {
            $stmtDetail->execute([
                $saleId, 
                $item['id'], 
                $item['qty'], 
                $item['price'], 
                $item['cost'], 
                $item['total'], 
                $item['profit']
            ]);

            $stmtUpdateStock->execute([$item['qty'], $item['id']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'บันทึกการขายและตัดสต็อกสำเร็จ']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลสินค้า']);
}