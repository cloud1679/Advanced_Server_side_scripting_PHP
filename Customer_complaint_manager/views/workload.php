<?php

/** @var array $workloads */
/** @var bool $adminComplaintPageUnavailable */

?>

<?php if (!$adminComplaintPageUnavailable): ?>
    <?php if (!$workloads): ?>
        <p>No technicians have been added yet.</p>
    <?php else: ?>
        <table>
            <caption>Open Complaints by Technician</caption>

            <thead>
                <tr>
                    <th scope="col">Technician</th>
                    <th scope="col">Open Complaints</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($workloads as $workload): ?>
                    <tr>
                        <td><?= e($workload['name']) ?></td>
                        <td><?= e($workload['count']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>