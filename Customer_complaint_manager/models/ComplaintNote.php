<?php

class ComplaintNote
{
    private $noteId;
    private $complaintId;
    private $employeeId;
    private $noteText = '';
    private $createdAt;
    private $authorName = '';

    public function getNoteId()
    {
        return $this->noteId;
    }

    public function setNoteId($value)
    {
        $this->noteId = (int) $value;
    }

    public function getComplaintId()
    {
        return $this->complaintId;
    }

    public function setComplaintId($value)
    {
        $this->complaintId = (int) $value;
    }

    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    public function setEmployeeId($value)
    {
        $this->employeeId = (int) $value;
    }

    public function getNoteText()
    {
        return $this->noteText;
    }

    public function setNoteText($value)
    {
        $this->noteText = trim($value);
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value;
    }

    public function getAuthorName()
    {
        return $this->authorName;
    }

    public function setAuthorName($value)
    {
        $this->authorName = $value;
    }
}
