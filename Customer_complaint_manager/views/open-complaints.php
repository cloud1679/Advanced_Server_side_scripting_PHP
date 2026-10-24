<?php

/** @var array $complaints */
/** @var bool $adminComplaintPageUnavailable */

?>

<?php if (!$adminComplaintPageUnavailable): ?>
    <?php if (($_GET['assigned'] ?? '') === '1'): ?>
        <p role="status">Technician assignment saved.</p>
    <?php endif; ?>

    <?php
    complaintTable(
        $complaints,
        'assign',
        'Assign'
    );
    ?>
<?php endif; ?>