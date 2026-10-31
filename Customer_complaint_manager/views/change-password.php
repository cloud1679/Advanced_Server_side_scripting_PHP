<?php

/** @var array<string, string> $values */
/** @var string $dashboardPage */
/** @var bool $mustChangePassword */

$changeRequired = $mustChangePassword ?? false;
?>

<?php if ($changeRequired): ?>
    <p role="status">
        You are using a temporary password.
        Choose a new password before continuing to your dashboard.
    </p>
<?php elseif (($_GET['saved'] ?? '') === '1'): ?>
    <p role="status">Your password was changed.</p>
<?php endif; ?>

<form method="post" action="?page=change-password">
    <?php
    csrfField();

    inputField(
        $values,
        'current_password',
        $changeRequired ? 'Temporary Password' : 'Current Password',
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

    <?php if ($changeRequired): ?>
        <p>
            <button type="submit">Change Password</button>
        </p>
    <?php else: ?>
        <?php
        formActions(
            'Change Password',
            $dashboardPage ?? 'login'
        );
        ?>
    <?php endif; ?>
</form>

<?php if ($changeRequired): ?>
    <?php logoutForm(); ?>
<?php endif; ?>