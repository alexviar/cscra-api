CREATE TABLE `atenciones_externas` (
  `id` int(10) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL,
  `asegurado_id` varchar(15) NOT NULL,
  `empleador_id` varbinary(15) NOT NULL,
  `medico` varchar(150) NOT NULL,
  `especialidad` varchar(150) NOT NULL,
  `proveedor` varchar(150) NOT NULL,
  `regional_id` int(10) UNSIGNED NOT NULL,
  `url_dm11` varchar(150) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL
);

CREATE TABLE `detalles_atenciones_externas` (
  `id` int(10) UNSIGNED NOT NULL,
  `transferencia_id` int(10) UNSIGNED NOT NULL,
  `prestacion` varchar(150) NOT NULL
);

CREATE TABLE `lista_mora` (
  `id` int(10) UNSIGNED NOT NULL,
  `empleador_id` varchar(50) NOT NULL,
  `numero_patronal` varchar(9) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `regional_id` int(11) NOT NULL
);

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
);

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
);

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

CREATE TABLE `password_resets` (
  `username` varchar(10) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 'Ver usuarios', 'sanctum', NULL, '2021-07-12 13:24:52', '2021-07-12 13:24:52'),
(2, 'Ver usuarios (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:52', '2021-07-12 13:24:52'),
(3, 'Registrar usuarios', 'sanctum', NULL, '2021-07-12 13:24:52', '2021-07-12 13:24:52'),
(4, 'Registrar usuarios (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:52', '2021-07-12 13:24:52'),
(5, 'Editar usuarios', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(6, 'Editar usuarios (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(7, 'Bloquear usuarios', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(8, 'Bloquear usuarios (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(9, 'Desbloquear usuarios', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(10, 'Desbloquear usuarios (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(11, 'Cambiar contraseña', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(12, 'Cambiar contraseña (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(13, 'Ver roles', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(14, 'Registrar roles', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(15, 'Editar roles', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(16, 'Eliminar roles', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(17, 'Ver solicitudes de atencion externa', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(18, 'Ver solicitudes de atencion externa (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(19, 'Ver solicitudes de atencion externa (registrado por)', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(20, 'Registrar solicitudes de atencion externa', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(21, 'Registrar solicitudes de atencion externa (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:53', '2021-07-12 13:24:53'),
(22, 'Emitir solicitudes de atencion externa', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(23, 'Emitir solicitudes de atencion externa (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(24, 'Emitir solicitudes de atencion externa (registrado por)', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(25, 'Ver lista de mora', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(26, 'Ver lista de mora (regional)', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(27, 'Agregar empleador en mora', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(28, 'Agregar empleador en mora (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(29, 'Quitar empleador en mora', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(30, 'Quitar empleador en mora (misma regional)', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54'),
(31, 'Ver médicos', 'sanctum', NULL, '2021-07-12 13:24:54', '2021-07-12 13:24:54');

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `regionales` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
);

INSERT INTO `regionales` (`id`, `nombre`) VALUES
(1, 'La Paz'),
(2, 'Cochabamba'),
(3, 'Santa Cruz'),
(4, 'Oruro'),
(5, 'Potosí'),
(6, 'Sucre'),
(7, 'Tarija'),
(8, 'Trinidad'),
(9, 'Cobija'),
(10, 'Tupiza'),
(11, 'Riberalta');

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

INSERT INTO `roles` (`id`, `name`, `description`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super user', NULL, 'sanctum', '2021-06-11 15:14:17', '2021-06-11 15:14:17');

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
);

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ci_raiz` int(11) NOT NULL,
  `ci_complemento` char(2) DEFAULT NULL,
  `apellido_paterno` varchar(20) DEFAULT NULL,
  `apellido_materno` varchar(20) DEFAULT NULL,
  `nombres` varchar(40) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `regional_id` int(10) UNSIGNED NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
);

INSERT INTO `users` (`id`, `ci_raiz`, `ci_complemento`, `apellido_paterno`, `apellido_materno`, `nombres`, `username`, `password`, `estado`, `regional_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 0, NULL, NULL, NULL, '', 'admin', '$2y$10$QUnLfz295yFWvgHw0jHFeOinnL91AhgaQxhPEjAzbPSGTNCffrAhq', 1, 1, 'JBARe1RIyPn7WFH6LKTuqoWC3Ztkqza8DvG05wEuW1ZLphZyDNOyQXSP5BUO', '2021-06-11', '2021-06-11');

ALTER TABLE `atenciones_externas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IXFK_a_externas_regionales` (`regional_id`);

ALTER TABLE `detalles_atenciones_externas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IXFK_detalles_transferencias_externas_transferencias_externas` (`transferencia_id`);

ALTER TABLE `lista_mora`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_lista_mora` (`empleador_id`);

ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

ALTER TABLE `password_resets`
  ADD KEY `password_resets_username_index` (`username`);

ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`,`parent_id`) USING BTREE;

ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

ALTER TABLE `regionales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_regionales_nombre` (`nombre`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);
ALTER TABLE `roles` ADD FULLTEXT KEY `name` (`name`,`description`);

ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

ALTER TABLE `atenciones_externas`
  MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `detalles_atenciones_externas`
  MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `lista_mora`
  MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `regionales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;


ALTER TABLE `atenciones_externas`
  ADD FULLTEXT(`medico`),
  ADD FULLTEXT(`proveedor`);


ALTER TABLE `atenciones_externas`
  ADD CONSTRAINT `FK_a_externas_regionales` FOREIGN KEY (`regional_id`) REFERENCES `regionales` (`id`);

ALTER TABLE `detalles_atenciones_externas`
  ADD CONSTRAINT `FK_detalles_a_externas_a_externas` FOREIGN KEY (`transferencia_id`) REFERENCES `atenciones_externas` (`id`);

ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;