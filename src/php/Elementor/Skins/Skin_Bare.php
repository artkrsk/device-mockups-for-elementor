<?php

namespace Arts\DeviceMockups\Elementor\Skins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bare (frameless) skin — renders only the __screen and media, no device chrome.
 * All media/gallery/scroll/link behaviour is identical to device skins; only the outer
 * markup differs.
 */
class Skin_Bare extends BaseSkin {

	/**
	 * @return string
	 */
	public function get_id(): string {
		return 'bare';
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Bare', 'device-mockups-for-elementor' );
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
		arts_get_template_part( 'Mockup/Bare', $args, ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups' );
	}
}
