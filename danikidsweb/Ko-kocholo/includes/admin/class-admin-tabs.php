<?php
/**
 * Admin tabs rendering and saving
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Admin_Tabs {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Render specific tab content
     */
    public function render_tab($tab) {
        $method = 'render_' . $tab . '_tab';
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }
    
    /**
     * Save tab data
     */
    public function save_tab_data($tab, $data) {
        $method = 'save_' . $tab . '_tab';
        if (method_exists($this, $method)) {
            $this->$method($data);
        }
        
        KK_Cache::clear_products_cache();
    }
    
    // ==========================================
    // AUTH TAB
    // ==========================================
    
    private function render_auth_tab() {
        $google_client_id = get_option('khoshtip_google_client_id', '');
        $google_client_secret = get_option('khoshtip_google_client_secret', '');
        $faraz_api_key = get_option('khoshtip_faraz_api_key', '');
        $faraz_sender_number = get_option('khoshtip_faraz_sender_number', '');
        $sms_enabled = get_option('khoshtip_sms_enabled', '0');
        $require_phone = get_option('khoshtip_require_phone', '0');
        ?>
        <div class="settings-section">
            <h2>🔑 <?php esc_html_e('تنظیمات Google OAuth', 'khoshtip-kocholo'); ?></h2>
            <p class="description"><?php esc_html_e('برای فعال‌سازی ورود با Google، اطلاعات زیر را وارد کنید.', 'khoshtip-kocholo'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="khoshtip_google_client_id">Client ID</label></th>
                    <td>
                        <input type="text" id="khoshtip_google_client_id" name="khoshtip_google_client_id" 
                               value="<?php echo esc_attr($google_client_id); ?>" class="large-text" 
                               placeholder="1063548887243-xxx.apps.googleusercontent.com">
                        <p class="description"><?php esc_html_e('Client ID از Google Cloud Console', 'khoshtip-kocholo'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="khoshtip_google_client_secret">Client Secret</label></th>
                    <td>
                        <input type="text" id="khoshtip_google_client_secret" name="khoshtip_google_client_secret" 
                               value="<?php echo esc_attr($google_client_secret); ?>" class="large-text" 
                               placeholder="GOCSPX-xxxxxxxxxxxxx">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Redirect URI</th>
                    <td>
                        <div class="auth-help-box">
                            <code><?php echo esc_url(home_url('/google-callback/')); ?></code>
                            <p><?php esc_html_e('این آدرس را در Google Cloud Console اضافه کنید.', 'khoshtip-kocholo'); ?></p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="settings-section">
            <h2>📱 <?php esc_html_e('تنظیمات فراز اس‌ام‌اس', 'khoshtip-kocholo'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('فعال‌سازی ورود با موبایل', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="khoshtip_sms_enabled" value="1" <?php checked($sms_enabled, '1'); ?>>
                            <?php esc_html_e('فعال‌سازی ورود و ثبت‌نام با شماره موبایل', 'khoshtip-kocholo'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="khoshtip_faraz_api_key">API Key</label></th>
                    <td>
                        <input type="text" id="khoshtip_faraz_api_key" name="khoshtip_faraz_api_key" 
                               value="<?php echo esc_attr($faraz_api_key); ?>" class="large-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="khoshtip_faraz_sender_number"><?php esc_html_e('شماره ارسال‌کننده', 'khoshtip-kocholo'); ?></label></th>
                    <td>
                        <input type="text" id="khoshtip_faraz_sender_number" name="khoshtip_faraz_sender_number" 
                               value="<?php echo esc_attr($faraz_sender_number); ?>" class="large-text" dir="ltr">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('الزامی بودن شماره', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="khoshtip_require_phone" value="1" <?php checked($require_phone, '1'); ?>>
                            <?php esc_html_e('شماره موبایل برای ثبت‌نام الزامی باشد', 'khoshtip-kocholo'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="settings-section">
            <h2>✅ <?php esc_html_e('وضعیت تنظیمات', 'khoshtip-kocholo'); ?></h2>
            <table class="form-table">
                <tr>
                    <th>Google OAuth</th>
                    <td>
                        <?php if (!empty($google_client_id) && !empty($google_client_secret)) : ?>
                            <span class="status-active">✓ <?php esc_html_e('فعال', 'khoshtip-kocholo'); ?></span>
                        <?php else : ?>
                            <span class="status-inactive">✗ <?php esc_html_e('غیرفعال', 'khoshtip-kocholo'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('فراز اس‌ام‌اس', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <?php if ($sms_enabled === '1' && !empty($faraz_api_key) && !empty($faraz_sender_number)) : ?>
                            <span class="status-active">✓ <?php esc_html_e('فعال', 'khoshtip-kocholo'); ?></span>
                        <?php elseif ($sms_enabled === '1') : ?>
                            <span class="status-warning">⚠ <?php esc_html_e('اطلاعات ناقص', 'khoshtip-kocholo'); ?></span>
                        <?php else : ?>
                            <span class="status-inactive">✗ <?php esc_html_e('غیرفعال', 'khoshtip-kocholo'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    private function save_auth_tab($data) {
        update_option('khoshtip_google_client_id', sanitize_text_field($data['khoshtip_google_client_id'] ?? ''));
        update_option('khoshtip_google_client_secret', sanitize_text_field($data['khoshtip_google_client_secret'] ?? ''));
        update_option('khoshtip_faraz_api_key', sanitize_text_field($data['khoshtip_faraz_api_key'] ?? ''));
        update_option('khoshtip_faraz_sender_number', sanitize_text_field($data['khoshtip_faraz_sender_number'] ?? ''));
        update_option('khoshtip_sms_enabled', isset($data['khoshtip_sms_enabled']) ? '1' : '0');
        update_option('khoshtip_require_phone', isset($data['khoshtip_require_phone']) ? '1' : '0');
        
        flush_rewrite_rules();
    }
    
    // ==========================================
    // HEADER TAB
    // ==========================================
    
    private function render_header_tab() {
        $site_title = get_option('k_site_title', 'فروشگاه پوشاک Shop'); // Updated default brand name
        $site_tagline = get_option('k_site_tagline', '');
        $site_title_font_size = get_option('k_site_title_font_size', '1.5');
        $nav_links = get_option('k_nav_links', []);
        $mobile_menu_links = get_option('k_mobile_menu_links', []);
        $header_gradient_start = get_option('k_header_gradient_start', '#ff6b9d');
        $header_gradient_end = get_option('k_header_gradient_end', '#ffc3d7');
        
        $search_enabled_sizes = get_option('k_search_enabled_sizes', []);
        $clothing_sizes = [];
        if (taxonomy_exists('pa_size')) {
            $terms = get_terms(array(
                'taxonomy' => 'pa_size',
                'hide_empty' => false,
            ));
            if (!is_wp_error($terms)) {
                $clothing_sizes = $terms;
            }
        }
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('عنوان سایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_site_title" value="<?php echo esc_attr($site_title); ?>" class="regular-text"></td>
            </tr>
            <!-- Added font size field for site title -->
            <tr>
                <th scope="row"><?php esc_html_e('اندازه فونت عنوان (rem)', 'khoshtip-kocholo'); ?></th>
                <td>
                    <input type="number" name="k_site_title_font_size" value="<?php echo esc_attr($site_title_font_size); ?>" class="small-text" min="0.5" max="4" step="0.1">
                    <p class="description"><?php esc_html_e('اندازه فونت عنوان سایت در هدر (پیش‌فرض: 1.5rem)', 'khoshtip-kocholo'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('شعار سایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_site_tagline" value="<?php echo esc_attr($site_tagline); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('رنگ شروع گرادینت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_header_gradient_start" value="<?php echo esc_attr($header_gradient_start); ?>" class="k-color-picker"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('رنگ پایان گرادینت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_header_gradient_end" value="<?php echo esc_attr($header_gradient_end); ?>" class="k-color-picker"></td>
            </tr>
        </table>
        
        <!-- Added size selection for search filter -->
        <h3><?php esc_html_e('سایزهای قابل جستجو', 'khoshtip-kocholo'); ?></h3>
        <p class="description"><?php esc_html_e('سایزهایی که میخواهید در جستجو به کاربر نمایش داده شوند را انتخاب کنید.', 'khoshtip-kocholo'); ?></p>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; padding: 15px; background: #f9f9f9; border-radius: 8px; margin-bottom: 20px;">
            <?php if (!empty($clothing_sizes)) : ?>
                <?php foreach ($clothing_sizes as $size) : ?>
                    <label style="display: flex; align-items: center; gap: 5px; padding: 8px 12px; background: white; border-radius: 5px; cursor: pointer;">
                        <input type="checkbox" name="k_search_enabled_sizes[]" value="<?php echo esc_attr($size->slug); ?>" 
                            <?php checked(in_array($size->slug, $search_enabled_sizes)); ?>>
                        <?php echo esc_html($size->name); ?>
                    </label>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Updated error message to reflect pa_size -->
                <p style="color: #666;"><?php esc_html_e('ویژگی pa_size یافت نشد. لطفاً ابتدا این ویژگی را در ووکامرس ایجاد کنید.', 'khoshtip-kocholo'); ?></p>
            <?php endif; ?>
        </div>
        
        <h3><?php esc_html_e('لینک‌های منوی دسکتاپ', 'khoshtip-kocholo'); ?></h3>
        <div id="nav-links-repeater" class="k-repeater">
            <?php foreach ($nav_links as $index => $link) : ?>
                <div class="k-repeater-item">
                    <input type="text" name="k_nav_links[<?php echo $index; ?>][text]" 
                           value="<?php echo esc_attr($link['text'] ?? ''); ?>" placeholder="متن لینک" style="width: 30%;">
                    <input type="text" name="k_nav_links[<?php echo $index; ?>][url]" 
                           value="<?php echo esc_url($link['url'] ?? ''); ?>" placeholder="آدرس URL" style="width: 50%;">
                    <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-nav-link"><?php esc_html_e('افزودن لینک', 'khoshtip-kocholo'); ?></button>
        
        <h3><?php esc_html_e('لینک‌های منوی موبایل', 'khoshtip-kocholo'); ?></h3>
        <div id="mobile-menu-links-repeater" class="k-repeater">
            <?php foreach ($mobile_menu_links as $index => $link) : ?>
                <div class="k-repeater-item">
                    <input type="text" name="k_mobile_menu_links[<?php echo $index; ?>][text]" 
                           value="<?php echo esc_attr($link['text'] ?? ''); ?>" placeholder="متن لینک" style="width: 30%;">
                    <input type="text" name="k_mobile_menu_links[<?php echo $index; ?>][url]" 
                           value="<?php echo esc_url($link['url'] ?? ''); ?>" placeholder="آدرس URL" style="width: 50%;">
                    <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-mobile-menu-link"><?php esc_html_e('افزودن لینک', 'khoshtip-kocholo'); ?></button>
        <?php
    }
    
    private function save_header_tab($data) {
        update_option('k_site_title', sanitize_text_field($data['k_site_title'] ?? ''));
        update_option('k_site_title_font_size', sanitize_text_field($data['k_site_title_font_size'] ?? '1.5'));
        update_option('k_site_tagline', sanitize_text_field($data['k_site_tagline'] ?? ''));
        update_option('k_header_gradient_start', sanitize_hex_color($data['k_header_gradient_start'] ?? '#ff6b9d'));
        update_option('k_header_gradient_end', sanitize_hex_color($data['k_header_gradient_end'] ?? '#ffc3d7'));
        update_option('k_nav_links', KK_Helper::sanitize_repeater_array($data['k_nav_links'] ?? []));
        update_option('k_mobile_menu_links', KK_Helper::sanitize_repeater_array($data['k_mobile_menu_links'] ?? []));
        $enabled_sizes = isset($data['k_search_enabled_sizes']) ? array_map('sanitize_text_field', $data['k_search_enabled_sizes']) : [];
        update_option('k_search_enabled_sizes', $enabled_sizes);
    }
    
    // ==========================================
    // STORIES TAB
    // ==========================================
    
    private function render_stories_tab() {
        $stories = get_option('k_stories', []);
        ?>
        <h3><?php esc_html_e('مدیریت استوری‌ها', 'khoshtip-kocholo'); ?></h3>
        <div id="stories-repeater" class="k-repeater">
            <?php foreach ($stories as $index => $story) : ?>
                <?php $this->render_story_item($index, $story); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-story"><?php esc_html_e('افزودن استوری', 'khoshtip-kocholo'); ?></button>
        <?php
    }
    
    private function render_story_item($index, $story) {
        $media_items = $story['media'] ?? [];
        ?>
        <div class="k-repeater-item story-item">
            <h4><?php printf(__('استوری %d', 'khoshtip-kocholo'), $index + 1); ?></h4>
            
            <label><?php esc_html_e('نام کاربری:', 'khoshtip-kocholo'); ?></label>
            <input type="text" name="k_stories[<?php echo $index; ?>][username]" 
                   value="<?php echo esc_attr($story['username'] ?? ''); ?>" placeholder="نام کاربری">
            
            <label><?php esc_html_e('آواتار:', 'khoshtip-kocholo'); ?></label>
            <div class="avatar-upload">
                <input type="text" name="k_stories[<?php echo $index; ?>][avatar]" 
                       value="<?php echo esc_url($story['avatar'] ?? ''); ?>" class="story-avatar-input" placeholder="آدرس آواتار">
                <button type="button" class="button upload-btn upload-avatar"><?php esc_html_e('انتخاب', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($story['avatar'])) : ?>
                    <img src="<?php echo esc_url($story['avatar']); ?>" class="avatar-preview" style="max-width: 60px; border-radius: 50%;">
                <?php endif; ?>
            </div>
            
            <label><?php esc_html_e('رسانه‌ها:', 'khoshtip-kocholo'); ?></label>
            <div class="story-media-list" id="story-media-list-<?php echo $index; ?>">
                <?php foreach ($media_items as $media_index => $media) : ?>
                    <div class="story-media-item">
                        <select name="k_stories[<?php echo $index; ?>][media][<?php echo $media_index; ?>][type]">
                            <option value="image" <?php selected($media['type'] ?? '', 'image'); ?>>تصویر</option>
                            <option value="video" <?php selected($media['type'] ?? '', 'video'); ?>>ویدیو</option>
                        </select>
                        <input type="text" name="k_stories[<?php echo $index; ?>][media][<?php echo $media_index; ?>][url]" 
                               value="<?php echo esc_url($media['url'] ?? ''); ?>" placeholder="آدرس رسانه">
                        <input type="text" name="k_stories[<?php echo $index; ?>][media][<?php echo $media_index; ?>][link]" 
                               value="<?php echo esc_url($media['link'] ?? ''); ?>" placeholder="لینک (اختیاری)">
                        <button type="button" class="button k-remove-btn"><?php esc_html_e('حذف', 'khoshtip-kocholo'); ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button add-story-media" data-story="<?php echo $index; ?>"><?php esc_html_e('افزودن رسانه', 'khoshtip-kocholo'); ?></button>
            
            <label><?php esc_html_e('لینک دکمه:', 'khoshtip-kocholo'); ?></label>
            <input type="text" name="k_stories[<?php echo $index; ?>][button_url]" 
                   value="<?php echo esc_url($story['button_url'] ?? ''); ?>" placeholder="لینک دکمه">
            
            <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()"><?php esc_html_e('حذف استوری', 'khoshtip-kocholo'); ?></button>
        </div>
        <?php
    }
    
    private function save_stories_tab($data) {
        update_option('k_stories', KK_Helper::sanitize_repeater_array($data['k_stories'] ?? []));
    }
    
    // ==========================================
    // HERO TAB
    // ==========================================
    
    private function render_hero_tab() {
        $slides = get_option('k_hero_slides', []);
        ?>
        <h3><?php esc_html_e('اسلایدهای بنر اصلی', 'khoshtip-kocholo'); ?></h3>
        <div id="hero-repeater" class="k-repeater">
            <?php foreach ($slides as $index => $slide) : ?>
                <?php $this->render_hero_item($index, $slide); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-hero-slide"><?php esc_html_e('افزودن اسلاید', 'khoshtip-kocholo'); ?></button>
        <?php
    }
    
    private function render_hero_item($index, $slide) {
        ?>
        <div class="k-repeater-item hero-item">
            <h4><?php printf(__('اسلاید %d', 'khoshtip-kocholo'), $index + 1); ?></h4>
            
            <div class="image-upload-group">
                <label><?php esc_html_e('تصویر دسکتاپ:', 'khoshtip-kocholo'); ?></label>
                <input type="hidden" name="k_hero_slides[<?php echo $index; ?>][bg_image]" 
                       value="<?php echo esc_url($slide['bg_image'] ?? ''); ?>" class="hero-bg-input">
                <button type="button" class="button upload-hero-bg"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($slide['bg_image'])) : ?>
                    <img src="<?php echo esc_url($slide['bg_image']); ?>" class="k-image-preview">
                <?php endif; ?>
            </div>
            
            <div class="image-upload-group">
                <label><?php esc_html_e('تصویر موبایل:', 'khoshtip-kocholo'); ?></label>
                <input type="hidden" name="k_hero_slides[<?php echo $index; ?>][mobile_image]" 
                       value="<?php echo esc_url($slide['mobile_image'] ?? ''); ?>" class="hero-mobile-input">
                <button type="button" class="button upload-hero-mobile"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($slide['mobile_image'])) : ?>
                    <img src="<?php echo esc_url($slide['mobile_image']); ?>" class="k-image-preview">
                <?php endif; ?>
            </div>
            
            <input type="text" name="k_hero_slides[<?php echo $index; ?>][title]" 
                   value="<?php echo esc_attr($slide['title'] ?? ''); ?>" placeholder="عنوان">
            <textarea name="k_hero_slides[<?php echo $index; ?>][description]" 
                      placeholder="توضیحات" rows="3"><?php echo esc_textarea($slide['description'] ?? ''); ?></textarea>
            <input type="text" name="k_hero_slides[<?php echo $index; ?>][button_text]" 
                   value="<?php echo esc_attr($slide['button_text'] ?? ''); ?>" placeholder="متن دکمه">
            <input type="text" name="k_hero_slides[<?php echo $index; ?>][button_url]" 
                   value="<?php echo esc_url($slide['button_url'] ?? ''); ?>" placeholder="لینک دکمه">
            
            <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()"><?php esc_html_e('حذف اسلاید', 'khoshtip-kocholo'); ?></button>
        </div>
        <?php
    }
    
    private function save_hero_tab($data) {
        update_option('k_hero_slides', KK_Helper::sanitize_repeater_array($data['k_hero_slides'] ?? []));
    }
    
    // ==========================================
    // SALES TAB
    // ==========================================
    
    private function render_sales_tab() {
        $sale_hours = get_option('k_sale_hours', 0);
        $sale_minutes = get_option('k_sale_minutes', 0);
        $sale_end_timestamp = get_option('k_sale_end_timestamp', 0);
        $time_remaining = $sale_end_timestamp > 0 ? $sale_end_timestamp - time() : 0;
        
        $sale_button_enabled = get_option('k_sale_button_enabled', '1');
        $sale_button_color = get_option('k_sale_button_color', '#ff4757');
        
        $product_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));
        ?>
        <div class="k-sales-tab">
            <h3>مدیریت حراج</h3>
            
            <!-- Added sale button configuration section -->
            <div class="k-sale-button-section" style="background: #fff3cd; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 2px solid #ffc107;">
                <h4 style="margin-top: 0;">🔥 دکمه حراجی در صفحه اصلی</h4>
                <table class="form-table">
                    <tr>
                        <th scope="row">فعال کردن دکمه حراجی</th>
                        <td>
                            <label>
                                <input type="checkbox" name="k_sale_button_enabled" value="1" <?php checked($sale_button_enabled, '1'); ?> />
                                نمایش دکمه حراجی در کنار دکمه "همه محصولات"
                            </label>
                            <p class="description">این دکمه محصولات حراجی (دارای قیمت فروش ویژه) را نمایش می‌دهد.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">رنگ دکمه حراجی</th>
                        <td>
                            <input type="text" name="k_sale_button_color" value="<?php echo esc_attr($sale_button_color); ?>" class="k-color-picker" />
                            <p class="description">رنگ پس‌زمینه دکمه حراجی را انتخاب کنید.</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="k-sale-config-section" style="background: #f0f0f1; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h4 style="margin-top: 0;">⏰ تنظیمات حراجی</h4>
                <table class="form-table">
                    <tr>
                        <th scope="row">مدت زمان حراج</th>
                        <td>
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <div>
                                    <label>ساعت:</label>
                                    <input type="number" id="k-sale-hours" value="<?php echo esc_attr($sale_hours); ?>" min="0" max="720" style="width: 80px;" />
                                </div>
                                <div>
                                    <label>دقیقه:</label>
                                    <input type="number" id="k-sale-minutes" value="<?php echo esc_attr($sale_minutes); ?>" min="0" max="59" style="width: 80px;" />
                                </div>
                            </div>
                            <?php if ($time_remaining > 0) : ?>
                                <p class="description" style="color: #0073aa; font-weight: 600; margin-top: 10px;">
                                    ⚡ تایمر فعال - زمان باقی‌مانده: <?php echo human_time_diff(time(), $sale_end_timestamp); ?>
                                </p>
                            <?php else: ?>
                                <p class="description" style="color: #666; margin-top: 10px;">
                                    تایمر فعال نیست.
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">فیلتر دسته‌بندی</th>
                        <td>
                            <select id="k-category-filter" style="width: 300px;">
                                <option value="0">همه محصولات</option>
                                <?php foreach ($product_categories as $category) : ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>">
                                        <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">انتخاب محصولات</th>
                        <td>
                            <div id="k-products-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f0f0f1; border-radius: 8px;">
                                <p style="color: #666;">در حال بارگذاری محصولات...</p>
                            </div>
                            <div style="margin-top: 10px;">
                                <label>
                                    <input type="checkbox" id="k-select-all-products" />
                                    انتخاب همه محصولات
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">درصد تخفیف</th>
                        <td>
                            <input type="number" id="k-discount-percent" min="1" max="99" value="10" style="width: 100px;" /> %
                            <p class="description">درصد تخفیف از قیمت اصلی محصولات (1 تا 99 درصد)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <button type="button" class="button button-primary button-large" id="k-start-sale">
                                    🚀 شروع حراجی (اعمال تایمر + تخفیف‌ها)
                                </button>
                                <?php if ($time_remaining > 0) : ?>
                                <button type="button" class="button button-secondary" id="k-cancel-sale" style="background: #d63638; color: #fff; border-color: #d63638;">
                                    ❌ لغو حراجی و حذف تمام تخفیف‌ها
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="k-active-discounts" style="background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0;">📋 لیست حراجی‌های فعال</h4>
                    <button type="button" class="button button-secondary" id="k-remove-all-discounts" style="background: #d63638; color: #fff; border-color: #d63638;">
                        🗑️ حذف همه تخفیف‌ها
                    </button>
                </div>
                <div id="k-discounted-products-list">
                    <?php $this->render_discounted_products_table(); ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            loadProductsByCategory(0);
            
            $('#k-category-filter').on('change', function() {
                const categoryId = $(this).val();
                loadProductsByCategory(categoryId);
            });
            
            function loadProductsByCategory(categoryId) {
                const $productsList = $('#k-products-list');
                
                $productsList.html('<p style="color: #666;">در حال بارگذاری محصولات...</p>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_get_products_by_category',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>',
                        category_id: categoryId
                    },
                    success: function(response) {
                        if (response.success) {
                            let html = '';
                            
                            if (response.data.products.length === 0) {
                                html = '<p style="color: #666;">هیچ محصولی یافت نشد.</p>';
                            } else {
                                response.data.products.forEach(function(product) {
                                    const discountBadge = product.has_discount ? '<span style="color: #d63638; font-weight: 600;"> (در حال حاضر تخفیف دارد)</span>' : '';
                                    const productTypeInfo = product.type === 'variable' ? ' (متغیر)' : '';
                                    html += `
                                        <div style="padding: 8px; border-bottom: 1px solid #ddd;">
                                            <label style="display: flex; align-items: center; cursor: pointer;">
                                                <input type="checkbox" class="k-product-checkbox" value="${product.id}" style="margin-left: 8px;" />
                                                <span>${product.name}${productTypeInfo} - ${product.regular_price}${discountBadge}</span>
                                            </label>
                                        </div>
                                    `;
                                });
                            }
                            
                            $productsList.html(html);
                        }
                    },
                    error: function() {
                        $productsList.html('<p style="color: #d63638;">خطا در بارگذاری محصولات.</p>');
                    }
                });
            }
            
            $('#k-select-all-products').on('change', function() {
                $('.k-product-checkbox').prop('checked', $(this).is(':checked'));
            });
            
            $('#k-start-sale').on('click', function() {
                const selectedProducts = [];
                $('.k-product-checkbox:checked').each(function() {
                    selectedProducts.push($(this).val());
                });
                
                const discountPercent = $('#k-discount-percent').val();
                const saleHours = $('#k-sale-hours').val();
                const saleMinutes = $('#k-sale-minutes').val();
                
                if (selectedProducts.length === 0) {
                    alert('لطفاً حداقل یک محصول انتخاب کنید.');
                    return;
                }
                
                if (!discountPercent || discountPercent < 1 || discountPercent > 99) {
                    alert('لطفاً درصد تخفیف معتبر (1 تا 99) وارد کنید.');
                    return;
                }
                
                if (!saleHours && !saleMinutes) {
                    alert('لطفاً مدت زمان حراج را وارد کنید.');
                    return;
                }
                
                if (!confirm('آیا از شروع حراجی اطمینان دارید؟ تایمر و تخفیف‌ها همزمان اعمال می‌شوند.')) {
                    return;
                }
                
                const $button = $(this);
                $button.prop('disabled', true).text('در حال اعمال حراجی...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_start_sale',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>',
                        product_ids: selectedProducts,
                        discount_percent: discountPercent,
                        sale_hours: saleHours,
                        sale_minutes: saleMinutes
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || 'خطایی رخ داد.');
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('🚀 شروع حراجی (اعمال تایمر + تخفیف‌ها)');
                    }
                });
            });
            
            $('#k-cancel-sale').on('click', function() {
                if (!confirm('آیا از لغو حراجی و حذف تمام تخفیف‌ها اطمینان دارید؟')) {
                    return;
                }
                
                const $button = $(this);
                $button.prop('disabled', true).text('در حال لغو...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_cancel_sale',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || 'خطایی رخ داد.');
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('❌ لغو حراجی و حذف تمام تخفیف‌ها');
                    }
                });
            });
            
            $('#k-remove-all-discounts').on('click', function() {
                if (!confirm('آیا از حذف تمام تخفیف‌ها اطمینان دارید؟ (تایمر همچنان فعال می‌ماند)')) {
                    return;
                }
                
                const $button = $(this);
                $button.prop('disabled', true).text('در حال حذف...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_remove_all_discounts',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            updateDiscountedProductsList(response.data.discounted_products);
                            loadProductsByCategory($('#k-category-filter').val());
                        } else {
                            alert(response.data.message || 'خطایی رخ داد.');
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('🗑️ حذف همه تخفیف‌ها');
                    }
                });
            });
            
            $(document).on('click', '.k-remove-discount', function() {
                if (!confirm('آیا از حذف این تخفیف اطمینان دارید؟')) {
                    return;
                }
                
                const productId = $(this).data('product-id');
                const $button = $(this);
                
                $button.prop('disabled', true).text('در حال حذف...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'k_remove_discount',
                        nonce: '<?php echo wp_create_nonce('k_save_nonce'); ?>',
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            updateDiscountedProductsList(response.data.discounted_products);
                            loadProductsByCategory($('#k-category-filter').val());
                        } else {
                            alert(response.data.message || 'خطایی رخ داد.');
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('حذف');
                    }
                });
            });
            
            function updateDiscountedProductsList(products) {
                let html = '';
                
                if (products.length === 0) {
                    html = '<p style="color: #666; text-align: center; padding: 20px;">هیچ محصولی با تخفیف وجود ندارد.</p>';
                } else {
                    html = `
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>نام محصول</th>
                                    <th>قیمت اصلی</th>
                                    <th>قیمت با تخفیف</th>
                                    <th>درصد تخفیف</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    products.forEach(function(product) {
                        html += `
                            <tr>
                                <td>${product.name}</td>
                                <td>${product.regular_price}</td>
                                <td style="color: #d63638; font-weight: 600;">${product.sale_price}</td>
                                <td><span style="background: #d63638; color: white; padding: 3px 8px; border-radius: 3px;">${product.discount_percent}%</span></td>
                                <td>
                                    <button type="button" class="button button-small k-remove-discount" data-product-id="${product.id}">
                                        حذف
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += `
                            </tbody>
                        </table>
                    `;
                }
                
                $('#k-discounted-products-list').html(html);
            }
        });
        </script>
        <?php
    }
    
    public function render_discounted_products_table() {
        $discounted_products = $this->get_discounted_products_list();
        
        if (empty($discounted_products)) {
            echo '<p style="color: #666; text-align: center; padding: 20px;">هیچ محصولی با تخفیف وجود ندارد.</p>';
            return;
        }
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>نام محصول</th>
                    <th>قیمت اصلی</th>
                    <th>قیمت با تخفیف</th>
                    <th>درصد تخفیف</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($discounted_products as $product) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($product['name']); ?></strong></td>
                        <td><?php echo $product['regular_price']; ?></td>
                        <td><span style="color: #d63638; font-weight: 600;"><?php echo $product['sale_price']; ?></span></td>
                        <td><span style="background: #d63638; color: white; padding: 3px 8px; border-radius: 3px;"><?php echo $product['discount_percent']; ?>%</span></td>
                        <td>
                            <button type="button" class="button button-small k-remove-discount" data-product-id="<?php echo esc_attr($product['id']); ?>">
                                حذف
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    public function get_discounted_products_list() {
        $discounted_products_ids = get_option('k_discounted_products', array());
        $product_list = array();
        
        foreach ($discounted_products_ids as $product_id) {
            $product = wc_get_product($product_id);
            
            if ($product) {
                $product_type = $product->get_type();
                $regular_price = $product->get_regular_price();
                $sale_price = $product->get_sale_price();
                $discount_percent = 0;
                $display_regular_price = wc_price($regular_price);
                $display_sale_price = '';
                
                if ($product_type === 'variable') {
                    $min_reg_price = $product->get_variation_regular_price('min', true);
                    $max_reg_price = $product->get_variation_regular_price('max', true);
                    
                    $display_regular_price = ($min_reg_price == $max_reg_price) ? wc_price($min_reg_price) : wc_format_price_range($min_reg_price, $max_reg_price);
                    
                    $lowest_sale_price = PHP_INT_MAX;
                    $variations = $product->get_children();
                    foreach ($variations as $variation_id) {
                        $variation = wc_get_product($variation_id);
                        if ($variation) {
                            $var_reg_price = $variation->get_regular_price();
                            $var_sale_price = $variation->get_sale_price();
                            
                            if (!empty($var_sale_price) && $var_sale_price < $lowest_sale_price) {
                                $lowest_sale_price = $var_sale_price;
                            }
                        }
                    }
                    
                    if ($lowest_sale_price != PHP_INT_MAX) {
                        $display_sale_price = wc_price($lowest_sale_price);
                        if ($min_reg_price > 0) {
                            $discount_percent = round((($min_reg_price - $lowest_sale_price) / $min_reg_price) * 100);
                        }
                    }
                    
                } else {
                    $display_sale_price = !empty($sale_price) ? wc_price($sale_price) : '';
                    if ($regular_price > 0 && !empty($sale_price)) {
                        $discount_percent = round((($regular_price - $sale_price) / $regular_price) * 100);
                    }
                }
                
                $product_list[] = array(
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'regular_price' => $display_regular_price,
                    'sale_price' => $display_sale_price,
                    'discount_percent' => $discount_percent
                );
            }
        }
        
        return $product_list;
    }
    
    private function save_sales_tab($data) {
        update_option('k_sale_button_enabled', isset($data['k_sale_button_enabled']) ? '1' : '0');
        update_option('k_sale_button_color', sanitize_hex_color($data['k_sale_button_color'] ?? '#ff4757'));
        
        // Sales are managed via AJAX actions
    }
    
    // ==========================================
    // LATEST PRODUCTS TAB
    // ==========================================
    
    private function render_latest_tab() {
        $products = get_option('k_latest_products', []);
        ?>
        <h3><?php esc_html_e('جدیدترین محصولات', 'khoshtip-kocholo'); ?></h3>
        <p class="description"><?php esc_html_e('به طور پیش‌فرض، 20 محصول آخر نمایش داده می‌شود.', 'khoshtip-kocholo'); ?></p>
        
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('محصولات', 'khoshtip-kocholo'); ?></th>
                <td>
                    <?php $this->render_product_selector('k_latest_products', $products); ?>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private function render_product_selector($name, $selected_ids) {
        $all_products = wc_get_products(['limit' => -1, 'status' => 'publish']);
        ?>
        <select name="<?php echo esc_attr($name); ?>[]" multiple class="k-product-selector" style="width: 100%;">
            <?php foreach ($all_products as $product) : ?>
                <option value="<?php echo $product->get_id(); ?>" <?php echo in_array($product->get_id(), $selected_ids) ? 'selected' : ''; ?>>
                    <?php echo esc_html($product->get_name()); ?> (#<?php echo $product->get_id(); ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    
    private function save_latest_tab($data) {
        update_option('k_latest_products', KK_Helper::sanitize_int_array($data['k_latest_products'] ?? []));
    }
    
    // ==========================================
    // CATEGORIES TAB
    // ==========================================
    
    private function render_categories_tab() {
        $categories = get_option('k_categories', []);
        ?>
        <h3><?php esc_html_e('مدیریت دسته‌بندی‌ها', 'khoshtip-kocholo'); ?></h3>
        
        <div id="categories-repeater" class="k-repeater">
            <?php foreach ($categories as $index => $category) : ?>
                <?php $this->render_category_item($index, $category); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-category"><?php esc_html_e('افزودن دسته‌بندی', 'khoshtip-kocholo'); ?></button>
        <?php
    }
    
    private function render_category_item($index, $category) {
        ?>
        <div class="k-repeater-item category-item">
            <h4><?php printf(__('دسته‌بندی %d', 'khoshtip-kocholo'), $index + 1); ?></h4>
            
            <input type="text" name="k_categories[<?php echo $index; ?>][name]" 
                   value="<?php echo esc_attr($category['name'] ?? ''); ?>" placeholder="نام دسته‌بندی">
            <input type="text" name="k_categories[<?php echo $index; ?>][link]" 
                   value="<?php echo esc_url($category['link'] ?? ''); ?>" placeholder="لینک">
            
            <div class="image-upload-group">
                <label><?php esc_html_e('تصویر دکمه:', 'khoshtip-kocholo'); ?></label>
                <input type="hidden" name="k_categories[<?php echo $index; ?>][image]" 
                       value="<?php echo esc_url($category['image'] ?? ''); ?>" class="category-image-input">
                <button type="button" class="button upload-category-image"><?php esc_html_e('انتخاب', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($category['image'])) : ?>
                    <img src="<?php echo esc_url($category['image']); ?>" class="k-image-preview">
                <?php endif; ?>
            </div>
            
            <div class="image-upload-group">
                <label><?php esc_html_e('تصویر مودال:', 'khoshtip-kocholo'); ?></label>
                <input type="hidden" name="k_categories[<?php echo $index; ?>][modal_image]" 
                       value="<?php echo esc_url($category['modal_image'] ?? ''); ?>" class="category-modal-image-input">
                <button type="button" class="button upload-category-modal-image"><?php esc_html_e('انتخاب', 'khoshtip-kocholo'); ?></button>
                <?php if (!empty($category['modal_image'])) : ?>
                    <img src="<?php echo esc_url($category['modal_image']); ?>" class="k-image-preview">
                <?php endif; ?>
            </div>
            
            <label><?php esc_html_e('توضیحات:', 'khoshtip-kocholo'); ?></label>
            <textarea name="k_categories[<?php echo $index; ?>][description]" rows="3"><?php echo esc_textarea($category['description'] ?? ''); ?></textarea>
            
            <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()"><?php esc_html_e('حذف', 'khoshtip-kocholo'); ?></button>
        </div>
        <?php
    }
    
    private function save_categories_tab($data) {
        update_option('k_categories', KK_Helper::sanitize_repeater_array($data['k_categories'] ?? []));
    }
    
    // ==========================================
    // ACCORDION TAB
    // ==========================================
    
    private function render_accordion_tab() {
        $sections = [
            'girls' => [
                'title' => get_option('k_accordion_girls_title', 'محصولات دخترانه'),
                'category' => KK_Helper::ensure_array(get_option('k_girls_category', [])),
                'products' => get_option('k_girls_products', []),
                'icon' => get_option('k_girls_icon', ''),
                'grid_limit' => get_option('k_girls_grid_limit', 10),
            ],
            'boys' => [
                'title' => get_option('k_accordion_boys_title', 'محصولات پسرانه'),
                'category' => KK_Helper::ensure_array(get_option('k_boys_category', [])),
                'products' => get_option('k_boys_products', []),
                'icon' => get_option('k_boys_icon', ''),
                'grid_limit' => get_option('k_boys_grid_limit', 10),
            ],
            'sport' => [
                'title' => get_option('k_accordion_sport_title', 'محصولات ورزشی'),
                'category' => KK_Helper::ensure_array(get_option('k_sport_category', [])),
                'products' => get_option('k_sport_products', []),
                'icon' => get_option('k_sport_icon', ''),
                'grid_limit' => get_option('k_sport_grid_limit', 10),
            ],
        ];
        
        $extra_accordions = get_option('k_extra_accordions', []);
        $wc_categories = KK_Helper::get_product_categories();
        $all_products = wc_get_products(['limit' => -1, 'status' => 'publish']);
        
        ?>
        <h3><?php esc_html_e('دکمه‌های دسته‌بندی محصولات', 'khoshtip-kocholo'); ?></h3>
        <p class="description"><?php esc_html_e('در موبایل 3 دکمه و در دسکتاپ 6 دکمه در هر ردیف نمایش داده می‌شود. با کلیک روی هر دکمه محصولات به صورت عمودی نمایش داده می‌شوند.', 'khoshtip-kocholo'); ?></p>
        
        <?php foreach ($sections as $key => $section) : ?>
            <?php $this->render_accordion_section($key, $section, $wc_categories, $all_products); ?>
        <?php endforeach; ?>
        
        <h3><?php esc_html_e('بخش‌های اضافی', 'khoshtip-kocholo'); ?></h3>
        <div id="extra-accordions-container">
            <?php foreach ($extra_accordions as $id => $accordion) : ?>
                <?php $this->render_extra_accordion_item($id, $accordion, $wc_categories); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button button-primary" id="add-extra-accordion"><?php esc_html_e('افزودن بخش جدید', 'khoshtip-kocholo'); ?></button>
        <?php
    }
    
    private function render_accordion_section($key, $section, $categories, $products) {
        $labels = [
            'girls' => __('بخش اول (دخترانه)', 'khoshtip-kocholo'),
            'boys' => __('بخش دوم (پسرانه)', 'khoshtip-kocholo'),
            'sport' => __('بخش سوم (ورزشی)', 'khoshtip-kocholo'),
        ];
        
        $default_colors = [
            'girls' => '#ff6b9d',
            'boys' => '#4dabf7',
            'sport' => '#51cf66',
        ];
        $button_bg = get_option("k_{$key}_button_bg", $default_colors[$key]);
        ?>
        <div class="settings-section accordion-section" data-section="<?php echo esc_attr($key); ?>">
            <h4 style="background: #667eea; color: #fff; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px;"><?php echo esc_html($labels[$key]); ?></h4>
            
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('عنوان', 'khoshtip-kocholo'); ?></th>
                    <td><input type="text" name="k_accordion_<?php echo $key; ?>_title" value="<?php echo esc_attr($section['title']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('دسته‌بندی', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_<?php echo $key; ?>_category[]" multiple size="5">
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?php echo $cat->term_id; ?>" <?php echo in_array($cat->term_id, $section['category']) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('آیکون', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="text" name="k_<?php echo $key; ?>_icon" value="<?php echo esc_attr($section['icon']); ?>" class="regular-text" placeholder="ایموجی یا URL تصویر">
                        <button type="button" class="button upload-icon"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                    </td>
                </tr>
                <!-- افزودن فیلد رنگ دکمه -->
                <tr>
                    <th><?php esc_html_e('رنگ دکمه', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="color" name="k_<?php echo $key; ?>_button_bg" value="<?php echo esc_attr($button_bg); ?>" style="width: 60px; height: 40px; padding: 0; border: 1px solid #ccc; cursor: pointer;">
                        <input type="text" value="<?php echo esc_attr($button_bg); ?>" class="color-text-input" style="width: 100px; margin-right: 10px;" readonly>
                        <p class="description"><?php esc_html_e('رنگ پس‌زمینه دکمه در حالت فعال', 'khoshtip-kocholo'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('تعداد محصولات', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="number" name="k_<?php echo $key; ?>_grid_limit" value="<?php echo esc_attr($section['grid_limit']); ?>" min="1" max="50" style="width: 80px;">
                        <p class="description"><?php esc_html_e('تعداد محصولاتی که نمایش داده می‌شود', 'khoshtip-kocholo'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    private function render_extra_accordion_item($id, $accordion, $categories) {
        $button_bg = $accordion['button_bg'] ?? '#9c27b0';
        ?>
        <div class="k-repeater-item extra-accordion-item" data-id="<?php echo esc_attr($id); ?>">
            <h4><?php echo esc_html($accordion['title'] ?: __('بخش جدید', 'khoshtip-kocholo')); ?></h4>
            
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('عنوان', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="text" name="k_extra_accordions[<?php echo $id; ?>][title]" 
                               value="<?php echo esc_attr($accordion['title'] ?? ''); ?>" placeholder="عنوان بخش" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('دسته‌بندی', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <select name="k_extra_accordions[<?php echo $id; ?>][category][]" multiple size="5" style="min-width: 300px;">
                            <?php 
                            $selected_cats = KK_Helper::ensure_array($accordion['category'] ?? []);
                            foreach ($categories as $cat) : ?>
                                <option value="<?php echo $cat->term_id; ?>" <?php echo in_array($cat->term_id, $selected_cats) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('آیکون', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="text" name="k_extra_accordions[<?php echo $id; ?>][icon]" 
                               value="<?php echo esc_attr($accordion['icon'] ?? ''); ?>" placeholder="ایموجی یا URL تصویر" class="regular-text">
                        <button type="button" class="button upload-icon"><?php esc_html_e('انتخاب تصویر', 'khoshtip-kocholo'); ?></button>
                    </td>
                </tr>
                <!-- افزودن فیلد رنگ دکمه برای بخش اضافی -->
                <tr>
                    <th><?php esc_html_e('رنگ دکمه', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="color" name="k_extra_accordions[<?php echo $id; ?>][button_bg]" value="<?php echo esc_attr($button_bg); ?>" style="width: 60px; height: 40px; padding: 0; border: 1px solid #ccc; cursor: pointer;">
                        <input type="text" value="<?php echo esc_attr($button_bg); ?>" class="color-text-input" style="width: 100px; margin-right: 10px;" readonly>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('تعداد محصولات', 'khoshtip-kocholo'); ?></th>
                    <td>
                        <input type="number" name="k_extra_accordions[<?php echo $id; ?>][grid_limit]" 
                               value="<?php echo esc_attr($accordion['grid_limit'] ?? 10); ?>" min="1" max="50" style="width: 80px;">
                    </td>
                </tr>
            </table>
            
            <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()"><?php esc_html_e('حذف این بخش', 'khoshtip-kocholo'); ?></button>
        </div>
        <?php
    }
    
    private function save_accordion_tab($data) {
        $sections = ['girls', 'boys', 'sport'];
        
        foreach ($sections as $key) {
            update_option("k_accordion_{$key}_title", sanitize_text_field($data["k_accordion_{$key}_title"] ?? ''));
            update_option("k_{$key}_category", KK_Helper::sanitize_int_array($data["k_{$key}_category"] ?? []));
            update_option("k_{$key}_products", KK_Helper::sanitize_int_array($data["k_{$key}_products"] ?? []));
            update_option("k_{$key}_icon", sanitize_text_field($data["k_{$key}_icon"] ?? ''));
            update_option("k_{$key}_grid_limit", absint($data["k_{$key}_grid_limit"] ?? 10));
            update_option("k_{$key}_button_bg", sanitize_hex_color($data["k_{$key}_button_bg"] ?? ''));
        }
        
        // ذخیره بخش‌های اضافی
        $extra_accordions = [];
        if (isset($data['k_extra_accordions']) && is_array($data['k_extra_accordions'])) {
            foreach ($data['k_extra_accordions'] as $id => $accordion) {
                $extra_accordions[$id] = [
                    'title' => sanitize_text_field($accordion['title'] ?? ''),
                    'category' => KK_Helper::sanitize_int_array($accordion['category'] ?? []),
                    'icon' => sanitize_text_field($accordion['icon'] ?? ''),
                    'grid_limit' => absint($accordion['grid_limit'] ?? 10),
                    'button_bg' => sanitize_hex_color($accordion['button_bg'] ?? '#9c27b0'),
                ];
            }
        }
        update_option('k_extra_accordions', $extra_accordions);
    }
    
    // ==========================================
    // BLOG TAB
    // ==========================================
    
    private function render_blog_tab() {
        $posts = get_option('k_blog_posts', []);
        ?>
        <h3><?php esc_html_e('مدیریت بلاگ', 'khoshtip-kocholo'); ?></h3>
        <p class="description"><?php esc_html_e('به طور پیش‌فرض، آخرین پست‌ها نمایش داده می‌شوند.', 'khoshtip-kocholo'); ?></p>
        
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('پست‌ها', 'khoshtip-kocholo'); ?></th>
                <td><?php $this->render_post_selector('k_blog_posts', $posts); ?></td>
            </tr>
        </table>
        <?php
    }
    
    private function render_post_selector($name, $selected_ids) {
        $posts = get_posts(['numberposts' => -1, 'post_status' => 'publish']);
        ?>
        <select name="<?php echo esc_attr($name); ?>[]" multiple style="width: 100%; min-height: 200px;">
            <?php foreach ($posts as $post) : ?>
                <option value="<?php echo $post->ID; ?>" <?php echo in_array($post->ID, $selected_ids) ? 'selected' : ''; ?>>
                    <?php echo esc_html($post->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    
    private function save_blog_tab($data) {
        update_option('k_blog_posts', KK_Helper::sanitize_int_array($data['k_blog_posts'] ?? []));
    }
    
    // ==========================================
    // FOOTER TAB
    // ==========================================
    
    private function render_footer_tab() {
        $site_title = get_option('k_footer_site_title', 'دنیای رنگارنگ کودکان');
        $site_description = get_option('k_footer_site_description', '');
        $useful_links = get_option('k_footer_useful_links', []);
        $customer_service_links = get_option('k_footer_customer_service_links', []);
        $contact_address = get_option('k_footer_contact_address', '');
        $contact_phone = get_option('k_footer_contact_phone', '');
        $copyright_text = get_option('k_footer_copyright', '');
        
        $bottom_bar_show_home = get_option('k_bottom_bar_show_home', '1');
        $bottom_bar_show_products = get_option('k_bottom_bar_show_products', '1');
        $bottom_bar_show_all_products = get_option('k_bottom_bar_show_all_products', '1');
        $bottom_bar_show_profile = get_option('k_bottom_bar_show_profile', '1');
        $bottom_bar_buttons = get_option('k_bottom_bar_buttons', []);
        $bottom_bar_bg_start = get_option('k_bottom_bar_bg_start', '#ffffff');
        $bottom_bar_bg_end = get_option('k_bottom_bar_bg_end', '#f9fafb');
        $bottom_bar_text_color = get_option('k_bottom_bar_text_color', '#374151');
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('عنوان سایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_footer_site_title" value="<?php echo esc_attr($site_title); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('توضیحات سایت', 'khoshtip-kocholo'); ?></th>
                <td><textarea name="k_footer_site_description" rows="3" class="large-text"><?php echo esc_textarea($site_description); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('لینک‌های مفید', 'khoshtip-kocholo'); ?></th>
                <td>
                    <div id="useful-links-repeater" class="k-repeater">
                        <?php foreach ($useful_links as $index => $link) : ?>
                            <div class="k-repeater-item">
                                <input type="text" name="k_footer_useful_links[<?php echo $index; ?>][text]" value="<?php echo esc_attr($link['text'] ?? ''); ?>" placeholder="متن لینک" style="width:30%;">
                                <input type="text" name="k_footer_useful_links[<?php echo $index; ?>][url]" value="<?php echo esc_url($link['url'] ?? ''); ?>" placeholder="آدرس URL" style="width:50%;">
                                <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-primary" onclick="addUsefulLink()"><?php esc_html_e('افزودن لینک مفید', 'khoshtip-kocholo'); ?></button>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('خدمات مشتریان', 'khoshtip-kocholo'); ?></th>
                <td>
                    <div id="customer-service-links-repeater" class="k-repeater">
                        <?php foreach ($customer_service_links as $index => $link) : ?>
                            <div class="k-repeater-item">
                                <input type="text" name="k_footer_customer_service_links[<?php echo $index; ?>][text]" value="<?php echo esc_attr($link['text'] ?? ''); ?>" placeholder="متن لینک" style="width:30%;">
                                <input type="text" name="k_footer_customer_service_links[<?php echo $index; ?>][url]" value="<?php echo esc_url($link['url'] ?? ''); ?>" placeholder="آدرس URL" style="width:50%;">
                                <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-primary" onclick="addCustomerServiceLink()"><?php esc_html_e('افزودن لینک خدمات', 'khoshtip-kocholo'); ?></button>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('آدرس تماس', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_footer_contact_address" value="<?php echo esc_attr($contact_address); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('شماره تماس', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_footer_contact_phone" value="<?php echo esc_attr($contact_phone); ?>" class="regular-text" dir="ltr"></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('متن کپی‌رایت', 'khoshtip-kocholo'); ?></th>
                <td><input type="text" name="k_footer_copyright" value="<?php echo esc_attr($copyright_text); ?>" class="large-text"></td>
            </tr>
        </table>
        
        <h3><?php esc_html_e('نوار پایین موبایل', 'khoshtip-kocholo'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('رنگ‌های پس‌زمینه', 'khoshtip-kocholo'); ?></th>
                <td>
                    <label style="display:block;margin-bottom:10px;">
                        <span style="display:inline-block;width:120px;"><?php esc_html_e('رنگ شروع:', 'khoshtip-kocholo'); ?></span>
                        <input type="text" name="k_bottom_bar_bg_start" value="<?php echo esc_attr($bottom_bar_bg_start); ?>" class="k-color-picker">
                    </label>
                    <label style="display:block;">
                        <span style="display:inline-block;width:120px;"><?php esc_html_e('رنگ پایان:', 'khoshtip-kocholo'); ?></span>
                        <input type="text" name="k_bottom_bar_bg_end" value="<?php echo esc_attr($bottom_bar_bg_end); ?>" class="k-color-picker">
                    </label>
                </td>
            </tr>
            <!-- اضافه کردن فیلد انتخاب رنگ متن -->
            <tr>
                <th scope="row"><?php esc_html_e('رنگ متن و آیکون‌ها', 'khoshtip-kocholo'); ?></th>
                <td>
                    <label>
                        <input type="text" name="k_bottom_bar_text_color" value="<?php echo esc_attr($bottom_bar_text_color); ?>" class="k-color-picker">
                        <p class="description"><?php esc_html_e('رنگ متن و آیکون‌های منوی ثابت فوتر را انتخاب کنید', 'khoshtip-kocholo'); ?></p>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('نمایش در صفحات', 'khoshtip-kocholo'); ?></th>
                <td>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_home" value="1" <?php checked($bottom_bar_show_home, '1'); ?>>
                        <?php esc_html_e('صفحه اصلی', 'khoshtip-kocholo'); ?>
                    </label>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_products" value="1" <?php checked($bottom_bar_show_products, '1'); ?>>
                        <?php esc_html_e('صفحات محصول', 'khoshtip-kocholo'); ?>
                    </label>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_all_products" value="1" <?php checked($bottom_bar_show_all_products, '1'); ?>>
                        <?php esc_html_e('صفحه همه محصولات', 'khoshtip-kocholo'); ?>
                    </label>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="k_bottom_bar_show_profile" value="1" <?php checked($bottom_bar_show_profile, '1'); ?>>
                        <?php esc_html_e('صفحه پروفایل', 'khoshtip-kocholo'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('دکمه‌های سفارشی (حداکثر 6)', 'khoshtip-kocholo'); ?></th>
                <td>
                    <div id="bottom-bar-buttons-repeater" class="k-repeater">
                        <?php foreach ($bottom_bar_buttons as $index => $button) : ?>
                            <div class="k-repeater-item">
                                <input type="text" name="k_bottom_bar_buttons[<?php echo $index; ?>][label]" value="<?php echo esc_attr($button['label'] ?? ''); ?>" placeholder="عنوان دکمه" style="width:20%;">
                                <input type="text" name="k_bottom_bar_buttons[<?php echo $index; ?>][url]" value="<?php echo esc_url($button['url'] ?? ''); ?>" placeholder="لینک" style="width:30%;">
                                <input type="hidden" name="k_bottom_bar_buttons[<?php echo $index; ?>][image]" class="bottom-bar-image-<?php echo $index; ?>" value="<?php echo esc_url($button['image'] ?? ''); ?>">
                                <button type="button" class="button upload-btn" onclick="uploadBottomBarImage(<?php echo $index; ?>)"><?php esc_html_e('انتخاب تصویر/آیکون', 'khoshtip-kocholo'); ?></button>
                                <?php if (!empty($button['image'])) : ?>
                                    <img src="<?php echo esc_url($button['image']); ?>" class="k-image-preview bottom-bar-image-preview-<?php echo $index; ?>" style="width:40px;height:40px;object-fit:contain;">
                                <?php else : ?>
                                    <img src="/placeholder.svg" class="k-image-preview bottom-bar-image-preview-<?php echo $index; ?>" style="display:none;width:40px;height:40px;object-fit:contain;">
                                <?php endif; ?>
                                <button type="button" class="k-remove-btn" onclick="this.parentElement.remove()">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-primary" onclick="addBottomBarButton()"><?php esc_html_e('افزودن دکمه', 'khoshtip-kocholo'); ?></button>
                </td>
            </tr>
        </table>
        <?php
    }
    
    private function save_footer_tab($data) {
        update_option('k_footer_site_title', sanitize_text_field($data['k_footer_site_title'] ?? ''));
        update_option('k_footer_site_description', sanitize_textarea_field($data['k_footer_site_description'] ?? ''));
        update_option('k_footer_useful_links', KK_Helper::sanitize_repeater_array($data['k_footer_useful_links'] ?? []));
        update_option('k_footer_customer_service_links', KK_Helper::sanitize_repeater_array($data['k_footer_customer_service_links'] ?? []));
        update_option('k_footer_contact_address', sanitize_text_field($data['k_footer_contact_address'] ?? ''));
        update_option('k_footer_contact_phone', sanitize_text_field($data['k_footer_contact_phone'] ?? ''));
        update_option('k_footer_copyright', sanitize_text_field($data['k_footer_copyright'] ?? ''));
        
        update_option('k_bottom_bar_bg_start', sanitize_hex_color($data['k_bottom_bar_bg_start'] ?? '#ffffff'));
        update_option('k_bottom_bar_bg_end', sanitize_hex_color($data['k_bottom_bar_bg_end'] ?? '#f9fafb'));
        update_option('k_bottom_bar_text_color', sanitize_hex_color($data['k_bottom_bar_text_color'] ?? '#374151'));
        update_option('k_bottom_bar_show_home', isset($data['k_bottom_bar_show_home']) ? '1' : '0');
        update_option('k_bottom_bar_show_products', isset($data['k_bottom_bar_show_products']) ? '1' : '0');
        update_option('k_bottom_bar_show_all_products', isset($data['k_bottom_bar_show_all_products']) ? '1' : '0');
        update_option('k_bottom_bar_show_profile', isset($data['k_bottom_bar_show_profile']) ? '1' : '0');
        
        $buttons = [];
        if (isset($data['k_bottom_bar_buttons']) && is_array($data['k_bottom_bar_buttons'])) {
            foreach (array_slice($data['k_bottom_bar_buttons'], 0, 6) as $button) {
                $buttons[] = [
                    'label' => sanitize_text_field($button['label'] ?? ''),
                    'url' => esc_url_raw($button['url'] ?? ''),
                    'image' => esc_url_raw($button['image'] ?? ''),
                ];
            }
        }
        update_option('k_bottom_bar_buttons', $buttons);
    }
}
?>
