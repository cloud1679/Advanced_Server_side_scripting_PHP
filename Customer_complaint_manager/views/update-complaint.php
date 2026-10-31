<?php

/** @var array<string, string> $values */
/** @var Complaint|null $selectedComplaint */
/** @var bool $technicianComplaintPageUnavailable */
?>

<?php if (
    !$technicianComplaintPageUnavailable &&
    $selectedComplaint !== null
): ?>
    <?php complaintSummary($selectedComplaint); ?>

    <h3>Existing Technician Notes</h3>

    <p><?= nl2br(e(
            complaintNotesText($selectedComplaint->getNotes())
        )) ?></p>

    <form
        method="post"
        action="<?= e(
                    '?page=update-complaint&id='
                        . rawurlencode(
                            (string) $selectedComplaint->getComplaintId()
                        )
                ) ?>">
        <?php
        csrfField();

        textAreaField(
            $values,
            'technician_notes',
            'New Technician Notes / Analysis'
        );

        selectField(
            $values,
            'status',
            'Complaint Status',
            [
                'Open' => 'Open',
                'Closed' => 'Closed / Resolved'
            ]
        );

        inputField(
            $values,
            'resolution_date',
            'Resolution Date',
            'date',
            10,
            false
        );

        textAreaField(
            $values,
            'resolution_notes',
            'Resolution Notes'
        );
        ?>

        <p>
            Closing a complaint requires a resolution date
            and resolution notes.
        </p>

        <?php formActions('Save Update', 'technician'); ?>
    </form>
<?php else: ?>
    <p>
        <a href="?page=technician">Back to Assigned Complaints</a>
    </p>
<?php endif; ?>