<?php
/**
 * Post summary block for sidebar.
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

$excerpt = trim( (string) get_post_field( 'post_excerpt', $post_id ) );
$topics  = aptox_get_post_h2_topics( (string) get_post_field( 'post_content', $post_id ) );

if ( '' === $excerpt && empty( $topics ) ) {
	return;
}
?>

<section
	class="post-side-summary"
	aria-labelledby="post-side-summary-title"
>
	<header class="post-side-summary__header">
		<div class="post-side-summary__header-inner">
			<figure class="post-side-summary__icon" aria-hidden="true">
				<img
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-scroll.svg' ); ?>"
					alt=""
					loading="lazy"
					width="36"
					height="36"
				>
			</figure>

			<h3 id="post-side-summary-title" class="post-side-summary__title">
				<?php esc_html_e( 'Resumo desse post', 'aptox' ); ?>
			</h3>
		</div>
	</header>

	<?php if ( '' !== $excerpt ) : ?>
		<div class="post-side-summary__excerpt">
			<p><?php echo esc_html( $excerpt ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $topics ) ) : ?>
		<ul class="post-side-summary__topics">
			<?php foreach ( $topics as $index => $topic ) : ?>
				<li>
					<span class="post-side-summary__topic-number">
						<?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?>
					</span>

					<div class="post-side-summary__topic-body">
						<a
							class="post-side-summary__topic-label"
							href="<?php echo esc_url( '#' . $topic['id'] ); ?>"
						>
							<?php echo esc_html( $topic['text'] ); ?>
						</a>

						<?php if ( ! empty( $topic['summary'] ) ) : ?>
							<p class="post-side-summary__topic-summary">
								<?php echo esc_html( $topic['summary'] ); ?>
							</p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
