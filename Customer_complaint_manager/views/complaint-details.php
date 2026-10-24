<?php

/** @var array|null $selectedComplaint */
/** @var bool $complaintPageUnavailable */

?>

<?php if (!$complaintPageUnavailable && $selectedComplaint !== null): ?>
    <?php complaintSummary($selectedComplaint); ?>

    <h3>Technician Notes</h3>

    <p><?= nl2br(e($selectedComplaint['notes'])) ?></p>

    <?php if ($selectedComplaint['status'] === 'Closed'): ?>
        <h3>Resolution</h3>

        <p>
            <strong>Resolution Date:</strong>
            <?= e($selectedComplaint['resolution_date'] ?? '') ?>
        </p>

        <p>
            <?= nl2br(e($selectedComplaint['resolution_notes'] ?? '')) ?>
        </p>
    <?php endif; ?>

    <p>
        <a href="?page=my-complaints&amp;customer_id=<?= e($selectedComplaint['customer_id']) ?>">
            Back to My Complaints
        </a>
    </p>
<?php else: ?>
    <p>
        <a href="?page=my-complaints">Back to My Complaints</a>
    </p>
<?php endif; ?>