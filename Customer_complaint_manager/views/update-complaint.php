<?php
/** @var array<string, string> $selectedComplaint */
/** @var array<string, string> $values */
?>
<form method="post">
    <?php complaintSummary($selectedComplaint); ?>

    <h3>Existing Technician Notes</h3>

    <p><?= e($selectedComplaint['notes']) ?></p>

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

    <?php formActions('Save Update', 'technician'); ?>
</form>