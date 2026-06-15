<?php
/**
 * Component: Page Sobre timeline + video
 *
 * @context Page Sobre
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_id = 'ynRYaryKFGM';

$milestones = array(
	array(
		'datetime'  => '2016-05-21',
		'day_month' => '21 de maio',
		'year'      => '2016',
		'text'      => __( 'Primeira foto no Instagram', 'aptox' ),
	),
	array(
		'datetime'  => '2018-09-11',
		'day_month' => '11 de setembro',
		'year'      => '2018',
		'text'      => __( 'Criação do blog e Pinterest', 'aptox' ),
	),
	array(
		'datetime'  => '2025-05-29',
		'day_month' => '29 de maio',
		'year'      => '2025',
		'text'      => __( 'Criação do TikTok', 'aptox' ),
	),
	array(
		'datetime'  => '2025-11-14',
		'day_month' => '14 de novembro',
		'year'      => '2025',
		'text'      => __( 'Canal no YouTube', 'aptox' ),
	),
);
?>

<section
	class="page-sobre-timeline"
	aria-labelledby="page-sobre-timeline-title"
>
	<div class="page-sobre-timeline-inner">
		<div class="page-sobre-timeline-content">
			<ol class="page-sobre-timeline-list">
				<?php foreach ( $milestones as $milestone ) : ?>
					<li class="page-sobre-timeline-item">
						<span class="page-sobre-timeline-year" aria-hidden="true">
							<?php echo esc_html( $milestone['year'] ); ?>
						</span>

						<div class="page-sobre-timeline-track" aria-hidden="true">
							<span class="page-sobre-timeline-dot"></span>
						</div>

						<div class="page-sobre-timeline-body">
							<time
								class="page-sobre-timeline-day"
								datetime="<?php echo esc_attr( $milestone['datetime'] ); ?>"
							>
								<?php echo esc_html( $milestone['day_month'] ); ?>
							</time>

							<p class="page-sobre-timeline-text">
								<?php echo esc_html( $milestone['text'] ); ?>
							</p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>

		<div class="page-sobre-timeline-video-header">
			<h2 id="page-sobre-timeline-title" class="page-sobre-timeline-title">
				<?php esc_html_e( 'O blog já tem 10 anos', 'aptox' ); ?>
			</h2>

			<p class="page-sobre-timeline-description">
				<?php esc_html_e( 'Assista o vídeo para uma retrospectiva dos últimos 10 anos desse projeto', 'aptox' ); ?>
			</p>
		</div>

		<div class="page-sobre-timeline-video__embed">
				<iframe
					src="<?php echo esc_url( 'https://www.youtube.com/embed/' . $video_id ); ?>"
					title="<?php esc_attr_e( 'Vídeo do canal Aptox', 'aptox' ); ?>"
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
					referrerpolicy="strict-origin-when-cross-origin"
					allowfullscreen
					loading="lazy"
				></iframe>
		</div>
	</div>
</section>
