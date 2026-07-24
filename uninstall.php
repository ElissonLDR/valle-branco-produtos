<?php
/**
 * Desinstalação — remove opções do plugin.
 * Não apaga produtos (CPT) nem mídia.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'vb_prod_settings' );
delete_option( 'vb_prod_version' );
