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

    private function createObject(array $row)
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

        $employee->setMustChangePassword(
            $row['must_change_password']
        );

        return $employee;
    }

    public function getAll()
    {
        $statement = $this->connection->query(
            'SELECT * FROM employees
             ORDER BY last_name, first_name, employee_id'
        );

        $employees = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $employees[] = $this->createObject($row);
        }

        return $employees;
    }

    public function getById($employeeId)
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM employees
             WHERE employee_id = :employee_id'
        );

        $statement->execute([
            'employee_id' => $employeeId
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createObject($row) : null;
    }

    public function getByUserId($userId)
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM employees
             WHERE user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => trim($userId)
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->createObject($row) : null;
    }

    public function authenticate($userId, $password)
    {
        $employee = $this->getByUserId($userId);

        if ($employee === null) {
            return null;
        }

        if (!password_verify(
            $password,
            $employee->getPasswordHash()
        )) {
            return null;
        }

        if (password_needs_rehash(
            $employee->getPasswordHash(),
            PASSWORD_DEFAULT
        )) {
            $newHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Rehashing preserves any required password change.
            $this->updatePassword(
                $employee->getEmployeeId(),
                $newHash,
                $employee->getMustChangePassword()
            );

            $employee->setPasswordHash($newHash);
        }

        return $employee;
    }

    public function userIdExists($userId)
    {
        return $this->getByUserId($userId) !== null;
    }

    public function emailExists($email, $excludeId = null)
    {
        $sql = 'SELECT COUNT(*)
                FROM employees
                WHERE email = :email';

        $parameters = [
            'email' => trim($email)
        ];

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
                password_hash,
                must_change_password
             ) VALUES (
                :user_id,
                :first_name,
                :last_name,
                :email,
                :phone_extension,
                :employee_level,
                :password_hash,
                :must_change_password
             )'
        );

        $parameters = $employee->toFormArray();

        $parameters['password_hash'] =
            $employee->getPasswordHash();

        $parameters['must_change_password'] =
            $employee->getMustChangePassword() ? 1 : 0;

        $statement->execute($parameters);

        $employee->setEmployeeId(
            $this->connection->lastInsertId()
        );
    }

    public function update(Employee $employee)
    {
        // Ordinary editing does not change User ID or password information.
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

        $parameters['employee_id'] =
            $employee->getEmployeeId();

        $statement->execute($parameters);
    }

    // Expects a hash from password_hash(), not a plain password.
    // A normal password change clears the temporary-password flag.
    public function updatePassword(
        $employeeId,
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
            'UPDATE employees SET
                password_hash = :password_hash,
                must_change_password = :must_change_password
             WHERE employee_id = :employee_id'
        );

        $statement->execute([
            'password_hash' => $passwordHash,
            'must_change_password' => $mustChangePassword ? 1 : 0,
            'employee_id' => $employeeId
        ]);
    }

    // The administrator controller generates and hashes the password.
    public function setTemporaryPassword(
        $employeeId,
        $passwordHash
    ) {
        $this->updatePassword(
            $employeeId,
            $passwordHash,
            true
        );
    }
}
