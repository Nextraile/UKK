CREATE TABLE IF NOT EXISTS `users` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`first_name` VARCHAR(100) NOT NULL,
	`last_name` VARCHAR(100),
	`email` VARCHAR(254) NOT NULL UNIQUE,
	`email_verified_at` TIMESTAMP,
	`phone` VARCHAR(20),
	`phone_verified_at` TIMESTAMP,
	`role` ENUM('user', 'admin', 'superadmin') NOT NULL DEFAULT 'user',
	`avatar_path` VARCHAR(500),
	`remember_token` VARCHAR(100),
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `kosts` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`user_id` BIGINT UNSIGNED NOT NULL,
	`slug` VARCHAR(150) NOT NULL,
	`name` VARCHAR(150) NOT NULL,
	`description` TEXT,
	`contact_number` VARCHAR(20) NOT NULL,
	`status` ENUM('draft', 'pending_review', 'approved', 'active', 'inactive', 'suspended', 'rejected', 'archived') NOT NULL DEFAULT 'draft',
	`published_at` TIMESTAMP,
	`approved_at` TIMESTAMP,
	`approved_by` BIGINT UNSIGNED,
	`rejected_reason` TEXT,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `addresses` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`kost_id` BIGINT UNSIGNED NOT NULL UNIQUE,
	`full_address` TEXT NOT NULL,
	`district` VARCHAR(100) NOT NULL,
	`city` VARCHAR(100) NOT NULL,
	`province` VARCHAR(100) NOT NULL,
	`postal_code` VARCHAR(10),
	`country` VARCHAR(100) DEFAULT 'Indonesia' CHECK(Indonesia),
	`latitude` DECIMAL(10,8),
	`longitude` DECIMAL(11,8),
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `kost_images` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`kost_id` BIGINT UNSIGNED NOT NULL,
	`image_path` VARCHAR(500) NOT NULL,
	`is_thumbnail` BOOLEAN NOT NULL DEFAULT false,
	`sort_order` SMALLINT NOT NULL,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `categories` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`name` VARCHAR(100) NOT NULL,
	`slug` VARCHAR(120) NOT NULL UNIQUE,
	`description` TEXT,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `category_kost` (
	`kost_id` BIGINT UNSIGNED NOT NULL,
	`category_id` BIGINT UNSIGNED NOT NULL
);


CREATE INDEX `category_kost_index_0`
ON `category_kost` (`kost_id`, `category_id`);
CREATE TABLE IF NOT EXISTS `facilities` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`name` VARCHAR(100) NOT NULL,
	`slug` VARCHAR(120) NOT NULL UNIQUE,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `facility_schemes` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`kost_id` BIGINT UNSIGNED NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`description` TEXT,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `facility_scheme_items` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`facility_scheme_id` BIGINT UNSIGNED NOT NULL,
	`facility_id` BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `facility_scheme_kosts` (
	`facility_scheme_id` BIGINT UNSIGNED NOT NULL,
	`kost_id` BIGINT UNSIGNED NOT NULL
);


CREATE INDEX `facility_scheme_kosts_index_1`
ON `facility_scheme_kosts` (`facility_scheme_id`, `kost_id`);
CREATE TABLE IF NOT EXISTS `facility_scheme_room_types` (
	`facility_scheme_id` BIGINT UNSIGNED NOT NULL,
	`room_type_id` BIGINT UNSIGNED NOT NULL
);


CREATE INDEX `facility_scheme_room_types_index_2`
ON `facility_scheme_room_types` (`facility_scheme_id`, `room_type_id`);
CREATE TABLE IF NOT EXISTS `rules` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`name` VARCHAR(150) NOT NULL,
	`slug` VARCHAR(170) NOT NULL UNIQUE,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `rule_schemes` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`kost_id` BIGINT UNSIGNED NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`description` TEXT,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `rule_scheme_items` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`rule_scheme_id` BIGINT UNSIGNED NOT NULL,
	`rule_id` BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `rule_scheme_kosts` (
	`rule_scheme_id` BIGINT UNSIGNED NOT NULL,
	`kost_id` BIGINT UNSIGNED NOT NULL
);


CREATE INDEX `rule_scheme_kosts_index_3`
ON `rule_scheme_kosts` (`rule_scheme_id`, `kost_id`);
CREATE TABLE IF NOT EXISTS `rule_scheme_room_types` (
	`rule_scheme_id` BIGINT UNSIGNED NOT NULL,
	`room_type_id` BIGINT UNSIGNED NOT NULL
);


CREATE INDEX `rule_scheme_room_types_index_4`
ON `rule_scheme_room_types` (`rule_scheme_id`, `room_type_id`);
CREATE TABLE IF NOT EXISTS `room_types` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`kost_id` BIGINT UNSIGNED NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`slug` VARCHAR(120) NOT NULL,
	`description` TEXT,
	`room_size` VARCHAR(20) NOT NULL,
	`max_occupants` TINYINT UNSIGNED NOT NULL,
	`security_deposit` DECIMAL(12,2) NOT NULL DEFAULT 0,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `room_type_images` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`room_type_id` BIGINT UNSIGNED NOT NULL,
	`image_path` VARCHAR(500) NOT NULL,
	`is_thumbnail` BOOLEAN NOT NULL DEFAULT false,
	`sort_order` SMALLINT UNSIGNED NOT NULL,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `price_schemes` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`kost_id` BIGINT UNSIGNED NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`description` TEXT,
	`price` DECIMAL(12,2) NOT NULL,
	`duration_value` SMALLINT NOT NULL,
	`duration_unit` ENUM('day', 'week', 'month') NOT NULL,
	`is_active` BOOLEAN NOT NULL DEFAULT true,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `room_type_price_schemes` (
	`room_type_id` BIGINT UNSIGNED NOT NULL,
	`price_scheme_id` BIGINT UNSIGNED NOT NULL
);


CREATE INDEX `room_type_price_schemes_index_6`
ON `room_type_price_schemes` (`room_type_id`, `price_scheme_id`);
CREATE TABLE IF NOT EXISTS `rooms` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`kost_id` BIGINT UNSIGNED NOT NULL,
	`room_type_id` BIGINT UNSIGNED NOT NULL,
	`code` VARCHAR(30) NOT NULL,
	`status` ENUM('available', 'occupied', 'reserved', 'maintenance', 'inactive') NOT NULL DEFAULT 'available',
	`internal_notes` TEXT(65535),
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP,
	PRIMARY KEY(`id`)
);


CREATE UNIQUE INDEX `rooms_index_5`
ON `rooms` (`kost_id`, `code`);
CREATE TABLE IF NOT EXISTS `rentals` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`room_id` BIGINT UNSIGNED NOT NULL,
	`user_id` BIGINT UNSIGNED NOT NULL,
	`price_scheme_id` BIGINT UNSIGNED NOT NULL,
	`duration_value` SMALLINT NOT NULL,
	`duration_unit` ENUM('day', 'week', 'month') NOT NULL,
	`start_date` DATETIME NOT NULL,
	`end_date` DATETIME NOT NULL,
	`room_price` DECIMAL(12,2) NOT NULL,
	`security_deposit` DECIMAL(12,2) NOT NULL,
	`grand_total` DECIMAL(12,2) NOT NULL,
	`status` ENUM('pending', 'paid', 'confirmed', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
	`cancelled_reason` TEXT,
	`cancelled_at` TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `rental_documents` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`rental_id` BIGINT UNSIGNED NOT NULL,
	`document_type` VARCHAR(50) NOT NULL COMMENT 'example: ktp, selfie, student_card',
	`document_path` VARCHAR(500) NOT NULL,
	`uploaded_at` TIMESTAMP NOT NULL,
	`verification_status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
	`verified_by` BIGINT UNSIGNED,
	`verified_at` TIMESTAMP,
	`notes` TEXT,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `payments` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`rental_id` BIGINT UNSIGNED NOT NULL UNIQUE,
	`transaction_id` VARCHAR(100),
	`gateway` VARCHAR(30) NOT NULL,
	`method` VARCHAR(50) NOT NULL,
	`status` ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
	`amount` DECIMAL(12,2) NOT NULL,
	`payment_url` VARCHAR(500) NOT NULL,
	`expired_at` TIMESTAMP NOT NULL,
	`paid_at` TIMESTAMP,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `payment_logs` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`payment_id` BIGINT UNSIGNED NOT NULL,
	`gateway_status` VARCHAR(30) NOT NULL,
	`gateway_response` JSON,
	`created_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `rental_status_histories` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`rental_id` BIGINT UNSIGNED NOT NULL,
	`status` ENUM('pending', 'paid', 'confirmed', 'active', 'completed', 'cancelled') NOT NULL,
	`changed_by` BIGINT UNSIGNED,
	`internal_notes` TEXT,
	`created_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `kost_reviews` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`rental_id` BIGINT UNSIGNED NOT NULL UNIQUE,
	`kost_id` BIGINT UNSIGNED NOT NULL,
	`rating` TINYINT UNSIGNED NOT NULL,
	`comment` TEXT,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `room_reviews` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`rental_id` BIGINT UNSIGNED NOT NULL UNIQUE,
	`room_id` BIGINT UNSIGNED NOT NULL,
	`rating` TINYINT UNSIGNED NOT NULL,
	`comment` TEXT,
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


CREATE TABLE IF NOT EXISTS `review_images` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	`review_type` ENUM('kost', 'room') NOT NULL,
	`review_id` BIGINT UNSIGNED NOT NULL,
	`image_path` VARCHAR(500) NOT NULL,
	`sort_order` SMALLINT UNSIGNED NOT NULL,
	`created_at` TIMESTAMP NOT NULL,
	PRIMARY KEY(`id`)
);


ALTER TABLE `users`
ADD FOREIGN KEY(`id`) REFERENCES `kosts`(`user_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `users`
ADD FOREIGN KEY(`id`) REFERENCES `kosts`(`approved_by`)
ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `addresses`(`kost_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `categories`
ADD FOREIGN KEY(`id`) REFERENCES `category_kost`(`category_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `category_kost`(`kost_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `facility_schemes`
ADD FOREIGN KEY(`id`) REFERENCES `facility_scheme_items`(`facility_scheme_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `facilities`
ADD FOREIGN KEY(`id`) REFERENCES `facility_scheme_items`(`facility_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `facility_schemes`
ADD FOREIGN KEY(`id`) REFERENCES `facility_scheme_kosts`(`facility_scheme_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `facility_scheme_kosts`(`kost_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `rule_schemes`
ADD FOREIGN KEY(`id`) REFERENCES `rule_scheme_items`(`rule_scheme_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `rules`
ADD FOREIGN KEY(`id`) REFERENCES `rule_scheme_items`(`rule_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `rule_schemes`
ADD FOREIGN KEY(`id`) REFERENCES `rule_scheme_kosts`(`rule_scheme_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `rule_scheme_kosts`(`kost_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `room_types`
ADD FOREIGN KEY(`id`) REFERENCES `room_type_images`(`room_type_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `rooms`(`kost_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `room_types`
ADD FOREIGN KEY(`id`) REFERENCES `rooms`(`room_type_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `room_types`
ADD FOREIGN KEY(`id`) REFERENCES `room_type_price_schemes`(`room_type_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `price_schemes`
ADD FOREIGN KEY(`id`) REFERENCES `room_type_price_schemes`(`price_scheme_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `facility_schemes`
ADD FOREIGN KEY(`id`) REFERENCES `facility_scheme_room_types`(`facility_scheme_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `room_types`
ADD FOREIGN KEY(`id`) REFERENCES `facility_scheme_room_types`(`room_type_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `rule_schemes`
ADD FOREIGN KEY(`id`) REFERENCES `rule_scheme_room_types`(`rule_scheme_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `room_types`
ADD FOREIGN KEY(`id`) REFERENCES `rule_scheme_room_types`(`room_type_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `rooms`
ADD FOREIGN KEY(`id`) REFERENCES `rentals`(`room_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `users`
ADD FOREIGN KEY(`id`) REFERENCES `rentals`(`user_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `price_schemes`
ADD FOREIGN KEY(`id`) REFERENCES `rentals`(`price_scheme_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `rentals`
ADD FOREIGN KEY(`id`) REFERENCES `rental_status_histories`(`rental_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `users`
ADD FOREIGN KEY(`id`) REFERENCES `rental_status_histories`(`changed_by`)
ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE `rentals`
ADD FOREIGN KEY(`id`) REFERENCES `rental_documents`(`rental_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `users`
ADD FOREIGN KEY(`id`) REFERENCES `rental_documents`(`verified_by`)
ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE `rentals`
ADD FOREIGN KEY(`id`) REFERENCES `payments`(`rental_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `payments`
ADD FOREIGN KEY(`id`) REFERENCES `payment_logs`(`payment_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `rentals`
ADD FOREIGN KEY(`id`) REFERENCES `kost_reviews`(`rental_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `kost_reviews`(`kost_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `rentals`
ADD FOREIGN KEY(`id`) REFERENCES `room_reviews`(`rental_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `rooms`
ADD FOREIGN KEY(`id`) REFERENCES `room_reviews`(`room_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `kost_images`(`kost_id`)
ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `room_types`(`kost_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `price_schemes`(`kost_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `facility_schemes`(`kost_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `kosts`
ADD FOREIGN KEY(`id`) REFERENCES `rule_schemes`(`kost_id`)
ON UPDATE CASCADE ON DELETE RESTRICT;