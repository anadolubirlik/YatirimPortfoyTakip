<?php
/**
 * Eklenti aktivasyon işlemleri
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_Activator {

    /**
     * Eklenti aktif edildiğinde çalıştırılacak metot
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Veritabanı tablolarını oluştur
        $db = new Yatirim_Portfoyu_Takip_DB();
        $db->create_tables();
        
        // Özel zamanlanmış görev aralığı ekle
        if (!wp_next_scheduled('yatirim_portfoyu_cron_hook')) {
            wp_schedule_event(time(), 'hourly', 'yatirim_portfoyu_cron_hook');
        }
        
        if (!wp_get_schedule('fifteen_minutes')) {
            add_filter('cron_schedules', array('Yatirim_Portfoyu_Takip_Activator', 'add_cron_interval'));
        }
        
        // Rol ve yetkiler
        self::setup_roles_and_capabilities();
        
        // Sürüm bilgisini kaydet
        update_option('yatirim_portfoyu_takip_version', YPT_VERSION);
    }
    
    /**
     * Özel cron aralıkları ekler
     *
     * @param array $schedules Mevcut zamanlanmış görev aralıkları
     * @return array Güncellenmiş zamanlanmış görev aralıkları
     */
    public static function add_cron_interval($schedules) {
        $schedules['fifteen_minutes'] = array(
            'interval' => 15 * 60,
            'display'  => __('Her 15 dakika', 'yatirim-portfoyu-takip'),
        );
        
        return $schedules;
    }
    
    /**
     * Rol ve yetkiler oluşturur
     */
    public static function setup_roles_and_capabilities() {
        // Admin rolü
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_yatirim_portfoyu');
            $admin->add_cap('edit_yatirim_portfoyu_settings');
            $admin->add_cap('view_yatirim_portfoyu_reports');
        }
    }
}