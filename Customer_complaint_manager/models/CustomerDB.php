<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Customer.php';

class CustomerDB
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    private function createObject($row)
    {
        $customer = new Customer();

        $customer->setCustomerId($row['customer_id']);
        $customer->setFirstName($row['first_name']);
        $customer->setLastName($row['last_name']);
        $customer->setEmail($row['email']);
        $customer->setStreetAddress($row['street_address']);
        $customer->setCity($row['city']);
        $customer->setState($row['state']);
        $customer->setZipCode($row['zip_code']);
        $customer->setPhone($row['phone']);
        $customer->setPasswordHash($row['password_hash']);

        return $customer;
    }

    public function getAll()
    {
        $statement = $this->connection->query(
            'SELECT * FROM customers
             ORDER BY last_name, first_name, customer_id'
        );

        $customers = [];

        while ($row = $statement->fetch()) {
            $customers[] = $this->createObject($row);
        }

        return $customers;
    }

    public function getById($customerId)
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM customers
             WHERE customer_id = :customer_id'
        );

        $statement->execute([
            'customer_id' => $customerId
        ]);

        $row = $statement->fetch();

        return $row ? $this->createObject($row) : null;
    }

    public function emailExists($email, $excludeId = null)
    {
        $sql = 'SELECT COUNT(*) FROM customers WHERE email = :email';
        $parameters = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND customer_id <> :customer_id';
            $parameters['customer_id'] = $excludeId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn() > 0;
    }

    public function insert(Customer $customer)
    {
        $statement = $this->connection->prepare(
            'INSERT INTO customers (
                email,
                first_name,
                last_name,
                street_address,
                city,
                state,
                zip_code,
                phone,
                password_hash
             ) VALUES (
                :email,
                :first_name,
                :last_name,
                :street_address,
                :city,
                :state,
                :zip_code,
                :phone,
                :password_hash
             )'
        );

        $parameters = $customer->toFormArray();
        $parameters['password_hash'] = $customer->getPasswordHash();

        $statement->execute($parameters);

        $customer->setCustomerId(
            $this->connection->lastInsertId()
        );
    }

    public function update(Customer $customer)
    {
        $statement = $this->connection->prepare(
            'UPDATE customers SET
                email = :email,
                first_name = :first_name,
                last_name = :last_name,
                street_address = :street_address,
                city = :city,
                state = :state,
                zip_code = :zip_code,
                phone = :phone
             WHERE customer_id = :customer_id'
        );

        $parameters = $customer->toFormArray();
        $parameters['customer_id'] = $customer->getCustomerId();

        $statement->execute($parameters);
    }
}
