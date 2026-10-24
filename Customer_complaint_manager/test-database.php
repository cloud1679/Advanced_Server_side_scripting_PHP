<?php

require_once __DIR__ . '/models/Database.php';

$connected = false;
$productCount = 0;
$categoryCount = 0;

try {
    $connection = Database::getConnection();

    $productCount = (int) $connection
        ->query('SELECT COUNT(*) FROM products')
        ->fetchColumn();

    $categoryCount = (int) $connection
        ->query('SELECT COUNT(*) FROM complaint_categories')
        ->fetchColumn();

    $connected = true;
} catch (PDOException $exception) {
    error_log($exception->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Database Connection Test</title>
</head>

<body>
    <h1>Database Connection Test</h1>

    <?php if ($connected): ?>
        <p>Database connection successful.</p>

        <ul>
            <li>Products/services: <?= $productCount ?></li>
            <li>Complaint categories: <?= $categoryCount ?></li>
        </ul>
    <?php else: ?>
        <p>
            Connection or query failed. Check that MySQL is running,
            the database name and credentials are correct,
            and database.sql has been imported.
        </p>
        <p>The technical error was written to the PHP error log.</p>
    <?php endif; ?>

    <p><a href="index.php?page=admin-dashboard">Back to Dashboard</a></p>
</body>

</html>
