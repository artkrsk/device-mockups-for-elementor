<?php

namespace Arts\DeviceMockups\Elementor\Skins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;

/**
 * Browser skin — renders a macOS-style browser window with a URL bar containing
 * dots, a URL pill, and an icon-blink arrow (when linked).
 *
 * Extra controls (all auto-prefixed to `browser_*` by Skin_Base::add_control()):
 *   caption_position — SELECT: url_bar (default) / below
 *                      Controls whether the caption text appears in the URL bar
 *                      or below the frame as a figcaption.
 *   url_text        — TEXT (dynamic): decorative address shown in the URL pill when
 *                      caption_position = below. Registered as `url_text` so the
 *                      prefix yields `browser_url_text`, not `browser_browser_url_text`.
 *                      Condition references the full stored id `browser_caption_position`.
 */
class Skin_Browser extends BaseSkin {

	/**
	 * @return string
	 */
	public function get_id(): string {
		return 'browser';
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Browser', 'device-mockups-for-elementor' );
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
			'section_browser',
			array(
				'label' => esc_html__( 'Browser', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'caption_position',
			array(
				'label'   => esc_html__( 'Caption position', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'url_bar' => esc_html__( 'URL bar', 'device-mockups-for-elementor' ),
					'below'   => esc_html__( 'Below frame', 'device-mockups-for-elementor' ),
				),
				'default' => 'url_bar',
			)
		);

		$this->add_control(
			'url_text',
			array(
				'label'     => esc_html__( 'URL bar text', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'default'   => 'example.com',
				'condition' => array(
					'browser_caption_position' => 'below',
				),
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
		arts_get_template_part( 'Mockup/Browser', $args, ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups' );
	}
}
