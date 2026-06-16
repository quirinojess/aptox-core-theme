<?php
/**
 * Main Header Navigation
 * Theme: Aptox
 */

$season  = aptox_get_season_context();
$seasons = aptox_get_all_seasons();
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
            src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/brand/ui-brand-logo.svg' ); ?>"
            alt="Aptox"
          >
        </a>
      </div>

      <div class="sazonal-section">

        <div class="season-switcher">
          <button
            class="menu-button season-switcher__trigger"
            type="button"
            aria-haspopup="true"
            aria-expanded="false"
            aria-controls="season-switcher-menu"
          >
            <img
              src="<?php echo esc_url( aptox_season_icon( $season['icon'] ) ); ?>"
              alt=""
              aria-hidden="true"
            >
            <span class="season-switcher__label"><?php echo esc_html( $season['label'] ); ?></span>
          </button>

          <ul
            id="season-switcher-menu"
            class="season-switcher__menu"
            role="menu"
            hidden
          >
            <?php foreach ( $seasons as $season_option ) : ?>
              <li role="none">
                <button
                  type="button"
                  class="season-switcher__option<?php echo ! empty( $season_option['is_active'] ) ? ' is-active' : ''; ?>"
                  role="menuitem"
                  data-season="<?php echo esc_attr( $season_option['slug'] ); ?>"
                >
                  <img
                    src="<?php echo esc_url( aptox_season_icon( $season_option['icon'] ) ); ?>"
                    alt=""
                    aria-hidden="true"
                  >
                  <span><?php echo esc_html( $season_option['label'] ); ?></span>
                </button>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <button
          class="menu-button"
          id="openSearch"
          type="button"
          aria-haspopup="dialog"
          aria-controls="modal-search"
        >
          <img
            src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-search.svg' ); ?>"
            alt=""
            aria-hidden="true"
          >
          <span>Busca</span>
        </button>

        <?php
        $loja_url = function_exists( 'aptox_get_loja_archive_url' )
          ? aptox_get_loja_archive_url()
          : home_url( '/loja/' );
        ?>

        <a
          class="menu-button"
          href="<?php echo esc_url( $loja_url ); ?>"
        >
          <img
            src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ui/actions/ui-action-loja.svg' ); ?>"
            alt=""
            aria-hidden="true"
          >
          <span>Loja</span>
        </a>

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
