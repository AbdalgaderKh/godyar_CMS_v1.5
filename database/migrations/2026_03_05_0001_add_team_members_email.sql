-- Add missing email column to team_members (compat with older installs)
-- Safe for MySQL (will no-op if column exists via checks in migrator)
ALTER TABLE team_members ADD COLUMN email VARCHAR(190) NULL AFTER role;
