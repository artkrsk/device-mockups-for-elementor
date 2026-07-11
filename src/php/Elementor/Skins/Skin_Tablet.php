<?php

namespace Arts\DeviceMockups\Elementor\Skins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;

/**
 * Tablet skin — renders a tablet device frame. Adds an orientation control
 * (landscape / portrait) that adds a modifier class and adjusts the aspect ratio.
 * Control id is `orientation`; Skin_Base auto-prefixes it to `tablet_orientation`.
 * Read in render via get_instance_value('orientation').
 */
class Skin_Tablet extends BaseSkin {

	/**
	 * @return string
	 */
	public function get_id(): string {
		return 'tablet';
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Tablet', 'device-mockups-for-elementor' );
	}

	/**
	 * @return void
	 */
	protected function _register_controls_actions(): void {
		add_action( 'elementor/element/arts-device-mockup/section_layout/after_section_end', array( $this, 'register_controls' ) );
	}

	/**
	 * @return void
	 */
	public function register_controls( ?\Elementor\Widget_Base $widget = null ): void {
		// Re-target the shared skin object at the CURRENT widget instance passed by the
		// after_section_end action. Without this the skin keeps pointing at the type-instance
		// widget and pokes its already-built control stack, re-triggering the first-section
		// `_skin` injection ("Cannot redeclare control _skin").
		if ( $widget ) {
			$this->set_parent( $widget );
		}

		$this->start_controls_section(
			'section_tablet',
			array(
				'label' => esc_html__( 'Tablet', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'orientation',
			array(
				'label'   => esc_html__( 'Orientation', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'landscape' => array(
						'title' => esc_html__( 'Landscape', 'device-mockups-for-elementor' ),
						'icon'  => 'eicon-device-tablet',
					),
					'portrait' => array(
						'title' => esc_html__( 'Portrait', 'device-mockups-for-elementor' ),
						'icon'  => 'eicon-device-mobile',
					),
				),
				'default' => 'landscape',
			)
		);

		$this->end_controls_section();
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
		arts_get_template_part( 'Mockup/Tablet', $args, ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups' );
	}
}
