-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 23. Jun 2025 um 22:41
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `cms`
--

-- --------------------------------------------------------

--
-- Daten für Tabelle `media`
-- (Leer - Uploads werden durch .gitignore ignoriert)
--

-- --------------------------------------------------------

--
-- Daten für Tabelle `menus`
--

INSERT INTO `menus` (`id`, `name`) VALUES
(6, 'main');

-- --------------------------------------------------------

--
-- Daten für Tabelle `menu_items`
--

INSERT INTO `menu_items` (`id`, `menu_id`, `label`, `url`, `position`) VALUES
(31, 6, 'Willkommen', '/startseite', 0);

-- --------------------------------------------------------

--
-- Daten für Tabelle `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `template`, `page_blocks`, `created_at`, `created_by`, `views`) VALUES
(1, 'startseite', 'Willkommen', 'default', '[\n    {\n        \"id\": 1,\n        \"type\": \"heading\",\n        \"content\": \"Willkommen auf meiner Website\",\n        \"settings\": {\n            \"tag\": \"h1\",\n            \"fontSize\": \"48\",\n            \"fontWeight\": \"600\",\n            \"color\": \"#1e293b\",\n            \"textAlign\": \"center\",\n            \"paddingTop\": \"20\",\n            \"paddingRight\": \"0\",\n            \"paddingBottom\": \"20\",\n            \"paddingLeft\": \"0\",\n            \"marginTop\": \"0\",\n            \"marginRight\": \"0\",\n            \"marginBottom\": \"0\",\n            \"marginLeft\": \"0\",\n            \"width\": \"100%\",\n            \"height\": \"auto\"\n        }\n    },\n    {\n        \"id\": 2,\n        \"type\": \"text\",\n        \"content\": \"Dies ist eine Beispiel-Startseite. Sie können diese Seite im Page Builder bearbeiten und nach Ihren Wünschen anpassen.\",\n        \"settings\": {\n            \"fontSize\": \"18\",\n            \"fontWeight\": \"400\",\n            \"color\": \"#475569\",\n            \"textAlign\": \"center\",\n            \"paddingTop\": \"0\",\n            \"paddingRight\": \"0\",\n            \"paddingBottom\": \"0\",\n            \"paddingLeft\": \"0\",\n            \"marginTop\": \"20\",\n            \"marginRight\": \"0\",\n            \"marginBottom\": \"0\",\n            \"marginLeft\": \"0\",\n            \"width\": \"100%\",\n            \"height\": \"auto\"\n        }\n    },\n    {\n        \"id\": 3,\n        \"type\": \"text\",\n        \"content\": \"Fügen Sie weitere Blöcke hinzu, um Ihre Seite zu erweitern. Sie können Überschriften, Texte, Bilder und Container verwenden.\",\n        \"settings\": {\n            \"fontSize\": \"16\",\n            \"fontWeight\": \"400\",\n            \"color\": \"#64748b\",\n            \"textAlign\": \"left\",\n            \"paddingTop\": \"30\",\n            \"paddingRight\": \"0\",\n            \"paddingBottom\": \"0\",\n            \"paddingLeft\": \"0\",\n            \"marginTop\": \"0\",\n            \"marginRight\": \"0\",\n            \"marginBottom\": \"0\",\n            \"marginLeft\": \"0\",\n            \"width\": \"100%\",\n            \"height\": \"auto\"\n        }\n    }\n]', '2025-06-20 11:34:20', NULL, 0);

-- --------------------------------------------------------

--
-- Daten für Tabelle `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'site_title', 'Mein CMS'),
(2, 'site_description', 'Ein modernes Content Management System'),
(3, 'site_url', 'http://localhost/cms'),
(4, 'logo_path', ''),
(5, 'favicon_path', ''),
(6, 'footer_text', '© 2025 Mein CMS. Alle Rechte vorbehalten.'),
(7, 'color_primary', '#667eea'),
(8, 'color_secondary', '#764ba2'),
(9, 'color_background', '#f8f9fa'),
(10, 'font_family', 'Arial, sans-serif'),
(11, 'button_style', 'solid'),
(12, 'layout_container', 'fullwidth'),
(13, 'menu_header_id', '6'),
(14, 'menu_footer_id', ''),
(15, 'menu_sidebar_id', NULL),
(16, 'contact_email', 'kontakt@meinewebseite.de'),
(17, 'phone_number', '+49 123 4567890'),
(18, 'address', 'Musterstraße 1, 12345 Musterstadt'),
(19, 'maintenance_mode', 'off'),
(20, 'show_cookie_notice', 'yes'),
(21, 'cache_enabled', '0'),
(22, 'debug_mode', 'no'),
(23, 'og_image_path', ''),
(24, 'meta_keywords', 'cms, content management, php, website'),
(25, 'robots_directive', 'index, follow'),
(26, 'copyright_text', '© 2025 Mein CMS. Alle Rechte vorbehalten.');

-- --------------------------------------------------------

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `email`, `full_name`, `profile_image`) VALUES
(1, 'admin', '$2y$10$l8EoMPQLIv9qN2VGgczg5.QPgUPj8qIy2GpGMasAIvqoDqiwFWM8a', 'admin', '2025-06-20 11:34:20', 'admin@example.com', 'Administrator', NULL);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;