<?php

/** @var array<string, string> $selectedComplaint */ complaintSummary($selectedComplaint); ?>

<h3>Technician Notes</h3>

<p><?= e($selectedComplaint['notes']) ?></p>

<p>
    <a href="?page=my-complaints">Back to My Complaints</a>
</p>