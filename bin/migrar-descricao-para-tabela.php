<?php
/**
 * Migra HTML da descrição (ficha) → meta `_vb_nutricao` e limpa a descrição.
 *
 * Uso: php bin/migrar-descricao-para-tabela.php
 *
 * @package ValleBrancoProdutos
 */

require 'C:/xampp/htdocs/valle-branco/wp-load.php';

/**
 * Converte HTML da ficha em estrutura de tabela editável.
 *
 * @param string $html Conteúdo.
 * @return array{porcao:string,colunas:string[],linhas:array<int,string[]>}|null
 */
function vb_parse_ficha_to_nutricao( $html ) {
	$html = trim( (string) $html );
	if ( '' === $html ) {
		return null;
	}

	$linhas = array();

	if ( ! class_exists( 'DOMDocument' ) ) {
		echo "DOMDocument indisponível\n";
		return null;
	}

	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="vb-root">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();

	$root = $dom->getElementById( 'vb-root' );
	if ( ! $root ) {
		return null;
	}

	$section = '';
	foreach ( $root->childNodes as $node ) {
		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			continue;
		}

		$tag = strtolower( $node->nodeName );

		if ( 'h3' === $tag ) {
			$section = trim( $node->textContent );
			if ( '' !== $section ) {
				$linhas[] = array( $section, '' );
			}
			continue;
		}

		if ( 'table' !== $tag ) {
			continue;
		}

		$rows = $node->getElementsByTagName( 'tr' );
		foreach ( $rows as $tr ) {
			$ths = $tr->getElementsByTagName( 'th' );
			$tds = $tr->getElementsByTagName( 'td' );
			$campo = $ths->length ? trim( $ths->item( 0 )->textContent ) : '';
			$valor = $tds->length ? trim( $tds->item( 0 )->textContent ) : '';
			if ( '' === $campo && '' === $valor ) {
				continue;
			}
			$linhas[] = array( $campo, $valor );
		}
	}

	if ( empty( $linhas ) ) {
		return null;
	}

	return array(
		'porcao'  => '',
		'colunas' => array( 'Campo', 'Valor' ),
		'linhas'  => $linhas,
	);
}

$q = new WP_Query(
	array(
		'post_type'      => 'vb_produto',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);

$ok     = 0;
$skip   = 0;
$empty  = 0;
$report = array();

foreach ( $q->posts as $post ) {
	$html = $post->post_content;
	$data = vb_parse_ficha_to_nutricao( $html );

	if ( ! $data ) {
		// Já vazio ou sem ficha reconhecível.
		if ( '' === trim( (string) $html ) ) {
			++$empty;
			$report[] = "EMPTY | #{$post->ID} | {$post->post_title}";
			continue;
		}
		++$skip;
		$report[] = "SKIP  | #{$post->ID} | {$post->post_title}";
		continue;
	}

	update_post_meta( $post->ID, '_vb_nutricao', VB_Prod_Meta::sanitize_nutricao( $data ) );

	wp_update_post(
		array(
			'ID'           => $post->ID,
			'post_content' => '',
		)
	);

	++$ok;
	$report[] = 'OK    | #' . $post->ID . ' | ' . $post->post_title . ' | linhas=' . count( $data['linhas'] );
}

echo implode( "\n", $report ) . "\n";
echo "---\nMigrados: {$ok}\nSem conteúdo: {$empty}\nIgnorados: {$skip}\n";
