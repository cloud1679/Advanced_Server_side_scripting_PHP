<?php
/** @var array<string, string> $values */
?>
<form method="post">
    <?php
    selectField(
        $values,
        'product',
        'Product / Service',
        optionsFromList(PRODUCTS)
    );

    selectField(
        $values,
        'category',
        'Complaint Category',
        optionsFromList(CATEGORIES)
    );

    textAreaField(
        $values,
        'description',
        'Describe the Issue',
        true
    );

    formActions('Submit Complaint', 'customer-dashboard');
    ?>
</form>