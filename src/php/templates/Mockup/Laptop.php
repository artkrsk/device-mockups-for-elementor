<?php
use ArtsDeviceMockups\Arts\Utilities\Utilities;

defined( 'ABSPATH' ) || exit;

$args        = Utilities::parse_template_args( $args ?? array() );
$image_id    = Utilities::get_int_value( $args['image_id'] ?? 0 );
$poster_id   = Utilities::get_int_value( $args['poster_id'] ?? 0 );
$hosted_id   = Utilities::get_int_value( $args['video_hosted_id'] ?? 0 );
$video_type  = Utilities::get_string_value( $args['video_type'] ?? 'none' );
$video_url   = Utilities::get_string_value( $args['video_url'] ?? '' );
$gallery_ids = Utilities::get_array_value( $args['gallery_ids'] ?? array() );
$link        = Utilities::get_array_value( $args['link'] ?? array() );
$link_url    = Utilities::get_string_value( $link['url'] ?? '' );
$link_label  = Utilities::get_string_value( $link['aria_label'] ?? '' );
$caption     = Utilities::get_string_value( $args['caption'] ?? '' );
$description = Utilities::get_string_value( $args['description'] ?? '' );
$has_link    = '' !== $link_url;
$has_gallery = ! empty( $gallery_ids );

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
?>
<?php if ( $has_link ) : ?>
<a
	href="<?php echo esc_url( $link_url ); ?>"
	class="arts-device-mockup__link"
	<?php if ( ! empty( $link['is_external'] ) ) : ?>target="_blank"<?php endif; ?>
	<?php if ( ! empty( $link_rel ) ) : ?>rel="<?php echo esc_attr( implode( ' ', $link_rel ) ); ?>"<?php endif; ?>
	<?php if ( '' !== $link_label ) : ?>aria-label="<?php echo esc_attr( $link_label ); ?>"<?php endif; ?>
>
<?php endif; ?>
<figure class="arts-device-mockup arts-device-mockup_laptop" <?php echo $figure_data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values pre-escaped via esc_attr() ?>>
	<div class="arts-device-mockup__lid">
		<span class="arts-device-mockup__camera" aria-hidden="true"></span>
		<?php arts_get_template_part( 'Mockup/_screen', $args, ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups' ); ?>
	</div>
	<div class="arts-device-mockup__base" aria-hidden="true">
		<span class="arts-device-mockup__notch"></span>
	</div>
	<?php if ( '' !== $caption ) : ?>
	<figcaption class="arts-device-mockup__caption"><?php echo wp_kses_post( $caption ); ?></figcaption>
	<?php endif; ?>
</figure>
<?php if ( $has_link ) : ?>
</a>
<?php endif; ?>
<?php if ( '' !== $description ) : ?>
<p class="arts-device-mockup__description"><?php echo wp_kses_post( $description ); ?></p>
<?php endif; ?>
