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
    }

    /**
     * CollectAPI'dan BIST hisse senedi fiyatlarını günceller
     */
    public function update_stock_prices() {
        $api_settings = $this->db->get_api_settings('collectapi_bist');
        
        if (!$api_settings || $api_settings['status'] !== 'active' || empty($api_settings['api_key'])) {
            error_log('BIST API ayarları bulunamadı veya aktif değil.');
            return;
        }
        
        // Tüm aktif hisse kodlarını al
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_stocks';
        $stock_codes = $wpdb->get_col("SELECT DISTINCT stock_code FROM $table_name");
        
        if (empty($stock_codes)) {
            return; // Güncellenecek hisse yok
        }
        
        // API'dan fiyatları al
        $api_url = 'https://api.collectapi.com/economy/hisseSenedi';
        $api_key = $api_settings['api_key'];
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $api_key
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('BIST API hatası: ' . $response->get_error_message());
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['result']) || !is_array($data['result'])) {
            error_log('BIST API geçersiz yanıt: ' . $body);
            return;
        }
        
        // Fiyat güncellemeleri
        $current_time = current_time('mysql');
        $updated_count = 0;
        
        foreach ($data['result'] as $stock) {
            if (!isset($stock['code']) || !isset($stock['lastprice'])) {
                continue;
            }
            
            $stock_code = $stock['code'];
            $price = floatval(str_replace(',', '.', $stock['lastprice']));
            
            if (in_array($stock_code, $stock_codes)) {
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
        }
        
        // Log güncelleme sonucunu
        error_log(sprintf('BIST hisse senedi fiyatları güncellendi: %d hisse', $updated_count));
    }

    /**
     * CoinGecko API'dan kripto para fiyatlarını günceller
     */
    public function update_crypto_prices() {
        // Tüm aktif kripto kodlarını al
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_crypto';
        $crypto_codes = $wpdb->get_col("SELECT DISTINCT crypto_code FROM $table_name");
        
        if (empty($crypto_codes)) {
            return; // Güncellenecek kripto yok
        }
        
        // CoinGecko API ücretsiz versiyonu kullanılacak
        $api_url = 'https://api.coingecko.com/api/v3/simple/price';
        
        // Kripto kodlarını CoinGecko formatına dönüştür
        $ids = implode(',', array_map('strtolower', $crypto_codes));
        
        $request_url = add_query_arg(
            array(
                'ids' => $ids,
                'vs_currencies' => 'usd,try',
                'include_market_cap' => 'false',
                'include_24hr_vol' => 'false',
                'include_24hr_change' => 'false',
                'include_last_updated_at' => 'true',
            ),
            $api_url
        );
        
        $response = wp_remote_get($request_url);
        
        if (is_wp_error($response)) {
            error_log('CoinGecko API hatası: ' . $response->get_error_message());
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!is_array($data) || empty($data)) {
            error_log('CoinGecko API geçersiz yanıt: ' . $body);
            return;
        }
        
        // Fiyat güncellemeleri
        $current_time = current_time('mysql');
        $updated_count = 0;
        
        foreach ($data as $coin_id => $price_data) {
            // Kripto kodunu orijinal koda dönüştür (büyük harfler)
            $crypto_code = strtoupper($coin_id);
            
            if (!isset($price_data['usd']) || !in_array($crypto_code, $crypto_codes)) {
                continue;
            }
            
            $price_usd = floatval($price_data['usd']);
            $price_try = isset($price_data['try']) ? floatval($price_data['try']) : 0;
            
            // TL fiyatı yoksa USD'den hesapla (döviz kuru API'sı gerekebilir)
            if ($price_try === 0 && $price_usd > 0) {
                $exchange_rate = $this->get_usd_try_exchange_rate();
                if ($exchange_rate > 0) {
                    $price_try = $price_usd * $exchange_rate;
                }
            }
            
            if ($price_try > 0) {
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
        }
        
        // Log güncelleme sonucunu
        error_log(sprintf('Kripto para fiyatları güncellendi: %d kripto', $updated_count));
    }

    /**
     * USD/TRY döviz kurunu alır
     * 
     * @return float Döviz kuru
     */
    private function get_usd_try_exchange_rate() {
        $api_settings = $this->db->get_api_settings('collectapi_currency');
        
        if (!$api_settings || $api_settings['status'] !== 'active' || empty($api_settings['api_key'])) {
            error_log('Döviz API ayarları bulunamadı veya aktif değil.');
            return 0;
        }
        
        $api_url = 'https://api.collectapi.com/economy/exchange?base=USD';
        $api_key = $api_settings['api_key'];
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $api_key
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('Döviz API hatası: ' . $response->get_error_message());
            return 0;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['result']) || !is_array($data['result'])) {
            error_log('Döviz API geçersiz yanıt: ' . $body);
            return 0;
        }
        
        foreach ($data['result'] as $currency) {
            if (isset($currency['code']) && $currency['code'] === 'TRY' && isset($currency['rate'])) {
                return floatval($currency['rate']);
            }
        }
        
        return 0;
    }

    /**
     * CollectAPI'dan altın fiyatlarını günceller
     */
    public function update_gold_prices() {
        $api_settings = $this->db->get_api_settings('collectapi_gold');
        
        if (!$api_settings || $api_settings['status'] !== 'active' || empty($api_settings['api_key'])) {
            error_log('Altın API ayarları bulunamadı veya aktif değil.');
            return;
        }
        
        // Tüm aktif altın tiplerini al
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_gold';
        $gold_types = $wpdb->get_col("SELECT DISTINCT gold_type FROM $table_name");
        
        if (empty($gold_types)) {
            return; // Güncellenecek altın yok
        }
        
        // API'dan fiyatları al
        $api_url = 'https://api.collectapi.com/economy/goldPrice';
        $api_key = $api_settings['api_key'];
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $api_key
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('Altın API hatası: ' . $response->get_error_message());
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['result']) || !is_array($data['result'])) {
            error_log('Altın API geçersiz yanıt: ' . $body);
            return;
        }
        
        // Altın isimlerini standartlaştır
        $gold_mapping = array(
            'gram-altin' => 'gram',
            'tam-altin' => 'tam',
            'yarim-altin' => 'yarim',
            'ceyrek-altin' => 'ceyrek',
            'ons' => 'ons',
            'cumhuriyet-altini' => 'cumhuriyet',
            'ata-altin' => 'ata',
            'resat-altin' => 'resat',
            'hamit-altin' => 'hamit',
            'gumus' => 'gumus'
        );
        
        // Fiyat güncellemeleri
        $current_time = current_time('mysql');
        $updated_count = 0;
        
        foreach ($data['result'] as $gold) {
            if (!isset($gold['name']) || !isset($gold['buying'])) {
                continue;
            }
            
            $gold_name = strtolower($gold['name']);
            $gold_type = isset($gold_mapping[$gold_name]) ? $gold_mapping[$gold_name] : $gold_name;
            
            $price = floatval(str_replace(',', '.', $gold['buying']));
            
            if (in_array($gold_type, $gold_types)) {
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
        }
        
        // Log güncelleme sonucunu
        error_log(sprintf('Altın fiyatları güncellendi: %d altın türü', $updated_count));
    }

    /**
     * TEFAS API'dan fon fiyatlarını günceller
     */
    public function update_fund_prices() {
        // Tüm aktif fon kodlarını al
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_funds';
        $fund_codes = $wpdb->get_col("SELECT DISTINCT fund_code FROM $table_name");
        
        if (empty($fund_codes)) {
            return; // Güncellenecek fon yok
        }
        
        // TEFAS API için tarih formatını ayarla
        $date = date('d.m.Y');
        
        foreach ($fund_codes as $fund_code) {
            // Her bir fon için TEFAS API çağrısı yap
            $api_url = 'https://www.tefas.gov.tr/api/DB/BindFundDetailsDt';
            
            $response = wp_remote_post($api_url, array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Referer' => 'https://www.tefas.gov.tr/FonAnaliz.aspx',
                    'Origin' => 'https://www.tefas.gov.tr'
                ),
                'body' => json_encode(array(
                    'fontip' => null,
                    'fonkodu' => $fund_code,
                    'bastarih' => $date,
                    'bittarih' => $date
                ))
            ));
            
            if (is_wp_error($response)) {
                error_log('TEFAS API hatası (' . $fund_code . '): ' . $response->get_error_message());
                continue;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (!isset($data['data']) || !is_array($data['data']) || empty($data['data'])) {
                error_log('TEFAS API geçersiz yanıt (' . $fund_code . '): ' . $body);
                continue;
            }
            
            $fund_data = $data['data'][0];
            
            if (isset($fund_data['fd_price'])) {
                $price = floatval(str_replace(',', '.', $fund_data['fd_price']));
                $current_time = current_time('mysql');
                
                $wpdb->update(
                    $table_name,
                    array(
                        'current_price' => $price,
                        'last_update' => $current_time
                    ),
                    array('fund_code' => $fund_code)
                );
            }
            
            // API çağrıları arasında kısa bir süre bekle
            usleep(500000); // 0.5 saniye
        }
        
        // Log güncelleme sonucunu
        error_log(sprintf('Fon fiyatları güncellendi: %d fon', count($fund_codes)));
    }

    /**
     * Tekil bir hisse senedi fiyatını günceller
     *
     * @param string $stock_code Hisse kodu
     * @return array|WP_Error Fiyat bilgileri veya hata
     */
    public function get_stock_price($stock_code) {
        $api_settings = $this->db->get_api_settings('collectapi_bist');
        
        if (!$api_settings || $api_settings['status'] !== 'active' || empty($api_settings['api_key'])) {
            return new WP_Error('api_error', 'BIST API ayarları bulunamadı veya aktif değil.');
        }
        
        // API'dan fiyatı al
        $api_url = 'https://api.collectapi.com/economy/hisseSenedi';
        $api_key = $api_settings['api_key'];
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $api_key
            )
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'BIST API hatası: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['result']) || !is_array($data['result'])) {
            return new WP_Error('api_error', 'BIST API geçersiz yanıt.');
        }
        
        $stock_code = strtoupper($stock_code);
        
        foreach ($data['result'] as $stock) {
            if (isset($stock['code']) && $stock['code'] === $stock_code) {
                return array(
                    'code' => $stock['code'],
                    'name' => isset($stock['text']) ? $stock['text'] : '',
                    'price' => isset($stock['lastprice']) ? floatval(str_replace(',', '.', $stock['lastprice'])) : 0,
                    'currency' => 'TRY',
                    'change' => isset($stock['rate']) ? floatval(str_replace(',', '.', $stock['rate'])) : 0,
                    'last_update' => current_time('mysql')
                );
            }
        }
        
        return new WP_Error('not_found', 'Hisse senedi bulunamadı: ' . $stock_code);
    }

    /**
     * Tekil bir kripto para fiyatını günceller
     *
     * @param string $crypto_code Kripto kodu
     * @return array|WP_Error Fiyat bilgileri veya hata
     */
    public function get_crypto_price($crypto_code) {
        // CoinGecko API'den fiyat al
        $api_url = 'https://api.coingecko.com/api/v3/simple/price';
        
        $crypto_id = strtolower($crypto_code);
        
        $request_url = add_query_arg(
            array(
                'ids' => $crypto_id,
                'vs_currencies' => 'usd,try',
                'include_market_cap' => 'false',
                'include_24hr_vol' => 'false',
                'include_24hr_change' => 'true',
                'include_last_updated_at' => 'true',
            ),
            $api_url
        );
        
        $response = wp_remote_get($request_url);
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'CoinGecko API hatası: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!is_array($data) || empty($data)) {
            return new WP_Error('api_error', 'CoinGecko API geçersiz yanıt.');
        }
        
        if (!isset($data[$crypto_id])) {
            return new WP_Error('not_found', 'Kripto para bulunamadı: ' . $crypto_code);
        }
        
        $price_data = $data[$crypto_id];
        
        $price_usd = isset($price_data['usd']) ? floatval($price_data['usd']) : 0;
        $price_try = isset($price_data['try']) ? floatval($price_data['try']) : 0;
        
        // TL fiyatı yoksa USD'den hesapla
        if ($price_try === 0 && $price_usd > 0) {
            $exchange_rate = $this->get_usd_try_exchange_rate();
            if ($exchange_rate > 0) {
                $price_try = $price_usd * $exchange_rate;
            }
        }
        
        return array(
            'code' => strtoupper($crypto_code),
            'name' => strtoupper($crypto_code),
            'price_usd' => $price_usd,
            'price_try' => $price_try,
            'change_24h' => isset($price_data['usd_24h_change']) ? floatval($price_data['usd_24h_change']) : 0,
            'last_update' => current_time('mysql')
        );
    }

    /**
     * Tekil bir altın fiyatını günceller
     *
     * @param string $gold_type Altın türü
     * @return array|WP_Error Fiyat bilgileri veya hata
     */
    public function get_gold_price($gold_type) {
        $api_settings = $this->db->get_api_settings('collectapi_gold');
        
        if (!$api_settings || $api_settings['status'] !== 'active' || empty($api_settings['api_key'])) {
            return new WP_Error('api_error', 'Altın API ayarları bulunamadı veya aktif değil.');
        }
        
        // Altın tipini API formatına dönüştür
        $gold_mapping_reverse = array(
            'gram' => 'gram-altin',
            'tam' => 'tam-altin',
            'yarim' => 'yarim-altin',
            'ceyrek' => 'ceyrek-altin',
            'ons' => 'ons',
            'cumhuriyet' => 'cumhuriyet-altini',
            'ata' => 'ata-altin',
            'resat' => 'resat-altin',
            'hamit' => 'hamit-altin',
            'gumus' => 'gumus'
        );
        
        $api_gold_type = isset($gold_mapping_reverse[$gold_type]) ? $gold_mapping_reverse[$gold_type] : $gold_type;
        
        // API'dan fiyatları al
        $api_url = 'https://api.collectapi.com/economy/goldPrice';
        $api_key = $api_settings['api_key'];
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $api_key
            )
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'Altın API hatası: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['result']) || !is_array($data['result'])) {
            return new WP_Error('api_error', 'Altın API geçersiz yanıt.');
        }
        
        foreach ($data['result'] as $gold) {
            $name = strtolower($gold['name']);
            
            if ($name === $api_gold_type) {
                return array(
                    'type' => $gold_type,
                    'name' => $gold['name'],
                    'buying_price' => isset($gold['buying']) ? floatval(str_replace(',', '.', $gold['buying'])) : 0,
                    'selling_price' => isset($gold['selling']) ? floatval(str_replace(',', '.', $gold['selling'])) : 0,
                    'currency' => 'TRY',
                    'last_update' => current_time('mysql')
                );
            }
        }
        
        return new WP_Error('not_found', 'Altın türü bulunamadı: ' . $gold_type);
    }

    /**
     * Tekil bir fon fiyatını günceller
     *
     * @param string $fund_code Fon kodu
     * @return array|WP_Error Fiyat bilgileri veya hata
     */
    public function get_fund_price($fund_code) {
        // TEFAS API için tarih formatını ayarla
        $date = date('d.m.Y');
        
        // TEFAS API çağrısı yap
        $api_url = 'https://www.tefas.gov.tr/api/DB/BindFundDetailsDt';
        
        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Referer' => 'https://www.tefas.gov.tr/FonAnaliz.aspx',
                'Origin' => 'https://www.tefas.gov.tr'
            ),
            'body' => json_encode(array(
                'fontip' => null,
                'fonkodu' => $fund_code,
                'bastarih' => $date,
                'bittarih' => $date
            ))
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', 'TEFAS API hatası: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data']) || !is_array($data['data']) || empty($data['data'])) {
            return new WP_Error('api_error', 'TEFAS API geçersiz yanıt.');
        }
        
        $fund_data = $data['data'][0];
        
        if (!isset($fund_data['fd_price'])) {
            return new WP_Error('not_found', 'Fon fiyatı bulunamadı: ' . $fund_code);
        }
        
        return array(
            'code' => $fund_code,
            'name' => isset($fund_data['fon_unvani']) ? $fund_data['fon_unvani'] : $fund_code,
            'price' => floatval(str_replace(',', '.', $fund_data['fd_price'])),
            'date' => isset($fund_data['tarih']) ? $fund_data['tarih'] : $date,
            'currency' => 'TRY',
            'last_update' => current_time('mysql')
        );
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
}