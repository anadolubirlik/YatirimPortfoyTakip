<?php
/**
 * Eklentinin kullanıcı (frontend) bölümü
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_Public {

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
     * Kullanıcı yönetimi sınıfı
     *
     * @var Yatirim_Portfoyu_Takip_Users
     */
    private $users;

    /**
     * API sınıfı
     *
     * @var Yatirim_Portfoyu_Takip_API
     */
    private $api;

    /**
     * Veritabanı sınıfı
     *
     * @var Yatirim_Portfoyu_Takip_DB
     */
    private $db;

    /**
     * Yapılandırıcı
     *
     * @param string $plugin_name    Eklentinin adı.
     * @param string $version        Eklentinin versiyonu.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->users = new Yatirim_Portfoyu_Takip_Users();
        $this->api = new Yatirim_Portfoyu_Takip_API($plugin_name, $version);
        $this->db = new Yatirim_Portfoyu_Takip_DB();
    }

    /**
     * Frontend stil dosyalarını kaydet
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/yatirim-portfoyu-takip-public.css', array(), $this->version, 'all');
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', array(), '5.1.3');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '6.0.0');
        wp_enqueue_style('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css', array(), '3.7.0');
    }

    /**
     * Frontend script dosyalarını kaydet
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/yatirim-portfoyu-takip-public.js', array('jquery'), $this->version, false);
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.1.3', false);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js', array(), '3.7.0', false);
        
        // AJAX için scripts
        wp_localize_script($this->plugin_name, 'yatirim_portfoyu', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yatirim_portfoyu_nonce'),
            'texts' => array(
                'loading' => __('Yükleniyor...', 'yatirim-portfoyu-takip'),
                'error' => __('Hata oluştu', 'yatirim-portfoyu-takip'),
                'success' => __('İşlem başarılı', 'yatirim-portfoyu-takip'),
                'confirm_delete' => __('Bu kaydı silmek istediğinize emin misiniz?', 'yatirim-portfoyu-takip'),
                'no_data' => __('Veri bulunamadı', 'yatirim-portfoyu-takip'),
                'premium_required' => __('Bu özellik Premium üyelik gerektirir', 'yatirim-portfoyu-takip'),
                'free_limit_reached' => __('Ücretsiz üyelik sınırına ulaştınız. Lütfen Premium üyeliğe geçin.', 'yatirim-portfoyu-takip')
            )
        ));
    }

    /**
     * Portföy görüntüleme shortcode
     *
     * @param array $atts Shortcode parametreleri
     * @return string Portföy HTML çıktısı
     */
    public function display_portfolio_shortcode($atts) {
        // Shortcode parametrelerini al
        $atts = shortcode_atts(array(
            'view' => 'summary', // summary, stocks, crypto, gold, funds
        ), $atts);
        
        // Kullanıcı giriş yapmış mı kontrol et
        $current_user_id = $this->users->get_current_user_id();
        
        if (!$current_user_id) {
            // Kullanıcı giriş yapmamışsa login formunu göster
            return $this->get_login_form();
        }
        
        $user = $this->users->get_current_user();
        
        // Görünüme göre içeriği oluştur
        $view = $atts['view'];
        $content = '';
        
        // Üst menüyü oluştur
        $menu_items = array(
            'summary' => __('Genel Bakış', 'yatirim-portfoyu-takip'),
            'stocks' => __('Hisse Senetleri', 'yatirim-portfoyu-takip'),
            'crypto' => __('Kripto Paralar', 'yatirim-portfoyu-takip'),
            'gold' => __('Altın', 'yatirim-portfoyu-takip'),
            'funds' => __('Fonlar', 'yatirim-portfoyu-takip'),
            'reports' => __('Raporlar', 'yatirim-portfoyu-takip'),
            'profile' => __('Profil', 'yatirim-portfoyu-takip'),
            'logout' => __('Çıkış', 'yatirim-portfoyu-takip')
        );
        
        $menu = '<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">' . __('Yatırım Portföyü', 'yatirim-portfoyu-takip') . '</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portfolioNavbar" aria-controls="portfolioNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="portfolioNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">';
        
        foreach ($menu_items as $key => $label) {
            if ($key === 'logout') {
                continue;
            }
            
            $active = ($key === $view) ? 'active' : '';
            $menu .= '<li class="nav-item"><a class="nav-link ' . $active . '" href="' . esc_url(add_query_arg('view', $key)) . '">' . $label . '</a></li>';
        }
        
        $menu .= '</ul>';
        
        // Kullanıcı bilgisi ve çıkış düğmesi
        $menu .= '<div class="d-flex align-items-center">
                <span class="me-3">' . sprintf(__('Merhaba, %s', 'yatirim-portfoyu-takip'), esc_html($user['username'])) . '</span>';
        
        // Premium üyelik rozeti
        if ($this->users->is_premium($current_user_id)) {
            $menu .= '<span class="badge bg-warning me-3">' . __('Premium', 'yatirim-portfoyu-takip') . '</span>';
        }
        
        $menu .= '<a href="' . esc_url(add_query_arg('action', 'logout')) . '" class="btn btn-sm btn-outline-danger">' . __('Çıkış', 'yatirim-portfoyu-takip') . '</a>
                </div>
            </div>
        </div>
        </nav>';
        
        // Çıkış işlemi
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $this->users->logout_user();
            
            // Mevcut sayfaya yönlendir
            $current_url = remove_query_arg('action', $_SERVER['REQUEST_URI']);
            wp_redirect($current_url);
            exit;
        }
        
        // İçeriği hazırla
        switch ($view) {
            case 'stocks':
                $content = $this->get_stocks_view($current_user_id);
                break;
                
            case 'crypto':
                $content = $this->get_crypto_view($current_user_id);
                break;
                
            case 'gold':
                $content = $this->get_gold_view($current_user_id);
                break;
                
            case 'funds':
                $content = $this->get_funds_view($current_user_id);
                break;
                
            case 'reports':
                $content = $this->get_reports_view($current_user_id);
                break;
                
            case 'profile':
                $content = $this->get_profile_view($current_user_id);
                break;
                
            default:
                // Varsayılan olarak özet görünümü göster
                $content = $this->get_summary_view($current_user_id);
                break;
        }
        
        return $menu . $content;
    }

    /**
     * Giriş formu HTML'si oluştur
     *
     * @return string Giriş formu HTML'si
     */
    private function get_login_form() {
        $output = '<div class="yatirim-portfoyu-login-container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="m-0">' . __('Yatırım Portföyüm', 'yatirim-portfoyu-takip') . '</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">' . __('Yatırım portföyünüzü görüntülemek için lütfen giriş yapın.', 'yatirim-portfoyu-takip') . '</div>
                            
                            <form id="yatirim-portfoyu-login-form">
                                <div class="mb-3">
                                    <label for="username" class="form-label">' . __('Kullanıcı Adı / E-posta', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">' . __('Parola', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">' . __('Giriş Yap', 'yatirim-portfoyu-takip') . '</button>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="#" id="show-register-form">' . __('Üye değil misiniz? Kayıt olun', 'yatirim-portfoyu-takip') . '</a>
                                </div>
                                <div id="login-message" class="mt-3"></div>
                            </form>
                            
                            <form id="yatirim-portfoyu-register-form" style="display:none;">
                                <h4>' . __('Yeni Üyelik', 'yatirim-portfoyu-takip') . '</h4>
                                <div class="mb-3">
                                    <label for="reg_username" class="form-label">' . __('Kullanıcı Adı', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="text" class="form-control" id="reg_username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_email" class="form-label">' . __('E-posta', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="email" class="form-control" id="reg_email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_password" class="form-label">' . __('Parola', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="password" class="form-control" id="reg_password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_password_confirm" class="form-label">' . __('Parola Tekrar', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="password" class="form-control" id="reg_password_confirm" name="password_confirm" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms_agree" name="terms_agree" required>
                                    <label class="form-check-label" for="terms_agree">' . __('Kullanım şartlarını kabul ediyorum', 'yatirim-portfoyu-takip') . '</label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">' . __('Kayıt Ol', 'yatirim-portfoyu-takip') . '</button>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="#" id="show-login-form">' . __('Zaten üye misiniz? Giriş yapın', 'yatirim-portfoyu-takip') . '</a>
                                </div>
                                <div id="register-message" class="mt-3"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        
        return $output;
    }

    /**
     * Özet görünümü
     *
     * @param int $user_id Kullanıcı ID
     * @return string HTML içeriği
     */
    private function get_summary_view($user_id) {
        global $wpdb;
        
        // Portföy değer dağılımı
        $portfolio_value = array(
            'stocks' => 0,
            'crypto' => 0,
            'gold' => 0,
            'funds' => 0
        );
        
        // Hisse senetleri toplam değeri
        $stocks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT SUM(total_shares * current_price) as total_value FROM {$wpdb->prefix}ypt_stocks WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );
        
        if ($stocks[0]['total_value']) {
            $portfolio_value['stocks'] = floatval($stocks[0]['total_value']);
        }
        
        // Kripto para toplam değeri
        $crypto = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT SUM(total_amount * current_price) as total_value FROM {$wpdb->prefix}ypt_crypto WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );
        
        if ($crypto[0]['total_value']) {
            $portfolio_value['crypto'] = floatval($crypto[0]['total_value']);
        }
        
        // Altın toplam değeri
        $gold = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT SUM(total_weight * current_price) as total_value FROM {$wpdb->prefix}ypt_gold WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );
        
        if ($gold[0]['total_value']) {
            $portfolio_value['gold'] = floatval($gold[0]['total_value']);
        }
        
        // Fon toplam değeri
        $funds = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT SUM(total_shares * current_price) as total_value FROM {$wpdb->prefix}ypt_funds WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );
        
        if ($funds[0]['total_value']) {
            $portfolio_value['funds'] = floatval($funds[0]['total_value']);
        }
        
        // Toplam portföy değeri
        $total_portfolio_value = array_sum($portfolio_value);
        
        // Portföy dağılım yüzdeleri
        $portfolio_percentage = array();
        foreach ($portfolio_value as $key => $value) {
            if ($total_portfolio_value > 0) {
                $portfolio_percentage[$key] = round(($value / $total_portfolio_value) * 100, 2);
            } else {
                $portfolio_percentage[$key] = 0;
            }
        }
        
        // En karlı yatırımlar
        $top_profitable_stocks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT stock_code, total_shares * (current_price - average_cost) as profit,
                ((current_price / average_cost) - 1) * 100 as profit_percentage
                FROM {$wpdb->prefix}ypt_stocks
                WHERE user_id = %d AND total_shares > 0
                ORDER BY profit_percentage DESC
                LIMIT 5",
                $user_id
            ),
            ARRAY_A
        );
        
        // En zararlı yatırımlar
        $top_losing_stocks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT stock_code, total_shares * (current_price - average_cost) as profit,
                ((current_price / average_cost) - 1) * 100 as profit_percentage
                FROM {$wpdb->prefix}ypt_stocks
                WHERE user_id = %d AND total_shares > 0
                ORDER BY profit_percentage ASC
                LIMIT 5",
                $user_id
            ),
            ARRAY_A
        );
        
        // Temettü toplamı
        $total_dividends = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(amount) FROM {$wpdb->prefix}ypt_dividends WHERE user_id = %d",
                $user_id
            )
        );
        
        // HTML çıktısını oluştur
        $output = '<div class="container-fluid">
            <h2>' . __('Portföy Özeti', 'yatirim-portfoyu-takip') . '</h2>
            
            <div class="row mt-4">
                <div class="col-md-3 mb-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <h5 class="card-title">' . __('Toplam Portföy Değeri', 'yatirim-portfoyu-takip') . '</h5>
                            <h2 class="display-6">' . number_format($total_portfolio_value, 2, ',', '.') . ' ₺</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <h5 class="card-title">' . __('Toplam Temettü Geliri', 'yatirim-portfoyu-takip') . '</h5>
                            <h2 class="display-6">' . number_format($total_dividends ?: 0, 2, ',', '.') . ' ₺</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <h5 class="card-title">' . __('Hisse Sayısı', 'yatirim-portfoyu-takip') . '</h5>
                            <h2 class="display-6">' . count($wpdb->get_results($wpdb->prepare("SELECT DISTINCT stock_code FROM {$wpdb->prefix}ypt_stocks WHERE user_id = %d AND total_shares > 0", $user_id))) . '</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card bg-warning h-100">
                        <div class="card-body">
                            <h5 class="card-title">' . __('Toplam Varlık Sayısı', 'yatirim-portfoyu-takip') . '</h5>
                            <h2 class="display-6">' . $this->db->get_user_instrument_count($user_id) . '</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>' . __('Portföy Dağılımı', 'yatirim-portfoyu-takip') . '</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="portfolioDistributionChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>' . __('En Karlı Hisseler', 'yatirim-portfoyu-takip') . '</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>' . __('Hisse', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Kar/Zarar', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Yüzde', 'yatirim-portfoyu-takip') . '</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
        
        if (empty($top_profitable_stocks)) {
            $output .= '<tr><td colspan="3" class="text-center">' . __('Henüz karlı hisseniz yok', 'yatirim-portfoyu-takip') . '</td></tr>';
        } else {
            foreach ($top_profitable_stocks as $stock) {
                $profit_class = $stock['profit'] >= 0 ? 'text-success' : 'text-danger';
                $profit_sign = $stock['profit'] >= 0 ? '+' : '';
                
                $output .= '<tr>
                    <td>' . esc_html($stock['stock_code']) . '</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($stock['profit'], 2, ',', '.') . ' ₺</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($stock['profit_percentage'], 2, ',', '.') . '%</td>
                </tr>';
            }
        }
        
        $output .= '</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>' . __('Varlık Dağılımı', 'yatirim-portfoyu-takip') . '</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>' . __('Varlık Tipi', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Değer', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Oran', 'yatirim-portfoyu-takip') . '</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>' . __('Hisse Senetleri', 'yatirim-portfoyu-takip') . '</td>
                                            <td>' . number_format($portfolio_value['stocks'], 2, ',', '.') . ' ₺</td>
                                            <td>' . number_format($portfolio_percentage['stocks'], 2, ',', '.') . '%</td>
                                        </tr>
                                        <tr>
                                            <td>' . __('Kripto Paralar', 'yatirim-portfoyu-takip') . '</td>
                                            <td>' . number_format($portfolio_value['crypto'], 2, ',', '.') . ' ₺</td>
                                            <td>' . number_format($portfolio_percentage['crypto'], 2, ',', '.') . '%</td>
                                        </tr>
                                        <tr>
                                            <td>' . __('Altın', 'yatirim-portfoyu-takip') . '</td>
                                            <td>' . number_format($portfolio_value['gold'], 2, ',', '.') . ' ₺</td>
                                            <td>' . number_format($portfolio_percentage['gold'], 2, ',', '.') . '%</td>
                                        </tr>
                                        <tr>
                                            <td>' . __('Fonlar', 'yatirim-portfoyu-takip') . '</td>
                                            <td>' . number_format($portfolio_value['funds'], 2, ',', '.') . ' ₺</td>
                                            <td>' . number_format($portfolio_percentage['funds'], 2, ',', '.') . '%</td>
                                        </tr>
                                        <tr class="table-primary fw-bold">
                                            <td>' . __('Toplam', 'yatirim-portfoyu-takip') . '</td>
                                            <td>' . number_format($total_portfolio_value, 2, ',', '.') . ' ₺</td>
                                            <td>100%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>' . __('En Zararlı Hisseler', 'yatirim-portfoyu-takip') . '</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>' . __('Hisse', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Kar/Zarar', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Yüzde', 'yatirim-portfoyu-takip') . '</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
        
        if (empty($top_losing_stocks)) {
            $output .= '<tr><td colspan="3" class="text-center">' . __('Henüz zararlı hisseniz yok', 'yatirim-portfoyu-takip') . '</td></tr>';
        } else {
            foreach ($top_losing_stocks as $stock) {
                $profit_class = $stock['profit'] >= 0 ? 'text-success' : 'text-danger';
                $profit_sign = $stock['profit'] >= 0 ? '+' : '';
                
                $output .= '<tr>
                    <td>' . esc_html($stock['stock_code']) . '</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($stock['profit'], 2, ',', '.') . ' ₺</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($stock['profit_percentage'], 2, ',', '.') . '%</td>
                </tr>';
            }
        }
        
        $output .= '</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>' . __('Son İşlemler', 'yatirim-portfoyu-takip') . '</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>' . __('Tarih', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Tür', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Varlık', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('İşlem', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Miktar', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Fiyat', 'yatirim-portfoyu-takip') . '</th>
                                            <th>' . __('Toplam', 'yatirim-portfoyu-takip') . '</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
        
        // Son 10 işlem
        $last_transactions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 'stock' as type, st.transaction_date, s.stock_code as asset_code, 
                st.transaction_type, st.shares as amount, st.price, st.shares * st.price as total
                FROM {$wpdb->prefix}ypt_stock_transactions st
                JOIN {$wpdb->prefix}ypt_stocks s ON st.stock_id = s.id
                WHERE st.user_id = %d
                
                UNION
                
                SELECT 'crypto' as type, ct.transaction_date, c.crypto_code as asset_code, 
                ct.transaction_type, ct.amount, ct.price, ct.amount * ct.price as total
                FROM {$wpdb->prefix}ypt_crypto_transactions ct
                JOIN {$wpdb->prefix}ypt_crypto c ON ct.crypto_id = c.id
                WHERE ct.user_id = %d
                
                UNION
                
                SELECT 'gold' as type, gt.transaction_date, g.gold_type as asset_code, 
                gt.transaction_type, gt.weight as amount, gt.price, gt.weight * gt.price as total
                FROM {$wpdb->prefix}ypt_gold_transactions gt
                JOIN {$wpdb->prefix}ypt_gold g ON gt.gold_id = g.id
                WHERE gt.user_id = %d
                
                UNION
                
                SELECT 'fund' as type, ft.transaction_date, f.fund_code as asset_code, 
                ft.transaction_type, ft.shares as amount, ft.price, ft.shares * ft.price as total
                FROM {$wpdb->prefix}ypt_fund_transactions ft
                JOIN {$wpdb->prefix}ypt_funds f ON ft.fund_id = f.id
                WHERE ft.user_id = %d
                
                ORDER BY transaction_date DESC
                LIMIT 10",
                $user_id, $user_id, $user_id, $user_id
            ),
            ARRAY_A
        );
        
        if (empty($last_transactions)) {
            $output .= '<tr><td colspan="7" class="text-center">' . __('Henüz işlem yapmadınız', 'yatirim-portfoyu-takip') . '</td></tr>';
        } else {
            foreach ($last_transactions as $transaction) {
                $type_labels = array(
                    'stock' => __('Hisse', 'yatirim-portfoyu-takip'),
                    'crypto' => __('Kripto', 'yatirim-portfoyu-takip'),
                    'gold' => __('Altın', 'yatirim-portfoyu-takip'),
                    'fund' => __('Fon', 'yatirim-portfoyu-takip')
                );
                
                $transaction_type_class = $transaction['transaction_type'] === 'buy' ? 'text-success' : 'text-danger';
                $transaction_type_label = $transaction['transaction_type'] === 'buy' ? __('Alış', 'yatirim-portfoyu-takip') : __('Satış', 'yatirim-portfoyu-takip');
                
                $output .= '<tr>
                    <td>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($transaction['transaction_date'])) . '</td>
                    <td>' . $type_labels[$transaction['type']] . '</td>
                    <td>' . esc_html($transaction['asset_code']) . '</td>
                    <td class="' . $transaction_type_class . '">' . $transaction_type_label . '</td>
                    <td>' . number_format($transaction['amount'], $transaction['type'] === 'crypto' ? 8 : 2, ',', '.') . '</td>
                    <td>' . number_format($transaction['price'], 2, ',', '.') . ' ₺</td>
                    <td>' . number_format($transaction['total'], 2, ',', '.') . ' ₺</td>
                </tr>';
            }
        }
        
        $output .= '</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Portföy dağılımı pasta grafiği
            var ctx = document.getElementById("portfolioDistributionChart").getContext("2d");
            var portfolioChart = new Chart(ctx, {
                type: "pie",
                data: {
                    labels: ["' . __('Hisse Senetleri', 'yatirim-portfoyu-takip') . '", "' . __('Kripto Paralar', 'yatirim-portfoyu-takip') . '", "' . __('Altın', 'yatirim-portfoyu-takip') . '", "' . __('Fonlar', 'yatirim-portfoyu-takip') . '"],
                    datasets: [{
                        data: [' . $portfolio_percentage['stocks'] . ', ' . $portfolio_percentage['crypto'] . ', ' . $portfolio_percentage['gold'] . ', ' . $portfolio_percentage['funds'] . '],
                        backgroundColor: ["#0d6efd", "#6f42c1", "#ffc107", "#20c997"],
                        hoverOffset: 4
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: "right"
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || "";
                                    var value = context.parsed || 0;
                                    return label + ": " + value.toFixed(2) + "%";
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>';
        
        return $output;
    }

    /**
     * Hisse senetleri görünümü
     *
     * @param int $user_id Kullanıcı ID
     * @return string HTML içeriği
     */
    private function get_stocks_view($user_id) {
        global $wpdb;
        
        // Kullanıcının hisse senetleri
        $stocks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ypt_stocks WHERE user_id = %d ORDER BY stock_code ASC",
                $user_id
            ),
            ARRAY_A
        );
        
        // Hisse ekleme formu ve butonlar
        $output = '<div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>' . __('Hisse Senetleri', 'yatirim-portfoyu-takip') . '</h2>
                <div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStockTransactionModal">
                        <i class="fas fa-plus"></i> ' . __('Yeni İşlem', 'yatirim-portfoyu-takip') . '
                    </button>
                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addDividendModal">
                        <i class="fas fa-money-bill-wave"></i> ' . __('Temettü Ekle', 'yatirim-portfoyu-takip') . '
                    </button>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>' . __('Hisse Portföyü', 'yatirim-portfoyu-takip') . '</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="stocksTable">
                            <thead>
                                <tr>
                                    <th>' . __('Hisse Kodu', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Toplam Adet', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Ortalama Maliyet', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Güncel Fiyat', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Güncel Değer', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Kar/Zarar', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Kar/Zarar %', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Temettüler', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Son Güncelleme', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('İşlemler', 'yatirim-portfoyu-takip') . '</th>
                                </tr>
                            </thead>
                            <tbody>';
        
        if (empty($stocks)) {
            $output .= '<tr><td colspan="10" class="text-center">' . __('Henüz hisse senedi eklenmemiş', 'yatirim-portfoyu-takip') . '</td></tr>';
        } else {
            $total_value = 0;
            $total_cost = 0;
            
            foreach ($stocks as $stock) {
                // Kar/zarar hesapla
                $current_value = floatval($stock['total_shares']) * floatval($stock['current_price']);
                $total_cost_value = floatval($stock['total_shares']) * floatval($stock['average_cost']);
                $profit = $current_value - $total_cost_value;
                $profit_percentage = $total_cost_value > 0 ? ($profit / $total_cost_value) * 100 : 0;
                
                $profit_class = $profit >= 0 ? 'text-success' : 'text-danger';
                $profit_sign = $profit >= 0 ? '+' : '';
                
                // Temettüler
                $dividends = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(amount) FROM {$wpdb->prefix}ypt_dividends WHERE stock_id = %d",
                    $stock['id']
                ));
                
                $output .= '<tr>
                    <td>' . esc_html($stock['stock_code']) . '</td>
                    <td>' . number_format($stock['total_shares'], 2, ',', '.') . '</td>
                    <td>' . number_format($stock['average_cost'], 2, ',', '.') . ' ₺</td>
                    <td>' . number_format($stock['current_price'], 2, ',', '.') . ' ₺</td>
                    <td>' . number_format($current_value, 2, ',', '.') . ' ₺</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($profit, 2, ',', '.') . ' ₺</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($profit_percentage, 2, ',', '.') . '%</td>
                    <td>' . number_format($dividends ?: 0, 2, ',', '.') . ' ₺</td>
                    <td>' . ($stock['last_update'] ? date_i18n(get_option('date_format'), strtotime($stock['last_update'])) : '-') . '</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info view-stock-details" data-stock-id="' . $stock['id'] . '">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-warning edit-stock" data-stock-id="' . $stock['id'] . '">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>';
                
                $total_value += $current_value;
                $total_cost += $total_cost_value;
            }
            
            // Toplam satırı
            $total_profit = $total_value - $total_cost;
            $total_profit_percentage = $total_cost > 0 ? ($total_profit / $total_cost) * 100 : 0;
            $total_profit_class = $total_profit >= 0 ? 'text-success' : 'text-danger';
            $total_profit_sign = $total_profit >= 0 ? '+' : '';
            
            $output .= '<tr class="table-primary fw-bold">
                <td>' . __('TOPLAM', 'yatirim-portfoyu-takip') . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td>' . number_format($total_value, 2, ',', '.') . ' ₺</td>
                <td class="' . $total_profit_class . '">' . $total_profit_sign . number_format($total_profit, 2, ',', '.') . ' ₺</td>
                <td class="' . $total_profit_class . '">' . $total_profit_sign . number_format($total_profit_percentage, 2, ',', '.') . '%</td>
                <td>' . number_format($wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}ypt_dividends WHERE user_id = %d", $user_id)) ?: 0, 2, ',', '.') . ' ₺</td>
                <td></td>
                <td></td>
            </tr>';
        }
        
        $output .= '</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>';
        
        // Yeni işlem modal
        $output .= '<div class="modal fade" id="addStockTransactionModal" tabindex="-1" aria-labelledby="addStockTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStockTransactionModalLabel">' . __('Yeni Hisse İşlemi', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="stockTransactionForm">
                            <input type="hidden" name="action" value="add_stock_transaction">
                            <input type="hidden" name="user_id" value="' . $user_id . '">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="stock_code" class="form-label">' . __('Hisse Kodu', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="text" class="form-control" id="stock_code" name="stock_code" required placeholder="Örn: TUPRS">
                                    <div id="stockCodeHelp" class="form-text">' . __('BIST hisse kodunu girin.', 'yatirim-portfoyu-takip') . '</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="transaction_type" class="form-label">' . __('İşlem Türü', 'yatirim-portfoyu-takip') . '</label>
                                    <select class="form-select" id="transaction_type" name="transaction_type" required>
                                        <option value="buy">' . __('Alış', 'yatirim-portfoyu-takip') . '</option>
                                        <option value="sell">' . __('Satış', 'yatirim-portfoyu-takip') . '</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="transaction_date" class="form-label">' . __('İşlem Tarihi', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="datetime-local" class="form-control" id="transaction_date" name="transaction_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="shares" class="form-label">' . __('Adet', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="shares" name="shares" step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="price" class="form-label">' . __('Fiyat (₺)', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="commission" class="form-label">' . __('Komisyon (₺)', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="commission" name="commission" step="0.01" min="0" value="0">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="notes" class="form-label">' . __('Notlar', 'yatirim-portfoyu-takip') . '</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div id="transactionMessage"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('İptal', 'yatirim-portfoyu-takip') . '</button>
                        <button type="button" class="btn btn-primary" id="saveStockTransaction">' . __('İşlemi Kaydet', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // Temettü ekleme modal
        $output .= '<div class="modal fade" id="addDividendModal" tabindex="-1" aria-labelledby="addDividendModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDividendModalLabel">' . __('Temettü Ekle', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="dividendForm">
                            <input type="hidden" name="action" value="add_dividend">
                            <input type="hidden" name="user_id" value="' . $user_id . '">
                            
                            <div class="mb-3">
                                <label for="stock_id" class="form-label">' . __('Hisse Seçin', 'yatirim-portfoyu-takip') . '</label>
                                <select class="form-select" id="stock_id" name="stock_id" required>
                                    <option value="">' . __('Hisse seçin', 'yatirim-portfoyu-takip') . '</option>';
        
        // Portföydeki hisseleri listele
        foreach ($stocks as $stock) {
            $output .= '<option value="' . $stock['id'] . '">' . esc_html($stock['stock_code']) . '</option>';
        }
        
        $output .= '</select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="dividend_date" class="form-label">' . __('Temettü Tarihi', 'yatirim-portfoyu-takip') . '</label>
                                <input type="date" class="form-control" id="dividend_date" name="dividend_date" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="amount" class="form-label">' . __('Temettü Tutarı (₺)', 'yatirim-portfoyu-takip') . '</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="dividend_notes" class="form-label">' . __('Notlar', 'yatirim-portfoyu-takip') . '</label>
                                <textarea class="form-control" id="dividend_notes" name="notes" rows="3"></textarea>
                            </div>
                            
                            <div id="dividendMessage"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('İptal', 'yatirim-portfoyu-takip') . '</button>
                        <button type="button" class="btn btn-primary" id="saveDividend">' . __('Temettü Ekle', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // Hisse detayları modal
        $output .= '<div class="modal fade" id="stockDetailsModal" tabindex="-1" aria-labelledby="stockDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="stockDetailsModalLabel">' . __('Hisse Detayları', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="stockDetailsContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">' . __('Yükleniyor...', 'yatirim-portfoyu-takip') . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('Kapat', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // Hisse düzenleme modal
        $output .= '<div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStockModalLabel">' . __('Hisse Düzenle', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editStockForm">
                            <input type="hidden" name="action" value="edit_stock">
                            <input type="hidden" name="stock_id" id="edit_stock_id">
                            
                            <div class="mb-3">
                                <label for="edit_stock_code" class="form-label">' . __('Hisse Kodu', 'yatirim-portfoyu-takip') . '</label>
                                <input type="text" class="form-control" id="edit_stock_code" name="stock_code" required readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_current_price" class="form-label">' . __('Güncel Fiyat (₺)', 'yatirim-portfoyu-takip') . '</label>
                                <input type="number" class="form-control" id="edit_current_price" name="current_price" step="0.01" min="0" required>
                                <div class="form-text">' . __('Fiyatı manuel olarak düzenleyebilirsiniz.', 'yatirim-portfoyu-takip') . '</div>
                            </div>
                            
                            <div id="editStockMessage"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('İptal', 'yatirim-portfoyu-takip') . '</button>
                        <button type="button" class="btn btn-primary" id="saveEditStock">' . __('Kaydet', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // JS kodları ekle
        $output .= '<script>
        jQuery(document).ready(function($) {
            // İşlem tarih alanını bugün olarak ayarla
            $("#transaction_date").val(new Date().toISOString().substr(0, 16));
            $("#dividend_date").val(new Date().toISOString().substr(0, 10));
            
            // Hisse işlemi kaydetme
            $("#saveStockTransaction").on("click", function() {
                var formData = $("#stockTransactionForm").serialize();
                formData += "&nonce=" + yatirim_portfoyu.nonce;
                
                // Kullanıcı premium değilse ve sınıra ulaştıysa kontrol et
                ' . ($this->users->is_premium($user_id) ? '' : '
                if (' . $this->db->get_user_instrument_count($user_id) . ' >= 5) {
                    $("#transactionMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.free_limit_reached + "</div>");
                    return;
                }
                ') . '
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#transactionMessage").html("<div class=\"alert alert-info\">" + yatirim_portfoyu.texts.loading + "</div>");
                        $("#saveStockTransaction").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#transactionMessage").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $("#transactionMessage").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                            $("#saveStockTransaction").prop("disabled", false);
                        }
                    },
                    error: function() {
                        $("#transactionMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.error + "</div>");
                        $("#saveStockTransaction").prop("disabled", false);
                    }
                });
            });
            
            // Temettü ekleme
            $("#saveDividend").on("click", function() {
                var formData = $("#dividendForm").serialize();
                formData += "&nonce=" + yatirim_portfoyu.nonce;
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#dividendMessage").html("<div class=\"alert alert-info\">" + yatirim_portfoyu.texts.loading + "</div>");
                        $("#saveDividend").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#dividendMessage").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $("#dividendMessage").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                            $("#saveDividend").prop("disabled", false);
                        }
                    },
                    error: function() {
                        $("#dividendMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.error + "</div>");
                        $("#saveDividend").prop("disabled", false);
                    }
                });
            });
            
            // Hisse detayları görüntüleme
            $(".view-stock-details").on("click", function() {
                var stockId = $(this).data("stock-id");
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: {
                        action: "get_stock_details",
                        stock_id: stockId,
                        nonce: yatirim_portfoyu.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#stockDetailsContent").html(response.data.html);
                            $("#stockDetailsModal").modal("show");
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        alert(yatirim_portfoyu.texts.error);
                    }
                });
            });
            
            // Hisse düzenleme
            $(".edit-stock").on("click", function() {
                var stockId = $(this).data("stock-id");
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: {
                        action: "get_stock_for_edit",
                        stock_id: stockId,
                        nonce: yatirim_portfoyu.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#edit_stock_id").val(response.data.id);
                            $("#edit_stock_code").val(response.data.stock_code);
                            $("#edit_current_price").val(response.data.current_price);
                            $("#editStockModal").modal("show");
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        alert(yatirim_portfoyu.texts.error);
                    }
                });
            });
            
            // Hisse düzenleme kaydet
            $("#saveEditStock").on("click", function() {
                var formData = $("#editStockForm").serialize();
                formData += "&nonce=" + yatirim_portfoyu.nonce;
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#editStockMessage").html("<div class=\"alert alert-info\">" + yatirim_portfoyu.texts.loading + "</div>");
                        $("#saveEditStock").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#editStockMessage").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $("#editStockMessage").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                            $("#saveEditStock").prop("disabled", false);
                        }
                    },
                    error: function() {
                        $("#editStockMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.error + "</div>");
                        $("#saveEditStock").prop("disabled", false);
                    }
                });
            });
        });
        </script>';
        
        return $output;
    }

    /**
     * Kripto para görünümü
     *
     * @param int $user_id Kullanıcı ID
     * @return string HTML içeriği
     */
    private function get_crypto_view($user_id) {
        global $wpdb;
        
        // Kullanıcının kripto paraları
        $cryptos = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ypt_crypto WHERE user_id = %d ORDER BY crypto_code ASC",
                $user_id
            ),
            ARRAY_A
        );
        
        // Kripto ekleme formu ve butonlar
        $output = '<div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>' . __('Kripto Paralar', 'yatirim-portfoyu-takip') . '</h2>
                <div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCryptoTransactionModal">
                        <i class="fas fa-plus"></i> ' . __('Yeni İşlem', 'yatirim-portfoyu-takip') . '
                    </button>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>' . __('Kripto Portföyü', 'yatirim-portfoyu-takip') . '</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="cryptoTable">
                            <thead>
                                <tr>
                                    <th>' . __('Kripto Kodu', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Toplam Miktar', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Ortalama Maliyet (₺)', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Güncel Fiyat (₺)', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Güncel Değer (₺)', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Kar/Zarar (₺)', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Kar/Zarar %', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Son Güncelleme', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('İşlemler', 'yatirim-portfoyu-takip') . '</th>
                                </tr>
                            </thead>
                            <tbody>';
        
        if (empty($cryptos)) {
            $output .= '<tr><td colspan="9" class="text-center">' . __('Henüz kripto para eklenmemiş', 'yatirim-portfoyu-takip') . '</td></tr>';
        } else {
            $total_value = 0;
            $total_cost = 0;
            
            foreach ($cryptos as $crypto) {
                // Kar/zarar hesapla
                $current_value = floatval($crypto['total_amount']) * floatval($crypto['current_price']);
                $total_cost_value = floatval($crypto['total_amount']) * floatval($crypto['average_cost']);
                $profit = $current_value - $total_cost_value;
                $profit_percentage = $total_cost_value > 0 ? ($profit / $total_cost_value) * 100 : 0;
                
                $profit_class = $profit >= 0 ? 'text-success' : 'text-danger';
                $profit_sign = $profit >= 0 ? '+' : '';
                
                $output .= '<tr>
                    <td>' . esc_html($crypto['crypto_code']) . '</td>
                    <td>' . number_format($crypto['total_amount'], 8, ',', '.') . '</td>
                    <td>' . number_format($crypto['average_cost'], 2, ',', '.') . ' ₺</td>
                    <td>' . number_format($crypto['current_price'], 2, ',', '.') . ' ₺</td>
                    <td>' . number_format($current_value, 2, ',', '.') . ' ₺</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($profit, 2, ',', '.') . ' ₺</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($profit_percentage, 2, ',', '.') . '%</td>
                    <td>' . ($crypto['last_update'] ? date_i18n(get_option('date_format'), strtotime($crypto['last_update'])) : '-') . '</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info view-crypto-details" data-crypto-id="' . $crypto['id'] . '">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-warning edit-crypto" data-crypto-id="' . $crypto['id'] . '">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>';
                
                $total_value += $current_value;
                $total_cost += $total_cost_value;
            }
            
            // Toplam satırı
            $total_profit = $total_value - $total_cost;
            $total_profit_percentage = $total_cost > 0 ? ($total_profit / $total_cost) * 100 : 0;
            $total_profit_class = $total_profit >= 0 ? 'text-success' : 'text-danger';
            $total_profit_sign = $total_profit >= 0 ? '+' : '';
            
            $output .= '<tr class="table-primary fw-bold">
                <td>' . __('TOPLAM', 'yatirim-portfoyu-takip') . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td>' . number_format($total_value, 2, ',', '.') . ' ₺</td>
                <td class="' . $total_profit_class . '">' . $total_profit_sign . number_format($total_profit, 2, ',', '.') . ' ₺</td>
                <td class="' . $total_profit_class . '">' . $total_profit_sign . number_format($total_profit_percentage, 2, ',', '.') . '%</td>
                <td></td>
                <td></td>
            </tr>';
        }
        
        $output .= '</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>';
        
        // Yeni işlem modal
        $output .= '<div class="modal fade" id="addCryptoTransactionModal" tabindex="-1" aria-labelledby="addCryptoTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCryptoTransactionModalLabel">' . __('Yeni Kripto İşlemi', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="cryptoTransactionForm">
                            <input type="hidden" name="action" value="add_crypto_transaction">
                            <input type="hidden" name="user_id" value="' . $user_id . '">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="crypto_code" class="form-label">' . __('Kripto Kodu', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="text" class="form-control" id="crypto_code" name="crypto_code" required placeholder="Örn: BTC">
                                    <div id="cryptoCodeHelp" class="form-text">' . __('Kripto para kodunu girin (örn: BTC, ETH, SOL).', 'yatirim-portfoyu-takip') . '</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="crypto_transaction_type" class="form-label">' . __('İşlem Türü', 'yatirim-portfoyu-takip') . '</label>
                                    <select class="form-select" id="crypto_transaction_type" name="transaction_type" required>
                                        <option value="buy">' . __('Alış', 'yatirim-portfoyu-takip') . '</option>
                                        <option value="sell">' . __('Satış', 'yatirim-portfoyu-takip') . '</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="crypto_transaction_date" class="form-label">' . __('İşlem Tarihi', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="datetime-local" class="form-control" id="crypto_transaction_date" name="transaction_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="crypto_amount" class="form-label">' . __('Miktar', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="crypto_amount" name="amount" step="0.00000001" min="0" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="crypto_price" class="form-label">' . __('Fiyat (₺)', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="crypto_price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="crypto_commission" class="form-label">' . __('Komisyon (₺)', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="crypto_commission" name="commission" step="0.01" min="0" value="0">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="crypto_notes" class="form-label">' . __('Notlar', 'yatirim-portfoyu-takip') . '</label>
                                    <textarea class="form-control" id="crypto_notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div id="cryptoTransactionMessage"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('İptal', 'yatirim-portfoyu-takip') . '</button>
                        <button type="button" class="btn btn-primary" id="saveCryptoTransaction">' . __('İşlemi Kaydet', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // Kripto detayları modal
        $output .= '<div class="modal fade" id="cryptoDetailsModal" tabindex="-1" aria-labelledby="cryptoDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cryptoDetailsModalLabel">' . __('Kripto Detayları', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="cryptoDetailsContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">' . __('Yükleniyor...', 'yatirim-portfoyu-takip') . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('Kapat', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // Kripto düzenleme modal
        $output .= '<div class="modal fade" id="editCryptoModal" tabindex="-1" aria-labelledby="editCryptoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCryptoModalLabel">' . __('Kripto Düzenle', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editCryptoForm">
                            <input type="hidden" name="action" value="edit_crypto">
                            <input type="hidden" name="crypto_id" id="edit_crypto_id">
                            
                            <div class="mb-3">
                                <label for="edit_crypto_code" class="form-label">' . __('Kripto Kodu', 'yatirim-portfoyu-takip') . '</label>
                                <input type="text" class="form-control" id="edit_crypto_code" name="crypto_code" required readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_crypto_current_price" class="form-label">' . __('Güncel Fiyat (₺)', 'yatirim-portfoyu-takip') . '</label>
                                <input type="number" class="form-control" id="edit_crypto_current_price" name="current_price" step="0.01" min="0" required>
                                <div class="form-text">' . __('Fiyatı manuel olarak düzenleyebilirsiniz.', 'yatirim-portfoyu-takip') . '</div>
                            </div>
                            
                            <div id="editCryptoMessage"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('İptal', 'yatirim-portfoyu-takip') . '</button>
                        <button type="button" class="btn btn-primary" id="saveEditCrypto">' . __('Kaydet', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // JS kodları ekle
        $output .= '<script>
        jQuery(document).ready(function($) {
            // İşlem tarih alanını bugün olarak ayarla
            $("#crypto_transaction_date").val(new Date().toISOString().substr(0, 16));
            
            // Kripto işlemi kaydetme
            $("#saveCryptoTransaction").on("click", function() {
                var formData = $("#cryptoTransactionForm").serialize();
                formData += "&nonce=" + yatirim_portfoyu.nonce;
                
                // Kullanıcı premium değilse ve sınıra ulaştıysa kontrol et
                ' . ($this->users->is_premium($user_id) ? '' : '
                if (' . $this->db->get_user_instrument_count($user_id) . ' >= 5) {
                    $("#cryptoTransactionMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.free_limit_reached + "</div>");
                    return;
                }
                ') . '
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#cryptoTransactionMessage").html("<div class=\"alert alert-info\">" + yatirim_portfoyu.texts.loading + "</div>");
                        $("#saveCryptoTransaction").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#cryptoTransactionMessage").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $("#cryptoTransactionMessage").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                            $("#saveCryptoTransaction").prop("disabled", false);
                        }
                    },
                    error: function() {
                        $("#cryptoTransactionMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.error + "</div>");
                        $("#saveCryptoTransaction").prop("disabled", false);
                    }
                });
            });
            
            // Kripto detayları görüntüleme
            $(".view-crypto-details").on("click", function() {
                var cryptoId = $(this).data("crypto-id");
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: {
                        action: "get_crypto_details",
                        crypto_id: cryptoId,
                        nonce: yatirim_portfoyu.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#cryptoDetailsContent").html(response.data.html);
                            $("#cryptoDetailsModal").modal("show");
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        alert(yatirim_portfoyu.texts.error);
                    }
                });
            });
            
            // Kripto düzenleme
            $(".edit-crypto").on("click", function() {
                var cryptoId = $(this).data("crypto-id");
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: {
                        action: "get_crypto_for_edit",
                        crypto_id: cryptoId,
                        nonce: yatirim_portfoyu.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#edit_crypto_id").val(response.data.id);
                            $("#edit_crypto_code").val(response.data.crypto_code);
                            $("#edit_crypto_current_price").val(response.data.current_price);
                            $("#editCryptoModal").modal("show");
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        alert(yatirim_portfoyu.texts.error);
                    }
                });
            });
            
            // Kripto düzenleme kaydet
            $("#saveEditCrypto").on("click", function() {
                var formData = $("#editCryptoForm").serialize();
                formData += "&nonce=" + yatirim_portfoyu.nonce;
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#editCryptoMessage").html("<div class=\"alert alert-info\">" + yatirim_portfoyu.texts.loading + "</div>");
                        $("#saveEditCrypto").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#editCryptoMessage").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $("#editCryptoMessage").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                            $("#saveEditCrypto").prop("disabled", false);
                        }
                    },
                    error: function() {
                        $("#editCryptoMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.error + "</div>");
                        $("#saveEditCrypto").prop("disabled", false);
                    }
                });
            });
        });
        </script>';
        
        return $output;
    }

    /**
     * Altın görünümü
     *
     * @param int $user_id Kullanıcı ID
     * @return string HTML içeriği
     */
    private function get_gold_view($user_id) {
        global $wpdb;
        
        // Kullanıcının altınları
        $gold_items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ypt_gold WHERE user_id = %d ORDER BY gold_type ASC",
                $user_id
            ),
            ARRAY_A
        );
        
        // Altın tipleri
        $gold_types = array(
            'gram' => __('Gram Altın', 'yatirim-portfoyu-takip'),
            'ceyrek' => __('Çeyrek Altın', 'yatirim-portfoyu-takip'),
            'yarim' => __('Yarım Altın', 'yatirim-portfoyu-takip'),
            'tam' => __('Tam Altın', 'yatirim-portfoyu-takip'),
            'cumhuriyet' => __('Cumhuriyet Altını', 'yatirim-portfoyu-takip'),
            'ata' => __('Ata Altın', 'yatirim-portfoyu-takip'),
            'resat' => __('Reşat Altın', 'yatirim-portfoyu-takip'),
            'hamit' => __('Hamit Altın', 'yatirim-portfoyu-takip'),
            'ons' => __('Ons Altın', 'yatirim-portfoyu-takip'),
            'gumus' => __('Gümüş', 'yatirim-portfoyu-takip')
        );
        
        // Altın ekleme formu ve butonlar
        $output = '<div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>' . __('Altın', 'yatirim-portfoyu-takip') . '</h2>
                <div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addGoldTransactionModal">
                        <i class="fas fa-plus"></i> ' . __('Yeni İşlem', 'yatirim-portfoyu-takip') . '
                    </button>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>' . __('Altın Portföyü', 'yatirim-portfoyu-takip') . '</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="goldTable">
                            <thead>
                                <tr>
                                    <th>' . __('Altın Türü', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Toplam Ağırlık', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Ortalama Maliyet (₺/gr)', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Güncel Fiyat (₺/gr)', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Güncel Değer (₺)', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Kar/Zarar (₺)', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Kar/Zarar %', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('Son Güncelleme', 'yatirim-portfoyu-takip') . '</th>
                                    <th>' . __('İşlemler', 'yatirim-portfoyu-takip') . '</th>
                                </tr>
                            </thead>
                            <tbody>';
        
        if (empty($gold_items)) {
            $output .= '<tr><td colspan="9" class="text-center">' . __('Henüz altın eklenmemiş', 'yatirim-portfoyu-takip') . '</td></tr>';
        } else {
            $total_value = 0;
            $total_cost = 0;
            
            foreach ($gold_items as $gold) {
                // Altın türü adı
                $gold_type_name = isset($gold_types[$gold['gold_type']]) ? $gold_types[$gold['gold_type']] : $gold['gold_type'];
                
                // Kar/zarar hesapla
                $current_value = floatval($gold['total_weight']) * floatval($gold['current_price']);
                $total_cost_value = floatval($gold['total_weight']) * floatval($gold['average_cost']);
                $profit = $current_value - $total_cost_value;
                $profit_percentage = $total_cost_value > 0 ? ($profit / $total_cost_value) * 100 : 0;
                
                $profit_class = $profit >= 0 ? 'text-success' : 'text-danger';
                $profit_sign = $profit >= 0 ? '+' : '';
                
                $output .= '<tr>
                    <td>' . $gold_type_name . '</td>
                    <td>' . number_format($gold['total_weight'], 2, ',', '.') . ' gr</td>
                    <td>' . number_format($gold['average_cost'], 2, ',', '.') . ' ₺</td>
                    <td>' . number_format($gold['current_price'], 2, ',', '.') . ' ₺</td>
                    <td>' . number_format($current_value, 2, ',', '.') . ' ₺</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($profit, 2, ',', '.') . ' ₺</td>
                    <td class="' . $profit_class . '">' . $profit_sign . number_format($profit_percentage, 2, ',', '.') . '%</td>
                    <td>' . ($gold['last_update'] ? date_i18n(get_option('date_format'), strtotime($gold['last_update'])) : '-') . '</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info view-gold-details" data-gold-id="' . $gold['id'] . '">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-warning edit-gold" data-gold-id="' . $gold['id'] . '">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>';
                
                $total_value += $current_value;
                $total_cost += $total_cost_value;
            }
            
            // Toplam satırı
            $total_profit = $total_value - $total_cost;
            $total_profit_percentage = $total_cost > 0 ? ($total_profit / $total_cost) * 100 : 0;
            $total_profit_class = $total_profit >= 0 ? 'text-success' : 'text-danger';
            $total_profit_sign = $total_profit >= 0 ? '+' : '';
            
            $output .= '<tr class="table-primary fw-bold">
                <td>' . __('TOPLAM', 'yatirim-portfoyu-takip') . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td>' . number_format($total_value, 2, ',', '.') . ' ₺</td>
                <td class="' . $total_profit_class . '">' . $total_profit_sign . number_format($total_profit, 2, ',', '.') . ' ₺</td>
                <td class="' . $total_profit_class . '">' . $total_profit_sign . number_format($total_profit_percentage, 2, ',', '.') . '%</td>
                <td></td>
                <td></td>
            </tr>';
        }
        
        $output .= '</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>';
        
        // Yeni işlem modal
        $output .= '<div class="modal fade" id="addGoldTransactionModal" tabindex="-1" aria-labelledby="addGoldTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGoldTransactionModalLabel">' . __('Yeni Altın İşlemi', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="goldTransactionForm">
                            <input type="hidden" name="action" value="add_gold_transaction">
                            <input type="hidden" name="user_id" value="' . $user_id . '">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="gold_type" class="form-label">' . __('Altın Türü', 'yatirim-portfoyu-takip') . '</label>
                                    <select class="form-select" id="gold_type" name="gold_type" required>';
        
        foreach ($gold_types as $type_code => $type_name) {
            $output .= '<option value="' . $type_code . '">' . $type_name . '</option>';
        }
        
        $output .= '</select>
                                </div>
                                <div class="col-md-6">
                                    <label for="gold_transaction_type" class="form-label">' . __('İşlem Türü', 'yatirim-portfoyu-takip') . '</label>
                                    <select class="form-select" id="gold_transaction_type" name="transaction_type" required>
                                        <option value="buy">' . __('Alış', 'yatirim-portfoyu-takip') . '</option>
                                        <option value="sell">' . __('Satış', 'yatirim-portfoyu-takip') . '</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="gold_transaction_date" class="form-label">' . __('İşlem Tarihi', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="datetime-local" class="form-control" id="gold_transaction_date" name="transaction_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="gold_weight" class="form-label">' . __('Ağırlık (gram)', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="gold_weight" name="weight" step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="gold_price" class="form-label">' . __('Fiyat (₺/gr)', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="gold_price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="gold_total" class="form-label">' . __('Toplam Tutar (₺)', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="number" class="form-control" id="gold_total" step="0.01" min="0" readonly>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="gold_notes" class="form-label">' . __('Notlar', 'yatirim-portfoyu-takip') . '</label>
                                    <textarea class="form-control" id="gold_notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div id="goldTransactionMessage"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('İptal', 'yatirim-portfoyu-takip') . '</button>
                        <button type="button" class="btn btn-primary" id="saveGoldTransaction">' . __('İşlemi Kaydet', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // Altın detayları modal
        $output .= '<div class="modal fade" id="goldDetailsModal" tabindex="-1" aria-labelledby="goldDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="goldDetailsModalLabel">' . __('Altın Detayları', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="goldDetailsContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">' . __('Yükleniyor...', 'yatirim-portfoyu-takip') . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('Kapat', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // Altın düzenleme modal
        $output .= '<div class="modal fade" id="editGoldModal" tabindex="-1" aria-labelledby="editGoldModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editGoldModalLabel">' . __('Altın Düzenle', 'yatirim-portfoyu-takip') . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editGoldForm">
                            <input type="hidden" name="action" value="edit_gold">
                            <input type="hidden" name="gold_id" id="edit_gold_id">
                            
                            <div class="mb-3">
                                <label for="edit_gold_type" class="form-label">' . __('Altın Türü', 'yatirim-portfoyu-takip') . '</label>
                                <input type="text" class="form-control" id="edit_gold_type_display" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_gold_current_price" class="form-label">' . __('Güncel Fiyat (₺/gr)', 'yatirim-portfoyu-takip') . '</label>
                                <input type="number" class="form-control" id="edit_gold_current_price" name="current_price" step="0.01" min="0" required>
                                <div class="form-text">' . __('Fiyatı manuel olarak düzenleyebilirsiniz.', 'yatirim-portfoyu-takip') . '</div>
                            </div>
                            
                            <div id="editGoldMessage"></div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . __('İptal', 'yatirim-portfoyu-takip') . '</button>
                        <button type="button" class="btn btn-primary" id="saveEditGold">' . __('Kaydet', 'yatirim-portfoyu-takip') . '</button>
                    </div>
                </div>
            </div>
        </div>';
        
        // JS kodları ekle
        $output .= '<script>
        jQuery(document).ready(function($) {
            // İşlem tarih alanını bugün olarak ayarla
            $("#gold_transaction_date").val(new Date().toISOString().substr(0, 16));
            
            // Toplam tutar hesaplama
            $("#gold_weight, #gold_price").on("change keyup", function() {
                var weight = parseFloat($("#gold_weight").val()) || 0;
                var price = parseFloat($("#gold_price").val()) || 0;
                $("#gold_total").val((weight * price).toFixed(2));
            });
            
            // Altın işlemi kaydetme
            $("#saveGoldTransaction").on("click", function() {
                var formData = $("#goldTransactionForm").serialize();
                formData += "&nonce=" + yatirim_portfoyu.nonce;
                
                // Kullanıcı premium değilse ve sınıra ulaştıysa kontrol et
                ' . ($this->users->is_premium($user_id) ? '' : '
                if (' . $this->db->get_user_instrument_count($user_id) . ' >= 5) {
                    $("#goldTransactionMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.free_limit_reached + "</div>");
                    return;
                }
                ') . '
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#goldTransactionMessage").html("<div class=\"alert alert-info\">" + yatirim_portfoyu.texts.loading + "</div>");
                        $("#saveGoldTransaction").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#goldTransactionMessage").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $("#goldTransactionMessage").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                            $("#saveGoldTransaction").prop("disabled", false);
                        }
                    },
                    error: function() {
                        $("#goldTransactionMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.error + "</div>");
                        $("#saveGoldTransaction").prop("disabled", false);
                    }
                });
            });
            
            // Altın detayları görüntüleme
            $(".view-gold-details").on("click", function() {
                var goldId = $(this).data("gold-id");
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: {
                        action: "get_gold_details",
                        gold_id: goldId,
                        nonce: yatirim_portfoyu.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#goldDetailsContent").html(response.data.html);
                            $("#goldDetailsModal").modal("show");
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        alert(yatirim_portfoyu.texts.error);
                    }
                });
            });
            
            // Altın düzenleme
            $(".edit-gold").on("click", function() {
                var goldId = $(this).data("gold-id");
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: {
                        action: "get_gold_for_edit",
                        gold_id: goldId,
                        nonce: yatirim_portfoyu.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#edit_gold_id").val(response.data.id);
                            
                            // Altın türü adını göster
                            var goldTypes = ' . json_encode($gold_types) . ';
                            var goldTypeDisplay = goldTypes[response.data.gold_type] || response.data.gold_type;
                            $("#edit_gold_type_display").val(goldTypeDisplay);
                            
                            $("#edit_gold_current_price").val(response.data.current_price);
                            $("#editGoldModal").modal("show");
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        alert(yatirim_portfoyu.texts.error);
                    }
                });
            });
            
            // Altın düzenleme kaydet
            $("#saveEditGold").on("click", function() {
                var formData = $("#editGoldForm").serialize();
                formData += "&nonce=" + yatirim_portfoyu.nonce;
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#editGoldMessage").html("<div class=\"alert alert-info\">" + yatirim_portfoyu.texts.loading + "</div>");
                        $("#saveEditGold").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#editGoldMessage").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $("#editGoldMessage").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                            $("#saveEditGold").prop("disabled", false);
                        }
                    },
                    error: function() {
                        $("#editGoldMessage").html("<div class=\"alert alert-danger\">" + yatirim_portfoyu.texts.error + "</div>");
                        $("#saveEditGold").prop("disabled", false);
                    }
                });
            });
        });
        </script>';
        
        return $output;
    }

    /**
     * Login shortcode
     *
     * @param array $atts Shortcode parametreleri
     * @return string HTML içeriği
     */
    public function display_login_shortcode($atts) {
        // Kullanıcı zaten giriş yapmış mı kontrol et
        $current_user_id = $this->users->get_current_user_id();
        if ($current_user_id) {
            // Kullanıcı giriş yapmış, portföy sayfasına yönlendir
            $portfolio_page = isset($atts['redirect']) ? $atts['redirect'] : '';
            if (empty($portfolio_page)) {
                $portfolio_page = home_url();
            }
            wp_redirect($portfolio_page);
            exit;
        }
        
        return $this->get_login_form();
    }

    /**
     * Register shortcode
     *
     * @param array $atts Shortcode parametreleri
     * @return string HTML içeriği
     */
    public function display_register_shortcode($atts) {
        // Kullanıcı zaten giriş yapmış mı kontrol et
        $current_user_id = $this->users->get_current_user_id();
        if ($current_user_id) {
            // Kullanıcı giriş yapmış, portföy sayfasına yönlendir
            $portfolio_page = isset($atts['redirect']) ? $atts['redirect'] : '';
            if (empty($portfolio_page)) {
                $portfolio_page = home_url();
            }
            wp_redirect($portfolio_page);
            exit;
        }
        
        // Kayıt formunu göster
        $output = '<div class="yatirim-portfoyu-register-container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h4 class="m-0">' . __('Üyelik Oluştur', 'yatirim-portfoyu-takip') . '</h4>
                        </div>
                        <div class="card-body">
                            <form id="yatirim-portfoyu-register-form">
                                <div class="mb-3">
                                    <label for="reg_username" class="form-label">' . __('Kullanıcı Adı', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="text" class="form-control" id="reg_username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_email" class="form-label">' . __('E-posta', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="email" class="form-control" id="reg_email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_password" class="form-label">' . __('Parola', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="password" class="form-control" id="reg_password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reg_password_confirm" class="form-label">' . __('Parola Tekrar', 'yatirim-portfoyu-takip') . '</label>
                                    <input type="password" class="form-control" id="reg_password_confirm" name="password_confirm" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms_agree" name="terms_agree" required>
                                    <label class="form-check-label" for="terms_agree">' . __('Kullanım şartlarını kabul ediyorum', 'yatirim-portfoyu-takip') . '</label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">' . __('Kayıt Ol', 'yatirim-portfoyu-takip') . '</button>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="' . esc_url(isset($atts['login_url']) ? $atts['login_url'] : '') . '">' . __('Zaten üye misiniz? Giriş yapın', 'yatirim-portfoyu-takip') . '</a>
                                </div>
                                <div id="register-message" class="mt-3"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $("#yatirim-portfoyu-register-form").on("submit", function(e) {
                e.preventDefault();
                
                // Parolalar eşleşiyor mu kontrol et
                var password = $("#reg_password").val();
                var passwordConfirm = $("#reg_password_confirm").val();
                
                if (password !== passwordConfirm) {
                    $("#register-message").html("<div class=\"alert alert-danger\">' . __('Parolalar eşleşmiyor.', 'yatirim-portfoyu-takip') . '</div>");
                    return;
                }
                
                var formData = $(this).serialize();
                formData += "&action=yatirim_portfoyu_register_action&nonce=" + yatirim_portfoyu.nonce;
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#register-message").html("<div class=\"alert alert-info\">' . __('Kayıt işlemi yapılıyor...', 'yatirim-portfoyu-takip') . '</div>");
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#register-message").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.href = "' . esc_url(isset($atts['redirect']) ? $atts['redirect'] : home_url()) . '";
                            }, 2000);
                        } else {
                            $("#register-message").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                        }
                    },
                    error: function() {
                        $("#register-message").html("<div class=\"alert alert-danger\">' . __('Bir hata oluştu, lütfen tekrar deneyin.', 'yatirim-portfoyu-takip') . '</div>");
                    }
                });
            });
        });
        </script>';
        
        return $output;
    }

    /**
     * AJAX giriş işlemleri
     */
    public function handle_login_ajax() {
        check_ajax_referer('yatirim_portfoyu_nonce', 'nonce');
        
        $username_or_email = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        $result = $this->users->login_user($username_or_email, $password);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array(
                'message' => __('Giriş başarılı, yönlendiriliyorsunuz...', 'yatirim-portfoyu-takip'),
                'user_id' => $result['id']
            ));
        }
        
        wp_die();
    }

    /**
     * AJAX kayıt işlemleri
     */
    public function handle_register_ajax() {
        check_ajax_referer('yatirim_portfoyu_nonce', 'nonce');
        
        $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
        $terms_agree = isset($_POST['terms_agree']) && $_POST['terms_agree'] === 'on';
        
        // Validasyonlar
        if (empty($username) || empty($email) || empty($password)) {
            wp_send_json_error(array('message' => __('Lütfen tüm alanları doldurun.', 'yatirim-portfoyu-takip')));
            wp_die();
        }
        
        if ($password !== $password_confirm) {
            wp_send_json_error(array('message' => __('Parolalar eşleşmiyor.', 'yatirim-portfoyu-takip')));
            wp_die();
        }
        
        if (!$terms_agree) {
            wp_send_json_error(array('message' => __('Kullanım şartlarını kabul etmelisiniz.', 'yatirim-portfoyu-takip')));
            wp_die();
        }
        
        // Yeni kullanıcı oluştur
        $result = $this->users->register_user(array(
            'username' => $username,
            'email' => $email,
            'password' => $password
        ));
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            // Otomatik giriş yap
            $this->users->login_user($username, $password);
            
            wp_send_json_success(array(
                'message' => __('Üyeliğiniz başarıyla oluşturuldu, yönlendiriliyorsunuz...', 'yatirim-portfoyu-takip'),
                'user_id' => $result['id']
            ));
        }
        
        wp_die();
    }

    /**
     * AJAX işlemleri
     */
    public function handle_ajax() {
        check_ajax_referer('yatirim_portfoyu_nonce', 'nonce');
        
        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        
        switch ($action) {
            case 'add_stock_transaction':
                $this->handle_add_stock_transaction();
                break;
                
            case 'add_dividend':
                $this->handle_add_dividend();
                break;
                
            case 'get_stock_details':
                $this->handle_get_stock_details();
                break;
                
            case 'get_stock_for_edit':
                $this->handle_get_stock_for_edit();
                break;
                
            case 'edit_stock':
                $this->handle_edit_stock();
                break;
                
            case 'add_crypto_transaction':
                $this->handle_add_crypto_transaction();
                break;
                
            case 'get_crypto_details':
                $this->handle_get_crypto_details();
                break;
                
            case 'get_crypto_for_edit':
                $this->handle_get_crypto_for_edit();
                break;
                
            case 'edit_crypto':
                $this->handle_edit_crypto();
                break;
                
            case 'add_gold_transaction':
                $this->handle_add_gold_transaction();
                break;
                
            case 'get_gold_details':
                $this->handle_get_gold_details();
                break;
                
            case 'get_gold_for_edit':
                $this->handle_get_gold_for_edit();
                break;
                
            case 'edit_gold':
                $this->handle_edit_gold();
                break;
                
            default:
                wp_send_json_error(array('message' => __('Geçersiz işlem.', 'yatirim-portfoyu-takip')));
                break;
        }
        
        wp_die();
    }
}

