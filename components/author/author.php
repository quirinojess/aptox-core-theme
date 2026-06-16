<?php
/**
 * About the Author
 *
 * @context Single Post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = wp_parse_args(
	$args ?? array(),
	array(
		'layout' => 'auto',
	)
);

$layout = $args['layout'];

if ( 'auto' === $layout ) {
	$layout = 'receitas' === get_post_type() ? 'horizontal' : 'vertical';
}

$is_horizontal      = 'horizontal' === $layout;
$section_class      = $is_horizontal ? 'about-author about-author--horizontal' : 'about-author about-author--vertical';
$author_title       = aptox_hand_text( __( 'Olá, sou jess', 'aptox' ), false );
$author_bio         = __(
	'Escrevo diretamente da cidade de Curitiba, Brasil. Sou apaixonada por decoração, culinária e uma vida estilo "feito a mão". Gosto de escrever sobre tudo que me inspira e acredito em uma vida feita com mais amor.',
	'aptox'
);
$author_avatar_html = get_avatar(
	get_the_author_meta( 'ID' ),
	120,
	'',
	esc_attr( get_the_author_meta( 'display_name' ) )
);
?>

<section
	class="<?php echo esc_attr( $section_class ); ?>"
	aria-labelledby="about-author-title"
>
	<?php if ( $is_horizontal ) : ?>
		<figure class="about-author-avatar">
			<?php echo $author_avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</figure>

		<div class="about-author-body">
			<h4 id="about-author-title" class="about-author-title">
				<?php echo esc_html( $author_title ); ?>
			</h4>

			<div class="about-author-bio">
				<p><?php echo esc_html( $author_bio ); ?></p>
			</div>
		</div>
	<?php else : ?>
		<div class="about-author-head">
			<figure class="about-author-avatar">
				<?php echo $author_avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</figure>

			<h4 id="about-author-title" class="about-author-title">
				<?php echo esc_html( $author_title ); ?>
			</h4>
		</div>

		<div class="about-author-bio">
			<p><?php echo esc_html( $author_bio ); ?></p>
		</div>
	<?php endif; ?>
</section>
