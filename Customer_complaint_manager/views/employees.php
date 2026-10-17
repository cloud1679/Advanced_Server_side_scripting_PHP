<?php
/** @var array<string, array<string, string>> $employees */
?>
<p>
    <a href="?page=employee-form">Add Employee</a>
</p>

<table>
    <caption>Employee List</caption>

    <thead>
        <tr>
            <th scope="col">User ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Extension</th>
            <th scope="col">Level</th>
            <th scope="col">Action</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($employees as $record): ?>
            <tr>
                <td><?= e($record['user_id']) ?></td>

                <td>
                    <?= e($record['first_name']) ?>
                    <?= e($record['last_name']) ?>
                </td>

                <td><?= e($record['email']) ?></td>
                <td><?= e($record['phone_extension']) ?></td>
                <td><?= e($record['employee_level']) ?></td>

                <td>
                    <a href="?page=employee-form&amp;mode=edit&amp;employee=<?= e($record['user_id']) ?>">
                        Edit
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>