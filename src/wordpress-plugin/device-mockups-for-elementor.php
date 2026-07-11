<?php
/**
 * Plugin Name: Arts Device Mockups for Elementor
 * Description: Device mockups (browser, laptop, tablet, bare) as a native Elementor widget with image, video, and gallery screens.
 * Version: 0.1.0
 * Author: Artem Semkin
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Requires Plugins: elementor
 * Text Domain: device-mockups-for-elementor
 * Plugin URI: https://artemsemkin.com/plugins/device-mockups-for-elementor/
 * Author URI: https://artemsemkin.com
 * Tested up to: 7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARTS_DEVICE_MOCKUPS_PLUGIN_VERSION', '0.1.0' );

require_once __DIR__ . '/vendor/autoload.php';

// Scaffold stub: the extraction of arts-store-mockups boots \Arts\DeviceMockups\Plugin here.
