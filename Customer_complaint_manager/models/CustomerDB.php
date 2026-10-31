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

    private function createObject(array $row)
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

        $customer->setMustChangePassword(
            $row['must_change_password']
        );

        return $customer;
    }

    public function getAll()
    {
        $statement = $this->connection->query(
            'SELECT * FROM customers
             ORDER BY last_name, first_name, customer_id'
        );

        $customers = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
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

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createObject($row) : null;
    }

    public function getByEmail($email)
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM customers
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute([
            'email' => trim($email)
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createObject($row) : null;
    }

    public function authenticate($email, $password)
    {
        $customer = $this->getByEmail($email);

        if ($customer === null) {
            return null;
        }

        if (!password_verify(
            $password,
            $customer->getPasswordHash()
        )) {
            return null;
        }

        if (password_needs_rehash(
            $customer->getPasswordHash(),
            PASSWORD_DEFAULT
        )) {
            $newHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Rehashing must preserve any required password change.
            $this->updatePassword(
                $customer->getCustomerId(),
                $newHash,
                $customer->getMustChangePassword()
            );

            $customer->setPasswordHash($newHash);
        }

        return $customer;
    }

    public function emailExists($email, $excludeId = null)
    {
        $sql = 'SELECT COUNT(*)
                FROM customers
                WHERE email = :email';

        $parameters = [
            'email' => trim($email)
        ];

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
                password_hash,
                must_change_password
             ) VALUES (
                :email,
                :first_name,
                :last_name,
                :street_address,
                :city,
                :state,
                :zip_code,
                :phone,
                :password_hash,
                :must_change_password
             )'
        );

        $parameters = $customer->toFormArray();

        $parameters['password_hash'] =
            $customer->getPasswordHash();

        $parameters['must_change_password'] =
            $customer->getMustChangePassword() ? 1 : 0;

        $statement->execute($parameters);

        $customer->setCustomerId(
            $this->connection->lastInsertId()
        );
    }

    public function update(Customer $customer)
    {
        // Profile editing does not change password information.
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

        $parameters['customer_id'] =
            $customer->getCustomerId();

        $statement->execute($parameters);
    }

    // Expects a hash from password_hash(), not a plain password.
    // A normal password change clears the temporary-password flag.
    public function updatePassword(
        $customerId,
        $passwordHash,
        $mustChangePassword = false
    ) {
        $hashInformation = is_string($passwordHash)
            ? password_get_info($passwordHash)
            : [];

        if (
            ($hashInformation['algoName'] ?? 'unknown')
            === 'unknown'
        ) {
            throw new InvalidArgumentException(
                'A valid password hash is required.'
            );
        }

        $statement = $this->connection->prepare(
            'UPDATE customers SET
                password_hash = :password_hash,
                must_change_password = :must_change_password
             WHERE customer_id = :customer_id'
        );

        $statement->execute([
            'password_hash' => $passwordHash,
            'must_change_password' => $mustChangePassword ? 1 : 0,
            'customer_id' => $customerId
        ]);
    }

    // The administrator controller generates and hashes the password.
    public function setTemporaryPassword(
        $customerId,
        $passwordHash
    ) {
        $this->updatePassword(
            $customerId,
            $passwordHash,
            true
        );
    }
}
