<?php

/** @var array<string, string> $values */
/** @var Customer|null $profileCustomerRecord */
/** @var bool $profilePageUnavailable */
?>

<?php if (
    !$profilePageUnavailable &&
    $profileCustomerRecord !== null
): ?>
    <?php if (($_GET['saved'] ?? '') === '1'): ?>
        <p role="status">Profile changes saved.</p>
    <?php endif; ?>

    <form method="post" action="?page=profile">
        <?php
        csrfField();

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
    <p>Your profile could not be displayed.</p>

    <p>
        <a href="?page=customer-dashboard">Back to Dashboard</a>
    </p>
<?php endif; ?>