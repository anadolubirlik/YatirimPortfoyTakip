<?php
/**
 * API ayarları sayfası şablonu
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */
?>

<div class="wrap yatirim-portfoyu-takip">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (!empty($message)) : ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3><?php _e('API Ayarları', 'yatirim-portfoyu-takip'); ?></h3>
        </div>
        <div class="card-body">
            <p><?php _e('Eklenti, aşağıdaki API\'ler aracılığıyla güncel fiyat bilgilerini çeker. Çalışması için API anahtarlarının doğru şekilde yapılandırılması gerekir.', 'yatirim-portfoyu-takip'); ?></p>
            
            <ul class="nav nav-tabs mb-3" id="apiTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="bist-tab" data-bs-toggle="tab" data-bs-target="#bist" type="button" role="tab" aria-controls="bist" aria-selected="true">
                        <?php _e('Borsa İstanbul API', 'yatirim-portfoyu-takip'); ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="gold-tab" data-bs-toggle="tab" data-bs-target="#gold" type="button" role="tab" aria-controls="gold" aria-selected="false">
                        <?php _e('Altın API', 'yatirim-portfoyu-takip'); ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="currency-tab" data-bs-toggle="tab" data-bs-target="#currency" type="button" role="tab" aria-controls="currency" aria-selected="false">
                        <?php _e('Döviz API', 'yatirim-portfoyu-takip'); ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="crypto-tab" data-bs-toggle="tab" data-bs-target="#crypto" type="button" role="tab" aria-controls="crypto" aria-selected="false">
                        <?php _e('Kripto API', 'yatirim-portfoyu-takip'); ?>
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="apiTabsContent">
                <!-- Borsa İstanbul API -->
                <div class="tab-pane fade show active" id="bist" role="tabpanel" aria-labelledby="bist-tab">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5><?php _e('Borsa İstanbul API Ayarları (CollectAPI)', 'yatirim-portfoyu-takip'); ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="save_api_settings">
                                <input type="hidden" name="api_name" value="collectapi_bist">
                                
                                <div class="mb-3">
                                    <label for="bist_api_key" class="form-label"><?php _e('API Anahtarı', 'yatirim-portfoyu-takip'); ?></label>
                                    <input type="text" class="form-control" id="bist_api_key" name="api_key" value="<?php echo esc_attr($collectapi_bist['api_key'] ?? ''); ?>">
                                    <div class="form-text"><?php _e('CollectAPI hisse senedi veri API anahtarı.', 'yatirim-portfoyu-takip'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="bist_status" class="form-label"><?php _e('API Durumu', 'yatirim-portfoyu-takip'); ?></label>
                                    <select class="form-select" id="bist_status" name="status">
                                        <option value="active" <?php selected(($collectapi_bist['status'] ?? ''), 'active'); ?>><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></option>
                                        <option value="inactive" <?php selected(($collectapi_bist['status'] ?? ''), 'inactive'); ?>><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></option>
                                    </select>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-info test-api-btn" data-api-name="collectapi_bist" data-api-key-field="bist_api_key">
                                        <?php _e('API Bağlantısını Test Et', 'yatirim-portfoyu-takip'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-primary"><?php _e('Ayarları Kaydet', 'yatirim-portfoyu-takip'); ?></button>
                                </div>
                                
                                <div id="bist_api_test_result" class="mt-3"></div>
                                
                                <div class="mt-4">
                                    <div class="alert alert-info">
                                        <h5><?php _e('CollectAPI Hakkında', 'yatirim-portfoyu-takip'); ?></h5>
                                        <p><?php _e('CollectAPI, Borsa İstanbul hisse senetleri dahil birçok veri kaynağı için API hizmetleri sunan bir servistir.', 'yatirim-portfoyu-takip'); ?></p>
                                        <p><?php _e('API anahtarı edinmek için:', 'yatirim-portfoyu-takip'); ?></p>
                                        <ol>
                                            <li><?php _e('<a href="https://collectapi.com/tr/api/economy/borsa-istanbul-api" target="_blank">CollectAPI Borsa İstanbul API</a> sayfasını ziyaret edin.', 'yatirim-portfoyu-takip'); ?></li>
                                            <li><?php _e('Üye olun ve ücretsiz veya ücretli paketlerden birini seçin.', 'yatirim-portfoyu-takip'); ?></li>
                                            <li><?php _e('API anahtarınızı bu sayfaya girin.', 'yatirim-portfoyu-takip'); ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Altın API -->
                <div class="tab-pane fade" id="gold" role="tabpanel" aria-labelledby="gold-tab">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h5><?php _e('Altın API Ayarları (CollectAPI)', 'yatirim-portfoyu-takip'); ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="save_api_settings">
                                <input type="hidden" name="api_name" value="collectapi_gold">
                                
                                <div class="mb-3">
                                    <label for="gold_api_key" class="form-label"><?php _e('API Anahtarı', 'yatirim-portfoyu-takip'); ?></label>
                                    <input type="text" class="form-control" id="gold_api_key" name="api_key" value="<?php echo esc_attr($collectapi_gold['api_key'] ?? ''); ?>">
                                    <div class="form-text"><?php _e('CollectAPI altın veri API anahtarı.', 'yatirim-portfoyu-takip'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="gold_status" class="form-label"><?php _e('API Durumu', 'yatirim-portfoyu-takip'); ?></label>
                                    <select class="form-select" id="gold_status" name="status">
                                        <option value="active" <?php selected(($collectapi_gold['status'] ?? ''), 'active'); ?>><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></option>
                                        <option value="inactive" <?php selected(($collectapi_gold['status'] ?? ''), 'inactive'); ?>><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></option>
                                    </select>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-info test-api-btn" data-api-name="collectapi_gold" data-api-key-field="gold_api_key">
                                        <?php _e('API Bağlantısını Test Et', 'yatirim-portfoyu-takip'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-primary"><?php _e('Ayarları Kaydet', 'yatirim-portfoyu-takip'); ?></button>
                                </div>
                                
                                <div id="gold_api_test_result" class="mt-3"></div>
                                
                                <div class="mt-4">
                                    <div class="alert alert-info">
                                        <h5><?php _e('CollectAPI Altın Verileri Hakkında', 'yatirim-portfoyu-takip'); ?></h5>
                                        <p><?php _e('CollectAPI Altın API\'si, güncel altın fiyatlarını sağlar.', 'yatirim-portfoyu-takip'); ?></p>
                                        <p><?php _e('API anahtarı edinmek için:', 'yatirim-portfoyu-takip'); ?></p>
                                        <ol>
                                            <li><?php _e('<a href="https://collectapi.com/tr/api/economy/altin-doviz-ve-borsa-api" target="_blank">CollectAPI Altın API</a> sayfasını ziyaret edin.', 'yatirim-portfoyu-takip'); ?></li>
                                            <li><?php _e('Üye olun ve ücretsiz veya ücretli paketlerden birini seçin.', 'yatirim-portfoyu-takip'); ?></li>
                                            <li><?php _e('API anahtarınızı bu sayfaya girin.', 'yatirim-portfoyu-takip'); ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Döviz API -->
                <div class="tab-pane fade" id="currency" role="tabpanel" aria-labelledby="currency-tab">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5><?php _e('Döviz API Ayarları (CollectAPI)', 'yatirim-portfoyu-takip'); ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="save_api_settings">
                                <input type="hidden" name="api_name" value="collectapi_currency">
                                
                                <div class="mb-3">
                                    <label for="currency_api_key" class="form-label"><?php _e('API Anahtarı', 'yatirim-portfoyu-takip'); ?></label>
                                    <input type="text" class="form-control" id="currency_api_key" name="api_key" value="<?php echo esc_attr($collectapi_currency['api_key'] ?? ''); ?>">
                                    <div class="form-text"><?php _e('CollectAPI döviz veri API anahtarı.', 'yatirim-portfoyu-takip'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="currency_status" class="form-label"><?php _e('API Durumu', 'yatirim-portfoyu-takip'); ?></label>
                                    <select class="form-select" id="currency_status" name="status">
                                        <option value="active" <?php selected(($collectapi_currency['status'] ?? ''), 'active'); ?>><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></option>
                                        <option value="inactive" <?php selected(($collectapi_currency['status'] ?? ''), 'inactive'); ?>><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></option>
                                    </select>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-info test-api-btn" data-api-name="collectapi_currency" data-api-key-field="currency_api_key">
                                        <?php _e('API Bağlantısını Test Et', 'yatirim-portfoyu-takip'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-primary"><?php _e('Ayarları Kaydet', 'yatirim-portfoyu-takip'); ?></button>
                                </div>
                                
                                <div id="currency_api_test_result" class="mt-3"></div>
                                
                                <div class="mt-4">
                                    <div class="alert alert-info">
                                        <h5><?php _e('Döviz API\'si Ne İçin Kullanılır?', 'yatirim-portfoyu-takip'); ?></h5>
                                        <p><?php _e('Döviz API\'si, özellikle yabancı para birimi cinsinden olan kripto para değerlerini TL\'ye çevirmek için kullanılır.', 'yatirim-portfoyu-takip'); ?></p>
                                        <p><?php _e('API anahtarı edinmek için:', 'yatirim-portfoyu-takip'); ?></p>
                                        <ol>
                                            <li><?php _e('<a href="https://collectapi.com/tr/api/economy/altin-doviz-ve-borsa-api" target="_blank">CollectAPI Döviz API</a> sayfasını ziyaret edin.', 'yatirim-portfoyu-takip'); ?></li>
                                            <li><?php _e('Üye olun ve ücretsiz veya ücretli paketlerden birini seçin.', 'yatirim-portfoyu-takip'); ?></li>
                                            <li><?php _e('API anahtarınızı bu sayfaya girin.', 'yatirim-portfoyu-takip'); ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Kripto API -->
                <div class="tab-pane fade" id="crypto" role="tabpanel" aria-labelledby="crypto-tab">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5><?php _e('Kripto API Ayarları (CoinGecko)', 'yatirim-portfoyu-takip'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <p><strong><?php _e('Not:', 'yatirim-portfoyu-takip'); ?></strong> <?php _e('Kripto veriler için CoinGecko API\'sinin ücretsiz sürümü kullanılmaktadır. Ücretsiz sürüm sınırlı sayıda istek desteği sunar, ancak çoğu kişisel kullanım için yeterlidir.', 'yatirim-portfoyu-takip'); ?></p>
                                <p><?php _e('Ücretsiz API erişiminde API anahtarına ihtiyaç yoktur. API çağrıları otomatik olarak sınırlandırılır.', 'yatirim-portfoyu-takip'); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><?php _e('API Durumu', 'yatirim-portfoyu-takip'); ?></label>
                                <select class="form-select" disabled>
                                    <option value="active" selected><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></option>
                                </select>
                                <div class="form-text"><?php _e('CoinGecko API otomatik olarak aktiftir ve yapılandırma gerektirmez.', 'yatirim-portfoyu-takip'); ?></div>
                            </div>
                            
                            <button type="button" class="btn btn-info test-api-btn" data-api-name="coingecko" data-api-key-field="">
                                <?php _e('API Bağlantısını Test Et', 'yatirim-portfoyu-takip'); ?>
                            </button>
                            
                            <div id="crypto_api_test_result" class="mt-3"></div>
                            
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <h5><?php _e('CoinGecko API Hakkında', 'yatirim-portfoyu-takip'); ?></h5>
                                    <p><?php _e('CoinGecko, kripto para verileri için ücretsiz bir API hizmeti sunar.', 'yatirim-portfoyu-takip'); ?></p>
                                    <p><?php _e('Daha fazla bilgi için:', 'yatirim-portfoyu-takip'); ?></p>
                                    <ol>
                                        <li><?php _e('<a href="https://www.coingecko.com/en/api" target="_blank">CoinGecko API Dokümantasyonu</a> sayfasını ziyaret edin.', 'yatirim-portfoyu-takip'); ?></li>
                                        <li><?php _e('İhtiyaç duyarsanız, Pro sürüme geçerek daha yüksek istek limitlerine sahip olabilirsiniz.', 'yatirim-portfoyu-takip'); ?></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // API bağlantı testi
    $('.test-api-btn').on('click', function() {
        var button = $(this);
        var originalText = button.text();
        var apiName = button.data('api-name');
        var apiKeyField = button.data('api-key-field');
        var apiKey = '';
        
        if (apiKeyField) {
            apiKey = $('#' + apiKeyField).val();
        }
        
        if (apiKeyField && !apiKey) {
            $('#' + apiName + '_api_test_result').html('<div class="alert alert-danger"><?php _e('API anahtarı gereklidir.', 'yatirim-portfoyu-takip'); ?></div>');
            return;
        }
        
        button.prop('disabled', true).text('<?php _e('Test Ediliyor...', 'yatirim-portfoyu-takip'); ?>');
        
        // API bağlantı testi AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yatirim_portfoyu_admin_action',
                admin_action: 'test_api_connection',
                api_name: apiName,
                api_key: apiKey,
                nonce: yatirim_portfoyu_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#' + apiName + '_api_test_result').html('<div class="alert alert-success">' + response.data.message + '</div>');
                } else {
                    $('#' + apiName + '_api_test_result').html('<div class="alert alert-danger">' + response.data.message + '</div>');
                }
                button.prop('disabled', false).text(originalText);
            },
            error: function() {
                $('#' + apiName + '_api_test_result').html('<div class="alert alert-danger"><?php _e('Test sırasında bir hata oluştu.', 'yatirim-portfoyu-takip'); ?></div>');
                button.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>