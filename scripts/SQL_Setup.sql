-- Optional: saubere Reihenfolge bei erneutem Ausführen (löscht Tabellen!)
-- Achtung: nur benutzen, wenn du sicher bist.
-- DROP TABLE IF EXISTS part_comments;
-- DROP TABLE IF EXISTS parts;
-- DROP TABLE IF EXISTS statuses;
-- DROP TABLE IF EXISTS part_types;

-- 1) Teiltypen / Bezeichnungen
CREATE TABLE part_types (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  short_name VARCHAR(20) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_part_types_name (name),
  UNIQUE KEY uq_part_types_short_name (short_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Status-Liste
CREATE TABLE statuses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  sort_order INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_statuses_name (name),
  UNIQUE KEY uq_statuses_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Teile / Einzelexemplare (Seriennummern)
CREATE TABLE parts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  serial_number VARCHAR(20) NOT NULL,

  part_type_id INT UNSIGNED NOT NULL,
  status_id INT UNSIGNED NOT NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY uq_parts_serial_number (serial_number),
  KEY idx_parts_part_type_id (part_type_id),
  KEY idx_parts_status_id (status_id),

  CONSTRAINT fk_parts_part_type
    FOREIGN KEY (part_type_id) REFERENCES part_types(id)
    ON UPDATE RESTRICT ON DELETE RESTRICT,

  CONSTRAINT fk_parts_status
    FOREIGN KEY (status_id) REFERENCES statuses(id)
    ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Kommentare zu Teilen
CREATE TABLE part_comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  part_id INT UNSIGNED NOT NULL,
  comment TEXT NOT NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  KEY idx_part_comments_part_id (part_id),

  CONSTRAINT fk_part_comments_part
    FOREIGN KEY (part_id) REFERENCES parts(id)
    ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) Logon-Daten für Benutzer
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at TIMESTAMP NULL,
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users
  ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin' AFTER password_hash;

  

-- Optional: Beispiel-Startdaten (kannst du drin lassen oder entfernen)

INSERT INTO part_types (name, short_name, description)
VALUES
('Steuerplatine Rev. B', 'CTRL_B', 'Controller-Platine, Revision B'),
('Motorhalter links', 'MOT_L', 'Halterung für Motor, linke Seite');

INSERT INTO statuses (name, sort_order)
VALUES
('Geplant', 10),
('In Arbeit', 20),
('Fertig', 30),
('Defekt', 40),
('Ausgemustert', 50);

-- Beispiel-Teil anlegen (nutzt short_name + Statusname)
INSERT INTO parts (serial_number, part_type_id, status_id)
VALUES (
  'SN-2026-0001',
  (SELECT id FROM part_types WHERE short_name = 'CTRL_B'),
  (SELECT id FROM statuses WHERE name = 'Geplant')
);

-- Beispiel-Kommentar
INSERT INTO part_comments (part_id, comment)
VALUES (
  (SELECT id FROM parts WHERE serial_number = 'SN-2026-0001'),
  'Erster Funktionstest erfolgreich.'
);

