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
                'ip' => null,
                'legacy' => false,
            ];
        }

        return [
            'customer' => 'c_id',
            'product' => 'p_id',
            'ip' => isset($columns['ip']) ? 'ip' : 'ip_add',
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
            "SELECT c.{$productColumn} AS product_id, c.qty, p.product_title, p.product_image, p.product_price, p.product_currency, b.brand_name
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

    // Makes sure the order tables exist with the columns the payment code needs. Older copies of the
    // database (from earlier labs) may be missing them. Existing orders are kept as they are.
    // This runs before any transaction because ALTER TABLE commits implicitly in MySQL.
    private function ensureOrderTables() {
        static $checked = false;
        if ($checked) {
            return;
        }

        $this->conn->query(
            "CREATE TABLE IF NOT EXISTS `orders` (
              `order_id` int NOT NULL AUTO_INCREMENT,
              `customer_id` int NOT NULL,
              `invoice_no` int NOT NULL,
              `reference` varchar(100) DEFAULT NULL,
              `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
              `order_date` date NOT NULL,
              `order_status` varchar(20) NOT NULL DEFAULT 'paid',
              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`order_id`),
              UNIQUE KEY `orders_invoice_unique` (`invoice_no`),
              UNIQUE KEY `orders_reference_unique` (`reference`),
              KEY `fk_orders_customer` (`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->conn->query(
            "CREATE TABLE IF NOT EXISTS `orderdetails` (
              `orderdetail_id` int NOT NULL AUTO_INCREMENT,
              `order_id` int NOT NULL,
              `product_id` int NOT NULL,
              `qty` int NOT NULL,
              PRIMARY KEY (`orderdetail_id`),
              KEY `fk_orderdetails_order` (`order_id`),
              KEY `fk_orderdetails_product` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $existing = [];
        $columns = $this->conn->query('SHOW COLUMNS FROM `orders`');
        while ($columns && ($column = $columns->fetch_assoc())) {
            $existing[strtolower($column['Field'])] = true;
        }

        $additions = [
            'reference' => 'ADD COLUMN `reference` varchar(100) NULL AFTER `invoice_no`',
            'amount' => "ADD COLUMN `amount` decimal(10,2) NOT NULL DEFAULT '0.00'",
            'order_status' => "ADD COLUMN `order_status` varchar(20) NOT NULL DEFAULT 'paid'",
            'created_at' => 'ADD COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ];
        $changes = [];
        foreach ($additions as $name => $definition) {
            if (empty($existing[$name])) {
                $changes[] = $definition;
            }
        }
        if ($changes) {
            $this->conn->query('ALTER TABLE `orders` ' . implode(', ', $changes));
        }

        // Each payment reference may be recorded only once.
        $unique = $this->conn->query("SHOW INDEX FROM `orders` WHERE Column_name = 'reference' AND Non_unique = 0");
        if ($unique && $unique->num_rows === 0) {
            try {
                $this->conn->query('ALTER TABLE `orders` ADD UNIQUE KEY `orders_reference_unique` (`reference`)');
            } catch (Throwable $exception) {
                error_log('Could not add the unique index on orders.reference: ' . $exception->getMessage());
            }
        }

        $checked = true;
    }

    private function generateInvoiceNumber() {
        $stmt = $this->conn->prepare('SELECT 1 FROM orders WHERE invoice_no = ? LIMIT 1');
        if (!$stmt) {
            throw new RuntimeException('Unable to prepare invoice check.');
        }

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $invoiceNumber = mt_rand(100000, 999999);
            $stmt->bind_param('i', $invoiceNumber);
            $stmt->execute();
            $taken = $stmt->get_result()->fetch_row();
            if (!$taken) {
                $stmt->close();
                return $invoiceNumber;
            }
        }

        $stmt->close();
        throw new RuntimeException('Unable to generate a unique invoice number.');
    }

    public function processPayment($customerId, $ip, $reference, $amount) {
        $customerId = (int) $customerId;
        $ip = (string) $ip;
        $reference = trim((string) $reference);
        $amount = (float) $amount;

        if ($customerId < 1 || $reference === '' || $amount < 0) {
            return false;
        }

        try {
            $this->ensureOrderTables();
        } catch (Throwable $exception) {
            error_log('processPayment could not prepare the order tables: ' . $exception->getMessage());
            return false;
        }

        // The cart is keyed by customer_id. The legacy schema is keyed by IP address.
        $cartColumns = $this->getCartColumns();
        $productColumn = $cartColumns['product'];
        $ownerColumn = $cartColumns['legacy'] ? $cartColumns['ip'] : 'customer_id';
        $ownerType = $cartColumns['legacy'] ? 's' : 'i';
        $ownerValue = $cartColumns['legacy'] ? $ip : $customerId;

        $this->conn->begin_transaction();
        try {
            $cartStmt = $this->conn->prepare(
                "SELECT {$productColumn} AS product_id, qty
                 FROM cart
                 WHERE {$ownerColumn} = ?
                 FOR UPDATE"
            );
            if (!$cartStmt) {
                throw new RuntimeException('Unable to prepare cart lookup.');
            }
            $cartStmt->bind_param($ownerType, $ownerValue);
            if (!$cartStmt->execute()) {
                $cartStmt->close();
                throw new RuntimeException('Unable to load cart items.');
            }
            $cartItems = $cartStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $cartStmt->close();

            if (empty($cartItems)) {
                throw new RuntimeException('The cart is empty.');
            }

            $invoiceNumber = $this->generateInvoiceNumber();
            $orderStmt = $this->conn->prepare(
                'INSERT INTO orders (customer_id, invoice_no, reference, amount, order_date, order_status, created_at)
                 VALUES (?, ?, ?, ?, CURRENT_DATE(), \'paid\', NOW())'
            );
            if (!$orderStmt) {
                throw new RuntimeException('Unable to prepare order creation.');
            }
            $orderStmt->bind_param('iisd', $customerId, $invoiceNumber, $reference, $amount);
            if (!$orderStmt->execute()) {
                $orderStmt->close();
                throw new RuntimeException('Unable to create the order.');
            }
            $orderId = (int) $this->conn->insert_id;
            $orderStmt->close();

            $detailStmt = $this->conn->prepare(
                'INSERT INTO orderdetails (order_id, product_id, qty) VALUES (?, ?, ?)'
            );
            if (!$detailStmt) {
                throw new RuntimeException('Unable to prepare order details.');
            }
            foreach ($cartItems as $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) $item['qty'];
                $detailStmt->bind_param('iii', $orderId, $productId, $qty);
                if (!$detailStmt->execute()) {
                    $detailStmt->close();
                    throw new RuntimeException('Unable to save order details.');
                }
            }
            $detailStmt->close();

            $deleteStmt = $this->conn->prepare("DELETE FROM cart WHERE {$ownerColumn} = ?");
            if (!$deleteStmt) {
                throw new RuntimeException('Unable to prepare cart cleanup.');
            }
            $deleteStmt->bind_param($ownerType, $ownerValue);
            if (!$deleteStmt->execute()) {
                $deleteStmt->close();
                throw new RuntimeException('Unable to clear the cart.');
            }
            $deleteStmt->close();

            $this->conn->commit();
            return $orderId;
        } catch (Throwable $exception) {
            $this->conn->rollback();
            error_log('processPayment failed: ' . $exception->getMessage());
            return false;
        }
    }

    public function getOrderIdByReference($customerId, $reference) {
        $customerId = (int) $customerId;
        $reference = trim((string) $reference);
        if ($customerId < 1 || $reference === '') {
            return false;
        }

        try {
            $this->ensureOrderTables();
            $stmt = $this->conn->prepare('SELECT order_id FROM orders WHERE reference = ? AND customer_id = ? LIMIT 1');
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('si', $reference, $customerId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } catch (Throwable $exception) {
            error_log('getOrderIdByReference failed: ' . $exception->getMessage());
            return false;
        }

        return $row ? (int) $row['order_id'] : false;
    }

    public function getOrderById($orderId) {
        try {
            $this->ensureOrderTables();
            return $this->loadOrderById($orderId);
        } catch (Throwable $exception) {
            error_log('getOrderById failed: ' . $exception->getMessage());
            return false;
        }
    }

    private function loadOrderById($orderId) {
        $orderId = (int) $orderId;
        if ($orderId < 1) {
            return false;
        }

        $orderStmt = $this->conn->prepare(
            'SELECT order_id, customer_id, invoice_no, reference, amount, order_date, order_status, created_at
             FROM orders WHERE order_id = ?'
        );
        if (!$orderStmt) {
            return false;
        }
        $orderStmt->bind_param('i', $orderId);
        $orderStmt->execute();
        $order = $orderStmt->get_result()->fetch_assoc();
        $orderStmt->close();

        if (!$order) {
            return false;
        }

        $detailStmt = $this->conn->prepare(
            'SELECT od.product_id, od.qty, p.product_title, p.product_price, p.product_currency
             FROM orderdetails od
             INNER JOIN products p ON p.product_id = od.product_id
             WHERE od.order_id = ?
             ORDER BY p.product_title ASC, od.product_id ASC'
        );
        if (!$detailStmt) {
            return false;
        }
        $detailStmt->bind_param('i', $orderId);
        $detailStmt->execute();
        $order['items'] = $detailStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $detailStmt->close();

        return $order;
    }
}
