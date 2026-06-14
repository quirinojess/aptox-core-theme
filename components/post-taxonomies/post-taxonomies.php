<?php
/**
 * Component: Post Taxonomies
 * Theme: Aptox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_singular() ) {
	return;
}

$post_id = get_the_ID();

if ( ! $post_id ) {
	return;
}

$category_taxonomy = aptox_detect_post_taxonomy( $post_id );
$categories        = array();
$tags              = array();

if ( $category_taxonomy ) {
	$category_terms = get_the_terms( $post_id, $category_taxonomy );

	if ( ! empty( $category_terms ) && ! is_wp_error( $category_terms ) ) {
		$categories = $category_terms;
	}
}

$tag_terms = get_the_terms( $post_id, 'post_tag' );

if ( ! empty( $tag_terms ) && ! is_wp_error( $tag_terms ) ) {
	$tags = $tag_terms;
}

if ( empty( $categories ) && empty( $tags ) ) {
	return;
}
?>

<section
	class="post-taxonomies"
	aria-label="<?php esc_attr_e( 'Categorias e tags do post', 'aptox' ); ?>"
>
	<div class="post-taxonomies-inner">
		<?php if ( ! empty( $categories ) ) : ?>
			<div class="post-taxonomies-group post-taxonomies-group--categories">
				<p class="post-taxonomies-label">
					<?php esc_html_e( 'Categorias:', 'aptox' ); ?>
				</p>

				<p class="post-taxonomies-list">
					<?php
					$category_links = array();

					foreach ( $categories as $term ) {
						$category_links[] = sprintf(
							'<a class="post-taxonomies-link" href="%1$s">%2$s</a>',
							esc_url( get_term_link( $term ) ),
							esc_html( $term->name )
						);
					}

					echo wp_kses_post( implode( '<span class="post-taxonomies-sep">, </span>', $category_links ) );
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $tags ) ) : ?>
			<div class="post-taxonomies-group post-taxonomies-group--tags">
				<p class="post-taxonomies-label">
					<?php esc_html_e( 'Tags:', 'aptox' ); ?>
				</p>

				<p class="post-taxonomies-list">
					<?php
					$tag_links = array();

					foreach ( $tags as $term ) {
						$tag_links[] = sprintf(
							'<a class="post-taxonomies-link" href="%1$s">%2$s</a>',
							esc_url( get_term_link( $term ) ),
							esc_html( $term->name )
						);
					}

					echo wp_kses_post( implode( '<span class="post-taxonomies-sep">, </span>', $tag_links ) );
					?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</section>
