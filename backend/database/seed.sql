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
   '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj/Rk.WFi8uu',
   'admin@utm.my');

-- NOTE: password_hash is a bcrypt hash of 'Admin@123' (cost 12).
-- Regenerate at any time with:
--   php -r "echo password_hash('Admin@123', PASSWORD_BCRYPT, ['cost'=>12]);"
-- Then run:
--   UPDATE users SET password_hash='<output>' WHERE username='admin';
