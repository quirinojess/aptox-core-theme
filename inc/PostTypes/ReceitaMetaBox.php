<?php
/**
 * Admin meta box for receitas recipe code (WP Recipe Maker ID).
 *
 * @package Aptox
 */

namespace Aptox\PostTypes;

class ReceitaMetaBox {
	/**
	 * Meta key shared with legacy ACF field storage.
	 *
	 * @var string
	 */
	private const META_KEY = 'codigo_receita';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_receitas', array( $this, 'save_meta' ), 10, 2 );
	}

	/**
	 * Register the recipe code meta box.
	 *
	 * @return void
	 */
	public function register_meta_box() {
		add_meta_box(
			'aptox_codigo_receita',
			'Código receita',
			array( $this, 'render_meta_box' ),
			'receitas',
			'side',
			'default'
		);
	}

	/**
	 * Render the recipe code field.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'aptox_codigo_receita', 'aptox_codigo_receita_nonce' );

		$value = get_post_meta( $post->ID, self::META_KEY, true );
		?>
		<p>
			<label for="codigo_receita">
				<strong><?php esc_html_e( 'Código receita', 'aptox' ); ?></strong>
			</label>
		</p>
		<p>
			<input
				type="text"
				inputmode="numeric"
				pattern="[0-9]*"
				id="codigo_receita"
				name="codigo_receita"
				value="<?php echo esc_attr( (string) $value ); ?>"
				class="widefat"
				placeholder="<?php esc_attr_e( 'Ex.: 123', 'aptox' ); ?>"
			>
		</p>
		<p class="description">
			<?php esc_html_e( 'ID da receita no WP Recipe Maker usado para exibir o card no post.', 'aptox' ); ?>
		</p>
		<?php
	}

	/**
	 * Persist the recipe code meta value.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		unset( $post );

		if (
			! isset( $_POST['aptox_codigo_receita_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( (string) $_POST['aptox_codigo_receita_nonce'] ) ),
				'aptox_codigo_receita'
			)
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['codigo_receita'] ) ) {
			return;
		}

		$raw_value = wp_unslash( (string) $_POST['codigo_receita'] );
		$value     = absint( $raw_value );

		if ( '' === trim( $raw_value ) || 0 === $value ) {
			delete_post_meta( $post_id, self::META_KEY );
			return;
		}

		update_post_meta( $post_id, self::META_KEY, $value );
	}
}
