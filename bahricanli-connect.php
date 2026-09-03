<?php
/**
 * Plugin Name:       BahriCanli Connect
 * Plugin URI:        https://message-manager.tr/wordpress-plugin
 * Description:        WhatsApp Business team inbox for WordPress. Connects to the Message Manager platform to read and reply to customer conversations.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Bahri Canlı
 * Author URI:        https://message-manager.tr
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bahricanli-connect
 * Domain Path:       /languages
 *
 * @package BahriCanliConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BAHRICANLI_CONNECT_VERSION', '0.1.0' );
define( 'BAHRICANLI_CONNECT_FILE', __FILE__ );
define( 'BAHRICANLI_CONNECT_DIR', plugin_dir_path( __FILE__ ) );
define( 'BAHRICANLI_CONNECT_URL', plugin_dir_url( __FILE__ ) );
define( 'BAHRICANLI_CONNECT_DEFAULT_API', 'https://message-manager.tr' );

require_once BAHRICANLI_CONNECT_DIR . 'includes/class-bc-api-client.php';
require_once BAHRICANLI_CONNECT_DIR . 'includes/class-bc-ajax.php';
require_once BAHRICANLI_CONNECT_DIR . 'includes/class-bc-admin.php';
require_once BAHRICANLI_CONNECT_DIR . 'includes/class-bc-plugin.php';

/**
 * Tekil eklenti örneğini başlat.
 */
function bahricanli_connect() {
	return BC_Plugin::instance();
}

bahricanli_connect();
