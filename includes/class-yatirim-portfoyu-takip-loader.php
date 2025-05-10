<?php
/**
 * Eklenti için aksiyonları ve filtreleri kaydeder ve çalıştırır.
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_Loader {

    /**
     * Kayıtlı aksiyonları depolar.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    Kayıtlı aksiyonları depolar.
     */
    protected $actions;

    /**
     * Kayıtlı filtreleri depolar.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    Kayıtlı filtreleri depolar.
     */
    protected $filters;

    /**
     * Kayıtlı shortcode'ları depolar.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $shortcodes    Kayıtlı shortcode'ları depolar.
     */
    protected $shortcodes;

    /**
     * Initialize the collections used to maintain the actions, filters, and shortcodes.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
        $this->shortcodes = array();
    }

    /**
     * Bir aksiyonu koleksiyona ekler.
     *
     * @since    1.0.0
     * @param    string     $hook             Aksiyonun bağlandığı kanca.
     * @param    object     $component        Aksiyonun tanımlı olduğu nesne.
     * @param    string     $callback         Bileşendeki callback metodu.
     * @param    int        $priority         Aksiyonun çalıştırılma önceliği.
     * @param    int        $accepted_args    Callback'e kabul edilen argüman sayısı.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Bir filtreyi koleksiyona ekler.
     *
     * @since    1.0.0
     * @param    string     $hook             Filtrenin bağlandığı kanca.
     * @param    object     $component        Filtrenin tanımlı olduğu nesne.
     * @param    string     $callback         Bileşendeki callback metodu.
     * @param    int        $priority         Filtrenin çalıştırılma önceliği.
     * @param    int        $accepted_args    Callback'e kabul edilen argüman sayısı.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Bir shortcode'u koleksiyona ekler.
     *
     * @since    1.0.0
     * @param    string     $tag              Shortcode etiketi.
     * @param    object     $component        Shortcode'un tanımlı olduğu nesne.
     * @param    string     $callback         Bileşendeki callback metodu.
     */
    public function add_shortcode($tag, $component, $callback) {
        $this->shortcodes = $this->add_shortcode_instance($this->shortcodes, $tag, $component, $callback);
    }

    /**
     * Bir kancayı koleksiyona eklemek için kullanılan yardımcı metod.
     *
     * @since    1.0.0
     * @access   private
     * @param    array      $hooks            Kanca türü için koleksiyon.
     * @param    string     $hook             Kanca tanımlayıcısı.
     * @param    object     $component        Callback'i tanımlayan nesne.
     * @param    string     $callback         Çağırılacak callback.
     * @param    int        $priority         Kanca önceliği.
     * @param    int        $accepted_args    Kabul edilen argüman sayısı.
     * @return   array                        Koleksiyona eklenen kancayı içerir.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Bir shortcode'u koleksiyona eklemek için kullanılan yardımcı metod.
     *
     * @since    1.0.0
     * @access   private
     * @param    array      $shortcodes       Shortcode koleksiyonu.
     * @param    string     $tag              Shortcode etiketi.
     * @param    object     $component        Callback'i tanımlayan nesne.
     * @param    string     $callback         Çağırılacak callback.
     * @return   array                        Koleksiyona eklenen shortcode'u içerir.
     */
    private function add_shortcode_instance($shortcodes, $tag, $component, $callback) {
        $shortcodes[] = array(
            'tag'           => $tag,
            'component'     => $component,
            'callback'      => $callback
        );

        return $shortcodes;
    }

    /**
     * WordPress'e kayıtlı tüm filtreleri ve aksiyonları kaydeder.
     *
     * @since    1.0.0
     */
    public function run() {
        // Aksiyonları kaydet
        foreach ($this->actions as $hook) {
            add_action(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        // Filtreleri kaydet
        foreach ($this->filters as $hook) {
            add_filter(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        // Shortcode'ları kaydet
        foreach ($this->shortcodes as $shortcode) {
            add_shortcode(
                $shortcode['tag'],
                array($shortcode['component'], $shortcode['callback'])
            );
        }
    }
}