<?php

namespace Arts\DeviceMockups\Compat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPML {

	/**
	 * @param array<string, mixed> $widgets
	 * @return array<string, mixed>
	 */
	public function register( array $widgets ): array {
		$widgets['arts-device-mockup'] = [
			'conditions' => [ 'widgetType' => 'arts-device-mockup' ],
			'fields'     => [
				[
					'field'       => 'caption',
					'type'        => esc_html__( 'Mockup: Caption', 'device-mockups-for-elementor' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'description',
					'type'        => esc_html__( 'Mockup: Description', 'device-mockups-for-elementor' ),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'browser_url_text',
					'type'        => esc_html__( 'Mockup: URL text', 'device-mockups-for-elementor' ),
					'editor_type' => 'LINE',
				],
			],
		];
		return $widgets;
	}
}
