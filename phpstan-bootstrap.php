<?php
/**
 * PHPStan-only constant definitions. ARTS_DEVICE_MOCKUPS_PLUGIN_VERSION is defined at runtime in the
 * plugin main file (src/wordpress-plugin/), which sits outside the analysed paths;
 * ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR is defined in src/php/Plugin.php behind a defined() guard.
 */

define( 'ARTS_DEVICE_MOCKUPS_PLUGIN_VERSION', '0.1.0' );
define( 'ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR', __DIR__ . '/src/php/templates' );
