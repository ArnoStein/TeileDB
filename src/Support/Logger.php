<?php
declare(strict_types=1);

namespace App\Support;

use RuntimeException;

class Logger
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $dir = dirname($filePath);
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            throw new RuntimeException('Log directory could not be created: ' . $dir);
        }
    }

    /**
     * Append-only logger. Writes one JSON line per entry.
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $entry = [
            'ts' => date('c'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
        ];

        $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($line === false) {
            $line = json_encode([
                'ts' => date('c'),
                'level' => 'ERROR',
                'message' => 'Failed to encode log entry',
            ]);
        }

        $result = @file_put_contents($this->filePath, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        if ($result === false) {
            throw new RuntimeException('Failed to write to log file: ' . $this->filePath);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
}
