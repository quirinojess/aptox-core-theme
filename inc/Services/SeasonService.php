<?php
/**
 * Seasonal context and content helpers.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class SeasonService {
	/**
	 * Get current season context.
	 *
	 * @return array<string, string>
	 */
	public static function get_season_context() {
		$season_slug = self::detect_current_season_slug();

		$map = array(
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

		return $map[ $season_slug ] ?? $map['verao'];
	}

	/**
	 * Detect season slug for southern hemisphere based on current date.
	 *
	 * December is always treated as year-end context, regardless of the solstice.
	 *
	 * @return string
	 */
	private static function get_site_datetime() {
		$timezone = function_exists( 'wp_timezone' )
			? wp_timezone()
			: new \DateTimeZone( 'America/Sao_Paulo' );

		return new \DateTimeImmutable( 'now', $timezone );
	}

	/**
	 * Detect astronomical season slug (southern hemisphere).
	 *
	 * @param \DateTimeImmutable $now Current site datetime.
	 * @return string
	 */
	private static function detect_astronomical_season_slug( \DateTimeImmutable $now ) {
		$year = (int) $now->format( 'Y' );
		$timezone = $now->getTimezone();

		$autumn_start = new \DateTimeImmutable( $year . '-03-20 00:00:00', $timezone );
		$winter_start = new \DateTimeImmutable( $year . '-06-21 00:00:00', $timezone );
		$spring_start = new \DateTimeImmutable( $year . '-09-23 00:00:00', $timezone );
		$summer_start = new \DateTimeImmutable( $year . '-12-21 00:00:00', $timezone );

		if ( $now >= $summer_start || $now < $autumn_start ) {
			return 'verao';
		}

		if ( $now >= $autumn_start && $now < $winter_start ) {
			return 'outono';
		}

		if ( $now >= $winter_start && $now < $spring_start ) {
			return 'inverno';
		}

		return 'primavera';
	}

	/**
	 * Detect season slug for southern hemisphere based on current date.
	 *
	 * December is always treated as year-end context, regardless of the solstice.
	 *
	 * @return string
	 */
	private static function detect_current_season_slug() {
		$now = self::get_site_datetime();

		if ( 12 === (int) $now->format( 'n' ) ) {
			return 'fim-de-ano';
		}

		return self::detect_astronomical_season_slug( $now );
	}

	/**
	 * Resolve season icon URL.
	 *
	 * @param string $icon Icon slug.
	 * @return string
	 */
	public static function season_icon( $icon ) {
		return get_template_directory_uri() . '/assets/icons/seasons/' . $icon . '.svg';
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
			'verao'      => 'icon-recipe-summer.png',
			'outono'     => 'icon-recipe-autumn.png',
			'inverno'    => 'icon-recipe-winter.png',
			'primavera'  => 'icon-recipe-spring.png',
			'fim-de-ano' => 'icon-recipe-end-year.png',
		);

		$file = $map[ $season_slug ] ?? 'icon-recipe-summer.png';

		return get_template_directory_uri() . '/assets/icons/ui/' . $file;
	}

	/**
	 * Get seasonal newsletter data.
	 *
	 * @return array<string, string>
	 */
	public static function get_season_newsletter_data() {
		$season = self::get_season_context()['slug'];

		$data = array(
			'verao'     => array(
				'title'       => 'Chegou a estação mais quente do ano!',
				'description' => 'Gostaria de receber inspirações e ideias para você curtir o verão da melhor maneira? Inscreva-se para receber conteúdos e mensagens exclusivas de forma gratuita.',
				'button'      => 'QUERO RECEBER',
				'list'        => 'newsletter-summer',
				'image'       => get_template_directory_uri() . '/assets/img/cta-news-summer.jpg',
			),
			'outono'    => array(
				'title'       => 'Ideias para um outono acolhedor',
				'description' => 'Conteúdos especiais, receitas e celebrações para o outono.',
				'button'      => 'QUERO RECEBER',
				'list'        => 'newsletter-autumn',
				'image'       => get_template_directory_uri() . '/assets/img/cta-news-autumn.png',
			),
			'inverno'   => array(
				'title'       => 'Conteúdos quentinhos para o inverno',
				'description' => 'Inspirações afetivas, festas intimistas e novidades de inverno.',
				'button'      => 'QUERO RECEBER',
				'list'        => 'newsletter-winter',
			),
			'primavera' => array(
				'title'       => 'A primavera chegou!',
				'description' => 'Flores, cores e ideias para celebrar a primavera.',
				'button'      => 'QUERO RECEBER',
				'list'        => 'newsletter-spring',
			),
			'fim-de-ano' => array(
				'title'       => 'Dezembro pede celebração!',
				'description' => 'Gostaria de receber inspirações e ideias para você curtir o fim de ano da melhor maneira? Inscreva-se para receber conteúdos e mensagens exclusivas de forma gratuita.',
				'button'      => 'QUERO RECEBER',
				'list'        => 'newsletter-year-end',
				'image'       => get_template_directory_uri() . '/assets/img/cta-news-autumn.png',
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
}
