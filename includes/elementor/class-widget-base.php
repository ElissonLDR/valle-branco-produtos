<?php
/**
 * Base dos widgets Elementor de produtos.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Base
 */
abstract class VB_Prod_Widget_Base extends \Elementor\Widget_Base {

	/**
	 * Categorias.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'vb-produtos' );
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'produto', 'valle', 'branco', 'catalogo', 'vitrine' );
	}

	/**
	 * CSS/JS no editor e no front.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'vb-prod-front' );
	}

	/**
	 * Scripts no editor e no front.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'vb-prod-front' );
	}

	/**
	 * ID do produto (contexto Theme Builder / loop).
	 *
	 * @return int
	 */
	protected function produto_id() {
		return VB_Prod_Product::current_id();
	}

	/**
	 * Controles de grade (colunas / gap) — SELECT + CSS var para preview ao vivo.
	 *
	 * @param string $selector  Selector da grade (um só, sem vírgula).
	 * @param array  $condition Condition Elementor opcional.
	 */
	protected function controles_grade( $selector = '.vb-prod-lista', $condition = array() ) {
		$section = array(
			'label' => __( 'Grade / Layout', 'valle-branco-produtos' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		);
		if ( $condition ) {
			$section['condition'] = $condition;
		}
		$this->start_controls_section( 'sec_grade', $section );
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Colunas', 'valle-branco-produtos' ),
				'type'           => \Elementor\Controls_Manager::SELECT,
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'selectors'      => array(
					'{{WRAPPER}} ' . $selector => '--vb-cols: {{VALUE}};',
				),
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'          => __( 'Espaçamento', 'valle-branco-produtos' ),
				'type'           => \Elementor\Controls_Manager::SLIDER,
				'size_units'     => array( 'px', 'rem' ),
				'range'          => array( 'px' => array( 'min' => 0, 'max' => 64 ) ),
				'default'        => array( 'size' => 20, 'unit' => 'px' ),
				'tablet_default' => array( 'size' => 16, 'unit' => 'px' ),
				'mobile_default' => array( 'size' => 12, 'unit' => 'px' ),
				'selectors'      => array(
					'{{WRAPPER}} ' . $selector => '--vb-gap: {{SIZE}}{{UNIT}}; gap: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Inline CSS vars das colunas (garante preview mesmo antes do CSS do Elementor).
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	protected function grade_inline_style( $s ) {
		// Colunas e gap ficam só no CSS responsivo do Elementor (evita inline
		// desktop bloquear tablet/mobile no preview).
		$align = isset( $s['products_align'] ) ? $s['products_align'] : 'left';
		$map   = array(
			'left'    => 'flex-start',
			'center'  => 'center',
			'right'   => 'flex-end',
			'justify' => 'space-between',
		);
		$jc = isset( $map[ $align ] ) ? $map[ $align ] : 'flex-start';
		return '--vb-align:' . esc_attr( $jc ) . ';justify-content:' . esc_attr( $jc ) . ';';
	}

	/**
	 * Colunas (desktop) para inline style.
	 *
	 * @param array  $s       Settings.
	 * @param string $key     Chave.
	 * @param int    $default Default.
	 * @return int
	 */
	protected function grade_cols_value( $s, $key, $default ) {
		$val = isset( $s[ $key ] ) && '' !== $s[ $key ] ? $s[ $key ] : $default;
		return max( 1, min( 6, absint( $val ) ) );
	}

	/**
	 * Gap (desktop) para inline style.
	 *
	 * @param array  $s       Settings.
	 * @param string $key     Chave.
	 * @param number $default Default size.
	 * @param string $unit    Unidade default.
	 * @return string
	 */
	protected function grade_gap_value( $s, $key, $default, $unit ) {
		if ( ! empty( $s[ $key ]['size'] ) || ( isset( $s[ $key ]['size'] ) && '0' === (string) $s[ $key ]['size'] ) ) {
			$u = ! empty( $s[ $key ]['unit'] ) ? $s[ $key ]['unit'] : $unit;
			return floatval( $s[ $key ]['size'] ) . $u;
		}
		return floatval( $default ) . $unit;
	}

	/**
	 * Controles de card (o que mostrar).
	 */
	protected function controles_card_conteudo() {
		$this->start_controls_section(
			'sec_card',
			array(
				'label' => __( 'Card', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'card_tipo',
			array(
				'label'   => __( 'Tipo de card', 'valle-branco-produtos' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'padrao',
				'options' => array(
					'padrao'   => __( 'Padrão (imagem + texto)', 'valle-branco-produtos' ),
					'compacto' => __( 'Compacto', 'valle-branco-produtos' ),
					'horizontal' => __( 'Horizontal', 'valle-branco-produtos' ),
					'minimo'   => __( 'Só imagem + título', 'valle-branco-produtos' ),
				),
			)
		);
		$this->add_control(
			'show_image',
			array(
				'label'        => __( 'Mostrar imagem', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_marca',
			array(
				'label'        => __( 'Mostrar marca', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_categoria',
			array(
				'label'        => __( 'Mostrar categoria', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);
		$this->add_control(
			'show_excerpt',
			array(
				'label'        => __( 'Mostrar resumo', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);
		$this->add_control(
			'show_btn',
			array(
				'label'        => __( 'Mostrar botão', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'btn_text',
			array(
				'label'     => __( 'Texto do botão', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array( 'show_btn' => 'yes' ),
			)
		);
		$this->add_control(
			'image_size',
			array(
				'label'     => __( 'Tamanho da imagem', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'medium',
				'options'   => array(
					'thumbnail' => 'Thumbnail',
					'medium'    => 'Medium',
					'large'     => 'Large',
					'full'      => 'Full',
				),
				'condition' => array( 'show_image' => 'yes' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Estilos do card.
	 */
	protected function controles_card_estilo() {
		$this->start_controls_section(
			'estilo_card',
			array(
				'label' => __( 'Card', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'card_bg',
				'selector' => '{{WRAPPER}} .vb-prod-card',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_borda',
				'selector' => '{{WRAPPER}} .vb-prod-card',
			)
		);
		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_sombra',
				'selector' => '{{WRAPPER}} .vb-prod-card',
			)
		);
		$this->add_responsive_control(
			'card_pad',
			array(
				'label'      => __( 'Padding do corpo', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-card__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_card_hover',
			array(
				'label' => __( 'Hover do card', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'hover_transicao',
			array(
				'label'       => __( 'Duração da transição (ms)', 'valle-branco-produtos' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 100,
						'max'  => 800,
						'step' => 50,
					),
				),
				'default'     => array( 'size' => 300 ),
				'selectors'   => array(
					'{{WRAPPER}} .vb-prod-card' => '--vb-hover-ms: {{SIZE}}ms;',
				),
				'description' => __( 'Aplica transição suave no card, imagem, título e botão.', 'valle-branco-produtos' ),
			)
		);
		$this->add_control(
			'hover_fundo',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card:hover' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'hover_borda',
			array(
				'label'     => __( 'Cor da borda', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card:hover' => 'border-color: {{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'hover_sombra',
				'selector' => '{{WRAPPER}} .vb-prod-card:hover',
			)
		);
		$this->add_control(
			'hover_lift',
			array(
				'label'     => __( 'Elevar card (px)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 24,
						'step' => 1,
					),
				),
				'default'   => array( 'size' => 4 ),
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card:hover' => 'transform: translateY(-{{SIZE}}px);',
				),
			)
		);
		$this->add_control(
			'hover_titulo',
			array(
				'label'     => __( 'Cor do título', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card:hover .vb-prod-card__title a' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'hover_meta',
			array(
				'label'     => __( 'Cor da meta', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card:hover .vb-prod-card__marca' => 'color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-card:hover .vb-prod-card__meta' => 'color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'hover_img_zoom',
			array(
				'label'     => __( 'Zoom da imagem (%)', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 100,
						'max'  => 120,
						'step' => 1,
					),
				),
				'default'   => array( 'size' => 105 ),
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card:hover .vb-prod-card__media img' => 'transform: scale(calc({{SIZE}} / 100));',
				),
			)
		);
		$this->add_control(
			'hover_img_bg',
			array(
				'label'     => __( 'Fundo da área da imagem', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card:hover .vb-prod-card__media' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_titulo_card',
			array(
				'label' => __( 'Título do card', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo_titulo_card',
				'selector' => '{{WRAPPER}} .vb-prod-card__title, {{WRAPPER}} .vb-prod-card__title a',
			)
		);
		$this->add_control(
			'cor_titulo_card',
			array(
				'label'     => __( 'Cor', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card__title, {{WRAPPER}} .vb-prod-card__title a' => 'color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_meta_card',
			array(
				'label' => __( 'Marca / meta', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo_meta',
				'selector' => '{{WRAPPER}} .vb-prod-card__marca, {{WRAPPER}} .vb-prod-card__meta',
			)
		);
		$this->add_control(
			'cor_meta',
			array(
				'label'     => __( 'Cor', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card__marca' => 'color: {{VALUE}};',
					'{{WRAPPER}} .vb-prod-card__meta'  => 'color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_btn_card',
			array(
				'label' => __( 'Botão do card', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->start_controls_tabs( 'tabs_btn_card' );
		$this->start_controls_tab( 'tab_btn_n', array( 'label' => __( 'Normal', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'btn_bg',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-card__btn' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'btn_cor',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-card__btn' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'tab_btn_h', array( 'label' => __( 'Hover', 'valle-branco-produtos' ) ) );
		$this->add_control(
			'btn_bg_h',
			array(
				'label'     => __( 'Fundo', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-card__btn:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'btn_cor_h',
			array(
				'label'     => __( 'Texto', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .vb-prod-card__btn:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo_btn_card',
				'selector' => '{{WRAPPER}} .vb-prod-card__btn',
			)
		);
		$this->add_responsive_control(
			'btn_pad',
			array(
				'label'      => __( 'Padding', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-card__btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_responsive_control(
			'btn_radius',
			array(
				'label'      => __( 'Arredondamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .vb-prod-card__btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'estilo_img_card',
			array(
				'label' => __( 'Imagem do card', 'valle-branco-produtos' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'img_ratio',
			array(
				'label'     => __( 'Proporção', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '4/3',
				'options'   => array(
					'1/1'  => '1:1',
					'4/3'  => '4:3',
					'3/4'  => '3:4',
					'16/9' => '16:9',
					'auto' => __( 'Auto', 'valle-branco-produtos' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card__media' => 'aspect-ratio: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'img_bg',
			array(
				'label'     => __( 'Fundo da área', 'valle-branco-produtos' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .vb-prod-card__media' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Opções do card a partir dos settings.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	protected function card_opts_from_settings( $s ) {
		return array(
			'tipo'           => isset( $s['card_tipo'] ) ? $s['card_tipo'] : 'padrao',
			'show_image'     => ! isset( $s['show_image'] ) || 'yes' === $s['show_image'],
			// Grade: marca sempre acima do título (pedido de layout).
			'show_marca'     => true,
			'show_categoria' => isset( $s['show_categoria'] ) && 'yes' === $s['show_categoria'],
			'show_excerpt'   => isset( $s['show_excerpt'] ) && 'yes' === $s['show_excerpt'],
			'show_btn'       => ! isset( $s['show_btn'] ) || 'yes' === $s['show_btn'],
			'btn_text'       => isset( $s['btn_text'] ) ? $s['btn_text'] : '',
			'image_size'     => isset( $s['image_size'] ) ? $s['image_size'] : 'medium',
		);
	}
}
