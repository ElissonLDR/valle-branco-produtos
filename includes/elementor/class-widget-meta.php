<?php
/**
 * Widget: Meta (marca / categoria / pesos).
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Meta
 */
class VB_Prod_Widget_Meta extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_meta';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Marca / categoria / pesos', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-tags';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Campos', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'show_marca',
			array(
				'label'        => __( 'Marca', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_categoria',
			array(
				'label'        => __( 'Categoria', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_pesos',
			array(
				'label'        => __( 'Pesos', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alinhamento', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => 'Esq.', 'icon' => 'eicon-text-align-left' ),
					'center'     => array( 'title' => 'Centro', 'icon' => 'eicon-text-align-center' ),
					'flex-end'   => array( 'title' => 'Dir.', 'icon' => 'eicon-text-align-right' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-el-meta' => 'justify-content: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo',
			array(
				'label' => __( 'Chips', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Espaçamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array( '{{WRAPPER}} .vb-prod-el-meta' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo',
				'selector' => '{{WRAPPER}} .vb-prod-el-chip',
			)
		);
		$this->add_control(
			'bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-chip' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'cor',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-chip' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'borda',
				'selector' => '{{WRAPPER}} .vb-prod-el-chip',
			)
		);
		$this->add_responsive_control(
			'pad',
			array(
				'label'      => __( 'Padding', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-chip' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-chip' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$id = $this->produto_id();
		$s  = $this->get_settings_for_display();
		if ( ! $id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'Marca / categoria', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}
		VB_Prod_Frontend::enqueue();
		echo '<div class="vb-prod-el-meta">';
		if ( isset( $s['show_marca'] ) && 'yes' === $s['show_marca'] ) {
			$marca = VB_Prod_Product::get_marca_nome( $id );
			if ( $marca ) {
				echo '<span class="vb-prod-el-chip">' . esc_html( $marca ) . '</span>';
			}
		}
		if ( isset( $s['show_categoria'] ) && 'yes' === $s['show_categoria'] ) {
			$cat = VB_Prod_Product::get_categoria_nome( $id );
			if ( $cat ) {
				echo '<span class="vb-prod-el-chip vb-prod-el-chip--muted">' . esc_html( $cat ) . '</span>';
			}
		}
		if ( isset( $s['show_pesos'] ) && 'yes' === $s['show_pesos'] ) {
			foreach ( VB_Prod_Product::get_pesos_lista( $id ) as $peso ) {
				echo '<span class="vb-prod-el-chip vb-prod-el-chip--outline">' . esc_html( $peso ) . '</span>';
			}
		}
		echo '</div>';
	}
}
