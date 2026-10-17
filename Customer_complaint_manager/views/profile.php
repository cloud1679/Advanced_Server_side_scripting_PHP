<?php
/** @var array<string, string> $values */
?>
<form method="post">
    <?php
    inputField($values, 'first_name', 'First Name', 'text', 50);
    inputField($values, 'last_name', 'Last Name', 'text', 50);
    inputField($values, 'email', 'Email Address', 'email', 150);

    inputField(
        $values,
        'street_address',
        'Street Address',
        'text',
        150
    );

    inputField($values, 'city', 'City', 'text', 75);

    selectField(
        $values,
        'state',
        'State',
        optionsFromList(STATES)
    );

    inputField($values, 'zip_code', 'ZIP Code', 'text', 10);
    inputField($values, 'phone', 'Phone Number', 'tel', 20);

    formActions('Save Changes', 'customer-dashboard');
    ?>
</form>