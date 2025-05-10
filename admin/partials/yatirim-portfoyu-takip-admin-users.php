<?php
/**
 * Kullanıcı yönetimi sayfası şablonu
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
            <div class="d-flex justify-content-between align-items-center">
                <h3><?php _e('Kullanıcı Yönetimi', 'yatirim-portfoyu-takip'); ?></h3>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i> <?php _e('Yeni Kullanıcı', 'yatirim-portfoyu-takip'); ?>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php _e('Kullanıcı Adı', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('E-posta', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('Üyelik Tipi', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('Üyelik Bitiş', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('Kayıt Tarihi', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('Son Giriş', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('Durum', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('İşlemler', 'yatirim-portfoyu-takip'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)) : ?>
                            <tr>
                                <td colspan="9" class="text-center"><?php _e('Henüz kullanıcı bulunmuyor.', 'yatirim-portfoyu-takip'); ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td><?php echo esc_html($user['id']); ?></td>
                                    <td><?php echo esc_html($user['username']); ?></td>
                                    <td><?php echo esc_html($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['membership_type'] === 'premium') : ?>
                                            <span class="badge bg-success"><?php _e('Premium', 'yatirim-portfoyu-takip'); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary"><?php _e('Ücretsiz', 'yatirim-portfoyu-takip'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($user['membership_type'] === 'premium' && $user['membership_expires']) {
                                            echo date_i18n(get_option('date_format'), strtotime($user['membership_expires']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php 
                                        if ($user['last_login']) {
                                            echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($user['last_login']));
                                        } else {
                                            _e('Hiç giriş yapmadı', 'yatirim-portfoyu-takip');
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'active') : ?>
                                            <span class="badge bg-success"><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-danger"><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info view-user-btn" data-user-id="<?php echo esc_attr($user['id']); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary edit-user-btn" data-user-id="<?php echo esc_attr($user['id']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-users&action=delete&user_id=' . $user['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php _e('Bu kullanıcıyı silmek istediğinize emin misiniz?', 'yatirim-portfoyu-takip'); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1) : ?>
                <div class="pagination">
                    <nav aria-label="<?php _e('Sayfalama', 'yatirim-portfoyu-takip'); ?>">
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo admin_url('admin.php?page=yatirim-portfoyu-users&paged=' . $i); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Kullanıcı Ekle Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel"><?php _e('Yeni Kullanıcı Ekle', 'yatirim-portfoyu-takip'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label"><?php _e('Kullanıcı Adı', 'yatirim-portfoyu-takip'); ?></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><?php _e('E-posta', 'yatirim-portfoyu-takip'); ?></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label"><?php _e('Parola', 'yatirim-portfoyu-takip'); ?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="membership_type" class="form-label"><?php _e('Üyelik Tipi', 'yatirim-portfoyu-takip'); ?></label>
                        <select class="form-select" id="membership_type" name="membership_type">
                            <option value="free"><?php _e('Ücretsiz', 'yatirim-portfoyu-takip'); ?></option>
                            <option value="premium"><?php _e('Premium', 'yatirim-portfoyu-takip'); ?></option>
                        </select>
                    </div>
                    
                    <div class="mb-3 premium-fields" style="display:none;">
                        <label for="membership_expires" class="form-label"><?php _e('Üyelik Bitiş Tarihi', 'yatirim-portfoyu-takip'); ?></label>
                        <input type="date" class="form-control" id="membership_expires" name="membership_expires">
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('İptal', 'yatirim-portfoyu-takip'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php _e('Kullanıcı Ekle', 'yatirim-portfoyu-takip'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Kullanıcı Düzenle Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel"><?php _e('Kullanıcı Düzenle', 'yatirim-portfoyu-takip'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="mb-3">
                        <label for="edit_username" class="form-label"><?php _e('Kullanıcı Adı', 'yatirim-portfoyu-takip'); ?></label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label"><?php _e('E-posta', 'yatirim-portfoyu-takip'); ?></label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_password" class="form-label"><?php _e('Parola (boş bırakırsanız değişmez)', 'yatirim-portfoyu-takip'); ?></label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_membership_type" class="form-label"><?php _e('Üyelik Tipi', 'yatirim-portfoyu-takip'); ?></label>
                        <select class="form-select" id="edit_membership_type" name="membership_type">
                            <option value="free"><?php _e('Ücretsiz', 'yatirim-portfoyu-takip'); ?></option>
                            <option value="premium"><?php _e('Premium', 'yatirim-portfoyu-takip'); ?></option>
                        </select>
                    </div>
                    
                    <div class="mb-3 edit-premium-fields" style="display:none;">
                        <label for="edit_membership_expires" class="form-label"><?php _e('Üyelik Bitiş Tarihi', 'yatirim-portfoyu-takip'); ?></label>
                        <input type="date" class="form-control" id="edit_membership_expires" name="membership_expires">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label"><?php _e('Durum', 'yatirim-portfoyu-takip'); ?></label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active"><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></option>
                            <option value="inactive"><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('İptal', 'yatirim-portfoyu-takip'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php _e('Kaydet', 'yatirim-portfoyu-takip'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Kullanıcı Detay Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel"><?php _e('Kullanıcı Detayları', 'yatirim-portfoyu-takip'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="userDetails" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden"><?php _e('Yükleniyor...', 'yatirim-portfoyu-takip'); ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Kapat', 'yatirim-portfoyu-takip'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Premium alanlarını göster/gizle
    $('#membership_type').on('change', function() {
        if ($(this).val() === 'premium') {
            $('.premium-fields').show();
        } else {
            $('.premium-fields').hide();
        }
    });
    
    $('#edit_membership_type').on('change', function() {
        if ($(this).val() === 'premium') {
            $('.edit-premium-fields').show();
        } else {
            $('.edit-premium-fields').hide();
        }
    });
    
    // Kullanıcı düzenleme modalı
    $('.edit-user-btn').on('click', function() {
        var userId = $(this).data('user-id');
        
        // AJAX ile kullanıcı bilgilerini al
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yatirim_portfoyu_admin_action',
                admin_action: 'get_user_details',
                user_id: userId,
                nonce: yatirim_portfoyu_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var user = response.data.user;
                    
                    $('#edit_user_id').val(user.id);
                    $('#edit_username').val(user.username);
                    $('#edit_email').val(user.email);
                    $('#edit_membership_type').val(user.membership_type);
                    $('#edit_status').val(user.status);
                    
                    if (user.membership_expires) {
                        // ISO formatını YYYY-MM-DD'ye çevir
                        var expiryDate = new Date(user.membership_expires);
                        var formattedDate = expiryDate.toISOString().split('T')[0];
                        $('#edit_membership_expires').val(formattedDate);
                    } else {
                        $('#edit_membership_expires').val('');
                    }
                    
                    // Premium alanlarını göster/gizle
                    if (user.membership_type === 'premium') {
                        $('.edit-premium-fields').show();
                    } else {
                        $('.edit-premium-fields').hide();
                    }
                    
                    $('#editUserModal').modal('show');
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('<?php _e('Bir hata oluştu, lütfen tekrar deneyin.', 'yatirim-portfoyu-takip'); ?>');
            }
        });
    });
    
    // Kullanıcı detay modalı
    $('.view-user-btn').on('click', function() {
        var userId = $(this).data('user-id');
        
        $('#viewUserModal').modal('show');
        
        // AJAX ile kullanıcı detaylarını al
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yatirim_portfoyu_admin_action',
                admin_action: 'get_user_details',
                user_id: userId,
                nonce: yatirim_portfoyu_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var user = response.data.user;
                    var stats = response.data.stats;
                    
                    var html = '<div class="row">';
                    
                    // Kullanıcı bilgileri
                    html += '<div class="col-md-6">';
                    html += '<div class="card mb-4">';
                    html += '<div class="card-header bg-primary text-white"><h5><?php _e('Kullanıcı Bilgileri', 'yatirim-portfoyu-takip'); ?></h5></div>';
                    html += '<div class="card-body">';
                    html += '<table class="table">';
                    html += '<tr><th><?php _e('ID', 'yatirim-portfoyu-takip'); ?></th><td>' + user.id + '</td></tr>';
                    html += '<tr><th><?php _e('Kullanıcı Adı', 'yatirim-portfoyu-takip'); ?></th><td>' + user.username + '</td></tr>';
                    html += '<tr><th><?php _e('E-posta', 'yatirim-portfoyu-takip'); ?></th><td>' + user.email + '</td></tr>';
                    html += '<tr><th><?php _e('Üyelik Tipi', 'yatirim-portfoyu-takip'); ?></th><td>';
                    
                    if (user.membership_type === 'premium') {
                        html += '<span class="badge bg-success"><?php _e('Premium', 'yatirim-portfoyu-takip'); ?></span>';
                    } else {
                        html += '<span class="badge bg-secondary"><?php _e('Ücretsiz', 'yatirim-portfoyu-takip'); ?></span>';
                    }
                    
                    html += '</td></tr>';
                    
                    if (user.membership_type === 'premium' && user.membership_expires) {
                        html += '<tr><th><?php _e('Üyelik Bitiş', 'yatirim-portfoyu-takip'); ?></th><td>' + new Date(user.membership_expires).toLocaleDateString() + '</td></tr>';
                    }
                    
                    html += '<tr><th><?php _e('Durum', 'yatirim-portfoyu-takip'); ?></th><td>';
                    
                    if (user.status === 'active') {
                        html += '<span class="badge bg-success"><?php _e('Aktif', 'yatirim-portfoyu-takip'); ?></span>';
                    } else {
                        html += '<span class="badge bg-danger"><?php _e('Pasif', 'yatirim-portfoyu-takip'); ?></span>';
                    }
                    
                    html += '</td></tr>';
                    html += '<tr><th><?php _e('Kayıt Tarihi', 'yatirim-portfoyu-takip'); ?></th><td>' + new Date(user.created_at).toLocaleDateString() + '</td></tr>';
                    
                    if (user.last_login) {
                        html += '<tr><th><?php _e('Son Giriş', 'yatirim-portfoyu-takip'); ?></th><td>' + new Date(user.last_login).toLocaleString() + '</td></tr>';
                    } else {
                        html += '<tr><th><?php _e('Son Giriş', 'yatirim-portfoyu-takip'); ?></th><td><?php _e('Hiç giriş yapmadı', 'yatirim-portfoyu-takip'); ?></td></tr>';
                    }
                    
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Kullanıcı istatistikleri
                    html += '<div class="col-md-6">';
                    html += '<div class="card mb-4">';
                    html += '<div class="card-header bg-success text-white"><h5><?php _e('Portföy İstatistikleri', 'yatirim-portfoyu-takip'); ?></h5></div>';
                    html += '<div class="card-body">';
                    html += '<table class="table">';
                    html += '<tr><th><?php _e('Hisse Senetleri', 'yatirim-portfoyu-takip'); ?></th><td>' + stats.stocks_count + '</td></tr>';
                    html += '<tr><th><?php _e('Kripto Paralar', 'yatirim-portfoyu-takip'); ?></th><td>' + stats.crypto_count + '</td></tr>';
                    html += '<tr><th><?php _e('Altın', 'yatirim-portfoyu-takip'); ?></th><td>' + stats.gold_count + '</td></tr>';
                    html += '<tr><th><?php _e('Fonlar', 'yatirim-portfoyu-takip'); ?></th><td>' + stats.funds_count + '</td></tr>';
                    html += '<tr><th><?php _e('Toplam İşlemler', 'yatirim-portfoyu-takip'); ?></th><td>' + 
                            (parseInt(stats.stock_transactions) + parseInt(stats.crypto_transactions) + 
                             parseInt(stats.gold_transactions) + parseInt(stats.fund_transactions)) + '</td></tr>';
                    html += '<tr><th><?php _e('Toplam Temettüler', 'yatirim-portfoyu-takip'); ?></th><td>' + stats.dividends_count + '</td></tr>';
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '</div>';
                    
                    $('#userDetails').html(html);
                } else {
                    $('#userDetails').html('<div class="alert alert-danger">' + response.data.message + '</div>');
                }
            },
            error: function() {
                $('#userDetails').html('<div class="alert alert-danger"><?php _e('Bir hata oluştu, lütfen tekrar deneyin.', 'yatirim-portfoyu-takip'); ?></div>');
            }
        });
    });
});
</script>