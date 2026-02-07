<?php
declare(strict_types=1);

use App\Repository\PartRepository;
use App\Repository\StatusRepository;
use App\Repository\PartCommentRepository;
use App\Auth\Auth;
use App\Domain\SerialNumber;
use Throwable;

require_once __DIR__ . '/../Auth/Auth.php';
require_once __DIR__ . '/../Repository/PartRepository.php';
require_once __DIR__ . '/../Repository/StatusRepository.php';
require_once __DIR__ . '/../Repository/PartCommentRepository.php';
require_once __DIR__ . '/../Domain/SerialNumber.php';

Auth::requireLogin();

$partRepo = new PartRepository($ctx['pdo']);
$statusRepo = new StatusRepository($ctx['pdo']);
$commentRepo = new PartCommentRepository($ctx['pdo']);

$serialNumberParam = $_GET['serial_number'] ?? '';
$idParam = $_GET['id'] ?? null;
$partId = is_numeric($idParam) ? (int) $idParam : 0;
$scanError = '';
$part = null;
$scanCanonical = null;

if ($serialNumberParam !== '') {
    $scanInput = rtrim((string) $serialNumberParam, "\r\n");

    if ($scanInput === '') {
        $scanError = 'Ungültiges Format';
    } else {
        $parseResult = SerialNumber::parse($scanInput);
        if ($parseResult->ok === false) {
            $scanError = $parseResult->errorMessage ?? 'Ungültiges Format';
        } else {
            $scanCanonical = $parseResult->canonical;
            try {
                $serialPart = $partRepo->getPartBySerial((string) $scanCanonical);
                if ($serialPart !== null) {
                    $targetId = (int) ($serialPart['id'] ?? 0);
                    if ($targetId > 0 && $targetId !== $partId) {
                        header('Location: index.php?page=part_detail&id=' . $targetId);
                        exit;
                    }
                    $partId = $targetId;
                    $part = $serialPart;
                } else {
                    $scanError = 'Seriennummer nicht gefunden: ' . $scanCanonical;
                }
            } catch (Throwable $e) {
                $ctx['logger']->error('Fehler beim Laden eines Teils per Seriennummer', [
                    'serial_number' => $scanCanonical,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $scanError = 'Seriennummer nicht gefunden: ' . $scanCanonical;
            }
        }
    }
}

if ($partId <= 0 && $serialNumberParam === '') {
    http_response_code(404);
    echo 'Teil nicht gefunden.';
    return;
}

try {
    if ($part === null) {
        $part = $partRepo->getPartById($partId);
    }
} catch (Throwable $e) {
    $ctx['logger']->error('Fehler beim Laden eines Teils', [
        'part_id' => $partId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    http_response_code(500);
    echo 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.';
    return;
}

if ($part === null && $serialNumberParam === '') {
    http_response_code(404);
    echo 'Teil nicht gefunden.';
    return;
}

try {
    $statuses = $statusRepo->listStatusesOrdered();
} catch (Throwable $e) {
    $ctx['logger']->error('Fehler beim Laden der Statusliste', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    http_response_code(500);
    echo 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.';
    return;
}

$errors = [];
$commentErrors = [];
$commentInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'status_update';

    if ($action === 'status_update') {
        $statusIdParam = $_POST['status_id'] ?? '';
        $statusId = is_numeric($statusIdParam) ? (int) $statusIdParam : 0;

        $validStatus = false;
        foreach ($statuses as $status) {
            if ((int) ($status['id'] ?? 0) === $statusId) {
                $validStatus = true;
                break;
            }
        }

        if ($statusId <= 0 || !$validStatus) {
            $errors['status_id'] = 'Ungültiger Status.';
        }

        if ($errors === []) {
            try {
                $partRepo->updateStatus($partId, $statusId);
                header('Location: index.php?page=part_detail&id=' . $partId . '&updated=1');
                exit;
            } catch (Throwable $e) {
                $ctx['logger']->error('Fehler beim Status-Update', [
                    'part_id' => $partId,
                    'status_id' => $statusId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors['global'] = 'Status konnte nicht gespeichert werden.';
            }
        }
    } elseif ($action === 'comment_create') {
        $commentInput = trim((string) ($_POST['comment'] ?? ''));
        if ($commentInput === '') {
            $commentErrors['comment'] = 'Kommentar darf nicht leer sein.';
        } elseif (mb_strlen($commentInput) > 2000) {
            $commentErrors['comment'] = 'Kommentar ist zu lang (max. 2000 Zeichen).';
        }

        if ($commentErrors === []) {
            try {
                $commentRepo->createForPart($partId, $commentInput);
                header('Location: index.php?page=part_detail&id=' . $partId . '&commented=1#comments');
                exit;
            } catch (Throwable $e) {
                $ctx['logger']->error('Fehler beim Anlegen eines Kommentars', [
                    'part_id' => $partId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $commentErrors['global'] = 'Kommentar konnte nicht gespeichert werden.';
            }
        }
    }
}

try {
    $comments = $commentRepo->listForPart($partId);
} catch (Throwable $e) {
    $ctx['logger']->error('Fehler beim Laden der Kommentare', [
        'part_id' => $partId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    $comments = [];
}

$pageTitle = 'Teil-Details';
$contentTemplate = __DIR__ . '/../../templates/part_detail.php';
$viewData = [
    'part' => $part,
    'statuses' => $statuses,
    'errors' => $errors,
    'commentErrors' => $commentErrors,
    'commentInput' => $commentInput,
    'updated' => isset($_GET['updated']) && $_GET['updated'] === '1',
    'commented' => isset($_GET['commented']) && $_GET['commented'] === '1',
    'comments' => $comments,
    'scanError' => $scanError,
    'isLoggedIn' => Auth::check(),
];

require __DIR__ . '/../../templates/layout.php';
