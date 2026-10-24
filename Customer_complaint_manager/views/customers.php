<?php
/** @var bool $customerPageUnavailable */
/** @var array<int, Customer> $customers */
?>
<?php if (!$customerPageUnavailable): ?>

    <?php if (($_GET['updated'] ?? '') === '1'): ?>
        <p role="status">Customer information saved.</p>
    <?php endif; ?>

    <?php if (!$customers): ?>
        <p>No customers have been registered yet.</p>
    <?php else: ?>
        <table>
            <caption>Customer List</caption>

            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($customers as $record): ?>
                    <tr>
                        <td><?= e($record->getFullName()) ?></td>
                        <td><?= e($record->getEmail()) ?></td>
                        <td><?= e($record->getPhone()) ?></td>

                        <td>
                            <a href="?page=customer-edit&amp;id=<?= e($record->getCustomerId()) ?>">
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php endif; ?>

<p>
    <a href="?page=admin-dashboard">Back to Dashboard</a>
</p>
