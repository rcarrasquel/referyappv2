-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 10, 2026 at 02:25 AM
-- Server version: 9.3.0
-- PHP Version: 8.4.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `newreferyapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `card_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interest` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` smallint UNSIGNED NOT NULL DEFAULT '30',
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `card_id`, `product_id`, `full_name`, `phone`, `email`, `interest`, `duration_minutes`, `starts_at`, `ends_at`, `status`, `notes`, `source`, `created_at`, `updated_at`) VALUES
('5239d821-568c-497e-aeb2-40d5ef95c85b', 1, '733f18e5-d744-437c-97a8-ae98b733a957', '7f7e1f4c-eb08-4a5a-91df-94ecf9142f24', 'ertert', '34345', 'dgertert@asd.com', 'retertert', 30, '2026-03-09 11:00:00', '2026-03-09 11:30:00', 'scheduled', NULL, 'dashboard', '2026-03-09 22:31:11', '2026-03-09 22:31:11'),
('80b55f99-0172-4621-965f-6f0565bc25cf', 1, '733f18e5-d744-437c-97a8-ae98b733a957', '22f5a515-aa90-4ff3-9188-8cdb8c73e5f4', 'qweqwe', '234234', 'test@tewst.com', 'werwerwerwer', 30, '2026-03-09 09:30:00', '2026-03-09 10:00:00', 'attended', NULL, 'dashboard', '2026-03-09 22:31:43', '2026-03-09 22:31:43'),
('b949c6a6-fc3a-451b-8ad5-3d1382058ad9', 1, '733f18e5-d744-437c-97a8-ae98b733a957', '7f7e1f4c-eb08-4a5a-91df-94ecf9142f24', 'Andreina romero', '123456789', 'ajrg@gmaaao.com', 'sitio web', 45, '2026-03-09 11:30:00', '2026-03-09 12:15:00', 'scheduled', 'Necesiot un sitoo para mi empresa', 'public', '2026-03-09 23:15:09', '2026-03-09 23:15:09'),
('bf33de43-aaae-4bca-955b-3ae0deb4f791', 1, '733f18e5-d744-437c-97a8-ae98b733a957', '7f7e1f4c-eb08-4a5a-91df-94ecf9142f24', 'Adrian Carrasquel', '2816406955', 'adrian@gmail.com', 'Pagina web', 30, '2026-03-09 09:00:00', '2026-03-09 09:30:00', 'confirmed', NULL, 'dashboard', '2026-03-09 22:25:26', '2026-03-09 22:30:12'),
('d5014a95-e7f5-48bd-9eee-91e329623f67', 1, '733f18e5-d744-437c-97a8-ae98b733a957', '7f7e1f4c-eb08-4a5a-91df-94ecf9142f24', 'la prueba', 'dadsadqwe', 'email@test.com', 'werwer', 30, '2026-03-09 10:30:00', '2026-03-09 11:00:00', 'confirmed', NULL, 'dashboard', '2026-03-09 22:32:27', '2026-03-09 22:51:34'),
('d903f68a-d0bf-49dc-9ed0-784700f4dd6d', 1, '733f18e5-d744-437c-97a8-ae98b733a957', '22f5a515-aa90-4ff3-9188-8cdb8c73e5f4', 'asdasdqewr', 'werrwe', 'test@gma.com', 'werwerwer', 30, '2026-03-09 10:00:00', '2026-03-09 10:30:00', 'scheduled', NULL, 'dashboard', '2026-03-09 22:30:47', '2026-03-09 22:30:47');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_maps_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image_style` enum('circle','rounded','square') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'circle',
  `header_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6DBE45',
  `background_type` enum('color','gradient','image') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'color',
  `background_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#F5F5F5',
  `background_gradient` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_style` enum('rounded','normal','square') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rounded',
  `template_style` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'classic',
  `text_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#111111',
  `button_background_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6DBE45',
  `button_text_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#FFFFFF',
  `links` json DEFAULT NULL,
  `schedule` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cards`
--

INSERT INTO `cards` (`id`, `created_at`, `updated_at`, `user_id`, `name`, `username`, `slug`, `description`, `phone`, `email`, `address`, `google_maps_url`, `profile_image`, `profile_image_style`, `header_image`, `header_color`, `background_type`, `background_color`, `background_gradient`, `background_image`, `button_style`, `template_style`, `text_color`, `button_background_color`, `button_text_color`, `links`, `schedule`, `is_active`) VALUES
('733f18e5-d744-437c-97a8-ae98b733a957', '2026-03-09 09:14:11', '2026-03-10 07:17:56', 1, 'Xperteam', 'xperteam', 'xperteam', 'We do websites and apps', '22800606', 'ramon@gmail.com', 'Houston Texas', 'https://www.google.com/maps/place//@29.8016857,-95.5092479,15z?entry=ttu&g_ep=EgoyMDI2MDMwNS4wIKXMDSoASAFQAw%3D%3D', 'cards/733f18e5-d744-437c-97a8-ae98b733a957/profile_image/JJ8CIC096WiqfmWKLoUY426rFXilYZODtmRe2xiP.jpg', 'circle', 'cards/733f18e5-d744-437c-97a8-ae98b733a957/header_image/th0iExnAfZTvy4muQXdjRLrSMh6s8wgcIbceJuKH.jpg', '#474365', 'color', '#F5F5F5', NULL, 'cards/733f18e5-d744-437c-97a8-ae98b733a957/background_image/TCT3HVIZdAmSgP0d68MG5P5HP1H10NoiB5zC5H3J.jpg', 'square', 'layered_right', '#f49090', '#000000', '#FFFFFF', '[{\"url\": \"2816406988\", \"icon\": \"phone\", \"title\": \"2816406988\", \"auto_key\": \"\", \"description\": \"Telefono principal\"}, {\"url\": \"ramon@test.com\", \"icon\": \"gmail\", \"title\": \"Lo que sea\", \"auto_key\": \"\", \"description\": \"loquesea ponto con\"}, {\"url\": \"qqweqwe\", \"icon\": \"stripe\", \"title\": \"pagame\", \"auto_key\": \"\", \"description\": \"qweqweqwe\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"agarrolo\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"menealo\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"subelo\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"bajalo\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"linkealo\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"menealoll\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"subelo\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"agarra uno nuevo\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"\", \"icon\": \"link\", \"title\": \"suelta lo viejo\", \"auto_key\": \"\", \"description\": \"\"}, {\"url\": \"22800606\", \"icon\": \"phone\", \"title\": \"Phone\", \"auto_key\": \"auto_phone\", \"description\": \"\"}, {\"url\": \"ramon@gmail.com\", \"icon\": \"email\", \"title\": \"Email\", \"auto_key\": \"auto_email\", \"description\": \"\"}, {\"url\": \"https://www.google.com/maps/place//@29.8016857,-95.5092479,15z?entry=ttu&g_ep=EgoyMDI2MDMwNS4wIKXMDSoASAFQAw%3D%3D\", \"icon\": \"google\", \"title\": \"Google Maps\", \"auto_key\": \"auto_maps\", \"description\": \"\"}]', '[{\"day\": 0, \"open\": \"09:00\", \"close\": \"17:00\"}, {\"day\": 1, \"open\": \"09:00\", \"close\": \"17:00\"}, {\"day\": 2, \"open\": \"09:00\", \"close\": \"17:00\"}, {\"day\": 3, \"open\": \"09:00\", \"close\": \"17:00\"}, {\"day\": 4, \"open\": \"09:00\", \"close\": \"17:00\"}, {\"day\": 5, \"open\": \"09:00\", \"close\": \"17:00\"}, {\"day\": 6, \"open\": \"09:00\", \"close\": \"17:00\"}]', 1);

-- --------------------------------------------------------

--
-- Table structure for table `card_link_clicks`
--

CREATE TABLE `card_link_clicks` (
  `id` bigint UNSIGNED NOT NULL,
  `card_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_index` smallint UNSIGNED DEFAULT NULL,
  `link_title` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_url` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fingerprint` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accept_language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referer` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `clicked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `card_link_clicks`
--

INSERT INTO `card_link_clicks` (`id`, `card_id`, `link_index`, `link_title`, `link_url`, `ip_address`, `browser`, `os`, `device_type`, `session_id`, `fingerprint`, `accept_language`, `referer`, `user_agent`, `clicked_at`, `created_at`, `updated_at`) VALUES
(1, '733f18e5-d744-437c-97a8-ae98b733a957', 1, 'Lo que sea', 'https://loquesea', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:35:02', '2026-03-09 20:35:02', '2026-03-09 20:35:02'),
(2, '733f18e5-d744-437c-97a8-ae98b733a957', 0, 'ejemplo', 'https://asdasdasd', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:35:02', '2026-03-09 20:35:02', '2026-03-09 20:35:02'),
(3, '733f18e5-d744-437c-97a8-ae98b733a957', 0, '2816406988', 'https://2816406988', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:29:39', '2026-03-10 03:29:39', '2026-03-10 03:29:39'),
(4, '733f18e5-d744-437c-97a8-ae98b733a957', 0, '2816406988', 'https://2816406988', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:31:01', '2026-03-10 03:31:01', '2026-03-10 03:31:01'),
(5, '733f18e5-d744-437c-97a8-ae98b733a957', 1, 'Lo que sea', 'https://ramon@gmail.com', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:38:20', '2026-03-10 03:38:20', '2026-03-10 03:38:20'),
(6, '733f18e5-d744-437c-97a8-ae98b733a957', 1, 'Lo que sea', 'https://ramon@test.com', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:40:00', '2026-03-10 03:40:00', '2026-03-10 03:40:00'),
(7, '733f18e5-d744-437c-97a8-ae98b733a957', 0, '2816406988', 'tel:2816406988', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:43:08', '2026-03-10 03:43:08', '2026-03-10 03:43:08'),
(8, '733f18e5-d744-437c-97a8-ae98b733a957', 1, 'Lo que sea', 'https://ramon@test.com', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:43:16', '2026-03-10 03:43:16', '2026-03-10 03:43:16'),
(9, '733f18e5-d744-437c-97a8-ae98b733a957', 0, '2816406988', 'tel:2816406988', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:45:17', '2026-03-10 03:45:17', '2026-03-10 03:45:17'),
(10, '733f18e5-d744-437c-97a8-ae98b733a957', 1, 'Lo que sea', 'https://ramon@test.com', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:45:20', '2026-03-10 03:45:20', '2026-03-10 03:45:20'),
(11, '733f18e5-d744-437c-97a8-ae98b733a957', 12, 'Phone', 'tel:22800606', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 04:31:11', '2026-03-10 04:31:11', '2026-03-10 04:31:11'),
(12, '733f18e5-d744-437c-97a8-ae98b733a957', 13, 'Email', 'mailto:ramon@gmail.com', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 04:31:14', '2026-03-10 04:31:14', '2026-03-10 04:31:14');

-- --------------------------------------------------------

--
-- Table structure for table `card_share_events`
--

CREATE TABLE `card_share_events` (
  `id` bigint UNSIGNED NOT NULL,
  `card_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fingerprint` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accept_language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referer` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `shared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `card_visits`
--

CREATE TABLE `card_visits` (
  `id` bigint UNSIGNED NOT NULL,
  `card_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fingerprint` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accept_language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referer` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `visited_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `card_visits`
--

INSERT INTO `card_visits` (`id`, `card_id`, `ip_address`, `browser`, `os`, `device_type`, `session_id`, `fingerprint`, `accept_language`, `referer`, `path`, `user_agent`, `visited_at`, `created_at`, `updated_at`) VALUES
(1, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', NULL, 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:26:28', '2026-03-09 20:26:28', '2026-03-09 20:26:28'),
(2, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', NULL, 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:28:40', '2026-03-09 20:28:40', '2026-03-09 20:28:40'),
(3, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', NULL, 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:30:00', '2026-03-09 20:30:00', '2026-03-09 20:30:00'),
(4, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:47:54', '2026-03-09 20:47:54', '2026-03-09 20:47:54'),
(5, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:48:05', '2026-03-09 20:48:05', '2026-03-09 20:48:05'),
(6, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:50:02', '2026-03-09 20:50:02', '2026-03-09 20:50:02'),
(7, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 20:50:06', '2026-03-09 20:50:06', '2026-03-09 20:50:06'),
(8, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 20:50:21', '2026-03-09 20:50:21', '2026-03-09 20:50:21'),
(9, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 20:58:53', '2026-03-09 20:58:53', '2026-03-09 20:58:53'),
(10, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:03:40', '2026-03-09 21:03:40', '2026-03-09 21:03:40'),
(11, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:03:42', '2026-03-09 21:03:42', '2026-03-09 21:03:42'),
(12, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:04:10', '2026-03-09 21:04:10', '2026-03-09 21:04:10'),
(13, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:04:36', '2026-03-09 21:04:36', '2026-03-09 21:04:36'),
(14, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:05:32', '2026-03-09 21:05:32', '2026-03-09 21:05:32'),
(15, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:05:52', '2026-03-09 21:05:52', '2026-03-09 21:05:52'),
(16, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:06:08', '2026-03-09 21:06:08', '2026-03-09 21:06:08'),
(17, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:07:18', '2026-03-09 21:07:18', '2026-03-09 21:07:18'),
(18, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:07:26', '2026-03-09 21:07:26', '2026-03-09 21:07:26'),
(19, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:09:18', '2026-03-09 21:09:18', '2026-03-09 21:09:18'),
(20, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:09:26', '2026-03-09 21:09:26', '2026-03-09 21:09:26'),
(21, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:10:29', '2026-03-09 21:10:29', '2026-03-09 21:10:29'),
(22, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:10:36', '2026-03-09 21:10:36', '2026-03-09 21:10:36'),
(23, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:11:54', '2026-03-09 21:11:54', '2026-03-09 21:11:54'),
(24, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:11:57', '2026-03-09 21:11:57', '2026-03-09 21:11:57'),
(25, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '0542a3ca4e911394e3491a1ec8543d49808fa85d0c7f916a29b7a145a692e8a4', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-09 21:12:36', '2026-03-09 21:12:36', '2026-03-09 21:12:36'),
(26, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:12:47', '2026-03-09 21:12:47', '2026-03-09 21:12:47'),
(27, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:25:14', '2026-03-09 21:25:14', '2026-03-09 21:25:14'),
(28, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:25:18', '2026-03-09 21:25:18', '2026-03-09 21:25:18'),
(29, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:39:12', '2026-03-09 21:39:12', '2026-03-09 21:39:12'),
(30, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:39:24', '2026-03-09 21:39:24', '2026-03-09 21:39:24'),
(31, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:39:30', '2026-03-09 21:39:30', '2026-03-09 21:39:30'),
(32, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', 'GLknuZsj6xWZiB007TOh6MBMVT7VW28ks7cREtHr', '268ee6c070050792bac73f995fc70f290843956292e89e91bc88458dba3fed46', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:39:40', '2026-03-09 21:39:40', '2026-03-09 21:39:40'),
(33, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:42:32', '2026-03-09 21:42:32', '2026-03-09 21:42:32'),
(34, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:42:40', '2026-03-09 21:42:40', '2026-03-09 21:42:40'),
(35, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:49:11', '2026-03-09 21:49:11', '2026-03-09 21:49:11'),
(36, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 21:50:28', '2026-03-09 21:50:28', '2026-03-09 21:50:28'),
(37, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:18:20', '2026-03-09 22:18:20', '2026-03-09 22:18:20'),
(38, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:18:28', '2026-03-09 22:18:28', '2026-03-09 22:18:28'),
(39, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:18:33', '2026-03-09 22:18:33', '2026-03-09 22:18:33'),
(40, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:20:06', '2026-03-09 22:20:06', '2026-03-09 22:20:06'),
(41, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:21:53', '2026-03-09 22:21:53', '2026-03-09 22:21:53'),
(42, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:22:13', '2026-03-09 22:22:13', '2026-03-09 22:22:13'),
(43, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:22:28', '2026-03-09 22:22:28', '2026-03-09 22:22:28'),
(44, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:26:50', '2026-03-09 22:26:50', '2026-03-09 22:26:50'),
(45, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:26:54', '2026-03-09 22:26:54', '2026-03-09 22:26:54'),
(46, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:36:59', '2026-03-09 22:36:59', '2026-03-09 22:36:59'),
(47, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:37:07', '2026-03-09 22:37:07', '2026-03-09 22:37:07'),
(48, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:39:09', '2026-03-09 22:39:09', '2026-03-09 22:39:09'),
(49, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 22:42:46', '2026-03-09 22:42:46', '2026-03-09 22:42:46'),
(50, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:06:44', '2026-03-09 23:06:44', '2026-03-09 23:06:44'),
(51, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:07:08', '2026-03-09 23:07:08', '2026-03-09 23:07:08'),
(52, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:07:12', '2026-03-09 23:07:12', '2026-03-09 23:07:12'),
(53, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:07:15', '2026-03-09 23:07:15', '2026-03-09 23:07:15'),
(54, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:15:09', '2026-03-09 23:15:09', '2026-03-09 23:15:09'),
(55, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:15:09', '2026-03-09 23:15:09', '2026-03-09 23:15:09'),
(56, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:15:19', '2026-03-09 23:15:19', '2026-03-09 23:15:19'),
(57, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:15:20', '2026-03-09 23:15:20', '2026-03-09 23:15:20'),
(58, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:18:13', '2026-03-09 23:18:13', '2026-03-09 23:18:13'),
(59, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:18:13', '2026-03-09 23:18:13', '2026-03-09 23:18:13'),
(60, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:21:21', '2026-03-09 23:21:21', '2026-03-09 23:21:21'),
(61, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:25:41', '2026-03-09 23:25:41', '2026-03-09 23:25:41'),
(62, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:26:37', '2026-03-09 23:26:37', '2026-03-09 23:26:37'),
(63, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:32:50', '2026-03-09 23:32:50', '2026-03-09 23:32:50'),
(64, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '2RkQxABz4QoQxjv7y1SZd3i0VWlypk856FFoouJ2', '304543345a1949f8c44be88e0f42ac46c1f1499d9cdeb118ab5cc58bd6bd3fda', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-09 23:42:32', '2026-03-09 23:42:32', '2026-03-09 23:42:32'),
(65, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 03:04:15', '2026-03-10 03:04:15', '2026-03-10 03:04:15'),
(66, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 03:10:36', '2026-03-10 03:10:36', '2026-03-10 03:10:36'),
(67, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 03:11:30', '2026-03-10 03:11:30', '2026-03-10 03:11:30'),
(68, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:16:44', '2026-03-10 03:16:44', '2026-03-10 03:16:44'),
(69, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:22:55', '2026-03-10 03:22:55', '2026-03-10 03:22:55'),
(70, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:28:00', '2026-03-10 03:28:00', '2026-03-10 03:28:00'),
(71, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:28:10', '2026-03-10 03:28:10', '2026-03-10 03:28:10'),
(72, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:28:19', '2026-03-10 03:28:19', '2026-03-10 03:28:19'),
(73, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:29:37', '2026-03-10 03:29:37', '2026-03-10 03:29:37'),
(74, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:30:06', '2026-03-10 03:30:06', '2026-03-10 03:30:06'),
(75, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:31:00', '2026-03-10 03:31:00', '2026-03-10 03:31:00'),
(76, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:31:23', '2026-03-10 03:31:23', '2026-03-10 03:31:23'),
(77, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:38:18', '2026-03-10 03:38:18', '2026-03-10 03:38:18'),
(78, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:39:58', '2026-03-10 03:39:58', '2026-03-10 03:39:58'),
(79, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:41:39', '2026-03-10 03:41:39', '2026-03-10 03:41:39'),
(80, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:45:14', '2026-03-10 03:45:14', '2026-03-10 03:45:14'),
(81, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:45:23', '2026-03-10 03:45:23', '2026-03-10 03:45:23'),
(82, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:50:45', '2026-03-10 03:50:45', '2026-03-10 03:50:45'),
(83, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:51:11', '2026-03-10 03:51:11', '2026-03-10 03:51:11'),
(84, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 03:51:16', '2026-03-10 03:51:16', '2026-03-10 03:51:16'),
(85, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Safari', 'macOS', 'mobile', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '5162f60261ff1295e11fdd0f471ad7f409537bafaaaf828459ed2ef657f9670d', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-03-10 04:01:13', '2026-03-10 04:01:13', '2026-03-10 04:01:13'),
(86, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 04:31:02', '2026-03-10 04:31:02', '2026-03-10 04:31:02'),
(87, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 04:34:57', '2026-03-10 04:34:57', '2026-03-10 04:34:57'),
(88, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 05:29:03', '2026-03-10 05:29:03', '2026-03-10 05:29:03'),
(89, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 05:29:06', '2026-03-10 05:29:06', '2026-03-10 05:29:06'),
(90, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 06:05:07', '2026-03-10 06:05:07', '2026-03-10 06:05:07'),
(91, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 06:05:11', '2026-03-10 06:05:11', '2026-03-10 06:05:11'),
(92, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 06:05:57', '2026-03-10 06:05:57', '2026-03-10 06:05:57'),
(93, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 06:06:01', '2026-03-10 06:06:01', '2026-03-10 06:06:01'),
(94, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 06:06:12', '2026-03-10 06:06:12', '2026-03-10 06:06:12'),
(95, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 06:32:51', '2026-03-10 06:32:51', '2026-03-10 06:32:51'),
(96, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:03:10', '2026-03-10 07:03:10', '2026-03-10 07:03:10'),
(97, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:05:19', '2026-03-10 07:05:19', '2026-03-10 07:05:19'),
(98, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:09:07', '2026-03-10 07:09:07', '2026-03-10 07:09:07'),
(99, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:11:33', '2026-03-10 07:11:33', '2026-03-10 07:11:33'),
(100, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:11:38', '2026-03-10 07:11:38', '2026-03-10 07:11:38'),
(101, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:13:20', '2026-03-10 07:13:20', '2026-03-10 07:13:20');
INSERT INTO `card_visits` (`id`, `card_id`, `ip_address`, `browser`, `os`, `device_type`, `session_id`, `fingerprint`, `accept_language`, `referer`, `path`, `user_agent`, `visited_at`, `created_at`, `updated_at`) VALUES
(102, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:13:28', '2026-03-10 07:13:28', '2026-03-10 07:13:28'),
(103, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:13:34', '2026-03-10 07:13:34', '2026-03-10 07:13:34'),
(104, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:14:46', '2026-03-10 07:14:46', '2026-03-10 07:14:46'),
(105, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:16:30', '2026-03-10 07:16:30', '2026-03-10 07:16:30'),
(106, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:16:37', '2026-03-10 07:16:37', '2026-03-10 07:16:37'),
(107, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:17:45', '2026-03-10 07:17:45', '2026-03-10 07:17:45'),
(108, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:18:46', '2026-03-10 07:18:46', '2026-03-10 07:18:46'),
(109, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:18:51', '2026-03-10 07:18:51', '2026-03-10 07:18:51'),
(110, '733f18e5-d744-437c-97a8-ae98b733a957', '127.0.0.1', 'Chrome', 'macOS', 'desktop', '9wdsxEzIWWWandbB0F7JME98AvvCHkNUSxFknDwe', '1d37e373d32d26a414b872d74e26d2f06a97042df2fbf681125a3f6f3cd6bfb7', 'en-US,en;q=0.9,es-US;q=0.8,es-CR;q=0.7,es;q=0.6', 'http://127.0.0.1:8000/p/xperteam', 'p/xperteam', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-10 07:22:36', '2026-03-10 07:22:36', '2026-03-10 07:22:36');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `card_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interest` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `user_id`, `card_id`, `product_id`, `full_name`, `phone`, `email`, `interest`, `notes`, `status`, `source`, `created_at`, `updated_at`) VALUES
('85de391c-9a00-4df4-83cc-22d2c64a7764', 1, '733f18e5-d744-437c-97a8-ae98b733a957', '7f7e1f4c-eb08-4a5a-91df-94ecf9142f24', 'Juan Pland', 'qqew656465', 'test@email.com', 'asdadsasd', 'qweqew asdasd asda ds', 'new', 'public', '2026-03-09 23:18:13', '2026-03-09 23:18:13');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_09_032133_create_cards_table', 2),
(5, '2026_03_09_033607_add_plan_to_users_table', 2),
(6, '2026_03_09_035845_add_links_and_slug_to_cards_table', 3),
(7, '2026_03_09_045122_add_profile_image_style_to_cards_table', 4),
(8, '2026_03_09_210000_add_template_style_to_cards_table', 5),
(9, '2026_03_09_220000_create_card_visits_table', 6),
(10, '2026_03_09_220100_create_card_link_clicks_table', 6),
(11, '2026_03_09_231000_create_products_table', 7),
(12, '2026_03_09_163809_add_schedule_to_cards_table', 8),
(13, '2026_03_09_235000_add_duration_minutes_to_products_table', 9),
(14, '2026_03_09_235100_create_appointments_table', 9),
(15, '2026_03_10_000100_create_leads_table', 10),
(16, '2026_03_10_020000_create_card_share_events_table', 11),
(17, '2026_03_10_030000_add_contact_fields_to_cards_table', 12),
(18, '2026_03_10_010518_create_personal_access_tokens_table', 13);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` smallint UNSIGNED DEFAULT NULL,
  `link` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `user_id`, `name`, `description`, `image`, `price`, `duration_minutes`, `link`, `created_at`, `updated_at`) VALUES
('22f5a515-aa90-4ff3-9188-8cdb8c73e5f4', 1, 'soporte tecnico', 'soporte tecnico de envergadura', 'products/22f5a515-aa90-4ff3-9188-8cdb8c73e5f4/image/5or6RcYXk3DGkLVXSx0xZ02LsNs1E8O2zhqKH5Q2.jpg', 'A convenir', 30, NULL, '2026-03-09 20:15:32', '2026-03-09 22:24:19'),
('7f7e1f4c-eb08-4a5a-91df-94ecf9142f24', 1, 'Pagina web', 'sitios web profesionales + hosting + de todos', 'products/7f7e1f4c-eb08-4a5a-91df-94ecf9142f24/image/fmVB69becFDaGi1dQ5FzrH0ERLEoZkOvaBgkE0PU.jpg', '$254', 45, NULL, '2026-03-09 20:00:58', '2026-03-09 22:24:23');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('GasEiFnJkgxN9Ht1sL8Fb4da0WtWhzlvcHzmuzqi', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSUx5Y3E5STRVME1VRWxIak1YanNBTFlMQWxSUVpMckhRUzF6a2c0SiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO319', 1773023194);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('business','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'business',
  `language` enum('en','es') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `plan` enum('free','monthly','lifetime') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `language`, `plan`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'ReferyApp', 'admin@refery.app', NULL, '$2y$12$104vwn869vgJmg.6ZmvG5ea0Q3PU2VKErLzI7l5QbyfRK2UBOFPey', 'business', 'en', 'free', 1, 'h2xqzCmqCY2pyOjgy7yOdlDdUt8o2XosMGsfSkCHZ8prWUbIV6b4E7gtic9s', '2026-03-09 07:50:30', '2026-03-09 07:50:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointments_product_id_foreign` (`product_id`),
  ADD KEY `appointments_user_id_starts_at_index` (`user_id`,`starts_at`),
  ADD KEY `appointments_card_id_starts_at_index` (`card_id`,`starts_at`),
  ADD KEY `appointments_status_starts_at_index` (`status`,`starts_at`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cards_username_unique` (`username`),
  ADD UNIQUE KEY `cards_slug_unique` (`slug`),
  ADD KEY `cards_user_id_foreign` (`user_id`);

--
-- Indexes for table `card_link_clicks`
--
ALTER TABLE `card_link_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_link_clicks_card_id_clicked_at_index` (`card_id`,`clicked_at`),
  ADD KEY `card_link_clicks_card_id_link_index_index` (`card_id`,`link_index`),
  ADD KEY `card_link_clicks_clicked_at_index` (`clicked_at`);

--
-- Indexes for table `card_share_events`
--
ALTER TABLE `card_share_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_share_events_card_id_shared_at_index` (`card_id`,`shared_at`),
  ADD KEY `card_share_events_card_id_channel_index` (`card_id`,`channel`),
  ADD KEY `card_share_events_shared_at_index` (`shared_at`);

--
-- Indexes for table `card_visits`
--
ALTER TABLE `card_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_visits_card_id_visited_at_index` (`card_id`,`visited_at`),
  ADD KEY `card_visits_card_id_fingerprint_index` (`card_id`,`fingerprint`),
  ADD KEY `card_visits_visited_at_index` (`visited_at`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leads_product_id_foreign` (`product_id`),
  ADD KEY `leads_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `leads_card_id_created_at_index` (`card_id`,`created_at`),
  ADD KEY `leads_status_created_at_index` (`status`,`created_at`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `card_link_clicks`
--
ALTER TABLE `card_link_clicks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `card_share_events`
--
ALTER TABLE `card_share_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `card_visits`
--
ALTER TABLE `card_visits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cards`
--
ALTER TABLE `cards`
  ADD CONSTRAINT `cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `card_link_clicks`
--
ALTER TABLE `card_link_clicks`
  ADD CONSTRAINT `card_link_clicks_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `card_share_events`
--
ALTER TABLE `card_share_events`
  ADD CONSTRAINT `card_share_events_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `card_visits`
--
ALTER TABLE `card_visits`
  ADD CONSTRAINT `card_visits_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leads_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
