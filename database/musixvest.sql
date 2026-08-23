-- =========================================================================
-- MusixVest — database schema + mockup data
-- =========================================================================
-- Import this into a MySQL/MariaDB database (utf8mb4), then point
-- config/Controller.php at it (or set DB_HOST / DB_NAME / DB_USER / DB_PASS
-- environment variables). Every page in the app is written to fall back to
-- its own static mockup arrays if this database isn't connected yet, so
-- the site works before and after you import this file.
--
-- Demo login (seeded below): james@example.com / password
-- Demo admin login (seeded below): admin@musixvest.com / admin12345
-- =========================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------------------
-- users
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id                       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name               VARCHAR(100) NOT NULL,
    last_name                VARCHAR(100) NOT NULL,
    email                    VARCHAR(190) NOT NULL UNIQUE,
    password_hash            VARCHAR(255) NOT NULL,
    phone                    VARCHAR(40)  NULL,
    country                  VARCHAR(80)  NULL,
    verified                 TINYINT(1)   NOT NULL DEFAULT 0,
    preferred_offering_type  VARCHAR(60)  NULL,
    investment_range         VARCHAR(60)  NULL,
    autobuy                  TINYINT(1)   NOT NULL DEFAULT 0,
    email_notifications      TINYINT(1)   NOT NULL DEFAULT 1,
    offering_alerts          TINYINT(1)   NOT NULL DEFAULT 1,
    royalty_alerts           TINYINT(1)   NOT NULL DEFAULT 1,
    created_at               DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- password for the demo account is: password
INSERT INTO users (id, first_name, last_name, email, password_hash, phone, country, verified, preferred_offering_type, investment_range, autobuy) VALUES
(1, 'James', 'Williams', 'james@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+234 800 000 0000', 'Nigeria', 1, 'All SongShares', '$1,000 - $5,000', 0);

-- -------------------------------------------------------------------------
-- kyc_details (verify.php form)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS kyc_details;
CREATE TABLE kyc_details (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             INT UNSIGNED NOT NULL UNIQUE,
    citizenship_status  VARCHAR(40)  NULL,
    dob                 VARCHAR(20)  NULL,
    ssn_last4           VARCHAR(4)   NULL,
    address_line1       VARCHAR(190) NULL,
    address_line2       VARCHAR(190) NULL,
    city                VARCHAR(100) NULL,
    state               VARCHAR(100) NULL,
    country             VARCHAR(100) NULL,
    zip                 VARCHAR(20)  NULL,
    mobile              VARCHAR(40)  NULL,
    submitted_at        DATETIME     NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO kyc_details (user_id, citizenship_status, dob, ssn_last4, address_line1, city, state, country, zip, mobile, submitted_at) VALUES
(1, 'us_citizen', '04-12-1994', '4242', '1 Music Row', 'Lagos', NULL, 'Nigeria', '100001', '+2348000000000', NOW());

-- -------------------------------------------------------------------------
-- songs (the offerings / SongShares catalog)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS songs;
CREATE TABLE songs (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title             VARCHAR(190) NOT NULL,
    artist            VARCHAR(190) NOT NULL,
    category          VARCHAR(80)  NULL,          -- Single Catalog / Multi-Track Catalog / Songwriter Royalty / Album Catalog
    description       TEXT         NULL,
    image_url         VARCHAR(500) NULL,
    price_per_share   DECIMAL(10,2) NULL,
    total_shares      INT UNSIGNED  NULL,
    yield_percent     DECIMAL(5,2)  NULL,
    duration_days     INT UNSIGNED  NULL,          -- illustrative investment term, shown on the offering detail page
    status            ENUM('sale','auction','soldout') NOT NULL DEFAULT 'sale',
    featured          TINYINT(1)   NOT NULL DEFAULT 0,
    ends_at           DATETIME     NULL,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO songs (id, title, artist, category, description, image_url, price_per_share, total_shares, yield_percent, duration_days, status, featured, ends_at) VALUES
(1,  'Stand Strong',                              'Davido ft. Sunday Service Choir', 'Single Catalog',        'A soaring, gospel-inflected anthem that broke out on streaming and picked up sync interest from two independent films, giving it a royalty base beyond streaming alone.', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=600&auto=format&fit=crop', 100.00, 5000, 12.40, 365, 'sale',    1, DATE_ADD(NOW(), INTERVAL 58 DAY)),
(2,  'Under Pressure (Series 2)',                 'Queen',                            'Single Catalog',        'A rock classic with a durable royalty base across radio, streaming, and film/TV licensing that has held steady for decades.', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=600&auto=format&fit=crop', 137.50, 4000, 7.40,  270, 'sale',    0, DATE_ADD(NOW(), INTERVAL 22 DAY)),
(3,  'Radio (Series 2)',                          'Beyoncé',                          'Single Catalog',        'A catalog single from a major global artist with consistent editorial playlist placement across streaming platforms.', 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=600&auto=format&fit=crop', 130.00, 4500, 8.90,  270, 'sale',    0, DATE_ADD(NOW(), INTERVAL 22 DAY)),
(4,  'Vol. 1',                                     'Mariah Carey',                     'Songwriter Royalty',    'A songwriter-royalty offering drawing on a multi-track catalog with a long history of streaming and licensing income.', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&auto=format&fit=crop', 125.00, 3000, 10.10, 180, 'sale',    1, DATE_ADD(NOW(), INTERVAL 207 DAY)),
(5,  'Fight Song',                                 'Rachel Platten',                   'Single Catalog',        'A high-energy single that peaked on regional charts, with royalties drawn from streaming, radio spins, and early licensing deals.', 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?w=600&auto=format&fit=crop', 63.00,  3800, 5.80,  180, 'sale',    0, DATE_ADD(NOW(), INTERVAL 22 DAY)),
(6,  'Chris Brown & Pitbull - Vol.1',              'Pitbull',                          'Multi-Track Catalog',   'A multi-track catalog offering spanning several radio-charting collaborations with a diversified royalty base.', 'https://images.unsplash.com/photo-1511735111819-9a3f7709049c?w=600&auto=format&fit=crop', 95.00,  2500, 9.20,  270, 'sale',    0, DATE_ADD(NOW(), INTERVAL 22 DAY)),
(7,  'Go Crazy',                                   'Chris Brown',                      'Single Catalog',        'A high-rotation single with strong streaming and radio performance since release.', 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=600&auto=format&fit=crop', 88.00,  2200, 11.00, 180, 'sale',    0, DATE_ADD(NOW(), INTERVAL 22 DAY)),
(8,  'Come Undone (Series 2)',                     'Duran Duran',                      'Single Catalog',        'A catalog classic that fully sold out its SongShare offering shortly after listing.', 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=600&auto=format&fit=crop', 74.00,  1800, 6.50,  365, 'soldout', 0, NULL),
(9,  'Octobers Very Own',                          'Various Artists',                  'Multi-Track Catalog',   'A multi-artist compilation catalog offered via VIP auction, combining royalties from several charting tracks.', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&auto=format&fit=crop', 150.00, 2500, NULL,  270, 'auction', 1, DATE_ADD(NOW(), INTERVAL 9 DAY)),
(10, 'Touch My Body',                              'Mariah Carey',                     'Songwriter Royalty',    'A chart-topping single with a long-running royalty history across streaming, radio, and sync licensing.', 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?w=600&auto=format&fit=crop', 125.00, 2000, 10.10, 180, 'sale',    1, DATE_ADD(NOW(), INTERVAL 40 DAY)),
(11, 'Dynamite',                                   'BTS',                              'Single Catalog',        'A global chart-topping single with sustained streaming volume across every major market.', 'https://images.unsplash.com/photo-1601643157091-ce5c665179ab?w=600&auto=format&fit=crop', 210.00, 3000, 14.80, 365, 'sale',    1, DATE_ADD(NOW(), INTERVAL 14 DAY)),
(12, 'Un Verano Sin Ti',                           'Bad Bunny',                        'Album Catalog',         'A record-breaking global album catalog offered via VIP auction, with royalties spanning dozens of streaming-heavy tracks.', 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=600&auto=format&fit=crop', 145.00, 3800, NULL,  365, 'auction', 1, DATE_ADD(NOW(), INTERVAL 5 DAY)),
(13, 'Last Last',                                  'Burna Boy',                        'Songwriter Royalty',    'An Afrobeats hit with fast-growing streaming numbers and strong international licensing interest.', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&auto=format&fit=crop', 88.00,  2600, 13.60, 270, 'sale',    1, DATE_ADD(NOW(), INTERVAL 30 DAY)),
(14, 'Essence',                                    'Wizkid ft. Tems',                  'Single Catalog',        'A breakout Afrobeats single with sustained global streaming growth and playlist momentum.', 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?w=600&auto=format&fit=crop', 92.00,  2400, 12.90, 270, 'sale',    0, DATE_ADD(NOW(), INTERVAL 18 DAY)),
(15, 'Hips Don''t Lie',                            'Shakira',                          'Single Catalog',        'A globally recognized catalog single with durable streaming and licensing income across two decades.', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=600&auto=format&fit=crop', 118.00, 2900, 9.70,  365, 'sale',    0, DATE_ADD(NOW(), INTERVAL 40 DAY)),
(16, 'Despechá',                                   'Rosalía',                          'Single Catalog',        'A summer breakout single with strong short-form video traction and streaming growth.', 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=600&auto=format&fit=crop', 79.00,  2100, 8.40,  180, 'sale',    0, DATE_ADD(NOW(), INTERVAL 12 DAY)),
(17, 'Kesariya',                                   'Arijit Singh',                     'Songwriter Royalty',    'A chart-topping Bollywood single offered via VIP auction, with a large and fast-growing streaming base.', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=600&auto=format&fit=crop', 68.00,  3200, NULL,  270, 'auction', 0, DATE_ADD(NOW(), INTERVAL 9 DAY)),
(18, 'Blinding Lights',                            'The Weeknd',                       'Single Catalog',        'One of the most-streamed songs of all time, with a broad and durable royalty base across platforms.', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&auto=format&fit=crop', 165.00, 3500, 11.50, 365, 'sale',    0, DATE_ADD(NOW(), INTERVAL 60 DAY));

-- -------------------------------------------------------------------------
-- offering_milestones (offering-detail.php — illustrative projected value growth)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS offering_milestones;
CREATE TABLE offering_milestones (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    song_id     INT UNSIGNED NOT NULL,
    days        INT UNSIGNED NOT NULL,
    pct         DECIMAL(5,2) NOT NULL,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO offering_milestones (song_id, days, pct, sort_order) VALUES
(1, 30, 3, 1), (1, 90, 9, 2), (1, 180, 18, 3), (1, 365, 32, 4),
(2, 30, 2, 1), (2, 90, 6, 2), (2, 180, 13, 3), (2, 270, 21, 4),
(4, 30, 3, 1), (4, 90, 8, 2), (4, 180, 16, 3),
(5, 30, 3, 1), (5, 60, 7, 2), (5, 120, 15, 3), (5, 180, 24, 4),
(10, 30, 3, 1), (10, 90, 8, 2), (10, 180, 16, 3),
(11, 30, 4, 1), (11, 90, 11, 2), (11, 180, 22, 3), (11, 365, 38, 4),
(13, 30, 3, 1), (13, 90, 10, 2), (13, 180, 20, 3), (13, 270, 30, 4),
(18, 30, 3, 1), (18, 90, 9, 2), (18, 180, 19, 3), (18, 365, 34, 4);

-- -------------------------------------------------------------------------
-- investments (SongShares a user owns — investments.php / dashboard.php)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS investments;
CREATE TABLE investments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    song_id         INT UNSIGNED NOT NULL,
    shares          INT UNSIGNED NOT NULL,
    value           DECIMAL(12,2) NOT NULL,
    return_percent  DECIMAL(5,2)  NOT NULL DEFAULT 0,
    status          ENUM('Active','Pending') NOT NULL DEFAULT 'Active',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO investments (user_id, song_id, shares, value, return_percent, status, created_at) VALUES
(1, 1,  8,  4160.00, 12.20, 'Active',  DATE_SUB(NOW(), INTERVAL 40 DAY)),
(1, 2,  5,  2750.00, 7.40,  'Active',  DATE_SUB(NOW(), INTERVAL 35 DAY)),
(1, 13, 10, 880.00,  13.60, 'Active',  DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 5,  3,  1890.00, 5.80,  'Active',  DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 11, 4,  840.00,  9.10,  'Active',  DATE_SUB(NOW(), INTERVAL 12 DAY)),
(1, 18, 6,  1560.00, 6.30,  'Active',  DATE_SUB(NOW(), INTERVAL 8 DAY)),
(1, 3,  2,  650.00,  4.00,  'Pending', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- -------------------------------------------------------------------------
-- transactions (invoices.php)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS transactions;
CREATE TABLE transactions (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    description  VARCHAR(190) NOT NULL,
    type         ENUM('Deposit','Investment','Credit') NOT NULL,
    amount       DECIMAL(12,2) NOT NULL,
    status       ENUM('Completed','Pending') NOT NULL DEFAULT 'Completed',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO transactions (user_id, description, type, amount, status, created_at) VALUES
(1, 'Royalty Distribution',          'Credit',     184.20,   'Completed', '2026-08-12'),
(1, 'Stand Strong SongShares',       'Investment', -640.00,  'Completed', '2026-08-08'),
(1, 'Account Deposit',               'Deposit',    1000.00,  'Completed', '2026-08-02'),
(1, 'Last Last SongShares',          'Investment', -88.00,   'Completed', '2026-07-27'),
(1, 'Royalty Distribution',          'Credit',     96.40,    'Completed', '2026-07-19'),
(1, 'Dynamite SongShares',           'Investment', -210.00,  'Completed', '2026-07-05'),
(1, 'Account Deposit',               'Deposit',    500.00,   'Completed', '2026-06-28'),
(1, 'Under Pressure SongShares',     'Investment', -550.00,  'Pending',   '2026-06-14');

-- -------------------------------------------------------------------------
-- favorites (favorites.php)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS favorites;
CREATE TABLE favorites (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    song_id     INT UNSIGNED NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_song (user_id, song_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO favorites (user_id, song_id, created_at) VALUES
(1, 2,  DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 11, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(1, 13, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 3,  DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 12, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 18, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- -------------------------------------------------------------------------
-- payment_methods (settings.php)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS payment_methods;
CREATE TABLE payment_methods (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    card_brand     VARCHAR(40)  NOT NULL,
    last4          VARCHAR(4)   NOT NULL,
    expiry_month   VARCHAR(2)   NOT NULL,
    expiry_year    VARCHAR(4)   NOT NULL,
    is_default     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO payment_methods (user_id, card_brand, last4, expiry_month, expiry_year, is_default, created_at) VALUES
(1, 'Visa', '4242', '12', '2028', 1, DATE_SUB(NOW(), INTERVAL 90 DAY));

-- -------------------------------------------------------------------------
-- deposits (deposit.php — crypto-only funding)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS deposits;
CREATE TABLE deposits (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    network     VARCHAR(40)   NOT NULL,   -- e.g. "USDT (TRC20)", "BTC", "ETH"
    amount      DECIMAL(12,2) NOT NULL,
    tx_hash     VARCHAR(190)  NOT NULL,
    status      ENUM('Pending','Confirmed','Failed') NOT NULL DEFAULT 'Pending',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO deposits (user_id, network, amount, tx_hash, status, created_at) VALUES
(1, 'USDT (TRC20)', 3000.00, 'TXb92f1c7e4a5d8e0f3c6b9a2d1e7f4c8b0a3d6e9f2c5b8a1d4e7f0c3b6a9d2e5f', 'Confirmed', '2026-08-10');

-- -------------------------------------------------------------------------
-- withdrawals (withdraw.php — crypto-only, 5% fee)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS withdrawals;
CREATE TABLE withdrawals (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    network         VARCHAR(40)   NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    fee             DECIMAL(12,2) NOT NULL DEFAULT 0,
    wallet_address  VARCHAR(190)  NOT NULL,
    status          ENUM('Pending','Completed','Rejected') NOT NULL DEFAULT 'Pending',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (no seed rows — matches the "No withdrawal requests yet" empty state)

-- -------------------------------------------------------------------------
-- team_members (about.php)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS team_members;
CREATE TABLE team_members (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    role        VARCHAR(150) NOT NULL,
    image_url   VARCHAR(500) NULL,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO team_members (name, role, image_url, sort_order) VALUES
('Sean Peace',    'CEO / Founder',               'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=400&auto=format&fit=crop', 1),
('Zac Andersen',  'CFO',                         'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=400&auto=format&fit=crop', 2),
('Brian Casto',   'CRO',                         'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=400&auto=format&fit=crop', 3),
('Jayce Varden',  'CSO',                         'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=400&auto=format&fit=crop', 4),
('Drew Small',    'VP Marketing',                'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=400&auto=format&fit=crop', 5),
('Alex Guiva',    'Chairman / Co-Founder',       'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=400&auto=format&fit=crop', 6),
('Drew Smyser',   'Director of Client Strategy', 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?q=80&w=400&auto=format&fit=crop', 7),
('Katie Kneale',  'Director of Operations',      'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=400&auto=format&fit=crop', 8),
('Ronnie Thomas', 'Business Development',        'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=400&auto=format&fit=crop', 9);

-- -------------------------------------------------------------------------
-- faqs (how-it-works.php)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS faqs;
CREATE TABLE faqs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question    VARCHAR(255) NOT NULL,
    answer      TEXT NOT NULL,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO faqs (question, answer, sort_order) VALUES
('What is a SongShare?', 'A SongShare represents a fractional interest in a song''s royalty income. Owning shares means you receive a portion of the income that song generates.', 1),
('Why does a deposit need admin approval?', 'Every deposit is reviewed by an admin before funds appear in your available balance. This mirrors how real financial platforms confirm incoming transfers before releasing funds.', 2),
('Why do I need to verify my identity?', 'Verification confirms you''re eligible to invest, similar to identity checks used by real investment platforms.', 3),
('Is MusixVest a real investment platform?', 'MusixVest is designed to provide a streamlined experience for discovering, reviewing, and managing music royalty investments.', 4);

-- -------------------------------------------------------------------------
-- testimonials (how-it-works.php)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS testimonials;
CREATE TABLE testimonials (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote       TEXT NOT NULL,
    author      VARCHAR(150) NOT NULL,
    image_url   VARCHAR(500) NULL,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO testimonials (quote, author, image_url, sort_order) VALUES
('Owning a piece of music I listen to every day is an incredible feeling.', 'Jason M.', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=400&auto=format&fit=crop', 1),
('A brand new asset class that directly connects me with music creator earnings.', 'Sarah K.', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=400&auto=format&fit=crop', 2),
('Quarterly payouts sent straight to my account. Simple and transparent.', 'David R.', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=400&auto=format&fit=crop', 3);

-- -------------------------------------------------------------------------
-- page_content (editable prose blocks used on landing pages)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS page_content;
CREATE TABLE page_content (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_key     VARCHAR(60)  NOT NULL,
    section_key  VARCHAR(60)  NOT NULL,
    content      TEXT NOT NULL,
    UNIQUE KEY uniq_page_section (page_key, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO page_content (page_key, section_key, content) VALUES
('about', 'intro_1', 'For decades, the music industry kept ownership locked away for labels and insiders. We believe the music you love should belong to the people who made it matter — the fans. SongShares are your ticket into the story, the legacy, and the success of the songs that shaped you. This isn''t just listening. This is belonging.'),
('about', 'intro_2', 'With over $25M in total sales, MusixVest has been trusted to provide access to shares in recordings by iconic artists from every corner of the globe — including Beyoncé, Queen, TLC, Cardi B, Bad Bunny, BTS, Burna Boy, Eminem, and Mariah Carey. With this innovative offering, anyone can turn their passion for music into passive income and forge unique, lasting bonds with the artists and songs they love. Join the thousands already on MusixVest and become an invested fan today.'),
('how_it_works', 'hero_subtitle', 'SongShares allow music fans to buy fractional shares of music rights and earn royalties alongside artists.'),
('how_it_works', 'songshare_desc', 'A SongShare is a real unit of ownership in a music royalty stream.'),
('how_it_works', 'fan_desc', 'Take your passion for music a step further. SongShares allow fans to build real portfolios while supporting music creators directly.');

-- -------------------------------------------------------------------------
-- contact_messages (contact.php)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS contact_messages;
CREATE TABLE contact_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(190) NOT NULL,
    email       VARCHAR(190) NOT NULL,
    subject     VARCHAR(190) NULL,
    message     TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------------------
-- admin_users (admin/ — separate login table from the investor `users`
-- table above, so admin and investor auth never mix)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS admin_users;
CREATE TABLE admin_users (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(120) NOT NULL,
    email          VARCHAR(190) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           VARCHAR(40)  NOT NULL DEFAULT 'admin',
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- password for the demo admin account is: admin12345
INSERT INTO admin_users (name, email, password_hash, role) VALUES
('Platform Admin', 'admin@musixvest.com', '$2y$10$VYHcdFKYsgR4OIIbkwnkku0ySt9VNEnOqmZKN4YvU9qGvtnxK7tb2', 'admin');

-- -------------------------------------------------------------------------
-- payment_wallets (admin/ Payment Settings tab — the crypto deposit
-- addresses shown to investors on settings.php. Admin-managed; investor
-- pages read from this table via Controller::PaymentWallets()).
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS payment_wallets;
CREATE TABLE payment_wallets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    network     VARCHAR(40)  NOT NULL UNIQUE,   -- e.g. "USDT (TRC20)", "BTC", "ETH"
    address     VARCHAR(190) NOT NULL,
    qr_code_url VARCHAR(500) NULL,              -- scannable QR image for this address
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO payment_wallets (network, address, qr_code_url, sort_order) VALUES
('USDT (TRC20)', 'TQn9Y2khEsLMWD1c3v7ZmXk9Z8Rj4pQhAB', 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=TQn9Y2khEsLMWD1c3v7ZmXk9Z8Rj4pQhAB', 1),
('USDT (ERC20)', '0x8f3a2C1b6E4d9A0F7c5B2e8D1a4C6f9E3b7D5a2C', 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=0x8f3a2C1b6E4d9A0F7c5B2e8D1a4C6f9E3b7D5a2C', 2),
('BTC',          'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh', 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh', 3),
('ETH',          '0x4a1B7c2E9f6D3a8C5e0F2b7A4d9C1e6B3f8A2d5E', 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=0x4a1B7c2E9f6D3a8C5e0F2b7A4d9C1e6B3f8A2d5E', 4);

SET FOREIGN_KEY_CHECKS = 1;
