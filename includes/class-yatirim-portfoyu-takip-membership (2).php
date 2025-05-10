<?php
/**
 * Üyelik planları yönetimi
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

class Yatirim_Portfoyu_Takip_Membership {

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
     * Kullanıcının üyelik tipini günceller
     *
     * @param int $user_id Kullanıcı ID
     * @param string $membership_type Üyelik tipi (free veya premium)
     * @param string|null $expiry_date Üyelik bitiş tarihi (null olursa süresiz)
     * @return bool İşlem başarılı mı?
     */
    public function update_membership($user_id, $membership_type, $expiry_date = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_users';
        
        $data = array(
            'membership_type' => $membership_type
        );
        
        if (!is_null($expiry_date)) {
            $data['membership_expires'] = $expiry_date;
        }
        
        return $wpdb->update(
            $table_name,
            $data,
            array('id' => $user_id)
        ) !== false;
    }

    /**
     * Premium üyelik satın alma işlemleri
     *
     * @param int $user_id Kullanıcı ID
     * @param string $payment_method Ödeme yöntemi
     * @param float $payment_amount Ödeme tutarı
     * @param string $payment_currency Para birimi
     * @param int $duration_months Süre (ay cinsinden)
     * @return bool|string İşlem başarılı ise true, başarısız ise hata mesajı
     */
    public function purchase_premium($user_id, $payment_method, $payment_amount, $payment_currency, $duration_months) {
        // Kullanıcı kontrolü
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_users';
        
        $user = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $user_id
            )
        );
        
        if (!$user) {
            return __('Kullanıcı bulunamadı.', 'yatirim-portfoyu-takip');
        }
        
        // Ödeme kaydı
        $payment_table = $wpdb->prefix . 'ypt_payments';
        
        $payment_data = array(
            'user_id' => $user_id,
            'payment_method' => $payment_method,
            'payment_amount' => $payment_amount,
            'payment_currency' => $payment_currency,
            'payment_date' => current_time('mysql'),
            'status' => 'completed',
            'description' => sprintf(
                __('%d aylık Premium üyelik', 'yatirim-portfoyu-takip'),
                $duration_months
            )
        );
        
        $payment_result = $wpdb->insert($payment_table, $payment_data);
        
        if (!$payment_result) {
            return __('Ödeme kaydı oluşturulamadı.', 'yatirim-portfoyu-takip');
        }
        
        // Üyelik süresini hesapla
        $expiry_date = null;
        
        // Mevcut üyelik süresi varsa ve hala geçerliyse, ona ekle
        if ($user->membership_type === 'premium' && $user->membership_expires && strtotime($user->membership_expires) > time()) {
            $expiry_date = date('Y-m-d H:i:s', strtotime($user->membership_expires . " + " . $duration_months . " months"));
        } else {
            // Yeni süre başlat
            $expiry_date = date('Y-m-d H:i:s', strtotime("+ " . $duration_months . " months"));
        }
        
        // Üyelik güncelle
        $update_result = $this->update_membership($user_id, 'premium', $expiry_date);
        
        if (!$update_result) {
            return __('Üyelik güncellenemedi.', 'yatirim-portfoyu-takip');
        }
        
        // Bildirim e-postası gönder
        $this->send_premium_notification_email($user_id, $expiry_date, $duration_months);
        
        return true;
    }

    /**
     * Premium üyelik bilgilendirme e-postası gönderir
     *
     * @param int $user_id Kullanıcı ID
     * @param string $expiry_date Bitiş tarihi
     * @param int $duration_months Süre (ay)
     * @return bool E-posta gönderildiyse true
     */
    private function send_premium_notification_email($user_id, $expiry_date, $duration_months) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypt_users';
        
        $user = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $user_id
            )
        );
        
        if (!$user) {
            return false;
        }
        
        $to = $user->email;
        $subject = __('Premium Üyeliğiniz Aktifleştirildi', 'yatirim-portfoyu-takip');
        
        $message = sprintf(
            __('Merhaba %s,', 'yatirim-portfoyu-takip') . "\n\n" .
            __('%d aylık Premium üyeliğiniz aktifleştirildi. Hesabınız %s tarihine kadar Premium olarak devam edecektir.', 'yatirim-portfoyu-takip') . "\n\n" .
            __('Premium üyelik avantajlarınız:', 'yatirim-portfoyu-takip') . "\n" .
            __('- Sınırsız sayıda yatırım aracı ekleyebilme', 'yatirim-portfoyu-takip') . "\n" .
            __('- Detaylı portföy analizi ve raporları', 'yatirim-portfoyu-takip') . "\n" .
            __('- Özel bildirimler ve uyarılar', 'yatirim-portfoyu-takip') . "\n\n" .
            __('Yatırım Portföyü Takip uygulamasını tercih ettiğiniz için teşekkür ederiz.', 'yatirim-portfoyu-takip') . "\n\n" .
            __('Saygılarımızla,', 'yatirim-portfoyu-takip') . "\n" .
            __('Yatırım Portföyü Takip Ekibi', 'yatirim-portfoyu-takip'),
            $user->username,
            $duration_months,
            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($expiry_date))
        );
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($to, $subject, nl2br($message), $headers);
    }

    /**
     * Ödeme sayfası içeriğini oluşturur
     *
     * @return string HTML içeriği
     */
    public function get_payment_page_content() {
        $output = '<div class="container mt-4">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="m-0">' . __('Premium Üyelik', 'yatirim-portfoyu-takip') . '</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                ' . __('Premium üyelik avantajları:', 'yatirim-portfoyu-takip') . '
                                <ul>
                                    <li>' . __('Sınırsız sayıda yatırım aracı ekleyebilme', 'yatirim-portfoyu-takip') . '</li>
                                    <li>' . __('Detaylı portföy analizi ve raporları', 'yatirim-portfoyu-takip') . '</li>
                                    <li>' . __('Özel bildirimler ve uyarılar', 'yatirim-portfoyu-takip') . '</li>
                                </ul>
                            </div>
                            
                            <h5 class="mb-4">' . __('Üyelik Planları', 'yatirim-portfoyu-takip') . '</h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="m-0">' . __('1 Aylık', 'yatirim-portfoyu-takip') . '</h5>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h3 class="text-center mb-3">49.90 ₺</h3>
                                            <ul class="list-unstyled mb-4">
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('1 ay Premium üyelik', 'yatirim-portfoyu-takip') . '</li>
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('Tüm özelliklere erişim', 'yatirim-portfoyu-takip') . '</li>
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('Sınırsız yatırım aracı', 'yatirim-portfoyu-takip') . '</li>
                                            </ul>
                                            <button class="btn btn-primary mt-auto w-100 select-plan" data-plan="monthly" data-amount="49.90" data-months="1">' . __('Seç', 'yatirim-portfoyu-takip') . '</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="m-0">' . __('6 Aylık', 'yatirim-portfoyu-takip') . '</h5>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h3 class="text-center mb-3">239.90 ₺</h3>
                                            <p class="text-center text-success mb-3">' . __('20% İndirim', 'yatirim-portfoyu-takip') . '</p>
                                            <ul class="list-unstyled mb-4">
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('6 ay Premium üyelik', 'yatirim-portfoyu-takip') . '</li>
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('Tüm özelliklere erişim', 'yatirim-portfoyu-takip') . '</li>
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('Sınırsız yatırım aracı', 'yatirim-portfoyu-takip') . '</li>
                                            </ul>
                                            <button class="btn btn-primary mt-auto w-100 select-plan" data-plan="semi_annual" data-amount="239.90" data-months="6">' . __('Seç', 'yatirim-portfoyu-takip') . '</button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="m-0">' . __('Yıllık', 'yatirim-portfoyu-takip') . '</h5>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h3 class="text-center mb-3">449.90 ₺</h3>
                                            <p class="text-center text-success mb-3">' . __('25% İndirim', 'yatirim-portfoyu-takip') . '</p>
                                            <ul class="list-unstyled mb-4">
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('12 ay Premium üyelik', 'yatirim-portfoyu-takip') . '</li>
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('Tüm özelliklere erişim', 'yatirim-portfoyu-takip') . '</li>
                                                <li><i class="fas fa-check text-success me-2"></i>' . __('Sınırsız yatırım aracı', 'yatirim-portfoyu-takip') . '</li>
                                            </ul>
                                            <button class="btn btn-primary mt-auto w-100 select-plan" data-plan="annual" data-amount="449.90" data-months="12">' . __('Seç', 'yatirim-portfoyu-takip') . '</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="payment-form-container" style="display: none;">
                                <h5 class="mb-3">' . __('Ödeme Bilgileri', 'yatirim-portfoyu-takip') . '</h5>
                                
                                <form id="premium-payment-form">
                                    <input type="hidden" name="action" value="process_premium_payment">
                                    <input type="hidden" name="plan_type" id="plan_type" value="">
                                    <input type="hidden" name="plan_amount" id="plan_amount" value="">
                                    <input type="hidden" name="plan_months" id="plan_months" value="">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="card_name" class="form-label">' . __('Kart Üzerindeki İsim', 'yatirim-portfoyu-takip') . '</label>
                                            <input type="text" class="form-control" id="card_name" name="card_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="card_number" class="form-label">' . __('Kart Numarası', 'yatirim-portfoyu-takip') . '</label>
                                            <input type="text" class="form-control" id="card_number" name="card_number" required placeholder="XXXX XXXX XXXX XXXX">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="card_expiry_month" class="form-label">' . __('Son Kullanma Ay', 'yatirim-portfoyu-takip') . '</label>
                                            <select class="form-select" id="card_expiry_month" name="card_expiry_month" required>
                                                <option value="">' . __('Ay', 'yatirim-portfoyu-takip') . '</option>';
        
        for ($i = 1; $i <= 12; $i++) {
            $output .= '<option value="' . sprintf('%02d', $i) . '">' . sprintf('%02d', $i) . '</option>';
        }
        
        $output .= '</select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="card_expiry_year" class="form-label">' . __('Son Kullanma Yıl', 'yatirim-portfoyu-takip') . '</label>
                                            <select class="form-select" id="card_expiry_year" name="card_expiry_year" required>
                                                <option value="">' . __('Yıl', 'yatirim-portfoyu-takip') . '</option>';
        
        $current_year = date('Y');
        for ($i = $current_year; $i <= $current_year + 10; $i++) {
            $output .= '<option value="' . $i . '">' . $i . '</option>';
        }
        
        $output .= '</select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="card_cvv" class="form-label">CVV</label>
                                            <input type="text" class="form-control" id="card_cvv" name="card_cvv" required placeholder="XXX">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" required>
                                                <label class="form-check-label" for="terms_agreement">
                                                    ' . __('Ödeme şartlarını ve koşullarını kabul ediyorum.', 'yatirim-portfoyu-takip') . '
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="alert alert-primary d-flex align-items-center">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <div>
                                                    ' . __('Bu bir demo uygulamasıdır. Gerçek ödeme işlemi yapılmayacaktır.', 'yatirim-portfoyu-takip') . '
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-secondary" id="cancel-payment">' . __('İptal', 'yatirim-portfoyu-takip') . '</button>
                                        <button type="submit" class="btn btn-success" id="complete-payment">
                                            <span id="payment-amount"></span> ' . __('Ödeme Yap', 'yatirim-portfoyu-takip') . '
                                        </button>
                                    </div>
                                    
                                    <div id="payment-message" class="mt-3"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Plan seçimi
            $(".select-plan").on("click", function() {
                var plan = $(this).data("plan");
                var amount = $(this).data("amount");
                var months = $(this).data("months");
                
                $("#plan_type").val(plan);
                $("#plan_amount").val(amount);
                $("#plan_months").val(months);
                $("#payment-amount").text(amount + " ₺");
                
                $("#payment-form-container").slideDown();
                $("html, body").animate({ scrollTop: $("#payment-form-container").offset().top - 50 }, 500);
            });
            
            // İptal butonu
            $("#cancel-payment").on("click", function() {
                $("#payment-form-container").slideUp();
            });
            
            // Kart numarası formatı
            $("#card_number").on("input", function() {
                var value = $(this).val().replace(/\D/g, "").substring(0, 16);
                var formatted = "";
                
                for (var i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formatted += " ";
                    }
                    formatted += value.charAt(i);
                }
                
                $(this).val(formatted);
            });
            
            // CVV formatı
            $("#card_cvv").on("input", function() {
                $(this).val($(this).val().replace(/\D/g, "").substring(0, 3));
            });
            
            // Ödeme formu gönderimi
            $("#premium-payment-form").on("submit", function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += "&nonce=" + yatirim_portfoyu.nonce;
                
                $.ajax({
                    url: yatirim_portfoyu.ajax_url,
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#payment-message").html("<div class=\"alert alert-info\">' . __('Ödeme işlemi yapılıyor...', 'yatirim-portfoyu-takip') . '</div>");
                        $("#complete-payment").prop("disabled", true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#payment-message").html("<div class=\"alert alert-success\">" + response.data.message + "</div>");
                            setTimeout(function() {
                                window.location.href = response.data.redirect;
                            }, 2000);
                        } else {
                            $("#payment-message").html("<div class=\"alert alert-danger\">" + response.data.message + "</div>");
                            $("#complete-payment").prop("disabled", false);
                        }
                    },
                    error: function() {
                        $("#payment-message").html("<div class=\"alert alert-danger\">' . __('Bir hata oluştu, lütfen tekrar deneyin.', 'yatirim-portfoyu-takip') . '</div>");
                        $("#complete-payment").prop("disabled", false);
                    }
                });
            });
        });
        </script>';
        
        return $output;
    }

    /**
     * Ödeme işlemini simüle eder
     *
     * @param array $payment_data Ödeme bilgileri
     * @param int $user_id Kullanıcı ID
     * @return bool|string İşlem başarılı ise true, başarısız ise hata mesajı
     */
    public function process_payment($payment_data, $user_id) {
        // Demo uygulaması için, ödeme işlemini başarılı kabul et
        // Gerçek bir ödeme entegrasyonu burada yapılabilir (iyzico, paytr, stripe vb.)
        
        $plan_type = isset($payment_data['plan_type']) ? sanitize_text_field($payment_data['plan_type']) : '';
        $plan_amount = isset($payment_data['plan_amount']) ? floatval($payment_data['plan_amount']) : 0;
        $plan_months = isset($payment_data['plan_months']) ? intval($payment_data['plan_months']) : 0;
        
        if (empty($plan_type) || $plan_amount <= 0 || $plan_months <= 0) {
            return __('Geçersiz plan bilgileri.', 'yatirim-portfoyu-takip');
        }
        
        // Premium üyelik satın alma işlemi
        return $this->purchase_premium($user_id, 'credit_card', $plan_amount, 'TRY', $plan_months);
    }
}