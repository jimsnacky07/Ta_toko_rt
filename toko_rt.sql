/*
SQLyog Ultimate v12.5.1 (64 bit)
MySQL - 10.4.32-MariaDB : Database - toko_rt
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`toko_rt` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `toko_rt`;

/*Table structure for table `cache` */

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cache` */

/*Table structure for table `data_ukuran_badan` */

DROP TABLE IF EXISTS `data_ukuran_badan`;

CREATE TABLE `data_ukuran_badan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lingkaran_dada` int(11) DEFAULT NULL,
  `lingkaran_pinggang` int(11) DEFAULT NULL,
  `lingkaran_pinggul` int(11) DEFAULT NULL,
  `lingkaran_leher` int(11) DEFAULT NULL,
  `lingkaran_lengan` int(11) DEFAULT NULL,
  `lingkaran_paha` int(11) DEFAULT NULL,
  `lingkaran_lutut` int(11) DEFAULT NULL,
  `panjang_baju` int(11) DEFAULT NULL,
  `panjang_lengan` int(11) DEFAULT NULL,
  `panjang_celana` int(11) DEFAULT NULL,
  `panjang_rok` int(11) DEFAULT NULL,
  `lebar_bahu` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_ukuran_badan_user_id_foreign` (`user_id`),
  CONSTRAINT `data_ukuran_badan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `data_ukuran_badan` */

insert  into `data_ukuran_badan`(`id`,`user_id`,`lingkaran_dada`,`lingkaran_pinggang`,`lingkaran_pinggul`,`lingkaran_leher`,`lingkaran_lengan`,`lingkaran_paha`,`lingkaran_lutut`,`panjang_baju`,`panjang_lengan`,`panjang_celana`,`panjang_rok`,`lebar_bahu`,`created_at`,`updated_at`) values 
(8,13,12,21,21,12,12,21,21,12,12,21,NULL,12,'2025-09-05 16:02:49','2025-09-05 16:06:47'),
(10,15,31,31,31,31,31,NULL,NULL,31,31,NULL,NULL,31,'2025-09-06 20:03:10','2025-09-06 20:03:10');

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `failed_jobs` */

/*Table structure for table `job_batches` */

DROP TABLE IF EXISTS `job_batches`;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `job_batches` */

/*Table structure for table `jobs` */

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `jobs` */

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values 
(1,'0001_01_01_000001_create_cache_table',1),
(2,'0001_01_01_000002_create_jobs_table',1),
(3,'2025_07_24_062649_create_users_table',1),
(4,'2025_08_10_082622_create_products_table',1),
(5,'2025_08_12_131248_create_data_ukuran_badan_table',1),
(6,'2025_08_26_200919_create_orders_table',1),
(7,'2025_08_28_042618_create_order_items_table',1),
(8,'2025_08_28_122015_create_order_items_table',1);

/*Table structure for table `order_items` */

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `garment_type` varchar(255) NOT NULL,
  `fabric_type` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `special_request` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `order_items` */

insert  into `order_items`(`id`,`order_id`,`product_id`,`garment_type`,`fabric_type`,`size`,`price`,`quantity`,`total_price`,`special_request`,`image`,`status`,`created_at`,`updated_at`) values 
(52,82,NULL,'Ready Made','Standard','L',100000.00,1,100000.00,NULL,NULL,'menunggu','2025-09-05 16:01:14','2025-09-05 16:01:14'),
(53,83,1,'celana_levis','jeans','Custom',310000.00,1,310000.00,'-',NULL,'menunggu','2025-09-05 16:01:14','2025-09-05 16:01:14'),
(54,85,NULL,'dress','katun','baju',255000.00,1,255000.00,'-',NULL,'pending','2025-09-05 16:02:49','2025-09-05 16:02:49'),
(55,87,NULL,'Ready Made','Standard','L',100000.00,1,100000.00,NULL,NULL,'menunggu','2025-09-05 16:05:25','2025-09-05 16:05:25'),
(56,88,1,'celana_levis','jeans','Custom',310000.00,1,310000.00,'-',NULL,'menunggu','2025-09-05 16:05:25','2025-09-05 16:05:25'),
(57,89,NULL,'celana_bahan','linen','celana',280000.00,1,280000.00,'-',NULL,'siap','2025-09-05 16:06:47','2025-09-06 20:50:19'),
(58,90,NULL,'Ready Made','Standard','L',100000.00,1,100000.00,NULL,NULL,'menunggu','2025-09-05 16:07:17','2025-09-05 16:07:17'),
(59,91,1,'celana_bahan','linen','Custom',280000.00,1,280000.00,'-',NULL,'menunggu','2025-09-05 16:07:17','2025-09-05 16:07:17'),
(61,93,NULL,'baju_seragam','batik','baju',182500.00,1,182500.00,'Antahlah',NULL,'diproses','2025-09-06 20:03:10','2025-09-06 20:49:59');

/*Table structure for table `orders` */

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `kode_pesanan` varchar(255) NOT NULL,
  `order_code` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','diproses','siap-diambil','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `total_harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `metode_pembayaran` varchar(255) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `nama_pengiriman` varchar(255) DEFAULT NULL,
  `no_telp_pengiriman` varchar(255) DEFAULT NULL,
  `alamat_pengiriman` varchar(500) DEFAULT NULL,
  `kota_pengiriman` varchar(255) DEFAULT NULL,
  `kecamatan_pengiriman` varchar(255) DEFAULT NULL,
  `kode_pos_pengiriman` varchar(10) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `tailor_id` int(11) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_kode_pesanan_unique` (`kode_pesanan`),
  UNIQUE KEY `orders_order_code_unique` (`order_code`),
  KEY `orders_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `orders` */

insert  into `orders`(`id`,`user_id`,`kode_pesanan`,`order_code`,`status`,`total_harga`,`total_amount`,`metode_pembayaran`,`bukti_pembayaran`,`nama_pengiriman`,`no_telp_pengiriman`,`alamat_pengiriman`,`kota_pengiriman`,`kecamatan_pengiriman`,`kode_pos_pengiriman`,`catatan`,`tailor_id`,`paid_at`,`created_at`,`updated_at`) values 
(82,13,'OP-ORDER9934','OP-ORDER9934','diproses',100000.00,100000.00,'Bank Transfer',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:01:14','2025-09-05 16:01:14','2025-09-05 16:01:14'),
(83,13,'OC-ORDER9934','OC-ORDER9934','selesai',310000.00,310000.00,'Bank Transfer',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'2025-09-05 16:01:14','2025-09-05 16:01:14','2025-09-05 16:59:45'),
(84,13,'ORD-20250905160148051-PGNKLI','ORD-20250905160148051-PGNKLI','diproses',85000.00,85000.00,'Bank Transfer BNI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:02:07','2025-09-05 16:01:48','2025-09-05 16:02:07'),
(85,13,'OC-20250905160249-13','OC-20250905160249-13','diproses',255000.00,255000.00,'Bank Transfer BNI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:03:06','2025-09-05 16:02:49','2025-09-05 16:03:06'),
(86,13,'OP-20250905160412091-AZDKDN','OP-20250905160412091-AZDKDN','diproses',180000.00,180000.00,'QRIS',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:04:37','2025-09-05 16:04:12','2025-09-05 16:04:37'),
(87,13,'OP-ORDER0918','OP-ORDER0918','diproses',100000.00,100000.00,'QRIS',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:05:25','2025-09-05 16:05:25','2025-09-05 16:05:25'),
(88,13,'OC-ORDER0918','OC-ORDER0918','diproses',310000.00,310000.00,'QRIS',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:05:25','2025-09-05 16:05:25','2025-09-05 16:05:25'),
(89,13,'OC-20250905160647-13','OC-20250905160647-13','siap-diambil',280000.00,280000.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:06:47','2025-09-06 19:24:18'),
(90,13,'OP-ORDER1328','OP-ORDER1328','selesai',100000.00,100000.00,'QRIS',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:07:17','2025-09-05 16:07:17','2025-09-06 19:04:31'),
(91,13,'OC-ORDER1328','OC-ORDER1328','diproses',280000.00,280000.00,'QRIS',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-05 16:07:17','2025-09-05 16:07:17','2025-09-05 16:07:17'),
(93,15,'OC-20250906200310-15','OC-20250906200310-15','diproses',182500.00,182500.00,'Bank Transfer BNI',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-06 20:03:54','2025-09-06 20:03:10','2025-09-06 20:12:53');

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `kategory` varchar(100) DEFAULT NULL,
  `bahan` varchar(100) DEFAULT NULL,
  `motif` varchar(100) DEFAULT NULL,
  `dikirim_dari` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `deskripsi_ukuran` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `warna` varchar(255) DEFAULT NULL,
  `ukuran` varchar(255) DEFAULT NULL,
  `colors` varchar(255) DEFAULT NULL,
  `sizes` varchar(255) DEFAULT NULL,
  `fabric_type` varchar(100) DEFAULT NULL,
  `is_preorder` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `products` */

insert  into `products`(`id`,`name`,`image`,`price`,`kategory`,`bahan`,`motif`,`dikirim_dari`,`deskripsi`,`deskripsi_ukuran`,`description`,`warna`,`ukuran`,`colors`,`sizes`,`fabric_type`,`is_preorder`,`created_at`,`updated_at`) values 
(1,'Kemeja Cowok Polos Lengan Panjang','images/baju kemeja cowok 2.jpg',85000,'Kemeja','Cotton Premium','Polos','Jakarta','Kemeja cowok polos lengan panjang dengan bahan cotton premium yang nyaman dan breathable. Desain klasik yang cocok untuk acara formal maupun kasual. Dilengkapi dengan kancing berkualitas dan jahitan rapi.','S: Lingkar dada 88-92cm, M: Lingkar dada 92-96cm, L: Lingkar dada 96-100cm, XL: Lingkar dada 100-104cm','Kemeja formal pria dengan bahan cotton premium, nyaman dipakai seharian','Putih, Biru Muda, Abu-abu','S, M, L, XL','Putih,Biru Muda,Abu-abu','S,M,L,XL','Cotton Premium',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(2,'Kemeja Cowok Garis Casual','images/baju kemeja cowok 1.jpg',90000,'Kemeja','Linen Blend','Garis Halus','Jakarta','Kemeja casual dengan motif garis halus yang memberikan kesan modern dan stylish. Berbahan linen blend yang adem dan nyaman untuk aktivitas sehari-hari. Perfect untuk gaya smart casual.','S: Lingkar dada 94cm, M: Lingkar dada 98cm, L: Lingkar dada 102cm, XL: Lingkar dada 106cm','Kemeja casual bergaris dengan bahan linen yang adem dan nyaman','Biru Garis, Hitam Garis','S, M, L, XL','Biru Garis,Hitam Garis','S,M,L,XL','Linen Blend',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(3,'Kemeja Cowok Levis Style','images/baju kemeja cowok 3.jpg',220000,'Kemeja','Denim Premium','Polos','Jakarta','Kemeja cowok style levis dengan kualitas premium. Berbahan denim tebal yang tahan lama dan nyaman. Cocok untuk gaya kasual modern dengan tampilan yang maskulin dan trendy.','S: Lingkar dada 88-92cm, M: Lingkar dada 92-96cm, L: Lingkar dada 96-100cm, XL: Lingkar dada 100-104cm','Kemeja denim premium dengan style levis yang trendy','Biru Tua, Hitam','S, M, L, XL','Biru Tua,Hitam','S,M,L,XL','Denim Premium',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(4,'Workshirt Kemeja Lengan Pendek','images/baju kemeja cowok 4.jpg',180000,'Kemeja','Cotton Twill','Polos','Jakarta','Kemeja workshirt lengan pendek yang praktis dan stylish untuk aktivitas sehari-hari. Berbahan cotton twill yang kuat dan tahan lama. Cocok untuk gaya casual atau semi formal.','S: Lingkar dada 95cm, M: Lingkar dada 99cm, L: Lingkar dada 103cm, XL: Lingkar dada 107cm','Workshirt casual untuk aktivitas sehari-hari','Abu-abu, Khaki, Navy','S, M, L, XL','Abu-abu,Khaki,Navy','S,M,L,XL','Cotton Twill',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(5,'Kemeja Cowok Abu-Abu Formal','images/baju kemeja cowok 5.jpg',100000,'Kemeja','Cotton','Polos','Jakarta','Kemeja cowok warna abu-abu yang elegan dan serbaguna. Cocok untuk berbagai acara formal maupun kasual. Bahan cotton yang nyaman dan mudah dirawat.','S: Lingkar dada 94cm, M: Lingkar dada 98cm, L: Lingkar dada 102cm, XL: Lingkar dada 106cm','Kemeja formal warna abu-abu yang elegan','Abu-abu Muda, Abu-abu Tua','S, M, L, XL','Abu-abu Muda,Abu-abu Tua','S,M,L,XL','Cotton',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(6,'Kemeja Batik Modern','images/kemeja cowok.jpg',150000,'Kemeja','Katun Batik','Batik Modern','Yogyakarta','Kemeja batik dengan motif modern yang memadukan tradisi dan kontemporer. Berbahan katun batik asli dengan pewarnaan alami. Cocok untuk acara formal, kondangan, atau ke kantor.','M: Lingkar dada 98cm, L: Lingkar dada 102cm, XL: Lingkar dada 106cm, XXL: Lingkar dada 110cm','Kemeja batik modern dengan motif eksklusif','Biru Batik, Coklat Batik','M, L, XL, XXL','Biru Batik,Coklat Batik','M,L,XL,XXL','Katun Batik',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(7,'Rok Ungu Shimmer Elegant','images/rok 4.jpg',160000,'Rok','Shimmer Fabric','Shimmer','Jakarta','Rok shimmer dengan warna ungu yang elegan dan mewah. Cocok untuk acara pesta, gala dinner, atau acara formal malam hari. Bahan shimmer yang berkilau memberikan kesan glamour.','S: Pinggang 62cm, M: Pinggang 66cm, L: Pinggang 70cm, XL: Pinggang 74cm','Rok shimmer elegan untuk acara formal','Ungu, Silver, Gold','S, M, L, XL','Ungu,Silver,Gold','S,M,L,XL','Shimmer Fabric',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(8,'Rok Beige Shimmer Soft','images/rok 6.jpg',120000,'Rok','Shimmer Fabric','Shimmer','Jakarta','Rok shimmer warna beige yang memberikan kesan soft dan elegan. Perfect untuk acara semi formal atau cocktail party. Warna netral yang mudah dipadukan dengan berbagai atasan.','S: Pinggang 60cm, M: Pinggang 64cm, L: Pinggang 68cm, XL: Lingkar pinggang 72cm','Rok shimmer warna soft untuk penampilan elegan','Beige, Cream, Champagne','S, M, L, XL','Beige,Cream,Champagne','S,M,L,XL','Shimmer Fabric',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(9,'Rok Brukat Maron Premium','images/rok 5 (1).jpg',198000,'Rok','Brukat Premium','Brukat','Jakarta','Rok brukat dengan warna maron yang memberikan kesan klasik dan elegan. Menggunakan bahan brukat premium dengan detail yang halus dan indah. Cocok untuk acara pernikahan atau acara formal.','S: Pinggang 61cm, M: Pinggang 65cm, L: Pinggang 69cm, XL: Pinggang 73cm','Rok brukat premium untuk acara formal','Maron, Hitam, Navy','S, M, L, XL','Maron,Hitam,Navy','S,M,L,XL','Brukat Premium',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(10,'Rok Susun Ruffel Feminin','images/rok 3.jpg',180000,'Rok','Chiffon','Ruffel','Jakarta','Rok dengan desain susun ruffel yang feminin dan trendy. Berbahan chiffon yang ringan dan mengalir indah. Perfect untuk tampilan romantic dan girly.','S: Pinggang 60cm, M: Pinggang 64cm, L: Pinggang 68cm, XL: Pinggang 72cm','Rok susun dengan detail ruffel yang feminin','Putih, Pink, Lavender','S, M, L, XL','Putih,Pink,Lavender','S,M,L,XL','Chiffon',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(11,'Celana Formal Coklat Executive','images/celana 1.jpg',170000,'Celana','Wool Blend','Polos','Jakarta','Celana formal warna coklat yang cocok untuk acara resmi dan kantor. Berbahan wool blend yang nyaman dan tidak mudah kusut. Cutting slim fit yang memberikan kesan profesional.','S: Lingkar pinggang 76cm, M: Lingkar pinggang 80cm, L: Lingkar pinggang 84cm, XL: Lingkar pinggang 88cm','Celana formal untuk penampilan profesional','Coklat, Hitam, Navy','S, M, L, XL','Coklat,Hitam,Navy','S,M,L,XL','Wool Blend',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(12,'Celana Cream Casual Chino','images/celana 2.jpg',160000,'Celana','Cotton Twill','Polos','Jakarta','Celana casual warna cream yang nyaman untuk aktivitas sehari-hari. Model chino yang versatile dan mudah dipadukan. Berbahan cotton twill yang breathable.','S: Lingkar pinggang 76cm, M: Lingkar pinggang 80cm, L: Lingkar pinggang 84cm, XL: Lingkar pinggang 88cm','Celana casual untuk aktivitas sehari-hari','Cream, Khaki, Olive','S, M, L, XL','Cream,Khaki,Olive','S,M,L,XL','Cotton Twill',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(13,'Jas Wanita Navy Professional','images/jas.jpg',310000,'Jas','Wool Premium','Polos','Jakarta','Jas wanita warna navy yang elegan untuk acara formal dan bisnis. Berbahan wool premium dengan cutting yang sempurna. Cocok untuk meeting, presentasi, atau acara corporate.','S: Lingkar dada 88cm, M: Lingkar dada 92cm, L: Lingkar dada 96cm, XL: Lingkar dada 100cm','Jas formal wanita untuk penampilan profesional','Navy, Hitam, Abu-abu','S, M, L, XL','Navy,Hitam,Abu-abu','S,M,L,XL','Wool Premium',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(14,'Blazer Wanita Abu-Abu Modern','images/jas cewek 2.jpg',350000,'Blazer','Wool Premium','Polos','Jakarta','Blazer wanita abu-abu dengan desain elegan dan modern. Cutting yang sempurna memberikan siluet yang flattering. Perfect untuk tampilan business casual yang sophisticated.','S: Lingkar dada 88cm, M: Lingkar dada 92cm, L: Lingkar dada 96cm, XL: Lingkar dada 100cm','Blazer wanita elegan dengan desain modern','Abu-abu, Hitam, Cream','S, M, L, XL','Abu-abu,Hitam,Cream','S,M,L,XL','Wool Premium',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(15,'Kebaya Traditional Bordir','images/kebaya.jpg',350000,'Kebaya','Brokat Premium','Bordir Tradisional','Jakarta','Kebaya tradisional dengan detail bordir halus yang dikerjakan dengan tangan terampil. Menggunakan bahan brokat premium yang memberikan kesan mewah dan elegan. Cocok untuk acara pernikahan, wisuda, atau acara formal lainnya.','S: Lingkar dada 86cm, M: Lingkar dada 90cm, L: Lingkar dada 94cm, XL: Lingkar dada 98cm','Kebaya tradisional dengan bordir halus, cocok untuk acara formal','Putih, Cream, Gold','S, M, L, XL','Putih,Cream,Gold','S,M,L,XL','Brokat Premium',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(16,'Blouse Wanita Elegant Chiffon','images/baju blus.png',120000,'Blouse','Chiffon Premium','Polos','Jakarta','Blouse wanita dengan desain elegant dan feminin. Berbahan chiffon premium yang ringan dan nyaman. Cocok untuk ke kantor atau acara semi formal. Dilengkapi dengan detail kancing mutiara yang menambah kesan mewah.','S: Lingkar dada 88cm, M: Lingkar dada 92cm, L: Lingkar dada 96cm, XL: Lingkar dada 100cm','Blouse elegant berbahan chiffon untuk tampilan profesional','Putih, Pink, Navy','S, M, L, XL','Putih,Pink,Navy','S,M,L,XL','Chiffon Premium',0,'2025-08-28 10:10:27','2025-08-28 10:10:27'),
(17,'Dress Casual Modern Minimalis','images/baju blus 2.jpg',180000,'Dress','Cotton Stretch','Minimalis','Jakarta','Dress casual dengan desain modern dan minimalis. Berbahan cotton stretch yang nyaman dan tidak mudah kusut. Perfect untuk daily wear atau hangout dengan teman. Model A-line yang flattering untuk semua bentuk tubuh.','S: Lingkar dada 86cm, M: Lingkar dada 90cm, L: Lingkar dada 94cm, XL: Lingkar dada 98cm','Dress casual modern dengan bahan stretch yang nyaman','Hitam, Navy, Maroon','S, M, L, XL','Hitam,Navy,Maroon','S,M,L,XL','Cotton Stretch',0,'2025-08-28 10:10:27','2025-08-28 10:10:27');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `no_telp` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `level` enum('admin','tailor','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`nama`,`email`,`email_verified_at`,`password`,`remember_token`,`no_telp`,`alamat`,`level`,`created_at`,`updated_at`) values 
(1,'Administrator','admin@tokort.com','2025-08-29 07:25:36','$2y$12$7pFFh5Prano41xup627zgunOSxZ63TDRhYETSeSxCr8KZ3ewJ9Pae',NULL,'081234567890','Jl. Admin No. 1, Jakarta','admin','2025-08-29 07:25:36','2025-08-29 05:32:05'),
(2,'Master Tailor','tailor@tokort.com','2025-08-29 07:25:36','$2y$12$TesvS0l0K7zUEvWougw2POD/j0Pgl4cK.f9xRfs/Qk1QvslaA/Vky',NULL,'081234567891','Jl. Tailor No. 1, Jakarta','tailor','2025-08-29 07:25:36','2025-09-01 21:50:20'),
(3,'Siti Nurhaliza','siti@user.com','2025-08-29 07:25:36','$2y$12$ErdV7phs93lhyycEmStxa.YVyV5XdTT4HzQ1uMggA8coaxhERWgOy',NULL,'083186517655','Jl. ganting 1 no 16','user','2025-08-29 07:25:36','2025-09-01 21:40:41'),
(4,'Budi Santoso','budi@user.com','2025-08-29 07:25:36','$2y$10$RGM2c9E1Gjv6rIeiVH7Aou9KwPylEe8Z7mljH2.NXDj9P4AiwGLHW',NULL,'081234567893','Jl. Mawar No. 15, Surabaya','user','2025-08-29 07:25:36','2025-08-29 07:25:36'),
(5,'Rina Kartika','rina@user.com','2025-08-29 07:25:36','$2y$10$RGM2c9E1Gjv6rIeiVH7Aou9KwPylEe8Z7mljH2.NXDj9P4AiwGLHW',NULL,'081234567894','Jl. Anggrek No. 8, Yogyakarta','user','2025-08-29 07:25:36','2025-08-29 07:25:36'),
(6,'Ahmad Fauzi','ahmad@user.com','2025-08-29 07:25:36','$2y$10$RGM2c9E1Gjv6rIeiVH7Aou9KwPylEe8Z7mljH2.NXDj9P4AiwGLHW',NULL,'081234567895','Jl. Kenanga No. 22, Medan','user','2025-08-29 07:25:36','2025-08-29 07:25:36'),
(7,'Dewi Sartika','dewi@user.com','2025-08-29 07:25:36','$2y$10$RGM2c9E1Gjv6rIeiVH7Aou9KwPylEe8Z7mljH2.NXDj9P4AiwGLHW',NULL,'081234567896','Jl. Cempaka No. 7, Semarang','user','2025-08-29 07:25:36','2025-08-29 07:25:36'),
(8,'Rudi Hermawan','rudi@user.com','2025-08-29 07:25:36','$2y$10$RGM2c9E1Gjv6rIeiVH7Aou9KwPylEe8Z7mljH2.NXDj9P4AiwGLHW',NULL,'081234567897','Jl. Dahlia No. 19, Malang','user','2025-08-29 07:25:36','2025-08-29 07:25:36'),
(9,'Maya Sari','maya@user.com','2025-08-29 07:25:36','$2y$10$RGM2c9E1Gjv6rIeiVH7Aou9KwPylEe8Z7mljH2.NXDj9P4AiwGLHW',NULL,'081234567898','Jl. Tulip No. 3, Denpasar','user','2025-08-29 07:25:36','2025-08-29 07:25:36'),
(10,'Indra Gunawan','indra@user.com','2025-08-29 07:25:36','$2y$10$RGM2c9E1Gjv6rIeiVH7Aou9KwPylEe8Z7mljH2.NXDj9P4AiwGLHW',NULL,'081234567899','Jl. Sakura No. 11, Makassar','user','2025-08-29 07:25:36','2025-08-29 07:25:36'),
(11,'Lestari Wulandari','lestari@user.com','2025-08-29 07:25:36','$2y$10$RGM2c9E1Gjv6rIeiVH7Aou9KwPylEe8Z7mljH2.NXDj9P4AiwGLHW',NULL,'081234567800','Jl. Bougenville No. 25, Palembang','user','2025-08-29 07:25:36','2025-08-29 07:25:36'),
(12,'YUDHA BIMA SAKTI','yudhabimasakti787@gmail.com',NULL,'$2y$12$41ubM110biq1cphFDajyhuNulJNTbRo/NiGWI1HmaSq6amELOuo.O',NULL,'083838294757','Jl. Rimbo Data No. 12','admin','2025-09-02 15:57:58','2025-09-02 15:57:58'),
(13,'Anton Sabu','anton@gmail.com',NULL,'$2y$12$7RJSwDJvoV9DawLrDK.EmeG7qnUwCYEMPxOfi3CvaVEItyQbtlJ5e',NULL,'081234567890','padang','user','2025-09-02 16:26:15','2025-09-02 16:26:15'),
(14,'Tailor Doank','tailor@gmail.com',NULL,'$2y$12$Rh4ORIatTWd4Qepvyu/I7uunEUOG4EL/KVZJBFdUgTKnopMsBbKeK',NULL,'081234567890','Padang','tailor','2025-09-06 19:17:03','2025-09-06 19:17:03'),
(15,'Ucup','ucup@gmail.com',NULL,'$2y$12$H5sFgrpD5qPIEmyE.CjpW./BS6Lged6kSuz5z.NGzf.mV3gmkl0Q6',NULL,'081234567890','PP','user','2025-09-06 19:57:37','2025-09-06 19:57:37');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
