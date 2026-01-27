<?php
declare(strict_types=1);

use App\Auth\Auth;

require_once __DIR__ . '/../Auth/Auth.php';

Auth::logout();
header('Location: index.php?page=login');
exit;
