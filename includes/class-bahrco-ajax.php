<?php
/**
 * admin-ajax proxy uçları. Her istek nonce + yetki doğrular,
 * çekirdek API'ye sunucu tarafında iletir.
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BAHRCO_Ajax
 */
class BAHRCO_Ajax {

	/**
	 * Hook'ları bağla.
	 */
	public function register() {
		add_action( 'wp_ajax_bahrco_test_connection', array( $this, 'test_connection' ) );
		add_action( 'wp_ajax_bahrco_conversations', array( $this, 'conversations' ) );
		add_action( 'wp_ajax_bahrco_messages', array( $this, 'messages' ) );
		add_action( 'wp_ajax_bahrco_send_message', array( $this, 'send_message' ) );
		add_action( 'wp_ajax_bahrco_templates', array( $this, 'templates' ) );
		add_action( 'wp_ajax_bahrco_send_template', array( $this, 'send_template' ) );
		add_action( 'wp_ajax_bahrco_archive', array( $this, 'archive' ) );
		add_action( 'wp_ajax_bahrco_send_new_template', array( $this, 'send_new_template' ) );
	}

	/**
	 * Ortak ön kontrol: önce nonce, sonra yetki.
	 * İkisi de başarısızlıkta isteği sonlandırır.
	 */
	private function guard() {
		check_ajax_referer( 'bahricanli_connect', 'nonce' );

		if ( ! current_user_can( BAHRCO_Admin::CAPABILITY ) ) {
			wp_send_json_error( array( 'message' => __( 'Yetkisiz.', 'bahricanli-connect' ) ), 403 );
		}
	}

	/**
	 * WP_Error → JSON hata; dizi → JSON başarı.
	 *
	 * @param array|WP_Error $result API sonucu.
	 */
	private function respond( $result ) {
		if ( is_wp_error( $result ) ) {
			$data   = $result->get_error_data();
			$status = ( is_array( $data ) && ! empty( $data['status'] ) ) ? (int) $data['status'] : 502;
			wp_send_json_error( array( 'message' => $result->get_error_message() ), $status );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Bağlantı testi — kaydedilmemiş değerlerle de çalışır.
	 */
	public function test_connection() {
		$this->guard();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
		$raw_base = isset( $_POST['api_base'] ) ? esc_url_raw( wp_unslash( $_POST['api_base'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
		$raw_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';

		// Alan boşsa kayıtlı değeri kullan (null geç).
		$base = '' !== $raw_base ? $raw_base : null;
		$key  = '' !== $raw_key ? $raw_key : null;

		$client = new BAHRCO_Api_Client( $base, $key );
		$this->respond( $client->ping() );
	}

	/**
	 * Konuşma listesi.
	 */
	public function conversations() {
		$this->guard();

		$args = array(
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
			'status'   => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'open',
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
			'per_page' => isset( $_POST['per_page'] ) ? absint( wp_unslash( $_POST['per_page'] ) ) : 50,
		);

		$this->respond( ( new BAHRCO_Api_Client() )->conversations( $args ) );
	}

	/**
	 * Bir konuşmanın mesajları.
	 */
	public function messages() {
		$this->guard();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
		$id = isset( $_POST['conversation_id'] ) ? absint( wp_unslash( $_POST['conversation_id'] ) ) : 0;

		if ( $id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Geçersiz konuşma.', 'bahricanli-connect' ) ), 400 );
		}

		$this->respond( ( new BAHRCO_Api_Client() )->messages( $id, array( 'per_page' => 200 ) ) );
	}

	/**
	 * Mesaj gönder.
	 */
	public function send_message() {
		$this->guard();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
		$id = isset( $_POST['conversation_id'] ) ? absint( wp_unslash( $_POST['conversation_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
		$body = isset( $_POST['body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['body'] ) ) : '';

		if ( $id <= 0 || '' === trim( $body ) ) {
			wp_send_json_error( array( 'message' => __( 'Konuşma ve mesaj gövdesi gerekli.', 'bahricanli-connect' ) ), 400 );
		}

		$this->respond( ( new BAHRCO_Api_Client() )->send_message( $id, $body ) );
	}

	/**
	 * Onaylı şablonları listele.
	 */
	public function templates() {
		$this->guard();
		$this->respond( ( new BAHRCO_Api_Client() )->templates() );
	}

	/**
	 * POST 'params' alanındaki JSON değişken listesini doğrula ve temizle.
	 * Geçersizse isteği sonlandırır.
	 *
	 * @param string $missing_message Liste eksik/bozuksa gösterilecek mesaj.
	 * @return string[]
	 */
	private function template_params( $missing_message ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce guard() içinde doğrulanır; JSON aşağıda çözülüp her öğe temizlenir.
		$raw_params = isset( $_POST['params'] ) && is_string( $_POST['params'] ) ? wp_unslash( $_POST['params'] ) : '';
		$params     = json_decode( $raw_params, true );

		if ( ! is_array( $params ) || '[' !== substr( ltrim( $raw_params ), 0, 1 ) ) {
			wp_send_json_error( array( 'message' => $missing_message ), 400 );
		}
		foreach ( $params as $param ) {
			if ( ! is_string( $param ) ) {
				wp_send_json_error( array( 'message' => __( 'Değişkenler metin olmalıdır.', 'bahricanli-connect' ) ), 400 );
			}
		}

		return array_map( 'sanitize_textarea_field', $params );
	}

	/**
	 * Seçili konuşmaya şablon gönder.
	 */
	public function send_template() {
		$this->guard();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
		$id          = isset( $_POST['conversation_id'] ) ? absint( wp_unslash( $_POST['conversation_id'] ) ) : 0;
		$template_id = isset( $_POST['template_id'] ) ? absint( wp_unslash( $_POST['template_id'] ) ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$missing = __( 'Konuşma, şablon ve değişken listesi gerekli.', 'bahricanli-connect' );

		if ( $id <= 0 || $template_id <= 0 ) {
			wp_send_json_error( array( 'message' => $missing ), 400 );
		}
		$params = $this->template_params( $missing );

		$this->respond( ( new BAHRCO_Api_Client() )->send_template( $id, $template_id, $params ) );
	}

	/**
	 * Konuşmayı arşivle / arşivden çıkar.
	 */
	public function archive() {
		$this->guard();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
		$id       = isset( $_POST['conversation_id'] ) ? absint( wp_unslash( $_POST['conversation_id'] ) ) : 0;
		$archived = isset( $_POST['archived'] ) && '1' === sanitize_key( wp_unslash( $_POST['archived'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( $id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Geçersiz konuşma.', 'bahricanli-connect' ) ), 400 );
		}

		$this->respond( ( new BAHRCO_Api_Client() )->set_archived( $id, $archived ) );
	}

	/**
	 * Telefon numarasına onaylı şablonla yeni mesaj gönder.
	 */
	public function send_new_template() {
		$this->guard();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce, self::guard() içinde doğrulanır.
		$to       = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : '';
		$template = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : '';
		$language = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$missing = __( 'Telefon numarası, şablon ve değişken listesi gerekli.', 'bahricanli-connect' );

		if ( '' === trim( $to ) || '' === $template ) {
			wp_send_json_error( array( 'message' => $missing ), 400 );
		}
		$params = $this->template_params( $missing );

		$this->respond( ( new BAHRCO_Api_Client() )->send_template_to( $to, $template, $language, $params ) );
	}
}
