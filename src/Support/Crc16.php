<?php
declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

class Crc16
{
    private const POLYNOMIAL = 0x1021; // CRC-16/CCITT-FALSE
    private const INITIAL_VALUE = 0xFFFF;
    private const XOR_OUT = 0x0000;

    /**
     * @param string $payload8HexUpper Eight uppercase hexadecimal characters representing four bytes.
     */
    public static function crcHexForPayload(string $payload8HexUpper): string
    {
        if (preg_match('/^[0-9A-F]{8}$/', $payload8HexUpper) !== 1) {
            throw new InvalidArgumentException('Payload must be exactly 8 hexadecimal characters (uppercase).');
        }

        // NOTE: CRC is computed over decoded payload bytes (hex2bin), not over ASCII hex characters.
        // TODO: Align wording in spec-crc.md to clarify byte-based input if it still mentions ASCII chars.
        $binary = hex2bin($payload8HexUpper);
        if ($binary === false) {
            throw new InvalidArgumentException('Payload hex string could not be decoded.');
        }

        $crc = self::INITIAL_VALUE;

        $length = strlen($binary);
        for ($i = 0; $i < $length; $i++) {
            $byte = ord($binary[$i]);
            $crc ^= ($byte << 8);

            for ($bit = 0; $bit < 8; $bit++) {
                $msbSet = ($crc & 0x8000) !== 0;
                $crc = ($crc << 1) & 0xFFFF;
                if ($msbSet) {
                    $crc ^= self::POLYNOMIAL;
                }
            }
        }

        $crc ^= self::XOR_OUT;

        return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
    }
}
