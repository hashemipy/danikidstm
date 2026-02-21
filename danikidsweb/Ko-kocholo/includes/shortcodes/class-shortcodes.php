<?php
/**
 * Shortcodes for outputting JSON data
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Shortcodes {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('k_header_data', [$this, 'header_data']);
        add_shortcode('k_stories_data', [$this, 'stories_data']);
        add_shortcode('k_hero_data', [$this, 'hero_data']);
        add_shortcode('k_sales_data', [$this, 'sales_data']);
        add_shortcode('k_latest_products_data', [$this, 'latest_products_data']);
        add_shortcode('k_categories_data', [$this, 'categories_data']);
        add_shortcode('k_accordion_products_data', [$this, 'accordion_products_data']);
        add_shortcode('k_blog_data', [$this, 'blog_data']);
        add_shortcode('k_footer_data', [$this, 'footer_data']);
        add_shortcode('k_bottom_bar', [$this, 'bottom_bar_data']);
    }
    
    public function header_data() {
        return json_encode([
            'site_title' => get_option('k_site_title', 'فروشگاه پوشاک Shop'), // Updated default brand name
            'site_tagline' => get_option('k_site_tagline', ''),
            'site_title_color' => get_option('k_site_title_color', '#ffffff'),
            'site_tagline_color' => get_option('k_site_tagline_color', '#ffffff'),
            'nav_links' => get_option('k_nav_links', []),
            'mobile_menu_links' => get_option('k_mobile_menu_links', []),
            'header_gradient_start' => get_option('k_header_gradient_start', '#ff6b9d'),
            'header_gradient_end' => get_option('k_header_gradient_end', '#ffc3d7')
        ]);
    }
    
    public function stories_data() {
        $stories = get_option('k_stories', []);
        $transformed = [];
        
        foreach ($stories as $story) {
            $transformed[] = [
                'user_name' => $story['username'] ?? '',
                'avatar_url' => $story['avatar'] ?? '',
                'media' => $story['media'] ?? [],
                'button_url' => $story['button_url'] ?? ''
            ];
        }
        
        return json_encode(['stories' => $transformed]);
    }
    
    public function hero_data() {
        $slides = get_option('k_hero_slides', []);
        $transformed = [];
        
        foreach ($slides as $slide) {
            $transformed[] = [
                'background_image' => $slide['bg_image'] ?? '',
                'mobile_image' => $slide['mobile_image'] ?? ($slide['bg_image'] ?? ''),
                'title' => $slide['title'] ?? '',
                'description' => $slide['description'] ?? '',
                'button_text' => $slide['button_text'] ?? '',
                'button_url' => $slide['button_url'] ?? ''
            ];
        }
        
        return json_encode(['slides' => $transformed]);
    }
    
    public function sales_data() {
        $cache_key = 'k_sales_products_data';
        $cached = KK_Cache::get($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $end_timestamp = get_option('k_sale_end_timestamp', 0);
        
        if ($end_timestamp === 0 || time() >= $end_timestamp) {
            $data = ['sale_end_timestamp' => 0, 'product_ids' => []];
        } else {
            $products = wc_get_products(['status' => 'publish', 'limit' => -1]);
            $sale_ids = [];
            
            foreach ($products as $product) {
                if ($product->is_on_sale() && $product->is_in_stock()) {
                    $sale_ids[] = $product->get_id();
                }
            }
            
            $data = ['sale_end_timestamp' => $end_timestamp, 'product_ids' => $sale_ids];
        }
        
        $json = json_encode($data);
        $cache_time = $end_timestamp > 0 ? min($end_timestamp - time(), HOUR_IN_SECONDS) : MINUTE_IN_SECONDS;
        KK_Cache::set($cache_key, $json, $cache_time);
        
        return $json;
    }
    
    public function latest_products_data() {
        $cache_key = 'k_latest_products_data';
        $cached = KK_Cache::get($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $manual = get_option('k_latest_products', []);
        
        if (!empty($manual)) {
            $product_ids = KK_Helper::filter_in_stock($manual);
        } else {
            $product_ids = wc_get_products([
                'limit' => 20,
                'orderby' => 'date',
                'order' => 'DESC',
                'status' => 'publish',
                'stock_status' => 'instock',
                'return' => 'ids'
            ]);
        }
        
        $json = json_encode(['product_ids' => $product_ids]);
        KK_Cache::set($cache_key, $json, HOUR_IN_SECONDS);
        
        return $json;
    }
    
    public function categories_data() {
        $categories = get_option('k_categories', []);
        return json_encode(['categories' => $categories]);
    }
    
    public function accordion_products_data() {
        $sections = ['girls', 'boys', 'sport'];
        $data = [];
        
        $defaults = [
            'girls' => [
                'gradient_start' => '#ff6b9d',
                'gradient_end' => '#ffc3d7',
                'button_bg' => '#ff6b9d',
            ],
            'boys' => [
                'gradient_start' => '#4dabf7',
                'gradient_end' => '#a5d8ff',
                'button_bg' => '#4dabf7',
            ],
            'sport' => [
                'gradient_start' => '#51cf66',
                'gradient_end' => '#b2f2bb',
                'button_bg' => '#51cf66',
            ],
        ];
        
        foreach ($sections as $key) {
            $category_ids = KK_Helper::ensure_array(get_option("k_{$key}_category", []));
            
            $data["{$key}_title"] = get_option("k_accordion_{$key}_title", '');
            
            $data["{$key}_gradient_start"] = get_option("k_{$key}_gradient_start", $defaults[$key]['gradient_start']);
            $data["{$key}_gradient_end"] = get_option("k_{$key}_gradient_end", $defaults[$key]['gradient_end']);
            $data["{$key}_button_bg"] = get_option("k_{$key}_button_bg", $defaults[$key]['button_bg']);
            $data["{$key}_button_text"] = get_option("k_{$key}_button_text", '#ffffff');
            $data["{$key}_icon"] = get_option("k_{$key}_icon", '');
            $data["{$key}_layout"] = get_option("k_{$key}_layout", 'scroll');
            $data["{$key}_default_open"] = get_option("k_{$key}_default_open", '0');
            $data["{$key}_scroll_limit"] = get_option("k_{$key}_scroll_limit", 20);
            $data["{$key}_grid_limit"] = get_option("k_{$key}_grid_limit", 10);
            
            if (!empty($category_ids)) {
                $products = [];
                foreach ($category_ids as $cat_id) {
                    $products = array_merge($products, KK_Helper::get_products_by_category($cat_id));
                }
                $data["{$key}_products"] = KK_Helper::filter_in_stock(array_unique($products));
            } else {
                $manual = get_option("k_{$key}_products", []);
                $data["{$key}_products"] = KK_Helper::filter_in_stock($manual);
            }
        }
        
        // Extra accordions
        $extra = get_option('k_extra_accordions', []);
        $data['extra_accordions'] = [];
        
        foreach ($extra as $id => $accordion) {
            $accordion_data = [
                'id' => $id,
                'title' => $accordion['title'] ?? '',
                'gradient_start' => $accordion['gradient_start'] ?? '#9c27b0',
                'gradient_end' => $accordion['gradient_end'] ?? '#e1bee7',
                'button_bg' => $accordion['button_bg'] ?? '#9c27b0',
                'button_text' => $accordion['button_text'] ?? '#ffffff',
                'icon' => $accordion['icon'] ?? '',
                'layout' => $accordion['layout'] ?? 'scroll',
                'default_open' => $accordion['default_open'] ?? '0',
                'scroll_limit' => $accordion['scroll_limit'] ?? 20,
                'grid_limit' => $accordion['grid_limit'] ?? 10,
                'products' => [],
            ];
            
            $cats = KK_Helper::ensure_array($accordion['category'] ?? []);
            if (!empty($cats)) {
                $products = [];
                foreach ($cats as $cat_id) {
                    $products = array_merge($products, KK_Helper::get_products_by_category($cat_id));
                }
                $accordion_data['products'] = KK_Helper::filter_in_stock(array_unique($products));
            }
            
            $data['extra_accordions'][$id] = $accordion_data;
        }
        
        return json_encode($data);
    }
    
    public function blog_data() {
        $cache_key = 'k_blog_posts_data';
        $cached = KK_Cache::get($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $manual = get_option('k_blog_posts', []);
        
        if (!empty($manual)) {
            $post_ids = $manual;
        } else {
            $posts = get_posts(['numberposts' => 8, 'post_status' => 'publish', 'fields' => 'ids']);
            $post_ids = $posts;
        }
        
        $json = json_encode(['post_ids' => $post_ids]);
        KK_Cache::set($cache_key, $json, 30 * MINUTE_IN_SECONDS);
        
        return $json;
    }
    
    public function footer_data() {
        return json_encode([
            'site_title' => get_option('k_footer_site_title', ''),
            'site_description' => get_option('k_footer_site_description', ''),
            'useful_links' => get_option('k_footer_useful_links', []),
            'customer_service_links' => get_option('k_footer_customer_service_links', []),
            'contact_address' => get_option('k_footer_contact_address', ''),
            'contact_phone' => get_option('k_footer_contact_phone', ''),
            'copyright' => get_option('k_footer_copyright', '')
        ]);
    }
    
    public function bottom_bar_data() {
        return json_encode([
            'show_on_home' => get_option('k_bottom_bar_show_home', '1') === '1',
            'show_on_products' => get_option('k_bottom_bar_show_products', '1') === '1',
            'show_on_all_products' => get_option('k_bottom_bar_show_all_products', '1') === '1',
            'show_on_profile' => get_option('k_bottom_bar_show_profile', '1') === '1',
            'buttons' => get_option('k_bottom_bar_buttons', []),
            'bg_start' => get_option('k_bottom_bar_bg_start', '#ffffff'),
            'bg_end' => get_option('k_bottom_bar_bg_end', '#f9fafb')
        ]);
    }
}
