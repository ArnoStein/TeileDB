<?php
declare(strict_types=1);

use App\Support\Logger;
use App\Db\Connection;
use RuntimeException;
use Throwable;

require_once __DIR__ . '/Support/Logger.php';
require_once __DIR__ . '/Db/Connection.php';

date_default_timezone_set('Europe/Berlin');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = require __DIR__ . '/../config/config.example.php';
if (!is_array($config)) {
    throw new RuntimeException('Konfiguration konnte nicht geladen werden.');
}
$localConfigFile = __DIR__ . '/../config/config.local.php';
// config.local.php enthält sensible Daten und gehört nicht ins Repo.
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        $config = array_replace_recursive($config, $localConfig);
    }
}

$logger = new Logger($config['log_file'] ?? (__DIR__ . '/../storage/logs/app.log'));

set_exception_handler(function (Throwable $e) use ($logger): void {
    $logger->error('Unhandled exception', [
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.';
});

$pdo = Connection::create($config['db']);

return [
    'config' => $config,
    'logger' => $logger,
    'pdo' => $pdo,
];
