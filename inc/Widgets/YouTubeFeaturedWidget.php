<?php
/**
 * Widget: featured YouTube video for the home section.
 *
 * @package Aptox
 */

namespace Aptox\Widgets;

use Aptox\Services\YouTubeService;
use WP_Widget;

class YouTubeFeaturedWidget extends WP_Widget {
	/**
	 * Sidebar ID where this widget is configured.
	 */
	public const SIDEBAR_ID = 'youtube-home-featured';

	/**
	 * Register widget cache hooks.
	 *
	 * @return void
	 */
	public static function register_hooks() {
		add_action( 'update_option_widget_' . self::get_option_name(), array( self::class, 'clear_feed_cache' ) );
		add_action( 'update_option_sidebars_widgets', array( self::class, 'clear_feed_cache' ) );
	}

	/**
	 * Widget option key suffix.
	 *
	 * @return string
	 */
	private static function get_option_name() {
		return 'aptox_youtube_featured';
	}

	/**
	 * Clear cached YouTube section markup.
	 *
	 * @return void
	 */
	public static function clear_feed_cache() {
		YouTubeService::clear_feed_cache();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			self::get_option_name(),
			__( 'Vídeo YouTube em destaque', 'aptox' ),
			array(
				'description' => __( 'Escolhe o vídeo exibido em destaque na seção YouTube da home.', 'aptox' ),
			)
		);
	}

	/**
	 * Get the configured featured video URL from the home sidebar widget.
	 *
	 * @return string
	 */
	public static function get_configured_video_url() {
		$sidebars_widgets = wp_get_sidebars_widgets();

		if (
			! is_array( $sidebars_widgets )
			|| empty( $sidebars_widgets[ self::SIDEBAR_ID ] )
			|| ! is_array( $sidebars_widgets[ self::SIDEBAR_ID ] )
		) {
			return '';
		}

		$instances = get_option( 'widget_' . self::get_option_name(), array() );

		if ( ! is_array( $instances ) ) {
			return '';
		}

		foreach ( $sidebars_widgets[ self::SIDEBAR_ID ] as $widget_id ) {
			if ( ! is_string( $widget_id ) || false === strpos( $widget_id, self::get_option_name() . '-' ) ) {
				continue;
			}

			$instance_number = (int) substr( $widget_id, strrpos( $widget_id, '-' ) + 1 );

			if ( $instance_number <= 0 || empty( $instances[ $instance_number ] ) ) {
				continue;
			}

			$instance = $instances[ $instance_number ];

			if ( ! is_array( $instance ) || empty( $instance['video_url'] ) ) {
				continue;
			}

			return esc_url_raw( (string) $instance['video_url'] );
		}

		return '';
	}

	/**
	 * Front-end output is handled by the YouTube feed component.
	 *
	 * @param array<string, mixed> $args     Widget arguments.
	 * @param array<string, mixed> $instance Saved instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		unset( $args, $instance );
	}

	/**
	 * Render widget settings form.
	 *
	 * @param array<string, mixed> $instance Saved instance.
	 * @return void
	 */
	public function form( $instance ) {
		$video_url = ! empty( $instance['video_url'] ) ? (string) $instance['video_url'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'video_url' ) ); ?>">
				<?php esc_html_e( 'Link do vídeo', 'aptox' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'video_url' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'video_url' ) ); ?>"
				type="url"
				value="<?php echo esc_attr( $video_url ); ?>"
				placeholder="https://www.youtube.com/watch?v=..."
			>
		</p>
		<p class="description">
			<?php esc_html_e( 'Cole a URL do vídeo longo que deve aparecer em destaque na home.', 'aptox' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize and save widget settings.
	 *
	 * @param array<string, mixed> $new_instance New instance.
	 * @param array<string, mixed> $old_instance Old instance.
	 * @return array<string, mixed>
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );

		$video_url = ! empty( $new_instance['video_url'] )
			? esc_url_raw( (string) $new_instance['video_url'] )
			: '';

		if ( $video_url && ! YouTubeService::parse_video_id( $video_url ) ) {
			$video_url = '';
		}

		self::clear_feed_cache();

		return array(
			'video_url' => $video_url,
		);
	}
}
