<?php
/**
 * Component: Footer Loja carousel
 *
 * @context Global footer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! post_type_exists( 'loja' ) ) {
	return;
}

$cache_key    = function_exists( 'aptox_footer_loja_cache_key' ) ? aptox_footer_loja_cache_key() : 'aptox_footer_loja_v8';
$cached_items = get_transient( $cache_key );
$items        = is_array( $cached_items ) ? $cached_items : array();

if ( empty( $items ) ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'loja',
			'posts_per_page'         => -1,
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => true,
			'orderby'                => 'date',
			'order'                  => 'DESC',
		)
	);

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return;
	}

	while ( $query->have_posts() ) {
		$query->the_post();

		if ( ! has_post_thumbnail() ) {
			continue;
		}

		$link_compra = get_post_meta( get_the_ID(), 'link_compra', true );
		$url         = $link_compra ? $link_compra : get_permalink();

		$items[] = array(
			'id'    => get_the_ID(),
			'title' => get_the_title(),
			'url'   => $url,
		);
	}

	wp_reset_postdata();

	if ( empty( $items ) ) {
		return;
	}

	set_transient( $cache_key, $items, HOUR_IN_SECONDS );
}

ob_start();
?>

<section class="footer-loja" aria-label="<?php esc_attr_e( 'Loja', 'aptox' ); ?>">
	<div class="footer-loja__layout">
		<div class="footer-loja__intro">
			<p class="footer-loja__intro-title">
				<?php esc_html_e( 'Compre', 'aptox' ); ?>
			</p>
			<p class="footer-loja__intro-text">
				<?php esc_html_e( 'itens do aptox', 'aptox' ); ?>
			</p>
		</div>

		<div class="footer-loja__carousel recipe-tags-carousel">
			<div class="recipe-tags-carousel__viewport">
				<button
					type="button"
					class="recipe-tags-nav recipe-tags-nav--prev"
					aria-label="<?php echo esc_attr__( 'Ver produtos anteriores', 'aptox' ); ?>"
					disabled
				>
					<span class="recipe-tags-nav__icon" aria-hidden="true">‹</span>
				</button>

				<div class="tags-track footer-loja__track">
					<?php foreach ( $items as $item ) : ?>
						<?php
						$image = function_exists( 'aptox_render_loja_thumbnail' )
							? aptox_render_loja_thumbnail( (int) $item['id'], 'medium' )
							: get_the_post_thumbnail( (int) $item['id'], 'medium', array( 'loading' => 'eager' ) );

						if ( '' === $image ) {
							continue;
						}
						?>
						<article class="footer-loja__item tag-item-wrapper">
							<a
								href="<?php echo esc_url( $item['url'] ); ?>"
								class="footer-loja__card tag-item"
								target="_blank"
								rel="noopener noreferrer sponsored nofollow"
								aria-label="<?php echo esc_attr( sprintf( __( 'Comprar %s', 'aptox' ), $item['title'] ) ); ?>"
							>
								<figure class="footer-loja__media">
									<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</figure>

								<span class="footer-loja__overlay">
									<span class="footer-loja__name">
										<?php echo esc_html( $item['title'] ); ?>
									</span>

									<span class="footer-loja__cta">
										<?php esc_html_e( 'Compre', 'aptox' ); ?>
										<span class="footer-loja__arrow" aria-hidden="true">→</span>
									</span>
								</span>
							</a>
						</article>
					<?php endforeach; ?>
				</div>

				<button
					type="button"
					class="recipe-tags-nav recipe-tags-nav--next"
					aria-label="<?php echo esc_attr__( 'Ver próximos produtos', 'aptox' ); ?>"
				>
					<span class="recipe-tags-nav__icon" aria-hidden="true">›</span>
				</button>
			</div>
		</div>
	</div>
</section>

<?php
echo ob_get_clean();
