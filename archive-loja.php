<?php
/**
 * Archive template for Loja products.
 */

get_header();
?>

<main class="container">
	<?php if ( have_posts() ) : ?>
		<?php
		$paged     = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$max_pages = (int) $wp_query->max_num_pages;
		?>
		<section class="archive-grid" aria-label="<?php echo esc_attr__( 'Loja', 'aptox' ); ?>">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php
				$link_compra = get_post_meta( get_the_ID(), 'link_compra', true );
				$card_url    = $link_compra ? $link_compra : get_permalink();
				$card_attrs  = $link_compra
					? ' target="_blank" rel="noopener noreferrer sponsored nofollow"'
					: '';
				?>
				<article <?php post_class( 'archive-card loja-card' ); ?>>
					<a
						href="<?php echo esc_url( $card_url ); ?>"
						class="archive-thumb"
						aria-hidden="true"
						tabindex="-1"
						<?php echo $card_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					>
						<?php if ( has_post_thumbnail() ) : ?>
							<figure class="archive-image">
								<?php the_post_thumbnail( 'large' ); ?>
							</figure>
						<?php endif; ?>
					</a>

					<h3 class="archive-title">
						<a href="<?php echo esc_url( $card_url ); ?>"<?php echo $card_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php the_title(); ?>
						</a>
					</h3>
				</article>
			<?php endwhile; ?>
		</section>

		<?php if ( $max_pages > $paged ) : ?>
			<?php
			aptox_render_archive_load_more(
				array(
					'paged'     => $paged,
					'max_pages' => $max_pages,
					'next_url'  => get_pagenum_link( $paged + 1 ),
				)
			);
			?>
		<?php endif; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nenhum produto encontrado.', 'aptox' ); ?></p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
