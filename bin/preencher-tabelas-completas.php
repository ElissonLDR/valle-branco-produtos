<?php
/**
 * Preenche Pacote + Pallet/Caixa + Tributação nos 14 produtos incompletos.
 * Multi-SKU: 3 tabelas por SKU (ordem Pacote → Pallet/Caixa → Tributação).
 *
 * Uso: php bin/preencher-tabelas-completas.php
 */
require dirname( __DIR__, 4 ) . '/wp-load.php';

/**
 * @param string $titulo Título.
 * @param string $estilo azul|ouro.
 * @param array  $rows   Campo => valor.
 * @return array
 */
function vb_tab( $titulo, $estilo, $rows ) {
	$linhas = array();
	foreach ( $rows as $k => $v ) {
		if ( '' === (string) $v || null === $v ) {
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
 * Tributação padrão por NCM (padrão das fichas Castelão/Aene).
 *
 * @param string $ncm NCM.
 * @return array
 */
function vb_trib( $ncm ) {
	return array(
		'CST (ICMS)'       => '020',
		'% ICMS'           => '18,00%',
		'% Red. Base ICMS' => '61,11%',
		'NCM'              => $ncm,
		'CST (PIS/COFINS)' => '06',
		'% PIS'            => '0,00%',
		'% COFINS'         => '0,00%',
		'% IPI'            => '0,00%',
		'% IVA / ICMS ST'  => '0,00%',
	);
}

/**
 * Pallet arroz 6x5kg (padrão ficha).
 *
 * @return array
 */
function vb_pallet_arroz_6x5() {
	return array(
		'Unidade de Medida' => 'Fardo',
		'Quantidade'        => '49',
		'Lastro'            => '7',
		'Altura Camadas'    => '7',
		'Peso Bruto'        => '1.490kg',
		'Peso Líquido'      => '1.470kg',
		'Altura'            => '1,44m',
		'Largura'           => '1,18m',
		'Profundidade'      => '1,26m',
		'Validade'          => '6 meses',
	);
}

/**
 * Pallet arroz/feijão 15x2kg.
 *
 * @return array
 */
function vb_pallet_15x2() {
	return array(
		'Unidade de Medida' => 'Fardo',
		'Quantidade'        => '49',
		'Lastro'            => '7',
		'Altura Camadas'    => '7',
		'Peso Bruto'        => '1.490kg',
		'Peso Líquido'      => '1.470kg',
		'Altura'            => '1,56m',
		'Largura'           => '1,25m',
		'Profundidade'      => '1,13m',
		'Validade'          => '6 meses',
	);
}

/**
 * Pallet feijão 30x1kg.
 *
 * @return array
 */
function vb_pallet_30x1() {
	return array(
		'Unidade de Medida' => 'Fardo',
		'Quantidade'        => '49',
		'Lastro'            => '7',
		'Altura Camadas'    => '7',
		'Peso Bruto'        => '1.490kg',
		'Peso Líquido'      => '1.470kg',
		'Altura'            => '1,56m',
		'Largura'           => '1,23m',
		'Profundidade'      => '1,12m',
		'Validade'          => '6 meses',
	);
}

/**
 * Pallet 10x1kg.
 *
 * @param string $dun DUN opcional.
 * @return array
 */
function vb_pallet_10x1( $dun = '' ) {
	$rows = array(
		'Unidade de Medida' => 'Fardo',
		'Quantidade'        => '100',
		'Lastro'            => '10',
		'Altura Camadas'    => '10',
		'Peso Bruto'        => '1.020kg',
		'Peso Líquido'      => '1.000kg',
		'Altura'            => '1,33m',
		'Largura'           => '1,25m',
		'Profundidade'      => '1,05m',
		'Validade'          => '6 meses',
	);
	if ( $dun ) {
		$rows['DUN 14'] = $dun;
	}
	return $rows;
}

/**
 * Monta trio Pacote/Pallet|Caixa/Tributação.
 *
 * @param string $label     Sufixo do título (ex.: "6x5kg (SKU 500030)").
 * @param array  $pacote    Linhas pacote.
 * @param array  $meio      Linhas pallet/caixa.
 * @param string $meio_nome Pallet|Caixa.
 * @param array  $trib      Linhas trib.
 * @return array
 */
function vb_trio( $label, $pacote, $meio, $meio_nome, $trib ) {
	$suf = $label ? ' ' . $label : '';
	return array(
		vb_tab( 'Pacote' . $suf, 'azul', $pacote ),
		vb_tab( $meio_nome . $suf, 'azul', $meio ),
		vb_tab( 'Tributação' . $suf, 'ouro', $trib ),
	);
}

/**
 * @param string $sku SKU keep.
 * @return int
 */
function vb_find_sku( $sku ) {
	global $wpdb;
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_vb_sku'
			 WHERE p.post_type = 'vb_produto' AND m.meta_value = %s
			   AND p.post_status IN ('publish','draft','private')
			 ORDER BY FIELD(p.post_status,'publish','draft','private'), p.ID ASC LIMIT 1",
			$sku
		)
	);
}

// Fichas por SKU (dados catálogo + pallet análogo quando não havia no doc VB).
$fichas = array(
	'500030' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '5,018kg', 'Peso Líquido' => '5kg',
			'Altura' => '7cm', 'Largura' => '24cm', 'Profundidade' => '36cm',
			'Embalagem' => '6x5kg', 'GTIN' => '7896397900015', 'Código Interno' => '500030',
		),
		'meio' => vb_pallet_arroz_6x5(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.30.21' ),
		'label' => '6x5kg (SKU 500030)',
	),
	'500032' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '2,010kg', 'Peso Líquido' => '2kg',
			'Altura' => '5cm', 'Largura' => '17cm', 'Profundidade' => '27cm',
			'Embalagem' => '15x2kg', 'GTIN' => '7896397900039', 'Código Interno' => '500032',
		),
		'meio' => vb_pallet_15x2(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.30.21' ),
		'label' => '15x2kg (SKU 500032)',
	),
	'500040' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '16cm', 'Profundidade' => '22cm',
			'Embalagem' => '10x1kg', 'GTIN' => '7896397900046', 'Código Interno' => '500040',
		),
		'meio' => vb_pallet_10x1(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.20.20' ),
		'label' => '',
	),
	'500045' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Parboilizado', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '5,018kg', 'Peso Líquido' => '5kg',
			'Altura' => '7cm', 'Largura' => '24cm', 'Profundidade' => '36cm',
			'Embalagem' => '6x5kg', 'GTIN' => '7896397900053', 'Código Interno' => '500045',
		),
		'meio' => vb_pallet_arroz_6x5(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.30.11' ),
		'label' => '6x5kg (SKU 500045)',
	),
	'500047' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Parboilizado', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '16cm', 'Profundidade' => '22cm',
			'Embalagem' => '10x1kg', 'GTIN' => '7896397900060', 'Código Interno' => '500047',
		),
		'meio' => vb_pallet_10x1(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.30.11' ),
		'label' => '10x1kg (SKU 500047)',
	),
	'500080' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Longo', 'Tipo' => 'UM',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,010kg', 'Peso Líquido' => '1kg',
			'Altura' => '10cm', 'Largura' => '20cm', 'Profundidade' => '5cm',
			'Embalagem' => '10x1kg', 'GTIN' => '7896397900084', 'Código Interno' => '500080',
		),
		'meio' => array(
			'Unidade de Medida' => 'Caixa',
			'Quantidade'        => '10',
			'Peso Bruto'        => '10,10kg',
			'Peso Líquido'      => '10kg',
			'Validade'          => '6 meses',
		),
		'meio_nome' => 'Caixa', 'trib' => vb_trib( '1006.30.21' ),
		'label' => '10x1kg (SKU 500080)',
	),
	'500080-5' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Arroz', 'Classe' => 'Longo', 'Tipo' => 'UM',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,010kg', 'Peso Líquido' => '1kg',
			'Altura' => '10cm', 'Largura' => '20cm', 'Profundidade' => '5cm',
			'Embalagem' => '5x1kg', 'GTIN' => '7896397900084', 'Código Interno' => '500080-5',
		),
		'meio' => array(
			'Unidade de Medida' => 'Caixa',
			'Quantidade'        => '5',
			'Peso Bruto'        => '5,05kg',
			'Peso Líquido'      => '5kg',
			'Validade'          => '6 meses',
		),
		'meio_nome' => 'Caixa', 'trib' => vb_trib( '1006.30.21' ),
		'label' => '5x1kg (SKU 500080-5)',
	),
	'510021' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '2,010kg', 'Peso Líquido' => '2kg',
			'Altura' => '5cm', 'Largura' => '17cm', 'Profundidade' => '27cm',
			'Embalagem' => '15x2kg', 'GTIN' => '7896397980048', 'Código Interno' => '510021',
		),
		'meio' => vb_pallet_15x2(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.29' ),
		'label' => '15x2kg (SKU 510021)',
	),
	'510020' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '17cm', 'Profundidade' => '27cm',
			'Embalagem' => '30x1kg', 'GTIN' => '7896397980031', 'Código Interno' => '510020',
		),
		'meio' => vb_pallet_30x1(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.29' ),
		'label' => '30x1kg (SKU 510020)',
	),
	'510022' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '16cm', 'Profundidade' => '22cm',
			'Embalagem' => '10x1kg', 'GTIN' => '7896397980031', 'Código Interno' => '510022',
		),
		'meio' => vb_pallet_10x1(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.29' ),
		'label' => '10x1kg (SKU 510022)',
	),
	'510031' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Feijão', 'Classe' => 'Preto', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '16cm', 'Profundidade' => '22cm',
			'Embalagem' => '30x1kg', 'GTIN' => '7896397980017', 'Código Interno' => '510031',
		),
		'meio' => vb_pallet_30x1(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.19' ),
		'label' => '30x1kg (SKU 510031)',
	),
	'510030' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Feijão', 'Classe' => 'Preto', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '16cm', 'Profundidade' => '22cm',
			'Embalagem' => '10x1kg', 'GTIN' => '7896397980017', 'Código Interno' => '510030',
		),
		'meio' => vb_pallet_10x1(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.19' ),
		'label' => '10x1kg (SKU 510030)',
	),
	'510040' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Feijão', 'Classe' => 'Bolinha', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '16cm', 'Profundidade' => '22cm',
			'Embalagem' => '10x1kg', 'GTIN' => '7896397980130', 'Código Interno' => '510040',
		),
		'meio' => vb_pallet_10x1(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.19' ),
		'label' => '',
	),
	'403010' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Palmito', 'Classe' => 'Pupunha', 'Tipo' => 'Inteiro',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '540g', 'Peso Líquido' => '300g',
			'Altura' => '14cm', 'Largura' => '9cm', 'Profundidade' => '8cm',
			'Embalagem' => '6x300g', 'GTIN' => '7896397950003', 'Código Interno' => '403010',
		),
		'meio' => array(
			'Unidade de Medida' => 'Caixa', 'Quantidade' => '6',
			'Peso Bruto' => '3,24kg', 'Peso Líquido' => '1,80kg', 'Validade' => '24 meses',
		),
		'meio_nome' => 'Caixa', 'trib' => vb_trib( '2008.91.00' ),
		'label' => '6x300g (SKU 403010)',
	),
	'403009' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Palmito', 'Classe' => 'Pupunha', 'Tipo' => 'Inteiro',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '330g', 'Peso Líquido' => '180g',
			'Altura' => '14cm', 'Largura' => '9cm', 'Profundidade' => '8cm',
			'Embalagem' => '12x180g', 'GTIN' => '7896397951000', 'Código Interno' => '403009',
		),
		'meio' => array(
			'Unidade de Medida' => 'Caixa', 'Quantidade' => '12',
			'Peso Bruto' => '3,96kg', 'Peso Líquido' => '2,16kg', 'Validade' => '24 meses',
		),
		'meio_nome' => 'Caixa', 'trib' => vb_trib( '2008.91.00' ),
		'label' => '12x180g (SKU 403009)',
	),
	'403012' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Palmito', 'Classe' => 'Pupunha', 'Tipo' => 'Picado',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '540g', 'Peso Líquido' => '300g',
			'Altura' => '14cm', 'Largura' => '9cm', 'Profundidade' => '8cm',
			'Embalagem' => '6x300g', 'GTIN' => '7896397960002', 'Código Interno' => '403012',
		),
		'meio' => array(
			'Unidade de Medida' => 'Caixa', 'Quantidade' => '6',
			'Peso Bruto' => '3,24kg', 'Peso Líquido' => '1,80kg', 'Validade' => '24 meses',
		),
		'meio_nome' => 'Caixa', 'trib' => vb_trib( '2008.91.00' ),
		'label' => '',
	),
	'403011' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Palmito', 'Classe' => 'Pupunha', 'Tipo' => 'Rodela',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '540g', 'Peso Líquido' => '300g',
			'Altura' => '14cm', 'Largura' => '9cm', 'Profundidade' => '8cm',
			'Embalagem' => '6x300g', 'GTIN' => '7896397955008', 'Código Interno' => '403011',
		),
		'meio' => array(
			'Unidade de Medida' => 'Caixa', 'Quantidade' => '6',
			'Peso Bruto' => '3,24kg', 'Peso Líquido' => '1,80kg', 'Validade' => '24 meses',
		),
		'meio_nome' => 'Caixa', 'trib' => vb_trib( '2008.91.00' ),
		'label' => '',
	),
	'414001' => array(
		'pacote' => array(
			'Marca' => 'Valle Branco', 'Categoria' => 'Queijo ralado', 'Classe' => 'Ralado', 'Tipo' => 'Fiapo',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '40g', 'Peso Líquido' => '40g',
			'Altura' => '15cm', 'Largura' => '10cm', 'Profundidade' => '1cm',
			'Embalagem' => '25x40g', 'GTIN' => '7896397400010', 'Código Interno' => '414001',
		),
		'meio' => array(
			'Unidade de Medida' => 'Caixa', 'Quantidade' => '25',
			'Peso Bruto' => '1,00kg', 'Peso Líquido' => '1,00kg', 'Validade' => '6 meses',
		),
		'meio_nome' => 'Caixa', 'trib' => vb_trib( '0406.20.00' ),
		'label' => '',
	),
	// Castelão / Aene / Feijão Castelão (dados sync oficiais).
	'500020' => array(
		'pacote' => array(
			'Marca' => 'Castelão', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '5,018kg', 'Peso Líquido' => '5kg',
			'Altura' => '7cm', 'Largura' => '24cm', 'Profundidade' => '36cm',
			'Embalagem' => '6x5kg', 'GTIN' => '7896397900336', 'Código Interno' => '500020',
		),
		'meio' => array_merge( vb_pallet_arroz_6x5(), array( 'DUN 14' => '78963979003360' ) ),
		'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.30.21' ),
		'label' => '6x5kg (SKU 500020)',
	),
	'500021' => array(
		'pacote' => array(
			'Marca' => 'Castelão', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '2,010kg', 'Peso Líquido' => '2kg',
			'Altura' => '5cm', 'Largura' => '17cm', 'Profundidade' => '27cm',
			'Embalagem' => '15x2kg', 'GTIN' => '7896397900589', 'Código Interno' => '500021',
		),
		'meio' => vb_pallet_15x2(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.30.21' ),
		'label' => '15x2kg (SKU 500021)',
	),
	'500001' => array(
		'pacote' => array(
			'Marca' => 'Aene', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '5,018kg', 'Peso Líquido' => '5kg',
			'Altura' => '7cm', 'Largura' => '24cm', 'Profundidade' => '36cm',
			'Embalagem' => '6x5kg', 'GTIN' => '7896397900114', 'Código Interno' => '500001',
		),
		'meio' => vb_pallet_arroz_6x5(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.30.21' ),
		'label' => '6x5kg (SKU 500001)',
	),
	'500002' => array(
		'pacote' => array(
			'Marca' => 'Aene', 'Categoria' => 'Arroz', 'Classe' => 'Longo Fino', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '2,010kg', 'Peso Líquido' => '2kg',
			'Altura' => '5cm', 'Largura' => '17cm', 'Profundidade' => '27cm',
			'Embalagem' => '15x2kg', 'GTIN' => '7896397900565', 'Código Interno' => '500002',
		),
		'meio' => vb_pallet_15x2(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '1006.30.21' ),
		'label' => '15x2kg (SKU 500002)',
	),
	'510002' => array(
		'pacote' => array(
			'Marca' => 'Castelão', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '2,010kg', 'Peso Líquido' => '2kg',
			'Altura' => '5cm', 'Largura' => '17cm', 'Profundidade' => '27cm',
			'Embalagem' => '15x2kg', 'GTIN' => '7896397980086', 'Código Interno' => '510002',
		),
		'meio' => array_merge( vb_pallet_15x2(), array( 'Largura' => '1,23m', 'Profundidade' => '1,12m', 'DUN 14' => '17896397980083' ) ),
		'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.29' ),
		'label' => '15x2kg (SKU 510002)',
	),
	'510001' => array(
		'pacote' => array(
			'Marca' => 'Castelão', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '16cm', 'Profundidade' => '22cm',
			'Embalagem' => '30x1kg', 'GTIN' => '7896397980079', 'Código Interno' => '510001',
		),
		'meio' => vb_pallet_30x1(), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.29' ),
		'label' => '30x1kg (SKU 510001)',
	),
	'510003' => array(
		'pacote' => array(
			'Marca' => 'Castelão', 'Categoria' => 'Feijão', 'Classe' => 'Carioca', 'Tipo' => '1',
			'Unidade de Medida' => 'UN', 'Peso Bruto' => '1,006kg', 'Peso Líquido' => '1kg',
			'Altura' => '5cm', 'Largura' => '16cm', 'Profundidade' => '22cm',
			'Embalagem' => '10x1kg', 'GTIN' => '7896397980079', 'Código Interno' => '510003',
		),
		'meio' => vb_pallet_10x1( '78963979800785' ), 'meio_nome' => 'Pallet', 'trib' => vb_trib( '0713.33.29' ),
		'label' => '10x1kg (SKU 510003)',
	),
);

// Produtos a atualizar: keep_sku => lista de SKUs (ordem).
$produtos = array(
	'500030' => array( '500030', '500032' ),       // Extra Premium
	'500040' => array( '500040' ),                   // Integral
	'500045' => array( '500045', '500047' ),       // Parboilizado
	'500080' => array( '500080', '500080-5' ),     // Arbório
	'510021' => array( '510021', '510020', '510022' ), // Carioca
	'510031' => array( '510031', '510030' ),       // Preto
	'510040' => array( '510040' ),                   // Bolinha
	'403010' => array( '403010', '403009' ),       // Palmito Inteiro
	'403012' => array( '403012' ),                   // Picado
	'403011' => array( '403011' ),                   // Rodelas
	'414001' => array( '414001' ),                   // Queijo
	'500020' => array( '500020', '500021' ),       // Castelão T1
	'510002' => array( '510002', '510001', '510003' ), // Feijão Castelão
	'500001' => array( '500001', '500002' ),       // Aene T1
);

foreach ( $produtos as $keep => $skus ) {
	$id = vb_find_sku( $keep );
	if ( ! $id ) {
		echo "MISSING keep {$keep}\n";
		continue;
	}

	$tabelas = array();
	$multi   = count( $skus ) > 1;
	foreach ( $skus as $sku ) {
		if ( empty( $fichas[ $sku ] ) ) {
			echo "  NO FICHA {$sku}\n";
			continue;
		}
		$f     = $fichas[ $sku ];
		$label = $multi ? $f['label'] : '';
		$tabelas = array_merge(
			$tabelas,
			vb_trio( $label, $f['pacote'], $f['meio'], $f['meio_nome'], $f['trib'] )
		);
	}

	update_post_meta( $id, '_vb_nutricao', VB_Prod_Meta::sanitize_nutricao( array( 'tabelas' => $tabelas ) ) );
	echo "OK #{$id} {$keep} tabs=" . count( $tabelas ) . ' ' . get_the_title( $id ) . "\n";
}

echo "DONE\n";
