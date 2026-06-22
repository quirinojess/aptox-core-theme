<?php
/**
 * Component: Casa DIY marquee strip
 *
 * @context Archive Casas / Page Casa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cache_key   = 'aptox_casa_diy_marquee_v1';
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$house_taxonomy = 'casa_categoria';

foreach ( array( 'casa_categoria', 'casa' ) as $candidate_taxonomy ) {
	if ( ! taxonomy_exists( $candidate_taxonomy ) ) {
		continue;
	}

	$diy_term = get_term_by( 'slug', 'faca-voce-mesmo', $candidate_taxonomy );

	if ( $diy_term && ! is_wp_error( $diy_term ) ) {
		$house_taxonomy = $candidate_taxonomy;
		break;
	}
}

$query = new WP_Query(
	array(
		'post_type'              => 'casas',
		'posts_per_page'         => 12,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'tax_query'              => array(
			array(
				'taxonomy' => $house_taxonomy,
				'field'    => 'slug',
				'terms'    => 'faca-voce-mesmo',
			),
		),
	)
);

if ( ! $query->have_posts() ) {
	return;
}

$posts = $query->posts;

wp_reset_postdata();

ob_start();

$render_track = static function ( $posts_list, $hidden = false ) {
	$hidden_attr = $hidden ? ' aria-hidden="true"' : '';

	echo '<div class="casa-diy-marquee__track"' . $hidden_attr . '>';

	echo '<span class="casa-diy-marquee__title">';
	esc_html_e( 'Faça você mesmo', 'aptox' );
	echo '</span>';

	foreach ( $posts_list as $post ) {
		echo '<span class="casa-diy-marquee__dot" aria-hidden="true"></span>';

		printf(
			'<a class="casa-diy-marquee__link" href="%1$s">%2$s</a>',
			esc_url( get_permalink( $post ) ),
			esc_html( get_the_title( $post ) )
		);
	}

	echo '<span class="casa-diy-marquee__dot" aria-hidden="true"></span>';
	echo '</div>';
};
?>

<section class="casa-diy-marquee" aria-label="<?php esc_attr_e( 'Faça você mesmo', 'aptox' ); ?>">
	<div class="casa-diy-marquee__viewport">
		<div class="casa-diy-marquee__inner">
			<?php
			$render_track( $posts );
			$render_track( $posts, true );
			?>
		</div>
	</div>
</section>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
