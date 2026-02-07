# spec-crc.md
Stand: 2026-02-07  
Zweck: Eindeutige Spezifikation für Seriennummern-Strings (SNR) inkl. Versionierung und CRC-Prüfsumme (CRC-16/CCITT-FALSE).

---

## 1. Überblick

Dieses Dokument beschreibt das Stringformat für Seriennummern, wie sie z.B. in einem DataMatrix-Code abgelegt werden.
Das Format ist versionsfähig über ein führendes **Versionszeichen** (aktuell: `G`) und enthält eine Prüfsumme (**CRC16**).

---

## 2. Format: Version G (8HEX + CRC16)

### 2.1 String-Layout

**Version G:**

G<8HEX>-<4HEX>

**Beispiel:**

G00000001-94E1

### 2.2 Felddefinitionen

| Feld | Länge | Zeichenmenge | Beschreibung |
|------|------:|--------------|--------------|
| `G` | 1 | fest `G` | Versionskennung |
| `<8HEX>` | 8 | `0-9A-F` (empfohlen uppercase) | Seriennummer als 8-stellige Hex-Darstellung (führende Nullen erlaubt) |
| `-` | 1 | fest `-` | Trennzeichen |
| `<4HEX>` | 4 | `0-9A-F` (empfohlen uppercase) | CRC16-Ergebnis als 4-stellige Hex-Darstellung |

**Gesamtlänge:** 14 Zeichen

---

## 3. CRC-Spezifikation (CRC-16/CCITT-FALSE)

### 3.1 Algorithmus-Parameter

- **Name:** CRC-16/CCITT-FALSE
- **Polynom:** `0x1021`
- **Init:** `0xFFFF`
- **RefIn:** `false`
- **RefOut:** `false`
- **XorOut:** `0x0000`

### 3.2 CRC-Eingabedaten (für Version G)

Für Version `G` wird die CRC **nur** über den Seriennummern-Teil `<8HEX>` berechnet.

**Wichtig:** Die CRC wird über die **dekodierten Bytes** der Payload berechnet (nicht über die ASCII-Zeichen der Hex-Darstellung).

- Eingabe = `hex_to_bytes(<8HEX>)` → **4 Bytes**
- Byte-Reihenfolge = so wie die Hex-Zeichen gelesen werden (big-endian / „von links nach rechts“).
- **Nicht** enthalten in der CRC-Berechnung:
  - das Versionszeichen (`G`)
  - das Trennzeichen (`-`)
  - die CRC selbst (`<4HEX>`)

**Beispiele:**
- `<8HEX> = "00000001"` → Eingabebytes: `00 00 00 01` → CRC: `94E1`
- `<8HEX> = "00010000"` → Eingabebytes: `00 01 00 00` → CRC: `B3F0`

### 3.3 CRC-Ausgabeformat

- 16-bit CRC-Ergebnis wird als **4 Hex-Zeichen** kodiert
- Empfohlen: **uppercase**
- Beispiel: `94E1`

---

## 4. Validierungsregeln (Decoder)

Ein String ist gültig, wenn:

1. Gesamtlänge = 14 (ohne Zeilenendezeichen)
2. `s[0] == 'G'`
3. `s[9] == '-'`
4. `s[1:9]` besteht ausschließlich aus Hex-Zeichen (`0-9A-F`, tolerant auch `a-f`)
5. `s[10:14]` besteht ausschließlich aus Hex-Zeichen (`0-9A-F`, tolerant auch `a-f`)
6. `CRC16_CCITT_FALSE( hex_to_bytes(s[1:9]) ) == hex_to_uint16(s[10:14])`

### 4.1 Zeilenendezeichen vom Scanner

Viele Scanner senden am Ende ein Zeilenende (`\r`, `\n` oder `\r\n`).  
Decoder dürfen **nur** diese finalen Zeilenendezeichen entfernen, bevor Regel 1–6 geprüft werden.
Andere Whitespaces innerhalb oder außerhalb der Nutzdaten gelten als ungültiges Format.

---

## 5. Referenz-Check (Algorithmus-Testvektor)

Der Standard-Testvektor für CRC-16/CCITT-FALSE muss erfüllt sein:

- Eingabe (ASCII-Bytes): `"123456789"`
- Erwartete CRC: `0x29B1`

Dieser Check dient zur Absicherung, dass Implementierungen denselben CRC-Algorithmus verwenden.

---

## 6. Versionierung / Ausblick

- Das führende Versionszeichen definiert **vollständig** das Parsing- und CRC-Verfahren.
- Zukünftige Versionen (z.B. `H`, `J`, `K`, …) dürfen andere Längen, andere CRCs oder andere Nutzdatenformate definieren.
- Empfehlung für Versionszeichen: keine leicht verwechselbaren Zeichen wie `I`/`O`.

---

