<?php

namespace Arts\DeviceMockups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR' ) ) {
	define( 'ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR', __DIR__ . '/templates' );
}

final class Plugin extends Base\Plugin {

	/**
	 * @return array<string, mixed>
	 */
	protected function get_default_config(): array {
		return array();
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_default_strings(): array {
		return array();
	}

	/**
	 * @return string
	 */
	protected function get_default_run_action(): string {
		return 'plugins_loaded';
	}

	/**
	 * @return int
	 */
	protected function get_run_action_priority(): int {
		return 20;
	}

	/**
	 * @return array<string, class-string>
	 */
	protected function get_managers_classes(): array {
		return array(
			'elementor' => Managers\Elementor::class,
			'assets'    => Managers\Assets::class,
		);
	}

	protected function add_actions(): void {
		// Asset registration — register handles early so the widget can declare deps
		// via get_style_depends()/get_script_depends() without needing to enqueue manually.
		add_action( 'wp_enqueue_scripts', array( $this->managers->assets, 'register' ) );
		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this->managers->assets, 'register' ) );

		add_action( 'elementor/elements/categories_registered', array( $this->managers->elementor, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this->managers->elementor, 'register_widget' ) );

		add_filter( 'wpml_elementor_widgets_to_translate', array( new Compat\WPML(), 'register' ) );
	}
}
