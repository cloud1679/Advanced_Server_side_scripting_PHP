<?php
/** @var array<string, string> $customer */
?>
<p>
    Welcome,
    <?= e($customer['first_name']) ?>
    <?= e($customer['last_name']) ?>.
</p>

<ul>
    <li><a href="?page=profile">My Profile</a></li>
    <li><a href="?page=new-complaint">Submit a Complaint</a></li>
    <li><a href="?page=my-complaints">My Complaints</a></li>
</ul>