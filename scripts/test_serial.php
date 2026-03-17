<?php
declare(strict_types=1);

use App\Domain\SerialNumber;
use App\Domain\SerialNumberResult;
use App\Domain\Crc16;

require_once __DIR__ . '/../src/Domain/Crc16.php';
require_once __DIR__ . '/../src/Domain/SerialNumberResult.php';
require_once __DIR__ . '/../src/Domain/SerialNumber.php';

$payload = '1A2B3C4D';
$crc = Crc16::crcHexForPayload($payload);
$validBarcode = 'G' . $payload . '-' . $crc;

echo "== CRC Tests ==\n";
// Hinweis: Offizieller Vektor (ASCII "123456789" => 29B1) bezieht sich auf Rohbytes.
// Hier zum Vergleich mit der implementierten Routine über Payload-Bytes:
$crcAscii = crcAscii('123456789');
$crcAsciiOk = $crcAscii === '29B1';
echo sprintf("CRC(ASCII '123456789') => %s (%s)\n", $crcAscii, $crcAsciiOk ? 'OK' : 'FEHLER');

echo "\n== Parse Tests ==\n";
$cases = [
    ['Direktes 8HEX', '1a2b3c4d'],
    ['Mit Bindestrichen', '1a-2b-3c-4d'],
    ['Barcode gültig', $validBarcode],
    ['Barcode Beispiel aus Spec', 'G00010000-29B1'],
    ['Barcode Beispiel 2', 'G00000001-94E1'],
    ['Barcode CRC falsch', 'G' . $payload . '-0000'],
    ['Barcode Version H', 'H' . $payload . '-' . $crc],
    ['Legacy SN Prefix', 'SN:00-00-00-01'],
    ['Legacy SN Kleinbuchstaben', 'SN:ab-cd-ef-01'],
    ['Legacy SN Invalid', 'SN:00-00-00-0G'],
    ['Leerzeichen Fehler', '1A2B 3C4D'],
    ['Zu kurz', '1234'],
];

foreach ($cases as [$label, $input]) {
    $result = SerialNumber::parse($input);
    outputResult($label, $input, $result);
}

function outputResult(string $label, string $input, SerialNumberResult $result): void
{
    if ($result->ok) {
        echo sprintf("[%s] %s => OK, kanonisch: %s\n", $label, $input, $result->canonical);
    } else {
        echo sprintf("[%s] %s => FEHLER (%s): %s\n", $label, $input, $result->errorCode, $result->errorMessage);
    }
}

function crcAscii(string $text): string
{
    $crc = 0xFFFF;
    $poly = 0x1021;

    $len = strlen($text);
    for ($i = 0; $i < $len; $i++) {
        $crc ^= (ord($text[$i]) << 8);
        for ($bit = 0; $bit < 8; $bit++) {
            $msbSet = ($crc & 0x8000) !== 0;
            $crc = ($crc << 1) & 0xFFFF;
            if ($msbSet) {
                $crc ^= $poly;
            }
        }
    }

    return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
}
