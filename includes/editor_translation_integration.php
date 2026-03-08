
<?php
/**
 * Godyar CMS v3.8.1
 * Smart Translation integration with article editor
 */

require_once __DIR__ . '/smart_translation_engine.php';

function gdy_editor_suggest_translation($text, $sourceLang='ar'){
    return gdy_smart_translation_payload($text,$sourceLang);
}
