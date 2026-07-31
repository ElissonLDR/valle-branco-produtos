<?php
/**
 * Widget: Tags do produto (marca, pesos, embalagens).
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Widget_Tags
 */
class VB_Prod_Widget_Tags extends VB_Prod_Widget_Base {

	/**
	 * Nome.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'vb_prod_tags';
	}

	/**
	 * Título.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Tags do produto', 'valle-branco-produtos' );
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
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'tags', 'marca', 'peso', 'embalagem', 'variacao', 'produto' );
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
			'show_marca',
			array(
				'label'        => __( 'Marca', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_pesos',
			array(
				'label'        => __( 'Pesos / variações', 'valle-branco-produtos' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_embalagens',
			array(
				'label'        => __( 'Embalagens (fardo)', 'valle-branco-produtos' ),
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
					'{{WRAPPER}} .vb-prod-el-tags' => 'justify-content: {{VALUE}};',
				),
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
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Espaçamento', 'valle-branco-produtos' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array( 'size' => 8, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .vb-prod-el-tags' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo',
				'selector' => '{{WRAPPER}} .vb-prod-tag',
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
				echo '<div class="vb-prod-el-tags" aria-hidden="true">';
				echo '<span class="vb-prod-tag vb-prod-tag--marca">Valle Branco</span>';
				echo '<span class="vb-prod-tag vb-prod-tag--peso">5kg</span>';
				echo '<span class="vb-prod-tag vb-prod-tag--peso">2kg</span>';
				echo '<span class="vb-prod-tag vb-prod-tag--embalagem">6x5kg · 15x2kg</span>';
				echo '</div>';
			}
			return;
		}

		VB_Prod_Frontend::enqueue();

		$has = false;
		ob_start();
		echo '<div class="vb-prod-el-tags">';

		if ( isset( $s['show_marca'] ) && 'yes' === $s['show_marca'] ) {
			$marca = VB_Prod_Product::get_marca_nome( $id );
			if ( $marca ) {
				$has = true;
				echo '<span class="vb-prod-tag vb-prod-tag--marca">' . esc_html( $marca ) . '</span>';
			}
		}

		if ( isset( $s['show_pesos'] ) && 'yes' === $s['show_pesos'] ) {
			foreach ( VB_Prod_Product::get_pesos_lista( $id ) as $peso ) {
				$has = true;
				echo '<span class="vb-prod-tag vb-prod-tag--peso">' . esc_html( $peso ) . '</span>';
			}
		}

		if ( isset( $s['show_embalagens'] ) && 'yes' === $s['show_embalagens'] ) {
			$emb = VB_Prod_Product::get_embalagens_label( $id );
			if ( $emb ) {
				$has = true;
				echo '<span class="vb-prod-tag vb-prod-tag--embalagem">' . esc_html( $emb ) . '</span>';
			}
		}

		echo '</div>';
		$html = ob_get_clean();

		if ( ! $has ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="vb-prod-el-placeholder">' . esc_html__( 'Tags do produto', 'valle-branco-produtos' ) . '</div>';
			}
			return;
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
