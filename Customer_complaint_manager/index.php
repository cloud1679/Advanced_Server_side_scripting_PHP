<?php

require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/controllers/PageController.php';

$controller = new PageController();
$controller->handleRequest();
