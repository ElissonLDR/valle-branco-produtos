<?php
/**
 * Separa catálogo (31) do mapa: marca, publica só o catálogo e tira o resto da vitrine.
 *
 * Uso local:  php bin/separar-catalogo-mapa.php
 * Uso prod:   php separar-catalogo-mapa.php  (com wp-load no mesmo site)
 */
$wp_load_candidates = array(
	dirname( __DIR__, 4 ) . '/wp-load.php',
	dirname( __DIR__, 3 ) . '/wp-load.php',
	__DIR__ . '/wp-load.php',
	dirname( __DIR__ ) . '/wp-load.php',
);
$loaded = false;
foreach ( $wp_load_candidates as $candidate ) {
	if ( is_readable( $candidate ) ) {
		require $candidate;
		$loaded = true;
		break;
	}
}
if ( ! $loaded ) {
	fwrite( STDERR, "wp-load.php nao encontrado\n" );
	exit( 1 );
}

$catalogo_skus = array(
	'500030', '500032', '500040', '500045', '500047', '500080', '500080-5',
	'510021', '510020', '510022', '510031', '510030', '510040',
	'403010', '403011', '403012', '403009', '414001',
	'500020', '500021', '500022', '500023', '500024',
	'510002', '510001', '510003', '510004',
	'500001', '500002', '500005', '500060',
);

$keep = array_fill_keys( $catalogo_skus, true );
$marked_cat = 0;
$demoted    = 0;
$missing    = array();

$all = get_posts(
	array(
		'post_type'      => 'vb_produto',
		'post_status'    => array( 'publish', 'private', 'draft' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

$found_skus = array();
foreach ( $all as $pid ) {
	$sku = (string) get_post_meta( $pid, '_vb_sku', true );
	if ( isset( $keep[ $sku ] ) ) {
		$found_skus[ $sku ] = true;
		update_post_meta( $pid, '_vb_catalogo', '1' );
		delete_post_meta( $pid, '_vb_origem' );
		if ( 'publish' !== get_post_status( $pid ) ) {
			wp_update_post(
				array(
					'ID'          => $pid,
					'post_status' => 'publish',
				)
			);
		}
		++$marked_cat;
		echo "CATALOGO #{$pid} [{$sku}]\n";
		continue;
	}

	update_post_meta( $pid, '_vb_origem', 'mapa' );
	delete_post_meta( $pid, '_vb_catalogo' );
	$status = get_post_status( $pid );
	if ( 'publish' === $status ) {
		wp_update_post(
			array(
				'ID'          => $pid,
				'post_status' => 'private',
			)
		);
		++$demoted;
		echo "MAPA/PRIVATE #{$pid} [{$sku}]\n";
	} else {
		echo "MAPA #{$pid} [{$sku}] status={$status}\n";
	}
}

foreach ( $catalogo_skus as $sku ) {
	if ( empty( $found_skus[ $sku ] ) ) {
		$missing[] = $sku;
	}
}

$counts = wp_count_posts( 'vb_produto' );
echo "---\n";
echo 'Catalogo marcados: ' . $marked_cat . "\n";
echo 'Removidos da vitrine (private): ' . $demoted . "\n";
echo 'Publicados agora: ' . (int) $counts->publish . "\n";
echo 'Privados agora: ' . (int) $counts->private . "\n";
if ( $missing ) {
	echo 'SKUs do catalogo ausentes: ' . implode( ', ', $missing ) . "\n";
	exit( 2 );
}
if ( (int) $counts->publish !== count( $catalogo_skus ) ) {
	echo "AVISO: esperava " . count( $catalogo_skus ) . " publicados.\n";
	exit( 3 );
}
echo "OK: vitrine com " . count( $catalogo_skus ) . " produtos do catalogo.\n";
