<?php
/**
 * API anahtarı ayarlanmadığında gösterilen bilgi ekranı.
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap bc-wrap">
	<h1><?php esc_html_e( 'Bahri Canlı Connect', 'bahricanli-connect' ); ?></h1>

	<div class="notice notice-warning inline">
		<p>
			<?php esc_html_e( 'Henüz bağlanmadınız. Devam etmek için API anahtarınızı girin.', 'bahricanli-connect' ); ?>
		</p>
	</div>

	<p>
		<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=bahricanli-connect-settings' ) ); ?>">
			<?php esc_html_e( 'Ayarlara git', 'bahricanli-connect' ); ?>
		</a>
	</p>
</div>
