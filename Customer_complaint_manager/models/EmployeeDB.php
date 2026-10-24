<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Employee.php';

class EmployeeDB
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    private function createObject($row)
    {
        $employee = new Employee();

        $employee->setEmployeeId($row['employee_id']);
        $employee->setUserId($row['user_id']);
        $employee->setFirstName($row['first_name']);
        $employee->setLastName($row['last_name']);
        $employee->setEmail($row['email']);
        $employee->setPhoneExtension($row['phone_extension']);
        $employee->setEmployeeLevel($row['employee_level']);
        $employee->setPasswordHash($row['password_hash']);

        return $employee;
    }

    public function getAll()
    {
        $statement = $this->connection->query(
            'SELECT * FROM employees
             ORDER BY last_name, first_name, employee_id'
        );

        $employees = [];

        while ($row = $statement->fetch()) {
            $employees[] = $this->createObject($row);
        }

        return $employees;
    }

    public function getByUserId($userId)
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM employees WHERE user_id = :user_id'
        );

        $statement->execute(['user_id' => $userId]);

        $row = $statement->fetch();

        return $row ? $this->createObject($row) : null;
    }

    public function userIdExists($userId)
    {
        return $this->getByUserId($userId) !== null;
    }

    public function emailExists($email, $excludeId = null)
    {
        $sql = 'SELECT COUNT(*) FROM employees WHERE email = :email';
        $parameters = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND employee_id <> :employee_id';
            $parameters['employee_id'] = $excludeId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn() > 0;
    }

    public function insert(Employee $employee)
    {
        $statement = $this->connection->prepare(
            'INSERT INTO employees (
                user_id,
                first_name,
                last_name,
                email,
                phone_extension,
                employee_level,
                password_hash
             ) VALUES (
                :user_id,
                :first_name,
                :last_name,
                :email,
                :phone_extension,
                :employee_level,
                :password_hash
             )'
        );

        $parameters = $employee->toFormArray();
        $parameters['password_hash'] = $employee->getPasswordHash();

        $statement->execute($parameters);

        $employee->setEmployeeId($this->connection->lastInsertId());
    }

    public function update(Employee $employee)
    {
        // User ID and password are deliberately not updated here.
        $statement = $this->connection->prepare(
            'UPDATE employees SET
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone_extension = :phone_extension,
                employee_level = :employee_level
             WHERE employee_id = :employee_id'
        );

        $parameters = $employee->toFormArray();
        unset($parameters['user_id']);

        $parameters['employee_id'] = $employee->getEmployeeId();

        $statement->execute($parameters);
    }
}
