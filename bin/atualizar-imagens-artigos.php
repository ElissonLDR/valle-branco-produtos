<?php
/**
 * Atualiza as imagens destacadas dos artigos/receitas.
 *
 * Lê webp de bin/artigos-imagens/{slug}.webp e associa ao post pelo slug.
 * Uso local:  C:\xampp\php\php.exe bin/atualizar-imagens-artigos.php
 * Uso remoto: wp eval-file ... ou php com wp-load do servidor.
 *
 * @package ValleBrancoProdutos
 */

$wp_load_candidates = array(
	'C:/xampp/htdocs/valle-branco/wp-load.php',
	dirname( __DIR__, 4 ) . '/wp-load.php',
	dirname( __DIR__, 3 ) . '/wp-load.php',
);

$loaded = false;
foreach ( $wp_load_candidates as $candidate ) {
	if ( file_exists( $candidate ) ) {
		require $candidate;
		$loaded = true;
		break;
	}
}

if ( ! $loaded ) {
	fwrite( STDERR, "wp-load.php não encontrado.\n" );
	exit( 1 );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$dir = __DIR__ . '/artigos-imagens';
if ( ! is_dir( $dir ) ) {
	fwrite( STDERR, "Pasta ausente: {$dir}\n" );
	exit( 1 );
}

$files = glob( $dir . '/*.webp' );
if ( ! $files ) {
	fwrite( STDERR, "Nenhuma imagem .webp em {$dir}\n" );
	exit( 1 );
}

/**
 * Encontra post importado pelo slug.
 *
 * @param string $slug Slug.
 * @return int
 */
function vb_find_artigo_post( $slug ) {
	$by_meta = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'meta_key'       => '_vb_lovable_slug',
			'meta_value'     => $slug,
			'fields'         => 'ids',
		)
	);
	if ( $by_meta ) {
		return (int) $by_meta[0];
	}

	$by_name = get_page_by_path( $slug, OBJECT, 'post' );
	return $by_name ? (int) $by_name->ID : 0;
}

/**
 * Faz upload (ou reutiliza) a imagem e devolve attachment ID.
 *
 * @param string $path Caminho absoluto.
 * @param string $filename Nome do arquivo.
 * @return int|\WP_Error
 */
function vb_sideload_artigo_image( $path, $filename ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
			'meta_key'       => '_vb_lovable_asset',
			'meta_value'     => $filename,
			'fields'         => 'ids',
		)
	);

	if ( $existing ) {
		$att_id   = (int) $existing[0];
		$dest     = get_attached_file( $att_id );
		$uploads  = wp_upload_dir();
		$basedir  = trailingslashit( $uploads['basedir'] ) . gmdate( 'Y/m' );
		wp_mkdir_p( $basedir );
		$new_path = $basedir . '/' . $filename;

		if ( ! copy( $path, $new_path ) ) {
			return new WP_Error( 'copy_failed', "Falha ao copiar {$filename}" );
		}

		update_attached_file( $att_id, $new_path );
		$meta = wp_generate_attachment_metadata( $att_id, $new_path );
		if ( $meta ) {
			wp_update_attachment_metadata( $att_id, $meta );
		}

		return $att_id;
	}

	$tmp = wp_tempnam( $filename );
	copy( $path, $tmp );

	$att_id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		),
		0,
		pathinfo( $filename, PATHINFO_FILENAME )
	);

	if ( is_wp_error( $att_id ) ) {
		return $att_id;
	}

	update_post_meta( $att_id, '_vb_lovable_asset', $filename );

	return (int) $att_id;
}

$ok    = 0;
$fail  = 0;

foreach ( $files as $path ) {
	$filename = basename( $path );
	$slug     = pathinfo( $filename, PATHINFO_FILENAME );
	$post_id  = vb_find_artigo_post( $slug );

	if ( ! $post_id ) {
		fwrite( STDERR, "Post não encontrado: {$slug}\n" );
		$fail++;
		continue;
	}

	$att_id = vb_sideload_artigo_image( $path, $filename );
	if ( is_wp_error( $att_id ) ) {
		fwrite( STDERR, "Erro {$slug}: " . $att_id->get_error_message() . "\n" );
		$fail++;
		continue;
	}

	set_post_thumbnail( $post_id, $att_id );
	echo "#{$post_id} | {$slug} → attachment #{$att_id} | {$filename}\n";
	$ok++;
}

echo "\nConcluído: {$ok} atualizados, {$fail} falhas.\n";
