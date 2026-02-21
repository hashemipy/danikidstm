<?php
/**
 * AJAX Handler for sales and product management
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Ajax_Handler {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_k_search_products', [$this, 'search_products']);
        add_action('wp_ajax_k_get_products_by_category', [$this, 'get_products_by_category']);
        add_action('wp_ajax_k_start_sale', [$this, 'start_sale']);
        add_action('wp_ajax_k_cancel_sale', [$this, 'cancel_sale']);
        add_action('wp_ajax_k_remove_all_discounts', [$this, 'remove_all_discounts']);
        add_action('wp_ajax_k_remove_discount', [$this, 'remove_discount']);
    }
    
    public function search_products() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error();
        }
        
        $search = sanitize_text_field($_GET['q'] ?? '');
        $page = absint($_GET['page'] ?? 1);
        
        if (!function_exists('wc_get_products')) {
            wp_send_json(['results' => []]);
            return;
        }
        
        $products = wc_get_products([
            'limit' => 20,
            'page' => $page,
            'status' => 'publish',
            's' => $search
        ]);
        
        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->get_id(),
                'text' => $product->get_name() . ' (#' . $product->get_id() . ')'
            ];
        }
        
        wp_send_json(['results' => $results]);
    }
    
    public function get_products_by_category() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        if (!function_exists('wc_get_products')) {
            wp_send_json_success(['products' => []]);
            return;
        }
        
        $category_id = absint($_POST['category_id'] ?? 0);
        
        $args = ['status' => 'publish', 'limit' => -1];
        if ($category_id > 0) {
            $args['category'] = [$category_id];
        }
        
        $products = wc_get_products($args);
        $list = [];
        
        foreach ($products as $product) {
            $type = $product->get_type();
            $has_discount = false;
            
            if ($type === 'variable') {
                $variations = $product->get_available_variations();
                foreach ($variations as $v) {
                    if ($v['display_price'] < $v['display_regular_price']) {
                        $has_discount = true;
                        break;
                    }
                }
                $price = $product->get_variation_regular_price('min', true);
            } else {
                $has_discount = !empty($product->get_sale_price());
                $price = $product->get_regular_price();
            }
            
            $list[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'regular_price' => wc_price($price),
                'has_discount' => $has_discount,
                'type' => $type
            ];
        }
        
        wp_send_json_success(['products' => $list]);
    }
    
    public function start_sale() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        if (!function_exists('wc_get_product')) {
            wp_send_json_error(['message' => __('ووکامرس فعال نیست', 'khoshtip-kocholo')]);
        }
        
        $product_ids = isset($_POST['product_ids']) ? array_map('absint', $_POST['product_ids']) : [];
        $discount = absint($_POST['discount_percent'] ?? 0);
        $hours = absint($_POST['sale_hours'] ?? 0);
        $minutes = absint($_POST['sale_minutes'] ?? 0);
        
        if (empty($product_ids) || $discount <= 0 || $discount >= 100) {
            wp_send_json_error(['message' => __('اطلاعات نامعتبر', 'khoshtip-kocholo')]);
        }
        
        if ($hours <= 0 && $minutes <= 0) {
            wp_send_json_error(['message' => __('مدت زمان وارد کنید', 'khoshtip-kocholo')]);
        }
        
        $duration = ($hours * 3600) + ($minutes * 60);
        $end_timestamp = time() + $duration;
        
        update_option('k_sale_hours', $hours);
        update_option('k_sale_minutes', $minutes);
        update_option('k_sale_end_timestamp', $end_timestamp);
        
        $discounted = [];
        
        foreach ($product_ids as $id) {
            $product = wc_get_product($id);
            if (!$product) continue;
            
            if ($product->get_type() === 'variable') {
                $variations = $product->get_children();
                foreach ($variations as $var_id) {
                    $variation = wc_get_product($var_id);
                    if ($variation) {
                        $regular = $variation->get_regular_price();
                        if ($regular > 0) {
                            $sale = $regular * (1 - ($discount / 100));
                            $variation->set_sale_price(wc_format_decimal($sale, 2));
                            $variation->save();
                        }
                    }
                }
                $discounted[] = $id;
            } else {
                $regular = $product->get_regular_price();
                if ($regular > 0) {
                    $sale = $regular * (1 - ($discount / 100));
                    $product->set_sale_price(wc_format_decimal($sale, 2));
                    $product->save();
                    $discounted[] = $id;
                }
            }
        }
        
        // Save to k_discounted_products for the list display
        update_option('k_discounted_products', $discounted);
        update_option('k_sale_product_ids', $discounted);
        KK_Cache::clear_products_cache();
        
        wp_send_json_success([
            'message' => sprintf(__('%d محصول تخفیف خوردند', 'khoshtip-kocholo'), count($discounted)),
            'end_timestamp' => $end_timestamp
        ]);
    }
    
    public function cancel_sale() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        $product_ids = get_option('k_discounted_products', []);
        
        foreach ($product_ids as $id) {
            $product = wc_get_product($id);
            if (!$product) continue;
            
            if ($product->get_type() === 'variable') {
                $variations = $product->get_children();
                foreach ($variations as $var_id) {
                    $variation = wc_get_product($var_id);
                    if ($variation) {
                        $variation->set_sale_price('');
                        $variation->save();
                    }
                }
            } else {
                $product->set_sale_price('');
                $product->save();
            }
        }
        
        update_option('k_sale_end_timestamp', 0);
        update_option('k_discounted_products', []);
        update_option('k_sale_product_ids', []);
        KK_Cache::clear_products_cache();
        
        wp_send_json_success(['message' => __('حراج لغو شد', 'khoshtip-kocholo')]);
    }
    
    public function remove_all_discounts() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        if (!function_exists('wc_get_products')) {
            wp_send_json_error(['message' => __('ووکامرس فعال نیست', 'khoshtip-kocholo')]);
        }
        
        $products = wc_get_products(['status' => 'publish', 'limit' => -1]);
        $count = 0;
        
        foreach ($products as $product) {
            $removed = false;
            
            if ($product->get_type() === 'variable') {
                $variations = $product->get_children();
                foreach ($variations as $var_id) {
                    $variation = wc_get_product($var_id);
                    if ($variation && $variation->get_sale_price()) {
                        $variation->set_sale_price('');
                        $variation->save();
                        $removed = true;
                    }
                }
            } else {
                if ($product->get_sale_price()) {
                    $product->set_sale_price('');
                    $product->save();
                    $removed = true;
                }
            }
            
            if ($removed) $count++;
        }
        
        update_option('k_sale_end_timestamp', 0);
        update_option('k_discounted_products', []);
        update_option('k_sale_product_ids', []);
        KK_Cache::clear_products_cache();
        
        wp_send_json_success([
            'message' => sprintf(__('تخفیف %d محصول حذف شد', 'khoshtip-kocholo'), $count),
            'discounted_products' => []
        ]);
    }
    
    public function remove_discount() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('دسترسی رد شد', 'khoshtip-kocholo')]);
        }
        
        $product_id = absint($_POST['product_id'] ?? 0);
        
        if (!$product_id) {
            wp_send_json_error(['message' => __('محصول نامعتبر', 'khoshtip-kocholo')]);
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(['message' => __('محصول یافت نشد', 'khoshtip-kocholo')]);
        }
        
        if ($product->get_type() === 'variable') {
            $variations = $product->get_children();
            foreach ($variations as $var_id) {
                $variation = wc_get_product($var_id);
                if ($variation) {
                    $variation->set_sale_price('');
                    $variation->save();
                }
            }
        } else {
            $product->set_sale_price('');
            $product->save();
        }
        
        // Remove from discounted products list
        $discounted = get_option('k_discounted_products', []);
        $discounted = array_diff($discounted, [$product_id]);
        update_option('k_discounted_products', array_values($discounted));
        update_option('k_sale_product_ids', array_values($discounted));
        
        KK_Cache::clear_products_cache();
        
        wp_send_json_success([
            'message' => __('تخفیف حذف شد', 'khoshtip-kocholo'),
            'discounted_products' => $this->get_discounted_products_for_js()
        ]);
    }
    
    private function get_discounted_products_for_js() {
        $discounted_products_ids = get_option('k_discounted_products', []);
        $product_list = [];
        
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
                            $var_sale_price = $variation->get_sale_price();
                            
                            if (!empty($var_sale_price) && $var_sale_price < $lowest_sale_price) {
                                $lowest_sale_price = floatval($var_sale_price);
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
                
                $product_list[] = [
                    'id' => $product_id,
                    'name' => $product->get_name(),
                    'regular_price' => $display_regular_price,
                    'sale_price' => $display_sale_price,
                    'discount_percent' => $discount_percent
                ];
            }
        }
        
        return $product_list;
    }
}
