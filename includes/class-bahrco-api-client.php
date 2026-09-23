<?php
/**
 * Message Manager çekirdek API istemcisi (sunucu tarafı).
 *
 * Tüm çağrılar WordPress sunucusundan yapılır; tenant API anahtarı
 * hiçbir zaman tarayıcıya gönderilmez.
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BAHRCO_Api_Client
 */
class BAHRCO_Api_Client {

	/**
	 * @var string
	 */
	private $base;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @param string|null $base API kök adresi (varsayılan: kayıtlı ayar).
	 * @param string|null $key  Tenant API anahtarı (varsayılan: kayıtlı ayar).
	 */
	public function __construct( $base = null, $key = null ) {
		$settings   = BAHRCO_Plugin::settings();
		$this->base = null !== $base ? untrailingslashit( $base ) : $settings['api_base'];
		$this->key  = null !== $key ? $key : $settings['api_key'];
	}

	/**
	 * Bağlantıyı test et.
	 *
	 * @return array|WP_Error
	 */
	public function ping() {
		return $this->request( 'GET', '/api/v1/ping' );
	}

	/**
	 * Konuşmaları listele.
	 *
	 * @param array $args status, per_page.
	 * @return array|WP_Error
	 */
	public function conversations( $args = array() ) {
		return $this->request( 'GET', '/api/v1/conversations', $args );
	}

	/**
	 * Bir konuşmanın mesajları.
	 *
	 * @param int   $conversation_id Konuşma kimliği.
	 * @param array $args            per_page.
	 * @return array|WP_Error
	 */
	public function messages( $conversation_id, $args = array() ) {
		return $this->request( 'GET', '/api/v1/conversations/' . (int) $conversation_id . '/messages', $args );
	}

	/**
	 * Serbest metin mesajı gönder (WhatsApp veya SMS konuşması).
	 *
	 * @param int    $conversation_id Konuşma kimliği.
	 * @param string $body            Mesaj gövdesi.
	 * @param string $sender_header   Yalnız SMS: kısa ad (boşsa hesabın varsayılanı).
	 * @return array|WP_Error
	 */
	public function send_message( $conversation_id, $body, $sender_header = '' ) {
		$data = array( 'body' => $body );

		if ( '' !== $sender_header ) {
			$data['sender_header'] = $sender_header;
		}

		return $this->request(
			'POST',
			'/api/v1/conversations/' . (int) $conversation_id . '/messages',
			$data
		);
	}

	/**
	 * Onaylı mesaj şablonları.
	 *
	 * @return array|WP_Error
	 */
	public function templates() {
		return $this->request( 'GET', '/api/v1/templates', array( 'status' => 'approved' ) );
	}

	/**
	 * Seçili konuşmaya şablon gönder.
	 *
	 * @param int   $conversation_id Konuşma kimliği.
	 * @param int   $template_id     Şablon kimliği.
	 * @param array $params          Sıralı gövde değişkenleri.
	 * @return array|WP_Error
	 */
	public function send_template( $conversation_id, $template_id, $params ) {
		return $this->request(
			'POST',
			'/api/v1/conversations/' . (int) $conversation_id . '/templates',
			array( 'template_id' => (int) $template_id, 'params' => array_values( $params ) )
		);
	}

	/**
	 * Konuşmayı arşivle ya da arşivden çıkar.
	 *
	 * @param int  $conversation_id Konuşma kimliği.
	 * @param bool $archived        true: arşivle, false: arşivden çıkar.
	 * @return array|WP_Error
	 */
	public function set_archived( $conversation_id, $archived ) {
		return $this->request(
			'POST',
			'/api/v1/conversations/' . (int) $conversation_id . ( $archived ? '/archive' : '/unarchive' )
		);
	}

	/**
	 * Telefon numarasına onaylı şablonla yeni mesaj gönder.
	 *
	 * @param string $to       Alıcı telefon numarası.
	 * @param string $template Şablon adı.
	 * @param string $language Şablon dili.
	 * @param array  $params   Sıralı gövde değişkenleri.
	 * @return array|WP_Error
	 */
	public function send_template_to( $to, $template, $language, $params ) {
		return $this->request(
			'POST',
			'/api/v1/messages',
			array(
				'to'       => $to,
				'template' => $template,
				'language' => $language,
				'params'   => array_values( $params ),
			)
		);
	}

	/**
	 * SMS hesapları ve onaylı kısa adları.
	 *
	 * @return array|WP_Error
	 */
	public function sms_accounts() {
		return $this->request( 'GET', '/api/v1/sms/accounts' );
	}

	/**
	 * Telefon numarasına yeni SMS gönder.
	 *
	 * @param string $to             Alıcı telefon numarası.
	 * @param string $body           Mesaj metni.
	 * @param string $sender_header  Kısa ad (boşsa hesabın varsayılanı).
	 * @param int    $sms_account_id SMS hesabı (0 ise ilk hesap).
	 * @return array|WP_Error
	 */
	public function send_sms( $to, $body, $sender_header = '', $sms_account_id = 0 ) {
		$data = array(
			'to'   => $to,
			'body' => $body,
		);

		if ( '' !== $sender_header ) {
			$data['sender_header'] = $sender_header;
		}
		if ( $sms_account_id > 0 ) {
			$data['sms_account_id'] = (int) $sms_account_id;
		}

		return $this->request( 'POST', '/api/v1/sms/messages', $data );
	}

	/**
	 * Ham HTTP isteği.
	 *
	 * @param string $method GET|POST.
	 * @param string $path   /api/... yolu.
	 * @param array  $data   GET için query, POST için gövde.
	 * @return array|WP_Error Çözümlenmiş gövde dizisi ya da WP_Error.
	 */
	private function request( $method, $path, $data = array() ) {
		if ( '' === $this->key ) {
			return new WP_Error( 'bahrco_not_configured', __( 'API anahtarı ayarlanmamış.', 'bahricanli-connect' ) );
		}

		$url  = $this->base . $path;
		$args = array(
			'method'  => $method,
			'timeout' => 20,
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->key,
				'Accept'        => 'application/json',
			),
		);

		if ( 'GET' === $method ) {
			if ( ! empty( $data ) ) {
				$url = add_query_arg( array_map( 'rawurlencode', $data ), $url );
			}
		} else {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = wp_json_encode( $data );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 200 && $code < 300 ) {
			return is_array( $body ) ? $body : array();
		}

		$message = is_array( $body ) && ! empty( $body['message'] )
			? $body['message']
			: sprintf( /* translators: %d: HTTP status code */ __( 'API hatası (HTTP %d)', 'bahricanli-connect' ), $code );

		return new WP_Error( 'bahrco_api_error', $message, array( 'status' => $code, 'body' => $body ) );
	}
}
