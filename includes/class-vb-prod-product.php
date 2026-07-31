<?php
/**
 * Helpers de leitura de produto (front + Elementor).
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Product
 */
class VB_Prod_Product {

	/**
	 * ID do produto no contexto atual (single / loop Elementor).
	 *
	 * @param int $fallback Fallback.
	 * @return int
	 */
	public static function current_id( $fallback = 0 ) {
		$id = absint( $fallback );
		if ( $id ) {
			return $id;
		}

		$id = get_the_ID();
		if ( $id && VB_Prod_CPT::POST_TYPE === get_post_type( $id ) ) {
			return (int) $id;
		}

		// Elementor Theme Builder / preview.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$doc = \Elementor\Plugin::$instance->documents->get_current();
			if ( $doc && method_exists( $doc, 'get_main_id' ) ) {
				$main = (int) $doc->get_main_id();
				if ( $main && VB_Prod_CPT::POST_TYPE === get_post_type( $main ) ) {
					return $main;
				}
			}
		}

		return 0;
	}

	/**
	 * Título.
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function get_title( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		return $post_id ? get_the_title( $post_id ) : '';
	}

	/**
	 * Nome curto para vitrine / H1: sem marca.
	 * Remove “tipo N” só se ainda sobrar um nome distintivo (ex.: “Arroz Mix”).
	 * Se sobrar só “Arroz”/“Feijão”, mantém o tipo (ex.: “Arroz tipo 2”).
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function get_titulo_destaque( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		if ( ! $post_id ) {
			return '';
		}
		$title = self::get_title( $post_id );
		if ( ! $title ) {
			return '';
		}

		$marcas = array();
		$marca  = self::get_marca_nome( $post_id );
		if ( $marca ) {
			$marcas[] = $marca;
		}
		foreach ( array( 'Valle Branco', 'Castelão', 'Castelao', 'Aene', 'Vita' ) as $m ) {
			$marcas[] = $m;
		}
		$marcas = array_unique( array_filter( $marcas ) );
		usort(
			$marcas,
			static function ( $a, $b ) {
				return mb_strlen( $b ) - mb_strlen( $a );
			}
		);

		$clean = $title;
		foreach ( $marcas as $m ) {
			$clean = preg_replace( '/\b' . preg_quote( $m, '/' ) . '\b/iu', ' ', (string) $clean );
		}
		$clean = preg_replace( '/\s{2,}/u', ' ', (string) $clean );
		$clean = trim( (string) $clean, " \t\n\r\0\x0B-–—" );

		$sem_tipo = preg_replace( '/\s*tipo\s*\d+/iu', '', (string) $clean );
		$sem_tipo = preg_replace( '/\s*\bT\d+\b/iu', '', (string) $sem_tipo );
		$sem_tipo = preg_replace( '/\s{2,}/u', ' ', (string) $sem_tipo );
		$sem_tipo = trim( (string) $sem_tipo, " \t\n\r\0\x0B-–—" );

		$genericos = array( 'arroz', 'feijão', 'feijao', 'palmito', 'queijo', 'queijo ralado' );
		$sem_key   = mb_strtolower( $sem_tipo );
		if ( $sem_tipo && ! in_array( $sem_key, $genericos, true ) ) {
			return $sem_tipo;
		}

		return $clean ? $clean : $title;
	}

	/**
	 * Descrição (conteúdo).
	 *
	 * @param int  $post_id ID.
	 * @param bool $raw     Sem filtros.
	 * @return string
	 */
	public static function get_description( $post_id = 0, $raw = false ) {
		$post_id = self::current_id( $post_id );
		if ( ! $post_id ) {
			return '';
		}
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}
		return $raw ? (string) $post->post_content : apply_filters( 'the_content', $post->post_content );
	}

	/**
	 * SKU.
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function get_sku( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		return $post_id ? (string) get_post_meta( $post_id, '_vb_sku', true ) : '';
	}

	/**
	 * Produto faz parte do catálogo público (vitrine)?
	 *
	 * @param int $post_id ID.
	 * @return bool
	 */
	public static function is_catalogo( $post_id = 0 ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}
		return '1' === (string) get_post_meta( $post_id, VB_Prod_CPT::META_CATALOGO, true );
	}

	/**
	 * meta_query para listar só produtos do catálogo.
	 *
	 * @return array
	 */
	public static function catalogo_meta_query() {
		return array(
			array(
				'key'     => VB_Prod_CPT::META_CATALOGO,
				'value'   => '1',
				'compare' => '=',
			),
		);
	}

	/**
	 * Aplica filtro de catálogo em args de WP_Query / get_posts.
	 *
	 * @param array $args Args.
	 * @return array
	 */
	public static function apply_catalogo_args( $args ) {
		if ( ! is_array( $args ) ) {
			$args = array();
		}
		$meta = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
		if ( ! empty( $meta ) && ! isset( $meta['relation'] ) ) {
			$meta = array_merge( array( 'relation' => 'AND' ), $meta );
		}
		$meta[]             = self::catalogo_meta_query()[0];
		$args['meta_query'] = $meta; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		return $args;
	}

	/**
	 * Normaliza meta de lista (string “a, b” ou array) em lista limpa.
	 *
	 * @param mixed $raw Meta bruta.
	 * @return string[]
	 */
	private static function normalize_lista_meta( $raw ) {
		if ( is_array( $raw ) ) {
			$parts = $raw;
		} elseif ( is_string( $raw ) && '' !== $raw && 'Array' !== $raw ) {
			$parts = preg_split( '/[,;|·]+/u', $raw );
		} else {
			return array();
		}
		$out = array();
		foreach ( (array) $parts as $p ) {
			if ( is_array( $p ) ) {
				continue;
			}
			$p = trim( (string) $p );
			if ( '' === $p || 'Array' === $p ) {
				continue;
			}
			if ( ! in_array( $p, $out, true ) ) {
				$out[] = $p;
			}
		}
		return $out;
	}

	/**
	 * Pesos (texto).
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function get_pesos( $post_id = 0 ) {
		$list = self::get_pesos_lista( $post_id );
		return $list ? implode( ', ', $list ) : '';
	}

	/**
	 * Lista de pesos únicos (para tags).
	 *
	 * @param int $post_id ID.
	 * @return string[]
	 */
	public static function get_pesos_lista( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		if ( ! $post_id ) {
			return array();
		}
		$from_vars = self::pesos_from_variacoes( $post_id );
		if ( $from_vars ) {
			return $from_vars;
		}
		return self::normalize_lista_meta( get_post_meta( $post_id, '_vb_pesos', true ) );
	}

	/**
	 * Variações (SKU / peso / embalagem).
	 *
	 * @param int $post_id ID.
	 * @return array<int,array{sku:string,peso:string,embalagem:string,gtin:string}>
	 */
	public static function get_variacoes( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		if ( ! $post_id ) {
			return array();
		}
		$raw = get_post_meta( $post_id, '_vb_variacoes', true );
		if ( ! is_array( $raw ) ) {
			return array();
		}
		$out = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$out[] = array(
				'sku'       => isset( $row['sku'] ) ? (string) $row['sku'] : '',
				'peso'      => isset( $row['peso'] ) ? (string) $row['peso'] : '',
				'embalagem' => isset( $row['embalagem'] ) ? (string) $row['embalagem'] : '',
				'gtin'      => isset( $row['gtin'] ) ? (string) $row['gtin'] : '',
			);
		}
		return $out;
	}

	/**
	 * Rótulo de embalagens (ex.: 6x5kg · 15x2kg).
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function get_embalagens_label( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		if ( ! $post_id ) {
			return '';
		}

		$embs = array();
		foreach ( self::get_variacoes( $post_id ) as $v ) {
			if ( ! empty( $v['embalagem'] ) && ! in_array( $v['embalagem'], $embs, true ) ) {
				$embs[] = $v['embalagem'];
			}
		}
		if ( ! $embs ) {
			$embs = self::normalize_lista_meta( get_post_meta( $post_id, '_vb_embalagens', true ) );
		}
		$single = get_post_meta( $post_id, '_vb_embalagem', true );
		if ( is_string( $single ) && $single && 'Array' !== $single && ! in_array( $single, $embs, true ) ) {
			$embs[] = $single;
		}
		return implode( ' · ', $embs );
	}

	/**
	 * Pesos únicos a partir das variações.
	 *
	 * @param int $post_id ID.
	 * @return string[]
	 */
	private static function pesos_from_variacoes( $post_id ) {
		$out = array();
		foreach ( self::get_variacoes( $post_id ) as $v ) {
			$p = isset( $v['peso'] ) ? trim( (string) $v['peso'] ) : '';
			if ( $p && ! in_array( $p, $out, true ) ) {
				$out[] = $p;
			}
		}
		return $out;
	}

	/**
	 * IDs da galeria (sem a capa).
	 *
	 * @param int $post_id ID.
	 * @return int[]
	 */
	public static function get_gallery_ids( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		if ( ! $post_id ) {
			return array();
		}
		$raw = get_post_meta( $post_id, '_vb_galeria', true );
		if ( ! is_array( $raw ) ) {
			return array();
		}
		return array_values( array_filter( array_map( 'absint', $raw ) ) );
	}

	/**
	 * IDs para carrossel: capa + galeria (únicos).
	 *
	 * @param int $post_id ID.
	 * @return int[]
	 */
	public static function get_carousel_ids( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		$ids     = array();
		if ( $post_id && has_post_thumbnail( $post_id ) ) {
			$ids[] = (int) get_post_thumbnail_id( $post_id );
		}
		foreach ( self::get_gallery_ids( $post_id ) as $gid ) {
			if ( ! in_array( $gid, $ids, true ) ) {
				$ids[] = $gid;
			}
		}
		return $ids;
	}

	/**
	 * Tabelas do produto (Pacote / Pallet / Tributação…).
	 *
	 * @param int $post_id ID.
	 * @return array{tabelas:array<int,array{titulo:string,estilo:string,linhas:array<int,array{campo:string,valor:string}>}>}
	 */
	public static function get_nutricao( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		$default = array( 'tabelas' => array() );
		if ( ! $post_id ) {
			return $default;
		}
		$raw = get_post_meta( $post_id, '_vb_nutricao', true );
		if ( ! is_array( $raw ) ) {
			return $default;
		}
		return VB_Prod_Meta::sanitize_nutricao( $raw );
	}

	/**
	 * Nome da primeira categoria.
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function get_categoria_nome( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		if ( ! $post_id ) {
			return '';
		}
		$terms = get_the_terms( $post_id, VB_Prod_CPT::TAX_CATEGORIA );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return (string) get_post_meta( $post_id, '_vb_categoria', true );
		}
		return $terms[0]->name;
	}

	/**
	 * Nome da primeira marca.
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function get_marca_nome( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		if ( ! $post_id ) {
			return '';
		}
		$terms = get_the_terms( $post_id, VB_Prod_CPT::TAX_MARCA );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return (string) get_post_meta( $post_id, '_vb_marca', true );
		}
		return $terms[0]->name;
	}

	/**
	 * Produtos relacionados (mesma categoria).
	 *
	 * @param int $post_id ID.
	 * @param int $limit   Limite.
	 * @return WP_Post[]
	 */
	public static function get_relacionados( $post_id = 0, $limit = 4 ) {
		$post_id = self::current_id( $post_id );
		$limit   = max( 1, min( 24, absint( $limit ) ) );
		if ( ! $post_id ) {
			return array();
		}

		$term_ids = wp_get_post_terms( $post_id, VB_Prod_CPT::TAX_CATEGORIA, array( 'fields' => 'ids' ) );
		$args     = self::apply_catalogo_args(
			array(
				'post_type'              => VB_Prod_CPT::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => $limit,
				'post__not_in'           => array( $post_id ),
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => true,
			)
		);

		if ( ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => VB_Prod_CPT::TAX_CATEGORIA,
					'field'    => 'term_id',
					'terms'    => $term_ids,
				),
			);
		}

		$query = new WP_Query( $args );
		return $query->posts;
	}
}
