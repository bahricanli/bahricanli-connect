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
 * Class BC_Ajax
 */
class BC_Ajax {

	/**
	 * Hook'ları bağla.
	 */
	public function register() {
		add_action( 'wp_ajax_bc_test_connection', array( $this, 'test_connection' ) );
		add_action( 'wp_ajax_bc_conversations', array( $this, 'conversations' ) );
		add_action( 'wp_ajax_bc_messages', array( $this, 'messages' ) );
		add_action( 'wp_ajax_bc_send_message', array( $this, 'send_message' ) );
	}

	/**
	 * Ortak ön kontrol: nonce + yetki.
	 */
	private function guard() {
		if ( ! current_user_can( BC_Admin::CAPABILITY ) ) {
			wp_send_json_error( array( 'message' => __( 'Yetkisiz.', 'bahricanli-connect' ) ), 403 );
		}

		check_ajax_referer( 'bahricanli_connect', 'nonce' );
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

		$base = isset( $_POST['api_base'] ) ? esc_url_raw( wp_unslash( $_POST['api_base'] ) ) : null;
		$key  = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : null;

		$client = new BC_Api_Client( $base, $key );
		$this->respond( $client->ping() );
	}

	/**
	 * Konuşma listesi.
	 */
	public function conversations() {
		$this->guard();

		$args = array(
			'status'   => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'open',
			'per_page' => isset( $_POST['per_page'] ) ? (int) $_POST['per_page'] : 50,
		);

		$this->respond( ( new BC_Api_Client() )->conversations( $args ) );
	}

	/**
	 * Bir konuşmanın mesajları.
	 */
	public function messages() {
		$this->guard();

		$id = isset( $_POST['conversation_id'] ) ? (int) $_POST['conversation_id'] : 0;

		if ( $id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Geçersiz konuşma.', 'bahricanli-connect' ) ), 400 );
		}

		$this->respond( ( new BC_Api_Client() )->messages( $id, array( 'per_page' => 200 ) ) );
	}

	/**
	 * Mesaj gönder.
	 */
	public function send_message() {
		$this->guard();

		$id   = isset( $_POST['conversation_id'] ) ? (int) $_POST['conversation_id'] : 0;
		$body = isset( $_POST['body'] ) ? sanitize_textarea_field( wp_unslash( $_POST['body'] ) ) : '';

		if ( $id <= 0 || '' === trim( $body ) ) {
			wp_send_json_error( array( 'message' => __( 'Konuşma ve mesaj gövdesi gerekli.', 'bahricanli-connect' ) ), 400 );
		}

		$this->respond( ( new BC_Api_Client() )->send_message( $id, $body ) );
	}
}
