<?php
/**
 * Yeni gelen mesaj e-posta bildirimi (WP-Cron).
 *
 * Belirli aralıklarla konuşma listesini çeker; son kontrolden sonra
 * mesaj gelen konuşmalar için tek bir özet e-posta gönderir.
 * Mesaj içeriği e-postaya konmaz, WordPress'te saklanmaz.
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BAHRCO_Notifier
 */
class BAHRCO_Notifier {

	const HOOK          = 'bahrco_check_new_messages';
	const SCHEDULE      = 'bahrco_five_minutes';
	const CURSOR_OPTION = 'bahricanli_connect_notify_cursor';
	const LOCK          = 'bahrco_notify_lock';

	/**
	 * Hook'ları bağla.
	 */
	public function register() {
		add_filter( 'cron_schedules', array( $this, 'schedules' ) ); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval -- 5 dakika bilinçli seçim.
		add_action( self::HOOK, array( $this, 'run' ) );
		add_action( 'init', array( $this, 'sync_schedule' ) );
	}

	/**
	 * 5 dakikalık cron aralığı.
	 *
	 * @param array $schedules Mevcut aralıklar.
	 * @return array
	 */
	public function schedules( $schedules ) {
		$schedules[ self::SCHEDULE ] = array(
			'interval' => 5 * MINUTE_IN_SECONDS,
			'display'  => __( 'Her 5 dakikada bir (Bahri Canlı Connect)', 'bahricanli-connect' ),
		);

		return $schedules;
	}

	/**
	 * Bildirim açık mı?
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$settings = BAHRCO_Plugin::settings();

		return $settings['notify_enabled'] && BAHRCO_Plugin::is_configured();
	}

	/**
	 * Zamanlanmış görevi ayarla ya da kaldır (ayara göre).
	 * Eklenti güncellemelerinde etkinleştirme hook'u çalışmadığı için her istekte denetlenir.
	 */
	public function sync_schedule() {
		$scheduled = wp_next_scheduled( self::HOOK );

		if ( self::is_enabled() ) {
			if ( ! $scheduled ) {
				wp_schedule_event( time() + MINUTE_IN_SECONDS, self::SCHEDULE, self::HOOK );
			}
			return;
		}

		if ( $scheduled ) {
			self::unschedule();
		}
	}

	/**
	 * Zamanlanmış görevi ve imleci temizle.
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::HOOK );
		delete_option( self::CURSOR_OPTION );
	}

	/**
	 * Bildirim alıcıları.
	 *
	 * @return string[]
	 */
	public static function recipients() {
		$settings = BAHRCO_Plugin::settings();
		$emails   = array_filter( array_map( 'sanitize_email', explode( ',', $settings['notify_email'] ) ) );

		if ( empty( $emails ) ) {
			$emails = array( get_option( 'admin_email' ) );
		}

		return array_values( array_unique( $emails ) );
	}

	/**
	 * Cron görevi: yeni gelen mesajları denetle, varsa e-posta gönder.
	 */
	public function run() {
		if ( ! self::is_enabled() || get_transient( self::LOCK ) ) {
			return;
		}

		set_transient( self::LOCK, 1, 2 * MINUTE_IN_SECONDS );

		$client = new BAHRCO_Api_Client();
		$result = $client->conversations( array( 'per_page' => 50 ) );

		if ( is_wp_error( $result ) || empty( $result['data'] ) || ! is_array( $result['data'] ) ) {
			delete_transient( self::LOCK );
			return;
		}

		$cursor = (int) get_option( self::CURSOR_OPTION, 0 );
		$latest = $cursor;
		$fresh  = array();

		foreach ( $result['data'] as $conversation ) {
			$inbound_at = ! empty( $conversation['last_inbound_at'] ) ? (int) strtotime( $conversation['last_inbound_at'] ) : 0;

			if ( $inbound_at <= $cursor ) {
				continue;
			}

			$latest  = max( $latest, $inbound_at );
			$fresh[] = $conversation;
		}

		// İlk çalıştırma: eski mesajlar için e-posta atma, yalnız imleci başlat.
		if ( 0 === $cursor ) {
			update_option( self::CURSOR_OPTION, $latest > 0 ? $latest : time(), false );
			delete_transient( self::LOCK );
			return;
		}

		if ( ! empty( $fresh ) && $this->send( $fresh ) ) {
			update_option( self::CURSOR_OPTION, $latest, false );
		}

		delete_transient( self::LOCK );
	}

	/**
	 * Özet e-postayı gönder.
	 *
	 * @param array $conversations Yeni mesaj gelen konuşmalar.
	 * @return bool
	 */
	private function send( $conversations ) {
		$site  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$count = count( $conversations );

		$subject = sprintf(
			/* translators: 1: site name, 2: number of conversations */
			__( '[%1$s] %2$d konuşmada yeni mesaj', 'bahricanli-connect' ),
			$site,
			$count
		);

		$lines = array( __( 'Yeni mesaj gelen konuşmalar:', 'bahricanli-connect' ), '' );

		foreach ( $conversations as $conversation ) {
			$lines[] = '- ' . $this->describe( $conversation );
		}

		$lines[] = '';
		$lines[] = __( 'Okumak ve yanıtlamak için:', 'bahricanli-connect' );
		$lines[] = admin_url( 'admin.php?page=bahricanli-connect' );

		return (bool) wp_mail( self::recipients(), $subject, implode( "\n", $lines ) );
	}

	/**
	 * E-postadaki tek satırlık konuşma özeti.
	 *
	 * @param array $conversation Konuşma kaydı.
	 * @return string
	 */
	private function describe( $conversation ) {
		$contact = isset( $conversation['contact'] ) && is_array( $conversation['contact'] ) ? $conversation['contact'] : array();
		$number  = isset( $contact['wa_id'] ) ? (string) $contact['wa_id'] : '';
		$name    = '';

		foreach ( array( 'name', 'profile_name' ) as $field ) {
			if ( ! empty( $contact[ $field ] ) ) {
				$name = (string) $contact[ $field ];
				break;
			}
		}

		$who     = '' !== $name && '' !== $number ? $name . ' (' . $number . ')' : ( '' !== $name ? $name : ( '' !== $number ? $number : '—' ) );
		$channel = isset( $conversation['channel'] ) && 'sms' === $conversation['channel'] ? 'SMS' : 'WhatsApp';
		$time    = wp_date( get_option( 'time_format' ), (int) strtotime( $conversation['last_inbound_at'] ) );
		$unread  = isset( $conversation['unread_count'] ) ? (int) $conversation['unread_count'] : 0;

		$line = sprintf( '%s — %s, %s', $who, $channel, $time );

		if ( $unread > 0 ) {
			/* translators: %d: unread message count */
			$line .= ' ' . sprintf( __( '(%d okunmamış)', 'bahricanli-connect' ), $unread );
		}

		return $line;
	}
}
