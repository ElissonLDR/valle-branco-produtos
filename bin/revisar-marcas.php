<?php
/**
 * Audita e corrige marcas (tax vb_marca) dos produtos do catálogo.
 */
require dirname( __DIR__, 4 ) . '/wp-load.php';

$esperado = array(
	'500030' => 'Valle Branco',
	'500040' => 'Valle Branco',
	'500045' => 'Valle Branco',
	'500080' => 'Valle Branco',
	'510021' => 'Valle Branco',
	'510031' => 'Valle Branco',
	'510040' => 'Valle Branco',
	'414001' => 'Valle Branco',
	'403010' => 'Valle Branco',
	'403012' => 'Valle Branco',
	'403011' => 'Valle Branco',
	'500020' => 'Castelão',
	'500022' => 'Castelão',
	'500023' => 'Castelão',
	'500024' => 'Castelão',
	'510002' => 'Castelão',
	'510004' => 'Castelão',
	'500001' => 'Aene',
	'500005' => 'Aene',
	'500060' => 'Vita',
);

$ids = get_posts(
	array(
		'post_type'      => 'vb_produto',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'   => '_vb_catalogo',
				'value' => '1',
			),
		),
	)
);

$fix = isset( $_SERVER['argv'][1] ) && 'fix' === $_SERVER['argv'][1];

echo $fix ? "MODE=FIX\n" : "MODE=AUDIT\n";

foreach ( $ids as $p ) {
	$id    = $p->ID;
	$sku   = (string) get_post_meta( $id, '_vb_sku', true );
	$atual = VB_Prod_Product::get_marca_nome( $id );
	$meta  = (string) get_post_meta( $id, '_vb_marca', true );
	$terms = wp_get_post_terms( $id, 'vb_marca', array( 'fields' => 'names' ) );
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}
	$want = isset( $esperado[ $sku ] ) ? $esperado[ $sku ] : '';

	$ok = $want && 0 === strcasecmp( $atual, $want ) && count( $terms ) === 1;
	$status = $ok ? 'OK' : 'ERR';
	echo "[{$status}] #{$id} sku={$sku} title={$p->post_title}\n";
	echo "  atual={$atual} meta={$meta} terms=[" . implode( ', ', $terms ) . "] esperado={$want}\n";

	if ( $fix && $want && ! $ok ) {
		$term = term_exists( $want, 'vb_marca' );
		if ( ! $term ) {
			$term = wp_insert_term( $want, 'vb_marca' );
		}
		if ( is_wp_error( $term ) ) {
			echo "  FAIL term: " . $term->get_error_message() . "\n";
			continue;
		}
		$tid = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
		wp_set_object_terms( $id, array( $tid ), 'vb_marca', false );
		update_post_meta( $id, '_vb_marca', $want );
		$novo = VB_Prod_Product::get_marca_nome( $id );
		echo "  FIXED -> {$novo}\n";
	}
}
