<?php
/**
 * Corrige títulos: restaura linha Castelão/Aene/Vita; remove só “Valle Branco”.
 */
require dirname( __DIR__, 4 ) . '/wp-load.php';

$map = array(
	500020 => 'Arroz Castelão tipo 1',
	500022 => 'Arroz Castelão tipo 2',
	500023 => 'Arroz Castelão tipo 3',
	500024 => 'Arroz Castelão Série Ouro tipo 1',
	510002 => 'Feijão Castelão tipo 1',
	510004 => 'Feijão Castelão Econômico tipo 1',
	500001 => 'Arroz Aene tipo 1',
	500005 => 'Arroz Aene Mix tipo 1',
	500060 => 'Arroz Vita Abaixo Padrão',
);

foreach ( $map as $sku => $title ) {
	$ids = get_posts(
		array(
			'post_type'      => 'vb_produto',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_vb_sku',
			'meta_value'     => (string) $sku,
		)
	);
	if ( empty( $ids ) ) {
		echo "MISSING {$sku}\n";
		continue;
	}
	$id = (int) $ids[0];
	wp_update_post(
		array(
			'ID'         => $id,
			'post_title' => $title,
		)
	);
	echo "OK #{$id} [{$sku}] {$title}\n";
}
