<?php
declare(strict_types=1);

use App\Repository\PartRepository;
use App\Repository\PartTypeRepository;
use App\Repository\StatusRepository;
use App\Auth\Auth;
use App\Domain\SerialNumber;
use Throwable;

require_once __DIR__ . '/../Auth/Auth.php';
require_once __DIR__ . '/../Repository/PartRepository.php';
require_once __DIR__ . '/../Repository/PartTypeRepository.php';
require_once __DIR__ . '/../Repository/StatusRepository.php';
require_once __DIR__ . '/../Domain/SerialNumber.php';

Auth::requireLogin();

$partRepo = new PartRepository($ctx['pdo']);
$partTypeRepo = new PartTypeRepository($ctx['pdo']);
$statusRepo = new StatusRepository($ctx['pdo']);

$errors = [];
$formData = [
    'serial_number' => '',
    'part_type_id' => '',
    'status_id' => '',
];
$canonicalSerial = null;

try {
    $partTypes = $partTypeRepo->listPartTypes();
    $statuses = $statusRepo->listStatusesOrdered();
} catch (Throwable $e) {
    $ctx['logger']->error('Fehler beim Laden der Stammdaten (Teiltypen/Status)', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    http_response_code(500);
    echo 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['serial_number'] = (string) ($_POST['serial_number'] ?? '');
    $formData['part_type_id'] = (string) ($_POST['part_type_id'] ?? '');
    $formData['status_id'] = (string) ($_POST['status_id'] ?? '');

    // TODO: CSRF-Schutz ergänzen

    if ($formData['serial_number'] === '') {
        $errors['serial_number'] = 'Seriennummer ist erforderlich.';
    } else {
        $result = SerialNumber::parse($formData['serial_number']);
        if ($result->ok === false) {
            $errors['serial_number'] = $result->errorMessage ?? 'Ungültige Seriennummer.';
        } else {
            $canonicalSerial = $result->canonical;
        }
    }

    $partTypeId = ctype_digit($formData['part_type_id']) ? (int) $formData['part_type_id'] : 0;
    if ($partTypeId <= 0) {
        $errors['part_type_id'] = 'Teiltyp ist erforderlich.';
    } else {
        $exists = false;
        foreach ($partTypes as $type) {
            if ((int) ($type['id'] ?? 0) === $partTypeId) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $errors['part_type_id'] = 'Teiltyp ist ungültig.';
        }
    }

    $statusId = ctype_digit($formData['status_id']) ? (int) $formData['status_id'] : 0;
    if ($statusId <= 0) {
        $errors['status_id'] = 'Status ist erforderlich.';
    } else {
        $exists = false;
        foreach ($statuses as $status) {
            if ((int) ($status['id'] ?? 0) === $statusId) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $errors['status_id'] = 'Status ist ungültig.';
        }
    }

    if ($errors === []) {
        try {
            $partRepo->createPart([
                'serial_number' => (string) $canonicalSerial,
                'part_type_id' => $partTypeId,
                'status_id' => $statusId,
            ]);

            header('Location: index.php?page=parts_list');
            exit;
        } catch (Throwable $e) {
            $prev = $e->getPrevious();
            if ($e->getMessage() === 'duplicate_serial_number' || ($prev instanceof \PDOException && $prev->getCode() === '23000')) {
                $errors['serial_number'] = 'Seriennummer existiert bereits.';
                $ctx['logger']->error('Duplicate serial number', [
                    'serial_number' => $canonicalSerial ?? $formData['serial_number'],
                    'error' => $e->getMessage(),
                ]);
            } else {
                $errors['global'] = 'Speichern nicht möglich. Bitte später erneut versuchen.';
                $ctx['logger']->error('Fehler beim Anlegen eines Teils', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }
}

$pageTitle = 'Teil anlegen';
$contentTemplate = __DIR__ . '/../../templates/parts_create.php';
$viewData = [
    'errors' => $errors,
    'formData' => $formData,
    'partTypes' => $partTypes,
    'statuses' => $statuses,
    'isLoggedIn' => Auth::check(),
];

require __DIR__ . '/../../templates/layout.php';
