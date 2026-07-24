<?php
/**
 * Ativação.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Activator
 */
class VB_Prod_Activator {

	/**
	 * Roda na ativação.
	 */
	public static function activate() {
		VB_Prod_CPT::register();
		VB_Prod_Settings::ensure_defaults();

		update_option( 'vb_prod_version', VB_PROD_VERSION );
		flush_rewrite_rules();
	}
}
