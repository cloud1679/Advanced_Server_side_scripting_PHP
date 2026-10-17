<?php
/** @var array<string, string> $values */
?>
<form method="post">
    <?php
    inputField(
        $values,
        'current_password',
        'Current Password',
        'password',
        72
    );

    inputField(
        $values,
        'new_password',
        'New Password',
        'password',
        72
    );

    inputField(
        $values,
        'confirm_password',
        'Confirm New Password',
        'password',
        72
    );
    ?>

    <p>
        Use 8–72 characters with uppercase and lowercase letters,
        a number, and a special character.
    </p>

    <?php formActions('Change Password', 'technician-dashboard'); ?>
</form>