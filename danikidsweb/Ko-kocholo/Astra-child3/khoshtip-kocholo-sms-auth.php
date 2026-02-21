<?php
/**
 * Faraz SMS Authentication Handler
 * سیستم احراز هویت با شماره موبایل و ارسال کد تایید
 */

class Khoshtip_SMS_Auth {
    
    private $api_key;
    private $sender_number;
    private $enabled;
    
    public function __construct() {
        $this->api_key = get_option('khoshtip_faraz_api_key', '');
        $this->sender_number = get_option('khoshtip_faraz_sender_number', '');
        $this->enabled = get_option('khoshtip_sms_enabled', '0') === '1';
        
        if ($this->enabled) {
            add_action('wp_ajax_nopriv_send_sms_code', array($this, 'send_sms_code'));
            add_action('wp_ajax_send_sms_code', array($this, 'send_sms_code'));
            
            add_action('wp_ajax_nopriv_verify_sms_code', array($this, 'verify_sms_code'));
            add_action('wp_ajax_verify_sms_code', array($this, 'verify_sms_code'));
            
            add_action('woocommerce_register_form', array($this, 'add_phone_field_to_registration'));
            add_action('woocommerce_created_customer', array($this, 'save_phone_number'));
        }
    }
    
    /**
     * اضافه کردن فیلد شماره موبایل به فرم ثبت‌نام
     */
    public function add_phone_field_to_registration() {
        $required = get_option('khoshtip_require_phone', '0') === '1';
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_phone">
                شماره موبایل&nbsp;<?php if ($required) echo '<span class="required">*</span>'; ?>
            </label>
            <input type="tel" 
                   class="woocommerce-Input woocommerce-Input--text input-text" 
                   name="phone" 
                   id="reg_phone" 
                   value="<?php echo (!empty($_POST['phone'])) ? esc_attr(wp_unslash($_POST['phone'])) : ''; ?>" 
                   placeholder="09123456789"
                   pattern="09[0-9]{9}"
                   <?php if ($required) echo 'required'; ?> />
            <small class="form-text">شماره موبایل خود را با فرمت 09xxxxxxxxx وارد کنید</small>
        </p>
        <?php
    }
    
    /**
     * ذخیره شماره موبایل هنگام ثبت‌نام
     */
    public function save_phone_number($customer_id) {
        if (isset($_POST['phone'])) {
            $phone = sanitize_text_field($_POST['phone']);
            update_user_meta($customer_id, 'billing_phone', $phone);
            update_user_meta($customer_id, 'phone_number', $phone);
        }
    }
    
    /**
     * ارسال کد تایید به شماره موبایل
     */
    public function send_sms_code() {
        check_ajax_referer('sms_auth_nonce', 'nonce');
        
        if (!isset($_POST['phone'])) {
            wp_send_json_error(array('message' => 'شماره موبایل وارد نشده است'));
            return;
        }
        
        $phone = sanitize_text_field($_POST['phone']);
        
        // اعتبارسنجی شماره موبایل
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            wp_send_json_error(array('message' => 'شماره موبایل نامعتبر است'));
            return;
        }
        
        // تولید کد تایید 6 رقمی
        $code = sprintf('%06d', mt_rand(100000, 999999));
        
        // ذخیره کد در transient برای 5 دقیقه
        set_transient('sms_code_' . $phone, $code, 300);
        
        // ارسال پیامک
        $result = $this->send_sms($phone, "کد تایید شما: {$code}\nکودک فشن");
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success(array('message' => 'کد تایید به شماره موبایل شما ارسال شد'));
    }
    
    /**
     * تایید کد و ورود/ثبت‌نام کاربر
     */
    public function verify_sms_code() {
        check_ajax_referer('sms_auth_nonce', 'nonce');
        
        if (!isset($_POST['phone']) || !isset($_POST['code'])) {
            wp_send_json_error(array('message' => 'اطلاعات ناقص است'));
            return;
        }
        
        $phone = sanitize_text_field($_POST['phone']);
        $code = sanitize_text_field($_POST['code']);
        
        // بررسی کد تایید
        $saved_code = get_transient('sms_code_' . $phone);
        
        if (!$saved_code) {
            wp_send_json_error(array('message' => 'کد تایید منقضی شده است'));
            return;
        }
        
        if ($saved_code !== $code) {
            wp_send_json_error(array('message' => 'کد تایید نادرست است'));
            return;
        }
        
        // حذف کد استفاده شده
        delete_transient('sms_code_' . $phone);
        
        // جستجوی کاربر با این شماره موبایل
        $users = get_users(array(
            'meta_key' => 'phone_number',
            'meta_value' => $phone,
            'number' => 1
        ));
        
        if (!empty($users)) {
            // کاربر وجود دارد - ورود
            $user = $users[0];
        } else {
            // ثبت‌نام کاربر جدید
            $username = $phone;
            $email = $phone . '@temp.local'; // ایمیل موقت
            
            // بررسی تکراری نبودن username
            $counter = 1;
            $original_username = $username;
            while (username_exists($username)) {
                $username = $original_username . $counter;
                $counter++;
            }
            
            $user_id = wp_create_user($username, wp_generate_password(), $email);
            
            if (is_wp_error($user_id)) {
                wp_send_json_error(array('message' => 'خطا در ایجاد حساب کاربری'));
                return;
            }
            
            // ذخیره شماره موبایل
            update_user_meta($user_id, 'phone_number', $phone);
            update_user_meta($user_id, 'billing_phone', $phone);
            
            // تنظیم نقش
            wp_update_user(array(
                'ID' => $user_id,
                'role' => 'customer'
            ));
            
            $user = get_user_by('id', $user_id);
        }
        
        // ورود کاربر
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_send_json_success(array(
            'message' => 'ورود موفقیت‌آمیز بود',
            'redirect' => wc_get_page_permalink('myaccount')
        ));
    }
    
    /**
     * ارسال پیامک از طریق Faraz SMS
     */
    private function send_sms($phone, $message) {
        if (empty($this->api_key) || empty($this->sender_number)) {
            return new WP_Error('sms_config', 'تنظیمات فراز اس‌ام‌اس کامل نیست');
        }
        
        $phone_e164 = $this->convert_to_e164($phone);
        $sender_e164 = $this->convert_to_e164($this->sender_number);
        
        $url = 'https://edge.ippanel.com/v1/api/send';
        
        $response = wp_remote_post($url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => $this->api_key, // حذف Bearer
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'sending_type' => 'webservice', // اضافه sending_type
                'from_number' => $sender_e164, // استفاده از from_number
                'message' => $message,
                'params' => array( // استفاده از params
                    'recipients' => array($phone_e164)
                )
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['meta']['status']) && $body['meta']['status'] === true) {
            return true;
        }
        
        $error_message = isset($body['meta']['message']) ? $body['meta']['message'] : 'خطا در ارسال پیامک';
        return new WP_Error('sms_send_failed', $error_message);
    }
    
    /**
     * تبدیل شماره به فرمت E.164
     */
    private function convert_to_e164($phone) {
        // حذف فاصله‌ها و کاراکترهای غیرضروری
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // اگر با 09 شروع می‌شود، تبدیل به +98
        if (preg_match('/^09[0-9]{9}$/', $phone)) {
            return '+98' . substr($phone, 1);
        }
        
        // اگر با 3000505 است، تبدیل به +983000505
        if ($phone === '3000505') {
            return '+983000505';
        }
        
        // اگر قبلا + دارد، همان را برگردان
        if (strpos($phone, '+') === 0) {
            return $phone;
        }
        
        // در غیر این صورت، فرض می‌کنیم ایران است
        return '+98' . ltrim($phone, '0');
    }
}

// راه‌اندازی کلاس
new Khoshtip_SMS_Auth();
