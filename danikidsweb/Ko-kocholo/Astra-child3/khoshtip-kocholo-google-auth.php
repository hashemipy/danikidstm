<?php
/**
 * Google OAuth Authentication Handler
 * سیستم احراز هویت Google برای ورود به سایت
 */

class Khoshtip_Google_Auth {
    
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    
    public function __construct() {
        $this->client_id = get_option('khoshtip_google_client_id', '1063548887243-og14p852tat4fqor88ckinl65fufn9jc.apps.googleusercontent.com');
        $this->client_secret = get_option('khoshtip_google_client_secret', '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');
        $this->redirect_uri = home_url('/google-callback/');
        
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_google_callback'));
        
        add_action('wp_ajax_nopriv_google_auth_url', array($this, 'get_google_auth_url'));
        add_action('wp_ajax_google_auth_url', array($this, 'get_google_auth_url'));
    }
    
    /**
     * اضافه کردن rewrite rules برای Google callback
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^google-callback/?', 'index.php?google_callback=1', 'top');
    }
    
    /**
     * اضافه کردن query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'google_callback';
        return $vars;
    }
    
    /**
     * دریافت URL احراز هویت Google
     */
    public function get_google_auth_url() {
        // بررسی nonce برای امنیت
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'google_auth_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $state = wp_create_nonce('google_auth_state');
        set_transient('google_auth_state_' . $state, true, 600); // 10 دقیقه
        
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account'
        );
        
        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        
        wp_send_json_success(array('url' => $auth_url));
    }
    
    /**
     * مدیریت callback از Google
     */
    public function handle_google_callback() {
        $is_callback = get_query_var('google_callback') || (isset($_GET['code']) && isset($_GET['state']) && strpos($_SERVER['REQUEST_URI'], 'google-callback') !== false);
        
        if (!$is_callback) {
            return;
        }
        
        // بررسی وجود کد و state
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            wp_redirect(home_url('/my-account/?error=missing_params'));
            exit;
        }
        
        $code = sanitize_text_field($_GET['code']);
        $state = sanitize_text_field($_GET['state']);
        
        // بررسی state برای امنیت
        if (!get_transient('google_auth_state_' . $state)) {
            wp_redirect(home_url('/my-account/?error=invalid_state'));
            exit;
        }
        
        delete_transient('google_auth_state_' . $state);
        
        // دریافت access token
        $token_response = $this->get_access_token($code);
        
        if (is_wp_error($token_response)) {
            wp_redirect(home_url('/my-account/?error=token_failed&message=' . urlencode($token_response->get_error_message())));
            exit;
        }
        
        // دریافت اطلاعات کاربر
        $user_info = $this->get_user_info($token_response['access_token']);
        
        if (is_wp_error($user_info)) {
            wp_redirect(home_url('/my-account/?error=user_info_failed&message=' . urlencode($user_info->get_error_message())));
            exit;
        }
        
        // ورود یا ثبت‌نام کاربر
        $user = $this->login_or_register_user($user_info);
        
        if (is_wp_error($user)) {
            wp_redirect(home_url('/my-account/?error=login_failed&message=' . urlencode($user->get_error_message())));
            exit;
        }
        
        // ورود کاربر به سیستم
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_redirect(home_url('/my-account/'));
        exit;
    }
    
    /**
     * دریافت access token از Google
     */
    private function get_access_token($code) {
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code' => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'grant_type' => 'authorization_code'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('token_error', $body['error_description']);
        }
        
        return $body;
    }
    
    /**
     * دریافت اطلاعات کاربر از Google
     */
    private function get_user_info($access_token) {
        $response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('user_info_error', $body['error']['message']);
        }
        
        return $body;
    }
    
    /**
     * ورود یا ثبت‌نام کاربر
     */
    private function login_or_register_user($user_info) {
        $email = sanitize_email($user_info['email']);
        
        // بررسی وجود کاربر با این ایمیل
        $user = get_user_by('email', $email);
        
        if ($user) {
            // کاربر وجود دارد، فقط ورود
            // بروزرسانی تصویر پروفایل اگر تغییر کرده
            if (isset($user_info['picture'])) {
                update_user_meta($user->ID, 'google_profile_picture', $user_info['picture']);
            }
            return $user;
        }
        
        // ثبت‌نام کاربر جدید
        $username = sanitize_user($user_info['email']);
        $first_name = isset($user_info['given_name']) ? sanitize_text_field($user_info['given_name']) : '';
        $last_name = isset($user_info['family_name']) ? sanitize_text_field($user_info['family_name']) : '';
        
        // اگر username تکراری بود، یک عدد اضافه کن
        $original_username = $username;
        $counter = 1;
        while (username_exists($username)) {
            $username = $original_username . $counter;
            $counter++;
        }
        
        $user_id = wp_create_user($username, wp_generate_password(), $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // بروزرسانی اطلاعات کاربر
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
            'role' => 'customer' // نقش مشتری برای WooCommerce
        ));
        
        // ذخیره Google ID
        update_user_meta($user_id, 'google_id', $user_info['id']);
        
        // ذخیره تصویر پروفایل
        if (isset($user_info['picture'])) {
            update_user_meta($user_id, 'google_profile_picture', $user_info['picture']);
        }
        
        return get_user_by('id', $user_id);
    }
}

// راه‌اندازی کلاس
new Khoshtip_Google_Auth();

function khoshtip_google_auth_activate() {
    $auth = new Khoshtip_Google_Auth();
    $auth->add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'khoshtip_google_auth_activate');
