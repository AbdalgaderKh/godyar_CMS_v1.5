<?php
/**
 * v3.8.2 compatibility patch for frontend/views/category.php
 * Fixes: Cannot use isset() on the result of an expression
 */

if (!function_exists('gdy_safe_category_name')) {
    function gdy_safe_category_name($category) {
        $fallback = '';
        if (is_array($category) && isset($category['name'])) {
            $fallback = (string)$category['name'];
        }

        if (function_exists('gdy_tr')) {
            $translated = gdy_tr('category', isset($category['id']) ? $category['id'] : 0, 'name', $fallback);
            return $translated !== null ? (string)$translated : $fallback;
        }

        return $fallback;
    }
}

// Usage example to replace old line 16 style:
// $categoryName = gdy_safe_category_name($category);
