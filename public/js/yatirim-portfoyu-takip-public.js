/**
 * Public JavaScript
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
        
        // Login ve register formları arasında geçiş
        $('#show-register-form').on('click', function(e) {
            e.preventDefault();
            $('#yatirim-portfoyu-login-form').hide();
            $('#yatirim-portfoyu-register-form').fadeIn();
        });
        
        $('#show-login-form').on('click', function(e) {
            e.preventDefault();
            $('#yatirim-portfoyu-register-form').hide();
            $('#yatirim-portfoyu-login-form').fadeIn();
        });
        
        // Login form submit
        $('#yatirim-portfoyu-login-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = {
                action: 'yatirim_portfoyu_login_action',
                username: $('#username').val(),
                password: $('#password').val(),
                nonce: yatirim_portfoyu.nonce
            };
            
            $.ajax({
                url: yatirim_portfoyu.ajax_url,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#login-message').html('<div class="alert alert-info">Giriş yapılıyor...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        $('#login-message').html('<div class="alert alert-success">' + response.data.message + '</div>');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        $('#login-message').html('<div class="alert alert-danger">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    $('#login-message').html('<div class="alert alert-danger">Bir hata oluştu, lütfen tekrar deneyin.</div>');
                }
            });
        });
        
        // Register form validasyonu
        $('#yatirim-portfoyu-register-form').on('submit', function(e) {
            e.preventDefault();
            
            var password = $('#reg_password').val();
            var password_confirm = $('#reg_password_confirm').val();
            
            if (password !== password_confirm) {
                $('#register-message').html('<div class="alert alert-danger">Parolalar eşleşmiyor.</div>');
                return;
            }
            
            if (!$('#terms_agree').is(':checked')) {
                $('#register-message').html('<div class="alert alert-danger">Kullanım şartlarını kabul etmelisiniz.</div>');
                return;
            }
            
            var formData = {
                action: 'yatirim_portfoyu_register_action',
                username: $('#reg_username').val(),
                email: $('#reg_email').val(),
                password: password,
                password_confirm: password_confirm,
                terms_agree: $('#terms_agree').is(':checked'),
                nonce: yatirim_portfoyu.nonce
            };
            
            $.ajax({
                url: yatirim_portfoyu.ajax_url,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#register-message').html('<div class="alert alert-info">Kayıt işlemi yapılıyor...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        $('#register-message').html('<div class="alert alert-success">' + response.data.message + '</div>');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        $('#register-message').html('<div class="alert alert-danger">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    $('#register-message').html('<div class="alert alert-danger">Bir hata oluştu, lütfen tekrar deneyin.</div>');
                }
            });
        });
        
        // Sayısal inputların formatlanması
        $('input[type="number"]').on('change', function() {
            var value = parseFloat($(this).val());
            if (!isNaN(value)) {
                $(this).val(value);
            }
        });
        
        // Tarih alanı bugünün tarihi ile doldurulması
        if ($('#transaction_date').length > 0) {
            var now = new Date();
            var dateTimeStr = now.getFullYear() + '-' + 
                            String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                            String(now.getDate()).padStart(2, '0') + 'T' + 
                            String(now.getHours()).padStart(2, '0') + ':' + 
                            String(now.getMinutes()).padStart(2, '0');
            $('#transaction_date').val(dateTimeStr);
        }
        
        // Mobil cihazlarda tablo hücrelerini etiketleme
        if (window.innerWidth < 768) {
            $('.table-responsive table').find('th').each(function(index) {
                var label = $(this).text();
                $('.table-responsive table tr td:nth-child(' + (index + 1) + ')').attr('data-label', label);
            });
        }
        
        // Hisse işlemi formu gönderildiğinde
        $('#stockTransactionForm').on('submit', function(e) {
            e.preventDefault();
            $('#saveStockTransaction').trigger('click');
        });
        
        // Kripto işlemi formu gönderildiğinde
        $('#cryptoTransactionForm').on('submit', function(e) {
            e.preventDefault();
            $('#saveCryptoTransaction').trigger('click');
        });
        
        // Altın işlemi formu gönderildiğinde
        $('#goldTransactionForm').on('submit', function(e) {
            e.preventDefault();
            $('#saveGoldTransaction').trigger('click');
        });
        
        // Fon işlemi formu gönderildiğinde
        $('#fundTransactionForm').on('submit', function(e) {
            e.preventDefault();
            $('#saveFundTransaction').trigger('click');
        });
        
        // Silme onayı
        $('a.delete-btn').on('click', function(e) {
            if (!confirm(yatirim_portfoyu.texts.confirm_delete)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Toplam tutar otomatik hesaplama
        function calculateTotal() {
            var amount = parseFloat($('#amount, #weight, #shares').val()) || 0;
            var price = parseFloat($('#price').val()) || 0;
            $('#total').val((amount * price).toFixed(2));
        }
        
        $('#amount, #weight, #shares, #price').on('input', calculateTotal);
        
        // Bootstrap 5 tooltip
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
    });

})(jQuery);