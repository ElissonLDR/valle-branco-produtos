<?php
/**
 * Converte `_vb_nutricao` do formato plano para várias tabelas.
 *
 * Uso: php bin/migrar-tabelas-multi.php
 *
 * @package ValleBrancoProdutos
 */

require 'C:/xampp/htdocs/valle-branco/wp-load.php';

$q = new WP_Query(
	array(
		'post_type'      => 'vb_produto',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);

$ok   = 0;
$skip = 0;

foreach ( $q->posts as $post ) {
	$raw = get_post_meta( $post->ID, '_vb_nutricao', true );
	if ( ! is_array( $raw ) ) {
		++$skip;
		echo "SKIP #{$post->ID} (vazio)\n";
		continue;
	}

	// Já no formato novo.
	if ( ! empty( $raw['tabelas'] ) && is_array( $raw['tabelas'] ) ) {
		$clean = VB_Prod_Meta::sanitize_nutricao( $raw );
		update_post_meta( $post->ID, '_vb_nutricao', $clean );
		++$ok;
		echo "OK   #{$post->ID} | {$post->post_title} | tabelas=" . count( $clean['tabelas'] ) . " (já multi)\n";
		continue;
	}

	$clean = VB_Prod_Meta::sanitize_nutricao( $raw );
	update_post_meta( $post->ID, '_vb_nutricao', $clean );
	++$ok;
	echo 'OK   #' . $post->ID . ' | ' . $post->post_title . ' | tabelas=' . count( $clean['tabelas'] ) . "\n";
}

echo "---\nAtualizados: {$ok}\nIgnorados: {$skip}\n";
