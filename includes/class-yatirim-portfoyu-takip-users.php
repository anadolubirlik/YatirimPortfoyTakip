<?php
/**
 * Kullanıcı yönetimi
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_Users {

    /**
     * Veritabanı sınıfı
     *
     * @var Yatirim_Portfoyu_Takip_DB
     */
    private $db;

    /**
     * Yapılandırıcı
     */
    public function __construct() {
        $this->db = new Yatirim_Portfoyu_Takip_DB();
    }

    /**
     * Yeni kullanıcı kaydeder
     *
     * @param array $user_data Kullanıcı bilgileri
     * @return array|WP_Error Başarılı ise kullanıcı bilgileri, değilse hata
     */
    public function register_user($user_data) {
        // Gerekli alanlar kontrol edilir
        if (empty($user_data['username']) || empty($user_data['email']) || empty($user_data['password'])) {
            return new WP_Error('missing_fields', __('Lütfen tüm alanları doldurun.', 'yatirim-portfoyu-takip'));
        }
        
        // Email geçerliliği kontrol edilir
        if (!is_email($user_data['email'])) {
            return new WP_Error('invalid_email', __('Geçerli bir e-posta adresi girin.', 'yatirim-portfoyu-takip'));
        }
        
        // Kullanıcı adı ve email benzersiz mi kontrol edilir
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_users';
        
        $existing_user = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE username = %s OR email = %s",
                $user_data['username'],
                $user_data['email']
            )
        );
        
        if ($existing_user) {
            return new WP_Error('user_exists', __('Bu kullanıcı adı veya e-posta adresi zaten kayıtlı.', 'yatirim-portfoyu-takip'));
        }
        
        // Kullanıcıyı kaydet
        $user_id = $this->db->add_user($user_data);
        
        if (!$user_id) {
            return new WP_Error('registration_failed', __('Kayıt işlemi başarısız oldu, lütfen tekrar deneyin.', 'yatirim-portfoyu-takip'));
        }
        
        // Otomatik portföy oluştur
        $portfolio_data = array(
            'user_id' => $user_id,
            'name' => __('Ana Portföy', 'yatirim-portfoyu-takip'),
            'description' => __('Otomatik oluşturulan portföy', 'yatirim-portfoyu-takip'),
            'created_at' => current_time('mysql')
        );
        
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'ypt_portfolio', $portfolio_data);
        
        // Başarılı kayıt
        $user_data['id'] = $user_id;
        unset($user_data['password']);
        
        return $user_data;
    }

    /**
     * Kullanıcı girişi yapar
     *
     * @param string $username_or_email Kullanıcı adı veya email
     * @param string $password Parola
     * @return array|WP_Error Başarılı ise kullanıcı bilgileri, değilse hata
     */
    public function login_user($username_or_email, $password) {
        $user = $this->db->authenticate_user($username_or_email, $password);
        
        if (!$user) {
            return new WP_Error('login_failed', __('Geçersiz kullanıcı adı veya parola.', 'yatirim-portfoyu-takip'));
        }
        
        // Aktif kullanıcı değilse engelle
        if ($user['status'] !== 'active') {
            return new WP_Error('account_inactive', __('Hesabınız aktif değil.', 'yatirim-portfoyu-takip'));
        }
        
        // Kullanıcı oturumu başlat
        $this->set_user_session($user['id']);
        
        return $user;
    }

    /**
     * Kullanıcı oturumunu sonlandırır
     */
    public function logout_user() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['ypt_user_id']);
        session_destroy();
        
        return true;
    }

    /**
     * Kullanıcı oturumunu başlatır
     *
     * @param int $user_id Kullanıcı ID'si
     */
    private function set_user_session($user_id) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['ypt_user_id'] = $user_id;
    }

    /**
     * Mevcut kullanıcı ID'sini alır
     *
     * @return int|false Oturum açık ise kullanıcı ID'si, değilse false
     */
    public function get_current_user_id() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['ypt_user_id']) ? $_SESSION['ypt_user_id'] : false;
    }

    /**
     * Mevcut kullanıcı bilgilerini alır
     *
     * @return array|false Oturum açık ise kullanıcı bilgileri, değilse false
     */
    public function get_current_user() {
        $user_id = $this->get_current_user_id();
        
        if (!$user_id) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_users';
        
        $user = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, username, email, membership_type, membership_expires, created_at, last_login, status FROM $table_name WHERE id = %d",
                $user_id
            ),
            ARRAY_A
        );
        
        return $user;
    }

    /**
     * Kullanıcının Premium üyeliğe sahip olup olmadığını kontrol eder
     *
     * @param int $user_id Kullanıcı ID'si (boş ise mevcut kullanıcı)
     * @return bool Premium üyeliğe sahip mi?
     */
    public function is_premium($user_id = null) {
        if ($user_id === null) {
            $user_id = $this->get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_users';
        
        $membership = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT membership_type, membership_expires FROM $table_name WHERE id = %d",
                $user_id
            )
        );
        
        if (!$membership) {
            return false;
        }
        
        // Premium üyelik kontrolü
        if ($membership->membership_type !== 'premium') {
            return false;
        }
        
        // Süre kontrolü
        if ($membership->membership_expires && strtotime($membership->membership_expires) < time()) {
            // Süresi dolmuşsa free'ye çevir
            $wpdb->update(
                $table_name,
                array('membership_type' => 'free'),
                array('id' => $user_id)
            );
            return false;
        }
        
        return true;
    }

    /**
     * Kullanıcının daha fazla yatırım aracı eklemesine izin verilip verilmediğini kontrol eder
     *
     * @param int $user_id Kullanıcı ID'si (boş ise mevcut kullanıcı)
     * @return bool Ekleme yapılabilir mi?
     */
    public function can_add_more_instruments($user_id = null) {
        if ($user_id === null) {
            $user_id = $this->get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        // Premium üyelik kontrolü
        if ($this->is_premium($user_id)) {
            return true; // Premium üyeler sınırsız ekleyebilir
        }
        
        // Mevcut yatırım araçları sayısı
        $count = $this->db->get_user_instrument_count($user_id);
        
        // Free üyeler maksimum 5 adet ekleyebilir
        return $count < 5;
    }
}