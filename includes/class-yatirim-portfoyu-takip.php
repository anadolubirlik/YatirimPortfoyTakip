<?php
/**
 * Ana eklenti sınıfı
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip {

    /**
     * Eklentinin yükleyicisi.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Yatirim_Portfoyu_Takip_Loader    $loader    Tüm aksiyonları ve filtreleri yönetir.
     */
    protected $loader;

    /**
     * Eklentinin benzersiz kimliği
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    Eklentinin benzersiz kimliği
     */
    protected $plugin_name;

    /**
     * Eklentinin mevcut versiyonu.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    Eklentinin mevcut versiyonu.
     */
    protected $version;

    /**
     * Eklentinin sınıfını tanımlar ve gerekli özellikleri ayarlar.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('YPT_VERSION')) {
            $this->version = YPT_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'yatirim-portfoyu-takip';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
    }

    /**
     * Eklentinin çalışması için gerekli bağımlılıkları yükler.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Loader sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-loader.php';

        // i18n sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-i18n.php';

        // Admin bölümü sınıfı
        require_once YPT_PLUGIN_DIR . 'admin/class-yatirim-portfoyu-takip-admin.php';

        // Kullanıcı bölümü sınıfı
        require_once YPT_PLUGIN_DIR . 'public/class-yatirim-portfoyu-takip-public.php';

        // Kullanıcı yönetimi sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-users.php';
        
        // Üyelik planları sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-membership.php';
        
        // API işlemleri sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-api.php';
        
        // Portföy işlemleri sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-portfolio.php';

        // Hisse senedi işlemleri sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/instruments/class-yatirim-portfoyu-takip-stocks.php';
        
        // Kripto para işlemleri sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/instruments/class-yatirim-portfoyu-takip-crypto.php';
        
        // Altın işlemleri sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/instruments/class-yatirim-portfoyu-takip-gold.php';
        
        // Fon işlemleri sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/instruments/class-yatirim-portfoyu-takip-funds.php';

        // Database sınıfı
        require_once YPT_PLUGIN_DIR . 'includes/class-yatirim-portfoyu-takip-db.php';

        $this->loader = new Yatirim_Portfoyu_Takip_Loader();
    }

    /**
     * Eklentinin yerelleştirme özelliklerini tanımlar.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Yatirim_Portfoyu_Takip_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Admin tarafı için tüm kancaları kaydeder.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Yatirim_Portfoyu_Takip_Admin($this->get_plugin_name(), $this->get_version());
        
        // Admin stil ve scriptleri
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Admin menü ve sayfalar
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // AJAX işleyicileri
        $this->loader->add_action('wp_ajax_yatirim_portfoyu_admin_action', $plugin_admin, 'handle_ajax');
    }

    /**
     * Kullanıcı tarafı için tüm kancaları kaydeder.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Yatirim_Portfoyu_Takip_Public($this->get_plugin_name(), $this->get_version());
        
        // Public stil ve scriptleri
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Shortcode'ları kaydet
        $this->loader->add_shortcode('yatirim_portfoyu', $plugin_public, 'display_portfolio_shortcode');
        $this->loader->add_shortcode('yatirim_portfoyu_login', $plugin_public, 'display_login_shortcode');
        $this->loader->add_shortcode('yatirim_portfoyu_register', $plugin_public, 'display_register_shortcode');
        
        // AJAX işleyicileri
        $this->loader->add_action('wp_ajax_yatirim_portfoyu_action', $plugin_public, 'handle_ajax');
        $this->loader->add_action('wp_ajax_nopriv_yatirim_portfoyu_login_action', $plugin_public, 'handle_login_ajax');
        $this->loader->add_action('wp_ajax_nopriv_yatirim_portfoyu_register_action', $plugin_public, 'handle_register_ajax');
    }

    /**
     * API entegrasyonları için kancaları kaydeder.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {
        $plugin_api = new Yatirim_Portfoyu_Takip_API($this->get_plugin_name(), $this->get_version());
        
        // API veri güncelleme işlemleri
        $this->loader->add_action('yatirim_portfoyu_update_stock_prices', $plugin_api, 'update_stock_prices');
        $this->loader->add_action('yatirim_portfoyu_update_crypto_prices', $plugin_api, 'update_crypto_prices');
        $this->loader->add_action('yatirim_portfoyu_update_gold_prices', $plugin_api, 'update_gold_prices');
        $this->loader->add_action('yatirim_portfoyu_update_fund_prices', $plugin_api, 'update_fund_prices');
        
        // Zamanlanmış görevler
        if (!wp_next_scheduled('yatirim_portfoyu_update_stock_prices')) {
            wp_schedule_event(time(), 'hourly', 'yatirim_portfoyu_update_stock_prices');
        }
        
        if (!wp_next_scheduled('yatirim_portfoyu_update_crypto_prices')) {
            wp_schedule_event(time(), 'fifteen_minutes', 'yatirim_portfoyu_update_crypto_prices');
        }
        
        if (!wp_next_scheduled('yatirim_portfoyu_update_gold_prices')) {
            wp_schedule_event(time(), 'daily', 'yatirim_portfoyu_update_gold_prices');
        }
        
        if (!wp_next_scheduled('yatirim_portfoyu_update_fund_prices')) {
            wp_schedule_event(time(), 'daily', 'yatirim_portfoyu_update_fund_prices');
        }
    }

    /**
     * Eklentinin adını döndürür.
     *
     * @since     1.0.0
     * @return    string    Eklenti adı.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Eklentinin yükleyicisine referans döndürür.
     *
     * @since     1.0.0
     * @return    Yatirim_Portfoyu_Takip_Loader    Eklentinin kancalarını yöneten yükleyici.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Eklentinin versiyonunu döndürür.
     *
     * @since     1.0.0
     * @return    string    Eklenti versiyon numarası.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Eklentiyi çalıştırır.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }
}