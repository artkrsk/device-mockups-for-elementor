<?php
/**
 * Shared screen partial: renders `.arts-device-mockup__screen` with its media (image OR video) and the
 * hover-gallery items, all from attachment IDs via WP core functions (responsive srcset/sizes/lazy,
 * proper alt). Included by every skin partial so the media markup lives in one place.
 *
 * Expects the id-based $args contract: image_id, video_type, video_hosted_id, poster_id, video_hosted_url,
 * gallery_ids, media_size.
 */

use ArtsDeviceMockups\Arts\Utilities\Utilities;

defined( 'ABSPATH' ) || exit;

$args        = Utilities::parse_template_args( $args ?? array() );
$media_size  = Utilities::get_string_value( $args['media_size'] ?? 'full' );
$video_type  = Utilities::get_string_value( $args['video_type'] ?? 'none' );
$image_id    = Utilities::get_int_value( $args['image_id'] ?? 0 );
$poster_id   = Utilities::get_int_value( $args['poster_id'] ?? 0 );
$hosted_id   = Utilities::get_int_value( $args['video_hosted_id'] ?? 0 );
$hosted_url  = Utilities::get_string_value( $args['video_hosted_url'] ?? '' );
$gallery_ids = Utilities::get_array_value( $args['gallery_ids'] ?? array() );

// Loading hints for the MAIN media image only (gallery items are always lazy/low). Without an explicit
// value WP core's loading-optimization heuristic omits loading=lazy for the first few images it renders
// in OUTPUT order (not viewport order) — which can be a hidden offscreen card, not the hero.
// The skins always pass 'lazy'; the $args contract still accepts 'eager' + 'high' for an
// above-the-fold caller, and an empty value keeps core's default.
$media_attrs         = array(
	'class' => 'arts-device-mockup__media',
	'sizes' => '100vw',
);
$media_loading       = Utilities::get_string_value( $args['media_loading'] ?? '' );
$media_fetchpriority = Utilities::get_string_value( $args['media_fetchpriority'] ?? '' );
if ( '' !== $media_loading ) {
	$media_attrs['loading'] = $media_loading;
}
if ( '' !== $media_fetchpriority ) {
	$media_attrs['fetchpriority'] = $media_fetchpriority;
}

if ( $hosted_id > 0 && '' === $hosted_url ) {
	$hosted_url = (string) wp_get_attachment_url( $hosted_id );
}

$hosted_mime = 'video/mp4';
if ( $hosted_id > 0 && get_post_mime_type( $hosted_id ) ) {
	$hosted_mime = get_post_mime_type( $hosted_id );
}
?>
<div class="arts-device-mockup__screen">
	<?php if ( 'hosted' === $video_type && '' !== $hosted_url ) : ?>
		<?php
		$poster_url = $poster_id > 0 ? (string) wp_get_attachment_image_url( $poster_id, $media_size ) : '';
		// The LCP hero (media_fetchpriority=high) can't put fetchpriority on <video> — that attribute is
		// image/link/script-only — so preload the poster image instead (deduped against poster="") and let
		// the video fetch metadata early. Below-fold videos keep the default deferred load.
		$is_lcp        = 'high' === $media_fetchpriority;
		$video_preload = $is_lcp ? 'metadata' : 'none';
		?>
		<?php if ( $is_lcp && '' !== $poster_url ) : ?>
			<link rel="preload" as="image" href="<?php echo esc_url( $poster_url ); ?>" fetchpriority="high">
		<?php endif; ?>
		<video class="arts-device-mockup__media"<?php echo $poster_url ? ' poster="' . esc_url( $poster_url ) . '"' : ''; ?> autoplay muted loop playsinline preload="<?php echo esc_attr( $video_preload ); ?>">
			<source src="<?php echo esc_url( $hosted_url ); ?>" type="<?php echo esc_attr( $hosted_mime ); ?>">
		</video>
	<?php elseif ( in_array( $video_type, array( 'youtube', 'vimeo' ), true ) && $poster_id > 0 ) : ?>
		<?php
		// External video: the JS handler swaps in the player; the poster is the server-rendered frame.
		echo wp_get_attachment_image( $poster_id, $media_size, false, $media_attrs );
		?>
	<?php elseif ( $image_id > 0 ) : ?>
		<?php echo wp_get_attachment_image( $image_id, $media_size, false, $media_attrs ); ?>
	<?php endif; ?>
	<?php foreach ( $gallery_ids as $index => $gallery_id ) : ?>
		<?php
		echo wp_get_attachment_image(
			Utilities::get_int_value( $gallery_id ),
			$media_size,
			false,
			array(
				'class'                                 => 'arts-device-mockup__gallery-item',
				'sizes'                                 => '100vw',
				'loading'                               => 'lazy',
				'fetchpriority'                         => 'low',
				'aria-hidden'                           => 'true',
				'data-arts-device-mockup-gallery-index' => (int) $index,
			)
		);
		?>
	<?php endforeach; ?>
</div>
