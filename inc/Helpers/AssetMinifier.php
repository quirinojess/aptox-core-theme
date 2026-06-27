<?php
/**
 * Lightweight CSS/JS minifiers for theme assets.
 *
 * @package Aptox
 */

namespace Aptox\Helpers;

class AssetMinifier {
	/**
	 * Minify CSS text.
	 *
	 * @param string $css Raw CSS.
	 * @return string
	 */
	public static function minify_css( $css ) {
		$css = (string) preg_replace( '/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', (string) $css );
		$css = (string) preg_replace( '/\s+/', ' ', $css );
		$css = (string) preg_replace( '/\s*([\{\}\;:,\>])\s*/', '$1', $css );
		$css = (string) preg_replace( '/;}/', '}', $css );

		return trim( $css );
	}

	/**
	 * Minify JS while preserving strings, templates and line breaks for ASI.
	 *
	 * @param string $js Raw JavaScript.
	 * @return string
	 */
	public static function minify_js( $js ) {
		$js     = str_replace( array( "\r\n", "\r" ), "\n", (string) $js );
		$length = strlen( $js );
		$output = '';
		$state  = 'code';
		$i      = 0;

		while ( $i < $length ) {
			$char      = $js[ $i ];
			$next_char = $i + 1 < $length ? $js[ $i + 1 ] : '';

			if ( 'line_comment' === $state ) {
				if ( "\n" === $char ) {
					$state  = 'code';
					$output .= $char;
				}

				++$i;
				continue;
			}

			if ( 'block_comment' === $state ) {
				if ( '*' === $char && '/' === $next_char ) {
					$state = 'code';
					$i    += 2;
					continue;
				}

				++$i;
				continue;
			}

			if ( 'single' === $state ) {
				$output .= $char;

				if ( '\\' === $char ) {
					if ( $i + 1 < $length ) {
						$output .= $js[ $i + 1 ];
						$i     += 2;
						continue;
					}
				} elseif ( "'" === $char ) {
					$state = 'code';
				}

				++$i;
				continue;
			}

			if ( 'double' === $state ) {
				$output .= $char;

				if ( '\\' === $char ) {
					if ( $i + 1 < $length ) {
						$output .= $js[ $i + 1 ];
						$i     += 2;
						continue;
					}
				} elseif ( '"' === $char ) {
					$state = 'code';
				}

				++$i;
				continue;
			}

			if ( 'template' === $state ) {
				$output .= $char;

				if ( '\\' === $char ) {
					if ( $i + 1 < $length ) {
						$output .= $js[ $i + 1 ];
						$i     += 2;
						continue;
					}
				} elseif ( '`' === $char ) {
					$state = 'code';
				}

				++$i;
				continue;
			}

			if ( '/' === $char && '/' === $next_char ) {
				$state = 'line_comment';
				$i    += 2;
				continue;
			}

			if ( '/' === $char && '*' === $next_char ) {
				$state = 'block_comment';
				$i    += 2;
				continue;
			}

			if ( "'" === $char ) {
				$output .= $char;
				$state   = 'single';
				++$i;
				continue;
			}

			if ( '"' === $char ) {
				$output .= $char;
				$state   = 'double';
				++$i;
				continue;
			}

			if ( '`' === $char ) {
				$output .= $char;
				$state   = 'template';
				++$i;
				continue;
			}

			if ( preg_match( '/\s/u', $char ) ) {
				if ( "\n" === $char ) {
					$output = rtrim( $output, " \t" ) . "\n";
				} elseif ( '' !== $output && ! preg_match( '/[\s\n]$/u', $output ) ) {
					$output .= ' ';
				}

				++$i;
				continue;
			}

			$output .= $char;
			++$i;
		}

		$lines = array_filter(
			array_map( 'trim', explode( "\n", $output ) ),
			static function ( $line ) {
				return '' !== $line;
			}
		);

		return implode( "\n", $lines );
	}
}
