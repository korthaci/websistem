-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 26 Oca 2026, 14:28:52
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `websistem`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_blok_html`
--

CREATE TABLE `r_blok_html` (
  `no` int(11) NOT NULL,
  `adi` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `icerik` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `hash` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `tema` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `sira` int(11) NOT NULL DEFAULT 200,
  `yayin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `r_blok_html`
--

INSERT INTO `r_blok_html` (`no`, `adi`, `icerik`, `hash`, `tema`, `sira`, `yayin`) VALUES
(39, 'Hero Box', '<div class=\"hero-box\">\r\n            <h2>A lightweight PHP core for building real websites</h2>\r\n            <p class=\"subtitle\">A self-hosted, installable CMS core with pages, menus, blocks, themes and permissions — ready to use.</p>\r\n            <p class=\"support-text\">This is not a theme, not a framework, and not a WordPress clone. It is a clean core system you can build products and websites on.</p>\r\n            <div class=\"buttons\">\r\n                <button class=\"button is-white is-medium\">Get Started</button>\r\n                <button class=\"button is-light is-medium\">Learn More</button>\r\n            </div>\r\n        </div>', 'hero-box', 'bulma', 200, 1),
(40, 'What is this System', '<div class=\"content-box is-info\">\r\n            <h3 class=\"box-title\">What is this system?</h3>\r\n            <p class=\"box-intro\">\r\n                This project is the result of years of building custom PHP-based websites.\r\n                Over time, the system was stripped from domain-specific logic, client-specific features and SaaS overhead, and turned into a reusable, installable core.\r\n                The goal is simple: provide a solid foundation for real websites without forcing you into a heavy framework or an over-engineered CMS.\r\n            </p>\r\n\r\n            <div class=\"color-cards\">\r\n                <div class=\"color-card is-purple\">\r\n                    <h4>Not a theme</h4>\r\n                    <p>This system does not sit on top of another CMS. It is not a visual skin or a template layer.</p>\r\n                </div>\r\n                <div class=\"color-card is-blue\">\r\n                    <h4>Not a framework</h4>\r\n                    <p>It does not require you to assemble everything from scratch. Pages, admin panel, permissions and structure already exist.</p>\r\n                </div>\r\n                <div class=\"color-card is-green\">\r\n                    <h4>Not WordPress</h4>\r\n                    <p>There are no plugins fighting each other, no hidden magic, and no unnecessary legacy decisions.</p>\r\n                </div>\r\n            </div>\r\n\r\n            <div class=\"notification is-info\">\r\n                <strong>Key Principle:</strong> It is a clean, opinionated core that stays out of your way.\r\n            </div>\r\n        </div>', 'what-is-this-system', 'bulma', 240, 1),
(41, 'How it Works', '<div class=\"content-box is-success\">\r\n            <h3 class=\"box-title\">How it works</h3>\r\n            \r\n            <div class=\"steps-grid\">\r\n                <div class=\"step-card\">\r\n                    <span class=\"tag is-primary step-tag\">STEP 1</span>\r\n                    <h5>Install</h5>\r\n                    <p>Upload the files and run the installer. No manual configuration or complex setup required.</p>\r\n                </div>\r\n                <div class=\"step-card\">\r\n                    <span class=\"tag is-info step-tag\">STEP 2</span>\r\n                    <h5>Login to admin</h5>\r\n                    <p>Access a structured admin panel designed for managing real content.</p>\r\n                </div>\r\n                <div class=\"step-card\">\r\n                    <span class=\"tag is-success step-tag\">STEP 3</span>\r\n                    <h5>Create pages and menus</h5>\r\n                    <p>Pages, menus and navigation are fully dynamic and managed from the admin.</p>\r\n                </div>\r\n                <div class=\"step-card\">\r\n                    <span class=\"tag is-warning step-tag\">STEP 4</span>\r\n                    <h5>Build with blocks</h5>\r\n                    <p>Homepage and layouts are composed using a flexible block system.</p>\r\n                </div>\r\n                <div class=\"step-card\">\r\n                    <span class=\"tag is-danger step-tag\">STEP 5</span>\r\n                    <h5>Choose a theme</h5>\r\n                    <p>Switch themes without breaking content or structure.</p>\r\n                </div>\r\n            </div>\r\n\r\n            <div class=\"mt-5\">\r\n                <label class=\"label\">Installation Progress</label>\r\n                <progress class=\"progress is-primary\" value=\"100\" max=\"100\">100%</progress>\r\n                <label class=\"label mt-3\">Configuration Progress</label>\r\n                <progress class=\"progress is-info\" value=\"85\" max=\"100\">85%</progress>\r\n                <label class=\"label mt-3\">Setup Progress</label>\r\n                <progress class=\"progress is-success\" value=\"60\" max=\"100\">60%</progress>\r\n            </div>\r\n        </div>', 'how-it-works', 'bulma', 280, 1),
(42, 'Core Features', '<div class=\"content-box is-warning\">\r\n            <h3 class=\"box-title\">Core features</h3>\r\n            \r\n            <div class=\"tile is-ancestor tile-container\">\r\n                <div class=\"tile is-parent\">\r\n                    <div class=\"tile is-child is-primary\">\r\n                        <h5>Modular architecture</h5>\r\n                        <p>Enable only what you need. The core stays clean and focused.</p>\r\n                    </div>\r\n                </div>\r\n                <div class=\"tile is-parent\">\r\n                    <div class=\"tile is-child is-info\">\r\n                        <h5>Theme system</h5>\r\n                        <p>Separate logic from presentation. Each theme has its own templates and assets.</p>\r\n                    </div>\r\n                </div>\r\n                <div class=\"tile is-parent\">\r\n                    <div class=\"tile is-child is-success\">\r\n                        <h5>Block-based homepage</h5>\r\n                        <p>Control layout and content without hardcoding sections.</p>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n\r\n            <div class=\"tile is-ancestor tile-container\">\r\n                <div class=\"tile is-parent\">\r\n                    <div class=\"tile is-child is-warning\">\r\n                        <h5>Permission-based access</h5>\r\n                        <p>Numeric permission system for precise role control.</p>\r\n                    </div>\r\n                </div>\r\n                <div class=\"tile is-parent\">\r\n                    <div class=\"tile is-child is-primary\">\r\n                        <h5>Built-in installer</h5>\r\n                        <p>Install once, reuse everywhere.</p>\r\n                    </div>\r\n                </div>\r\n                <div class=\"tile is-parent\">\r\n                    <div class=\"tile is-child is-info\">\r\n                        <h5>Clean foundation</h5>\r\n                        <p>No bloat, no legacy code, no unnecessary dependencies.</p>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n        </div>', 'core-features', 'bulma', 320, 1),
(43, 'Philosophy', '<div class=\"content-box is-danger\">\r\n            <h3 class=\"box-title\">Built around content, not assumptions</h3>\r\n            \r\n            <div class=\"message is-primary\">\r\n                <div class=\"message-header\">\r\n                    <p>Content Freedom</p>\r\n                </div>\r\n                <div class=\"message-body\">\r\n                    Content is not locked inside templates.\r\n                </div>\r\n            </div>\r\n\r\n            <div class=\"message is-info mt-4\">\r\n                <div class=\"message-header\">\r\n                    <p>First-class Citizens</p>\r\n                </div>\r\n                <div class=\"message-body\">\r\n                    Menus, pages, blocks and layouts are first-class citizens of the system.\r\n                </div>\r\n            </div>\r\n\r\n            <div class=\"message is-success mt-4\">\r\n                <div class=\"message-header\">\r\n                    <p>Admin-centric Design</p>\r\n                </div>\r\n                <div class=\"message-body\">\r\n                    The admin panel is not an afterthought — it is the center of how the website works.\r\n                </div>\r\n            </div>\r\n        </div>', 'philosophy', 'bulma', 360, 1),
(44, 'Who is this for', '<div class=\"content-box\">\r\n            <h3 class=\"box-title\">Who is this for?</h3>\r\n            \r\n            <div class=\"audience-list\">\r\n                <div class=\"box\">\r\n                    <span class=\"tag is-success\">Freelancers</span>\r\n                    <p class=\"mt-2\">Freelancers building multiple client websites</p>\r\n                </div>\r\n                <div class=\"box\">\r\n                    <span class=\"tag is-success\">Agencies</span>\r\n                    <p class=\"mt-2\">Small agencies needing a reusable core</p>\r\n                </div>\r\n                <div class=\"box\">\r\n                    <span class=\"tag is-success\">Developers</span>\r\n                    <p class=\"mt-2\">Developers who want control without reinventing everything</p>\r\n                </div>\r\n                <div class=\"box\">\r\n                    <span class=\"tag is-success\">Teams</span>\r\n                    <p class=\"mt-2\">Teams tired of over-engineered CMS solutions</p>\r\n                </div>\r\n            </div>\r\n\r\n            <div class=\"notification is-success mt-4\">\r\n                <strong>Perfect fit:</strong> If you want full control without unnecessary complexity, this system is for you.\r\n            </div>\r\n        </div>', 'who-is-this-for', 'bulma', 400, 1),
(45, 'Version Info', '<div class=\"content-box\">\r\n            <h3 class=\"box-title\">\r\n                Version 1.0 \r\n                <span class=\"tag is-primary is-medium ml-3\">Current Release</span>\r\n            </h3>\r\n            \r\n            <div class=\"notification is-warning\">\r\n                <p class=\"mb-2\">The current release focuses on stability, clarity and documentation.</p>\r\n                <p class=\"mb-2\">Multi-language support exists in the core but is intentionally disabled in v1.0.</p>\r\n                <p>It is planned as a future feature, not a marketing promise.</p>\r\n            </div>\r\n\r\n            <div class=\"buttons mt-4\">\r\n                <button class=\"button is-primary\">Download v1.0</button>\r\n                <button class=\"button is-info\">View Changelog</button>\r\n                <button class=\"button is-light\">GitHub Repository</button>\r\n            </div>\r\n        </div>', 'version-info', 'bulma', 440, 1);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `r_blok_html`
--
ALTER TABLE `r_blok_html`
  ADD PRIMARY KEY (`no`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `r_blok_html`
--
ALTER TABLE `r_blok_html`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
