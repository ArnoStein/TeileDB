<?php
declare(strict_types=1);

use App\Auth\Auth;
use App\Repository\UserRepository;
use Throwable;


Auth::requireLogin();

$userId = $_SESSION['user_id'] ?? 0;
$userRepo = new UserRepository($ctx['pdo']);
$errors = [];
$success = isset($_GET['updated']) && $_GET['updated'] === '1';
$setupMode = false;

try {
    $user = $userRepo->findById((int) $userId);
} catch (Throwable $e) {
    $ctx['logger']->error('Fehler beim Laden des aktuellen Benutzers', [
        'user_id' => $userId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    $user = null;
}

if ($user === null) {
    Auth::logout();
    header('Location: index.php?page=login');
    exit;
}

$setupMode = ($user['password_hash'] === null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $newPasswordConfirm = (string) ($_POST['new_password_confirm'] ?? '');

    if (!$setupMode) {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        if ($currentPassword === '') {
            $errors['current_password'] = 'Aktuelles Passwort ist erforderlich.';
        } elseif (!password_verify($currentPassword, (string) $user['password_hash'])) {
            $errors['current_password'] = 'Aktuelles Passwort ist falsch.';
        }
    }

    if ($newPassword === '') {
        $errors['new_password'] = 'Neues Passwort ist erforderlich.';
    } elseif (mb_strlen($newPassword) < 4) {
        $errors['new_password'] = 'Mindestens 4 Zeichen.';
    } elseif (mb_strlen($newPassword) > 200) {
        $errors['new_password'] = 'Maximal 200 Zeichen.';
    }
    if ($newPasswordConfirm !== $newPassword) {
        $errors['new_password_confirm'] = 'Passwörter stimmen nicht überein.';
    }

    if ($errors === []) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        try {
            $userRepo->setPasswordHash((int) $user['id'], $hash);
            $userRepo->updateLastLogin((int) $user['id']);
            Auth::login((int) $user['id']);
            header('Location: index.php?page=account&updated=1');
            exit;
        } catch (Throwable $e) {
            $ctx['logger']->error('Fehler beim Passwort-Update', [
                'user_id' => $user['id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $errors['global'] = 'Passwort konnte nicht geändert werden.';
        }
    }
}

$pageTitle = 'Passwort verwalten';
$contentTemplate = __DIR__ . '/../../templates/account.php';
$viewData = [
    'user' => $user,
    'setupMode' => $setupMode,
    'errors' => $errors,
    'success' => $success,
    'isLoggedIn' => Auth::check(),
];

require __DIR__ . '/../../templates/layout.php';
