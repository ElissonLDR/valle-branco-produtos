<?php
/**
 * Consolida SKUs duplicados (só peso/embalagem) em 1 produto com variações + descrições SEO/GEO.
 *
 * Uso: php bin/consolidar-variacoes-seo.php
 */
require dirname( __DIR__, 4 ) . '/wp-load.php';

/**
 * Busca post do catálogo pelo SKU.
 *
 * @param string $sku SKU.
 * @return int
 */
function vb_prod_find_by_sku( $sku ) {
	$ids = get_posts(
		array(
			'post_type'      => 'vb_produto',
			'post_status'    => array( 'publish', 'private', 'draft' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_vb_sku',
					'value' => $sku,
				),
			),
		)
	);
	if ( empty( $ids ) ) {
		global $wpdb;
		$id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_vb_sku'
				 WHERE p.post_type = 'vb_produto' AND m.meta_value = %s
				 ORDER BY p.ID ASC LIMIT 1",
				$sku
			)
		);
		return $id;
	}
	return (int) $ids[0];
}

/**
 * Descrição SEO/GEO padrão.
 *
 * @param array $p Produto consolidado.
 * @return string HTML.
 */
function vb_prod_seo_descricao( $p ) {
	$marca   = $p['marca'];
	$titulo  = $p['title'];
	$cat     = $p['cat'];
	$classe  = $p['classe'];
	$tipo    = $p['tipo'];
	$pesos   = implode( ', ', array_unique( array_column( $p['vars'], 'peso' ) ) );
	$embs    = implode( ' · ', array_unique( array_column( $p['vars'], 'embalagem' ) ) );
	$skus    = implode( ', ', array_column( $p['vars'], 'sku' ) );

	$tipo_txt = $tipo ? " tipo {$tipo}" : '';
	$lead     = "{$titulo} é um {$cat} da marca {$marca}";
	if ( $classe ) {
		$lead .= ", classificação {$classe}{$tipo_txt}";
	}
	$lead .= '.';

	$tec = '';
	if ( 'Arroz' === $cat ) {
		$tec = 'Indicado para o varejo alimentar e canais de distribuição, com padrão de grãos selecionados, estabilidade de cozimento e identificação comercial por SKU/GTIN para rastreabilidade fiscal e logística.';
	} elseif ( 'Feijão' === $cat ) {
		$tec = 'Feijão selecionado para o mercado brasileiro, com calibração de grãos, embalagem adequada à gôndola e códigos de produto compatíveis com ERP/SAP e notas fiscais.';
	} elseif ( 'Palmito' === $cat ) {
		$tec = 'Conserva de palmito pupunha pronta para o consumo e para a linha food service, com identificação por SKU e formato (inteiro, picado ou rodelas) claramente especificado na ficha.';
	} elseif ( 'Queijo ralado' === $cat ) {
		$tec = 'Queijo ralado fiapo para finalização culinária e gôndola refrigerada/secos conforme canal, com embalagem unitária e fardo comercial definidos na ficha técnica.';
	} else {
		$tec = 'Produto da linha Valle Branco / Cerealista Nardo, com ficha técnica, embalagem e código comercial padronizados para o canal de distribuição.';
	}

	$html  = '<p>' . $lead . ' ' . $tec . '</p>';
	$html .= '<p><strong>Variações disponíveis:</strong> pesos ' . $pesos;
	if ( $embs ) {
		$html .= '; embalagens ' . $embs;
	}
	$html .= '.</p>';
	$html .= '<p><strong>Códigos comerciais (SKU):</strong> ' . $skus . '. ';
	$html .= 'Fabricante: Cerealista Nardo (Alimentos Valle Branco), Santa Cruz do Rio Pardo–SP. ';
	$html .= 'Categoria: ' . $cat . '; marca: ' . $marca . '.</p>';

	return $html;
}

$grupos = array(
	array(
		'title'  => 'Arroz Extra Premium Valle Branco tipo 1',
		'slug'   => 'arroz-extra-premium-valle-branco-tipo-1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Arroz',
		'classe' => 'Longo Fino',
		'tipo'   => '1',
		'keep'   => '500030',
		'vars'   => array(
			array( 'sku' => '500030', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900015' ),
			array( 'sku' => '500032', 'peso' => '2kg', 'embalagem' => '15x2kg', 'gtin' => '7896397900039' ),
		),
	),
	array(
		'title'  => 'Arroz Integral Valle Branco tipo 1',
		'slug'   => 'arroz-integral-valle-branco-tipo-1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Arroz',
		'classe' => 'Longo Fino',
		'tipo'   => '1',
		'keep'   => '500040',
		'vars'   => array(
			array( 'sku' => '500040', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397900046' ),
		),
	),
	array(
		'title'  => 'Arroz Parboilizado Valle Branco tipo 1',
		'slug'   => 'arroz-parboilizado-valle-branco-tipo-1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Arroz',
		'classe' => 'Parboilizado',
		'tipo'   => '1',
		'keep'   => '500045',
		'vars'   => array(
			array( 'sku' => '500045', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900053' ),
			array( 'sku' => '500047', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397900060' ),
		),
	),
	array(
		'title'  => 'Arroz Arbório Valle Branco',
		'slug'   => 'arroz-arborio-valle-branco',
		'marca'  => 'Valle Branco',
		'cat'    => 'Arroz',
		'classe' => 'Longo',
		'tipo'   => 'UM',
		'keep'   => '500080',
		'vars'   => array(
			array( 'sku' => '500080', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397900084' ),
			array( 'sku' => '500080-5', 'peso' => '1kg', 'embalagem' => '5x1kg', 'gtin' => '7896397900084' ),
		),
	),
	array(
		'title'  => 'Feijão Carioca Valle Branco tipo 1',
		'slug'   => 'feijao-carioca-valle-branco-tipo-1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Feijão',
		'classe' => 'Carioca',
		'tipo'   => '1',
		'keep'   => '510021',
		'vars'   => array(
			array( 'sku' => '510021', 'peso' => '2kg', 'embalagem' => '15x2kg', 'gtin' => '7896397980048' ),
			array( 'sku' => '510020', 'peso' => '1kg', 'embalagem' => '30x1kg', 'gtin' => '7896397980031' ),
			array( 'sku' => '510022', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397980031' ),
		),
	),
	array(
		'title'  => 'Feijão Preto Valle Branco tipo 1',
		'slug'   => 'feijao-preto-valle-branco-tipo-1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Feijão',
		'classe' => 'Preto',
		'tipo'   => '1',
		'keep'   => '510031',
		'vars'   => array(
			array( 'sku' => '510031', 'peso' => '1kg', 'embalagem' => '30x1kg', 'gtin' => '7896397980017' ),
			array( 'sku' => '510030', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397980017' ),
		),
	),
	array(
		'title'  => 'Feijão Bolinha Valle Branco tipo 1',
		'slug'   => 'feijao-bolinha-valle-branco-tipo-1',
		'marca'  => 'Valle Branco',
		'cat'    => 'Feijão',
		'classe' => 'Bolinha',
		'tipo'   => '1',
		'keep'   => '510040',
		'vars'   => array(
			array( 'sku' => '510040', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397980130' ),
		),
	),
	array(
		'title'  => 'Queijo Ralado Valle Branco Fiapo',
		'slug'   => 'queijo-ralado-valle-branco-fiapo',
		'marca'  => 'Valle Branco',
		'cat'    => 'Queijo ralado',
		'classe' => 'Ralado',
		'tipo'   => 'Fiapo',
		'keep'   => '414001',
		'vars'   => array(
			array( 'sku' => '414001', 'peso' => '40g', 'embalagem' => '25x40g', 'gtin' => '7896397400010' ),
		),
	),
	array(
		'title'  => 'Palmito Valle Branco Pupunha Inteiro',
		'slug'   => 'palmito-valle-branco-pupunha-inteiro',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'classe' => 'Pupunha',
		'tipo'   => 'Inteiro',
		'keep'   => '403010',
		'vars'   => array(
			array( 'sku' => '403010', 'peso' => '300g', 'embalagem' => '6x300g', 'gtin' => '7896397950003' ),
			array( 'sku' => '403009', 'peso' => '180g', 'embalagem' => '12x180g', 'gtin' => '7896397951000' ),
		),
	),
	array(
		'title'  => 'Palmito Valle Branco Pupunha Picado',
		'slug'   => 'palmito-valle-branco-pupunha-picado',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'classe' => 'Pupunha',
		'tipo'   => 'Picado',
		'keep'   => '403012',
		'vars'   => array(
			array( 'sku' => '403012', 'peso' => '300g', 'embalagem' => '6x300g', 'gtin' => '7896397960002' ),
		),
	),
	array(
		'title'  => 'Palmito Valle Branco Pupunha Rodelas',
		'slug'   => 'palmito-valle-branco-pupunha-rodelas',
		'marca'  => 'Valle Branco',
		'cat'    => 'Palmito',
		'classe' => 'Pupunha',
		'tipo'   => 'Rodela',
		'keep'   => '403011',
		'vars'   => array(
			array( 'sku' => '403011', 'peso' => '300g', 'embalagem' => '6x300g', 'gtin' => '7896397955008' ),
		),
	),
	array(
		'title'  => 'Arroz Castelão tipo 1',
		'slug'   => 'arroz-castelao-tipo-1',
		'marca'  => 'Castelão',
		'cat'    => 'Arroz',
		'classe' => 'Longo Fino',
		'tipo'   => '1',
		'keep'   => '500020',
		'vars'   => array(
			array( 'sku' => '500020', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900336' ),
			array( 'sku' => '500021', 'peso' => '2kg', 'embalagem' => '15x2kg', 'gtin' => '7896397900589' ),
		),
	),
	array(
		'title'  => 'Arroz Castelão tipo 2',
		'slug'   => 'arroz-castelao-tipo-2',
		'marca'  => 'Castelão',
		'cat'    => 'Arroz',
		'classe' => 'Longo Fino',
		'tipo'   => '2',
		'keep'   => '500022',
		'vars'   => array(
			array( 'sku' => '500022', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900220' ),
		),
	),
	array(
		'title'  => 'Arroz Castelão tipo 3',
		'slug'   => 'arroz-castelao-tipo-3',
		'marca'  => 'Castelão',
		'cat'    => 'Arroz',
		'classe' => 'Longo Fino',
		'tipo'   => '3',
		'keep'   => '500023',
		'vars'   => array(
			array( 'sku' => '500023', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900558' ),
		),
	),
	array(
		'title'  => 'Arroz Castelão Série Ouro tipo 1',
		'slug'   => 'arroz-castelao-serie-ouro-tipo-1',
		'marca'  => 'Castelão',
		'cat'    => 'Arroz',
		'classe' => 'Longo Fino',
		'tipo'   => '1',
		'keep'   => '500024',
		'vars'   => array(
			array( 'sku' => '500024', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900343' ),
		),
	),
	array(
		'title'  => 'Feijão Castelão tipo 1',
		'slug'   => 'feijao-castelao-tipo-1',
		'marca'  => 'Castelão',
		'cat'    => 'Feijão',
		'classe' => 'Carioca',
		'tipo'   => '1',
		'keep'   => '510002',
		'vars'   => array(
			array( 'sku' => '510002', 'peso' => '2kg', 'embalagem' => '15x2kg', 'gtin' => '7896397980086' ),
			array( 'sku' => '510001', 'peso' => '1kg', 'embalagem' => '30x1kg', 'gtin' => '7896397980079' ),
			array( 'sku' => '510003', 'peso' => '1kg', 'embalagem' => '10x1kg', 'gtin' => '7896397980079' ),
		),
	),
	array(
		'title'  => 'Feijão Castelão Econômico tipo 1',
		'slug'   => 'feijao-castelao-economico-tipo-1',
		'marca'  => 'Castelão',
		'cat'    => 'Feijão',
		'classe' => 'Carioca',
		'tipo'   => '1',
		'keep'   => '510004',
		'vars'   => array(
			array( 'sku' => '510004', 'peso' => '1kg', 'embalagem' => '30x1kg', 'gtin' => '7896397980109' ),
		),
	),
	array(
		'title'  => 'Arroz Aene tipo 1',
		'slug'   => 'arroz-aene-tipo-1',
		'marca'  => 'Aene',
		'cat'    => 'Arroz',
		'classe' => 'Longo Fino',
		'tipo'   => '1',
		'keep'   => '500001',
		'vars'   => array(
			array( 'sku' => '500001', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900114' ),
			array( 'sku' => '500002', 'peso' => '2kg', 'embalagem' => '15x2kg', 'gtin' => '7896397900565' ),
		),
	),
	array(
		'title'  => 'Arroz Aene Mix tipo 1',
		'slug'   => 'arroz-aene-mix-tipo-1',
		'marca'  => 'Aene',
		'cat'    => 'Arroz',
		'classe' => 'Longo Fino',
		'tipo'   => '1',
		'keep'   => '500005',
		'vars'   => array(
			array( 'sku' => '500005', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900886' ),
		),
	),
	array(
		'title'  => 'Arroz Vita Abaixo Padrão',
		'slug'   => 'arroz-vita-abaixo-padrao',
		'marca'  => 'Vita',
		'cat'    => 'Arroz',
		'classe' => 'Abaixo Padrão',
		'tipo'   => '1',
		'keep'   => '500060',
		'vars'   => array(
			array( 'sku' => '500060', 'peso' => '5kg', 'embalagem' => '6x5kg', 'gtin' => '7896397900572' ),
		),
	),
);

$ok      = 0;
$trashed = 0;
$keep_ids = array();

foreach ( $grupos as $g ) {
	$keep_id = vb_prod_find_by_sku( $g['keep'] );
	if ( ! $keep_id ) {
		echo "MISSING keep SKU {$g['keep']}\n";
		continue;
	}

	$pesos = array();
	$embs  = array();
	foreach ( $g['vars'] as $v ) {
		if ( $v['peso'] && ! in_array( $v['peso'], $pesos, true ) ) {
			$pesos[] = $v['peso'];
		}
		if ( $v['embalagem'] && ! in_array( $v['embalagem'], $embs, true ) ) {
			$embs[] = $v['embalagem'];
		}
	}

	$excerpt = sprintf(
		'%s — marca %s. Variações: %s. Embalagens: %s.',
		$g['title'],
		$g['marca'],
		implode( ', ', $pesos ),
		implode( ' · ', $embs )
	);

	wp_update_post(
		array(
			'ID'           => $keep_id,
			'post_title'   => $g['title'],
			'post_name'    => $g['slug'],
			'post_content' => vb_prod_seo_descricao( $g ),
			'post_excerpt' => $excerpt,
			'post_status'  => 'publish',
		)
	);

	update_post_meta( $keep_id, '_vb_catalogo', '1' );
	delete_post_meta( $keep_id, '_vb_origem' );
	update_post_meta( $keep_id, '_vb_sku', $g['keep'] );
	update_post_meta( $keep_id, '_vb_marca', $g['marca'] );
	update_post_meta( $keep_id, '_vb_categoria', $g['cat'] );
	update_post_meta( $keep_id, '_vb_classe', $g['classe'] );
	update_post_meta( $keep_id, '_vb_tipo', $g['tipo'] );
	update_post_meta( $keep_id, '_vb_pesos', implode( ', ', $pesos ) );
	update_post_meta( $keep_id, '_vb_embalagens', implode( ' · ', $embs ) );
	update_post_meta( $keep_id, '_vb_embalagem', $embs[0] ?? '' );
	update_post_meta( $keep_id, '_vb_variacoes', $g['vars'] );
	update_post_meta( $keep_id, '_vb_gtin', $g['vars'][0]['gtin'] ?? '' );

	if ( taxonomy_exists( 'vb_marca' ) ) {
		wp_set_object_terms( $keep_id, $g['marca'], 'vb_marca', false );
	}
	if ( taxonomy_exists( 'vb_categoria_produto' ) ) {
		wp_set_object_terms( $keep_id, $g['cat'], 'vb_categoria_produto', false );
	}

	$keep_ids[] = $keep_id;
	++$ok;
	echo "OK #{$keep_id} {$g['title']} (" . count( $g['vars'] ) . " vars)\n";

	foreach ( $g['vars'] as $v ) {
		if ( $v['sku'] === $g['keep'] ) {
			continue;
		}
		$dup = vb_prod_find_by_sku( $v['sku'] );
		if ( $dup && $dup !== $keep_id ) {
			wp_trash_post( $dup );
			++$trashed;
			echo "  TRASH #{$dup} [{$v['sku']}]\n";
		}
	}
}

// Remove publicados de catálogo que não estão nos keep_ids.
$all = get_posts(
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
foreach ( $all as $pid ) {
	if ( ! in_array( (int) $pid, $keep_ids, true ) ) {
		wp_trash_post( $pid );
		++$trashed;
		echo "TRASH extra #{$pid}\n";
	}
}

$counts = wp_count_posts( 'vb_produto' );
echo "---\nConsolidados: {$ok}\nTrashed: {$trashed}\nPublicados: " . (int) $counts->publish . "\n";
