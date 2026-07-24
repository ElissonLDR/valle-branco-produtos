<?php
/**
 * Assets e colunas do painel.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Admin
 */
class VB_Prod_Admin {

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_filter( 'manage_' . VB_Prod_CPT::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . VB_Prod_CPT::POST_TYPE . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
	}

	/**
	 * CSS/JS só na tela do produto.
	 *
	 * @param string $hook Hook.
	 */
	public function assets( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || VB_Prod_CPT::POST_TYPE !== $screen->post_type ) {
			return;
		}
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style(
			'vb-prod-admin',
			VB_PROD_URL . 'admin/css/admin.css',
			array(),
			VB_PROD_VERSION
		);
		wp_enqueue_script(
			'vb-prod-admin',
			VB_PROD_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			VB_PROD_VERSION,
			true
		);
	}

	/**
	 * Colunas extras.
	 *
	 * @param array $cols Colunas.
	 * @return array
	 */
	public function columns( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['vb_sku']   = __( 'SKU', 'valle-branco-produtos' );
				$new['vb_thumb'] = __( 'Capa', 'valle-branco-produtos' );
			}
		}
		return $new;
	}

	/**
	 * Conteúdo das colunas.
	 *
	 * @param string $col     Coluna.
	 * @param int    $post_id ID.
	 */
	public function column_content( $col, $post_id ) {
		if ( 'vb_sku' === $col ) {
			echo esc_html( (string) get_post_meta( $post_id, '_vb_sku', true ) );
			return;
		}
		if ( 'vb_thumb' === $col ) {
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 48, 48 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '—';
			}
		}
	}
}
