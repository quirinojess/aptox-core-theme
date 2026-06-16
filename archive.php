<?php
/**
 * Archive template (global fallback)
 *
 * @package Aptox
 */

get_header();
?>
<?php
$home_url = home_url('/');

$post_type_obj = null;
$primary_term  = null;

if ( have_posts() ) {
  the_post();

  $post_type = get_post_type();
  $post_type_obj = get_post_type_object( $post_type );

  $taxonomies = get_object_taxonomies( $post_type, 'objects' );

  if ( ! empty( $taxonomies ) ) {
    foreach ( $taxonomies as $tax ) {
      if ( $tax->public && $tax->hierarchical ) {
        $terms = get_the_terms( get_the_ID(), $tax->name );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
          $primary_term = $terms[0];
          break;
        }
      }
    }
  }

  rewind_posts();
}
?>

<section class="hero-container">

  <nav class="taxonomy-breadcrumb" aria-label="Breadcrumb">
    <a href="<?php echo esc_url( $home_url ); ?>">Home</a>

    <?php if ( $post_type_obj ) : ?>
      <span>›</span>
      <a href="<?php echo esc_url( get_post_type_archive_link( $post_type_obj->name ) ); ?>">
        <?php echo esc_html( $post_type_obj->labels->name ); ?>
      </a>
    <?php endif; ?>

    <?php if ( $primary_term ) : ?>
      <span>›</span>
      <span><?php echo esc_html( $primary_term->name ); ?></span>
    <?php endif; ?>
  </nav>

  <h1 class="taxonomy-title">
    <?php the_archive_title(); ?>
  </h1>

  <?php if ( get_the_archive_description() ) : ?>
    <div class="taxonomy-description">
      <?php the_archive_description(); ?>
    </div>
  <?php endif; ?>

</section>


<main id="primary" class="site-main container">
  <?php
  $paged     = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
  $max_pages = (int) $wp_query->max_num_pages;
  ?>

  <?php if ( have_posts() ) : ?>



    <section
      class="archive-grid"
      aria-labelledby="archive-title"
    >

      <?php while ( have_posts() ) : the_post(); ?>

        <article <?php post_class( 'archive-card' ); ?>>

          <a
            href="<?php the_permalink(); ?>"
            class="archive-thumb"
            aria-hidden="true"
            tabindex="-1"
          >
            <?php if ( has_post_thumbnail() ) : ?>
              <figure class="archive-image">
                <?php the_post_thumbnail( 'aptox-card' ); ?>
              </figure>
            <?php endif; ?>
          </a>

          <h3 class="archive-title">
            <a href="<?php the_permalink(); ?>">
              <?php the_title(); ?>
            </a>
          </h3>

        </article>

      <?php endwhile; ?>

    </section>

    <?php if ( $max_pages > $paged ) : ?>
      <?php
      aptox_render_archive_load_more(
        array(
          'paged'     => $paged,
          'max_pages' => $max_pages,
          'next_url'  => get_pagenum_link( $paged + 1 ),
        )
      );
      ?>
    <?php endif; ?>

  <?php else : ?>

    <section class="no-results not-found">
      <h1><?php esc_html_e( 'Nada encontrado', 'seu-tema' ); ?></h1>
      <p><?php esc_html_e( 'Não há conteúdos para este arquivo.', 'seu-tema' ); ?></p>
    </section>

  <?php endif; ?>

</main>

<?php get_footer(); ?>
