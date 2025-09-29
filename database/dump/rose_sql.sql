-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 15 Eki 2020, 01:57:45
-- Sunucu sürümü: 10.4.13-MariaDB
-- PHP Sürümü: 7.2.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `rose`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_accounts`
--

CREATE TABLE `rose_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `number` varchar(80) NOT NULL,
  `holder` varchar(200) NOT NULL,
  `balance` decimal(16,4) DEFAULT 0.0000,
  `code` varchar(60) DEFAULT NULL,
  `account_type` varchar(100) NOT NULL DEFAULT 'Basic',
  `note` varchar(255) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_accounts`
--

INSERT INTO `rose_accounts` (`id`, `number`, `holder`, `balance`, `code`, `account_type`, `note`, `ins`, `created_at`, `updated_at`) VALUES
(1, 'Default Account', 'Default Account', '20.0000', '1234', 'Basic', NULL, 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(2, 'Default Account Purchase', 'Default Account Purchase', '0.0000', '12345678', 'Basic', NULL, 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_additionals`
--

CREATE TABLE `rose_additionals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` decimal(10,4) UNSIGNED DEFAULT 0.0000,
  `class` int(1) UNSIGNED NOT NULL,
  `type1` enum('%','flat','b_flat','b_per') NOT NULL DEFAULT '%',
  `type2` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `type3` enum('inclusive','exclusive','cgst','igst') DEFAULT 'exclusive',
  `default_a` int(1) UNSIGNED DEFAULT 0,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_additionals`
--

INSERT INTO `rose_additionals` (`id`, `name`, `value`, `class`, `type1`, `type2`, `type3`, `default_a`, `ins`, `created_at`, `updated_at`) VALUES
(2, 'Discount % (after Tax) ', '0.0000', 2, '%', 'exclusive', 'exclusive', NULL, 1, NULL, NULL),
(4, 'Discount flat (after Tax) ', '0.0000', 2, 'flat', 'exclusive', 'exclusive', NULL, 1, NULL, NULL),
(7, 'Discount flat (before Tax) ', '0.0000', 2, 'b_flat', 'exclusive', 'exclusive', NULL, 1, NULL, NULL),
(12, 'Sales Tax 1', '9.0000', 1, '%', 'inclusive', 'inclusive', 0, 1, '2020-06-02 22:35:22', '2020-06-02 22:35:59'),
(13, 'Sales Tax 2', '12.0000', 1, '%', 'inclusive', 'inclusive', 0, 1, '2020-06-02 22:36:30', '2020-06-02 22:36:30');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_attendances`
--

CREATE TABLE `rose_attendances` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `present` date NOT NULL,
  `t_from` time NOT NULL,
  `t_to` time NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `actual_hours` int(11) UNSIGNED DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_banks`
--

CREATE TABLE `rose_banks` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `bank` varchar(100) NOT NULL,
  `number` varchar(70) NOT NULL,
  `code` varchar(60) DEFAULT NULL,
  `note` varchar(2000) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `enable` enum('Yes','No') NOT NULL DEFAULT 'No',
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_companies`
--

CREATE TABLE `rose_companies` (
  `id` int(4) UNSIGNED NOT NULL,
  `cname` char(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `region` varchar(60) NOT NULL,
  `country` varchar(30) NOT NULL,
  `postbox` varchar(15) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `taxid` varchar(30) NOT NULL,
  `tax` int(11) UNSIGNED NOT NULL,
  `currency` varchar(4) NOT NULL,
  `currency_format` int(1) UNSIGNED NOT NULL,
  `main_date_format` enum('d-m-Y','m-d-Y','Y-m-d') NOT NULL DEFAULT 'd-m-Y',
  `user_date_format` enum('DD-MM-YYYY','MM-DD-YYYY','YYYY-MM-DD','') NOT NULL DEFAULT 'DD-MM-YYYY',
  `zone` varchar(25) NOT NULL,
  `logo` varchar(30) DEFAULT NULL,
  `theme_logo` varchar(255) DEFAULT NULL,
  `icon` varchar(30) DEFAULT NULL,
  `lang` varchar(20) DEFAULT 'english',
  `valid` int(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_companies`
--

INSERT INTO `rose_companies` (`id`, `cname`, `address`, `city`, `region`, `country`, `postbox`, `phone`, `email`, `taxid`, `tax`, `currency`, `currency_format`, `main_date_format`, `user_date_format`, `zone`, `logo`, `theme_logo`, `icon`, `lang`, `valid`, `created_at`, `updated_at`) VALUES
(1, 'STORE', '132', 'Re', 'NY', 'USA', '', '(233) 111-1111', 'EXAMPLE@aol.com', 'R444324', 0, 'E', 0, 'd-m-Y', 'DD-MM-YYYY', 'US/Central', '15908605.png', '1590860.png', '159086.ico', 'english', 1, '2020-05-27 18:22:54', '2020-06-09 02:18:37');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_config_meta`
--

CREATE TABLE `rose_config_meta` (
  `id` bigint(20) NOT NULL,
  `feature_id` int(10) UNSIGNED NOT NULL,
  `feature_value` int(10) NOT NULL,
  `value1` varchar(400) DEFAULT NULL,
  `value2` varchar(400) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_config_meta`
--

INSERT INTO `rose_config_meta` (`id`, `feature_id`, `feature_value`, `value1`, `value2`, `ins`, `created_at`, `updated_at`) VALUES
(2, 2, 1, 'default_currency', '{\"key\":\"api_key\",\"base_currency\":\"USD\",\"endpoint\":\"live\",\"enable\":\"0\"}', 1, NULL, NULL),
(3, 1, 1, 'default_warehouse', 'noti@email.com', 1, NULL, NULL),
(4, 3, 11, 'default_discount', '%', 1, NULL, NULL),
(5, 4, 9, 'default_tax', '0', 1, NULL, NULL),
(6, 5, 1, 'online_payment', '1', 1, NULL, NULL),
(7, 6, 1, 'online_payment_account', '180493605', 1, NULL, NULL),
(8, 7, 0, 'url_shorten_service', '56546x', 1, NULL, '2020-06-03 02:39:00'),
(9, 8, 1, 'default_sales_transaction_category', 'MHJhMXo1Q2NEQTRyaVNLSUczcjAxUT09', 1, NULL, NULL),
(18, 9, 1, 'jpeg,gif,png,pdf,xls', NULL, 1, NULL, NULL),
(19, 10, 2, 'purchase_transaction_category', NULL, 1, NULL, NULL),
(20, 11, 0, 'sample@email.com', '{\"new_invoice\":\"0\",\"new_trans\":\"0\",\"cust_new_invoice\":\"0\",\"del_invoice\":\"0\",\"del_trans\":\"0\",\"sms_new_invoice\":\"0\",\"task_new\":\"0\"}', 1, NULL, NULL),
(21, 12, 0, 'sample@email.com', 'Invoice_delete_and_email', 1, NULL, NULL),
(22, 13, 0, '1', '2', 1, NULL, NULL),
(23, 14, 0, '0', 'auto_sms_email', 1, NULL, NULL),
(235, 15, 1, 'ltr', 'ltr_rtl', 1, NULL, NULL),
(371, 16, 1, '2', 'done_due_status', 1, NULL, NULL),
(436, 17, 1, '[\"Basic\",\"Assets\",\"Equity\",\"Expenses\",\"Income\",\"Liabilities\",\"Test\"]', '[\"Cash\",\"Bank Transfer\",\"Cheque\",\"Prepaid Card\",\"Other\"]', 1, NULL, NULL),
(454, 18, 0, 'crm_hrm', '0', 1, NULL, NULL),
(455, 19, 0, '{\"address\":\"10.10.10.11\",\"port\":\"9100\",\"mode\":\"1\"}', '9100', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_currencies`
--

CREATE TABLE `rose_currencies` (
  `id` int(4) UNSIGNED NOT NULL,
  `code` varchar(3) DEFAULT NULL,
  `symbol` varchar(3) DEFAULT NULL,
  `rate` decimal(10,4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `thousand_sep` char(1) DEFAULT NULL,
  `decimal_sep` char(1) DEFAULT NULL,
  `precision_point` tinyint(2) NOT NULL,
  `symbol_position` tinyint(1) NOT NULL DEFAULT 1,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_currencies`
--

INSERT INTO `rose_currencies` (`id`, `code`, `symbol`, `rate`, `thousand_sep`, `decimal_sep`, `precision_point`, `symbol_position`, `ins`, `created_at`, `updated_at`) VALUES
(1, 'USD', '$', '1.0000', ',', '.', 2, 0, 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_customers`
--

CREATE TABLE `rose_customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `main` tinyint(2) UNSIGNED DEFAULT 1,
  `rel_id` bigint(20) UNSIGNED DEFAULT 0,
  `employee_id` int(11) DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `region` varchar(30) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postbox` varchar(20) DEFAULT NULL,
  `email` varchar(90) NOT NULL,
  `picture` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `taxid` varchar(100) DEFAULT NULL,
  `name_s` varchar(100) DEFAULT NULL,
  `phone_s` varchar(100) DEFAULT NULL,
  `email_s` varchar(100) DEFAULT NULL,
  `address_s` varchar(100) DEFAULT NULL,
  `city_s` varchar(100) DEFAULT NULL,
  `region_s` varchar(100) DEFAULT NULL,
  `country_s` varchar(100) DEFAULT NULL,
  `postbox_s` varchar(100) DEFAULT NULL,
  `balance` decimal(16,2) DEFAULT 0.00,
  `docid` varchar(255) DEFAULT NULL,
  `custom1` varchar(255) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `active` int(1) UNSIGNED DEFAULT 1,
  `password` varchar(191) DEFAULT NULL,
  `role_id` int(10) UNSIGNED DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_customers`
--

INSERT INTO `rose_customers` (`id`, `main`, `rel_id`, `employee_id`, `name`, `phone`, `address`, `city`, `region`, `country`, `postbox`, `email`, `picture`, `company`, `taxid`, `name_s`, `phone_s`, `email_s`, `address_s`, `city_s`, `region_s`, `country_s`, `postbox_s`, `balance`, `docid`, `custom1`, `ins`, `active`, `password`, `role_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 1, 'Walk In', '123456', 'Address Line 1', 'City', 'Region', 'Country', 'Post Boxx', 'customer@example.com', NULL, 'Company', 'Tax ID', 'Shipping Name', 'Shipping Phone', 'email_s', 'Shipping Address', 'Shipping City', 'Shiping Region', 'Shipiing Country', 'Post Box', '885.05', 'Document ID ', NULL, 1, 1, '$2y$10$fVzntgWDFOapAa3LJlW8EOGwFBovLnc88GXqMKD4sSOneLP7NUuPG', 0, NULL, '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(2, 1, 0, 0, 'Julian', '112333', '132', 'NY', 'Newyork', 'United States', '', 'sales@gmail.com', NULL, 'jjj', '', '', '', '', '', '', '', '', '', '0.00', '', '', 1, 1, '$2y$10$1VjNHAMzWQlZ0FG80xWdNOH7Xq1havm5QvFN/R3cmlD/p.odMeIXW', 0, NULL, '2020-05-31 23:42:16', '2020-05-31 23:42:16');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_customer_groups`
--

CREATE TABLE `rose_customer_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(80) NOT NULL,
  `summary` varchar(250) DEFAULT NULL,
  `disc_rate` decimal(10,4) DEFAULT 0.0000,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_customer_groups`
--

INSERT INTO `rose_customer_groups` (`id`, `title`, `summary`, `disc_rate`, `ins`, `created_at`, `updated_at`) VALUES
(6, 'Same Group', 'sample', '0.0000', 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_customer_group_entries`
--

CREATE TABLE `rose_customer_group_entries` (
  `id` bigint(20) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `customer_group_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_custom_entries`
--

CREATE TABLE `rose_custom_entries` (
  `id` int(11) UNSIGNED NOT NULL,
  `custom_field_id` int(11) UNSIGNED NOT NULL,
  `rid` int(11) UNSIGNED DEFAULT NULL,
  `module` int(3) UNSIGNED DEFAULT NULL,
  `data` varchar(255) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_custom_entries`
--

INSERT INTO `rose_custom_entries` (`id`, `custom_field_id`, `rid`, `module`, `data`, `ins`, `created_at`, `updated_at`) VALUES
(4, 1, 76, 3, NULL, 1, '2020-06-06 17:56:45', '2020-06-06 17:56:45'),
(6, 1, 85, 3, 'https://www.website.com', 1, '2020-06-08 19:59:17', '2020-06-08 19:59:17'),
(7, 2, 85, 3, 'Million', 1, '2020-06-08 19:59:17', '2020-06-08 19:59:17');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_custom_fields`
--

CREATE TABLE `rose_custom_fields` (
  `id` int(11) UNSIGNED NOT NULL,
  `module_id` int(3) NOT NULL,
  `field_type` varchar(30) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `placeholder` varchar(30) DEFAULT NULL,
  `default_data` text DEFAULT NULL,
  `field_view` tinyint(2) NOT NULL,
  `other` varchar(50) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_custom_fields`
--

INSERT INTO `rose_custom_fields` (`id`, `module_id`, `field_type`, `name`, `placeholder`, `default_data`, `field_view`, `other`, `ins`, `created_at`, `updated_at`) VALUES
(1, 3, 'text', 'Html Link', '', '', 1, NULL, 1, '2020-06-02 21:46:38', '2020-06-09 01:31:25'),
(2, 3, 'text', 'Vendor/Supplier', '', '', 3, NULL, 1, '2020-06-09 01:24:11', '2020-06-09 01:31:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_departments`
--

CREATE TABLE `rose_departments` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `note` varchar(100) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_departments`
--

INSERT INTO `rose_departments` (`id`, `name`, `note`, `ins`, `created_at`, `updated_at`) VALUES
(1, 'Sample Departmet', 'Sample Departmet', 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_drafts`
--

CREATE TABLE `rose_drafts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tid` bigint(20) UNSIGNED NOT NULL,
  `invoicedate` date NOT NULL,
  `invoiceduedate` date NOT NULL,
  `subtotal` decimal(16,4) DEFAULT 0.0000,
  `shipping` decimal(16,4) DEFAULT 0.0000,
  `ship_tax` decimal(16,4) DEFAULT 0.0000,
  `ship_tax_type` enum('inclusive','exclusive','off','none') DEFAULT 'off',
  `ship_tax_rate` decimal(16,4) DEFAULT 0.0000,
  `discount` decimal(16,4) DEFAULT 0.0000,
  `extra_discount` decimal(16,4) DEFAULT 0.0000,
  `discount_rate` decimal(10,4) DEFAULT 0.0000,
  `tax` decimal(16,4) DEFAULT 0.0000,
  `total` decimal(16,4) DEFAULT 0.0000,
  `pmethod` varchar(25) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `status` enum('paid','due','canceled','partial') NOT NULL DEFAULT 'due',
  `customer_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL,
  `pamnt` decimal(16,4) DEFAULT 0.0000,
  `items` decimal(10,4) NOT NULL,
  `tax_format` enum('exclusive','inclusive','off','cgst','igst') NOT NULL DEFAULT 'exclusive',
  `tax_id` bigint(20) DEFAULT 0,
  `discount_format` enum('%','flat','b_flat','b_per') NOT NULL DEFAULT '%',
  `refer` varchar(20) DEFAULT NULL,
  `term_id` bigint(20) UNSIGNED DEFAULT NULL,
  `currency` int(4) DEFAULT NULL,
  `i_class` int(1) NOT NULL DEFAULT 0,
  `r_time` varchar(10) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_draft_items`
--

CREATE TABLE `rose_draft_items` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) NOT NULL DEFAULT 0,
  `product_name` varchar(255) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `product_qty` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `product_price` decimal(16,4) NOT NULL DEFAULT 0.0000,
  `product_tax` decimal(16,4) DEFAULT 0.0000,
  `product_discount` decimal(16,4) DEFAULT 0.0000,
  `product_subtotal` decimal(16,4) DEFAULT 0.0000,
  `total_tax` decimal(16,4) DEFAULT 0.0000,
  `total_discount` decimal(16,4) DEFAULT 0.0000,
  `product_des` text DEFAULT NULL,
  `i_class` int(1) NOT NULL DEFAULT 0,
  `unit` varchar(5) DEFAULT NULL,
  `serial` varchar(100) DEFAULT NULL,
  `unit_value` decimal(16,4) NOT NULL DEFAULT 1.0000,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_email_settings`
--

CREATE TABLE `rose_email_settings` (
  `id` bigint(20) NOT NULL,
  `active` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `driver` varchar(50) NOT NULL DEFAULT 'smtp',
  `host` varchar(100) NOT NULL,
  `port` int(11) NOT NULL,
  `auth` enum('true','false') NOT NULL,
  `auth_type` enum('none','tls','ssl') NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(100) NOT NULL,
  `sender` varchar(100) NOT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_email_settings`
--

INSERT INTO `rose_email_settings` (`id`, `active`, `driver`, `host`, `port`, `auth`, `auth_type`, `username`, `password`, `sender`, `ins`, `created_at`, `updated_at`) VALUES
(1, 1, 'smtp', 'secure.emailsrvr.com', 587, 'true', 'ssl', 'Katee@barterpost.net', 'Temp00234', 'billing@barterpost.net', 1, '2020-05-27 18:22:54', '2020-06-03 02:39:00');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_events`
--

CREATE TABLE `rose_events` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3a87ad',
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ins` int(4) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_event_relations`
--

CREATE TABLE `rose_event_relations` (
  `id` int(11) UNSIGNED NOT NULL,
  `event_id` int(11) UNSIGNED DEFAULT NULL,
  `related` int(3) UNSIGNED DEFAULT NULL,
  `r_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_history`
--

CREATE TABLE `rose_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `type_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `class` varchar(191) DEFAULT NULL,
  `text` varchar(191) NOT NULL,
  `assets` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_history_types`
--

CREATE TABLE `rose_history_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_history_types`
--

INSERT INTO `rose_history_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'User', '2020-05-27 18:22:54', NULL),
(2, 'Role', '2020-05-27 18:22:54', NULL),
(3, 'Permission', '2020-05-27 18:22:54', NULL),
(4, 'Page', '2020-05-27 18:22:54', NULL),
(5, 'BlogTag', '2020-05-27 18:22:54', NULL),
(6, 'BlogCategory', '2020-05-27 18:22:54', NULL),
(7, 'Blog', '2020-05-27 18:22:54', NULL),
(8, 'PlanSubscriptions', '2020-05-27 18:22:54', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_hrm_metas`
--

CREATE TABLE `rose_hrm_metas` (
  `id` bigint(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `department_id` bigint(20) DEFAULT NULL,
  `salary` decimal(16,4) DEFAULT NULL,
  `hra` decimal(16,4) DEFAULT NULL,
  `entry_time` time DEFAULT NULL,
  `exit_time` time DEFAULT NULL,
  `clock` int(1) DEFAULT NULL,
  `clock_in` int(11) DEFAULT NULL,
  `clock_out` int(11) DEFAULT NULL,
  `commission` decimal(10,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_hrm_metas`
--

INSERT INTO `rose_hrm_metas` (`id`, `user_id`, `department_id`, `salary`, `hra`, `entry_time`, `exit_time`, `clock`, `clock_in`, `clock_out`, `commission`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '7.0000', '5.0000', '00:02:00', '02:00:00', 0, 0, 1582821197, '15.0000', '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_invoices`
--

CREATE TABLE `rose_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tid` bigint(20) UNSIGNED NOT NULL,
  `invoicedate` date NOT NULL,
  `invoiceduedate` date NOT NULL,
  `subtotal` decimal(16,4) DEFAULT 0.0000,
  `shipping` decimal(16,4) DEFAULT 0.0000,
  `ship_tax` decimal(16,4) DEFAULT 0.0000,
  `ship_tax_type` enum('inclusive','exclusive','off','none') DEFAULT 'off',
  `ship_tax_rate` decimal(16,4) DEFAULT 0.0000,
  `discount` decimal(16,4) DEFAULT 0.0000,
  `extra_discount` decimal(16,4) DEFAULT 0.0000,
  `discount_rate` decimal(10,4) DEFAULT 0.0000,
  `tax` decimal(16,4) DEFAULT 0.0000,
  `total` decimal(16,4) DEFAULT 0.0000,
  `pmethod` varchar(25) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `status` enum('paid','due','canceled','partial') NOT NULL DEFAULT 'due',
  `customer_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL,
  `pamnt` decimal(16,4) DEFAULT 0.0000,
  `items` decimal(10,4) NOT NULL,
  `tax_format` enum('exclusive','inclusive','off','cgst','igst') NOT NULL DEFAULT 'exclusive',
  `tax_id` bigint(20) DEFAULT 0,
  `discount_format` enum('%','flat','b_flat','b_per') NOT NULL DEFAULT '%',
  `refer` varchar(20) DEFAULT NULL,
  `term_id` bigint(20) UNSIGNED DEFAULT NULL,
  `currency` int(4) DEFAULT NULL,
  `i_class` int(1) NOT NULL DEFAULT 0,
  `r_time` varchar(10) NOT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_invoices`
--

INSERT INTO `rose_invoices` (`id`, `tid`, `invoicedate`, `invoiceduedate`, `subtotal`, `shipping`, `ship_tax`, `ship_tax_type`, `ship_tax_rate`, `discount`, `extra_discount`, `discount_rate`, `tax`, `total`, `pmethod`, `notes`, `status`, `customer_id`, `user_id`, `pamnt`, `items`, `tax_format`, `tax_id`, `discount_format`, `refer`, `term_id`, `currency`, `i_class`, `r_time`, `ins`, `created_at`, `updated_at`) VALUES
(1, 1, '2020-05-31', '2020-05-31', '1599.0000', '99.0000', '0.0000', 'none', '0.0000', '159.9000', '159.9000', '10.0000', '0.0000', '1538.1000', NULL, '', 'due', 2, 1, '0.0000', '1.0000', 'off', 0, '%', '', 1, 1, 0, '', 1, '2020-05-31 23:45:54', '2020-05-31 23:49:26');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_invoice_items`
--

CREATE TABLE `rose_invoice_items` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) NOT NULL DEFAULT 0,
  `product_name` varchar(255) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `product_qty` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `product_price` decimal(16,4) NOT NULL DEFAULT 0.0000,
  `product_tax` decimal(16,4) DEFAULT 0.0000,
  `product_discount` decimal(16,4) DEFAULT 0.0000,
  `product_subtotal` decimal(16,4) DEFAULT 0.0000,
  `total_tax` decimal(16,4) DEFAULT 0.0000,
  `total_discount` decimal(16,4) DEFAULT 0.0000,
  `product_des` text DEFAULT NULL,
  `i_class` int(1) NOT NULL DEFAULT 0,
  `unit` varchar(5) DEFAULT NULL,
  `serial` varchar(100) DEFAULT NULL,
  `unit_value` decimal(16,4) NOT NULL DEFAULT 1.0000,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_invoice_items`
--

INSERT INTO `rose_invoice_items` (`id`, `invoice_id`, `product_id`, `product_name`, `code`, `product_qty`, `product_price`, `product_tax`, `product_discount`, `product_subtotal`, `total_tax`, `total_discount`, `product_des`, `i_class`, `unit`, `serial`, `unit_value`, `ins`, `created_at`, `updated_at`) VALUES
(1, 1, 22, '02-2-15-40-50 - 15w rough cut bedroom set', NULL, '1.0000', '1599.0000', '99914.0000', '0.0000', '1599.0000', '0.0000', '0.0000', 'Rustic bedroom set, queen, bed, dresser,mirror, chest', 0, '9', NULL, '1.0000', 1, '2020-05-31 18:15:54', '2020-05-31 18:15:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_menus`
--

CREATE TABLE `rose_menus` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` enum('backend','frontend') NOT NULL,
  `name` varchar(191) NOT NULL,
  `items` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_menus`
--

INSERT INTO `rose_menus` (`id`, `type`, `name`, `items`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'backend', 'Backend Sidebar Menu', '[{\"view_permission_id\":\"view-access-management\",\"icon\":\"fa-users\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"\",\"name\":\"Access Management\",\"id\":11,\"content\":\"Access Management\",\"children\":[{\"view_permission_id\":\"view-user-management\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.access.user.index\",\"name\":\"Companies Management\",\"id\":12,\"content\":\"Companies  Management\"},{\"view_permission_id\":\"view-role-management\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.access.role.index\",\"name\":\"Role Management\",\"id\":13,\"content\":\"Role Management\"},{\"view_permission_id\":\"view-permission-management\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.access.permission.index\",\"name\":\"Permission Management\",\"id\":14,\"content\":\"Permission Management\"}]},{\"view_permission_id\":\"view-page\",\"icon\":\"fa-file-text\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.pages.index\",\"name\":\"Pages\",\"id\":2,\"content\":\"Pages\"},{\"view_permission_id\":\"view-plans-permission\",\"icon\":\"fa-money\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.plans.index\",\"name\":\"Plans\",\"id\":20,\"content\":\"Plans\"},{\"view_permission_id\":\"edit-settings\",\"icon\":\"fa-gear\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.settings.edit?setting=1\",\"name\":\"Settings\",\"id\":9,\"content\":\"Settings\"},{\"view_permission_id\":\"view-blog\",\"icon\":\"fa-commenting\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"\",\"name\":\"Blog Management\",\"id\":15,\"content\":\"Blog Management\",\"children\":[{\"view_permission_id\":\"view-blog-category\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.blogCategories.index\",\"name\":\"Blog Category Management\",\"id\":16,\"content\":\"Blog Category Management\"},{\"view_permission_id\":\"view-blog-tag\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.blogTags.index\",\"name\":\"Blog Tag Management\",\"id\":17,\"content\":\"Blog Tag Management\"},{\"view_permission_id\":\"view-blog\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.blogs.index\",\"name\":\"Blog Management\",\"id\":18,\"content\":\"Blog Management\"}]},{\"view_permission_id\":\"view-faq\",\"icon\":\"fa-question-circle\",\"open_in_new_tab\":0,\"url_type\":\"route\",\"url\":\"admin.faqs.index\",\"name\":\"Faq Management\",\"id\":19,\"content\":\"Faq Management\"}]', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_messages`
--

CREATE TABLE `rose_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `thread_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_meta_entries`
--

CREATE TABLE `rose_meta_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rel_type` int(2) UNSIGNED NOT NULL DEFAULT 0,
  `rel_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `value` varchar(255) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_migrations`
--

CREATE TABLE `rose_migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_miscs`
--

CREATE TABLE `rose_miscs` (
  `id` int(11) UNSIGNED NOT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#0b97c4',
  `section` int(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_miscs`
--

INSERT INTO `rose_miscs` (`id`, `ins`, `name`, `color`, `section`, `created_at`, `updated_at`) VALUES
(1, 1, 'Done', '#12C538', 2, NULL, NULL),
(2, 1, 'Due', '#FF0000', 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_notes`
--

CREATE TABLE `rose_notes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `section` tinyint(1) DEFAULT 0,
  `ins` int(4) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_notifications`
--

CREATE TABLE `rose_notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_orders`
--

CREATE TABLE `rose_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tid` bigint(20) NOT NULL,
  `invoicedate` date NOT NULL,
  `invoiceduedate` date NOT NULL,
  `subtotal` decimal(16,4) DEFAULT 0.0000,
  `shipping` decimal(16,4) DEFAULT 0.0000,
  `ship_tax` decimal(16,4) DEFAULT 0.0000,
  `ship_tax_type` enum('inclusive','exclusive','off','none') DEFAULT 'off',
  `ship_tax_rate` decimal(16,4) DEFAULT 0.0000,
  `discount` decimal(16,4) DEFAULT 0.0000,
  `extra_discount` decimal(16,4) DEFAULT 0.0000,
  `discount_rate` decimal(10,4) DEFAULT 0.0000,
  `tax` decimal(16,4) DEFAULT 0.0000,
  `total` decimal(16,4) DEFAULT 0.0000,
  `pmethod` varchar(14) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `status` enum('paid','due','canceled','partial') NOT NULL DEFAULT 'due',
  `customer_id` bigint(20) NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL,
  `pamnt` decimal(16,4) DEFAULT 0.0000,
  `items` decimal(10,4) NOT NULL,
  `tax_format` enum('exclusive','inclusive','off','cgst','igst') NOT NULL DEFAULT 'exclusive',
  `tax_id` bigint(20) DEFAULT 0,
  `discount_format` enum('%','flat','b_flat','b_per') NOT NULL DEFAULT '%',
  `refer` varchar(20) DEFAULT NULL,
  `term_id` bigint(20) UNSIGNED DEFAULT NULL,
  `currency` int(4) DEFAULT NULL,
  `i_class` int(1) NOT NULL DEFAULT 0,
  `r_time` varchar(10) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_order_items`
--

CREATE TABLE `rose_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `product_name` varchar(255) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `product_qty` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `product_price` decimal(16,4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `product_tax` decimal(16,4) DEFAULT 0.0000,
  `product_discount` decimal(16,4) DEFAULT 0.0000,
  `product_subtotal` decimal(16,4) DEFAULT 0.0000,
  `total_tax` decimal(16,4) DEFAULT 0.0000,
  `total_discount` decimal(16,4) DEFAULT 0.0000,
  `product_des` text DEFAULT NULL,
  `i_class` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `unit` varchar(5) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_pages`
--

CREATE TABLE `rose_pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `page_slug` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `cannonical_link` varchar(191) DEFAULT NULL,
  `seo_title` varchar(191) DEFAULT NULL,
  `seo_keyword` varchar(191) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED NOT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_pages`
--

INSERT INTO `rose_pages` (`id`, `title`, `page_slug`, `description`, `cannonical_link`, `seo_title`, `seo_keyword`, `seo_description`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Terms and conditions', 'terms-and-conditions', '<h2><strong>Terms and Conditions</strong></h2>\r\n<p>Welcome to Rose billing!</p>\r\n<p>These terms and conditions outline the rules and regulations for the use of Rose Billing\'s Website, located at ultimatekode.com.</p>\r\n<p>By accessing this website we assume you accept these terms and conditions. Do not continue to use Rose billing if you do not agree to take all of the terms and conditions stated on this page.</p>\r\n<p>The following terminology applies to these Terms and Conditions, Privacy Statement and Disclaimer Notice and all Agreements: \"Client\", \"You\" and \"Your\" refers to you, the person log on this website and compliant to the Company&rsquo;s terms and conditions. \"The Company\", \"Ourselves\", \"We\", \"Our\" and \"Us\", refers to our Company. \"Party\", \"Parties\", or \"Us\", refers to both the Client and ourselves. All terms refer to the offer, acceptance and consideration of payment necessary to undertake the process of our assistance to the Client in the most appropriate manner for the express purpose of meeting the Client&rsquo;s needs in respect of provision of the Company&rsquo;s stated services, in accordance with and subject to, prevailing law of Netherlands. Any use of the above terminology or other words in the singular, plural, capitalization and/or he/she or they, are taken as interchangeable and therefore as referring to same.</p>\r\n<h3><strong>Cookies</strong></h3>\r\n<p>We employ the use of cookies. By accessing Rose billing, you agreed to use cookies in agreement with the Rose Billing\'s Privacy Policy.</p>\r\n<p>Most interactive websites use cookies to let us retrieve the user&rsquo;s details for each visit. Cookies are used by our website to enable the functionality of certain areas to make it easier for people visiting our website. Some of our affiliate/advertising partners may also use cookies.</p>\r\n<h3><strong>License</strong></h3>\r\n<p>Unless otherwise stated, Rose Billing and/or its licensors own the intellectual property rights for all material on Rose billing. All intellectual property rights are reserved. You may access this from Rose billing for your own personal use subjected to restrictions set in these terms and conditions.</p>\r\n<p>You must not:</p>\r\n<ul>\r\n<li>Republish material from Rose billing</li>\r\n<li>Sell, rent or sub-license material from Rose billing</li>\r\n<li>Reproduce, duplicate or copy material from Rose billing</li>\r\n<li>Redistribute content from Rose billing</li>\r\n</ul>\r\n<p>This Agreement shall begin on the date hereof.</p>\r\n<p>Parts of this website offer an opportunity for users to post and exchange opinions and information in certain areas of the website. Rose Billing does not filter, edit, publish or review Comments prior to their presence on the website. Comments do not reflect the views and opinions of Rose Billing,its agents and/or affiliates. Comments reflect the views and opinions of the person who post their views and opinions. To the extent permitted by applicable laws, Rose Billing shall not be liable for the Comments or for any liability, damages or expenses caused and/or suffered as a result of any use of and/or posting of and/or appearance of the Comments on this website.</p>\r\n<p>Rose Billing reserves the right to monitor all Comments and to remove any Comments which can be considered inappropriate, offensive or causes breach of these Terms and Conditions.</p>\r\n<p>You warrant and represent that:</p>\r\n<ul>\r\n<li>You are entitled to post the Comments on our website and have all necessary licenses and consents to do so;</li>\r\n<li>The Comments do not invade any intellectual property right, including without limitation copyright, patent or trademark of any third party;</li>\r\n<li>The Comments do not contain any defamatory, libelous, offensive, indecent or otherwise unlawful material which is an invasion of privacy</li>\r\n<li>The Comments will not be used to solicit or promote business or custom or present commercial activities or unlawful activity.</li>\r\n</ul>\r\n<p>You hereby grant Rose Billing a non-exclusive license to use, reproduce, edit and authorize others to use, reproduce and edit any of your Comments in any and all forms, formats or media.</p>\r\n<h3><strong>Hyperlinking to our Content</strong></h3>\r\n<p>The following organizations may link to our Website without prior written approval:</p>\r\n<ul>\r\n<li>Government agencies;</li>\r\n<li>Search engines;</li>\r\n<li>News organizations;</li>\r\n<li>Online directory distributors may link to our Website in the same manner as they hyperlink to the Websites of other listed businesses; and</li>\r\n<li>System wide Accredited Businesses except soliciting non-profit organizations, charity shopping malls, and charity fundraising groups which may not hyperlink to our Web site.</li>\r\n</ul>\r\n<p>These organizations may link to our home page, to publications or to other Website information so long as the link: (a) is not in any way deceptive; (b) does not falsely imply sponsorship, endorsement or approval of the linking party and its products and/or services; and (c) fits within the context of the linking party&rsquo;s site.</p>\r\n<p>We may consider and approve other link requests from the following types of organizations:</p>\r\n<ul>\r\n<li>commonly-known consumer and/or business information sources;</li>\r\n<li>dot.com community sites;</li>\r\n<li>associations or other groups representing charities;</li>\r\n<li>online directory distributors;</li>\r\n<li>internet portals;</li>\r\n<li>accounting, law and consulting firms; and</li>\r\n<li>educational institutions and trade associations.</li>\r\n</ul>\r\n<p>We will approve link requests from these organizations if we decide that: (a) the link would not make us look unfavorably to ourselves or to our accredited businesses; (b) the organization does not have any negative records with us; (c) the benefit to us from the visibility of the hyperlink compensates the absence of Rose Billing; and (d) the link is in the context of general resource information.</p>\r\n<p>These organizations may link to our home page so long as the link: (a) is not in any way deceptive; (b) does not falsely imply sponsorship, endorsement or approval of the linking party and its products or services; and (c) fits within the context of the linking party&rsquo;s site.</p>\r\n<p>If you are one of the organizations listed in paragraph 2 above and are interested in linking to our website, you must inform us by sending an e-mail to Rose Billing. Please include your name, your organization name, contact information as well as the URL of your site, a list of any URLs from which you intend to link to our Website, and a list of the URLs on our site to which you would like to link. Wait 2-3 weeks for a response.</p>\r\n<p>Approved organizations may hyperlink to our Website as follows:</p>\r\n<ul>\r\n<li>By use of our corporate name; or</li>\r\n<li>By use of the uniform resource locator being linked to; or</li>\r\n<li>By use of any other description of our Website being linked to that makes sense within the context and format of content on the linking party&rsquo;s site.</li>\r\n</ul>\r\n<p>No use of Rose Billing\'s logo or other artwork will be allowed for linking absent a trademark license agreement.</p>\r\n<h3><strong>iFrames</strong></h3>\r\n<p>Without prior approval and written permission, you may not create frames around our Webpages that alter in any way the visual presentation or appearance of our Website.</p>\r\n<h3><strong>Content Liability</strong></h3>\r\n<p>We shall not be hold responsible for any content that appears on your Website. You agree to protect and defend us against all claims that is rising on your Website. No link(s) should appear on any Website that may be interpreted as libelous, obscene or criminal, or which infringes, otherwise violates, or advocates the infringement or other violation of, any third party rights.</p>\r\n<h3><strong>Your Privacy</strong></h3>\r\n<p>Please read Privacy Policy</p>\r\n<h3><strong>Reservation of Rights</strong></h3>\r\n<p>We reserve the right to request that you remove all links or any particular link to our Website. You approve to immediately remove all links to our Website upon request. We also reserve the right to amen these terms and conditions and it&rsquo;s linking policy at any time. By continuously linking to our Website, you agree to be bound to and follow these linking terms and conditions.</p>\r\n<h3><strong>Removal of links from our website</strong></h3>\r\n<p>If you find any link on our Website that is offensive for any reason, you are free to contact and inform us any moment. We will consider requests to remove links but we are not obligated to or so or to respond to you directly.</p>\r\n<p>We do not ensure that the information on this website is correct, we do not warrant its completeness or accuracy; nor do we promise to ensure that the website remains available or that the material on the website is kept up to date.</p>\r\n<h3><strong>Disclaimer</strong></h3>\r\n<p>To the maximum extent permitted by applicable law, we exclude all representations, warranties and conditions relating to our website and the use of this website. Nothing in this disclaimer will:</p>\r\n<ul>\r\n<li>limit or exclude our or your liability for death or personal injury;</li>\r\n<li>limit or exclude our or your liability for fraud or fraudulent misrepresentation;</li>\r\n<li>limit any of our or your liabilities in any way that is not permitted under applicable law; or</li>\r\n<li>exclude any of our or your liabilities that may not be excluded under applicable law.</li>\r\n</ul>\r\n<p>The limitations and prohibitions of liability set in this Section and elsewhere in this disclaimer: (a) are subject to the preceding paragraph; and (b) govern all liabilities arising under the disclaimer, including liabilities arising in contract, in tort and for breach of statutory duty.</p>\r\n<p>As long as the website and the information and services on the website are provided free of charge, we will not be liable for any loss or damage of any nature.</p>', NULL, NULL, NULL, NULL, 1, 1, 1, '2020-05-27 11:22:53', '2020-05-27 11:22:53', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_participants`
--

CREATE TABLE `rose_participants` (
  `id` int(10) UNSIGNED NOT NULL,
  `thread_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `last_read` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_password_resets`
--

CREATE TABLE `rose_password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_permissions`
--

CREATE TABLE `rose_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `display_name` varchar(191) NOT NULL,
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_permissions`
--

INSERT INTO `rose_permissions` (`id`, `name`, `display_name`, `sort`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'billing', 'Admin Billing DashBoard', 0, 1, NULL, NULL, NULL, NULL, NULL),
(2, 'manage-general', 'General Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(3, 'manage-customer', 'Customers View', 0, 1, NULL, NULL, NULL, NULL, NULL),
(4, 'customer-create', 'Customer Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(5, 'edit-customer', 'Customer Edit Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(6, 'delete-customer', 'Customer Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(7, 'service-payment', 'Service Payment', 0, 1, NULL, NULL, NULL, NULL, NULL),
(8, 'manage-customergroup', 'Customergroup Manage Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(9, 'create-customergroup', 'Customergroup Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(10, 'edit-customergroup', 'Customergroup Edit Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(11, 'delete-customergroup', 'Customergroup Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(12, 'manage-warehouse', 'Warehouse Manage Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(13, 'warehouse-data', 'Warehouse Create-Update-Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(14, 'productcategory-manage', 'Product Category View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(15, 'productcategory-data', 'Product Category Create-Update-Deletee Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(16, 'product-manage', 'Products View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(17, 'product-create', 'Product Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(18, 'product-edit', 'Product Edit Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(19, 'product-delete', 'Product Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(20, 'invoice-manage', 'Invoices View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(21, 'invoice-create', 'Invoice Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(22, 'invoice-edit', 'Invoice Edit Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(23, 'invoice-delete', 'Invoice Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(24, 'crm', 'Customer Login', 0, 1, NULL, NULL, NULL, NULL, NULL),
(25, 'account-manage', 'Accounts View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(26, 'account-data', 'Account Create-Update-Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(27, 'transaction-manage', 'Transactions View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(28, 'transaction-data', 'Transactions Create-Update-Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(29, 'manage-hrm', 'Employee Management Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(30, 'department-manage', 'Employee Departments View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(31, 'department-data', 'Employee Department Create-Update-Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(32, 'quote-manage', 'Quotes View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(33, 'quote-create', 'Quote Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(34, 'quote-edit', 'Quote Edit Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(35, 'quote-delete', 'Quote Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(36, 'purchaseorder-manage', 'Purchase Order View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(37, 'purchaseorder-data', 'Purchase Order Create-Update-Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(38, 'supplier-manage', 'Suppliers View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(39, 'supplier-data', 'Supplier Create-Update-Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(40, 'dashboard-owner', 'Dashboard Business Owner', 0, 1, NULL, NULL, NULL, NULL, NULL),
(41, 'dashboard-stock', 'Extra 2', 0, 1, NULL, NULL, NULL, NULL, NULL),
(42, 'dashboard-self', 'Extra 1', 0, 1, NULL, NULL, NULL, NULL, NULL),
(43, 'reports-statements', 'Reports & Statements', 0, 1, NULL, NULL, NULL, NULL, NULL),
(44, 'stockreturn-manage', 'Stock Returns View Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(45, 'stockreturn-data', 'Stock Return Create-Update-Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(46, 'creditnote-manage', 'Credit Notes View', 0, 1, NULL, NULL, NULL, NULL, NULL),
(47, 'data-creditnote', 'Credit Note Create-Update-Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(48, 'stocktransfer', 'Stock Transfer Management', 0, 1, NULL, NULL, NULL, NULL, NULL),
(49, 'business_settings', 'Business Admin Settings', 0, 1, NULL, NULL, NULL, NULL, NULL),
(50, 'task-manage', 'Task Manage Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(51, 'task-create', 'Task Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(52, 'task-edit', 'Task Edit Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(53, 'task-delete', 'Task Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(54, 'misc-manage', 'Tags & Status Manage', 0, 1, NULL, NULL, NULL, NULL, NULL),
(55, 'misc-create', 'Tags & Status Create Misc Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(56, 'misc-data', 'Tags & Status - Edit Delete', 0, 1, NULL, NULL, NULL, NULL, NULL),
(57, 'project-manage', 'Project Manage Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(58, 'project-create', 'Project Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(59, 'project-edit', 'Project Edit Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(60, 'project-delete', 'Project Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(61, 'note-manage', 'Note Manage Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(62, 'note-create', 'Note Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(63, 'note-data', 'Note Edit Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(64, 'manage-event', 'Event Manage Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(65, 'create-event', 'Event Create Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(66, 'edit-event', 'Event Edit Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(67, 'delete-event', 'Event Delete Permission', 0, 1, NULL, NULL, NULL, NULL, NULL),
(68, 'communication', 'Send Email & SMS', 0, 1, NULL, NULL, NULL, NULL, NULL),
(69, 'make-payment', 'Make Receive Payments', 0, 1, NULL, NULL, NULL, NULL, NULL),
(70, 'wallet', 'Customer Wallet', 0, 1, NULL, NULL, NULL, NULL, NULL),
(71, 'product_search', 'Product Search', 0, 1, NULL, NULL, NULL, NULL, NULL),
(72, 'pos', 'POS Screen', 0, 1, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_permission_role`
--

CREATE TABLE `rose_permission_role` (
  `id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_permission_role`
--

INSERT INTO `rose_permission_role` (`id`, `permission_id`, `role_id`) VALUES
(1, 1, 2),
(2, 2, 2),
(3, 3, 2),
(4, 4, 2),
(5, 5, 2),
(6, 6, 2),
(7, 7, 2),
(8, 8, 2),
(9, 9, 2),
(10, 10, 2),
(11, 11, 2),
(12, 12, 2),
(13, 13, 2),
(14, 14, 2),
(15, 15, 2),
(16, 16, 2),
(17, 17, 2),
(18, 18, 2),
(19, 19, 2),
(20, 20, 2),
(21, 21, 2),
(22, 22, 2),
(23, 23, 2),
(24, 24, 2),
(25, 25, 2),
(26, 26, 2),
(27, 27, 2),
(28, 28, 2),
(29, 29, 2),
(30, 30, 2),
(31, 31, 2),
(32, 32, 2),
(33, 33, 2),
(34, 34, 2),
(35, 35, 2),
(36, 36, 2),
(37, 37, 2),
(38, 38, 2),
(39, 39, 2),
(40, 40, 2),
(41, 41, 2),
(42, 42, 2),
(43, 43, 2),
(44, 44, 2),
(45, 45, 2),
(46, 46, 2),
(47, 47, 2),
(48, 48, 2),
(49, 49, 2),
(50, 50, 2),
(51, 51, 2),
(52, 52, 2),
(53, 53, 2),
(54, 54, 2),
(55, 55, 2),
(56, 56, 2),
(57, 57, 2),
(58, 58, 2),
(59, 59, 2),
(60, 60, 2),
(61, 61, 2),
(62, 62, 2),
(63, 63, 2),
(64, 64, 2),
(65, 65, 2),
(66, 66, 2),
(67, 67, 2),
(68, 68, 2),
(69, 69, 2),
(70, 70, 2),
(71, 71, 2),
(72, 72, 2),
(73, 2, 3),
(74, 3, 3),
(75, 4, 3),
(76, 5, 3),
(77, 6, 3),
(78, 8, 3),
(79, 9, 3),
(80, 10, 3),
(81, 11, 3),
(82, 12, 3),
(83, 13, 3),
(84, 14, 3),
(85, 15, 3),
(86, 16, 3),
(87, 17, 3),
(88, 18, 3),
(89, 19, 3),
(90, 20, 3),
(91, 21, 3),
(92, 22, 3),
(93, 23, 3),
(94, 24, 3),
(95, 25, 3),
(96, 26, 3),
(97, 27, 3),
(98, 28, 3),
(99, 29, 3),
(100, 30, 3),
(101, 31, 3),
(102, 32, 3),
(103, 33, 3),
(104, 34, 3),
(105, 35, 3),
(106, 36, 3),
(107, 37, 3),
(108, 38, 3),
(109, 39, 3),
(110, 40, 3),
(111, 43, 3),
(112, 44, 3),
(113, 45, 3),
(114, 46, 3),
(115, 47, 3),
(116, 48, 3),
(117, 49, 3),
(118, 50, 3),
(119, 51, 3),
(120, 52, 3),
(121, 53, 3),
(122, 54, 3),
(123, 55, 3),
(124, 56, 3),
(125, 57, 3),
(126, 58, 3),
(127, 59, 3),
(128, 60, 3),
(129, 61, 3),
(130, 62, 3),
(131, 63, 3),
(132, 64, 3),
(133, 65, 3),
(134, 66, 3),
(135, 67, 3),
(136, 68, 3),
(137, 69, 3),
(138, 70, 3),
(139, 71, 3),
(140, 72, 3),
(142, 20, 4),
(143, 21, 4),
(144, 22, 4),
(145, 23, 4),
(146, 72, 4);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_permission_user`
--

CREATE TABLE `rose_permission_user` (
  `id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_permission_user`
--

INSERT INTO `rose_permission_user` (`id`, `permission_id`, `user_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 1),
(6, 6, 1),
(7, 7, 1),
(8, 8, 1),
(9, 9, 1),
(10, 10, 1),
(11, 11, 1),
(12, 12, 1),
(13, 13, 1),
(14, 14, 1),
(15, 15, 1),
(16, 16, 1),
(17, 17, 1),
(18, 18, 1),
(19, 19, 1),
(20, 20, 1),
(21, 21, 1),
(22, 22, 1),
(23, 23, 1),
(24, 24, 1),
(25, 25, 1),
(26, 26, 1),
(27, 27, 1),
(28, 28, 1),
(29, 29, 1),
(30, 30, 1),
(31, 31, 1),
(32, 32, 1),
(33, 33, 1),
(34, 34, 1),
(35, 35, 1),
(36, 36, 1),
(37, 37, 1),
(38, 38, 1),
(39, 39, 1),
(40, 40, 1),
(41, 41, 1),
(42, 42, 1),
(43, 43, 1),
(44, 44, 1),
(45, 45, 1),
(46, 46, 1),
(47, 47, 1),
(48, 48, 1),
(49, 49, 1),
(50, 50, 1),
(51, 51, 1),
(52, 52, 1),
(53, 53, 1),
(54, 54, 1),
(55, 55, 1),
(56, 56, 1),
(57, 57, 1),
(58, 58, 1),
(59, 59, 1),
(60, 60, 1),
(61, 61, 1),
(62, 62, 1),
(63, 63, 1),
(64, 64, 1),
(65, 65, 1),
(66, 66, 1),
(67, 67, 1),
(68, 68, 1),
(69, 69, 1),
(70, 70, 1),
(71, 71, 1),
(72, 72, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_prefixes`
--

CREATE TABLE `rose_prefixes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ins` int(4) UNSIGNED NOT NULL,
  `class` int(2) UNSIGNED NOT NULL DEFAULT 1,
  `value` varchar(10) DEFAULT NULL,
  `note` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_prefixes`
--

INSERT INTO `rose_prefixes` (`id`, `ins`, `class`, `value`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'INV', 'invoice', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(4, 1, 2, 'DO', 'delivery_note', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(5, 1, 3, 'PRO', 'proforma_invoice', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(6, 1, 4, 'REC', 'payment_receipt', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(16, 1, 5, 'QT', 'quotes', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(17, 1, 6, 'SUB', 'subscriptions', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(18, 1, 7, 'CN', 'credit_note', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(19, 1, 8, 'SR', 'stock_return', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(20, 1, 9, 'PO', 'purchase_order', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(30, 1, 10, 'POS', 'POS', '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_products`
--

CREATE TABLE `rose_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `productcategory_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(80) NOT NULL,
  `taxrate` decimal(16,4) DEFAULT 0.0000,
  `product_des` text DEFAULT NULL,
  `unit` varchar(4) DEFAULT NULL,
  `code_type` varchar(8) DEFAULT 'EAN13',
  `sub_cat_id` int(11) UNSIGNED DEFAULT 0,
  `brand_id` int(11) UNSIGNED DEFAULT 0,
  `stock_type` int(1) UNSIGNED DEFAULT 1,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_product_categories`
--

CREATE TABLE `rose_product_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `extra` varchar(255) DEFAULT NULL,
  `c_type` int(2) UNSIGNED DEFAULT 0,
  `rel_id` int(11) UNSIGNED DEFAULT 0,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_product_meta`
--

CREATE TABLE `rose_product_meta` (
  `id` bigint(20) NOT NULL,
  `rel_type` int(2) UNSIGNED NOT NULL DEFAULT 0,
  `rel_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ref_id` bigint(20) UNSIGNED DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `value2` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_product_variables`
--

CREATE TABLE `rose_product_variables` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `code` varchar(5) NOT NULL,
  `type` int(1) NOT NULL,
  `val` decimal(16,4) DEFAULT NULL,
  `rid` int(11) NOT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_product_variations`
--

CREATE TABLE `rose_product_variations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT 0,
  `parent_id` bigint(20) UNSIGNED DEFAULT 0,
  `variation_class` int(1) UNSIGNED DEFAULT 0,
  `name` varchar(250) DEFAULT NULL,
  `warehouse_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `code` varchar(30) DEFAULT NULL,
  `price` decimal(16,4) DEFAULT 0.0000,
  `purchase_price` decimal(16,4) DEFAULT 0.0000,
  `disrate` decimal(16,4) DEFAULT 0.0000,
  `qty` decimal(10,4) NOT NULL,
  `alert` int(11) UNSIGNED DEFAULT NULL,
  `image` varchar(120) DEFAULT 'default.png',
  `barcode` varchar(16) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_projects`
--

CREATE TABLE `rose_projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `priority` enum('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  `progress` int(3) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` datetime DEFAULT current_timestamp(),
  `end_date` datetime DEFAULT NULL,
  `phase` varchar(255) DEFAULT NULL,
  `short_desc` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `worth` decimal(16,4) DEFAULT 0.0000,
  `project_share` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_project_logs`
--

CREATE TABLE `rose_project_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `value` varchar(250) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_project_meta`
--

CREATE TABLE `rose_project_meta` (
  `id` int(11) NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `meta_key` int(11) NOT NULL,
  `meta_data` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `key3` varchar(255) DEFAULT NULL,
  `key4` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_project_milestones`
--

CREATE TABLE `rose_project_milestones` (
  `id` int(11) NOT NULL,
  `project_id` bigint(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_project_relations`
--

CREATE TABLE `rose_project_relations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `related` int(11) UNSIGNED NOT NULL,
  `rid` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_purchase_orders`
--

CREATE TABLE `rose_purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tid` bigint(20) UNSIGNED NOT NULL,
  `invoicedate` date NOT NULL,
  `invoiceduedate` date NOT NULL,
  `subtotal` decimal(16,4) DEFAULT 0.0000,
  `shipping` decimal(16,4) DEFAULT 0.0000,
  `ship_tax` decimal(16,4) DEFAULT 0.0000,
  `ship_tax_type` enum('inclusive','exclusive','off','none') DEFAULT 'off',
  `ship_tax_rate` decimal(16,4) DEFAULT 0.0000,
  `discount` decimal(16,4) DEFAULT 0.0000,
  `extra_discount` decimal(16,4) DEFAULT 0.0000,
  `discount_rate` decimal(10,4) DEFAULT 0.0000,
  `tax` decimal(16,4) DEFAULT 0.0000,
  `total` decimal(16,4) DEFAULT 0.0000,
  `pmethod` varchar(14) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `status` enum('paid','due','canceled','partial') NOT NULL DEFAULT 'due',
  `supplier_id` bigint(20) NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL,
  `pamnt` decimal(16,4) DEFAULT 0.0000,
  `items` decimal(10,4) NOT NULL,
  `tax_format` enum('exclusive','inclusive','off','cgst','igst') NOT NULL DEFAULT 'exclusive',
  `tax_id` bigint(20) UNSIGNED DEFAULT 0,
  `discount_format` enum('%','flat','b_flat','b_per') NOT NULL DEFAULT '%',
  `refer` varchar(20) DEFAULT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL,
  `currency` int(4) UNSIGNED DEFAULT NULL,
  `i_class` int(1) UNSIGNED DEFAULT 0,
  `r_time` varchar(10) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_purchase_order_items`
--

CREATE TABLE `rose_purchase_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bill_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `product_name` varchar(255) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `product_qty` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `product_price` decimal(16,4) NOT NULL DEFAULT 0.0000,
  `product_tax` decimal(16,4) DEFAULT 0.0000,
  `product_discount` decimal(16,4) DEFAULT 0.0000,
  `product_subtotal` decimal(16,4) DEFAULT 0.0000,
  `total_tax` decimal(16,4) DEFAULT 0.0000,
  `total_discount` decimal(16,4) DEFAULT 0.0000,
  `product_des` text DEFAULT NULL,
  `i_class` int(1) UNSIGNED DEFAULT 0,
  `unit` varchar(5) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_quotes`
--

CREATE TABLE `rose_quotes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tid` bigint(20) UNSIGNED NOT NULL,
  `invoicedate` date NOT NULL,
  `invoiceduedate` date NOT NULL,
  `subtotal` decimal(16,4) DEFAULT 0.0000,
  `shipping` decimal(16,4) DEFAULT 0.0000,
  `ship_tax` decimal(16,4) DEFAULT 0.0000,
  `ship_tax_type` enum('inclusive','exclusive','off','none') DEFAULT 'off',
  `ship_tax_rate` decimal(16,4) DEFAULT 0.0000,
  `discount` decimal(16,4) DEFAULT 0.0000,
  `extra_discount` decimal(16,4) DEFAULT 0.0000,
  `discount_rate` decimal(10,4) DEFAULT 0.0000,
  `tax` decimal(16,4) DEFAULT 0.0000,
  `total` decimal(16,4) DEFAULT 0.0000,
  `pmethod` varchar(14) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `status` enum('approved','canceled','pending','client_approved') NOT NULL DEFAULT 'pending',
  `customer_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL,
  `pamnt` decimal(16,4) DEFAULT 0.0000,
  `items` decimal(10,4) NOT NULL,
  `tax_format` enum('exclusive','inclusive','off','cgst','igst') NOT NULL DEFAULT 'exclusive',
  `tax_id` bigint(20) UNSIGNED DEFAULT 0,
  `discount_format` enum('%','flat','b_flat','b_per') NOT NULL DEFAULT '%',
  `refer` varchar(20) DEFAULT NULL,
  `term_id` bigint(20) UNSIGNED DEFAULT NULL,
  `currency` int(4) UNSIGNED DEFAULT NULL,
  `i_class` int(1) UNSIGNED DEFAULT 0,
  `r_time` varchar(10) DEFAULT NULL,
  `proposal` text DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_quote_items`
--

CREATE TABLE `rose_quote_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quote_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `product_name` varchar(255) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `product_qty` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `product_price` decimal(16,4) NOT NULL DEFAULT 0.0000,
  `product_tax` decimal(16,4) DEFAULT 0.0000,
  `product_discount` decimal(16,4) DEFAULT 0.0000,
  `product_subtotal` decimal(16,4) DEFAULT 0.0000,
  `total_tax` decimal(16,4) DEFAULT 0.0000,
  `total_discount` decimal(16,4) DEFAULT 0.0000,
  `product_des` text DEFAULT NULL,
  `i_class` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `unit` varchar(5) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_registers`
--

CREATE TABLE `rose_registers` (
  `id` bigint(20) NOT NULL,
  `ins` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `data` varchar(800) DEFAULT NULL,
  `data1` varchar(500) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_roles`
--

CREATE TABLE `rose_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `all` tinyint(1) NOT NULL DEFAULT 0,
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `ins` int(4) UNSIGNED DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_roles`
--

INSERT INTO `rose_roles` (`id`, `name`, `all`, `sort`, `status`, `ins`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Business Owner User', 0, 2, 0, NULL, 1, 1, NULL, NULL, NULL),
(3, 'Business Employee - Manager', 0, 3, 0, NULL, 1, 1, NULL, NULL, NULL),
(4, 'Business Employee - Sales Manager', 0, 5, 0, NULL, 1, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_role_user`
--

CREATE TABLE `rose_role_user` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_role_user`
--

INSERT INTO `rose_role_user` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_sessions`
--

CREATE TABLE `rose_sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_settings`
--

CREATE TABLE `rose_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `logo` varchar(191) DEFAULT NULL,
  `favicon` varchar(191) DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `seo_title` varchar(191) DEFAULT NULL,
  `seo_keyword` mediumtext DEFAULT NULL,
  `seo_description` mediumtext DEFAULT NULL,
  `company_contact` varchar(191) DEFAULT NULL,
  `company_address` mediumtext DEFAULT NULL,
  `from_name` varchar(191) DEFAULT NULL,
  `from_email` varchar(191) DEFAULT NULL,
  `facebook` varchar(191) DEFAULT NULL,
  `twitter` varchar(191) DEFAULT NULL,
  `google` varchar(191) DEFAULT NULL,
  `copyright_text` varchar(191) DEFAULT NULL,
  `footer_text` varchar(400) DEFAULT NULL,
  `google_analytics` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_settings`
--

INSERT INTO `rose_settings` (`id`, `logo`, `favicon`, `title`, `seo_title`, `seo_keyword`, `seo_description`, `company_contact`, `company_address`, `from_name`, `from_email`, `facebook`, `twitter`, `google`, `copyright_text`, `footer_text`, `google_analytics`, `created_at`, `updated_at`) VALUES
(1, '1586706120preview-b.jpg', NULL, 'Abc Company', 'Abc Company System', 'POS, Rose, ultimatekode', 'Rose POS is a beautifully crafted Point Of Sale application. The application is packed with a ton of features', 'info@test.com', 'Madalinskiego 8, 78 VRB, Country', 'Abc Company', 'support@example.com', 'https://fb.com', 'https://twitter.com', 'https://youtube.com', 'Company Copyright', 'Rose Business Suite is a premium and feature rich billing application . The application is developed over Laravel framework.', '', '2020-05-27 11:22:53', '2020-05-27 11:22:53');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_sms_settings`
--

CREATE TABLE `rose_sms_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `active` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `driver_id` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `driver` varchar(50) NOT NULL DEFAULT 'Twilio',
  `username` varchar(400) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `sender` varchar(100) DEFAULT NULL,
  `data` varchar(400) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_sms_settings`
--

INSERT INTO `rose_sms_settings` (`id`, `active`, `driver_id`, `driver`, `username`, `password`, `sender`, `data`, `ins`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Twilio', 'ACe2be01b8ba6b577b8d6d25209285a394', 'dad2dfa6a58875d40c31d9769c2c4c83', '2564004242', 'fgfd', 1, '2020-05-27 18:22:54', '2020-06-03 02:39:00');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_suppliers`
--

CREATE TABLE `rose_suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `region` varchar(30) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postbox` varchar(20) DEFAULT NULL,
  `email` varchar(90) NOT NULL,
  `picture` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `taxid` varchar(100) DEFAULT NULL,
  `balance` float(16,2) DEFAULT 0.00,
  `docid` varchar(255) DEFAULT NULL,
  `custom1` varchar(255) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `active` int(1) UNSIGNED NOT NULL DEFAULT 1,
  `password` varchar(191) DEFAULT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_suppliers`
--

INSERT INTO `rose_suppliers` (`id`, `employee_id`, `name`, `phone`, `address`, `city`, `region`, `country`, `postbox`, `email`, `picture`, `company`, `taxid`, `balance`, `docid`, `custom1`, `ins`, `active`, `password`, `role_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 0, 'MILLION', '252333', '11', 'Rr', 'AA', 'United States', '', 'sales@aol.com', '', 'Barter Post', '', 0.00, '', '', 1, 1, NULL, 0, NULL, '2020-05-31 02:59:54', '2020-05-31 02:59:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_templates`
--

CREATE TABLE `rose_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ins` int(4) UNSIGNED NOT NULL,
  `title` varchar(250) NOT NULL,
  `body` text NOT NULL,
  `category` int(11) NOT NULL,
  `other` int(11) DEFAULT NULL,
  `info` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_templates`
--

INSERT INTO `rose_templates` (`id`, `ins`, `title`, `body`, `category`, `other`, `info`, `created_at`, `updated_at`) VALUES
(1, 1, '[{Company}] Invoice #{BillNumber} Generated', '\r\nDear {Name},\r\n\r\n\r\nWe are contacting you in regard to an invoice # {BillNumber} that has been created on your account. You may find the invoice with below link.\r\n\r\nView Invoice\r\n{URL}\r\n\r\nWe look forward to conducting future business with you.\r\n\r\nKind Regards,\r\nTeam\r\n{CompanyDetails}\r\n\r\n', 1, 1, 'invoice_generated', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(2, 1, '[{Company}] Invoice #{BillNumber} Payment Reminder', '\r\nDear Client,\r\n\r\nWe are contacting you in regard to a payment reminder of invoice # {BillNumber} that has been created on your account. You may find the invoice with below link. Please pay the balance of {Amount} due by {DueDate}.\r\n\r\n\r\n\r\nView Invoice\r\n\r\n{URL}\r\n\r\n\r\n\r\nWe look forward to conducting future business with you.\r\n\r\n\r\n\r\nKind Regards,\r\n\r\n\r\n\r\nTeam\r\n\r\n\r\n\r\n{CompanyDetails}\r\n\r\n', 1, 2, 'invoice_payment_reminder', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(3, 1, '[{Company}] Invoice #{BillNumber} Payment Received', '\r\nDear Client,\r\n\r\n\r\nWe are contacting you in regard to a payment received for invoice  # {BillNumber} that has been created on your account. You can find the invoice with below link.\r\n\r\n\r\n\r\nView Invoice\r\n\r\n\r\n{URL}\r\n\r\n\r\n\r\nWe look forward to conducting future business with you.\r\n\r\n\r\n\r\nKind Regards,\r\n\r\n\r\n\r\nTeam\r\n\r\n\r\n\r\n{CompanyDetails}\r\n\r\n', 1, 3, 'invoice_payment_received', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(4, 1, '{Company} Invoice #{BillNumber} OverDue', '\r\nDear Client,\r\n\r\n\r\nWe are contacting you in regard to an Overdue Notice for invoice # {BillNumber} that has been created on your account. You may find the invoice with below link.\r\nPlease pay the balance of {Amount} due by {DueDate}.\r\n\r\n\r\nView Invoice\r\n\r\n\r\n{URL}\r\n\r\n\r\n\r\nWe look forward to conducting future business with you.\r\n\r\n\r\n\r\nKind Regards,\r\n\r\n\r\n\r\nTeam\r\n\r\n\r\n\r\n{CompanyDetails}\r\n\r\n', 1, 4, 'invoice_payment_overdue', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(5, 1, '{Company} Invoice #{BillNumber} Refund Proceeded', '\r\nDear Client,\r\n\r\n\r\nWe are contacting you in regard to a refund request processed for invoice # {BillNumber} that has been created on your account. You may find the invoice with below link. Please pay the balance of {Amount}  by {DueDate}.\r\n\r\n\r\n\r\nView Invoice\r\n\r\n\r\n{URL}\r\n\r\n\r\n\r\nWe look forward to conducting future business with you.\r\n\r\n\r\n\r\nKind Regards,\r\n\r\n\r\n\r\nTeam\r\n\r\n{CompanyDetails}\r\n\r\n', 1, 5, 'invoice_payment_refund', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(6, 1, 'SMS - New Invoice Notification', 'Dear Customer, new invoice  # {BillNumber} generated. {URL} Regards', 2, 11, 's_invoice_generated', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(7, 1, 'SMS - Invoice Payment Reminder', 'Dear Customer, Please make payment of invoice  # {BillNumber}. {URL} Regards', 2, 12, 's_invoice_payment_reminder', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(8, 1, 'SMS - Invoice Refund Proceeded', 'Dear Customer, Refund generated of invoice # {BillNumber}. {URL} Regards', 2, 15, 's_invoice_payment_refund', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(9, 1, 'SMS - Invoice payment Received', 'Dear Customer, Payment received of invoice # {BillNumber}. {URL} Regards', 2, 13, 's_invoice_payment_received', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(10, 1, 'SMS-Invoice Overdue Notice', 'Dear Customer, Dear Customer,Payment is overdue of invoice # {BillNumber}. {URL} Regards', 2, 14, 's_invoice_payment_overdue', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(161, 1, '[{Company}] Quote #{BillNumber} Generated', '\r\nDear {Name},\r\n\r\n\r\nWe are contacting you in regard to a quote # {BillNumber} that has been created on your account. You may find the quote with the below link.\r\n\r\nView Quote\r\n{URL}\r\n\r\nWe look forward to conducting future business with you.\r\n\r\nKind Regards,\r\nTeam\r\n{CompanyDetails}\r\n\r\n', 4, 6, 'quote_proposal', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(162, 1, 'SMS - New Quote Notification', 'Dear Customer, new Quote  # {BillNumber} generated. {URL} Regards', 4, 16, 's_quote_proposal', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(163, 1, '[{Company}] {BillType} #{BillNumber} Generated', '\r\nDear {Name},\r\n\r\n\r\nWe are contacting you in regard to a {BillType} # {BillNumber} that has been created on your account. You may find the {BillType} with the below link.\r\n\r\nView {BillType}\r\n{URL}\r\n\r\nWe look forward to conducting future business with you.\r\n\r\nKind Regards,\r\nTeam\r\n{CompanyDetails}\r\n\r\n', 5, 7, 'BillType_notification', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(164, 1, 'SMS - New {BillType} Notification', 'Dear Customer, new {BillType} # {BillNumber} generated. {URL} Regards', 5, 17, 's_BillType_notification', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(165, 1, '[{Company}] {BillType} #{BillNumber} Generated', '\r\nDear {Name},\r\n\r\n\r\nWe are contacting you in regard to a {BillType} # {BillNumber} that has been created on your account. You may find the {BillType} with the below link.\r\n\r\nView {BillType}\r\n{URL}\r\n\r\nWe look forward to conducting future business with you.\r\n\r\nKind Regards,\r\nTeam\r\n{CompanyDetails}\r\n\r\n', 9, 8, 'purchase_orders', '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_terms`
--

CREATE TABLE `rose_terms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` int(1) UNSIGNED NOT NULL,
  `terms` text NOT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_terms`
--

INSERT INTO `rose_terms` (`id`, `title`, `type`, `terms`, `ins`, `created_at`, `updated_at`) VALUES
(1, 'Default Term', 0, 'Default Term', 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_threads`
--

CREATE TABLE `rose_threads` (
  `id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_todolists`
--

CREATE TABLE `rose_todolists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `duedate` datetime NOT NULL,
  `short_desc` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `creator_id` int(10) UNSIGNED NOT NULL,
  `priority` enum('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `star` tinyint(1) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_todolist_relations`
--

CREATE TABLE `rose_todolist_relations` (
  `id` bigint(20) NOT NULL,
  `todolist_id` bigint(20) UNSIGNED NOT NULL,
  `related` int(11) UNSIGNED NOT NULL,
  `rid` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_transactions`
--

CREATE TABLE `rose_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `trans_category_id` int(10) UNSIGNED NOT NULL,
  `debit` decimal(16,4) DEFAULT 0.0000,
  `credit` decimal(16,4) DEFAULT 0.0000,
  `payer` varchar(200) DEFAULT NULL,
  `payer_id` bigint(20) UNSIGNED DEFAULT 0,
  `method` varchar(100) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `bill_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `relation_id` int(1) UNSIGNED DEFAULT 0,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_transaction_categories`
--

CREATE TABLE `rose_transaction_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(25) NOT NULL,
  `note` varchar(100) DEFAULT NULL,
  `sub_category` int(1) UNSIGNED DEFAULT 0,
  `sub_category_id` int(20) UNSIGNED DEFAULT 0,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_transaction_categories`
--

INSERT INTO `rose_transaction_categories` (`id`, `name`, `note`, `sub_category`, `sub_category_id`, `ins`, `created_at`, `updated_at`) VALUES
(1, 'Sales Transactions', 'Sales Transactions', 0, 0, 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(2, 'Purchase Transactions', 'Purchase Transactions', 0, 0, 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_transaction_history`
--

CREATE TABLE `rose_transaction_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `party_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL,
  `note` varchar(500) DEFAULT NULL,
  `relation_id` int(1) UNSIGNED DEFAULT 0,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_users`
--

CREATE TABLE `rose_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `confirmation_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `is_term_accept` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = not accepted,1 = accepted',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ins` int(4) UNSIGNED DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `rose_users`
--

INSERT INTO `rose_users` (`id`, `first_name`, `last_name`, `email`, `picture`, `signature`, `password`, `status`, `confirmation_code`, `confirmed`, `is_term_accept`, `remember_token`, `ins`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Null', 'Master', 'nullmaster@babiato.org', NULL, NULL, '$2y$10$1TXgjGlIcgwU5paIVWo85e.g00fcEW.6IbELrL5tCH4LZYADrtZ2m', 1, NULL, 1, 0, 'w3fs4cJvWzlp5OAIYY4wUxv7taAPmXe18ad5poinciNB2dBsAOmuOWkEGWC5', 1, 1, 1, '2020-05-27 18:22:54', '2020-05-27 18:22:54', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_user_gateways`
--

CREATE TABLE `rose_user_gateways` (
  `id` int(5) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `fields` varchar(255) NOT NULL,
  `enable` enum('Yes','No') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Tablo döküm verisi `rose_user_gateways`
--

INSERT INTO `rose_user_gateways` (`id`, `name`, `fields`, `enable`, `created_at`, `updated_at`) VALUES
(1, 'Stripe', '{\"1\":\"key1\",\"2\":\"key2\"}', 'Yes', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(2, 'Paypal', ' {\"1\":\"key1\",\"2\":\"key2\"}', 'Yes', '2020-05-27 18:22:54', '2020-05-27 18:22:54'),
(3, 'Test Gate', '{\"1\":\"key1\",\"2\":\"key2\"}', 'No', '2020-05-27 18:22:54', '2020-05-27 18:22:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_user_gateway_entries`
--

CREATE TABLE `rose_user_gateway_entries` (
  `id` int(5) UNSIGNED NOT NULL,
  `user_gateway_id` int(5) UNSIGNED DEFAULT 0,
  `enable` enum('Yes','No') NOT NULL,
  `key1` varchar(255) NOT NULL,
  `key2` varchar(255) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `dev_mode` enum('true','false') NOT NULL DEFAULT 'true',
  `ord` int(5) UNSIGNED NOT NULL,
  `surcharge` decimal(16,2) UNSIGNED NOT NULL,
  `extra` varchar(40) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Tablo döküm verisi `rose_user_gateway_entries`
--

INSERT INTO `rose_user_gateway_entries` (`id`, `user_gateway_id`, `enable`, `key1`, `key2`, `currency`, `dev_mode`, `ord`, `surcharge`, `extra`, `ins`, `created_at`, `updated_at`) VALUES
(1, 1, 'Yes', 'pk_live_lWfL032jHDMwKF0n0ae3SnqH00r1foV8cR', 'sk_live_XSQllaUEG7wbD0LFZ8vCNhtW00mIqIoW95', 'Str', 'true', 0, '3.00', 'Stripe Credit Card Processing', 1, '2020-06-02 22:43:27', '2020-06-02 22:43:27'),
(2, 2, 'Yes', '', '', '', 'true', 0, '0.00', '', 1, '2020-06-02 22:43:57', '2020-06-02 22:43:57');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_user_profiles`
--

CREATE TABLE `rose_user_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `address_1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `rose_user_profiles`
--

INSERT INTO `rose_user_profiles` (`id`, `user_id`, `address_1`, `city`, `state`, `country`, `postal`, `company`, `contact`, `tax_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'Test Street', 'City', 'State', 'Country', '123456', 'UltimateKode', '07867867867', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rose_warehouses`
--

CREATE TABLE `rose_warehouses` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `extra` varchar(255) DEFAULT NULL,
  `ins` int(4) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablo döküm verisi `rose_warehouses`
--

INSERT INTO `rose_warehouses` (`id`, `title`, `extra`, `ins`, `created_at`, `updated_at`) VALUES
(7, 'WHS35', 'Warehous', 1, '2020-05-31 02:57:48', '2020-05-31 02:57:48');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `rose_accounts`
--
ALTER TABLE `rose_accounts`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `acn` (`number`,`ins`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_additionals`
--
ALTER TABLE `rose_additionals`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_attendances`
--
ALTER TABLE `rose_attendances`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `attendances_fk1` (`ins`) USING BTREE,
  ADD KEY `attendances_fk2` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_banks`
--
ALTER TABLE `rose_banks`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `banks_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_companies`
--
ALTER TABLE `rose_companies`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `rose_config_meta`
--
ALTER TABLE `rose_config_meta`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `feature_id` (`feature_id`,`ins`) USING BTREE,
  ADD KEY `config_meta_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_currencies`
--
ALTER TABLE `rose_currencies`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_customers`
--
ALTER TABLE `rose_customers`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `email` (`email`,`ins`) USING BTREE,
  ADD KEY `name` (`name`) USING BTREE,
  ADD KEY `email_2` (`email`) USING BTREE,
  ADD KEY `phone` (`phone`) USING BTREE,
  ADD KEY `customers_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_customer_groups`
--
ALTER TABLE `rose_customer_groups`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `customer_groups_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_customer_group_entries`
--
ALTER TABLE `rose_customer_group_entries`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `customer_id` (`customer_id`,`customer_group_id`) USING BTREE,
  ADD KEY `customer_group_id` (`customer_group_id`) USING BTREE;

--
-- Tablo için indeksler `rose_custom_entries`
--
ALTER TABLE `rose_custom_entries`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `fid` (`custom_field_id`,`rid`) USING BTREE,
  ADD KEY `custom_entries_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_custom_fields`
--
ALTER TABLE `rose_custom_fields`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `f_module` (`module_id`) USING BTREE,
  ADD KEY `custom_fields_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_departments`
--
ALTER TABLE `rose_departments`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `departments_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_drafts`
--
ALTER TABLE `rose_drafts`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `invoices_fk1` (`ins`) USING BTREE,
  ADD KEY `customer_id` (`customer_id`) USING BTREE,
  ADD KEY `invoices_fk3` (`term_id`) USING BTREE,
  ADD KEY `invoices_fk4` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_draft_items`
--
ALTER TABLE `rose_draft_items`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `i_class` (`i_class`) USING BTREE,
  ADD KEY `invoice_items_fk1` (`ins`) USING BTREE,
  ADD KEY `invoice_items_fk2` (`invoice_id`) USING BTREE;

--
-- Tablo için indeksler `rose_email_settings`
--
ALTER TABLE `rose_email_settings`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `email_settings_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_events`
--
ALTER TABLE `rose_events`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_event_relations`
--
ALTER TABLE `rose_event_relations`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `event_id` (`event_id`) USING BTREE;

--
-- Tablo için indeksler `rose_history`
--
ALTER TABLE `rose_history`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `history_type_id_foreign` (`type_id`) USING BTREE,
  ADD KEY `history_user_id_foreign` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_history_types`
--
ALTER TABLE `rose_history_types`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `rose_hrm_metas`
--
ALTER TABLE `rose_hrm_metas`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `department_id` (`department_id`) USING BTREE;

--
-- Tablo için indeksler `rose_invoices`
--
ALTER TABLE `rose_invoices`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `invoices_fk1` (`ins`) USING BTREE,
  ADD KEY `customer_id` (`customer_id`) USING BTREE,
  ADD KEY `invoices_fk3` (`term_id`) USING BTREE,
  ADD KEY `invoices_fk4` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_invoice_items`
--
ALTER TABLE `rose_invoice_items`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `i_class` (`i_class`) USING BTREE,
  ADD KEY `invoice_items_fk1` (`ins`) USING BTREE,
  ADD KEY `invoice_items_fk2` (`invoice_id`) USING BTREE;

--
-- Tablo için indeksler `rose_menus`
--
ALTER TABLE `rose_menus`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `rose_messages`
--
ALTER TABLE `rose_messages`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `messages_fk1` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_meta_entries`
--
ALTER TABLE `rose_meta_entries`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `meta_entries_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_migrations`
--
ALTER TABLE `rose_migrations`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `rose_miscs`
--
ALTER TABLE `rose_miscs`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `miscs_fk1` (`ins`) USING BTREE,
  ADD KEY `section` (`section`) USING BTREE;

--
-- Tablo için indeksler `rose_notes`
--
ALTER TABLE `rose_notes`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_notifications`
--
ALTER TABLE `rose_notifications`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`) USING BTREE;

--
-- Tablo için indeksler `rose_orders`
--
ALTER TABLE `rose_orders`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `csd` (`customer_id`) USING BTREE,
  ADD KEY `invoice` (`tid`) USING BTREE,
  ADD KEY `i_class` (`i_class`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `term_id` (`term_id`) USING BTREE,
  ADD KEY `orders_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_order_items`
--
ALTER TABLE `rose_order_items`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `order_items_fk1` (`ins`) USING BTREE,
  ADD KEY `order_items_fk2` (`order_id`) USING BTREE;

--
-- Tablo için indeksler `rose_pages`
--
ALTER TABLE `rose_pages`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `pages_page_slug_unique` (`page_slug`) USING BTREE;

--
-- Tablo için indeksler `rose_participants`
--
ALTER TABLE `rose_participants`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_password_resets`
--
ALTER TABLE `rose_password_resets`
  ADD KEY `password_resets_email_index` (`email`) USING BTREE;

--
-- Tablo için indeksler `rose_permissions`
--
ALTER TABLE `rose_permissions`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `permissions_name_unique` (`name`) USING BTREE;

--
-- Tablo için indeksler `rose_permission_role`
--
ALTER TABLE `rose_permission_role`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `permission_role_permission_id_foreign` (`permission_id`) USING BTREE,
  ADD KEY `permission_role_role_id_foreign` (`role_id`) USING BTREE;

--
-- Tablo için indeksler `rose_permission_user`
--
ALTER TABLE `rose_permission_user`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `permission_user_permission_id_foreign` (`permission_id`) USING BTREE,
  ADD KEY `permission_user_user_id_foreign` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_prefixes`
--
ALTER TABLE `rose_prefixes`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_products`
--
ALTER TABLE `rose_products`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `pcat` (`productcategory_id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_product_categories`
--
ALTER TABLE `rose_product_categories`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `product_categories_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_product_meta`
--
ALTER TABLE `rose_product_meta`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ref_id` (`ref_id`) USING BTREE;

--
-- Tablo için indeksler `rose_product_variables`
--
ALTER TABLE `rose_product_variables`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_product_variations`
--
ALTER TABLE `rose_product_variations`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `product_variations_fk1` (`ins`) USING BTREE,
  ADD KEY `product_variations_fk2` (`product_id`) USING BTREE,
  ADD KEY `product_variations_fk3` (`warehouse_id`) USING BTREE;

--
-- Tablo için indeksler `rose_projects`
--
ALTER TABLE `rose_projects`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `projects_fk1` (`ins`) USING BTREE,
  ADD KEY `customer_id` (`customer_id`) USING BTREE;

--
-- Tablo için indeksler `rose_project_logs`
--
ALTER TABLE `rose_project_logs`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `project_id` (`project_id`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_project_meta`
--
ALTER TABLE `rose_project_meta`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `project_id` (`project_id`) USING BTREE;

--
-- Tablo için indeksler `rose_project_milestones`
--
ALTER TABLE `rose_project_milestones`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `project_id` (`project_id`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_project_relations`
--
ALTER TABLE `rose_project_relations`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `project_id` (`project_id`) USING BTREE;

--
-- Tablo için indeksler `rose_purchase_orders`
--
ALTER TABLE `rose_purchase_orders`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `purchase_orders_fk1` (`ins`) USING BTREE,
  ADD KEY `purchase_orders_fk2` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_purchase_order_items`
--
ALTER TABLE `rose_purchase_order_items`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `purchase_order_items_fk1` (`ins`) USING BTREE,
  ADD KEY `purchase_order_items_fk2` (`bill_id`) USING BTREE;

--
-- Tablo için indeksler `rose_quotes`
--
ALTER TABLE `rose_quotes`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `quotes_fk1` (`ins`) USING BTREE,
  ADD KEY `quotes_fk2` (`user_id`) USING BTREE,
  ADD KEY `quotes_fk3` (`term_id`) USING BTREE,
  ADD KEY `customer_id` (`customer_id`);

--
-- Tablo için indeksler `rose_quote_items`
--
ALTER TABLE `rose_quote_items`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `quote_items_fk1` (`ins`) USING BTREE,
  ADD KEY `quote_items_fk2` (`quote_id`) USING BTREE;

--
-- Tablo için indeksler `rose_registers`
--
ALTER TABLE `rose_registers`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_roles`
--
ALTER TABLE `rose_roles`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `roles_name_unique` (`name`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_role_user`
--
ALTER TABLE `rose_role_user`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `role_user_user_id_foreign` (`user_id`) USING BTREE,
  ADD KEY `role_user_role_id_foreign` (`role_id`) USING BTREE;

--
-- Tablo için indeksler `rose_sessions`
--
ALTER TABLE `rose_sessions`
  ADD UNIQUE KEY `sessions_id_unique` (`id`) USING BTREE;

--
-- Tablo için indeksler `rose_settings`
--
ALTER TABLE `rose_settings`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `rose_sms_settings`
--
ALTER TABLE `rose_sms_settings`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `email_settings_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_suppliers`
--
ALTER TABLE `rose_suppliers`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `suppliers_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_templates`
--
ALTER TABLE `rose_templates`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_terms`
--
ALTER TABLE `rose_terms`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `terms_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_threads`
--
ALTER TABLE `rose_threads`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `rose_todolists`
--
ALTER TABLE `rose_todolists`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `todolists_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_todolist_relations`
--
ALTER TABLE `rose_todolist_relations`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `todolist_relations_fk1` (`todolist_id`) USING BTREE;

--
-- Tablo için indeksler `rose_transactions`
--
ALTER TABLE `rose_transactions`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `transactions_fk1` (`ins`) USING BTREE,
  ADD KEY `transactions_fk3` (`trans_category_id`) USING BTREE,
  ADD KEY `transactions_fk4` (`user_id`) USING BTREE,
  ADD KEY `transactions_fk2` (`account_id`) USING BTREE;

--
-- Tablo için indeksler `rose_transaction_categories`
--
ALTER TABLE `rose_transaction_categories`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `transaction_categories_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_transaction_history`
--
ALTER TABLE `rose_transaction_history`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `transactions_fk1` (`ins`) USING BTREE,
  ADD KEY `transactions_fk4` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_users`
--
ALTER TABLE `rose_users`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `users_email_unique` (`email`) USING BTREE,
  ADD KEY `users_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_user_gateways`
--
ALTER TABLE `rose_user_gateways`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Tablo için indeksler `rose_user_gateway_entries`
--
ALTER TABLE `rose_user_gateway_entries`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `unique_index` (`user_gateway_id`,`ins`) USING BTREE,
  ADD KEY `user_gateway_entries_fk1` (`ins`) USING BTREE;

--
-- Tablo için indeksler `rose_user_profiles`
--
ALTER TABLE `rose_user_profiles`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `user_profiles_user_id_foreign` (`user_id`) USING BTREE;

--
-- Tablo için indeksler `rose_warehouses`
--
ALTER TABLE `rose_warehouses`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `ins` (`ins`) USING BTREE;

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `rose_accounts`
--
ALTER TABLE `rose_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `rose_additionals`
--
ALTER TABLE `rose_additionals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `rose_attendances`
--
ALTER TABLE `rose_attendances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_banks`
--
ALTER TABLE `rose_banks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_companies`
--
ALTER TABLE `rose_companies`
  MODIFY `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_config_meta`
--
ALTER TABLE `rose_config_meta`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=456;

--
-- Tablo için AUTO_INCREMENT değeri `rose_currencies`
--
ALTER TABLE `rose_currencies`
  MODIFY `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_customers`
--
ALTER TABLE `rose_customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `rose_customer_groups`
--
ALTER TABLE `rose_customer_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `rose_customer_group_entries`
--
ALTER TABLE `rose_customer_group_entries`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_custom_entries`
--
ALTER TABLE `rose_custom_entries`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `rose_custom_fields`
--
ALTER TABLE `rose_custom_fields`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `rose_departments`
--
ALTER TABLE `rose_departments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_drafts`
--
ALTER TABLE `rose_drafts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_draft_items`
--
ALTER TABLE `rose_draft_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_email_settings`
--
ALTER TABLE `rose_email_settings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_events`
--
ALTER TABLE `rose_events`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_event_relations`
--
ALTER TABLE `rose_event_relations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_history`
--
ALTER TABLE `rose_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_history_types`
--
ALTER TABLE `rose_history_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Tablo için AUTO_INCREMENT değeri `rose_hrm_metas`
--
ALTER TABLE `rose_hrm_metas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_invoices`
--
ALTER TABLE `rose_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_invoice_items`
--
ALTER TABLE `rose_invoice_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_menus`
--
ALTER TABLE `rose_menus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_messages`
--
ALTER TABLE `rose_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_meta_entries`
--
ALTER TABLE `rose_meta_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_migrations`
--
ALTER TABLE `rose_migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_miscs`
--
ALTER TABLE `rose_miscs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `rose_notes`
--
ALTER TABLE `rose_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_orders`
--
ALTER TABLE `rose_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_order_items`
--
ALTER TABLE `rose_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_pages`
--
ALTER TABLE `rose_pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_participants`
--
ALTER TABLE `rose_participants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_permissions`
--
ALTER TABLE `rose_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- Tablo için AUTO_INCREMENT değeri `rose_permission_role`
--
ALTER TABLE `rose_permission_role`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- Tablo için AUTO_INCREMENT değeri `rose_permission_user`
--
ALTER TABLE `rose_permission_user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- Tablo için AUTO_INCREMENT değeri `rose_prefixes`
--
ALTER TABLE `rose_prefixes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Tablo için AUTO_INCREMENT değeri `rose_products`
--
ALTER TABLE `rose_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1688;

--
-- Tablo için AUTO_INCREMENT değeri `rose_product_categories`
--
ALTER TABLE `rose_product_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `rose_product_meta`
--
ALTER TABLE `rose_product_meta`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_product_variables`
--
ALTER TABLE `rose_product_variables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_product_variations`
--
ALTER TABLE `rose_product_variations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1676;

--
-- Tablo için AUTO_INCREMENT değeri `rose_projects`
--
ALTER TABLE `rose_projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_project_logs`
--
ALTER TABLE `rose_project_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_project_meta`
--
ALTER TABLE `rose_project_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_project_milestones`
--
ALTER TABLE `rose_project_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_project_relations`
--
ALTER TABLE `rose_project_relations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_purchase_orders`
--
ALTER TABLE `rose_purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_purchase_order_items`
--
ALTER TABLE `rose_purchase_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_quotes`
--
ALTER TABLE `rose_quotes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_quote_items`
--
ALTER TABLE `rose_quote_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_registers`
--
ALTER TABLE `rose_registers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_roles`
--
ALTER TABLE `rose_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `rose_role_user`
--
ALTER TABLE `rose_role_user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_settings`
--
ALTER TABLE `rose_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_sms_settings`
--
ALTER TABLE `rose_sms_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_suppliers`
--
ALTER TABLE `rose_suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_templates`
--
ALTER TABLE `rose_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- Tablo için AUTO_INCREMENT değeri `rose_terms`
--
ALTER TABLE `rose_terms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_threads`
--
ALTER TABLE `rose_threads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_todolists`
--
ALTER TABLE `rose_todolists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_todolist_relations`
--
ALTER TABLE `rose_todolist_relations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_transactions`
--
ALTER TABLE `rose_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_transaction_categories`
--
ALTER TABLE `rose_transaction_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `rose_transaction_history`
--
ALTER TABLE `rose_transaction_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `rose_users`
--
ALTER TABLE `rose_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_user_gateways`
--
ALTER TABLE `rose_user_gateways`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `rose_user_gateway_entries`
--
ALTER TABLE `rose_user_gateway_entries`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `rose_user_profiles`
--
ALTER TABLE `rose_user_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `rose_warehouses`
--
ALTER TABLE `rose_warehouses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `rose_accounts`
--
ALTER TABLE `rose_accounts`
  ADD CONSTRAINT `rose_accounts_ibfk_1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_additionals`
--
ALTER TABLE `rose_additionals`
  ADD CONSTRAINT `additionals_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_attendances`
--
ALTER TABLE `rose_attendances`
  ADD CONSTRAINT `attendances_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `attendances_fk2` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_banks`
--
ALTER TABLE `rose_banks`
  ADD CONSTRAINT `banks_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_config_meta`
--
ALTER TABLE `rose_config_meta`
  ADD CONSTRAINT `config_meta_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_currencies`
--
ALTER TABLE `rose_currencies`
  ADD CONSTRAINT `currencies_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_customers`
--
ALTER TABLE `rose_customers`
  ADD CONSTRAINT `customers_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_customer_groups`
--
ALTER TABLE `rose_customer_groups`
  ADD CONSTRAINT `customer_groups_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_customer_group_entries`
--
ALTER TABLE `rose_customer_group_entries`
  ADD CONSTRAINT `customer_group_entries_fk1` FOREIGN KEY (`customer_group_id`) REFERENCES `rose_customer_groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `customer_group_entries_fk2` FOREIGN KEY (`customer_id`) REFERENCES `rose_customers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_custom_entries`
--
ALTER TABLE `rose_custom_entries`
  ADD CONSTRAINT `custom_entries_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `custom_entries_fk2` FOREIGN KEY (`custom_field_id`) REFERENCES `rose_custom_fields` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_custom_fields`
--
ALTER TABLE `rose_custom_fields`
  ADD CONSTRAINT `custom_fields_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_departments`
--
ALTER TABLE `rose_departments`
  ADD CONSTRAINT `departments_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_draft_items`
--
ALTER TABLE `rose_draft_items`
  ADD CONSTRAINT `rose_draft_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `rose_drafts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_email_settings`
--
ALTER TABLE `rose_email_settings`
  ADD CONSTRAINT `email_settings_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_events`
--
ALTER TABLE `rose_events`
  ADD CONSTRAINT `rose_events_ibfk_1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_event_relations`
--
ALTER TABLE `rose_event_relations`
  ADD CONSTRAINT `rose_event_relations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `rose_events` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_history`
--
ALTER TABLE `rose_history`
  ADD CONSTRAINT `history_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `rose_history_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `rose_hrm_metas`
--
ALTER TABLE `rose_hrm_metas`
  ADD CONSTRAINT `hrm_metas_fk1` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_invoices`
--
ALTER TABLE `rose_invoices`
  ADD CONSTRAINT `invoices_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_fk2` FOREIGN KEY (`customer_id`) REFERENCES `rose_customers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_fk3` FOREIGN KEY (`term_id`) REFERENCES `rose_terms` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoices_fk4` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_invoice_items`
--
ALTER TABLE `rose_invoice_items`
  ADD CONSTRAINT `invoice_items_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `invoice_items_fk2` FOREIGN KEY (`invoice_id`) REFERENCES `rose_invoices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_messages`
--
ALTER TABLE `rose_messages`
  ADD CONSTRAINT `messages_fk1` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_meta_entries`
--
ALTER TABLE `rose_meta_entries`
  ADD CONSTRAINT `meta_entries_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_miscs`
--
ALTER TABLE `rose_miscs`
  ADD CONSTRAINT `miscs_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_notes`
--
ALTER TABLE `rose_notes`
  ADD CONSTRAINT `rose_notes_ibfk_1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_orders`
--
ALTER TABLE `rose_orders`
  ADD CONSTRAINT `orders_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `orders_fk2` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `rose_orders_ibfk_1` FOREIGN KEY (`term_id`) REFERENCES `rose_terms` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_order_items`
--
ALTER TABLE `rose_order_items`
  ADD CONSTRAINT `order_items_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `order_items_fk2` FOREIGN KEY (`order_id`) REFERENCES `rose_orders` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_participants`
--
ALTER TABLE `rose_participants`
  ADD CONSTRAINT `rose_participants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_permission_role`
--
ALTER TABLE `rose_permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `rose_permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `rose_roles` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `rose_permission_user`
--
ALTER TABLE `rose_permission_user`
  ADD CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `rose_permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `rose_prefixes`
--
ALTER TABLE `rose_prefixes`
  ADD CONSTRAINT `prefixes_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_products`
--
ALTER TABLE `rose_products`
  ADD CONSTRAINT `products_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `products_fk2` FOREIGN KEY (`productcategory_id`) REFERENCES `rose_product_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_product_categories`
--
ALTER TABLE `rose_product_categories`
  ADD CONSTRAINT `product_categories_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_product_variables`
--
ALTER TABLE `rose_product_variables`
  ADD CONSTRAINT `rose_product_variables_ibfk_1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_product_variations`
--
ALTER TABLE `rose_product_variations`
  ADD CONSTRAINT `product_variations_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `product_variations_fk2` FOREIGN KEY (`product_id`) REFERENCES `rose_products` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `product_variations_fk3` FOREIGN KEY (`warehouse_id`) REFERENCES `rose_warehouses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_projects`
--
ALTER TABLE `rose_projects`
  ADD CONSTRAINT `projects_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `rose_projects_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `rose_customers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_project_logs`
--
ALTER TABLE `rose_project_logs`
  ADD CONSTRAINT `rose_project_logs_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `rose_projects` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `rose_project_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Tablo kısıtlamaları `rose_project_meta`
--
ALTER TABLE `rose_project_meta`
  ADD CONSTRAINT `rose_project_meta_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `rose_projects` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_project_milestones`
--
ALTER TABLE `rose_project_milestones`
  ADD CONSTRAINT `rose_project_milestones_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `rose_projects` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `rose_project_milestones_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_project_relations`
--
ALTER TABLE `rose_project_relations`
  ADD CONSTRAINT `rose_project_relations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `rose_projects` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_purchase_orders`
--
ALTER TABLE `rose_purchase_orders`
  ADD CONSTRAINT `purchase_orders_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `purchase_orders_fk2` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_purchase_order_items`
--
ALTER TABLE `rose_purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `purchase_order_items_fk2` FOREIGN KEY (`bill_id`) REFERENCES `rose_purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_quotes`
--
ALTER TABLE `rose_quotes`
  ADD CONSTRAINT `quotes_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `quotes_fk2` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `quotes_fk3` FOREIGN KEY (`term_id`) REFERENCES `rose_terms` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `rose_quotes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `rose_customers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_quote_items`
--
ALTER TABLE `rose_quote_items`
  ADD CONSTRAINT `quote_items_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `quote_items_fk2` FOREIGN KEY (`quote_id`) REFERENCES `rose_quotes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_registers`
--
ALTER TABLE `rose_registers`
  ADD CONSTRAINT `rose_registers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_roles`
--
ALTER TABLE `rose_roles`
  ADD CONSTRAINT `rose_roles_ibfk_1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_role_user`
--
ALTER TABLE `rose_role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `rose_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `rose_sms_settings`
--
ALTER TABLE `rose_sms_settings`
  ADD CONSTRAINT `rose_sms_settings_ibfk_1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_suppliers`
--
ALTER TABLE `rose_suppliers`
  ADD CONSTRAINT `suppliers_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_templates`
--
ALTER TABLE `rose_templates`
  ADD CONSTRAINT `rose_templates_ibfk_1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_terms`
--
ALTER TABLE `rose_terms`
  ADD CONSTRAINT `terms_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_todolists`
--
ALTER TABLE `rose_todolists`
  ADD CONSTRAINT `todolists_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_todolist_relations`
--
ALTER TABLE `rose_todolist_relations`
  ADD CONSTRAINT `todolist_relations_fk1` FOREIGN KEY (`todolist_id`) REFERENCES `rose_todolists` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_transactions`
--
ALTER TABLE `rose_transactions`
  ADD CONSTRAINT `transactions_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `transactions_fk2` FOREIGN KEY (`account_id`) REFERENCES `rose_accounts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `transactions_fk3` FOREIGN KEY (`trans_category_id`) REFERENCES `rose_transaction_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `transactions_fk4` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_transaction_categories`
--
ALTER TABLE `rose_transaction_categories`
  ADD CONSTRAINT `transaction_categories_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_transaction_history`
--
ALTER TABLE `rose_transaction_history`
  ADD CONSTRAINT `rose_transaction_history_ibfk_1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_users`
--
ALTER TABLE `rose_users`
  ADD CONSTRAINT `users_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_user_gateway_entries`
--
ALTER TABLE `rose_user_gateway_entries`
  ADD CONSTRAINT `user_gateway_entries_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_gateway_entries_fk2` FOREIGN KEY (`user_gateway_id`) REFERENCES `rose_user_gateways` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Tablo kısıtlamaları `rose_user_profiles`
--
ALTER TABLE `rose_user_profiles`
  ADD CONSTRAINT `user_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `rose_users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `rose_warehouses`
--
ALTER TABLE `rose_warehouses`
  ADD CONSTRAINT `warehouses_fk1` FOREIGN KEY (`ins`) REFERENCES `rose_companies` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
