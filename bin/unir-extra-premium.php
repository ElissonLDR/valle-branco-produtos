<?php
/**
 * Une Extra Premium 5kg + 2kg em 1 produto com 2 tabelas e 2 imagens.
 *
 * Uso: php bin/unir-extra-premium.php
 */
require dirname( __DIR__, 4 ) . '/wp-load.php';

/**
 * @param string $sku SKU.
 * @return int
 */
function vb_find_sku( $sku ) {
	global $wpdb;
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_vb_sku'
			 WHERE p.post_type = 'vb_produto' AND m.meta_value = %s
			 ORDER BY FIELD(p.post_status,'publish','draft','private','trash'), p.ID ASC
			 LIMIT 1",
			$sku
		)
	);
}

/**
 * @param string $filename Arquivo.
 * @return int
 */
function vb_find_att( $filename ) {
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $filename );
	$id   = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta}
			 WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s
			 ORDER BY post_id DESC LIMIT 1",
			$like
		)
	);
	if ( $id ) {
		return $id;
	}
	// Fallback: busca parcial.
	$base = pathinfo( $filename, PATHINFO_FILENAME );
	$posts = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 30,
			's'              => $base,
		)
	);
	foreach ( $posts as $p ) {
		$file = (string) get_attached_file( $p->ID );
		if ( false !== stripos( basename( $file ), '15x2kg' ) && false !== stripos( $filename, '15x2kg' ) ) {
			return (int) $p->ID;
		}
		if ( false !== stripos( basename( $file ), '6x5kg' ) && false !== stripos( $filename, '6x5kg' ) ) {
			return (int) $p->ID;
		}
		if ( 0 === strcasecmp( basename( $file ), $filename ) ) {
			return (int) $p->ID;
		}
	}
	return 0;
}

/**
 * @param string $titulo Título da tabela.
 * @param string $estilo azul|ouro.
 * @param array  $rows   Campo => valor.
 * @return array
 */
function vb_tab( $titulo, $estilo, $rows ) {
	$linhas = array();
	foreach ( $rows as $k => $v ) {
		if ( '' === (string) $v ) {
			continue;
		}
		$linhas[] = array(
			'campo' => (string) $k,
			'valor' => (string) $v,
		);
	}
	return array(
		'titulo' => $titulo,
		'estilo' => $estilo,
		'linhas' => $linhas,
	);
}

$keep_id = vb_find_sku( '500030' );
$dup_id  = vb_find_sku( '500032' );

if ( ! $keep_id ) {
	fwrite( STDERR, "SKU 500030 não encontrado.\n" );
	exit( 1 );
}

echo "KEEP #{$keep_id} " . get_the_title( $keep_id ) . ' [' . get_post_status( $keep_id ) . "]\n";
if ( $dup_id ) {
	echo "DUP  #{$dup_id} " . get_the_title( $dup_id ) . ' [' . get_post_status( $dup_id ) . "]\n";
} else {
	echo "DUP 500032 não encontrado (ok se já unido).\n";
}

$img_5 = vb_find_att( 'Arroz-Extra-Premium-Valle-Branco-T1-6x5kg.webp' );
$img_2 = vb_find_att( 'Arroz-Extra-Premium-Valle-Branco-T1-15x2kg.webp' );

// Fallbacks comuns.
if ( ! $img_5 ) {
	$img_5 = vb_find_att( 'arroz-extra-premium-5kg.webp' );
}
if ( ! $img_2 ) {
	$img_2 = (int) get_post_thumbnail_id( $dup_id );
}
if ( ! $img_5 ) {
	$img_5 = (int) get_post_thumbnail_id( $keep_id );
}

echo "IMG 5kg={$img_5} " . ( $img_5 ? wp_get_attachment_url( $img_5 ) : 'MISSING' ) . "\n";
echo "IMG 2kg={$img_2} " . ( $img_2 ? wp_get_attachment_url( $img_2 ) : 'MISSING' ) . "\n";

if ( ! $img_5 || ! $img_2 ) {
	fwrite( STDERR, "Faltam imagens. Abortando.\n" );
	exit( 1 );
}

// 2 tabelas de Pacote — uma por SKU (dados do catálogo 2024).
$nutricao = array(
	'tabelas' => array(
		vb_tab(
			'Pacote 6x5kg (SKU 500030)',
			'azul',
			array(
				'Marca'           => 'Valle Branco',
				'Categoria'       => 'Arroz',
				'Classe'          => 'Longo Fino',
				'Tipo'            => '1',
				'Peso Líquido'    => '5kg',
				'Peso Bruto'      => '5,018kg',
				'Altura'          => '7 cm',
				'Largura'         => '24 cm',
				'Profundidade'    => '36 cm',
				'Embalagem'       => '6x5kg',
				'GTIN'            => '7896397900015',
				'Código Interno'  => '500030',
				'Validade'        => '6 meses',
				'NCM'             => '1006.30.21',
			)
		),
		vb_tab(
			'Pacote 15x2kg (SKU 500032)',
			'azul',
			array(
				'Marca'           => 'Valle Branco',
				'Categoria'       => 'Arroz',
				'Classe'          => 'Longo Fino',
				'Tipo'            => '1',
				'Peso Líquido'    => '2kg',
				'Peso Bruto'      => '2,010kg',
				'Altura'          => '5 cm',
				'Largura'         => '17 cm',
				'Profundidade'    => '27 cm',
				'Embalagem'       => '15x2kg',
				'GTIN'            => '7896397900039',
				'Código Interno'  => '500032',
				'Validade'        => '6 meses',
				'NCM'             => '1006.30.21',
			)
		),
	),
);

$vars = array(
	array(
		'sku'       => '500030',
		'peso'      => '5kg',
		'embalagem' => '6x5kg',
		'gtin'      => '7896397900015',
	),
	array(
		'sku'       => '500032',
		'peso'      => '2kg',
		'embalagem' => '15x2kg',
		'gtin'      => '7896397900039',
	),
);

wp_update_post(
	array(
		'ID'         => $keep_id,
		'post_title' => 'Arroz Extra Premium Valle Branco tipo 1',
		'post_name'  => 'arroz-extra-premium-valle-branco-tipo-1',
		'post_status'=> 'publish',
	)
);

update_post_meta( $keep_id, '_vb_catalogo', '1' );
update_post_meta( $keep_id, '_vb_sku', '500030' );
update_post_meta( $keep_id, '_vb_pesos', array( '5kg', '2kg' ) );
update_post_meta( $keep_id, '_vb_embalagens', array( '6x5kg', '15x2kg' ) );
update_post_meta( $keep_id, '_vb_variacoes', $vars );
update_post_meta( $keep_id, '_vb_nutricao', VB_Prod_Meta::sanitize_nutricao( $nutricao ) );
// Capa = 5kg; galeria = 2kg → carrossel com as 2 imagens.
update_post_meta( $keep_id, '_vb_galeria', array( $img_2 ) );

set_post_thumbnail( $keep_id, $img_5 );

if ( $dup_id && $dup_id !== $keep_id ) {
	wp_trash_post( $dup_id );
	echo "TRASHED #{$dup_id}\n";
}

echo "OK #{$keep_id}\n";
echo 'tabelas=' . count( get_post_meta( $keep_id, '_vb_nutricao', true )['tabelas'] ?? array() ) . "\n";
echo 'galeria=' . implode( ',', (array) get_post_meta( $keep_id, '_vb_galeria', true ) ) . "\n";
echo 'permalink=' . get_permalink( $keep_id ) . "\n";
