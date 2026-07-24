<?php
/**
 * Cria termos de categoria/marca a partir das metas e associa aos produtos.
 */
require 'C:/xampp/htdocs/valle-branco/wp-load.php';

$q = new WP_Query(
	array(
		'post_type'      => 'vb_produto',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	)
);

$ok = 0;
foreach ( $q->posts as $id ) {
	$marca = get_post_meta( $id, '_vb_marca', true );
	$cat   = get_post_meta( $id, '_vb_categoria', true );

	// Inferir marca pelo título se meta vazia.
	if ( ! $marca ) {
		$title = remove_accents( strtoupper( get_the_title( $id ) ) );
		if ( false !== strpos( $title, 'CASTELAO' ) ) {
			$marca = 'Castelão';
		} elseif ( false !== strpos( $title, 'AENE' ) ) {
			$marca = 'Aene';
		} elseif ( false !== strpos( $title, 'VITA' ) ) {
			$marca = 'Vita';
		} elseif ( false !== strpos( $title, 'VALLE BRANCO' ) || false !== strpos( $title, 'V.BRANCO' ) ) {
			$marca = 'Valle Branco';
		}
		if ( $marca ) {
			update_post_meta( $id, '_vb_marca', $marca );
		}
	}

	// Inferir categoria pelo título se meta vazia.
	if ( ! $cat ) {
		$title = remove_accents( strtoupper( get_the_title( $id ) ) );
		if ( false !== strpos( $title, 'INTEGRAL' ) ) {
			$cat = 'Arroz Integral';
		} elseif ( false !== strpos( $title, 'PARBOILIZADO' ) ) {
			$cat = 'Arroz Parboilizado';
		} elseif ( false !== strpos( $title, 'ARROZ' ) ) {
			$cat = 'Arroz';
		} elseif ( false !== strpos( $title, 'FEIJAO' ) ) {
			$cat = 'Feijão';
		} elseif ( false !== strpos( $title, 'PALM' ) || false !== strpos( $title, 'PUPUNHA' ) ) {
			$cat = 'Conservas';
		} elseif ( false !== strpos( $title, 'QUEIJO' ) ) {
			$cat = 'Laticínios';
		}
		if ( $cat ) {
			update_post_meta( $id, '_vb_categoria', $cat );
		}
	}

	if ( $marca ) {
		$term = term_exists( $marca, 'vb_marca' );
		if ( ! $term ) {
			$term = wp_insert_term( $marca, 'vb_marca' );
		}
		if ( ! is_wp_error( $term ) ) {
			$tid = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
			wp_set_object_terms( $id, array( $tid ), 'vb_marca', false );
		}
	}

	if ( $cat ) {
		$term = term_exists( $cat, 'vb_categoria_produto' );
		if ( ! $term ) {
			$term = wp_insert_term( $cat, 'vb_categoria_produto' );
		}
		if ( ! is_wp_error( $term ) ) {
			$tid = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
			wp_set_object_terms( $id, array( $tid ), 'vb_categoria_produto', false );
		}
	}
	++$ok;
}

echo "Sincronizados: {$ok}\n";
echo 'Marcas: ' . wp_count_terms( array( 'taxonomy' => 'vb_marca', 'hide_empty' => false ) ) . "\n";
echo 'Categorias: ' . wp_count_terms( array( 'taxonomy' => 'vb_categoria_produto', 'hide_empty' => false ) ) . "\n";
