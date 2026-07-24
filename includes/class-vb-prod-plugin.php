<?php
/**
 * Orquestra o plugin.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Plugin
 */
class VB_Prod_Plugin {

	/**
	 * Liga módulos.
	 */
	public function run() {
		load_plugin_textdomain( 'valle-branco-produtos', false, dirname( VB_PROD_BASENAME ) . '/languages' );

		// CPT + taxonomias cedo (prioridade 5) para o OE detectar o CPT.
		add_action( 'init', array( 'VB_Prod_CPT', 'register' ), 5 );
		add_action( 'init', array( $this, 'maybe_flush_rewrites' ), 99 );

		$meta = new VB_Prod_Meta();
		$meta->hooks();

		$settings = new VB_Prod_Settings();
		$settings->hooks();

		if ( is_admin() ) {
			$admin = new VB_Prod_Admin();
			$admin->hooks();
		}

		$front = new VB_Prod_Frontend();
		$front->hooks();

		$cf7 = new VB_Prod_CF7();
		$cf7->hooks();

		$elementor = new VB_Prod_Elementor();
		$elementor->hooks();
	}

	/**
	 * Atualiza permalinks quando a versão do plugin muda (ex.: slug do CPT).
	 */
	public function maybe_flush_rewrites() {
		$stored = get_option( 'vb_prod_version', '' );
		if ( VB_PROD_VERSION === $stored ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'vb_prod_version', VB_PROD_VERSION );
	}
}
