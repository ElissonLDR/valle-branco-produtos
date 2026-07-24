<?php
require 'C:/xampp/htdocs/valle-branco/wp-load.php';

$q = new WP_Query(
	array(
		'post_type'      => 'vb_produto',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);

foreach ( $q->posts as $p ) {
	$pesos = get_post_meta( $p->ID, '_vb_pesos', true );
	if ( is_array( $pesos ) ) {
		$pesos = implode( ', ', $pesos );
		update_post_meta( $p->ID, '_vb_pesos', $pesos );
	}
	$thumb = has_post_thumbnail( $p->ID ) ? 'img' : 'NO-IMG';
	echo $p->ID . "\t" . $thumb . "\t" . get_post_meta( $p->ID, '_vb_sku', true ) . "\t" . get_post_meta( $p->ID, '_vb_marca', true ) . "\t" . get_post_meta( $p->ID, '_vb_categoria', true ) . "\t" . $pesos . "\t" . $p->post_title . PHP_EOL;
}
echo 'TOTAL: ' . count( $q->posts ) . PHP_EOL;

$trash = get_posts( array( 'post_type' => 'vb_produto', 'post_status' => 'trash', 'posts_per_page' => -1, 'fields' => 'ids' ) );
echo 'Lixeira: ' . count( $trash ) . PHP_EOL;
