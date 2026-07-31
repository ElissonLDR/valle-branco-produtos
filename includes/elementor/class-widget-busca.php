<?php
/**
 * Widget: Busca de produtos (ícone circular + campo expansível).
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Busca
 */
class VB_Prod_Widget_Busca extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_busca';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Busca de produtos', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-search';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Busca', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'mode',
			array(
				'label'   => __( 'Comportamento', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'filter',
				'options' => array(
					'filter'   => __( 'Filtrar grade na página (JS)', 'valle-branco-produtos' ),
					'redirect' => __( 'Ir para página de resultados', 'valle-branco-produtos' ),
				),
			)
		);
		$this->add_control(
			'placeholder',
			array(
				'label'   => __( 'Placeholder', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Buscar produto...', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'search_icon',
			array(
				'label'            => __( 'Ícone', 'valle-branco-produtos' ),
				'type'             => \Elementor\Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'recommended'      => array(
					'fa-solid' => array( 'search', 'magnifying-glass' ),
				),
				'default'          => array(
					'value'   => '',
					'library' => '',
				),
			)
		);
		$this->add_control(
			'show_field_icon',
			array(
				'label'        => __( 'Ícone dentro do campo', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'live',
			array(
				'label'        => __( 'Busca ao digitar', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'mode' => 'filter' ),
			)
		);
		$this->add_control(
			'ajuda',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<div style="padding:12px;background:#1e1f22;border:1px solid #3d3f44;border-radius:6px;line-height:1.5;color:#f3f3f3;font-size:12px;">' . __( 'Botão circular com lupa: ao clicar, abre o campo <strong style="color:#fff;">abaixo</strong>. Fecha ao clicar fora ou pressionar Esc. Use junto com a <strong style="color:#fff;">Grade de produtos</strong>.', 'valle-branco-produtos' ) . '</div>',
				'content_classes' => '',
			)
		);
		$this->add_control(
			'results_url',
			array(
				'label'       => __( 'URL de resultados', 'valle-branco-produtos' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => home_url( '/produtos' ),
				'condition'   => array( 'mode' => 'redirect' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_toggle',
			array(
				'label' => __( 'Botão lupa', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'cor_brand',
			array(
				'label'     => __( 'Cor da marca (ativo)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0A3C6B',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-busca' => '--vb-filter-brand: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'toggle_size',
			array(
				'label'      => __( 'Tamanho', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 32, 'max' => 72 ) ),
				'default'    => array( 'size' => 40, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-busca__toggle' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important; min-width: {{SIZE}}{{UNIT}} !important; min-height: {{SIZE}}{{UNIT}} !important; max-width: {{SIZE}}{{UNIT}} !important; max-height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);
		$this->add_responsive_control(
			'toggle_icon',
			array(
				'label'      => __( 'Tamanho do ícone', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 12, 'max' => 40 ) ),
				'default'    => array( 'size' => 18, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-busca__toggle i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vb-prod-busca__toggle svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->start_controls_tabs( 'tabs_toggle' );
		$this->start_controls_tab( 'toggle_n', array( 'label' => __( 'Normal', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'toggle_bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-busca__toggle' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'toggle_cor',
			array(
				'label'     => __( 'Ícone', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-busca__toggle' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'toggle_borda',
				'selector' => '{{WRAPPER}} .vb-prod-busca__toggle',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'toggle_sombra',
				'selector' => '{{WRAPPER}} .vb-prod-busca__toggle',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'toggle_h', array( 'label' => __( 'Hover', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'toggle_bg_h',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-busca__toggle:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'toggle_cor_h',
			array(
				'label'     => __( 'Ícone', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-busca__toggle:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'toggle_borda_h',
			array(
				'label'     => __( 'Borda', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-busca__toggle:hover' => 'border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'toggle_a', array( 'label' => __( 'Ativo / aberto', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'toggle_bg_a',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-busca.is-open .vb-prod-busca__toggle'   => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-busca.has-query .vb-prod-busca__toggle' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'toggle_cor_a',
			array(
				'label'     => __( 'Ícone', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-busca.is-open .vb-prod-busca__toggle'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-busca.has-query .vb-prod-busca__toggle' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'toggle_borda_a',
			array(
				'label'     => __( 'Borda', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-busca.is-open .vb-prod-busca__toggle'   => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-busca.has-query .vb-prod-busca__toggle' => 'border-color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'toggle_sombra_a',
				'selector' => '{{WRAPPER}} .vb-prod-busca.is-open .vb-prod-busca__toggle, {{WRAPPER}} .vb-prod-busca.has-query .vb-prod-busca__toggle',
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_campo',
			array(
				'label' => __( 'Campo de busca', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'field_width',
			array(
				'label'      => __( 'Largura (aberto)', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 180, 'max' => 520 ),
					'%'  => array( 'min' => 40, 'max' => 100 ),
				),
				'default'    => array( 'size' => 260, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-busca__field' => 'width: {{SIZE}}{{UNIT}}; max-width: min({{SIZE}}{{UNIT}}, calc(100vw - 2rem));',
				),
			)
		);
		$this->add_control(
			'field_bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-busca__field' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'field_borda',
				'selector' => '{{WRAPPER}} .vb-prod-busca.is-open .vb-prod-busca__field',
			)
		);
		$this->add_responsive_control(
			'field_pad',
			array(
				'label'      => __( 'Padding interno', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-busca.is-open .vb-prod-busca__field' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'field_radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-busca__field' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'field_sombra',
				'selector' => '{{WRAPPER}} .vb-prod-busca.is-open .vb-prod-busca__field',
			)
		);
		$this->add_control(
			'heading_input',
			array(
				'label'     => __( 'Texto digitado', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo_input',
				'selector' => '{{WRAPPER}} .vb-prod-busca__input',
			)
		);
		$this->add_control(
			'input_cor',
			array(
				'label'     => __( 'Cor do texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-busca__input' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'input_ph',
			array(
				'label'     => __( 'Placeholder', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-busca__input::placeholder' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'heading_icon_field',
			array(
				'label'     => __( 'Ícone dentro do campo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'field_icon_size',
			array(
				'label'      => __( 'Tamanho', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 12, 'max' => 28 ) ),
				'condition'  => array( 'show_field_icon' => 'yes' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-busca__field-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vb-prod-busca__field-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'field_icon_cor',
			array(
				'label'     => __( 'Cor', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'condition' => array( 'show_field_icon' => 'yes' ),
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-busca__field-icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-busca__field-icon svg' => 'fill: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Ícone lupa (fallback SVG).
	 *
	 * @return string
	 */
	protected function icon_search_fallback() {
		return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="2"/><path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
	}

	/**
	 * Renderiza ícone do Elementor ou fallback SVG.
	 * Font Awesome sem CSS some no front — usa SVG nativo salvo em FA/vazio.
	 *
	 * @param array  $icon     Setting ICONS.
	 * @param string $fallback HTML fallback.
	 */
	protected function render_icon_html( $icon, $fallback = '' ) {
		$library = isset( $icon['library'] ) ? (string) $icon['library'] : '';
		$value   = isset( $icon['value'] ) ? $icon['value'] : '';

		// SVG enviado pelo Elementor.
		if ( 'svg' === $library && is_array( $value ) && ! empty( $value['url'] ) && class_exists( '\Elementor\Icons_Manager' ) ) {
			\Elementor\Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) );
			return;
		}

		// FA ou vazio → lupa SVG (sempre visível, sem depender de fonte).
		if ( $fallback ) {
			echo $fallback; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		if ( ! empty( $value ) && class_exists( '\Elementor\Icons_Manager' ) ) {
			if ( $library ) {
				\Elementor\Icons_Manager::enqueue_icon_fonts( $library );
			}
			\Elementor\Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) );
		}
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s           = $this->get_settings_for_display();
		$mode        = isset( $s['mode'] ) ? $s['mode'] : 'filter';
		$ph          = ! empty( $s['placeholder'] ) ? $s['placeholder'] : __( 'Buscar produto...', 'valle-branco-produtos' );
		$live        = ( ! isset( $s['live'] ) || 'yes' === $s['live'] ) ? '1' : '0';
		$show_field  = ! isset( $s['show_field_icon'] ) || 'yes' === $s['show_field_icon'];
		$uid         = 'vb-prod-busca-' . $this->get_id();
		$search_icon = isset( $s['search_icon'] ) ? $s['search_icon'] : array();

		$action = home_url( '/produtos' );
		if ( 'redirect' === $mode && ! empty( $s['results_url']['url'] ) ) {
			$action = $s['results_url']['url'];
		}

		$prefill = '';
		if ( isset( $_GET['vb_busca'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$prefill = sanitize_text_field( wp_unslash( $_GET['vb_busca'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		VB_Prod_Frontend::enqueue();
		$open_class = $prefill ? ' is-open' : '';
		$fallback   = $this->icon_search_fallback();

		if ( 'redirect' === $mode ) {
			echo '<form class="vb-prod-busca vb-prod-busca--icon' . esc_attr( $open_class ) . '" method="get" action="' . esc_url( $action ) . '" role="search" data-vb-prod-busca>';
		} else {
			echo '<div class="vb-prod-busca vb-prod-busca--icon' . esc_attr( $open_class ) . '" data-vb-prod-busca data-live="' . esc_attr( $live ) . '" role="search">';
		}

		echo '<button type="button" class="vb-prod-busca__toggle" aria-expanded="' . ( $prefill ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $uid ) . '-field" aria-label="' . esc_attr__( 'Buscar produto', 'valle-branco-produtos' ) . '">';
		$this->render_icon_html( $search_icon, $fallback );
		echo '</button>';

		echo '<div class="vb-prod-busca__field" id="' . esc_attr( $uid ) . '-field">';
		echo '<label class="screen-reader-text" for="' . esc_attr( $uid ) . '">' . esc_html__( 'Buscar produtos', 'valle-branco-produtos' ) . '</label>';
		if ( $show_field ) {
			echo '<span class="vb-prod-busca__field-icon" aria-hidden="true">';
			$this->render_icon_html( $search_icon, $fallback );
			echo '</span>';
		}
		$input_name = ( 'redirect' === $mode ) ? ' name="vb_busca"' : '';
		echo '<input type="search" class="vb-prod-busca__input" id="' . esc_attr( $uid ) . '"' . $input_name . ' value="' . esc_attr( $prefill ) . '" placeholder="' . esc_attr( $ph ) . '" autocomplete="off" />';
		echo '</div>';

		if ( 'redirect' === $mode ) {
			echo '</form>';
		} else {
			echo '</div>';
		}
	}
}
