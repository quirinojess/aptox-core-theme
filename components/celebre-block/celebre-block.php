<?php
/**
 * Component: Celebre festivity block
 *
 * @context Archive Celebracoes / Page Celebration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block = isset( $args['block'] ) && is_array( $args['block'] ) ? $args['block'] : array();

if ( empty( $block['posts'] ) || empty( $block['label'] ) ) {
	return;
}

$label        = aptox_hand_text( sanitize_text_field( $block['label'] ) );
$icon         = ! empty( $block['icon'] ) ? sanitize_file_name( $block['icon'] ) : '';
$term_url     = ! empty( $block['term_url'] ) ? esc_url( $block['term_url'] ) : '';
$posts        = $block['posts'];
$post_count   = count( $posts );
$use_carousel = $post_count > 1;
$section_id   = 'celebre-block-' . sanitize_title( $label );
$layout_class = $use_carousel ? 'celebre-block-layout--carousel' : 'celebre-block-layout--flat';
$icon_base    = get_template_directory_uri() . '/assets/icons/category/';

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

$render_title = static function () use ( $section_id, $label, $icon, $icon_base, $term_url ) {
	?>
	<header class="celebre-block-header">
		<?php if ( $icon ) : ?>
			<figure class="celebre-block-icon" aria-hidden="true">
				<img
					src="<?php echo esc_url( $icon_base . $icon ); ?>"
					alt=""
					loading="lazy"
				>
			</figure>
		<?php endif; ?>

		<h2 id="<?php echo esc_attr( $section_id ); ?>" class="celebre-block-title">
			<span class="celebre-block-title-display"><?php esc_html_e( 'celebre', 'aptox' ); ?></span>
			<span class="celebre-block-title-hand"><?php echo esc_html( $label ); ?></span>
		</h2>

		<?php if ( $term_url ) : ?>
			<a class="celebre-block-link" href="<?php echo esc_url( $term_url ); ?>">
				<?php esc_html_e( 'ver todos', 'aptox' ); ?>
			</a>
		<?php endif; ?>
	</header>
	<?php
};
?>

<section
	class="celebre-block<?php echo $use_carousel ? ' celebre-block--carousel' : ''; ?>"
	aria-labelledby="<?php echo esc_attr( $section_id ); ?>"
>
	<div class="celebre-block-inner">
		<?php if ( $use_carousel ) : ?>
		<div class="celebre-block-layout <?php echo esc_attr( $layout_class ); ?>">
			<?php $render_title(); ?>

			<div class="celebre-block-content">
				<div class="celebre-block-carousel">
					<div class="celebre-block-carousel__controls">
						<button
							type="button"
							class="celebre-block-nav celebre-block-nav--prev"
							aria-label="<?php esc_attr_e( 'Ver posts anteriores', 'aptox' ); ?>"
							disabled
							hidden
						>
							<span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
						</button>
						<button
							type="button"
							class="celebre-block-nav celebre-block-nav--next"
							aria-label="<?php esc_attr_e( 'Ver próximos posts', 'aptox' ); ?>"
							hidden
						>
							<span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
						</button>
					</div>

					<div class="celebre-block-track">
						<?php foreach ( $posts as $post ) : ?>
							<?php $render_card( $post ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php else : ?>
		<div class="celebre-block-layout <?php echo esc_attr( $layout_class ); ?>">
			<?php $render_title(); ?>
			<?php foreach ( $posts as $post ) : ?>
				<?php $render_card( $post ); ?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
