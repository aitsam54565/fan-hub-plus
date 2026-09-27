-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2026 at 10:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fanhubplus`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity` varchar(80) DEFAULT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `entity`, `entity_id`, `ip_address`, `created_at`) VALUES
(1, 3, 'registered', 'account', NULL, NULL, '2026-09-27 07:10:23'),
(2, 1, 'sign_in', 'account', NULL, NULL, '2026-09-27 07:20:45'),
(3, 3, 'sign_in', 'account', NULL, NULL, '2026-09-27 07:40:41'),
(4, 1, 'sign_in', 'account', NULL, NULL, '2026-09-27 07:55:37'),
(5, 1, 'sign_in', 'account', NULL, NULL, '2026-09-27 07:59:57'),
(6, 1, 'sign_in', 'account', NULL, NULL, '2026-09-27 08:08:07'),
(7, 1, 'admin_created', 'characters', 1, NULL, '2026-09-27 08:10:06'),
(8, 1, 'admin_created', 'content', 1, NULL, '2026-09-27 08:20:13'),
(9, 1, 'rated', 'content', 1, NULL, '2026-09-27 08:20:34'),
(10, 1, 'admin_updated', 'content', 1, NULL, '2026-09-27 08:21:45'),
(11, 1, 'admin_delete', 'content', 1, NULL, '2026-09-27 08:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `article_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `author_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `body` longtext NOT NULL,
  `hero_image` varchar(700) DEFAULT NULL,
  `status` enum('draft','published','pending') DEFAULT 'published',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`article_id`, `category_id`, `author_id`, `title`, `slug`, `excerpt`, `body`, `hero_image`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Your guide to Fan Hub Plus', 'your-guide-to-fan-hub-plus', 'Find a fandom, save your discoveries and share your own work.', '<p>Fan Hub Plus brings eight fandom categories together in one place.</p><h2>Make it yours</h2><p>Choose your favorite categories in your profile. Browse the explorer and save articles, character profiles, videos and merchandise to your bookmarks.</p><h2>Share with the community</h2><p>Send your original fan work through the submission form. An administrator reviews every submission before publication.</p>', NULL, 'published', '2026-09-27 07:09:06', '2026-09-27 07:09:06', '2026-09-27 07:09:06');

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `bookmark_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `article_id` int(10) UNSIGNED DEFAULT NULL,
  `character_id` int(10) UNSIGNED DEFAULT NULL,
  `media_id` int(10) UNSIGNED DEFAULT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `carousel`
--

CREATE TABLE `carousel` (
  `carousel_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(700) NOT NULL,
  `button_text` varchar(80) DEFAULT 'Explore Now',
  `button_url` varchar(700) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `slug`, `description`, `image_url`, `sort_order`, `is_active`) VALUES
(1, 'Anime', 'anime', 'Japanese animation, series, movies, characters and releases.', NULL, 1, 1),
(2, 'Gaming', 'gaming', 'Games, franchises, trailers, updates and community highlights.', NULL, 2, 1),
(3, 'Movies', 'movies', 'Movies, cinematic universes, trailers and featured releases.', NULL, 3, 1),
(4, 'TV Shows', 'tv-shows', 'Series, episodes, characters and television fandoms.', NULL, 4, 1),
(5, 'K-Pop', 'k-pop', 'Groups, solo artists, music videos, comebacks and performances.', NULL, 5, 1),
(6, 'Comics', 'comics', 'Superheroes, publishers, story arcs and comic culture.', NULL, 6, 1),
(7, 'Manga', 'manga', 'Manga series, creators, characters and releases.', NULL, 7, 1),
(8, 'Cosplay', 'cosplay', 'Cosplay showcases, guides, conventions and creators.', NULL, 8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `characters`
--

CREATE TABLE `characters` (
  `character_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(180) NOT NULL,
  `bio` text DEFAULT NULL,
  `image_url` varchar(700) DEFAULT NULL,
  `popularity_score` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fandom` varchar(180) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `characters`
--

INSERT INTO `characters` (`character_id`, `category_id`, `name`, `bio`, `image_url`, `popularity_score`, `created_at`, `fandom`, `status`) VALUES
(1, 1, 'Monkey D. Luffy', 'Monkey D. Luffy is the main hero and captain of the Straw Hat Pirates in the One Piece series.', 'https://static.wikia.nocookie.net/onepiece/images/6/6d/Monkey_D._Luffy_Anime_Post_Timeskip_Infobox.png/revision/latest?cb=20260611004637', 0.00, '2026-09-27 08:10:06', 'One Piece', 'published');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_faqs`
--

CREATE TABLE `chatbot_faqs` (
  `faq_id` int(10) UNSIGNED NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `keywords` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_faqs`
--

INSERT INTO `chatbot_faqs` (`faq_id`, `question`, `answer`, `category_id`, `keywords`, `is_active`, `created_at`) VALUES
(1, 'What is Fan Hub Plus?', 'Fan Hub Plus is a fandom discovery platform covering Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay.', NULL, 'about,platform,fan hub,hello,hi', 1, '2026-09-27 07:09:06'),
(2, 'How do I bookmark an item?', 'Sign in, open an article, character, media, merchandise or content detail page, then choose Save bookmark. Find saved items and add notes in My bookmarks.', NULL, 'bookmark,save,saved,notes', 1, '2026-09-27 07:09:06'),
(3, 'How do I find events near me?', 'Open Events, choose a city or month, or press Find nearby. Your browser asks for location permission. Coordinates stay in your browser.', NULL, 'event,events,nearby,location,city', 1, '2026-09-27 07:09:06'),
(4, 'How do I share my fan work?', 'Open Submit fan work from your dashboard. Add a title, category and your original work. An administrator reviews it before publication.', NULL, 'submit,submission,fan work,approval,article', 1, '2026-09-27 07:09:06'),
(5, 'How do I reset my password?', 'Choose Forgot password on the sign-in page. Local installations can show a one-time token link. Hosted installations need an email delivery service configured by the operator.', NULL, 'password,reset,forgot,login', 1, '2026-09-27 07:09:06'),
(6, 'Can I buy merchandise here?', 'Merchandise is a discovery showcase. Fan Hub Plus does not take payments, place orders or provide checkout.', NULL, 'merchandise,buy,payment,checkout', 1, '2026-09-27 07:09:06'),
(7, 'How do I change display preferences?', 'Use the Display menu to change the color theme and text size. Signed-in members can save these choices in Profile.', NULL, 'display,font,theme,dark,profile', 1, '2026-09-27 07:09:06');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_messages`
--

CREATE TABLE `chatbot_messages` (
  `message_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `session_key` varchar(120) DEFAULT NULL,
  `message` text NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot_messages`
--

INSERT INTO `chatbot_messages` (`message_id`, `user_id`, `session_key`, `message`, `response`, `created_at`) VALUES
(1, 3, 'feaa81e7e49e2dff2f4521ad5efa8442bd67c4ef77fda1df4fd87691886d5d6c', 'How do I bookmark an item?', 'Sign in, open an article, character, media, merchandise or content detail page, then choose Save bookmark. Find saved items and add notes in My bookmarks.', '2026-09-27 07:41:23'),
(2, 3, 'feaa81e7e49e2dff2f4521ad5efa8442bd67c4ef77fda1df4fd87691886d5d6c', 'Hello', 'Fan Hub Plus is a fandom discovery platform covering Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay.', '2026-09-27 07:41:34');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `content_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL,
  `type` enum('article','video','audio','image','trailer','podcast') NOT NULL DEFAULT 'article',
  `subcategory` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `content_url` varchar(700) DEFAULT NULL,
  `thumbnail_url` varchar(700) DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `popularity_score` decimal(8,2) NOT NULL DEFAULT 0.00,
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `language` varchar(20) DEFAULT 'en',
  `age_rating` varchar(20) DEFAULT 'all',
  `visibility` enum('draft','published','scheduled') NOT NULL DEFAULT 'published',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `allow_comments` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trailer_url` varchar(700) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_tags`
--

CREATE TABLE `content_tags` (
  `content_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `country` varchar(120) DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `ticket_url` varchar(700) DEFAULT NULL,
  `image_url` varchar(700) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fan_submissions`
--

CREATE TABLE `fan_submissions` (
  `submission_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content_url` varchar(700) DEFAULT NULL,
  `image_url` varchar(700) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `article_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `type` enum('bug','suggestion','query','complaint','other') NOT NULL DEFAULT 'query',
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','in_progress','resolved','closed') DEFAULT 'new',
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` bigint(20) UNSIGNED NOT NULL,
  `identity_hash` char(64) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `media_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `media_type` enum('video','audio','image','trailer') NOT NULL,
  `media_url` varchar(700) NOT NULL,
  `thumbnail_url` varchar(700) DEFAULT NULL,
  `duration_seconds` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tags` varchar(500) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merchandise`
--

CREATE TABLE `merchandise` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(700) DEFAULT NULL,
  `tag` varchar(80) DEFAULT NULL,
  `is_upcoming` tinyint(1) DEFAULT 0,
  `release_date` date DEFAULT NULL,
  `popularity_score` decimal(8,2) DEFAULT 0.00,
  `view_count` int(10) UNSIGNED DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fandom` varchar(180) DEFAULT NULL,
  `resource_url` varchar(700) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merchandise_images`
--

CREATE TABLE `merchandise_images` (
  `image_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `image_url` varchar(700) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `status` enum('subscribed','unsubscribed') DEFAULT 'subscribed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(700) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `media_id` int(10) UNSIGNED DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `schema_versions`
--

CREATE TABLE `schema_versions` (
  `version` int(11) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schema_versions`
--

INSERT INTO `schema_versions` (`version`, `applied_at`) VALUES
(2, '2026-09-27 07:09:05');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('site_name', 'Fan Hub Plus', '2026-09-27 07:09:06'),
('site_tagline', 'One universe. Every fandom.', '2026-09-27 07:09:06');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `upcoming_releases`
--

CREATE TABLE `upcoming_releases` (
  `release_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `release_type` enum('anime','game','movie','tv','comic','manga','music','merchandise','other') NOT NULL,
  `description` text DEFAULT NULL,
  `release_date` date NOT NULL,
  `image_url` varchar(700) DEFAULT NULL,
  `resource_url` varchar(700) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','editor','admin') NOT NULL DEFAULT 'user',
  `avatar_url` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','suspended','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `session_version` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `avatar_url`, `bio`, `status`, `created_at`, `updated_at`, `last_login`, `session_version`) VALUES
(1, 'System Admin', 'admin@fanhubplus.local', '$2y$12$9ryuKP.TlYjasb2cA4Y1QeDVc31.6P42n42tNEdG2w0KGJPyTd9wS', 'admin', NULL, NULL, 'active', '2026-09-27 07:09:00', '2026-09-27 08:08:07', '2026-09-27 08:08:07', 1),
(2, 'Demo User', 'user@fanhubplus.local', '$2y$12$bVbFNvz2Szp6EF7XGFqfF.pKKWcb9qrzoLMU1JaL99UTF4v0xkBze', 'user', NULL, NULL, 'active', '2026-09-27 07:09:00', '2026-09-27 07:09:00', NULL, 1),
(3, 'Aitsam Faheem', 'aitsamfaheem32@gmail.com', '$2y$10$649JdW8/Gi87RiwHXMM1MOzUrLEAdZomN4bOfmgDBiuXsKKBNrWkO', 'user', NULL, NULL, 'active', '2026-09-27 07:10:23', '2026-09-27 07:40:41', '2026-09-27 07:40:41', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `preference_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `dark_mode` tinyint(1) DEFAULT 1,
  `font_size` enum('small','medium','large') DEFAULT 'medium',
  `notifications` tinyint(1) DEFAULT 1,
  `language` varchar(10) DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`preference_id`, `user_id`, `dark_mode`, `font_size`, `notifications`, `language`) VALUES
(1, 1, 1, 'medium', 1, 'en'),
(2, 2, 1, 'medium', 1, 'en'),
(4, 3, 1, 'medium', 1, 'en');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_activity_user` (`user_id`,`log_id`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`article_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`bookmark_id`),
  ADD UNIQUE KEY `uq_bookmark` (`user_id`,`content_id`),
  ADD UNIQUE KEY `uq_bookmark_article` (`user_id`,`article_id`),
  ADD UNIQUE KEY `uq_bookmark_character` (`user_id`,`character_id`),
  ADD UNIQUE KEY `uq_bookmark_media` (`user_id`,`media_id`),
  ADD UNIQUE KEY `uq_bookmark_item` (`user_id`,`item_id`),
  ADD KEY `content_id` (`content_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `character_id` (`character_id`),
  ADD KEY `media_id` (`media_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `carousel`
--
ALTER TABLE `carousel`
  ADD PRIMARY KEY (`carousel_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`character_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `chatbot_faqs`
--
ALTER TABLE `chatbot_faqs`
  ADD PRIMARY KEY (`faq_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_key` (`session_key`);

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`content_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `visibility` (`visibility`),
  ADD KEY `release_date` (`release_date`),
  ADD KEY `popularity_score` (`popularity_score`),
  ADD KEY `idx_content_category_visibility` (`category_id`,`visibility`,`created_at`);

--
-- Indexes for table `content_tags`
--
ALTER TABLE `content_tags`
  ADD PRIMARY KEY (`content_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `event_date` (`event_date`),
  ADD KEY `city` (`city`);

--
-- Indexes for table `fan_submissions`
--
ALTER TABLE `fan_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `identity_hash` (`identity_hash`,`attempted_at`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `merchandise`
--
ALTER TABLE `merchandise`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `merchandise_images`
--
ALTER TABLE `merchandise_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`subscriber_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token_hash` (`token_hash`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `uq_rating` (`user_id`,`content_id`),
  ADD UNIQUE KEY `uq_rating_media` (`user_id`,`media_id`),
  ADD KEY `content_id` (`content_id`),
  ADD KEY `media_id` (`media_id`);

--
-- Indexes for table `schema_versions`
--
ALTER TABLE `schema_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `upcoming_releases`
--
ALTER TABLE `upcoming_releases`
  ADD PRIMARY KEY (`release_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `release_date` (`release_date`,`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`user_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `article_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `bookmark_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carousel`
--
ALTER TABLE `carousel`
  MODIFY `carousel_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `characters`
--
ALTER TABLE `characters`
  MODIFY `character_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chatbot_faqs`
--
ALTER TABLE `chatbot_faqs`
  MODIFY `faq_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  MODIFY `message_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `content`
--
ALTER TABLE `content`
  MODIFY `content_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fan_submissions`
--
ALTER TABLE `fan_submissions`
  MODIFY `submission_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `media_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merchandise`
--
ALTER TABLE `merchandise`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merchandise_images`
--
ALTER TABLE `merchandise_images`
  MODIFY `image_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `subscriber_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `upcoming_releases`
--
ALTER TABLE `upcoming_releases`
  MODIFY `release_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `preference_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `content` (`content_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_3` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_4` FOREIGN KEY (`character_id`) REFERENCES `characters` (`character_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_5` FOREIGN KEY (`media_id`) REFERENCES `media` (`media_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_6` FOREIGN KEY (`item_id`) REFERENCES `merchandise` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `chatbot_faqs`
--
ALTER TABLE `chatbot_faqs`
  ADD CONSTRAINT `chatbot_faqs_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `chatbot_messages`
--
ALTER TABLE `chatbot_messages`
  ADD CONSTRAINT `chatbot_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `content_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `content_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `content_tags`
--
ALTER TABLE `content_tags`
  ADD CONSTRAINT `content_tags_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `content` (`content_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `fan_submissions`
--
ALTER TABLE `fan_submissions`
  ADD CONSTRAINT `fan_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fan_submissions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fan_submissions_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fan_submissions_ibfk_4` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `media_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `merchandise`
--
ALTER TABLE `merchandise`
  ADD CONSTRAINT `merchandise_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `merchandise_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `merchandise_images`
--
ALTER TABLE `merchandise_images`
  ADD CONSTRAINT `merchandise_images_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `merchandise` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `content` (`content_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`media_id`) REFERENCES `media` (`media_id`) ON DELETE CASCADE;

--
-- Constraints for table `upcoming_releases`
--
ALTER TABLE `upcoming_releases`
  ADD CONSTRAINT `upcoming_releases_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `upcoming_releases_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
