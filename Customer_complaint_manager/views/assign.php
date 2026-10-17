<?php

/** @var array<string, string> $values */
/** @var array<int|string, string> $complaintOptions */
/** @var array<int|string, string> $technicians */
?>
<form method="post">
    <?php
    selectField(
        $values,
        'complaint_id',
        'Complaint',
        $complaintOptions
    );

    selectField(
        $values,
        'technician_id',
        'Technician',
        $technicians
    );

    formActions('Assign Complaint', 'admin-dashboard');
    ?>
</form>