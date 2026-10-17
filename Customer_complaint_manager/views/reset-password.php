<?php
/** @var array<string, string> $values */
?>
<form method="post">
    <?php
    selectField(
        $values,
        'account_type',
        'Account Type',
        optionsFromList(ACCOUNT_TYPES)
    );

    inputField(
        $values,
        'login_id',
        'Email or Employee User ID'
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

    <p>
        Password reset verification and saving will be added
        during the security and database-support steps.
    </p>

    <?php formActions('Reset Password', 'login'); ?>
</form>