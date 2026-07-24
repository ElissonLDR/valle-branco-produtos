<?php
/**
 * Widget: Imagem de capa.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Imagem
 */
class VB_Prod_Widget_Imagem extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_imagem';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Imagem do produto', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-image';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Imagem', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'size',
			array(
				'label'   => __( 'Tamanho', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'large',
				'options' => array(
					'thumbnail' => 'Thumbnail',
					'medium'    => 'Medium',
					'large'     => 'Large',
					'full'      => 'Full',
				),
			)
		);
		$this->add_control(
			'link_produto',
			array(
				'label'        => __( 'Linkar para o produto', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);
		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alinhamento', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array( 'title' => 'Esq.', 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => 'Centro', 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => 'Dir.', 'icon' => 'eicon-text-align-right' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-el-imagem' => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo',
			array(
				'label' => __( 'Imagem', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'width',
			array(
				'label'      => __( 'Largura', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 50, 'max' => 1200 ),
					'%'  => array( 'min' => 10, 'max' => 100 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-imagem img' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
				),
			)
		);
		$this->add_responsive_control(
			'max_height',
			array(
				'label'      => __( 'Altura máxima', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-imagem img' => 'max-height: {{SIZE}}{{UNIT}}; object-fit: contain;',
				),
			)
		);
		$this->add_control(
			'bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-imagem' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'pad',
			array(
				'label'      => __( 'Padding', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-imagem' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'borda',
				'selector' => '{{WRAPPER}} .vb-prod-el-imagem',
			)
		);
		$this->add_responsive_control(
			'radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-imagem, {{WRAPPER}} .vb-prod-el-imagem img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'sombra',
				'selector' => '{{WRAPPER}} .vb-prod-el-imagem',
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
		if ( ! $id || ! has_post_thumbnail( $id ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'Imagem de capa do produto', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}
		VB_Prod_Frontend::enqueue();
		$size = isset( $s['size'] ) ? $s['size'] : 'large';
		$img  = get_the_post_thumbnail( $id, $size, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
		echo '<div class="vb-prod-el-imagem">';
		if ( isset( $s['link_produto'] ) && 'yes' === $s['link_produto'] ) {
			echo '<a href="' . esc_url( get_permalink( $id ) ) . '">' . $img . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}
}
