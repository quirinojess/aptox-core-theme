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
$icon_base            = get_template_directory_uri() . '/assets/icons/celebre/ocasioes/';

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

<nav class="filter-nav filter-nav--celebre" aria-label="Filtros da categoria Celebrações">
  <ul class="filter-list">

    <li class="filter-item">
      <a href="<?php echo esc_url( $aniversario_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-aniversario.png' ); ?>"
          alt="Aniversário"
        >
        <span>Aniversário</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $casamento_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-casamento.png' ); ?>"
          alt="Casamento"
        >
        <span>Casamento</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $carnaval_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-carnaval.png' ); ?>"
          alt="Carnaval"
        >
        <span>Carnaval</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $pascoa_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-pascoa.png' ); ?>"
          alt="Páscoa"
        >
        <span>Páscoa</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $maes_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-dia-das-maes.png' ); ?>"
          alt="Mães"
        >
        <span>Mães</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $junina_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-festa-junina.png' ); ?>"
          alt="Festa Junina"
        >
        <span>Festa Junina</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $pais_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-dia-dos-pais.png' ); ?>"
          alt="Pais"
        >
        <span>Pais</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $namorados_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-namorados.png' ); ?>"
          alt="Namorados"
        >
        <span>Namorados</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $halloween_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-halloween.png' ); ?>"
          alt="Halloween"
        >
        <span>Halloween</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $muertos_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-dia-de-los-muertos.png' ); ?>"
          alt="Los Muertos"
        >
        <span>Los Muertos</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $natal_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-natal.png' ); ?>"
          alt="Natal"
        >
        <span>Natal</span>
      </a>
    </li>

    <li class="filter-item">
      <a href="<?php echo esc_url( $ano_novo_url ); ?>">
        <img
          src="<?php echo esc_url( $icon_base . 'celebre-ocasiao-ano-novo.png' ); ?>"
          alt="Ano Novo"
        >
        <span>Ano Novo</span>
      </a>
    </li>

  </ul>
</nav>
