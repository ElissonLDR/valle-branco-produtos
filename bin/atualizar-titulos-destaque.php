<?php
/**
 * Atualiza post_title removendo a marca (fica só o destaque).
 */
require dirname( __DIR__, 4 ) . '/wp-load.php';

$ids = get_posts(
	array(
		'post_type'      => 'vb_produto',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'   => '_vb_catalogo',
				'value' => '1',
			),
		),
	)
);

$n = 0;
foreach ( $ids as $id ) {
	$old   = get_the_title( $id );
	$clean = VB_Prod_Product::get_titulo_destaque( $id );
	if ( ! $clean || $clean === $old ) {
		echo "SKIP #{$id} {$old}\n";
		continue;
	}
	wp_update_post(
		array(
			'ID'         => $id,
			'post_title' => $clean,
		)
	);
	++$n;
	echo "UPD #{$id}: {$old} → {$clean}\n";
}
echo "---\nAtualizados: {$n}\n";
