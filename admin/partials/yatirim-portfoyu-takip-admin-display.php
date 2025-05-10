<?php
/**
 * Ana admin sayfası şablonu
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */
?>

<div class="wrap yatirim-portfoyu-takip">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('Yatırım Portföyü Takip eklentisi ile kullanıcılar kendi portföylerini yönetebilir ve takip edebilir.', 'yatirim-portfoyu-takip'); ?></p>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3><?php _e('Yönetim Paneli', 'yatirim-portfoyu-takip'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <h5><?php _e('Toplam Kullanıcılar', 'yatirim-portfoyu-takip'); ?></h5>
                                    <h2><?php echo esc_html($total_users); ?></h2>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-users'); ?>"><?php _e('Detayları Görüntüle', 'yatirim-portfoyu-takip'); ?></a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body">
                                    <h5><?php _e('Premium Üyeler', 'yatirim-portfoyu-takip'); ?></h5>
                                    <h2><?php echo esc_html($premium_users); ?></h2>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-users'); ?>"><?php _e('Detayları Görüntüle', 'yatirim-portfoyu-takip'); ?></a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning mb-4">
                                <div class="card-body">
                                    <h5><?php _e('Ücretsiz Üyeler', 'yatirim-portfoyu-takip'); ?></h5>
                                    <h2><?php echo esc_html($free_users); ?></h2>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-dark stretched-link" href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-users'); ?>"><?php _e('Detayları Görüntüle', 'yatirim-portfoyu-takip'); ?></a>
                                    <div class="small text-dark"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5><?php _e('API Durumu', 'yatirim-portfoyu-takip'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th><?php _e('API', 'yatirim-portfoyu-takip'); ?></th>
                                                <th><?php _e('Durum', 'yatirim-portfoyu-takip'); ?></th>
                                                <th><?php _e('İşlemler', 'yatirim-portfoyu-takip'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php _e('Borsa İstanbul API', 'yatirim-portfoyu-takip'); ?></td>
                                                <td>
                                                    <?php if ($collectapi_bist && $collectapi_bist['status'] === 'active') : ?>
                                                        <span class="badge bg-success"><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></span>
                                                    <?php else : ?>
                                                        <span class="badge bg-danger"><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-api'); ?>" class="btn btn-sm btn-primary"><?php _e('Düzenle', 'yatirim-portfoyu-takip'); ?></a>
                                                    <button class="btn btn-sm btn-info update-prices-btn" data-type="stocks"><?php _e('Fiyat Güncelle', 'yatirim-portfoyu-takip'); ?></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Altın API', 'yatirim-portfoyu-takip'); ?></td>
                                                <td>
                                                    <?php if ($collectapi_gold && $collectapi_gold['status'] === 'active') : ?>
                                                        <span class="badge bg-success"><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></span>
                                                    <?php else : ?>
                                                        <span class="badge bg-danger"><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-api'); ?>" class="btn btn-sm btn-primary"><?php _e('Düzenle', 'yatirim-portfoyu-takip'); ?></a>
                                                    <button class="btn btn-sm btn-info update-prices-btn" data-type="gold"><?php _e('Fiyat Güncelle', 'yatirim-portfoyu-takip'); ?></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Döviz API', 'yatirim-portfoyu-takip'); ?></td>
                                                <td>
                                                    <?php if ($collectapi_currency && $collectapi_currency['status'] === 'active') : ?>
                                                        <span class="badge bg-success"><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></span>
                                                    <?php else : ?>
                                                        <span class="badge bg-danger"><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-api'); ?>" class="btn btn-sm btn-primary"><?php _e('Düzenle', 'yatirim-portfoyu-takip'); ?></a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5><?php _e('İşlem İstatistikleri', 'yatirim-portfoyu-takip'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td><?php _e('Toplam Hisse Senedi', 'yatirim-portfoyu-takip'); ?></td>
                                                <td class="text-end"><?php echo esc_html($total_stocks); ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Toplam Kripto Para', 'yatirim-portfoyu-takip'); ?></td>
                                                <td class="text-end"><?php echo esc_html($total_crypto); ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Toplam Altın', 'yatirim-portfoyu-takip'); ?></td>
                                                <td class="text-end"><?php echo esc_html($total_gold); ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Toplam Fon', 'yatirim-portfoyu-takip'); ?></td>
                                                <td class="text-end"><?php echo esc_html($total_funds); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button class="btn btn-primary w-100 update-prices-btn" data-type="all"><?php _e('Tüm Fiyatları Güncelle', 'yatirim-portfoyu-takip'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h3><?php _e('Son Kullanıcılar', 'yatirim-portfoyu-takip'); ?></h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php _e('Kullanıcı', 'yatirim-portfoyu-takip'); ?></th>
                                <th><?php _e('Üyelik', 'yatirim-portfoyu-takip'); ?></th>
                                <th><?php _e('Tarih', 'yatirim-portfoyu-takip'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_users)) : ?>
                                <tr>
                                    <td colspan="3" class="text-center"><?php _e('Henüz kullanıcı bulunmuyor.', 'yatirim-portfoyu-takip'); ?></td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($recent_users as $user) : ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-users&action=view&user_id=' . $user['id']); ?>">
                                                <?php echo esc_html($user['username']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($user['membership_type'] === 'premium') : ?>
                                                <span class="badge bg-success"><?php _e('Premium', 'yatirim-portfoyu-takip'); ?></span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary"><?php _e('Ücretsiz', 'yatirim-portfoyu-takip'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date_i18n(get_option('date_format'), strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <a href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-users'); ?>" class="btn btn-primary w-100"><?php _e('Tüm Kullanıcıları Görüntüle', 'yatirim-portfoyu-takip'); ?></a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3><?php _e('Kısa Kodlar', 'yatirim-portfoyu-takip'); ?></h3>
                </div>
                <div class="card-body">
                    <p><?php _e('Eklentiyi sayfalarınıza eklemek için aşağıdaki kısa kodları kullanabilirsiniz:', 'yatirim-portfoyu-takip'); ?></p>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>[yatirim_portfoyu]</strong>
                            <p class="mb-0 small"><?php _e('Ana portföy takip sayfası', 'yatirim-portfoyu-takip'); ?></p>
                        </li>
                        <li class="list-group-item">
                            <strong>[yatirim_portfoyu_login]</strong>
                            <p class="mb-0 small"><?php _e('Giriş sayfası', 'yatirim-portfoyu-takip'); ?></p>
                        </li>
                        <li class="list-group-item">
                            <strong>[yatirim_portfoyu_register]</strong>
                            <p class="mb-0 small"><?php _e('Kayıt sayfası', 'yatirim-portfoyu-takip'); ?></p>
                        </li>
                    </ul>
                    <a href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-shortcodes'); ?>" class="btn btn-primary w-100 mt-3"><?php _e('Tüm Kısa Kodlar', 'yatirim-portfoyu-takip'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Fiyat güncelleme işlemi
    $('.update-prices-btn').on('click', function() {
        var button = $(this);
        var originalText = button.text();
        var type = button.data('type');
        
        button.prop('disabled', true).text('<?php _e('Güncelleniyor...', 'yatirim-portfoyu-takip'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yatirim_portfoyu_admin_action',
                admin_action: 'update_prices_manually',
                type: type,
                nonce: yatirim_portfoyu_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
                button.prop('disabled', false).text(originalText);
            },
            error: function() {
                alert('<?php _e('Bir hata oluştu, lütfen tekrar deneyin.', 'yatirim-portfoyu-takip'); ?>');
                button.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>