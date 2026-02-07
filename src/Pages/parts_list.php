<?php
declare(strict_types=1);

use App\Repository\PartRepository;
use App\Repository\StatusRepository;
use App\Auth\Auth;
use App\Domain\SerialNumber;
use Throwable;

require_once __DIR__ . '/../Auth/Auth.php';
require_once __DIR__ . '/../Repository/PartRepository.php';
require_once __DIR__ . '/../Repository/StatusRepository.php';
require_once __DIR__ . '/../Domain/SerialNumber.php';

Auth::requireLogin();

$repository = new PartRepository($ctx['pdo']);
$statusRepository = new StatusRepository($ctx['pdo']);
$statuses = [];
$statusError = null;
$filterErrors = [];
$serialCanonical = null;
$focusSerial = false;
$clearSerialInput = false;
$serialInput = isset($_GET['serial_number']) ? (string) $_GET['serial_number'] : '';

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
if ($serialInput !== '') {
    $scanInput = rtrim($serialInput, "\r\n");
    if ($scanInput === '') {
        $filterErrors['serial_number'] = 'Ungültiges Format';
        $focusSerial = true;
    } else {
        $parseResult = SerialNumber::parse($scanInput);
        if ($parseResult->ok === false) {
            $filterErrors['serial_number'] = $parseResult->errorMessage ?? 'Ungültiges Format';
            $focusSerial = true;
        } else {
            $serialCanonical = $parseResult->canonical;
            $filters['serial_number'] = $serialCanonical;
            $focusSerial = true;
            $clearSerialInput = true;
        }
    }
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
    'filterErrors' => $filterErrors,
    'serialInput' => $serialInput,
    'serialCanonical' => $serialCanonical,
    'focusSerial' => $focusSerial,
    'clearSerialInput' => $clearSerialInput,
    'isLoggedIn' => Auth::check(),
];

require __DIR__ . '/../../templates/layout.php';
