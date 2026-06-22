<?php
/**
 * Component: Celebre seasonal festivity blocks
 *
 * @context Archive Celebracoes / Page Celebration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Aptox\Services\CelebreSeasonService;

$cache_key   = 'aptox_celebre_season_v26_' . CelebreSeasonService::get_cache_suffix();
$cached_html = get_transient( $cache_key );

if ( false !== $cached_html ) {
	echo $cached_html;
	return;
}

$blocks = CelebreSeasonService::get_season_blocks();

if ( empty( $blocks ) ) {
	return;
}

$default_key  = CelebreSeasonService::get_default_festivity_key();
$block_keys   = wp_list_pluck( $blocks, 'key' );
$has_filter   = count( $blocks ) > 1;

if ( ! in_array( $default_key, $block_keys, true ) ) {
	$default_key = $block_keys[0];
}

ob_start();
?>

<div
	class="celebre-season<?php echo $has_filter ? ' celebre-season--has-filter' : ''; ?>"
	data-default-festivity="<?php echo esc_attr( $default_key ); ?>"
>
	<div class="celebre-season-panels">
		<?php
		foreach ( $blocks as $block ) {
			$festivity_key = ! empty( $block['key'] ) ? sanitize_key( $block['key'] ) : '';
			$is_active     = $festivity_key === $default_key;

			get_template_part(
				'components/celebre-block/celebre-block',
				null,
				array(
					'block'         => $block,
					'festivity_key' => $festivity_key,
					'is_active'     => $is_active,
					'has_filter'    => $has_filter,
				)
			);
		}
		?>
	</div>

	<?php if ( $has_filter ) : ?>
		<div class="celebre-season-filter-bar">
			<div class="celebre-season-filter-nav">
				<nav
					class="celebre-season-filter"
					aria-label="<?php esc_attr_e( 'Festividades da estação', 'aptox' ); ?>"
				>
					<?php
					$last_block       = end( $blocks );
					$last_festivity_key = ! empty( $last_block['key'] ) ? sanitize_key( $last_block['key'] ) : '';

					foreach ( $blocks as $block ) :
						$festivity_key = ! empty( $block['key'] ) ? sanitize_key( $block['key'] ) : '';
						$is_active     = $festivity_key === $default_key;
						?>
						<button
							type="button"
							class="celebre-season-filter__tab<?php echo $is_active ? ' is-active' : ''; ?>"
							data-festivity-key="<?php echo esc_attr( $festivity_key ); ?>"
							aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( ! empty( $block['filter_label'] ) ? $block['filter_label'] : $block['label'] ); ?>
						</button>
						<?php if ( $festivity_key !== $last_festivity_key ) : ?>
							<span class="celebre-season-filter__sep" aria-hidden="true">·</span>
						<?php endif; ?>
					<?php endforeach; ?>
				</nav>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php
$html = ob_get_clean();

set_transient( $cache_key, $html, HOUR_IN_SECONDS );

echo $html;
