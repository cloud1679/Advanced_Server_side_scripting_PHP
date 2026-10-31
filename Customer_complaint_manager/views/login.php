<?php

/** @var array<string, string> $values */
?>

<?php if (($_GET['registered'] ?? '') === '1'): ?>
    <p role="status">
        Your customer account was saved. You can now log in.
    </p>
<?php endif; ?>

<?php if (($_GET['logged_out'] ?? '') === '1'): ?>
    <p role="status">
        You have been signed out.
    </p>
<?php endif; ?>

<?php if (!empty($_SESSION['session_expired'])): ?>
    <p role="status">
        Your session expired. Please log in again.
    </p>

    <?php unset($_SESSION['session_expired']); ?>
<?php endif; ?>

<h2>Login</h2>

<form method="post" action="?page=login">
    <?php
    csrfField();

    selectField(
        $values,
        'account_type',
        'Account Type',
        optionsFromList(ACCOUNT_TYPES)
    );

    inputField(
        $values,
        'login_id',
        'Customer Email/Employee ID',
        'text',
        150
    );

    inputField(
        $values,
        'password',
        'Password',
        'password',
        72
    );
    ?>

    <p>
        Forgot your password? Contact an administrator
        to receive a temporary password.
    </p>

    <p>
        <button type="submit">Login</button>
    </p>
</form>

<p>
    <a href="?page=register">Create Account</a>
</p>