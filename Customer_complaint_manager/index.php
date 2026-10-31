<?php

const APP_URL =
'https://localhost/Customer_complaint_manager';

const APP_COOKIE_PATH =
'/Customer_complaint_manager/';

const SESSION_TIMEOUT = 1800;

// HTTPS must be configured in Apache for this address to work.
$isHttps = (
    isset($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== '' &&
    strtolower((string) $_SERVER['HTTPS']) !== 'off'
) || (
    (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
);

if (!$isHttps) {
    $queryString = http_build_query($_GET);

    $destination = APP_URL . '/index.php';

    if ($queryString !== '') {
        $destination .= '?' . $queryString;
    }

    // Redirect to a fresh GET request over HTTPS.
    header('Location: ' . $destination, true, 303);
    exit;
}

// Prevent browsers from caching private application pages.
header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');

// Basic browser protections.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

// Configure the session before starting it.
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

$currentTime = time();

// End authenticated sessions after 30 minutes without activity.
if (
    isset($_SESSION['last_activity']) &&
    $currentTime - (int) $_SESSION['last_activity']
    >= SESSION_TIMEOUT
) {
    $_SESSION = [];

    session_regenerate_id(true);

    $_SESSION['session_expired'] = true;
}

$_SESSION['last_activity'] = $currentTime;

// The updated forms and controller will use this token.
if (
    !isset($_SESSION['csrf_token']) ||
    !is_string($_SESSION['csrf_token'])
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/controllers/PageController.php';

$controller = new PageController();
$controller->handleRequest();
