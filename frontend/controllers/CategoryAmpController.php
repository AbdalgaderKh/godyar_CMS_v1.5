<?php

declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * AMP controllers were removed from the new structure.
 * Keep a minimal controller to avoid router fatals if old routes exist.
 */
final class CategoryAmpController
{
    public function __invoke(): void
    {
        $slug = $_GET['slug'] ?? '';
        $slug = is_string($slug) ? $slug : '';
        $to = '/category/' . rawurlencode($slug);
        header('Location: ' . $to, true, 302);
        exit;
    }
}
