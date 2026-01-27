<?php
declare(strict_types=1);

use App\Repository\PartTypeRepository;
use App\Auth\Auth;
use Throwable;

require_once __DIR__ . '/../Auth/Auth.php';
require_once __DIR__ . '/../Repository/PartTypeRepository.php';

Auth::requireLogin();

$repository = new PartTypeRepository($ctx['pdo']);
$errorMessage = null;

try {
    $partTypes = $repository->listPartTypes();
} catch (Throwable $e) {
    $ctx['logger']->error('Konnte Teiltypen nicht laden', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    $errorMessage = 'Die Liste der Teiltypen ist momentan nicht verfügbar.';
    $partTypes = [];
}

$pageTitle = 'Teiltypen';
$contentTemplate = __DIR__ . '/../../templates/part_types_list.php';
$viewData = [
    'partTypes' => $partTypes,
    'errorMessage' => $errorMessage,
    'isLoggedIn' => Auth::check(),
];

require __DIR__ . '/../../templates/layout.php';
