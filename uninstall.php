<?php
/**
 * Eklenti kaldırıldığında ayarları temizle.
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bahricanli_connect_settings' );
delete_option( 'bahricanli_connect_notify_cursor' );
wp_clear_scheduled_hook( 'bahrco_check_new_messages' );
