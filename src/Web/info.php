<?php
declare(strict_types=1);

use App\Auth\Auth;
use App\AppVersion;


Auth::requireLogin();

$pageTitle = 'Info';
$contentTemplate = __DIR__ . '/../../templates/info.php';
$viewData = [
    'version' => AppVersion::VERSION,
    'phpVersion' => PHP_VERSION,
    'serverTime' => date('d.m.Y H:i:s'),
    'isLoggedIn' => Auth::check(),
];

require __DIR__ . '/../../templates/layout.php';
