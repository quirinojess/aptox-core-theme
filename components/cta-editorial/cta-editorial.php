<?php
/**
 * Seasonal Editorial CTA
 *
 * @index
 */

$cta = aptox_get_season_cta_data();
?>

<section
  class="cta-editorial"
  aria-labelledby="cta-editorial-title"
>
  <div class="cta-editorial-inner">

    <header class="cta-content">

      <h2
        id="cta-editorial-title"
        class="cta-title"
      >
        <?php if ( ! empty( $cta['label'] ) ) : ?>
          <span class="cta-label">
            <?php echo esc_html( $cta['label'] ); ?>
          </span>
          <br>
        <?php endif; ?>
        <?php echo esc_html( $cta['title'] ); ?>
      </h2>

      <p class="cta-text">
        <?php echo esc_html( $cta['description'] ); ?>
      </p>

      <div class="cta-actions">
        <a
          href="<?php echo esc_url( $cta['link'] ); ?>"
          class="cta-link"
        >
          VEJA MAIS
        </a>
      </div>

    </header>

    <figure class="cta-image">
      <img
        src="<?php echo esc_url( $cta['image'] ); ?>"
        alt="<?php echo esc_attr( $cta['title'] ); ?>"
        loading="lazy"
      >
    </figure>

  </div>
</section>
