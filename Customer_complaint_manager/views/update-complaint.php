<?php

/** @var array $values */
/** @var array|null $selectedComplaint */
/** @var string $selectedTechnicianId */
/** @var bool $technicianComplaintPageUnavailable */

?>

<?php if (
    !$technicianComplaintPageUnavailable &&
    $selectedComplaint !== null
): ?>
    <?php complaintSummary($selectedComplaint); ?>

    <h3>Existing Technician Notes</h3>

    <p><?= nl2br(e($selectedComplaint['notes'])) ?></p>

    <form method="post">
        <?php
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

        <p>
            <button type="submit">Save Update</button>

            <a href="<?= e(
                            '?page=technician&technician_id='
                                . rawurlencode($selectedTechnicianId)
                        ) ?>">
                Cancel and Return
            </a>
        </p>
    </form>
<?php else: ?>
    <p>
        <a href="?page=technician">Back to Assigned Complaints</a>
    </p>
<?php endif; ?>