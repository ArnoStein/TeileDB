<?php
declare(strict_types=1);

use App\Repository\PartRepository;
use App\Repository\StatusRepository;
use Throwable;

require_once __DIR__ . '/../Repository/PartRepository.php';
require_once __DIR__ . '/../Repository/StatusRepository.php';

$repository = new PartRepository($ctx['pdo']);
$statusRepository = new StatusRepository($ctx['pdo']);
$statuses = [];
$statusError = null;

try {
    $statuses = $statusRepository->listStatusesOrdered();
} catch (Throwable $e) {
    $ctx['logger']->error('Konnte Statusliste nicht laden', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    $statusError = 'Statusfilter nicht verfügbar.';
}

$filters = [];
if (isset($_GET['status_id']) && $_GET['status_id'] !== '') {
    $filters['status_id'] = (int) $_GET['status_id'];
}
if (isset($_GET['q']) && trim((string) $_GET['q']) !== '') {
    $filters['search'] = trim((string) $_GET['q']);
}

$parts = $repository->listParts($filters);

$pageTitle = 'Teileliste';
$contentTemplate = __DIR__ . '/../../templates/parts_list.php';
$viewData = [
    'parts' => $parts,
    'filters' => $filters,
    'statuses' => $statuses,
    'statusError' => $statusError,
    'selectedStatusId' => $filters['status_id'] ?? '',
];

require __DIR__ . '/../../templates/layout.php';
