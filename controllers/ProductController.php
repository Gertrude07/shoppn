<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController {
    protected $model;

    public function __construct() {
        $this->model = new ProductClass();
    }

    public function addBrand($name) {
        return $this->model->addBrand($name);
    }

    public function getAllBrands() {
        return $this->model->getAllBrands();
    }

    public function getBrandById($id) {
        return $this->model->getBrandById($id);
    }

    public function updateBrand($id, $name) {
        return $this->model->updateBrand($id, $name);
    }

    public function addCategory($name) {
        return $this->model->addCategory($name);
    }

    public function getAllCategories() {
        return $this->model->getAllCategories();
    }

    public function getCategoryById($id) {
        return $this->model->getCategoryById($id);
    }

    public function updateCategory($id, $name) {
        return $this->model->updateCategory($id, $name);
    }

    public function addProduct($cat, $brand, $title, $price, $currency, $desc, $imageFilename, $keywords) {
        return $this->model->addProduct($cat, $brand, $title, $price, $currency, $desc, $imageFilename, $keywords);
    }

    public function updateProduct($id, $cat, $brand, $title, $price, $currency, $desc, $imageFilename, $keywords) {
        return $this->model->updateProduct($id, $cat, $brand, $title, $price, $currency, $desc, $imageFilename, $keywords);
    }

    public function getProductById($id) {
        return $this->model->getProductById($id);
    }

    public function getAllProducts() {
        return $this->model->getAllProducts();
    }

    public function searchProducts($query) {
        return $this->model->searchProducts($query);
    }

    public function deleteBrand($id) {
        return $this->model->deleteBrand($id);
    }

    public function deleteCategory($id) {
        return $this->model->deleteCategory($id);
    }

    public function deleteProduct($id) {
        return $this->model->deleteProduct($id);
    }
}
