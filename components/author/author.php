<?php
/**
 * About the Author
 *
 * @context Single Post
 */
?>

<section
  class="about-author"
  aria-labelledby="about-author-title"
>

  <figure class="about-author-avatar">
    <?php
      echo get_avatar(
        get_the_author_meta( 'ID' ),
        120,
        '',
        esc_attr( get_the_author_meta( 'display_name' ) )
      );
    ?>
  </figure>

  <div class="about-author-content">

    <h4
      id="about-author-title"
      class="about-author-title"
    >
      <span class="about-author-label">ESCRITO POR:</span>
      <?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?>
    </h4>

    <div class="about-author-bio">
      <?php echo wp_kses_post( wpautop( get_the_author_meta( 'description' ) ) ); ?>
    </div>

  </div>

</section>
