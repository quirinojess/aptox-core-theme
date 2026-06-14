<?php
/**
 * Main Header Navigation
 * Theme: Aptox
 */

$season = aptox_get_season_context();
?>

<header class="site-header">

  <div class="menu">
    <div class="menu-inner">

      <nav
        class="categories"
        aria-label="Categorias principais"
      >
        <a href="<?php echo esc_url( home_url( '/em-casa' ) ); ?>">Casa</a>
        <a href="<?php echo esc_url( home_url( '/na-cozinha' ) ); ?>">Receitas</a>
        <a href="<?php echo esc_url( home_url( '/celebrando' ) ); ?>">Celebre</a>
      </nav>

      <div class="logo">
        <a
          href="<?php echo esc_url( home_url( '/' ) ); ?>"
          aria-label="Página inicial Aptox"
        >
          <img
            src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/logo.svg' ); ?>"
            alt="Aptox"
          >
        </a>
      </div>

      <div class="sazonal-section">

        <button
          class="menu-button"
          id="openSeason"
          type="button"
          aria-haspopup="dialog"
          aria-controls="modal-season"
        >
          <img
            src="<?php echo esc_url( aptox_season_icon( $season['icon'] ) ); ?>"
            alt=""
            aria-hidden="true"
          >
          <span><?php echo esc_html( $season['label'] ); ?></span>
        </button>

        <button
          class="menu-button"
          id="openSearch"
          type="button"
          aria-haspopup="dialog"
          aria-controls="modal-search"
        >
          <img
            src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/search.svg' ); ?>"
            alt=""
            aria-hidden="true"
          >
          <span>Busca</span>
        </button>

      </div>

    </div>
  </div>

</header>

<?php get_template_part( 'components/modal-search/modal-search' ); ?>
<?php get_template_part( 'components/modal-season/modal-season' ); ?>

<nav
  id="menu-mob"
  class="menu-mobile"
  aria-label="Menu mobile"
>
  <a href="<?php echo esc_url( home_url( '/em-casa' ) ); ?>">Casa</a>
   <a href="<?php echo esc_url( home_url( '/na-cozinha' ) ); ?>">Receitas</a>
  <a href="<?php echo esc_url( home_url( '/celebrando' ) ); ?>">Celebre</a>
</nav>
