<?php
/**
 * Public template functions: render a mockup from PHP, outside the Elementor widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print a mockup.
 *
 * @param string               $skin Partial name: Bare, Laptop, Tablet, Browser or Phone.
 * @param array<string, mixed> $args The partial's $args contract.
 * @return void
 */
function arts_device_mockups_render( string $skin, array $args ): void {
	// The name becomes a file path: letters only, so a caller can't walk out of the templates dir.
	if ( 1 !== preg_match( '/^[A-Za-z]+$/', $skin ) ) {
		return;
	}

	arts_get_template_part( "Mockup/{$skin}", $args, ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups' );
}

/**
 * Return the markup arts_device_mockups_render() would print.
 *
 * @param string               $skin Partial name: Bare, Laptop, Tablet, Browser or Phone.
 * @param array<string, mixed> $args The partial's $args contract.
 * @return string
 */
function arts_device_mockups_get( string $skin, array $args ): string {
	ob_start();
	arts_device_mockups_render( $skin, $args );

	return (string) ob_get_clean();
}

/**
 * The wp_kses() list for the hover overlay's icon markup (the `overlay_icon` arg).
 *
 * @return array<string, array<string, bool>>
 */
function arts_device_mockups_icon_allowed_html(): array {
	// shortcut: icon-grade SVG only (shapes, no gradients, masks or filters), extend when a real icon gets stripped.
	$paint = array(
		'class'             => true,
		'fill'              => true,
		'fill-rule'         => true,
		'fill-opacity'      => true,
		'clip-rule'         => true,
		'stroke'            => true,
		'stroke-width'      => true,
		'stroke-linecap'    => true,
		'stroke-linejoin'   => true,
		'stroke-miterlimit' => true,
		'stroke-dasharray'  => true,
		'stroke-opacity'    => true,
		'opacity'           => true,
		'transform'         => true,
	);

	return array(
		'svg'      => $paint + array(
			'xmlns'               => true,
			'viewbox'             => true,
			'width'               => true,
			'height'              => true,
			'preserveaspectratio' => true,
			'role'                => true,
			'focusable'           => true,
			'aria-hidden'         => true,
			'aria-label'          => true,
		),
		'g'        => $paint,
		'title'    => array(),
		'path'     => $paint + array( 'd' => true ),
		'circle'   => $paint + array(
			'cx' => true,
			'cy' => true,
			'r'  => true,
		),
		'ellipse'  => $paint + array(
			'cx' => true,
			'cy' => true,
			'rx' => true,
			'ry' => true,
		),
		'rect'     => $paint + array(
			'x'      => true,
			'y'      => true,
			'width'  => true,
			'height' => true,
			'rx'     => true,
			'ry'     => true,
		),
		'line'     => $paint + array(
			'x1' => true,
			'y1' => true,
			'x2' => true,
			'y2' => true,
		),
		'polyline' => $paint + array( 'points' => true ),
		'polygon'  => $paint + array( 'points' => true ),
		'i'        => array(
			'class'       => true,
			'aria-hidden' => true,
		),
	);
}
