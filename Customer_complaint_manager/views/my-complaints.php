<?php

/** @var array<int, string> $customerOptions */
/** @var string $selectedCustomerId */
/** @var array $customerComplaints */
/** @var bool $complaintPageUnavailable */

?>

<?php if (!$complaintPageUnavailable): ?>
    <?php if (($_GET['saved'] ?? '') === '1'): ?>
        <p role="status">Complaint saved.</p>
    <?php endif; ?>

    <?php if ($customerOptions): ?>
        <form method="get">
            <input type="hidden" name="page" value="my-complaints">

            <?php
            selectField(
                ['customer_id' => $selectedCustomerId],
                'customer_id',
                'Test Customer',
                $customerOptions
            );
            ?>

            <p>
                <button type="submit">View Complaints</button>
            </p>
        </form>

        <?php if ($selectedCustomerId !== ''): ?>
            <?php
            complaintTable(
                $customerComplaints,
                'complaint-details',
                'View'
            );
            ?>
        <?php else: ?>
            <p>Select a customer to view their complaints.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>No customer accounts have been created yet.</p>
    <?php endif; ?>
<?php endif; ?>

<p>
    <a href="?page=customer-dashboard">Back to Dashboard</a>
</p>