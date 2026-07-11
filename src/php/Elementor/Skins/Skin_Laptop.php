<?php

namespace Arts\DeviceMockups\Elementor\Skins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Laptop skin — renders the full MacBook-style device frame with lid, camera, screen,
 * base, and notch. Default skin for product hero and below-fold mockups.
 */
class Skin_Laptop extends BaseSkin {

	/**
	 * @return string
	 */
	public function get_id(): string {
		return 'laptop';
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Laptop', 'device-mockups-for-elementor' );
	}

	/**
	 * @return void
	 */
	public function render(): void {
		if ( ! $this->parent ) {
			return;
		}
		$settings = $this->parent->get_settings_for_display();
		$args     = $this->build_args( $settings );
		arts_get_template_part( 'Mockup/Laptop', $args, ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups' );
	}
}
