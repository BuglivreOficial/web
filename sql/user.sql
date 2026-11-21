-- ==========================================================================
-- Tabela de usuários
-- Estrutura baseada nos grandes frameworks (Laravel/Symfony):
-- chave primária auto incremental, credenciais únicas, controle de status,
-- timestamps automáticos e senha armazenada via hash (bcrypt).
-- ==========================================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(120)    NOT NULL,
    `username`          VARCHAR(60)     NOT NULL,
    `email`             VARCHAR(180)    NOT NULL,
    `password`          VARCHAR(255)    NOT NULL,
    `role`              VARCHAR(30)     NOT NULL DEFAULT 'user',
    `status`            TINYINT(1)      NOT NULL DEFAULT 1,
    `email_verified_at` TIMESTAMP       NULL DEFAULT NULL,
    `last_login_at`     TIMESTAMP       NULL DEFAULT NULL,
    `remember_token`    VARCHAR(100)    NULL DEFAULT NULL,
    `created_at`        TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_username_unique` (`username`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
