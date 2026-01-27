<?php
declare(strict_types=1);

use App\Auth\Auth;
use App\Repository\UserRepository;
use Throwable;

require_once __DIR__ . '/../Auth/Auth.php';
require_once __DIR__ . '/../Repository/UserRepository.php';

if (Auth::check()) {
    header('Location: index.php?page=parts_list');
    exit;
}

$userRepo = new UserRepository($ctx['pdo']);

$errors = [];
$usernameInput = (string) ($_POST['username'] ?? '');
$setupMode = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($usernameInput === '') {
        $errors['username'] = 'Benutzername ist erforderlich.';
    } else {
        try {
            $user = $userRepo->findByUsername($usernameInput);
        } catch (Throwable $e) {
            $ctx['logger']->error('Fehler beim Laden des Benutzers', [
                'username' => $usernameInput,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $errors['global'] = 'Login momentan nicht möglich.';
            $user = null;
        }

        if ($user === null || (int) ($user['is_active'] ?? 0) !== 1) {
            $errors['global'] = 'Ungültige Zugangsdaten.';
        } else {
            $setupMode = ($user['password_hash'] === null);

            if ($setupMode) {
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $newPasswordConfirm = (string) ($_POST['new_password_confirm'] ?? '');

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
                        header('Location: index.php?page=parts_list');
                        exit;
                    } catch (Throwable $e) {
                        $ctx['logger']->error('Fehler beim Setzen des Erst-Passworts', [
                            'user_id' => $user['id'],
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $errors['global'] = 'Passwort konnte nicht gesetzt werden.';
                    }
                }
            } else {
                $password = (string) ($_POST['password'] ?? '');
                if ($password === '') {
                    $errors['password'] = 'Passwort ist erforderlich.';
                } elseif (!password_verify($password, (string) $user['password_hash'])) {
                    $errors['global'] = 'Ungültige Zugangsdaten.';
                }

                if ($errors === []) {
                    try {
                        Auth::login((int) $user['id']);
                        $userRepo->updateLastLogin((int) $user['id']);
                        header('Location: index.php?page=parts_list');
                        exit;
                    } catch (Throwable $e) {
                        $ctx['logger']->error('Fehler beim Login', [
                            'user_id' => $user['id'],
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $errors['global'] = 'Login momentan nicht möglich.';
                    }
                }
            }
        }
    }
}

$pageTitle = 'Login';
$contentTemplate = __DIR__ . '/../../templates/login.php';
$viewData = [
    'errors' => $errors,
    'usernameInput' => $usernameInput,
    'setupMode' => $setupMode,
    'isLoggedIn' => false,
];

require __DIR__ . '/../../templates/layout.php';
