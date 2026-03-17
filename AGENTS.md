# AGENTS.md — Programm_TeileDB_PHP

Dieses Repository wird von mehreren Agenten bearbeitet. Ziel ist klare Zuständigkeit, minimale Überschneidung und maximale Wiederverwendung (Shared Core).

## 1) Grundprinzipien

- **Ein Repo, drei Oberflächen**: Web-UI, JSON-API, Python-Client.
- **Shared Core ist die einzige Stelle** für DB-Zugriff, Validierung, Serial-/Barcode-Logik und Daten-Mapping.
- **Web-UI und API greifen nie direkt auf PDO/SQL zu**, sondern ausschließlich über `src/Repository/*`.
- **Keine Breaking Changes ohne Versionierung** (API & Python-Client).

## 2) Verzeichnis-Struktur (Soll)

- `public/`
  - `index.php`  → Web-UI Entry
  - `api.php`    → JSON-API Entry
- `src/`
  - `Db/`        → PDO Connection, Config-Laden
  - `Repository/`→ SQL/Queries, DTO/Mapper (read-only und ggf. write)
  - `Domain/`    → Serial/Barcode Parsing, Formatierung, Validierung
  - `Web/`       → Controller/UseCases fürs Web
  - `Api/`       → Controller/Responses fürs API
- `templates/`   → Web-Templates
- `config/`      → Konfiguration (Secrets niemals committen)
- `docs/`        → Spezifikationen (API-Vertrag, Datenmodell, Workflows)
- `client_py/`   → Python-Client-Paket + Beispiele
- `storage/logs/`→ Logs (nicht versionieren)

## 3) Agenten & Zuständigkeiten

### Agent: WEB_UI
**Mission:** Webinterface (Masken, Listen, Detailseiten, UX), ohne API zu brechen.

**Darf ändern:**
- `public/index.php`
- `src/Web/**`
- `templates/**`
- `docs/ui.md` (falls vorhanden)

**Darf NICHT:**
- Direkt SQL/DB-Zugriffe in Web-Code einbauen (kein PDO in `src/Web`).
- API-Vertrag (`docs/api.md`) ohne Abstimmung ändern.

**Muss nutzen:**
- `src/Repository/**` für alle DB-Lese-/Schreibvorgänge
- `src/Domain/**` für Serial-/Barcode-Logik und Formatierungen

---

### Agent: API
**Mission:** Read-only (oder später read/write) JSON-API für externe Clients.

**Darf ändern:**
- `public/api.php`
- `src/Api/**`
- `docs/api.md` (API-Vertrag)
- Auth/Rate Limit Logik (innerhalb API)

**Darf NICHT:**
- Businesslogik in `public/api.php` “hinein-coden” (Entry bleibt dünn).
- Web-Templates/UX verändern (außer minimale Routing-Kollisionen fixen).

**Muss liefern:**
- Konsistentes JSON-Schema: `{ "ok": true, "data": ... }` / `{ "ok": false, "error": { ... } }`
- Fehlercodes stabil halten (z.B. `AUTH_REQUIRED`, `INVALID_ARGUMENT`, `NOT_FOUND`, `INTERNAL_ERROR`)
- API-Key Schutz (mindestens) und Logging ohne Secrets

**Muss nutzen:**
- `src/Repository/**` (keine direkten SQL-Strings in `src/Api`)
- `src/Domain/**` für Normalisierung/Validierung

---

### Agent: PY_CLIENT
**Mission:** Python-Client für komfortable Abfragen vom beliebigen PC aus (HTTP/JSON).

**Darf ändern:**
- `client_py/**`
- `docs/python_client.md` (falls vorhanden)
- Beispiele in `client_py/examples/**`

**Darf NICHT:**
- PHP-Code ändern (außer durch separate, explizite Tasks)
- API-Vertrag selbst “interpretieren” — Änderungen müssen in `docs/api.md` reflektiert sein

**Muss:**
- Alle Requests über `requests` o.ä. mit Timeout
- Sauberes Error-Handling (HTTP-Fehler → Exceptions)
- Optionale Auth via Header `X-API-Key`
- Kompatibilität zur API-Version (siehe Versionierung)

## 4) Shared Core Regeln (für ALLE Agenten)

### `src/Db/**`
- Enthält *nur* DB/Config/Connection concerns.
- Zugangsdaten kommen aus `config/config.local.php` (nicht committen).

### `src/Repository/**`
- Enthält alle SQL-Statements.
- Nur Prepared Statements.
- Methoden benennen nach Use-Case: `findBySerial()`, `listParts($filter)`, …

### `src/Domain/**`
- Enthält:
  - Serial-Formatierung (Anzeige `xx-xx-xx-xx`)
  - Parsing/Normalisierung (inkl. Legacy-Formate, falls unterstützt)
  - Barcode/CRC-bezogene Logik (sofern genutzt)
- Keine DB-Zugriffe.

**Breaking-Rule:** Änderungen in `Domain`/`Repository`, die API oder Web beeinflussen könnten, erfordern:
1) Anpassung Tests/Beispiele (falls vorhanden)
2) Anpassung `docs/api.md` (wenn API betroffen)
3) Versionsbump (siehe unten)

## 5) API-Vertrag & Versionierung

### API Version
- API hat eine explizite Version: `docs/api.md` und optional Response-Feld `"api_version"`.
- Breaking Changes: Major bump
- Abwärtskompatible Erweiterungen: Minor bump
- Bugfix: Patch bump

### Python Client Version
- Python Client folgt SemVer und deklariert unterstützte API-Version(en) in README.

## 6) Security Mindeststandard

- API ist nicht öffentlich offen: mindestens **API-Key Pflicht**.
- Secrets nie in Git. Keine Keys/Passwörter in Logs.
- Rate-Limit optional, aber empfohlen (später).

## 7) Qualitätsregeln / Coding Standards

- PHP: strikte Trennung Entry/Controller/Repository/Domain.
- Keine Duplikation: Formatierung/Validierung nur in `Domain`.
- Fehlerbehandlung:
  - Web: userfreundliche Meldung
  - API: strukturierte JSON-Errors
- Logging: `storage/logs/` (ohne personenbezogene Daten/Secrets)

## 8) Arbeitsablauf (kurz)

1) Änderungen klein halten, pro Commit ein Thema.
2) Bei Shared-Core Änderungen: Web + API kurz “smoke testen”.
3) Bei API Änderungen: `docs/api.md` aktualisieren + Python-Beispiele prüfen.
4) Merge nur, wenn:
   - Web lädt Startseite und Teileliste
   - API liefert mindestens `op=statuses` und `op=part_by_serial` korrekt
   - Python-Beispielskript läuft gegen API (wenn verfügbar)

## 9) Kommunikationsschnittstellen zwischen Agenten

- Anforderungen an `Repository`-Methoden werden in Issues/Tasks beschrieben.
- API-Agent ist Owner von `docs/api.md`.
- PY_CLIENT-Agent ist Owner von `client_py/` und meldet API-Änderungsbedarf zurück.

## 10) “Do not touch” Liste (ohne expliziten Task)

- Deployment/Hosting-spezifische Dateien, sofern vorhanden (z.B. IONOS-Konfiguration)
- SQL-Migrationen/Setup, außer bei klarer Anforderung
- Produktiv-Config unter `config/*.local.php`