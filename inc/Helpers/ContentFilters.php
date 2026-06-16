<?php
/**
 * Content and query filters.
 *
 * @package Aptox
 */

namespace Aptox\Helpers;

use Aptox\Services\LikesService;
use Aptox\Services\SeasonService;

class ContentFilters {
	/**
	 * Likes domain service.
	 *
	 * @var LikesService
	 */
	private $likes_service;

	/**
	 * Constructor.
	 *
	 * @param LikesService $likes_service Likes service.
	 */
	public function __construct( LikesService $likes_service ) {
		$this->likes_service = $likes_service;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'the_content', array( $this, 'append_termos_afiliados_disclaimer' ), 10 );
		add_filter( 'the_content', array( $this, 'inject_recipe_after_second_image' ), 20 );
		add_filter( 'the_content', array( $this, 'add_h2_anchors' ), 15 );
		add_filter( 'the_content', array( $this, 'wrap_leia_tambem_blocks' ), 25 );
		add_filter( 'the_content', array( $this, 'lazy_load_post_images' ), 30 );
		add_filter( 'the_content', array( $this, 'add_post_image_pin_buttons' ), 999 );
		add_action( 'wp_head', array( $this, 'render_favicon_links' ) );
		add_action( 'pre_get_posts', array( $this, 'extend_tag_archive_post_types' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_archives_by_tag_query_param' ) );
	}

	/**
	 * Append affiliate products disclaimer on the terms of use page.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function append_termos_afiliados_disclaimer( $content ) {
		if ( is_admin() || ! is_page( 'termos-de-uso' ) || '' === trim( $content ) ) {
			return $content;
		}

		$marker = 'O site reserva o direito de veicular anúncios e campanhas de publicidade, sempre sinalizadas para manter a transparência junto ao leitor.';

		if ( false === strpos( wp_strip_all_tags( $content ), wp_strip_all_tags( $marker ) ) ) {
			return $content;
		}

		if ( false !== strpos( $content, 'aptox-termos-afiliados' ) ) {
			return $content;
		}

		$paragraph = '<p class="aptox-termos-afiliados">Os produtos recomendados no site são links de afiliados. Não nos responsabilizamos pela entrega e envio dos mesmos. A plataforma de compra parceira gerencia, legal e juridicamente, todos os aspectos da transação.</p>';

		$pattern = '/(<p[^>]*>\s*' . preg_quote( $marker, '/' ) . '\s*<\/p>)/iu';

		if ( preg_match( $pattern, $content ) ) {
			return preg_replace( $pattern, '$1' . $paragraph, $content, 1 );
		}

		$marker_pos = strpos( $content, $marker );

		if ( false === $marker_pos ) {
			return $content;
		}

		$close_pos = strpos( $content, '</p>', $marker_pos );

		if ( false === $close_pos ) {
			return $content;
		}

		$insert_at = $close_pos + 4;

		return substr( $content, 0, $insert_at ) . $paragraph . substr( $content, $insert_at );
	}

	/**
	 * Wrap blocks that start with "Leia também" and include links.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function wrap_leia_tambem_blocks( $content ) {
		if ( is_admin() || ! is_singular() || '' === trim( $content ) ) {
			return $content;
		}

		$tags = array( 'p', 'li', 'blockquote', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' );

		foreach ( $tags as $tag ) {
			$content = preg_replace_callback(
				'/<' . $tag . '(\s[^>]*)?>(.*?)<\/' . $tag . '>/is',
				function ( $matches ) use ( $tag ) {
					return $this->mark_leia_tambem_element( $tag, $matches );
				},
				$content
			);
		}

		return $content;
	}

	/**
	 * Add the Leia também class to a matching element.
	 *
	 * @param string               $tag     HTML tag name.
	 * @param array<int, string>   $matches Regex matches.
	 * @return string
	 */
	private function mark_leia_tambem_element( $tag, array $matches ) {
		$element = $matches[0];
		$attrs   = $matches[1] ?? '';
		$inner   = $matches[2];

		if ( false !== strpos( $element, 'post-leia-tambem' ) ) {
			return $element;
		}

		if ( ! $this->is_leia_tambem_block( $inner ) ) {
			return $element;
		}

		if ( preg_match( '/\bclass=(["\'])([^"\']*)\1/', $attrs, $class_match ) ) {
			$quote   = $class_match[1];
			$classes = trim( $class_match[2] . ' post-leia-tambem' );
			$attrs   = preg_replace( '/\bclass=(["\'])([^"\']*)\1/', 'class=' . $quote . $classes . $quote, $attrs, 1 );
		} else {
			$attrs = ' class="post-leia-tambem"' . $attrs;
		}

		return '<' . $tag . $attrs . '>' . $inner . '</' . $tag . '>';
	}

	/**
	 * Detect content that starts with "Leia também" and contains a link.
	 *
	 * @param string $html Element inner HTML.
	 * @return bool
	 */
	private function is_leia_tambem_block( $html ) {
		if ( false === stripos( $html, '<a' ) ) {
			return false;
		}

		$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' );
		$text = preg_replace( '/\s+/u', ' ', trim( $text ) );

		if ( function_exists( 'remove_accents' ) ) {
			$text = remove_accents( $text );
		}

		$text = strtolower( $text );

		return 0 === strpos( $text, 'leia tambem' ) || 0 === strpos( $text, 'leia também' );
	}

	/**
	 * Ensure post content images use native lazy loading.
	 *
	 * Keeps the first image eager for LCP; defers the rest.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function lazy_load_post_images( $content ) {
		if ( is_admin() || ! is_singular() || '' === trim( $content ) || false === stripos( $content, '<img' ) ) {
			return $content;
		}

		$image_index = 0;

		return preg_replace_callback(
			'/<img\b([^>]*?)(\/?)>/i',
			function ( $matches ) use ( &$image_index ) {
				$attrs = $matches[1];
				$close = $matches[2];
				$image_index++;

				if ( preg_match( '/\bloading\s*=/i', $attrs ) ) {
					return $matches[0];
				}

				$loading = 1 === $image_index ? 'eager' : 'lazy';
				$extra   = ' loading="' . esc_attr( $loading ) . '"';

				if ( 1 === $image_index && ! preg_match( '/\bfetchpriority\s*=/i', $attrs ) ) {
					$extra .= ' fetchpriority="high"';
				}

				if ( $image_index > 1 && ! preg_match( '/\bdecoding\s*=/i', $attrs ) ) {
					$extra .= ' decoding="async"';
				}

				return '<img' . $extra . $attrs . $close . '>';
			},
			$content
		);
	}

	/**
	 * Block extension Pin It overlays and inject the theme Pin It button on content images.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function add_post_image_pin_buttons( $content ) {
		if ( is_admin() || ! is_singular() || '' === trim( $content ) || false === stripos( $content, '<img' ) ) {
			return $content;
		}

		$post_url = get_permalink();
		$title    = get_the_title();
		$index    = 0;

		$content = preg_replace_callback(
			'/<figure(\s[^>]*)>\s*((?:<a\b[^>]*>\s*)?<img\b([^>]*)>\s*(?:<\/a>\s*)?)(\s*(?:<figcaption\b[^>]*>.*?<\/figcaption>\s*)?)<\/figure>/is',
			function ( $matches ) use ( $post_url, $title, &$index ) {
				if ( ! $this->is_pinnable_content_image( $matches[1], $matches[3] ) ) {
					return $matches[0];
				}

				return $this->inject_post_image_pin_button( $matches[1], $matches[2], $matches[3], $matches[4], $post_url, $title, $index );
			},
			$content
		);

		return $content;
	}

	/**
	 * Determine whether an image should receive the Pin It button.
	 *
	 * @param string $figure_attrs Figure attributes.
	 * @param string $img_attrs    Image attributes.
	 * @return bool
	 */
	private function is_pinnable_content_image( $figure_attrs, $img_attrs ) {
		if ( false !== stripos( $figure_attrs, 'post-image-pin__media' ) ) {
			return false;
		}

		if ( false !== stripos( $figure_attrs, 'wp-block-gallery' ) ) {
			return false;
		}

		$src = $this->extract_img_attr( $img_attrs, 'src' );
		if ( ! $src ) {
			return false;
		}

		if ( false !== stripos( $src, '/assets/icons/' ) || false !== stripos( $src, 'ico-pin-heart' ) ) {
			return false;
		}

		if ( false !== stripos( $figure_attrs, 'wp-block-image' ) ) {
			return true;
		}

		$class = $this->extract_img_attr( $img_attrs, 'class' );
		if ( false !== stripos( $class, 'wp-image-' ) ) {
			return true;
		}

		if ( false !== stripos( $src, '/uploads/' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Inject Pin It button markup into a figure that wraps an image.
	 *
	 * @param string $figure_attrs Figure attributes.
	 * @param string $image_html   Image markup, optionally wrapped in a link.
	 * @param string $img_attrs    Image attributes.
	 * @param string $figcaption   Optional figcaption markup.
	 * @param string $post_url     Post permalink.
	 * @param string $title        Post title.
	 * @param int    $index        Button index for unique SVG ids.
	 * @return string
	 */
	private function inject_post_image_pin_button( $figure_attrs, $image_html, $img_attrs, $figcaption, $post_url, $title, &$index ) {
		$src = $this->extract_img_attr( $img_attrs, 'src' );
		if ( ! $src ) {
			return '<figure' . $figure_attrs . '>' . $image_html . $figcaption . '</figure>';
		}

		++$index;
		$image_html = $this->mark_img_for_pin_block( $image_html );
		$button     = $this->render_post_pin_button( $post_url, $src, $title, $index );

		return '<figure' . $figure_attrs . '><div class="post-image-pin__media">' . $image_html . $button . '</div>' . $figcaption . '</figure>';
	}

	/**
	 * Render the circular Pin It badge button.
	 *
	 * @param string $post_url  Post permalink.
	 * @param string $image_url Image source URL.
	 * @param string $title     Post title.
	 * @param int    $index     Unique index for SVG ids.
	 * @return string
	 */
	private function render_post_pin_button( $post_url, $image_url, $title, $index ) {
		$pin_url = 'https://pinterest.com/pin/create/button/?url=' . rawurlencode( $post_url ) . '&media=' . rawurlencode( $image_url ) . '&description=' . rawurlencode( $title );
		$marquee = str_repeat( 'pin it · ', 6 );

		ob_start();
		?>
		<a
			class="post-image-pin__button"
			href="<?php echo esc_url( $pin_url ); ?>"
			data-marquee="<?php echo esc_attr( $marquee ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			aria-label="<?php esc_attr_e( 'Salvar no Pinterest', 'aptox' ); ?>"
		>
			<span class="post-image-pin__badge" aria-hidden="true">
				<span class="post-image-pin__ring"></span>
				<span class="post-image-pin__heart">
					<img
						src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-pin-heart.svg' ); ?>"
						alt=""
						width="16"
						height="16"
						data-aptox-pin="skip"
					>
				</span>
			</span>
		</a>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Add Pinterest block attributes to content images.
	 *
	 * @param string $img_tag Img tag markup.
	 * @return string
	 */
	private function mark_img_for_pin_block( $html ) {
		if ( ! preg_match( '/<img\b/i', $html ) ) {
			return $html;
		}

		return preg_replace_callback(
			'/<img\b([^>]*?)(\/?)>/i',
			function ( $matches ) {
				$attrs = $matches[1];
				$close = $matches[2];

				if ( ! preg_match( '/\bdata-pin-nopin=/i', $attrs ) ) {
					$attrs = ' data-pin-nopin="true" data-pin-no-hover="true"' . $attrs;
				}

				if ( ! preg_match( '/\bdata-aptox-pin=/i', $attrs ) ) {
					$attrs = ' data-aptox-pin="1"' . $attrs;
				}

				return '<img' . $attrs . $close . '>';
			},
			$html,
			1
		);
	}

	/**
	 * Extract an attribute value from an img attribute string.
	 *
	 * @param string $attrs Attribute string.
	 * @param string $name  Attribute name.
	 * @return string
	 */
	private function extract_img_attr( $attrs, $name ) {
		if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '=(["\'])([^"\']*)\1/i', $attrs, $matches ) ) {
			return html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' );
		}

		return '';
	}

	/**
	 * Append a class name to an HTML attribute string.
	 *
	 * @param string $attrs      Existing attributes.
	 * @param string $class_name Class to append.
	 * @return string
	 */
	private function append_html_class( $attrs, $class_name ) {
		if ( preg_match( '/\bclass=(["\'])([^"\']*)\1/', $attrs, $matches ) ) {
			$quote   = $matches[1];
			$classes = trim( $matches[2] . ' ' . $class_name );

			return preg_replace( '/\bclass=(["\'])([^"\']*)\1/', 'class=' . $quote . $classes . $quote, $attrs, 1 );
		}

		return $attrs . ' class="' . esc_attr( $class_name ) . '"';
	}

	/**
	 * Add anchor ids to h2 headings in Casa / Celebre singles.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function add_h2_anchors( $content ) {
		if ( is_admin() || ! is_singular( array( 'casas', 'celebracoes' ) ) ) {
			return $content;
		}

		return aptox_add_h2_anchors_to_content( $content );
	}

	/**
	 * Inject recipe shortcode after second image.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function inject_recipe_after_second_image( $content ) {
		if ( is_admin() || ! is_singular() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return $content;
		}

		$recipe_id = get_post_meta( $post_id, 'codigo_receita', true );
		if ( ! $recipe_id ) {
			return $content;
		}

		$raw_content = get_post_field( 'post_content', $post_id );
		$blocks      = parse_blocks( $raw_content );
		$image_count = 0;
		$output      = '';
		$injected    = false;

		foreach ( $blocks as $block ) {
			$rendered = render_block( $block );
			$output  .= $rendered;

			if ( ! $injected && $this->block_has_image( $block, $rendered ) ) {
				$image_count++;

				if ( 2 === $image_count ) {
					$output   .= $this->render_recipe_section_heading();
					$output   .= do_shortcode( '[wprm-recipe id="' . esc_attr( $recipe_id ) . '"]' );
					$injected = true;
				}
			}
		}

		if ( $injected ) {
			return $output;
		}

		return $this->inject_after_html_images( $content, $recipe_id );
	}

	/**
	 * Check if block includes image semantics.
	 *
	 * @param array<string, mixed> $block Block.
	 * @param string               $rendered Rendered block HTML.
	 * @return bool
	 */
	private function block_has_image( $block, $rendered ) {
		if (
			isset( $block['blockName'] ) &&
			in_array(
				$block['blockName'],
				array(
					'core/image',
					'core/gallery',
					'core/media-text',
					'core/cover',
				),
				true
			)
		) {
			return true;
		}

		return false !== stripos( $rendered, '<img ' );
	}

	/**
	 * Render recipe section heading markup.
	 *
	 * @return string
	 */
	private function render_recipe_section_heading() {
		$icon_url = get_template_directory_uri() . '/assets/img/ico-recipe.png';

		ob_start();
		?>
		<header class="recipe-section-heading">
			<div class="recipe-section-heading__inner">
				<figure class="recipe-section-heading__icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( $icon_url ); ?>"
						alt=""
						loading="lazy"
					>
				</figure>

				<h5 id="receita" class="recipe-section-heading__title">
					Vamos a
					<span class="recipe-section-heading__hand"><?php echo esc_html( aptox_hand_text( 'receita' ) ); ?></span>?
				</h5>
			</div>
		</header>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Inject recipe fallback for classic HTML content.
	 *
	 * @param string $content Content.
	 * @param string $recipe_id Recipe ID.
	 * @return string
	 */
	private function inject_after_html_images( $content, $recipe_id ) {
		$parts  = preg_split( '/(<img[^>]*>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		$output = '';
		$count  = 0;

		foreach ( $parts as $part ) {
			$output .= $part;

			if ( false !== stripos( $part, '<img' ) ) {
				$count++;

				if ( 2 === $count ) {
					$output .= $this->render_recipe_section_heading();
					$output .= do_shortcode( '[wprm-recipe id="' . esc_attr( $recipe_id ) . '"]' );
				}
			}
		}

		return $output;
	}

	/**
	 * Inject favicon links in document head.
	 *
	 * @return void
	 */
	public function render_favicon_links() {
		?>
		<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/favicon/favicon.ico' ); ?>" sizes="any">
		<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/favicon/favicon.png' ); ?>" type="image/svg+xml">
		<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/favicon/apple-touch-icon.png' ); ?>">
		<?php
	}

	/**
	 * Expand tag archives to include custom post types.
	 *
	 * @param \WP_Query $query Query object.
	 * @return void
	 */
	public function extend_tag_archive_post_types( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_tag() ) {
			return;
		}

		$query->set(
			'post_type',
			array(
				'post',
				'casas',
				'receitas',
				'celebracoes',
				'loja',
			)
		);
	}

	/**
	 * Filter archive queries when ?tag=slug is present.
	 *
	 * @param \WP_Query $query Query object.
	 * @return void
	 */
	public function filter_archives_by_tag_query_param( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$tag_slug = $this->resolve_tag_query_slug( $query );

		if ( '' === $tag_slug ) {
			return;
		}

		$is_receitas_archive = $query->is_post_type_archive( 'receitas' );
		$is_receita_tax      = $query->is_tax( array( 'receita_categoria', 'receita' ) );
		$is_casa_tax         = $query->is_tax( array( 'casa_categoria', 'casa' ) );

		if ( ! $is_receitas_archive && ! $is_receita_tax && ! $is_casa_tax ) {
			return;
		}

		$tax_query = $query->get( 'tax_query' );

		if ( ! is_array( $tax_query ) ) {
			$tax_query = array();
		}

		if ( ! empty( $tax_query ) && ! isset( $tax_query['relation'] ) ) {
			$tax_query['relation'] = 'AND';
		}

		$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'field'    => 'slug',
			'terms'    => $tag_slug,
		);

		$query->set( 'tax_query', $tax_query );

		if ( $is_receitas_archive ) {
			$query->set( 'post_type', 'receitas' );
		}
	}

	/**
	 * Resolve tag slug from query vars or query string.
	 *
	 * @param \WP_Query $query Query object.
	 * @return string
	 */
	private function resolve_tag_query_slug( $query ) {
		$tag = $query->get( 'tag' );

		if ( is_string( $tag ) && '' !== $tag ) {
			return sanitize_title( $tag );
		}

		if ( ! isset( $_GET['tag'] ) ) {
			return '';
		}

		return sanitize_title( wp_unslash( (string) $_GET['tag'] ) );
	}

	/**
	 * Expose likes service for compatibility layer.
	 *
	 * @return LikesService
	 */
	public function get_likes_service() {
		return $this->likes_service;
	}

	/**
	 * Proxy legacy season label helper.
	 *
	 * @param string $slug Season slug.
	 * @return string
	 */
	public function get_season_label( $slug ) {
		return SeasonService::get_season_label( $slug );
	}
}
