<?php
/**
 * Admin meta box for Loja affiliate products.
 *
 * @package Aptox
 */

namespace Aptox\PostTypes;

class LojaMetaBox {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_loja', array( $this, 'save_meta' ), 10, 2 );
	}

	/**
	 * Register the purchase link meta box.
	 *
	 * @return void
	 */
	public function register_meta_box() {
		add_meta_box(
			'aptox_loja_compra',
			'Link para compra',
			array( $this, 'render_meta_box' ),
			'loja',
			'normal',
			'high'
		);
	}

	/**
	 * Render the purchase link field.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'aptox_loja_compra', 'aptox_loja_compra_nonce' );

		$link = get_post_meta( $post->ID, 'link_compra', true );
		?>
		<p>
			<label for="link_compra">
				<strong>URL do produto</strong>
			</label>
		</p>
		<p>
			<input
				type="url"
				id="link_compra"
				name="link_compra"
				value="<?php echo esc_attr( $link ); ?>"
				class="widefat"
				placeholder="https://"
			>
		</p>
		<p class="description">
			Link de afiliada para a página de compra do produto.
		</p>
		<?php
	}

	/**
	 * Persist the purchase link meta value.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		unset( $post );

		if (
			! isset( $_POST['aptox_loja_compra_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( (string) $_POST['aptox_loja_compra_nonce'] ) ),
				'aptox_loja_compra'
			)
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['link_compra'] ) ) {
			return;
		}

		update_post_meta(
			$post_id,
			'link_compra',
			esc_url_raw( wp_unslash( (string) $_POST['link_compra'] ) )
		);
	}
}
