<?php

/** @var array<string, string> $values */
/** @var array<int, string> $productOptions */
/** @var array<int, string> $categoryOptions */
/** @var bool $complaintChoicesAvailable */
/** @var bool $complaintPageUnavailable */
?>

<?php if (
    $complaintChoicesAvailable &&
    !$complaintPageUnavailable
): ?>
    <form method="post" action="?page=new-complaint">
        <?php
        csrfField();

        selectField(
            $values,
            'product_id',
            'Product / Service',
            $productOptions
        );

        selectField(
            $values,
            'category_id',
            'Complaint Category',
            $categoryOptions
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
<?php else: ?>
    <p>
        <a href="?page=customer-dashboard">Back to Dashboard</a>
    </p>
<?php endif; ?>