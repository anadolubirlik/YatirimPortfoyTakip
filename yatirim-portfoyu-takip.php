<?php
/**
 * Plugin Name: Yatırım Portföyü Takip
 * Plugin URI: https://github.com/anadolubirlik/YatirimPortfoyTakip
 * Description: Borsa İstanbul hisse senetleri, kriptolar, fonlar ve altın yatırımlarınızı kolayca takip edebileceğiniz profesyonel bir portföy yönetim aracı.
 * Version: 1.0.0
 * Author: Temettücü Baba
 * Author URI: https://github.com/anadolubirlik
 * Text Domain: yatirim-portfoyu-takip
 * Domain Path: /languages
 * Requires at least: 5.2
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('YPT_VERSION', '1.0.0');
define('YPT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YPT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YPT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('YPT_PLUGIN_FILE', __FILE__);

// Include necessary files
require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip.php';
require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-activator.php';
require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-deactivator.php';

// Activation and deactivation hooks
register_activation_hook(YPT_PLUGIN_FILE, array('Yatirim_Portfoyu_Takip_Activator', 'activate'));
register_deactivation_hook(YPT_PLUGIN_FILE, array('Yatirim_Portfoyu_Takip_Deactivator', 'deactivate'));

// Initialize the plugin
function run_yatirim_portfoyu_takip() {
    $plugin = new Yatirim_Portfoyu_Takip();
    $plugin->run();
}
run_yatirim_portfoyu_takip();