<?php
declare(strict_types=1);

namespace App\Domain;

class SerialFormatter
{
    public static function formatForDisplay(string $serial8Hex): string
    {
        if (strlen($serial8Hex) !== 8) {
            return $serial8Hex;
        }

        return substr($serial8Hex, 0, 2)
            . '-' . substr($serial8Hex, 2, 2)
            . '-' . substr($serial8Hex, 4, 2)
            . '-' . substr($serial8Hex, 6, 2);
    }
}
