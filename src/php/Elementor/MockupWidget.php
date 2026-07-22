<?php

namespace Arts\DeviceMockups\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Arts\DeviceMockups\Elementor\Skins\Skin_Bare;
use Arts\DeviceMockups\Elementor\Skins\Skin_Laptop;
use Arts\DeviceMockups\Elementor\Skins\Skin_Tablet;
use Arts\DeviceMockups\Elementor\Skins\Skin_Browser;
use Arts\DeviceMockups\Elementor\Skins\Skin_Phone;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;

/**
 * Elementor widget presenting product screenshots/videos inside device frames.
 * Content is entirely control-driven (static picks or dynamic tags). All rendering
 * is delegated to the active skin → template partial; render() is empty.
 *
 * The JS layer is data-attribute-driven (not settings-driven): the partial emits
 * data-arts-device-mockup-* attributes, and the frontend JS reads only those.
 */
class MockupWidget extends \Elementor\Widget_Base {
	/**
	 * Remove the "Default" skin option from the editor picker. Elementor's register_skin_control()
	 * prepends '' → 'Default' (invoking the base render() path) when this is true; render() here
	 * is intentionally empty because all output goes through the active skin.
	 *
	 * @var bool
	 */
	protected $_has_template_content = false;

	/**
	 * @return string
	 */
	public function get_name(): string {
		return 'arts-device-mockup';
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Device Mockup', 'device-mockups-for-elementor' );
	}

	/**
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-device-laptop';
	}

	/**
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( 'arts-widgets' );
	}

	/**
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( 'mockup', 'device', 'laptop', 'tablet', 'browser', 'screenshot' );
	}

	/**
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'arts-device-mockups' );
	}

	/**
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'arts-device-mockups' );
	}

	/**
	 * @return void
	 */
	protected function register_skins(): void {
		$this->add_skin( new Skin_Bare( $this ) );
		$this->add_skin( new Skin_Laptop( $this ) );
		$this->add_skin( new Skin_Tablet( $this ) );
		$this->add_skin( new Skin_Browser( $this ) );
		$this->add_skin( new Skin_Phone( $this ) );
	}

	/**
	 * Register all shared controls (Content tab + Style tab). Skins add their own
	 * controls via _register_controls_actions() + after_section_end actions.
	 *
	 * @return void
	 */
	protected function _register_controls(): void {

		// Sections are grouped Content → Settings → Layout → Style here purely for source readability;
		// the editor panel actually orders tabs by Elementor's fixed canonical sequence (Content, Style,
		// Advanced, Responsive, Layout, Settings), not by registration order. All four used here are
		// native Elementor tabs.

		// ── Content tab ──────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_media',
			array(
				'label' => esc_html__( 'Media', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'image',
			array(
				'label'   => esc_html__( 'Image', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		// Output resolution for every attachment the mockup renders (image, video poster, gallery).
		// Produces a media_size slug, read into the partials' $args['media_size'] by build_args().
		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'media',
				'default' => 'full',
				// Registered sizes only — 'custom' would need Elementor's BFI Thumb (no srcset, extra
				// coupling); excluding it keeps every choice resolvable by wp_get_attachment_image().
				'exclude' => array( 'custom' ),
			)
		);

		$this->add_control(
			'video_type',
			array(
				'label'              => esc_html__( 'Video type', 'device-mockups-for-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'none'    => esc_html__( 'None', 'device-mockups-for-elementor' ),
					'hosted'  => esc_html__( 'Self-hosted', 'device-mockups-for-elementor' ),
					'youtube' => 'YouTube',
					'vimeo'   => 'Vimeo',
				),
				'default'            => 'none',
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'video_hosted_url',
			array(
				'label'       => esc_html__( 'Video file', 'device-mockups-for-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'video_type' => 'hosted' ),
			)
		);

		$this->add_control(
			'video_youtube_url',
			array(
				'label'     => esc_html__( 'YouTube URL', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'video_type' => 'youtube' ),
			)
		);

		$this->add_control(
			'video_vimeo_url',
			array(
				'label'     => esc_html__( 'Vimeo URL', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'video_type' => 'vimeo' ),
			)
		);

		$this->add_control(
			'video_poster',
			array(
				'label'     => esc_html__( 'Video poster', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'video_type!' => 'none' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_caption',
			array(
				'label' => esc_html__( 'Caption', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'caption_source',
			array(
				'label'   => esc_html__( 'Source', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_media_text_source_options(),
				'default' => 'none',
			)
		);

		$this->add_control(
			'caption',
			array(
				'label'     => esc_html__( 'Caption', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'caption_source' => 'custom' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_description',
			array(
				'label' => esc_html__( 'Description', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'description_source',
			array(
				'label'   => esc_html__( 'Source', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_media_text_source_options(),
				'default' => 'none',
			)
		);

		$this->add_control(
			'description',
			array(
				'label'     => esc_html__( 'Description', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::TEXTAREA,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'description_source' => 'custom' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_link',
			array(
				'label' => esc_html__( 'Link', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
				'options' => array( 'url', 'is_external', 'nofollow', 'custom_attributes' ),
			)
		);

		$this->add_control(
			'show_arrow',
			array(
				'label'        => esc_html__( 'Link arrow', 'device-mockups-for-elementor' ),
				'description'  => esc_html__( 'The diagonal blink arrow shown next to the caption (Browser skin only).', 'device-mockups-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'device-mockups-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'device-mockups-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'_skin'      => 'browser',
					'link[url]!' => '',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_gallery',
			array(
				'label' => esc_html__( 'Gallery', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'gallery',
			array(
				'label'   => esc_html__( 'Gallery images', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::GALLERY,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->end_controls_section();

		// ── Settings tab ──────────────────────────────────────────────────────
		// Behavioural knobs split one section per feature (kept out of Content, which stays "what to show").

		$this->start_controls_section(
			'section_video',
			array(
				'label'     => esc_html__( 'Video', 'device-mockups-for-elementor' ),
				'tab'       => Controls_Manager::TAB_SETTINGS,
				// Its only control is video-gated, so hide the whole section when no video is set.
				'condition' => array( 'video_type!' => 'none' ),
			)
		);

		$this->add_control(
			'video_play_on_hover',
			array(
				'label'              => esc_html__( 'Play video on hover only', 'device-mockups-for-elementor' ),
				'type'               => Controls_Manager::SWITCHER,
				'condition'          => array( 'video_type!' => 'none' ),
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_gallery_rotation',
			array(
				'label' => esc_html__( 'Gallery', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			)
		);

		$this->add_control(
			'gallery_interval',
			array(
				'label'   => esc_html__( 'Rotation interval (ms)', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min'  => 200,
						'max'  => 3000,
						'step' => 100,
					),
				),
				'default' => array( 'size' => 1000 ),
			)
		);

		$this->add_control(
			'gallery_loop',
			array(
				'label'   => esc_html__( 'Loop gallery', 'device-mockups-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'gallery_trigger',
			array(
				'label'              => esc_html__( 'Gallery trigger', 'device-mockups-for-elementor' ),
				'type'               => Controls_Manager::CHOOSE,
				'options'            => array(
					'hover' => array(
						'title' => esc_html__( 'Hover', 'device-mockups-for-elementor' ),
						'icon'  => 'eicon-cursor-move',
					),
					'auto'  => array(
						'title' => esc_html__( 'Auto', 'device-mockups-for-elementor' ),
						'icon'  => 'eicon-play',
					),
				),
				'default'            => 'hover',
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_scroll',
			array(
				'label' => esc_html__( 'Scroll', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			)
		);

		$this->add_control(
			'scroll_on_hover',
			array(
				'label'              => esc_html__( 'Scroll on hover', 'device-mockups-for-elementor' ),
				'type'               => Controls_Manager::SWITCHER,
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'scroll_duration',
			array(
				'label'      => esc_html__( 'Scroll duration', 'device-mockups-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's', 'ms' ),
				'default'    => array(
					'size' => 6,
					'unit' => 's',
				),
				'range'      => array(
					's'  => array(
						'min'  => 1,
						'max'  => 20,
						'step' => 0.5,
					),
					'ms' => array(
						'min'  => 500,
						'max'  => 20000,
						'step' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .arts-device-mockup' => '--arts-device-mockup-scroll-duration: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'scroll_on_hover' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// ── Layout tab ────────────────────────────────────────────────────────

		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'Layout', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_LAYOUT,
			)
		);

		$this->add_responsive_control(
			'aspect_ratio',
			array(
				'label'       => esc_html__( 'Aspect ratio', 'device-mockups-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'description' => esc_html__( 'Override the screen aspect ratio. Leave on Default to keep each skin\'s natural ratio (and the Tablet orientation).', 'device-mockups-for-elementor' ),
				'options'     => array(
					// Empty default emits NO aspect-ratio, so the per-skin CSS (incl. tablet portrait 2:3)
					// is what applies; a non-empty pick overrides it.
					''     => esc_html__( 'Default (per skin)', 'device-mockups-for-elementor' ),
					'3/2'  => '3:2',
					'4/3'  => '4:3',
					'16/9' => '16:9',
					'1/1'  => '1:1',
					'2/3'  => '2:3',
					'9/16' => '9:16',
				),
				'default'     => '',
				'selectors'   => array(
					'{{WRAPPER}} .arts-device-mockup__screen' => 'aspect-ratio: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'object_fit',
			array(
				'label'     => esc_html__( 'Object fit', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'cover'   => esc_html__( 'Cover', 'device-mockups-for-elementor' ),
					'contain' => esc_html__( 'Contain', 'device-mockups-for-elementor' ),
					'fill'    => esc_html__( 'Fill', 'device-mockups-for-elementor' ),
				),
				'default'   => 'cover',
				'selectors' => array(
					'{{WRAPPER}} .arts-device-mockup' => '--arts-device-mockup-object-fit: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'object_position_x',
			array(
				'label'      => esc_html__( 'Object position X', 'device-mockups-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px', 'custom' ),
				'default'    => array(
					'size' => 50,
					'unit' => '%',
				),
				'range'      => array(
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .arts-device-mockup' => '--arts-device-mockup-object-position-x: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'object_fit!' => 'fill' ),
			)
		);

		$this->add_responsive_control(
			'object_position_y',
			array(
				'label'      => esc_html__( 'Object position Y', 'device-mockups-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px', 'custom' ),
				'default'    => array(
					'size' => 50,
					'unit' => '%',
				),
				'range'      => array(
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .arts-device-mockup' => '--arts-device-mockup-object-position-y: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'object_fit!' => 'fill' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_device_colors',
			array(
				'label' => esc_html__( 'Device Colors', 'device-mockups-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Each colour only applies to the skins whose markup uses its CSS var — gated by `_skin` so
		// Elementor neither shows the control nor emits its CSS for skins that don't use it.
		// `skins` empty = applies to every skin (the screen letterbox).
		$color_controls = array(
			'color_body'   => array(
				'label' => esc_html__( 'Body', 'device-mockups-for-elementor' ),
				'var'   => '--arts-device-mockup-body-color',
				'skins' => array( 'laptop', 'tablet', 'browser', 'phone' ),
			),
			'color_chrome' => array(
				'label' => esc_html__( 'Chrome', 'device-mockups-for-elementor' ),
				'var'   => '--arts-device-mockup-chrome-color',
				'skins' => array( 'laptop', 'browser' ),
			),
			'color_screen' => array(
				'label' => esc_html__( 'Screen', 'device-mockups-for-elementor' ),
				'var'   => '--arts-device-mockup-screen-color',
				'skins' => array(),
			),
			'color_border' => array(
				'label' => esc_html__( 'Border', 'device-mockups-for-elementor' ),
				'var'   => '--arts-device-mockup-border-color',
				'skins' => array( 'laptop', 'tablet', 'browser', 'phone' ),
			),
			'color_rule'   => array(
				'label' => esc_html__( 'Rule', 'device-mockups-for-elementor' ),
				'var'   => '--arts-device-mockup-rule-color',
				'skins' => array( 'laptop', 'browser' ),
			),
			'color_dot'    => array(
				'label' => esc_html__( 'Traffic dots', 'device-mockups-for-elementor' ),
				'var'   => '--arts-device-mockup-dot-color',
				'skins' => array( 'browser' ),
			),
			'color_url'    => array(
				'label' => esc_html__( 'URL bar', 'device-mockups-for-elementor' ),
				'var'   => '--arts-device-mockup-url-color',
				'skins' => array( 'browser' ),
			),
			'color_camera' => array(
				'label' => esc_html__( 'Camera', 'device-mockups-for-elementor' ),
				'var'   => '--arts-device-mockup-camera-color',
				'skins' => array( 'laptop', 'phone' ),
			),
		);

		foreach ( $color_controls as $id => $opts ) {
			$control_args = array(
				'label'     => $opts['label'],
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					// Write the var on `.arts-device-mockup` itself (NOT the bare {{WRAPPER}} ancestor): the
					// `:where(.arts-device-mockup)` defaults are declared on this same element, so a control
					// value only wins by being a same-element, higher-specificity declaration. A bare
					// {{WRAPPER}} write is an ancestor value and loses to the descendant default.
					'{{WRAPPER}} .arts-device-mockup' => $opts['var'] . ': {{VALUE}};',
				),
			);

			if ( ! empty( $opts['skins'] ) ) {
				$control_args['condition'] = array( '_skin' => $opts['skins'] );
			}

			$this->add_control( $id, $control_args );
		}

		$this->end_controls_section();

		// Bare-only: the device/browser frame geometry is measured from the reference and fixed, so
		// the only meaningful geometry control is the frameless card's corner radius.
		$this->start_controls_section(
			'section_geometry',
			array(
				'label'     => esc_html__( 'Geometry', 'device-mockups-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( '_skin' => 'bare' ),
			)
		);

		$this->add_responsive_control(
			'border_radius',
			array(
				'label'      => esc_html__( 'Corner radius', 'device-mockups-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'cqw', '%', 'px', 'custom' ),
				'range'      => array(
					'cqw' => array(
						'min'  => 0,
						'max'  => 20,
						'step' => 0.1,
					),
					'%'   => array(
						'min' => 0,
						'max' => 50,
					),
					'px'  => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .arts-device-mockup_bare .arts-device-mockup__screen' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_typography',
			array(
				'label'      => esc_html__( 'Typography & Colors', 'device-mockups-for-elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				// Every control here is gated on a caption or a description; hide the section when neither exists.
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'caption_source',
							'operator' => '!=',
							'value'    => 'none',
						),
						array(
							'name'     => 'description_source',
							'operator' => '!=',
							'value'    => 'none',
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'text_align',
			array(
				'label'      => esc_html__( 'Text align', 'device-mockups-for-elementor' ),
				'type'       => Controls_Manager::CHOOSE,
				'options'    => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'device-mockups-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => esc_html__( 'Center', 'device-mockups-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => esc_html__( 'Right', 'device-mockups-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => esc_html__( 'Justify', 'device-mockups-for-elementor' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .arts-device-mockup__caption, {{WRAPPER}} .arts-device-mockup__description' => 'text-align: {{VALUE}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'caption_source',
							'operator' => '!=',
							'value'    => 'none',
						),
						array(
							'name'     => 'description_source',
							'operator' => '!=',
							'value'    => 'none',
						),
					),
				),
			)
		);

		$this->add_control(
			'caption_heading',
			array(
				'label'     => esc_html__( 'Caption', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'caption_source!' => 'none' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'caption_typography',
				// Targets the beneath-frame caption AND the browser URL pill (so its url-text + arrow
				// both inherit the font), styling all caption text from this one section.
				'selector'  => '{{WRAPPER}} .arts-device-mockup__caption, {{WRAPPER}} .arts-device-mockup__url-pill',
				'condition' => array( 'caption_source!' => 'none' ),
			)
		);

		$this->add_control(
			'caption_color',
			array(
				'label'     => esc_html__( 'Color', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					// Drive the caption colour through a var (read by the caption text, the URL-bar text
					// AND the arrow) instead of setting `color` directly: the hover state reassigns this
					// var on the nearest container, so it wins by inheritance proximity rather than
					// fighting {{WRAPPER}}'s (variable, often higher) specificity at each leaf.
					'{{WRAPPER}} .arts-device-mockup' => '--arts-device-mockup-caption-color: {{VALUE}};',
				),
				'condition' => array( 'caption_source!' => 'none' ),
			)
		);

		$this->add_control(
			'caption_hover_color',
			array(
				'label'       => esc_html__( 'Hover color', 'device-mockups-for-elementor' ),
				'description' => esc_html__( 'Used when the mockup is linked and hovered — unifies the caption text, the browser URL-bar pill border, and the arrow.', 'device-mockups-for-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .arts-device-mockup' => '--arts-device-mockup-caption-hover-color: {{VALUE}};',
				),
				'condition'   => array( 'caption_source!' => 'none' ),
			)
		);

		$this->add_control(
			'caption_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .arts-device-mockup' => '--arts-device-mockup-caption-spacing: {{SIZE}}{{UNIT}};',
				),
				'condition' => array( 'caption_source!' => 'none' ),
			)
		);

		$this->add_control(
			'description_heading',
			array(
				'label'     => esc_html__( 'Description', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'description_source!' => 'none' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'description_typography',
				'selector'  => '{{WRAPPER}} .arts-device-mockup__description',
				'condition' => array( 'description_source!' => 'none' ),
			)
		);

		$this->add_control(
			'description_color',
			array(
				'label'     => esc_html__( 'Color', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .arts-device-mockup__description' => 'color: {{VALUE}};',
				),
				'condition' => array( 'description_source!' => 'none' ),
			)
		);

		$this->add_control(
			'description_spacing',
			array(
				'label'     => esc_html__( 'Spacing', 'device-mockups-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}}' => '--arts-device-mockup-description-spacing: {{SIZE}}{{UNIT}};',
				),
				'condition' => array( 'description_source!' => 'none' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Shared Source dropdown for caption and description (attachment metadata or custom).
	 *
	 * @return array<string, string>
	 */
	private function get_media_text_source_options(): array {
		return array(
			'none'        => esc_html__( 'None', 'device-mockups-for-elementor' ),
			'alt'         => esc_html__( 'Alternative Text', 'device-mockups-for-elementor' ),
			'title'       => esc_html__( 'Title', 'device-mockups-for-elementor' ),
			'caption'     => esc_html__( 'Caption', 'device-mockups-for-elementor' ),
			'description' => esc_html__( 'Description', 'device-mockups-for-elementor' ),
			'custom'      => esc_html__( 'Custom', 'device-mockups-for-elementor' ),
		);
	}
}
