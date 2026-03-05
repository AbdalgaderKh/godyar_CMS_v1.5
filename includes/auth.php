<?php
/**
 * Legacy Auth entrypoint.
 *
 * Some parts of the project include includes/Auth.php expecting a global Auth.
 * The canonical implementation is now namespaced: Godyar\\Auth.
 */

require_once __DIR__ . '/bootstrap.php';

// Provide a global Auth alias for legacy code.
if (!class_exists('Auth', false) && class_exists('Godyar\\Auth')) {
    class Auth extends \Godyar\Auth {}
}
