<?php

/** @var string $title */
/** @var array<string, string> $navigation */
/** @var string $page */
/** @var bool $isPost */
/** @var array<int, string> $errors */
/** @var string $viewFile */

$dashboardPages = [
    'customer-dashboard',
    'technician-dashboard',
    'admin-dashboard'
];

$isDashboard = in_array($page, $dashboardPages, true);

$backLinks = [
    'employees' => [
        'admin-dashboard',
        'Back to Dashboard'
    ],
    'open-complaints' => [
        'admin-dashboard',
        'Back to Dashboard'
    ],
    'unassigned' => [
        'admin-dashboard',
        'Back to Dashboard'
    ],
    'workload' => [
        'admin-dashboard',
        'Back to Dashboard'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= e($title) ?> | Customer Complaint Manager</title>
    <link rel="stylesheet" href="<?= e(APP_URL . '/style.css') ?>">
</head>

<body>
    <main>
        <h1><?= e($title) ?></h1>

        <?php if ($isDashboard): ?>
            <p>
                Customer registration, customer profiles,
                customer and employee management, complaints,
                assignments, and technician updates use MySQL.
            </p>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div role="alert" class="error-box">
                <p>
                    <strong>Please correct the following:</strong>
                </p>

                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php require $viewFile; ?>

        <?php if (isset($backLinks[$page])): ?>
            <p>
                <a href="<?= e(
                                '?page=' . $backLinks[$page][0]
                            ) ?>">
                    <?= e($backLinks[$page][1]) ?>
                </a>
            </p>
        <?php endif; ?>

        <?php if ($isDashboard): ?>
            <?php logoutForm(); ?>
        <?php endif; ?>
    </main>
</body>

</html>