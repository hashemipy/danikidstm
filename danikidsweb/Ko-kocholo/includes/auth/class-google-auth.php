<?php
/**
 * Google OAuth Authentication
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Google_Auth {
    
    private static $instance = null;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->client_id = get_option('khoshtip_google_client_id', '');
        $this->client_secret = get_option('khoshtip_google_client_secret', '');
        $this->redirect_uri = home_url('/google-callback/');
        
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_callback']);
        
        add_action('wp_ajax_nopriv_google_auth_url', [$this, 'get_auth_url']);
        add_action('wp_ajax_google_auth_url', [$this, 'get_auth_url']);
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule('^google-callback/?', 'index.php?google_callback=1', 'top');
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'google_callback';
        return $vars;
    }
    
    public function get_auth_url() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'google_auth_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }
        
        $state = wp_create_nonce('google_auth_state');
        set_transient('google_auth_state_' . $state, true, 600);
        
        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];
        
        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        
        wp_send_json_success(['url' => $auth_url]);
    }
    
    public function handle_callback() {
        $is_callback = get_query_var('google_callback') || 
                       (isset($_GET['code']) && isset($_GET['state']) && 
                        strpos($_SERVER['REQUEST_URI'], 'google-callback') !== false);
        
        if (!$is_callback) {
            return;
        }
        
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            wp_redirect(home_url('/my-account/?error=missing_params'));
            exit;
        }
        
        $code = sanitize_text_field($_GET['code']);
        $state = sanitize_text_field($_GET['state']);
        
        if (!get_transient('google_auth_state_' . $state)) {
            wp_redirect(home_url('/my-account/?error=invalid_state'));
            exit;
        }
        
        delete_transient('google_auth_state_' . $state);
        
        $token = $this->get_access_token($code);
        
        if (is_wp_error($token)) {
            wp_redirect(home_url('/my-account/?error=token_failed'));
            exit;
        }
        
        $user_info = $this->get_user_info($token['access_token']);
        
        if (is_wp_error($user_info)) {
            wp_redirect(home_url('/my-account/?error=user_info_failed'));
            exit;
        }
        
        $user = $this->login_or_register($user_info);
        
        if (is_wp_error($user)) {
            wp_redirect(home_url('/my-account/?error=login_failed'));
            exit;
        }
        
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_redirect(home_url('/my-account/'));
        exit;
    }
    
    private function get_access_token($code) {
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'code' => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'grant_type' => 'authorization_code'
            ]
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('token_error', $body['error_description'] ?? 'Unknown error');
        }
        
        return $body;
    }
    
    private function get_user_info($access_token) {
        $response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', [
            'headers' => ['Authorization' => 'Bearer ' . $access_token]
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('user_info_error', $body['error']['message'] ?? 'Unknown error');
        }
        
        return $body;
    }
    
    private function login_or_register($user_info) {
        $email = sanitize_email($user_info['email']);
        $user = get_user_by('email', $email);
        
        if ($user) {
            if (isset($user_info['picture'])) {
                update_user_meta($user->ID, 'google_profile_picture', $user_info['picture']);
            }
            return $user;
        }
        
        // Create new user
        $username = sanitize_user($email);
        $first_name = sanitize_text_field($user_info['given_name'] ?? '');
        $last_name = sanitize_text_field($user_info['family_name'] ?? '');
        
        $counter = 1;
        $original_username = $username;
        while (username_exists($username)) {
            $username = $original_username . $counter++;
        }
        
        $user_id = wp_create_user($username, wp_generate_password(), $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => trim("$first_name $last_name") ?: $username,
            'role' => 'customer'
        ]);
        
        if (isset($user_info['picture'])) {
            update_user_meta($user_id, 'google_profile_picture', $user_info['picture']);
        }
        
        return get_user_by('id', $user_id);
    }
}
