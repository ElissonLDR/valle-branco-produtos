<?php
/**
 * Vincula imagens já enviadas na biblioteca de mídia aos produtos (capa).
 *
 * Uso: php bin/vincular-imagens-midia.php
 *
 * @package ValleBrancoProdutos
 */

require 'C:/xampp/htdocs/valle-branco/wp-load.php';

$map = array(
	'500005'   => 'Arroz-Aene-Mix-T1-6x5kg.webp',
	'500001'   => 'Arroz-Aene-T1-6x5kg.webp',
	'500002'   => 'Arroz-Aene-T1-15x2kg.webp',
	'500080'   => 'ArrozArborioValleBranco10x1.webp',
	'500080-5' => 'ArrozArborioValleBranco5x1.webp',
	'500024'   => 'Arroz-Castelao-Serie-Ouro-T1-6x5kg.webp',
	'500020'   => 'Arroz-Castelao-T1-6x5kg.webp',
	'500021'   => 'Arroz-Castelao-T1-15x2kg.webp',
	'500022'   => 'Arroz-Castelao-T2-6x5kg.webp',
	'500023'   => 'Arroz-Castelao-T3-6x5kg.webp',
	'500030'   => 'Arroz-Extra-Premium-Valle-Branco-T1-6x5kg.webp',
	'500032'   => 'Arroz-Extra-Premium-Valle-Branco-T1-15x2kg.webp',
	'500040'   => 'Arroz-Integral-Valle-Branco-T1-10x1kg.webp',
	'500045'   => 'Arroz-Parboilizado-Valle-Branco-T1-6x5kg.webp',
	'500047'   => 'Arroz-Parboilizado-Valle-Branco-T1-10x1kg.webp',
	'500060'   => 'Arroz-Vita-Abaixo-Padrao-6x5kg.webp',
	'510040'   => 'Feijao-Bolinha-Valle-Branco-T1-10x1kg.webp',
	'510022'   => 'Feijao-Carioca-Valle-Branco-T1-10x1kg.webp',
	'510021'   => 'Feijao-Carioca-Valle-Branco-T1-15x2kg.webp',
	'510020'   => 'Feijao-Carioca-Valle-Branco-T1-30x1kg.webp',
	'510004'   => 'Feijao-Castelao-Economico-T1-30x1kg.webp',
	'510003'   => 'Feijao-Castelao-T1-10x1kg.webp',
	'510002'   => 'Feijao-Castelao-T1-15x2kg.webp',
	'510001'   => 'Feijao-Castelao-T1-30x1kg.webp',
	'510031'   => 'Feijao-Preto-Valle-Branco-T1-30x1kg.webp',
	'510030'   => 'Feijao-Preto-Valle-Branco-T1-30x1kg.webp', // fallback: só há arte 30x1kg
	'403010'   => 'Palmito-Valle-Branco-Pupunha-Inteiro-6x300g.webp',
	'403009'   => 'Palmito-Valle-Branco-Pupunha-Inteiro-12x180g.webp',
	'403012'   => 'Palmito-Valle-Branco-Pupunha-Picado-6x300g.webp',
	'403011'   => 'Palmito-Valle-Branco-Pupunha-Rodelas-6x300g.webp',
	'414001'   => 'Queijo-Ralado-Valle-Branco-Fiapo-25x40g.webp',
);

/**
 * Localiza attachment pelo nome do arquivo.
 *
 * @param string $filename Nome do arquivo.
 * @return int
 */
function vb_find_att_by_filename( $filename ) {
	global $wpdb;

	$like = '%' . $wpdb->esc_like( $filename );
	$id   = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
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
			'posts_per_page' => 20,
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

$ok   = 0;
$fail = 0;

foreach ( $map as $sku => $file ) {
	$posts = get_posts(
		array(
			'post_type'      => 'vb_produto',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'meta_key'       => '_vb_sku',
			'meta_value'     => $sku,
			'fields'         => 'ids',
		)
	);
	if ( empty( $posts ) ) {
		echo "NO PRODUCT {$sku}\n";
		++$fail;
		continue;
	}

	$pid = (int) $posts[0];
	$att = vb_find_att_by_filename( $file );
	if ( ! $att ) {
		echo "NO ATT {$sku} | {$file}\n";
		++$fail;
		continue;
	}

	set_post_thumbnail( $pid, $att );
	$title = get_the_title( $pid );
	echo "OK #{$pid} [{$sku}] ← #{$att} {$file} | {$title}\n";
	++$ok;
}

echo "---\nOK={$ok} FAIL={$fail}\n";
