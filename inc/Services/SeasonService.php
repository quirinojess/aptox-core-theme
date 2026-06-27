<?php
/**
 * Seasonal context and content helpers.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class SeasonService {
	public const SEASON_COOKIE_NAME = 'aptox_season_session';
	public const LEGACY_SEASON_COOKIE_NAME = 'aptox_season';
	public const SEASON_CALENDAR_COOKIE_NAME = 'aptox_season_calendar';
	public const SEASON_STORAGE_SYNC_KEY = 'aptox-season-calendar';
	public const SEASON_QUERY_PARAM = 'estacao';

	/**
	 * Get current season context.
	 *
	 * @return array<string, string>
	 */
	public static function get_season_context() {
		return self::get_season_context_by_slug( self::detect_current_season_slug() );
	}

	/**
	 * All selectable season slugs.
	 *
	 * @return array<int, string>
	 */
	public static function get_available_season_slugs() {
		return array( 'verao', 'outono', 'inverno', 'primavera', 'fim-de-ano' );
	}

	/**
	 * Resolve and validate a season slug.
	 *
	 * @param string $slug Raw slug.
	 * @return string|null
	 */
	public static function resolve_season_slug( $slug ) {
		$slug = sanitize_title( (string) $slug );

		return in_array( $slug, self::get_available_season_slugs(), true ) ? $slug : null;
	}

	/**
	 * User-selected season from query string or session cookie.
	 *
	 * @return string|null
	 */
	public static function get_override_season_slug() {
		if ( isset( $_GET[ self::SEASON_QUERY_PARAM ] ) ) {
			$slug = self::resolve_season_slug( wp_unslash( $_GET[ self::SEASON_QUERY_PARAM ] ) );

			if ( null !== $slug ) {
				return $slug;
			}
		}

		if ( isset( $_COOKIE[ self::SEASON_COOKIE_NAME ] ) ) {
			$slug = self::resolve_season_slug( wp_unslash( $_COOKIE[ self::SEASON_COOKIE_NAME ] ) );

			if ( null !== $slug ) {
				return $slug;
			}
		}

		return null;
	}

	/**
	 * Whether the visitor chose a season manually.
	 *
	 * @return bool
	 */
	public static function has_season_override() {
		return null !== self::get_override_season_slug();
	}

	/**
	 * Remove legacy persistent cookie and reset stale session overrides.
	 *
	 * @return void
	 */
	public static function bootstrap_season_cookies() {
		self::expire_season_cookie( self::LEGACY_SEASON_COOKIE_NAME );
		self::sync_natural_season_boundary();
	}

	/**
	 * Clear manual season overrides when the natural calendar season changes.
	 *
	 * @return void
	 */
	private static function sync_natural_season_boundary() {
		if ( headers_sent() ) {
			return;
		}

		$current_natural = self::detect_natural_season_slug();
		$stored_calendar = isset( $_COOKIE[ self::SEASON_CALENDAR_COOKIE_NAME ] )
			? self::resolve_season_slug( wp_unslash( $_COOKIE[ self::SEASON_CALENDAR_COOKIE_NAME ] ) )
			: null;

		if ( null !== $stored_calendar && $stored_calendar !== $current_natural ) {
			self::expire_season_cookie( self::SEASON_COOKIE_NAME );
			self::clear_season_caches();
		}

		if ( $stored_calendar !== $current_natural ) {
			self::set_calendar_season_cookie( $current_natural );
		}
	}

	/**
	 * Apply season switch from query string: clear caches, persist session cookie, reload.
	 *
	 * @return void
	 */
	public static function handle_season_switch() {
		if ( ! isset( $_GET[ self::SEASON_QUERY_PARAM ] ) ) {
			return;
		}

		if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		$slug = self::resolve_season_slug( wp_unslash( $_GET[ self::SEASON_QUERY_PARAM ] ) );

		if ( null === $slug ) {
			return;
		}

		$previous = isset( $_COOKIE[ self::SEASON_COOKIE_NAME ] )
			? self::resolve_season_slug( wp_unslash( $_COOKIE[ self::SEASON_COOKIE_NAME ] ) )
			: null;

		if ( $previous !== $slug ) {
			self::clear_season_caches();
		}

		self::bootstrap_request_season( $slug );

		if ( ! headers_sent() ) {
			self::set_season_session_cookie( $slug );

			nocache_headers();
			wp_safe_redirect( remove_query_arg( self::SEASON_QUERY_PARAM ) );
			exit;
		}
	}

	/**
	 * Apply a season override for the current request render.
	 *
	 * @param string|null $slug Season slug.
	 * @return void
	 */
	public static function bootstrap_request_season( $slug ) {
		$resolved = self::resolve_season_slug( (string) $slug );

		if ( null === $resolved ) {
			return;
		}

		$_COOKIE[ self::SEASON_COOKIE_NAME ] = $resolved;
	}

	/**
	 * Cookie settings exposed to front-end scripts.
	 *
	 * @return array<string, string>
	 */
	public static function get_client_cookie_config() {
		return array(
			'name' => self::SEASON_COOKIE_NAME,
			'path' => COOKIEPATH ? COOKIEPATH : '/',
		);
	}

	/**
	 * Persist the selected season for the current browser session only.
	 *
	 * @param string $slug Season slug.
	 * @return void
	 */
	private static function set_season_session_cookie( $slug ) {
		setcookie(
			self::SEASON_COOKIE_NAME,
			$slug,
			self::get_session_cookie_options()
		);

		$_COOKIE[ self::SEASON_COOKIE_NAME ] = $slug;
	}

	/**
	 * Remember the last natural season seen by the visitor.
	 *
	 * @param string $slug Natural season slug.
	 * @return void
	 */
	private static function set_calendar_season_cookie( $slug ) {
		setcookie(
			self::SEASON_CALENDAR_COOKIE_NAME,
			$slug,
			array(
				'expires'  => time() + YEAR_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => false,
				'samesite' => 'Lax',
			)
		);

		$_COOKIE[ self::SEASON_CALENDAR_COOKIE_NAME ] = $slug;
	}

	/**
	 * Browser storage keys cleared when the natural season changes.
	 *
	 * @return array<int, string>
	 */
	public static function get_client_storage_keys_to_reset() {
		return array(
			'aptox-season-modal-shown',
		);
	}

	/**
	 * Config for front-end sessionStorage sync on season boundary changes.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_client_storage_config() {
		return array(
			'calendarKey' => self::SEASON_STORAGE_SYNC_KEY,
			'calendarSlug' => self::detect_natural_season_slug(),
			'keysToClear'  => self::get_client_storage_keys_to_reset(),
		);
	}

	/**
	 * Output an early head script that resets browser session state on season change.
	 *
	 * @return void
	 */
	public static function render_client_storage_sync_script() {
		if ( is_admin() || headers_sent() ) {
			return;
		}

		$config = self::get_client_storage_config();

		if ( empty( $config['calendarSlug'] ) ) {
			return;
		}

		$config_json = wp_json_encode( $config );

		if ( ! is_string( $config_json ) ) {
			return;
		}

		echo '<script>(function(){try{var config=' . $config_json . ';var stored=sessionStorage.getItem(config.calendarKey);if(stored&&stored!==config.calendarSlug){config.keysToClear.forEach(function(item){sessionStorage.removeItem(item);localStorage.removeItem(item);});}sessionStorage.setItem(config.calendarKey,config.calendarSlug);}catch(e){}})();</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function get_session_cookie_options() {
		return array(
			'expires'  => 0,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		);
	}

	/**
	 * @param string $cookie_name Cookie name.
	 * @return void
	 */
	private static function expire_season_cookie( $cookie_name ) {
		if ( ! isset( $_COOKIE[ $cookie_name ] ) || headers_sent() ) {
			return;
		}

		setcookie(
			$cookie_name,
			'',
			array_merge(
				self::get_session_cookie_options(),
				array(
					'expires' => time() - YEAR_IN_SECONDS,
				)
			)
		);

		unset( $_COOKIE[ $cookie_name ] );
	}

	/**
	 * Delete theme transients so seasonal sections rebuild on next load.
	 *
	 * @return void
	 */
	public static function clear_season_caches() {
		global $wpdb;

		if ( ! isset( $wpdb->options ) ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_aptox_%'
			OR option_name LIKE '_transient_timeout_aptox_%'"
		);
	}

	/**
	 * Persist season preference from ?estacao= query param.
	 *
	 * @return void
	 * @deprecated Use handle_season_switch().
	 */
	public static function persist_season_from_request() {
		self::handle_season_switch();
	}

	/**
	 * Season options for the header switcher.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_all_seasons() {
		$current = self::detect_current_season_slug();
		$items   = array();

		foreach ( self::get_available_season_slugs() as $slug ) {
			$item             = self::get_season_context_by_slug( $slug );
			$item['is_active'] = ( $slug === $current );
			$items[]          = $item;
		}

		return $items;
	}

	/**
	 * Build season context for a slug.
	 *
	 * @param string $season_slug Season slug.
	 * @return array<string, string>
	 */
	public static function get_season_context_by_slug( $season_slug ) {
		$map = self::get_season_map();

		return $map[ $season_slug ] ?? $map['verao'];
	}

	/**
	 * @return array<string, array<string, string>>
	 */
	private static function get_season_map() {
		return array(
			'verao'      => array(
				'icon'        => 'verao',
				'label'       => 'Verão',
				'slug'        => 'verao',
				'description' => 'Ideias, inspirações e detalhes pensados para celebrar os momentos mais especiais do verão.',
			),
			'outono'     => array(
				'icon'        => 'outono',
				'label'       => 'Outono',
				'slug'        => 'outono',
				'description' => 'Ideias, inspirações e detalhes pensados para celebrar os momentos mais especiais do outono.',
			),
			'inverno'    => array(
				'icon'        => 'inverno',
				'label'       => 'Inverno',
				'slug'        => 'inverno',
				'description' => 'Ideias, inspirações e detalhes pensados para celebrar os momentos mais especiais do inverno.',
			),
			'primavera'  => array(
				'icon'        => 'primavera',
				'label'       => 'Primavera',
				'slug'        => 'primavera',
				'description' => 'Ideias, inspirações e detalhes pensados para celebrar os momentos mais especiais da primavera.',
			),
			'fim-de-ano' => array(
				'icon'            => 'fim-de-ano',
				'label'           => 'Fim de Ano',
				'slug'            => 'fim-de-ano',
				'highlight_title' => 'Chegou a época da magia',
				'description'     => 'Ideias, inspirações e detalhes pensados para celebrar os momentos mais especiais do natal e ano novo',
			),
		);
	}

	/**
	 * @return \DateTimeImmutable
	 */
	private static function get_site_datetime() {
		$timezone = function_exists( 'wp_timezone' )
			? wp_timezone()
			: new \DateTimeZone( 'America/Sao_Paulo' );

		return new \DateTimeImmutable( 'now', $timezone );
	}

	/**
	 * Detect meteorological season slug for the southern hemisphere (Brazil).
	 *
	 * Mar–May: outono · Jun–Aug: inverno · Sep–Nov: primavera · Jan–Feb: verão.
	 * December is handled separately as fim-de-ano.
	 *
	 * @param \DateTimeImmutable $now Current site datetime.
	 * @return string
	 */
	public static function detect_calendar_season_slug( \DateTimeImmutable $now ) {
		$month = (int) $now->format( 'n' );

		if ( $month >= 3 && $month <= 5 ) {
			return 'outono';
		}

		if ( $month >= 6 && $month <= 8 ) {
			return 'inverno';
		}

		if ( $month >= 9 && $month <= 11 ) {
			return 'primavera';
		}

		return 'verao';
	}

	/**
	 * Active season slug (override or natural calendar).
	 *
	 * @return string
	 */
	private static function detect_current_season_slug() {
		$override = self::get_override_season_slug();

		if ( null !== $override ) {
			return $override;
		}

		return self::detect_natural_season_slug();
	}

	/**
	 * Detect season slug for southern hemisphere based on current date.
	 *
	 * December is always treated as year-end context.
	 *
	 * @return string
	 */
	private static function detect_natural_season_slug() {
		$now = self::get_site_datetime();

		if ( 12 === (int) $now->format( 'n' ) ) {
			return 'fim-de-ano';
		}

		return self::detect_calendar_season_slug( $now );
	}

	/**
	 * Resolve season icon URL.
	 *
	 * @param string $icon Icon slug.
	 * @return string
	 */
	public static function season_icon( $icon ) {
		return get_template_directory_uri() . '/assets/icons/estacoes/estacao-' . $icon . '.svg';
	}

	/**
	 * Resolve recipe section icon URL for a season slug.
	 *
	 * @param string|null $season_slug Season slug.
	 * @return string
	 */
	public static function recipe_season_icon( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$map = array(
			'verao'      => 'ui-section-receitas-verao.png',
			'outono'     => 'ui-section-receitas-outono.png',
			'inverno'    => 'ui-section-receitas-inverno.png',
			'primavera'  => 'ui-section-receitas-primavera.png',
			'fim-de-ano' => 'ui-section-receitas-fim-de-ano.png',
		);

		$file = $map[ $season_slug ] ?? 'ui-section-receitas-verao.png';

		return get_template_directory_uri() . '/assets/icons/ui/sections/' . $file;
	}

	/**
	 * Resolve festivities section icon URL for a season slug.
	 *
	 * @param string|null $season_slug Season slug.
	 * @return string
	 */
	public static function party_season_icon( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$map = array(
			'verao'      => 'ui-section-festas-verao.png',
			'outono'     => 'ui-section-festas-outono.png',
			'inverno'    => 'ui-section-festas-inverno.png',
			'primavera'  => 'ui-section-festas-primavera.png',
			'fim-de-ano' => 'ui-section-festas-fim-de-ano.png',
		);

		$file = $map[ $season_slug ] ?? 'ui-section-festas.png';

		return get_template_directory_uri() . '/assets/icons/ui/sections/' . $file;
	}

	/**
	 * Resolve decoration section icon URL for a season slug.
	 *
	 * @param string|null $season_slug Season slug.
	 * @return string
	 */
	public static function decor_season_icon( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$map = array(
			'verao'      => 'ui-section-decoracao-verao.png',
			'outono'     => 'ui-section-decoracao-outono.png',
			'inverno'    => 'ui-section-decoracao-inverno.png',
			'primavera'  => 'ui-section-decoracao-primavera.png',
			'fim-de-ano' => 'ui-section-decoracao-fim-de-ano.png',
		);

		$file = $map[ $season_slug ] ?? 'ui-section-decoracao.png';

		return get_template_directory_uri() . '/assets/icons/ui/sections/' . $file;
	}

	/**
	 * Resolve Casa filter nav season icon URL for a season slug.
	 *
	 * @param string|null $season_slug Season slug.
	 * @return string
	 */
	public static function filter_home_season_icon( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$map = array(
			'verao'      => 'casa-sazonal-verao.png',
			'outono'     => 'casa-sazonal-outono.png',
			'inverno'    => 'casa-sazonal-inverno.png',
			'primavera'  => 'casa-sazonal-primavera.png',
			'fim-de-ano' => 'casa-sazonal-fim-de-ano.png',
		);

		$file = $map[ $season_slug ] ?? 'casa-sazonal-verao.png';

		return get_template_directory_uri() . '/assets/icons/casa/sazonal/' . $file;
	}

	/**
	 * Get seasonal newsletter data.
	 *
	 * @return array<string, string>
	 */
	public static function get_season_newsletter_data() {
		$season = self::get_season_context()['slug'];

		$copy = array(
			'description'       => 'Receba um guia especial com tudo o que você precisa para viver cada estação de forma mais aconchegante e intencional: receitas, decoração, tradições, organização do lar e inspirações selecionadas especialmente para esse período.',
			'description_extra' => 'São apenas quatro edições por ano, preparadas com carinho para você aproveitar o melhor de cada estação.',
			'button'            => 'QUERO RECEBER',
		);

		$data = array(
			'verao'      => array_merge(
				$copy,
				array(
					'title' => 'Receba ideias para um verão especial!',
					'list'  => 'newsletter-summer',
					'image' => aptox_theme_image_uri( 'cta-news-summer' ),
				)
			),
			'outono'     => array_merge(
				$copy,
				array(
					'title' => 'Receba ideias para um outono acolhedor!',
					'list'  => 'newsletter-autumn',
					'image' => aptox_theme_image_uri( 'cta-news-autumn' ),
				)
			),
			'inverno'    => array_merge(
				$copy,
				array(
					'title' => 'Receba ideias para um inverno aconchegante!',
					'list'  => 'newsletter-winter',
					'image' => aptox_theme_image_uri( 'cta-news-winter' ),
				)
			),
			'primavera'  => array_merge(
				$copy,
				array(
					'title' => 'Receba ideias para uma primavera especial!',
					'list'  => 'newsletter-spring',
					'image' => aptox_theme_image_uri( 'cta-news-spring' ),
				)
			),
			'fim-de-ano' => array_merge(
				$copy,
				array(
					'title' => 'Receba ideias para um fim de ano memorável!',
					'list'  => 'newsletter-year-end',
					'image' => aptox_theme_image_uri( 'cta-fim-de-ano' ),
				)
			),
		);

		return $data[ $season ] ?? $data['verao'];
	}

	/**
	 * Get seasonal recipe term.
	 *
	 * @param string $season_slug Season slug.
	 * @return \WP_Term|null
	 */
	public static function get_season_recipe_term( $season_slug ) {
		$term = get_term_by( 'slug', $season_slug, 'category' );

		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}

		$parent = get_term( $term->parent, 'category' );

		if ( $parent && ! is_wp_error( $parent ) && 'receitas' === $parent->slug ) {
			return $term;
		}

		return null;
	}

	/**
	 * Resolve editorial cover image for a page and season.
	 *
	 * @param int         $page_id     Page ID.
	 * @param string|null $season_slug Optional season slug.
	 * @return array{url: string, alt: string, width: int, height: int}|null
	 */
	public static function get_editorial_cover_image( $page_id, $season_slug = null ) {
		$page_id = absint( $page_id );

		if ( $page_id <= 0 ) {
			return null;
		}

		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$season_slug = sanitize_key( (string) $season_slug );

		if ( '' === $season_slug ) {
			return null;
		}

		$attachment_id = \Aptox\PostTypes\EditorialMetaBox::get_cover_attachment_id( $page_id, $season_slug );

		if ( $attachment_id <= 0 || ! wp_attachment_is_image( $attachment_id ) ) {
			return null;
		}

		$image_url = wp_get_attachment_image_url( $attachment_id, 'large' );

		if ( ! $image_url ) {
			return null;
		}

		$alt_text = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

		if ( '' === $alt_text ) {
			$alt_text = sprintf(
				/* translators: %s: season label */
				__( 'Cover editorial de %s', 'aptox' ),
				self::get_season_label( $season_slug )
			);
		}

		$meta = wp_get_attachment_metadata( $attachment_id );

		return array(
			'url'    => $image_url,
			'alt'    => $alt_text,
			'width'  => isset( $meta['width'] ) ? (int) $meta['width'] : 0,
			'height' => isset( $meta['height'] ) ? (int) $meta['height'] : 0,
		);
	}

	/**
	 * Legacy season labels map.
	 *
	 * @param string $slug Season slug.
	 * @return string
	 */
	public static function get_season_label( $slug ) {
		$map = array(
			'verao'      => 'verão',
			'inverno'    => 'inverno',
			'outono'     => 'outono',
			'primavera'  => 'primavera',
			'fim-de-ano' => 'fim de ano',
		);

		return $map[ $slug ] ?? ucfirst( $slug );
	}

	/**
	 * Home CTA copy keyed by season slug.
	 *
	 * @return array<string, string>
	 */
	private static function get_season_home_cta_text_map() {
		return array(
			'primavera'  => 'Uma estação cheia de flores e novos começos. Perfeita para abrir as janelas, encher a casa de cores e aproveitar os dias mais leves. Venha conferir as nossas seleções para essa estação e se inspirar para curtir essa época em grande estilo.',
			'verao'      => 'Uma estação cheia de sol e momentos ao ar livre. Perfeita para reunir quem você ama, preparar receitas refrescantes e aproveitar cada dia ao máximo. Venha conferir as nossas seleções para essa estação e se inspirar para curtir essa época em grande estilo.',
			'inverno'    => 'Uma estação cheia de aconchego. Perfeita para colocar uma manta no sofá, acender uma velinha e saborear uma bebida bem quentinha. Venha conferir as nossas seleções para essa estação e se inspirar para curtir esses dias em grande estilo.',
			'outono'     => 'Uma estação cheia de aconchego. Perfeita para acender aquela velinha e tomar um delicioso cafézinho. Venha conferir as nossas seleções para essa estação e se inspirar para curtir esses dias em grande estilo.',
			'fim-de-ano' => 'Uma época cheia de encanto e tradição. Perfeita para decorar a casa, preparar receitas especiais e compartilhar momentos à mesa. Venha conferir as nossas seleções para o fim de ano e se inspirar para celebrar essa temporada em grande estilo.',
		);
	}

	/**
	 * Get personalized home CTA text for the current or given season.
	 *
	 * @param string|null $season_slug Optional season slug.
	 * @return string
	 */
	public static function get_season_home_cta_text( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$map = self::get_season_home_cta_text_map();

		return $map[ $season_slug ] ?? '';
	}

	/**
	 * Editorial ritual examples keyed by season slug.
	 *
	 * @return array<string, array<int, string>>
	 */
	private static function get_season_editorial_rituals_map() {
		return array(
			'primavera'  => array(
				'Refazer o jardim',
				'Assar um bolo de mel',
				'Decorar em tons pastéis',
				'Um novo cheirinho para casa',
				'Organizar armário para vestidos',
			),
			'verao'      => array(
				'Tomar um sorvete',
				'Decorar com itens tropicais',
				'Cultivar uma nova planta',
				'Preparar um almoço ao ar livre',
				'Organizar armário para biquínis',
				'Uma festa colorida',
			),
			'outono'     => array(
				'Tomar um café',
				'Acender uma vela e ler um livro',
				'Organizar armário para botas e itens de outono',
				'Decorar com tons terrosos',
				'Algo com chocolate',
			),
			'inverno'    => array(
				'Tomar chocolate quente',
				'Organizar armário para casacos',
				'Trocar as mantas e deixar o sofá mais aconchegante',
				'Montar uma noite de filmes em casa',
				'Decorar com rosas brancas',
			),
			'fim-de-ano' => array(
				'Montar a decoração de natal',
				'Elaborar um cardápio para o fim de ano',
				'Assar biscoitos aromáticos',
				'Montar uma mesa especial para celebrar em família',
				'Preparar presentes artesanais',
				'Guardar com carinho as memórias do ano que passou',
			),
		);
	}

	/**
	 * Seasonal intro copy for the editorial rituals block.
	 *
	 * @return array<string, string>
	 */
	private static function get_season_editorial_rituals_intro_map() {
		return array(
			'primavera'  => 'Na primavera, acreditamos que pequenas mudanças são capazes de renovar a forma como vivemos:',
			'verao'      => 'No verão, acreditamos que pequenas mudanças são capazes de renovar a forma como vivemos:',
			'outono'     => 'No outono, acreditamos que pequenas mudanças são capazes de renovar a forma como vivemos:',
			'inverno'    => 'No inverno, acreditamos que pequenas mudanças são capazes de renovar a forma como vivemos:',
			'fim-de-ano' => 'No fim de ano, acreditamos que pequenas mudanças são capazes de renovar a forma como vivemos:',
		);
	}

	/**
	 * Get editorial ritual examples for the current or given season.
	 *
	 * @param string|null $season_slug Optional season slug.
	 * @return array<int, string>
	 */
	public static function get_season_editorial_rituals( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$map = self::get_season_editorial_rituals_map();

		return $map[ $season_slug ] ?? $map['verao'];
	}

	/**
	 * Get editorial rituals intro copy for the current or given season.
	 *
	 * @param string|null $season_slug Optional season slug.
	 * @return string
	 */
	public static function get_season_editorial_rituals_intro( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$map = self::get_season_editorial_rituals_intro_map();

		return $map[ $season_slug ] ?? $map['verao'];
	}

	/**
	 * Get the latest published celebration post for the current or given season.
	 *
	 * @param string|null $season_slug Optional season slug.
	 * @return \WP_Post|null
	 */
	public static function get_latest_season_celebration_post( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$season_slug = sanitize_key( (string) $season_slug );

		if ( '' === $season_slug || ! post_type_exists( 'celebracoes' ) ) {
			return null;
		}

		$taxonomies = array( 'celebracao_categoria', 'celebracao' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$term = get_term_by( 'slug', $season_slug, $taxonomy );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			$post = self::get_latest_celebration_for_term( $term->term_id, $taxonomy );

			if ( $post instanceof \WP_Post ) {
				return $post;
			}
		}

		return null;
	}

	/**
	 * Seasonal festivity categories shown on the home page.
	 *
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	public static function get_season_festivities_config() {
		return array(
			'verao'      => array(
				array(
					'slugs' => array( 'carnaval' ),
					'label' => 'Carnaval',
				),
				array(
					'slugs' => array( 'aniversario-verao', 'aniversario-de-verao' ),
					'label' => 'Aniversário Verão',
				),
			),
			'outono'     => array(
				array(
					'slugs' => array( 'pascoa' ),
					'label' => 'Páscoa',
				),
				array(
					'slugs' => array( 'dia-das-maes', 'dias-das-maes' ),
					'label' => 'Dia das Mães',
				),
				array(
					'slugs' => array( 'festa-junina' ),
					'label' => 'Festa Junina',
				),
				array(
					'slugs' => array( 'dia-dos-namorados' ),
					'label' => 'Dia dos Namorados',
				),
				array(
					'slugs' => array( 'aniversario-outono', 'aniversario-de-outono' ),
					'label' => 'Aniversário Outono',
				),
			),
			'inverno'    => array(
				array(
					'slugs' => array( 'aniversario-inverno', 'aniversario-de-inverno' ),
					'label' => 'Aniversário de Inverno',
				),
				array(
					'slugs' => array( 'dia-dos-pais' ),
					'label' => 'Dia dos Pais',
				),
			),
			'primavera'  => array(
				array(
					'slugs' => array( 'dia-de-los-muertos' ),
					'label' => 'Dia de los Muertos',
				),
				array(
					'slugs' => array( 'halloween' ),
					'label' => 'Halloween',
				),
				array(
					'slugs' => array( 'aniversario-primavera', 'aniversario-de-primavera' ),
					'label' => 'Aniversário Primavera',
				),
			),
			'fim-de-ano' => array(
				array(
					'slugs' => array( 'natal' ),
					'label' => 'Natal',
				),
				array(
					'slugs' => array( 'ano-novo' ),
					'label' => 'Ano Novo',
				),
			),
		);
	}

	/**
	 * Get resolved home festivity cards for the current or given season.
	 *
	 * @param string|null $season_slug Optional season slug.
	 * @return array<int, array<string, string>>
	 */
	public static function get_season_festivities( $season_slug = null ) {
		if ( null === $season_slug ) {
			$season_slug = self::detect_current_season_slug();
		}

		$config = self::get_season_festivities_config();

		if ( empty( $config[ $season_slug ] ) || ! post_type_exists( 'celebracoes' ) ) {
			return array();
		}

		$items = array();

		foreach ( $config[ $season_slug ] as $festivity ) {
			$item = self::resolve_festivity_item( $festivity );

			if ( null !== $item ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Resolve a festivity config entry into a renderable card.
	 *
	 * @param array<string, mixed> $festivity Festivity config.
	 * @return array<string, string>|null
	 */
	private static function resolve_festivity_item( array $festivity ) {
		$slugs = isset( $festivity['slugs'] ) && is_array( $festivity['slugs'] )
			? $festivity['slugs']
			: array();
		$label = isset( $festivity['label'] ) ? (string) $festivity['label'] : '';

		$term_data = self::resolve_celebration_term( $slugs );

		if ( null === $term_data ) {
			return null;
		}

		$post = self::get_latest_celebration_for_term(
			$term_data['term']->term_id,
			$term_data['taxonomy']
		);

		if ( null === $post ) {
			return null;
		}

		$image = function_exists( 'aptox_get_post_thumbnail_src' )
			? aptox_get_post_thumbnail_src( $post->ID, 'aptox-card' )
			: get_the_post_thumbnail_url( $post->ID, 'aptox-card' );

		if ( ! $image ) {
			return null;
		}

		$term_link = get_term_link( $term_data['term'] );

		if ( is_wp_error( $term_link ) ) {
			return null;
		}

		return array(
			'label' => ! empty( $term_data['term']->name )
				? sanitize_text_field( $term_data['term']->name )
				: sanitize_text_field( $label ),
			'url'   => esc_url_raw( $term_link ),
			'image' => esc_url_raw( $image ),
		);
	}

	/**
	 * Resolve a celebration taxonomy term from slug candidates.
	 *
	 * @param array<int, string> $slugs Term slug candidates.
	 * @return array{term: \WP_Term, taxonomy: string}|null
	 */
	private static function resolve_celebration_term( array $slugs ) {
		$taxonomies = array( 'celebracao_categoria', 'celebracao' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			foreach ( $slugs as $slug ) {
				$term = get_term_by( 'slug', sanitize_title( $slug ), $taxonomy );

				if ( $term && ! is_wp_error( $term ) ) {
					return array(
						'term'     => $term,
						'taxonomy' => $taxonomy,
					);
				}
			}
		}

		return null;
	}

	/**
	 * Get the latest published celebration post for a taxonomy term.
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return \WP_Post|null
	 */
	private static function get_latest_celebration_for_term( $term_id, $taxonomy ) {
		$query = new \WP_Query(
			array(
				'post_type'              => 'celebracoes',
				'posts_per_page'         => 1,
				'post_status'            => 'publish',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => true,
				'orderby'                => array(
					'date' => 'DESC',
					'ID'   => 'DESC',
				),
				'tax_query'              => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => array( (int) $term_id ),
					),
				),
			)
		);

		if ( ! $query->have_posts() ) {
			return null;
		}

		$post = $query->posts[0];

		wp_reset_postdata();

		return $post instanceof \WP_Post ? $post : null;
	}
}
