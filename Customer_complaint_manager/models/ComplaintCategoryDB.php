<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ComplaintCategory.php';

class ComplaintCategoryDB
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function getAll()
    {
        $statement = $this->connection->query(
            'SELECT category_id, category_name
             FROM complaint_categories
             ORDER BY category_name'
        );

        $categories = [];

        while ($row = $statement->fetch()) {
            $category = new ComplaintCategory();

            $category->setCategoryId($row['category_id']);
            $category->setCategoryName($row['category_name']);

            $categories[] = $category;
        }

        return $categories;
    }
}
