<?php
/**
 * About the Author
 *
 * @context Single Post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_type        = get_post_type();
$is_editorial_bio = in_array( $post_type, array( 'casas', 'celebracoes' ), true );
$section_class    = $is_editorial_bio ? 'about-author about-author--editorial' : 'about-author';
?>

<section
	class="<?php echo esc_attr( $section_class ); ?>"
	aria-labelledby="about-author-title"
>
	<?php if ( $is_editorial_bio ) : ?>
		<div class="about-author-intro">
			<h4 id="about-author-title" class="about-author-title">
				<?php echo esc_html( aptox_hand_text( __( 'Olá, sou jess', 'aptox' ), false ) ); ?>
			</h4>

			<figure class="about-author-avatar">
				<?php
				echo get_avatar(
					get_the_author_meta( 'ID' ),
					120,
					'',
					esc_attr( get_the_author_meta( 'display_name' ) )
				);
				?>
			</figure>
		</div>

		<div class="about-author-bio">
			<p>
				<?php
				esc_html_e(
					'Escrevo diretamente da cidade de Curitiba, Brasil. Sou apaixonada por decoração, culinária e uma vida estilo "feito a mão". Gosto de escrever sobre tudo que me inspira e acredito em uma vida feita com mais amor.',
					'aptox'
				);
				?>
			</p>
		</div>
	<?php else : ?>
		<figure class="about-author-avatar">
			<?php
			echo get_avatar(
				get_the_author_meta( 'ID' ),
				120,
				'',
				esc_attr( get_the_author_meta( 'display_name' ) )
			);
			?>
		</figure>

		<div class="about-author-content">
			<h4 id="about-author-title" class="about-author-title">
				<span class="about-author-label"><?php esc_html_e( 'ESCRITO POR:', 'aptox' ); ?></span>
				<?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?>
			</h4>

			<div class="about-author-bio">
				<?php echo wp_kses_post( wpautop( get_the_author_meta( 'description' ) ) ); ?>
			</div>
		</div>
	<?php endif; ?>
</section>
