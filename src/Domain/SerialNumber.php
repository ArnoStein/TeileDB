<?php
declare(strict_types=1);

namespace App\Domain;

use App\Support\Crc16;

class SerialNumber
{
    public static function parse(string $input): SerialNumberResult
    {
        if (preg_match('/^[0-9A-Fa-f]{8}$/', $input) === 1) {
            return SerialNumberResult::success(strtoupper($input));
        }

        if (preg_match('/^[0-9A-Fa-f]{2}-[0-9A-Fa-f]{2}-[0-9A-Fa-f]{2}-[0-9A-Fa-f]{2}$/', $input) === 1) {
            $canonical = strtoupper(str_replace('-', '', $input));

            return SerialNumberResult::success($canonical);
        }

        if (preg_match('/^SN:[0-9A-Fa-f]{2}-[0-9A-Fa-f]{2}-[0-9A-Fa-f]{2}-[0-9A-Fa-f]{2}$/i', $input) === 1) {
            $withoutPrefix = substr($input, 3); // remove "SN:"
            $canonical = strtoupper(str_replace('-', '', $withoutPrefix));

            return SerialNumberResult::success($canonical);
        }

        if (preg_match('/^([A-Za-z])([0-9A-Fa-f]{8})-([0-9A-Fa-f]{4})$/', $input, $matches) === 1) {
            $version = strtoupper($matches[1]);
            $payload = strtoupper($matches[2]);
            $crc = strtoupper($matches[3]);

            if ($version !== 'G') {
                return SerialNumberResult::fail('unsupported_version', 'Unbekannte Barcode-Version');
            }

            $calculated = Crc16::crcHexForPayload($payload);
            if ($calculated !== $crc) {
                return SerialNumberResult::fail('crc_mismatch', 'CRC-Prüfung fehlgeschlagen');
            }

            return SerialNumberResult::success($payload);
        }

        return SerialNumberResult::fail('invalid_format', 'Ungültiges Format');
    }
}
