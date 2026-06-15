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
  : array(
    'slug'  => 'verao',
    'label' => 'Verão',
  );

$house_taxonomy = taxonomy_exists( 'casa_categoria' ) ? 'casa_categoria' : 'casa';
$home_archive   = home_url( '/casas/' );
$icon_base      = get_template_directory_uri() . '/assets/icons/category/';

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
$decoracao_url   = $term_url( 'decoracao-por-espacos' );
$jardinagem_url  = $term_url( 'jardinagem' );
$organizacao_url = $term_url( 'organizacao' );
$diy_url         = $term_url( 'faca-voce-mesmo' );
$planejar_url    = $term_url( 'planejando-um-lar' );

$season_slug  = sanitize_title( $season['slug'] ?? 'verao' );
$season_label = $season['label'] ?? 'Verão';
$season_icon  = function_exists( 'aptox_filter_home_season_icon' )
  ? aptox_filter_home_season_icon( $season_slug )
  : $icon_base . 'sun-home-decor.png';
$season_url   = add_query_arg(
  'tag',
  'decoracao-de-' . $season_slug,
  $term_url( 'decoracao' )
);
?>



<nav class="filter-nav" aria-label="Filtros da categoria Casa">
  <ul class="filter-list">

    <li class="filter-item">
      <a href="<?php echo esc_url( $season_url ); ?>">
        <img
          src="<?php echo esc_url( $season_icon ); ?>"
          alt="<?php echo esc_attr( $season_label ); ?>"
        >
        <span><?php echo esc_html( $season_label ); ?></span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $decoracao_url ); ?>">
        <img src="<?php echo esc_url( $icon_base . 'decor.png' ); ?>" alt="Decoração">
        <span>Decoração</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $reforma_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'reforma.png' ); ?>"
          alt="Reforma"
        >
        <span>Reforma</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $diy_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'ico-diy.png' ); ?>"
          alt="Faça você mesmo"
        >
        <span>Faça você mesmo</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $organizacao_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'organize.png' ); ?>"
          alt="Organização"
        >
        <span>Organização</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $jardinagem_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'garden.png' ); ?>"
          alt="Jardinagem"
        >
        <span>Jardinagem</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $planejar_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'planner-home.png' ); ?>"
          alt="Planejamento"
        >
        <span>Planejando um lar</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $lares_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'home-loved.png' ); ?>"
          alt="Lares"
        >
        <span>Lares</span>
      </a>
    </li>

  </ul>
</nav>
