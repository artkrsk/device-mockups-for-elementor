<?php

namespace Arts\DeviceMockups\Managers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Arts\DeviceMockups\Base\Manager;
use Arts\DeviceMockups\Elementor\MockupWidget;

/**
 * Registers the mockup Elementor surfaces: the Device Mockup widget (which carries
 * the skins) and the shared `arts-widgets` category.
 */
class Elementor extends Manager {

	/**
	 * Register the shared `arts-widgets` category. Hooked to
	 * `elementor/elements/categories_registered`.
	 *
	 * @param mixed $elements_manager
	 * @return void
	 */
	public function register_category( $elements_manager ) {
		if ( ! $elements_manager instanceof \Elementor\Elements_Manager ) {
			return;
		}

		$elements_manager->add_category(
			'arts-widgets',
			array( 'title' => esc_html__( 'Arts', 'device-mockups-for-elementor' ) )
		);
	}

	/**
	 * Register the MockupWidget. Hooked to `elementor/widgets/register`.
	 *
	 * @param mixed $widgets_manager
	 * @return void
	 */
	public function register_widget( $widgets_manager ) {
		if ( ! $widgets_manager instanceof \Elementor\Widgets_Manager ) {
			return;
		}

		$widgets_manager->register( new MockupWidget() );
	}
}
