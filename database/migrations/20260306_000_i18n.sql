-- 2026-03-06 i18n tables (strings + fields)
CREATE TABLE IF NOT EXISTS i18n_strings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  lang VARCHAR(5) NOT NULL,
  k VARCHAR(190) NOT NULL,
  v TEXT NOT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_i18n_strings (lang, k),
  KEY idx_i18n_strings_k (k)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS i18n_fields (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  scope VARCHAR(32) NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  lang VARCHAR(5) NOT NULL,
  field VARCHAR(64) NOT NULL,
  value LONGTEXT NOT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_i18n_fields (scope, item_id, lang, field),
  KEY idx_i18n_fields_scope (scope, lang),
  KEY idx_i18n_fields_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
