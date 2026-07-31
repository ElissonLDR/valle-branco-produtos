<?php
/**
 * Widget: Tabelas do produto.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Nutricao
 */
class VB_Prod_Widget_Nutricao extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_nutricao';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Produto — Tabela', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-table';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Tabela', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'info',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<div style="padding:12px;background:#1e1f22;border:1px solid #3d3f44;border-radius:6px;line-height:1.5;color:#f3f3f3;font-size:12px;">' . esc_html__( 'Usa as tabelas do painel do produto (Pacote, Pallet, Tributação…).', 'valle-branco-produtos' ) . '</div>',
				'content_classes' => '',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo',
			array(
				'label' => __( 'Estilo', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'titulo_azul',
			array(
				'label'     => __( 'Fundo título azul', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-ficha--azul .vb-prod-ficha__titulo' => 'background-color: {{VALUE}}; color: #ffffff !important;',
				),
			)
		);
		$this->add_control(
			'titulo_ouro',
			array(
				'label'     => __( 'Fundo título ouro', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-ficha--ouro .vb-prod-ficha__titulo' => 'background-color: {{VALUE}}; color: #ffffff !important;',
				),
			)
		);
		$this->add_control(
			'titulo_texto',
			array(
				'label'     => __( 'Texto do título', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-ficha__titulo' => 'color: {{VALUE}} !important;',
				),
			)
		);
		$this->add_control(
			'campo_cor',
			array(
				'label'     => __( 'Labels (campos)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-ficha__campo' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'texto_cor',
			array(
				'label'     => __( 'Valores', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-ficha__valor' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'linha_cor',
			array(
				'label'     => __( 'Cor das divisórias', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-ficha__item' => 'border-bottom-color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo',
				'selector' => '{{WRAPPER}} .vb-prod-ficha__item',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo_titulo',
				'label'    => __( 'Tipografia do título', 'valle-branco-produtos' ),
				'selector' => '{{WRAPPER}} .vb-prod-ficha__titulo',
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Espaço entre tabelas', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px' => array( 'min' => 8, 'max' => 80 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-fichas' => 'gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .vb-prod-ficha-grupo__cols' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$html = VB_Prod_Frontend::render_nutricao( $this->produto_id() );
		if ( '' === $html ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'Tabela', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
