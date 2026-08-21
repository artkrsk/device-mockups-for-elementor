<?php
/**
 * Plugin Name: Arts Device Mockups for Elementor
 * Description: Device mockups as a native Elementor widget: browser, laptop, tablet, phone, and bare frames for images, videos, and rotating galleries.
 * Version: 1.0.0
 * Author: Artem Semkin
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Requires Plugins: elementor
 * Text Domain: device-mockups-for-elementor
 * Plugin URI: https://artemsemkin.com/plugins/device-mockups-for-elementor/
 * Author URI: https://artemsemkin.com
 * Tested up to: 7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARTS_DEVICE_MOCKUPS_PLUGIN_VERSION', '1.0.0' );

require_once __DIR__ . '/vendor/autoload.php';
// The production autoloader is regenerated without package `files` entries (root classmap only),
// so the shared template-part global must be loaded explicitly; its function_exists guard makes
// the dev-time double load (vendor files autoload + this require) a no-op.
require_once __DIR__ . '/vendor-prefixed/arts/get-template-part/src/php/arts-get-template-part.php';

\Arts\DeviceMockups\Plugin::instance();
