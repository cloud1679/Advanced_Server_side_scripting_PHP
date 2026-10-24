<?php

/** @var string $title */
/** @var array<string, string> $navigation */
/** @var string $page */
/** @var bool $isPost */
/** @var array<int, string> $errors */
/** @var string $viewFile */
/** @var array $customers */
/** @var Customer|null $customerRecord */
/** @var bool $customerPageUnavailable */
/** @var array $employees */
/** @var Employee|null $employeeRecord */
/** @var bool $employeePageUnavailable */

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($title) ?> | Customer Complaint Manager</title>
</head>

<body>
    <main>
        <h1><?= e($title) ?></h1>

        <?php if (in_array($page, ['customer-dashboard', 'technician-dashboard', 'admin-dashboard'], true)): ?>
            <p>
                Customer registration, administrator customer editing,
                and employee management use MySQL.
                Other sections currently use sample data.
            </p>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div role="alert">
                <p><strong>Please correct the following:</strong></p>

                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($isPost): ?>
            <p role="status">
                <?php if ($page === 'login'): ?>
                    The input passed validation.
                    Login is not connected yet.
                <?php else: ?>
                    The input passed validation.
                    This form is not connected to database saving yet.
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php require $viewFile; ?>

        <?php
        $backLinks = [
            'customer-dashboard' => ['login', 'sign out'],
            'technician-dashboard' => ['login', 'sign out'],
            'admin-dashboard' => ['login', 'sign out'],
            'employees' => ['admin-dashboard', 'Back to Dashboard'],
            'open-complaints' => ['admin-dashboard', 'Back to Dashboard'],
            'unassigned' => ['admin-dashboard', 'Back to Dashboard'],
            'workload' => ['admin-dashboard', 'Back to Dashboard']
        ];
        ?>

        <?php if (isset($backLinks[$page])): ?>
            <p>
                <a href="?page=<?= e($backLinks[$page][0]) ?>">
                    <?= e($backLinks[$page][1]) ?>
                </a>
            </p>
        <?php endif; ?>
    </main>
</body>

</html>
