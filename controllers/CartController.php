<?php
require_once __DIR__ . '/../classes/CartClass.php';

class CartController {
    protected $model;

    public function __construct() {
        $this->model = new CartClass();
    }

    public function addToCart($customerId, $productId, $qty = 1) {
        return $this->model->addToCart($customerId, $productId, $qty);
    }

    public function getCartItems($customerId) {
        return $this->model->getCartItems($customerId);
    }

    public function updateQty($customerId, $productId, $qty) {
        return $this->model->updateQty($customerId, $productId, $qty);
    }

    public function removeFromCart($customerId, $productId) {
        return $this->model->removeFromCart($customerId, $productId);
    }

    public function getCartTotals($customerId) {
        return $this->model->getCartTotals($customerId);
    }

    public function clearCart($customerId) {
        return $this->model->clearCart($customerId);
    }
}
