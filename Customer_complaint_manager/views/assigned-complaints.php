<?php

/** @var Complaint[] $assignedComplaints */
/** @var bool $technicianComplaintPageUnavailable */
?>

<?php if (!$technicianComplaintPageUnavailable): ?>
    <?php if (($_GET['saved'] ?? '') === '1'): ?>
        <p role="status">Complaint update saved.</p>
    <?php endif; ?>

    <?php if (!$assignedComplaints): ?>
        <p>No open complaints are assigned to you.</p>
    <?php else: ?>
        <table>
            <caption>Assigned Open Complaints</caption>

            <thead>
                <tr>
                    <th scope="col">Complaint ID</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Product / Service</th>
                    <th scope="col">Category</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($assignedComplaints as $record): ?>
                    <tr>
                        <td><?= e($record->getComplaintId()) ?></td>
                        <td><?= e($record->getCustomerName()) ?></td>
                        <td><?= e($record->getProductName()) ?></td>
                        <td><?= e($record->getCategoryName()) ?></td>
                        <td><?= e($record->getStatus()) ?></td>

                        <td>
                            <a href="<?= e(
                                            '?page=update-complaint&id='
                                                . rawurlencode(
                                                    (string) $record->getComplaintId()
                                                )
                                        ) ?>">
                                Update
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

<p>
    <a href="?page=technician-dashboard">Back to Dashboard</a>
</p>