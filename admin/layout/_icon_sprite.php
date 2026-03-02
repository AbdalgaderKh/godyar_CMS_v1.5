<?php
// Inline SVG sprite injector (keeps icons working without external requests)
// Usage: require_once __DIR__.'/_icon_sprite.php'; inside <body> (or at end of <head>)

$paths = [
  __DIR__ . '/../../assets/icons/gdy-icons.svg',
  __DIR__ . '/../../assets/icons/godyar-icons.svg',
];

foreach ($paths as $p) {
  if (is_file($p) && is_readable($p)) {
    echo '<div style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">';
    echo file_get_contents($p);
    echo '</div>';
    break;
  }
}
