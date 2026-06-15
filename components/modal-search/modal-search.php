<?php
/**
 * Component: Search Modal
 * Theme: Aptox
 */
?>

<div
  id="searchModal"
  class="modal"
  aria-hidden="true"
>

  <div
    class="modal-content"
    role="dialog"
    aria-modal="true"
    aria-labelledby="search-modal-title"
  >

    <header class="modal-header">

   

      <button
        class="close"
        type="button"
        aria-label="Fechar busca"
      >
        ×
      </button>

    </header>

    <form
      class="search-form"
      action="<?php echo esc_url( home_url( '/' ) ); ?>"
      method="get"
      role="search"
    >

      <div class="search-field">

        <label for="search-input">
          <h5 id="search-modal-title">Digite aqui o que procura</h5>
        </label>

        <input
          type="search"
          name="s"
          id="search-input"
          autocomplete="off"
          required
        />

      </div>

      <fieldset class="search-filters">

        <legend class="screen-reader-text">
          Filtrar por tipo de conteúdo
        </legend>

        <span class="search-filters-label">
          Filtre por
        </span>

        <label class="radio">
          <input
            type="radio"
            name="post_type"
            value=""
            checked
          >
          <span class="radio-mark"></span>
          <span class="radio-text">Todos</span>
        </label>

        <label class="radio">
          <input
            type="radio"
            name="post_type"
            value="casas"
          >
          <span class="radio-mark"></span>
          <span class="radio-text">Casa</span>
        </label>

        <label class="radio">
          <input
            type="radio"
            name="post_type"
            value="receitas"
          >
          <span class="radio-mark"></span>
          <span class="radio-text">Receitas</span>
        </label>

        <label class="radio">
          <input
            type="radio"
            name="post_type"
            value="celebracoes"
          >
          <span class="radio-mark"></span>
          <span class="radio-text">Celebre</span>
        </label>

      </fieldset>

    </form>

  </div>

</div>
