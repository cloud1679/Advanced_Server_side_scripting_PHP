<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Product.php';

class ProductDB
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function getAll()
    {
        $statement = $this->connection->query(
            'SELECT product_id, product_name, description
             FROM products
             ORDER BY product_name'
        );

        $products = [];

        while ($row = $statement->fetch()) {
            $product = new Product();

            $product->setProductId($row['product_id']);
            $product->setProductName($row['product_name']);
            $product->setDescription($row['description']);

            $products[] = $product;
        }

        return $products;
    }
}
