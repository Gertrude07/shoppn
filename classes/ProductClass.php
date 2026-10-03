<?php
require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends Database {
    private function ensureProductCurrencyColumn() {
        $result = $this->conn->query("SHOW COLUMNS FROM products LIKE 'product_currency'");

        if ($result && $result->num_rows === 0) {
            $this->conn->query("ALTER TABLE products ADD COLUMN product_currency VARCHAR(10) NOT NULL DEFAULT 'USD' AFTER product_price");
        }
    }

    public function addBrand($name) {
        $stmt = $this->conn->prepare('INSERT INTO brands (brand_name) VALUES (?)');
        $stmt->bind_param('s', $name);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getAllBrands() {
        $result = $this->conn->query('SELECT * FROM brands ORDER BY brand_name ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getBrandById($id) {
        $stmt = $this->conn->prepare('SELECT * FROM brands WHERE brand_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: false;
    }

    public function updateBrand($id, $name) {
        $stmt = $this->conn->prepare('UPDATE brands SET brand_name = ? WHERE brand_id = ?');
        $stmt->bind_param('si', $name, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function addCategory($name) {
        $stmt = $this->conn->prepare('INSERT INTO categories (cat_name) VALUES (?)');
        $stmt->bind_param('s', $name);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getAllCategories() {
        $result = $this->conn->query('SELECT * FROM categories ORDER BY cat_name ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getCategoryById($id) {
        $stmt = $this->conn->prepare('SELECT * FROM categories WHERE cat_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: false;
    }

    public function updateCategory($id, $name) {
        $stmt = $this->conn->prepare('UPDATE categories SET cat_name = ? WHERE cat_id = ?');
        $stmt->bind_param('si', $name, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function addProduct($cat, $brand, $title, $price, $currency, $desc, $imageFilename, $keywords) {
        $this->ensureProductCurrencyColumn();

        $stmt = $this->conn->prepare(
            'INSERT INTO products (product_cat, product_brand, product_title, product_price, product_currency, product_desc, product_image, product_keywords)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iisdssss', $cat, $brand, $title, $price, $currency, $desc, $imageFilename, $keywords);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateProduct($id, $cat, $brand, $title, $price, $currency, $desc, $imageFilename, $keywords) {
        $this->ensureProductCurrencyColumn();

        $stmt = $this->conn->prepare(
            'UPDATE products
               SET product_cat = ?, product_brand = ?, product_title = ?, product_price = ?, product_currency = ?, product_desc = ?, product_image = ?, product_keywords = ?
             WHERE product_id = ?'
        );
        $stmt->bind_param('iisdssssi', $cat, $brand, $title, $price, $currency, $desc, $imageFilename, $keywords, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getProductById($id) {
        $this->ensureProductCurrencyColumn();

        $stmt = $this->conn->prepare(
              'SELECT p.*, p.product_cat AS cat_id, p.product_brand AS brand_id, c.cat_name, b.brand_name
             FROM products p
               INNER JOIN categories c ON p.product_cat = c.cat_id
               INNER JOIN brands b ON p.product_brand = b.brand_id
             WHERE p.product_id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: false;
    }

    public function getAllProducts() {
        $this->ensureProductCurrencyColumn();

        $result = $this->conn->query(
              'SELECT p.*, p.product_cat AS cat_id, p.product_brand AS brand_id, c.cat_name, b.brand_name
             FROM products p
               INNER JOIN categories c ON p.product_cat = c.cat_id
               INNER JOIN brands b ON p.product_brand = b.brand_id
             ORDER BY p.product_title ASC'
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
