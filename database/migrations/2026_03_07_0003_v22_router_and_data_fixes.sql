-- Godyar CMS v2.2 PRO repair migration (review before running if you already have duplicates)
ALTER TABLE categories ADD UNIQUE KEY uq_categories_slug (slug);
ALTER TABLE users ADD UNIQUE KEY uq_users_email (email);
DROP TRIGGER IF EXISTS trg_news_bi;
DROP TRIGGER IF EXISTS trg_news_bu;
