<?php

class ComplaintCategory
{
    private $categoryId;
    private $categoryName = '';

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function setCategoryId($value)
    {
        $this->categoryId = (int) $value;
    }

    public function getCategoryName()
    {
        return $this->categoryName;
    }

    public function setCategoryName($value)
    {
        $this->categoryName = trim($value);
    }
}
