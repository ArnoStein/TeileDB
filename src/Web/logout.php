<?php
declare(strict_types=1);

use App\Auth\Auth;


Auth::logout();
header('Location: index.php?page=login');
exit;
