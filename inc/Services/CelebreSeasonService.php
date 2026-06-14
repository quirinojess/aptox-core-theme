<?php
/**
 * Celebration page seasonal festivity blocks.
 *
 * @package Aptox
 */

namespace Aptox\Services;

class CelebreSeasonService {
	/**
	 * Festivity catalog keyed by internal slug.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_festivity_catalog() {
		return array(
			'carnaval'  => array(
				'slugs' => array( 'carnaval' ),
				'label' => 'Carnaval',
				'icon'  => 'ico-carnival.png',
			),
			'pascoa'    => array(
				'slugs' => array( 'pascoa' ),
				'label' => 'Pascoa',
				'icon'  => 'ico-easter.png',
			),
			'maes'      => array(
				'slugs' => array( 'dia-das-maes', 'dias-das-maes' ),
				'label' => 'Maes',
				'icon'  => 'ico-mother.png',
			),
			'junina'    => array(
				'slugs' => array( 'festa-junina' ),
				'label' => 'Festa Junina',
				'icon'  => 'ico-junine.png',
			),
			'pais'      => array(
				'slugs' => array( 'dia-dos-pais' ),
				'label' => 'Pais',
				'icon'  => 'ico-dad.png',
			),
			'halloween' => array(
				'slugs' => array( 'halloween' ),
				'label' => 'Halloween',
				'icon'  => 'ico-halloween.png',
			),
			'muertos'   => array(
				'slugs' => array( 'dia-de-los-muertos' ),
				'label' => 'Dia de los Muertos',
				'icon'  => 'ico-muertos.png',
			),
			'natal'     => array(
				'slugs' => array( 'natal' ),
				'label' => 'Natal',
				'icon'  => 'ico-xmas.png',
			),
			'ano-novo'  => array(
				'slugs' => array( 'ano-novo' ),
				'label' => 'Ano Novo',
				'icon'  => 'ico-new-year.png',
			),
		);
	}

	/**
	 * Active festivity keys for the Celebre page calendar.
	 *
	 * @param \DateTimeImmutable|null $now Optional reference datetime.
	 * @return array<int, string>
	 */
	public static function get_active_festivity_keys( $now = null ) {
		$now   = $now instanceof \DateTimeImmutable ? $now : self::get_site_datetime();
		$month = (int) $now->format( 'n' );
		$day   = (int) $now->format( 'j' );

		if ( 12 === $month ) {
			return $day <= 24 ? array( 'natal' ) : array( 'ano-novo' );
		}

		$season = self::detect_astronomical_season_slug( $now );

		switch ( $season ) {
			case 'verao':
				return array( 'carnaval' );
			case 'outono':
				return self::get_outono_festivity_keys( $month, $day );
			case 'inverno':
				return array( 'pais' );
			case 'primavera':
				return self::get_primavera_festivity_keys( $month, $day );
		}

		return array();
	}

	/**
	 * Cache key suffix for the current festivity window.
	 *
	 * @return string
	 */
	public static function get_cache_suffix() {
		$now  = self::get_site_datetime();
		$keys = self::get_active_festivity_keys( $now );

		return sanitize_key( implode( '-', $keys ) . '_' . $now->format( 'Ymd' ) );
	}

	/**
	 * Resolve celebration blocks for the current calendar window.
	 *
	 * @param string|null $season_slug Optional season slug (unused; kept for BC).
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_season_blocks( $season_slug = null ) {
		unset( $season_slug );

		if ( ! post_type_exists( 'celebracoes' ) ) {
			return array();
		}

		$catalog = self::get_festivity_catalog();
		$blocks  = array();

		foreach ( self::get_active_festivity_keys() as $festivity_key ) {
			if ( empty( $catalog[ $festivity_key ] ) ) {
				continue;
			}

			$festivity = $catalog[ $festivity_key ];
			$term_data = self::resolve_celebration_term( $festivity['slugs'] );

			if ( null === $term_data ) {
				continue;
			}

			$posts = self::get_celebration_posts_for_term(
				(int) $term_data['term']->term_id,
				$term_data['taxonomy'],
				8
			);

			if ( empty( $posts ) ) {
				continue;
			}

			$term_link = get_term_link( $term_data['term'] );

			if ( is_wp_error( $term_link ) ) {
				continue;
			}

			$blocks[] = array(
				'label'    => $festivity['label'],
				'icon'     => $festivity['icon'],
				'term_url' => esc_url_raw( $term_link ),
				'posts'    => $posts,
				'carousel' => count( $posts ) > 4,
			);
		}

		return $blocks;
	}

	/**
	 * Outono festivity calendar.
	 *
	 * @param int $month Month number.
	 * @param int $day   Day of month.
	 * @return array<int, string>
	 */
	private static function get_outono_festivity_keys( $month, $day ) {
		if ( 3 === $month || ( 4 === $month && $day < 15 ) ) {
			return array( 'pascoa' );
		}

		if ( ( 4 === $month && $day >= 15 ) || ( 5 === $month && $day < 15 ) ) {
			return array( 'maes' );
		}

		if ( ( 5 === $month && $day >= 15 ) || 6 === $month ) {
			return array( 'junina' );
		}

		return array();
	}

	/**
	 * Primavera festivity calendar.
	 *
	 * @param int $month Month number.
	 * @param int $day   Day of month.
	 * @return array<int, string>
	 */
	private static function get_primavera_festivity_keys( $month, $day ) {
		if ( ( 9 === $month && $day >= 30 ) || 10 === $month ) {
			return array( 'halloween' );
		}

		if ( 11 === $month ) {
			return array( 'muertos' );
		}

		return array();
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
	 * @param \DateTimeImmutable $now Current site datetime.
	 * @return string
	 */
	private static function detect_astronomical_season_slug( \DateTimeImmutable $now ) {
		$year     = (int) $now->format( 'Y' );
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
	 * Get celebration posts for a taxonomy term.
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @param int    $limit    Maximum number of posts.
	 * @return array<int, \WP_Post>
	 */
	private static function get_celebration_posts_for_term( $term_id, $taxonomy, $limit = 8 ) {
		$query = new \WP_Query(
			array(
				'post_type'              => 'celebracoes',
				'posts_per_page'         => max( 1, (int) $limit ),
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
			return array();
		}

		$posts = $query->posts;

		wp_reset_postdata();

		return is_array( $posts ) ? $posts : array();
	}
}
