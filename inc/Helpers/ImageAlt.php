<?php
/**
 * Automatic semantic alt text for theme and content images.
 *
 * @package Aptox
 */

namespace Aptox\Helpers;

class ImageAlt {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_attachment_image_attributes' ), 10, 3 );
		add_filter( 'wp_content_img_tag', array( $this, 'filter_content_image_tag' ), 14, 3 );
		add_filter( 'the_content', array( $this, 'ensure_content_image_alt' ), 35 );
	}

	/**
	 * Resolve a semantic alt string for an attachment.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $fallback      Context fallback, usually a post title.
	 * @param int    $post_id       Post ID for excerpt/title context.
	 * @return string
	 */
	public function resolve_alt( $attachment_id, $fallback = '', $post_id = 0 ) {
		$attachment_id = (int) $attachment_id;

		if ( $attachment_id > 0 ) {
			$meta_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

			if ( is_string( $meta_alt ) && '' !== trim( $meta_alt ) ) {
				return trim( $meta_alt );
			}

			$caption = $this->get_attachment_caption( $attachment_id );

			if ( '' !== $caption ) {
				return $caption;
			}
		}

		$excerpt = $this->get_post_excerpt_text( $post_id );

		if ( '' !== $excerpt ) {
			return $excerpt;
		}

		if ( is_string( $fallback ) && '' !== trim( $fallback ) ) {
			return trim( $fallback );
		}

		$title = $this->get_post_title_text( $post_id );

		if ( '' !== $title ) {
			return $title;
		}

		if ( in_the_loop() ) {
			$title = get_the_title();

			if ( '' !== $title ) {
				return $title;
			}
		}

		if ( is_singular() ) {
			$title = get_the_title( (int) get_queried_object_id() );

			if ( '' !== $title ) {
				return $title;
			}
		}

		return '';
	}

	/**
	 * Ensure attachment image markup includes semantic alt text.
	 *
	 * @param array<string, string> $attr       Image attributes.
	 * @param \WP_Post                $attachment Attachment post object.
	 * @param string|int[]            $size       Requested size.
	 * @return array<string, string>
	 */
	public function filter_attachment_image_attributes( $attr, $attachment, $size ) {
		unset( $size );

		if ( $this->attributes_have_meaningful_alt( $attr ) ) {
			return $attr;
		}

		$context_post_id = in_the_loop() ? (int) get_the_ID() : 0;

		$attr['alt'] = $this->resolve_alt(
			$attachment instanceof \WP_Post ? (int) $attachment->ID : 0,
			'',
			$context_post_id
		);

		return $attr;
	}

	/**
	 * Ensure post content images include semantic alt text.
	 *
	 * @param string $filtered_image Full img tag.
	 * @param string $context        Context.
	 * @param int    $attachment_id  Attachment ID.
	 * @return string
	 */
	public function filter_content_image_tag( $filtered_image, $context, $attachment_id ) {
		unset( $context );

		if ( $this->tag_has_meaningful_alt( $filtered_image ) ) {
			return $filtered_image;
		}

		$src = $this->extract_attribute( $filtered_image, 'src' );

		if ( $this->is_decorative_image( $filtered_image, $src ) ) {
			return $filtered_image;
		}

		$alt = $this->resolve_alt(
			(int) $attachment_id,
			'',
			is_singular() ? (int) get_queried_object_id() : 0
		);

		if ( '' === $alt ) {
			return $filtered_image;
		}

		return $this->inject_img_alt( $filtered_image, $alt );
	}

	/**
	 * Backfill alt text for legacy content image markup.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function ensure_content_image_alt( $content ) {
		if ( is_admin() || ! is_singular() || false === stripos( $content, '<img' ) ) {
			return $content;
		}

		$content = preg_replace_callback(
			'/<figure(\s[^>]*)>\s*((?:<a\b[^>]*>\s*)?<img\b([^>]*)>(?:\s*<\/a>)?)\s*<figcaption\b[^>]*>(.*?)<\/figcaption>\s*<\/figure>/is',
			function ( $matches ) {
				return $this->replace_figure_image_alt( $matches );
			},
			$content
		);

		return preg_replace_callback(
			'/<img\b([^>]*?)(\/?)>/i',
			function ( $matches ) {
				$attrs = $matches[1];
				$close = $matches[2];
				$tag   = '<img' . $attrs . $close . '>';

				if ( $this->tag_has_meaningful_alt( $tag ) ) {
					return $matches[0];
				}

				$src = $this->extract_attribute( $tag, 'src' );

				if ( $this->is_decorative_image( $tag, $src ) ) {
					return $matches[0];
				}

				$attachment_id = $this->resolve_attachment_id_from_tag( $attrs, $src );
				$alt           = $this->resolve_alt(
					$attachment_id,
					'',
					(int) get_queried_object_id()
				);

				if ( '' === $alt ) {
					return $matches[0];
				}

				return $this->inject_img_alt( $matches[0], $alt );
			},
			$content
		);
	}

	/**
	 * Inject alt text into a figure when a figcaption is present.
	 *
	 * @param array<int, string> $matches Regex matches.
	 * @return string
	 */
	private function replace_figure_image_alt( array $matches ) {
		$img_attrs = $matches[3];
		$caption   = trim( wp_strip_all_tags( $matches[4] ) );

		if ( '' === $caption || $this->tag_has_meaningful_alt( '<img' . $img_attrs . '>' ) ) {
			return $matches[0];
		}

		$src = $this->extract_attribute( '<img' . $img_attrs . '>', 'src' );

		if ( $this->is_decorative_image( '<img' . $img_attrs . '>', $src ) ) {
			return $matches[0];
		}

		$new_image = $this->inject_img_alt( $matches[2], $caption );

		return '<figure' . $matches[1] . '>' . $new_image . '<figcaption>' . $matches[4] . '</figcaption></figure>';
	}

	/**
	 * Whether image attributes already contain meaningful alt text.
	 *
	 * @param array<string, string> $attr Image attributes.
	 * @return bool
	 */
	private function attributes_have_meaningful_alt( array $attr ) {
		return isset( $attr['alt'] ) && '' !== trim( (string) $attr['alt'] );
	}

	/**
	 * Whether an img tag already contains meaningful alt text.
	 *
	 * @param string $tag Image tag HTML.
	 * @return bool
	 */
	private function tag_has_meaningful_alt( $tag ) {
		$alt = $this->extract_attribute( $tag, 'alt' );

		return '' !== $alt;
	}

	/**
	 * Determine whether an image should remain decorative.
	 *
	 * @param string $tag Image tag HTML.
	 * @param string $src Image source URL.
	 * @return bool
	 */
	private function is_decorative_image( $tag, $src ) {
		if ( preg_match( '/\baria-hidden=(["\'])true\1/i', $tag ) ) {
			return true;
		}

		if ( preg_match( '/\brole=(["\'])presentation\1/i', $tag ) ) {
			return true;
		}

		if ( '' !== $src && $this->is_decorative_src( $src ) ) {
			return true;
		}

		if ( preg_match( '/\bclass=(["\'])([^"\']*)\1/i', $tag, $matches ) ) {
			$class = strtolower( $matches[2] );

			foreach ( array( 'icon', 'chevron', 'badge', 'logo-mark', 'screen-reader-text' ) as $needle ) {
				if ( false !== strpos( $class, $needle ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Whether a source URL points to a decorative theme asset.
	 *
	 * @param string $src Image source URL.
	 * @return bool
	 */
	private function is_decorative_src( $src ) {
		$patterns = array(
			'/assets/icons/',
			'/assets/favicon/',
			'ico-pin-heart',
			'ui-action-',
			'ui-section-',
		);

		foreach ( $patterns as $pattern ) {
			if ( false !== stripos( $src, $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Resolve an attachment ID from image attributes or source URL.
	 *
	 * @param string $attrs Attribute string.
	 * @param string $src   Image source URL.
	 * @return int
	 */
	private function resolve_attachment_id_from_tag( $attrs, $src ) {
		if ( preg_match( '/\bwp-image-(\d+)\b/i', $attrs, $matches ) ) {
			return (int) $matches[1];
		}

		if ( '' === $src ) {
			return 0;
		}

		return (int) attachment_url_to_postid( $src );
	}

	/**
	 * Resolve the post ID used for excerpt/title fallbacks.
	 *
	 * @param int $post_id Explicit post ID.
	 * @return int
	 */
	private function resolve_context_post_id( $post_id = 0 ) {
		$post_id = (int) $post_id;

		if ( $post_id > 0 ) {
			return $post_id;
		}

		if ( in_the_loop() ) {
			return (int) get_the_ID();
		}

		if ( is_singular() ) {
			return (int) get_queried_object_id();
		}

		return 0;
	}

	/**
	 * Read a trimmed post excerpt for alt text.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_post_excerpt_text( $post_id = 0 ) {
		$post_id = $this->resolve_context_post_id( $post_id );

		if ( $post_id <= 0 ) {
			return '';
		}

		$excerpt = get_post_field( 'post_excerpt', $post_id );

		if ( ! is_string( $excerpt ) || '' === trim( $excerpt ) ) {
			return '';
		}

		$excerpt = trim( wp_strip_all_tags( $excerpt ) );

		return wp_trim_words( $excerpt, 20, '…' );
	}

	/**
	 * Read a post title for alt text.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_post_title_text( $post_id = 0 ) {
		$post_id = $this->resolve_context_post_id( $post_id );

		if ( $post_id <= 0 ) {
			return '';
		}

		$title = get_the_title( $post_id );

		return is_string( $title ) ? trim( $title ) : '';
	}

	/**
	 * Read the media library caption for an attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string
	 */
	private function get_attachment_caption( $attachment_id ) {
		$attachment = get_post( (int) $attachment_id );

		if ( ! $attachment instanceof \WP_Post ) {
			return '';
		}

		return trim( (string) $attachment->post_excerpt );
	}

	/**
	 * Extract an attribute value from an HTML tag.
	 *
	 * @param string $tag  HTML tag.
	 * @param string $name Attribute name.
	 * @return string
	 */
	private function extract_attribute( $tag, $name ) {
		if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '=(["\'])([^"\']*)\1/i', $tag, $matches ) ) {
			return trim( html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' ) );
		}

		return '';
	}

	/**
	 * Inject or replace alt text on an img tag.
	 *
	 * @param string $tag Image tag HTML.
	 * @param string $alt Alt text.
	 * @return string
	 */
	private function inject_img_alt( $tag, $alt ) {
		$alt = trim( $alt );

		if ( '' === $alt ) {
			return $tag;
		}

		if ( preg_match( '/\balt=(["\'])([^"\']*)\1/i', $tag ) ) {
			return (string) preg_replace(
				'/\balt=(["\'])([^"\']*)\1/i',
				'alt=$1' . esc_attr( $alt ) . '$1',
				$tag,
				1
			);
		}

		return (string) preg_replace( '/<img\b/i', '<img alt="' . esc_attr( $alt ) . '"', $tag, 1 );
	}
}
