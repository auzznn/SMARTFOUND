-- SmartFound Database Schema (PostgreSQL / Supabase)
-- Run this file first, then seed.sql

DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS comments CASCADE;
DROP TABLE IF EXISTS reports CASCADE;
DROP TABLE IF EXISTS items CASCADE;
DROP TABLE IF EXISTS locations CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

CREATE TABLE roles (
  roleid   SERIAL PRIMARY KEY,
  rolename VARCHAR(50) NOT NULL
);

CREATE TABLE users (
  uuid          SERIAL PRIMARY KEY,
  roleid        INT NOT NULL,
  username      VARCHAR(100) UNIQUE NOT NULL,
  fullname      VARCHAR(150),
  contactno     VARCHAR(20),
  password_hash VARCHAR(255),
  google_id     VARCHAR(255),
  email         VARCHAR(255),
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (roleid) REFERENCES roles(roleid)
);

CREATE TABLE categories (
  categoryid    SERIAL PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL,
  category_type VARCHAR(50),
  description   TEXT
);

CREATE TABLE locations (
  locationid    SERIAL PRIMARY KEY,
  location_name VARCHAR(150) NOT NULL,
  description   TEXT
);

CREATE TABLE items (
  itemid      SERIAL PRIMARY KEY,
  uuid        INT NOT NULL,
  categoryid  INT NOT NULL,
  locationid  INT NOT NULL,
  itemname    VARCHAR(200) NOT NULL,
  totalitems  INT DEFAULT 1,
  png         VARCHAR(300),
  datetime    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uuid)       REFERENCES users(uuid),
  FOREIGN KEY (categoryid) REFERENCES categories(categoryid),
  FOREIGN KEY (locationid) REFERENCES locations(locationid)
);

CREATE TABLE reports (
  reportid    SERIAL PRIMARY KEY,
  uuid        INT NOT NULL,
  categoryid  INT NOT NULL,
  locationid  INT NOT NULL,
  itemid      INT NOT NULL,
  reporttype  VARCHAR(10) NOT NULL CHECK (reporttype IN ('lost', 'found')),
  date        DATE NOT NULL,
  status      VARCHAR(10) DEFAULT 'open' CHECK (status IN ('open', 'closed')),
  FOREIGN KEY (uuid)       REFERENCES users(uuid),
  FOREIGN KEY (categoryid) REFERENCES categories(categoryid),
  FOREIGN KEY (locationid) REFERENCES locations(locationid),
  FOREIGN KEY (itemid)     REFERENCES items(itemid)
);

CREATE TABLE comments (
  commentid   SERIAL PRIMARY KEY,
  reportid    INT NOT NULL,
  uuid        INT NOT NULL,
  comment     TEXT NOT NULL,
  createdat   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reportid) REFERENCES reports(reportid) ON DELETE CASCADE,
  FOREIGN KEY (uuid)     REFERENCES users(uuid)
);

CREATE TABLE messages (
  messagesid         SERIAL PRIMARY KEY,
  uuid               INT NOT NULL,
  messagedescription TEXT NOT NULL,
  FOREIGN KEY (uuid) REFERENCES users(uuid)
);

CREATE TABLE notifications (
  uuid       INT NOT NULL,
  itemid     INT NOT NULL,
  categoryid INT NOT NULL,
  PRIMARY KEY (uuid, itemid),
  FOREIGN KEY (uuid)       REFERENCES users(uuid),
  FOREIGN KEY (itemid)     REFERENCES items(itemid),
  FOREIGN KEY (categoryid) REFERENCES categories(categoryid)
);
