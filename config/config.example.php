<?php
declare(strict_types=1);

// Kopiere diese Datei zu config.local.php (nicht ins Repo committen) und passe die Werte an.
return [
    'db' => [
        // Beispiel-DSN anpassen: Host/Port/DB-Name
        'dsn' => 'mysql:host=localhost;dbname=teile_db;charset=utf8mb4',
        'user' => 'db_user',
        'pass' => 'db_password',
    ],
    // Pfad relativ zum Projektwurzelverzeichnis; config.local.php darf dies überschreiben
    'log_file' => __DIR__ . '/../storage/logs/app.log',
];
