<?php
/**
 * Patch-ready replacement for frontend/views/category.php issue:
 * Fatal error: Cannot use isset() on the result of an expression
 *
 * Use:
 *   require_once __DIR__ . '/../../includes/frontend_compat_fixes.php';
 *   $categoryName = gdy_safe_category_name($category);
 */

// Example snippet:
require_once __DIR__ . '/../../includes/frontend_compat_fixes.php';

$categoryName = gdy_safe_category_name(isset($category) ? $category : array());
