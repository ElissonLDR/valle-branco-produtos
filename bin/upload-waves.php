<?php
/**
 * Sobe ondas do Lovable para a mídia (SVG liberado só neste script).
 */
require 'C:/xampp/htdocs/valle-branco/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

add_filter(
	'upload_mimes',
	static function ( $mimes ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		return $mimes;
	}
);

// WP 4.7.1+ checagem extra.
add_filter(
	'wp_check_filetype_and_ext',
	static function ( $data, $file, $filename, $mimes ) {
		$ext = pathinfo( $filename, PATHINFO_EXTENSION );
		if ( 'svg' === strtolower( $ext ) ) {
			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		}
		return $data;
	},
	10,
	4
);

$dir   = 'C:/xampp/htdocs/valle-branco/wp-content/plugins/valle-branco-produtos/public/images/';
$files = array(
	'card-wave-divider.svg' => 'Onda card produtos (Lovable)',
	'hero-wave-divider.svg' => 'Onda hero (Lovable)',
	'card-wave-divider.png' => 'Onda card produtos PNG (Lovable)',
);

foreach ( $files as $file => $title ) {
	$path = $dir . $file;
	if ( ! file_exists( $path ) ) {
		echo "AUSENTE: {$file}\n";
		continue;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
			'meta_key'       => '_vb_prod_asset',
			'meta_value'     => $file,
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		echo "JA EXISTE: {$file} → #{$existing[0]}\n";
		echo 'URL: ' . wp_get_attachment_url( $existing[0] ) . "\n";
		continue;
	}

	$tmp = wp_tempnam( $file );
	copy( $path, $tmp );
	$att = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0,
		$title
	);
	if ( is_wp_error( $att ) ) {
		echo 'ERRO ' . $file . ': ' . $att->get_error_message() . "\n";
		continue;
	}
	update_post_meta( $att, '_vb_prod_asset', $file );
	wp_update_post(
		array(
			'ID'         => $att,
			'post_title' => $title,
		)
	);
	echo "OK: {$file} → #{$att}\n";
	echo 'URL: ' . wp_get_attachment_url( $att ) . "\n";
}
