-- Godyar CMS v3.8 Smart Translation Engine
CREATE TABLE IF NOT EXISTS translation_jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(50) NOT NULL,
  entity_id INT UNSIGNED NOT NULL,
  source_lang VARCHAR(10) NOT NULL DEFAULT 'ar',
  target_lang VARCHAR(10) NOT NULL,
  fields_json MEDIUMTEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'queued',
  provider VARCHAR(50) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  started_at DATETIME DEFAULT NULL,
  finished_at DATETIME DEFAULT NULL,
  INDEX idx_translation_jobs_status (status),
  INDEX idx_translation_jobs_entity (entity_type, entity_id),
  INDEX idx_translation_jobs_target (target_lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS translation_suggestions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id INT UNSIGNED NOT NULL,
  field_key VARCHAR(100) NOT NULL,
  original_text MEDIUMTEXT,
  translated_text MEDIUMTEXT,
  confidence DECIMAL(5,2) DEFAULT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  approved_at DATETIME DEFAULT NULL,
  INDEX idx_translation_suggestions_job (job_id),
  CONSTRAINT fk_translation_suggestions_job
    FOREIGN KEY (job_id) REFERENCES translation_jobs(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
