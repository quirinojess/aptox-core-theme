<?php
/**
 * Single template for Loja products.
 */

get_header();

$link_compra = get_post_meta( get_the_ID(), 'link_compra', true );
$loja_url    = function_exists( 'aptox_get_loja_archive_url' )
	? aptox_get_loja_archive_url()
	: home_url( '/loja/' );
?>

<main>
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<section class="hero-container loja-product-hero">
				<nav class="taxonomy-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'aptox' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'aptox' ); ?></a>
					<span aria-hidden="true">›</span>
					<a href="<?php echo esc_url( $loja_url ); ?>"><?php esc_html_e( 'Loja', 'aptox' ); ?></a>
					<span aria-hidden="true">›</span>
					<span><?php the_title(); ?></span>
				</nav>

				<h1 class="taxonomy-title loja-product__title"><?php the_title(); ?></h1>
			</section>

			<section class="container">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'loja-product' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="loja-product__image">
							<?php
							the_post_thumbnail(
								'large',
								array(
									'loading' => 'eager',
									'alt'     => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ?: get_the_title(),
								)
							);
							?>
						</figure>
					<?php endif; ?>

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
								<?php esc_html_e( 'Comprar', 'aptox' ); ?>
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
