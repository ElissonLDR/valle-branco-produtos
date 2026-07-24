<?php
/**
 * CPT e taxonomias do catálogo.
 *
 * Reutiliza o CPT `vb_produto` (mesmo do Onde Encontrar / SAP)
 * com supports e rewrite adequados à vitrine.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_CPT
 */
class VB_Prod_CPT {

	const POST_TYPE     = 'vb_produto';
	const TAX_CATEGORIA = 'vb_categoria_produto';
	const TAX_MARCA     = 'vb_marca';

	/**
	 * Registra CPT e taxonomias.
	 */
	public static function register() {
		self::register_produto();
		self::register_taxonomias();
	}

	/**
	 * CPT Produto (catálogo público).
	 */
	private static function register_produto() {
		$labels = array(
			'name'                  => __( 'Produtos', 'valle-branco-produtos' ),
			'singular_name'         => __( 'Produto', 'valle-branco-produtos' ),
			'menu_name'             => __( 'Produtos', 'valle-branco-produtos' ),
			'name_admin_bar'        => __( 'Produto', 'valle-branco-produtos' ),
			'add_new'               => __( 'Adicionar novo', 'valle-branco-produtos' ),
			'add_new_item'          => __( 'Adicionar produto', 'valle-branco-produtos' ),
			'edit_item'             => __( 'Editar produto', 'valle-branco-produtos' ),
			'new_item'              => __( 'Novo produto', 'valle-branco-produtos' ),
			'view_item'             => __( 'Ver produto', 'valle-branco-produtos' ),
			'view_items'            => __( 'Ver produtos', 'valle-branco-produtos' ),
			'search_items'          => __( 'Buscar produtos', 'valle-branco-produtos' ),
			'not_found'             => __( 'Nenhum produto encontrado', 'valle-branco-produtos' ),
			'not_found_in_trash'    => __( 'Nenhum produto na lixeira', 'valle-branco-produtos' ),
			'all_items'             => __( 'Todos os produtos', 'valle-branco-produtos' ),
			'archives'              => __( 'Arquivo de produtos', 'valle-branco-produtos' ),
			'featured_image'        => __( 'Imagem de capa', 'valle-branco-produtos' ),
			'set_featured_image'    => __( 'Definir imagem de capa', 'valle-branco-produtos' ),
			'remove_featured_image' => __( 'Remover imagem de capa', 'valle-branco-produtos' ),
			'use_featured_image'    => __( 'Usar como imagem de capa', 'valle-branco-produtos' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => $labels,
				'description'         => __( 'Catálogo de produtos Valle Branco', 'valle-branco-produtos' ),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_rest'        => true,
				'rest_base'           => 'vb-produtos',
				'menu_position'       => 25,
				'menu_icon'           => 'dashicons-products',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'has_archive'         => false,
				'exclude_from_search' => false,
				'can_export'          => true,
				'delete_with_user'    => false,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'page-attributes' ),
				'rewrite'             => array(
					'slug'       => 'produto',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Categoria e marca.
	 */
	private static function register_taxonomias() {
		register_taxonomy(
			self::TAX_CATEGORIA,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Categorias', 'valle-branco-produtos' ),
					'singular_name' => __( 'Categoria', 'valle-branco-produtos' ),
					'search_items'  => __( 'Buscar categorias', 'valle-branco-produtos' ),
					'all_items'     => __( 'Todas as categorias', 'valle-branco-produtos' ),
					'edit_item'     => __( 'Editar categoria', 'valle-branco-produtos' ),
					'update_item'   => __( 'Atualizar categoria', 'valle-branco-produtos' ),
					'add_new_item'  => __( 'Adicionar categoria', 'valle-branco-produtos' ),
					'new_item_name' => __( 'Nova categoria', 'valle-branco-produtos' ),
					'menu_name'     => __( 'Categorias', 'valle-branco-produtos' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'categoria-produto' ),
			)
		);

		register_taxonomy(
			self::TAX_MARCA,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Marcas', 'valle-branco-produtos' ),
					'singular_name' => __( 'Marca', 'valle-branco-produtos' ),
					'search_items'  => __( 'Buscar marcas', 'valle-branco-produtos' ),
					'all_items'     => __( 'Todas as marcas', 'valle-branco-produtos' ),
					'edit_item'     => __( 'Editar marca', 'valle-branco-produtos' ),
					'update_item'   => __( 'Atualizar marca', 'valle-branco-produtos' ),
					'add_new_item'  => __( 'Adicionar marca', 'valle-branco-produtos' ),
					'new_item_name' => __( 'Nova marca', 'valle-branco-produtos' ),
					'menu_name'     => __( 'Marcas', 'valle-branco-produtos' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'marca' ),
			)
		);
	}
}
