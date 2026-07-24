<?php
/**
 * Desativação.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Deactivator
 */
class VB_Prod_Deactivator {

	/**
	 * Limpeza leve (não apaga produtos).
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
