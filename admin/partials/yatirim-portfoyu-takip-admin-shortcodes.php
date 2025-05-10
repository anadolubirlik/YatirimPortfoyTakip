<?php
/**
 * Kısa kodlar sayfası şablonu
 *
 * @since      1.0.0
 * @package    Yatirim_Portfoyu_Takip
 */
?>

<div class="wrap yatirim-portfoyu-takip">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('Bu eklentinin kısa kodlarını kullanarak portföy takip sistemini WordPress sitenize ekleyebilirsiniz.', 'yatirim-portfoyu-takip'); ?></p>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3><?php _e('Mevcut Kısa Kodlar', 'yatirim-portfoyu-takip'); ?></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php _e('Kısa Kod', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('Açıklama', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('Parametreler', 'yatirim-portfoyu-takip'); ?></th>
                            <th><?php _e('Örnek Kullanım', 'yatirim-portfoyu-takip'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[yatirim_portfoyu]</code></td>
                            <td><?php _e('Ana portföy takip sayfası. Kullanıcı giriş yapmadıysa giriş formu gösterilir.', 'yatirim-portfoyu-takip'); ?></td>
                            <td><code>view</code>: <?php _e('Görüntülenecek sekme (summary, stocks, crypto, gold, funds, reports, profile)', 'yatirim-portfoyu-takip'); ?></td>
                            <td><code>[yatirim_portfoyu view="stocks"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[yatirim_portfoyu_login]</code></td>
                            <td><?php _e('Giriş formunu gösterir. Kullanıcı zaten giriş yapmışsa belirtilen sayfaya yönlendirilir.', 'yatirim-portfoyu-takip'); ?></td>
                            <td><code>redirect</code>: <?php _e('Giriş sonrası yönlendirilecek sayfa', 'yatirim-portfoyu-takip'); ?></td>
                            <td><code>[yatirim_portfoyu_login redirect="https://siteniz.com/portfolyo"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[yatirim_portfoyu_register]</code></td>
                            <td><?php _e('Kayıt formunu gösterir. Kullanıcı zaten giriş yapmışsa belirtilen sayfaya yönlendirilir.', 'yatirim-portfoyu-takip'); ?></td>
                            <td>
                                <code>redirect</code>: <?php _e('Kayıt sonrası yönlendirilecek sayfa', 'yatirim-portfoyu-takip'); ?><br>
                                <code>login_url</code>: <?php _e('Giriş sayfası URL\'i', 'yatirim-portfoyu-takip'); ?>
                            </td>
                            <td><code>[yatirim_portfoyu_register redirect="https://siteniz.com/portfolyo" login_url="https://siteniz.com/giris"]</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3><?php _e('Sayfa Oluşturma Önerileri', 'yatirim-portfoyu-takip'); ?></h3>
        </div>
        <div class="card-body">
            <p><?php _e('En iyi kullanıcı deneyimi için aşağıdaki sayfaları oluşturmanızı öneririz:', 'yatirim-portfoyu-takip'); ?></p>
            
            <ol>
                <li>
                    <strong><?php _e('Giriş Sayfası', 'yatirim-portfoyu-takip'); ?></strong>
                    <p><?php _e('Bu sayfada sadece giriş formu olmalıdır:', 'yatirim-portfoyu-takip'); ?></p>
                    <pre><code>[yatirim_portfoyu_login redirect="https://siteniz.com/portfolyo"]</code></pre>
                    <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="btn btn-sm btn-primary"><?php _e('Sayfa Oluştur', 'yatirim-portfoyu-takip'); ?></a>
                </li>
                <li class="mt-3">
                    <strong><?php _e('Kayıt Sayfası', 'yatirim-portfoyu-takip'); ?></strong>
                    <p><?php _e('Bu sayfada sadece kayıt formu olmalıdır:', 'yatirim-portfoyu-takip'); ?></p>
                    <pre><code>[yatirim_portfoyu_register redirect="https://siteniz.com/portfolyo" login_url="https://siteniz.com/giris"]</code></pre>
                    <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="btn btn-sm btn-primary"><?php _e('Sayfa Oluştur', 'yatirim-portfoyu-takip'); ?></a>
                </li>
                <li class="mt-3">
                    <strong><?php _e('Portföy Sayfası', 'yatirim-portfoyu-takip'); ?></strong>
                    <p><?php _e('Bu sayfa ana portföy takip sisteminizi içermelidir:', 'yatirim-portfoyu-takip'); ?></p>
                    <pre><code>[yatirim_portfoyu]</code></pre>
                    <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="btn btn-sm btn-primary"><?php _e('Sayfa Oluştur', 'yatirim-portfoyu-takip'); ?></a>
                </li>
            </ol>
            
            <div class="alert alert-warning mt-4">
                <p><strong><?php _e('Not:', 'yatirim-portfoyu-takip'); ?></strong> <?php _e('Sayfa oluşturduktan sonra, URL\'leri kendi sitenizin yapısına göre güncellemeyi unutmayın.', 'yatirim-portfoyu-takip'); ?></p>
            </div>
        </div>
    </div>
</div>