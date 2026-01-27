<?php
declare(strict_types=1);

use App\Repository\PartRepository;
use App\Repository\StatusRepository;
use App\Repository\PartCommentRepository;
use Throwable;

require_once __DIR__ . '/../Repository/PartRepository.php';
require_once __DIR__ . '/../Repository/StatusRepository.php';
require_once __DIR__ . '/../Repository/PartCommentRepository.php';

$partRepo = new PartRepository($ctx['pdo']);
$statusRepo = new StatusRepository($ctx['pdo']);
$commentRepo = new PartCommentRepository($ctx['pdo']);

$serialNumberParam = $_GET['serial_number'] ?? '';
$idParam = $_GET['id'] ?? null;
$partId = is_numeric($idParam) ? (int) $idParam : 0;
$scanError = '';
$part = null;

if ($serialNumberParam !== '') {
    try {
        $serialPart = $partRepo->getPartBySerial((string) $serialNumberParam);
        if ($serialPart !== null) {
            $targetId = (int) ($serialPart['id'] ?? 0);
            if ($targetId > 0 && $targetId !== $partId) {
                header('Location: index.php?page=part_detail&id=' . $targetId);
                exit;
            }
            $partId = $targetId;
            $part = $serialPart;
        } else {
            $scanError = 'Seriennummer nicht gefunden: ' . $serialNumberParam;
        }
    } catch (Throwable $e) {
        $ctx['logger']->error('Fehler beim Laden eines Teils per Seriennummer', [
            'serial_number' => $serialNumberParam,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        $scanError = 'Seriennummer nicht gefunden: ' . $serialNumberParam;
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
];

require __DIR__ . '/../../templates/layout.php';
