<?php

/** @var string $currentUserName */
?>

<p>
    Welcome, <?= e($currentUserName ?? '') ?>.
</p>

<ul>
    <li>
        <a href="?page=customers">Manage Customers</a>
    </li>

    <li>
        <a href="?page=employees">Manage Employees</a>
    </li>

    <li>
        <a href="?page=open-complaints">Open Complaints</a>
    </li>

    <li>
        <a href="?page=unassigned">Unassigned Complaints</a>
    </li>

    <li>
        <a href="?page=workload">Technician Workload</a>
    </li>

    <li>
        <a href="?page=assign">Assign Complaint</a>
    </li>

    <li>
        <a href="?page=change-password">Change Password</a>
    </li>

    <li>
        <a href="test-database.php">Test Database Connection</a>
    </li>
</ul>