<?php

/** @var array<string, string> $values */
/** @var array $complaintOptions */
/** @var array $technicians */
/** @var bool $adminComplaintPageUnavailable */

?>

<?php if (!$adminComplaintPageUnavailable): ?>
    <?php if (!$complaintOptions): ?>
        <p>No open complaints are available for assignment.</p>

        <p>
            <a href="?page=admin-dashboard">Back to Dashboard</a>
        </p>
    <?php elseif (!$technicians): ?>
        <p>
            Add an employee with the Technician level
            before assigning complaints.
        </p>

        <p>
            <a href="?page=employees">Manage Employees</a>
        </p>

        <p>
            <a href="?page=admin-dashboard">Back to Dashboard</a>
        </p>
    <?php else: ?>
        <form method="post">
            <?php
            selectField(
                $values,
                'complaint_id',
                'Complaint',
                $complaintOptions
            );

            selectField(
                $values,
                'technician_id',
                'Technician',
                $technicians
            );

            formActions('Assign Complaint', 'admin-dashboard');
            ?>
        </form>
    <?php endif; ?>
<?php else: ?>
    <p>
        <a href="?page=admin-dashboard">Back to Dashboard</a>
    </p>
<?php endif; ?>