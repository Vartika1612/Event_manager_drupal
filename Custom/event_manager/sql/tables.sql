CREATE TABLE event_config (
  id SERIAL PRIMARY KEY,
  event_name VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  event_date DATE NOT NULL,
  reg_start DATE NOT NULL,
  reg_end DATE NOT NULL
);

CREATE TABLE event_registration (
  id SERIAL PRIMARY KEY,
  full_name VARCHAR(255),
  email VARCHAR(255),
  college VARCHAR(255),
  department VARCHAR(255),
  event_id INT REFERENCES event_config(id),
  created INT
);
