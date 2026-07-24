<?php
/**
 * Widget: Relacionados.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Relacionados
 */
class VB_Prod_Widget_Relacionados extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_relacionados';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Produtos relacionados', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-carousel';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Relacionados', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Quantidade', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 4,
				'min'     => 1,
				'max'     => 12,
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'      => __( 'Grade', 'valle-branco-produtos' ),
					'carrossel' => __( 'Carrossel horizontal', 'valle-branco-produtos' ),
				),
			)
		);
		$this->add_control(
			'show_nav',
			array(
				'label'        => __( 'Setas de navegação', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'layout' => 'carrossel' ),
			)
		);
		$this->end_controls_section();

		$this->controles_card_conteudo();
		$this->controles_grade( '.vb-prod-rel--grid' );
		$this->controles_card_estilo();

		$this->start_controls_section(
			'estilo_nav',
			array(
				'label'     => __( 'Navegação do carrossel', 'valle-branco-produtos' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array( 'layout' => 'carrossel' ),
			)
		);
		$this->add_control(
			'nav_bg',
			array(
				'label'     => __( 'Fundo das setas', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-rel__nav' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'nav_cor',
			array(
				'label'     => __( 'Cor das setas', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-rel__nav' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$limit = isset( $s['limit'] ) ? absint( $s['limit'] ) : 4;
		$posts = VB_Prod_Product::get_relacionados( $this->produto_id(), $limit );
		if ( empty( $posts ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'Produtos relacionados', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}

		VB_Prod_Frontend::enqueue();
		$layout = isset( $s['layout'] ) ? $s['layout'] : 'grid';
		$opts   = $this->card_opts_from_settings( $s );
		$class  = 'carrossel' === $layout ? 'vb-prod-rel vb-prod-rel--carrossel' : 'vb-prod-rel vb-prod-rel--grid';
		$uid    = 'vb-rel-' . wp_unique_id();
		$inline = ( 'carrossel' !== $layout ) ? $this->grade_inline_style( $s ) : '';

		echo '<div class="vb-prod-rel-wrap">';
		if ( 'carrossel' === $layout && isset( $s['show_nav'] ) && 'yes' === $s['show_nav'] ) {
			echo '<button type="button" class="vb-prod-rel__nav vb-prod-rel__nav--prev" data-target="' . esc_attr( $uid ) . '" aria-label="' . esc_attr__( 'Anterior', 'valle-branco-produtos' ) . '"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
		}
		echo '<div class="' . esc_attr( $class ) . '" id="' . esc_attr( $uid ) . '" style="' . esc_attr( $inline ) . '"' . ( 'carrossel' === $layout ? ' data-vb-rel-carrossel' : '' ) . '>';
		foreach ( $posts as $post ) {
			echo VB_Prod_Frontend::render_card( $post->ID, $opts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
		if ( 'carrossel' === $layout && isset( $s['show_nav'] ) && 'yes' === $s['show_nav'] ) {
			echo '<button type="button" class="vb-prod-rel__nav vb-prod-rel__nav--next" data-target="' . esc_attr( $uid ) . '" aria-label="' . esc_attr__( 'Próximo', 'valle-branco-produtos' ) . '"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
		}
		echo '</div>';
	}
}
