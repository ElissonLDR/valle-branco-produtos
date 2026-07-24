<?php
/**
 * Sincroniza produtos com o Catálogo Valle Branco 2024.
 * Mantém só as linhas do PDF; exclui o restante.
 *
 * Uso: php bin/sync-catalogo-2024.php
 */
require 'C:/xampp/htdocs/valle-branco/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$assets = 'C:/Users/eliss/Desktop/V4 Company/02. SITES/VALLE BRANCO/site-valle-branco/src/assets';

/**
 * Catálogo 2024 — uma vitrine por linha de produto (pesos agregados).
 * Fonte: CATALAGO VALLE BRANCO ATUAL 2026.pdf (versão 2024).
 */
$catalogo = array(
	array(
		'slug'   => 'arroz-extra-premium-valle-branco-tipo-1',
		'title'  => 'Arroz Extra Premium Valle Branco tipo 1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Arroz',
		'pesos'  => array( '2kg', '5kg' ),
		'sku'    => '500030',
		'image'  => 'arroz-extra-premium-5kg.webp',
		'order'  => 10,
	),
	array(
		'slug'   => 'arroz-integral-valle-branco-tipo-1',
		'title'  => 'Arroz Integral Valle Branco tipo 1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Arroz',
		'pesos'  => array( '1kg' ),
		'sku'    => '500040',
		'image'  => 'arroz-integral-1kg.webp',
		'order'  => 20,
	),
	array(
		'slug'   => 'arroz-parboilizado-valle-branco-tipo-1',
		'title'  => 'Arroz Parboilizado Valle Branco tipo 1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Arroz',
		'pesos'  => array( '1kg', '5kg' ),
		'sku'    => '500045',
		'image'  => 'arroz-parboilizado-5kg.webp',
		'order'  => 30,
	),
	array(
		'slug'   => 'arroz-arborio-valle-branco',
		'title'  => 'Arroz Arbório Valle Branco',
		'marca'  => 'Valle Branco',
		'cat'    => 'Arroz',
		'pesos'  => array( '1kg' ),
		'sku'    => '500080',
		'image'  => 'arroz-extra-premium-5kg.webp', // placeholder até arte oficial
		'order'  => 40,
	),
	array(
		'slug'   => 'feijao-carioca-valle-branco-tipo-1',
		'title'  => 'Feijão Carioca Valle Branco tipo 1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Feijão',
		'pesos'  => array( '1kg', '2kg' ),
		'sku'    => '510020',
		'image'  => 'feijao-carioca-1kg.webp',
		'order'  => 50,
	),
	array(
		'slug'   => 'feijao-preto-valle-branco-tipo-1',
		'title'  => 'Feijão Preto Valle Branco tipo 1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Feijão',
		'pesos'  => array( '1kg' ),
		'sku'    => '510030',
		'image'  => 'feijao-preto-1kg.webp',
		'order'  => 60,
	),
	array(
		'slug'   => 'feijao-bolinha-valle-branco-tipo-1',
		'title'  => 'Feijão Bolinha Valle Branco tipo 1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Feijão',
		'pesos'  => array( '1kg' ),
		'sku'    => '510040',
		'image'  => 'feijao-bolinha-1kg.webp',
		'order'  => 70,
	),
	array(
		'slug'   => 'palmito-valle-branco-pupunha-inteiro-300g',
		'title'  => 'Palmito Valle Branco Pupunha Inteiro',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'pesos'  => array( '300g' ),
		'sku'    => '403010',
		'image'  => 'palmito-inteiro-300g.webp',
		'order'  => 78,
	),
	array(
		'slug'   => 'palmito-valle-branco-pupunha-inteiro-180g',
		'title'  => 'Palmito Valle Branco Pupunha Inteiro',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'pesos'  => array( '180g' ),
		'sku'    => '403009',
		'image'  => 'palmito-inteiro-300g.webp',
		'order'  => 79,
	),
	array(
		'slug'   => 'palmito-valle-branco-pupunha-picado-300g',
		'title'  => 'Palmito Valle Branco Pupunha Picado',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'pesos'  => array( '300g' ),
		'sku'    => '403012',
		'image'  => 'palmito-picado-300g.webp',
		'order'  => 80,
	),
	array(
		'slug'   => 'palmito-valle-branco-pupunha-rodelas-300g',
		'title'  => 'Palmito Valle Branco Pupunha Rodelas',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'pesos'  => array( '300g' ),
		'sku'    => '403011',
		'image'  => 'palmito-rodelas-300g.webp',
		'order'  => 82,
	),
	array(
		'slug'   => 'queijo-ralado-valle-branco-fiapo-40g',
		'title'  => 'Queijo Ralado Valle Branco Fiapo',
		'marca'  => 'Valle Branco',
		'cat'    => 'Queijo ralado',
		'pesos'  => array( '40g' ),
		'sku'    => '414001',
		'image'  => 'queijo-ralado-40g.webp',
		'order'  => 75,
	),
	array(
		'slug'   => 'arroz-castelao-tipo-1',
		'title'  => 'Arroz Castelão tipo 1',
		'marca'  => 'Castelão',
		'cat'    => 'Arroz',
		'pesos'  => array( '2kg', '5kg' ),
		'sku'    => '500020',
		'image'  => 'arroz-castelao-5kg.webp',
		'order'  => 100,
	),
	array(
		'slug'   => 'arroz-castelao-tipo-2',
		'title'  => 'Arroz Castelão tipo 2',
		'marca'  => 'Castelão',
		'cat'    => 'Arroz',
		'pesos'  => array( '5kg' ),
		'sku'    => '500022',
		'image'  => 'arroz-castelao-5kg.webp',
		'order'  => 110,
	),
	array(
		'slug'   => 'arroz-castelao-tipo-3',
		'title'  => 'Arroz Castelão tipo 3',
		'marca'  => 'Castelão',
		'cat'    => 'Arroz',
		'pesos'  => array( '5kg' ),
		'sku'    => '500023',
		'image'  => 'arroz-castelao-5kg.webp',
		'order'  => 120,
	),
	array(
		'slug'   => 'arroz-castelao-serie-ouro-tipo-1',
		'title'  => 'Arroz Castelão Série Ouro tipo 1',
		'marca'  => 'Castelão',
		'cat'    => 'Arroz',
		'pesos'  => array( '5kg' ),
		'sku'    => '500024',
		'image'  => 'arroz-castelao-5kg.webp',
		'order'  => 130,
	),
	array(
		'slug'   => 'feijao-castelao-tipo-1',
		'title'  => 'Feijão Castelão tipo 1',
		'marca'  => 'Castelão',
		'cat'    => 'Feijão',
		'pesos'  => array( '1kg', '2kg' ),
		'sku'    => '510001',
		'image'  => 'feijao-carioca-1kg.webp',
		'order'  => 140,
	),
	array(
		'slug'   => 'feijao-castelao-economico-tipo-1',
		'title'  => 'Feijão Castelão Econômico tipo 1',
		'marca'  => 'Castelão',
		'cat'    => 'Feijão',
		'pesos'  => array( '1kg' ),
		'sku'    => '510004',
		'image'  => 'feijao-castelao-economico-1kg.webp',
		'order'  => 150,
	),
	array(
		'slug'   => 'arroz-aene-tipo-1',
		'title'  => 'Arroz Aene tipo 1',
		'marca'  => 'Aene',
		'cat'    => 'Arroz',
		'pesos'  => array( '2kg', '5kg' ),
		'sku'    => '500001',
		'image'  => 'arroz-aene-mix-5kg.webp',
		'order'  => 160,
	),
	array(
		'slug'   => 'arroz-aene-mix-tipo-1',
		'title'  => 'Arroz Aene Mix tipo 1',
		'marca'  => 'Aene',
		'cat'    => 'Arroz',
		'pesos'  => array( '5kg' ),
		'sku'    => '500005',
		'image'  => 'arroz-aene-mix-5kg.webp',
		'order'  => 170,
	),
	array(
		'slug'   => 'arroz-vita-abaixo-padrao',
		'title'  => 'Arroz Vita Abaixo Padrão',
		'marca'  => 'Vita',
		'cat'    => 'Arroz',
		'pesos'  => array( '5kg' ),
		'sku'    => '500060',
		'image'  => 'arroz-vita-abaixo-5kg.webp',
		'order'  => 180,
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

function vb_ensure_attachment( $filepath, $filename ) {
	if ( ! file_exists( $filepath ) ) {
		return 0;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
			'meta_key'       => '_wp_attached_file',
			'meta_value'     => $filename,
			'fields'         => 'ids',
		)
	);
	// Busca por título/nome do arquivo.
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
	$meta = wp_generate_attachment_metadata( $att_id, $upload['file'] );
	wp_update_attachment_metadata( $att_id, $meta );
	return (int) $att_id;
}

// 1) Excluir (lixeira) todos os produtos atuais.
$old = get_posts(
	array(
		'post_type'      => 'vb_produto',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
	)
);
foreach ( $old as $oid ) {
	wp_trash_post( $oid );
}
echo 'Movidos para lixeira: ' . count( $old ) . "\n";

// 2) Categorias do filtro (sem Queijo — fora do catálogo 2024).
$cats_keep = array( 'Arroz', 'Feijão', 'Palmito' );
foreach ( $cats_keep as $cn ) {
	vb_ensure_term_id( $cn, 'vb_categoria_produto' );
}
$all_cats = get_terms( array( 'taxonomy' => 'vb_categoria_produto', 'hide_empty' => false ) );
foreach ( $all_cats as $t ) {
	if ( ! in_array( $t->name, $cats_keep, true ) ) {
		wp_delete_term( $t->term_id, 'vb_categoria_produto' );
		echo "Categoria removida: {$t->name}\n";
	}
}

$marcas_keep = array( 'Valle Branco', 'Castelão', 'Aene', 'Vita' );
foreach ( $marcas_keep as $mn ) {
	vb_ensure_term_id( $mn, 'vb_marca' );
}

// 3) Criar produtos do catálogo.
$created = 0;
foreach ( $catalogo as $item ) {
	$pid = wp_insert_post(
		array(
			'post_type'    => 'vb_produto',
			'post_status'  => 'publish',
			'post_title'   => $item['title'],
			'post_name'    => $item['slug'],
			'post_content' => '',
			'menu_order'   => $item['order'],
		),
		true
	);
	if ( is_wp_error( $pid ) || ! $pid ) {
		echo 'Erro: ' . $item['title'] . "\n";
		continue;
	}

	update_post_meta( $pid, '_vb_sku', $item['sku'] );
	update_post_meta( $pid, '_vb_marca', $item['marca'] );
	update_post_meta( $pid, '_vb_categoria', $item['cat'] );
	update_post_meta( $pid, '_vb_pesos', implode( ', ', $item['pesos'] ) );

	$tid_m = vb_ensure_term_id( $item['marca'], 'vb_marca' );
	$tid_c = vb_ensure_term_id( $item['cat'], 'vb_categoria_produto' );
	if ( $tid_m ) {
		wp_set_object_terms( $pid, array( $tid_m ), 'vb_marca', false );
	}
	if ( $tid_c ) {
		wp_set_object_terms( $pid, array( $tid_c ), 'vb_categoria_produto', false );
	}

	$path = $assets . '/' . $item['image'];
	$att  = vb_ensure_attachment( $path, $item['image'] );
	if ( $att ) {
		set_post_thumbnail( $pid, $att );
	}

	++$created;
	echo "OK #{$pid} {$item['title']}\n";
}

echo "---\nCriados: {$created}\n";
echo 'Publicados: ' . wp_count_posts( 'vb_produto' )->publish . "\n";
