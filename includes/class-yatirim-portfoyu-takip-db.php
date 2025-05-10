<?php
/**
 * Veritabanı işlemleri için sınıf
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_DB {

    /**
     * Tablo adı ön eki
     *
     * @var string
     */
    private $prefix;

    /**
     * Yapılandırıcı
     */
    public function __construct() {
        global $wpdb;
        $this->prefix = $wpdb->prefix . 'ypt_';
    }

    /**
     * Eklenti tablolarını oluşturur
     *
     * @return void
     */
    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Kullanıcılar tablosu
        $table_users = $this->prefix . 'users';
        $sql_users = "CREATE TABLE $table_users (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            username varchar(50) NOT NULL,
            email varchar(100) NOT NULL,
            password varchar(255) NOT NULL,
            membership_type varchar(20) NOT NULL DEFAULT 'free',
            membership_expires datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login datetime DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            PRIMARY KEY  (id),
            UNIQUE KEY username (username),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        // Portföy tablosu
        $table_portfolio = $this->prefix . 'portfolio';
        $sql_portfolio = "CREATE TABLE $table_portfolio (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            name varchar(100) NOT NULL,
            description text DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Hisse senetleri tablosu
        $table_stocks = $this->prefix . 'stocks';
        $sql_stocks = "CREATE TABLE $table_stocks (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            portfolio_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            stock_code varchar(20) NOT NULL,
            stock_name varchar(100) DEFAULT '',
            total_shares decimal(15,4) NOT NULL DEFAULT 0,
            average_cost decimal(15,4) NOT NULL DEFAULT 0,
            current_price decimal(15,4) DEFAULT 0,
            last_update datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY portfolio_id (portfolio_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Hisse senedi işlemleri tablosu
        $table_stock_transactions = $this->prefix . 'stock_transactions';
        $sql_stock_transactions = "CREATE TABLE $table_stock_transactions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            stock_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            transaction_type varchar(10) NOT NULL,
            shares decimal(15,4) NOT NULL,
            price decimal(15,4) NOT NULL,
            commission decimal(15,4) DEFAULT 0,
            transaction_date datetime NOT NULL,
            notes text DEFAULT '',
            PRIMARY KEY  (id),
            KEY stock_id (stock_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Temettü tablosu
        $table_dividends = $this->prefix . 'dividends';
        $sql_dividends = "CREATE TABLE $table_dividends (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            stock_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            amount decimal(15,4) NOT NULL,
            dividend_date date NOT NULL,
            notes text DEFAULT '',
            PRIMARY KEY  (id),
            KEY stock_id (stock_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Kripto para tablosu
        $table_crypto = $this->prefix . 'crypto';
        $sql_crypto = "CREATE TABLE $table_crypto (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            portfolio_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            crypto_code varchar(20) NOT NULL,
            crypto_name varchar(100) DEFAULT '',
            total_amount decimal(18,8) NOT NULL DEFAULT 0,
            average_cost decimal(18,8) NOT NULL DEFAULT 0,
            current_price decimal(18,8) DEFAULT 0,
            last_update datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY portfolio_id (portfolio_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Kripto para işlemleri tablosu
        $table_crypto_transactions = $this->prefix . 'crypto_transactions';
        $sql_crypto_transactions = "CREATE TABLE $table_crypto_transactions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            crypto_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            transaction_type varchar(10) NOT NULL,
            amount decimal(18,8) NOT NULL,
            price decimal(18,8) NOT NULL,
            commission decimal(15,4) DEFAULT 0,
            transaction_date datetime NOT NULL,
            notes text DEFAULT '',
            PRIMARY KEY  (id),
            KEY crypto_id (crypto_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Altın tablosu
        $table_gold = $this->prefix . 'gold';
        $sql_gold = "CREATE TABLE $table_gold (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            portfolio_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            gold_type varchar(50) NOT NULL,
            total_weight decimal(15,4) NOT NULL DEFAULT 0,
            average_cost decimal(15,4) NOT NULL DEFAULT 0,
            current_price decimal(15,4) DEFAULT 0,
            last_update datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY portfolio_id (portfolio_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Altın işlemleri tablosu
        $table_gold_transactions = $this->prefix . 'gold_transactions';
        $sql_gold_transactions = "CREATE TABLE $table_gold_transactions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            gold_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            transaction_type varchar(10) NOT NULL,
            weight decimal(15,4) NOT NULL,
            price decimal(15,4) NOT NULL,
            transaction_date datetime NOT NULL,
            notes text DEFAULT '',
            PRIMARY KEY  (id),
            KEY gold_id (gold_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Fon tablosu
        $table_funds = $this->prefix . 'funds';
        $sql_funds = "CREATE TABLE $table_funds (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            portfolio_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            fund_code varchar(20) NOT NULL,
            fund_name varchar(100) DEFAULT '',
            total_shares decimal(15,4) NOT NULL DEFAULT 0,
            average_cost decimal(15,4) NOT NULL DEFAULT 0,
            current_price decimal(15,4) DEFAULT 0,
            last_update datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY portfolio_id (portfolio_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Fon işlemleri tablosu
        $table_fund_transactions = $this->prefix . 'fund_transactions';
        $sql_fund_transactions = "CREATE TABLE $table_fund_transactions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            fund_id mediumint(9) NOT NULL,
            user_id mediumint(9) NOT NULL,
            transaction_type varchar(10) NOT NULL,
            shares decimal(15,4) NOT NULL,
            price decimal(15,4) NOT NULL,
            transaction_date datetime NOT NULL,
            notes text DEFAULT '',
            PRIMARY KEY  (id),
            KEY fund_id (fund_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // API ayarları tablosu
        $table_api_settings = $this->prefix . 'api_settings';
        $sql_api_settings = "CREATE TABLE $table_api_settings (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            api_name varchar(100) NOT NULL,
            api_key text DEFAULT NULL,
            api_secret text DEFAULT NULL,
            api_endpoint varchar(255) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY api_name (api_name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Tabloları oluştur
        dbDelta($sql_users);
        dbDelta($sql_portfolio);
        dbDelta($sql_stocks);
        dbDelta($sql_stock_transactions);
        dbDelta($sql_dividends);
        dbDelta($sql_crypto);
        dbDelta($sql_crypto_transactions);
        dbDelta($sql_gold);
        dbDelta($sql_gold_transactions);
        dbDelta($sql_funds);
        dbDelta($sql_fund_transactions);
        dbDelta($sql_api_settings);
    }

    /**
     * Bir kullanıcıyı veritabanına ekler
     *
     * @param array $user_data Kullanıcı bilgileri
     * @return int|false Eklenen kullanıcının ID'si veya hata durumunda false
     */
    public function add_user($user_data) {
        global $wpdb;
        $table_name = $this->prefix . 'users';
        
        // Şifreyi hash'le
        $password_hash = wp_hash_password($user_data['password']);
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'username' => $user_data['username'],
                'email' => $user_data['email'],
                'password' => $password_hash,
                'membership_type' => isset($user_data['membership_type']) ? $user_data['membership_type'] : 'free',
                'created_at' => current_time('mysql')
            )
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Kullanıcı bilgilerini günceller
     *
     * @param int $user_id Kullanıcı ID'si
     * @param array $user_data Güncellenecek kullanıcı bilgileri
     * @return bool İşlem başarılı mı?
     */
    public function update_user($user_id, $user_data) {
        global $wpdb;
        $table_name = $this->prefix . 'users';
        
        // Şifre değiştiriliyorsa hash'le
        if (isset($user_data['password'])) {
            $user_data['password'] = wp_hash_password($user_data['password']);
        }
        
        $result = $wpdb->update(
            $table_name,
            $user_data,
            array('id' => $user_id)
        );
        
        return $result !== false;
    }

    /**
     * Kullanıcı doğrulama işlemi
     *
     * @param string $username_or_email Kullanıcı adı veya email
     * @param string $password Parola
     * @return array|false Kullanıcı bilgileri veya hata durumunda false
     */
    public function authenticate_user($username_or_email, $password) {
        global $wpdb;
        $table_name = $this->prefix . 'users';
        
        $user = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE username = %s OR email = %s",
                $username_or_email,
                $username_or_email
            ),
            ARRAY_A
        );
        
        if (!$user) {
            return false;
        }
        
        if (wp_check_password($password, $user['password'])) {
            // Başarılı giriş - son giriş tarihini güncelle
            $wpdb->update(
                $table_name,
                array('last_login' => current_time('mysql')),
                array('id' => $user['id'])
            );
            
            // Parola dışındaki tüm bilgileri döndür
            unset($user['password']);
            return $user;
        }
        
        return false;
    }

    /**
     * Hisse senedi ekle
     *
     * @param array $stock_data Hisse senedi bilgileri
     * @return int|false Eklenen hisse senedi ID'si veya hata durumunda false
     */
    public function add_stock($stock_data) {
        global $wpdb;
        $table_name = $this->prefix . 'stocks';
        
        $result = $wpdb->insert($table_name, $stock_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Hisse senedi güncelle
     *
     * @param int $stock_id Hisse senedi ID'si
     * @param array $stock_data Güncellenecek bilgiler
     * @return bool İşlem başarılı mı?
     */
    public function update_stock($stock_id, $stock_data) {
        global $wpdb;
        $table_name = $this->prefix . 'stocks';
        
        $result = $wpdb->update(
            $table_name,
            $stock_data,
            array('id' => $stock_id)
        );
        
        return $result !== false;
    }

    /**
     * Hisse senedi işlemi ekle
     *
     * @param array $transaction_data İşlem bilgileri
     * @return int|false Eklenen işlem ID'si veya hata durumunda false
     */
    public function add_stock_transaction($transaction_data) {
        global $wpdb;
        $table_name = $this->prefix . 'stock_transactions';
        
        $result = $wpdb->insert($table_name, $transaction_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Temettü ekle
     *
     * @param array $dividend_data Temettü bilgileri
     * @return int|false Eklenen temettü ID'si veya hata durumunda false
     */
    public function add_dividend($dividend_data) {
        global $wpdb;
        $table_name = $this->prefix . 'dividends';
        
        $result = $wpdb->insert($table_name, $dividend_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Kullanıcıya ait hisse senetlerini getir
     *
     * @param int $user_id Kullanıcı ID'si
     * @return array Hisse senetleri dizisi
     */
    public function get_user_stocks($user_id) {
        global $wpdb;
        $table_name = $this->prefix . 'stocks';
        
        $stocks = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );
        
        return $stocks;
    }

    /**
     * Kullanıcının sahip olduğu yatırım aracı sayısını döndürür
     *
     * @param int $user_id Kullanıcı ID'si
     * @return int Yatırım aracı sayısı
     */
    public function get_user_instrument_count($user_id) {
        global $wpdb;
        
        $stocks_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->prefix}stocks WHERE user_id = %d",
                $user_id
            )
        );
        
        $crypto_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->prefix}crypto WHERE user_id = %d",
                $user_id
            )
        );
        
        $gold_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->prefix}gold WHERE user_id = %d",
                $user_id
            )
        );
        
        $funds_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->prefix}funds WHERE user_id = %d",
                $user_id
            )
        );
        
        return $stocks_count + $crypto_count + $gold_count + $funds_count;
    }

    /**
     * Bir hisse senedinin işlemlerini getir
     *
     * @param int $stock_id Hisse senedi ID'si
     * @return array İşlemler dizisi
     */
    public function get_stock_transactions($stock_id) {
        global $wpdb;
        $table_name = $this->prefix . 'stock_transactions';
        
        $transactions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE stock_id = %d ORDER BY transaction_date DESC",
                $stock_id
            ),
            ARRAY_A
        );
        
        return $transactions;
    }

    /**
     * API ayarlarını güncelle
     *
     * @param string $api_name API adı
     * @param array $settings Ayarlar
     * @return bool İşlem başarılı mı?
     */
    public function update_api_settings($api_name, $settings) {
        global $wpdb;
        $table_name = $this->prefix . 'api_settings';
        
        // API ayarının mevcut olup olmadığını kontrol et
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE api_name = %s",
                $api_name
            )
        );
        
        if ($existing) {
            // Mevcut kaydı güncelle
            $settings['updated_at'] = current_time('mysql');
            $result = $wpdb->update(
                $table_name,
                $settings,
                array('api_name' => $api_name)
            );
        } else {
            // Yeni kayıt ekle
            $settings['api_name'] = $api_name;
            $settings['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $settings);
        }
        
        return $result !== false;
    }

    /**
     * API ayarlarını getir
     *
     * @param string $api_name API adı
     * @return array|false API ayarları veya hata durumunda false
     */
    public function get_api_settings($api_name) {
        global $wpdb;
        $table_name = $this->prefix . 'api_settings';
        
        $settings = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE api_name = %s",
                $api_name
            ),
            ARRAY_A
        );
        
        return $settings;
    }
}