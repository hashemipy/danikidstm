<?php
/**
 * Plugin Name: کنترلر فروشگاه پوشاک Shop
 * Plugin URI: https://koodakefashion.ir
 * Description: افزونه حرفه‌ای برای مدیریت محتوای صفحه اصلی، احراز هویت Google و فراز اس‌ام‌اس
 * Version: 3.0.0
 * Author: hashemipy
 * Text Domain: koodakefashion
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KK_VERSION', '3.0.0');
define('KK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KK_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class Khoshtip_Kocholo {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        // Core files - always load
        require_once KK_PLUGIN_DIR . 'includes/class-helper.php';
        require_once KK_PLUGIN_DIR . 'includes/class-cache.php';
        
        // Admin files
        require_once KK_PLUGIN_DIR . 'includes/admin/class-admin.php';
        require_once KK_PLUGIN_DIR . 'includes/admin/class-admin-tabs.php';
        
        // Authentication
        require_once KK_PLUGIN_DIR . 'includes/auth/class-google-auth.php';
        require_once KK_PLUGIN_DIR . 'includes/auth/class-sms-auth.php';
        
        // Shortcodes
        require_once KK_PLUGIN_DIR . 'includes/shortcodes/class-shortcodes.php';
        
        // AJAX Handlers
        require_once KK_PLUGIN_DIR . 'includes/ajax/class-ajax-handler.php';
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    public function init() {
        if (is_admin()) {
            KK_Admin::instance();
        }
        
        // These work on both frontend and admin
        KK_Google_Auth::instance();
        KK_SMS_Auth::instance();
        KK_Shortcodes::instance();
        KK_Ajax_Handler::instance();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('khoshtip-kocholo', false, dirname(KK_PLUGIN_BASENAME) . '/languages');
    }
    
    public function activate() {
        $this->ensure_products_page();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function ensure_products_page() {
        $products_page = get_page_by_path('products');
        
        if (!$products_page) {
            wp_insert_post([
                'post_title' => 'محصولات',
                'post_name' => 'products',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '<!-- صفحه محصولات -->',
            ]);
        }
    }
}

/**
 * Initialize plugin
 */
function khoshtip_kocholo() {
    return Khoshtip_Kocholo::instance();
}

// Start the plugin
add_action('plugins_loaded', 'khoshtip_kocholo');
