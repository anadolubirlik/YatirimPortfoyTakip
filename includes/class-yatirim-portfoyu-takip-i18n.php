<?php
/**
 * Eklenti yerelleştirme işlemleri
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_i18n {

    /**
     * Eklentinin metin alanını yükler
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'yatirim-portfoyu-takip',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}