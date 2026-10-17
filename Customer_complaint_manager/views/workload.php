<?php
/** @var array<int, array{name: string, count: int}> $workloads */
?>
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