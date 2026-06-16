<?php
/**
 * Single template for Loja products.
 */

get_header();

$link_compra = get_post_meta( get_the_ID(), 'link_compra', true );
?>

<main>
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<section class="container">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'loja-product' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="loja-product__image">
							<?php the_post_thumbnail( 'large' ); ?>
						</figure>
					<?php endif; ?>

					<h1 class="loja-product__title"><?php the_title(); ?></h1>

					<?php if ( has_excerpt() ) : ?>
						<div class="loja-product__excerpt">
							<?php the_excerpt(); ?>
						</div>
					<?php endif; ?>

					<?php if ( get_the_content() ) : ?>
						<div class="loja-product__content">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>

					<?php if ( $link_compra ) : ?>
						<p class="loja-product__cta">
							<a
								href="<?php echo esc_url( $link_compra ); ?>"
								class="loja-product__buy-link"
								target="_blank"
								rel="noopener noreferrer sponsored nofollow"
							>
								Comprar
							</a>
						</p>
					<?php endif; ?>
				</article>
			</section>
		<?php endwhile; ?>
	<?php endif; ?>

	<?php get_template_part( 'components/post-share-stack/post-share-stack' ); ?>
	<?php get_template_part( 'components/related-posts/related-posts' ); ?>
	<?php get_template_part( 'components/post-taxonomies/post-taxonomies' ); ?>
	<?php get_template_part( 'components/post-nav/post-nav' ); ?>
</main>

<?php get_footer(); ?>
