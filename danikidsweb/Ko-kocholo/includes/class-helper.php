<?php
/**
 * Helper functions for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Helper {
    
    /**
     * Sanitize repeater array data
     */
    public static function sanitize_repeater_array($input) {
        if (!is_array($input)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($input as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $sanitized_item = [];
            $has_content = false;
            
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    $sanitized_item[$key] = self::sanitize_repeater_array($value);
                    if (!empty($sanitized_item[$key])) {
                        $has_content = true;
                    }
                } else {
                    $sanitized_value = sanitize_text_field($value);
                    $sanitized_item[$key] = $sanitized_value;
                    if (!empty($sanitized_value)) {
                        $has_content = true;
                    }
                }
            }
            
            if ($has_content) {
                $sanitized[] = $sanitized_item;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize integer array
     */
    public static function sanitize_int_array($input) {
        if (!is_array($input)) {
            return [];
        }
        
        return array_filter(array_map('absint', $input));
    }
    
    /**
     * Get products by category
     */
    public static function get_products_by_category($category_id) {
        if (!function_exists('wc_get_products') || empty($category_id)) {
            return [];
        }
        
        $category_ids = is_array($category_id) ? $category_id : [absint($category_id)];
        
        return wc_get_products([
            'limit' => -1,
            'status' => 'publish',
            'stock_status' => 'instock',
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_ids,
                ]
            ]
        ]);
    }
    
    /**
     * Filter in-stock products
     */
    public static function filter_in_stock($product_ids) {
        return array_values(array_filter($product_ids, function($product_id) {
            $product = wc_get_product($product_id);
            return $product && $product->is_in_stock();
        }));
    }
    
    /**
     * Get WooCommerce categories
     */
    public static function get_product_categories() {
        if (!function_exists('get_terms')) {
            return [];
        }
        
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ]);
        
        return is_wp_error($terms) ? [] : $terms;
    }
    
    /**
     * Check if option value is array (for backward compatibility)
     */
    public static function ensure_array($value) {
        if (!is_array($value) && !empty($value)) {
            return [$value];
        }
        return is_array($value) ? $value : [];
    }
    
    /**
     * Convert any image URL to optimized WebP URL if available
     * Works with direct URLs (not just attachment IDs)
     * 
     * @param string $image_url Original image URL
     * @return string Optimized WebP URL or original URL
     */
    public static function convert_to_webp_url($image_url) {
        if (empty($image_url)) {
            return '';
        }
        
        // دریافت اطلاعات پوشه آپلود
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];
        $base_dir = $upload_dir['basedir'];
        
        // بررسی اینکه URL در پوشه uploads باشد
        if (strpos($image_url, $base_url) === false) {
            return $image_url;
        }
        
        // اگر قبلاً در پوشه webp هست، برگردان
        if (strpos($image_url, '/webp/') !== false) {
            return $image_url;
        }
        
        // استخراج مسیر نسبی از URL
        $relative_path = str_replace($base_url, '', $image_url);
        
        // ساخت مسیر WebP
        $webp_relative_path = '/webp' . $relative_path;
        
        // بررسی وجود فایل WebP در سرور
        $webp_file_path = $base_dir . $webp_relative_path;
        
        if (file_exists($webp_file_path)) {
            return $base_url . $webp_relative_path;
        }
        
        // اگر فایل WebP وجود نداشت، URL اصلی را برگردان
        return $image_url;
    }

    /**
     * Get optimized WebP image URL if available, otherwise return original
     * 
     * @param int $attachment_id Image attachment ID
     * @param string $size Image size (thumbnail, medium, large, full)
     * @return string Image URL
     */
    public static function get_optimized_image_url($attachment_id, $size = 'medium') {
        if (empty($attachment_id)) {
            return '';
        }
        
        // دریافت مسیر اصلی تصویر
        $image_data = wp_get_attachment_image_src($attachment_id, $size);
        if (!$image_data) {
            return '';
        }
        
        $original_url = $image_data[0];
        
        return self::convert_to_webp_url($original_url);
    }
    
    /**
     * Get optimized product image HTML
     * 
     * @param WC_Product $product Product object
     * @param string $size Image size
     * @param array $attr Additional attributes
     * @return string Image HTML
     */
    public static function get_optimized_product_image($product, $size = 'medium', $attr = array()) {
        if (!$product) {
            return '';
        }
        
        $image_id = $product->get_image_id();
        
        if (!$image_id) {
            // اگر تصویر شاخص نداشت، تصویر پیش‌فرض WooCommerce را نمایش بده
            return $product->get_image($size, $attr);
        }
        
        // دریافت URL بهینه شده
        $image_url = self::get_optimized_image_url($image_id, $size);
        
        if (empty($image_url)) {
            return $product->get_image($size, $attr);
        }
        
        // دریافت اطلاعات تصویر
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        if (empty($image_alt)) {
            $image_alt = $product->get_name();
        }
        
        // ساخت attributes
        $default_attr = array(
            'alt' => $image_alt,
            'loading' => 'lazy',
            'onload' => 'this.classList.add("loaded")',
        );
        
        $attr = array_merge($default_attr, $attr);
        
        // دریافت ابعاد تصویر
        $image_data = wp_get_attachment_image_src($image_id, $size);
        $width = $image_data[1] ?? '';
        $height = $image_data[2] ?? '';
        
        // ساخت HTML
        $attr_string = '';
        foreach ($attr as $name => $value) {
            $attr_string .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
        }
        
        $html = sprintf(
            '<img src="%s" width="%s" height="%s"%s>',
            esc_url($image_url),
            esc_attr($width),
            esc_attr($height),
            $attr_string
        );
        
        return $html;
    }
}
