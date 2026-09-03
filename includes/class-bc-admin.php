<?php
/**
 * Yönetim menüsü, ayar kaydı ve sayfa çıktıları.
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BC_Admin
 */
class BC_Admin {

	const CAPABILITY = 'manage_options';

	/**
	 * Hook'ları bağla.
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Menü öğeleri.
	 */
	public function menu() {
		add_menu_page(
			__( 'Bahri Canlı Connect', 'bahricanli-connect' ),
			__( 'Connect', 'bahricanli-connect' ),
			self::CAPABILITY,
			'bahricanli-connect',
			array( $this, 'render_inbox' ),
			'dashicons-format-chat',
			26
		);

		add_submenu_page(
			'bahricanli-connect',
			__( 'Gelen Kutusu', 'bahricanli-connect' ),
			__( 'Gelen Kutusu', 'bahricanli-connect' ),
			self::CAPABILITY,
			'bahricanli-connect',
			array( $this, 'render_inbox' )
		);

		add_submenu_page(
			'bahricanli-connect',
			__( 'Ayarlar', 'bahricanli-connect' ),
			__( 'Ayarlar', 'bahricanli-connect' ),
			self::CAPABILITY,
			'bahricanli-connect-settings',
			array( $this, 'render_settings' )
		);
	}

	/**
	 * Ayar alanlarını kaydet.
	 */
	public function register_settings() {
		register_setting(
			'bahricanli_connect',
			'bahricanli_connect_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Ayar temizleme.
	 *
	 * @param array $input Gelen değerler.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$existing = get_option( 'bahricanli_connect_settings', array() );

		$out             = array();
		$out['api_base'] = isset( $input['api_base'] ) ? esc_url_raw( trim( $input['api_base'] ) ) : '';

		// Anahtar formda hiç gösterilmez. Alan boş bırakıldıysa kayıtlı anahtar korunur.
		$submitted_key  = isset( $input['api_key'] ) ? sanitize_text_field( trim( $input['api_key'] ) ) : '';
		$out['api_key'] = '' !== $submitted_key
			? $submitted_key
			: ( isset( $existing['api_key'] ) ? (string) $existing['api_key'] : '' );

		if ( '' === $out['api_base'] ) {
			$out['api_base'] = BAHRICANLI_CONNECT_DEFAULT_API;
		}

		return $out;
	}

	/**
	 * Sayfa varlıkları (JS/CSS).
	 *
	 * @param string $hook Aktif admin sayfası.
	 */
	public function assets( $hook ) {
		if ( false === strpos( (string) $hook, 'bahricanli-connect' ) ) {
			return;
		}

		wp_enqueue_style(
			'bahricanli-connect-admin',
			BAHRICANLI_CONNECT_URL . 'assets/css/admin.css',
			array(),
			BAHRICANLI_CONNECT_VERSION
		);

		wp_enqueue_script(
			'bahricanli-connect-admin',
			BAHRICANLI_CONNECT_URL . 'assets/js/admin.js',
			array(),
			BAHRICANLI_CONNECT_VERSION,
			true
		);

		wp_localize_script(
			'bahricanli-connect-admin',
			'BahriCanliConnect',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'bahricanli_connect' ),
			)
		);
	}

	/**
	 * Gelen kutusu sayfası.
	 */
	public function render_inbox() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		if ( ! BC_Plugin::is_configured() ) {
			require BAHRICANLI_CONNECT_DIR . 'admin/views/not-configured.php';
			return;
		}

		require BAHRICANLI_CONNECT_DIR . 'admin/views/inbox.php';
	}

	/**
	 * Ayarlar sayfası.
	 */
	public function render_settings() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$settings = BC_Plugin::settings();
		require BAHRICANLI_CONNECT_DIR . 'admin/views/settings.php';
	}
}
