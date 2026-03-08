<?php
/**
 * Back-compat include for legacy paths:
 * admin/* scripts sometimes require "includes/auth.php" (lowercase).
 */
require_once __DIR__ . '/Auth.php';
