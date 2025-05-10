<?php
/**
 * API entegrasyonları
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_API {

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
     * Yapılandırıcı
     *
     * @param string $plugin_name    Eklentinin adı.
     * @param string $version        Eklentinin versiyonu.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->db = new Yatirim_Portfoyu_Takip_DB();
        
        // Demo API anahtarlarını ayarla
        $this->setup_demo_api_keys();
    }
    
    /**
     * Demo API anahtarlarını ayarlar
     */
    private function setup_demo_api_keys() {
        // API ayarlarını kontrol et ve yoksa ekle
        $collectapi_bist = $this->db->get_api_settings('collectapi_bist');
        $collectapi_gold = $this->db->get_api_settings('collectapi_gold');
        $collectapi_currency = $this->db->get_api_settings('collectapi_currency');
        
        if (!$collectapi_bist) {
            $this->db->update_api_settings('collectapi_bist', array(
                'api_key' => 'demo_bist_key_' . md5(rand()),
                'status' => 'active',
                'created_at' => current_time('mysql')
            ));
        }
        
        if (!$collectapi_gold) {
            $this->db->update_api_settings('collectapi_gold', array(
                'api_key' => 'demo_gold_key_' . md5(rand()),
                'status' => 'active',
                'created_at' => current_time('mysql')
            ));
        }
        
        if (!$collectapi_currency) {
            $this->db->update_api_settings('collectapi_currency', array(
                'api_key' => 'demo_currency_key_' . md5(rand()),
                'status' => 'active',
                'created_at' => current_time('mysql')
            ));
        }
    }

    /**
     * CollectAPI'dan BIST hisse senedi fiyatlarını günceller
     * DEMO: Gerçek API yerine örnek veriler kullanılır
     */
    public function update_stock_prices() {
        // Tüm aktif hisse kodlarını al
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_stocks';
        $stock_codes = $wpdb->get_col("SELECT DISTINCT stock_code FROM $table_name");
        
        if (empty($stock_codes)) {
            return; // Güncellenecek hisse yok
        }
        
        // DEMO: Örnek hisse verilerini oluştur
        $demo_stock_data = $this->get_demo_stock_data();
        
        // Fiyat güncellemeleri
        $current_time = current_time('mysql');
        $updated_count = 0;
        
        foreach ($stock_codes as $stock_code) {
            // Demo veriler içinde bu hisse var mı bak
            $price = isset($demo_stock_data[$stock_code]) ? $demo_stock_data[$stock_code]['price'] : rand(10, 1000) / 10;
            
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table_name SET current_price = %f, last_update = %s WHERE stock_code = %s",
                    $price,
                    $current_time,
                    $stock_code
                )
            );
            $updated_count++;
        }
        
        // Log güncelleme sonucunu
        error_log(sprintf('BIST hisse senedi fiyatları güncellendi: %d hisse', $updated_count));
    }

    /**
     * CoinGecko API'dan kripto para fiyatlarını günceller
     * DEMO: Gerçek API yerine örnek veriler kullanılır
     */
    public function update_crypto_prices() {
        // Tüm aktif kripto kodlarını al
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_crypto';
        $crypto_codes = $wpdb->get_col("SELECT DISTINCT crypto_code FROM $table_name");
        
        if (empty($crypto_codes)) {
            return; // Güncellenecek kripto yok
        }
        
        // DEMO: Örnek kripto verilerini oluştur
        $demo_crypto_data = $this->get_demo_crypto_data();
        
        // Fiyat güncellemeleri
        $current_time = current_time('mysql');
        $updated_count = 0;
        
        foreach ($crypto_codes as $crypto_code) {
            // Demo veriler içinde bu kripto var mı bak
            $price_try = isset($demo_crypto_data[$crypto_code]) ? $demo_crypto_data[$crypto_code]['price_try'] : rand(100, 100000) / 10;
            
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table_name SET current_price = %f, last_update = %s WHERE crypto_code = %s",
                    $price_try,
                    $current_time,
                    $crypto_code
                )
            );
            $updated_count++;
        }
        
        // Log güncelleme sonucunu
        error_log(sprintf('Kripto para fiyatları güncellendi: %d kripto', $updated_count));
    }

    /**
     * USD/TRY döviz kurunu alır
     * DEMO: Sabit değer döndürür
     * 
     * @return float Döviz kuru
     */
    private function get_usd_try_exchange_rate() {
        // DEMO: Örnek kur değeri
        return 31.25;
    }

    /**
     * CollectAPI'dan altın fiyatlarını günceller
     * DEMO: Gerçek API yerine örnek veriler kullanılır
     */
    public function update_gold_prices() {
        // Tüm aktif altın tiplerini al
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_gold';
        $gold_types = $wpdb->get_col("SELECT DISTINCT gold_type FROM $table_name");
        
        if (empty($gold_types)) {
            return; // Güncellenecek altın yok
        }
        
        // DEMO: Örnek altın verilerini oluştur
        $demo_gold_data = $this->get_demo_gold_data();
        
        // Fiyat güncellemeleri
        $current_time = current_time('mysql');
        $updated_count = 0;
        
        foreach ($gold_types as $gold_type) {
            // Demo veriler içinde bu altın tipi var mı bak
            $price = isset($demo_gold_data[$gold_type]) ? $demo_gold_data[$gold_type]['buying_price'] : rand(1500, 2500);
            
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table_name SET current_price = %f, last_update = %s WHERE gold_type = %s",
                    $price,
                    $current_time,
                    $gold_type
                )
            );
            $updated_count++;
        }
        
        // Log güncelleme sonucunu
        error_log(sprintf('Altın fiyatları güncellendi: %d altın türü', $updated_count));
    }

    /**
     * TEFAS API'dan fon fiyatlarını günceller
     * DEMO: Gerçek API yerine örnek veriler kullanılır
     */
    public function update_fund_prices() {
        // Tüm aktif fon kodlarını al
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_funds';
        $fund_codes = $wpdb->get_col("SELECT DISTINCT fund_code FROM $table_name");
        
        if (empty($fund_codes)) {
            return; // Güncellenecek fon yok
        }
        
        // DEMO: Örnek fon verilerini oluştur
        $demo_fund_data = $this->get_demo_fund_data();
        
        // Fiyat güncellemeleri
        $current_time = current_time('mysql');
        
        foreach ($fund_codes as $fund_code) {
            // Demo veriler içinde bu fon var mı bak
            $price = isset($demo_fund_data[$fund_code]) ? $demo_fund_data[$fund_code]['price'] : rand(10, 100) / 10;
            
            $wpdb->update(
                $table_name,
                array(
                    'current_price' => $price,
                    'last_update' => $current_time
                ),
                array('fund_code' => $fund_code)
            );
        }
        
        // Log güncelleme sonucunu
        error_log(sprintf('Fon fiyatları güncellendi: %d fon', count($fund_codes)));
    }

    /**
     * Tekil bir hisse senedi fiyatını günceller
     * DEMO: Gerçek API yerine örnek veriler kullanılır
     *
     * @param string $stock_code Hisse kodu
     * @return array|WP_Error Fiyat bilgileri veya hata
     */
    public function get_stock_price($stock_code) {
        // DEMO: Örnek hisse verilerini oluştur
        $demo_data = $this->get_demo_stock_data();
        $stock_code = strtoupper($stock_code);
        
        // Demo veriler içinde hisse varsa kullan, yoksa rastgele değer oluştur
        if (isset($demo_data[$stock_code])) {
            return array(
                'code' => $stock_code,
                'name' => $demo_data[$stock_code]['name'],
                'price' => $demo_data[$stock_code]['price'],
                'currency' => 'TRY',
                'change' => $demo_data[$stock_code]['change'],
                'last_update' => current_time('mysql')
            );
        } else {
            return array(
                'code' => $stock_code,
                'name' => $stock_code . ' Hissesi',
                'price' => rand(10, 1000) / 10,
                'currency' => 'TRY',
                'change' => (rand(-100, 100) / 10),
                'last_update' => current_time('mysql')
            );
        }
    }

    /**
     * Tekil bir kripto para fiyatını günceller
     * DEMO: Gerçek API yerine örnek veriler kullanılır
     *
     * @param string $crypto_code Kripto kodu
     * @return array|WP_Error Fiyat bilgileri veya hata
     */
    public function get_crypto_price($crypto_code) {
        // DEMO: Örnek kripto verilerini oluştur
        $demo_data = $this->get_demo_crypto_data();
        $crypto_code = strtoupper($crypto_code);
        
        // Demo veriler içinde kripto varsa kullan, yoksa rastgele değer oluştur
        if (isset($demo_data[$crypto_code])) {
            return array(
                'code' => $crypto_code,
                'name' => $demo_data[$crypto_code]['name'],
                'price_usd' => $demo_data[$crypto_code]['price_usd'],
                'price_try' => $demo_data[$crypto_code]['price_try'],
                'change_24h' => $demo_data[$crypto_code]['change_24h'],
                'last_update' => current_time('mysql')
            );
        } else {
            $price_usd = rand(100, 10000) / 100;
            $exchange_rate = $this->get_usd_try_exchange_rate();
            
            return array(
                'code' => $crypto_code,
                'name' => $crypto_code,
                'price_usd' => $price_usd,
                'price_try' => $price_usd * $exchange_rate,
                'change_24h' => (rand(-100, 100) / 10),
                'last_update' => current_time('mysql')
            );
        }
    }

    /**
     * Tekil bir altın fiyatını günceller
     * DEMO: Gerçek API yerine örnek veriler kullanılır
     *
     * @param string $gold_type Altın türü
     * @return array|WP_Error Fiyat bilgileri veya hata
     */
    public function get_gold_price($gold_type) {
        // DEMO: Örnek altın verilerini oluştur
        $demo_data = $this->get_demo_gold_data();
        
        // Demo veriler içinde altın türü varsa kullan, yoksa rastgele değer oluştur
        if (isset($demo_data[$gold_type])) {
            return array(
                'type' => $gold_type,
                'name' => $demo_data[$gold_type]['name'],
                'buying_price' => $demo_data[$gold_type]['buying_price'],
                'selling_price' => $demo_data[$gold_type]['selling_price'],
                'currency' => 'TRY',
                'last_update' => current_time('mysql')
            );
        } else {
            $buying_price = rand(1500, 2500);
            $selling_price = $buying_price + rand(10, 50);
            
            return array(
                'type' => $gold_type,
                'name' => ucfirst($gold_type) . ' Altın',
                'buying_price' => $buying_price,
                'selling_price' => $selling_price,
                'currency' => 'TRY',
                'last_update' => current_time('mysql')
            );
        }
    }

    /**
     * Tekil bir fon fiyatını günceller
     * DEMO: Gerçek API yerine örnek veriler kullanılır
     *
     * @param string $fund_code Fon kodu
     * @return array|WP_Error Fiyat bilgileri veya hata
     */
    public function get_fund_price($fund_code) {
        // DEMO: Örnek fon verilerini oluştur
        $demo_data = $this->get_demo_fund_data();
        $fund_code = strtoupper($fund_code);
        
        // Demo veriler içinde fon varsa kullan, yoksa rastgele değer oluştur
        if (isset($demo_data[$fund_code])) {
            return array(
                'code' => $fund_code,
                'name' => $demo_data[$fund_code]['name'],
                'price' => $demo_data[$fund_code]['price'],
                'date' => date('Y-m-d'),
                'currency' => 'TRY',
                'last_update' => current_time('mysql')
            );
        } else {
            return array(
                'code' => $fund_code,
                'name' => $fund_code . ' Fonu',
                'price' => rand(10, 100) / 10,
                'date' => date('Y-m-d'),
                'currency' => 'TRY',
                'last_update' => current_time('mysql')
            );
        }
    }

    /**
     * API ayarlarını kaydet
     *
     * @param string $api_name API adı
     * @param array $settings API ayarları
     * @return bool İşlem başarılı mı?
     */
    public function save_api_settings($api_name, $settings) {
        return $this->db->update_api_settings($api_name, $settings);
    }

    /**
     * API ayarlarını getir
     *
     * @param string $api_name API adı
     * @return array|false API ayarları veya hata durumunda false
     */
    public function get_api_settings($api_name) {
        return $this->db->get_api_settings($api_name);
    }
    
    /**
     * Demo hisse senedi verilerini döndürür
     *
     * @return array Hisse senedi verileri
     */
    private function get_demo_stock_data() {
        return array(
            'TUPRS' => array('name' => 'Türkiye Petrol Rafinerileri A.Ş.', 'price' => 142.50, 'change' => 2.3),
            'THYAO' => array('name' => 'Türk Hava Yolları A.O.', 'price' => 85.20, 'change' => -1.2),
            'KCHOL' => array('name' => 'Koç Holding A.Ş.', 'price' => 72.30, 'change' => 0.5),
            'ASELS' => array('name' => 'Aselsan Elektronik Sanayi ve Ticaret A.Ş.', 'price' => 32.10, 'change' => 1.8),
            'GARAN' => array('name' => 'Türkiye Garanti Bankası A.Ş.', 'price' => 28.40, 'change' => -0.7),
            'EREGL' => array('name' => 'Ereğli Demir ve Çelik Fabrikaları T.A.Ş.', 'price' => 18.75, 'change' => 0.3),
            'BIMAS' => array('name' => 'BİM Birleşik Mağazalar A.Ş.', 'price' => 155.80, 'change' => 1.5),
            'SISE' => array('name' => 'Türkiye Şişe ve Cam Fabrikaları A.Ş.', 'price' => 12.60, 'change' => -0.9),
            'SAHOL' => array('name' => 'Hacı Ömer Sabancı Holding A.Ş.', 'price' => 37.90, 'change' => 0.6),
            'AKBNK' => array('name' => 'Akbank T.A.Ş.', 'price' => 18.10, 'change' => 0.4),
        );
    }
    
    /**
     * Demo kripto para verilerini döndürür
     *
     * @return array Kripto para verileri
     */
    private function get_demo_crypto_data() {
        return array(
            'BTC' => array('name' => 'Bitcoin', 'price_usd' => 41500.25, 'price_try' => 1297000.25, 'change_24h' => 2.5),
            'ETH' => array('name' => 'Ethereum', 'price_usd' => 2250.75, 'price_try' => 70336.87, 'change_24h' => 3.2),
            'BNB' => array('name' => 'Binance Coin', 'price_usd' => 425.50, 'price_try' => 13296.87, 'change_24h' => -1.2),
            'ADA' => array('name' => 'Cardano', 'price_usd' => 0.45, 'price_try' => 14.06, 'change_24h' => 0.8),
            'XRP' => array('name' => 'XRP', 'price_usd' => 0.57, 'price_try' => 17.81, 'change_24h' => -0.5),
            'SOL' => array('name' => 'Solana', 'price_usd' => 75.25, 'price_try' => 2351.56, 'change_24h' => 5.7),
            'DOGE' => array('name' => 'Dogecoin', 'price_usd' => 0.12, 'price_try' => 3.75, 'change_24h' => -2.3),
            'DOT' => array('name' => 'Polkadot', 'price_usd' => 5.80, 'price_try' => 181.25, 'change_24h' => 1.4),
            'AVAX' => array('name' => 'Avalanche', 'price_usd' => 28.35, 'price_try' => 885.94, 'change_24h' => 3.6),
            'MATIC' => array('name' => 'Polygon', 'price_usd' => 0.68, 'price_try' => 21.25, 'change_24h' => -0.9),
        );
    }
    
    /**
     * Demo altın verilerini döndürür
     *
     * @return array Altın verileri
     */
    private function get_demo_gold_data() {
        return array(
            'gram' => array('name' => 'Gram Altın', 'buying_price' => 2185.50, 'selling_price' => 2190.75),
            'ceyrek' => array('name' => 'Çeyrek Altın', 'buying_price' => 3525.80, 'selling_price' => 3550.25),
            'yarim' => array('name' => 'Yarım Altın', 'buying_price' => 7050.30, 'selling_price' => 7090.50),
            'tam' => array('name' => 'Tam Altın', 'buying_price' => 14100.75, 'selling_price' => 14180.25),
            'cumhuriyet' => array('name' => 'Cumhuriyet Altını', 'buying_price' => 14250.30, 'selling_price' => 14325.50),
            'ata' => array('name' => 'Ata Altın', 'buying_price' => 14195.25, 'selling_price' => 14275.80),
            'resat' => array('name' => 'Reşat Altın', 'buying_price' => 14350.50, 'selling_price' => 14450.75),
            'hamit' => array('name' => 'Hamit Altın', 'buying_price' => 14375.25, 'selling_price' => 14475.50),
            'ons' => array('name' => 'Ons Altın', 'buying_price' => 2345.30, 'selling_price' => 2350.45),
            'gumus' => array('name' => 'Gümüş', 'buying_price' => 28.75, 'selling_price' => 29.25),
        );
    }
    
    /**
     * Demo fon verilerini döndürür
     *
     * @return array Fon verileri
     */
    private function get_demo_fund_data() {
        return array(
            'AFT' => array('name' => 'Ak Portföy Teknoloji Sektörü Fonu', 'price' => 12.45),
            'IPJ' => array('name' => 'İş Portföy BIST Teknoloji Ağırlıklı Sınırlamalı Endeks Fonu', 'price' => 4.83),
            'TTE' => array('name' => 'TEB Portföy Teknoloji Endeksi Fonu', 'price' => 8.27),
            'YAF' => array('name' => 'Yapı Kredi Portföy BIST 30 Endeksi Fonu', 'price' => 6.12),
            'TI2' => array('name' => 'İş Portföy İkinci Hisse Senedi Fonu', 'price' => 2.97),
            'IYA' => array('name' => 'İş Portföy Yabancı Hisse Senedi Fonu', 'price' => 6.53),
            'GAF' => array('name' => 'Garanti Portföy Altın Fonu', 'price' => 4.78),
            'TGE' => array('name' => 'TEB Portföy Gümüş Fonu', 'price' => 3.25),
            'IBF' => array('name' => 'İş Portföy BIST Banka Endeksi Hisse Senedi Fonu', 'price' => 2.18),
            'DAH' => array('name' => 'Deniz Portföy Altın Fonu', 'price' => 5.32),
        );
    }
}