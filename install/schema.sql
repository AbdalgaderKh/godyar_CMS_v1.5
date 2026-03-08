-- Godyar News Platform Clean Install Schema
-- Generated from provided database structure with data, views, triggers, and secrets removed.
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;
SET FOREIGN_KEY_CHECKS=0;

-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- مضيف: localhost:3306
-- وقت الجيل: 08 مارس 2026 الساعة 02:29
-- إصدار الخادم: 10.11.16-MariaDB-cll-lve
-- نسخة PHP: 8.4.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- قاعدة بيانات: `geqzylcq_myar`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `admin_saved_filters`
--

CREATE TABLE `admin_saved_filters` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `page_key` varchar(64) NOT NULL,
  `name` varchar(120) NOT NULL,
  `querystring` varchar(1000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `ads`
--

CREATE TABLE `ads` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(50) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `max_clicks` int(10) UNSIGNED DEFAULT NULL,
  `max_views` int(10) UNSIGNED DEFAULT NULL,
  `click_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `analytics_events`
--

CREATE TABLE `analytics_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_name` varchar(190) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `lang` varchar(12) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `is_members_only` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `lang`, `parent_id`, `sort_order`, `is_active`, `created_at`, `updated_at`, `is_members_only`) VALUES
(2, 'أخبار عامة', 'general-news', NULL, NULL, 0, 1, '2026-03-02 19:10:01', '2026-03-07 13:08:12', 0);

-- --------------------------------------------------------

--
-- --------------------------------------------------------

--
-- --------------------------------------------------------

--
-- بنية الجدول `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(190) NOT NULL,
  `email` varchar(190) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `replied_at` datetime DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `featured_videos`
--

CREATE TABLE `featured_videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `feeds`
--

CREATE TABLE `feeds` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `fetch_interval_minutes` int(11) NOT NULL DEFAULT 60,
  `last_fetched_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `gdy_glossary`
--

CREATE TABLE `gdy_glossary` (
  `id` int(10) UNSIGNED NOT NULL,
  `term` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `short_definition` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `migrations`
--

CREATE TABLE `migrations` (
  `id` varchar(191) NOT NULL,
  `ran_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news`
--

CREATE TABLE `news` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `opinion_author_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'published',
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `featured_image` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_breaking` tinyint(1) NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `is_members_only` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(300) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `publish_at` datetime DEFAULT NULL,
  `unpublish_at` datetime DEFAULT NULL,
  `author_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `news`
--

INSERT INTO `news` (`id`, `category_id`, `opinion_author_id`, `title`, `slug`, `excerpt`, `content`, `status`, `is_published`, `featured_image`, `image_path`, `image`, `is_breaking`, `view_count`, `published_at`, `created_at`, `updated_at`, `deleted_at`, `is_members_only`, `views`, `seo_title`, `seo_description`, `seo_keywords`, `publish_at`, `unpublish_at`, `author_id`) VALUES
(6, 2, NULL, 'طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست العراق', 'طهران-تعلن-استعدادها-لـ-حرب-طويلة-وواشنطن-إيران-ليست-العراق', '', 'اتسع نطاق الحرب الجوية الأميركية الإسرائيلية ضد إيران، اليوم الاثنين، دون أن تلوح في الأفق نهاية لها بعد أن شنت إسرائيل هجوما على لبنان ردا على هجمات لجماعة «حزب الله»، وأطلقت طهران صواريخ وطائرات مسيرة على دول خليجية ​وقاعدة جوية بريطانية في قبرص.\r\n\r\nوفي أول إحاطة رسمية لوزارة الحرب الأميركية منذ اندلاع الحملة العسكرية، أحجم وزير الحرب بيت هيغسيث عن تحديد إطار زمني لإنهاء الحرب، قائلا إن الأمر متروك للرئيس دونالد ترمب. وأوضح هيغسيث أن هدف الجيش هو تدمير قدرة إيران على بسط نفوذها خارج حدودها، والذي كانت تستخدمه غطاء لصنع سلاح نووي.\r\n\r\nوأسقطت الكويت بالخطأ ثلاث طائرات مقاتلة أميركية من طراز «إف-15إي» خلال التصدي لهجوم إيراني. وقفز جميع أفراد الطاقم الستة بالمظلات وجرى إنقاذهم. وأظهر مقطع فيديو إحدى الطائرات وهي تسقط ومحركها يشتعل.\r\n', 'published', 1, 'uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp', 'uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp', 'uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp', 1, 0, '2026-03-02 17:16:00', '2026-03-02 19:16:58', '2026-03-08 02:27:58', NULL, 0, 56, 'طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست ال', 'طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست العراق', 'الحرب, الأميركية, إيران, هيغسيث, اتسع, نطاق, الجوية, الإسرائيلية, اليوم, الاثنين', NULL, NULL, 2);

DELIMITER $$
CREATE TRIGGER `trg_news_bu` BEFORE UPDATE ON `news` FOR EACH ROW BEGIN
  SET NEW.is_published =
    CASE
      WHEN NEW.status = 'published' AND NEW.deleted_at IS NULL THEN 1
      ELSE 0
    END;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- بنية الجدول `news_attachments`
--

CREATE TABLE `news_attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `news_id` int(10) UNSIGNED NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_audit_log`
--

CREATE TABLE `news_audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `news_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `before_json` longtext DEFAULT NULL,
  `after_json` longtext DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_comments`
--

CREATE TABLE `news_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `news_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(190) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `body` text NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `score` int(11) NOT NULL DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_comment_votes`
--

CREATE TABLE `news_comment_votes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `value` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_imports`
--

CREATE TABLE `news_imports` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `feed_id` int(11) NOT NULL,
  `item_hash` char(40) NOT NULL,
  `item_link` varchar(1000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_import_items`
--

CREATE TABLE `news_import_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `feed_id` int(10) UNSIGNED DEFAULT NULL,
  `item_hash` char(64) NOT NULL,
  `item_link` varchar(500) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `payload_json` longtext DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `imported_news_id` int(10) UNSIGNED DEFAULT NULL,
  `error_text` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_notes`
--

CREATE TABLE `news_notes` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_questions`
--

CREATE TABLE `news_questions` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `answered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_reactions`
--

CREATE TABLE `news_reactions` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_revisions`
--

CREATE TABLE `news_revisions` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(30) NOT NULL DEFAULT 'update',
  `payload` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `news_revisions`
--

INSERT INTO `news_revisions` (`id`, `news_id`, `user_id`, `action`, `payload`, `created_at`) VALUES
(6, 6, 2, 'update', '{\"news\":{\"id\":6,\"category_id\":2,\"opinion_author_id\":null,\"title\":\"طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست العراق\",\"slug\":\"طهران-تعلن-استعدادها-لـ-حرب-طويلة-وواشنطن-إيران-ليست-العراق\",\"excerpt\":\"\",\"content\":\"اتسع نطاق الحرب الجوية الأميركية الإسرائيلية ضد إيران، اليوم الاثنين، دون أن تلوح في الأفق نهاية لها بعد أن شنت إسرائيل هجوما على لبنان ردا على هجمات لجماعة «حزب الله»، وأطلقت طهران صواريخ وطائرات مسيرة على دول خليجية ​وقاعدة جوية بريطانية في قبرص.\\r\\n\\r\\nوفي أول إحاطة رسمية لوزارة الحرب الأميركية منذ اندلاع الحملة العسكرية، أحجم وزير الحرب بيت هيغسيث عن تحديد إطار زمني لإنهاء الحرب، قائلا إن الأمر متروك للرئيس دونالد ترمب. وأوضح هيغسيث أن هدف الجيش هو تدمير قدرة إيران على بسط نفوذها خارج حدودها، والذي كانت تستخدمه غطاء لصنع سلاح نووي.\\r\\n\\r\\nوأسقطت الكويت بالخطأ ثلاث طائرات مقاتلة أميركية من طراز «إف-15إي» خلال التصدي لهجوم إيراني. وقفز جميع أفراد الطاقم الستة بالمظلات وجرى إنقاذهم. وأظهر مقطع فيديو إحدى الطائرات وهي تسقط ومحركها يشتعل.\\r\\n\",\"status\":\"published\",\"is_published\":1,\"featured_image\":\"uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp\",\"image_path\":\"uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp\",\"image\":\"uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp\",\"is_breaking\":1,\"view_count\":0,\"published_at\":\"2026-03-02 17:16:58\",\"created_at\":\"2026-03-02 19:16:58\",\"updated_at\":null,\"deleted_at\":null,\"is_members_only\":0,\"views\":0,\"seo_title\":\"طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست ال\",\"seo_description\":\"طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست العراق\",\"seo_keywords\":\"الحرب, الأميركية, إيران, هيغسيث, اتسع, نطاق, الجوية, الإسرائيلية, اليوم, الاثنين\",\"publish_at\":null,\"unpublish_at\":null,\"author_id\":2},\"tags\":\"إيران، استعدادها، تعلن، طهران، طويلة، وواشنطن\"}', '2026-03-02 19:17:06'),
(7, 6, 2, 'update', '{\"news\":{\"id\":6,\"category_id\":2,\"opinion_author_id\":null,\"title\":\"طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست العراق\",\"slug\":\"طهران-تعلن-استعدادها-لـ-حرب-طويلة-وواشنطن-إيران-ليست-العراق\",\"excerpt\":\"\",\"content\":\"اتسع نطاق الحرب الجوية الأميركية الإسرائيلية ضد إيران، اليوم الاثنين، دون أن تلوح في الأفق نهاية لها بعد أن شنت إسرائيل هجوما على لبنان ردا على هجمات لجماعة «حزب الله»، وأطلقت طهران صواريخ وطائرات مسيرة على دول خليجية ​وقاعدة جوية بريطانية في قبرص.\\r\\n\\r\\nوفي أول إحاطة رسمية لوزارة الحرب الأميركية منذ اندلاع الحملة العسكرية، أحجم وزير الحرب بيت هيغسيث عن تحديد إطار زمني لإنهاء الحرب، قائلا إن الأمر متروك للرئيس دونالد ترمب. وأوضح هيغسيث أن هدف الجيش هو تدمير قدرة إيران على بسط نفوذها خارج حدودها، والذي كانت تستخدمه غطاء لصنع سلاح نووي.\\r\\n\\r\\nوأسقطت الكويت بالخطأ ثلاث طائرات مقاتلة أميركية من طراز «إف-15إي» خلال التصدي لهجوم إيراني. وقفز جميع أفراد الطاقم الستة بالمظلات وجرى إنقاذهم. وأظهر مقطع فيديو إحدى الطائرات وهي تسقط ومحركها يشتعل.\\r\\n\",\"status\":\"published\",\"is_published\":1,\"featured_image\":\"uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp\",\"image_path\":\"uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp\",\"image\":\"uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp\",\"is_breaking\":1,\"view_count\":0,\"published_at\":\"2026-03-02 17:16:00\",\"created_at\":\"2026-03-02 19:16:58\",\"updated_at\":\"2026-03-02 19:17:06\",\"deleted_at\":null,\"is_members_only\":0,\"views\":0,\"seo_title\":\"طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست ال\",\"seo_description\":\"طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست العراق\",\"seo_keywords\":\"الحرب, الأميركية, إيران, هيغسيث, اتسع, نطاق, الجوية, الإسرائيلية, اليوم, الاثنين\",\"publish_at\":null,\"unpublish_at\":null,\"author_id\":2},\"tags\":\"إيران، استعدادها، تعلن، طهران، طويلة، وواشنطن\"}', '2026-03-02 19:17:06');

-- --------------------------------------------------------

--
-- بنية الجدول `news_tags`
--

CREATE TABLE `news_tags` (
  `news_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `news_tags`
--

INSERT INTO `news_tags` (`news_id`, `tag_id`) VALUES
(6, 26),
(6, 27),
(6, 28),
(6, 29),
(6, 30),
(6, 31);

-- --------------------------------------------------------

--
-- بنية الجدول `news_translations`
--

CREATE TABLE `news_translations` (
  `id` int(10) UNSIGNED NOT NULL,
  `news_id` int(11) NOT NULL,
  `lang` varchar(12) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `news_views`
--

CREATE TABLE `news_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `news_id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `opinion_authors`
--

CREATE TABLE `opinion_authors` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `page_title` varchar(190) DEFAULT NULL,
  `specialization` varchar(190) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `articles_count` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `content` longtext DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'published',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `token` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(120) NOT NULL,
  `label` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `slug` varchar(190) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `endpoint_hash` char(40) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `endpoint` text NOT NULL,
  `p256dh` text NOT NULL,
  `auth` text NOT NULL,
  `prefs_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prefs_json`)),
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `label` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `checksum` char(64) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(191) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `data` mediumtext DEFAULT NULL,
  `last_activity` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('facebook.app_id', '61585890473924', '2026-03-02 11:49:54'),
('facebook.pixel_id', '850674231109983', '2026-03-02 11:49:54'),
('front_preset', 'blue', '2026-03-08 02:24:08'),
('front_theme', 'assets/css/themes/theme-blue.css', '2026-03-08 02:24:08'),
('frontend_theme', 'assets/css/themes/theme-blue.css', '2026-03-08 02:24:08'),
('logo', '/assets/uploads/site/logo_20260308_002311_59fe0bfc.png', '2026-03-08 00:23:11'),
('media.compress.enabled', '1', '2026-03-08 00:23:11'),
('media.compress.max_width', '1920', '2026-03-08 00:23:11'),
('media.compress.quality', '82', '2026-03-08 00:23:11'),
('media.watermark.enabled', '1', '2026-03-08 00:23:11'),
('media.watermark.opacity', '35', '2026-03-08 00:23:11'),
('site_description', 'godyar_CMS_v1.5', NULL),
('site_email', 'abdalgaderkh@gmail.com', NULL),
('site_favicon', '', '2026-03-08 00:23:11'),
('site_installed_at', '2026-03-02 10:55:36', NULL),
('site_lang', 'ar', NULL),
('site_logo', '/assets/uploads/site/logo_20260308_002311_59fe0bfc.png', '2026-03-08 00:23:11'),
('site_name', 'Godyar CMS', NULL),
('site_theme', 'assets/css/themes/theme-blue.css', '2026-03-08 02:24:08'),
('site_timezone', 'Asia/Riyadh', NULL),
('site_url', 'Godyar.org', NULL),
('site_version', '1.5.0', NULL),
('site.address', 'الخرطوم', '2026-03-08 00:23:11'),
('site.desc', 'godyar CMS', '2026-03-08 00:23:11'),
('site.email', 'abdalgaderkh@gmail.com', '2026-03-08 00:23:11'),
('site.favicon', '', '2026-03-08 00:23:11'),
('site.logo', '/assets/uploads/site/logo_20260308_002311_59fe0bfc.png', '2026-03-08 00:23:11'),
('site.name', 'godyar CMS', '2026-03-08 00:23:11'),
('site.phone', '066554507127', '2026-03-08 00:23:11'),
('site.theme_color', '#0ea5e9', '2026-03-08 00:23:11'),
('site.url', 'https://www.godyar.org/', '2026-03-08 00:23:11'),
('social.facebook', 'https://www.facebook.com/profile.php?id=61585890473924', '2026-03-02 11:49:54'),
('social.instagram', 'godyar_cms', '2026-03-02 11:49:54'),
('social.telegram', 'godyar_cms', '2026-03-02 11:49:54'),
('social.twitter', 'godyar_cms', '2026-03-02 11:49:54'),
('social.whatsapp', '249964056666', '2026-03-02 11:49:54'),
('social.youtube', 'godyar_cms', '2026-03-02 11:49:54'),
('telegram.bot_token', 'Myar1979@', '2026-03-02 11:49:54'),
('telegram.chat_id', '', '2026-03-02 11:49:54');

-- --------------------------------------------------------

--
-- بنية الجدول `settings_snapshots`
--

CREATE TABLE `settings_snapshots` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `data_json` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key_name` varchar(191) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `slider`
--

CREATE TABLE `slider` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image_path` varchar(1024) DEFAULT NULL,
  `link_url` varchar(1024) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `slider`
--

INSERT INTO `slider` (`id`, `title`, `subtitle`, `image_path`, `link_url`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(4, 'طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست العراق', 'طهران تعلن استعدادها لـ«حرب طويلة»... وواشنطن: إيران ليست العراق', 'https://godyar.org/uploads/news/news_6197cc03042dc46a178867bb648bd04b.webp', 'https://godyar.org/news/id/6', 1, 1, '2026-03-07 20:24:47', '2026-03-07 20:24:47');

-- --------------------------------------------------------

--
-- بنية الجدول `tags`
--

CREATE TABLE `tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`, `is_active`, `created_at`) VALUES
(26, 'طهران', 'طهران', 1, '2026-03-02 19:16:58'),
(27, 'تعلن', 'تعلن', 1, '2026-03-02 19:16:58'),
(28, 'استعدادها', 'استعدادها', 1, '2026-03-02 19:16:58'),
(29, 'طويلة', 'طويلة', 1, '2026-03-02 19:16:58'),
(30, 'وواشنطن', 'وواشنطن', 1, '2026-03-02 19:16:58'),
(31, 'إيران', 'إيران', 1, '2026-03-02 19:16:58');

-- --------------------------------------------------------

--
-- بنية الجدول `tag_meta`
--

CREATE TABLE `tag_meta` (
  `tag_id` int(10) UNSIGNED NOT NULL,
  `intro` text DEFAULT NULL,
  `cover_path` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `team_members`
--

CREATE TABLE `team_members` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `role` varchar(190) DEFAULT NULL,
  `title` varchar(190) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `github_id` varchar(60) DEFAULT NULL,
  `name` varchar(190) DEFAULT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','editor','writer','author','user') NOT NULL DEFAULT 'user',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `twofa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `twofa_secret` varchar(255) DEFAULT NULL,
  `twofa_backup_codes` text DEFAULT NULL,
  `session_version` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `github_id`, `name`, `username`, `email`, `password_hash`, `password`, `role`, `is_admin`, `status`, `avatar`, `last_login_at`, `created_at`, `updated_at`, `twofa_enabled`, `twofa_secret`, `twofa_backup_codes`, `session_version`) VALUES
(2, NULL, 'admin', 'admin', 'abdalgaderkh@gmail.com', '$2y$12$cOA39Thaz16mPoPQR8xgt.RIIwaRugGO5bAcYAm8L/Ld7D2aP.SAG', NULL, 'admin', 1, 'active', NULL, NULL, '2026-03-02 10:55:37', NULL, 0, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `user_bookmarks`
--

CREATE TABLE `user_bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `visits`
--

CREATE TABLE `visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page` varchar(190) NOT NULL,
  `news_id` int(10) UNSIGNED DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `user_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `device` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- فهارس للجدول `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_notifications_user` (`user_id`),
  ADD KEY `idx_admin_notifications_read` (`is_read`);

--
-- فهارس للجدول `admin_saved_filters`
--
ALTER TABLE `admin_saved_filters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_saved_filters_user_page` (`user_id`,`page_key`),
  ADD KEY `idx_admin_saved_filters_default` (`user_id`,`page_key`,`is_default`);

--
-- فهارس للجدول `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ads_location` (`location`),
  ADD KEY `idx_ads_active` (`is_active`),
  ADD KEY `idx_ads_featured` (`is_featured`),
  ADD KEY `idx_ads_time` (`starts_at`,`ends_at`);

--
-- فهارس للجدول `analytics_events`
--
ALTER TABLE `analytics_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ae_event` (`event_name`),
  ADD KEY `idx_ae_user` (`user_id`),
  ADD KEY `idx_ae_created` (`created_at`);

--
-- فهارس للجدول `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_categories_slug` (`slug`),
  ADD KEY `idx_categories_parent` (`parent_id`),
  ADD KEY `idx_categories_members_only` (`is_members_only`),
  ADD KEY `idx_categories_slug` (`slug`),
  ADD KEY `idx_categories_lang` (`lang`);

--
-- فهارس للجدول `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- فهارس للجدول `featured_videos`
--
ALTER TABLE `featured_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- فهارس للجدول `feeds`
--
ALTER TABLE `feeds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_feeds_url` (`url`(191)),
  ADD KEY `idx_feeds_category_id` (`category_id`),
  ADD KEY `idx_feeds_is_active` (`is_active`);

--
-- فهارس للجدول `gdy_glossary`
--
ALTER TABLE `gdy_glossary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_glossary_slug` (`slug`),
  ADD KEY `idx_glossary_active` (`is_active`);

--
-- فهارس للجدول `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- فهارس للجدول `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_news_slug` (`slug`),
  ADD KEY `idx_news_category` (`category_id`),
  ADD KEY `idx_news_opinion_author` (`opinion_author_id`),
  ADD KEY `idx_news_status` (`status`),
  ADD KEY `idx_news_published` (`published_at`),
  ADD KEY `idx_news_members_only` (`is_members_only`),
  ADD KEY `idx_news_status_publish` (`status`,`published_at`),
  ADD KEY `idx_news_cat_pub` (`category_id`,`is_published`,`published_at`),
  ADD KEY `idx_news_category_id` (`category_id`),
  ADD KEY `idx_news_is_published` (`is_published`),
  ADD KEY `idx_news_category_pub` (`category_id`,`is_published`,`published_at`);

--
-- فهارس للجدول `news_attachments`
--
ALTER TABLE `news_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_att_news` (`news_id`);

--
-- فهارس للجدول `news_audit_log`
--
ALTER TABLE `news_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news_id` (`news_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- فهارس للجدول `news_comments`
--
ALTER TABLE `news_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nc_news` (`news_id`),
  ADD KEY `idx_nc_status` (`status`),
  ADD KEY `idx_nc_parent` (`parent_id`),
  ADD KEY `idx_nc_created` (`created_at`);

--
-- فهارس للجدول `news_comment_votes`
--
ALTER TABLE `news_comment_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_vote_user` (`comment_id`,`user_id`),
  ADD KEY `idx_vote_comment` (`comment_id`),
  ADD KEY `idx_vote_ip` (`ip`);

--
-- فهارس للجدول `news_imports`
--
ALTER TABLE `news_imports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_item_hash` (`item_hash`),
  ADD KEY `idx_feed` (`feed_id`),
  ADD KEY `idx_news` (`news_id`);

--
-- فهارس للجدول `news_import_items`
--
ALTER TABLE `news_import_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_item_hash` (`item_hash`),
  ADD KEY `idx_feed` (`feed_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- فهارس للجدول `news_notes`
--
ALTER TABLE `news_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news_id` (`news_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- فهارس للجدول `news_questions`
--
ALTER TABLE `news_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news` (`news_id`),
  ADD KEY `idx_status` (`status`);

--
-- فهارس للجدول `news_reactions`
--
ALTER TABLE `news_reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news` (`news_id`),
  ADD KEY `idx_news_type` (`news_id`,`type`);

--
-- فهارس للجدول `news_revisions`
--
ALTER TABLE `news_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news_id` (`news_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- فهارس للجدول `news_tags`
--
ALTER TABLE `news_tags`
  ADD PRIMARY KEY (`news_id`,`tag_id`),
  ADD KEY `idx_news_tags_news` (`news_id`),
  ADD KEY `idx_news_tags_tag` (`tag_id`);

--
-- فهارس للجدول `news_translations`
--
ALTER TABLE `news_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_news_lang` (`news_id`,`lang`),
  ADD KEY `idx_lang` (`lang`),
  ADD KEY `idx_news` (`news_id`);

--
-- فهارس للجدول `news_views`
--
ALTER TABLE `news_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nv_news` (`news_id`),
  ADD KEY `idx_nv_viewed` (`viewed_at`);

--
-- فهارس للجدول `opinion_authors`
--
ALTER TABLE `opinion_authors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_opinion_authors_slug` (`slug`),
  ADD KEY `idx_opinion_authors_active` (`is_active`),
  ADD KEY `idx_opinion_authors_order` (`display_order`);

--
-- فهارس للجدول `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_pages_slug` (`slug`);

--
-- فهارس للجدول `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- فهارس للجدول `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_permissions_code` (`code`);

--
-- فهارس للجدول `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_endpoint` (`endpoint_hash`),
  ADD KEY `idx_user` (`user_id`);

--
-- فهارس للجدول `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_roles_name` (`name`);

--
-- فهارس للجدول `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`);

--
-- فهارس للجدول `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_schema_migrations_name` (`name`);

--
-- فهارس للجدول `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sessions_session_id` (`session_id`),
  ADD KEY `idx_sessions_last_activity` (`last_activity`),
  ADD KEY `idx_sessions_user_id` (`user_id`);

--
-- فهارس للجدول `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- فهارس للجدول `settings_snapshots`
--
ALTER TABLE `settings_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- فهارس للجدول `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_site_settings_key` (`key_name`);

--
-- فهارس للجدول `slider`
--
ALTER TABLE `slider`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_slider_active_sort` (`is_active`,`sort_order`,`id`);

--
-- فهارس للجدول `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_tags_slug` (`slug`);

--
-- فهارس للجدول `tag_meta`
--
ALTER TABLE `tag_meta`
  ADD PRIMARY KEY (`tag_id`);

--
-- فهارس للجدول `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_team_active` (`is_active`),
  ADD KEY `idx_team_sort` (`sort_order`);

--
-- فهارس للجدول `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_users_email` (`email`),
  ADD UNIQUE KEY `uniq_users_username` (`username`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_is_admin` (`is_admin`);

--
-- فهارس للجدول `user_bookmarks`
--
ALTER TABLE `user_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_news` (`user_id`,`news_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_news_id` (`news_id`);

--
-- فهارس للجدول `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`);

--
-- فهارس للجدول `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_visits_page` (`page`),
  ADD KEY `idx_visits_news_id` (`news_id`),
  ADD KEY `idx_visits_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_saved_filters`
--
ALTER TABLE `admin_saved_filters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `analytics_events`
--
ALTER TABLE `analytics_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `featured_videos`
--
ALTER TABLE `featured_videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feeds`
--
ALTER TABLE `feeds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gdy_glossary`
--
ALTER TABLE `gdy_glossary`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `news_attachments`
--
ALTER TABLE `news_attachments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_audit_log`
--
ALTER TABLE `news_audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_comments`
--
ALTER TABLE `news_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_comment_votes`
--
ALTER TABLE `news_comment_votes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_imports`
--
ALTER TABLE `news_imports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_import_items`
--
ALTER TABLE `news_import_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_notes`
--
ALTER TABLE `news_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_questions`
--
ALTER TABLE `news_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_reactions`
--
ALTER TABLE `news_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_revisions`
--
ALTER TABLE `news_revisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `news_translations`
--
ALTER TABLE `news_translations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_views`
--
ALTER TABLE `news_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opinion_authors`
--
ALTER TABLE `opinion_authors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings_snapshots`
--
ALTER TABLE `settings_snapshots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slider`
--
ALTER TABLE `slider`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_bookmarks`
--
ALTER TABLE `user_bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET FOREIGN_KEY_CHECKS=1;
