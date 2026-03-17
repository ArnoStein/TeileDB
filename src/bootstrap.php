<?php
declare(strict_types=1);

use App\Db\Config;
use App\Db\Connection;
use App\Support\Logger;
use Throwable;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

date_default_timezone_set('Europe/Berlin');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$projectRoot = dirname(__DIR__);
$config = Config::load($projectRoot);
$logger = new Logger($config['log_file'] ?? ($projectRoot . '/storage/logs/app.log'));

set_exception_handler(function (Throwable $e) use ($logger): void {
    $context = [
        'type' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ];

    $previous = $e->getPrevious();
    if ($previous instanceof Throwable) {
        $context['previous'] = [
            'type' => get_class($previous),
            'message' => $previous->getMessage(),
            'code' => $previous->getCode(),
            'file' => $previous->getFile(),
            'line' => $previous->getLine(),
            'trace' => $previous->getTraceAsString(),
        ];
    }

    $logger->error('Unhandled exception', $context);

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
