# Use Cases / Workflows (TeileDB)

Dieses Dokument beschreibt die typischen Abläufe (Use-Cases) der Anwendung TeileDB.
Fokus: einfache Bedienung, robuste Fehlerbehandlung, nachvollziehbare Daten.

Grundregeln:
- `part_comments` sind **unveränderlich** (Journal-Ansatz). Korrekturen erfolgen durch einen **neuen** Kommentar.
- Seriennummern: **Phase 1** erlaubt jede Zeichenkette (bis max. Länge), nur **Eindeutigkeit** + **Länge** wird geprüft. Spätere Schema-Prüfung ist möglich, aber aktuell nicht aktiv.
- Performance ist kein Zielkriterium, Stabilität und Verständlichkeit sind wichtiger.

---

## UC-01: Part-Type anlegen (selten / Stammdatenpflege)

**Ziel:** Einen neuen Eintrag in `part_types` anlegen, damit Teile später diesem Typ zugeordnet werden können.

**Akteur:** Nutzer (später ggf. Admin, falls Login eingeführt wird)

**Häufigkeit:** selten

### Vorbedingungen
- Datenbank ist erreichbar.
- Tabelle `part_types` existiert.

### Ablauf (GUI)
1. Nutzer öffnet Seite „Part-Types verwalten“ (Stammdaten).
2. Anwendung zeigt Liste vorhandener Part-Types (Name, optional Beschreibung, created_at).
3. Nutzer klickt „Neuen Part-Type anlegen“.
4. Nutzer füllt Formularfelder aus (z.B. Name/Bezeichnung, optional Kurztext).
5. Nutzer klickt „Speichern“.
6. Anwendung validiert Eingaben:
   - Pflichtfelder vorhanden
   - Längenbegrenzungen eingehalten
   - Eindeutigkeit (falls Part-Type-Name eindeutig sein soll)
7. Anwendung speichert den neuen Datensatz in `part_types`.
8. Anwendung zeigt Erfolgsmeldung und aktualisierte Liste.

### Alternativ-/Fehlerfälle
- **Validierung fehlgeschlagen:** Formular wird erneut angezeigt, fehlerhafte Felder werden markiert, Daten bleiben erhalten.
- **Duplicate/UNIQUE verletzt:** Benutzerfreundliche Meldung (z.B. „Part-Type existiert bereits“).
- **DB nicht erreichbar / SQL-Fehler:** Nutzer sieht eine generische Fehlermeldung; Details werden geloggt.

### Ergebnis
- Neuer Datensatz in `part_types` vorhanden.

---

## UC-02: Teil anlegen (Hauptfunktion)

**Ziel:** Ein neues Teil in der Datenbank anlegen.

**Akteur:** Nutzer

**Häufigkeit:** häufig

### Vorbedingungen
- Mindestens ein `part_type` existiert (oder Typ ist optional, je nach Schema).
- Status-Liste existiert (z.B. Tabelle `statuses` oder definierte Statuswerte).

### Ablauf (GUI)
1. Nutzer öffnet „Teil anlegen“.
2. Anwendung zeigt Formular mit Feldern (Beispiel):
   - `serial_number`:
      - wird defensiv normalisiert (`trim()`)
      - Länge ≤ 20 Zeichen
      - enthält **keine Leerzeichen**
      - ist eindeutig (UNIQUE)
   - `part_type` (Dropdown)
   - `status` (Dropdown; initialer Status)
3. Nutzer füllt Formular aus und klickt „Speichern“.
4. Anwendung validiert:
   - Seriennummer nicht leer
   - Längenbegrenzungen eingehalten
   - keine Leerzeichen
   - Eindeutigkeit von `serial_number`
5. Anwendung speichert das Teil in `parts` und setzt `created_at`.
6. Anwendung leitet weiter zur Teil-Detailseite (UC-03) oder zeigt Erfolgsseite.

### Alternativ-/Fehlerfälle
- **Pflichtfeld fehlt / Länge überschritten:** Formular wird erneut angezeigt, Fehler werden angezeigt, Eingaben bleiben erhalten.
- **UNIQUE verletzt (Name/Seriennummer):** klare Meldung (z.B. „Seriennummer existiert bereits“).
- **DB-Fehler:** generische Fehlermeldung; Details werden geloggt.

### Ergebnis
- Neuer Datensatz in `parts` existiert.

---

## UC-03: Teil-Detailseite – Status anzeigen/ändern + Kommentare anzeigen + Kommentar hinzufügen (Hauptfunktion)

**Ziel:** Den aktuellen Status eines Teils sehen und ändern sowie Kommentare (Journal) sehen und neue Kommentare hinzufügen.

**Akteur:** Nutzer

**Häufigkeit:** sehr häufig

### Vorbedingungen
- Teil existiert (`parts.id` ist bekannt, z.B. über Liste/URL).
- Statuswerte existieren.
- Tabelle `part_comments` existiert.

### Ablauf (GUI)
1. Nutzer öffnet Teil-Detailseite (z.B. `index.php?page=part_detail&id=123`).
2. Anwendung lädt Teil-Daten und zeigt:
   - Seriennummer, Teiltyp (short_name + name), created_at
   - aktuellen Status (lesbar)
3. Anwendung lädt alle Kommentare zu diesem Teil aus `part_comments` und zeigt sie **neuester zuerst**.
4. Seite bietet zwei Aktionen:
   - **Status ändern** (Dropdown + Button „Status speichern“)
   - **Neuen Kommentar hinzufügen** (Textfeld + Button „Kommentar speichern“)

### Alternativer Einstieg (Scanner-first)
- Oben auf der Detailseite steht ein GET-Formular `serial_number` (autofocus).
- Handscanner füllt das Feld und sendet Enter → Request an `index.php?page=part_detail&serial_number=...`.
- Anwendung sucht das Teil exakt per `parts.serial_number` (keine Normalisierung) und zeigt dessen Detailseite; bei Treffer erfolgt Redirect auf `?page=part_detail&id=...`.
- Falls nicht gefunden, erscheint „Seriennummer nicht gefunden“ über den Details, aktuelle Seite bleibt.

#### 3A – Status ändern
5. Nutzer wählt neuen Status und klickt „Status speichern“.
6. Anwendung validiert:
   - Neuer Status ist ein gültiger Statuswert
   - Teil existiert weiterhin
7. Anwendung aktualisiert den Status des Teils (genau ein Status je Teil).
8. Anwendung zeigt Erfolgsmeldung und aktualisierte Anzeige.

#### 3B – Neuen Kommentar hinzufügen
5. Nutzer schreibt Kommentartext und klickt „Kommentar speichern“.
6. Anwendung validiert:
   - Kommentartext nicht leer (und ggf. minimale/maximale Länge, falls definiert)
7. Anwendung schreibt neuen Datensatz in `part_comments`:
   - `part_id`
   - `comment`
   - `created_at`
8. Anwendung zeigt Erfolgsmeldung und die aktualisierte Kommentarliste.

### Regeln / Design-Entscheidungen
- Kommentare sind **unveränderlich**:
  - Kein Bearbeiten, kein Löschen in der GUI.
  - Korrekturen erfolgen über einen neuen Kommentar („Korrektur: …“).
- Status ist genau **ein** Wert pro Teil:
  - Änderung ersetzt den bisherigen Status (keine Historie in v1).
  - Eine Status-Historie könnte später ergänzt werden (z.B. eigene Tabelle).

### Alternativ-/Fehlerfälle
- **Teil-ID fehlt/ungültig:** Nutzer bekommt eine klare Meldung („Teil nicht gefunden“), HTTP 404 möglich; Logeintrag mit Kontext.
- **Status ungültig:** Meldung („Ungültiger Status“), keine DB-Änderung.
- **Kommentar leer:** Meldung („Bitte Kommentar eingeben“), keine DB-Änderung.
- **DB-Fehler:** generische Fehlermeldung; Details werden geloggt.
- **Race Condition (zwei Nutzer ändern gleichzeitig):** In v1 akzeptabel („letzter gewinnt“). Später könnte man optimistic locking ergänzen.

### Ergebnis
- Status ist ggf. aktualisiert.
- Kommentar ist ggf. als neuer Datensatz in `part_comments` gespeichert.

---

## UC-04: Teileliste anzeigen / filtern (unterstützend)

**Ziel:** Teile finden und zur Detailseite springen.

**Akteur:** Nutzer

### Ablauf (GUI)
1. Nutzer öffnet „Teileliste“ (`index.php?page=parts_list`).
2. Anwendung zeigt tabellarisch (Beispiel): short_name, name, Seriennummer, Typ, Status, created_at.
3. Nutzer kann optional filtern/suchen:
   - nach Status (Dropdown mit Status-Namen)
   - nach Name/Short-Name
   - nach Seriennummer
4. Nutzer klickt auf ein Teil → öffnet UC-03.

### Fehlerfälle
- DB-Fehler → generische Meldung; Details geloggt.

---

## Offene Punkte (bewusst später)
- Login/Benutzerverwaltung:
  - Optionaler Schutz bestimmter Bereiche (z.B. Stammdatenpflege).
  - Session-basiert, Passwörter nur als Hash.
- Seriennummer-Schema-Validierung:
  - Phase 2: Validierung nach definierter Regel (z.B. Regex).
  - Optionaler Generator ist möglich, aber aktuell nicht festgelegt.
- Status-Historie:
  - Optional: Historie in separater Tabelle, um Änderungen nachzuverfolgen.
