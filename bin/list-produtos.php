<?php
require 'C:/xampp/htdocs/valle-branco/wp-load.php';

$q = new WP_Query(
	array(
		'post_type'      => 'vb_produto',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);

foreach ( $q->posts as $p ) {
	$sku   = get_post_meta( $p->ID, '_vb_sku', true );
	$marca = get_post_meta( $p->ID, '_vb_marca', true );
	$cat   = get_post_meta( $p->ID, '_vb_categoria', true );
	$pesos = get_post_meta( $p->ID, '_vb_pesos', true );
	if ( is_array( $pesos ) ) {
		$pesos = implode( ', ', $pesos );
	}
	echo $p->ID . "\t" . $p->post_status . "\t[" . $sku . "]\t" . $marca . "\t" . $cat . "\t" . $pesos . "\t" . $p->post_title . PHP_EOL;
}
echo 'TOTAL: ' . count( $q->posts ) . PHP_EOL;
