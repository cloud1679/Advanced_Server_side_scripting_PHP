<?php
/** @var bool $employeePageUnavailable */
/** @var array<int, Employee> $employees */
?>
<?php if (($_GET['saved'] ?? '') === '1'): ?>
    <p role="status">Employee information saved.</p>
<?php endif; ?>

<p>
    <a href="?page=employee-form">Add Employee</a>
</p>

<?php if (!$employeePageUnavailable): ?>
    <?php if (!$employees): ?>
        <p>No employees have been added yet.</p>
    <?php else: ?>
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
                        <td><?= e($record->getUserId()) ?></td>
                        <td><?= e($record->getFullName()) ?></td>
                        <td><?= e($record->getEmail()) ?></td>
                        <td><?= e($record->getPhoneExtension()) ?></td>
                        <td><?= e($record->getEmployeeLevel()) ?></td>

                        <td>
                            <a href="<?= e(
                                            '?page=employee-form&mode=edit&employee='
                                                . rawurlencode($record->getUserId())
                                        ) ?>">
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>
