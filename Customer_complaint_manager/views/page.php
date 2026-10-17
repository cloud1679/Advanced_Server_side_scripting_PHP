<?php
/** @var string $title */
/** @var array<string, string> $navigation */
/** @var string $page */
/** @var bool $isPost */
/** @var array<int, string> $errors */
/** @var string $viewFile */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($title) ?> | Customer Complaint Manager</title>
</head>

<body>
    <?php if ($navigation && !in_array($page, ['customer-dashboard', 'technician-dashboard', 'admin-dashboard'], true)): ?>
        <nav aria-label="Role navigation">
            <?php foreach ($navigation as $route => $label): ?>
                <a href="?page=<?= e($route) ?>">
                    <?= e($label) ?>
                </a>
                |
            <?php endforeach; ?>

            <a href="?page=login">Back to Login</a>
        </nav>

        <hr>
    <?php endif; ?>

    <main>
        <h1><?= e($title) ?></h1>

        <?php if ($navigation): ?>
            <p>
                <strong>Sample Data:</strong>
                Changes are not saved.
            </p>
        <?php endif; ?>

        <?php if ($isPost): ?>
            <?php if ($errors): ?>
                <div role="alert">
                    <p><strong>Please correct the following:</strong></p>

                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <p role="status">
                    <?php if ($page === 'login'): ?>
                        The input passed validation.
                        Login is not connected yet.
                    <?php else: ?>
                        The form passed validation.
                        No information was saved.
                    <?php endif; ?>
                </p>
            <?php endif; ?>
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
                <a href="?page=<?= e($backLinks[$page][0]) ?>"><?= e($backLinks[$page][1]) ?></a>
            </p>
        <?php endif; ?>
    </main>
</body>

</html>