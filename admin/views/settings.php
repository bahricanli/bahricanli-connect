<?php
/**
 * Ayarlar sayfası görünümü.
 *
 * @package BahriCanliConnect
 * @var array $settings api_base, api_key, notify_enabled, notify_email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap bc-wrap">
	<h1><?php esc_html_e( 'Bahri Canlı Connect — Ayarlar', 'bahricanli-connect' ); ?></h1>

	<p class="description">
		<?php esc_html_e( 'Message Manager panelinden aldığınız tenant API anahtarını girin. Anahtar yalnızca bu sunucuda saklanır, tarayıcıya gönderilmez.', 'bahricanli-connect' ); ?>
	</p>

	<form method="post" action="options.php">
		<?php settings_fields( 'bahricanli_connect' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="bahrco_api_base"><?php esc_html_e( 'API adresi', 'bahricanli-connect' ); ?></label>
				</th>
				<td>
					<input
						type="url"
						id="bahrco_api_base"
						name="bahricanli_connect_settings[api_base]"
						class="regular-text"
						value="<?php echo esc_attr( $settings['api_base'] ); ?>"
						placeholder="<?php echo esc_attr( BAHRICANLI_CONNECT_DEFAULT_API ); ?>"
					/>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bahrco_api_key"><?php esc_html_e( 'API anahtarı', 'bahricanli-connect' ); ?></label>
				</th>
				<td>
					<input
						type="password"
						id="bahrco_api_key"
						name="bahricanli_connect_settings[api_key]"
						class="regular-text"
						value=""
						autocomplete="off"
						placeholder="<?php echo '' !== $settings['api_key']
							? esc_attr__( 'Kayıtlı — değiştirmek için yeni anahtar girin', 'bahricanli-connect' )
							: 'mm_xxxxxxxx.xxxxxxxxxxxxxxxx'; ?>"
					/>
					<p class="description">
						<?php esc_html_e( 'Anahtar güvenlik için formda hiçbir zaman gösterilmez. Boş bırakırsanız kayıtlı anahtar korunur.', 'bahricanli-connect' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Bağlantı', 'bahricanli-connect' ); ?></th>
				<td>
					<button type="button" class="button" id="bc-test-connection">
						<?php esc_html_e( 'Bağlantıyı test et', 'bahricanli-connect' ); ?>
					</button>
					<span id="bc-test-result" class="bc-test-result"></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'E-posta bildirimi', 'bahricanli-connect' ); ?></th>
				<td>
					<label for="bahrco_notify_enabled">
						<input
							type="checkbox"
							id="bahrco_notify_enabled"
							name="bahricanli_connect_settings[notify_enabled]"
							value="1"
							<?php checked( $settings['notify_enabled'] ); ?>
						/>
						<?php esc_html_e( 'Yeni mesaj geldiğinde e-posta gönder', 'bahricanli-connect' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Yaklaşık 5 dakikada bir denetlenir (WP-Cron). E-postada yalnız kimin yazdığı yer alır, mesaj içeriği gönderilmez.', 'bahricanli-connect' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bahrco_notify_email"><?php esc_html_e( 'Bildirim e-postası', 'bahricanli-connect' ); ?></label>
				</th>
				<td>
					<input
						type="text"
						id="bahrco_notify_email"
						name="bahricanli_connect_settings[notify_email]"
						class="regular-text"
						value="<?php echo esc_attr( $settings['notify_email'] ); ?>"
						placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
					/>
					<p class="description">
						<?php esc_html_e( 'Boş bırakılırsa site yönetici e-postası kullanılır. Birden çok adres virgülle ayrılır.', 'bahricanli-connect' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
