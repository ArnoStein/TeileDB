# TeileDB (Programm_TeileDB_PHP)

## Ziel
TeileDB ist eine einfache, robuste Webanwendung zur Verwaltung selbst aufgebauter/entwickelter Teile (z.B. Elektronikbaugruppen).
Die Anwendung läuft auf einem Webspace (IONOS) mit PHP und einer SQL-Datenbank. Zugriff erfolgt über den Browser.

Schwerpunkt: **Verständlichkeit, Stabilität, saubere Fehlerbehandlung**. Performance ist kein Kriterium.

## Hauptfunktionen (geplant)
- Teile anlegen, anzeigen, bearbeiten
- Teile nach Status filtern/sortieren
- Seriennummern verwalten (eindeutig, Text bis 20 Zeichen)
- Status je Teil: genau ein Status (mit definierter Reihenfolge)
- Optional (später): Login/Benutzerverwaltung, um Zugriff zu schützen

## Datenmodell (Kurzüberblick)
Ein "Teil" besitzt u.a. folgende Felder:
- `name` (eindeutige Bezeichnung, bis 50 Zeichen)
- `short_name` (Pflichtfeld, bis 20 Zeichen)
- `serial_number` (eindeutig, Text bis 20 Zeichen)
- `status` (genau ein Status, Reihenfolge definiert)
- `created_at` (Zeitpunkt der Erstellung)

Seriennummern:
- **Phase 1 (Start):** jede Zeichenkette ist erlaubt (bis max. Länge), solange sie eindeutig ist.
- **Phase 2 (später):** optional Validierung gegen ein definiertes Seriennummer-Schema (z.B. Regex).
- **Seriennummer-Generator:** optional denkbar, aber noch nicht festgelegt.

Optional (später) Login:
- Login-Daten werden in der Datenbank gespeichert (z.B. Tabelle `users`).
- Passwörter werden **nicht im Klartext** gespeichert, sondern als **Hash** (z.B. via `password_hash()` / `password_verify()`).

> Hinweis: Das detaillierte SQL-Schema liegt als Script im Projekt (z.B. unter `/scripts`).

## Nicht-Ziele (bewusst nicht enthalten)
- Keine Mehrsprachigkeit (vorerst)
- Kein Fokus auf Performance-Optimierung
- Kein komplexes Rollen-/Rechtesystem (vorerst)
- Keine Framework-Abhängigkeit (plain PHP)
- Kein Login in der ersten Version (optional später)

## Qualitätsanforderungen / Leitlinien
- Defensive Programmierung: Eingaben validieren, Fehler abfangen, stabile Defaults
- Keine “Magie” / keine cleveren Einzeiler: Code soll für Einsteiger gut lesbar sein
- DB-Zugriff ausschließlich via PDO + Prepared Statements
- Ausgabe in HTML immer escaped (`htmlspecialchars`)
- Trennung von Logik und Templates
- Zentrale Fehlerbehandlung und Logging (Datei-Log unter `/storage/logs`)
Seriennummern:
- **Phase 1 (Start):** beliebige Zeichenkette ist erlaubt (bis max. Länge), solange sie eindeutig ist.
  - Zusätzlich gilt: **keine Leerzeichen** (weder innen noch am Anfang/Ende).
  - Eingaben werden defensiv normalisiert (`trim()`).
- **Phase 2 (später):** optional Validierung gegen ein definiertes Seriennummer-Schema (z.B. Regex).
- **Seriennummer-Generator:** optional denkbar, aber noch nicht festgelegt.


## Technische Basis
- PHP 8.2+
- SQL-Datenbank (IONOS)
- Webserver: IONOS Webspace
- Zugriff: Browser

## Projektstruktur (geplant)
- `/public` – Webroot (nur diese Dateien sind öffentlich erreichbar)
- `/src` – PHP-Quellcode (DB, Services, Domain, etc.)
- `/templates` – HTML/PHP-Templates
- `/config` – Konfiguration (lokal/produktiv)
- `/storage/logs` – Logdateien
- `/scripts` – SQL-Skripte (Tabellen erstellen, Beispiel-Daten)

## Setup (Entwurf)
1. SQL-Schema aus `/scripts` in der Datenbank ausführen
2. `config/config.local.php` anlegen (DB-Zugangsdaten, nicht in Git)
3. Webroot auf `/public` setzen (wenn möglich)
4. Aufruf im Browser: `/public/index.php`

## Roadmap (erster Wurf)
1. Projektgerüst + Konfiguration + DB-Verbindung
2. Zentrales Error-Handling + Logging
3. Seite: Teile-Liste (Read)
4. Seite: Teil anlegen (Create) inkl. einfacher Seriennummer-Regeln (Länge + UNIQUE)
5. Seite: Teil bearbeiten (Update)
6. Filter/Suche nach Status/Name/Seriennummer
7. Optional später:
   - Seriennummer-Schema validieren (konfigurierbar)
   - Login/Benutzerverwaltung (Tabelle `users`, Session-basiert)

## Installation / Setup
Datenbank (Schema) anlegen: TeileDB
Benutzer/Rechte: User mit allen Rechten auf diese DB
DB-Name ist konfigurierbar