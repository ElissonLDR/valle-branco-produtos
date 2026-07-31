<?php
/**
 * Widget: Título do produto (somente o nome, sem marca / tipo).
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Titulo
 */
class VB_Prod_Widget_Titulo extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_titulo';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Título do produto', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-heading';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Conteúdo', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tag',
			array(
				'label'   => __( 'HTML Tag do título', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'h1',
				'options' => array(
					'h1'  => 'H1',
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'div' => 'div',
					'p'   => 'p',
				),
			)
		);
		$this->add_control(
			'link_produto',
			array(
				'label'        => __( 'Linkar título para o produto', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
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
					'{{WRAPPER}} .vb-prod-el-heading' => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo',
			array(
				'label' => __( 'Título', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo',
				'selector' => '{{WRAPPER}} .vb-prod-el-titulo',
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => __( 'Cor', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-titulo, {{WRAPPER}} .vb-prod-el-titulo a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'shadow',
				'selector' => '{{WRAPPER}} .vb-prod-el-titulo',
			)
		);
		$this->add_responsive_control(
			'margin',
			array(
				'label'      => __( 'Margem', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-titulo' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render — só o nome curto (sem marca / tipo).
	 */
	protected function render() {
		$id = $this->produto_id();

		$title = '';
		if ( $id ) {
			$title = VB_Prod_Product::get_titulo_destaque( $id );
		}
		if ( '' === $title ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$title = __( 'Arroz Extra Premium', 'valle-branco-produtos' );
			} else {
				return;
			}
		}

		$s       = $this->get_settings_for_display();
		$tag     = isset( $s['tag'] ) ? $s['tag'] : 'h1';
		$allowed = array( 'h1', 'h2', 'h3', 'h4', 'div', 'p' );
		if ( ! in_array( $tag, $allowed, true ) ) {
			$tag = 'h1';
		}

		VB_Prod_Frontend::enqueue();

		$content = esc_html( $title );
		if ( $id && isset( $s['link_produto'] ) && 'yes' === $s['link_produto'] ) {
			$content = '<a href="' . esc_url( get_permalink( $id ) ) . '">' . $content . '</a>';
		}

		echo '<div class="vb-prod-el-heading">';
		printf( '<%1$s class="vb-prod-el-titulo">%2$s</%1$s>', esc_attr( $tag ), $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}
}
