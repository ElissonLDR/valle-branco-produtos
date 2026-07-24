<?php
/**
 * Widget: Descrição.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Descricao
 */
class VB_Prod_Widget_Descricao extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_descricao';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Descrição do produto', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-text';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Descrição', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'mode',
			array(
				'label'   => __( 'Conteúdo', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'content',
				'options' => array(
					'content' => __( 'Descrição completa', 'valle-branco-produtos' ),
					'excerpt' => __( 'Resumo (excerpt)', 'valle-branco-produtos' ),
				),
			)
		);
		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alinhamento', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'    => array( 'title' => 'Esq.', 'icon' => 'eicon-text-align-left' ),
					'center'  => array( 'title' => 'Centro', 'icon' => 'eicon-text-align-center' ),
					'right'   => array( 'title' => 'Dir.', 'icon' => 'eicon-text-align-right' ),
					'justify' => array( 'title' => 'Just.', 'icon' => 'eicon-text-align-justify' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-el-descricao' => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo',
			array(
				'label' => __( 'Descrição', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo',
				'selector' => '{{WRAPPER}} .vb-prod-el-descricao',
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => __( 'Cor', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-descricao' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => __( 'Largura máxima', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-descricao' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'pad',
			array(
				'label'      => __( 'Padding', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-descricao' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				echo '<div class="vb-prod-el-descricao">' . esc_html__( 'Descrição do produto', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}
		echo '<div class="vb-prod-el-descricao">';
		if ( isset( $s['mode'] ) && 'excerpt' === $s['mode'] ) {
			echo wp_kses_post( get_the_excerpt( $id ) );
		} else {
			echo wp_kses_post( VB_Prod_Product::get_description( $id ) );
		}
		echo '</div>';
	}
}
