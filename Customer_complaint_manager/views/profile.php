<?php

/** @var array $values */
/** @var array $profileCustomerOptions */
/** @var string $profileCustomerId */
/** @var Customer|null $profileCustomerRecord */
/** @var bool $profilePageUnavailable */

?>

<?php if (!$profilePageUnavailable): ?>
    <?php if (($_GET['saved'] ?? '') === '1'): ?>
        <p role="status">Profile changes saved.</p>
    <?php endif; ?>

    <?php if (!$profileCustomerOptions): ?>
        <p>Create a customer account before editing a profile.</p>

        <p>
            <a href="?page=register">Create Account</a>
        </p>
    <?php else: ?>
        <form method="get">
            <input type="hidden" name="page" value="profile">

            <?php
            selectField(
                ['customer_id' => $profileCustomerId],
                'customer_id',
                'Test Customer',
                $profileCustomerOptions
            );
            ?>

            <p>
                <button type="submit">Load Profile</button>
            </p>
        </form>

        <?php if ($profileCustomerRecord !== null): ?>
            <form
                method="post"
                action="<?= e(
                            '?page=profile&customer_id='
                                . rawurlencode($profileCustomerId)
                        ) ?>">
                <?php
                inputField(
                    $values,
                    'first_name',
                    'First Name',
                    'text',
                    50
                );

                inputField(
                    $values,
                    'last_name',
                    'Last Name',
                    'text',
                    50
                );

                inputField(
                    $values,
                    'email',
                    'Email Address',
                    'email',
                    150
                );

                inputField(
                    $values,
                    'street_address',
                    'Street Address',
                    'text',
                    150
                );

                inputField(
                    $values,
                    'city',
                    'City',
                    'text',
                    75
                );

                selectField(
                    $values,
                    'state',
                    'State',
                    optionsFromList(STATES)
                );

                inputField(
                    $values,
                    'zip_code',
                    'ZIP Code',
                    'text',
                    10
                );

                inputField(
                    $values,
                    'phone',
                    'Phone Number',
                    'tel',
                    20
                );

                formActions('Save Changes', 'customer-dashboard');
                ?>
            </form>
        <?php else: ?>
            <p>Select a customer and click Load Profile.</p>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<?php if ($profileCustomerRecord === null || $profilePageUnavailable): ?>
    <p>
        <a href="?page=customer-dashboard">Back to Dashboard</a>
    </p>
<?php endif; ?>