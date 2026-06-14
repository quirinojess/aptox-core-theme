<?php
/**
 * Celebration Filter Navigation
 *
 * @context Archive Celebration
 */
?>

<?php
$celebration_taxonomy = 'celebracao_categoria';
$archive_url          = home_url( '/celebracoes/' );

$term_url = static function ( array $slugs ) use ( $celebration_taxonomy, $archive_url ) {
  foreach ( $slugs as $slug ) {
    $term = get_term_by( 'slug', $slug, $celebration_taxonomy );
    if ( $term && ! is_wp_error( $term ) ) {
      $url = get_term_link( $term );
      if ( ! is_wp_error( $url ) ) {
        return $url;
      }
    }
  }

  $fallback_slug = reset( $slugs );

  return trailingslashit( $archive_url ) . $fallback_slug . '/';
};

$aniversario_url = $term_url( array( 'aniversario', 'aniversarios' ) );
$casamento_url   = $term_url( array( 'casamento', 'casamentos' ) );
$carnaval_url    = $term_url( array( 'carnaval' ) );
$pascoa_url      = $term_url( array( 'pascoa' ) );
$maes_url        = $term_url( array( 'dia-das-maes', 'dias-das-maes' ) );
$junina_url      = $term_url( array( 'festa-junina' ) );
$pais_url        = $term_url( array( 'dia-dos-pais' ) );
$namorados_url   = $term_url( array( 'dia-dos-namorados', 'namorados' ) );
$halloween_url   = $term_url( array( 'halloween' ) );
$muertos_url     = $term_url( array( 'dia-de-los-muertos' ) );
$natal_url       = $term_url( array( 'natal' ) );
$ano_novo_url    = $term_url( array( 'ano-novo' ) );
?>

<nav class="filter-nav" aria-label="Filtros da categoria Celebrações">
  <ul class="filter-list">

    <li class="filter-item">
      <a href="<?php echo esc_url( $aniversario_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration0.svg' ); ?>"
          alt="Aniversário"
        >
        <span>Aniversário</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $casamento_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration11.svg' ); ?>"
          alt="Casamento"
        >
        <span>Casamento</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $carnaval_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration1.svg' ); ?>"
          alt="Carnaval"
        >
        <span>Carnaval</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $pascoa_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration2.svg' ); ?>"
          alt="Páscoa"
        >
        <span>Páscoa</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $maes_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration9.svg' ); ?>"
          alt="Mães"
        >
        <span>Mães</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $junina_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration3.svg' ); ?>"
          alt="Festa Junina"
        >
        <span>Festa Junina</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $pais_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration10.svg' ); ?>"
          alt="Pais"
        >
        <span>Pais</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $namorados_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration4.svg' ); ?>"
          alt="Namorados"
        >
        <span>Namorados</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $halloween_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration5.svg' ); ?>"
          alt="Halloween"
        >
        <span>Halloween</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $muertos_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration6.svg' ); ?>"
          alt="Los Muertos"
        >
        <span>Los Muertos</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $natal_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration7.svg' ); ?>"
          alt="Natal"
        >
        <span>Natal</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $ano_novo_url ); ?>">
        <img
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/category/ico-celebration8.svg' ); ?>"
          alt="Ano Novo"
        >
        <span>Ano Novo</span>
      </a>
    </li>

  </ul>
</nav>
