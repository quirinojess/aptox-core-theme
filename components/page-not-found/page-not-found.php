<?php
/**
 * Component: Page not found
 *
 * @context 404 template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Aptox\Services\NotFoundService;

$context  = NotFoundService::get_context();
$posts    = NotFoundService::get_posts( $context );
$is_mixed = 'mixed' === ( $context['mode'] ?? '' );
$term     = ( ! empty( $context['term'] ) && $context['term'] instanceof WP_Term ) ? $context['term'] : null;
$is_loja_category = $term && 'loja_categoria' === $term->taxonomy;

$render_card = static function ( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	?>
	<article <?php post_class( 'archive-card', $post ); ?>>
		<a
			href="<?php echo esc_url( get_permalink( $post ) ); ?>"
			class="archive-thumb"
			aria-hidden="true"
			tabindex="-1"
		>
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<figure class="archive-image">
					<?php echo get_the_post_thumbnail( $post, 'large', array( 'loading' => 'lazy' ) ); ?>
				</figure>
			<?php endif; ?>
		</a>

		<h3 class="archive-title">
			<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
				<?php echo esc_html( get_the_title( $post ) ); ?>
			</a>
		</h3>
	</article>
	<?php
};
?>

<section class="page-not-found" aria-labelledby="page-not-found-title">
	<header class="page-not-found-header">
		<h1 id="page-not-found-title" class="page-not-found-title">
			<?php
			if ( $is_loja_category ) {
				echo esc_html( $term->name );
			} else {
				esc_html_e( 'Esse conteúdo ainda não está disponível.', 'aptox' );
			}
			?>
		</h1>
	</header>

	<?php if ( ! empty( $posts ) ) : ?>
		<div class="page-not-found-body">
			<div class="page-not-found-inner">
				<?php if ( $is_mixed ) : ?>
					<p class="page-not-found-hand">
						<?php echo esc_html( aptox_hand_text( __( 'mas veja esses posts', 'aptox' ) ) ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $is_loja_category ) : ?>
					<?php
					get_template_part(
						'components/grid-loja/grid-loja',
						null,
						array(
							'term_id'        => (int) $term->term_id,
							'posts_per_page' => 12,
						)
					);
					?>
				<?php else : ?>
					<section
						class="archive-grid page-not-found-grid<?php echo $is_mixed ? ' page-not-found-grid--mixed' : ''; ?>"
						aria-label="<?php esc_attr_e( 'Posts sugeridos', 'aptox' ); ?>"
					>
						<?php foreach ( $posts as $post ) : ?>
							<?php $render_card( $post ); ?>
						<?php endforeach; ?>
					</section>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</section>

<?php if ( $is_loja_category ) : ?>
	<?php get_template_part( 'components/filter-nav/filter-nav-loja' ); ?>
<?php endif; ?>
