<?php
/**
 * Search results template.
 */

get_header();

$search_term = get_search_query();
$post_type   = get_query_var( 'post_type' );

$labels = array(
	'casas'    => 'Casas',
	'receitas' => 'Receitas',
	'celebracoes' => 'Celebre',
);

$context = isset( $labels[ $post_type ] ) ? $labels[ $post_type ] : 'Tudo';
?>

<section class="hero-container">
	<div class="search-term">
		Você buscou por <strong><?php echo esc_html( $context ); ?></strong> com
		<strong><?php echo esc_html( $search_term ); ?></strong>
	</div>
</section>

<main class="container">
	<?php if ( have_posts() ) : ?>

		<section class="archive-grid">
			<?php while ( have_posts() ) : the_post(); ?>

				<article class="archive-card">
					<a href="<?php the_permalink(); ?>" class="archive-thumb">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'large' ); ?>
						<?php endif; ?>
					</a>

					<h3 class="recipe-title">
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</h3>
				</article>

			<?php endwhile; ?>
		</section>

		<div id="navigation">
			<?php if ( function_exists( 'load_more_button' ) ) : ?>
				<?php load_more_button(); ?>
			<?php endif; ?>
		</div>

	<?php else : ?>

		<p class="center">Nada foi encontrado com esse contexto.</p>

	<?php endif; ?>
</main>

<?php get_footer(); ?>
