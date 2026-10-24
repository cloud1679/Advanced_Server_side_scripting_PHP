<?php

class Complaint
{
    private $complaintId;
    private $customerId;
    private $productId;
    private $categoryId;
    private $description = '';

    public function getComplaintId()
    {
        return $this->complaintId;
    }

    public function setComplaintId($value)
    {
        $this->complaintId = (int) $value;
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function setCustomerId($value)
    {
        $this->customerId = (int) $value;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setProductId($value)
    {
        $this->productId = (int) $value;
    }

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function setCategoryId($value)
    {
        $this->categoryId = (int) $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = trim($value);
    }
}
