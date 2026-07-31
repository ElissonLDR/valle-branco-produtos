<?php
/**
 * Integração Elementor + Theme Builder.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Elementor
 */
class VB_Prod_Elementor {

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'categoria' ) );
		add_action( 'elementor/widgets/register', array( $this, 'registrar_widgets' ) );

		// Theme Builder: CPT elegível para Single / Archive.
		add_action( 'elementor/theme/register_locations', array( $this, 'register_locations' ) );
		add_filter( 'elementor_pro/utils/get_public_post_types', array( $this, 'public_post_types' ) );
		add_filter( 'elementor_pro/theme_builder/get_location_taxonomies', array( $this, 'location_taxonomies' ) );
	}

	/**
	 * Categoria no painel Elementor.
	 *
	 * @param \Elementor\Elements_Manager $manager Manager.
	 */
	public function categoria( $manager ) {
		$manager->add_category(
			'vb-produtos',
			array(
				'title' => __( 'VB — Produtos', 'valle-branco-produtos' ),
				'icon'  => 'fa fa-shopping-bag',
			)
		);
	}

	/**
	 * Locations Theme Builder (noop seguro — Elementor Pro já tem single/archive).
	 *
	 * @param mixed $manager Manager.
	 */
	public function register_locations( $manager ) {
		unset( $manager );
	}

	/**
	 * Garante CPT no Theme Builder.
	 *
	 * @param array $types Types.
	 * @return array
	 */
	public function public_post_types( $types ) {
		$types[ VB_Prod_CPT::POST_TYPE ] = __( 'Produtos', 'valle-branco-produtos' );
		return $types;
	}

	/**
	 * Taxonomias nas condições do Theme Builder.
	 *
	 * @param array $taxes Taxes.
	 * @return array
	 */
	public function location_taxonomies( $taxes ) {
		$taxes[ VB_Prod_CPT::TAX_CATEGORIA ] = get_taxonomy( VB_Prod_CPT::TAX_CATEGORIA );
		$taxes[ VB_Prod_CPT::TAX_MARCA ]     = get_taxonomy( VB_Prod_CPT::TAX_MARCA );
		return array_filter( $taxes );
	}

	/**
	 * Registra widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Manager.
	 */
	public function registrar_widgets( $widgets_manager ) {
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-base.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-imagem.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-titulo.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-descricao.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-botao.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-carrossel.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-relacionados.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-lista.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-categorias.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-busca.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-nutricao.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-meta.php';
		require_once VB_PROD_PATH . 'includes/elementor/class-widget-tags.php';

		$widgets_manager->register( new VB_Prod_Widget_Imagem() );
		$widgets_manager->register( new VB_Prod_Widget_Titulo() );
		$widgets_manager->register( new VB_Prod_Widget_Descricao() );
		$widgets_manager->register( new VB_Prod_Widget_Botao() );
		$widgets_manager->register( new VB_Prod_Widget_Carrossel() );
		$widgets_manager->register( new VB_Prod_Widget_Relacionados() );
		$widgets_manager->register( new VB_Prod_Widget_Lista() );
		$widgets_manager->register( new VB_Prod_Widget_Categorias() );
		$widgets_manager->register( new VB_Prod_Widget_Busca() );
		$widgets_manager->register( new VB_Prod_Widget_Nutricao() );
		$widgets_manager->register( new VB_Prod_Widget_Meta() );
		$widgets_manager->register( new VB_Prod_Widget_Tags() );
	}
}
