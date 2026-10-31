<?php

/** @var Complaint[] $customerComplaints */
/** @var bool $complaintPageUnavailable */
?>

<?php if (!$complaintPageUnavailable): ?>
    <?php if (($_GET['saved'] ?? '') === '1'): ?>
        <p role="status">Complaint saved.</p>
    <?php endif; ?>

    <?php
    complaintTable(
        $customerComplaints,
        'complaint-details',
        'View'
    );
    ?>
<?php endif; ?>

<p>
    <a href="?page=customer-dashboard">Back to Dashboard</a>
</p>