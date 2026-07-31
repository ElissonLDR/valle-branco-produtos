<?php
/**
 * Sincroniza 1 produto por SKU (fichas do catálogo).
 * Uso: php bin/sync-skus-catalogo.php
 */
require 'C:/xampp/htdocs/valle-branco/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$assets = 'C:/Users/eliss/Desktop/V4 Company/02. SITES/VALLE BRANCO/site-valle-branco/src/assets';

/**
 * Catálogo completo — 1 post por embalagem/SKU.
 */
$catalogo = array(
	// Valle Branco — Arroz
	array( 'sku' => '500030', 'slug' => 'arroz-extra-premium-valle-branco-t1-5kg', 'title' => 'Arroz Extra Premium Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900015', 'embalagem' => '6x5kg', 'image' => 'arroz-extra-premium-5kg.webp', 'order' => 10 ),
	array( 'sku' => '500032', 'slug' => 'arroz-extra-premium-valle-branco-t1-2kg', 'title' => 'Arroz Extra Premium Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Arroz', 'pesos' => '2kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900039', 'embalagem' => '15x2kg', 'image' => 'arroz-extra-premium-5kg.webp', 'order' => 11 ),
	array( 'sku' => '500040', 'slug' => 'arroz-integral-valle-branco-t1-1kg', 'title' => 'Arroz Integral Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Arroz', 'pesos' => '1kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900046', 'embalagem' => '10x1kg', 'image' => 'arroz-integral-1kg.webp', 'order' => 20 ),
	array( 'sku' => '500045', 'slug' => 'arroz-parboilizado-valle-branco-t1-5kg', 'title' => 'Arroz Parboilizado Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Parboilizado', 'tipo' => '1', 'gtin' => '7896397900053', 'embalagem' => '6x5kg', 'image' => 'arroz-parboilizado-5kg.webp', 'order' => 30 ),
	array( 'sku' => '500047', 'slug' => 'arroz-parboilizado-valle-branco-t1-1kg', 'title' => 'Arroz Parboilizado Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Arroz', 'pesos' => '1kg', 'classe' => 'Parboilizado', 'tipo' => '1', 'gtin' => '7896397900060', 'embalagem' => '10x1kg', 'image' => 'arroz-parboilizado-5kg.webp', 'order' => 31 ),
	array( 'sku' => '500080', 'slug' => 'arroz-arborio-valle-branco-10x1kg', 'title' => 'Arroz Arbório Valle Branco', 'marca' => 'Valle Branco', 'cat' => 'Arroz', 'pesos' => '1kg', 'classe' => 'Longo', 'tipo' => 'UM', 'gtin' => '7896397900084', 'embalagem' => '10x1kg', 'image' => 'arroz-extra-premium-5kg.webp', 'order' => 40 ),
	array( 'sku' => '500080-5', 'slug' => 'arroz-arborio-valle-branco-5x1kg', 'title' => 'Arroz Arbório Valle Branco', 'marca' => 'Valle Branco', 'cat' => 'Arroz', 'pesos' => '1kg', 'classe' => 'Longo', 'tipo' => 'UM', 'gtin' => '7896397900084', 'embalagem' => '5x1kg', 'image' => 'arroz-extra-premium-5kg.webp', 'order' => 41 ),

	// Valle Branco — Feijão
	array( 'sku' => '510021', 'slug' => 'feijao-carioca-valle-branco-t1-2kg', 'title' => 'Feijão Carioca Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Feijão', 'pesos' => '2kg', 'classe' => 'Carioca', 'tipo' => '1', 'gtin' => '7896397980048', 'embalagem' => '15x2kg', 'image' => 'feijao-carioca-1kg.webp', 'order' => 50 ),
	array( 'sku' => '510020', 'slug' => 'feijao-carioca-valle-branco-t1-30x1kg', 'title' => 'Feijão Carioca Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Feijão', 'pesos' => '1kg', 'classe' => 'Carioca', 'tipo' => '1', 'gtin' => '7896397980031', 'embalagem' => '30x1kg', 'image' => 'feijao-carioca-1kg.webp', 'order' => 51 ),
	array( 'sku' => '510022', 'slug' => 'feijao-carioca-valle-branco-t1-10x1kg', 'title' => 'Feijão Carioca Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Feijão', 'pesos' => '1kg', 'classe' => 'Carioca', 'tipo' => '1', 'gtin' => '7896397980031', 'embalagem' => '10x1kg', 'image' => 'feijao-carioca-1kg.webp', 'order' => 52 ),
	array( 'sku' => '510031', 'slug' => 'feijao-preto-valle-branco-t1-30x1kg', 'title' => 'Feijão Preto Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Feijão', 'pesos' => '1kg', 'classe' => 'Preto', 'tipo' => '1', 'gtin' => '7896397980017', 'embalagem' => '30x1kg', 'image' => 'feijao-preto-1kg.webp', 'order' => 60 ),
	array( 'sku' => '510030', 'slug' => 'feijao-preto-valle-branco-t1-10x1kg', 'title' => 'Feijão Preto Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Feijão', 'pesos' => '1kg', 'classe' => 'Preto', 'tipo' => '1', 'gtin' => '7896397980017', 'embalagem' => '10x1kg', 'image' => 'feijao-preto-1kg.webp', 'order' => 61 ),
	array( 'sku' => '510040', 'slug' => 'feijao-bolinha-valle-branco-t1-1kg', 'title' => 'Feijão Bolinha Valle Branco tipo 1', 'marca' => 'Valle Branco', 'cat' => 'Feijão', 'pesos' => '1kg', 'classe' => 'Bolinha', 'tipo' => '1', 'gtin' => '7896397980130', 'embalagem' => '10x1kg', 'image' => 'feijao-bolinha-1kg.webp', 'order' => 70 ),

	// Valle Branco — Queijo / Palmito
	array( 'sku' => '414001', 'slug' => 'queijo-ralado-valle-branco-fiapo-40g', 'title' => 'Queijo Ralado Valle Branco Fiapo', 'marca' => 'Valle Branco', 'cat' => 'Queijo ralado', 'pesos' => '40g', 'classe' => 'Ralado', 'tipo' => 'Fiapo', 'gtin' => '7896397400010', 'embalagem' => '25x40g', 'image' => 'queijo-ralado-40g.webp', 'order' => 75 ),
	array( 'sku' => '403010', 'slug' => 'palmito-valle-branco-pupunha-inteiro-300g', 'title' => 'Palmito Valle Branco Pupunha Inteiro', 'marca' => 'Valle Branco', 'cat' => 'Palmito', 'pesos' => '300g', 'classe' => 'Pupunha', 'tipo' => 'Inteiro', 'gtin' => '7896397950003', 'embalagem' => '6x300g', 'image' => 'palmito-inteiro-300g.webp', 'order' => 78 ),
	array( 'sku' => '403009', 'slug' => 'palmito-valle-branco-pupunha-inteiro-180g', 'title' => 'Palmito Valle Branco Pupunha Inteiro', 'marca' => 'Valle Branco', 'cat' => 'Palmito', 'pesos' => '180g', 'classe' => 'Pupunha', 'tipo' => 'Inteiro', 'gtin' => '7896397951000', 'embalagem' => '12x180g', 'image' => 'palmito-inteiro-300g.webp', 'order' => 79 ),
	array( 'sku' => '403012', 'slug' => 'palmito-valle-branco-pupunha-picado-300g', 'title' => 'Palmito Valle Branco Pupunha Picado', 'marca' => 'Valle Branco', 'cat' => 'Palmito', 'pesos' => '300g', 'classe' => 'Pupunha', 'tipo' => 'Picado', 'gtin' => '7896397960002', 'embalagem' => '6x300g', 'image' => 'palmito-picado-300g.webp', 'order' => 80 ),
	array( 'sku' => '403011', 'slug' => 'palmito-valle-branco-pupunha-rodelas-300g', 'title' => 'Palmito Valle Branco Pupunha Rodelas', 'marca' => 'Valle Branco', 'cat' => 'Palmito', 'pesos' => '300g', 'classe' => 'Pupunha', 'tipo' => 'Rodela', 'gtin' => '7896397955008', 'embalagem' => '6x300g', 'image' => 'palmito-rodelas-300g.webp', 'order' => 82 ),

	// Castelão
	array( 'sku' => '500020', 'slug' => 'arroz-castelao-t1-5kg', 'title' => 'Arroz Castelão tipo 1', 'marca' => 'Castelão', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900336', 'embalagem' => '6x5kg', 'image' => 'arroz-castelao-5kg.webp', 'order' => 100, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '5,018kg', 'pl' => '5kg', 'a' => '7cm', 'l' => '24cm', 'p' => '36cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,44m', 'l' => '1,18m', 'p' => '1,26m', 'dun' => '78963979003360' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '500021', 'slug' => 'arroz-castelao-t1-2kg', 'title' => 'Arroz Castelão tipo 1', 'marca' => 'Castelão', 'cat' => 'Arroz', 'pesos' => '2kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900589', 'embalagem' => '15x2kg', 'image' => 'arroz-castelao-5kg.webp', 'order' => 101, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '2,010kg', 'pl' => '2kg', 'a' => '5cm', 'l' => '17cm', 'p' => '27cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,56m', 'l' => '1,25m', 'p' => '1,13m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '500022', 'slug' => 'arroz-castelao-t2-5kg', 'title' => 'Arroz Castelão tipo 2', 'marca' => 'Castelão', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Longo Fino', 'tipo' => '2', 'gtin' => '7896397900220', 'embalagem' => '6x5kg', 'image' => 'arroz-castelao-5kg.webp', 'order' => 110, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '5,018kg', 'pl' => '5kg', 'a' => '7cm', 'l' => '24cm', 'p' => '36cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,44m', 'l' => '1,18m', 'p' => '1,26m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '500023', 'slug' => 'arroz-castelao-t3-5kg', 'title' => 'Arroz Castelão tipo 3', 'marca' => 'Castelão', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Longo Fino', 'tipo' => '3', 'gtin' => '7896397900558', 'embalagem' => '6x5kg', 'image' => 'arroz-castelao-5kg.webp', 'order' => 120, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '5,018kg', 'pl' => '5kg', 'a' => '7cm', 'l' => '24cm', 'p' => '36cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,44m', 'l' => '1,18m', 'p' => '1,26m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '500024', 'slug' => 'arroz-castelao-serie-ouro-t1-5kg', 'title' => 'Arroz Castelão Série Ouro tipo 1', 'marca' => 'Castelão', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900343', 'embalagem' => '6x5kg', 'image' => 'arroz-castelao-5kg.webp', 'order' => 130, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '5,018kg', 'pl' => '5kg', 'a' => '7cm', 'l' => '24cm', 'p' => '36cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,44m', 'l' => '1,18m', 'p' => '1,26m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '510002', 'slug' => 'feijao-castelao-t1-2kg', 'title' => 'Feijão Castelão tipo 1', 'marca' => 'Castelão', 'cat' => 'Feijão', 'pesos' => '2kg', 'classe' => 'Carioca', 'tipo' => '1', 'gtin' => '7896397980086', 'embalagem' => '15x2kg', 'image' => 'feijao-carioca-1kg.webp', 'order' => 140, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '2,010kg', 'pl' => '2kg', 'a' => '5cm', 'l' => '17cm', 'p' => '27cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,56m', 'l' => '1,23m', 'p' => '1,12m', 'dun' => '17896397980083' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '0713.33.29', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '510001', 'slug' => 'feijao-castelao-t1-30x1kg', 'title' => 'Feijão Castelão tipo 1', 'marca' => 'Castelão', 'cat' => 'Feijão', 'pesos' => '1kg', 'classe' => 'Carioca', 'tipo' => '1', 'gtin' => '7896397980079', 'embalagem' => '30x1kg', 'image' => 'feijao-carioca-1kg.webp', 'order' => 141, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '1,006kg', 'pl' => '1kg', 'a' => '5cm', 'l' => '16cm', 'p' => '22cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,56m', 'l' => '1,23m', 'p' => '1,12m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '0713.33.29', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '510003', 'slug' => 'feijao-castelao-t1-10x1kg', 'title' => 'Feijão Castelão tipo 1', 'marca' => 'Castelão', 'cat' => 'Feijão', 'pesos' => '1kg', 'classe' => 'Carioca', 'tipo' => '1', 'gtin' => '7896397980079', 'embalagem' => '10x1kg', 'image' => 'feijao-carioca-1kg.webp', 'order' => 142, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '1,006kg', 'pl' => '1kg', 'a' => '5cm', 'l' => '16cm', 'p' => '22cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '100', 'lastro' => '10', 'alt_cam' => '10', 'pb' => '1.020kg', 'pl' => '1.000kg', 'a' => '1,33m', 'l' => '1,25m', 'p' => '1,05m', 'dun' => '78963979800785' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '0713.33.29', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '510004', 'slug' => 'feijao-castelao-economico-t1-1kg', 'title' => 'Feijão Castelão Econômico tipo 1', 'marca' => 'Castelão', 'cat' => 'Feijão', 'pesos' => '1kg', 'classe' => 'Carioca', 'tipo' => '1', 'gtin' => '7896397980109', 'embalagem' => '30x1kg', 'image' => 'feijao-castelao-economico-1kg.webp', 'order' => 150, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '1,006kg', 'pl' => '1kg', 'a' => '5cm', 'l' => '16cm', 'p' => '22cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,56m', 'l' => '1,23m', 'p' => '1,12m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '0713.33.29', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),

	// Aene / Vita
	array( 'sku' => '500001', 'slug' => 'arroz-aene-t1-5kg', 'title' => 'Arroz Aene tipo 1', 'marca' => 'Aene', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900114', 'embalagem' => '6x5kg', 'image' => 'arroz-aene-mix-5kg.webp', 'order' => 160, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '5,018kg', 'pl' => '5kg', 'a' => '7cm', 'l' => '24cm', 'p' => '36cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,44m', 'l' => '1,18m', 'p' => '1,26m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '500002', 'slug' => 'arroz-aene-t1-2kg', 'title' => 'Arroz Aene tipo 1', 'marca' => 'Aene', 'cat' => 'Arroz', 'pesos' => '2kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900565', 'embalagem' => '15x2kg', 'image' => 'arroz-aene-mix-5kg.webp', 'order' => 161, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '2,010kg', 'pl' => '2kg', 'a' => '5cm', 'l' => '17cm', 'p' => '27cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,56m', 'l' => '1,25m', 'p' => '1,13m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '500005', 'slug' => 'arroz-aene-mix-t1-5kg', 'title' => 'Arroz Aene Mix tipo 1', 'marca' => 'Aene', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Longo Fino', 'tipo' => '1', 'gtin' => '7896397900886', 'embalagem' => '6x5kg', 'image' => 'arroz-aene-mix-5kg.webp', 'order' => 170, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '5,018kg', 'pl' => '5kg', 'a' => '7cm', 'l' => '24cm', 'p' => '36cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,44m', 'l' => '1,18m', 'p' => '1,26m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
	array( 'sku' => '500060', 'slug' => 'arroz-vita-abaixo-padrao-5kg', 'title' => 'Arroz Vita Abaixo Padrão', 'marca' => 'Vita', 'cat' => 'Arroz', 'pesos' => '5kg', 'classe' => 'Abaixo Padrão', 'tipo' => '1', 'gtin' => '7896397900572', 'embalagem' => '6x5kg', 'image' => 'arroz-vita-abaixo-5kg.webp', 'order' => 180, 'validade' => '6 meses',
		'pacote' => array( 'um' => 'UN', 'pb' => '5,018kg', 'pl' => '5kg', 'a' => '7cm', 'l' => '24cm', 'p' => '36cm' ),
		'pallet' => array( 'um' => 'Fardo', 'qtd' => '49', 'lastro' => '7', 'alt_cam' => '7', 'pb' => '1.490kg', 'pl' => '1.470kg', 'a' => '1,44m', 'l' => '1,18m', 'p' => '1,26m', 'dun' => '' ),
		'trib' => array( 'cst_icms' => '020', 'icms' => '18,00%', 'red' => '61,11%', 'ncm' => '1006.30.21', 'cest' => '', 'cst_pc' => '06', 'pis' => '0,00%', 'cofins' => '0,00%', 'ipi' => '0,00%', 'iva' => '0,00%' ) ),
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
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash' ),
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

function vb_ficha_to_nutricao( $item ) {
	$tabelas = array();

	// Pacote = identificação + dados de pacote (como no catálogo).
	$pacote_rows = array(
		'Marca'     => $item['marca'],
		'Categoria' => $item['cat'],
		'Classe'    => $item['classe'],
		'Tipo'      => $item['tipo'],
	);
	if ( ! empty( $item['pacote'] ) ) {
		$p = $item['pacote'];
		$pacote_rows['Unidade de Medida'] = $p['um'] ?? '';
		$pacote_rows['Peso Bruto']        = $p['pb'] ?? '';
		$pacote_rows['Peso Líquido']      = $p['pl'] ?? '';
		if ( isset( $p['a'], $p['l'], $p['p'] ) ) {
			$pacote_rows['Altura']       = $p['a'];
			$pacote_rows['Largura']      = $p['l'];
			$pacote_rows['Profundidade'] = $p['p'];
		}
	} else {
		$pacote_rows['Peso']      = $item['pesos'];
		$pacote_rows['Embalagem'] = $item['embalagem'];
	}
	$pacote_rows['GTIN']           = $item['gtin'];
	$pacote_rows['Código Interno'] = $item['sku'];
	$tabelas[]                     = vb_tabela_block( 'Pacote', 'azul', $pacote_rows );

	if ( ! empty( $item['pallet'] ) ) {
		$pl = $item['pallet'];
		$tabelas[] = vb_tabela_block(
			'Pallet',
			'azul',
			array(
				'Unidade de Medida' => $pl['um'] ?? '',
				'Quantidade'        => $pl['qtd'] ?? '',
				'Lastro'            => $pl['lastro'] ?? '',
				'Altura Camadas'    => $pl['alt_cam'] ?? '',
				'Peso Bruto'        => $pl['pb'] ?? '',
				'Peso Líquido'      => $pl['pl'] ?? '',
				'Altura'            => $pl['a'] ?? '',
				'Largura'           => $pl['l'] ?? '',
				'Profundidade'      => $pl['p'] ?? '',
				'DUN 14'            => $pl['dun'] ?? '',
				'Validade'          => $item['validade'] ?? '',
			)
		);
	}

	if ( ! empty( $item['caixa'] ) ) {
		$cx = $item['caixa'];
		$tabelas[] = vb_tabela_block(
			'Caixa',
			'azul',
			array(
				'Unidade de Medida' => $cx['um'] ?? '',
				'Quantidade'        => $cx['qtd'] ?? '',
				'Peso Bruto'        => $cx['pb'] ?? '',
				'Peso Líquido'      => $cx['pl'] ?? '',
				'Altura'            => $cx['a'] ?? '',
				'Largura'           => $cx['l'] ?? '',
				'Profundidade'      => $cx['p'] ?? '',
				'DUN 14'            => $cx['dun'] ?? '',
				'Validade'          => $item['validade'] ?? '',
			)
		);
	}

	if ( ! empty( $item['trib'] ) ) {
		$t = $item['trib'];
		$tabelas[] = vb_tabela_block(
			'Tributação',
			'ouro',
			array(
				'CST (ICMS)'           => $t['cst_icms'] ?? '',
				'% ICMS'               => $t['icms'] ?? '',
				'% Red. Base ICMS'     => $t['red'] ?? '',
				'NCM'                  => $t['ncm'] ?? '',
				'CEST'                 => $t['cest'] ?? '',
				'CST (PIS/COFINS)'     => $t['cst_pc'] ?? '',
				'% PIS'                => $t['pis'] ?? '',
				'% COFINS'             => $t['cofins'] ?? '',
				'% IPI'                => $t['ipi'] ?? '',
				'% IVA / ICMS ST'      => $t['iva'] ?? '',
			)
		);
	}

	return array( 'tabelas' => $tabelas );
}

function vb_tabela_block( $titulo, $estilo, $rows ) {
	$linhas = array();
	foreach ( $rows as $k => $v ) {
		if ( '' === (string) $v || '—' === (string) $v ) {
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

foreach ( array( 'Arroz', 'Feijão', 'Palmito', 'Queijo ralado' ) as $c ) {
	vb_ensure_term_id( $c, 'vb_categoria_produto' );
}
foreach ( array( 'Valle Branco', 'Castelão', 'Aene', 'Vita' ) as $m ) {
	vb_ensure_term_id( $m, 'vb_marca' );
}

$keep_skus = array();
$ok        = 0;

foreach ( $catalogo as $item ) {
	$keep_skus[] = $item['sku'];
	$display     = $item['title'] . ' — ' . $item['embalagem'];
	$pid         = vb_find_by_sku( $item['sku'] );

	$data = array(
		'post_type'    => 'vb_produto',
		'post_status'  => 'publish',
		'post_title'   => $display,
		'post_name'    => $item['slug'],
		'post_content' => '',
		'menu_order'   => $item['order'],
	);

	if ( $pid ) {
		// Restaura da lixeira se necessário.
		if ( 'trash' === get_post_status( $pid ) ) {
			wp_untrash_post( $pid );
		}
		$data['ID'] = $pid;
		wp_update_post( $data );
		$action = 'UPD';
	} else {
		$pid = wp_insert_post( $data, true );
		if ( is_wp_error( $pid ) || ! $pid ) {
			echo "ERRO {$item['sku']}\n";
			continue;
		}
		$action = 'NEW';
	}

	update_post_meta( $pid, '_vb_sku', $item['sku'] );
	update_post_meta( $pid, '_vb_catalogo', '1' );
	delete_post_meta( $pid, '_vb_origem' );
	update_post_meta( $pid, '_vb_marca', $item['marca'] );
	update_post_meta( $pid, '_vb_categoria', $item['cat'] );
	update_post_meta( $pid, '_vb_pesos', $item['pesos'] );
	update_post_meta( $pid, '_vb_gtin', $item['gtin'] );
	update_post_meta( $pid, '_vb_embalagem', $item['embalagem'] );
	update_post_meta( $pid, '_vb_classe', $item['classe'] );
	update_post_meta( $pid, '_vb_tipo', $item['tipo'] );
	update_post_meta( $pid, '_vb_nutricao', VB_Prod_Meta::sanitize_nutricao( vb_ficha_to_nutricao( $item ) ) );

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
	echo "{$action} [{$item['sku']}] {$display}\n";
}

// Produtos publicados fora do catálogo: marca como mapa e tira da vitrine (private).
// Não exclui físicos com relação no mapa — só remove da vitrine.
$all = get_posts(
	array(
		'post_type'      => 'vb_produto',
		'post_status'    => array( 'publish', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
$removed = 0;
foreach ( $all as $oid ) {
	$sku = (string) get_post_meta( $oid, '_vb_sku', true );
	if ( in_array( $sku, $keep_skus, true ) ) {
		continue;
	}
	update_post_meta( $oid, '_vb_origem', 'mapa' );
	delete_post_meta( $oid, '_vb_catalogo' );
	if ( 'publish' === get_post_status( $oid ) ) {
		wp_update_post(
			array(
				'ID'          => $oid,
				'post_status' => 'private',
			)
		);
		++$removed;
		echo "PRIVATE #{$oid} [{$sku}]\n";
	}
}

echo "---\nSincronizados: {$ok}\nRemovidos da vitrine: {$removed}\n";
echo 'Publicados: ' . (int) wp_count_posts( 'vb_produto' )->publish . "\n";
