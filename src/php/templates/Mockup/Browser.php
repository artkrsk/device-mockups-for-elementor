<?php
use ArtsDeviceMockups\Arts\Utilities\Utilities;

defined( 'ABSPATH' ) || exit;

$args      = Utilities::parse_template_args( $args ?? array() );
$widget_id = Utilities::get_string_value( $args['widget_id'] ?? '' );
if ( '' === $widget_id ) {
	$widget_id = wp_unique_id( 'arts-device-mockup-' );
}
$image_id         = Utilities::get_int_value( $args['image_id'] ?? 0 );
$poster_id        = Utilities::get_int_value( $args['poster_id'] ?? 0 );
$hosted_id        = Utilities::get_int_value( $args['video_hosted_id'] ?? 0 );
$video_type       = Utilities::get_string_value( $args['video_type'] ?? 'none' );
$video_url        = Utilities::get_string_value( $args['video_url'] ?? '' );
$gallery_ids      = Utilities::get_array_value( $args['gallery_ids'] ?? array() );
$link             = Utilities::get_array_value( $args['link'] ?? array() );
$link_url         = Utilities::get_string_value( $link['url'] ?? '' );
$link_label       = Utilities::get_string_value( $link['aria_label'] ?? '' );
$caption          = Utilities::get_string_value( $args['caption'] ?? '' );
$description      = Utilities::get_string_value( $args['description'] ?? '' );
$browser_url_text = Utilities::get_string_value( $args['browser_url_text'] ?? '' );
$has_link         = '' !== $link_url;
$has_gallery      = ! empty( $gallery_ids );
$caption_position = Utilities::get_string_value( $args['caption_position'] ?? 'url_bar' );
$is_url_bar_mode  = 'url_bar' === $caption_position;
$show_arrow       = $has_link && ! empty( $args['show_arrow'] );

// The diagonal blink arrow markup is shared between the URL-bar pill and the below-frame caption.
ob_start();
if ( $show_arrow ) :
	?>
	<span class="arts-device-mockup__icon-blink" aria-hidden="true">
		<span class="arts-device-mockup__icon-blink-inner arts-device-mockup__icon-blink-inner_normal">
			<svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M3.36 36L0 32.64L27.84 4.8H12V0H36V24H31.2V8.16L3.36 36Z"/></svg>
		</span>
		<span class="arts-device-mockup__icon-blink-inner arts-device-mockup__icon-blink-inner_hover">
			<svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M3.36 36L0 32.64L27.84 4.8H12V0H36V24H31.2V8.16L3.36 36Z"/></svg>
		</span>
	</span>
	<?php
endif;
$arrow_html = ob_get_clean();

// Graceful underflow: a dynamic tag bound to a non-existent repeater row resolves to empty —
// render nothing rather than an empty frame.
$has_media = $image_id > 0 || $hosted_id > 0 || $poster_id > 0 || '' !== $video_url || $has_gallery;
if ( ! $has_media ) {
	return;
}

$figure_data_attrs = 'data-arts-device-mockup';

if ( 'none' !== $video_type ) {
	$figure_data_attrs .= ' data-arts-device-mockup-video="' . esc_attr( $video_type ) . '"';
	if ( '' !== $video_url ) {
		$figure_data_attrs .= ' data-arts-device-mockup-video-url="' . esc_attr( $video_url ) . '"';
	}
	if ( ! empty( $args['video_play_on_hover'] ) ) {
		$figure_data_attrs .= ' data-arts-device-mockup-play-on-hover';
	}
}

if ( $has_gallery ) {
	$figure_data_attrs .= ' data-arts-device-mockup-gallery-trigger="' . esc_attr( Utilities::get_string_value( $args['gallery_trigger'] ?? 'hover' ) ) . '"';
	$figure_data_attrs .= ' data-arts-device-mockup-gallery-interval="' . esc_attr( Utilities::get_string_value( $args['gallery_interval'] ?? 1000 ) ) . '"';
	if ( ! empty( $args['gallery_loop'] ) ) {
		$figure_data_attrs .= ' data-arts-device-mockup-gallery-loop';
	}
}

if ( ! empty( $args['scroll_on_hover'] ) ) {
	$figure_data_attrs .= ' data-arts-device-mockup-scroll-on-hover';
}

$link_rel = array();
if ( $has_link ) {
	if ( ! empty( $link['nofollow'] ) ) {
		$link_rel[] = 'nofollow';
	}
	if ( ! empty( $link['is_external'] ) ) {
		$link_rel[] = 'noreferrer';
		$link_rel[] = 'noopener';
	}
}

// Capture the screen block once to reuse across the url_bar / below branches.
ob_start();
arts_get_template_part( 'Mockup/_screen', $args, ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups' );
$screen_html = ob_get_clean();
?>
<?php if ( $has_link ) : ?>
<a
	href="<?php echo esc_url( $link_url ); ?>"
	class="arts-device-mockup__link"
	<?php
	if ( ! empty( $link['is_external'] ) ) :
		?>
		target="_blank"<?php endif; ?>
	<?php
	if ( ! empty( $link_rel ) ) :
		?>
		rel="<?php echo esc_attr( implode( ' ', $link_rel ) ); ?>"<?php endif; ?>
	<?php
	if ( '' !== $link_label ) :
		?>
		aria-label="<?php echo esc_attr( $link_label ); ?>"<?php endif; ?>
>
<?php endif; ?>
<?php
// The browser window (border + clipped corners) is the __frame CHILD of the figure, so a
// "below frame" caption can sit OUTSIDE it (still inside the figure) instead of being clipped
// by the frame's overflow:hidden.
?>
<?php if ( $is_url_bar_mode ) : ?>
<figure class="arts-device-mockup arts-device-mockup_browser" aria-labelledby="arts-device-mockup-url-<?php echo esc_attr( $widget_id ); ?>" <?php echo $figure_data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values pre-escaped via esc_attr() ?>>
	<div class="arts-device-mockup__frame">
		<div class="arts-device-mockup__bar" aria-hidden="true">
			<span class="arts-device-mockup__dots" aria-hidden="true"><span class="arts-device-mockup__dot"></span><span class="arts-device-mockup__dot"></span><span class="arts-device-mockup__dot"></span></span>
			<span class="arts-device-mockup__url-pill">
				<span class="arts-device-mockup__url-text" id="arts-device-mockup-url-<?php echo esc_attr( $widget_id ); ?>"><?php echo esc_html( $caption ); ?></span>
				<?php echo $arrow_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?>
			</span>
			<span class="arts-device-mockup__spacer" aria-hidden="true"></span>
		</div>
		<?php echo $screen_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-rendered partial markup ?>
	</div>
</figure>
<?php else : ?>
<figure class="arts-device-mockup arts-device-mockup_browser" <?php echo $figure_data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values pre-escaped via esc_attr() ?>>
	<div class="arts-device-mockup__frame">
		<div class="arts-device-mockup__bar" aria-hidden="true">
			<span class="arts-device-mockup__dots" aria-hidden="true"><span class="arts-device-mockup__dot"></span><span class="arts-device-mockup__dot"></span><span class="arts-device-mockup__dot"></span></span>
			<span class="arts-device-mockup__url-pill" aria-hidden="true">
				<span class="arts-device-mockup__url-text"><?php echo esc_html( $browser_url_text ); ?></span>
			</span>
			<span class="arts-device-mockup__spacer" aria-hidden="true"></span>
		</div>
		<?php echo $screen_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-rendered partial markup ?>
	</div>
	<?php if ( ! empty( $args['caption'] ) ) : ?>
	<figcaption class="arts-device-mockup__caption"><?php echo wp_kses_post( $caption ); ?><?php echo $arrow_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></figcaption>
	<?php endif; ?>
</figure>
<?php endif; ?>
<?php if ( $has_link ) : ?>
</a>
<?php endif; ?>
<?php if ( ! empty( $args['description'] ) ) : ?>
<p class="arts-device-mockup__description"><?php echo wp_kses_post( $description ); ?></p>
<?php endif; ?>
