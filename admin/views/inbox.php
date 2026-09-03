<?php
/**
 * Gelen kutusu görünümü — JS ile doldurulur (admin-ajax proxy).
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap bc-wrap">
	<h1><?php esc_html_e( 'Gelen Kutusu', 'bahricanli-connect' ); ?></h1>

	<div class="bc-inbox" id="bc-inbox">
		<aside class="bc-inbox__list">
			<div class="bc-inbox__filters">
				<button type="button" class="bc-filter is-active" data-status="open"><?php esc_html_e( 'Açık', 'bahricanli-connect' ); ?></button>
				<button type="button" class="bc-filter" data-status="closed"><?php esc_html_e( 'Kapalı', 'bahricanli-connect' ); ?></button>
			</div>
			<ul class="bc-inbox__conversations" id="bc-conversations">
				<li class="bc-inbox__empty"><?php esc_html_e( 'Yükleniyor…', 'bahricanli-connect' ); ?></li>
			</ul>
		</aside>

		<section class="bc-inbox__thread" id="bc-thread">
			<p class="bc-inbox__placeholder"><?php esc_html_e( 'Bir konuşma seçin.', 'bahricanli-connect' ); ?></p>
		</section>
	</div>
</div>
