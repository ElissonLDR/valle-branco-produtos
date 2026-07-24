<?php
/**
 * Contact Form 7 — máscara e validação (telefone/WhatsApp + e-mail).
 *
 * @package ValleBrancoProdutos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe VB_Prod_CF7
 */
class VB_Prod_CF7 {

	/**
	 * Hooks.
	 */
	public function hooks() {
		if ( ! class_exists( 'WPCF7' ) ) {
			return;
		}

		add_action( 'wpcf7_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'wpcf7_validate_tel', array( $this, 'validate_tel' ), 20, 2 );
		add_filter( 'wpcf7_validate_tel*', array( $this, 'validate_tel' ), 20, 2 );
		add_filter( 'wpcf7_validate_email', array( $this, 'validate_email' ), 20, 2 );
		add_filter( 'wpcf7_validate_email*', array( $this, 'validate_email' ), 20, 2 );
	}

	/**
	 * Scripts do formulário (só quando o CF7 carrega JS).
	 */
	public function enqueue() {
		wp_register_script(
			'vb-prod-cf7',
			VB_PROD_URL . 'public/js/cf7-form.js',
			array(),
			VB_PROD_VERSION,
			true
		);

		wp_localize_script(
			'vb-prod-cf7',
			'vbCf7Form',
			array(
				'phoneInvalid' => __( 'Informe um telefone ou WhatsApp válido com DDD, ex.: (14) 99999-9999 ou (14) 3456-7890.', 'valle-branco-produtos' ),
				'emailInvalid' => __( 'Informe um e-mail válido (pessoal ou profissional).', 'valle-branco-produtos' ),
			)
		);

		wp_enqueue_script( 'vb-prod-cf7' );
	}

	/**
	 * Valida telefone fixo (10) ou celular/WhatsApp (11).
	 *
	 * @param WPCF7_Validation $result Resultado.
	 * @param WPCF7_FormTag    $tag    Tag.
	 * @return WPCF7_Validation
	 */
	public function validate_tel( $result, $tag ) {
		$name  = $tag->name;
		$value = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = is_string( $value ) ? trim( $value ) : '';

		if ( '' === $value && $tag->is_required() ) {
			return $result;
		}

		if ( '' === $value ) {
			return $result;
		}

		if ( ! self::is_valid_br_phone( $value ) ) {
			$result->invalidate(
				$tag,
				__( 'Informe um telefone ou WhatsApp válido com DDD, ex.: (14) 99999-9999 ou (14) 3456-7890.', 'valle-branco-produtos' )
			);
		}

		return $result;
	}

	/**
	 * Valida e-mail pessoal ou profissional.
	 *
	 * @param WPCF7_Validation $result Resultado.
	 * @param WPCF7_FormTag    $tag    Tag.
	 * @return WPCF7_Validation
	 */
	public function validate_email( $result, $tag ) {
		$name  = $tag->name;
		$value = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = is_string( $value ) ? trim( $value ) : '';

		if ( '' === $value ) {
			return $result;
		}

		if ( ! self::is_valid_email( $value ) ) {
			$result->invalidate(
				$tag,
				__( 'Informe um e-mail válido (pessoal ou profissional).', 'valle-branco-produtos' )
			);
		}

		return $result;
	}

	/**
	 * Telefone BR: 10 (fixo) ou 11 (celular/WhatsApp) dígitos, DDD 11–99.
	 *
	 * @param string $value Valor.
	 * @return bool
	 */
	public static function is_valid_br_phone( $value ) {
		$digits = preg_replace( '/\D+/', '', $value );
		$len    = strlen( $digits );

		if ( 10 !== $len && 11 !== $len ) {
			return false;
		}

		$ddd = (int) substr( $digits, 0, 2 );
		if ( $ddd < 11 || $ddd > 99 ) {
			return false;
		}

		// Rejeita sequências inválidas (0000… / 1111…).
		if ( preg_match( '/^(\d)\1+$/', $digits ) ) {
			return false;
		}

		$numero = substr( $digits, 2 );
		if ( '' === $numero || preg_match( '/^0+$/', $numero ) ) {
			return false;
		}

		return true;
	}

	/**
	 * E-mail (Gmail, Outlook, domínio corporativo, etc.).
	 *
	 * @param string $value Valor.
	 * @return bool
	 */
	public static function is_valid_email( $value ) {
		$value = trim( $value );
		if ( '' === $value || false !== strpos( $value, ' ' ) ) {
			return false;
		}

		if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			return false;
		}

		// Exige domínio com TLD de pelo menos 2 caracteres.
		$parts = explode( '@', $value );
		if ( 2 !== count( $parts ) ) {
			return false;
		}

		$domain = strtolower( $parts[1] );
		if ( ! preg_match( '/^[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?)+$/i', $domain ) ) {
			return false;
		}

		$tld = substr( strrchr( $domain, '.' ), 1 );
		return is_string( $tld ) && strlen( $tld ) >= 2;
	}
}
