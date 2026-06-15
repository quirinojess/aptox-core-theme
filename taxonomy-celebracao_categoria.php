<?php
/**
 * Taxonomy template for Celebracao categories.
 */

get_header();

$term = get_queried_object();
?>

<section class="hero-container">
	<nav class="taxonomy-breadcrumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
		<span>›</span>
		<a href="<?php echo esc_url( home_url( '/celebracoes' ) ); ?>">Celebrações</a>
		<span>›</span>
		<span><?php echo esc_html( $term->name ); ?></span>
	</nav>

	<h1 class="taxonomy-title">
		<?php echo esc_html( $term->name ); ?>
	</h1>

	<?php if ( ! empty( $term->description ) ) : ?>
		<div class="taxonomy-description">
			<?php echo wp_kses_post( wpautop( $term->description ) ); ?>
		</div>
	<?php endif; ?>
</section>

<main class="container">
	<?php if ( have_posts() ) : ?>
		<?php
		$paged     = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$max_pages = (int) $wp_query->max_num_pages;
		?>
		<section class="archive-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article class="archive-card">
					<a href="<?php the_permalink(); ?>" class="archive-thumb">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'large' ); ?>
						<?php endif; ?>
					</a>

					<h3 class="archive-title">
						<a href="<?php the_permalink(); ?>">
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
		<p>Nenhuma celebração encontrada.</p>
	<?php endif; ?>
</main>

<?php get_template_part( 'components/filter-nav/filter-nav-celebration' ); ?>

<?php get_footer(); ?>
