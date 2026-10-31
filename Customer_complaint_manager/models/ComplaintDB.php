<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Complaint.php';
require_once __DIR__ . '/ComplaintNote.php';

class ComplaintDB
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function insert(Complaint $complaint)
    {
        $statement = $this->connection->prepare(
            'INSERT INTO complaints (
                customer_id,
                product_id,
                category_id,
                description,
                status
             ) VALUES (
                :customer_id,
                :product_id,
                :category_id,
                :description,
                :status
             )'
        );

        $statement->execute([
            'customer_id' => $complaint->getCustomerId(),
            'product_id' => $complaint->getProductId(),
            'category_id' => $complaint->getCategoryId(),
            'description' => $complaint->getDescription(),
            'status' => 'Open'
        ]);

        $complaint->setComplaintId(
            $this->connection->lastInsertId()
        );
    }

    private function createObject(array $row)
    {
        $complaint = new Complaint();

        $complaint->setComplaintId($row['id']);
        $complaint->setCustomerId($row['customer_id']);
        $complaint->setProductId($row['product_id']);
        $complaint->setCategoryId($row['category_id']);
        $complaint->setTechnicianId($row['technician_id']);
        $complaint->setDescription($row['description']);
        $complaint->setStatus($row['status']);
        $complaint->setCreatedAt($row['created_at']);
        $complaint->setResolutionDate($row['resolution_date']);
        $complaint->setResolutionNotes($row['resolution_notes']);
        $complaint->setImagePath($row['image_path']);
        $complaint->setCustomerName($row['customer']);
        $complaint->setProductName($row['product']);
        $complaint->setCategoryName($row['category']);
        $complaint->setTechnicianName($row['technician']);

        return $complaint;
    }

    private function fetchComplaints($statement)
    {
        $complaints = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $complaints[] = $this->createObject($row);
        }

        return $complaints;
    }

    private function selectSql()
    {
        return "
            SELECT
                c.complaint_id AS id,
                c.customer_id,
                c.technician_id,
                c.product_id,
                c.category_id,
                c.description,
                c.image_path,
                c.status,
                c.created_at,
                c.resolution_date,
                c.resolution_notes,
                CONCAT(u.first_name, ' ', u.last_name) AS customer,
                p.product_name AS product,
                cat.category_name AS category,
                COALESCE(
                    CONCAT(e.first_name, ' ', e.last_name),
                    'Unassigned'
                ) AS technician
            FROM complaints c
            JOIN customers u
                ON u.customer_id = c.customer_id
            JOIN products p
                ON p.product_id = c.product_id
            JOIN complaint_categories cat
                ON cat.category_id = c.category_id
            LEFT JOIN employees e
                ON e.employee_id = c.technician_id
        ";
    }

    public function getByCustomerId($customerId)
    {
        $statement = $this->connection->prepare(
            $this->selectSql()
                . ' WHERE c.customer_id = :customer_id
                    ORDER BY c.created_at DESC, c.complaint_id DESC'
        );

        $statement->execute([
            'customer_id' => $customerId
        ]);

        return $this->fetchComplaints($statement);
    }

    public function getById($complaintId)
    {
        $statement = $this->connection->prepare(
            $this->selectSql()
                . ' WHERE c.complaint_id = :complaint_id'
        );

        $statement->execute([
            'complaint_id' => $complaintId
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $complaint = $this->createObject($row);

        $notesStatement = $this->connection->prepare(
            "SELECT
                n.note_id,
                n.complaint_id,
                n.employee_id,
                n.created_at,
                n.note_text,
                CONCAT(e.first_name, ' ', e.last_name) AS author
             FROM complaint_notes n
             JOIN employees e
                ON e.employee_id = n.employee_id
             WHERE n.complaint_id = :complaint_id
             ORDER BY n.created_at, n.note_id"
        );

        $notesStatement->execute([
            'complaint_id' => $complaintId
        ]);

        $notes = [];

        while ($noteRow = $notesStatement->fetch(PDO::FETCH_ASSOC)) {
            $note = new ComplaintNote();

            $note->setNoteId($noteRow['note_id']);
            $note->setComplaintId($noteRow['complaint_id']);
            $note->setEmployeeId($noteRow['employee_id']);
            $note->setNoteText($noteRow['note_text']);
            $note->setCreatedAt($noteRow['created_at']);
            $note->setAuthorName($noteRow['author']);

            $notes[] = $note;
        }

        $complaint->setNotes($notes);

        return $complaint;
    }

    public function getOpen()
    {
        $statement = $this->connection->query(
            $this->selectSql()
                . " WHERE c.status = 'Open'
                    ORDER BY c.created_at, c.complaint_id"
        );

        return $this->fetchComplaints($statement);
    }

    public function getUnassigned()
    {
        $statement = $this->connection->query(
            $this->selectSql()
                . " WHERE c.status = 'Open'
                    AND c.technician_id IS NULL
                    ORDER BY c.created_at, c.complaint_id"
        );

        return $this->fetchComplaints($statement);
    }

    public function getTechnicians()
    {
        $statement = $this->connection->query(
            "SELECT
                employee_id,
                user_id,
                CONCAT(first_name, ' ', last_name) AS name
             FROM employees
             WHERE employee_level = 'Technician'
             ORDER BY last_name, first_name, employee_id"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTechnicianWorkload()
    {
        $statement = $this->connection->query(
            "SELECT
                e.employee_id,
                CONCAT(e.first_name, ' ', e.last_name) AS name,
                COUNT(c.complaint_id) AS count
             FROM employees e
             LEFT JOIN complaints c
                ON c.technician_id = e.employee_id
                AND c.status = 'Open'
             WHERE e.employee_level = 'Technician'
             GROUP BY
                e.employee_id,
                e.first_name,
                e.last_name
             ORDER BY
                count,
                e.last_name,
                e.first_name,
                e.employee_id"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByTechnicianId($technicianId)
    {
        $statement = $this->connection->prepare(
            $this->selectSql()
                . " WHERE c.technician_id = :technician_id
                    AND c.status = 'Open'
                    ORDER BY c.created_at, c.complaint_id"
        );

        $statement->execute([
            'technician_id' => $technicianId
        ]);

        return $this->fetchComplaints($statement);
    }

    public function saveTechnicianUpdate(
        $complaintId,
        $technicianId,
        $notes,
        $status,
        $resolutionDate,
        $resolutionNotes
    ) {
        $notes = trim($notes);
        $resolutionNotes = trim($resolutionNotes);

        if (!in_array($status, ['Open', 'Closed'], true)) {
            throw new InvalidArgumentException(
                'Select a valid status.'
            );
        }

        if ($status === 'Closed') {
            if ($resolutionNotes === '') {
                throw new InvalidArgumentException(
                    'Resolution notes are required to close a complaint.'
                );
            }

            $validDate = false;

            if (preg_match(
                '/^(\d{4})-(\d{2})-(\d{2})$/',
                $resolutionDate,
                $parts
            )) {
                $validDate = (int) $parts[1] >= 1000
                    && checkdate(
                        (int) $parts[2],
                        (int) $parts[3],
                        (int) $parts[1]
                    );
            }

            if (!$validDate) {
                throw new InvalidArgumentException(
                    'A valid resolution date is required.'
                );
            }
        }

        $this->connection->beginTransaction();

        try {
            $statement = $this->connection->prepare(
                'SELECT technician_id, status
                 FROM complaints
                 WHERE complaint_id = :complaint_id
                 FOR UPDATE'
            );

            $statement->execute([
                'complaint_id' => $complaintId
            ]);

            $complaint = $statement->fetch(PDO::FETCH_ASSOC);

            if (
                !$complaint ||
                (int) $complaint['technician_id'] !== (int) $technicianId ||
                $complaint['status'] !== 'Open'
            ) {
                throw new InvalidArgumentException(
                    'This complaint is closed or is no longer assigned '
                        . 'to the selected technician.'
                );
            }

            $statement = $this->connection->prepare(
                'SELECT employee_level
                 FROM employees
                 WHERE employee_id = :employee_id
                 FOR UPDATE'
            );

            $statement->execute([
                'employee_id' => $technicianId
            ]);

            $employee = $statement->fetch(PDO::FETCH_ASSOC);

            if (
                !$employee ||
                $employee['employee_level'] !== 'Technician'
            ) {
                throw new InvalidArgumentException(
                    'Select a valid technician.'
                );
            }

            if ($notes !== '') {
                $statement = $this->connection->prepare(
                    'INSERT INTO complaint_notes (
                        complaint_id,
                        employee_id,
                        note_text
                     ) VALUES (
                        :complaint_id,
                        :employee_id,
                        :note_text
                     )'
                );

                $statement->execute([
                    'complaint_id' => $complaintId,
                    'employee_id' => $technicianId,
                    'note_text' => $notes
                ]);
            }

            $statement = $this->connection->prepare(
                'UPDATE complaints SET
                    status = :status,
                    resolution_date = :resolution_date,
                    resolution_notes = :resolution_notes
                 WHERE complaint_id = :complaint_id'
            );

            $statement->execute([
                'status' => $status,
                'resolution_date' =>
                $status === 'Closed' ? $resolutionDate : null,
                'resolution_notes' =>
                $status === 'Closed' ? $resolutionNotes : null,
                'complaint_id' => $complaintId
            ]);

            $this->connection->commit();
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }

    public function assignTechnician($complaintId, $technicianId)
    {
        $this->connection->beginTransaction();

        try {
            $statement = $this->connection->prepare(
                'SELECT status
                 FROM complaints
                 WHERE complaint_id = :complaint_id
                 FOR UPDATE'
            );

            $statement->execute([
                'complaint_id' => $complaintId
            ]);

            $complaint = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$complaint || $complaint['status'] !== 'Open') {
                throw new InvalidArgumentException(
                    'The complaint is no longer available for assignment.'
                );
            }

            $statement = $this->connection->prepare(
                'SELECT employee_level
                 FROM employees
                 WHERE employee_id = :employee_id
                 FOR UPDATE'
            );

            $statement->execute([
                'employee_id' => $technicianId
            ]);

            $employee = $statement->fetch(PDO::FETCH_ASSOC);

            if (
                !$employee ||
                $employee['employee_level'] !== 'Technician'
            ) {
                throw new InvalidArgumentException(
                    'Select an employee whose level is Technician.'
                );
            }

            $statement = $this->connection->prepare(
                'UPDATE complaints
                 SET technician_id = :technician_id
                 WHERE complaint_id = :complaint_id'
            );

            $statement->execute([
                'technician_id' => $technicianId,
                'complaint_id' => $complaintId
            ]);

            $this->connection->commit();
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }
}
