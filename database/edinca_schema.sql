-- ============================================================
-- EDINCA — Schema MySQL
-- Importar en phpMyAdmin sobre la base de datos dbacbbmi_edinca_db
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Tabla de migraciones Laravel (requerida por el framework)
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de tokens Sanctum
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuarios admin
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('ADMIN','TRABAJADOR','CLIENTE') NOT NULL DEFAULT 'ADMIN',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `rut` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Solicitudes de cotización (formulario landing)
CREATE TABLE IF NOT EXISTS `solicitudes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipo_proyecto` enum('CASA','EDIFICIO','LOCAL_COMERCIAL','AMPLIACION','REGULARIZACION') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('PENDIENTE','EN_REVISION','APROBADA','RECHAZADA','COMPLETADA') NOT NULL DEFAULT 'PENDIENTE',
  `archivo_referencia` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Proyectos
CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `tipo` enum('CONSTRUCCION_NUEVA','AMPLIACION','REGULARIZACION','REMODELACION') NOT NULL,
  `estado` enum('PENDIENTE','EN_PROCESO','EN_EJECUCION','FINALIZADO','CANCELADO') NOT NULL DEFAULT 'PENDIENTE',
  `descripcion` text DEFAULT NULL,
  `metros_cuadrados` decimal(10,2) DEFAULT NULL,
  `numero_trabajadores` int DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin_estimada` date DEFAULT NULL,
  `cliente_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proyectos_cliente_id_foreign` (`cliente_id`),
  CONSTRAINT `proyectos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cotizaciones
CREATE TABLE IF NOT EXISTS `cotizaciones` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `proyecto_id` bigint UNSIGNED NOT NULL,
  `precio_minimo` decimal(15,2) NOT NULL,
  `precio_maximo` decimal(15,2) NOT NULL,
  `tipo_material` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('PENDIENTE','ENVIADA','ACEPTADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cotizaciones_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `cotizaciones_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Documentos PDF
CREATE TABLE IF NOT EXISTS `documentos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_guardado` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `tamano` bigint DEFAULT NULL,
  `proyecto_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documentos_nombre_guardado_unique` (`nombre_guardado`),
  KEY `documentos_proyecto_id_foreign` (`proyecto_id`),
  CONSTRAINT `documentos_proyecto_id_foreign` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notificaciones del panel admin
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `tipo` enum('SOLICITUD_NUEVA','PROYECTO_ACTUALIZADO','SISTEMA') NOT NULL DEFAULT 'SISTEMA',
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Usuario admin inicial
-- Contraseña: edinca2026 (hash bcrypt)
-- ============================================================
INSERT IGNORE INTO `usuarios` (`nombre`, `email`, `password`, `rol`, `activo`, `created_at`)
VALUES (
  'Administrador EDINCA',
  'admin@edinca.cl',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'ADMIN',
  1,
  NOW()
);

-- Registrar migraciones como ejecutadas
INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
('2024_01_01_000001_create_usuarios_table', 1),
('2024_01_01_000002_create_clientes_table', 1),
('2024_01_01_000003_create_solicitudes_table', 1),
('2024_01_01_000004_create_proyectos_table', 1),
('2024_01_01_000005_create_cotizaciones_table', 1),
('2024_01_01_000006_create_documentos_table', 1),
('2024_01_01_000007_create_notificaciones_table', 1);

SET FOREIGN_KEY_CHECKS = 1;
