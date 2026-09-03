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
