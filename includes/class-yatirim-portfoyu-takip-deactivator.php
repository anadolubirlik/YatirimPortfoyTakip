<?php
/**
 * Eklenti deaktivasyon işlemleri
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_Deactivator {

    /**
     * Eklenti deaktif edildiğinde çalıştırılacak metot
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Zamanlanmış görevleri temizle
        wp_clear_scheduled_hook('yatirim_portfoyu_cron_hook');
        wp_clear_scheduled_hook('yatirim_portfoyu_update_stock_prices');
        wp_clear_scheduled_hook('yatirim_portfoyu_update_crypto_prices');
        wp_clear_scheduled_hook('yatirim_portfoyu_update_gold_prices');
        wp_clear_scheduled_hook('yatirim_portfoyu_update_fund_prices');
    }
}