<?php

namespace Arts\DeviceMockups\Elementor\Skins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtsDeviceMockups\Arts\Utilities\Utilities;

/**
 * Abstract base for all five MockupWidget skins. Provides the build_args() helper
 * that maps the flat settings array to the $args contract expected by template partials.
 */
abstract class BaseSkin extends \Elementor\Skin_Base {

	/**
	 * Build the $args array passed to arts_get_template_part(). The partial is a pure
	 * function of $args — zero Elementor dependency inside the template.
	 *
	 * Skin-specific values (orientation, caption_position, browser_url_text) are read
	 * via get_instance_value() which resolves the skin-prefix automatically.
	 *
	 * @param mixed $settings From get_settings_for_display() (mixed → narrowed here, not at call sites).
	 * @return array<string, mixed>
	 */
	protected function build_args( $settings ): array {
		$settings     = Utilities::get_array_value( $settings );
		$image        = Utilities::get_array_value( $settings['image'] ?? array() );
		$video_hosted = Utilities::get_array_value( $settings['video_hosted_url'] ?? array() );
		$video_poster = Utilities::get_array_value( $settings['video_poster'] ?? array() );
		$interval     = Utilities::get_array_value( $settings['gallery_interval'] ?? array() );
		$youtube      = Utilities::get_string_value( $settings['video_youtube_url'] ?? '' );
		$vimeo        = Utilities::get_string_value( $settings['video_vimeo_url'] ?? '' );
		$link         = Utilities::get_array_value( $settings['link'] ?? array() );

		return array(
			'widget_id'           => $this->parent ? $this->parent->get_id() : '',
			'media_size'          => Utilities::get_string_value( $settings['media_size'] ?? 'full' ),
			// No editor control for this: every mockup image is lazy, so a mockup can never take the
			// loading=lazy exemption WP core hands the first few images it renders (see _screen.php).
			'media_loading'       => 'lazy',
			'image_id'            => Utilities::get_int_value( $image['id'] ?? 0 ),
			'video_type'          => Utilities::get_string_value( $settings['video_type'] ?? 'none' ),
			'video_url'           => '' !== $youtube ? $youtube : $vimeo,
			'video_hosted_id'     => Utilities::get_int_value( $video_hosted['id'] ?? 0 ),
			'video_hosted_url'    => Utilities::get_string_value( $video_hosted['url'] ?? '' ),
			'poster_id'           => Utilities::get_int_value( $video_poster['id'] ?? 0 ),
			'video_play_on_hover' => 'yes' === Utilities::get_string_value( $settings['video_play_on_hover'] ?? '' ),
			'caption'             => $this->get_caption( $settings ),
			'description'         => $this->get_description( $settings ),
			'link'                => $link,
			// Elementor's parser drops 'href' and 'on*' keys and restricts key charset — reuse
			// it so the plugin's filtering matches what the editor UI promises.
			'link_custom_attrs'   => \Elementor\Utils::parse_custom_attributes( Utilities::get_string_value( $link['custom_attributes'] ?? '' ) ),
			'gallery_ids'         => $this->extract_gallery_ids( $settings['gallery'] ?? array() ),
			'gallery_interval'    => isset( $interval['size'] ) ? Utilities::get_int_value( $interval['size'] ) : 1000,
			'gallery_loop'        => 'yes' === Utilities::get_string_value( $settings['gallery_loop'] ?? 'yes' ),
			'gallery_trigger'     => Utilities::get_string_value( $settings['gallery_trigger'] ?? 'hover' ),
			'scroll_on_hover'     => 'yes' === Utilities::get_string_value( $settings['scroll_on_hover'] ?? '' ),
			'orientation'         => Utilities::get_string_value( $this->get_instance_value( 'orientation' ) ? $this->get_instance_value( 'orientation' ) : 'landscape' ),
			'caption_position'    => Utilities::get_string_value( $this->get_instance_value( 'caption_position' ) ? $this->get_instance_value( 'caption_position' ) : 'url_bar' ),
			'browser_url_text'    => Utilities::get_string_value( $this->get_instance_value( 'url_text' ) ),
			'show_arrow'          => 'yes' === Utilities::get_string_value( $settings['show_arrow'] ?? '' ),
		);
	}

	/**
	 * Extract attachment IDs from a GALLERY setting. Static picks, RepeaterRowGallery, and every
	 * known Elementor/Pro gallery dynamic tag key items lowercase ['id'=>int, ...]; the uppercase
	 * 'ID' fallback is defensive only — no confirmed producer emits it. Markup is rendered from the
	 * id via wp_get_attachment_image().
	 *
	 * @param mixed $gallery
	 * @return array<int, int>
	 */
	protected function extract_gallery_ids( $gallery ): array {
		if ( ! is_array( $gallery ) ) {
			return array();
		}

		$ids = array();

		foreach ( $gallery as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$id = isset( $item['id'] )
				? Utilities::get_int_value( $item['id'] )
				: Utilities::get_int_value( $item['ID'] ?? 0 );

			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Resolve caption text from caption_source + caption controls.
	 *
	 * @param mixed $settings
	 * @return string
	 */
	protected function get_caption( $settings ): string {
		return $this->resolve_media_text( Utilities::get_array_value( $settings ), 'caption_source', 'caption' );
	}

	/**
	 * Resolve description text from description_source + description controls.
	 *
	 * @param mixed $settings
	 * @return string
	 */
	protected function get_description( $settings ): string {
		return $this->resolve_media_text( Utilities::get_array_value( $settings ), 'description_source', 'description' );
	}

	/**
	 * Resolve text from the bound image attachment or a custom control value.
	 *
	 * @param array<int|string, mixed> $settings   As returned by Utilities::get_array_value().
	 * @param string                   $source_key Settings key for the source SELECT (e.g. caption_source).
	 * @param string                   $custom_key Settings key for the custom text control.
	 * @return string
	 */
	protected function resolve_media_text( array $settings, string $source_key, string $custom_key ): string {
		$source = Utilities::get_string_value( $settings[ $source_key ] ?? 'none' );

		if ( 'none' === $source || '' === $source ) {
			return '';
		}

		if ( 'custom' === $source ) {
			return Utilities::get_string_value( $settings[ $custom_key ] ?? '' );
		}

		$image    = Utilities::get_array_value( $settings['image'] ?? array() );
		$image_id = Utilities::get_int_value( $image['id'] ?? 0 );

		if ( $image_id <= 0 ) {
			return '';
		}

		switch ( $source ) {
			case 'alt':
				return Utilities::get_string_value( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
			case 'title':
				return Utilities::get_string_value( get_the_title( $image_id ) );
			case 'caption':
				return Utilities::get_string_value( wp_get_attachment_caption( $image_id ) );
			case 'description':
				// No core wrapper for the attachment Description — it's the attachment post's
				// post_content; 'raw' runs no filters at all, where the default 'display' context would
				// pass the value through the generic `post_content` filter (the partial wp_kses_post's
				// output).
				return Utilities::get_string_value( get_post_field( 'post_content', $image_id, 'raw' ) );
			default:
				return '';
		}
	}
}
