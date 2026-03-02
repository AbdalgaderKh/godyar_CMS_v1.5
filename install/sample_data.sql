-- Godyar CMS (Sample Data)
-- This file contains NON-SENSITIVE demo data only.
-- Import after schema.sql if you want demo defaults.

-- Example: social links
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('social_facebook',  'https://facebook.com/yourpage', NOW()),
('social_instagram', 'https://instagram.com/yourpage', NOW()),
('social_youtube',   'https://youtube.com/@yourchannel', NOW()),
('social_twitter',   'https://x.com/yourhandle', NOW()),
('social_telegram',  'https://t.me/yourchannel', NOW()),
('social_whatsapp',  'https://wa.me/0000000000', NOW())
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at);

-- Example: site identity
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('site.name',  'Godyar CMS', NOW()),
('site.email', 'admin@example.com', NOW())
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at);
