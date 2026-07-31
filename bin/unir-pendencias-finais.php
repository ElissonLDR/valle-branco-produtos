<?php
/**
 * Une os 5 grupos pendentes: Palmito Inteiro, Arbório, Castelão T1,
 * Feijão Castelão T1 e Aene T1 — com N tabelas e N imagens.
 *
 * Uso: php bin/unir-pendencias-finais.php
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

/**
 * Une um grupo.
 *
 * @param array $g Grupo.
 * @return void
 */
function vb_unir_grupo( $g ) {
	$keep_id = vb_find_sku( $g['keep'] );
	if ( ! $keep_id ) {
		echo "FAIL {$g['label']}: keep SKU {$g['keep']} missing\n";
		return;
	}

	$dup_ids = array();
	foreach ( $g['dups'] as $sku ) {
		$id = vb_find_sku( $sku );
		if ( $id && $id !== $keep_id ) {
			$dup_ids[ $sku ] = $id;
		}
	}

	$imgs = array();
	foreach ( $g['images'] as $sku => $file ) {
		$aid = vb_find_att( $file );
		if ( ! $aid && isset( $dup_ids[ $sku ] ) ) {
			$aid = (int) get_post_thumbnail_id( $dup_ids[ $sku ] );
		}
		if ( ! $aid && $sku === $g['keep'] ) {
			$aid = (int) get_post_thumbnail_id( $keep_id );
		}
		$imgs[ $sku ] = $aid;
		echo "  IMG {$sku}={$aid} " . ( $aid ? basename( (string) get_attached_file( $aid ) ) : 'MISSING' ) . "\n";
	}

	$thumb = $imgs[ $g['keep'] ] ?? 0;
	$gal   = array();
	foreach ( $g['vars'] as $v ) {
		$aid = $imgs[ $v['sku'] ] ?? 0;
		if ( $aid && $aid !== $thumb && ! in_array( $aid, $gal, true ) ) {
			$gal[] = $aid;
		}
	}

	if ( ! $thumb ) {
		echo "FAIL {$g['label']}: sem capa\n";
		return;
	}

	$pesos = array_values( array_unique( array_column( $g['vars'], 'peso' ) ) );
	$embs  = array_values( array_unique( array_column( $g['vars'], 'embalagem' ) ) );

	wp_update_post(
		array(
			'ID'          => $keep_id,
			'post_title'  => $g['title'],
			'post_name'   => $g['slug'],
			'post_status' => 'publish',
		)
	);

	update_post_meta( $keep_id, '_vb_catalogo', '1' );
	update_post_meta( $keep_id, '_vb_sku', $g['keep'] );
	update_post_meta( $keep_id, '_vb_pesos', $pesos );
	update_post_meta( $keep_id, '_vb_embalagens', $embs );
	update_post_meta( $keep_id, '_vb_variacoes', $g['vars'] );
	update_post_meta( $keep_id, '_vb_nutricao', VB_Prod_Meta::sanitize_nutricao( array( 'tabelas' => $g['tabelas'] ) ) );
	update_post_meta( $keep_id, '_vb_galeria', $gal );
	set_post_thumbnail( $keep_id, $thumb );

	foreach ( $dup_ids as $sku => $id ) {
		wp_trash_post( $id );
		echo "  TRASH #{$id} ({$sku})\n";
	}

	$car = VB_Prod_Product::get_carousel_ids( $keep_id );
	echo "OK {$g['label']} #{$keep_id} tabs=" . count( $g['tabelas'] ) . ' imgs=' . count( $car ) . ' ' . get_permalink( $keep_id ) . "\n";
}

$grupos = array(
	array(
		'label'  => 'Palmito Inteiro',
		'keep'   => '403010',
		'dups'   => array( '403009' ),
		'title'  => 'Palmito Valle Branco Pupunha Inteiro',
		'slug'   => 'palmito-valle-branco-pupunha-inteiro',
		'images' => array(
			'403010' => 'Palmito-Valle-Branco-Pupunha-Inteiro-6x300g.webp',
			'403009' => 'Palmito-Valle-Branco-Pupunha-Inteiro-12x180g.webp',
		),
		'vars'   => array(
			array( 'sku' => '403010', 'peso' => '300g', 'embalagem' => '6x300g', 'gtin' => '7896397950003' ),
			array( 'sku' => '403009', 'peso' => '180g', 'embalagem' => '12x180g', 'gtin' => '7896397951000' ),
		),
		'tabelas'=> array(
			vb_tab(
				'Pacote 6x300g (SKU 403010)',
				'azul',
				array(
					'Marca' => 'Valle Branco', 'Categoria' => 'Palmito', 'Classe' => 'Pupunha', 'Tipo' => 'Inteiro',
					'Peso Líquido' => '300g', 'Peso Bruto' => '540g',
					'Altura' => '14 cm', 'Largura' => '9 cm', 'Profundidade' => '8 cm',
					'Embalagem' => '6x300g', 'Caixa' => '6 und',
					'GTIN' => '7896397950003', 'Código Interno' => '403010',
					'Validade' => '24 meses', 'NCM' => '2008.91.00',
				)
			),
			vb_tab(
				'Pacote 12x180g (SKU 403009)',
				'azul',
				array(
					'Marca' => 'Valle Branco', 'Categoria' => 'Palmito', 'Classe' => 'Pupunha', 'Tipo' => 'Inteiro',
					'Peso Líquido' => '180g', 'Peso Bruto' => '330g',
					'Altura' => '14 cm', 'Largura' => '9 cm', 'Profundidade' => '8 cm',
					'Embalagem' => '12x180g', 'Caixa' => '12 und',
					'GTIN' => '7896397951000', 'Código Interno' => '403009',
					'Validade' => '24 meses', 'NCM' => '2008.91.00',
				)
			),
		),
	),
	array(
		'label'  => 'Arbório',
		'keep'   => '500080',
		'dups'   => array( '500080-5' ),
		'title'  => 'Arroz Arbório Valle Branco',
		'slug'   => 'arroz-arborio-valle-branco',
		'images' => array(
			'500080'   => 'ArrozArborioValleBranco10x1.webp',
			'500080-5' => 'ArrozArborioValleBranco5x1.webp',
		),
		'vars'   => array(
			array( 'sku' => '500080', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397900084' ),
			array( 'sku' => '500080-5', 'peso' => '1kg', 'embalagem' => '5x1kg', 'gtin' => '7896397900084' ),
		),
		'tabelas'=> array(
			vb_tab(
				'Pacote 10x1kg (SKU 500080)',
				'azul',
				array(
					'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Longo', 'Tipo' => 'UM',
					'Peso Líquido' => '1kg', 'Peso Bruto' => '1,010kg',
					'Altura' => '10 cm', 'Largura' => '20 cm', 'Profundidade' => '5 cm',
					'Embalagem' => '10x1kg', 'Caixa' => '10 und',
					'GTIN' => '7896397900084', 'Código Interno' => '500080',
					'Validade' => '6 meses', 'NCM' => '1006.30.21',
				)
			),
			vb_tab(
				'Pacote 5x1kg (SKU 500080-5)',
				'azul',
				array(
					'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Longo', 'Tipo' => 'UM',
					'Peso Líquido' => '1kg', 'Peso Bruto' => '1,010kg',
					'Altura' => '10 cm', 'Largura' => '20 cm', 'Profundidade' => '5 cm',
					'Embalagem' => '5x1kg', 'Caixa' => '5 und',
					'GTIN' => '7896397900084', 'Código Interno' => '500080-5',
					'Validade' => '6 meses', 'NCM' => '1006.30.21',
				)
			),
		),
	),
	array(
		'label'  => 'Castelão T1',
		'keep'   => '500020',
		'dups'   => array( '500021' ),
		'title'  => 'Arroz Castelão tipo 1',
		'slug'   => 'arroz-castelao-tipo-1',
		'images' => array(
			'500020' => 'Arroz-Castelao-T1-6x5kg.webp',
			'500021' => 'Arroz-Castelao-T1-15x2kg.webp',
		),
		'vars'   => array(
			array( 'sku' => '500020', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900336' ),
			array( 'sku' => '500021', 'peso' => '2kg', 'embalagem' => '15x2kg', 'gtin' => '7896397900589' ),
		),
		'tabelas'=> array(
			vb_tab(
				'Pacote 6x5kg (SKU 500020)',
				'azul',
				array(
					'Marca' => 'Castelão', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
					'Peso Líquido' => '5kg', 'Peso Bruto' => '5,018kg',
					'Altura' => '7 cm', 'Largura' => '24 cm', 'Profundidade' => '36 cm',
					'Embalagem' => '6x5kg',
					'GTIN' => '7896397900336', 'Código Interno' => '500020',
					'Validade' => '6 meses', 'NCM' => '1006.30.21',
				)
			),
			vb_tab(
				'Pacote 15x2kg (SKU 500021)',
				'azul',
				array(
					'Marca' => 'Castelão', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
					'Peso Líquido' => '2kg', 'Peso Bruto' => '2,010kg',
					'Altura' => '5 cm', 'Largura' => '17 cm', 'Profundidade' => '27 cm',
					'Embalagem' => '15x2kg',
					'GTIN' => '7896397900589', 'Código Interno' => '500021',
					'Validade' => '6 meses', 'NCM' => '1006.30.21',
				)
			),
		),
	),
	array(
		'label'  => 'Feijão Castelão T1',
		'keep'   => '510002',
		'dups'   => array( '510001', '510003' ),
		'title'  => 'Feijão Castelão tipo 1',
		'slug'   => 'feijao-castelao-tipo-1',
		'images' => array(
			'510002' => 'Feijao-Castelao-T1-15x2kg.webp',
			'510001' => 'Feijao-Castelao-T1-30x1kg.webp',
			'510003' => 'Feijao-Castelao-T1-10x1kg.webp',
		),
		'vars'   => array(
			array( 'sku' => '510002', 'peso' => '2kg', 'embalagem' => '15x2kg', 'gtin' => '7896397980086' ),
			array( 'sku' => '510001', 'peso' => '1kg', 'embalagem' => '30x1kg', 'gtin' => '7896397980079' ),
			array( 'sku' => '510003', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397980079' ),
		),
		'tabelas'=> array(
			vb_tab(
				'Pacote 15x2kg (SKU 510002)',
				'azul',
				array(
					'Marca' => 'Castelão', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
					'Peso Líquido' => '2kg', 'Peso Bruto' => '2,010kg',
					'Altura' => '5 cm', 'Largura' => '17 cm', 'Profundidade' => '27 cm',
					'Embalagem' => '15x2kg', 'DUN 14' => '17896397980083',
					'GTIN' => '7896397980086', 'Código Interno' => '510002',
					'Validade' => '6 meses', 'NCM' => '0713.33.29',
				)
			),
			vb_tab(
				'Pacote 30x1kg (SKU 510001)',
				'azul',
				array(
					'Marca' => 'Castelão', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
					'Peso Líquido' => '1kg', 'Peso Bruto' => '1,006kg',
					'Altura' => '5 cm', 'Largura' => '16 cm', 'Profundidade' => '22 cm',
					'Embalagem' => '30x1kg',
					'GTIN' => '7896397980079', 'Código Interno' => '510001',
					'Validade' => '6 meses', 'NCM' => '0713.33.29',
				)
			),
			vb_tab(
				'Pacote 10x1kg (SKU 510003)',
				'azul',
				array(
					'Marca' => 'Castelão', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
					'Peso Líquido' => '1kg', 'Peso Bruto' => '1,006kg',
					'Altura' => '5 cm', 'Largura' => '16 cm', 'Profundidade' => '22 cm',
					'Embalagem' => '10x1kg', 'DUN 14' => '78963979800785',
					'GTIN' => '7896397980079', 'Código Interno' => '510003',
					'Validade' => '6 meses', 'NCM' => '0713.33.29',
				)
			),
		),
	),
	array(
		'label'  => 'Aene T1',
		'keep'   => '500001',
		'dups'   => array( '500002' ),
		'title'  => 'Arroz Aene tipo 1',
		'slug'   => 'arroz-aene-tipo-1',
		'images' => array(
			'500001' => 'Arroz-Aene-T1-6x5kg.webp',
			'500002' => 'Arroz-Aene-T1-15x2kg.webp',
		),
		'vars'   => array(
			array( 'sku' => '500001', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900114' ),
			array( 'sku' => '500002', 'peso' => '2kg', 'embalagem' => '15x2kg', 'gtin' => '7896397900565' ),
		),
		'tabelas'=> array(
			vb_tab(
				'Pacote 6x5kg (SKU 500001)',
				'azul',
				array(
					'Marca' => 'Aene', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
					'Peso Líquido' => '5kg', 'Peso Bruto' => '5,018kg',
					'Altura' => '7 cm', 'Largura' => '24 cm', 'Profundidade' => '36 cm',
					'Embalagem' => '6x5kg',
					'GTIN' => '7896397900114', 'Código Interno' => '500001',
					'Validade' => '6 meses', 'NCM' => '1006.30.21',
				)
			),
			vb_tab(
				'Pacote 15x2kg (SKU 500002)',
				'azul',
				array(
					'Marca' => 'Aene', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
					'Peso Líquido' => '2kg', 'Peso Bruto' => '2,010kg',
					'Altura' => '5 cm', 'Largura' => '17 cm', 'Profundidade' => '27 cm',
					'Embalagem' => '15x2kg',
					'GTIN' => '7896397900565', 'Código Interno' => '500002',
					'Validade' => '6 meses', 'NCM' => '1006.30.21',
				)
			),
		),
	),
);

foreach ( $grupos as $g ) {
	echo "=== {$g['label']} ===\n";
	vb_unir_grupo( $g );
}

$pub = get_posts(
	array(
		'post_type'      => 'vb_produto',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_vb_catalogo',
				'value' => '1',
			),
		),
	)
);
echo "\nTOTAL PUBLISH CATALOGO=" . count( $pub ) . "\n";
