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
	 * Pesos (texto).
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function get_pesos( $post_id = 0 ) {
		$post_id = self::current_id( $post_id );
		return $post_id ? (string) get_post_meta( $post_id, '_vb_pesos', true ) : '';
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
		$args     = array(
			'post_type'              => VB_Prod_CPT::POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'post__not_in'           => array( $post_id ),
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
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
