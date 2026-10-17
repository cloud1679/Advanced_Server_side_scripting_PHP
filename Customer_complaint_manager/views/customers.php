<?php
/** @var array<string, string> $customer */
?>
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
        <tr>
            <td>
                <?= e($customer['first_name']) ?>
                <?= e($customer['last_name']) ?>
            </td>

            <td><?= e($customer['email']) ?></td>
            <td><?= e($customer['phone']) ?></td>

            <td>
                <a href="?page=customer-edit">Edit</a>
            </td>
        </tr>
    </tbody>
</table>

<p>
    <a href="?page=admin-dashboard">Back to Dashboard</a>
</p>