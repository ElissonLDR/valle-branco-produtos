<?php
/**
 * Widget: Carrossel de imagens.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Carrossel
 */
class VB_Prod_Widget_Carrossel extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_carrossel';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Carrossel de imagens', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-slider-push';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Carrossel', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'show_arrows',
			array(
				'label'        => __( 'Setas', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_dots',
			array(
				'label'        => __( 'Indicadores', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'info',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<div style="padding:12px;background:#1e1f22;border:1px solid #3d3f44;border-radius:6px;line-height:1.5;color:#f3f3f3;font-size:12px;">' . esc_html__( 'Usa a imagem de capa + galeria do produto.', 'valle-branco-produtos' ) . '</div>',
				'content_classes' => '',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo',
			array(
				'label' => __( 'Carrossel', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'ratio',
			array(
				'label'     => __( 'Proporção', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '4/3',
				'options'   => array(
					'1/1'  => '1:1',
					'4/3'  => '4:3',
					'3/4'  => '3:4',
					'16/9' => '16:9',
				),
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-carrossel__viewport' => 'aspect-ratio: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-carrossel' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-carrossel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'borda',
				'selector' => '{{WRAPPER}} .vb-prod-carrossel',
			)
		);
		$this->add_control(
			'arrow_bg',
			array(
				'label'     => __( 'Fundo das setas', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-carrossel__btn' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrow_cor',
			array(
				'label'     => __( 'Cor das setas', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-carrossel__btn' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'dot_cor',
			array(
				'label'     => __( 'Cor dos indicadores', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-carrossel__dot' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'dot_cor_a',
			array(
				'label'     => __( 'Indicador ativo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-carrossel__dot.is-active' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$html = VB_Prod_Frontend::render_carrossel( $this->produto_id() );
		if ( '' === $html ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'Carrossel do produto', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}
		$hide_arrows = ! isset( $s['show_arrows'] ) || 'yes' !== $s['show_arrows'];
		$hide_dots   = ! isset( $s['show_dots'] ) || 'yes' !== $s['show_dots'];
		if ( $hide_arrows ) {
			$html = preg_replace( '/<button[^>]*vb-prod-carrossel__btn[^>]*>.*?<\/button>/s', '', $html );
		}
		if ( $hide_dots ) {
			$html = preg_replace( '/<div class="vb-prod-carrossel__dots"[^>]*>.*?<\/div>/s', '', $html );
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
