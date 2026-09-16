<?php
/**
 * Onaylı şablonla yeni mesaj gönderme görünümü — JS ile doldurulur (admin-ajax proxy).
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap bc-wrap">
	<h1><?php esc_html_e( 'Mesaj Gönder', 'bahricanli-connect' ); ?></h1>

	<p><?php esc_html_e( 'Onaylı bir WhatsApp şablonuyla herhangi bir numaraya yeni mesaj başlatın. Yanıt geldiğinde konuşma Gelen Kutusu\'nda görünür.', 'bahricanli-connect' ); ?></p>

	<div class="bc-send" id="bc-send">
		<label for="bc-send-to"><?php esc_html_e( 'Telefon numarası', 'bahricanli-connect' ); ?></label>
		<input type="tel" id="bc-send-to" class="regular-text" placeholder="905551112233" autocomplete="off" required />
		<p class="description"><?php esc_html_e( 'Ülke koduyla birlikte, örn. 90 555 111 22 33.', 'bahricanli-connect' ); ?></p>

		<div id="bc-send-template"><?php esc_html_e( 'Şablonlar yükleniyor…', 'bahricanli-connect' ); ?></div>
	</div>
</div>
