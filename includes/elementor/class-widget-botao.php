<?php
/**
 * Widget: Botão.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Botao
 */
class VB_Prod_Widget_Botao extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_botao';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Botão do produto', 'valle-branco-produtos' );
	}

	/**
	 * Ícone.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * Controles.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'sec',
			array(
				'label' => __( 'Botão', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'tipo',
			array(
				'label'   => __( 'Tipo', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'revendedor',
				'options' => array(
					'revendedor'     => __( 'Falar com revendedor', 'valle-branco-produtos' ),
					'onde_encontrar' => __( 'Onde encontrar', 'valle-branco-produtos' ),
					'ver_mais'       => __( 'Ver mais (página do produto)', 'valle-branco-produtos' ),
					'custom'         => __( 'URL personalizada', 'valle-branco-produtos' ),
				),
			)
		);
		$this->add_control(
			'texto',
			array(
				'label'       => __( 'Texto (opcional)', 'valle-branco-produtos' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'Usa o texto das configurações', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'url_custom',
			array(
				'label'     => __( 'URL', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::URL,
				'condition' => array( 'tipo' => 'custom' ),
			)
		);
		$this->add_control(
			'estilo',
			array(
				'label'   => __( 'Variante', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'primary',
				'options' => array(
					'primary' => __( 'Primário', 'valle-branco-produtos' ),
					'outline' => __( 'Outline', 'valle-branco-produtos' ),
					'link'    => __( 'Link', 'valle-branco-produtos' ),
				),
			)
		);
		$this->add_control(
			'full_width',
			array(
				'label'        => __( 'Largura total', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'selectors'    => array(
					'{{WRAPPER}} .vb-prod-el-btn' => 'width: 100%;',
				),
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
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'icon',
			array(
				'label' => __( 'Ícone', 'valle-branco-produtos' ),
				'type'  => \Elementor\Controls_Manager::ICONS,
			)
		);
		$this->add_control(
			'icon_pos',
			array(
				'label'     => __( 'Posição do ícone', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => array(
					'before' => __( 'Antes', 'valle-branco-produtos' ),
					'after'  => __( 'Depois', 'valle-branco-produtos' ),
				),
				'condition' => array( 'icon[value]!' => '' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'sec_style',
			array(
				'label' => __( 'Botão', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo',
				'selector' => '{{WRAPPER}} .vb-prod-el-btn',
			)
		);
		$this->start_controls_tabs( 'tabs_btn' );
		$this->start_controls_tab( 'tab_n', array( 'label' => __( 'Normal', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-btn' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-btn' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_h', array( 'label' => __( 'Hover', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'bg_h',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-btn:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'color_h',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-el-btn:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'borda',
				'selector' => '{{WRAPPER}} .vb-prod-el-btn',
			)
		);
		$this->add_responsive_control(
			'pad',
			array(
				'label'      => __( 'Padding', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-el-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .vb-prod-el-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'sombra',
				'selector' => '{{WRAPPER}} .vb-prod-el-btn',
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$tipo = isset( $s['tipo'] ) ? $s['tipo'] : 'revendedor';
		$text = isset( $s['texto'] ) ? trim( $s['texto'] ) : '';
		$url  = '';

		switch ( $tipo ) {
			case 'onde_encontrar':
				$url  = VB_Prod_Settings::resolve_url( VB_Prod_Settings::get_value( 'url_onde_encontrar' ) );
				$text = $text ? $text : VB_Prod_Settings::get_value( 'texto_onde' );
				break;
			case 'ver_mais':
				$id   = $this->produto_id();
				$url  = $id ? (string) get_permalink( $id ) : home_url( '/produtos' );
				$text = $text ? $text : VB_Prod_Settings::get_value( 'texto_ver_mais' );
				break;
			case 'custom':
				$url  = ! empty( $s['url_custom']['url'] ) ? $s['url_custom']['url'] : '';
				$text = $text ? $text : __( 'Saiba mais', 'valle-branco-produtos' );
				break;
			case 'revendedor':
			default:
				$url  = VB_Prod_Settings::resolve_url( VB_Prod_Settings::get_value( 'url_revendedor' ) );
				$text = $text ? $text : VB_Prod_Settings::get_value( 'texto_revendedor' );
				break;
		}

		if ( '' === $url ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'URL do botão não configurada', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}

		$estilo   = isset( $s['estilo'] ) ? $s['estilo'] : 'primary';
		$target   = ( 'custom' === $tipo && ! empty( $s['url_custom']['is_external'] ) ) ? ' target="_blank"' : '';
		$nofollow = ( 'custom' === $tipo && ! empty( $s['url_custom']['nofollow'] ) ) ? ' rel="nofollow"' : '';
		$icon_html = '';
		if ( ! empty( $s['icon']['value'] ) && class_exists( '\Elementor\Icons_Manager' ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $s['icon'], array( 'aria-hidden' => 'true' ) );
			$icon_html = ob_get_clean();
		}
		$pos = isset( $s['icon_pos'] ) ? $s['icon_pos'] : 'after';

		VB_Prod_Frontend::enqueue();
		echo '<a class="vb-prod-el-btn vb-prod-el-btn--' . esc_attr( $estilo ) . '" href="' . esc_url( $url ) . '"' . $target . $nofollow . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $icon_html && 'before' === $pos ) {
			echo '<span class="vb-prod-el-btn__icon">' . $icon_html . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<span class="vb-prod-el-btn__text">' . esc_html( $text ) . '</span>';
		if ( $icon_html && 'after' === $pos ) {
			echo '<span class="vb-prod-el-btn__icon">' . $icon_html . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</a>';
	}
}
