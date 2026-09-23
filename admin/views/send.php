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

	<p><?php esc_html_e( 'Herhangi bir numaraya onaylı WhatsApp şablonuyla ya da SMS ile yeni mesaj başlatın. Yanıt geldiğinde konuşma Gelen Kutusu\'nda görünür.', 'bahricanli-connect' ); ?></p>

	<div class="bc-send" id="bc-send">
		<fieldset class="bc-send__channel">
			<legend><?php esc_html_e( 'Kanal', 'bahricanli-connect' ); ?></legend>
			<label><input type="radio" name="bc-send-channel" value="whatsapp" checked /> <?php esc_html_e( 'WhatsApp (onaylı şablon)', 'bahricanli-connect' ); ?></label>
			<label><input type="radio" name="bc-send-channel" value="sms" /> <?php esc_html_e( 'SMS', 'bahricanli-connect' ); ?></label>
		</fieldset>

		<label for="bc-send-to"><?php esc_html_e( 'Telefon numarası', 'bahricanli-connect' ); ?></label>
		<input type="tel" id="bc-send-to" class="regular-text" placeholder="905551112233" autocomplete="off" required />
		<p class="description"><?php esc_html_e( 'Ülke koduyla birlikte, örn. 90 555 111 22 33 (SMS için 0555 111 22 33 de olur).', 'bahricanli-connect' ); ?></p>

		<div id="bc-send-template"><?php esc_html_e( 'Şablonlar yükleniyor…', 'bahricanli-connect' ); ?></div>
		<div id="bc-send-sms" hidden></div>
	</div>
</div>
