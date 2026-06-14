<?php
/**
 * Home Filter Navigation
 *
 * @context Archive Home 
 */
?>

<?php
$season = function_exists( 'aptox_get_season_context' )
  ? aptox_get_season_context()
  : array( 'slug' => '' );

$house_taxonomy = taxonomy_exists( 'casa_categoria' ) ? 'casa_categoria' : 'casa';
$home_archive   = home_url( '/casas/' );

$term_url = static function ( $slug ) use ( $house_taxonomy, $home_archive ) {
  $term = get_term_by( 'slug', $slug, $house_taxonomy );
  if ( $term && ! is_wp_error( $term ) ) {
    $url = get_term_link( $term );
    if ( ! is_wp_error( $url ) ) {
      return $url;
    }
  }

  if ( 'casa' === $house_taxonomy ) {
    return home_url( '/' . $house_taxonomy . '/' . $slug . '/' );
  }

  return trailingslashit( $home_archive ) . 'categoria/' . $slug . '/';
};

$lares_url       = $term_url( 'lares-que-amamos' );
$reforma_url     = $term_url( 'reforma' );
$decoracao_url   = $term_url( 'decoracao' );
$jardinagem_url  = $term_url( 'jardinagem' );
$organizacao_url = $term_url( 'organizacao' );
$diy_url         = $term_url( 'faca-voce-mesmo' );
$planejar_url    = $term_url( 'planejando-um-lar' );

if ( ! empty( $season['slug'] ) ) {
  $season_tag_slug = 'decoracao-de-' . sanitize_title( $season['slug'] );
  $decoracao_url = add_query_arg(
    'tag',
    $season_tag_slug,
    $decoracao_url
  );
}
?>



<nav class="filter-nav" aria-label="Filtros da categoria Casa">
  <ul class="filter-list">

    <li class="filter-item">
      <a href="<?php echo esc_url( $lares_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-home1.svg' ); ?>"
          alt="Lares"
        >
        <span>Lares</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $reforma_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-home2.svg' ); ?>"
          alt="Reforma"
        >
        <span>Reforma</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $decoracao_url ); ?>">
        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-home3.svg' ); ?>" alt="Decoração">
        <span>Decoração</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $jardinagem_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-home4.svg' ); ?>"
          alt="Jardinagem"
        >
        <span>Jardinagem</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $organizacao_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-home5.svg' ); ?>"
          alt="Organização"
        >
        <span>Organização</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $diy_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-home6.svg' ); ?>"
          alt="Faça você mesmo"
        >
        <span>Faça você mesmo</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $planejar_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-home7.svg' ); ?>"
          alt="Planejamento"
        >
        <span>Planejando um lar</span>
      </a>
    </li>

  </ul>
</nav>
