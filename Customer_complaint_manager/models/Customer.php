<?php

class Customer
{
    private $customerId;
    private $firstName = '';
    private $lastName = '';
    private $email = '';
    private $streetAddress = '';
    private $city = '';
    private $state = '';
    private $zipCode = '';
    private $phone = '';
    private $passwordHash = '';
    private $mustChangePassword = false;

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function setCustomerId($value)
    {
        $this->customerId = $value === null
            ? null
            : (int) $value;
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

    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    public function setStreetAddress($value)
    {
        $this->streetAddress = trim($value);
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($value)
    {
        $this->city = trim($value);
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($value)
    {
        $this->state = strtoupper(trim($value));
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function setZipCode($value)
    {
        $this->zipCode = trim($value);
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($value)
    {
        $this->phone = trim($value);
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

    // Password information is excluded from ordinary profile forms.
    public function toFormArray()
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'street_address' => $this->streetAddress,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zipCode,
            'phone' => $this->phone
        ];
    }
}
