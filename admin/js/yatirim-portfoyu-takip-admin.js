/**
 * Admin JavaScript
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */

(function($) {
    'use strict';

    /**
     * Sayfa yüklendiğinde çalışacak kodlar
     */
    $(document).ready(function() {
        
        // Bootstrap 5 tooltip ve popover
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        }
        
        // Sayısal inputların formatlanması
        $('input[type="number"]').on('input', function() {
            var value = $(this).val().replace(/[^0-9.]/g, '');
            $(this).val(value);
        });
        
        // Onaylama dialogları
        $('.confirm-action').on('click', function(e) {
            if (!confirm('Bu işlemi gerçekleştirmek istediğinize emin misiniz?')) {
                e.preventDefault();
                return false;
            }
        });
        
        // API anahtarı test butonları
        $('.test-api-connection').on('click', function() {
            var $button = $(this);
            var apiType = $button.data('api-type');
            var apiKeyField = $('#' + apiType + '_api_key');
            
            if (apiKeyField.length === 0 || apiKeyField.val() === '') {
                alert('API anahtarı gereklidir.');
                return;
            }
            
            var originalText = $button.text();
            $button.prop('disabled', true).text('Test Ediliyor...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    'action': 'yatirim_portfoyu_admin_action',
                    'admin_action': 'test_api_connection',
                    'api_type': apiType,
                    'api_key': apiKeyField.val(),
                    'nonce': yatirim_portfoyu_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('API bağlantısı başarılı!');
                    } else {
                        alert('API bağlantısı başarısız: ' + response.data.message);
                    }
                    $button.prop('disabled', false).text(originalText);
                },
                error: function() {
                    alert('Test sırasında bir hata oluştu.');
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // Ödeme planı seçimi
        $('.select-payment-plan').on('click', function() {
            var $button = $(this);
            var planType = $button.data('plan-type');
            var planPrice = $button.data('plan-price');
            var planDuration = $button.data('plan-duration');
            
            $('#selected_plan_type').val(planType);
            $('#selected_plan_price').val(planPrice);
            $('#selected_plan_duration').val(planDuration);
            $('#selected_plan_text').text(planPrice + ' TL - ' + planDuration + ' ay');
            
            $('#payment-form-container').slideDown();
            $('html, body').animate({
                scrollTop: $('#payment-form-container').offset().top - 50
            }, 500);
        });
        
        // Kullanıcı formu üyelik tipine göre ek alanların gösterilmesi
        $('#membership_type, #edit_membership_type').on('change', function() {
            var membershipType = $(this).val();
            var $form = $(this).closest('form');
            
            if (membershipType === 'premium') {
                $form.find('.premium-fields').slideDown();
            } else {
                $form.find('.premium-fields').slideUp();
            }
        });
        
        // API ayarları kaydet butonu
        $('.save-api-settings').on('click', function(e) {
            e.preventDefault();
            
            var $form = $(this).closest('form');
            var $button = $(this);
            var originalText = $button.text();
            
            $button.prop('disabled', true).text('Kaydediliyor...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        alert('Ayarlar başarıyla kaydedildi.');
                    } else {
                        alert('Ayarlar kaydedilirken bir hata oluştu: ' + response.data.message);
                    }
                    $button.prop('disabled', false).text(originalText);
                },
                error: function() {
                    alert('İşlem sırasında bir hata oluştu.');
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // Fiyat güncelleme butonları
        $('.update-prices').on('click', function() {
            var $button = $(this);
            var updateType = $button.data('update-type');
            var originalText = $button.text();
            
            $button.prop('disabled', true).text('Güncelleniyor...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    'action': 'yatirim_portfoyu_admin_action',
                    'admin_action': 'update_prices',
                    'update_type': updateType,
                    'nonce': yatirim_portfoyu_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Fiyatlar başarıyla güncellendi.');
                    } else {
                        alert('Fiyatlar güncellenirken bir hata oluştu: ' + response.data.message);
                    }
                    $button.prop('disabled', false).text(originalText);
                },
                error: function() {
                    alert('İşlem sırasında bir hata oluştu.');
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });
        
    });

})(jQuery);