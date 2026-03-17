<?php
declare(strict_types=1);

use App\Repository\PartTypeRepository;
use App\Auth\Auth;
use Throwable;


Auth::requireLogin();

$repository = new PartTypeRepository($ctx['pdo']);

$errors = [];
$formData = [
    'name' => '',
    'short_name' => '',
    'description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = trim((string) ($_POST['name'] ?? ''));
    $formData['short_name'] = trim((string) ($_POST['short_name'] ?? ''));
    $formData['description'] = trim((string) ($_POST['description'] ?? ''));

    if ($formData['name'] === '') {
        $errors['name'] = 'Name ist erforderlich.';
    } elseif (mb_strlen($formData['name']) > 50) {
        $errors['name'] = 'Name darf höchstens 50 Zeichen lang sein.';
    }

    if ($formData['short_name'] === '') {
        $errors['short_name'] = 'Kurzname ist erforderlich.';
    } elseif (mb_strlen($formData['short_name']) > 20) {
        $errors['short_name'] = 'Kurzname darf höchstens 20 Zeichen lang sein.';
    } elseif (preg_match('/\s/', $formData['short_name'])) {
        $errors['short_name'] = 'Kurzname darf keine Leerzeichen enthalten.';
    }

    if (isset($formData['description']) && $formData['description'] === '') {
        $formData['description'] = null;
    }

    if ($errors === []) {
        try {
            $repository->createPartType([
                'name' => $formData['name'],
                'short_name' => $formData['short_name'],
                'description' => $formData['description'],
            ]);

            header('Location: index.php?page=part_types_list');
            exit;
        } catch (Throwable $e) {
            $prev = $e->getPrevious();
            if ($e->getMessage() === 'duplicate_part_type' || ($prev instanceof \PDOException && $prev->getCode() === '23000')) {
                $errors['global'] = 'Name oder Kurzname existiert bereits.';
                $ctx['logger']->error('Duplicate part type', [
                    'name' => $formData['name'],
                    'short_name' => $formData['short_name'],
                    'error' => $e->getMessage(),
                ]);
            } else {
                $errors['global'] = 'Speichern nicht möglich. Bitte später erneut versuchen.';
                $ctx['logger']->error('Fehler beim Anlegen eines Teiltyps', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }
}

$pageTitle = 'Teiltyp anlegen';
$contentTemplate = __DIR__ . '/../../templates/part_types_create.php';
$viewData = [
    'errors' => $errors,
    'formData' => $formData,
    'isLoggedIn' => Auth::check(),
];

require __DIR__ . '/../../templates/layout.php';
