
CREATE TABLE IF NOT EXISTS smart_translation_editor (
id INT AUTO_INCREMENT PRIMARY KEY,
source_text TEXT,
source_lang VARCHAR(5),
target_lang VARCHAR(5),
suggested_text TEXT,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
