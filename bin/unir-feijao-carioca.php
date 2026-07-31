<?php
/**
 * Une Feijão Carioca 15x2kg + 30x1kg + 10x1kg em 1 produto (3 tabelas, 3 imagens).
 *
 * Uso: php bin/unir-feijao-carioca.php
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
	$posts = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 30,
			's'              => pathinfo( $filename, PATHINFO_FILENAME ),
		)
	);
	foreach ( $posts as $p ) {
		if ( 0 === strcasecmp( basename( (string) get_attached_file( $p->ID ) ), $filename ) ) {
			return (int) $p->ID;
		}
	}
	return 0;
}

/**
 * @param string $titulo Título.
 * @param string $estilo Estilo.
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

$keep_id = vb_find_sku( '510021' );
$dup_a   = vb_find_sku( '510020' );
$dup_b   = vb_find_sku( '510022' );

if ( ! $keep_id ) {
	fwrite( STDERR, "SKU 510021 não encontrado.\n" );
	exit( 1 );
}

echo "KEEP #{$keep_id} " . get_the_title( $keep_id ) . ' [' . get_post_status( $keep_id ) . "]\n";
foreach ( array( $dup_a, $dup_b ) as $d ) {
	if ( $d ) {
		echo "DUP  #{$d} " . get_the_title( $d ) . ' [' . get_post_status( $d ) . "]\n";
	}
}

$img_2  = vb_find_att( 'Feijao-Carioca-Valle-Branco-T1-15x2kg.webp' );
$img_30 = vb_find_att( 'Feijao-Carioca-Valle-Branco-T1-30x1kg.webp' );
$img_10 = vb_find_att( 'Feijao-Carioca-Valle-Branco-T1-10x1kg.webp' );

if ( ! $img_2 ) {
	$img_2 = (int) get_post_thumbnail_id( $keep_id );
}
if ( ! $img_30 && $dup_a ) {
	$img_30 = (int) get_post_thumbnail_id( $dup_a );
}
if ( ! $img_10 && $dup_b ) {
	$img_10 = (int) get_post_thumbnail_id( $dup_b );
}

echo "IMG 15x2={$img_2} " . ( $img_2 ? wp_get_attachment_url( $img_2 ) : 'MISSING' ) . "\n";
echo "IMG 30x1={$img_30} " . ( $img_30 ? wp_get_attachment_url( $img_30 ) : 'MISSING' ) . "\n";
echo "IMG 10x1={$img_10} " . ( $img_10 ? wp_get_attachment_url( $img_10 ) : 'MISSING' ) . "\n";

if ( ! $img_2 || ! $img_30 || ! $img_10 ) {
	fwrite( STDERR, "Faltam imagens. Abortando.\n" );
	exit( 1 );
}

$nutricao = array(
	'tabelas' => array(
		vb_tab(
			'Pacote 15x2kg (SKU 510021)',
			'azul',
			array(
				'Marca'          => 'Valle Branco',
				'Categoria'      => 'Feijão',
				'Classe'         => 'Carioca',
				'Tipo'           => '1',
				'Peso Líquido'   => '2kg',
				'Peso Bruto'     => '2,010kg',
				'Altura'         => '5 cm',
				'Largura'        => '17 cm',
				'Profundidade'   => '27 cm',
				'Embalagem'      => '15x2kg',
				'GTIN'           => '7896397980048',
				'Código Interno' => '510021',
				'Validade'       => '6 meses',
				'NCM'            => '0713.33.29',
			)
		),
		vb_tab(
			'Pacote 30x1kg (SKU 510020)',
			'azul',
			array(
				'Marca'          => 'Valle Branco',
				'Categoria'      => 'Feijão',
				'Classe'         => 'Carioca',
				'Tipo'           => '1',
				'Peso Líquido'   => '1kg',
				'Peso Bruto'     => '1,006kg',
				'Altura'         => '5 cm',
				'Largura'        => '17 cm',
				'Profundidade'   => '27 cm',
				'Embalagem'      => '30x1kg',
				'GTIN'           => '7896397980031',
				'Código Interno' => '510020',
				'Validade'       => '6 meses',
				'NCM'            => '0713.33.29',
			)
		),
		vb_tab(
			'Pacote 10x1kg (SKU 510022)',
			'azul',
			array(
				'Marca'          => 'Valle Branco',
				'Categoria'      => 'Feijão',
				'Classe'         => 'Carioca',
				'Tipo'           => '1',
				'Peso Líquido'   => '1kg',
				'Peso Bruto'     => '1,006kg',
				'Altura'         => '5 cm',
				'Largura'        => '16 cm',
				'Profundidade'   => '22 cm',
				'Embalagem'      => '10x1kg',
				'GTIN'           => '7896397980031',
				'Código Interno' => '510022',
				'Validade'       => '6 meses',
				'NCM'            => '0713.33.29',
			)
		),
	),
);

$vars = array(
	array(
		'sku'       => '510021',
		'peso'      => '2kg',
		'embalagem' => '15x2kg',
		'gtin'      => '7896397980048',
	),
	array(
		'sku'       => '510020',
		'peso'      => '1kg',
		'embalagem' => '30x1kg',
		'gtin'      => '7896397980031',
	),
	array(
		'sku'       => '510022',
		'peso'      => '1kg',
		'embalagem' => '10x1kg',
		'gtin'      => '7896397980031',
	),
);

wp_update_post(
	array(
		'ID'          => $keep_id,
		'post_title'  => 'Feijão Carioca Valle Branco tipo 1',
		'post_name'   => 'feijao-carioca-valle-branco-tipo-1',
		'post_status' => 'publish',
	)
);

update_post_meta( $keep_id, '_vb_catalogo', '1' );
update_post_meta( $keep_id, '_vb_sku', '510021' );
update_post_meta( $keep_id, '_vb_pesos', array( '2kg', '1kg' ) );
update_post_meta( $keep_id, '_vb_embalagens', array( '15x2kg', '30x1kg', '10x1kg' ) );
update_post_meta( $keep_id, '_vb_variacoes', $vars );
update_post_meta( $keep_id, '_vb_nutricao', VB_Prod_Meta::sanitize_nutricao( $nutricao ) );
update_post_meta( $keep_id, '_vb_galeria', array( $img_30, $img_10 ) );
set_post_thumbnail( $keep_id, $img_2 );

foreach ( array( $dup_a, $dup_b ) as $d ) {
	if ( $d && $d !== $keep_id ) {
		wp_trash_post( $d );
		echo "TRASHED #{$d}\n";
	}
}

echo "OK #{$keep_id}\n";
echo 'tabelas=' . count( get_post_meta( $keep_id, '_vb_nutricao', true )['tabelas'] ?? array() ) . "\n";
echo 'carousel=' . implode( ',', VB_Prod_Product::get_carousel_ids( $keep_id ) ) . "\n";
echo 'permalink=' . get_permalink( $keep_id ) . "\n";
