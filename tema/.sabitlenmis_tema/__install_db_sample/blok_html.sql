-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 25 Oca 2026, 19:50:09
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
(32, 'hero', '<section class=\"hero bg-light\">\r\n    <div class=\"container\">\r\n        <h1>A lightweight PHP core for building real websites</h1>\r\n        <p class=\"subheadline\">A self-hosted, installable CMS core with pages, menus, blocks, themes and permissions — ready to use.</p>\r\n        <p class=\"supporting-text\">This is not a theme, not a framework, and not a WordPress clone. It is a clean core system you can build products and websites on.&nbsp;</p>\r\n        <div class=\"button-group\">\r\n            <a href=\"https://github.com/korthaci\" class=\"btn btn-primary\" target=\"_blank\">Github</a>\r\n            <a href=\"doc/\" class=\"btn btn-secondary\">Read Documentation</a>\r\n        </div>\r\n    </div>\r\n\r\n\r\n\r\n\r\n</section>', 'hero', 'minimal', 200, 1),
(33, 'Introduction', '<section>\r\n    <div class=\"container\">\r\n        <h2 class=\"section-title\">What is this system?</h2>\r\n        <p class=\"section-intro\">\r\n            This project is the result of years of building custom PHP-based websites.\r\n            Over time, the system was stripped from domain-specific logic, client-specific features and SaaS overhead, and turned into a reusable, installable core.\r\n            The goal is simple: provide a solid foundation for real websites without forcing you into a heavy framework or an over-engineered CMS.\r\n        </p>\r\n    </div>\r\n\r\n</section> <section>\r\n        <div class=\"container\">\r\n            <div class=\"cards-grid\">\r\n                <div class=\"card\">\r\n                    <h3>Not a theme</h3>\r\n                    <p>This system does not sit on top of another CMS. It is not a visual skin or a template layer.</p>\r\n                </div>\r\n                <div class=\"card\">\r\n                    <h3>Not a framework</h3>\r\n                    <p>It does not require you to assemble everything from scratch. Pages, admin panel, permissions and structure already exist.</p>\r\n                </div>\r\n                <div class=\"card\">\r\n                    <h3>Not WordPress</h3>\r\n                    <p>There are no plugins fighting each other, no hidden magic, and no unnecessary legacy decisions.</p>\r\n                </div>\r\n            </div>\r\n            <p class=\"closing-line\">It is a clean, opinionated core that stays out of your way.</p>\r\n        </div>\r\n    </section>', 'introduction', 'minimal', 240, 1),
(34, 'How it works', '<section class=\"bg-light\">\r\n    <div class=\"container\">\r\n        <h2 class=\"section-title\">How it works</h2>\r\n        <div class=\"steps\">\r\n            <div class=\"step\">\r\n                <div class=\"step-number\">1</div>\r\n                <h4>Install</h4>\r\n                <p>Upload the files and run the installer. No manual configuration or complex setup required.</p>\r\n            </div>\r\n            <div class=\"step\">\r\n                <div class=\"step-number\">2</div>\r\n                <h4>Login to admin</h4>\r\n                <p>Access a structured admin panel designed for managing real content.</p>\r\n            </div>\r\n            <div class=\"step\">\r\n                <div class=\"step-number\">3</div>\r\n                <h4>Create pages and menus</h4>\r\n                <p>Pages, menus and navigation are fully dynamic and managed from the admin.</p>\r\n            </div>\r\n            <div class=\"step\">\r\n                <div class=\"step-number\">4</div>\r\n                <h4>Build with blocks</h4>\r\n                <p>Homepage and layouts are composed using a flexible block system.</p>\r\n            </div>\r\n            <div class=\"step\">\r\n                <div class=\"step-number\">5</div>\r\n                <h4>Choose a theme</h4>\r\n                <p>Switch themes without breaking content or structure.</p>\r\n            </div>\r\n        </div>\r\n    </div>\r\n\r\n</section>', 'how-it-works', 'minimal', 280, 1),
(35, 'Core Features', '<section id=\"features\">\r\n        <div class=\"container\">\r\n            <h2 class=\"section-title\">Core features</h2>\r\n            <div class=\"features-grid\">\r\n                <div class=\"feature\">\r\n                    <h4>Modular architecture</h4>\r\n                    <p>Enable only what you need. The core stays clean and focused.</p>\r\n                </div>\r\n                <div class=\"feature\">\r\n                    <h4>Theme system</h4>\r\n                    <p>Separate logic from presentation. Each theme has its own templates and assets.</p>\r\n                </div>\r\n                <div class=\"feature\">\r\n                    <h4>Block-based homepage</h4>\r\n                    <p>Control layout and content without hardcoding sections.</p>\r\n                </div>\r\n                <div class=\"feature\">\r\n                    <h4>Permission-based access</h4>\r\n                    <p>Numeric permission system for precise role control.</p>\r\n                </div>\r\n                <div class=\"feature\">\r\n                    <h4>Built-in installer</h4>\r\n                    <p>Install once, reuse everywhere.</p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </section>', 'core-features', 'minimal', 320, 1),
(36, 'Content Philosophy', '<section class=\"bg-light\">\r\n    <div class=\"container\">\r\n        <div class=\"philosophy\">\r\n            <h3>Built around content, not assumptions</h3>\r\n            <p>Content is not locked inside templates.</p>\r\n            <p>Menus, pages, blocks and layouts are first-class citizens of the system.</p>\r\n            <p>The admin panel is not an afterthought — it is the center of how the website works.</p>\r\n        </div>\r\n    </div>\r\n\r\n</section>', 'content-philosophy', 'minimal', 360, 1),
(37, 'Who is this for', '<section>\r\n        <div class=\"container\">\r\n            <h2 class=\"section-title\">Who is this for?</h2>\r\n            <div class=\"audience-list\">\r\n                <ul>\r\n                    <li>Freelancers building multiple client websites</li>\r\n                    <li>Small agencies needing a reusable core</li>\r\n                    <li>Developers who want control without reinventing everything</li>\r\n                    <li>Teams tired of over-engineered CMS solutions</li>\r\n                </ul>\r\n                <p class=\"closing-line\">If you want full control without unnecessary complexity, this system is for you.</p>\r\n            </div>\r\n        </div>\r\n    </section>', 'who-is-this-for', 'minimal', 400, 1),
(38, 'Version', '<section>\r\n        <div class=\"container\">\r\n            <div class=\"version-section\">\r\n                <h3>Version 1.0</h3>\r\n                <p>The current release focuses on stability, clarity and documentation.</p>\r\n                <p>Multi-language support exists in the core but is intentionally disabled in v1.0.</p>\r\n                <p>It is planned as a future feature, not a marketing promise.</p>\r\n            </div>\r\n        </div>\r\n    </section>', 'version', 'minimal', 440, 1);

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
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
