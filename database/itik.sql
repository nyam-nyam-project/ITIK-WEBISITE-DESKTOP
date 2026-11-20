-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 20, 2026 at 09:59 AM
-- Server version: 8.4.3
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `itik`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-roster:project:v3:4dbb295e9d7fad7b721babc3e72e117e', 'O:26:\"Laravel\\Roster\\ProjectScan\":8:{s:8:\"basePath\";s:37:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\\";s:3:\"php\";O:35:\"Laravel\\Roster\\Ecosystems\\Ecosystem\":2:{s:9:\"\0*\0byName\";a:116:{s:10:\"brick/math\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"brick/math\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";E:43:\"Laravel\\Roster\\Enums\\PackageSource:Composer\";s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\brick\\math\";}s:31:\"carbonphp/carbon-doctrine-types\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:31:\"carbonphp/carbon-doctrine-types\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:75:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\carbonphp\\carbon-doctrine-types\";}s:23:\"dflydev/dot-access-data\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"dflydev/dot-access-data\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\dflydev\\dot-access-data\";}s:18:\"doctrine/inflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"doctrine/inflector\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\doctrine\\inflector\";}s:14:\"doctrine/lexer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"doctrine/lexer\";s:10:\"\0*\0version\";s:5:\"3.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\doctrine\\lexer\";}s:29:\"dragonmantank/cron-expression\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"dragonmantank/cron-expression\";s:10:\"\0*\0version\";s:5:\"3.6.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\dragonmantank\\cron-expression\";}s:23:\"egulias/email-validator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"egulias/email-validator\";s:10:\"\0*\0version\";s:5:\"4.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\egulias\\email-validator\";}s:18:\"fruitcake/php-cors\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"fruitcake/php-cors\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\fruitcake\\php-cors\";}s:27:\"graham-campbell/result-type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"graham-campbell/result-type\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\graham-campbell\\result-type\";}s:17:\"guzzlehttp/guzzle\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"guzzlehttp/guzzle\";s:10:\"\0*\0version\";s:5:\"8.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\guzzlehttp\\guzzle\";}s:19:\"guzzlehttp/promises\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"guzzlehttp/promises\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\guzzlehttp\\promises\";}s:15:\"guzzlehttp/psr7\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"guzzlehttp/psr7\";s:10:\"\0*\0version\";s:5:\"3.3.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\guzzlehttp\\psr7\";}s:23:\"guzzlehttp/uri-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"guzzlehttp/uri-template\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\guzzlehttp\\uri-template\";}s:17:\"laravel/framework\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"laravel/framework\";s:10:\"\0*\0version\";s:7:\"13.42.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^13.17\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\framework\";}s:15:\"laravel/prompts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/prompts\";s:10:\"\0*\0version\";s:6:\"0.3.27\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\prompts\";}s:15:\"laravel/sanctum\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"laravel/sanctum\";s:10:\"\0*\0version\";s:5:\"4.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^4.3\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\sanctum\";}s:28:\"laravel/serializable-closure\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"laravel/serializable-closure\";s:10:\"\0*\0version\";s:5:\"2.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\serializable-closure\";}s:14:\"laravel/tinker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/tinker\";s:10:\"\0*\0version\";s:5:\"3.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.0\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\tinker\";}s:17:\"league/commonmark\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"league/commonmark\";s:10:\"\0*\0version\";s:6:\"2.10.7\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\league\\commonmark\";}s:13:\"league/config\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"league/config\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\league\\config\";}s:16:\"league/flysystem\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"league/flysystem\";s:10:\"\0*\0version\";s:6:\"3.36.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\league\\flysystem\";}s:22:\"league/flysystem-local\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"league/flysystem-local\";s:10:\"\0*\0version\";s:6:\"3.35.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\league\\flysystem-local\";}s:26:\"league/mime-type-detection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"league/mime-type-detection\";s:10:\"\0*\0version\";s:6:\"1.17.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\league\\mime-type-detection\";}s:10:\"league/uri\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:10:\"league/uri\";s:10:\"\0*\0version\";s:6:\"7.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:54:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\league\\uri\";}s:21:\"league/uri-interfaces\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"league/uri-interfaces\";s:10:\"\0*\0version\";s:6:\"7.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\league\\uri-interfaces\";}s:15:\"monolog/monolog\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"monolog/monolog\";s:10:\"\0*\0version\";s:6:\"3.12.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\monolog\\monolog\";}s:13:\"nesbot/carbon\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"nesbot/carbon\";s:10:\"\0*\0version\";s:6:\"3.15.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\nesbot\\carbon\";}s:12:\"nette/schema\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"nette/schema\";s:10:\"\0*\0version\";s:5:\"1.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\nette\\schema\";}s:11:\"nette/utils\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"nette/utils\";s:10:\"\0*\0version\";s:5:\"4.1.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\nette\\utils\";}s:16:\"nikic/php-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"nikic/php-parser\";s:10:\"\0*\0version\";s:5:\"5.9.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\nikic\\php-parser\";}s:19:\"nunomaduro/termwind\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"nunomaduro/termwind\";s:10:\"\0*\0version\";s:5:\"2.4.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\nunomaduro\\termwind\";}s:19:\"phpoption/phpoption\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpoption/phpoption\";s:10:\"\0*\0version\";s:6:\"1.10.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phpoption\\phpoption\";}s:9:\"psr/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psr/clock\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psr\\clock\";}s:13:\"psr/container\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"psr/container\";s:10:\"\0*\0version\";s:5:\"2.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psr\\container\";}s:20:\"psr/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"psr/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psr\\event-dispatcher\";}s:15:\"psr/http-client\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"psr/http-client\";s:10:\"\0*\0version\";s:5:\"1.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psr\\http-client\";}s:16:\"psr/http-factory\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-factory\";s:10:\"\0*\0version\";s:5:\"1.1.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psr\\http-factory\";}s:16:\"psr/http-message\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/http-message\";s:10:\"\0*\0version\";s:3:\"2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psr\\http-message\";}s:7:\"psr/log\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:7:\"psr/log\";s:10:\"\0*\0version\";s:5:\"3.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:51:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psr\\log\";}s:16:\"psr/simple-cache\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"psr/simple-cache\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psr\\simple-cache\";}s:9:\"psy/psysh\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:9:\"psy/psysh\";s:10:\"\0*\0version\";s:7:\"0.12.24\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:53:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\psy\\psysh\";}s:17:\"ramsey/collection\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"ramsey/collection\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\ramsey\\collection\";}s:11:\"ramsey/uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"ramsey/uuid\";s:10:\"\0*\0version\";s:5:\"4.9.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\ramsey\\uuid\";}s:13:\"symfony/clock\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"symfony/clock\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:57:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\clock\";}s:15:\"symfony/console\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/console\";s:10:\"\0*\0version\";s:6:\"8.1.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\console\";}s:20:\"symfony/css-selector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"symfony/css-selector\";s:10:\"\0*\0version\";s:6:\"8.1.10\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\css-selector\";}s:29:\"symfony/deprecation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/deprecation-contracts\";s:10:\"\0*\0version\";s:5:\"3.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\deprecation-contracts\";}s:21:\"symfony/error-handler\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/error-handler\";s:10:\"\0*\0version\";s:6:\"8.1.10\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\error-handler\";}s:24:\"symfony/event-dispatcher\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:24:\"symfony/event-dispatcher\";s:10:\"\0*\0version\";s:5:\"8.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:68:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\event-dispatcher\";}s:34:\"symfony/event-dispatcher-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:34:\"symfony/event-dispatcher-contracts\";s:10:\"\0*\0version\";s:5:\"3.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:78:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\event-dispatcher-contracts\";}s:14:\"symfony/finder\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/finder\";s:10:\"\0*\0version\";s:6:\"8.1.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\finder\";}s:23:\"symfony/http-foundation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"symfony/http-foundation\";s:10:\"\0*\0version\";s:6:\"8.1.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\http-foundation\";}s:19:\"symfony/http-kernel\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/http-kernel\";s:10:\"\0*\0version\";s:6:\"8.1.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\http-kernel\";}s:14:\"symfony/mailer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/mailer\";s:10:\"\0*\0version\";s:6:\"8.1.10\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\mailer\";}s:12:\"symfony/mime\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/mime\";s:10:\"\0*\0version\";s:6:\"8.1.10\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\mime\";}s:22:\"symfony/polyfill-ctype\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-ctype\";s:10:\"\0*\0version\";s:6:\"1.37.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-ctype\";}s:30:\"symfony/polyfill-intl-grapheme\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:30:\"symfony/polyfill-intl-grapheme\";s:10:\"\0*\0version\";s:6:\"1.43.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:74:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-intl-grapheme\";}s:25:\"symfony/polyfill-intl-idn\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-intl-idn\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-intl-idn\";}s:32:\"symfony/polyfill-intl-normalizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:32:\"symfony/polyfill-intl-normalizer\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:76:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-intl-normalizer\";}s:25:\"symfony/polyfill-mbstring\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/polyfill-mbstring\";s:10:\"\0*\0version\";s:6:\"1.43.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-mbstring\";}s:22:\"symfony/polyfill-php80\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php80\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-php80\";}s:22:\"symfony/polyfill-php82\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php82\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-php82\";}s:22:\"symfony/polyfill-php84\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php84\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-php84\";}s:22:\"symfony/polyfill-php85\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php85\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-php85\";}s:22:\"symfony/polyfill-php86\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"symfony/polyfill-php86\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-php86\";}s:21:\"symfony/polyfill-time\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-time\";s:10:\"\0*\0version\";s:6:\"1.42.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-time\";}s:21:\"symfony/polyfill-uuid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"symfony/polyfill-uuid\";s:10:\"\0*\0version\";s:6:\"1.43.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\polyfill-uuid\";}s:15:\"symfony/process\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/process\";s:10:\"\0*\0version\";s:6:\"8.1.10\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\process\";}s:15:\"symfony/routing\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"symfony/routing\";s:10:\"\0*\0version\";s:6:\"8.1.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\routing\";}s:25:\"symfony/service-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"symfony/service-contracts\";s:10:\"\0*\0version\";s:5:\"3.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\service-contracts\";}s:14:\"symfony/string\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"symfony/string\";s:10:\"\0*\0version\";s:6:\"8.1.10\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\string\";}s:19:\"symfony/translation\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"symfony/translation\";s:10:\"\0*\0version\";s:6:\"8.1.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\translation\";}s:29:\"symfony/translation-contracts\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:29:\"symfony/translation-contracts\";s:10:\"\0*\0version\";s:5:\"3.8.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:73:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\translation-contracts\";}s:11:\"symfony/uid\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"symfony/uid\";s:10:\"\0*\0version\";s:5:\"8.1.9\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\uid\";}s:18:\"symfony/var-dumper\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"symfony/var-dumper\";s:10:\"\0*\0version\";s:6:\"8.1.10\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\var-dumper\";}s:33:\"tijsverkoyen/css-to-inline-styles\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:33:\"tijsverkoyen/css-to-inline-styles\";s:10:\"\0*\0version\";s:5:\"2.4.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:77:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\tijsverkoyen\\css-to-inline-styles\";}s:16:\"vlucas/phpdotenv\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"vlucas/phpdotenv\";s:10:\"\0*\0version\";s:5:\"5.8.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\vlucas\\phpdotenv\";}s:19:\"voku/portable-ascii\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"voku/portable-ascii\";s:10:\"\0*\0version\";s:5:\"2.1.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\voku\\portable-ascii\";}s:15:\"composer/semver\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"composer/semver\";s:10:\"\0*\0version\";s:5:\"3.6.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\composer\\semver\";}s:14:\"fakerphp/faker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"fakerphp/faker\";s:10:\"\0*\0version\";s:6:\"1.24.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.23\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\fakerphp\\faker\";}s:11:\"filp/whoops\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"filp/whoops\";s:10:\"\0*\0version\";s:6:\"2.19.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\filp\\whoops\";}s:21:\"hamcrest/hamcrest-php\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"hamcrest/hamcrest-php\";s:10:\"\0*\0version\";s:5:\"3.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\hamcrest\\hamcrest-php\";}s:22:\"laravel/agent-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"laravel/agent-detector\";s:10:\"\0*\0version\";s:5:\"2.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\agent-detector\";}s:13:\"laravel/boost\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:13:\"laravel/boost\";s:10:\"\0*\0version\";s:6:\"2.10.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^2.10\";s:7:\"\0*\0path\";s:57:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\boost\";}s:11:\"laravel/mcp\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/mcp\";s:10:\"\0*\0version\";s:5:\"1.2.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:55:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\mcp\";}s:12:\"laravel/pail\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pail\";s:10:\"\0*\0version\";s:5:\"1.3.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.2.5\";s:7:\"\0*\0path\";s:56:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\pail\";}s:11:\"laravel/pao\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"laravel/pao\";s:10:\"\0*\0version\";s:5:\"1.1.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^1.0.6\";s:7:\"\0*\0path\";s:55:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\pao\";}s:12:\"laravel/pint\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"laravel/pint\";s:10:\"\0*\0version\";s:6:\"1.32.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:5:\"^1.27\";s:7:\"\0*\0path\";s:56:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\pint\";}s:14:\"laravel/roster\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"laravel/roster\";s:10:\"\0*\0version\";s:5:\"1.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\laravel\\roster\";}s:15:\"mockery/mockery\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"mockery/mockery\";s:10:\"\0*\0version\";s:6:\"1.6.15\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^1.6\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\mockery\\mockery\";}s:17:\"myclabs/deep-copy\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"myclabs/deep-copy\";s:10:\"\0*\0version\";s:6:\"1.14.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\myclabs\\deep-copy\";}s:20:\"nunomaduro/collision\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"nunomaduro/collision\";s:10:\"\0*\0version\";s:5:\"8.9.6\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^8.6\";s:7:\"\0*\0path\";s:64:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\nunomaduro\\collision\";}s:16:\"phar-io/manifest\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:16:\"phar-io/manifest\";s:10:\"\0*\0version\";s:5:\"2.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:60:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phar-io\\manifest\";}s:15:\"phar-io/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phar-io/version\";s:10:\"\0*\0version\";s:5:\"3.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phar-io\\version\";}s:25:\"phpunit/php-code-coverage\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-code-coverage\";s:10:\"\0*\0version\";s:6:\"12.5.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phpunit\\php-code-coverage\";}s:25:\"phpunit/php-file-iterator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-file-iterator\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phpunit\\php-file-iterator\";}s:19:\"phpunit/php-invoker\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"phpunit/php-invoker\";s:10:\"\0*\0version\";s:5:\"6.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:63:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phpunit\\php-invoker\";}s:25:\"phpunit/php-text-template\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:25:\"phpunit/php-text-template\";s:10:\"\0*\0version\";s:5:\"5.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:69:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phpunit\\php-text-template\";}s:17:\"phpunit/php-timer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"phpunit/php-timer\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phpunit\\php-timer\";}s:15:\"phpunit/phpunit\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:15:\"phpunit/phpunit\";s:10:\"\0*\0version\";s:7:\"12.5.43\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:8:\"^12.5.12\";s:7:\"\0*\0path\";s:59:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\phpunit\\phpunit\";}s:20:\"sebastian/cli-parser\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/cli-parser\";s:10:\"\0*\0version\";s:5:\"4.2.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\cli-parser\";}s:20:\"sebastian/comparator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/comparator\";s:10:\"\0*\0version\";s:5:\"7.1.8\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\comparator\";}s:20:\"sebastian/complexity\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:20:\"sebastian/complexity\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:64:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\complexity\";}s:14:\"sebastian/diff\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/diff\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\diff\";}s:21:\"sebastian/environment\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:21:\"sebastian/environment\";s:10:\"\0*\0version\";s:5:\"8.1.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:65:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\environment\";}s:18:\"sebastian/exporter\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"sebastian/exporter\";s:10:\"\0*\0version\";s:5:\"7.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:62:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\exporter\";}s:22:\"sebastian/global-state\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:22:\"sebastian/global-state\";s:10:\"\0*\0version\";s:5:\"8.0.3\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:66:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\global-state\";}s:23:\"sebastian/lines-of-code\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:23:\"sebastian/lines-of-code\";s:10:\"\0*\0version\";s:5:\"4.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:67:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\lines-of-code\";}s:27:\"sebastian/object-enumerator\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/object-enumerator\";s:10:\"\0*\0version\";s:5:\"7.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\object-enumerator\";}s:26:\"sebastian/object-reflector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:26:\"sebastian/object-reflector\";s:10:\"\0*\0version\";s:5:\"5.0.0\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:70:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\object-reflector\";}s:27:\"sebastian/recursion-context\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:27:\"sebastian/recursion-context\";s:10:\"\0*\0version\";s:5:\"7.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:71:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\recursion-context\";}s:14:\"sebastian/type\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:14:\"sebastian/type\";s:10:\"\0*\0version\";s:5:\"6.0.4\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:58:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\type\";}s:17:\"sebastian/version\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"sebastian/version\";s:10:\"\0*\0version\";s:5:\"6.0.2\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\sebastian\\version\";}s:28:\"staabm/side-effects-detector\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:28:\"staabm/side-effects-detector\";s:10:\"\0*\0version\";s:5:\"1.0.5\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:72:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\staabm\\side-effects-detector\";}s:12:\"symfony/yaml\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"symfony/yaml\";s:10:\"\0*\0version\";s:6:\"8.1.11\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:56:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\symfony\\yaml\";}s:17:\"theseer/tokenizer\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"theseer/tokenizer\";s:10:\"\0*\0version\";s:5:\"2.0.1\";s:9:\"\0*\0source\";r:8;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:0;s:13:\"\0*\0constraint\";s:0:\"\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\vendor\\theseer\\tokenizer\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:116:{i:0;r:5;i:1;r:13;i:2;r:21;i:3;r:29;i:4;r:37;i:5;r:45;i:6;r:53;i:7;r:61;i:8;r:69;i:9;r:77;i:10;r:85;i:11;r:93;i:12;r:101;i:13;r:109;i:14;r:117;i:15;r:125;i:16;r:133;i:17;r:141;i:18;r:149;i:19;r:157;i:20;r:165;i:21;r:173;i:22;r:181;i:23;r:189;i:24;r:197;i:25;r:205;i:26;r:213;i:27;r:221;i:28;r:229;i:29;r:237;i:30;r:245;i:31;r:253;i:32;r:261;i:33;r:269;i:34;r:277;i:35;r:285;i:36;r:293;i:37;r:301;i:38;r:309;i:39;r:317;i:40;r:325;i:41;r:333;i:42;r:341;i:43;r:349;i:44;r:357;i:45;r:365;i:46;r:373;i:47;r:381;i:48;r:389;i:49;r:397;i:50;r:405;i:51;r:413;i:52;r:421;i:53;r:429;i:54;r:437;i:55;r:445;i:56;r:453;i:57;r:461;i:58;r:469;i:59;r:477;i:60;r:485;i:61;r:493;i:62;r:501;i:63;r:509;i:64;r:517;i:65;r:525;i:66;r:533;i:67;r:541;i:68;r:549;i:69;r:557;i:70;r:565;i:71;r:573;i:72;r:581;i:73;r:589;i:74;r:597;i:75;r:605;i:76;r:613;i:77;r:621;i:78;r:629;i:79;r:637;i:80;r:645;i:81;r:653;i:82;r:661;i:83;r:669;i:84;r:677;i:85;r:685;i:86;r:693;i:87;r:701;i:88;r:709;i:89;r:717;i:90;r:725;i:91;r:733;i:92;r:741;i:93;r:749;i:94;r:757;i:95;r:765;i:96;r:773;i:97;r:781;i:98;r:789;i:99;r:797;i:100;r:805;i:101;r:813;i:102;r:821;i:103;r:829;i:104;r:837;i:105;r:845;i:106;r:853;i:107;r:861;i:108;r:869;i:109;r:877;i:110;r:885;i:111;r:893;i:112;r:901;i:113;r:909;i:114;r:917;i:115;r:925;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:2:\"js\";O:37:\"Laravel\\Roster\\Ecosystems\\JsEcosystem\":3:{s:9:\"\0*\0byName\";a:6:{s:17:\"@tailwindcss/vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:17:\"@tailwindcss/vite\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";E:38:\"Laravel\\Roster\\Enums\\PackageSource:Npm\";s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^4.0.0\";s:7:\"\0*\0path\";s:67:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\node_modules\\@tailwindcss\\vite\";}s:12:\"concurrently\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:12:\"concurrently\";s:10:\"\0*\0version\";s:6:\"10.0.3\";s:9:\"\0*\0source\";r:1057;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:7:\"^10.0.3\";s:7:\"\0*\0path\";s:62:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\node_modules\\concurrently\";}s:19:\"laravel-vite-plugin\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:19:\"laravel-vite-plugin\";s:10:\"\0*\0version\";s:3:\"3.1\";s:9:\"\0*\0source\";r:1057;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:4:\"^3.1\";s:7:\"\0*\0path\";s:69:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\node_modules\\laravel-vite-plugin\";}s:11:\"tailwindcss\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:11:\"tailwindcss\";s:10:\"\0*\0version\";s:5:\"4.0.0\";s:9:\"\0*\0source\";r:1057;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^4.0.0\";s:7:\"\0*\0path\";s:61:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\node_modules\\tailwindcss\";}s:4:\"vite\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:4:\"vite\";s:10:\"\0*\0version\";s:5:\"8.0.0\";s:9:\"\0*\0source\";r:1057;s:6:\"\0*\0dev\";b:1;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^8.0.0\";s:7:\"\0*\0path\";s:54:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\node_modules\\vite\";}s:18:\"@laravel/multiplex\";O:22:\"Laravel\\Roster\\Package\":7:{s:7:\"\0*\0name\";s:18:\"@laravel/multiplex\";s:10:\"\0*\0version\";s:5:\"0.4.1\";s:9:\"\0*\0source\";r:1057;s:6:\"\0*\0dev\";b:0;s:9:\"\0*\0direct\";b:1;s:13:\"\0*\0constraint\";s:6:\"^0.4.1\";s:7:\"\0*\0path\";s:68:\"C:\\laragon\\www\\ITIK-WEBISITE-DESKTOP\\node_modules\\@laravel\\multiplex\";}}s:11:\"\0*\0packages\";O:32:\"Laravel\\Roster\\PackageCollection\":2:{s:8:\"\0*\0items\";a:6:{i:0;r:1054;i:1;r:1062;i:2;r:1070;i:3;r:1078;i:4;r:1086;i:5;r:1094;}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:17:\"\0*\0packageManager\";N;}s:6:\"stacks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:1:{i:0;E:30:\"Laravel\\Roster\\Enums\\Stack:Api\";}}s:21:\"browserTestFrameworks\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:9:\"frontends\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}s:6:\"agents\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:2:{i:0;E:37:\"Laravel\\Roster\\Enums\\Agent:ClaudeCode\";i:1;E:32:\"Laravel\\Roster\\Enums\\Agent:Codex\";}}s:7:\"editors\";O:30:\"Laravel\\Roster\\Support\\EnumSet\":1:{s:8:\"\0*\0cases\";a:0:{}}}', 1794537647);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `attempts` smallint UNSIGNED NOT NULL,
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
(4, '2026_11_11_020106_create_tm_tahun_ajaran_table', 1),
(5, '2026_11_11_020121_create_tm_mapel_table', 1),
(6, '2026_11_11_020233_create_tm_kelas_table', 1),
(7, '2026_11_11_020342_create_tm_materi_table', 1),
(8, '2026_11_11_021044_create_tm_kuis_table', 1),
(9, '2026_11_11_021148_create_tm_soal_table', 1),
(10, '2026_11_11_021550_create_tp_anggota_kelas_table', 1),
(11, '2026_11_11_021920_create_tp_mapel_kelas_table', 1),
(12, '2026_11_11_022007_create_tp_materi_kuis_table', 1),
(13, '2026_11_11_022101_create_tr_mengerjakan_table', 1),
(14, '2026_11_11_022239_create_tr_remedial_siswa_table', 1),
(15, '2026_11_11_042022_personal_access_tokens', 1),
(16, '2026_11_11_144852_sessions', 1),
(17, '2026_11_12_033928_tr_progress_table', 1),
(18, '2026_11_12_034924_create_tr_sesi_kuis_table', 1),
(19, '2026_11_12_035139_create_tr_draft_jawaban_table', 1),
(20, '2026_11_12_035727_create_tr_remedial_table', 1),
(21, '2026_11_13_014154_change_sessions_user_id_to_string', 2),
(22, '2026_11_13_021019_create_tm_materis_table', 3),
(23, '2026_11_13_021020_create_tr_nilai_essay_table', 3),
(24, '2026_11_13_021021_create_tr_pengajaran_table', 3),
(25, '2026_11_13_021022_create_password_reset_tokens_table', 3),
(26, '2026_11_13_031104_add_unique_bab_index_to_tm_materi_table', 4),
(27, '2026_11_15_040140_add_index_dashboard_kelas', 4),
(28, '2026_11_15_050000_create_tp_kuis_kelas_table', 5),
(29, '2026_11_19_141500_add_tampil_jawaban_to_tm_kuis_table', 6),
(30, '2026_11_19_144000_add_is_aktif_to_tm_materi_table', 7),
(31, '2026_11_19_191000_add_tipe_and_tingkat_kesulitan_to_tm_soal_table', 8);

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
  `tokenable_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('OxRk21dKKfbabsn6UaDN8Lw3TGPVkHWGMo4oZYa0', 'U003', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJvWFZ1MlJ0TkNITnRTN3loV1EyQzVXOG01UmhXS1hMVmNybTNpaFI1IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImFkbWluLmRhc2hib2FyZCJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6IlUwMDMiLCJwYXNzd29yZF9oYXNoX3dlYiI6ImUzNjM4YzE5YmRmYWQzYjVkZjAwYWY2ZWQ1ZjlhYTU0NTUwNGUxNmJjOTE1ZTgyNjFhMDMwZWM5NDBlY2U1ODAifQ==', 1795167804);

-- --------------------------------------------------------

--
-- Table structure for table `tm_kelas`
--

CREATE TABLE `tm_kelas` (
  `id_kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_tahun_ajaran` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tm_kelas`
--

INSERT INTO `tm_kelas` (`id_kelas`, `nama_kelas`, `tingkat`, `id_tahun_ajaran`, `is_aktif`, `created_at`, `updated_at`) VALUES
('KLS001', 'X TKJ 1', 'X', 'TA001', 1, '2026-11-12 18:22:32', '2026-11-12 18:22:32'),
('KLS002', 'X TKJ 2', 'X', 'TA001', 1, '2026-11-12 18:22:32', '2026-11-12 18:22:32'),
('KLS003', 'XI TKJ 1', 'XI', 'TA001', 1, '2026-11-20 08:52:48', '2026-11-20 08:52:48'),
('KLS004', 'XI TKJ 2', 'XI', 'TA001', 1, '2026-11-20 08:53:12', '2026-11-20 08:53:12');

-- --------------------------------------------------------

--
-- Table structure for table `tm_kuis`
--

CREATE TABLE `tm_kuis` (
  `id_kuis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat_kesulitan` enum('mudah','sedang','sulit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mudah',
  `id_mapel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_tahun_ajaran` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alokasi_waktu` int UNSIGNED DEFAULT NULL,
  `kkm` decimal(5,2) DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_publikasi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `tampil_jawaban` enum('tidak','setelah_submit','setelah_kkm') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'setelah_submit',
  `id_publisher` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tm_kuis`
--

INSERT INTO `tm_kuis` (`id_kuis`, `judul`, `deskripsi`, `kategori`, `tingkat_kesulitan`, `id_mapel`, `id_tahun_ajaran`, `alokasi_waktu`, `kkm`, `waktu_mulai`, `waktu_selesai`, `id_user`, `status_publikasi`, `tampil_jawaban`, `id_publisher`, `is_aktif`, `created_at`) VALUES
('K001', 'Kuis HTML Dasar', 'Kuis untuk menguji pemahaman dasar struktur HTML.', 'kuis harian', 'mudah', 'MP001', 'TA001', 30, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-13 01:22:32'),
('K002', 'Kuis CSS & Layout', 'Latihan untuk memahami selector, warna, dan layout dengan CSS.', 'post-test', 'mudah', 'MP001', 'TA001', 45, 80.00, NULL, NULL, 'U001', 'draft', 'setelah_submit', NULL, 1, '2026-11-13 01:22:32'),
('K003', 'Ulangan Harian JavaScript', 'Ulangan untuk materi JavaScript dasar.', 'ulangan', 'mudah', 'MP001', 'TA001', 60, 70.00, '2026-11-13 00:22:32', '2026-11-13 02:22:32', 'U001', 'draft', 'setelah_submit', NULL, 1, '2026-11-13 01:22:32'),
('K005', 'FREE FIRE', 'assa', 'kuis harian', 'mudah', 'MP001', 'TA001', 12, 90.00, '2026-11-20 06:50:00', '2026-11-20 07:00:00', 'U001', 'draft', 'setelah_submit', NULL, 1, '2026-11-19 11:56:22'),
('KUIS-TIK-B2-POST', 'Post-test Bab 2: Berpikir Komputasional', 'Uji pemahaman komprehensif konsep pencarian, pengurutan, stack, dan queue dengan tingkat kesulitan bertahap.', 'post-test', 'sedang', 'MP001', 'TA001', 15, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-20 07:37:15'),
('KUIS-TIK-B2-PRE', 'Pre-test Bab 2: Berpikir Komputasional', 'Tes awal untuk menguji pemahaman konsep dasar berpikir komputasional sebelum memulai materi.', 'pre-test', 'mudah', 'MP001', 'TA001', 10, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-20 07:37:15'),
('KUIS-TIK-B3-POST', 'Post-test Bab 3: Integrasi Aplikasi Perkantoran', 'Evaluasi penerapan integrasi data antar aplikasi pengolah kata, angka, dan presentasi.', 'post-test', 'sedang', 'MP001', 'TA001', 15, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-20 07:37:15'),
('KUIS-TIK-B3-PRE', 'Pre-test Bab 3: Integrasi Aplikasi Perkantoran', 'Tes diagnostik awal mengenai fungsi dasar aplikasi perkantoran dan fitur integrasi dokumen.', 'pre-test', 'mudah', 'MP001', 'TA001', 10, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-20 07:37:15'),
('KUIS-TIK-B4-POST', 'Post-test Bab 4: Sistem Komputer', 'Uji pemahaman komprehensif arsitektur komputer, mekanisme memori, dan interaksi sistem.', 'post-test', 'sedang', 'MP001', 'TA001', 15, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-20 07:37:15'),
('KUIS-TIK-B4-PRE', 'Pre-test Bab 4: Sistem Komputer', 'Uji pemahaman dasar mengenai komponen utama komputer dan cara kerjanya.', 'pre-test', 'mudah', 'MP001', 'TA001', 10, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-20 07:37:15'),
('KUIS-TIK-B5-POST', 'Post-test Bab 5: Jaringan Komputer dan Internet', 'Uji pemahaman komprehensif arsitektur topologi jaringan, pengalamatan IP, dan teknik proteksi data.', 'post-test', 'sedang', 'MP001', 'TA001', 15, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-20 07:37:15'),
('KUIS-TIK-B5-PRE', 'Pre-test Bab 5: Jaringan Komputer dan Internet', 'Uji pemahaman dasar mengenai tipe jaringan, perangkat komunikasi, dan koneksi internet.', 'pre-test', 'mudah', 'MP001', 'TA001', 10, 75.00, NULL, NULL, 'U001', 'terbit', 'setelah_submit', 'U001', 1, '2026-11-20 07:37:15');

-- --------------------------------------------------------

--
-- Table structure for table `tm_mapel`
--

CREATE TABLE `tm_mapel` (
  `id_mapel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_mapel` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_mapel` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tm_mapel`
--

INSERT INTO `tm_mapel` (`id_mapel`, `kode_mapel`, `nama_mapel`, `deskripsi`, `is_aktif`, `created_at`, `updated_at`) VALUES
('MP001', 'TIK', 'Teknologi Informasi dan Komunikasi', NULL, 1, '2026-11-12 18:22:32', '2026-11-12 18:22:32'),
('MP002', 'BINDO', 'Bahasa Indonesia', 'Bahasa indonesia', 1, '2026-11-20 09:02:17', '2026-11-20 09:02:17'),
('MP003', 'MTK', 'Matematika', NULL, 1, '2026-11-20 09:02:28', '2026-11-20 09:02:28');

-- --------------------------------------------------------

--
-- Table structure for table `tm_materi`
--

CREATE TABLE `tm_materi` (
  `id_materi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bab` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_mapel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `isi_materi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tm_materi`
--

INSERT INTO `tm_materi` (`id_materi`, `judul`, `bab`, `kelas`, `id_mapel`, `deskripsi`, `isi_materi`, `link_youtube`, `id_user`, `is_aktif`, `created_at`, `updated_at`) VALUES
('M001', 'Pengenalan HTML', '1', 'X TKJ 1', 'MP001', 'Materi dasar tentang struktur halaman HTML.', 'materi/TUdrPqe7bmNgt2eX1rAgpbkCvbFdtFCwbC6ZEGts.pdf', 'https://www.youtube.com/watch?v=ok-plXXHlWw', 'U001', 1, '2026-11-12 18:22:32', '2026-11-20 07:55:16'),
('M002', 'CSS Dasar', '2', 'X', 'MP001', 'Materi dasar tentang styling elemen halaman.', 'CSS digunakan untuk mengatur tampilan dan layout halaman web.', 'https://www.youtube.com/watch?v=1Rs2ND1ryYc', 'U001', 1, '2026-11-12 18:22:32', '2026-11-12 18:22:32'),
('M003', 'JavaScript Fundamental', '3', 'XI', 'MP001', 'Dasar-dasar JavaScript untuk interaksi halaman.', 'JavaScript membuat halaman web menjadi interaktif.', 'https://www.youtube.com/watch?v=W6NZfCO5SIk', 'U001', 1, '2026-11-12 18:22:32', '2026-11-12 18:22:32'),
('MAT-TIK-X-B2', 'Berpikir Komputasional: Pencarian, Pengurutan, dan Struktur Data', 'Bab 2', 'X', 'MP001', 'Mempelajari 4 pilar computational thinking, metode pencarian (Linear & Binary Search), pengurutan (Bubble, Insertion, Selection Sort), serta struktur data Stack dan Queue.\n\nTopik Pembahasan:\n1. Algoritma Pencarian (Searching): Linear Search memeriksa satu per satu elemen data. Binary Search memerlukan data terurut untuk mencari dengan membagi dua area pencarian.\n2. Algoritma Pengurutan (Sorting): Mengatur elemen dalam urutan menaik atau menurun menggunakan Bubble Sort, Selection Sort, atau Insertion Sort.\n3. Struktur Data Tumpukan (Stack): Prinsip LIFO (Last In First Out) seperti tumpukan piring.\n4. Struktur Data Antrean (Queue): Prinsip FIFO (First In First Out) seperti antrean tiket.', NULL, 'https://www.youtube.com/watch?v=mUXo-S8gkws', 'U001', 1, '2026-11-20 07:37:15', '2026-11-20 07:37:15'),
('MAT-TIK-X-B3', 'Teknologi Informasi dan Komunikasi: Integrasi Aplikasi Perkantoran', 'Bab 3', 'X', 'MP001', 'Mempelajari integrasi konten antar aplikasi perkantoran (pengolah kata, angka, presentasi), fitur Mail Merge, Object Linking and Embedding (OLE), dan pembuatan laporan terstruktur.\n\nTopik Utama:\n1. Integrasi Konten: Memasukkan tabel atau grafik dari pengolah angka (Excel/Spreadsheet) ke dalam dokumen pengolah kata (Word) atau presentasi (PowerPoint).\n2. Teknik Cut/Copy-Paste vs Object Linking and Embedding (OLE): Pada OLE, perubahan data di berkas sumber dapat diperbarui secara otomatis pada berkas tujuan.\n3. Fitur Mail Merge (Surat Massal): Menghasilkan surat, label undangan, atau sertifikat dalam jumlah banyak dengan format sama namun data penerima berbeda secara otomatis.\n4. Fitur Lanjut: Pembuatan daftar isi otomatis (Table of Contents) menggunakan Heading Styles.', NULL, 'https://www.youtube.com/watch?v=e_04ZrNroTo', 'U001', 1, '2026-11-20 07:37:15', '2026-11-20 07:37:15'),
('MAT-TIK-X-B4', 'Sistem Komputer: Perangkat Keras, Perangkat Lunak, dan Interaksi', 'Bab 4', 'X', 'MP001', 'Mempelajari komponen arsitektur komputer (von Neumann), siklus pemrosesan data (Fetch-Decode-Execute), perangkat keras, perangkat lunak, dan interaksi manusia-komputer (HCI).\n\nKomponen Pokok:\n1. Perangkat Keras (Hardware): Input Device (Keyboard, Mouse), Processing Unit (CPU, ALU, CU), Storage Device (RAM, ROM, SSD/HDD), dan Output Device (Monitor, Printer).\n2. Perangkat Lunak (Software): Sistem Operasi (Windows, Linux, macOS) yang mengelola sumber daya perangkat keras, dan Perangkat Lunak Aplikasi.\n3. Interaksi Manusia dan Komputer (IMK): Berbagai antarmuka seperti GUI (Graphical User Interface), CLI (Command Line Interface), dan antarmuka berbasis suara/gerak.\n4. Siklus Mesin CPU: Siklus Ambil-Terjemah-Eksekusi (Fetch, Decode, Execute).', NULL, 'https://www.youtube.com/watch?v=AkFi90lZmXA', 'U001', 1, '2026-11-20 07:37:15', '2026-11-20 07:37:15'),
('MAT-TIK-X-B5', 'Jaringan Komputer dan Internet: Konektivitas dan Keamanan Data', 'Bab 5', 'X', 'MP001', 'Mempelajari klasifikasi jaringan komputer (LAN, MAN, WAN), perangkat keras jaringan, protokol komunikasi (TCP/IP), mekanisme routing, dan proteksi data di internet.\n\nTopik Utama:\n1. Klasifikasi Jaringan Berdasarkan Cakupan: LAN (Local Area Network), MAN (Metropolitan Area Network), WAN (Wide Area Network), dan Internet.\n2. Topologi Jaringan: Bus, Star, Ring, Mesh, dan Tree beserta kelebihan dan kekurangannya.\n3. Perangkat Jaringan: Router (mengarahkan paket data antarjaringan berbeda), Switch (menghubungkan perangkat di jaringan lokal secara cerdas), Access Point (koneksi nirkabel).\n4. Keamanan Jaringan: Enkripsi data (HTTPS/SSL), bahaya phishing, malware, dan pentingnya penggunaan kata sandi yang kuat.', NULL, 'https://www.youtube.com/watch?v=0Q7bY8b3KjQ', 'U001', 1, '2026-11-20 07:37:15', '2026-11-20 07:37:15');

-- --------------------------------------------------------

--
-- Table structure for table `tm_materis`
--

CREATE TABLE `tm_materis` (
  `id_materi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bab` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `isi_materi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path/nama file PDF materi',
  `link_youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Guru/operator yang mengelola materi ini',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tm_soal`
--

CREATE TABLE `tm_soal` (
  `id_soal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kuis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` enum('pilihan_ganda','essay') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pilihan_ganda',
  `pertanyaan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `opsi_a` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opsi_b` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opsi_c` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opsi_d` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opsi_e` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jawaban` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bobot` decimal(5,2) NOT NULL DEFAULT '1.00',
  `tingkat_kesulitan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tm_soal`
--

INSERT INTO `tm_soal` (`id_soal`, `id_kuis`, `tipe`, `pertanyaan`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `opsi_e`, `jawaban`, `bobot`, `tingkat_kesulitan`) VALUES
('KUIS-TIK-B2-POST-S01', 'KUIS-TIK-B2-POST', 'pilihan_ganda', 'Karakteristik utama algoritma Binary Search yang membedakannya dari Linear Search adalah ...', 'Dapat langsung dijalankan pada data acak yang belum terurut', 'Data harus sudah dalam keadaan terurut terlebih dahulu', 'Hanya bisa digunakan untuk data bertipe teks', 'Selalu membutuhkan waktu lebih lama dibanding Linear Search', 'Hanya bisa mencari angka genap', 'B', 20.00, 'mudah'),
('KUIS-TIK-B2-POST-S02', 'KUIS-TIK-B2-POST', 'pilihan_ganda', 'Operasi untuk memasukkan elemen baru ke dalam tumpukan (Stack) disebut ...', 'Pop', 'Push', 'Enqueue', 'Dequeue', 'Peek', 'B', 20.00, 'mudah'),
('KUIS-TIK-B2-POST-S03', 'KUIS-TIK-B2-POST', 'pilihan_ganda', 'Diberikan sekumpulan data angka: [12, 5, 8, 1, 9]. Jika dilakukan Bubble Sort (ascending), data yang akan berpindah paling cepat ke posisi paling kanan pada putaran (pass) pertama adalah ...', '1', '5', '8', '9', '12', 'E', 20.00, 'sedang'),
('KUIS-TIK-B2-POST-S04', 'KUIS-TIK-B2-POST', 'pilihan_ganda', 'Sebuah antrean (Queue) mula-mula kosong. Dilakukan operasi berturut-turut: Enqueue(A), Enqueue(B), Dequeue(), Enqueue(C), Dequeue(). Elemen yang tersisa dalam antrean saat ini adalah ...', 'A', 'B', 'C', 'B dan C', 'Antrean kosong', 'C', 20.00, 'sedang'),
('KUIS-TIK-B2-POST-S05', 'KUIS-TIK-B2-POST', 'pilihan_ganda', 'Sebuah daftar terurut memiliki 64 elemen unik [1, 2, 3, ..., 64]. Jika kita mencari angka tertentu menggunakan algoritma Binary Search, berapa kali perbandingan maksimal (worst-case) yang dibutuhkan sampai elemen ditemukan atau dipastikan tidak ada?', '5 kali', '6 kali', '7 kali', '32 kali', '64 kali', 'C', 20.00, 'sulit'),
('KUIS-TIK-B2-PRE-S01', 'KUIS-TIK-B2-PRE', 'pilihan_ganda', 'Kemampuan memecahkan masalah besar menjadi bagian-bagian yang lebih kecil dan mudah dikelola dalam berpikir komputasional disebut ...', 'Dekomposisi', 'Abstraksi', 'Pengenalan Pola', 'Algoritma', 'Debugging', 'A', 20.00, 'mudah'),
('KUIS-TIK-B2-PRE-S02', 'KUIS-TIK-B2-PRE', 'pilihan_ganda', 'Struktur data tumpukan (Stack) bekerja berdasarkan prinsip dasar ...', 'FIFO (First In First Out)', 'LIFO (Last In First Out)', 'LILO (Last In Last Out)', 'Random Access', 'Linear Sorting', 'B', 20.00, 'mudah'),
('KUIS-TIK-B2-PRE-S03', 'KUIS-TIK-B2-PRE', 'pilihan_ganda', 'Antrean loket pembelian tiket bioskop merupakan contoh nyata dalam kehidupan sehari-hari dari struktur data ...', 'Tree', 'Graph', 'Stack', 'Queue', 'Array dua dimensi', 'D', 20.00, 'mudah'),
('KUIS-TIK-B2-PRE-S04', 'KUIS-TIK-B2-PRE', 'pilihan_ganda', 'Proses mengatur sekumpulan data menjadi urutan membesar (misal dari A ke Z atau dari 1 ke 100) disebut urutan ...', 'Descending', 'Ascending', 'Filtering', 'Indexing', 'Merging', 'B', 20.00, 'mudah'),
('KUIS-TIK-B2-PRE-S05', 'KUIS-TIK-B2-PRE', 'pilihan_ganda', 'Metode pencarian data di mana setiap elemen diperiksa satu persatu secara berurutan dari awal sampai akhir disebut ...', 'Binary Search', 'Linear Search', 'Interpolation Search', 'Bubble Search', 'Hashing', 'B', 20.00, 'mudah'),
('KUIS-TIK-B3-POST-S01', 'KUIS-TIK-B3-POST', 'pilihan_ganda', 'Dua dokumen utama yang dibutuhkan saat menjalankan proses pembuatan surat dengan Mail Merge adalah ...', 'Dokumen gambar dan dokumen suara', 'Dokumen utama (template) dan sumber data (data source)', 'Dokumen presentasi dan berkas video', 'File installer dan file konfigurasi', 'Dokumen PDF dan dokumen scan', 'B', 20.00, 'mudah'),
('KUIS-TIK-B3-POST-S02', 'KUIS-TIK-B3-POST', 'pilihan_ganda', 'Singkatan OLE dalam konteks integrasi konten aplikasi perkantoran adalah ...', 'Online Learning Environment', 'Object Linking and Embedding', 'Output Line Evaluation', 'Open Layout Element', 'Operating Level Execution', 'B', 20.00, 'mudah'),
('KUIS-TIK-B3-POST-S03', 'KUIS-TIK-B3-POST', 'pilihan_ganda', 'Andi menyalin tabel data dari Excel ke Word dengan opsi \"Paste Link\". Jika Andi mengubah nilai angka pada file Excel sumber, apa yang akan terjadi pada dokumen Word?', 'Dokumen Word akan rusak (corrupt)', 'Tabel di Word otomatis ikut terbarui menyesuaikan nilai di Excel', 'Tabel di Word tidak berubah sama sekali sampai dihapus dan disalin ulang', 'File Excel akan terhapus otomatis', 'Tabel di Word berubah format menjadi gambar statis', 'B', 20.00, 'sedang'),
('KUIS-TIK-B3-POST-S04', 'KUIS-TIK-B3-POST', 'pilihan_ganda', 'Perbedaan mendasar antara teknik \"Embedding\" dan \"Linking\" pada integrasi konten adalah ...', 'Embedding menyimpan objek secara mandiri di dalam berkas tujuan, sedangkan Linking hanya menyimpan rujukan ke berkas sumber', 'Linking membuat ukuran file tujuan menjadi jauh lebih besar dibanding Embedding', 'Embedding hanya dapat digunakan pada teks, sedangkan Linking hanya untuk grafik', 'Embedding memerlukan koneksi internet, sedangkan Linking bersifat offline', 'Linking otomatis menghapus data sumber setelah ditempel', 'A', 20.00, 'sedang'),
('KUIS-TIK-B3-POST-S05', 'KUIS-TIK-B3-POST', 'pilihan_ganda', 'Panitia ujian sekolah ingin mencetak 500 kartu peserta ujian lengkap dengan foto, barcode nomor ujian, dan ruang kelas yang dinamis dari basis data spreadsheet. Masalah yang sering terjadi adalah foto peserta tidak muncul saat Mail Merge digabungkan. Solusi teknis yang paling tepat menurut panduan integrasi tingkat lanjut adalah ...', 'Menempelkan foto secara manual satu per satu ke 500 halaman', 'Menyimpan path direktori foto pada spreadsheet dan memanfaatkan field code { INCLUDEPICTURE } yang digabungkan dengan merge field', 'Mengubah seluruh file spreadsheet menjadi berkas PDF terlebih dahulu', 'Menonaktifkan proteksi sistem operasi agar foto terdeteksi otomatis', 'Menghapus barcode agar ukuran foto sesuai dengan margin', 'B', 20.00, 'sulit'),
('KUIS-TIK-B3-PRE-S01', 'KUIS-TIK-B3-PRE', 'pilihan_ganda', 'Fitur pada aplikasi pengolah kata yang digunakan untuk membuat surat massal dengan data penerima berbeda secara otomatis adalah ...', 'Track Changes', 'Mail Merge', 'Macro Script', 'Page Break', 'Table of Figures', 'B', 20.00, 'mudah'),
('KUIS-TIK-B3-PRE-S02', 'KUIS-TIK-B3-PRE', 'pilihan_ganda', 'Aplikasi perkantoran yang paling tepat digunakan untuk mengolah data tabel angka, rumus statistik, dan pembuatan grafik visual adalah ...', 'Aplikasi pengolah kata', 'Aplikasi lembar kerja (Spreadsheet)', 'Aplikasi presentasi', 'Aplikasi pemutar video', 'Aplikasi editor gambar vektor', 'B', 20.00, 'mudah'),
('KUIS-TIK-B3-PRE-S03', 'KUIS-TIK-B3-PRE', 'pilihan_ganda', 'Kombinasi tombol pintasan (keyboard shortcut) standar untuk menyalin (copy) objek yang dipilih adalah ...', 'Ctrl + V', 'Ctrl + X', 'Ctrl + C', 'Ctrl + Z', 'Ctrl + P', 'C', 20.00, 'mudah'),
('KUIS-TIK-B3-PRE-S04', 'KUIS-TIK-B3-PRE', 'pilihan_ganda', 'Agar daftar isi dalam dokumen pengolah kata dapat dibuat secara otomatis dan rapi, format judul dan subjudul sebaiknya diatur menggunakan ...', 'Bold biasa', 'Styles (Heading 1, Heading 2)', 'Underline dan spasi ganda', 'Bullet and Numbering biasa', 'Font size manual', 'B', 20.00, 'mudah'),
('KUIS-TIK-B3-PRE-S05', 'KUIS-TIK-B3-PRE', 'pilihan_ganda', 'Format file dokumen standar yang dirancang agar tata letak dan tampilannya tidak berubah ketika dibuka di berbagai perangkat adalah ...', '.txt', '.pdf', '.docx', '.rtf', '.bat', 'B', 20.00, 'mudah'),
('KUIS-TIK-B4-POST-S01', 'KUIS-TIK-B4-POST', 'pilihan_ganda', 'Unit pada prosesor (CPU) yang bertanggung jawab melakukan perhitungan aritmatika dan operasi logika biner adalah ...', 'CU (Control Unit)', 'ALU (Arithmetic Logic Unit)', 'Register', 'L3 Cache', 'Bus Interface', 'B', 20.00, 'mudah'),
('KUIS-TIK-B4-POST-S02', 'KUIS-TIK-B4-POST', 'pilihan_ganda', 'Contoh sistem operasi sumber terbuka (open source) yang bebas dikembangkan dan dimodifikasi adalah ...', 'Microsoft Windows 11', 'Apple macOS', 'Linux Ubuntu', 'Apple iOS', 'MS-DOS berlisensi komersial', 'C', 20.00, 'mudah'),
('KUIS-TIK-B4-POST-S03', 'KUIS-TIK-B4-POST', 'pilihan_ganda', 'Tiga tahapan utama yang dilakukan prosesor secara berulang-ulang dalam siklus instruksi mesin adalah ...', 'Booting - Formatting - Defragmenting', 'Fetch (Ambil) - Decode (Terjemah) - Execute (Eksekusi)', 'Compress - Encrypt - Upload', 'Input - Print - Scan', 'Read - Overwrite - Delete', 'B', 20.00, 'sedang'),
('KUIS-TIK-B4-POST-S04', 'KUIS-TIK-B4-POST', 'pilihan_ganda', 'Mengapa memori Cache pada prosesor dibuat dengan kapasitas yang relatif kecil tetapi memiliki kecepatan transfer data tertinggi di antara jenis memori lainnya?', 'Karena biaya produksi silikon statis (SRAM) sangat mahal dan letaknya menyatu di dalam inti CPU', 'Agar panas yang dihasilkan prosesor tetap dingin', 'Karena Cache hanya berfungsi saat komputer dimatikan', 'Untuk membatasi jumlah aplikasi yang boleh diinstal pengguna', 'Karena Cache hanya menyimpan file dokumen berformat teks', 'A', 20.00, 'sedang'),
('KUIS-TIK-B4-POST-S05', 'KUIS-TIK-B4-POST', 'pilihan_ganda', 'Sebuah komputer server mengalami penurunan performa drastis (*thrashing*), di mana aktivitas lampu indikator media penyimpanan (SSD/HDD) menyala terus-menerus dengan penggunaan CPU rendah. Berdasarkan konsep manajemen memori sistem operasi, penyebab utama fenomena ini dan tindakan korektif yang paling tepat adalah ...', 'Terjadi overheat pada GPU; tindakan tepat adalah mengganti pasta pendingin prosesor grafis', 'Kapasitas RAM fisik tidak mencukupi sehingga sistem operasi terlalu sering menukar data antara RAM dan Virtual Memory (paging); tindakan tepat adalah menambah kapasitas modul RAM', 'Koneksi kabel LAN putus; tindakan tepat adalah memperbarui driver kartu jaringan', 'Kapasitas monitor terlalu besar; tindakan tepat adalah menurunkan resolusi layar monitor', 'Format partisi harddisk salah; tindakan tepat adalah membersihkan debu kipas pendingin', 'B', 20.00, 'sulit'),
('KUIS-TIK-B4-PRE-S01', 'KUIS-TIK-B4-PRE', 'pilihan_ganda', 'Komponen perangkat keras komputer yang sering disebut sebagai \"otak komputer\" karena bertugas memproses semua instruksi adalah ...', 'RAM', 'Hard Disk', 'CPU (Central Processing Unit)', 'Power Supply', 'VGA Card', 'C', 20.00, 'mudah'),
('KUIS-TIK-B4-PRE-S02', 'KUIS-TIK-B4-PRE', 'pilihan_ganda', 'Memori utama komputer yang bersifat sementara (volatile), di mana datanya akan hilang jika aliran listrik terputus adalah ...', 'ROM', 'RAM', 'Flashdisk', 'Hard Disk Drive', 'Solid State Drive', 'B', 20.00, 'mudah'),
('KUIS-TIK-B4-PRE-S03', 'KUIS-TIK-B4-PRE', 'pilihan_ganda', 'Manakah di bawah ini yang seluruhnya tergolong sebagai perangkat masukan (input device)?', 'Monitor, Printer, Speaker', 'Keyboard, Mouse, Scanner', 'Proyektor, Harddisk, RAM', 'CPU, Motherboard, VGA', 'Webcam, Speaker, Plotter', 'B', 20.00, 'mudah'),
('KUIS-TIK-B4-PRE-S04', 'KUIS-TIK-B4-PRE', 'pilihan_ganda', 'Perangkat lunak yang berfungsi sebagai perantara antara pengguna, aplikasi, dan perangkat keras komputer disebut ...', 'Sistem Operasi (Operating System)', 'Antivirus', 'Compiler', 'Web Browser', 'Media Player', 'A', 20.00, 'mudah'),
('KUIS-TIK-B4-PRE-S05', 'KUIS-TIK-B4-PRE', 'pilihan_ganda', 'Antarmuka pengguna yang memanfaatkan elemen grafis seperti ikon, tombol, dan jendela visual disebut ...', 'CLI (Command Line Interface)', 'GUI (Graphical User Interface)', 'API (Application Programming Interface)', 'BIOS', 'Terminal Prompt', 'B', 20.00, 'mudah'),
('KUIS-TIK-B5-POST-S01', 'KUIS-TIK-B5-POST', 'pilihan_ganda', 'Kelebihan utama topologi Star dibandingkan topologi Bus konvensional adalah ...', 'Tidak membutuhkan kabel sama sekali', 'Jika satu kabel komputer klien putus, jaringan komputer lainnya tetap dapat beroperasi normal', 'Biaya instalasi selalu paling murah', 'Tidak membutuhkan perangkat switch atau hub', 'Kecepatan internet otomatis naik 100 kali lipat', 'B', 20.00, 'mudah'),
('KUIS-TIK-B5-POST-S02', 'KUIS-TIK-B5-POST', 'pilihan_ganda', 'Metode penipuan digital di mana pelaku memalsukan halaman web login bank atau media sosial untuk mencuri kata sandi pengguna disebut ...', 'Phishing', 'Dekomposisi', 'Sorting', 'Tethering', 'Overclocking', 'A', 20.00, 'mudah'),
('KUIS-TIK-B5-POST-S03', 'KUIS-TIK-B5-POST', 'pilihan_ganda', 'Perbedaan mendasar antara perangkat Switch dan Hub pada jaringan lokal adalah ...', 'Hub memeriksa alamat MAC tujuan sebelum mengirim data, sedangkan Switch membroadcast data ke semua port', 'Switch membaca MAC address tujuan dan hanya meneruskan frame ke port tujuan, sedangkan Hub meneruskan sinyal ke seluruh port tanpa filter', 'Hub hanya bisa digunakan untuk jaringan nirkabel (wireless)', 'Switch tidak memerlukan sumber daya listrik sama sekali', 'Hub selalu memiliki kecepatan transfer lebih tinggi daripada Switch', 'B', 20.00, 'sedang'),
('KUIS-TIK-B5-POST-S04', 'KUIS-TIK-B5-POST', 'pilihan_ganda', 'Dua komputer berada pada jaringan yang sama dengan subnet mask 255.255.255.0. Komputer A memiliki IP 192.168.1.10. Manakah di bawah ini alamat IP yang valid agar Komputer B dapat berkomunikasi langsung tanpa router?', '192.168.2.10', '192.168.1.25', '10.0.0.1', '172.16.1.10', '192.168.1.255', 'B', 20.00, 'sedang'),
('KUIS-TIK-B5-POST-S05', 'KUIS-TIK-B5-POST', 'pilihan_ganda', 'Sebuah instansi perbankan ingin menghubungkan 6 kantor cabang utama dengan tingkat keandalan tertinggi (fault tolerance maksimal), di mana kegagalan beberapa jalur kabel tidak boleh memutus komunikasi antar kantor manapun. Jika instansi menggunakan topologi Full Mesh, berapa total jumlah jalur kabel fisik langsung yang harus dipasang antar ke-6 cabang tersebut?', '6 jalur', '12 jalur', '15 jalur', '30 jalur', '36 jalur', 'C', 20.00, 'sulit'),
('KUIS-TIK-B5-PRE-S01', 'KUIS-TIK-B5-PRE', 'pilihan_ganda', 'Jaringan komputer yang menghubungkan komputer dalam ruangan atau gedung yang sama, seperti lab komputer sekolah, disebut ...', 'LAN (Local Area Network)', 'MAN (Metropolitan Area Network)', 'WAN (Wide Area Network)', 'Internet', 'PAN antarbenua', 'A', 20.00, 'mudah'),
('KUIS-TIK-B5-PRE-S02', 'KUIS-TIK-B5-PRE', 'pilihan_ganda', 'Topologi jaringan di mana semua perangkat komputer terhubung langsung ke satu perangkat konsentrator pusat (Switch/Hub) disebut ...', 'Topologi Bus', 'Topologi Star', 'Topologi Ring', 'Topologi Mesh', 'Topologi Peer-to-Peer', 'B', 20.00, 'mudah'),
('KUIS-TIK-B5-PRE-S03', 'KUIS-TIK-B5-PRE', 'pilihan_ganda', 'Perangkat jaringan yang bertugas meneruskan dan mengarahkan paket data antara dua atau lebih jaringan yang berbeda alamat IP adalah ...', 'Router', 'Monitor', 'Sound Card', 'VGA Card', 'RAM', 'A', 20.00, 'mudah'),
('KUIS-TIK-B5-PRE-S04', 'KUIS-TIK-B5-PRE', 'pilihan_ganda', 'Protokol pada peramban web (browser) yang menandakan bahwa komunikasi data antara pengguna dan situs web telah terenkripsi secara aman adalah ...', 'HTTP', 'HTTPS', 'FTP', 'SMTP', 'Telnet', 'B', 20.00, 'mudah'),
('KUIS-TIK-B5-PRE-S05', 'KUIS-TIK-B5-PRE', 'pilihan_ganda', 'Nomor identitas unik yang diberikan kepada setiap perangkat yang terhubung dalam jaringan komputer berbasis protokol TCP/IP disebut ...', 'Serial Number', 'IP Address', 'Barcode', 'ZIP Code', 'IMEI Layar', 'B', 20.00, 'mudah'),
('S001', 'K001', 'pilihan_ganda', 'Tag HTML yang digunakan untuk membuat judul utama adalah?', '<h1>', '<p>', '<div>', '<span>', NULL, 'A', 50.00, NULL),
('S002', 'K001', 'pilihan_ganda', 'Atribut HTML untuk menentukan alamat tautan adalah?', 'src', 'href', 'alt', 'class', NULL, 'B', 50.00, NULL),
('S003', 'K002', 'pilihan_ganda', 'Properti CSS untuk mengubah warna teks adalah?', 'background-color', 'font-size', 'color', 'text-style', NULL, 'C', 50.00, NULL),
('S004', 'K002', 'pilihan_ganda', 'Properti CSS yang digunakan untuk mengatur jarak dalam elemen adalah?', 'margin', 'padding', 'border', 'display', NULL, 'B', 50.00, NULL),
('S005', 'K003', 'pilihan_ganda', 'Kata kunci untuk mendeklarasikan variabel yang nilainya dapat berubah adalah?', 'const', 'static', 'let', 'fixed', NULL, 'C', 50.00, NULL),
('S006', 'K003', 'pilihan_ganda', 'Method JavaScript untuk menampilkan pesan ke console adalah?', 'console.log()', 'print.console()', 'message.log()', 'write.console()', NULL, 'A', 50.00, NULL),
('S007', 'K005', 'essay', 'yy', NULL, NULL, NULL, NULL, NULL, NULL, 100.00, 'mudah');

-- --------------------------------------------------------

--
-- Table structure for table `tm_tahun_ajaran`
--

CREATE TABLE `tm_tahun_ajaran` (
  `id_tahun_ajaran` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tm_tahun_ajaran`
--

INSERT INTO `tm_tahun_ajaran` (`id_tahun_ajaran`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `is_aktif`, `created_at`, `updated_at`) VALUES
('TA001', '2026/2027', NULL, NULL, 1, '2026-11-12 18:22:32', '2026-11-12 18:22:32');

-- --------------------------------------------------------

--
-- Table structure for table `tm_user`
--

CREATE TABLE `tm_user` (
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_induk` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','guru','siswa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wajib_ganti_password` tinyint(1) NOT NULL DEFAULT '0',
  `dibuat_oleh` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tm_user`
--

INSERT INTO `tm_user` (`id_user`, `nomor_induk`, `nama_lengkap`, `role`, `status`, `kelas`, `email`, `password`, `wajib_ganti_password`, `dibuat_oleh`, `created_at`, `updated_at`) VALUES
('U001', '198001', 'Budi Guru', 'guru', 'aktif', NULL, 'budi@guru.sch.id', '$2y$12$XDLYPQwqVF8sPJjfr6MEluADcnz7pcqtHb0fQ0bjXIocjWevabwhG', 0, NULL, '2026-11-12 18:22:31', '2026-11-12 18:22:31'),
('U002', '202401', 'Siti Siswa', 'siswa', 'aktif', 'X', 'siti@siswa.sch.id', '$2y$12$hd4w4nLDq65HNzArFR8IU.clZrzdAg6x.IOOqTauGCnG2MyWVtWhW', 0, NULL, '2026-11-12 18:22:31', '2026-11-12 18:22:31'),
('U003', '199001', 'Andi Operator', 'admin', 'aktif', NULL, 'andi@sekolah.sch.id', '$2y$12$D5qDVc6/I1T//Co9CxmheuPh1/tjhB7/tF11D/92UEzqIv8XTqKR2', 0, NULL, '2026-11-12 18:22:31', '2026-11-12 18:22:31'),
('U004', '10101010', 'Sadan bukan guru', 'guru', 'aktif', NULL, NULL, '$2y$12$XYJVSYu.Y.5DpgXPc2NClurehyMnwEO3uBQ/CKmR2a0HlKisWliyq', 1, 'U003', '2026-11-20 09:06:38', '2026-11-20 09:06:38'),
('U005', '0987654321', 'Desu guru gadungan', 'guru', 'aktif', NULL, NULL, '$2y$12$p20cA3fSMT1axZ8.kS6Ax.byvBtf9vJGVEAdmHkqDGQH122HDrSA2', 1, 'U003', '2026-11-20 09:07:07', '2026-11-20 09:07:07'),
('U006', '0113377232', 'ABI REPALDI', 'siswa', 'aktif', 'X', NULL, '$2y$12$3sOBawELawoQydsEwK8T3.mg06lNQR0kYz63jkk7BDo/mZAmXBzPe', 1, 'U003', '2026-11-20 09:14:08', '2026-11-20 09:14:08'),
('U007', '0106366752', 'ACHMAD ZAKY MAULANA', 'siswa', 'aktif', NULL, NULL, '$2y$12$YLDWpyC2COXN2QIr92MLSOyb8FSBMWfEvoNtTyfiRkdjcI9um5JXu', 1, 'U003', '2026-11-20 09:15:00', '2026-11-20 09:15:25'),
('U008', '0116106095', 'JIMI SAPUTRA OKTAVIMBER', 'siswa', 'aktif', 'X', NULL, '$2y$12$ci0dvFcXwU8fGsSOCZpi8.zaZyOIPPuElCjW98YUWPEGBP6esfkRC', 1, 'U003', '2026-11-20 09:23:25', '2026-11-20 09:23:25'),
('U009', '0093136564', 'KEVIN PUTRA MAHENDRA', 'siswa', 'aktif', 'XI', NULL, '$2y$12$nCOL5RnmT.fJOq6We2VucegMi8QdMWh9RBRplWq4IJb0YseIaaCZO', 1, 'U003', '2026-11-20 09:25:50', '2026-11-20 09:25:50'),
('U010', '0091409445', 'REHAN TRI WAHYU ADI PRASETYO', 'siswa', 'aktif', 'XI', NULL, '$2y$12$wXyWl50y7LFLIBaMSQ7bM.7iSLLKtjDOJz9zGyEVlM9.DCQFPGPYq', 1, 'U003', '2026-11-20 09:26:23', '2026-11-20 09:26:23');

-- --------------------------------------------------------

--
-- Table structure for table `tp_anggota_kelas`
--

CREATE TABLE `tp_anggota_kelas` (
  `id_anggota_kelas` bigint UNSIGNED NOT NULL,
  `id_kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tp_anggota_kelas`
--

INSERT INTO `tp_anggota_kelas` (`id_anggota_kelas`, `id_kelas`, `id_user`, `created_at`) VALUES
(1, 'KLS001', 'U002', '2026-11-13 01:22:32'),
(2, 'KLS001', 'U006', '2026-11-20 09:14:08'),
(4, 'KLS001', 'U007', '2026-11-20 09:21:59'),
(5, 'KLS002', 'U008', '2026-11-20 09:23:25'),
(6, 'KLS003', 'U009', '2026-11-20 09:25:50'),
(7, 'KLS004', 'U010', '2026-11-20 09:26:23');

-- --------------------------------------------------------

--
-- Table structure for table `tp_kuis_kelas`
--

CREATE TABLE `tp_kuis_kelas` (
  `id_kuis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tp_kuis_kelas`
--

INSERT INTO `tp_kuis_kelas` (`id_kuis`, `id_kelas`) VALUES
('K001', 'KLS001'),
('K002', 'KLS001'),
('K003', 'KLS001'),
('KUIS-TIK-B2-POST', 'KLS001'),
('KUIS-TIK-B2-PRE', 'KLS001'),
('KUIS-TIK-B3-POST', 'KLS001'),
('KUIS-TIK-B3-PRE', 'KLS001'),
('KUIS-TIK-B4-POST', 'KLS001'),
('KUIS-TIK-B4-PRE', 'KLS001'),
('KUIS-TIK-B5-POST', 'KLS001'),
('KUIS-TIK-B5-PRE', 'KLS001'),
('K001', 'KLS002'),
('K002', 'KLS002'),
('K003', 'KLS002'),
('KUIS-TIK-B2-POST', 'KLS002'),
('KUIS-TIK-B2-PRE', 'KLS002'),
('KUIS-TIK-B3-POST', 'KLS002'),
('KUIS-TIK-B3-PRE', 'KLS002'),
('KUIS-TIK-B4-POST', 'KLS002'),
('KUIS-TIK-B4-PRE', 'KLS002'),
('KUIS-TIK-B5-POST', 'KLS002'),
('KUIS-TIK-B5-PRE', 'KLS002');

-- --------------------------------------------------------

--
-- Table structure for table `tp_mapel_kelas`
--

CREATE TABLE `tp_mapel_kelas` (
  `id_mapel_kelas` bigint UNSIGNED NOT NULL,
  `id_mapel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tp_mapel_kelas`
--

INSERT INTO `tp_mapel_kelas` (`id_mapel_kelas`, `id_mapel`, `id_kelas`, `created_at`) VALUES
(1, 'MP001', 'KLS001', '2026-11-13 01:22:32'),
(2, 'MP001', 'KLS002', '2026-11-13 01:22:32'),
(3, 'MP001', 'KLS003', '2026-11-20 08:52:48'),
(4, 'MP001', 'KLS004', '2026-11-20 08:53:12'),
(5, 'MP002', 'KLS003', '2026-11-20 09:03:18'),
(6, 'MP003', 'KLS003', '2026-11-20 09:03:18'),
(7, 'MP002', 'KLS004', '2026-11-20 09:03:24'),
(8, 'MP003', 'KLS004', '2026-11-20 09:03:24'),
(9, 'MP002', 'KLS001', '2026-11-20 09:03:28'),
(10, 'MP003', 'KLS001', '2026-11-20 09:03:28'),
(11, 'MP002', 'KLS002', '2026-11-20 09:03:34'),
(12, 'MP003', 'KLS002', '2026-11-20 09:03:34');

-- --------------------------------------------------------

--
-- Table structure for table `tp_materi_kuis`
--

CREATE TABLE `tp_materi_kuis` (
  `id_materi_kuis` int UNSIGNED NOT NULL,
  `id_materi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kuis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tp_materi_kuis`
--

INSERT INTO `tp_materi_kuis` (`id_materi_kuis`, `id_materi`, `id_kuis`) VALUES
(1, 'M001', 'K001'),
(6, 'M001', 'K005'),
(2, 'M002', 'K001'),
(3, 'M002', 'K002'),
(4, 'M003', 'K003'),
(8, 'MAT-TIK-X-B2', 'KUIS-TIK-B2-POST'),
(7, 'MAT-TIK-X-B2', 'KUIS-TIK-B2-PRE'),
(10, 'MAT-TIK-X-B3', 'KUIS-TIK-B3-POST'),
(9, 'MAT-TIK-X-B3', 'KUIS-TIK-B3-PRE'),
(12, 'MAT-TIK-X-B4', 'KUIS-TIK-B4-POST'),
(11, 'MAT-TIK-X-B4', 'KUIS-TIK-B4-PRE'),
(14, 'MAT-TIK-X-B5', 'KUIS-TIK-B5-POST'),
(13, 'MAT-TIK-X-B5', 'KUIS-TIK-B5-PRE');

-- --------------------------------------------------------

--
-- Table structure for table `tr_draft_jawaban`
--

CREATE TABLE `tr_draft_jawaban` (
  `id_draft` int UNSIGNED NOT NULL,
  `id_kuis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Siswa yang mengerjakan',
  `id_soal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jawaban_dipilih` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A/B/C/D/E',
  `jawaban_teks` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tr_draft_jawaban`
--

INSERT INTO `tr_draft_jawaban` (`id_draft`, `id_kuis`, `id_user`, `id_soal`, `jawaban_dipilih`, `jawaban_teks`, `updated_at`) VALUES
(3, 'K001', 'U002', 'S001', 'A', NULL, '2026-11-12 19:48:03'),
(4, 'K001', 'U002', 'S002', 'B', NULL, '2026-11-12 19:48:04'),
(5, 'K005', 'U002', 'S007', NULL, 'mkdo0wmed', '2026-11-19 23:58:57');

-- --------------------------------------------------------

--
-- Table structure for table `tr_mengerjakan`
--

CREATE TABLE `tr_mengerjakan` (
  `id_mengerjakan` int UNSIGNED NOT NULL,
  `id_kuis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `percobaan_ke` int NOT NULL DEFAULT '1',
  `status_pengerjaan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'final',
  `status_validasi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum divalidasi',
  `id_validator` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tr_mengerjakan`
--

INSERT INTO `tr_mengerjakan` (`id_mengerjakan`, `id_kuis`, `id_user`, `nilai`, `tanggal`, `percobaan_ke`, `status_pengerjaan`, `status_validasi`, `id_validator`) VALUES
(1, 'K001', 'U002', 50.00, '2026-11-13', 1, 'final', 'sudah divalidasi', 'U001'),
(2, 'K001', 'U002', 100.00, '2026-11-13', 2, 'final', 'sudah divalidasi', 'U001'),
(3, 'K005', 'U002', NULL, '2026-11-20', 1, 'final', 'menunggu penilaian', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tr_nilai_essay`
--

CREATE TABLE `tr_nilai_essay` (
  `id_nilai_essay` int UNSIGNED NOT NULL,
  `id_mengerjakan` int UNSIGNED NOT NULL,
  `id_soal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jawaban_teks` text COLLATE utf8mb4_unicode_ci,
  `nilai` decimal(5,2) DEFAULT NULL,
  `catatan_guru` text COLLATE utf8mb4_unicode_ci,
  `id_penilai` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dinilai_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tr_nilai_essay`
--

INSERT INTO `tr_nilai_essay` (`id_nilai_essay`, `id_mengerjakan`, `id_soal`, `jawaban_teks`, `nilai`, `catatan_guru`, `id_penilai`, `dinilai_at`) VALUES
(1, 3, 'S007', 'mkdo0wmed', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tr_pengajaran`
--

CREATE TABLE `tr_pengajaran` (
  `id_pengajaran` int UNSIGNED NOT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kelas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_mapel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tr_pengajaran`
--

INSERT INTO `tr_pengajaran` (`id_pengajaran`, `id_user`, `id_kelas`, `id_mapel`, `created_at`) VALUES
(1, 'U001', 'KLS001', 'MP001', '2026-11-13 03:00:12'),
(2, 'U001', 'KLS002', 'MP001', '2026-11-13 03:00:12'),
(3, 'U001', 'KLS003', 'MP002', '2026-11-20 09:03:43'),
(4, 'U001', 'KLS004', 'MP002', '2026-11-20 09:03:43'),
(5, 'U005', 'KLS003', 'MP002', '2026-11-20 09:07:46'),
(6, 'U005', 'KLS003', 'MP003', '2026-11-20 09:07:46'),
(7, 'U005', 'KLS004', 'MP002', '2026-11-20 09:07:46'),
(8, 'U005', 'KLS004', 'MP003', '2026-11-20 09:07:46'),
(9, 'U004', 'KLS001', 'MP003', '2026-11-20 09:08:13'),
(10, 'U004', 'KLS003', 'MP003', '2026-11-20 09:08:13');

-- --------------------------------------------------------

--
-- Table structure for table `tr_progress`
--

CREATE TABLE `tr_progress` (
  `id_progress` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contoh: belum mulai, sedang belajar, selesai',
  `tanggal` date DEFAULT NULL,
  `id_materi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Siswa pemilik progress ini'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tr_progress`
--

INSERT INTO `tr_progress` (`id_progress`, `status`, `tanggal`, `id_materi`, `id_user`) VALUES
('PE9WI2UDDYF', 'selesai', '2026-11-15', 'M001', 'U002'),
('PJ0BR6VNCTF', 'sedang belajar', '2026-11-19', 'M002', 'U002');

-- --------------------------------------------------------

--
-- Table structure for table `tr_remedial`
--

CREATE TABLE `tr_remedial` (
  `id_remedial` int UNSIGNED NOT NULL,
  `id_kuis_remedial` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kuis dengan kategori=remedial',
  `id_kuis_asal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kuis asal (misal pass-test) yang jadi alasan remedial'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tr_remedial_siswa`
--

CREATE TABLE `tr_remedial_siswa` (
  `id_remedial_siswa` int UNSIGNED NOT NULL,
  `id_kuis_remedial` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_mengerjakan_asal` int UNSIGNED NOT NULL,
  `id_guru` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_ditugaskan` date NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ditugaskan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tr_sesi_kuis`
--

CREATE TABLE `tr_sesi_kuis` (
  `id_sesi` bigint UNSIGNED NOT NULL,
  `id_kuis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percobaan_ke` int UNSIGNED NOT NULL DEFAULT '1',
  `waktu_mulai` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_selesai` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tr_sesi_kuis`
--

INSERT INTO `tr_sesi_kuis` (`id_sesi`, `id_kuis`, `id_user`, `percobaan_ke`, `waktu_mulai`, `waktu_selesai`) VALUES
(1, 'K001', 'U002', 1, '2026-11-12 19:47:26', '2026-11-12 19:47:48'),
(2, 'K001', 'U002', 2, '2026-11-12 19:48:00', '2026-11-12 19:48:06'),
(3, 'K005', 'U002', 1, '2026-11-19 23:58:41', '2026-11-19 23:58:57');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

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
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tm_kelas`
--
ALTER TABLE `tm_kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD UNIQUE KEY `uq_kelas_nama_tahun` (`nama_kelas`,`id_tahun_ajaran`),
  ADD KEY `fk_kelas_tahun` (`id_tahun_ajaran`);

--
-- Indexes for table `tm_kuis`
--
ALTER TABLE `tm_kuis`
  ADD PRIMARY KEY (`id_kuis`),
  ADD KEY `fk_kuis_mapel` (`id_mapel`),
  ADD KEY `fk_kuis_tahun` (`id_tahun_ajaran`),
  ADD KEY `fk_kuis_user` (`id_user`),
  ADD KEY `fk_kuis_publisher` (`id_publisher`);

--
-- Indexes for table `tm_mapel`
--
ALTER TABLE `tm_mapel`
  ADD PRIMARY KEY (`id_mapel`),
  ADD UNIQUE KEY `tm_mapel_kode_mapel_unique` (`kode_mapel`);

--
-- Indexes for table `tm_materi`
--
ALTER TABLE `tm_materi`
  ADD PRIMARY KEY (`id_materi`),
  ADD UNIQUE KEY `uq_materi_judul` (`kelas`,`bab`,`judul`),
  ADD UNIQUE KEY `uq_materi_mapel_kelas_bab` (`id_mapel`,`kelas`,`bab`),
  ADD KEY `fk_materi_user` (`id_user`);

--
-- Indexes for table `tm_materis`
--
ALTER TABLE `tm_materis`
  ADD PRIMARY KEY (`id_materi`),
  ADD UNIQUE KEY `uq_materis_judul` (`kelas`,`bab`,`judul`);

--
-- Indexes for table `tm_soal`
--
ALTER TABLE `tm_soal`
  ADD PRIMARY KEY (`id_soal`),
  ADD KEY `fk_kuis` (`id_kuis`);

--
-- Indexes for table `tm_tahun_ajaran`
--
ALTER TABLE `tm_tahun_ajaran`
  ADD PRIMARY KEY (`id_tahun_ajaran`),
  ADD UNIQUE KEY `tm_tahun_ajaran_nama_unique` (`nama`);

--
-- Indexes for table `tm_user`
--
ALTER TABLE `tm_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `tm_user_nomor_induk_unique` (`nomor_induk`),
  ADD KEY `fk_user_dibuat_oleh` (`dibuat_oleh`);

--
-- Indexes for table `tp_anggota_kelas`
--
ALTER TABLE `tp_anggota_kelas`
  ADD PRIMARY KEY (`id_anggota_kelas`),
  ADD UNIQUE KEY `uq_anggota_kelas` (`id_kelas`,`id_user`),
  ADD KEY `fk_anggota_kelas_user` (`id_user`);

--
-- Indexes for table `tp_kuis_kelas`
--
ALTER TABLE `tp_kuis_kelas`
  ADD PRIMARY KEY (`id_kuis`,`id_kelas`),
  ADD KEY `tp_kuis_kelas_id_kelas_foreign` (`id_kelas`);

--
-- Indexes for table `tp_mapel_kelas`
--
ALTER TABLE `tp_mapel_kelas`
  ADD PRIMARY KEY (`id_mapel_kelas`),
  ADD UNIQUE KEY `uq_mapel_kelas` (`id_mapel`,`id_kelas`),
  ADD KEY `fk_mapelkelas_kelas` (`id_kelas`);

--
-- Indexes for table `tp_materi_kuis`
--
ALTER TABLE `tp_materi_kuis`
  ADD PRIMARY KEY (`id_materi_kuis`),
  ADD UNIQUE KEY `uq_materi_kuis` (`id_materi`,`id_kuis`),
  ADD KEY `fk_materikuis_kuis` (`id_kuis`);

--
-- Indexes for table `tr_draft_jawaban`
--
ALTER TABLE `tr_draft_jawaban`
  ADD PRIMARY KEY (`id_draft`),
  ADD UNIQUE KEY `uq_draft` (`id_kuis`,`id_user`,`id_soal`),
  ADD KEY `fk_draft_user` (`id_user`),
  ADD KEY `fk_draft_soal` (`id_soal`);

--
-- Indexes for table `tr_mengerjakan`
--
ALTER TABLE `tr_mengerjakan`
  ADD PRIMARY KEY (`id_mengerjakan`),
  ADD UNIQUE KEY `uq_kuis_siswa_percobaan` (`id_kuis`,`id_user`,`percobaan_ke`),
  ADD KEY `fk_mengerjakan_user` (`id_user`),
  ADD KEY `fk_mengerjakan_validator` (`id_validator`);

--
-- Indexes for table `tr_nilai_essay`
--
ALTER TABLE `tr_nilai_essay`
  ADD PRIMARY KEY (`id_nilai_essay`),
  ADD UNIQUE KEY `uq_nilai_essay` (`id_mengerjakan`,`id_soal`),
  ADD KEY `fk_nilai_essay_soal` (`id_soal`),
  ADD KEY `fk_nilai_essay_penilai` (`id_penilai`);

--
-- Indexes for table `tr_pengajaran`
--
ALTER TABLE `tr_pengajaran`
  ADD PRIMARY KEY (`id_pengajaran`),
  ADD UNIQUE KEY `uq_pengajaran` (`id_user`,`id_kelas`,`id_mapel`),
  ADD KEY `fk_pengajaran_mapel_kelas` (`id_mapel`,`id_kelas`);

--
-- Indexes for table `tr_progress`
--
ALTER TABLE `tr_progress`
  ADD PRIMARY KEY (`id_progress`),
  ADD UNIQUE KEY `uq_progress_materi_user` (`id_materi`,`id_user`),
  ADD KEY `fk_progress_materi` (`id_materi`),
  ADD KEY `fk_progress_user` (`id_user`);

--
-- Indexes for table `tr_remedial`
--
ALTER TABLE `tr_remedial`
  ADD PRIMARY KEY (`id_remedial`),
  ADD UNIQUE KEY `uq_kuis_remedial` (`id_kuis_remedial`),
  ADD KEY `fk_remedial_kuisasal` (`id_kuis_asal`);

--
-- Indexes for table `tr_remedial_siswa`
--
ALTER TABLE `tr_remedial_siswa`
  ADD PRIMARY KEY (`id_remedial_siswa`),
  ADD UNIQUE KEY `uq_remedial_siswa` (`id_kuis_remedial`,`id_mengerjakan_asal`),
  ADD KEY `fk_remedialsiswa_guru` (`id_guru`),
  ADD KEY `fk_remedialsiswa_mengerjakan` (`id_mengerjakan_asal`);

--
-- Indexes for table `tr_sesi_kuis`
--
ALTER TABLE `tr_sesi_kuis`
  ADD PRIMARY KEY (`id_sesi`),
  ADD UNIQUE KEY `uq_sesi_kuis_siswa_percobaan` (`id_kuis`,`id_user`,`percobaan_ke`),
  ADD KEY `fk_sesi_user` (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tp_anggota_kelas`
--
ALTER TABLE `tp_anggota_kelas`
  MODIFY `id_anggota_kelas` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tp_mapel_kelas`
--
ALTER TABLE `tp_mapel_kelas`
  MODIFY `id_mapel_kelas` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tp_materi_kuis`
--
ALTER TABLE `tp_materi_kuis`
  MODIFY `id_materi_kuis` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tr_draft_jawaban`
--
ALTER TABLE `tr_draft_jawaban`
  MODIFY `id_draft` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tr_mengerjakan`
--
ALTER TABLE `tr_mengerjakan`
  MODIFY `id_mengerjakan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tr_nilai_essay`
--
ALTER TABLE `tr_nilai_essay`
  MODIFY `id_nilai_essay` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tr_pengajaran`
--
ALTER TABLE `tr_pengajaran`
  MODIFY `id_pengajaran` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tr_remedial`
--
ALTER TABLE `tr_remedial`
  MODIFY `id_remedial` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tr_remedial_siswa`
--
ALTER TABLE `tr_remedial_siswa`
  MODIFY `id_remedial_siswa` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tr_sesi_kuis`
--
ALTER TABLE `tr_sesi_kuis`
  MODIFY `id_sesi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tm_kelas`
--
ALTER TABLE `tm_kelas`
  ADD CONSTRAINT `fk_kelas_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tm_tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tm_kuis`
--
ALTER TABLE `tm_kuis`
  ADD CONSTRAINT `fk_kuis_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `tm_mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuis_publisher` FOREIGN KEY (`id_publisher`) REFERENCES `tm_user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuis_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tm_tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuis_user` FOREIGN KEY (`id_user`) REFERENCES `tm_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tm_materi`
--
ALTER TABLE `tm_materi`
  ADD CONSTRAINT `fk_materi_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `tm_mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_materi_user` FOREIGN KEY (`id_user`) REFERENCES `tm_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tm_soal`
--
ALTER TABLE `tm_soal`
  ADD CONSTRAINT `fk_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tm_user`
--
ALTER TABLE `tm_user`
  ADD CONSTRAINT `fk_user_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `tm_user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tp_anggota_kelas`
--
ALTER TABLE `tp_anggota_kelas`
  ADD CONSTRAINT `fk_anggota_kelas_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `tm_kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_anggota_kelas_user` FOREIGN KEY (`id_user`) REFERENCES `tm_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tp_kuis_kelas`
--
ALTER TABLE `tp_kuis_kelas`
  ADD CONSTRAINT `tp_kuis_kelas_id_kelas_foreign` FOREIGN KEY (`id_kelas`) REFERENCES `tm_kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `tp_kuis_kelas_id_kuis_foreign` FOREIGN KEY (`id_kuis`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tp_mapel_kelas`
--
ALTER TABLE `tp_mapel_kelas`
  ADD CONSTRAINT `fk_mapelkelas_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `tm_kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mapelkelas_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `tm_mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tp_materi_kuis`
--
ALTER TABLE `tp_materi_kuis`
  ADD CONSTRAINT `fk_materikuis_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_materikuis_materi` FOREIGN KEY (`id_materi`) REFERENCES `tm_materi` (`id_materi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tr_draft_jawaban`
--
ALTER TABLE `tr_draft_jawaban`
  ADD CONSTRAINT `fk_draft_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_draft_soal` FOREIGN KEY (`id_soal`) REFERENCES `tm_soal` (`id_soal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_draft_user` FOREIGN KEY (`id_user`) REFERENCES `tm_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tr_mengerjakan`
--
ALTER TABLE `tr_mengerjakan`
  ADD CONSTRAINT `fk_mengerjakan_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mengerjakan_user` FOREIGN KEY (`id_user`) REFERENCES `tm_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mengerjakan_validator` FOREIGN KEY (`id_validator`) REFERENCES `tm_user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tr_nilai_essay`
--
ALTER TABLE `tr_nilai_essay`
  ADD CONSTRAINT `fk_nilai_essay_mengerjakan` FOREIGN KEY (`id_mengerjakan`) REFERENCES `tr_mengerjakan` (`id_mengerjakan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_essay_penilai` FOREIGN KEY (`id_penilai`) REFERENCES `tm_user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_essay_soal` FOREIGN KEY (`id_soal`) REFERENCES `tm_soal` (`id_soal`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tr_pengajaran`
--
ALTER TABLE `tr_pengajaran`
  ADD CONSTRAINT `fk_pengajaran_mapel_kelas` FOREIGN KEY (`id_mapel`,`id_kelas`) REFERENCES `tp_mapel_kelas` (`id_mapel`, `id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengajaran_user` FOREIGN KEY (`id_user`) REFERENCES `tm_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tr_progress`
--
ALTER TABLE `tr_progress`
  ADD CONSTRAINT `fk_progress_materi` FOREIGN KEY (`id_materi`) REFERENCES `tm_materi` (`id_materi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_progress_user` FOREIGN KEY (`id_user`) REFERENCES `tm_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tr_remedial`
--
ALTER TABLE `tr_remedial`
  ADD CONSTRAINT `fk_remedial_kuisasal` FOREIGN KEY (`id_kuis_asal`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedial_kuisremedial` FOREIGN KEY (`id_kuis_remedial`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tr_remedial_siswa`
--
ALTER TABLE `tr_remedial_siswa`
  ADD CONSTRAINT `fk_remedialsiswa_guru` FOREIGN KEY (`id_guru`) REFERENCES `tm_user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedialsiswa_kuis` FOREIGN KEY (`id_kuis_remedial`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedialsiswa_mengerjakan` FOREIGN KEY (`id_mengerjakan_asal`) REFERENCES `tr_mengerjakan` (`id_mengerjakan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tr_sesi_kuis`
--
ALTER TABLE `tr_sesi_kuis`
  ADD CONSTRAINT `fk_sesi_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `tm_kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sesi_user` FOREIGN KEY (`id_user`) REFERENCES `tm_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
