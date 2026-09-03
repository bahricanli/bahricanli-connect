<?php
/**
 * Ana eklenti sınıfı — hook kaydı ve alt bileşenlerin bağlanması.
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BC_Plugin
 */
final class BC_Plugin {

	/**
	 * Tekil örnek.
	 *
	 * @var BC_Plugin|null
	 */
	private static $instance = null;

	/**
	 * @var BC_Admin
	 */
	public $admin;

	/**
	 * @var BC_Ajax
	 */
	public $ajax;

	/**
	 * Tekil erişim.
	 *
	 * @return BC_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Kurucu — bileşenleri oluşturur ve hook'ları bağlar.
	 */
	private function __construct() {
		$this->admin = new BC_Admin();
		$this->ajax  = new BC_Ajax();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( BAHRICANLI_CONNECT_FILE ), array( $this, 'action_links' ) );

		$this->admin->register();
		$this->ajax->register();
	}

	/**
	 * Çeviri dosyalarını yükle.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'bahricanli-connect', false, dirname( plugin_basename( BAHRICANLI_CONNECT_FILE ) ) . '/languages' );
	}

	/**
	 * Eklenti listesi satırına "Ayarlar" bağlantısı ekle.
	 *
	 * @param array $links Mevcut bağlantılar.
	 * @return array
	 */
	public function action_links( $links ) {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=bahricanli-connect-settings' ) ),
			esc_html__( 'Ayarlar', 'bahricanli-connect' )
		);

		array_unshift( $links, $settings );

		return $links;
	}

	/**
	 * Kayıtlı ayarlar.
	 *
	 * @return array{api_base:string, api_key:string}
	 */
	public static function settings() {
		$opts = get_option( 'bahricanli_connect_settings', array() );

		return array(
			'api_base' => ! empty( $opts['api_base'] ) ? untrailingslashit( $opts['api_base'] ) : BAHRICANLI_CONNECT_DEFAULT_API,
			'api_key'  => isset( $opts['api_key'] ) ? (string) $opts['api_key'] : '',
		);
	}

	/**
	 * Bağlantı yapılandırılmış mı?
	 *
	 * @return bool
	 */
	public static function is_configured() {
		$s = self::settings();

		return '' !== $s['api_key'];
	}
}
