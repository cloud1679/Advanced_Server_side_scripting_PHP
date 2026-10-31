<?php

require_once __DIR__ . '/ComplaintNote.php';

class Complaint
{
    private $complaintId;
    private $customerId;
    private $productId;
    private $categoryId;
    private $technicianId;
    private $description = '';
    private $status = 'Open';
    private $createdAt;
    private $resolutionDate;
    private $resolutionNotes;
    private $imagePath;

    private $customerName = '';
    private $productName = '';
    private $categoryName = '';
    private $technicianName = 'Unassigned';

    private $notes = [];

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

    public function getTechnicianId()
    {
        return $this->technicianId;
    }

    public function setTechnicianId($value)
    {
        $this->technicianId = $value === null
            ? null
            : (int) $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = trim($value);
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        if (!in_array($value, ['Open', 'Closed'], true)) {
            throw new InvalidArgumentException(
                'Invalid complaint status.'
            );
        }

        $this->status = $value;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value;
    }

    public function getResolutionDate()
    {
        return $this->resolutionDate;
    }

    public function setResolutionDate($value)
    {
        $this->resolutionDate = $value;
    }

    public function getResolutionNotes()
    {
        return $this->resolutionNotes;
    }

    public function setResolutionNotes($value)
    {
        $this->resolutionNotes = $value;
    }

    public function getImagePath()
    {
        return $this->imagePath;
    }

    public function setImagePath($value)
    {
        $this->imagePath = $value;
    }

    public function getCustomerName()
    {
        return $this->customerName;
    }

    public function setCustomerName($value)
    {
        $this->customerName = $value;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    public function setProductName($value)
    {
        $this->productName = $value;
    }

    public function getCategoryName()
    {
        return $this->categoryName;
    }

    public function setCategoryName($value)
    {
        $this->categoryName = $value;
    }

    public function getTechnicianName()
    {
        return $this->technicianName;
    }

    public function setTechnicianName($value)
    {
        $this->technicianName = $value;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes(array $notes)
    {
        foreach ($notes as $note) {
            if (!$note instanceof ComplaintNote) {
                throw new InvalidArgumentException(
                    'Notes must be ComplaintNote objects.'
                );
            }
        }

        $this->notes = $notes;
    }
}
