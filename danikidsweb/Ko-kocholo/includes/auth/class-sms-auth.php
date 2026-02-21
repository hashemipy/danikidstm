<?php
/**
 * SMS Authentication (Faraz SMS)
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_SMS_Auth {
    
    private static $instance = null;
    private $api_key;
    private $sender_number;
    private $enabled;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->api_key = get_option('koodakfashion_faraz_api_key', '');
        $this->sender_number = get_option('koodakfashion_faraz_sender_number', '');
        $this->enabled = get_option('koodakfashion_sms_enabled', '0') === '1';
        
        if (!$this->enabled) {
            return;
        }
        
        add_action('wp_ajax_nopriv_send_sms_code', [$this, 'send_code']);
        add_action('wp_ajax_send_sms_code', [$this, 'send_code']);
        add_action('wp_ajax_nopriv_verify_sms_code', [$this, 'verify_code']);
        add_action('wp_ajax_verify_sms_code', [$this, 'verify_code']);
        
        add_action('woocommerce_register_form', [$this, 'add_phone_field']);
        add_action('woocommerce_created_customer', [$this, 'save_phone']);
    }
    
    public function add_phone_field() {
        $required = get_option('koodakfashion_require_phone', '0') === '1';
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_phone">
                <?php esc_html_e('شماره موبایل', 'koodakfashion'); ?>
                <?php if ($required) echo '<span class="required">*</span>'; ?>
            </label>
            <input type="tel" class="woocommerce-Input input-text" name="phone" id="reg_phone" 
                   value="<?php echo isset($_POST['phone']) ? esc_attr($_POST['phone']) : ''; ?>" 
                   placeholder="09123456789" pattern="09[0-9]{9}" <?php echo $required ? 'required' : ''; ?>>
        </p>
        <?php
    }
    
    public function save_phone($customer_id) {
        if (isset($_POST['phone'])) {
            $phone = sanitize_text_field($_POST['phone']);
            update_user_meta($customer_id, 'billing_phone', $phone);
            update_user_meta($customer_id, 'phone_number', $phone);
        }
    }
    
    public function send_code() {
        check_ajax_referer('sms_auth_nonce', 'nonce');
        
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        
        if (empty($phone)) {
            wp_send_json_error(['message' => __('شماره موبایل وارد نشده است', 'koodakfashion')]);
        }
        
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            wp_send_json_error(['message' => __('شماره موبایل نامعتبر است', 'koodakfashion')]);
        }
        
        $code = sprintf('%06d', mt_rand(100000, 999999));
        set_transient('sms_code_' . $phone, $code, 300);
        
        $site_name = 'کودک فشن';
        $message = sprintf('کد تایید شما: %s' . "\n" . '%s', $code, $site_name);
        $result = $this->send_sms($phone, $message);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('کد تایید ارسال شد', 'koodakfashion')]);
    }
    
    public function verify_code() {
        check_ajax_referer('sms_auth_nonce', 'nonce');
        
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $code = sanitize_text_field($_POST['code'] ?? '');
        
        if (empty($phone) || empty($code)) {
            wp_send_json_error(['message' => __('اطلاعات ناقص است', 'koodakfashion')]);
        }
        
        $saved_code = get_transient('sms_code_' . $phone);
        
        if (!$saved_code) {
            wp_send_json_error(['message' => __('کد تایید منقضی شده است', 'koodakfashion')]);
        }
        
        if ($saved_code !== $code) {
            wp_send_json_error(['message' => __('کد تایید نادرست است', 'koodakfashion')]);
        }
        
        delete_transient('sms_code_' . $phone);
        
        // Find or create user
        $users = get_users([
            'meta_key' => 'phone_number',
            'meta_value' => $phone,
            'number' => 1
        ]);
        
        if (!empty($users)) {
            $user = $users[0];
        } else {
            $username = $phone;
            $counter = 1;
            $original_username = $username;
            while (username_exists($username)) {
                $username = $original_username . $counter++;
            }
            
            $user_id = wp_create_user($username, wp_generate_password(), $phone . '@temp.local');
            
            if (is_wp_error($user_id)) {
                wp_send_json_error(['message' => __('خطا در ایجاد حساب', 'koodakfashin')]);
            }
            
            update_user_meta($user_id, 'phone_number', $phone);
            update_user_meta($user_id, 'billing_phone', $phone);
            wp_update_user(['ID' => $user_id, 'role' => 'customer']);
            
            $user = get_user_by('id', $user_id);
        }
        
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_send_json_success([
            'message' => __('ورود موفق', 'koodakfashion'),
            'redirect' => wc_get_page_permalink('myaccount')
        ]);
    }
    
    private function send_sms($phone, $message) {
        if (empty($this->api_key) || empty($this->sender_number)) {
            return new WP_Error('sms_config', __('تنظیمات SMS ناقص است', 'koodakfashion'));
        }
        
        $phone_e164 = $this->convert_to_e164($phone);
        $sender_e164 = $this->convert_to_e164($this->sender_number);
        
        $response = wp_remote_post('https://edge.ippanel.com/v1/api/send', [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->api_key, // حذف Bearer
            ],
            'body' => json_encode([
                'sending_type' => 'webservice', // اضافه sending_type
                'from_number' => $sender_e164, // استفاده از from_number
                'message' => $message,
                'params' => [ // استفاده از params
                    'recipients' => [$phone_e164]
                ]
            ]),
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['meta']['status']) && $body['meta']['status'] === true) {
            return true;
        }
        
        $error_message = isset($body['meta']['message']) ? $body['meta']['message'] : __('خطا در ارسال SMS', 'koodakfashion');
        return new WP_Error('sms_failed', $error_message);
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
