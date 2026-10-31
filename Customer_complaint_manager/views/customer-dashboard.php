<?php

/** @var string $currentUserName */
?>

<p>
    Welcome, <?= e($currentUserName ?? '') ?>.
</p>

<ul>
    <li>
        <a href="?page=profile">My Profile</a>
    </li>

    <li>
        <a href="?page=new-complaint">Submit a Complaint</a>
    </li>

    <li>
        <a href="?page=my-complaints">My Complaints</a>
    </li>

    <li>
        <a href="?page=change-password">Change Password</a>
    </li>
</ul>