-- Padel Court Booking System - Database Schema
-- Web Development 2

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO users (name, email, password_hash, role) VALUES
('Admin Demo', 'admin@padel.local', '$2y$10$R13DT3Wzn7lIUQbnq44meOawSQCOS4z4uW7zzP3jAKtIrY6SAllGm', 'admin'),
('User Demo', 'user@padel.local', '$2y$10$dn3BpfE.nwirGWkHmamSAe5hEzkL.S00AXk8A94LhC5iTF9gt7MFG', 'user');

CREATE TABLE IF NOT EXISTS courts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  location VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS timeslots (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  court_id INT UNSIGNED NOT NULL,
  slot_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_timeslots_court_date (court_id, slot_date),
  UNIQUE KEY uq_timeslot_per_day (court_id, slot_date, start_time, end_time),
  CONSTRAINT fk_timeslots_court
    FOREIGN KEY (court_id) REFERENCES courts(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  court_id INT UNSIGNED NOT NULL,
  date DATE NOT NULL,
  timeslot_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_bookings_user_id (user_id),
  KEY idx_bookings_court_date (court_id, date),
  KEY idx_bookings_timeslot_id (timeslot_id),
  UNIQUE KEY uq_booking_slot (court_id, date, timeslot_id),
  CONSTRAINT fk_bookings_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_bookings_court
    FOREIGN KEY (court_id) REFERENCES courts(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_bookings_timeslot
    FOREIGN KEY (timeslot_id) REFERENCES timeslots(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed courts
INSERT INTO courts (name, location, description)
SELECT v.name, v.location, v.description
FROM (
  SELECT 'Court 1' AS name, 'Main Hall' AS location, 'Indoor court with professional lighting' AS description
  UNION ALL
  SELECT 'Court 2', 'Main Hall', 'Indoor court near the entrance'
  UNION ALL
  SELECT 'Court 3', 'Outdoor Area', 'Outdoor court with natural lighting'
) AS v
WHERE NOT EXISTS (
  SELECT 1 FROM courts c WHERE c.name = v.name AND c.location = v.location
);

-- Seed timeslots for today and the next 7 days
INSERT INTO timeslots (court_id, slot_date, start_time, end_time)
SELECT v.court_id, v.slot_date, v.start_time, v.end_time
FROM (
  SELECT c.id AS court_id, DATE_ADD(CURDATE(), INTERVAL d.n DAY) AS slot_date, t.start_time, t.end_time
  FROM courts c
  CROSS JOIN (SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7) d
  CROSS JOIN (
    SELECT '09:00:00' AS start_time, '10:00:00' AS end_time
    UNION ALL SELECT '10:00:00', '11:00:00'
    UNION ALL SELECT '11:00:00', '12:00:00'
    UNION ALL SELECT '13:00:00', '14:00:00'
    UNION ALL SELECT '14:00:00', '15:00:00'
    UNION ALL SELECT '15:00:00', '16:00:00'
    UNION ALL SELECT '16:00:00', '17:00:00'
  ) t
) AS v
WHERE NOT EXISTS (
  SELECT 1 FROM timeslots ts
  WHERE ts.court_id = v.court_id
    AND ts.slot_date = v.slot_date
    AND ts.start_time = v.start_time
    AND ts.end_time = v.end_time
);
