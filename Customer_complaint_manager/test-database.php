<?php

const APP_URL =
'https://localhost/Customer_complaint_manager';

const APP_COOKIE_PATH =
'/Customer_complaint_manager/';

const SESSION_TIMEOUT = 1800;

$isHttps = (
    isset($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== '' &&
    strtolower((string) $_SERVER['HTTPS']) !== 'off'
) || (
    (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
);

if (!$isHttps) {
    header(
        'Location: ' . APP_URL . '/test-database.php',
        true,
        303
    );
    exit;
}

header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);

    exit('This page only accepts GET requests.');
}

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');

session_name('CCM_SESSION');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => APP_COOKIE_PATH,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/models/EmployeeDB.php';

$currentTime = time();

if (
    isset($_SESSION['last_activity']) &&
    $currentTime - (int) $_SESSION['last_activity']
    >= SESSION_TIMEOUT
) {
    $_SESSION = [];

    session_regenerate_id(true);

    $_SESSION['session_expired'] = true;
}

$auth = $_SESSION['auth'] ?? null;

$authenticated = is_array($auth)
    && isset(
        $auth['account_id'],
        $auth['account_type'],
        $auth['password_fingerprint']
    )
    && is_string($auth['account_type'])
    && is_string($auth['password_fingerprint']);

if (!$authenticated) {
    header(
        'Location: ' . APP_URL . '/index.php?page=login',
        true,
        303
    );
    exit;
}

$connected = false;
$productCount = 0;
$categoryCount = 0;
$accessError = '';
$responseStatus = 200;

if ($auth['account_type'] !== 'Administrator') {
    $accessError =
        'Only administrators can access the database connection test.';

    $responseStatus = 403;
} else {
    $employeeId = filter_var(
        $auth['account_id'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    try {
        $employeeDB = new EmployeeDB();

        $administrator = $employeeId !== false
            ? $employeeDB->getById($employeeId)
            : null;

        $validAdministrator = $administrator !== null
            && $administrator->getEmployeeLevel() === 'Administrator'
            && hash_equals(
                $auth['password_fingerprint'],
                hash(
                    'sha256',
                    $administrator->getPasswordHash()
                )
            );

        if (!$validAdministrator) {
            $_SESSION = [];

            session_regenerate_id(true);

            header(
                'Location: ' . APP_URL . '/index.php?page=login',
                true,
                303
            );
            exit;
        }

        $_SESSION['last_activity'] = $currentTime;

        if ($administrator->getMustChangePassword()) {
            header(
                'Location: ' . APP_URL
                    . '/index.php?page=change-password',
                true,
                303
            );
            exit;
        }

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

        $accessError =
            'The administrator account or database connection '
            . 'could not be verified. Check the PHP error log.';

        $responseStatus = 503;
    }
}

http_response_code($responseStatus);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Database Connection Test</title>
</head>

<body>
    <main>
        <h1>Database Connection Test</h1>

        <?php if ($accessError !== ''): ?>
            <p role="alert"><?= e($accessError) ?></p>

            <p>
                <a href="index.php?page=login">
                    Return to Your Dashboard
                </a>
            </p>
        <?php elseif ($connected): ?>
            <p>Database connection successful.</p>

            <ul>
                <li>
                    Products/services: <?= e($productCount) ?>
                </li>

                <li>
                    Complaint categories: <?= e($categoryCount) ?>
                </li>
            </ul>

            <p>
                <a href="index.php?page=admin-dashboard">
                    Back to Dashboard
                </a>
            </p>
        <?php endif; ?>
    </main>
</body>

</html>