<?php

namespace Arts\DeviceMockups\Managers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Arts\DeviceMockups\Base\Manager;

/**
 * Registers the `arts-device-mockups` CSS and JS handles so Elementor's
 * get_style_depends()/get_script_depends() can declare them as widget deps.
 *
 * Both handles are registered independently of whether any widget is on the
 * page — consumers enqueue them. The JS dep array is empty so the script runs
 * in non-Elementor contexts too.
 */
class Assets extends Manager {

	/**
	 * Register CSS + JS handles. Hooked to `wp_enqueue_scripts` and
	 * `elementor/frontend/before_enqueue_scripts`.
	 *
	 * @return void
	 */
	public function register() {
		wp_register_style(
			'arts-device-mockups',
			$this->asset_url( 'libraries/device-mockups-for-elementor/device-mockups-for-elementor.css' ),
			array(),
			$this->asset_version( 'libraries/device-mockups-for-elementor/device-mockups-for-elementor.css' )
		);

		wp_register_script(
			'arts-device-mockups',
			$this->asset_url( 'libraries/device-mockups-for-elementor/device-mockups-for-elementor.js' ),
			array(),
			$this->asset_version( 'libraries/device-mockups-for-elementor/device-mockups-for-elementor.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/** Build the public URL for a path relative to the plugin's src/php dir. */
	private function asset_url( string $relative ): string {
		return untrailingslashit( $this->plugin_dir_url ) . '/' . $relative;
	}

	/** Plugin version + filemtime cache-buster, mirroring the chassis convention. */
	private function asset_version( string $relative ): string {
		$mtime = filemtime( $this->plugin_dir_path . $relative );

		return ARTS_DEVICE_MOCKUPS_PLUGIN_VERSION . '.' . ( false !== $mtime ? (string) $mtime : '0' );
	}
}
