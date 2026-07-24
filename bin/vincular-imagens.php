<?php
/**
 * Importa imagens de src/assets e vincula como capa dos produtos vb_produto.
 *
 * Uso: php bin/vincular-imagens.php
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	require 'C:/xampp/htdocs/valle-branco/wp-load.php';
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$assets_dir = 'C:/Users/eliss/Desktop/V4 Company/02. SITES/VALLE BRANCO/site-valle-branco/src/assets';

/**
 * Remove acentos e normaliza.
 *
 * @param string $s Texto.
 * @return string
 */
function vb_norm( $s ) {
	$s = wp_strip_all_tags( (string) $s );
	$s = remove_accents( $s );
	$s = strtoupper( $s );
	$s = preg_replace( '/\s+/', ' ', $s );
	return trim( $s );
}

/**
 * Decide qual arquivo de imagem usar para o título do produto.
 *
 * @param string $title Título.
 * @return string|null Nome do arquivo.
 */
function vb_match_image( $title ) {
	$t = vb_norm( $title );

	// Ignorar itens que não são vitrine.
	$skip = array( 'DETECTOR', 'FARELO', 'PALHA', 'BANDINHA', 'MILHO', 'SOJA', 'QUEBRADOS', 'DO TANI' );
	foreach ( $skip as $sk ) {
		if ( false !== strpos( $t, $sk ) ) {
			return null;
		}
	}

	if ( false !== strpos( $t, 'QUEIJO' ) ) {
		return 'queijo-ralado-40g.webp';
	}
	if ( false !== strpos( $t, 'PUPUNHA INT' ) || ( false !== strpos( $t, 'PALM' ) && false !== strpos( $t, 'INT' ) ) ) {
		return 'palmito-inteiro-300g.webp';
	}
	if ( false !== strpos( $t, 'PUPUNHA PIC' ) || ( false !== strpos( $t, 'PALM' ) && false !== strpos( $t, 'PIC' ) ) ) {
		return 'palmito-picado-300g.webp';
	}
	if ( false !== strpos( $t, 'PUPUNHA ROD' ) || ( false !== strpos( $t, 'PALM' ) && false !== strpos( $t, 'ROD' ) ) ) {
		return 'palmito-rodelas-300g.webp';
	}

	if ( false !== strpos( $t, 'FEIJAO' ) || false !== strpos( $t, 'FEIJÃO' ) ) {
		if ( false !== strpos( $t, 'BOLINHA' ) ) {
			return 'feijao-bolinha-1kg.webp';
		}
		if ( false !== strpos( $t, 'PRETO' ) ) {
			return 'feijao-preto-1kg.webp';
		}
		if ( false !== strpos( $t, 'CASTELAO' ) && false !== strpos( $t, 'ECONOM' ) ) {
			return 'feijao-castelao-economico-1kg.webp';
		}
		if ( false !== strpos( $t, 'CASTELAO' ) ) {
			return 'feijao-carioca-1kg.webp';
		}
		if ( false !== strpos( $t, 'AENE' ) ) {
			return 'feijao-aene-mix-1kg.webp';
		}
		// Feijão Valle Branco carioca (padrão).
		return 'feijao-carioca-1kg.webp';
	}

	if ( false !== strpos( $t, 'ARROZ' ) ) {
		if ( false !== strpos( $t, 'INTEGRAL' ) ) {
			return 'arroz-integral-1kg.webp';
		}
		if ( false !== strpos( $t, 'PARBOILIZADO' ) ) {
			return 'arroz-parboilizado-5kg.webp';
		}
		if ( false !== strpos( $t, 'ARBORIO' ) ) {
			return 'arroz-extra-premium-5kg.webp';
		}
		if ( false !== strpos( $t, 'VITA' ) && false !== strpos( $t, 'ABAIXO' ) ) {
			return 'arroz-vita-abaixo-5kg.webp';
		}
		if ( false !== strpos( $t, 'VITA' ) ) {
			return 'arroz-vita-tipo-1-5kg.webp';
		}
		if ( false !== strpos( $t, 'AENE' ) ) {
			return 'arroz-aene-mix-5kg.webp';
		}
		if ( false !== strpos( $t, 'CASTELAO' ) ) {
			return 'arroz-castelao-5kg.webp';
		}
		if ( false !== strpos( $t, 'VALLE BRANCO' ) || false !== strpos( $t, 'V.BRANCO' ) ) {
			return 'arroz-extra-premium-5kg.webp';
		}
	}

	return null;
}

/**
 * Garante attachment na biblioteca (reutiliza por nome de arquivo).
 *
 * @param string $filepath Caminho absoluto.
 * @param string $filename Nome.
 * @return int Attachment ID ou 0.
 */
function vb_ensure_attachment( $filepath, $filename ) {
	static $cache = array();

	if ( isset( $cache[ $filename ] ) ) {
		return $cache[ $filename ];
	}

	// Já existe na mídia?
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => '_vb_prod_asset',
			'meta_value'     => $filename,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $existing ) ) {
		$cache[ $filename ] = (int) $existing[0];
		return $cache[ $filename ];
	}

	if ( ! file_exists( $filepath ) ) {
		echo "ARQUIVO AUSENTE: {$filename}\n";
		$cache[ $filename ] = 0;
		return 0;
	}

	$tmp = wp_tempnam( $filename );
	if ( ! copy( $filepath, $tmp ) ) {
		echo "FALHA AO COPIAR: {$filename}\n";
		$cache[ $filename ] = 0;
		return 0;
	}

	$file_array = array(
		'name'     => $filename,
		'tmp_name' => $tmp,
	);

	$att_id = media_handle_sideload( $file_array, 0, null );
	if ( is_wp_error( $att_id ) ) {
		@unlink( $tmp );
		echo 'ERRO UPLOAD ' . $filename . ': ' . $att_id->get_error_message() . "\n";
		$cache[ $filename ] = 0;
		return 0;
	}

	update_post_meta( $att_id, '_vb_prod_asset', $filename );
	wp_update_post(
		array(
			'ID'         => $att_id,
			'post_title' => preg_replace( '/\.[^.]+$/', '', str_replace( '-', ' ', $filename ) ),
		)
	);

	$cache[ $filename ] = (int) $att_id;
	echo "IMPORTADO: {$filename} → #{$att_id}\n";
	return $cache[ $filename ];
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
$fail   = 0;
$report = array();

foreach ( $q->posts as $post ) {
	$file = vb_match_image( $post->post_title );
	if ( ! $file ) {
		++$skip;
		$report[] = "SKIP | {$post->ID} | {$post->post_title}";
		continue;
	}

	$path  = trailingslashit( $assets_dir ) . $file;
	$att   = vb_ensure_attachment( $path, $file );
	if ( ! $att ) {
		++$fail;
		$report[] = "FAIL | {$post->ID} | {$post->post_title} | {$file}";
		continue;
	}

	set_post_thumbnail( $post->ID, $att );
	++$ok;
	$report[] = "OK   | {$post->ID} | {$post->post_title} | {$file} | #{$att}";
}

echo "\n---- RESUMO ----\n";
echo "Vinculados: {$ok}\n";
echo "Ignorados:  {$skip}\n";
echo "Falhas:     {$fail}\n\n";
echo implode( "\n", $report ) . "\n";
