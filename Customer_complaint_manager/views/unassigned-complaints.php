<?php

/** @var array $unassignedComplaints */
/** @var bool $adminComplaintPageUnavailable */

?>

<?php if (!$adminComplaintPageUnavailable): ?>
    <?php
    complaintTable(
        $unassignedComplaints,
        'assign',
        'Assign'
    );
    ?>
<?php endif; ?>