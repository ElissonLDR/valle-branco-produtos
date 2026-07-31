<?php
/**
 * Une Feijão Preto 30x1kg + 10x1kg em 1 produto (2 tabelas, 2 imagens se houver).
 *
 * Uso: php bin/unir-feijao-preto.php
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
			'posts_per_page' => 40,
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
 * Busca attachment por trecho no nome do arquivo.
 *
 * @param string $needle Trecho.
 * @return int
 */
function vb_find_att_like( $needle ) {
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $needle ) . '%';
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

$keep_id = vb_find_sku( '510031' );
$dup_id  = vb_find_sku( '510030' );

if ( ! $keep_id ) {
	fwrite( STDERR, "SKU 510031 não encontrado.\n" );
	exit( 1 );
}

echo "KEEP #{$keep_id} " . get_the_title( $keep_id ) . ' [' . get_post_status( $keep_id ) . "]\n";
if ( $dup_id ) {
	echo "DUP  #{$dup_id} " . get_the_title( $dup_id ) . ' [' . get_post_status( $dup_id ) . "]\n";
}

$img_30 = vb_find_att( 'Feijao-Preto-Valle-Branco-T1-30x1kg.webp' );
$img_10 = vb_find_att( 'Feijao-Preto-Valle-Branco-T1-10x1kg.webp' );
if ( ! $img_10 ) {
	$img_10 = vb_find_att( 'feijao-preto-1kg.webp' );
}
if ( ! $img_10 ) {
	global $wpdb;
	$rows = $wpdb->get_results(
		"SELECT post_id, meta_value FROM {$wpdb->postmeta}
		 WHERE meta_key = '_wp_attached_file'
		   AND (meta_value LIKE '%Preto%' OR meta_value LIKE '%preto%')
		 ORDER BY post_id DESC LIMIT 20"
	);
	echo "Candidatos imagem Preto:\n";
	foreach ( $rows as $r ) {
		echo "  #{$r->post_id} {$r->meta_value}\n";
		if ( ! $img_10 && false !== stripos( $r->meta_value, '10x1' ) ) {
			$img_10 = (int) $r->post_id;
		}
	}
}
if ( ! $img_30 ) {
	$img_30 = (int) get_post_thumbnail_id( $keep_id );
}
if ( ! $img_10 && $dup_id ) {
	$t = (int) get_post_thumbnail_id( $dup_id );
	if ( $t && $t !== $img_30 ) {
		$img_10 = $t;
	}
}

echo "IMG 30x1={$img_30} " . ( $img_30 ? wp_get_attachment_url( $img_30 ) : 'MISSING' ) . "\n";
echo "IMG 10x1={$img_10} " . ( $img_10 ? wp_get_attachment_url( $img_10 ) : 'MISSING (só 1 arte disponível)' ) . "\n";

if ( ! $img_30 ) {
	fwrite( STDERR, "Falta imagem 30x1. Abortando.\n" );
	exit( 1 );
}

$nutricao = array(
	'tabelas' => array(
		vb_tab(
			'Pacote 30x1kg (SKU 510031)',
			'azul',
			array(
				'Marca'          => 'Valle Branco',
				'Categoria'      => 'Feijão',
				'Classe'         => 'Preto',
				'Tipo'           => '1',
				'Peso Líquido'   => '1kg',
				'Peso Bruto'     => '1,006kg',
				'Altura'         => '5 cm',
				'Largura'        => '16 cm',
				'Profundidade'   => '22 cm',
				'Embalagem'      => '30x1kg',
				'GTIN'           => '7896397980017',
				'Código Interno' => '510031',
				'Validade'       => '6 meses',
				'NCM'            => '0713.33.19',
			)
		),
		vb_tab(
			'Pacote 10x1kg (SKU 510030)',
			'azul',
			array(
				'Marca'          => 'Valle Branco',
				'Categoria'      => 'Feijão',
				'Classe'         => 'Preto',
				'Tipo'           => '1',
				'Peso Líquido'   => '1kg',
				'Peso Bruto'     => '1,006kg',
				'Altura'         => '5 cm',
				'Largura'        => '16 cm',
				'Profundidade'   => '22 cm',
				'Embalagem'      => '10x1kg',
				'GTIN'           => '7896397980017',
				'Código Interno' => '510030',
				'Validade'       => '6 meses',
				'NCM'            => '0713.33.19',
			)
		),
	),
);

$vars = array(
	array(
		'sku'       => '510031',
		'peso'      => '1kg',
		'embalagem' => '30x1kg',
		'gtin'      => '7896397980017',
	),
	array(
		'sku'       => '510030',
		'peso'      => '1kg',
		'embalagem' => '10x1kg',
		'gtin'      => '7896397980017',
	),
);

wp_update_post(
	array(
		'ID'          => $keep_id,
		'post_title'  => 'Feijão Preto Valle Branco tipo 1',
		'post_name'   => 'feijao-preto-valle-branco-tipo-1',
		'post_status' => 'publish',
	)
);

update_post_meta( $keep_id, '_vb_catalogo', '1' );
update_post_meta( $keep_id, '_vb_sku', '510031' );
update_post_meta( $keep_id, '_vb_pesos', array( '1kg' ) );
update_post_meta( $keep_id, '_vb_embalagens', array( '30x1kg', '10x1kg' ) );
update_post_meta( $keep_id, '_vb_variacoes', $vars );
update_post_meta( $keep_id, '_vb_nutricao', VB_Prod_Meta::sanitize_nutricao( $nutricao ) );

set_post_thumbnail( $keep_id, $img_30 );
if ( $img_10 && $img_10 !== $img_30 ) {
	update_post_meta( $keep_id, '_vb_galeria', array( $img_10 ) );
} else {
	update_post_meta( $keep_id, '_vb_galeria', array() );
	echo "AVISO: sem arte distinta de 10x1kg — carrossel com 1 imagem.\n";
}

if ( $dup_id && $dup_id !== $keep_id ) {
	wp_trash_post( $dup_id );
	echo "TRASHED #{$dup_id}\n";
}

echo "OK #{$keep_id}\n";
echo 'tabelas=' . count( get_post_meta( $keep_id, '_vb_nutricao', true )['tabelas'] ?? array() ) . "\n";
echo 'carousel=' . implode( ',', VB_Prod_Product::get_carousel_ids( $keep_id ) ) . "\n";
echo 'permalink=' . get_permalink( $keep_id ) . "\n";
