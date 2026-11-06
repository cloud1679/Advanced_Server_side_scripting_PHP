<?php

class Employee
{
    private $employeeId;
    private $userId = '';
    private $firstName = '';
    private $lastName = '';
    private $email = '';
    private $phoneNumber = '';
    private $employeeLevel = 'Technician';
    private $passwordHash = '';
    private $mustChangePassword = false;

    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    public function setEmployeeId($value)
    {
        $this->employeeId = $value === null
            ? null
            : (int) $value;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($value)
    {
        $this->userId = trim($value);
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($value)
    {
        $this->firstName = trim($value);
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($value)
    {
        $this->lastName = trim($value);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = trim($value);
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($value)
    {
        $this->phoneNumber = trim($value ?? '');
    }

    public function getEmployeeLevel()
    {
        return $this->employeeLevel;
    }

    public function setEmployeeLevel($value)
    {
        if (!in_array(
            $value,
            ['Technician', 'Administrator'],
            true
        )) {
            throw new InvalidArgumentException(
                'Invalid employee level.'
            );
        }

        $this->employeeLevel = $value;
    }

    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    public function setPasswordHash($value)
    {
        $this->passwordHash = $value;
    }

    public function getMustChangePassword()
    {
        return $this->mustChangePassword;
    }

    public function setMustChangePassword($value)
    {
        $this->mustChangePassword = (bool) $value;
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    // Password information is excluded from ordinary employee forms.
    public function toFormArray()
    {
        return [
            'user_id' => $this->userId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
            'employee_level' => $this->employeeLevel
        ];
    }
}
