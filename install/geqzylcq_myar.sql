-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- مضيف: localhost:3306
-- وقت الجيل: 02 مارس 2026 الساعة 12:12
-- إصدار الخادم: 10.11.16-MariaDB-cll-lve
-- نسخة PHP: 8.4.17

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

DELIMITER $$
--
-- الإجراءات
--
CREATE PROCEDURE `add_column_if_not_exists` (IN `p_table_name` VARCHAR(128), IN `p_column_name` VARCHAR(128), IN `p_column_definition` VARCHAR(512))   BEGIN
    DECLARE column_count INT;

    -- Check if column exists
    SELECT COUNT(*)
    INTO column_count
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
    AND table_name = p_table_name
    AND column_name = p_column_name;

    -- Add column if it doesn't exist
    IF column_count = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table_name, '` ADD COLUMN `', p_column_name, '` ', p_column_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

CREATE PROCEDURE `add_foreign_key_if_not_exists` (IN `p_table_name` VARCHAR(128), IN `p_constraint_name` VARCHAR(128), IN `p_foreign_key_sql` VARCHAR(512))   BEGIN
    DECLARE fk_count INT;

    -- Check if foreign key exists
    SELECT COUNT(*)
    INTO fk_count
    FROM information_schema.table_constraints
    WHERE constraint_schema = DATABASE()
    AND table_name = p_table_name
    AND constraint_name = p_constraint_name
    AND constraint_type = 'FOREIGN KEY';

    -- Add foreign key if it doesn't exist
    IF fk_count = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table_name, '` ADD CONSTRAINT `', p_constraint_name, '` ', p_foreign_key_sql);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

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

--
-- إرجاع أو استيراد بيانات الجدول `ads`
--

INSERT INTO `ads` (`id`, `title`, `description`, `location`, `image_url`, `target_url`, `is_active`, `is_featured`, `starts_at`, `ends_at`, `max_clicks`, `max_views`, `click_count`, `view_count`, `created_at`, `updated_at`) VALUES
(1, 'ترحيب في الموقع', 'إعلان ترحيبي يظهر لأول مرة في الموقع', 'header_top', 'https://via.placeholder.com/728x90/0f172a/0ea5e9?text=مرحبا+بكم+في+موقعنا', 'https://example.com/welcome', 1, 1, '2026-02-20 18:37:26', '2026-03-22 18:37:26', 1000, 10000, 0, 0, '2026-02-20 20:37:26', '2026-02-20 20:37:26'),
(2, 'عرض خاص', 'عرض محدود لفترة قصيرة', 'sidebar_top', 'https://via.placeholder.com/300x250/1e293b/f59e0b?text=عرض+خاص+لمدة+محدودة', 'https://example.com/special-offer', 1, 0, '2026-02-20 18:37:26', '2026-02-27 18:37:26', 500, 5000, 0, 0, '2026-02-20 20:37:26', '2026-02-20 20:37:26'),
(3, 'إعلان تجريبي', 'إعلان غير نشط للتجربة', 'footer_bottom', 'https://via.placeholder.com/468x60/334155/94a3b8?text=إعلان+تجريبي', 'https://example.com/test', 0, 0, '2026-02-10 18:37:26', '2026-02-25 18:37:26', 100, 1000, 0, 0, '2026-02-20 20:37:26', '2026-02-20 20:37:26'),
(4, 'ترحيب في الموقع', 'إعلان ترحيبي يظهر لأول مرة في الموقع', 'header_top', 'https://via.placeholder.com/728x90/0f172a/0ea5e9?text=مرحبا+بكم+في+موقعنا', 'https://example.com/welcome', 1, 1, '2026-02-23 10:10:15', '2026-03-25 10:10:15', 1000, 10000, 0, 0, '2026-02-23 12:10:15', '2026-02-23 12:10:15'),
(5, 'عرض خاص', 'عرض محدود لفترة قصيرة', 'sidebar_top', 'https://via.placeholder.com/300x250/1e293b/f59e0b?text=عرض+خاص+لمدة+محدودة', 'https://example.com/special-offer', 1, 0, '2026-02-23 10:10:15', '2026-03-02 10:10:15', 500, 5000, 0, 0, '2026-02-23 12:10:15', '2026-02-23 12:10:15'),
(6, 'إعلان تجريبي', 'إعلان غير نشط للتجربة', 'footer_bottom', 'https://via.placeholder.com/468x60/334155/94a3b8?text=إعلان+تجريبي', 'https://example.com/test', 0, 0, '2026-02-13 10:10:15', '2026-02-28 10:10:15', 100, 1000, 0, 0, '2026-02-23 12:10:15', '2026-02-23 12:10:15');

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
(1, 'أخبار عامة', 'general-news', 'ar', NULL, 0, 1, '2026-02-26 20:26:53', '2026-03-01 02:45:13', 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `comments`
-- (See below for the actual view)
--
CREATE TABLE `comments` (
`id` bigint(20) unsigned
,`news_id` int(10) unsigned
,`user_id` int(10) unsigned
,`name` varchar(190)
,`email` varchar(190)
,`body` text
,`parent_id` bigint(20) unsigned
,`status` varchar(20)
,`score` int(11)
,`ip` varchar(45)
,`user_agent` varchar(255)
,`created_at` datetime
,`updated_at` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `comment_votes`
-- (See below for the actual view)
--
CREATE TABLE `comment_votes` (
`id` bigint(20) unsigned
,`comment_id` bigint(20) unsigned
,`user_id` int(10) unsigned
,`ip` varchar(45)
,`value` tinyint(4)
,`created_at` datetime
);

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
(1, 1, NULL, 'بالصور.. سامسونج تطلق سلسلة جوالات جالاكسي إس 26', 'بالصور-سامسونج-تطلق-سلسلة-جوالات-جالاكسي-إس-26', '', 'كشفت \"سامسونج\" اليوم الأربعاء عن سلسلة جوالاتها الجديدة \"جالاكسي إس 26\"، مع تركيز واضح على مزايا الذكاء الاصطناعي وتعزيز الخصوصية، بدلاً من إدخال تغييرات جذرية على التصميم الخارجي.\r\nوكشفت الشركة خلال فعالية في سان فرانسيسكو عن السلسلة الجديدة، حيث تضم جوال \"جالاكسي إس 26 ألترا\" بسعر 1300 دولار، و\"جالاكسي إس 26 بلس\" بسعر 1100 دولار، و\"جالاكسي إس 26\" بسعر 900 دولار.\r\n  \r\n\r\nوبينما حافظ طراز \"ألترا\" على سعره مقارنة بالجيل السابق، ارتفع سعر الإصدارين الآخرين بمقدار 100 دولار لكل منهما، في زيادة قد تعود جزئياً إلى أزمة رقائق الذاكرة المستمرة.\r\nورغم بعض التحسينات في التصميم والكاميرا، فإن الإضافات البرمجية المدعومة بالذكاء الاصطناعي استحوذت على النصيب الأكبر من الاهتمام، في خطوة تُعد مخاطرة في ظل تفضيل كثير من المستهلكين تأجيل الترقية إلى حين صدور طرازات تحمل تغييرات ملموسة.\r\n \r\n\r\nوتخلت \"سامسونج\" عن الإطارات الجانبية المصنوعة من التيتانيوم لصالح الألومنيوم، مما أتاح تقليل وزن الجهاز وجعله أنحف، فيما احتفظت الكاميرات بالدقة نفسها البالغة 200 ميجابكسل للمستشعر الرئيسي و50 ميجابكسل لعدسات التقريب، مع فتحة عدسة أوسع.\r\nوأعلنت الشركة عن دمج تقنية الذكاء الاصطناعي من \"بيربليكسيتي\" داخل الجوالات الجديدة، لكن تطبيق \"جيميناي\" يظل حاضراً بقوة باعتباره المساعد الشخصي الافتراضي، مع إمكانية تنفيذ إجراءات تلقائية عبر الأوامر الصوتية مثل طلب سيارة من \"أوبر\".', 'published', 1, 'uploads/news/news_9609d80357840c49f95ba0eb915b5f0f.jpg', 'uploads/news/news_9609d80357840c49f95ba0eb915b5f0f.jpg', 'uploads/news/news_9609d80357840c49f95ba0eb915b5f0f.jpg', 1, 0, '2026-02-26 18:31:00', '2026-02-26 20:31:20', '2026-03-02 01:31:14', NULL, 0, 117, 'بالصور.. سامسونج تطلق سلسلة جوالات جالاكسي إس 26', 'بالصور.. سامسونج تطلق سلسلة جوالات جالاكسي إس 26', 'بالصور, سامسونج, تطلق, سلسلة, جوالات, جالاكسي', NULL, NULL, 1),
(2, 1, NULL, 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية', 'انفجارات-في-أبو-ظبي-والمنامة-والرياض-ودبي-وقطر-تعترض-صواريخ-إيرانية', '', 'أفادت وكالة رويترز للأنباء بأن انفجارا هائلا دوى في العاصمة الإماراتية أبو ظبي وبالقرب من دبي، اليوم السبت، وذكرت وكالة الصحافة الفرنسية أن انفجارات دوت في العاصمة السعودية الرياض، بينما قالت إيران إنها تستهدف قواعد أمريكية في المنطقة.\r\n\r\nوأعلنت وكالة أنباء الإمارات أن البلاد تعترض عددا من الصواريخ الإيرانية، وقالت إن شخصا قُتل إثر سقوط شظايا صاروخية على منطقة سكنية في أبو ظبي.\r\n\r\nوقال الحرس الثوري الإيراني \"استهدفنا قواعد أمريكية في قطر والإمارات ومراكز عسكرية وأمنية في إسرائيل\".\r\n\r\nوأضاف \"الحرس الثوري الإيراني: استهدفنا مقر قيادة الأسطول الأمريكي الخامس في البحرين بالصواريخ والمسيرات\".\r\n\r\nودوت انفجارات في البحرين، وقالت الداخلية البحرينية إن صفارات الإنذار جرى تفعليها في أنحاء البلاد، مطالبة المواطنين والمقيمين بالتوجه إلى أقرب مكان آمن.\r\n\r\nوأظهرت صور سقوط صاروخ في العاصمة البحرينية المنامة، وقالت رويترز إن الدخان تصاعد من منطقة الجفير في البحرين، والتي تضم قاعدة بحرية أمريكية.\r\nوقالت وكالة أنباء البحرين إن مركز الخدمات التابع للأسطول الخامس الأمريكي تعرّض لهجوم صاروخي.\r\n\r\nوأعلن الجيش الأردني \"إسقاط صاروخين باليستيين بنجاح من قبل أنظمة الدفاع الجوي الأردنية\".\r\n\r\nمن جانبها، ذكرت وزارة الدفاع القطرية أنها تصدت بنجاح لعدد من الهجمات التي استهدفت الأراضي القطرية.\r\n\r\nودعت الداخلية القطرية المواطنين والمقيمين للبقاء في منازلهم وتجنب التحرك إلا للضرورة القصوى.\r\n\r\nوأعلنت كل من الكويت والإمارات وقطر إغلاقا مؤقتا لمجالها الجوي.', 'published', 1, 'uploads/news/news_16946e772444b61ae7bf7db5f03e2c24.webp', 'uploads/news/news_16946e772444b61ae7bf7db5f03e2c24.webp', 'uploads/news/news_16946e772444b61ae7bf7db5f03e2c24.webp', 0, 0, '2026-02-28 11:03:00', '2026-02-28 13:03:17', '2026-03-01 02:45:13', NULL, 0, 1, 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواري', 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية', 'انفجارات, أبو, ظبي, والمنامة, والرياض, ودبي, وقطر, تعترض, صواريخ, إيرانية', NULL, NULL, 1),
(3, 1, NULL, 'الدفاع الإماراتية: وفاة شخص من جنسية آسيوية إثر سقوط شظايا على منطقة سكنية في أبوظبي', 'الدفاع-الإماراتية-وفاة-شخص-من-جنسية-آسيوية-إثر-سقوط-شظايا-على-منطقة-سكنية-في-أبوظبي', '', 'أعلنت وكالة أنباء الإمارات (وام) مقتل شخص جرّاء سقوط شظايا صاروخية على منطقة سكنية في العاصمة الإماراتية أبوظبي، في أول حصيلة بشرية مُعلنة على مستوى دول الخليج منذ اندلاع المواجهة العسكرية.\r\nويُسجّل هذا التطوّر منعطفاً خطيراً في الأزمة، إذ تتحوّل التداعيات من أصوات انفجارات واعتراضات جوية إلى خسائر بشرية مباشرة في دول لم تكن طرفاً في المواجهة.\r\n\r\nولم تُوضح الوكالة تفاصيل إضافية حول جنسية الضحية أو الموقع الدقيق للسقوط، فيما تشهد أبوظبي والعواصم الخليجية حالة استنفار قصوى مع استمرار الردّ الإيراني الذي يستهدف القواعد الأمريكية في المنطقة.', 'published', 1, 'uploads/news/news_66c9c141c0cc66c14c3bd0967262d74f.png', 'uploads/news/news_66c9c141c0cc66c14c3bd0967262d74f.png', 'uploads/news/news_66c9c141c0cc66c14c3bd0967262d74f.png', 1, 0, '2026-02-28 11:35:00', '2026-02-28 13:35:10', '2026-03-01 02:45:13', NULL, 0, 1, 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواري', 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية', 'انفجارات, أبو, ظبي, والمنامة, والرياض, ودبي, وقطر, تعترض, صواريخ, إيرانية', NULL, NULL, 1),
(4, 1, NULL, 'ماذا قصفت إسرائيل وأمريكا في إيران حتى الآن؟', 'ماذا-قصفت-إسرائيل-وأمريكا-في-إيران-حتى-الآن', '', 'أكد الإعلام الإيراني وقوع انفجارات في عدة مدن إيرانية، جراء الهجوم الأمريكي الإسرائيلي على البلاد، الذي بدأ منذ صباح اليوم السبت. وسُمِع دوي عدة انفجارات في مختلف جهات العاصمة طهران، حيث بدأ الهجوم بقلب العاصمة في ميدان فلسطين الذي يضم مقر إقامة المرشد علي خامنئي، وشارع وصال بالقرب من مبنى السلطة القضائية، وكذلك جنوب العاصمة شارع باستور الذي يضم المبنى الرئاسي.\r\n\r\nوأعقاب ذلك، أكد الإعلام الإيراني أن الرئيس مسعود بزشكيان ورئيس السلطة القضائية غلام حسين محسني إيجئي في صحة جيدة، كما نقلت رويترز عن مسؤول إيراني أن خامنئي ليس في طهران وأنه نُقل إلى مكان آمن.\r\n\r\nواستمرت الاستهدافات في شمال وشرق العاصمة، حيث تقع مؤسسات الدولة ومواقع عسكرية أو مناطق سكن مسؤولين وقادة كبار، كما شملت ضواحي العاصمة مثل مدينة شهريار غرب محافظة طهران.\r\n\r\nواستهدفت الهجمات مناطق داخلية منها مدينة كرج في محافظة ألبرز، وأصفهان، وقم، ومناطق بغرب البلاد مثل مدينة كنغاور في محافظة كرمانشاه، ومدينة نهاوند في محافظة همدان ومحافظة لُرستان، ومدينتا دزفول وأنديمشك في محافظة خوزستان، وإيلام، ومدينة تبريز في محافظة أذربيجان الشرقية، ومدينة ميناب وجزيرة خارك التابعتين لمحافظة هرمزغان بالمياه الخليجية، ومدينة تشابهار في محافظة سيستان وبلوشستان جنوب شرقي البلاد المطلة على بحر عمان، ومدينة شيراز بمحافظة فارس.\r\nوقال الصحفي عبد القادر فايز وهو متخصص في الدراسات الإيرانية إن \"الضربة مختلفة تماما، عن كل ما جرى سابقا\"، مشيرا إلى أن هناك استهدافا مباشرا لما يُوصف في إيران ببيت المرشد، وهو في وسط العاصمة طهران.\r\n\r\nوأوضح أن هناك استهدافا لرأس هرم السلطة في البلاد، بالمعنى الجغرافي على الأقل، مضيفا: لا نعرف حتى الآن ما إذا كان موجودا في المكان أم لا.\r\nوأشار إلى استهداف المجمع الرئاسي، أو ما يُسمى بالقصر الرئاسي في طهران، الواقع جنوب العاصمة، وهو مجمع ضخم من المباني، لافتا إلى أن المعلومات تشير إلى أن الرئيس بزشكيان لم يكن موجودا في المجمع الرئاسي وقت استهدافه.\r\n\r\nوأضاف أن هناك استهدافا لجزء من مقر الخارجية الإيرانية في جنوب العاصمة طهران، مؤكدا أن ذلك يمثل استهدافا لجسد سياسي هذه المرة في النظام الإيراني، وهو ما لم تُقدم عليه إسرائيل والولايات المتحدة في كل حروبها السابقة مع إيران، على الأقل في حرب الـ12 يوما التي بدأت عسكرية أمنية، وتطورت بجزء بسيط منها إلى الجسد السياسي.\r\n\r\nوقال إن هجوم \"اليوم بدأ بضربة مشتركة، وليس هناك ضربة إسرائيلية مدعومة أمريكيا أو ضربة أمريكية مدعومة إسرائيليا، هي ضربة مشتركة، وهذا مستجد كبير حقيقة\".\r\n\r\nوأشار إلى أن \"الاستهداف للجسد السياسي في إيران يتقاطع بشكل كبير مع كلمة الرئيس الأمريكي قبل قليل، إذ يقول للجسد العسكري ألقوا أسلحتكم، ويقول للجسد السياسي يجب أن تستسلم\"، مضيفا أن رسالته للشعب الإيراني هي أننا بدأنا هذه المعركة، ويجب عليكم أن تكونوا جزءا منها.\r\nكما أوضح أن استهدافا آخر طال الشق العسكري، هيئة الأركان في شرق العاصمة طهران، لافتا إلى أن هناك مواقع عسكرية غير قليلة الأهمية، من بينها الموقع الشهير المعروف بموقع بارتشين، وهو موقع عسكري مهم جدا يقع جنوب العاصمة.\r\n\r\nوأشار إلى أن كل الوزارات تقريبا تقع في وسط العاصمة باتجاه الجنوب، وعلى رأس هذه الوزارات وزارة الخارجية التي تفاوض الأمريكيين الآن، والمقر الدائم لعباس عراقجي وزير الخارجية الإيراني.\r\n\r\nوأضاف أن مطار مهرآباد، وهو مطار داخلي الآن وكان مطارا دوليا قبل أن تشيّد إيران مطار الخميني، موضحا أن هذا المطار قريب جدا من أحد المواقع العسكرية المهمة التي يُعتقد أنها تشكّل عصبا في عملية الدفاع الجوي عن طهران.\r\n\r\nوشدد على وجود معلومات غير مؤكدة -ونضع خطين تحت كلمة غير مؤكدة- دارت في الأسبوع الأخير قبل هذه الحرب، بأن إيران نصبت رادارا جديدا وأجهزة ومعدات عسكرية جديدة في هذه المنطقة، مضيفا: هل نتحدث عن روسيا والصين مثلا؟ هذا وارد، ولكنْ لا نتحدث عن معلومات رسمية.\r\n\r\nوأشار إلى أن هذا المطار لم يُقصف حتى الآن، وما قُصف هو قاعدة عسكرية بالقرب منه فيها مقر أمني، مؤكدا أنه إذا رسمنا خريطة المدن فهناك استهداف للعاصمة طهران في زواياها الأربع، من الغرب الاقتصادي إلى الشرق العسكري، مرورا بالوسط والجنوب حيث التمركز السياسي، وصولا إلى بيت المرشد والمجمّع الرئاسي ومقر الخارجية.\r\n\r\nوأكد أن ما جرى هو استهداف واسع لمواقع داخل طهران، يشمل أهدافا سياسية وعسكرية كلها داخل العاصمة الإيرانية.', 'published', 1, 'uploads/news/news_45afc6505e3db71433dab92c35e732e4.webp', 'uploads/news/news_45afc6505e3db71433dab92c35e732e4.webp', 'uploads/news/news_45afc6505e3db71433dab92c35e732e4.webp', 1, 0, '2026-02-28 11:42:00', '2026-02-28 13:42:26', '2026-03-01 02:45:13', NULL, 0, 1, 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواري', 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية', 'انفجارات, أبو, ظبي, والمنامة, والرياض, ودبي, وقطر, تعترض, صواريخ, إيرانية', NULL, NULL, 1),
(5, 1, NULL, 'Galaxy S26 Ultra مقارنة بـ S25 Ultra .. هل تستحق الترقية؟', 'galaxy-s26-ultra-مقارنة-بـ-s25-ultra-هل-تستحق-الترقية', '', 'أطلقت شركة سامسونغ هاتف Galaxy S26 Ultra الجديد بميزات جديدة، منها تقنية \"شاشة الخصوصية\" المبتكرة، وشحن أسرع بقوة 60 واط، وتحسينات في الكاميرا تُحسن الأداء في الإضاءة المنخفضة.\r\n\r\nويبدو الهاتف متينًا ويمكن استخدامه لسنوات عديدة، ولكن هذا ينطبق أيضًا على سابقة هاتف S25 Ultra. لذا إذا كنت قد اشتريت هذا الهاتف العام الماضي، فلن تجد سببًا وجيهًا لتحديثه هذا العام، بحسب تقرير لموقع \"أندرويد أوثورتي\" المتخصص في أخبار التكنولوجيا، اطلعت عليه \"العربية Business\".\r\nهاتف أفضل لكن ليس بشكل كبير\r\nأتى هاتف S25 Ultra العام الماضي بمجموعة من التحسينات المفيدة وكان هاتفًا جيدًا، لذلك مع التحسينات الجديدة في S26 Ultra فإنه يُعد أفضل.\r\n\r\nوتعتبر ميزة \"شاشة الخصوصية\" على وجه الخصوص إضافة فريدة، إذ أنها تجعل من الصعب رؤية الشاشة من الزاوية الجانبية، مما يجعل من الصعب على الأشخاص من حولك قراءة ما على شاشتك، كما لو أنها حماية شاشة خاصة مدمجة عند الطلب.\r\n\r\nوميزة \"شاشة الخصوصية\" غير متوفرة في الهواتف الأخرى حاليًا، كما أنها غير متاحة في هواتف S26 الأقل سعرًا، فهي حصرية لهاتف S26 Ultra على الأقل في الوقت الحالي.\r\n\r\nإضافةً إلى تعزيز الخصوصية، تستخدم شاشة S26 Ultra لوحة 10 بت، مما يجعل الهاتف قادرًا على عرض ألوان أكثر من هاتف S25 Ultra.\r\n\r\nوفي حين يُعد هذا تحسينًا رائعًا نظريًا، فإن تأثيره لن يكون ملحوظًا لمعظم المستخدمين في معظم الأوقات، ويعتمد وجود أي فرق على المحتوى المعروض على شاشة S26 Ultra في أي لحظة.\r\n\r\nوفي حين أن جميع كاميرات S26 Ultra تأتي بدقة كاميرات S25 Ultra نفسها، ينبغي أن يكون هناك بعض التحسن في أداء التصوير في الإضاءة المنخفضة بفضل الفتحات الأوسع للكاميرا الرئيسية وكاميرا التقريب البصري حتى 10x.\r\n\r\nوبالاقتران مع تحسينات معالجة الصور، قالت \"سامسونغ\" إن الفتحات الأوسع للكاميرا الرئيسية وكاميرا 10x تعني أنه يمكنك توقع صور أكثر سطوعًا بنسبة 47% من الكاميرا الرئيسية و37% أكثر سطوعًا من كاميرا التقريب البصري.\r\n\r\nأما التغيير الأخير الملحوظ مقارنة بالعام الماضي فهو الشحن، إذ يمكن لهاتف S26 Ultra الشحن بقدرة 60 واط، ليصل من صفر إلى 70% في حوالي نصف ساعة باستخدام الشاحن المناسب، بينما كان الحد الأقصى للشحن في هاتف S25 Ultra هو 45 واط.\r\n\r\nويدعم الهاتف الجديد أيضًا الشحن اللاسلكي بقدرة 25 واط، مقارنة ب 15 واط في العام الماضي، لكنه لا يحتوي على المغناطيسات المدمجة اللازمة لاستخدام ملحقات Qi2 المغناطيسية.\r\n\r\nهناك تغييرات أخرى أيضًا، بما في ذلك اختلاف التصميم بين الجيلين مع نتوء كاميرا مُعاد تصميمه، وزوايا أكثر استدارة، وهيكل أنحف قليلًا في هاتف S26 Ultra.\r\n\r\nيستخدم الهاتف الجديد معالج \"Qualcomm Snapdragon 8 Elite Gen 5 \" المخصص لهواتف غالاكسي، والذي تقول \"سامسونغ\" إنه يحسن أداء وحدة المعالجة المركزية بنسبة 19% تقريبًا، وأداء وحدة معالجة الرسومات بنسبة 24%. ويبدو أن المعالج الجديد أفضل بكثير في مهام الذكاء الاصطناعي المدمجة في الجهاز.\r\n\r\nوبالحديث عن الذكاء الاصطناعي، يأتي S26 Ultra مزودًا بجيميناي وGalaxy AI وبيربلكسيتي، هناك مجموعة من الميزات المدعومة بالذكاء الاصطناعي، مثل تحرير الصور بناءً على نصوص، وتصنيف لقطات الشاشة بالذكاء الاصطناعي، وملصقات وخلفيات مولدة بالذكاء الاصطناعي.\r\n\r\nومن بين الإضافات الأكثر نفعًا، يمكن لميزة \"Audio Eraser\" من سامسونغ الآن محاولة عزل الأصوات ورفع مستواها في محتوى الفيديو عبر التطبيقات المختلفة.\r\n\r\nهل يستحق S26 Ultra الترقية؟\r\nهناك بالتأكيد بعض التغييرات في S26 Ultra التي تلفت الانتباه، بما في ذلك تحسين التصوير في الإضاءة المنخفضة، وشاشة الخصوصية التي تُعد ابتكارًا حقيقيًا، والشحن الأسرع.\r\n\r\nلكن سيكون من غير المجدي الترقية من هاتف Galaxy S25 Ultra إلى Galaxy S26 Ultra الجديد، لأن الهاتف الجديد يكاد يكون نفس القديم تمامًا.\r\n\r\nويشترك S26 Ultra مع S25 Ultra في مستشعرات الكاميرا؛ وشاشة بحجم 6.9 بوصة ودقة 1440 بكسل ومعدل تحديث 120 هرتز؛ و12 غيغابايت من ذاكرة الوصول العشوائي؛ وبطارية بسعة 5,000 مللي أمبير/ساعة.\r\n\r\nوهناك تحسينات هامشية بالطبع هذا العام، لكن طراز العام الماضي كان ممتازًا ومرتفع الثمن، ومع وعد سامسونغ بتحديثات نظام تشغيل لمدة سبع سنوات، سيظل خيارًا قابلًا للاستخدام لسنوات قادمة.\r\n\r\nلقد تباطأت التحسينات من سنة إلى أخرى بشكل كبير منذ بداية العقد؛ ولم يكن من السهل تبرير ترقيات الهواتف الذكية السنوية للمستخدمين العاديين منذ فترة طويلة. وحتى بالنسبة للهواة والمتحمسين، يقدم S25 Ultra تجربة تكاد تكون متطابقة مع S26 Ultra الجديد.', 'published', 1, 'uploads/news/news_2cc529c3c3feb0d3c93d7cae9710f32b.webp', 'uploads/news/news_2cc529c3c3feb0d3c93d7cae9710f32b.webp', 'uploads/news/news_2cc529c3c3feb0d3c93d7cae9710f32b.webp', 0, 0, '2026-02-28 11:48:00', '2026-02-28 13:48:17', '2026-03-01 02:45:13', NULL, 0, 1, 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواري', 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية', 'انفجارات, أبو, ظبي, والمنامة, والرياض, ودبي, وقطر, تعترض, صواريخ, إيرانية', NULL, NULL, 1);

--
-- القوادح `news`
--
DELIMITER $$
CREATE TRIGGER `trg_news_bi` BEFORE INSERT ON `news` FOR EACH ROW BEGIN
  SET NEW.is_published =
    CASE
      WHEN NEW.status = 'published' AND NEW.deleted_at IS NULL THEN 1
      ELSE 0
    END;
END
$$
DELIMITER ;
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
(1, 1, 1, 'update', '{\"news\":{\"id\":1,\"category_id\":1,\"opinion_author_id\":null,\"title\":\"بالصور.. سامسونج تطلق سلسلة جوالات جالاكسي إس 26\",\"slug\":\"بالصور-سامسونج-تطلق-سلسلة-جوالات-جالاكسي-إس-26\",\"excerpt\":\"\",\"content\":\"كشفت \\u0022سامسونج\\u0022 اليوم الأربعاء عن سلسلة جوالاتها الجديدة \\u0022جالاكسي إس 26\\u0022، مع تركيز واضح على مزايا الذكاء الاصطناعي وتعزيز الخصوصية، بدلاً من إدخال تغييرات جذرية على التصميم الخارجي.\\r\\nوكشفت الشركة خلال فعالية في سان فرانسيسكو عن السلسلة الجديدة، حيث تضم جوال \\u0022جالاكسي إس 26 ألترا\\u0022 بسعر 1300 دولار، و\\u0022جالاكسي إس 26 بلس\\u0022 بسعر 1100 دولار، و\\u0022جالاكسي إس 26\\u0022 بسعر 900 دولار.\\r\\n  \\r\\n\\r\\nوبينما حافظ طراز \\u0022ألترا\\u0022 على سعره مقارنة بالجيل السابق، ارتفع سعر الإصدارين الآخرين بمقدار 100 دولار لكل منهما، في زيادة قد تعود جزئياً إلى أزمة رقائق الذاكرة المستمرة.\\r\\nورغم بعض التحسينات في التصميم والكاميرا، فإن الإضافات البرمجية المدعومة بالذكاء الاصطناعي استحوذت على النصيب الأكبر من الاهتمام، في خطوة تُعد مخاطرة في ظل تفضيل كثير من المستهلكين تأجيل الترقية إلى حين صدور طرازات تحمل تغييرات ملموسة.\\r\\n \\r\\n\\r\\nوتخلت \\u0022سامسونج\\u0022 عن الإطارات الجانبية المصنوعة من التيتانيوم لصالح الألومنيوم، مما أتاح تقليل وزن الجهاز وجعله أنحف، فيما احتفظت الكاميرات بالدقة نفسها البالغة 200 ميجابكسل للمستشعر الرئيسي و50 ميجابكسل لعدسات التقريب، مع فتحة عدسة أوسع.\\r\\nوأعلنت الشركة عن دمج تقنية الذكاء الاصطناعي من \\u0022بيربليكسيتي\\u0022 داخل الجوالات الجديدة، لكن تطبيق \\u0022جيميناي\\u0022 يظل حاضراً بقوة باعتباره المساعد الشخصي الافتراضي، مع إمكانية تنفيذ إجراءات تلقائية عبر الأوامر الصوتية مثل طلب سيارة من \\u0022أوبر\\u0022.\",\"status\":\"published\",\"featured_image\":\"uploads/news/news_9609d80357840c49f95ba0eb915b5f0f.jpg\",\"image_path\":\"uploads/news/news_9609d80357840c49f95ba0eb915b5f0f.jpg\",\"image\":\"uploads/news/news_9609d80357840c49f95ba0eb915b5f0f.jpg\",\"is_breaking\":1,\"view_count\":0,\"published_at\":\"2026-02-26 18:31:20\",\"created_at\":\"2026-02-26 20:31:20\",\"updated_at\":null,\"deleted_at\":null,\"is_members_only\":0,\"views\":0,\"seo_title\":\"بالصور.. سامسونج تطلق سلسلة جوالات جالاكسي إس 26\",\"seo_description\":\"بالصور.. سامسونج تطلق سلسلة جوالات جالاكسي إس 26\",\"seo_keywords\":\"بالصور, سامسونج, تطلق, سلسلة, جوالات, جالاكسي\",\"publish_at\":null,\"unpublish_at\":null,\"author_id\":1},\"tags\":\"بالصور، تطلق، جالاكسي، جوالات، سامسونج، سلسلة\"}', '2026-02-26 20:31:34'),
(2, 2, 1, 'update', '{\"news\":{\"id\":2,\"category_id\":1,\"opinion_author_id\":null,\"title\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية\",\"slug\":\"انفجارات-في-أبو-ظبي-والمنامة-والرياض-ودبي-وقطر-تعترض-صواريخ-إيرانية\",\"excerpt\":\"\",\"content\":\"أفادت وكالة رويترز للأنباء بأن انفجارا هائلا دوى في العاصمة الإماراتية أبو ظبي وبالقرب من دبي، اليوم السبت، وذكرت وكالة الصحافة الفرنسية أن انفجارات دوت في العاصمة السعودية الرياض، بينما قالت إيران إنها تستهدف قواعد أمريكية في المنطقة.\\r\\n\\r\\nوأعلنت وكالة أنباء الإمارات أن البلاد تعترض عددا من الصواريخ الإيرانية، وقالت إن شخصا قُتل إثر سقوط شظايا صاروخية على منطقة سكنية في أبو ظبي.\\r\\n\\r\\nوقال الحرس الثوري الإيراني \\u0022استهدفنا قواعد أمريكية في قطر والإمارات ومراكز عسكرية وأمنية في إسرائيل\\u0022.\\r\\n\\r\\nوأضاف \\u0022الحرس الثوري الإيراني: استهدفنا مقر قيادة الأسطول الأمريكي الخامس في البحرين بالصواريخ والمسيرات\\u0022.\\r\\n\\r\\nودوت انفجارات في البحرين، وقالت الداخلية البحرينية إن صفارات الإنذار جرى تفعليها في أنحاء البلاد، مطالبة المواطنين والمقيمين بالتوجه إلى أقرب مكان آمن.\\r\\n\\r\\nوأظهرت صور سقوط صاروخ في العاصمة البحرينية المنامة، وقالت رويترز إن الدخان تصاعد من منطقة الجفير في البحرين، والتي تضم قاعدة بحرية أمريكية.\\r\\nوقالت وكالة أنباء البحرين إن مركز الخدمات التابع للأسطول الخامس الأمريكي تعرّض لهجوم صاروخي.\\r\\n\\r\\nوأعلن الجيش الأردني \\u0022إسقاط صاروخين باليستيين بنجاح من قبل أنظمة الدفاع الجوي الأردنية\\u0022.\\r\\n\\r\\nمن جانبها، ذكرت وزارة الدفاع القطرية أنها تصدت بنجاح لعدد من الهجمات التي استهدفت الأراضي القطرية.\\r\\n\\r\\nودعت الداخلية القطرية المواطنين والمقيمين للبقاء في منازلهم وتجنب التحرك إلا للضرورة القصوى.\\r\\n\\r\\nوأعلنت كل من الكويت والإمارات وقطر إغلاقا مؤقتا لمجالها الجوي.\",\"status\":\"published\",\"featured_image\":\"uploads/news/news_16946e772444b61ae7bf7db5f03e2c24.webp\",\"image_path\":\"uploads/news/news_16946e772444b61ae7bf7db5f03e2c24.webp\",\"image\":\"uploads/news/news_16946e772444b61ae7bf7db5f03e2c24.webp\",\"is_breaking\":0,\"view_count\":0,\"published_at\":\"2026-02-28 11:03:17\",\"created_at\":\"2026-02-28 13:03:17\",\"updated_at\":null,\"deleted_at\":null,\"is_members_only\":0,\"views\":0,\"seo_title\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواري\",\"seo_description\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية\",\"seo_keywords\":\"انفجارات, أبو, ظبي, والمنامة, والرياض, ودبي, وقطر, تعترض, صواريخ, إيرانية\",\"publish_at\":null,\"unpublish_at\":null,\"author_id\":1},\"tags\":\"انفجارات، تعترض، والرياض، والمنامة، ودبي، وقطر\"}', '2026-02-28 13:03:24'),
(3, 3, 1, 'update', '{\"news\":{\"id\":3,\"category_id\":1,\"opinion_author_id\":null,\"title\":\"الدفاع الإماراتية: وفاة شخص من جنسية آسيوية إثر سقوط شظايا على منطقة سكنية في أبوظبي\",\"slug\":\"الدفاع-الإماراتية-وفاة-شخص-من-جنسية-آسيوية-إثر-سقوط-شظايا-على-منطقة-سكنية-في-أبوظبي\",\"excerpt\":\"\",\"content\":\"أعلنت وكالة أنباء الإمارات (وام) مقتل شخص جرّاء سقوط شظايا صاروخية على منطقة سكنية في العاصمة الإماراتية أبوظبي، في أول حصيلة بشرية مُعلنة على مستوى دول الخليج منذ اندلاع المواجهة العسكرية.\\r\\nويُسجّل هذا التطوّر منعطفاً خطيراً في الأزمة، إذ تتحوّل التداعيات من أصوات انفجارات واعتراضات جوية إلى خسائر بشرية مباشرة في دول لم تكن طرفاً في المواجهة.\\r\\n\\r\\nولم تُوضح الوكالة تفاصيل إضافية حول جنسية الضحية أو الموقع الدقيق للسقوط، فيما تشهد أبوظبي والعواصم الخليجية حالة استنفار قصوى مع استمرار الردّ الإيراني الذي يستهدف القواعد الأمريكية في المنطقة.\",\"status\":\"published\",\"featured_image\":\"uploads/news/news_66c9c141c0cc66c14c3bd0967262d74f.png\",\"image_path\":\"uploads/news/news_66c9c141c0cc66c14c3bd0967262d74f.png\",\"image\":\"uploads/news/news_66c9c141c0cc66c14c3bd0967262d74f.png\",\"is_breaking\":1,\"view_count\":0,\"published_at\":\"2026-02-28 11:35:10\",\"created_at\":\"2026-02-28 13:35:10\",\"updated_at\":null,\"deleted_at\":null,\"is_members_only\":0,\"views\":0,\"seo_title\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواري\",\"seo_description\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية\",\"seo_keywords\":\"انفجارات, أبو, ظبي, والمنامة, والرياض, ودبي, وقطر, تعترض, صواريخ, إيرانية\",\"publish_at\":null,\"unpublish_at\":null,\"author_id\":1},\"tags\":\"أبو، انفجارات، ظبي، والرياض، والمنامة، ودبي\"}', '2026-02-28 13:35:17'),
(4, 4, 1, 'update', '{\"news\":{\"id\":4,\"category_id\":1,\"opinion_author_id\":null,\"title\":\"ماذا قصفت إسرائيل وأمريكا في إيران حتى الآن؟\",\"slug\":\"ماذا-قصفت-إسرائيل-وأمريكا-في-إيران-حتى-الآن\",\"excerpt\":\"\",\"content\":\"أكد الإعلام الإيراني وقوع انفجارات في عدة مدن إيرانية، جراء الهجوم الأمريكي الإسرائيلي على البلاد، الذي بدأ منذ صباح اليوم السبت. وسُمِع دوي عدة انفجارات في مختلف جهات العاصمة طهران، حيث بدأ الهجوم بقلب العاصمة في ميدان فلسطين الذي يضم مقر إقامة المرشد علي خامنئي، وشارع وصال بالقرب من مبنى السلطة القضائية، وكذلك جنوب العاصمة شارع باستور الذي يضم المبنى الرئاسي.\\r\\n\\r\\nوأعقاب ذلك، أكد الإعلام الإيراني أن الرئيس مسعود بزشكيان ورئيس السلطة القضائية غلام حسين محسني إيجئي في صحة جيدة، كما نقلت رويترز عن مسؤول إيراني أن خامنئي ليس في طهران وأنه نُقل إلى مكان آمن.\\r\\n\\r\\nواستمرت الاستهدافات في شمال وشرق العاصمة، حيث تقع مؤسسات الدولة ومواقع عسكرية أو مناطق سكن مسؤولين وقادة كبار، كما شملت ضواحي العاصمة مثل مدينة شهريار غرب محافظة طهران.\\r\\n\\r\\nواستهدفت الهجمات مناطق داخلية منها مدينة كرج في محافظة ألبرز، وأصفهان، وقم، ومناطق بغرب البلاد مثل مدينة كنغاور في محافظة كرمانشاه، ومدينة نهاوند في محافظة همدان ومحافظة لُرستان، ومدينتا دزفول وأنديمشك في محافظة خوزستان، وإيلام، ومدينة تبريز في محافظة أذربيجان الشرقية، ومدينة ميناب وجزيرة خارك التابعتين لمحافظة هرمزغان بالمياه الخليجية، ومدينة تشابهار في محافظة سيستان وبلوشستان جنوب شرقي البلاد المطلة على بحر عمان، ومدينة شيراز بمحافظة فارس.\\r\\nوقال الصحفي عبد القادر فايز وهو متخصص في الدراسات الإيرانية إن \\u0022الضربة مختلفة تماما، عن كل ما جرى سابقا\\u0022، مشيرا إلى أن هناك استهدافا مباشرا لما يُوصف في إيران ببيت المرشد، وهو في وسط العاصمة طهران.\\r\\n\\r\\nوأوضح أن هناك استهدافا لرأس هرم السلطة في البلاد، بالمعنى الجغرافي على الأقل، مضيفا: لا نعرف حتى الآن ما إذا كان موجودا في المكان أم لا.\\r\\nوأشار إلى استهداف المجمع الرئاسي، أو ما يُسمى بالقصر الرئاسي في طهران، الواقع جنوب العاصمة، وهو مجمع ضخم من المباني، لافتا إلى أن المعلومات تشير إلى أن الرئيس بزشكيان لم يكن موجودا في المجمع الرئاسي وقت استهدافه.\\r\\n\\r\\nوأضاف أن هناك استهدافا لجزء من مقر الخارجية الإيرانية في جنوب العاصمة طهران، مؤكدا أن ذلك يمثل استهدافا لجسد سياسي هذه المرة في النظام الإيراني، وهو ما لم تُقدم عليه إسرائيل والولايات المتحدة في كل حروبها السابقة مع إيران، على الأقل في حرب الـ12 يوما التي بدأت عسكرية أمنية، وتطورت بجزء بسيط منها إلى الجسد السياسي.\\r\\n\\r\\nوقال إن هجوم \\u0022اليوم بدأ بضربة مشتركة، وليس هناك ضربة إسرائيلية مدعومة أمريكيا أو ضربة أمريكية مدعومة إسرائيليا، هي ضربة مشتركة، وهذا مستجد كبير حقيقة\\u0022.\\r\\n\\r\\nوأشار إلى أن \\u0022الاستهداف للجسد السياسي في إيران يتقاطع بشكل كبير مع كلمة الرئيس الأمريكي قبل قليل، إذ يقول للجسد العسكري ألقوا أسلحتكم، ويقول للجسد السياسي يجب أن تستسلم\\u0022، مضيفا أن رسالته للشعب الإيراني هي أننا بدأنا هذه المعركة، ويجب عليكم أن تكونوا جزءا منها.\\r\\nكما أوضح أن استهدافا آخر طال الشق العسكري، هيئة الأركان في شرق العاصمة طهران، لافتا إلى أن هناك مواقع عسكرية غير قليلة الأهمية، من بينها الموقع الشهير المعروف بموقع بارتشين، وهو موقع عسكري مهم جدا يقع جنوب العاصمة.\\r\\n\\r\\nوأشار إلى أن كل الوزارات تقريبا تقع في وسط العاصمة باتجاه الجنوب، وعلى رأس هذه الوزارات وزارة الخارجية التي تفاوض الأمريكيين الآن، والمقر الدائم لعباس عراقجي وزير الخارجية الإيراني.\\r\\n\\r\\nوأضاف أن مطار مهرآباد، وهو مطار داخلي الآن وكان مطارا دوليا قبل أن تشيّد إيران مطار الخميني، موضحا أن هذا المطار قريب جدا من أحد المواقع العسكرية المهمة التي يُعتقد أنها تشكّل عصبا في عملية الدفاع الجوي عن طهران.\\r\\n\\r\\nوشدد على وجود معلومات غير مؤكدة -ونضع خطين تحت كلمة غير مؤكدة- دارت في الأسبوع الأخير قبل هذه الحرب، بأن إيران نصبت رادارا جديدا وأجهزة ومعدات عسكرية جديدة في هذه المنطقة، مضيفا: هل نتحدث عن روسيا والصين مثلا؟ هذا وارد، ولكنْ لا نتحدث عن معلومات رسمية.\\r\\n\\r\\nوأشار إلى أن هذا المطار لم يُقصف حتى الآن، وما قُصف هو قاعدة عسكرية بالقرب منه فيها مقر أمني، مؤكدا أنه إذا رسمنا خريطة المدن فهناك استهداف للعاصمة طهران في زواياها الأربع، من الغرب الاقتصادي إلى الشرق العسكري، مرورا بالوسط والجنوب حيث التمركز السياسي، وصولا إلى بيت المرشد والمجمّع الرئاسي ومقر الخارجية.\\r\\n\\r\\nوأكد أن ما جرى هو استهداف واسع لمواقع داخل طهران، يشمل أهدافا سياسية وعسكرية كلها داخل العاصمة الإيرانية.\",\"status\":\"published\",\"featured_image\":\"uploads/news/news_45afc6505e3db71433dab92c35e732e4.webp\",\"image_path\":\"uploads/news/news_45afc6505e3db71433dab92c35e732e4.webp\",\"image\":\"uploads/news/news_45afc6505e3db71433dab92c35e732e4.webp\",\"is_breaking\":0,\"view_count\":0,\"published_at\":\"2026-02-28 11:42:26\",\"created_at\":\"2026-02-28 13:42:26\",\"updated_at\":null,\"deleted_at\":null,\"is_members_only\":0,\"views\":0,\"seo_title\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواري\",\"seo_description\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية\",\"seo_keywords\":\"انفجارات, أبو, ظبي, والمنامة, والرياض, ودبي, وقطر, تعترض, صواريخ, إيرانية\",\"publish_at\":null,\"unpublish_at\":null,\"author_id\":1},\"tags\":\"إسرائيل، إيران، الآن؟، قصفت، ماذا، وأمريكا\"}', '2026-02-28 13:42:39'),
(5, 5, 1, 'update', '{\"news\":{\"id\":5,\"category_id\":1,\"opinion_author_id\":null,\"title\":\"Galaxy S26 Ultra مقارنة بـ S25 Ultra .. هل تستحق الترقية؟\",\"slug\":\"galaxy-s26-ultra-مقارنة-بـ-s25-ultra-هل-تستحق-الترقية\",\"excerpt\":\"\",\"content\":\"أطلقت شركة سامسونغ هاتف Galaxy S26 Ultra الجديد بميزات جديدة، منها تقنية \\u0022شاشة الخصوصية\\u0022 المبتكرة، وشحن أسرع بقوة 60 واط، وتحسينات في الكاميرا تُحسن الأداء في الإضاءة المنخفضة.\\r\\n\\r\\nويبدو الهاتف متينًا ويمكن استخدامه لسنوات عديدة، ولكن هذا ينطبق أيضًا على سابقة هاتف S25 Ultra. لذا إذا كنت قد اشتريت هذا الهاتف العام الماضي، فلن تجد سببًا وجيهًا لتحديثه هذا العام، بحسب تقرير لموقع \\u0022أندرويد أوثورتي\\u0022 المتخصص في أخبار التكنولوجيا، اطلعت عليه \\u0022العربية Business\\u0022.\\r\\nهاتف أفضل لكن ليس بشكل كبير\\r\\nأتى هاتف S25 Ultra العام الماضي بمجموعة من التحسينات المفيدة وكان هاتفًا جيدًا، لذلك مع التحسينات الجديدة في S26 Ultra فإنه يُعد أفضل.\\r\\n\\r\\nوتعتبر ميزة \\u0022شاشة الخصوصية\\u0022 على وجه الخصوص إضافة فريدة، إذ أنها تجعل من الصعب رؤية الشاشة من الزاوية الجانبية، مما يجعل من الصعب على الأشخاص من حولك قراءة ما على شاشتك، كما لو أنها حماية شاشة خاصة مدمجة عند الطلب.\\r\\n\\r\\nوميزة \\u0022شاشة الخصوصية\\u0022 غير متوفرة في الهواتف الأخرى حاليًا، كما أنها غير متاحة في هواتف S26 الأقل سعرًا، فهي حصرية لهاتف S26 Ultra على الأقل في الوقت الحالي.\\r\\n\\r\\nإضافةً إلى تعزيز الخصوصية، تستخدم شاشة S26 Ultra لوحة 10 بت، مما يجعل الهاتف قادرًا على عرض ألوان أكثر من هاتف S25 Ultra.\\r\\n\\r\\nوفي حين يُعد هذا تحسينًا رائعًا نظريًا، فإن تأثيره لن يكون ملحوظًا لمعظم المستخدمين في معظم الأوقات، ويعتمد وجود أي فرق على المحتوى المعروض على شاشة S26 Ultra في أي لحظة.\\r\\n\\r\\nوفي حين أن جميع كاميرات S26 Ultra تأتي بدقة كاميرات S25 Ultra نفسها، ينبغي أن يكون هناك بعض التحسن في أداء التصوير في الإضاءة المنخفضة بفضل الفتحات الأوسع للكاميرا الرئيسية وكاميرا التقريب البصري حتى 10x.\\r\\n\\r\\nوبالاقتران مع تحسينات معالجة الصور، قالت \\u0022سامسونغ\\u0022 إن الفتحات الأوسع للكاميرا الرئيسية وكاميرا 10x تعني أنه يمكنك توقع صور أكثر سطوعًا بنسبة 47% من الكاميرا الرئيسية و37% أكثر سطوعًا من كاميرا التقريب البصري.\\r\\n\\r\\nأما التغيير الأخير الملحوظ مقارنة بالعام الماضي فهو الشحن، إذ يمكن لهاتف S26 Ultra الشحن بقدرة 60 واط، ليصل من صفر إلى 70% في حوالي نصف ساعة باستخدام الشاحن المناسب، بينما كان الحد الأقصى للشحن في هاتف S25 Ultra هو 45 واط.\\r\\n\\r\\nويدعم الهاتف الجديد أيضًا الشحن اللاسلكي بقدرة 25 واط، مقارنة ب 15 واط في العام الماضي، لكنه لا يحتوي على المغناطيسات المدمجة اللازمة لاستخدام ملحقات Qi2 المغناطيسية.\\r\\n\\r\\nهناك تغييرات أخرى أيضًا، بما في ذلك اختلاف التصميم بين الجيلين مع نتوء كاميرا مُعاد تصميمه، وزوايا أكثر استدارة، وهيكل أنحف قليلًا في هاتف S26 Ultra.\\r\\n\\r\\nيستخدم الهاتف الجديد معالج \\u0022Qualcomm Snapdragon 8 Elite Gen 5 \\u0022 المخصص لهواتف غالاكسي، والذي تقول \\u0022سامسونغ\\u0022 إنه يحسن أداء وحدة المعالجة المركزية بنسبة 19% تقريبًا، وأداء وحدة معالجة الرسومات بنسبة 24%. ويبدو أن المعالج الجديد أفضل بكثير في مهام الذكاء الاصطناعي المدمجة في الجهاز.\\r\\n\\r\\nوبالحديث عن الذكاء الاصطناعي، يأتي S26 Ultra مزودًا بجيميناي وGalaxy AI وبيربلكسيتي، هناك مجموعة من الميزات المدعومة بالذكاء الاصطناعي، مثل تحرير الصور بناءً على نصوص، وتصنيف لقطات الشاشة بالذكاء الاصطناعي، وملصقات وخلفيات مولدة بالذكاء الاصطناعي.\\r\\n\\r\\nومن بين الإضافات الأكثر نفعًا، يمكن لميزة \\u0022Audio Eraser\\u0022 من سامسونغ الآن محاولة عزل الأصوات ورفع مستواها في محتوى الفيديو عبر التطبيقات المختلفة.\\r\\n\\r\\nهل يستحق S26 Ultra الترقية؟\\r\\nهناك بالتأكيد بعض التغييرات في S26 Ultra التي تلفت الانتباه، بما في ذلك تحسين التصوير في الإضاءة المنخفضة، وشاشة الخصوصية التي تُعد ابتكارًا حقيقيًا، والشحن الأسرع.\\r\\n\\r\\nلكن سيكون من غير المجدي الترقية من هاتف Galaxy S25 Ultra إلى Galaxy S26 Ultra الجديد، لأن الهاتف الجديد يكاد يكون نفس القديم تمامًا.\\r\\n\\r\\nويشترك S26 Ultra مع S25 Ultra في مستشعرات الكاميرا؛ وشاشة بحجم 6.9 بوصة ودقة 1440 بكسل ومعدل تحديث 120 هرتز؛ و12 غيغابايت من ذاكرة الوصول العشوائي؛ وبطارية بسعة 5,000 مللي أمبير/ساعة.\\r\\n\\r\\nوهناك تحسينات هامشية بالطبع هذا العام، لكن طراز العام الماضي كان ممتازًا ومرتفع الثمن، ومع وعد سامسونغ بتحديثات نظام تشغيل لمدة سبع سنوات، سيظل خيارًا قابلًا للاستخدام لسنوات قادمة.\\r\\n\\r\\nلقد تباطأت التحسينات من سنة إلى أخرى بشكل كبير منذ بداية العقد؛ ولم يكن من السهل تبرير ترقيات الهواتف الذكية السنوية للمستخدمين العاديين منذ فترة طويلة. وحتى بالنسبة للهواة والمتحمسين، يقدم S25 Ultra تجربة تكاد تكون متطابقة مع S26 Ultra الجديد.\",\"status\":\"published\",\"featured_image\":\"uploads/news/news_2cc529c3c3feb0d3c93d7cae9710f32b.webp\",\"image_path\":\"uploads/news/news_2cc529c3c3feb0d3c93d7cae9710f32b.webp\",\"image\":\"uploads/news/news_2cc529c3c3feb0d3c93d7cae9710f32b.webp\",\"is_breaking\":0,\"view_count\":0,\"published_at\":\"2026-02-28 11:48:17\",\"created_at\":\"2026-02-28 13:48:17\",\"updated_at\":null,\"deleted_at\":null,\"is_members_only\":0,\"views\":0,\"seo_title\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواري\",\"seo_description\":\"انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية\",\"seo_keywords\":\"انفجارات, أبو, ظبي, والمنامة, والرياض, ودبي, وقطر, تعترض, صواريخ, إيرانية\",\"publish_at\":null,\"unpublish_at\":null,\"author_id\":1},\"tags\":\"Galaxy، Ultra، الترقية؟، تستحق، مقارنة\"}', '2026-02-28 13:48:21');

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
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 13),
(3, 14),
(4, 15),
(4, 16),
(4, 17),
(4, 18),
(4, 19),
(4, 20),
(5, 21),
(5, 22),
(5, 23),
(5, 24),
(5, 25);

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

--
-- إرجاع أو استيراد بيانات الجدول `permissions`
--

INSERT INTO `permissions` (`id`, `code`, `label`, `description`, `created_at`, `slug`) VALUES
(1, '*', 'صلاحيات كاملة', 'جميع الصلاحيات', '2026-02-17 08:07:04', NULL),
(2, 'manage_users', 'إدارة المستخدمين', NULL, '2026-02-17 08:07:04', NULL),
(3, 'manage_roles', 'إدارة الأدوار والصلاحيات', NULL, '2026-02-17 08:07:04', NULL),
(4, 'manage_security', 'إعدادات الأمان', NULL, '2026-02-17 08:07:04', NULL),
(5, 'manage_plugins', 'إدارة الإضافات', NULL, '2026-02-17 08:07:04', NULL),
(6, 'posts.*', 'إدارة الأخبار', NULL, '2026-02-17 08:07:04', NULL);

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

--
-- إرجاع أو استيراد بيانات الجدول `roles`
--

INSERT INTO `roles` (`id`, `name`, `label`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'مدير', 'صلاحيات كاملة', 1, '2026-02-17 08:07:04', '2026-02-17 08:07:04'),
(2, 'writer', 'كاتب', 'كتابة وتعديل أخبار', 1, '2026-02-17 08:07:04', NULL),
(3, 'user', 'مستخدم', 'حساب مستخدم عادي', 1, '2026-02-17 08:07:04', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1);

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

--
-- إرجاع أو استيراد بيانات الجدول `schema_migrations`
--

INSERT INTO `schema_migrations` (`id`, `name`, `checksum`, `applied_at`) VALUES
(1, '2025_11_12_add_missing_tables_and_columns.sql', '4d7586b401b239a18c85cf89419ee51e8572f5a29a08014036886b06282e04fb', '2026-02-17 06:07:04'),
(2, '2025_11_21_0000_create_pages.sql', '4f0f542c7e128c14582a0c556d124f6476dd851d06d9e64369254c4e63e67e95', '2026-02-17 06:07:04'),
(3, '2025_11_21_0001_create_user_bookmarks.sql', 'ccdf44aaa03d7d521b4bf9f4b39f98aefc547d80918c1f6932037494ddf7bb04', '2026-02-17 06:07:04'),
(4, '2025_11_21_0002_alter_opinion_optional.sql', 'f78041ec0c25be41f913869eac9be350ddafc21cad7806a716422e4859c35f48', '2026-02-17 06:07:04'),
(5, '2025_11_21_0004_create_password_resets.sql', 'fb66dd71a8bbefff82531e6d4665f022ac3ef9184f18c7d420fab84c897acabe', '2026-02-17 06:07:04'),
(6, '2025_11_21_0005_create_news_reactions.sql', '27bdaa56d052f9121fa27066e075762d91d53269ff81adb14a1d972194336967', '2026-02-17 06:07:04'),
(7, '2025_11_21_0007_create_settings_snapshots.sql', '07c50f5fa440ee87ea1589d6cebc7797416083301701316afe73d07bf15032c1', '2026-02-17 06:07:04'),
(8, '2025_11_21_0008_seed_default_pages.sql', '01ba4719c80b6fe911b091a7c05124b64eeece964e09c058ef8f9805daca546b', '2026-02-17 06:07:04'),
(9, '2025_11_21_0009_create_contact_messages.sql', 'a4d95ce431c4b1fcc4aefa6752c8bfed96e25790ff1e6bf65ef06458351f47af', '2026-02-17 06:07:04'),
(10, '2025_12_13_add_writer_role.sql', '6c754d181194c894d788384fd889b3bc98927b1eb9dcb304d06d84389391da4f', '2026-02-17 06:07:04'),
(11, '2025_12_13_news_pro_columns.sql', '05fab87002b567e6d12f2c2c44219364bc19a5c8a049762bdd40f4988c72356a', '2026-02-17 06:07:04'),
(12, '2025_12_24_0000_create_news_imports.sql', 'be93f4bb6c7d218fca6ca1071ee04096d5006a9841ba58fdf7a42b52417288cc', '2026-02-17 06:07:04'),
(13, '2025_12_25_120000_news_workflow.sql', '7ec4cb6174ba2e697e8d9891df03ba14cf308210d3d936f38a54c4bcea69afc1', '2026-02-17 06:07:04'),
(14, '2025_12_25_news_translations.sql', '7fafaa63f8875c5c301cdd7af6918d5c58c0cec7b70718ecac521cca2389168a', '2026-02-17 06:07:04'),
(15, '2026_01_02_add_members_only.sql', '416deab4245defa868d354d04d2a609019f9d3f2416f4380b924be7eb7cb6934', '2026-02-17 06:07:04'),
(16, '2026_01_02_create_news_questions.sql', '7717f6f70be1d2a9eb41d7f51b1277915983f414a82ee3da5747a88c3b021dea', '2026-02-17 06:07:04'),
(17, '2026_01_04_admin_notifications.sql', '07c791b7852f24a305575c2ec107042d2c7fc5c4f078351024a11f2cb4ac3baa', '2026-02-17 06:07:04'),
(18, '2026_01_04_admin_saved_filters.sql', '1ea7c375d588d26620ce014d31e7ff5859b9c22b8a7ca23c86160d04dddc1107', '2026-02-17 06:07:04'),
(19, '2026_01_05_admin_saved_filters_default.sql', 'cee858d8e7025eb58f34e29c59cacbe5b0410d78440f4a42826ec434fb55d2cc', '2026-02-17 06:07:04'),
(20, '2026_01_10_schema_runtime_compat.sql', 'b45fd1c3a31d314b78ec5a66b345d98a47a0156bf65babaca64f70c5c0dd6452', '2026-02-17 06:07:04'),
(21, '2026_01_14_0001_create_opinion_authors.sql', 'd43d9e7673bd6386533833f2188f88200dc6681ef544589be171caac7cdaa000', '2026-02-17 06:07:04'),
(22, '2026_02_02_add_users_2fa_backup_codes.sql', 'ec983a9904c541d5e095e273be974187301608a4ee074d0477b0821a1f5deafe', '2026-02-17 06:07:04'),
(23, '2026_02_09_add_news_author_id.sql', 'b93df6cbe0058135f891b93583abaf92d03efd4f5cacd29274fc1801bc33a28f', '2026-02-17 06:07:04'),
(24, '2026_02_09_create_opinion_authors.sql', 'a73fc86f57c0884504ed1f96d5f12156432c6eeb7796c5a66b005a4f23cf2419', '2026-02-17 06:07:04'),
(25, 'v1_11_cleanup.sql', '2c71b85e5d5b7957a70a942b5c548e4eb16051788021f1ba7719b6ab3f6e3f9a', '2026-02-17 06:07:04');

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
('accent_color', '#0ea5e9', '2026-02-18 20:22:00'),
('admin_theme', 'red', '2026-02-18 21:27:04'),
('admin.theme', 'blue', '2026-02-22 07:09:01'),
('blocks.editors_pick', '1', '2026-02-22 07:09:01'),
('blocks.newsletter', '1', '2026-02-22 07:09:01'),
('blocks.trending', '1', '2026-02-22 07:09:01'),
('blocks.videos', '1', '2026-02-22 07:09:01'),
('cache.enabled', '1', '2026-02-21 15:04:29'),
('cache.ttl', '300', '2026-02-21 15:04:29'),
('facebook.app_id', '61585890473924', '2026-02-21 10:44:13'),
('facebook.pixel_id', '850674231109983', '2026-02-21 10:44:13'),
('front_preset', 'green', '2026-03-02 02:23:54'),
('front_theme', 'assets/css/themes/theme-green.css', '2026-03-02 02:23:54'),
('frontend_theme', 'assets/css/themes/theme-green.css', '2026-03-02 02:23:54'),
('logo', '', '2026-02-25 13:37:14'),
('media.compress.enabled', '1', '2026-02-26 13:35:44'),
('media.compress.max_width', '1920', '2026-02-26 13:35:44'),
('media.compress.quality', '82', '2026-02-26 13:35:44'),
('media.watermark.enabled', '1', '2026-02-26 13:35:44'),
('media.watermark.opacity', '35', '2026-02-26 13:35:44'),
('og.accent_color', '#111827', '2026-02-26 19:04:17'),
('og.arabic_mode', 'auto', '2026-02-26 19:04:17'),
('og.bg_color', '#F5F5F5', '2026-02-26 19:04:17'),
('og.default_image', 'assets/images/og-default.png', '2026-02-26 19:04:17'),
('og.enabled', '1', '2026-02-26 19:04:17'),
('og.engine', 'auto', '2026-02-26 19:04:17'),
('og.logo_image', '', '2026-02-26 19:04:17'),
('og.mode', 'dynamic', '2026-02-26 19:04:17'),
('og.muted_color', '#4B5563', '2026-02-26 19:04:17'),
('og.site_name', 'godyar', '2026-02-26 19:04:17'),
('og.tagline', 'godyar CMS', '2026-02-26 19:04:17'),
('og.template_image', '', '2026-02-26 19:04:17'),
('og.text_color', '#141414', '2026-02-26 19:04:17'),
('primary_color', '#ef4444', '2026-02-18 20:22:00'),
('primary_dark', '#b91c1c', '2026-02-18 20:22:00'),
('push.enabled', '1', '2026-02-23 20:18:39'),
('push.subject', 'mailto:admin@godyar.org', '2026-02-23 20:18:39'),
('push.vapid_private', 'pp1cctBxu1CsygyIop7HI2NgytmNmPYIZ2twBZ1oWsM', '2026-02-23 20:18:39'),
('push.vapid_public', 'BHXaiRQCX2E1FCoKKipHrP_HcgT-w1vNbt9GfXBcixycSkITcQd-n4ZWlHcm-XmlpljzWeAhDxkEcLUE5scIu14', '2026-02-23 20:18:39'),
('site_dir', 'rtl', '2026-02-17 08:07:04'),
('site_favicon', '', '2026-02-26 13:35:44'),
('site_lang', 'ar', '2026-02-17 08:07:04'),
('site_logo', '', '2026-02-26 13:35:44'),
('site_logo_url', '', '2026-02-25 13:37:14'),
('site_name', 'Godyar', '2026-02-17 08:07:04'),
('site_theme', 'assets/css/themes/theme-green.css', '2026-03-02 02:23:54'),
('site.address', 'makkah-taif, 3251', '2026-02-26 13:35:44'),
('site.desc', 'Godyar CMS', '2026-02-26 13:35:44'),
('site.email', 'abdalgaderkh@gmail.com', '2026-02-26 13:35:44'),
('site.favicon', '', '2026-02-26 13:35:44'),
('site.logo', '', '2026-02-26 13:35:44'),
('site.name', 'Godyar CMS', '2026-02-26 13:35:44'),
('site.phone', '0554507127', '2026-02-26 13:35:44'),
('site.theme_color', '#0ea5e9', '2026-02-26 13:35:44'),
('site.url', 'Godyar.org', '2026-02-26 13:35:44'),
('social.facebook', 'https://www.facebook.com/profile.php?id=61585890473924', '2026-02-21 10:44:13'),
('social.instagram', '249964056666', '2026-02-21 10:44:13'),
('social.telegram', 'abdalgaderkh@gmail.com', '2026-02-21 10:44:13'),
('social.twitter', '249964056666', '2026-02-21 10:44:13'),
('social.whatsapp', 'https://wa.me/249964056666', '2026-02-21 10:44:13'),
('social.youtube', '249964056666', '2026-02-21 10:44:13'),
('telegram.bot_token', 'Myar1979@', '2026-02-21 10:44:13'),
('telegram.chat_id', '', '2026-02-21 10:44:13'),
('theme', 'blue', '2026-02-25 22:03:19'),
('theme_file', 'blue', '2026-02-25 22:03:19'),
('theme_front', 'blue', '2026-02-25 22:03:19'),
('theme_frontend', 'blue', '2026-02-25 22:03:19'),
('theme_mode', 'light', '2026-02-17 19:36:36'),
('theme_name', 'blue', '2026-02-25 22:03:19'),
('theme_primary', '#0d6efd', '2026-02-17 19:36:36'),
('theme.accent', '#111111', '2026-02-22 07:09:01'),
('theme.container', 'boxed', '2026-02-22 07:09:01'),
('theme.footer_style', 'dark', '2026-02-22 07:09:01'),
('theme.front', 'default', '2026-02-22 07:09:01'),
('theme.frontend', 'default', '2026-02-22 07:09:01'),
('theme.header_bg_enabled', '0', '2026-02-22 07:09:01'),
('theme.header_bg_source', 'upload', '2026-02-22 07:09:01'),
('theme.header_bg_url', '', '2026-02-22 07:09:01'),
('theme.header_style', 'dark', '2026-02-22 07:09:01'),
('theme.primary', '#111111', '2026-02-22 07:09:01'),
('theme.primary_dark', '#000000', '2026-02-22 07:09:01');

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
(1, 'انفجارات في أبو ظبي والمنامة والرياض ودبي.. وقطر تعترض صواريخ إيرانية', 'الحرس الثوري استهدف 4 قواعد أمريكية في الإمارات وقطر والكويت والبحرين', 'https://godyar.org/uploads/news/news_16946e772444b61ae7bf7db5f03e2c24.webp', 'https://godyar.org/news/id/2', 1, 1, '2026-02-28 13:24:29', '2026-02-28 13:25:06'),
(2, 'الدفاع الإماراتية: وفاة شخص من جنسية آسيوية إثر سقوط شظايا على منطقة سكنية في أبوظبي', 'الدفاع الإماراتية: وفاة شخص من جنسية آسيوية إثر سقوط شظايا على منطقة سكنية في أبوظبي', 'https://godyar.org/uploads/news/news_66c9c141c0cc66c14c3bd0967262d74f.png', 'https://godyar.org/news/id/3', 1, 2, '2026-02-28 13:36:39', '2026-02-28 13:36:39'),
(3, 'ماذا قصفت إسرائيل وأمريكا في إيران حتى الآن؟', 'ماذا قصفت إسرائيل وأمريكا في إيران حتى الآن؟', 'https://godyar.org/uploads/news/news_45afc6505e3db71433dab92c35e732e4.webp', '', 1, 3, '2026-02-28 13:43:52', '2026-02-28 13:43:52');

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
(1, 'بالصور', 'بالصور', 1, '2026-02-26 20:31:20'),
(2, 'سامسونج', 'سامسونج', 1, '2026-02-26 20:31:20'),
(3, 'تطلق', 'تطلق', 1, '2026-02-26 20:31:20'),
(4, 'سلسلة', 'سلسلة', 1, '2026-02-26 20:31:20'),
(5, 'جوالات', 'جوالات', 1, '2026-02-26 20:31:20'),
(6, 'جالاكسي', 'جالاكسي', 1, '2026-02-26 20:31:20'),
(7, 'انفجارات', 'انفجارات', 1, '2026-02-28 13:03:17'),
(8, 'والمنامة', 'والمنامة', 1, '2026-02-28 13:03:17'),
(9, 'والرياض', 'والرياض', 1, '2026-02-28 13:03:17'),
(10, 'ودبي', 'ودبي', 1, '2026-02-28 13:03:17'),
(11, 'وقطر', 'وقطر', 1, '2026-02-28 13:03:17'),
(12, 'تعترض', 'تعترض', 1, '2026-02-28 13:03:17'),
(13, 'أبو', 'أبو', 1, '2026-02-28 13:35:10'),
(14, 'ظبي', 'ظبي', 1, '2026-02-28 13:35:10'),
(15, 'ماذا', 'ماذا', 1, '2026-02-28 13:42:26'),
(16, 'قصفت', 'قصفت', 1, '2026-02-28 13:42:26'),
(17, 'إسرائيل', 'إسرائيل', 1, '2026-02-28 13:42:26'),
(18, 'وأمريكا', 'وأمريكا', 1, '2026-02-28 13:42:26'),
(19, 'إيران', 'إيران', 1, '2026-02-28 13:42:26'),
(20, 'الآن؟', 'الآن', 1, '2026-02-28 13:42:26'),
(21, 'Galaxy', 'galaxy', 1, '2026-02-28 13:48:17'),
(22, 'Ultra', 'ultra', 1, '2026-02-28 13:48:17'),
(23, 'مقارنة', 'مقارنة', 1, '2026-02-28 13:48:17'),
(24, 'تستحق', 'تستحق', 1, '2026-02-28 13:48:17'),
(25, 'الترقية؟', 'الترقية', 1, '2026-02-28 13:48:17');

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
(1, NULL, NULL, 'abdalgaderkh@gmail.com', 'abdalgaderkh@gmail.com', '$2y$12$FjRDJyYX4zmzIYmIrRE8PePGnSQ9MCRWa4R/NhZJD2gVYmO4aYfS.', '$2y$12$FjRDJyYX4zmzIYmIrRE8PePGnSQ9MCRWa4R/NhZJD2gVYmO4aYfS.', 'admin', 1, 'active', '/uploads/avatars/avatar_1_1771743201.png', NULL, '2026-02-17 06:07:04', '2026-02-22 08:53:21', 0, NULL, NULL, 1);

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

--
-- إرجاع أو استيراد بيانات الجدول `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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

-- --------------------------------------------------------

--
-- Structure for view `comments`
--
DROP TABLE IF EXISTS `comments`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `comments`  AS SELECT `news_comments`.`id` AS `id`, `news_comments`.`news_id` AS `news_id`, `news_comments`.`user_id` AS `user_id`, `news_comments`.`name` AS `name`, `news_comments`.`email` AS `email`, `news_comments`.`body` AS `body`, `news_comments`.`parent_id` AS `parent_id`, `news_comments`.`status` AS `status`, `news_comments`.`score` AS `score`, `news_comments`.`ip` AS `ip`, `news_comments`.`user_agent` AS `user_agent`, `news_comments`.`created_at` AS `created_at`, `news_comments`.`updated_at` AS `updated_at` FROM `news_comments` ;

-- --------------------------------------------------------

--
-- Structure for view `comment_votes`
--
DROP TABLE IF EXISTS `comment_votes`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `comment_votes`  AS SELECT `news_comment_votes`.`id` AS `id`, `news_comment_votes`.`comment_id` AS `comment_id`, `news_comment_votes`.`user_id` AS `user_id`, `news_comment_votes`.`ip` AS `ip`, `news_comment_votes`.`value` AS `value`, `news_comment_votes`.`created_at` AS `created_at` FROM `news_comment_votes` ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;