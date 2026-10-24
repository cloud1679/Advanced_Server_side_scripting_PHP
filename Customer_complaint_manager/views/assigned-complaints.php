<?php

/** @var array $technicianOptions */
/** @var string $selectedTechnicianId */
/** @var array $assignedComplaints */
/** @var bool $technicianComplaintPageUnavailable */

?>

<?php if (!$technicianComplaintPageUnavailable): ?>
    <?php if (($_GET['saved'] ?? '') === '1'): ?>
        <p role="status">Complaint update saved.</p>
    <?php endif; ?>

    <?php if (!$technicianOptions): ?>
        <p>No technicians have been added yet.</p>
    <?php else: ?>
        <form method="get">
            <input type="hidden" name="page" value="technician">

            <?php
            selectField(
                ['technician_id' => $selectedTechnicianId],
                'technician_id',
                'Test Technician',
                $technicianOptions
            );
            ?>

            <p>
                <button type="submit">View Assigned Complaints</button>
            </p>
        </form>

        <?php if ($selectedTechnicianId === ''): ?>
            <p>Select a technician to view their assigned complaints.</p>
        <?php elseif (!$assignedComplaints): ?>
            <p>No open complaints are assigned to this technician.</p>
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
                            <td><?= e($record['id']) ?></td>
                            <td><?= e($record['customer']) ?></td>
                            <td><?= e($record['product']) ?></td>
                            <td><?= e($record['category']) ?></td>
                            <td><?= e($record['status']) ?></td>

                            <td>
                                <a href="<?= e(
                                                '?page=update-complaint&id='
                                                    . rawurlencode((string) $record['id'])
                                                    . '&technician_id='
                                                    . rawurlencode($selectedTechnicianId)
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
<?php endif; ?>

<p>
    <a href="?page=technician-dashboard">Back to Dashboard</a>
</p>