CREATE TABLE IF NOT EXISTS `family_gallery_albums` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `family_id` int unsigned NOT NULL,
  `title` varchar(120) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `created_by_userid` int unsigned NOT NULL,
  `updated_by_userid` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_gallery_albums_family_id_index` (`family_id`),
  KEY `family_gallery_albums_created_by_userid_index` (`created_by_userid`),
  KEY `family_gallery_albums_updated_by_userid_index` (`updated_by_userid`),
  KEY `family_gallery_albums_family_title_index` (`family_id`, `title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_gallery_photos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `family_id` int unsigned NOT NULL,
  `album_id` int unsigned NOT NULL,
  `uploader_userid` int unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `privacy_status` varchar(30) NOT NULL DEFAULT 'public_family',
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_gallery_photos_family_id_index` (`family_id`),
  KEY `family_gallery_photos_album_id_index` (`album_id`),
  KEY `family_gallery_photos_uploader_userid_index` (`uploader_userid`),
  KEY `family_gallery_photos_privacy_status_index` (`privacy_status`),
  KEY `family_gallery_photos_uploaded_at_index` (`uploaded_at`),
  KEY `family_gallery_photos_family_album_uploaded_index` (`family_id`, `album_id`, `uploaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_gallery_photo_viewers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `family_id` int unsigned NOT NULL,
  `photo_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `family_gallery_photo_viewers_unique` (`photo_id`, `user_id`),
  KEY `family_gallery_photo_viewers_family_user_index` (`family_id`, `user_id`),
  KEY `family_gallery_photo_viewers_photo_id_index` (`photo_id`),
  KEY `family_gallery_photo_viewers_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
