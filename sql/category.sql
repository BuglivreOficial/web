-- ==========================================================================
-- Tabela de categorias
-- Segue o mesmo padrão da tabela `users`: chave primária auto incremental,
-- identificador único (slug), controle de status e timestamps automáticos.
-- ==========================================================================

CREATE TABLE IF NOT EXISTS `categories` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(120)    NOT NULL,
    `slug`        VARCHAR(140)    NOT NULL,
    `description` VARCHAR(255)    NULL DEFAULT NULL,
    `status`      TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `categories_slug_unique` (`slug`),
    KEY `categories_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
