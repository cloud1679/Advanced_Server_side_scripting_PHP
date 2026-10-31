<?php

/** @var string $currentUserName */
?>

<p>
    Welcome, <?= e($currentUserName ?? '') ?>.
</p>

<ul>
    <li>
        <a href="?page=technician">Assigned Complaints</a>
    </li>

    <li>
        <a href="?page=change-password">Change Password</a>
    </li>
</ul>