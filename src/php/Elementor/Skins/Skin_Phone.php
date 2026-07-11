<?php

namespace Arts\DeviceMockups\Elementor\Skins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phone skin — renders a portrait phone frame with an island-pill cutout.
 * Portrait-only in v1; no skin-specific controls (unlike Tablet/Browser),
 * so no _register_controls_actions() and no set_parent() re-target needed.
 */
class Skin_Phone extends BaseSkin {

	/**
	 * @return string
	 */
	public function get_id(): string {
		return 'phone';
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Phone', 'device-mockups-for-elementor' );
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
		arts_get_template_part( 'Mockup/Phone', $args, ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups' );
	}
}
