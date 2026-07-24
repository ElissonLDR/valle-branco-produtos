<?php
/**
 * Widget: Filtro de categorias e marcas (estilo Lovable).
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Categorias
 */
class VB_Prod_Widget_Categorias extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_categorias';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Filtro categorias / marcas', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-filter';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Filtro', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tipo',
			array(
				'label'   => __( 'Exibir', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'ambos',
				'options' => array(
					'categorias' => __( 'Só categorias', 'valle-branco-produtos' ),
					'marcas'     => __( 'Só marcas', 'valle-branco-produtos' ),
					'ambos'      => __( 'Categorias e marcas', 'valle-branco-produtos' ),
				),
			)
		);
		$this->add_control(
			'all_label',
			array(
				'label'   => __( 'Texto “Todos”', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'Todos os produtos',
			)
		);
		$this->add_control(
			'label_ver_cats',
			array(
				'label'     => __( 'Botão categorias', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => 'Ver categorias',
				'condition' => array( 'tipo' => array( 'categorias', 'ambos' ) ),
			)
		);
		$this->add_control(
			'label_ver_marcas',
			array(
				'label'     => __( 'Botão marcas', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => 'Ver marcas',
				'condition' => array( 'tipo' => array( 'marcas', 'ambos' ) ),
			)
		);
		$this->add_control(
			'abrir_padrao',
			array(
				'label'   => __( 'Aberto por padrão', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'nenhum',
				'options' => array(
					'nenhum'     => __( 'Nenhum (fechados)', 'valle-branco-produtos' ),
					'categorias' => __( 'Ver categorias', 'valle-branco-produtos' ),
					'marcas'     => __( 'Ver marcas', 'valle-branco-produtos' ),
				),
				'description' => __( 'Qual lista começa expandida ao carregar a página.', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'show_count',
			array(
				'label'        => __( 'Mostrar quantidade', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'hide_empty',
			array(
				'label'        => __( 'Ocultar vazias', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_layout',
			array(
				'label' => __( 'Layout', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'toolbar_gap',
			array(
				'label'      => __( 'Espaço vertical (barra × contagem)', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .vb-prod-toolbar' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'row_gap',
			array(
				'label'      => __( 'Espaço entre botões', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-toolbar__row' => 'gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vb-prod-tabs'        => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'row_align',
			array(
				'label'     => __( 'Alinhamento', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => 'Esq.', 'icon' => 'eicon-text-align-left' ),
					'center'     => array( 'title' => 'Centro', 'icon' => 'eicon-text-align-center' ),
					'flex-end'   => array( 'title' => 'Dir.', 'icon' => 'eicon-text-align-right' ),
				),
				'selectors' => array( '{{WRAPPER}} .vb-prod-toolbar__row' => 'justify-content: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_botoes',
			array(
				'label' => __( 'Botões e abas', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'cor_brand',
			array(
				'label'     => __( 'Cor da marca (ativa)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0A3C6B',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-toolbar' => '--vb-filter-brand: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo_tabs',
				'selector' => '{{WRAPPER}} .vb-prod-tab, {{WRAPPER}} .vb-prod-expand__trigger',
			)
		);
		$this->add_responsive_control(
			'tab_pad',
			array(
				'label'      => __( 'Padding', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-tab'              => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .vb-prod-expand__trigger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'tab_radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-tab'              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .vb-prod-expand__trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->start_controls_tabs( 'tabs_btn_filtro' );
		$this->start_controls_tab(
			'tab_f_n',
			array(
				'label' => __( 'Normal', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'tab_bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-tab:not(.is-active)'              => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-expand__trigger:not(.is-active)' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'tab_cor',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0A3C6B',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-tab:not(.is-active)'              => 'color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-expand__trigger:not(.is-active)' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'tab_borda_n',
				'selector' => '{{WRAPPER}} .vb-prod-tab:not(.is-active), {{WRAPPER}} .vb-prod-expand__trigger:not(.is-active)',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_f_h',
			array(
				'label' => __( 'Hover', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'tab_bg_h',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-tab:not(.is-active):hover'              => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-expand__trigger:not(.is-active):hover' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'tab_cor_h',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-tab:not(.is-active):hover'              => 'color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-expand__trigger:not(.is-active):hover' => 'color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_f_a',
			array(
				'label' => __( 'Ativo', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'tab_bg_a',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0A3C6B',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-tab.is-active'              => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-expand__trigger.is-active' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'tab_cor_a',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-tab.is-active'              => 'color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-expand__trigger.is-active' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'tab_borda_a',
				'selector' => '{{WRAPPER}} .vb-prod-tab.is-active, {{WRAPPER}} .vb-prod-expand__trigger.is-active',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'tab_sombra_a',
				'selector' => '{{WRAPPER}} .vb-prod-tab.is-active, {{WRAPPER}} .vb-prod-expand__trigger.is-active',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'heading_seta',
			array(
				'label'     => __( 'Seta (Ver categorias / marcas)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'seta_tam',
			array(
				'label'      => __( 'Tamanho', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 24 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-expand__arrow'     => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vb-prod-expand__arrow svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'seta_cor',
			array(
				'label'     => __( 'Cor', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-expand__arrow' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_box_aberto',
			array(
				'label' => __( 'Box aberto (categorias / marcas)', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'box_bg',
			array(
				'label'     => __( 'Fundo do box', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-expand.is-open' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'box_borda',
				'selector' => '{{WRAPPER}} .vb-prod-expand.is-open',
			)
		);
		$this->add_responsive_control(
			'box_radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 999 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'default'    => array( 'size' => 999, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-expand' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'box_pad',
			array(
				'label'      => __( 'Padding interno', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 24 ) ),
				'default'    => array( 'size' => 6, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-expand.is-open' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_sombra',
				'selector' => '{{WRAPPER}} .vb-prod-expand.is-open',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_count',
			array(
				'label'     => __( 'Quantidade', 'valle-branco-produtos' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_count' => 'yes' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo_count',
				'selector' => '{{WRAPPER}} .vb-prod-count',
			)
		);
		$this->add_control(
			'count_num_cor',
			array(
				'label'     => __( 'Cor do número', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-count__n' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'count_txt_cor',
			array(
				'label'     => __( 'Cor do texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-count__label' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'count_margin',
			array(
				'label'      => __( 'Margem superior', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'selectors'  => array( '{{WRAPPER}} .vb-prod-count' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Seta SVG.
	 *
	 * @return string
	 */
	protected function icon_arrow() {
		return '<svg class="vb-prod-expand__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s         = $this->get_settings_for_display();
		$tipo      = isset( $s['tipo'] ) ? $s['tipo'] : 'ambos';
		$hide      = ! isset( $s['hide_empty'] ) || 'yes' === $s['hide_empty'];
		$show_cats = in_array( $tipo, array( 'categorias', 'ambos' ), true );
		$show_mar  = in_array( $tipo, array( 'marcas', 'ambos' ), true );
		$show_count = ! isset( $s['show_count'] ) || 'yes' === $s['show_count'];
		$all_label = ! empty( $s['all_label'] ) ? $s['all_label'] : 'Todos os produtos';
		$lab_cats  = ! empty( $s['label_ver_cats'] ) ? $s['label_ver_cats'] : 'Ver categorias';
		$lab_mar   = ! empty( $s['label_ver_marcas'] ) ? $s['label_ver_marcas'] : 'Ver marcas';
		$abrir     = isset( $s['abrir_padrao'] ) ? $s['abrir_padrao'] : 'nenhum';

		$cats = $show_cats ? get_terms(
			array(
				'taxonomy'   => VB_Prod_CPT::TAX_CATEGORIA,
				'hide_empty' => $hide,
			)
		) : array();
		$mars = $show_mar ? get_terms(
			array(
				'taxonomy'   => VB_Prod_CPT::TAX_MARCA,
				'hide_empty' => $hide,
			)
		) : array();

		$cats_ok = ! is_wp_error( $cats ) && ! empty( $cats );
		$mars_ok = ! is_wp_error( $mars ) && ! empty( $mars );

		if ( ! $cats_ok && ! $mars_ok ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'Cadastre categorias ou marcas nos produtos', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}

		// Só abre o que estiver disponível na exibição.
		if ( 'categorias' === $abrir && ! $cats_ok ) {
			$abrir = 'nenhum';
		}
		if ( 'marcas' === $abrir && ! $mars_ok ) {
			$abrir = 'nenhum';
		}

		$total = (int) wp_count_posts( VB_Prod_CPT::POST_TYPE )->publish;

		VB_Prod_Frontend::enqueue();

		$menu_id = 'vb-prod-filtros-menu-' . $this->get_id();

		echo '<div class="vb-prod-toolbar" data-vb-prod-filtros="filter" data-open-default="' . esc_attr( $abrir ) . '">';
		echo '<div class="vb-prod-toolbar__bar">';
		echo '<button type="button" class="vb-prod-toolbar__hamburger" data-vb-filtros-menu aria-expanded="false" aria-controls="' . esc_attr( $menu_id ) . '" aria-label="' . esc_attr__( 'Abrir filtros', 'valle-branco-produtos' ) . '">';
		echo '<svg class="vb-prod-toolbar__hamburger-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
		echo '<svg class="vb-prod-toolbar__close-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
		echo '</button>';

		echo '<div class="vb-prod-toolbar__menu" id="' . esc_attr( $menu_id ) . '">';
		echo '<div class="vb-prod-toolbar__row">';

		echo '<button type="button" class="vb-prod-tab vb-prod-tab--all is-active" data-all="1" data-cat="" data-marca="">' . esc_html( $all_label ) . '</button>';

		if ( $cats_ok ) {
			echo '<div class="vb-prod-expand" data-expand="categorias">';
			echo '<button type="button" class="vb-prod-expand__trigger" data-vb-expand="categorias" aria-expanded="false">';
			echo '<span>' . esc_html( $lab_cats ) . '</span>' . $this->icon_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</button>';
			echo '<div class="vb-prod-expand__panel" hidden>';
			echo '<nav class="vb-prod-tabs vb-prod-cats--categorias" aria-label="' . esc_attr__( 'Categorias', 'valle-branco-produtos' ) . '">';
			foreach ( $cats as $term ) {
				echo '<button type="button" class="vb-prod-tab" data-cat="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</button>';
			}
			echo '</nav>';
			echo '</div>';
			echo '</div>';
		}

		if ( $mars_ok ) {
			echo '<div class="vb-prod-expand" data-expand="marcas">';
			echo '<button type="button" class="vb-prod-expand__trigger" data-vb-expand="marcas" aria-expanded="false">';
			echo '<span>' . esc_html( $lab_mar ) . '</span>' . $this->icon_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</button>';
			echo '<div class="vb-prod-expand__panel" hidden>';
			echo '<nav class="vb-prod-tabs vb-prod-cats--marcas" aria-label="' . esc_attr__( 'Marcas', 'valle-branco-produtos' ) . '">';
			foreach ( $mars as $term ) {
				echo '<button type="button" class="vb-prod-tab" data-marca="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</button>';
			}
			echo '</nav>';
			echo '</div>';
			echo '</div>';
		}

		echo '</div>'; // row
		echo '</div>'; // menu
		echo '</div>'; // bar

		echo '<button type="button" class="vb-prod-toolbar__clear" data-vb-filtros-clear hidden><span class="vb-prod-toolbar__clear-x" aria-hidden="true">×</span><span class="vb-prod-toolbar__clear-label">' . esc_html__( 'Limpar os filtros', 'valle-branco-produtos' ) . '</span></button>';

		if ( $show_count ) {
			$label = ( 1 === $total )
				? __( 'produto encontrado', 'valle-branco-produtos' )
				: __( 'produtos encontrados', 'valle-branco-produtos' );
			echo '<p class="vb-prod-count" data-vb-prod-count>';
			echo '<span class="vb-prod-count__n">' . esc_html( (string) $total ) . '</span> ';
			echo '<span class="vb-prod-count__label">' . esc_html( $label ) . '</span>';
			echo '</p>';
		}

		echo '</div>';
	}
}
