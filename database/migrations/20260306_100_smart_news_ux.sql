-- v3.7 starter migration
CREATE TABLE IF NOT EXISTS live_updates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  news_id INT UNSIGNED NOT NULL,
  content MEDIUMTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_live_news (news_id),
  INDEX idx_live_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fact_checks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(190) NOT NULL,
  title VARCHAR(255) NOT NULL,
  claim TEXT NULL,
  verdict VARCHAR(100) NULL,
  sources MEDIUMTEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL,
  UNIQUE KEY uq_fact_slug (slug),
  INDEX idx_fact_status (status),
  INDEX idx_fact_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
