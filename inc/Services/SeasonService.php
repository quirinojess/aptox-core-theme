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
		return array(
			'icon'  => 'verao',
			'label' => 'Verão',
			'slug'  => 'verao',
		);
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
	 * Get seasonal CTA data.
	 *
	 * @return array<string, string>
	 */
	public static function get_season_cta_data() {
		$season = self::get_season_context()['slug'];

		$data = array(
			'verao'     => array(
				'label'       => 'carnaval',
				'title'       => 'Que tal uma festa tropical?',
				'description' => 'Venha se inspirar com ideias, dicas e indicações celebrar no verão.',
				'image'       => get_template_directory_uri() . '/assets/img/cta-celebration-summer.png',
				'link'        => home_url( '/celebracoes/carnaval' ),
			),
			'outono'    => array(
				'title'       => 'Vamos nos preparar para a páscoa',
				'description' => 'Dicas e referências para uma deliciosa páscoa de outono.',
				'image'       => get_template_directory_uri() . '/assets/images/cta/cta-celebration-autumn.png',
				'link'        => home_url( '/celebracoes/pascoa' ),
			),
			'inverno'   => array(
				'title'       => 'Parabés para você...',
				'description' => 'Dicas para aniversários inspriadores e afetivos.',
				'image'       => get_template_directory_uri() . '/assets/images/cta/cta-celebration-winter.png',
				'link'        => home_url( '/celebracoes/aniversarios' ),
			),
			'primavera' => array(
				'title'       => 'Halloween de primavera?',
				'description' => 'Tudo para um Halloween delicado de primavera.',
				'image'       => get_template_directory_uri() . '/assets/images/cta/cta-celebration-spring.jpg',
				'link'        => home_url( '/celebracoes/halloween' ),
			),
		);

		return $data[ $season ] ?? $data['verao'];
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
				'image'       => get_template_directory_uri() . '/assets/img/cta-news-autumn.jpg',
			),
			'inverno'   => array(
				'title'       => 'Conteúdos quentinhos para o inverno',
				'description' => 'Inspirações afetivas, festas intimistas e novidades de inverno.',
				'button'      => 'QUERO RECEBER',
				'list'        => 'newsletter-winter',
				'image'       => get_template_directory_uri() . '/assets/img/cta-news-winter.jpg',
			),
			'primavera' => array(
				'title'       => 'A primavera chegou!',
				'description' => 'Flores, cores e ideias para celebrar a primavera.',
				'button'      => 'QUERO RECEBER',
				'list'        => 'newsletter-spring',
				'image'       => get_template_directory_uri() . '/assets/img/cta-news-spring.jpg',
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
			'verao'     => 'verão',
			'inverno'   => 'inverno',
			'outono'    => 'outono',
			'primavera' => 'primavera',
		);

		return $map[ $slug ] ?? ucfirst( $slug );
	}
}
