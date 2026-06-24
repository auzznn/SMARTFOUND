-- SmartFound Seed Data
-- Run AFTER schema.sql

-- Roles
INSERT INTO roles (roleid, rolename) VALUES
  (1, 'student'),
  (2, 'officer'),
  (3, 'admin');

-- Categories
INSERT INTO categories (category_name, category_type, description) VALUES
  ('Electronics',         'item', 'Phones, laptops, tablets, chargers, headphones, etc.'),
  ('Clothing',            'item', 'Shirts, jackets, hats, shoes, and other garments.'),
  ('Accessories',         'item', 'Watches, jewellery, sunglasses, and similar accessories.'),
  ('Books & Stationery',  'item', 'Textbooks, notebooks, pens, calculators, etc.'),
  ('ID & Cards',          'item', 'Student IDs, staff cards, ATM cards, driving licences.'),
  ('Keys',                'item', 'House keys, car keys, locker keys, and key chains.'),
  ('Bags & Wallets',      'item', 'Backpacks, handbags, purses, and wallets.'),
  ('Sports Equipment',    'item', 'Balls, rackets, gym gear, and sports accessories.'),
  ('Food & Drinks',       'item', 'Lunch boxes, water bottles, and food containers.'),
  ('Others',              'item', 'Items that do not fit into any other category.');

-- Locations
INSERT INTO locations (location_name, description) VALUES
  ('Library (PSZ)',                   'Perpustakaan Sultanah Zanariah — main university library.'),
  ('Faculty of Computing (FC)',       'Fakulti Komputeran building and surrounding area.'),
  ('Faculty of Engineering (FKE)',    'Fakulti Kejuruteraan Elektrik building and surrounding area.'),
  ('Faculty of Science (FS)',         'Fakulti Sains building and surrounding area.'),
  ('Faculty of Built Environment (FABU)', 'Fakulti Alam Bina building and surrounding area.'),
  ('Student Affairs Division (HEP)',  'Bahagian Hal Ehwal Pelajar office building.'),
  ('Residential College A (KA)',      'Kolej Kediaman A dormitory and common areas.'),
  ('Residential College B (KB)',      'Kolej Kediaman B dormitory and common areas.'),
  ('Residential College C (KC)',      'Kolej Kediaman C dormitory and common areas.'),
  ('Residential College D (KD)',      'Kolej Kediaman D dormitory and common areas.'),
  ('Residential College E (KE)',      'Kolej Kediaman E dormitory and common areas.'),
  ('Residential College F (KF)',      'Kolej Kediaman F dormitory and common areas.'),
  ('Residential College G (KG)',      'Kolej Kediaman G dormitory and common areas.'),
  ('Sports Complex',                  'Stadium and sports facilities.'),
  ('Cafeteria Central',               'Main cafeteria / Dewan Makan Pelajar.'),
  ('Bus Stop',                        'Campus bus stops and transit areas.'),
  ('Mosque UTM',                      'Masjid Sultan Ismail — UTM main mosque.'),
  ('Main Gate',                       'UTM main entrance and guardhouse area.'),
  ('Admin Building',                  'Bangunan Canselori — administration and registry.');

-- Admin user (password: Admin@123)
INSERT INTO users (roleid, username, fullname, contactno, password_hash, email) VALUES
  (3, 'admin', 'System Administrator', '0000000000',
   '$2y$10$3ghbbClBYlwC7rY5S2.pgeFKjP12E2l0lBcT4jXz2WTRtR/11o9Ny',
   'admin@utm.my');

-- Demo users
-- Passwords:
--   student_demo / Student@123
--   officer_demo / Officer@123
INSERT INTO users (roleid, username, fullname, contactno, password_hash, email) VALUES
  (1, 'student_demo', 'Aisyah Rahman', '+60 12-345 6789',
   '$2y$10$yDYLYdcbhB8P8uFgTl28F.bSw7Dkeqajk6w03lHTtbA5yz8Ul5Fvm',
   'student.demo@utm.my'),
  (2, 'officer_demo', 'Mohd Farid Campus Officer', '+60 13-555 0198',
   '$2y$10$kroZkxpkIpGuiD9PQNkYCOFjaHD4MWmE0Gvv1yLmRw1llxvuFfM1i',
   'officer.demo@utm.my');

-- Demo reports and related items
WITH new_item AS (
  INSERT INTO items (uuid, categoryid, locationid, itemname, totalitems)
  SELECT u.uuid, c.categoryid, l.locationid, 'Black Lenovo Laptop Bag', 1
    FROM users u, categories c, locations l
   WHERE u.username = 'student_demo'
     AND c.category_name = 'Bags & Wallets'
     AND l.location_name = 'Library (PSZ)'
  RETURNING itemid, uuid, categoryid, locationid
)
INSERT INTO reports (uuid, categoryid, locationid, itemid, reporttype, date, status)
SELECT uuid, categoryid, locationid, itemid, 'lost', '2026-06-20', 'open'
  FROM new_item;

WITH new_item AS (
  INSERT INTO items (uuid, categoryid, locationid, itemname, totalitems)
  SELECT u.uuid, c.categoryid, l.locationid, 'Blue Hydro Flask Bottle', 1
    FROM users u, categories c, locations l
   WHERE u.username = 'officer_demo'
     AND c.category_name = 'Food & Drinks'
     AND l.location_name = 'Sports Complex'
  RETURNING itemid, uuid, categoryid, locationid
)
INSERT INTO reports (uuid, categoryid, locationid, itemid, reporttype, date, status)
SELECT uuid, categoryid, locationid, itemid, 'found', '2026-06-21', 'open'
  FROM new_item;

WITH new_item AS (
  INSERT INTO items (uuid, categoryid, locationid, itemname, totalitems)
  SELECT u.uuid, c.categoryid, l.locationid, 'UTM Student Matric Card', 1
    FROM users u, categories c, locations l
   WHERE u.username = 'student_demo'
     AND c.category_name = 'ID & Cards'
     AND l.location_name = 'Faculty of Computing (FC)'
  RETURNING itemid, uuid, categoryid, locationid
)
INSERT INTO reports (uuid, categoryid, locationid, itemid, reporttype, date, status)
SELECT uuid, categoryid, locationid, itemid, 'lost', '2026-06-18', 'closed'
  FROM new_item;

-- Demo comments
INSERT INTO comments (reportid, uuid, comment)
SELECT r.reportid, u.uuid, 'I found a similar bottle near the futsal court. Please confirm the sticker on it.'
  FROM reports r
  JOIN items i ON i.itemid = r.itemid
  JOIN users u ON u.username = 'student_demo'
 WHERE i.itemname = 'Blue Hydro Flask Bottle';

INSERT INTO comments (reportid, uuid, comment)
SELECT r.reportid, u.uuid, 'Please bring your matric number verification to the counter before collecting.'
  FROM reports r
  JOIN items i ON i.itemid = r.itemid
  JOIN users u ON u.username = 'officer_demo'
 WHERE i.itemname = 'UTM Student Matric Card';

-- NOTE: admin password_hash is a bcrypt hash of 'Admin@123' (cost 12).
-- Regenerate at any time with:
--   php -r "echo password_hash('Admin@123', PASSWORD_BCRYPT, ['cost'=>12]);"
-- Then run:
--   UPDATE users SET password_hash='<output>' WHERE username='admin';
