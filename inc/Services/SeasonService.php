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
	 * Detect editorial CTA celebration key from calendar rules.
	 *
	 * @return string
	 */
	private static function detect_celebration_cta_key() {
		$now   = self::get_site_datetime();
		$month = (int) $now->format( 'n' );
		$day   = (int) $now->format( 'j' );

		if ( 12 === $month ) {
			return 'ano-novo';
		}

		if ( 5 === $month || 6 === $month ) {
			return 'festa-junina';
		}

		if ( 4 === $month || ( 3 === $month && $day >= 20 ) ) {
			return 'pascoa';
		}

		$season = self::detect_astronomical_season_slug( $now );

		if ( 'verao' === $season ) {
			return 'carnaval';
		}

		if ( 'inverno' === $season ) {
			return 'aniversarios';
		}

		if ( 'primavera' === $season ) {
			if ( 9 === $month ) {
				return 'dia-de-los-muertos';
			}

			if ( 10 === $month ) {
				return 'halloween';
			}

			if ( 11 === $month ) {
				return 'dia-de-los-muertos';
			}
		}

		return 'carnaval';
	}

	/**
	 * Featured image URL from the latest published post in a celebration category.
	 *
	 * @param string $category_slug Term slug under celebracao_categoria (or celebracao).
	 * @param string $fallback      Default image when no post or thumbnail.
	 * @return string
	 */
	private static function resolve_celebration_category_thumbnail_url( $category_slug, $fallback ) {
		$category_slug = sanitize_title( (string) $category_slug );

		if ( '' === $category_slug || ! post_type_exists( 'celebracoes' ) ) {
			return $fallback;
		}

		$cache_key = 'aptox_celeb_cta_thumb_v2_' . md5( $category_slug );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$taxonomies = array( 'celebracao_categoria', 'celebracao' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$term = get_term_by( 'slug', $category_slug, $taxonomy );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			$query = new \WP_Query(
				array(
					'post_type'              => 'celebracoes',
					'posts_per_page'         => 1,
					'post_status'            => 'publish',
					'orderby'                => array(
						'date' => 'DESC',
						'ID'   => 'DESC',
					),
					'ignore_sticky_posts'    => true,
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => true,
					'tax_query'              => array(
						array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => array( $category_slug ),
						),
					),
				)
			);

			if ( $query->have_posts() ) {
				$query->the_post();
				$post_id   = (int) get_the_ID();
				$thumb_url = get_the_post_thumbnail_url( $post_id, 'large' );
				wp_reset_postdata();

				if ( is_string( $thumb_url ) && '' !== $thumb_url ) {
					set_transient( $cache_key, $thumb_url, 15 * MINUTE_IN_SECONDS );
					return $thumb_url;
				}
			}
		}

		set_transient( $cache_key, $fallback, 5 * MINUTE_IN_SECONDS );

		return $fallback;
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
	 * Map CTA celebration keys to celebration category term slugs (URLs / taxonomia).
	 *
	 * @return array<string, string>
	 */
	private static function celebration_cta_category_slug_map() {
		return array(
			'carnaval'           => 'carnaval',
			'pascoa'             => 'pascoa',
			'festa-junina'       => 'festa-junina',
			'aniversarios'       => 'aniversarios',
			'dia-de-los-muertos' => 'dia-de-los-muertos',
			'halloween'          => 'halloween',
			'ano-novo'           => 'ano-novo',
		);
	}

	/**
	 * Editorial CTA catalog keyed by celebration.
	 *
	 * @return array<string, array<string, string>>
	 */
	private static function get_celebration_cta_catalog() {
		$summer_image = get_template_directory_uri() . '/assets/img/cta-celebration-summer.png';
		$autumn_image = get_template_directory_uri() . '/assets/img/cta-celebration-autumn.png';
		$winter_image = get_template_directory_uri() . '/assets/images/cta/cta-celebration-winter.png';
		$spring_image = get_template_directory_uri() . '/assets/images/cta/cta-celebration-spring.jpg';

		return array(
			'carnaval'           => array(
				'label'       => 'carnaval',
				'title'       => 'Que tal uma festa tropical?',
				'description' => 'Venha se inspirar com ideias, dicas e indicações para celebrar no carnaval.',
				'image'       => $summer_image,
				'link'        => home_url( '/celebracoes/carnaval' ),
			),
			'pascoa'             => array(
				'title'       => 'Vamos nos preparar para a páscoa',
				'description' => 'Dicas e referências para uma deliciosa páscoa de outono.',
				'image'       => $autumn_image,
				'link'        => home_url( '/celebracoes/pascoa' ),
			),
			'festa-junina'       => array(
				'title'       => 'Vamos nos preparar para as Festas Juninas?',
				'description' => 'Dicas e referências para você celebrar as festividades juninas com muita alegria e tradição.',
				'image'       => $autumn_image,
				'link'        => home_url( '/celebracoes/festa-junina' ),
			),
			'aniversarios'       => array(
				'title'       => 'Parabéns para você...',
				'description' => 'Dicas para aniversários inspiradores e afetivos.',
				'image'       => $winter_image,
				'link'        => home_url( '/celebracoes/aniversarios' ),
			),
			'dia-de-los-muertos' => array(
				'title'       => 'Vamos celebrar o Día de los Muertos',
				'description' => 'Inspirações e referências para uma celebração cheia de significado e afeto.',
				'image'       => $spring_image,
				'link'        => home_url( '/celebracoes/dia-de-los-muertos' ),
			),
			'halloween'          => array(
				'title'       => 'Halloween de primavera?',
				'description' => 'Tudo para um Halloween delicado de primavera.',
				'image'       => $spring_image,
				'link'        => home_url( '/celebracoes/halloween' ),
			),
			'ano-novo'           => array(
				'title'       => 'Vamos nos preparar para ano novo',
				'description' => 'Dicas e referências para você se preparar para a virada de ano com muita magia e amor',
				'image'       => $summer_image,
				'link'        => home_url( '/celebracoes/ano-novo' ),
			),
		);
	}

	/**
	 * Get seasonal CTA data.
	 *
	 * @return array<string, string>
	 */
	public static function get_season_cta_data() {
		$key     = self::detect_celebration_cta_key();
		$catalog = self::get_celebration_cta_catalog();
		$data    = $catalog[ $key ] ?? $catalog['carnaval'];

		$slug_map  = self::celebration_cta_category_slug_map();
		$term_slug = $slug_map[ $key ] ?? $key;

		$data['image'] = self::resolve_celebration_category_thumbnail_url( $term_slug, $data['image'] );

		return $data;
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
}
