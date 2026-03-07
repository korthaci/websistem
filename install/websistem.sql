-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 06 Şub 2026, 17:15:09
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
-- Tablo için tablo yapısı `r_bilesen`
--

CREATE TABLE `r_bilesen` (
  `no` int(11) NOT NULL,
  `url` varchar(200) DEFAULT NULL,
  `adi` varchar(255) DEFAULT NULL,
  `yayin` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_blok_html`
--

CREATE TABLE `r_blok_html` (
  `no` int(11) NOT NULL,
  `adi` varchar(255) DEFAULT NULL,
  `icerik` longtext DEFAULT NULL,
  `hash` varchar(40) DEFAULT NULL,
  `tema` varchar(40) DEFAULT NULL,
  `sira` int(11) NOT NULL DEFAULT 200,
  `yayin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_ceviriler`
--

CREATE TABLE `r_ceviriler` (
  `no` int(11) NOT NULL,
  `dil_no` int(11) DEFAULT 0,
  `tablo_no` int(11) DEFAULT NULL,
  `tablo` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `alan` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `yazi` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_diller`
--

CREATE TABLE `r_diller` (
  `no` int(11) NOT NULL,
  `dkod` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `dyon` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `adi` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `adie` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `adio` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ltr` tinyint(1) DEFAULT 1,
  `sira` int(11) DEFAULT 8,
  `yayin` tinyint(1) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Tablo döküm verisi `r_diller`
--

INSERT INTO `r_diller` (`no`, `dkod`, `dyon`, `adi`, `adie`, `adio`, `ltr`, `sira`, `yayin`) VALUES
(28, 'de', 'de', 'Almanca', 'Deutsch', 'Deutsch', 1, 280, 1),
(6, 'ar', 'ar', 'Arapça', 'Arabic', 'العربية', 0, 440, 1),
(10, 'bg', 'bg', 'Bulgarca', 'Bulgarian', 'български', 1, 1120, 0),
(24, 'cs', 'cs', 'Çekce', 'Czech', 'čeština', 1, 1280, 0),
(175, 'zh', 'zh', 'Çince', 'Chinese', '中国', 1, 320, 1),
(27, 'da', 'da', 'Danca', 'Dansk', 'Dansk', 1, 1480, 0),
(62, 'id', 'id', 'Endonezya Dili', 'Indonesia', 'Bahasa Indonesia', 1, 600, 0),
(36, 'et', 'et', 'Estonyaca', 'Estonian', 'Eesti', 1, 1640, 0),
(107, 'nl', 'nl', 'Felemenkçe', 'Dutch', 'Nederlandse', 1, 1800, 0),
(38, 'fi', 'fi', 'Fince', 'Finnish', 'Suomi', 1, 1880, 0),
(42, 'fr', 'fr', 'Fransızca', 'French', 'Français', 1, 400, 1),
(53, 'hi', 'hi', 'Hintçe', 'Hindi', 'हिन्दी', 1, 640, 0),
(178, 'hr', 'hr', 'Hırvatça', 'Hirvat', 'Hrvatski', 1, 2480, 0),
(52, 'he', 'he', 'İbranice', 'Hebrew', 'עברית', 0, 2600, 0),
(33, 'en', 'en', 'İngilizce', 'English', 'English', 1, 240, 1),
(35, 'es', 'es', 'İspanyolca', 'Spanish', 'Español', 1, 480, 1),
(148, 'sv', 'sv', 'İsveççe', 'Swedish', 'Svenska', 1, 2920, 0),
(66, 'it', 'it', 'İtalyanca', 'Italian', 'Italiano', 1, 520, 0),
(69, 'ja', 'ja', 'Japonca', 'Japanese', '日本人', 1, 3000, 0),
(77, 'ko', 'ko', 'Korece', 'Korean', '한국의', 1, 3560, 0),
(120, 'pl', 'pl', 'Lehçe', 'Polski', 'Polski', 1, 3840, 0),
(89, 'lv', 'lv', 'Letonca', 'Latvian', 'Latvijā', 1, 3880, 0),
(88, 'lt', 'lt', 'Litvanca', 'Lithuanian', 'Lietuvos', 1, 3920, 0),
(57, 'hu', 'hu', 'Macarca', 'Hungarian', 'Magyar', 1, 4000, 0),
(98, 'ms', 'ms', 'Malayca', 'Malay', 'Melayu', 1, 4160, 0),
(109, 'no', 'no', 'Norveççe', 'Norwegian', 'Norsk', 1, 4680, 0),
(124, 'pt', 'pt', 'Portekizce', 'Portuguese', 'Português', 1, 560, 0),
(127, 'ro', 'ro', 'Romence', 'Rumanian', 'Român', 1, 5280, 0),
(128, 'ru', 'ru', 'Rusça', 'Russian', 'русский', 1, 360, 1),
(144, 'sr', 'sr', 'Sırpça', 'Serbian', 'Српски', 1, 5800, 0),
(138, 'sk', 'sk', 'Slovakça', 'Slovak', 'Slovenský', 1, 5840, 0),
(139, 'sl', 'sl', 'Slovence', 'Slovene', 'Slovenski', 1, 5880, 0),
(152, 'th', 'th', 'Tayland dili', 'Siamese', 'ภาษาไทย', 1, 6280, 0),
(160, 'tr', 'tr', 'Türkçe', 'Türkçe', 'Türkçe', 1, 200, 1),
(165, 'uk', 'uk', 'Ukraynaca', 'Ukrainian', 'Український', 1, 6560, 0),
(169, 'vi', 'vi', 'Vietnam Dili', 'Vietnamese', 'Tiếng Việt', 1, 6680, 0),
(32, 'el', 'el', 'Yunanca', 'Greek', 'Ελληνικά', 1, 6960, 0),
(1, 'aa', 'en', 'Afar', NULL, NULL, 1, 680, 0),
(3, 'af', 'nl', 'Afrikanca (Hollanda Lehçesi)', 'Afrikaans', 'Afrikaans', 1, 720, 0),
(143, 'sq', 'sq', 'Arnavutça', 'Albanian', 'Shqiptar', 1, 760, 0),
(8, 'az', 'az', 'Azerice', 'Azerbaijani', 'Azerbaijani', 1, 800, 0),
(30, 'eu', 'eu', 'Baskça', 'Basque', 'Euskal', 1, 840, 0),
(12, 'bn', 'bn', 'Bengalli', 'Bengali', 'বাংলা', 1, 880, 0),
(9, 'be', 'ru', 'Beyaz Rusya (Belarusça)', 'Belarusian', 'Беларуская мова', 1, 920, 0),
(29, 'dz', 'dz', 'Bhutanlı dili', NULL, NULL, 1, 960, 0),
(100, 'my', 'my', 'Birmanya Dili', NULL, NULL, 1, 1000, 0),
(15, 'bs', 'bs', 'Boşnakça', 'Bosnian', 'Bosanski', 1, 1040, 0),
(14, 'br', 'br', 'Brezilya Portekizcesi', NULL, NULL, 1, 1080, 0),
(168, 'vc', 'vc', 'Cape Verdean Creole', NULL, NULL, 1, 1160, 0),
(17, 'cb', 'cb', 'Cebuano', NULL, NULL, 1, 1200, 0),
(19, 'ce', 'ru', 'Çeçen', NULL, NULL, 1, 1240, 0),
(2, 'ab', 'ru', 'Çerkez Dili (Abhaz)', 'Abhaz', 'абаза бызшва', 1, 1320, 0),
(20, 'ch', 'ch', 'Chamorro', 'Chamorro', 'chamoru', 1, 1360, 0),
(21, 'ck', 'ck', 'Chuukese', NULL, NULL, 1, 1400, 0),
(25, 'cv', 'ru', 'Çuvaş (Chuvash)', NULL, 'Чӑвашла', 1, 1440, 0),
(118, 'pg', 'pg', 'Dari (Afganistan Farsçası)', NULL, NULL, 1, 1520, 0),
(58, 'hy', 'hy', 'Ermenice', 'Armenian', 'հայերեն', 1, 1560, 0),
(34, 'eo', 'eo', 'Esperanto Dili', NULL, NULL, 1, 1600, 0),
(31, 'ee', 'ee', 'Ewe', NULL, NULL, 1, 1680, 0),
(41, 'fo', 'fo', 'Faroese', NULL, NULL, 1, 1720, 0),
(37, 'fa', 'fa', 'Farsça', NULL, NULL, 0, 1760, 0),
(39, 'fj', 'fj', 'Fiji', NULL, NULL, 1, 1840, 0),
(40, 'fl', 'fl', 'Flaman Dili', NULL, NULL, 1, 1920, 0),
(43, 'fy', 'fy', 'Frizye Dili', NULL, NULL, 1, 1960, 0),
(46, 'gl', 'gl', 'Galiçyaca', NULL, NULL, 1, 2000, 0),
(74, 'kl', 'kl', 'Grönland', NULL, NULL, 1, 2040, 0),
(47, 'gn', 'gn', 'Guaraní', NULL, NULL, 1, 2080, 0),
(48, 'gu', 'gu', 'Gujarati', NULL, NULL, 1, 2120, 0),
(110, 'nr', 'nr', 'Güney Ndebele', NULL, NULL, 1, 2160, 0),
(71, 'ka', 'ka', 'Gürcüce', 'Georgian', 'ქართული', 1, 2200, 0),
(4, 'am', 'en', 'Habeşistan Dili', NULL, NULL, 1, 2240, 0),
(50, 'hc', 'hc', 'Haiti Creole', NULL, NULL, 1, 2280, 0),
(51, 'ha', 'ha', 'Hausa', NULL, NULL, 1, 2320, 0),
(59, 'hz', 'hz', 'Herero', NULL, NULL, 1, 2360, 0),
(54, 'hl', 'hl', 'Hiligaynon', NULL, NULL, 1, 2400, 0),
(56, 'ho', 'ho', 'Hiri Motu', NULL, NULL, 1, 2440, 0),
(55, 'hm', 'hm', 'Hmong', NULL, NULL, 1, 2520, 0),
(61, 'ib', 'ib', 'Iban', NULL, NULL, 1, 2560, 0),
(65, 'il', 'il', 'Ilocano', NULL, NULL, 1, 2640, 0),
(60, 'ia', 'ia', 'Interlingua', NULL, NULL, 1, 2680, 0),
(67, 'iu', 'iu', 'İnuktitut', NULL, NULL, 1, 2720, 0),
(64, 'ik', 'ik', 'Inupiak', NULL, NULL, 1, 2760, 0),
(44, 'ga', 'ga', 'İrlandaca', 'Irish', 'Gaelic', 1, 2800, 0),
(45, 'gd', 'gd', 'İskoç Gallicesi', 'Scottish Gaelic', 'Gàidhlig', 1, 2840, 0),
(132, 'sc', 'en', 'İskoçca', 'Gaelic', 'Gàidhlig', 1, 2880, 0),
(63, 'is', 'is', 'İzlandaca', 'Icelandic', 'íslenska', 1, 2960, 0),
(70, 'jw', 'jw', 'Javanese', 'Javanese', NULL, 1, 3040, 0),
(75, 'km', 'km', 'Kamboçyaca', NULL, NULL, 1, 3080, 0),
(76, 'kn', 'kn', 'Kannada', NULL, NULL, 1, 3120, 0),
(18, 'cc', 'zh', 'Kanton Lehçesi (Güney Çin Dili)', NULL, NULL, 1, 3160, 0),
(16, 'ca', 'ca', 'Katalanca', 'Catalan', 'Català', 1, 3200, 0),
(73, 'kk', 'ru', 'Kazakça', 'Kazakh', 'Қазақ', 1, 3240, 0),
(83, 'kw', 'kw', 'Keltçe', NULL, NULL, 1, 3280, 0),
(79, 'ks', 'ks', 'Keşmir', NULL, NULL, 1, 3320, 0),
(72, 'ki', 'ki', 'Kikuyu', NULL, NULL, 1, 3360, 0),
(129, 'rw', 'rw', 'Kinyaruanda', NULL, NULL, 1, 3400, 0),
(126, 'rn', 'rn', 'Kirundi', NULL, NULL, 1, 3440, 0),
(82, 'ky', 'ky', 'Kırgızca', NULL, NULL, 1, 3480, 0),
(81, 'kv', 'kv', 'Komi', NULL, NULL, 1, 3520, 0),
(23, 'co', 'co', 'Korsika', 'Corsican', 'Corsu', 1, 3600, 0),
(78, 'kp', 'kp', 'Kpelle', NULL, NULL, 1, 3640, 0),
(80, 'ku', 'ku', 'Kürtçe', NULL, NULL, 1, 3680, 0),
(104, 'nd', 'nd', 'Kuzey Dakota', NULL, NULL, 1, 3720, 0),
(87, 'lo', 'lo', 'Lao', NULL, NULL, 1, 3760, 0),
(84, 'la', 'la', 'Latin', NULL, NULL, 1, 3800, 0),
(85, 'lb', 'de', 'Lüksemburg Dili', NULL, NULL, 1, 3960, 0),
(90, 'mg', 'mg', 'Madagaskar Dili', NULL, NULL, 1, 4040, 0),
(93, 'mk', 'mk', 'Makedonca', 'Macedonian', 'македонски', 1, 4080, 0),
(94, 'ml', 'ml', 'Malayalam', NULL, NULL, 1, 4120, 0),
(99, 'mt', 'mt', 'Malta Dili', NULL, NULL, 1, 4200, 0),
(22, 'cm', 'cm', 'Mandarin (Kuzey Çin Lehçesi)', NULL, NULL, 1, 4240, 0),
(49, 'gv', 'gv', 'Manx Gaelic', NULL, NULL, 1, 4280, 0),
(92, 'mi', 'mi', 'Maori', NULL, NULL, 1, 4320, 0),
(97, 'mr', 'mr', 'Marathi', NULL, NULL, 1, 4360, 0),
(91, 'mh', 'mh', 'Marshallese', NULL, NULL, 1, 4400, 0),
(95, 'mn', 'mn', 'Moğolca', NULL, NULL, 1, 4440, 0),
(96, 'mo', 'mo', 'Moldova (Boğdan)', NULL, NULL, 1, 4480, 0),
(101, 'na', 'na', 'Nauru', NULL, NULL, 1, 4520, 0),
(111, 'nv', 'nv', 'Navajo', NULL, NULL, 1, 4560, 0),
(103, 'ne', 'ne', 'Nepali', NULL, NULL, 1, 4600, 0),
(106, 'ni', 'ni', 'Nijerya Dili', NULL, NULL, 1, 4640, 0),
(102, 'nb', 'no', 'Norveççe (Bokmål)', NULL, NULL, 1, 4720, 0),
(108, 'nn', 'no', 'Norveçce (Nynorsk)', NULL, NULL, 1, 4760, 0),
(112, 'ny', 'ny', 'Nyanja', NULL, NULL, 1, 4800, 0),
(113, 'oc', 'oc', 'Occitan', NULL, NULL, 1, 4840, 0),
(114, 'or', 'or', 'Oriya', NULL, NULL, 1, 4880, 0),
(115, 'om', 'om', 'Oromo', NULL, NULL, 1, 4920, 0),
(116, 'os', 'os', 'Osetçe', NULL, NULL, 1, 4960, 0),
(167, 'uz', 'uz', 'Özbekçe', NULL, NULL, 1, 5000, 0),
(119, 'pi', 'pi', 'Pali', NULL, NULL, 1, 5040, 0),
(121, 'pn', 'pn', 'Pananyano', NULL, NULL, 1, 5080, 0),
(122, 'pp', 'pp', 'Papiamento', NULL, NULL, 1, 5120, 0),
(123, 'ps', 'ps', 'Pashto', NULL, NULL, 1, 5160, 0),
(117, 'pa', 'pa', 'Pencap Dili (Punjabi)', NULL, NULL, 1, 5200, 0),
(125, 'qu', 'qu', 'Quechua', NULL, NULL, 1, 5240, 0),
(134, 'se', 'se', 'Sámi', NULL, NULL, 1, 5320, 0),
(140, 'sm', 'en', 'Samoaca', NULL, NULL, 1, 5360, 0),
(135, 'sg', 'sg', 'Sangho', NULL, NULL, 1, 5400, 0),
(130, 'sa', 'sa', 'Sanskritçe', NULL, NULL, 1, 5440, 0),
(131, 'sc', 'sc', 'Sardince', NULL, NULL, 1, 5480, 0),
(136, 'sh', 'sh', 'Serbo-Croat', NULL, NULL, 1, 5520, 0),
(146, 'st', 'st', 'Sesotho', NULL, NULL, 1, 5560, 0),
(157, 'tn', 'tn', 'Setswana', NULL, NULL, 1, 5600, 0),
(141, 'sn', 'sn', 'Shona', NULL, NULL, 1, 5640, 0),
(133, 'sd', 'sd', 'Sindhi', NULL, NULL, 1, 5680, 0),
(137, 'si', 'si', 'Sinhalese', NULL, NULL, 1, 5720, 0),
(145, 'ss', 'ss', 'Siswati', NULL, NULL, 1, 5760, 0),
(142, 'so', 'so', 'Somali Dili', NULL, NULL, 1, 5920, 0),
(147, 'su', 'su', 'Sundanese', NULL, NULL, 1, 5960, 0),
(149, 'sw', 'sw', 'Swahili', NULL, NULL, 1, 6000, 0),
(151, 'tg', 'ru', 'Tacikçe', NULL, NULL, 1, 6040, 0),
(163, 'ty', 'ty', 'Tahitian', NULL, NULL, 1, 6080, 0),
(162, 'tw', 'tw', 'Taiwan (Tayvan)', NULL, NULL, 1, 6120, 0),
(155, 'tl', 'tl', 'Takalotça', NULL, NULL, 1, 6160, 0),
(156, 'tm', 'tm', 'Tamil', NULL, NULL, 1, 6200, 0),
(161, 'tt', 'ru', 'Tatar', NULL, NULL, 1, 6240, 0),
(150, 'te', 'te', 'Telugu', NULL, NULL, 1, 6320, 0),
(13, 'bo', 'zh', 'Tibetçe', 'Tibetan', 'བོད་སྐད་', 1, 6360, 0),
(153, 'ti', 'ti', 'Tigrinya', NULL, NULL, 1, 6400, 0),
(158, 'to', 'to', 'Tonga', NULL, NULL, 1, 6440, 0),
(159, 'ts', 'ts', 'Tsonga', NULL, NULL, 1, 6480, 0),
(154, 'tk', 'tk', 'Türkmence', NULL, NULL, 1, 6520, 0),
(166, 'ur', 'ur', 'Urduca', 'Urdu', 'اُردُو', 0, 6600, 0),
(164, 'ug', 'ug', 'Uygurca', NULL, NULL, 1, 6640, 0),
(170, 'vo', 'vo', 'Volapük', NULL, NULL, 1, 6720, 0),
(26, 'cy', 'en', 'Welsh', NULL, NULL, 1, 6760, 0),
(171, 'wo', 'wo', 'Wolof', NULL, NULL, 1, 6800, 0),
(172, 'xh', 'xh', 'Xhosa', NULL, NULL, 1, 6840, 0),
(173, 'yi', 'yi', 'Yidce. Alman İbranicesi', NULL, NULL, 1, 6880, 0),
(174, 'yo', 'yo', 'Yoruba', NULL, NULL, 1, 6920, 0),
(176, 'zj', 'zj', 'Zhuang', NULL, NULL, 1, 7000, 0),
(177, 'zu', 'zu', 'Zulu', NULL, NULL, 1, 7040, 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_genel_ayarlar`
--

CREATE TABLE `r_genel_ayarlar` (
  `no` int(11) NOT NULL,
  `adi` varchar(255) DEFAULT NULL,
  `aciklama` varchar(255) DEFAULT NULL,
  `grup` varchar(50) DEFAULT NULL,
  `anahtar` varchar(255) DEFAULT NULL,
  `deger` mediumtext DEFAULT NULL,
  `degistir` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Tablo döküm verisi `r_genel_ayarlar`
--


INSERT INTO `r_genel_ayarlar` (`no`, `adi`, `aciklama`, `grup`, `anahtar`, `deger`, `degistir`) VALUES
(2, 'Site Adı', 'Web sitesinin ana başlığı', 'Site', 'site_adi', 'Websistem v1', 1),
(3, 'Domain', 'Sitenin tam URL adresi (http/https dahil)', 'Site', 'www_site', 'https://n0n1.tr', 1),
(4, 'Email adresi', 'Sistem bildirimlerinin gönderileceği ana eposta', 'Site', 'email_adresi', 'mail@n0n1.tr', 1),
(5, 'Yabancı dil()', 'Sitede çoklu dil desteği aktif mi? (0: Hayır, 1: Evet)', 'Sistem', 'yabanci_dil', '1', 1),
(6, 'Varsayılan dil', 'Sitenin başlangıç dili (tr, en vb.)', 'Sistem', 'varsayilan_dil', 'tr', 1),
(16, 'Tema', 'Kullanılan aktif tema klasörü adı', 'Sistem', 'tema', 'minimal', 0),
(17, 'Permalink', NULL, 'Sistem', 'permalink', '1', 0),
(18, 'aciklama', 'Sayfanın ne hakkında olduğunu özetleyen kısa tanıtım cümlesi. (SEO)', 'Site', 'aciklama', NULL, 1),
(19, 'etiket', NULL, 'Site', 'etiket', NULL, 1),
(20, 'Google Login Client Id', 'Google Login Auth Client Id', 'API', 'google_login_client_id', NULL, 1),
(21, 'Google Map Key', 'Google Map Key', 'API', 'GOOGLE_MAP_KEY', NULL, 1),
(23, 'Mail Sistemi', 'Varsayılan mail kütüphanesi (local, amazon, sendgrid, mailgun vb.)', 'Mail', 'smtp_varsayilan', 'local', 1),
(24, 'SMTP Host', 'SMTP sunucu adresi', 'Mail', 'smtp_host', 'mail.localhost', 1),
(25, 'SMTP Port', 'SMTP port numarası (TLS: 587, SSL: 465)', 'Mail', 'smtp_port', '587', 1),
(26, 'SMTP Kullanıcı', 'SMTP kullanıcı adı / Email adresi', 'Mail', 'smtp_k_adi', 'mail@localhost', 1),
(27, 'SMTP Şifre', 'SMTP kullanıcı şifresi', 'Mail', 'smtp_sifre', NULL, 1),
(28, 'AWS Access Key', 'Amazon SES Access Key ID', 'Mail', 'smtp_aws_k_adi', '', 1),
(29, 'AWS Secret Key', 'Amazon SES Secret Access Key', 'Mail', 'smtp_aws_sifre', '', 1),
(30, 'AWS Region Host', 'Amazon SES SMTP sunucu adresi', 'Mail', 'smtp_aws_host', 'email-smtp.us-east-1.amazonaws.com', 1),
(31, 'Sendgrid API Key', 'Sendgrid API anahtarı', 'Mail', 'smtp_sendgrid_sifre', 'SG.XXXX', 1),
(32, 'Mailgun User', 'Mailgun SMTP kullanıcı adı', 'Mail', 'smtp_mailgun_k_adi', 'postmaster@mg.abc.com', 1),
(33, 'Mailgun Password', 'Mailgun SMTP şifresi', 'Mail', 'smtp_mailgun_sifre', 'XXXX', 1),
(35, 'noindex follow', 'Arama motorlarında gösterilmeyecek parametreler', 'Sistem', 'meta_noindex_follow', NULL, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_giris_deneme_log`
--

CREATE TABLE `r_giris_deneme_log` (
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deneme_sayisi` int(11) DEFAULT 1,
  `son_deneme` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_log_db`
--

CREATE TABLE `r_log_db` (
  `no` int(11) NOT NULL,
  `kullanici_no` int(11) DEFAULT NULL,
  `tablo_adi` varchar(100) NOT NULL,
  `kayit_no` int(11) DEFAULT NULL,
  `islem` text NOT NULL,
  `eski_veri` text DEFAULT NULL COMMENT 'JSON formatında eski veri',
  `yeni_veri` text DEFAULT NULL COMMENT 'JSON formatında yeni veri',
  `ip_adresi` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `tarih` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_menu`
--

CREATE TABLE `r_menu` (
  `no` int(11) NOT NULL,
  `tablo` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `tablo_no` int(11) DEFAULT 0,
  `ust_menu_no` int(11) DEFAULT 0,
  `dis_link` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `adi` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `sira` int(11) DEFAULT 200
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `r_menu`
--

INSERT INTO `r_menu` (`no`, `tablo`, `tablo_no`, `ust_menu_no`, `dis_link`, `adi`, `sira`) VALUES
(26, 'sayfa', 10, 0, NULL, NULL, 240),
(30, 'sayfa', 11, 0, NULL, NULL, 280);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_moduller`
--

CREATE TABLE `r_moduller` (
  `no` int(11) NOT NULL,
  `url` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `adi` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `hash` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `yayin` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `r_moduller`
--

INSERT INTO `r_moduller` (`no`, `url`, `adi`, `hash`, `yayin`) VALUES
(73, 'iletisim_formu', 'İletisim formu', NULL, 1),
(78, 'tawkto', 'tawkto', NULL, 1),
(82, 'kvkk', 'kvkk', NULL, 1),
(88, 'google_map', 'google_map', NULL, 1),
(90, 'yorumlar_form', 'Yorum formu', NULL, 1),
(102, 'yorumlar_genel', 'Genel yorumlar', NULL, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_sayfa`
--

CREATE TABLE `r_sayfa` (
  `no` int(11) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `ms_no` int(11) DEFAULT 0,
  `uye_no` int(11) DEFAULT 0,
  `adi` varchar(255) DEFAULT NULL,
  `icerik` mediumtext DEFAULT NULL,
  `manset` tinyint(1) DEFAULT 0,
  `resim` varchar(255) DEFAULT NULL,
  `yorum_acik` int(1) DEFAULT 0,
  `link_target` varchar(255) DEFAULT NULL,
  `sira` int(11) DEFAULT 200,
  `yayin` tinyint(1) DEFAULT 0,
  `tarih` date DEFAULT NULL,
  `aciklama` varchar(255) DEFAULT NULL,
  `etiket` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `r_sayfa`
--

INSERT INTO `r_sayfa` (`no`, `url`, `ms_no`, `uye_no`, `adi`, `icerik`, `manset`, `resim`, `yorum_acik`, `link_target`, `sira`, `yayin`, `tarih`, `aciklama`, `etiket`) VALUES
(10, 'iletisim', 1, 0, 'İletişim', '[al:modul:iletisim_formu]', 0, NULL, 1, NULL, 200, 1, '2025-10-14', NULL, NULL),
(11, 'kvkk', 0, 0, 'KVKK', '<h4>KİŞİSEL VERİLERİN KORUNMASI KANUNU (KVKK) AYDINLATMA METNİ</h4><p>Kişisel verilerinizin korunmasına önem veriyoruz. Bu metin 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) gereğince veri sorumlusu sıfatımızla kullanıcıları bilgilendirmek amacıyla hazırlanmıştır.</p><h4>1. Veri Sorumlusu</h4><p>Veri sorumlusu: <strong>Websistem</strong>.</p><h4>2. İşlenen Kişisel Veriler</h4><ul>  <li><strong>Kimlik/İletişim:</strong> Ad, soyad, telefon, e-posta.</li>  <li><strong>Rezervasyon verileri:</strong> Kiralanan araç bilgisi, rezervasyon tarihleri, rezervasyon numarası, ödeme tutarı, depozito bilgileri (ofiste tahsil edilen depozito tutarı bilgisi).</li>  <li><strong>İşlem/teknik veriler:</strong> IP adresi, user-agent (tarayıcı), işlem logları ve güvenlik logları.</li>  <li><strong>Ödeme ile ilgili durum bilgileri:</strong> provizyon (ön blokaj) bilgisi, tahsilat durumu; kredi kartı numarası/son kullanma/tüm kart verileri tarafımızda saklanmaz.</li></ul><h4>3. Kişisel Verilerin İşlenme Amaçları ve Hukuki Sebepleri</h4><ul>  <li>Rezervasyonun oluşturulması ve sözleşmenin ifası (KVKK m.5/2-c).</li>  <li>Rezervasyon, ödeme ve iletişim süreçlerinin yönetilmesi; rezervasyon onayı, hatırlatma ve bilgilendirme e-postaları/SMS’lerinin gönderilmesi.</li>  <li>Sistem ve işlem güvenliğinin sağlanması, sahte işlem ve kötüye kullanımın tespiti (meşru menfaat, KVKK m.5/2-f).</li>  <li>Açık rıza ile pazarlama/duyuru e-postalarının gönderilmesi (KVKK m.5/1).</li></ul><h4>4. Aktarım</h4><p>Rezervasyonun ifası için gerekli bilgiler (ad, soyad, iletişim, rezervasyon detayları, provizyon/tahsilat sonucu) rezervasyon yapılan araç tedarikçisine iletilir. Ödeme işlemleri banka/ödeme sağlayıcıları ile, e-posta/SMS gönderimleri ilgili servis sağlayıcıları ile paylaşılır. Pazarlama amaçlı veriler üçüncü taraf reklam şirketlerine satılmaz.</p><h4>5. Kredi Kartı ve Ödeme Güvenliği</h4><p>Kredi kartı bilgileri <strong>Websistem sunucularında saklanmaz</strong>. Ödeme esnasında girilen kart bilgileri yalnızca ödeme anında yetkili banka/ödeme sağlayıcısına iletilir. Site ile banka arasındaki iletişim ilgili ödeme sistemi tarafından sağlanan güvenlik protokollerine tabidir.</p><h4>6. Saklama Süreleri</h4><ul>  <li>Fatura ve ticari kayıtlar: yasal yükümlülükler çerçevesinde saklanır (ilgili mevzuat süreleri).</li>  <li>Rezervasyon ve işlem kayıtları: işletme ve güvenlik amaçlı asgari 2 yıl.</li>  <li>Pazarlama onayları: rıza geri çekilene kadar.</li></ul><h4>7. İlgili Kişi Hakları</h4><p>KVKK m.11 uyarınca; verilerinizin işlenip işlenmediğini öğrenme, işlenmişse bilgi talep etme, eksik/yanlış verilerin düzeltilmesini isteme, verilerin silinmesini veya anonimleştirilmesini talep etme, işleme amacına itiraz etme ve açık rıza geri çekme haklarınız bulunmaktadır. Talepleriniz için: <a href=\"mailto:mail@n0n1.tr\">mail@n0n1.tr</a>. Talepleriniz makul süre içinde cevaplandırılır.</p>', 0, NULL, 0, NULL, 200, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_sekme`
--

CREATE TABLE `r_sekme` (
  `no` int(11) NOT NULL,
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `hash` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `adi` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `icerik` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `icerik_sayfada` tinyint(1) DEFAULT 0,
  `manset` tinyint(1) DEFAULT 0,
  `sira` int(11) DEFAULT 8,
  `yayin` tinyint(1) DEFAULT 0,
  `aciklama` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `etiket` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `r_sekme`
--

INSERT INTO `r_sekme` (`no`, `url`, `hash`, `adi`, `icerik`, `icerik_sayfada`, `manset`, `sira`, `yayin`, `aciklama`, `etiket`) VALUES
(1, 'genel', '', 'Genel', NULL, 0, 0, 240, 1, '', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_uyeler`
--

CREATE TABLE `r_uyeler` (
  `no` int(11) NOT NULL,
  `adi` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefon` varchar(255) DEFAULT NULL,
  `gsm` varchar(255) NOT NULL,
  `ulke_no` int(11) NOT NULL DEFAULT 217,
  `sehir_no` int(11) NOT NULL DEFAULT 0,
  `fotograf` text DEFAULT NULL,
  `k_adi` varchar(255) DEFAULT NULL,
  `sifre` varchar(255) DEFAULT NULL,
  `email_alimi` tinyint(1) NOT NULL DEFAULT 1,
  `yetki_no` tinyint(1) NOT NULL DEFAULT 5,
  `aktivasyon` varchar(40) DEFAULT NULL,
  `tarih` timestamp NOT NULL DEFAULT current_timestamp(),
  `son_giris` datetime DEFAULT NULL,
  `yayin` tinyint(1) NOT NULL DEFAULT 0,
  `sifre_reset_token` varchar(255) DEFAULT NULL,
  `sifre_reset_token_expire` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Tablo döküm verisi `r_uyeler`
--

INSERT INTO `r_uyeler` (`no`, `adi`, `email`, `telefon`, `gsm`, `ulke_no`, `sehir_no`, `fotograf`, `k_adi`, `sifre`, `email_alimi`, `yetki_no`, `aktivasyon`, `tarih`, `son_giris`, `yayin`, `sifre_reset_token`, `sifre_reset_token_expire`) VALUES
(2, 'Yönetici', 'korthaci@gmail.com', NULL, '555 555 55 55', 0, 0, NULL, 'admin@websistem', '$2y$10$myELuH0qU6B1m1bDCKr0E.hoNUU/xDp/9JdgGO.hsfXS9WAOWN0h6', 1, 2, NULL, '2025-06-11 16:52:28', '2026-02-06 19:06:37', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `r_yorumlar`
--

CREATE TABLE `r_yorumlar` (
  `no` int(11) NOT NULL,
  `uye_no` int(11) DEFAULT 0,
  `tablo` varchar(30) DEFAULT NULL,
  `tablo_no` int(11) DEFAULT 0,
  `yorum_no` int(11) NOT NULL DEFAULT 0,
  `adi_soyadi` varchar(255) DEFAULT NULL,
  `email_adresi` varchar(255) DEFAULT NULL,
  `mesaj` text DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `tarih` datetime DEFAULT NULL,
  `yayin` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `r_bilesen`
--
ALTER TABLE `r_bilesen`
  ADD PRIMARY KEY (`no`),
  ADD UNIQUE KEY `url` (`url`);

--
-- Tablo için indeksler `r_blok_html`
--
ALTER TABLE `r_blok_html`
  ADD PRIMARY KEY (`no`);

--
-- Tablo için indeksler `r_ceviriler`
--
ALTER TABLE `r_ceviriler`
  ADD PRIMARY KEY (`no`);

--
-- Tablo için indeksler `r_diller`
--
ALTER TABLE `r_diller`
  ADD PRIMARY KEY (`no`);

--
-- Tablo için indeksler `r_genel_ayarlar`
--
ALTER TABLE `r_genel_ayarlar`
  ADD PRIMARY KEY (`no`);

--
-- Tablo için indeksler `r_log_db`
--
ALTER TABLE `r_log_db`
  ADD PRIMARY KEY (`no`);

--
-- Tablo için indeksler `r_menu`
--
ALTER TABLE `r_menu`
  ADD PRIMARY KEY (`no`),
  ADD UNIQUE KEY `tablo` (`tablo`,`tablo_no`);

--
-- Tablo için indeksler `r_moduller`
--
ALTER TABLE `r_moduller`
  ADD PRIMARY KEY (`no`),
  ADD UNIQUE KEY `url` (`url`);

--
-- Tablo için indeksler `r_sayfa`
--
ALTER TABLE `r_sayfa`
  ADD PRIMARY KEY (`no`);

--
-- Tablo için indeksler `r_sekme`
--
ALTER TABLE `r_sekme`
  ADD PRIMARY KEY (`no`);

--
-- Tablo için indeksler `r_uyeler`
--
ALTER TABLE `r_uyeler`
  ADD PRIMARY KEY (`no`),
  ADD UNIQUE KEY `k_adi` (`k_adi`);

--
-- Tablo için indeksler `r_yorumlar`
--
ALTER TABLE `r_yorumlar`
  ADD PRIMARY KEY (`no`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `r_bilesen`
--
ALTER TABLE `r_bilesen`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `r_blok_html`
--
ALTER TABLE `r_blok_html`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `r_ceviriler`
--
ALTER TABLE `r_ceviriler`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `r_diller`
--
ALTER TABLE `r_diller`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- Tablo için AUTO_INCREMENT değeri `r_genel_ayarlar`
--
ALTER TABLE `r_genel_ayarlar`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Tablo için AUTO_INCREMENT değeri `r_log_db`
--
ALTER TABLE `r_log_db`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Tablo için AUTO_INCREMENT değeri `r_menu`
--
ALTER TABLE `r_menu`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Tablo için AUTO_INCREMENT değeri `r_moduller`
--
ALTER TABLE `r_moduller`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- Tablo için AUTO_INCREMENT değeri `r_sayfa`
--
ALTER TABLE `r_sayfa`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `r_sekme`
--
ALTER TABLE `r_sekme`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `r_uyeler`
--
ALTER TABLE `r_uyeler`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `r_yorumlar`
--
ALTER TABLE `r_yorumlar`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
