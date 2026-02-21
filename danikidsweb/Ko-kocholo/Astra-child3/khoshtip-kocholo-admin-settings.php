<?php
/**
 * Admin Settings Panel
 * پنل تنظیمات مدیریت برای Google OAuth و Faraz SMS
 */

class Khoshtip_Admin_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }
    
    /**
     * اضافه کردن منوی تنظیمات به پنل مدیریت
     */
    public function add_admin_menu() {
        add_menu_page(
            'تنظیمات احراز هویت',
            'تنظیمات احراز هویت',
            'manage_options',
            'khoshtip-auth-settings',
            array($this, 'settings_page'),
            'dashicons-admin-generic',
            30
        );
    }
    
    /**
     * ثبت تنظیمات
     */
    public function register_settings() {
        // Google OAuth Settings
        register_setting('khoshtip_auth_settings', 'khoshtip_google_client_id');
        register_setting('khoshtip_auth_settings', 'khoshtip_google_client_secret');
        
        // Faraz SMS Settings
        register_setting('khoshtip_auth_settings', 'khoshtip_faraz_api_key');
        register_setting('khoshtip_auth_settings', 'khoshtip_faraz_sender_number');
        register_setting('khoshtip_auth_settings', 'khoshtip_sms_enabled');
        
        // General Settings
        register_setting('khoshtip_auth_settings', 'khoshtip_require_phone');
    }
    
    /**
     * بارگذاری استایل‌های مدیریت
     */
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'toplevel_page_khoshtip-auth-settings') {
            return;
        }
        
        wp_enqueue_style('khoshtip-admin-settings', get_stylesheet_directory_uri() . '/assets/css/admin-settings.css', array(), '1.0.0');
    }
    
    /**
     * صفحه تنظیمات
     */
    public function settings_page() {
        ?>
        <div class="wrap khoshtip-settings-wrap">
            <h1>تنظیمات احراز هویت</h1>
            
            <?php if (isset($_GET['settings-updated'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>تنظیمات با موفقیت ذخیره شد.</p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php settings_fields('khoshtip_auth_settings'); ?>
                
                <div class="khoshtip-settings-container">
                    
                    <!-- Google OAuth Settings -->
                    <div class="settings-section">
                        <h2>تنظیمات Google OAuth</h2>
                        <p class="description">برای فعال‌سازی ورود با Google، اطلاعات زیر را وارد کنید.</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="khoshtip_google_client_id">Client ID</label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="khoshtip_google_client_id" 
                                           name="khoshtip_google_client_id" 
                                           value="<?php echo esc_attr(get_option('khoshtip_google_client_id', '')); ?>" 
                                           class="regular-text" 
                                           placeholder="1063548887243-xxx.apps.googleusercontent.com">
                                    <p class="description">Client ID از Google Cloud Console</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="khoshtip_google_client_secret">Client Secret</label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="khoshtip_google_client_secret" 
                                           name="khoshtip_google_client_secret" 
                                           value="<?php echo esc_attr(get_option('khoshtip_google_client_secret', '')); ?>" 
                                           class="regular-text" 
                                           placeholder="GOCSPX-xxxxxxxxxxxxx">
                                    <p class="description">Client Secret از Google Cloud Console</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Redirect URI</th>
                                <td>
                                    <code><?php echo esc_url(home_url('/google-callback/')); ?></code>
                                    <p class="description">این آدرس را در Google Cloud Console در قسمت Authorized redirect URIs اضافه کنید.</p>
                                </td>
                            </tr>
                        </table>
                        
                        <button type="button" class="button button-secondary" onclick="openHelpModal('google')">
                            راهنمای ایجاد Google API
                        </button>
                    </div>
                    
                    <!-- Faraz SMS Settings -->
                    <div class="settings-section">
                        <h2>تنظیمات فراز اس‌ام‌اس</h2>
                        <p class="description">برای فعال‌سازی ورود با شماره موبایل و ارسال پیامک، اطلاعات زیر را وارد کنید.</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="khoshtip_sms_enabled">فعال‌سازی ورود با موبایل</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               id="khoshtip_sms_enabled" 
                                               name="khoshtip_sms_enabled" 
                                               value="1" 
                                               <?php checked(get_option('khoshtip_sms_enabled'), '1'); ?>>
                                        فعال‌سازی ورود و ثبت‌نام با شماره موبایل
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="khoshtip_faraz_api_key">API Key فراز اس‌ام‌اس</label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="khoshtip_faraz_api_key" 
                                           name="khoshtip_faraz_api_key" 
                                           value="<?php echo esc_attr(get_option('khoshtip_faraz_api_key', '')); ?>" 
                                           class="regular-text" 
                                           placeholder="your-api-key">
                                    <p class="description">کلید API از پنل فراز اس‌ام‌اس</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="khoshtip_faraz_sender_number">شماره ارسال‌کننده</label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="khoshtip_faraz_sender_number" 
                                           name="khoshtip_faraz_sender_number" 
                                           value="<?php echo esc_attr(get_option('khoshtip_faraz_sender_number', '')); ?>" 
                                           class="regular-text" 
                                           placeholder="50002710xxxxx">
                                    <p class="description">شماره خط ارسال پیامک</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="khoshtip_require_phone">الزامی بودن شماره موبایل</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               id="khoshtip_require_phone" 
                                               name="khoshtip_require_phone" 
                                               value="1" 
                                               <?php checked(get_option('khoshtip_require_phone'), '1'); ?>>
                                        شماره موبایل برای ثبت‌نام الزامی باشد
                                    </label>
                                </td>
                            </tr>
                        </table>
                        
                        <button type="button" class="button button-secondary" onclick="openHelpModal('faraz')">
                            راهنمای ایجاد Faraz SMS API
                        </button>
                    </div>
                    
                </div>
                
                <?php submit_button('ذخیره تنظیمات'); ?>
            </form>
        </div>
        
        <!-- Help Modal -->
        <div id="helpModal" class="khoshtip-modal" style="display: none;">
            <div class="khoshtip-modal-content">
                <span class="khoshtip-modal-close" onclick="closeHelpModal()">&times;</span>
                <div id="helpModalContent"></div>
            </div>
        </div>
        
        <script>
        function openHelpModal(type) {
            const modal = document.getElementById('helpModal');
            const content = document.getElementById('helpModalContent');
            
            if (type === 'google') {
                content.innerHTML = `
                    <h2>راهنمای ایجاد Google OAuth API</h2>
                    <div class="help-steps">
                        <div class="help-step">
                            <h3>مرحله ۱: ورود به Google Cloud Console</h3>
                            <p>به آدرس <a href="https://console.cloud.google.com" target="_blank">console.cloud.google.com</a> بروید و با حساب Google خود وارد شوید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۲: ایجاد پروژه جدید</h3>
                            <p>از منوی بالا، روی "Select a project" کلیک کنید و سپس "New Project" را انتخاب کنید.</p>
                            <p>نام پروژه را وارد کنید (مثلاً "My Website Auth") و روی "Create" کلیک کنید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۳: فعال‌سازی Google+ API</h3>
                            <p>از منوی سمت چپ، به <strong>APIs & Services > Library</strong> بروید.</p>
                            <p>"Google+ API" را جستجو کنید و روی آن کلیک کنید.</p>
                            <p>روی دکمه "Enable" کلیک کنید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۴: ایجاد OAuth Credentials</h3>
                            <p>از منوی سمت چپ، به <strong>APIs & Services > Credentials</strong> بروید.</p>
                            <p>روی "Create Credentials" کلیک کنید و "OAuth client ID" را انتخاب کنید.</p>
                            <p>اگر اولین بار است، باید OAuth consent screen را تنظیم کنید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۵: تنظیم OAuth Consent Screen</h3>
                            <p>User Type را "External" انتخاب کنید و روی "Create" کلیک کنید.</p>
                            <p>اطلاعات برنامه را وارد کنید (نام برنامه، ایمیل پشتیبانی، و غیره).</p>
                            <p>روی "Save and Continue" کلیک کنید تا به مرحله بعد بروید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۶: ایجاد Client ID</h3>
                            <p>Application type را "Web application" انتخاب کنید.</p>
                            <p>نام را وارد کنید (مثلاً "Website Login").</p>
                            <p>در قسمت <strong>Authorized JavaScript origins</strong> این آدرس‌ها را اضافه کنید:</p>
                            <ul>
                                <li><code><?php echo esc_url(home_url()); ?></code></li>
                            </ul>
                            <p>در قسمت <strong>Authorized redirect URIs</strong> این آدرس را اضافه کنید:</p>
                            <ul>
                                <li><code><?php echo esc_url(home_url('/google-callback/')); ?></code></li>
                            </ul>
                            <p>روی "Create" کلیک کنید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۷: کپی کردن اطلاعات</h3>
                            <p>پس از ایجاد، یک پنجره با Client ID و Client Secret نمایش داده می‌شود.</p>
                            <p>این اطلاعات را کپی کنید و در فرم بالا وارد کنید.</p>
                        </div>
                    </div>
                `;
            } else if (type === 'faraz') {
                content.innerHTML = `
                    <h2>راهنمای ایجاد Faraz SMS API</h2>
                    <div class="help-steps">
                        <div class="help-step">
                            <h3>مرحله ۱: ثبت‌نام در فراز اس‌ام‌اس</h3>
                            <p>به آدرس <a href="https://farazsms.com" target="_blank">farazsms.com</a> بروید.</p>
                            <p>روی "ثبت‌نام" کلیک کنید و فرم ثبت‌نام را تکمیل کنید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۲: ورود به پنل کاربری</h3>
                            <p>با نام کاربری و رمز عبور خود وارد پنل شوید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۳: دریافت API Key</h3>
                            <p>از منوی پنل، به قسمت <strong>تنظیمات > API</strong> بروید.</p>
                            <p>API Key خود را کپی کنید.</p>
                            <p>اگر API Key ندارید، روی "ایجاد API Key" کلیک کنید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۴: دریافت شماره خط</h3>
                            <p>از منوی پنل، به قسمت <strong>خطوط من</strong> بروید.</p>
                            <p>شماره خط ارسال پیامک خود را کپی کنید.</p>
                            <p>اگر خط ندارید، باید از قسمت "خرید خط" اقدام کنید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>مرحله ۵: وارد کردن اطلاعات</h3>
                            <p>API Key و شماره خط را در فرم بالا وارد کنید.</p>
                            <p>گزینه "فعال‌سازی ورود با موبایل" را فعال کنید.</p>
                            <p>روی "ذخیره تنظیمات" کلیک کنید.</p>
                        </div>
                        
                        <div class="help-step">
                            <h3>نکات مهم</h3>
                            <ul>
                                <li>حساب شما باید دارای اعتبار کافی برای ارسال پیامک باشد.</li>
                                <li>شماره خط باید تایید شده و فعال باشد.</li>
                                <li>برای تست، می‌توانید از پنل فراز اس‌ام‌اس یک پیامک آزمایشی ارسال کنید.</li>
                            </ul>
                        </div>
                    </div>
                `;
            }
            
            modal.style.display = 'block';
        }
        
        function closeHelpModal() {
            document.getElementById('helpModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('helpModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        </script>
        <?php
    }
}

// Initialize admin settings
new Khoshtip_Admin_Settings();
