# TeileDB (Programm_TeileDB_PHP)

## Ziel
TeileDB ist eine einfache, robuste Webanwendung zur Verwaltung selbst aufgebauter/entwickelter Teile (z.B. Elektronikbaugruppen).
Die Anwendung läuft auf einem Webspace (IONOS) mit PHP und einer SQL-Datenbank. Zugriff erfolgt über den Browser.

Schwerpunkt: **Verständlichkeit, Stabilität, saubere Fehlerbehandlung**. Performance ist kein Kriterium.

## Hauptfunktionen (Stand jetzt)
- Teile anlegen (Seriennummer Pflicht, eindeutig)
- Teileliste mit Status-Filter (Dropdown nach Status-Namen) und Suche (Serial/Typ)
- Teil-Detailseite: Status anzeigen/ändern, Kommentare anzeigen/hinzufügen
- Seriennummern verwalten (Text ≤ 20, eindeutig)
- Optional (später): Login/Benutzerverwaltung

## Datenmodell (Kurzüberblick)
Stammdaten:
- `part_types`: `short_name` (Pflicht, ≤20), `name` (≤50)
- `statuses`: `name`, optionale Reihenfolge

Teile (`parts`):
- `serial_number` (eindeutig, Text ≤20)
- `part_type_id` (FK → `part_types`)
- `status_id` (FK → `statuses`, genau einer)
- `created_at`

Seriennummern:
- **Formulare (z.B. Teil anlegen):** Eingaben werden getrimmt, max. 20 Zeichen, keine Leerzeichen, müssen eindeutig sein.
- **Scan-Feld auf Detailseite:** Eingabe wird **nicht** normalisiert (kein trim/upper/whitespace-removal); Wert wird 1:1 verwendet.
- **Phase 2 (später):** optionale Validierung gegen ein Seriennummer-Schema (Regex) / Generator denkbar.

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

Routing:
- Einstieg über `public/index.php?page=...` (z.B. `?page=parts_list`, `?page=part_detail&id=123`).


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

## Roadmap (aktueller Stand)
Vorhanden:
1. Projektgerüst + Konfiguration + DB-Verbindung
2. Zentrales Error-Handling + Logging
3. Teileliste mit Status-Filter (Dropdown) und Suche
4. Teil anlegen (Create) mit Seriennummer-Regeln (Trim, keine Leerzeichen, UNIQUE)
5. Teil-Detailseite: Status ändern, Kommentare (anzeigen + hinzufügen), Scan-Einstieg per `serial_number`

Optional später:
- Seriennummer-Schema validieren (konfigurierbar)
- Login/Benutzerverwaltung (Tabelle `users`, Session-basiert)
- Status-Historie, ggf. weitere Pflegefunktionen

## Installation / Setup
Datenbank (Schema) anlegen: TeileDB
Benutzer/Rechte: User mit allen Rechten auf diese DB
DB-Name ist konfigurierbar
