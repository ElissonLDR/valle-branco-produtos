<?php
/**
 * Adiciona palmitos e queijo do catálogo 2024 (páginas extras).
 */
require 'C:/xampp/htdocs/valle-branco/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$assets = 'C:/Users/eliss/Desktop/V4 Company/02. SITES/VALLE BRANCO/site-valle-branco/src/assets';

$itens = array(
	array(
		'sku'    => '403010',
		'slug'   => 'palmito-valle-branco-pupunha-inteiro-300g',
		'title'  => 'Palmito Valle Branco Pupunha Inteiro',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'pesos'  => '300g',
		'image'  => 'palmito-inteiro-300g.webp',
		'order'  => 78,
	),
	array(
		'sku'    => '403011',
		'slug'   => 'palmito-valle-branco-pupunha-rodelas-300g',
		'title'  => 'Palmito Valle Branco Pupunha Rodelas',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'pesos'  => '300g',
		'image'  => 'palmito-rodelas-300g.webp',
		'order'  => 82,
	),
	array(
		'sku'    => '403012',
		'slug'   => 'palmito-valle-branco-pupunha-picado-300g',
		'title'  => 'Palmito Valle Branco Pupunha Picado',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'pesos'  => '300g',
		'image'  => 'palmito-picado-300g.webp',
		'order'  => 80,
	),
	array(
		'sku'    => '403009',
		'slug'   => 'palmito-valle-branco-pupunha-inteiro-180g',
		'title'  => 'Palmito Valle Branco Pupunha Inteiro',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'pesos'  => '180g',
		'image'  => 'palmito-inteiro-300g.webp', // placeholder até arte 180g
		'order'  => 79,
	),
	array(
		'sku'    => '414001',
		'slug'   => 'queijo-ralado-valle-branco-fiapo-40g',
		'title'  => 'Queijo Ralado Valle Branco Fiapo',
		'marca'  => 'Valle Branco',
		'cat'    => 'Queijo ralado',
		'pesos'  => '40g',
		'image'  => 'queijo-ralado-40g.webp',
		'order'  => 75,
	),
);

function vb_ensure_term_id( $name, $tax ) {
	$term = term_exists( $name, $tax );
	if ( ! $term ) {
		$term = wp_insert_term( $name, $tax );
	}
	if ( is_wp_error( $term ) ) {
		return 0;
	}
	return is_array( $term ) ? (int) $term['term_id'] : (int) $term;
}

function vb_find_by_sku( $sku ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'vb_produto',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_vb_sku',
			'meta_value'     => $sku,
		)
	);
	return $q->posts ? (int) $q->posts[0] : 0;
}

function vb_ensure_attachment( $filepath, $filename ) {
	if ( ! file_exists( $filepath ) ) {
		echo "Sem arquivo: {$filename}\n";
		return 0;
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
			's'              => pathinfo( $filename, PATHINFO_FILENAME ),
			'fields'         => 'ids',
		)
	);
	if ( $q->posts ) {
		return (int) $q->posts[0];
	}
	$upload = wp_upload_bits( $filename, null, file_get_contents( $filepath ) );
	if ( ! empty( $upload['error'] ) ) {
		echo "Erro upload {$filename}: {$upload['error']}\n";
		return 0;
	}
	$filetype = wp_check_filetype( $filename );
	$att_id   = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	if ( is_wp_error( $att_id ) || ! $att_id ) {
		return 0;
	}
	wp_update_attachment_metadata( $att_id, wp_generate_attachment_metadata( $att_id, $upload['file'] ) );
	return (int) $att_id;
}

// Garante categorias.
vb_ensure_term_id( 'Palmito', 'vb_categoria_produto' );
vb_ensure_term_id( 'Queijo ralado', 'vb_categoria_produto' );
vb_ensure_term_id( 'Valle Branco', 'vb_marca' );

$ok = 0;
foreach ( $itens as $item ) {
	$pid = vb_find_by_sku( $item['sku'] );
	$data = array(
		'post_type'    => 'vb_produto',
		'post_status'  => 'publish',
		'post_title'   => $item['title'],
		'post_name'    => $item['slug'],
		'menu_order'   => $item['order'],
	);

	if ( $pid ) {
		$data['ID'] = $pid;
		wp_update_post( $data );
		$action = 'UPD';
	} else {
		$pid = wp_insert_post( $data, true );
		if ( is_wp_error( $pid ) || ! $pid ) {
			echo "Erro: {$item['title']}\n";
			continue;
		}
		$action = 'NEW';
	}

	update_post_meta( $pid, '_vb_sku', $item['sku'] );
	update_post_meta( $pid, '_vb_marca', $item['marca'] );
	update_post_meta( $pid, '_vb_categoria', $item['cat'] );
	update_post_meta( $pid, '_vb_pesos', $item['pesos'] );

	$tid_m = vb_ensure_term_id( $item['marca'], 'vb_marca' );
	$tid_c = vb_ensure_term_id( $item['cat'], 'vb_categoria_produto' );
	if ( $tid_m ) {
		wp_set_object_terms( $pid, array( $tid_m ), 'vb_marca', false );
	}
	if ( $tid_c ) {
		wp_set_object_terms( $pid, array( $tid_c ), 'vb_categoria_produto', false );
	}

	$att = vb_ensure_attachment( $assets . '/' . $item['image'], $item['image'] );
	if ( $att ) {
		set_post_thumbnail( $pid, $att );
	}

	++$ok;
	echo "{$action} #{$pid} [{$item['sku']}] {$item['title']} ({$item['pesos']})\n";
}

// Remove o post antigo genérico "Inteiro" se ainda existir com slug antigo e SKU errado já corrigido.
$dup = get_page_by_path( 'palmito-valle-branco-pupunha-inteiro', OBJECT, 'vb_produto' );
if ( $dup && (string) get_post_meta( $dup->ID, '_vb_sku', true ) === '403010' ) {
	// Já atualizado via SKU; só ajusta slug se necessário.
	wp_update_post(
		array(
			'ID'        => $dup->ID,
			'post_name' => 'palmito-valle-branco-pupunha-inteiro-300g',
		)
	);
}

echo "---\nProcessados: {$ok}\n";
$count = wp_count_posts( 'vb_produto' );
echo 'Publicados: ' . (int) $count->publish . "\n";

$terms = get_terms( array( 'taxonomy' => 'vb_categoria_produto', 'hide_empty' => false ) );
foreach ( $terms as $t ) {
	echo "Cat: {$t->name} ({$t->count})\n";
}
