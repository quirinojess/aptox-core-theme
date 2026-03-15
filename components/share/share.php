<?php
/**
 * Component: Share Bar
 * Theme: Aptox
 */
?>

<section
  class="share-bar"
  aria-labelledby="share-bar-title"
>

  <div class="share-bar-inner">

    <div class="share-like">

      <button
        class="like-btn"
        type="button"
        data-post-id="<?php echo get_the_ID(); ?>"
        aria-label="Curtir este conteúdo"
      >
        <img
          class="like-icon"
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/favorite-outline.svg' ); ?>"
          alt=""
          aria-hidden="true"
        >

        <span
          class="like-count"
          aria-live="polite"
        >
          <?php echo (int) get_post_meta( get_the_ID(), '_post_likes', true ); ?>
        </span>
      </button>

    </div>

    <p
      id="share-bar-title"
      class="share-text"
    >
      Gostou do conteúdo? <strong>Curta</strong> e espalhe por aí
    </p>

    <nav
      class="share-icons"
      aria-label="Compartilhar este conteúdo"
    >
      <ul>

        <li>
          <a
            href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url( get_permalink() ); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Compartilhar no Facebook"
          >
            <img
              src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/ico-facebook.svg' ); ?>"
              alt=""
              aria-hidden="true"
            >
          </a>
        </li>

        <li>
          <a
            href="https://pinterest.com/pin/create/button/?url=<?php echo esc_url( get_permalink() ); ?>&media=<?php echo esc_url( get_the_post_thumbnail_url() ); ?>&description=<?php echo esc_attr( get_the_title() ); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Salvar no Pinterest"
          >
            <img
              src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/ico-pinterest.svg' ); ?>"
              alt=""
              aria-hidden="true"
            >
          </a>
        </li>

        <li>
          <a
            href="https://wa.me/?text=<?php echo esc_attr( get_the_title() . ' - ' . get_permalink() ); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Compartilhar no WhatsApp"
          >
            <img
              src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/ico-whats.svg' ); ?>"
              alt=""
              aria-hidden="true"
            >
          </a>
        </li>

      </ul>
    </nav>

  </div>

</section>
