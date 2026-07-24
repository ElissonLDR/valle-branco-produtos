<?php
/**
 * Configurações globais (CTAs e páginas).
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_Settings
 */
class VB_Prod_Settings {

	const OPTION = 'vb_prod_settings';

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'menu' ), 20 );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Defaults (só usados na 1ª instalação; campos vazios não voltam ao default).
	 */
	public static function ensure_defaults() {
		if ( false === get_option( self::OPTION, false ) ) {
			update_option( self::OPTION, self::defaults() );
		}
	}

	/**
	 * Valores padrão.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'url_revendedor'     => '/contato',
			'url_onde_encontrar' => '/onde-encontrar',
			'texto_revendedor'   => 'Falar com revendedor',
			'texto_onde'         => 'Onde encontrar',
			'texto_ver_mais'     => 'Ver mais',
		);
	}

	/**
	 * Chaves de URL (podem ficar vazias).
	 *
	 * @return string[]
	 */
	private static function url_keys() {
		return array( 'url_revendedor', 'url_onde_encontrar' );
	}

	/**
	 * Lê settings. URLs vazias permanecem vazias (não repõem default).
	 *
	 * @return array
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		// Remove chave antiga (lista de produtos) se ainda existir no banco.
		if ( isset( $saved['url_lista_produtos'] ) ) {
			unset( $saved['url_lista_produtos'] );
			update_option( self::OPTION, $saved );
		}

		$defaults = self::defaults();
		$out      = $defaults;

		foreach ( $defaults as $key => $default ) {
			if ( ! array_key_exists( $key, $saved ) ) {
				continue;
			}
			// URL vazia = removida de propósito.
			if ( in_array( $key, self::url_keys(), true ) ) {
				$out[ $key ] = (string) $saved[ $key ];
				continue;
			}
			$out[ $key ] = '' !== (string) $saved[ $key ] ? (string) $saved[ $key ] : $default;
		}

		return $out;
	}

	/**
	 * Uma chave.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	public static function get_value( $key ) {
		$all = self::get();
		return isset( $all[ $key ] ) ? (string) $all[ $key ] : '';
	}

	/**
	 * Submenu em Produtos.
	 */
	public function menu() {
		add_submenu_page(
			'edit.php?post_type=' . VB_Prod_CPT::POST_TYPE,
			__( 'Configurações de produtos', 'valle-branco-produtos' ),
			__( 'Configurações', 'valle-branco-produtos' ),
			'manage_options',
			'vb-prod-settings',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register setting.
	 */
	public function register() {
		register_setting(
			'vb_prod_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitiza opções. URL em branco = salva vazia (sem default).
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function sanitize( $input ) {
		$defaults = self::defaults();
		$out      = $defaults;

		if ( ! is_array( $input ) ) {
			return $out;
		}

		foreach ( array_keys( $defaults ) as $key ) {
			if ( ! isset( $input[ $key ] ) ) {
				continue;
			}
			$val = sanitize_text_field( wp_unslash( $input[ $key ] ) );

			if ( in_array( $key, self::url_keys(), true ) ) {
				if ( '' === $val ) {
					$out[ $key ] = '';
					continue;
				}
				$out[ $key ] = esc_url_raw( $val );
				if ( '' === $out[ $key ] && isset( $val[0] ) && '/' === $val[0] ) {
					$out[ $key ] = $val;
				}
			} else {
				$out[ $key ] = $val;
			}
		}

		return $out;
	}

	/**
	 * Página de settings.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = self::get();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Configurações — Produtos', 'valle-branco-produtos' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Deixe a URL em branco para desativar o link correspondente. A página /produtos do site (Elementor) não é alterada por este plugin.', 'valle-branco-produtos' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'vb_prod_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="vb_url_revendedor"><?php esc_html_e( 'URL — Falar com revendedor', 'valle-branco-produtos' ); ?></label></th>
						<td><input name="<?php echo esc_attr( self::OPTION ); ?>[url_revendedor]" id="vb_url_revendedor" type="text" class="regular-text" value="<?php echo esc_attr( $s['url_revendedor'] ); ?>" placeholder="<?php esc_attr_e( 'Vazio = sem link', 'valle-branco-produtos' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vb_url_onde"><?php esc_html_e( 'URL — Onde encontrar', 'valle-branco-produtos' ); ?></label></th>
						<td><input name="<?php echo esc_attr( self::OPTION ); ?>[url_onde_encontrar]" id="vb_url_onde" type="text" class="regular-text" value="<?php echo esc_attr( $s['url_onde_encontrar'] ); ?>" placeholder="<?php esc_attr_e( 'Vazio = sem link', 'valle-branco-produtos' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vb_txt_rev"><?php esc_html_e( 'Texto botão revendedor', 'valle-branco-produtos' ); ?></label></th>
						<td><input name="<?php echo esc_attr( self::OPTION ); ?>[texto_revendedor]" id="vb_txt_rev" type="text" class="regular-text" value="<?php echo esc_attr( $s['texto_revendedor'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vb_txt_onde"><?php esc_html_e( 'Texto botão onde encontrar', 'valle-branco-produtos' ); ?></label></th>
						<td><input name="<?php echo esc_attr( self::OPTION ); ?>[texto_onde]" id="vb_txt_onde" type="text" class="regular-text" value="<?php echo esc_attr( $s['texto_onde'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vb_txt_mais"><?php esc_html_e( 'Texto botão ver mais', 'valle-branco-produtos' ); ?></label></th>
						<td><input name="<?php echo esc_attr( self::OPTION ); ?>[texto_ver_mais]" id="vb_txt_mais" type="text" class="regular-text" value="<?php echo esc_attr( $s['texto_ver_mais'] ); ?>" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Avisos de sucesso / erro ao salvar.
	 */
	public function admin_notices() {
		if ( ! isset( $_GET['page'] ) || 'vb-prod-settings' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-success is-dismissible"><p>';
			esc_html_e( 'Configurações salvas com sucesso.', 'valle-branco-produtos' );
			echo '</p></div>';
			return;
		}

		if ( isset( $_GET['settings-updated'] ) && 'false' === $_GET['settings-updated'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-error is-dismissible"><p>';
			esc_html_e( 'Não foi possível salvar as configurações. Tente novamente.', 'valle-branco-produtos' );
			echo '</p></div>';
		}
	}

	/**
	 * Resolve URL (relativa → home). Vazio retorna string vazia.
	 *
	 * @param string $path Path or URL.
	 * @return string
	 */
	public static function resolve_url( $path ) {
		$path = trim( (string) $path );
		if ( '' === $path ) {
			return '';
		}
		if ( preg_match( '#^https?://#i', $path ) ) {
			return esc_url( $path );
		}
		return esc_url( home_url( $path ) );
	}
}
