<?php
/**
 * Corrige capas Castelão T2/T3/Série Ouro para as artes específicas.
 */
require dirname( __DIR__, 4 ) . '/wp-load.php';

$map = array(
	'500022' => 'Arroz-Castelao-T2-6x5kg.webp',
	'500023' => 'Arroz-Castelao-T3-6x5kg.webp',
	'500024' => 'Arroz-Castelao-Serie-Ouro-T1-6x5kg.webp',
);

/**
 * @param string $filename Arquivo.
 * @return int
 */
function vb_att( $filename ) {
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $filename );
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s
			 ORDER BY post_id DESC LIMIT 1",
			$like
		)
	);
}

/**
 * @param string $sku SKU.
 * @return int
 */
function vb_sku( $sku ) {
	global $wpdb;
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_vb_sku' AND m.meta_value = %s
			 WHERE p.post_type = 'vb_produto' AND p.post_status = 'publish' LIMIT 1",
			$sku
		)
	);
}

foreach ( $map as $sku => $file ) {
	$id  = vb_sku( $sku );
	$aid = vb_att( $file );
	echo "SKU {$sku} post=#{$id} att=#{$aid} {$file}\n";
	if ( $id && $aid ) {
		set_post_thumbnail( $id, $aid );
		echo "  OK thumb " . wp_get_attachment_url( $aid ) . "\n";
		echo "  short=" . VB_Prod_Product::get_titulo_destaque( $id ) . "\n";
	} else {
		echo "  FAIL\n";
	}
}
