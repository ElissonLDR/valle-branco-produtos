<?php
/**
 * Metaboxes e persistência segura dos dados do produto.
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Meta
 */
class VB_Prod_Meta {

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_filter( 'get_user_option_meta-box-order_' . VB_Prod_CPT::POST_TYPE, array( $this, 'force_meta_box_order' ) );
		add_action( 'save_post_' . VB_Prod_CPT::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'set_object_terms', array( $this, 'sync_tax_to_meta' ), 10, 4 );
	}

	/**
	 * Metaboxes.
	 */
	public function add_boxes() {
		add_meta_box(
			'vb_prod_dados',
			__( 'Dados do produto', 'valle-branco-produtos' ),
			array( $this, 'render_dados' ),
			VB_Prod_CPT::POST_TYPE,
			'side',
			'high'
		);

		add_meta_box(
			'vb_prod_nutricao',
			__( 'Tabela', 'valle-branco-produtos' ),
			array( $this, 'render_nutricao' ),
			VB_Prod_CPT::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'vb_prod_galeria',
			__( 'Galeria (carrossel)', 'valle-branco-produtos' ),
			array( $this, 'render_galeria' ),
			VB_Prod_CPT::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Mantém Tabela acima da Galeria na coluna principal.
	 *
	 * @param array|false $order Ordem salva do usuário.
	 * @return array|false
	 */
	public function force_meta_box_order( $order ) {
		if ( ! is_array( $order ) ) {
			$order = array();
		}

		$normal = isset( $order['normal'] ) ? explode( ',', (string) $order['normal'] ) : array();
		$normal = array_values(
			array_filter(
				$normal,
				static function ( $id ) {
					return ! in_array( $id, array( 'vb_prod_galeria', 'vb_prod_nutricao' ), true );
				}
			)
		);
		array_unshift( $normal, 'vb_prod_nutricao', 'vb_prod_galeria' );
		$order['normal'] = implode( ',', $normal );

		return $order;
	}

	/**
	 * SKU + pesos.
	 *
	 * @param WP_Post $post Post.
	 */
	public function render_dados( $post ) {
		wp_nonce_field( 'vb_prod_save_meta', 'vb_prod_meta_nonce' );
		$sku   = get_post_meta( $post->ID, '_vb_sku', true );
		$pesos = get_post_meta( $post->ID, '_vb_pesos', true );
		?>
		<p>
			<label for="vb_prod_sku"><strong><?php esc_html_e( 'SKU / código SAP', 'valle-branco-produtos' ); ?></strong></label>
			<input type="text" class="widefat" id="vb_prod_sku" name="vb_prod_sku" value="<?php echo esc_attr( $sku ); ?>" autocomplete="off" />
			<span class="description"><?php esc_html_e( 'Usado pelo Onde Encontrar / n8n.', 'valle-branco-produtos' ); ?></span>
		</p>
		<p>
			<label for="vb_prod_pesos"><strong><?php esc_html_e( 'Pesos / embalagens', 'valle-branco-produtos' ); ?></strong></label>
			<input type="text" class="widefat" id="vb_prod_pesos" name="vb_prod_pesos" value="<?php echo esc_attr( $pesos ); ?>" placeholder="1kg, 5kg" />
		</p>
		<?php
	}

	/**
	 * Galeria.
	 *
	 * @param WP_Post $post Post.
	 */
	public function render_galeria( $post ) {
		$ids = VB_Prod_Product::get_gallery_ids( $post->ID );
		?>
		<div class="vb-prod-galeria" data-frame-title="<?php esc_attr_e( 'Selecionar imagens', 'valle-branco-produtos' ); ?>">
			<input type="hidden" name="vb_prod_galeria" id="vb_prod_galeria" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" />
			<ul class="vb-prod-galeria__list">
				<?php foreach ( $ids as $id ) : ?>
					<?php $url = wp_get_attachment_image_url( $id, 'thumbnail' ); ?>
					<?php if ( $url ) : ?>
						<li data-id="<?php echo esc_attr( (string) $id ); ?>">
							<img src="<?php echo esc_url( $url ); ?>" alt="" />
							<button type="button" class="vb-prod-galeria__remove" aria-label="<?php esc_attr_e( 'Remover', 'valle-branco-produtos' ); ?>">&times;</button>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<p>
				<button type="button" class="button" id="vb_prod_galeria_add"><?php esc_html_e( 'Adicionar imagens', 'valle-branco-produtos' ); ?></button>
				<button type="button" class="button-link-delete" id="vb_prod_galeria_clear"><?php esc_html_e( 'Limpar galeria', 'valle-branco-produtos' ); ?></button>
			</p>
			<p class="description"><?php esc_html_e( 'A capa é a Imagem destacada. Estas imagens entram no carrossel junto com a capa.', 'valle-branco-produtos' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Tabelas editáveis (Pacote / Pallet / Tributação…).
	 *
	 * @param WP_Post $post Post.
	 */
	public function render_nutricao( $post ) {
		$data    = VB_Prod_Product::get_nutricao( $post->ID );
		$tabelas = ! empty( $data['tabelas'] ) ? $data['tabelas'] : array(
			array(
				'titulo' => 'Pacote',
				'estilo' => 'azul',
				'linhas' => array(
					array( 'campo' => '', 'valor' => '' ),
				),
			),
		);
		?>
		<div
			class="vb-prod-tabelas"
			data-confirm-table="<?php esc_attr_e( 'Excluir esta tabela e todos os valores?', 'valle-branco-produtos' ); ?>"
			data-confirm-row="<?php esc_attr_e( 'Excluir esta linha?', 'valle-branco-produtos' ); ?>"
			data-title-placeholder="<?php esc_attr_e( 'Título da tabela', 'valle-branco-produtos' ); ?>"
			data-campo-placeholder="<?php esc_attr_e( 'Campo', 'valle-branco-produtos' ); ?>"
			data-valor-placeholder="<?php esc_attr_e( 'Valor', 'valle-branco-produtos' ); ?>"
		>
			<p class="description" style="margin-top:0">
				<?php esc_html_e( 'Cada bloco é uma coluna na página (ex.: Pacote, Pallet, Tributação). Você pode adicionar, editar ou excluir tabelas e linhas.', 'valle-branco-produtos' ); ?>
			</p>

			<div class="vb-prod-tabelas__list">
				<?php foreach ( $tabelas as $ti => $tabela ) : ?>
					<?php self::render_tabela_card( (int) $ti, $tabela ); ?>
				<?php endforeach; ?>
			</div>

			<p class="vb-prod-tabelas__toolbar">
				<button type="button" class="button button-primary" id="vb_prod_tabela_add"><?php esc_html_e( 'Adicionar tabela', 'valle-branco-produtos' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Card de uma tabela no admin.
	 *
	 * @param int   $index  Índice.
	 * @param array $tabela Dados.
	 */
	public static function render_tabela_card( $index, $tabela ) {
		$titulo = isset( $tabela['titulo'] ) ? $tabela['titulo'] : '';
		$estilo = isset( $tabela['estilo'] ) ? $tabela['estilo'] : 'azul';
		$linhas = ! empty( $tabela['linhas'] ) ? $tabela['linhas'] : array( array( 'campo' => '', 'valor' => '' ) );
		$prefix = 'vb_prod_nutricao[tabelas][' . $index . ']';
		?>
		<div class="vb-prod-tabela-card" data-index="<?php echo esc_attr( (string) $index ); ?>">
			<div class="vb-prod-tabela-card__head">
				<div class="vb-prod-tabela-card__title-wrap">
					<label class="screen-reader-text"><?php esc_html_e( 'Título', 'valle-branco-produtos' ); ?></label>
					<input
						type="text"
						class="vb-prod-tabela-card__titulo"
						name="<?php echo esc_attr( $prefix ); ?>[titulo]"
						value="<?php echo esc_attr( $titulo ); ?>"
						placeholder="<?php esc_attr_e( 'Título da tabela', 'valle-branco-produtos' ); ?>"
					/>
					<label class="vb-prod-tabela-card__estilo-label">
						<span><?php esc_html_e( 'Estilo', 'valle-branco-produtos' ); ?></span>
						<select name="<?php echo esc_attr( $prefix ); ?>[estilo]">
							<option value="azul" <?php selected( $estilo, 'azul' ); ?>><?php esc_html_e( 'Azul', 'valle-branco-produtos' ); ?></option>
							<option value="ouro" <?php selected( $estilo, 'ouro' ); ?>><?php esc_html_e( 'Ouro', 'valle-branco-produtos' ); ?></option>
						</select>
					</label>
				</div>
				<button type="button" class="button-link-delete vb-prod-tabela-card__rm" title="<?php esc_attr_e( 'Excluir tabela', 'valle-branco-produtos' ); ?>">
					<?php esc_html_e( 'Excluir tabela', 'valle-branco-produtos' ); ?>
				</button>
			</div>

			<table class="widefat striped vb-prod-tabela-card__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Campo', 'valle-branco-produtos' ); ?></th>
						<th><?php esc_html_e( 'Valor', 'valle-branco-produtos' ); ?></th>
						<th class="vb-prod-tabela-card__actions"></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $linhas as $ri => $linha ) : ?>
						<tr>
							<td>
								<input
									type="text"
									name="<?php echo esc_attr( $prefix ); ?>[linhas][<?php echo esc_attr( (string) $ri ); ?>][campo]"
									value="<?php echo esc_attr( $linha['campo'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Campo', 'valle-branco-produtos' ); ?>"
								/>
							</td>
							<td>
								<input
									type="text"
									name="<?php echo esc_attr( $prefix ); ?>[linhas][<?php echo esc_attr( (string) $ri ); ?>][valor]"
									value="<?php echo esc_attr( $linha['valor'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Valor', 'valle-branco-produtos' ); ?>"
								/>
							</td>
							<td class="vb-prod-tabela-card__actions">
								<button type="button" class="button-link-delete vb-prod-tabela-card__rm-row" title="<?php esc_attr_e( 'Excluir linha', 'valle-branco-produtos' ); ?>">&times;</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p class="vb-prod-tabela-card__footer">
				<button type="button" class="button vb-prod-tabela-card__add-row"><?php esc_html_e( 'Adicionar linha', 'valle-branco-produtos' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Salva meta com nonce + capability.
	 *
	 * @param int     $post_id ID.
	 * @param WP_Post $post    Post.
	 */
	public function save( $post_id, $post ) {
		if ( ! isset( $_POST['vb_prod_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vb_prod_meta_nonce'] ) ), 'vb_prod_save_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$sku = isset( $_POST['vb_prod_sku'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_prod_sku'] ) ) : '';
		update_post_meta( $post_id, '_vb_sku', $sku );

		$pesos = isset( $_POST['vb_prod_pesos'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_prod_pesos'] ) ) : '';
		update_post_meta( $post_id, '_vb_pesos', $pesos );

		$galeria_raw = isset( $_POST['vb_prod_galeria'] ) ? sanitize_text_field( wp_unslash( $_POST['vb_prod_galeria'] ) ) : '';
		$galeria_ids = array_filter( array_map( 'absint', explode( ',', $galeria_raw ) ) );
		$galeria_ids = array_values(
			array_filter(
				$galeria_ids,
				static function ( $id ) {
					return $id && wp_attachment_is_image( $id );
				}
			)
		);
		update_post_meta( $post_id, '_vb_galeria', $galeria_ids );

		$nutricao_in = isset( $_POST['vb_prod_nutricao'] ) && is_array( $_POST['vb_prod_nutricao'] )
			? wp_unslash( $_POST['vb_prod_nutricao'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();
		update_post_meta( $post_id, '_vb_nutricao', self::sanitize_nutricao( $nutricao_in ) );

		self::write_tax_meta( $post_id );
	}

	/**
	 * Sincroniza taxonomia → meta string (OE / n8n).
	 *
	 * @param int    $object_id Object ID.
	 * @param array  $terms     Terms.
	 * @param array  $tt_ids    Term taxonomy IDs.
	 * @param string $taxonomy  Taxonomy.
	 */
	public function sync_tax_to_meta( $object_id, $terms, $tt_ids, $taxonomy ) {
		unset( $terms, $tt_ids );
		if ( VB_Prod_CPT::POST_TYPE !== get_post_type( $object_id ) ) {
			return;
		}
		if ( ! in_array( $taxonomy, array( VB_Prod_CPT::TAX_CATEGORIA, VB_Prod_CPT::TAX_MARCA ), true ) ) {
			return;
		}
		self::write_tax_meta( (int) $object_id );
	}

	/**
	 * Grava `_vb_marca` e `_vb_categoria` a partir das taxonomias.
	 *
	 * @param int $post_id ID.
	 */
	public static function write_tax_meta( $post_id ) {
		$cats = get_the_terms( $post_id, VB_Prod_CPT::TAX_CATEGORIA );
		$cat  = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
		update_post_meta( $post_id, '_vb_categoria', sanitize_text_field( $cat ) );

		$marcas = get_the_terms( $post_id, VB_Prod_CPT::TAX_MARCA );
		$marca  = ( ! empty( $marcas ) && ! is_wp_error( $marcas ) ) ? $marcas[0]->name : '';
		update_post_meta( $post_id, '_vb_marca', sanitize_text_field( $marca ) );
	}

	/**
	 * Sanitiza estrutura de tabelas (com conversão do formato antigo).
	 *
	 * @param array $raw Raw.
	 * @return array{tabelas:array<int,array{titulo:string,estilo:string,linhas:array<int,array{campo:string,valor:string}>}>}
	 */
	public static function sanitize_nutricao( $raw ) {
		if ( ! is_array( $raw ) ) {
			return array( 'tabelas' => array() );
		}

		// Formato antigo: colunas + linhas planas.
		if ( empty( $raw['tabelas'] ) && ( ! empty( $raw['colunas'] ) || ! empty( $raw['linhas'] ) ) ) {
			$raw = array( 'tabelas' => self::legacy_flat_to_tabelas( $raw ) );
		}

		$tabelas = array();
		if ( ! empty( $raw['tabelas'] ) && is_array( $raw['tabelas'] ) ) {
			foreach ( $raw['tabelas'] as $tabela ) {
				if ( ! is_array( $tabela ) ) {
					continue;
				}
				$titulo = isset( $tabela['titulo'] ) ? sanitize_text_field( $tabela['titulo'] ) : '';
				$estilo = isset( $tabela['estilo'] ) ? sanitize_key( $tabela['estilo'] ) : 'azul';
				if ( ! in_array( $estilo, array( 'azul', 'ouro' ), true ) ) {
					$estilo = 'azul';
				}

				$linhas = array();
				if ( ! empty( $tabela['linhas'] ) && is_array( $tabela['linhas'] ) ) {
					foreach ( $tabela['linhas'] as $linha ) {
						if ( ! is_array( $linha ) ) {
							continue;
						}
						// Aceita ['campo'=>,'valor'=>] ou [0=>campo,1=>valor].
						if ( isset( $linha['campo'] ) || isset( $linha['valor'] ) ) {
							$campo = isset( $linha['campo'] ) ? sanitize_text_field( $linha['campo'] ) : '';
							$valor = isset( $linha['valor'] ) ? sanitize_text_field( $linha['valor'] ) : '';
						} else {
							$vals  = array_values( $linha );
							$campo = isset( $vals[0] ) ? sanitize_text_field( $vals[0] ) : '';
							$valor = isset( $vals[1] ) ? sanitize_text_field( $vals[1] ) : '';
						}
						if ( '' === $campo && '' === $valor ) {
							continue;
						}
						$linhas[] = array(
							'campo' => $campo,
							'valor' => $valor,
						);
					}
				}

				if ( '' === $titulo && empty( $linhas ) ) {
					continue;
				}

				$tabelas[] = array(
					'titulo' => $titulo,
					'estilo' => $estilo,
					'linhas' => $linhas,
				);
			}
		}

		return array( 'tabelas' => $tabelas );
	}

	/**
	 * Converte formato plano (Campo/Valor com seções) em várias tabelas.
	 *
	 * @param array $raw Raw antigo.
	 * @return array
	 */
	public static function legacy_flat_to_tabelas( $raw ) {
		$linhas = isset( $raw['linhas'] ) && is_array( $raw['linhas'] ) ? $raw['linhas'] : array();
		$out    = array();
		$atual  = null;

		foreach ( $linhas as $linha ) {
			if ( ! is_array( $linha ) ) {
				continue;
			}
			$vals  = array_values( $linha );
			$campo = isset( $vals[0] ) ? trim( (string) $vals[0] ) : '';
			$valor = isset( $vals[1] ) ? trim( (string) $vals[1] ) : '';

			// Linha de seção: só o título, valor vazio.
			if ( '' !== $campo && '' === $valor ) {
				if ( null !== $atual ) {
					$out[] = $atual;
				}
				$estilo = 'azul';
				$titulo_norm = strtolower( remove_accents( $campo ) );
				if ( false !== strpos( $titulo_norm, 'tribut' ) ) {
					$estilo = 'ouro';
				}
				$atual  = array(
					'titulo' => $campo,
					'estilo' => $estilo,
					'linhas' => array(),
				);
				continue;
			}

			if ( null === $atual ) {
				$atual = array(
					'titulo' => 'Tabela',
					'estilo' => 'azul',
					'linhas' => array(),
				);
			}

			if ( '' === $campo && '' === $valor ) {
				continue;
			}

			$atual['linhas'][] = array(
				'campo' => $campo,
				'valor' => $valor,
			);
		}

		if ( null !== $atual ) {
			$out[] = $atual;
		}

		return $out;
	}
}
