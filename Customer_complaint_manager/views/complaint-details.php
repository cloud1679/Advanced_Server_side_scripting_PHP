<?php

/** @var Complaint|null $selectedComplaint */
/** @var bool $complaintPageUnavailable */
?>

<?php if (
    !$complaintPageUnavailable &&
    $selectedComplaint !== null
): ?>
    <?php complaintSummary($selectedComplaint); ?>

    <h3>Technician Notes</h3>

    <p><?= nl2br(e(
            complaintNotesText($selectedComplaint->getNotes())
        )) ?></p>

    <?php if ($selectedComplaint->getStatus() === 'Closed'): ?>
        <h3>Resolution</h3>

        <p>
            <strong>Resolution Date:</strong>
            <?= e($selectedComplaint->getResolutionDate() ?? '') ?>
        </p>

        <p><?= nl2br(e(
                $selectedComplaint->getResolutionNotes() ?? ''
            )) ?></p>
    <?php endif; ?>
<?php endif; ?>

<p>
    <a href="?page=my-complaints">Back to My Complaints</a>
</p>