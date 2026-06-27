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

$festivity_key = ! empty( $args['festivity_key'] )
	? sanitize_key( $args['festivity_key'] )
	: ( ! empty( $block['key'] ) ? sanitize_key( $block['key'] ) : '' );
$is_active     = ! empty( $args['is_active'] );
$has_filter    = ! empty( $args['has_filter'] );
$label        = aptox_hand_text( sanitize_text_field( $block['label'] ) );
$icon         = ! empty( $block['icon'] ) ? sanitize_file_name( $block['icon'] ) : '';
$term_url     = ! empty( $block['term_url'] ) ? esc_url( $block['term_url'] ) : '';
$posts        = $block['posts'];
$post_count   = count( $posts );
$use_carousel = $post_count > 1 || $has_filter;
$section_id   = 'celebre-block-' . sanitize_title( $label );
$layout_class = $use_carousel ? 'celebre-block-layout--carousel' : 'celebre-block-layout--flat';
$icon_base    = get_template_directory_uri() . '/assets/icons/celebre/ocasioes/';

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
					<?php echo aptox_render_post_thumbnail( $post, 'aptox-card', array( 'loading' => 'lazy' ) ); ?>
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
			<span class="celebre-block-title-display"><?php esc_html_e( 'Celebre', 'aptox' ); ?></span>
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
	class="celebre-block<?php echo $use_carousel ? ' celebre-block--carousel' : ''; ?><?php echo $is_active ? '' : ' celebre-block--hidden'; ?>"
	data-festivity-key="<?php echo esc_attr( $festivity_key ); ?>"
	aria-labelledby="<?php echo esc_attr( $section_id ); ?>"
	<?php echo $is_active ? '' : ' hidden'; ?>
>
	<div class="celebre-block-inner">
		<?php if ( $use_carousel ) : ?>
		<div class="celebre-block-layout <?php echo esc_attr( $layout_class ); ?>">
			<?php $render_title(); ?>

			<div class="celebre-block-content">
				<div class="celebre-block-carousel">
					<div
						class="celebre-block-carousel__controls"
						<?php echo $has_filter ? ' data-celebre-filter-slot' : ''; ?>
					>
						<div class="celebre-block-carousel__nav">
							<button
								type="button"
								class="celebre-block-nav celebre-block-nav--prev"
								aria-label="<?php esc_attr_e( 'Ver posts anteriores', 'aptox' ); ?>"
								disabled
								hidden
							>
								<span class="celebre-block-nav__icon"><?php echo aptox_chevron_icon( 'left' ); ?></span>
							</button>
							<button
								type="button"
								class="celebre-block-nav celebre-block-nav--next"
								aria-label="<?php esc_attr_e( 'Ver próximos posts', 'aptox' ); ?>"
								hidden
							>
								<span class="celebre-block-nav__icon"><?php echo aptox_chevron_icon( 'right' ); ?></span>
							</button>
						</div>
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

			<div class="celebre-block-content celebre-block-content--flat">
				<div class="celebre-block-flat-cards">
					<?php foreach ( $posts as $post ) : ?>
						<?php $render_card( $post ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>
