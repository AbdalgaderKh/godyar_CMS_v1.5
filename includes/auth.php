<?php
/**
 * Back-compat include for legacy paths:
 * admin/* scripts sometimes require "includes/auth.php" (lowercase).
 */

declare(strict_types=1);

require_once __DIR__ . '/Auth.php';
