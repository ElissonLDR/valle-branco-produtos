<?php
/**
 * Widget: Lista / grade de produtos.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Lista
 */
class VB_Prod_Widget_Lista extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_lista';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Grade de produtos', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-products';
	}

	/**
	 * Opções de produtos para SELECT2 / repeater.
	 *
	 * @return array<int,string>
	 */
	protected function get_product_choices() {
		$posts = get_posts(
			array(
				'post_type'              => VB_Prod_CPT::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => 200,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		$opts = array();
		foreach ( $posts as $p ) {
			$opts[ (string) $p->ID ] = $p->post_title;
		}
		return $opts;
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec_query',
			array(
				'label' => __( 'Consulta', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'query_mode',
			array(
				'label'   => __( 'Fonte dos produtos', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'   => __( 'Consulta automática', 'valle-branco-produtos' ),
					'manual' => __( 'Seleção manual (ordem personalizada)', 'valle-branco-produtos' ),
				),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'product_id',
			array(
				'label'       => __( 'Produto', 'valle-branco-produtos' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_product_choices(),
				'label_block' => true,
			)
		);

		$this->add_control(
			'manual_products',
			array(
				'label'         => __( 'Produtos e ordem', 'valle-branco-produtos' ),
				'type'          => \Elementor\Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'default'       => array(),
				'title_field'   => __( 'Produto', 'valle-branco-produtos' ) . ' #{{{ product_id }}}',
				'prevent_empty' => false,
				'condition'     => array( 'query_mode' => 'manual' ),
				'description'   => __( 'Arraste os itens para definir a ordem no carrossel / grade.', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'manual_append_random',
			array(
				'label'        => __( 'Depois, adicionar produtos aleatórios', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array( 'query_mode' => 'manual' ),
				'description'  => __( 'Inclui produtos aleatórios (exceto os já selecionados) depois da lista manual.', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'manual_random_count',
			array(
				'label'     => __( 'Quantidade aleatória', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 4,
				'min'       => 1,
				'max'       => 48,
				'condition' => array(
					'query_mode'           => 'manual',
					'manual_append_random' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'per_page',
			array(
				'label'              => __( 'Quantidade (itens por página)', 'valle-branco-produtos' ),
				'type'               => \Elementor\Controls_Manager::NUMBER,
				'default'            => 12,
				'tablet_default'     => 8,
				'mobile_default'     => 4,
				'min'                => 1,
				'max'                => 48,
				'condition'          => array( 'query_mode' => 'auto' ),
				'frontend_available' => true,
				'description'        => __( 'Define quantos cards por página em cada dispositivo (ícones Desktop / Tablet / Mobile).', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'categoria',
			array(
				'label'       => __( 'Categoria (slug)', 'valle-branco-produtos' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => __( 'Vazio = todas.', 'valle-branco-produtos' ),
				'condition'   => array( 'query_mode' => 'auto' ),
			)
		);
		$this->add_control(
			'marca',
			array(
				'label'     => __( 'Marca (slug)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => array( 'query_mode' => 'auto' ),
			)
		);
		$this->add_control(
			'orderby',
			array(
				'label'     => __( 'Ordenar por', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'title',
				'condition' => array( 'query_mode' => 'auto' ),
				'options'   => array(
					'title'      => __( 'Título', 'valle-branco-produtos' ),
					'date'       => __( 'Data', 'valle-branco-produtos' ),
					'menu_order' => __( 'Ordem manual', 'valle-branco-produtos' ),
					'rand'       => __( 'Aleatório', 'valle-branco-produtos' ),
				),
			)
		);
		$this->add_control(
			'order',
			array(
				'label'     => __( 'Direção', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'ASC',
				'condition' => array( 'query_mode' => 'auto' ),
				'options'   => array(
					'ASC'  => 'ASC',
					'DESC' => 'DESC',
				),
			)
		);
		$this->add_control(
			'enable_filter',
			array(
				'label'        => __( 'Aceitar filtro de categorias (JS)', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'query_mode' => 'auto' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'sec_layout',
			array(
				'label' => __( 'Layout e navegação', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_responsive_control(
			'layout',
			array(
				'label'              => __( 'Layout', 'valle-branco-produtos' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'default'            => 'grid',
				'tablet_default'     => 'grid',
				'mobile_default'     => 'grid',
				'options'            => array(
					'grid'      => __( 'Grade', 'valle-branco-produtos' ),
					'carrossel' => __( 'Carrossel', 'valle-branco-produtos' ),
				),
				'frontend_available' => true,
				'description'        => __( 'Escolha Grade ou Carrossel em cada dispositivo (ícones Desktop / Tablet / Mobile).', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'paginacao',
			array(
				'label'        => __( 'Paginação no desktop (números)', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'layout'     => 'grid',
					'query_mode' => 'auto',
				),
				'description'  => __( 'Quando o layout do desktop for Grade: botões ‹ 1 2 3 ›. Usa a Quantidade como itens por página.', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'show_nav',
			array(
				'label'        => __( 'Ativar setas do carrossel', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Aparecem nos dispositivos em que o Layout for Carrossel.', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'nav_scroll',
			array(
				'label'     => __( 'Deslocamento das setas (px)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 300,
				'min'       => 100,
				'max'       => 800,
				'condition' => array( 'show_nav' => 'yes' ),
			)
		);
		$this->add_control(
			'heading_grade_resp',
			array(
				'label'     => __( 'Grade responsiva', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'layout' => 'grid' ),
			)
		);
		$this->add_responsive_control(
			'columns',
			array(
				'label'              => __( 'Colunas', 'valle-branco-produtos' ),
				'type'               => \Elementor\Controls_Manager::SELECT,
				'options'            => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'default'            => '4',
				'tablet_default'     => '2',
				'mobile_default'     => '1',
				'condition'          => array( 'layout' => 'grid' ),
				'frontend_available' => true,
				'description'        => __( 'Aplicado quando o Layout for Grade neste dispositivo.', 'valle-branco-produtos' ),
				'selectors'          => array(
					'{{WRAPPER}} .vb-prod-lista--grid' => '--vb-cols: {{VALUE}} !important;',
				),
			)
		);
		$this->add_responsive_control(
			'products_align',
			array(
				'label'                => __( 'Alinhamento dos produtos', 'valle-branco-produtos' ),
				'type'                 => \Elementor\Controls_Manager::CHOOSE,
				'options'              => array(
					'left'    => array(
						'title' => __( 'Esquerda', 'valle-branco-produtos' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => __( 'Centro', 'valle-branco-produtos' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => __( 'Direita', 'valle-branco-produtos' ),
						'icon'  => 'eicon-text-align-right',
					),
					'justify' => array(
						'title' => __( 'Justificado', 'valle-branco-produtos' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'default'              => 'left',
				'toggle'               => false,
				'condition'            => array( 'layout' => 'grid' ),
				'frontend_available'   => true,
				'selectors_dictionary' => array(
					'left'    => 'flex-start',
					'center'  => 'center',
					'right'   => 'flex-end',
					'justify' => 'space-between',
				),
				'selectors'            => array(
					'{{WRAPPER}} .vb-prod-lista--grid' => '--vb-align: {{VALUE}}; justify-content: {{VALUE}};',
				),
				'prefix_class'         => 'vb-prod-align-',
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'              => __( 'Espaçamento entre cards', 'valle-branco-produtos' ),
				'type'               => \Elementor\Controls_Manager::SLIDER,
				'size_units'         => array( 'px', 'rem' ),
				'range'              => array(
					'px'  => array( 'min' => 0, 'max' => 64 ),
					'rem' => array( 'min' => 0, 'max' => 4, 'step' => 0.1 ),
				),
				'default'            => array( 'size' => 20, 'unit' => 'px' ),
				'tablet_default'     => array( 'size' => 16, 'unit' => 'px' ),
				'mobile_default'     => array( 'size' => 12, 'unit' => 'px' ),
				'condition'          => array(
					'layout'          => 'grid',
					'products_align!' => 'justify',
				),
				'frontend_available' => true,
				'description'        => __( 'No alinhamento justificado o espaçamento horizontal é automático.', 'valle-branco-produtos' ),
				'selectors'          => array(
					'{{WRAPPER}} .vb-prod-lista--grid' => '--vb-gap: {{SIZE}}{{UNIT}} !important; gap: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);
		$this->end_controls_section();

		$this->controles_card_conteudo();
		$this->controles_card_estilo();
		$this->controles_nav_estilo();
	}

	/**
	 * Estilos das setas de navegação.
	 */
	protected function controles_nav_estilo() {
		$this->start_controls_section(
			'estilo_paginacao',
			array(
				'label'     => __( 'Paginação', 'valle-branco-produtos' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout'    => 'grid',
					'paginacao' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'pag_align',
			array(
				'label'     => __( 'Alinhamento', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Esquerda', 'valle-branco-produtos' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Centro', 'valle-branco-produtos' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Direita', 'valle-branco-produtos' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-paginacao' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'pag_typo',
				'selector' => '{{WRAPPER}} .vb-prod-paginacao__btn',
			)
		);

		$this->add_responsive_control(
			'pag_min_size',
			array(
				'label'      => __( 'Tamanho mínimo do botão', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 72 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-paginacao__btn' => 'min-width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'pag_padding',
			array(
				'label'      => __( 'Padding', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-paginacao__btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'pag_gap',
			array(
				'label'      => __( 'Espaço entre botões', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-paginacao' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'pag_radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-paginacao__btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'pag_borda',
				'selector' => '{{WRAPPER}} .vb-prod-paginacao__btn',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'pag_sombra',
				'selector' => '{{WRAPPER}} .vb-prod-paginacao__btn',
			)
		);

		$this->start_controls_tabs( 'tabs_pag' );

		$this->start_controls_tab( 'tab_pag_n', array( 'label' => __( 'Normal', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'pag_cor',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-paginacao__btn' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pag_bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-paginacao__btn' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_pag_h', array( 'label' => __( 'Hover', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'pag_cor_h',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-paginacao__btn:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pag_bg_h',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-paginacao__btn:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pag_borda_cor_h',
			array(
				'label'     => __( 'Cor da borda', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-paginacao__btn:hover' => 'border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_pag_a', array( 'label' => __( 'Ativo', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'pag_ativo_cor',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-paginacao__btn.is-active' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pag_ativo_bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-paginacao__btn.is-active' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pag_ativo_borda',
			array(
				'label'     => __( 'Cor da borda', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-paginacao__btn.is-active' => 'border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'pag_margin',
			array(
				'label'      => __( 'Margem externa', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-paginacao' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_nav',
			array(
				'label'     => __( 'Setas do carrossel', 'valle-branco-produtos' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_nav' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'nav_size',
			array(
				'label'      => __( 'Tamanho', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 28, 'max' => 72 ) ),
				'default'    => array( 'size' => 40, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-nav' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important; min-width: {{SIZE}}{{UNIT}} !important; min-height: {{SIZE}}{{UNIT}} !important; max-width: {{SIZE}}{{UNIT}} !important; max-height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'nav_offset_y',
			array(
				'label'      => __( 'Posição vertical', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array( '%' => array( 'min' => 10, 'max' => 90 ) ),
				'default'    => array( 'size' => 40, 'unit' => '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-nav' => 'top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'nav_offset_x',
			array(
				'label'      => __( 'Afastamento das bordas', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => -48, 'max' => 80 ) ),
				'default'    => array( 'size' => 12, 'unit' => 'px' ),
				'description'=> __( 'Setas em posição absoluta (não ocupam espaço). Valor positivo = para dentro do carrossel.', 'valle-branco-produtos' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-nav--prev' => 'left: {{SIZE}}{{UNIT}} !important;',
					'{{WRAPPER}} .vb-prod-nav--next' => 'right: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->start_controls_tabs( 'tabs_nav' );
		$this->start_controls_tab( 'tab_nav_n', array( 'label' => __( 'Normal', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'nav_bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-nav' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'nav_cor',
			array(
				'label'     => __( 'Ícone / texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-nav' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_nav_h', array( 'label' => __( 'Hover', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'nav_bg_h',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-nav:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'nav_cor_h',
			array(
				'label'     => __( 'Ícone / texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-nav:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'nav_borda',
				'selector' => '{{WRAPPER}} .vb-prod-nav',
			)
		);
		$this->add_responsive_control(
			'nav_radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-nav' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'nav_sombra',
				'selector' => '{{WRAPPER}} .vb-prod-nav',
			)
		);
		$this->add_control(
			'nav_opacity',
			array(
				'label'     => __( 'Opacidade', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0.2, 'max' => 1, 'step' => 0.05 ) ),
				'selectors' => array( '{{WRAPPER}} .vb-prod-nav' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_dots',
			array(
				'label' => __( 'Pontos do carrossel', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'dot_cor',
			array(
				'label'     => __( 'Cor (inativo)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(10, 60, 107, 0.28)',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-carousel-dots__dot' => 'background-color: {{VALUE}} !important;',
				),
			)
		);
		$this->add_control(
			'dot_cor_ativo',
			array(
				'label'     => __( 'Cor (ativo)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0a3c6b',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-carousel-dots__dot.is-active' => 'background-color: {{VALUE}} !important;',
				),
			)
		);
		$this->add_responsive_control(
			'dot_size',
			array(
				'label'      => __( 'Tamanho', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 4, 'max' => 14 ) ),
				'default'    => array( 'size' => 6, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-carousel-dots__dot' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important; min-width: {{SIZE}}{{UNIT}} !important; min-height: {{SIZE}}{{UNIT}} !important; max-width: {{SIZE}}{{UNIT}} !important; max-height: {{SIZE}}{{UNIT}} !important;',
					'{{WRAPPER}} .vb-prod-carousel-dots__dot.is-active' => 'width: calc({{SIZE}}{{UNIT}} * 2.5) !important; min-width: calc({{SIZE}}{{UNIT}} * 2.5) !important; max-width: calc({{SIZE}}{{UNIT}} * 2.5) !important; height: {{SIZE}}{{UNIT}} !important; min-height: {{SIZE}}{{UNIT}} !important; max-height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);
		$this->add_responsive_control(
			'dot_gap',
			array(
				'label'      => __( 'Espaço entre pontos', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 2, 'max' => 16 ) ),
				'default'    => array( 'size' => 6, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-carousel-dots' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Resolve layout por breakpoint (+ migração do antigo mobile_carrossel).
	 *
	 * @param array $s Settings.
	 * @return array{desktop:string,tablet:string,mobile:string}
	 */
	protected function resolve_layouts( $s ) {
		$desktop = ( isset( $s['layout'] ) && 'carrossel' === $s['layout'] ) ? 'carrossel' : 'grid';
		$tablet  = ( isset( $s['layout_tablet'] ) && '' !== $s['layout_tablet'] ) ? $s['layout_tablet'] : $desktop;
		$mobile  = ( isset( $s['layout_mobile'] ) && '' !== $s['layout_mobile'] ) ? $s['layout_mobile'] : $desktop;
		$tablet  = ( 'carrossel' === $tablet ) ? 'carrossel' : 'grid';
		$mobile  = ( 'carrossel' === $mobile ) ? 'carrossel' : 'grid';

		// Widgets antigos: só migra se layout_mobile ainda não foi salvo no Elementor.
		$raw = $this->get_settings();
		if (
			is_array( $raw )
			&& ! array_key_exists( 'layout_mobile', $raw )
			&& isset( $raw['mobile_carrossel'] )
			&& 'yes' === $raw['mobile_carrossel']
			&& 'grid' === $desktop
		) {
			$mobile = 'carrossel';
		}

		return array(
			'desktop' => $desktop,
			'tablet'  => $tablet,
			'mobile'  => $mobile,
		);
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s               = $this->get_settings_for_display();
		$layouts         = $this->resolve_layouts( $s );
		$layout          = $layouts['desktop'];
		$layout_tablet   = $layouts['tablet'];
		$layout_mobile   = $layouts['mobile'];
		$per_page        = isset( $s['per_page'] ) ? absint( $s['per_page'] ) : 12;
		$per_page_tablet = isset( $s['per_page_tablet'] ) && '' !== $s['per_page_tablet'] ? absint( $s['per_page_tablet'] ) : $per_page;
		$per_page_mobile = isset( $s['per_page_mobile'] ) && '' !== $s['per_page_mobile'] ? absint( $s['per_page_mobile'] ) : $per_page;
		$per_page        = max( 1, $per_page );
		$per_page_tablet = max( 1, $per_page_tablet );
		$per_page_mobile = max( 1, $per_page_mobile );
		$query_mode      = isset( $s['query_mode'] ) ? $s['query_mode'] : 'auto';
		$is_manual       = ( 'manual' === $query_mode );
		$paginacao       = ( ! $is_manual && 'grid' === $layout && isset( $s['paginacao'] ) && 'yes' === $s['paginacao'] );
		$any_carousel    = in_array( 'carrossel', array( $layout, $layout_tablet, $layout_mobile ), true );
		$desktop_carr    = ( 'carrossel' === $layout );
		$tablet_carr     = ( ! $desktop_carr && 'carrossel' === $layout_tablet );
		$mobile_carr     = ( ! $desktop_carr && 'carrossel' === $layout_mobile );
		// Com paginação, carrega o catálogo e pagina no front (compatível com filtros JS).
		$posts_limit = $paginacao ? 100 : max( $per_page, $per_page_tablet, $per_page_mobile );

		$args = array(
			'post_type'              => VB_Prod_CPT::POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => $posts_limit,
			'orderby'                => isset( $s['orderby'] ) ? sanitize_key( $s['orderby'] ) : 'title',
			'order'                  => ( isset( $s['order'] ) && 'DESC' === $s['order'] ) ? 'DESC' : 'ASC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		);

		if ( $is_manual ) {
			$ids = array();
			if ( ! empty( $s['manual_products'] ) && is_array( $s['manual_products'] ) ) {
				foreach ( $s['manual_products'] as $row ) {
					$id = isset( $row['product_id'] ) ? absint( $row['product_id'] ) : 0;
					if ( $id > 0 ) {
						$ids[] = $id;
					}
				}
			}
			$ids = array_values( array_unique( $ids ) );

			$append_random = ( isset( $s['manual_append_random'] ) && 'yes' === $s['manual_append_random'] );
			$random_count  = isset( $s['manual_random_count'] ) ? absint( $s['manual_random_count'] ) : 0;
			if ( $append_random && $random_count > 0 ) {
				$rand_args = array(
					'post_type'              => VB_Prod_CPT::POST_TYPE,
					'post_status'            => 'publish',
					'posts_per_page'         => $random_count,
					'orderby'                => 'rand',
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				);
				if ( $ids ) {
					$rand_args['post__not_in'] = $ids;
				}
				$extra = get_posts( $rand_args );
				if ( $extra ) {
					$ids = array_merge( $ids, array_map( 'absint', $extra ) );
				}
			}

			if ( $ids ) {
				$args['post__in']       = $ids;
				$args['orderby']        = 'post__in';
				$args['order']          = 'ASC';
				$args['posts_per_page'] = count( $ids );
			} else {
				$args['post__in'] = array( 0 );
			}
		} else {
			$tax_query = array();
			if ( ! empty( $s['categoria'] ) ) {
				$tax_query[] = array(
					'taxonomy' => VB_Prod_CPT::TAX_CATEGORIA,
					'field'    => 'slug',
					'terms'    => sanitize_title( $s['categoria'] ),
				);
			}
			if ( ! empty( $s['marca'] ) ) {
				$tax_query[] = array(
					'taxonomy' => VB_Prod_CPT::TAX_MARCA,
					'field'    => 'slug',
					'terms'    => sanitize_title( $s['marca'] ),
				);
			}
			if ( $tax_query ) {
				$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			}

			if ( isset( $_GET['vb_busca'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$busca = sanitize_text_field( wp_unslash( $_GET['vb_busca'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( $busca ) {
					$args['s'] = $busca;
				}
			}
		}

		$query     = new WP_Query( $args );
		$opts      = $this->card_opts_from_settings( $s );
		$show_nav  = ( isset( $s['show_nav'] ) && 'yes' === $s['show_nav'] && $any_carousel );
		$scroll    = isset( $s['nav_scroll'] ) ? absint( $s['nav_scroll'] ) : 300;
		$filter    = ( ! $is_manual && isset( $s['enable_filter'] ) && 'yes' === $s['enable_filter'] ) ? ' data-vb-prod-lista' : '';
		$uid       = 'vb-lista-' . $this->get_id();
		$inline    = $desktop_carr ? '' : $this->grade_inline_style( $s );
		$class     = $desktop_carr ? 'vb-prod-lista vb-prod-lista--carrossel' : 'vb-prod-lista vb-prod-lista--grid';
		if ( $mobile_carr ) {
			$class .= ' vb-prod-lista--mobile-carrossel';
		}
		if ( $tablet_carr ) {
			$class .= ' vb-prod-lista--tablet-carrossel';
		}
		$attrs = $filter;
		$attrs .= ' data-vb-layout-desktop="' . esc_attr( $layout ) . '"';
		$attrs .= ' data-vb-layout-tablet="' . esc_attr( $layout_tablet ) . '"';
		$attrs .= ' data-vb-layout-mobile="' . esc_attr( $layout_mobile ) . '"';
		if ( $paginacao ) {
			$attrs .= ' data-vb-paginacao="' . esc_attr( (string) $per_page ) . '"';
			$attrs .= ' data-vb-paginacao-tablet="' . esc_attr( (string) $per_page_tablet ) . '"';
			$attrs .= ' data-vb-paginacao-mobile="' . esc_attr( (string) $per_page_mobile ) . '"';
		}

		VB_Prod_Frontend::enqueue();

		$wrap_class = 'vb-prod-lista-wrap';
		if ( $desktop_carr ) {
			$wrap_class .= ' vb-prod-lista-wrap--carousel';
		}
		if ( $mobile_carr || $tablet_carr ) {
			$wrap_class .= ' vb-prod-lista-wrap--mobile-carousel';
		}
		if ( ( $mobile_carr || $tablet_carr ) && $show_nav ) {
			$wrap_class .= ' vb-prod-lista-wrap--mobile-nav';
		}

		$wrap_attrs  = ' data-vb-layout-desktop="' . esc_attr( $layout ) . '"';
		$wrap_attrs .= ' data-vb-layout-tablet="' . esc_attr( $layout_tablet ) . '"';
		$wrap_attrs .= ' data-vb-layout-mobile="' . esc_attr( $layout_mobile ) . '"';

		echo '<div class="' . esc_attr( $wrap_class ) . '"' . $wrap_attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $show_nav ) {
			$nav_extra = ( $mobile_carr || $tablet_carr ) && ! $desktop_carr ? ' vb-prod-nav--responsive-carousel' : '';
			echo '<button type="button" class="vb-prod-nav vb-prod-nav--prev' . esc_attr( $nav_extra ) . '" data-vb-carousel-prev aria-label="' . esc_attr__( 'Anterior', 'valle-branco-produtos' ) . '"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
		}

		if ( $any_carousel ) {
			echo '<div class="vb-prod-lista-viewport">';
		}

		$carousel_attr = $any_carousel ? ' data-vb-lista-carousel' : '';
		echo '<div class="' . esc_attr( $class ) . '" id="' . esc_attr( $uid ) . '" style="' . esc_attr( $inline ) . '"' . $attrs . $carousel_attr . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$pid   = get_the_ID();
				$terms = wp_get_post_terms( $pid, VB_Prod_CPT::TAX_CATEGORIA, array( 'fields' => 'slugs' ) );
				$slugs = ( ! is_wp_error( $terms ) && $terms ) ? implode( ' ', $terms ) : '';
				$marcas_terms = wp_get_post_terms( $pid, VB_Prod_CPT::TAX_MARCA, array( 'fields' => 'slugs' ) );
				$marcas_slugs = ( ! is_wp_error( $marcas_terms ) && $marcas_terms ) ? implode( ' ', $marcas_terms ) : '';
				$search_blob = strtolower(
					trim(
						get_the_title( $pid ) . ' ' .
						VB_Prod_Product::get_marca_nome( $pid ) . ' ' .
						VB_Prod_Product::get_categoria_nome( $pid ) . ' ' .
						VB_Prod_Product::get_sku( $pid )
					)
				);
				echo '<div class="vb-prod-lista__item" data-cats="' . esc_attr( $slugs ) . '" data-marcas="' . esc_attr( $marcas_slugs ) . '" data-search="' . esc_attr( $search_blob ) . '">';
				echo VB_Prod_Frontend::render_card( $pid, $opts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';
			}
			wp_reset_postdata();
		} elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'Nenhum produto publicado', 'valle-branco-produtos' ) . '</div>';
		} else {
			echo '<p class="vb-prod-lista__empty">' . esc_html__( 'Nenhum produto encontrado.', 'valle-branco-produtos' ) . '</p>';
		}

		echo '</div>'; // lista

		if ( $any_carousel ) {
			echo '</div>'; // viewport
		}

		if ( $show_nav ) {
			$nav_extra = ( $mobile_carr || $tablet_carr ) && ! $desktop_carr ? ' vb-prod-nav--responsive-carousel' : '';
			echo '<button type="button" class="vb-prod-nav vb-prod-nav--next' . esc_attr( $nav_extra ) . '" data-vb-carousel-next aria-label="' . esc_attr__( 'Próximo', 'valle-branco-produtos' ) . '"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
		}

		if ( $any_carousel ) {
			echo '<div class="vb-prod-carousel-dots" data-vb-carousel-dots role="tablist" aria-label="' . esc_attr__( 'Slides do carrossel', 'valle-branco-produtos' ) . '"></div>';
		}

		if ( $paginacao ) {
			echo '<nav class="vb-prod-paginacao" data-vb-paginacao-nav aria-label="' . esc_attr__( 'Paginação de produtos', 'valle-branco-produtos' ) . '"></nav>';
		}
		echo '</div>';
	}
}
