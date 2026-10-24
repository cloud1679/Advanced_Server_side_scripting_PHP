<?php

/** @var array<string, string> $values */
/** @var array<int, string> $customerOptions */
/** @var array<int, string> $productOptions */
/** @var array<int, string> $categoryOptions */
/** @var bool $complaintChoicesAvailable */
/** @var bool $complaintPageUnavailable */

?>

<?php if ($complaintChoicesAvailable && !$complaintPageUnavailable): ?>
    <form method="post">
        <?php
        selectField(
            $values,
            'customer_id',
            'Test Customer',
            $customerOptions
        );
        ?>

        <p>
            Select the customer submitting this complaint.
            This temporary selection will be replaced by login.
        </p>

        <?php
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