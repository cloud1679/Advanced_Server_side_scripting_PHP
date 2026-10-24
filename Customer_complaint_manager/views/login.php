<?php

/** @var array<string, string> $values */
?>

<?php if (($_GET['registered'] ?? '') === '1'): ?>
    <p role="status">
        Your customer account was saved.
        Login functionality will be added in the security phase.
    </p>
<?php endif; ?>

<h2>Login</h2>

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
        'Customer Email/Employee ID'
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
        <button type="submit">Login</button>
    </p>
</form>

<p>
    <a href="?page=register">Create Account</a>
</p>
<p>
    <a href="?page=reset-password">Forgot Password</a>
</p>