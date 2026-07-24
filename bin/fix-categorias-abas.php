<?php
/**
 * Padroniza categorias do filtro: Arroz, Feijão, Palmito, Queijo ralado.
 */
require 'C:/xampp/htdocs/valle-branco/wp-load.php';

$tax = 'vb_categoria_produto';

function vb_ensure_term( $name, $tax ) {
	$term = term_exists( $name, $tax );
	if ( ! $term ) {
		$term = wp_insert_term( $name, $tax );
	}
	if ( is_wp_error( $term ) ) {
		return 0;
	}
	return is_array( $term ) ? (int) $term['term_id'] : (int) $term;
}

$map = array(
	'Arroz'               => 'Arroz',
	'Arroz Integral'      => 'Arroz',
	'Arroz Parboilizado'  => 'Arroz',
	'Feijão'              => 'Feijão',
	'Conservas'           => 'Palmito',
	'Palmito'             => 'Palmito',
	'Laticínios'          => 'Queijo ralado',
	'Queijo ralado'       => 'Queijo ralado',
);

$keep = array(
	'Arroz'         => vb_ensure_term( 'Arroz', $tax ),
	'Feijão'        => vb_ensure_term( 'Feijão', $tax ),
	'Palmito'       => vb_ensure_term( 'Palmito', $tax ),
	'Queijo ralado' => vb_ensure_term( 'Queijo ralado', $tax ),
);

$q = new WP_Query(
	array(
		'post_type'      => 'vb_produto',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	)
);

foreach ( $q->posts as $id ) {
	$title = remove_accents( strtoupper( get_the_title( $id ) ) );
	$cat   = get_post_meta( $id, '_vb_categoria', true );

	if ( ! $cat ) {
		if ( false !== strpos( $title, 'QUEIJO' ) ) {
			$cat = 'Queijo ralado';
		} elseif ( false !== strpos( $title, 'PALM' ) || false !== strpos( $title, 'PUPUNHA' ) ) {
			$cat = 'Palmito';
		} elseif ( false !== strpos( $title, 'FEIJAO' ) ) {
			$cat = 'Feijão';
		} elseif ( false !== strpos( $title, 'ARROZ' ) ) {
			$cat = 'Arroz';
		}
	}

	if ( isset( $map[ $cat ] ) ) {
		$cat = $map[ $cat ];
	} elseif ( $cat && ! isset( $keep[ $cat ] ) ) {
		// Fallback pelo título.
		if ( false !== strpos( $title, 'QUEIJO' ) ) {
			$cat = 'Queijo ralado';
		} elseif ( false !== strpos( $title, 'PALM' ) || false !== strpos( $title, 'PUPUNHA' ) ) {
			$cat = 'Palmito';
		} elseif ( false !== strpos( $title, 'FEIJAO' ) ) {
			$cat = 'Feijão';
		} else {
			$cat = 'Arroz';
		}
	}

	if ( ! $cat || ! isset( $keep[ $cat ] ) ) {
		continue;
	}

	update_post_meta( $id, '_vb_categoria', $cat );
	wp_set_object_terms( $id, array( $keep[ $cat ] ), $tax, false );
}

// Remove termos extras.
$all = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
foreach ( $all as $term ) {
	if ( ! isset( $keep[ $term->name ] ) ) {
		wp_delete_term( $term->term_id, $tax );
		echo "Removido: {$term->name}\n";
	}
}

echo "---\n";
$final = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
foreach ( $final as $t ) {
	echo "{$t->name}\t{$t->count}\n";
}
