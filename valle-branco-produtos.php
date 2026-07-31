<?php
/**
 * Plugin Name:       Valle Branco — Produtos
 * Plugin URI:        https://vallebranco.com.br
 * Description:       Catálogo de produtos leve (sem WooCommerce): painel, galeria, tabela nutricional e widgets Elementor / Theme Builder.
 * Version:           1.0.5
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Valle Branco
 * Author URI:        https://vallebranco.com.br
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       valle-branco-produtos
 * Domain Path:       /languages
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VB_PROD_VERSION', '1.4.8' );
define( 'VB_PROD_FILE', __FILE__ );
define( 'VB_PROD_PATH', plugin_dir_path( __FILE__ ) );
define( 'VB_PROD_URL', plugin_dir_url( __FILE__ ) );
define( 'VB_PROD_BASENAME', plugin_basename( __FILE__ ) );

require_once VB_PROD_PATH . 'includes/class-vb-prod-activator.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-deactivator.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-cpt.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-product.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-meta.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-settings.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-admin.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-frontend.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-cf7.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-elementor.php';
require_once VB_PROD_PATH . 'includes/class-vb-prod-plugin.php';

/**
 * Ativação.
 */
function vb_prod_activate() {
	VB_Prod_Activator::activate();
}
register_activation_hook( __FILE__, 'vb_prod_activate' );

/**
 * Desativação.
 */
function vb_prod_deactivate() {
	VB_Prod_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'vb_prod_deactivate' );

/**
 * Boot.
 */
function vb_prod_run() {
	$plugin = new VB_Prod_Plugin();
	$plugin->run();
}
add_action( 'plugins_loaded', 'vb_prod_run' );
