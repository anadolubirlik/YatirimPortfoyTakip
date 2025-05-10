<?php
/**
 * Eklentinin admin bölümü
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_Admin {

    /**
     * Eklentinin benzersiz kimliği
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    Eklentinin benzersiz kimliği.
     */
    private $plugin_name;

    /**
     * Eklentinin sürümü
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    Eklentinin mevcut versiyonu.
     */
    private $version;

    /**
     * Veritabanı sınıfı
     *
     * @var Yatirim_Portfoyu_Takip_DB
     */
    private $db;

    /**
     * API sınıfı
     *
     * @var Yatirim_Portfoyu_Takip_API
     */
    private $api;

    /**
     * Yapılandırıcı
     *
     * @param string $plugin_name    Eklentinin adı.
     * @param string $version        Eklentinin versiyonu.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->db = new Yatirim_Portfoyu_Takip_DB();
        $this->api = new Yatirim_Portfoyu_Takip_API($plugin_name, $version);
    }

    /**
     * Admin stil dosyalarını kaydet
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/yatirim-portfoyu-takip-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css', array(), '3.7.0');
    }

    /**
     * Admin script dosyalarını kaydet
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/yatirim-portfoyu-takip-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js', array(), '3.7.0', false);
        
        // AJAX için scripts
        wp_localize_script($this->plugin_name, 'yatirim_portfoyu_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yatirim_portfoyu_admin_nonce'),
        ));
    }

    /**
     * Admin menü ve sayfaları ekle
     */
    public function add_plugin_admin_menu() {
        // Ana menü
        add_menu_page(
            __('Yatırım Portföyü Takip', 'yatirim-portfoyu-takip'),
            __('Yatırım Portföyü', 'yatirim-portfoyu-takip'),
            'manage_options',
            'yatirim-portfoyu-takip',
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-chart-area',
            26
        );
        
        // Kullanıcı yönetimi alt sayfası
        add_submenu_page(
            'yatirim-portfoyu-takip',
            __('Kullanıcı Yönetimi', 'yatirim-portfoyu-takip'),
            __('Kullanıcılar', 'yatirim-portfoyu-takip'),
            'manage_options',
            'yatirim-portfoyu-users',
            array($this, 'display_plugin_users_page')
        );
        
        // API ayarları alt sayfası
        add_submenu_page(
            'yatirim-portfoyu-takip',
            __('API Ayarları', 'yatirim-portfoyu-takip'),
            __('API Ayarları', 'yatirim-portfoyu-takip'),
            'manage_options',
            'yatirim-portfoyu-api',
            array($this, 'display_plugin_api_page')
        );
        
        // Kısa kodlar alt sayfası
        add_submenu_page(
            'yatirim-portfoyu-takip',
            __('Kısa Kodlar', 'yatirim-portfoyu-takip'),
            __('Kısa Kodlar', 'yatirim-portfoyu-takip'),
            'manage_options',
            'yatirim-portfoyu-shortcodes',
            array($this, 'display_plugin_shortcodes_page')
        );
    }

    /**
     * Ana dashboard sayfası
     */
    public function display_plugin_admin_dashboard() {
        // Kullanıcı sayıları
        global $wpdb;
        $users_table = $wpdb->prefix . 'ypt_users';
        $total_users = $wpdb->get_var("SELECT COUNT(*) FROM $users_table");
        $premium_users = $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE membership_type = 'premium'");
        $free_users = $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE membership_type = 'free'");
        
        // Toplam işlem sayıları
        $stocks_table = $wpdb->prefix . 'ypt_stocks';
        $total_stocks = $wpdb->get_var("SELECT COUNT(*) FROM $stocks_table");
        
        $crypto_table = $wpdb->prefix . 'ypt_crypto';
        $total_crypto = $wpdb->get_var("SELECT COUNT(*) FROM $crypto_table");
        
        $gold_table = $wpdb->prefix . 'ypt_gold';
        $total_gold = $wpdb->get_var("SELECT COUNT(*) FROM $gold_table");
        
        $funds_table = $wpdb->prefix . 'ypt_funds';
        $total_funds = $wpdb->get_var("SELECT COUNT(*) FROM $funds_table");
        
        // Son 10 kullanıcı
        $recent_users = $wpdb->get_results(
            "SELECT id, username, email, membership_type, created_at FROM $users_table ORDER BY created_at DESC LIMIT 10",
            ARRAY_A
        );
        
        // API durumları
        $collectapi_bist = $this->api->get_api_settings('collectapi_bist');
        $collectapi_gold = $this->api->get_api_settings('collectapi_gold');
        $collectapi_currency = $this->api->get_api_settings('collectapi_currency');
        
        // Şablonu görüntüle
        include_once('partials/yatirim-portfoyu-takip-admin-display.php');
    }

    /**
     * Kullanıcı yönetimi sayfası
     */
    public function display_plugin_users_page() {
        // Kullanıcı işlemleri için form gönderimleri işle
        $message = '';
        $message_type = '';
        
        if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
            if (isset($_POST['username'], $_POST['email'], $_POST['password'])) {
                $username = sanitize_text_field($_POST['username']);
                $email = sanitize_email($_POST['email']);
                $password = $_POST['password'];
                $membership_type = isset($_POST['membership_type']) ? sanitize_text_field($_POST['membership_type']) : 'free';
                
                $user_manager = new Yatirim_Portfoyu_Takip_Users();
                $result = $user_manager->register_user(array(
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'membership_type' => $membership_type
                ));
                
                if (!is_wp_error($result)) {
                    $message = __('Kullanıcı başarıyla eklendi.', 'yatirim-portfoyu-takip');
                    $message_type = 'success';
                } else {
                    $message = $result->get_error_message();
                    $message_type = 'error';
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
            if (isset($_POST['user_id'], $_POST['username'], $_POST['email'], $_POST['membership_type'])) {
                $user_id = intval($_POST['user_id']);
                $username = sanitize_text_field($_POST['username']);
                $email = sanitize_email($_POST['email']);
                $membership_type = sanitize_text_field($_POST['membership_type']);
                
                $update_data = array(
                    'username' => $username,
                    'email' => $email,
                    'membership_type' => $membership_type
                );
                
                // Şifre değiştirilecekse ekle
                if (!empty($_POST['password'])) {
                    $update_data['password'] = $_POST['password'];
                }
                
                // Üyelik süresi ekle
                if ($membership_type === 'premium' && !empty($_POST['membership_expires'])) {
                    $update_data['membership_expires'] = sanitize_text_field($_POST['membership_expires']);
                }
                
                $result = $this->db->update_user($user_id, $update_data);
                
                if ($result) {
                    $message = __('Kullanıcı başarıyla güncellendi.', 'yatirim-portfoyu-takip');
                    $message_type = 'success';
                } else {
                    $message = __('Kullanıcı güncellenemedi.', 'yatirim-portfoyu-takip');
                    $message_type = 'error';
                }
            }
        } elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
            
            // Kullanıcıya ait tüm verileri silme işlemi
            global $wpdb;
            
            // İşlem başarılı mı kontrolü
            $deleted = $wpdb->delete($wpdb->prefix . 'ypt_users', array('id' => $user_id));
            
            if ($deleted) {
                // Bağlantılı tüm verileri sil
                $wpdb->delete($wpdb->prefix . 'ypt_portfolio', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_stocks', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_stock_transactions', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_dividends', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_crypto', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_crypto_transactions', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_gold', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_gold_transactions', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_funds', array('user_id' => $user_id));
                $wpdb->delete($wpdb->prefix . 'ypt_fund_transactions', array('user_id' => $user_id));
                
                $message = __('Kullanıcı ve ilişkili tüm verileri başarıyla silindi.', 'yatirim-portfoyu-takip');
                $message_type = 'success';
            } else {
                $message = __('Kullanıcı silinemedi.', 'yatirim-portfoyu-takip');
                $message_type = 'error';
            }
        }
        
        // Kullanıcıları listele
        global $wpdb;
        $users_table = $wpdb->prefix . 'ypt_users';
        
        // Sayfalama için
        $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 20;
        $offset = ($current_page - 1) * $per_page;
        
        $total_users = $wpdb->get_var("SELECT COUNT(*) FROM $users_table");
        $total_pages = ceil($total_users / $per_page);
        
        $users = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $users_table ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
        
        // Şablonu görüntüle
        include_once('partials/yatirim-portfoyu-takip-admin-users.php');
    }

    /**
     * API ayarları sayfası
     */
    public function display_plugin_api_page() {
        $message = '';
        $message_type = '';
        
        // Form gönderimleri işle
        if (isset($_POST['action']) && $_POST['action'] === 'save_api_settings') {
            if (isset($_POST['api_name'])) {
                $api_name = sanitize_text_field($_POST['api_name']);
                $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
                $api_secret = isset($_POST['api_secret']) ? sanitize_text_field($_POST['api_secret']) : '';
                $api_endpoint = isset($_POST['api_endpoint']) ? sanitize_text_field($_POST['api_endpoint']) : '';
                $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'inactive';
                
                $settings = array(
                    'api_key' => $api_key,
                    'api_secret' => $api_secret,
                    'api_endpoint' => $api_endpoint,
                    'status' => $status,
                    'updated_at' => current_time('mysql')
                );
                
                $result = $this->api->save_api_settings($api_name, $settings);
                
                if ($result) {
                    $message = __('API ayarları başarıyla kaydedildi.', 'yatirim-portfoyu-takip');
                    $message_type = 'success';
                } else {
                    $message = __('API ayarları kaydedilemedi.', 'yatirim-portfoyu-takip');
                    $message_type = 'error';
                }
            }
        }
        
        // API ayarlarını al
        $collectapi_bist = $this->api->get_api_settings('collectapi_bist');
        $collectapi_gold = $this->api->get_api_settings('collectapi_gold');
        $collectapi_currency = $this->api->get_api_settings('collectapi_currency');
        $coingecko = $this->api->get_api_settings('coingecko');
        
        // Şablonu görüntüle
        include_once('partials/yatirim-portfoyu-takip-admin-api.php');
    }

    /**
     * Kısa kodlar sayfası
     */
    public function display_plugin_shortcodes_page() {
        // Kısa kod bilgilerini göster
        include_once('partials/yatirim-portfoyu-takip-admin-shortcodes.php');
    }

    /**
     * AJAX işleyicisi
     */
    public function handle_ajax() {
        // Nonce kontrolü
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'yatirim_portfoyu_admin_nonce')) {
            wp_send_json_error(array('message' => __('Güvenlik doğrulaması başarısız.', 'yatirim-portfoyu-takip')));
            die();
        }
        
        $action = isset($_REQUEST['admin_action']) ? sanitize_text_field($_REQUEST['admin_action']) : '';
        
        switch ($action) {
            case 'get_user_details':
                $user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
                
                if ($user_id <= 0) {
                    wp_send_json_error(array('message' => __('Geçersiz kullanıcı ID.', 'yatirim-portfoyu-takip')));
                    die();
                }
                
                global $wpdb;
                $users_table = $wpdb->prefix . 'ypt_users';
                
                $user = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM $users_table WHERE id = %d",
                        $user_id
                    ),
                    ARRAY_A
                );
                
                if (!$user) {
                    wp_send_json_error(array('message' => __('Kullanıcı bulunamadı.', 'yatirim-portfoyu-takip')));
                    die();
                }
                
                // Şifreyi çıkar
                unset($user['password']);
                
                // Kullanıcının yatırım araçları istatistiklerini al
                $stats = $this->get_user_investment_stats($user_id);
                
                $response = array(
                    'user' => $user,
                    'stats' => $stats
                );
                
                wp_send_json_success($response);
                break;
                
            case 'test_api_connection':
                $api_name = isset($_REQUEST['api_name']) ? sanitize_text_field($_REQUEST['api_name']) : '';
                $api_key = isset($_REQUEST['api_key']) ? sanitize_text_field($_REQUEST['api_key']) : '';
                
                if (empty($api_name) || empty($api_key)) {
                    wp_send_json_error(array('message' => __('API adı ve API anahtarı gereklidir.', 'yatirim-portfoyu-takip')));
                    die();
                }
                
                // API bağlantısını test et
                $test_result = $this->test_api_connection($api_name, $api_key);
                
                if (is_wp_error($test_result)) {
                    wp_send_json_error(array('message' => $test_result->get_error_message()));
                } else {
                    wp_send_json_success(array('message' => __('API bağlantısı başarılı!', 'yatirim-portfoyu-takip')));
                }
                break;
                
            case 'update_prices_manually':
                $type = isset($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : '';
                
                switch ($type) {
                    case 'stocks':
                        $this->api->update_stock_prices();
                        wp_send_json_success(array('message' => __('Hisse senedi fiyatları güncellendi.', 'yatirim-portfoyu-takip')));
                        break;
                        
                    case 'crypto':
                        $this->api->update_crypto_prices();
                        wp_send_json_success(array('message' => __('Kripto para fiyatları güncellendi.', 'yatirim-portfoyu-takip')));
                        break;
                        
                    case 'gold':
                        $this->api->update_gold_prices();
                        wp_send_json_success(array('message' => __('Altın fiyatları güncellendi.', 'yatirim-portfoyu-takip')));
                        break;
                        
                    case 'funds':
                        $this->api->update_fund_prices();
                        wp_send_json_success(array('message' => __('Fon fiyatları güncellendi.', 'yatirim-portfoyu-takip')));
                        break;
                        
                    case 'all':
                        $this->api->update_stock_prices();
                        $this->api->update_crypto_prices();
                        $this->api->update_gold_prices();
                        $this->api->update_fund_prices();
                        wp_send_json_success(array('message' => __('Tüm fiyatlar güncellendi.', 'yatirim-portfoyu-takip')));
                        break;
                        
                    default:
                        wp_send_json_error(array('message' => __('Geçersiz yatırım türü.', 'yatirim-portfoyu-takip')));
                }
                break;
                
            default:
                wp_send_json_error(array('message' => __('Geçersiz işlem.', 'yatirim-portfoyu-takip')));
        }
        
        die();
    }

    /**
     * API bağlantısını test et
     *
     * @param string $api_name API adı
     * @param string $api_key API anahtarı
     * @return bool|WP_Error Test sonucu
     */
    private function test_api_connection($api_name, $api_key) {
        switch ($api_name) {
            case 'collectapi_bist':
                $api_url = 'https://api.collectapi.com/economy/hisseSenedi';
                $headers = array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'apikey ' . $api_key
                );
                break;
                
            case 'collectapi_gold':
                $api_url = 'https://api.collectapi.com/economy/goldPrice';
                $headers = array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'apikey ' . $api_key
                );
                break;
                
            case 'collectapi_currency':
                $api_url = 'https://api.collectapi.com/economy/exchange?base=USD';
                $headers = array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'apikey ' . $api_key
                );
                break;
                
            case 'coingecko':
                // CoinGecko API ücretsiz olduğu için sadece erişilebilirliği kontrol et
                $api_url = 'https://api.coingecko.com/api/v3/ping';
                $headers = array();
                break;
                
            default:
                return new WP_Error('invalid_api', __('Geçersiz API adı.', 'yatirim-portfoyu-takip'));
        }
        
        $response = wp_remote_get($api_url, array('headers' => $headers));
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', sprintf(__('API hatası: %s', 'yatirim-portfoyu-takip'), $response->get_error_message()));
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            $error_message = isset($data['message']) ? $data['message'] : __('Bilinmeyen API hatası.', 'yatirim-portfoyu-takip');
            
            return new WP_Error('api_error', sprintf(__('API hatası (HTTP %d): %s', 'yatirim-portfoyu-takip'), $status_code, $error_message));
        }
        
        return true;
    }

    /**
     * Kullanıcının yatırım araçları istatistiklerini al
     *
     * @param int $user_id Kullanıcı ID'si
     * @return array İstatistikler
     */
    private function get_user_investment_stats($user_id) {
        global $wpdb;
        
        $stats = array();
        
        // Hisse senedi sayısı
        $stats['stocks_count'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_stocks WHERE user_id = %d",
                $user_id
            )
        );
        
        // Kripto para sayısı
        $stats['crypto_count'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_crypto WHERE user_id = %d",
                $user_id
            )
        );
        
        // Altın sayısı
        $stats['gold_count'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_gold WHERE user_id = %d",
                $user_id
            )
        );
        
        // Fon sayısı
        $stats['funds_count'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_funds WHERE user_id = %d",
                $user_id
            )
        );
        
        // Toplam işlem sayısı
        $stats['stock_transactions'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_stock_transactions WHERE user_id = %d",
                $user_id
            )
        );
        
        $stats['crypto_transactions'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_crypto_transactions WHERE user_id = %d",
                $user_id
            )
        );
        
        $stats['gold_transactions'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_gold_transactions WHERE user_id = %d",
                $user_id
            )
        );
        
        $stats['fund_transactions'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_fund_transactions WHERE user_id = %d",
                $user_id
            )
        );
        
        // Toplam temettü sayısı
        $stats['dividends_count'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ypt_dividends WHERE user_id = %d",
                $user_id
            )
        );
        
        // Son giriş tarihi
        $stats['last_login'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT last_login FROM {$wpdb->prefix}ypt_users WHERE id = %d",
                $user_id
            )
        );
        
        return $stats;
    }
}