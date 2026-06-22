<?php
/**
 * Post published date for sidebar.
 *
 * @context Single Post (Celebre / Decor)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_the_ID();

if ( ! $post_id ) {
	return;
}

$published_timestamp = get_post_timestamp( $post_id, 'date' );

if ( false === $published_timestamp ) {
	return;
}

$published_label = wp_date( 'j \d\e F \d\e Y', $published_timestamp );
$published_iso   = wp_date( 'c', $published_timestamp );
$date_icon_url   = get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-date.png';
?>

<section
	class="post-side-date"
	aria-labelledby="post-side-date-label"
>
	<div class="post-side-date__row">
		<figure class="post-side-date__icon" aria-hidden="true">
			<img
				src="<?php echo esc_url( $date_icon_url ); ?>"
				alt=""
				loading="lazy"
				width="44"
				height="44"
				decoding="async"
			>
		</figure>

		<div class="post-side-date__content">
			<p id="post-side-date-label" class="post-side-date__label">
				<?php esc_html_e( 'Artigo publicado em', 'aptox' ); ?>
			</p>

			<time
				class="post-side-date__value"
				datetime="<?php echo esc_attr( $published_iso ); ?>"
			>
				<?php echo esc_html( $published_label ); ?>
			</time>
		</div>
	</div>
</section>
