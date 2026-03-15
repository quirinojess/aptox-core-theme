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
		add_filter( 'the_content', array( $this, 'inject_recipe_after_second_image' ), 20 );
		add_action( 'wp_head', array( $this, 'render_favicon_links' ) );
		add_action( 'pre_get_posts', array( $this, 'extend_tag_archive_post_types' ) );
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
					$output   .= '<center><h5 id="receita">Vamos a receita?</h5></center>';
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
					$output .= '<center><h5 id="receita">Vamos à receita?</h5></center>';
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
			)
		);
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
