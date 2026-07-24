<?php
/**
 * Front: assets e shortcodes leves.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Frontend
 */
class VB_Prod_Frontend {

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'register_assets' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'enqueue' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'register_assets' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'enqueue' ) );
		add_shortcode( 'vb_produto_carrossel', array( $this, 'shortcode_carrossel' ) );
		add_shortcode( 'vb_produto_nutricao', array( $this, 'shortcode_nutricao' ) );
	}

	/**
	 * Registra (não enfileira globalmente).
	 */
	public function register_assets() {
		if ( wp_style_is( 'vb-prod-front', 'registered' ) ) {
			return;
		}
		wp_register_style(
			'vb-prod-front',
			VB_PROD_URL . 'public/css/produtos.css',
			array(),
			VB_PROD_VERSION
		);
		wp_register_script(
			'vb-prod-front',
			VB_PROD_URL . 'public/js/carrossel.js',
			array(),
			VB_PROD_VERSION,
			true
		);
	}

	/**
	 * Enfileira quando um widget/shortcode precisa.
	 */
	public static function enqueue() {
		wp_enqueue_style( 'vb-prod-front' );
		wp_enqueue_script( 'vb-prod-front' );
	}

	/**
	 * Shortcode carrossel.
	 *
	 * @param array $atts Atts.
	 * @return string
	 */
	public function shortcode_carrossel( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'vb_produto_carrossel'
		);
		return self::render_carrossel( absint( $atts['id'] ) );
	}

	/**
	 * Shortcode nutrição.
	 *
	 * @param array $atts Atts.
	 * @return string
	 */
	public function shortcode_nutricao( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'vb_produto_nutricao'
		);
		return self::render_nutricao( absint( $atts['id'] ) );
	}

	/**
	 * HTML do carrossel.
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function render_carrossel( $post_id = 0 ) {
		$post_id = VB_Prod_Product::current_id( $post_id );
		$ids     = VB_Prod_Product::get_carousel_ids( $post_id );
		if ( empty( $ids ) ) {
			return '';
		}

		self::enqueue();
		$title = VB_Prod_Product::get_title( $post_id );
		$uid   = 'vb-carrossel-' . $post_id . '-' . wp_unique_id();

		ob_start();
		?>
		<div class="vb-prod-carrossel" id="<?php echo esc_attr( $uid ); ?>" data-vb-carrossel>
			<div class="vb-prod-carrossel__viewport">
				<?php foreach ( $ids as $i => $aid ) : ?>
					<figure class="vb-prod-carrossel__slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
						<?php
						echo wp_get_attachment_image(
							$aid,
							'large',
							false,
							array(
								'alt'      => $title,
								'loading'  => 0 === $i ? 'eager' : 'lazy',
								'decoding' => 'async',
							)
						);
						?>
					</figure>
				<?php endforeach; ?>
			</div>
			<?php if ( count( $ids ) > 1 ) : ?>
				<button type="button" class="vb-prod-carrossel__btn vb-prod-carrossel__btn--prev" aria-label="<?php esc_attr_e( 'Anterior', 'valle-branco-produtos' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<button type="button" class="vb-prod-carrossel__btn vb-prod-carrossel__btn--next" aria-label="<?php esc_attr_e( 'Próximo', 'valle-branco-produtos' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<div class="vb-prod-carrossel__dots" role="tablist">
					<?php foreach ( $ids as $i => $aid ) : ?>
						<button type="button" class="vb-prod-carrossel__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr( (string) $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Imagem %d', 'valle-branco-produtos' ), $i + 1 ) ); ?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * HTML das tabelas do produto (Pacote / Pallet / Tributação).
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function render_nutricao( $post_id = 0 ) {
		$data = VB_Prod_Product::get_nutricao( $post_id );
		if ( empty( $data['tabelas'] ) ) {
			return '';
		}
		self::enqueue();

		ob_start();
		?>
		<div class="vb-prod-fichas">
			<?php foreach ( $data['tabelas'] as $tabela ) : ?>
				<?php
				$estilo = ! empty( $tabela['estilo'] ) ? $tabela['estilo'] : 'azul';
				$titulo = isset( $tabela['titulo'] ) ? $tabela['titulo'] : '';
				$linhas = ! empty( $tabela['linhas'] ) ? $tabela['linhas'] : array();
				if ( '' === $titulo && empty( $linhas ) ) {
					continue;
				}
				?>
				<section class="vb-prod-ficha vb-prod-ficha--<?php echo esc_attr( $estilo ); ?>">
					<?php if ( $titulo ) : ?>
						<h3 class="vb-prod-ficha__titulo"><?php echo esc_html( $titulo ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $linhas ) ) : ?>
						<ul class="vb-prod-ficha__lista">
							<?php foreach ( $linhas as $linha ) : ?>
								<?php
								$campo = isset( $linha['campo'] ) ? $linha['campo'] : '';
								$valor = isset( $linha['valor'] ) ? $linha['valor'] : '';
								if ( '' === $campo && '' === $valor ) {
									continue;
								}
								?>
								<li class="vb-prod-ficha__item">
									<?php if ( $campo ) : ?>
										<span class="vb-prod-ficha__campo"><?php echo esc_html( $campo ); ?></span>
									<?php endif; ?>
									<?php if ( $campo && '' !== $valor ) : ?>
										<span class="vb-prod-ficha__sep" aria-hidden="true">-</span>
									<?php endif; ?>
									<?php if ( '' !== $valor ) : ?>
										<span class="vb-prod-ficha__valor"><?php echo esc_html( $valor ); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Card de produto (lista / relacionados).
	 *
	 * @param int   $post_id ID.
	 * @param array $opts    Opções.
	 * @return string
	 */
	public static function render_card( $post_id, $opts = array() ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return '';
		}
		self::enqueue();

		if ( is_string( $opts ) ) {
			$opts = array( 'btn_text' => $opts );
		}

		$opts = wp_parse_args(
			$opts,
			array(
				'tipo'           => 'padrao',
				'show_image'     => true,
				'show_marca'     => true,
				'show_categoria' => true,
				'show_excerpt'   => false,
				'show_btn'       => true,
				'btn_text'       => '',
				'image_size'     => 'medium',
			)
		);

		$btn = $opts['btn_text'];
		if ( '' === $btn ) {
			$btn = VB_Prod_Settings::get_value( 'texto_ver_mais' );
		}

		$permalink = get_permalink( $post_id );
		$title     = get_the_title( $post_id );
		$marca     = $opts['show_marca'] ? VB_Prod_Product::get_marca_nome( $post_id ) : '';
		$cat       = $opts['show_categoria'] ? VB_Prod_Product::get_categoria_nome( $post_id ) : '';
		$tipo      = sanitize_html_class( $opts['tipo'] );

		ob_start();
		?>
		<article class="vb-prod-card vb-prod-card--<?php echo esc_attr( $tipo ); ?>">
			<?php if ( $opts['show_image'] ) : ?>
				<a class="vb-prod-card__media" href="<?php echo esc_url( $permalink ); ?>">
					<?php
					if ( has_post_thumbnail( $post_id ) ) {
						echo get_the_post_thumbnail( $post_id, $opts['image_size'], array( 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					echo self::wave_divider_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</a>
			<?php endif; ?>
			<div class="vb-prod-card__body">
				<?php if ( $cat || $marca ) : ?>
					<p class="vb-prod-card__meta">
						<?php echo esc_html( trim( $marca . ( $marca && $cat ? ' · ' : '' ) . $cat ) ); ?>
					</p>
				<?php endif; ?>
				<h3 class="vb-prod-card__title">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
				</h3>
				<?php if ( $opts['show_excerpt'] ) : ?>
					<div class="vb-prod-card__excerpt"><?php echo wp_kses_post( get_the_excerpt( $post_id ) ); ?></div>
				<?php endif; ?>
				<?php if ( $opts['show_btn'] ) : ?>
					<a class="vb-prod-card__btn" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $btn ); ?></a>
				<?php endif; ?>
			</div>
		</article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * SVG da onda do card (mesmo do Lovable).
	 *
	 * @return string
	 */
	public static function wave_divider_markup() {
		return '<svg class="vb-prod-card__wave" viewBox="0 0 338 55" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
			. '<path d="M339,27.6c-69.5,9.2-148.9,14.5-233.2,14.5s-71.7-1-105.8-2.8v15.7h78.3c.4,0,.7,0,1.1,0s.8,0,1.2,0h258.4v-11s0,0,0,0v-16.4Z" fill="#0A3C6B"/>'
			. '<path d="M339,0v19.5c-58.8,10.6-127.2,16.7-200.2,16.7S43.2,33.5,0,28.5v-10.7C100.6,39.7,227,31.8,339,0Z" fill="#91805B"/>'
			. '</svg>';
	}
}
