<?php

/** @var bool $resetPageUnavailable */
/** @var string $resetAccountType */
/** @var int $resetAccountId */
/** @var string $resetAccountName */
/** @var string $resetLoginId */
/** @var string|null $temporaryPassword */
?>

<?php if (!$resetPageUnavailable): ?>
    <h2>Account Information</h2>

    <dl>
        <dt>Account Type</dt>
        <dd><?= e($resetAccountType) ?></dd>

        <dt>Name</dt>
        <dd><?= e($resetAccountName) ?></dd>

        <dt>Login ID</dt>
        <dd><?= e($resetLoginId) ?></dd>
    </dl>

    <?php if ($temporaryPassword !== null): ?>
        <p role="status">
            The temporary password was saved.
            The user must change it after logging in.
        </p>

        <p>
            <strong>Temporary Password:</strong>
            <code><?= e($temporaryPassword) ?></code>
        </p>

        <p>
            This password is displayed once.
            Give it directly to the user after verifying their identity.
            No email is sent.
        </p>

        <p>
            Only the password’s hash is stored in the database.
            The previous password no longer works.
        </p>
    <?php else: ?>
        <p>
            Generating a temporary password replaces this account’s
            current password and requires the user to choose a new
            password after logging in.
        </p>

        <p>
            Verify the user’s identity before resetting their password.
            Enter your own administrator password to confirm.
        </p>

        <form
            method="post"
            action="<?= e(
                        '?page=admin-reset-password'
                            . '&account_type='
                            . rawurlencode($resetAccountType)
                            . '&id='
                            . rawurlencode((string) $resetAccountId)
                    ) ?>">
            <?php
            csrfField();

            inputField(
                [],
                'admin_current_password',
                'Your Administrator Password',
                'password',
                72
            );
            ?>

            <p>
                <button type="submit">
                    Generate Temporary Password
                </button>
            </p>
        </form>
    <?php endif; ?>
<?php endif; ?>

<p>
    <?php if ($resetAccountType === 'Customer'): ?>
        <a href="?page=customers">Back to Customers</a>
    <?php else: ?>
        <a href="?page=employees">Back to Employees</a>
    <?php endif; ?>
</p>