<?php
declare(strict_types=1);

namespace App\Db;

use RuntimeException;

class Config
{
    public static function load(string $projectRoot): array
    {
        $config = require $projectRoot . '/config/config.example.php';
        if (!is_array($config)) {
            throw new RuntimeException('Konfiguration konnte nicht geladen werden.');
        }

        $localConfigFile = $projectRoot . '/config/config.local.php';
        if (is_file($localConfigFile)) {
            $localConfig = require $localConfigFile;
            if (is_array($localConfig)) {
                $config = array_replace_recursive($config, $localConfig);
            }
        }

        return $config;
    }
}
