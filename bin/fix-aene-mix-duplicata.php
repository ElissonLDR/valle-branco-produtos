<?php
/**
 * Lista produtos ligados à arte Aene Mix e remove duplicatas (mantém 500005).
 *
 * Uso: php bin/fix-aene-mix-duplicata.php
 */
require dirname( __DIR__, 4 ) . '/wp-load.php';

global $wpdb;

$keep_sku = '500005';
$files    = array(
	'Arroz-Aene-Mix-T1-6x5kg.webp',
	'arroz-aene-mix-5kg.webp',
);

$att_ids = array();
foreach ( $files as $f ) {
	$like = '%' . $wpdb->esc_like( $f );
	$rows = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
			$like
		)
	);
	foreach ( $rows as $aid ) {
		$att_ids[ (int) $aid ] = true;
	}
}

// Também busca por trecho no arquivo.
$extra = $wpdb->get_results(
	"SELECT post_id, meta_value FROM {$wpdb->postmeta}
	 WHERE meta_key = '_wp_attached_file'
	   AND (meta_value LIKE '%Aene-Mix%' OR meta_value LIKE '%aene-mix%')"
);
foreach ( $extra as $r ) {
	$att_ids[ (int) $r->post_id ] = true;
	echo "ATT #{$r->post_id} {$r->meta_value}\n";
}

$att_ids = array_keys( $att_ids );
if ( ! $att_ids ) {
	fwrite( STDERR, "Nenhuma imagem Aene Mix encontrada.\n" );
	exit( 1 );
}

$in = implode( ',', array_map( 'intval', $att_ids ) );

// Produtos com thumbnail nesses attachments.
$thumb_posts = $wpdb->get_results(
	"SELECT p.ID, p.post_title, p.post_status, m.meta_value AS thumb
	 FROM {$wpdb->posts} p
	 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_thumbnail_id'
	 WHERE p.post_type = 'vb_produto' AND m.meta_value IN ($in)
	 ORDER BY p.ID ASC"
);

// Produtos com galeria contendo esses IDs.
$all_prods = get_posts(
	array(
		'post_type'      => 'vb_produto',
		'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

echo "=== Com thumbnail Mix ===\n";
$linked = array();
foreach ( $thumb_posts as $row ) {
	$sku = (string) get_post_meta( (int) $row->ID, '_vb_sku', true );
	echo "#{$row->ID} [{$row->post_status}] sku={$sku} {$row->post_title} thumb={$row->thumb}\n";
	$linked[ (int) $row->ID ] = $sku;
}

echo "=== Com galeria Mix ===\n";
foreach ( $all_prods as $pid ) {
	$gal = get_post_meta( $pid, '_vb_galeria', true );
	if ( ! is_array( $gal ) ) {
		continue;
	}
	$hit = array_intersect( array_map( 'intval', $gal ), $att_ids );
	if ( $hit ) {
		$sku = (string) get_post_meta( $pid, '_vb_sku', true );
		echo "#{$pid} [" . get_post_status( $pid ) . "] sku={$sku} " . get_the_title( $pid ) . ' gal=' . implode( ',', $hit ) . "\n";
		$linked[ (int) $pid ] = $sku;
	}
}

// Keep Mix by SKU.
$keep_id = 0;
foreach ( $linked as $pid => $sku ) {
	if ( $keep_sku === $sku ) {
		$keep_id = (int) $pid;
		break;
	}
}
if ( ! $keep_id ) {
	$keep_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_vb_sku' AND m.meta_value = %s
			 WHERE p.post_type = 'vb_produto'
			 ORDER BY FIELD(p.post_status,'publish','draft','private','trash'), p.ID ASC LIMIT 1",
			$keep_sku
		)
	);
}

if ( ! $keep_id ) {
	fwrite( STDERR, "SKU {$keep_sku} (Aene Mix) não encontrado.\n" );
	exit( 1 );
}

echo "KEEP #{$keep_id} " . get_the_title( $keep_id ) . "\n";

// Garantir capa Mix no keep.
$img_mix = 0;
foreach ( $att_ids as $aid ) {
	$file = basename( (string) get_attached_file( $aid ) );
	if ( false !== stripos( $file, 'Mix' ) || false !== stripos( $file, 'mix' ) ) {
		$img_mix = (int) $aid;
		break;
	}
}
if ( $img_mix ) {
	set_post_thumbnail( $keep_id, $img_mix );
	update_post_meta( $keep_id, '_vb_galeria', array() );
	update_post_meta( $keep_id, '_vb_catalogo', '1' );
	wp_update_post(
		array(
			'ID'          => $keep_id,
			'post_status' => 'publish',
			'post_title'  => 'Arroz Aene Mix tipo 1',
		)
	);
}

// Trash others that wrongly use Mix image (not Aene T1 proper products unless they ARE duplicates of mix).
foreach ( $linked as $pid => $sku ) {
	$pid = (int) $pid;
	if ( $pid === $keep_id ) {
		continue;
	}
	// Se for o próprio Mix duplicado (mesmo SKU ou título mix), trash.
	$title = get_the_title( $pid );
	$is_mix_title = ( false !== stripos( $title, 'Mix' ) );
	$is_same_sku  = ( $keep_sku === (string) $sku );

	if ( $is_mix_title || $is_same_sku || '' === (string) $sku ) {
		wp_trash_post( $pid );
		echo "TRASHED #{$pid} sku={$sku} {$title}\n";
		continue;
	}

	// Outro produto usando imagem Mix por engano: remove thumbnail Mix, tenta imagem correta do SKU.
	echo "FIX thumb #{$pid} sku={$sku} {$title}\n";
	delete_post_thumbnail( $pid );
	$map = array(
		'500001' => 'Arroz-Aene-T1-6x5kg.webp',
		'500002' => 'Arroz-Aene-T1-15x2kg.webp',
	);
	if ( isset( $map[ $sku ] ) ) {
		$like = '%' . $wpdb->esc_like( $map[ $sku ] );
		$aid  = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				 WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s
				 ORDER BY post_id DESC LIMIT 1",
				$like
			)
		);
		if ( $aid ) {
			set_post_thumbnail( $pid, $aid );
			echo "  -> thumb #{$aid}\n";
		}
	}
}

echo "OK Mix #{$keep_id} " . get_permalink( $keep_id ) . "\n";
echo 'thumb=' . basename( (string) get_attached_file( (int) get_post_thumbnail_id( $keep_id ) ) ) . "\n";
