<?php
/** @var bool $employeePageUnavailable */
/** @var bool $editingEmployee */
/** @var array<string, string> $values */
?>
<?php if (!$employeePageUnavailable): ?>
    <form method="post">
        <h2>
            <?= $editingEmployee ? 'Update Employee' : 'Add Employee' ?>
        </h2>

        <?php
        inputField(
            $values,
            'user_id',
            'User ID',
            'text',
            50,
            true,
            $editingEmployee
        );
        ?>

        <p>Existing employee User IDs cannot be changed.</p>

        <?php
        inputField($values, 'first_name', 'First Name', 'text', 50);
        inputField($values, 'last_name', 'Last Name', 'text', 50);
        inputField($values, 'email', 'Email Address', 'email', 150);

        inputField(
            $values,
            'phone_extension',
            'Phone Extension',
            'text',
            10,
            false
        );

        selectField(
            $values,
            'employee_level',
            'Employee Level',
            [
                'Technician' => 'Technician',
                'Administrator' => 'Administrator'
            ]
        );
        ?>

        <?php if (!$editingEmployee): ?>
            <?php
            inputField(
                $values,
                'new_password',
                'Initial Password',
                'password',
                72
            );

            inputField(
                $values,
                'confirm_password',
                'Confirm Initial Password',
                'password',
                72
            );
            ?>

            <p>
                Use 8–72 characters with uppercase and lowercase letters,
                a number, and a special character.
            </p>
        <?php endif; ?>

        <?php formActions('Save Employee', 'employees'); ?>
    </form>
<?php else: ?>
    <p><a href="?page=employees">Back to Employees</a></p>
<?php endif; ?>
