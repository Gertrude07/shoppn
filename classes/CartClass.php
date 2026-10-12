<?php
require_once __DIR__ . '/../core/db_class.php';

class CartClass extends Database {
    private function getCartColumns() {
        $result = $this->conn->query('SHOW COLUMNS FROM cart');
        $columns = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $columns[$row['Field']] = true;
            }
        }

        if (isset($columns['customer_id'], $columns['product_id'])) {
            return [
                'customer' => 'customer_id',
                'product' => 'product_id',
                'legacy' => false,
            ];
        }

        return [
            'customer' => 'c_id',
            'product' => 'p_id',
            'legacy' => true,
        ];
    }

    public function addToCart($customerId, $productId, $qty = 1) {
        $customerId = (int) $customerId;
        $productId = (int) $productId;
        $qty = (int) $qty;

        if ($customerId < 1 || $productId < 1 || $qty < 1) {
            return false;
        }

        $productStmt = $this->conn->prepare('SELECT product_price FROM products WHERE product_id = ?');
        if (!$productStmt) {
            return false;
        }

        $productStmt->bind_param('i', $productId);
        $productStmt->execute();
        $product = $productStmt->get_result()->fetch_assoc();
        $productStmt->close();

        if (!$product) {
            return false;
        }

        $cartColumns = $this->getCartColumns();
        $customerColumn = $cartColumns['customer'];
        $productColumn = $cartColumns['product'];
        $existingStmt = $this->conn->prepare("SELECT qty FROM cart WHERE {$customerColumn} = ? AND {$productColumn} = ?");
        if (!$existingStmt) {
            return false;
        }

        $existingStmt->bind_param('ii', $customerId, $productId);
        $existingStmt->execute();
        $existingRow = $existingStmt->get_result()->fetch_assoc();
        $existingStmt->close();

        if ($existingRow) {
            $newQty = (int) $existingRow['qty'] + $qty;
            $updateStmt = $this->conn->prepare("UPDATE cart SET qty = ? WHERE {$customerColumn} = ? AND {$productColumn} = ?");
            if (!$updateStmt) {
                return false;
            }

            $updateStmt->bind_param('iii', $newQty, $customerId, $productId);
            $success = $updateStmt->execute();
            $updateStmt->close();
            return $success;
        }

        if ($cartColumns['legacy']) {
            $insertStmt = $this->conn->prepare('INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)');
        } else {
            $insertStmt = $this->conn->prepare('INSERT INTO cart (product_id, customer_id, qty) VALUES (?, ?, ?)');
        }
        if (!$insertStmt) {
            return false;
        }

        if ($cartColumns['legacy']) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $insertStmt->bind_param('isii', $productId, $ipAddress, $customerId, $qty);
        } else {
            $insertStmt->bind_param('iii', $productId, $customerId, $qty);
        }
        $success = $insertStmt->execute();
        $insertStmt->close();

        return $success;
    }

    public function getCartItems($customerId) {
        $customerId = (int) $customerId;
        if ($customerId < 1) {
            return [];
        }

        $cartColumns = $this->getCartColumns();
        $customerColumn = $cartColumns['customer'];
        $productColumn = $cartColumns['product'];
        $stmt = $this->conn->prepare(
            "SELECT c.{$productColumn} AS product_id, c.qty, p.product_title, p.product_image, p.product_price, b.brand_name
             FROM cart c
             INNER JOIN products p ON p.product_id = c.{$productColumn}
             LEFT JOIN brands b ON b.brand_id = p.product_brand
             WHERE c.{$customerColumn} = ?
             ORDER BY c.{$productColumn} ASC"
        );
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $items;
    }

    public function updateQty($customerId, $productId, $qty) {
        $customerId = (int) $customerId;
        $productId = (int) $productId;
        $qty = (int) $qty;

        if ($customerId < 1 || $productId < 1 || $qty < 1) {
            return false;
        }

        $cartColumns = $this->getCartColumns();
        $stmt = $this->conn->prepare("UPDATE cart SET qty = ? WHERE {$cartColumns['customer']} = ? AND {$cartColumns['product']} = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iii', $qty, $customerId, $productId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function removeFromCart($customerId, $productId) {
        $customerId = (int) $customerId;
        $productId = (int) $productId;

        if ($customerId < 1 || $productId < 1) {
            return false;
        }

        $cartColumns = $this->getCartColumns();
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE {$cartColumns['customer']} = ? AND {$cartColumns['product']} = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $customerId, $productId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getCartTotals($customerId) {
        $items = $this->getCartItems($customerId);
        $totalQty = 0;
        $totalAmount = 0.0;

        foreach ($items as $item) {
            $qty = (int) ($item['qty'] ?? 1);
            $unitPrice = (float) ($item['product_price'] ?? 0);
            $totalQty += $qty;
            $totalAmount += $qty * $unitPrice;
        }

        return [
            'items' => $items,
            'qty' => $totalQty,
            'total' => $totalAmount,
        ];
    }

    public function clearCart($customerId) {
        $customerId = (int) $customerId;
        if ($customerId < 1) {
            return false;
        }

        $cartColumns = $this->getCartColumns();
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE {$cartColumns['customer']} = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $customerId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
