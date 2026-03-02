<?php

declare(strict_types=1);

/**
 * Legacy ThemeManager wrapper.
 *
 * Some old templates include this file directly.
 * The real implementation lives in: includes/classes/Theme/ThemeManager.php
 * This wrapper avoids fatal errors and provides a compatible API.
 */

if (!class_exists('ThemeManager')) {
    final class ThemeManager
    {
        private ?PDO $pdo;

        public function __construct(?PDO $pdo = null)
        {
            $this->pdo = $pdo;
        }

        public static function instance(?PDO $pdo = null): self
        {
            return new self($pdo);
        }

        public function getCurrentTheme(): string
        {
            // Prefer new theme manager
            if (class_exists('Godyar\\Theme\\ThemeManager')) {
                try {
                    return (string)\Godyar\Theme\ThemeManager::getActiveThemeId();
                } catch (\Throwable $e) {
                    // fallthrough
                }
            }

            // Fallback: read from settings
            try {
                $pdo = $this->pdo;
                if (!$pdo && class_exists('Godyar\\DB') && method_exists('Godyar\\DB', 'pdoOrNull')) {
                    $pdo = \Godyar\DB::pdoOrNull();
                }
                if ($pdo instanceof PDO) {
                    $col = function_exists('gdy_settings_value_column') ? gdy_settings_value_column($pdo) : 'setting_value';
                    $stmt = $pdo->prepare("SELECT {$col} FROM settings WHERE setting_key IN ('theme.front','theme_front','frontend_theme') LIMIT 1");
                    $stmt->execute();
                    $v = (string)($stmt->fetchColumn() ?: 'default');
                    return $v !== '' ? $v : 'default';
                }
            } catch (\Throwable $e) {
                // ignore
            }

            return 'default';
        }

        /**
         * @return array<int,string>
         */
        public function getThemes(): array
        {
            // Keep a small list to avoid filesystem scanning
            return ['default', 'red', 'blue', 'green', 'dark'];
        }
    }
}
