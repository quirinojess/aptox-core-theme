<?php
/**
 * Component: Season Newsletter Modal
 * Theme: Aptox
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$season     = aptox_get_season_context();
$newsletter = aptox_get_season_newsletter_data();
?>

<div
  id="seasonModal"
  class="modal"
  aria-hidden="true"
>

  <div
    class="modal-content season-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="season-modal-title"
  >

    <header class="season-modal-header">

      <button
        class="close"
        type="button"
        aria-label="Fechar newsletter"
      >
        ×
      </button>

    </header>

    <?php if ( ! empty( $newsletter['image'] ) ) : ?>
      <figure class="season-modal-image">
        <img
          src="<?php echo esc_url( $newsletter['image'] ); ?>"
          alt="<?php echo esc_attr( $newsletter['title'] ); ?>"
          loading="lazy"
        >
      </figure>
    <?php endif; ?>

    <div class="season-modal-body">

      <h2
        id="season-modal-title"
        class="season-modal-title"
      >
        <?php echo esc_html( $newsletter['title'] ); ?>
      </h2>

      <p class="season-modal-description">
        <?php echo esc_html( $newsletter['description'] ); ?>
      </p>

      <div class="newsletter-form-wrapper">
        <?php echo do_shortcode( '[forminator_form id="7369"]' ); ?>
      </div>

    </div>

  </div>

</div>
