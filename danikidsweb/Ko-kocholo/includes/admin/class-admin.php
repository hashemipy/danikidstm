<?php
/**
 * Admin functionality - Enhanced with professional styling
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Admin {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_k_save_tab_data', [$this, 'ajax_save_tab_data']);
        
        add_action('wp_ajax_k_get_products_by_category', [$this, 'ajax_get_products_by_category']);
        add_action('wp_ajax_k_start_sale', [$this, 'ajax_start_sale']);
        add_action('wp_ajax_k_cancel_sale', [$this, 'ajax_cancel_sale']);
        add_action('wp_ajax_k_remove_all_discounts', [$this, 'ajax_remove_all_discounts']);
        add_action('wp_ajax_k_remove_discount', [$this, 'ajax_remove_discount']);
        
        // WooCommerce hooks for cache clearing
        add_action('woocommerce_product_set_stock_status', ['KK_Cache', 'clear_products_cache']);
        add_action('woocommerce_variation_set_stock_status', ['KK_Cache', 'clear_products_cache']);
        add_action('woocommerce_update_product', ['KK_Cache', 'clear_products_cache']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('کنترلر فروشگاه پوشاک Shop', 'khoshtip-kocholo'), // Updated brand name
            __('فروشگاه پوشاک Shop', 'khoshtip-kocholo'), // Updated brand name
            'manage_options',
            'khoshtip-kocholo-control',
            [$this, 'render_admin_page'],
            'dashicons-store',
            30
        );
    }
    
    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_khoshtip-kocholo-control') {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
        
        $this->add_inline_styles();
        $this->add_inline_scripts();
    }
    
    private function add_inline_styles() {
        $css = '
        /* Main Container */
        .kk-admin-wrap { 
            direction: rtl; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        /* Tabs */
        .kk-admin-wrap .nav-tab-wrapper { 
            margin-bottom: 25px; 
            border-bottom: 2px solid #0073aa;
            background: linear-gradient(to bottom, #f9f9f9, #fff);
        }
        .kk-admin-wrap .nav-tab { 
            font-size: 14px; 
            padding: 12px 20px;
            font-weight: 600;
            border: none;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            transition: all 0.3s ease;
        }
        .kk-admin-wrap .nav-tab:hover {
            background: #e8f4f8;
            color: #0073aa;
        }
        .kk-admin-wrap .nav-tab-active { 
            background: #0073aa;
            color: #fff;
            border-bottom: 2px solid #0073aa;
            box-shadow: 0 2px 8px rgba(0,115,170,0.2);
        }
        
        /* Settings Container */
        .kk-settings-container { 
            background: #fff; 
            padding: 30px; 
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        /* Sections */
        .settings-section { 
            margin-bottom: 40px; 
            padding-bottom: 30px; 
            border-bottom: 2px solid #f0f0f0;
        }
        .settings-section:last-child { border-bottom: none; }
        .settings-section h2 { 
            margin-top: 0; 
            color: #0073aa;
            font-size: 22px;
            font-weight: 700;
            padding-bottom: 10px;
            border-bottom: 3px solid #0073aa;
            display: inline-block;
        }
        .settings-section h3 { 
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin: 20px 0 15px;
        }
        
        /* Repeater Items */
        .k-repeater { 
            margin-bottom: 20px; 
        }
        .k-repeater-item { 
            background: linear-gradient(135deg, #f9f9f9 0%, #fff 100%);
            padding: 20px; 
            margin-bottom: 15px; 
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }
        .k-repeater-item:hover {
            border-color: #0073aa;
            box-shadow: 0 4px 12px rgba(0,115,170,0.1);
        }
        .k-repeater-item h4 { 
            margin-top: 0; 
            color: #0073aa;
            font-weight: 600;
            font-size: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0073aa;
            cursor: move;
        }
        .k-repeater-item input[type="text"],
        .k-repeater-item textarea,
        .k-repeater-item select { 
            margin-bottom: 12px;
            border-radius: 4px;
            border: 1px solid #d0d0d0;
            padding: 8px 12px;
            transition: border-color 0.3s ease;
        }
        .k-repeater-item input[type="text"]:focus,
        .k-repeater-item textarea:focus,
        .k-repeater-item select:focus {
            border-color: #0073aa;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,115,170,0.1);
        }
        
        /* Buttons */
        .button-primary {
            background: linear-gradient(135deg, #0073aa 0%, #005177 100%);
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            box-shadow: 0 2px 8px rgba(0,115,170,0.3);
            transition: all 0.3s ease;
        }
        .button-primary:hover {
            background: linear-gradient(135deg, #005177 0%, #0073aa 100%);
            box-shadow: 0 4px 12px rgba(0,115,170,0.4);
            transform: translateY(-1px);
        }
        
        .k-remove-btn { 
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: #fff;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(220,53,69,0.3);
            transition: all 0.3s ease;
        }
        .k-remove-btn:hover { 
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            box-shadow: 0 4px 10px rgba(220,53,69,0.4);
            transform: translateY(-1px);
        }
        
        /* Image Previews */
        .k-image-preview { 
            max-width: 150px; 
            max-height: 100px; 
            margin-top: 12px; 
            border-radius: 6px;
            border: 2px solid #e5e5e5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .avatar-preview { 
            max-width: 60px; 
            border-radius: 50%; 
            margin-left: 10px;
            border: 3px solid #0073aa;
            box-shadow: 0 2px 8px rgba(0,115,170,0.2);
        }
        
        /* Image Upload Group */
        .image-upload-group { 
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
            border: 1px dashed #d0d0d0;
        }
        .image-upload-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600;
            color: #333;
        }
        
        /* Auth Help Box */
        .auth-help-box { 
            background: linear-gradient(135deg, #e8f4f8 0%, #f0f8fc 100%);
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid #0073aa;
            margin: 15px 0;
            box-shadow: 0 2px 8px rgba(0,115,170,0.1);
        }
        .auth-help-box code { 
            background: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-block;
            margin: 5px 0;
            font-family: "Courier New", monospace;
            border: 1px solid #d0d0d0;
        }
        
        /* Status Indicators */
        .status-active { 
            color: #46b450;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(70,180,80,0.2);
        }
        .status-inactive { 
            color: #dc3232;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(220,50,50,0.2);
        }
        .status-warning { 
            color: #ffb900;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(255,185,0,0.2);
        }
        
        /* Story and Hero Items */
        .story-item, .hero-item { 
            position: relative; 
        }
        .story-media-item { 
            display: flex; 
            gap: 12px; 
            margin-bottom: 12px; 
            align-items: center; 
            flex-wrap: wrap;
            padding: 12px;
            background: #fff;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
        }
        .story-media-item select { width: 100px; }
        .story-media-item input { flex: 1; min-width: 150px; }
        
        /* Sales Controls */
        .sale-controls { 
            display: flex; 
            gap: 20px; 
            flex-wrap: wrap; 
            align-items: flex-end; 
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #f9f9f9 0%, #fff 100%);
            border-radius: 8px;
            border: 2px solid #e5e5e5;
        }
        .sale-controls .control-group { 
            display: flex; 
            flex-direction: column; 
        }
        .sale-controls label { 
            margin-bottom: 8px; 
            font-weight: 600;
            color: #333;
        }
        
        /* Products List */
        .products-list { 
            max-height: 450px; 
            overflow-y: auto; 
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            background: #fff;
        }
        .product-row { 
            display: flex; 
            align-items: center; 
            padding: 12px 20px; 
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
        }
        .product-row:last-child { border-bottom: none; }
        .product-row:hover { 
            background: linear-gradient(90deg, #e8f4f8 0%, #f0f8fc 100%);
        }
        .product-row input[type="checkbox"] { margin-left: 12px; }
        .product-row .product-name { flex: 1; font-weight: 500; }
        .product-row .product-price { color: #666; font-size: 13px; }
        .product-row .has-discount { 
            color: #46b450; 
            font-size: 12px; 
            margin-right: 10px;
            font-weight: 600;
        }
        
        /* Notices */
        .k-notice { 
            padding: 15px 20px; 
            border-radius: 6px; 
            margin-bottom: 20px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .k-notice-success { 
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            color: #155724;
        }
        .k-notice-error { 
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .k-notice-warning { 
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            border: 2px solid #ffc107;
            color: #856404;
        }
        
        /* Extra Accordion Items */
        .extra-accordion-item { 
            background: #fff;
            border: 3px solid #9c27b0;
            padding: 25px; 
            margin-bottom: 25px; 
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(156,39,176,0.15);
        }
        .extra-accordion-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #9c27b0;
        }
        .extra-accordion-header h3 { 
            margin: 0; 
            color: #9c27b0;
            font-size: 20px;
            font-weight: 700;
        }
        
        /* Color Picker Enhancements */
        .wp-picker-holder { 
            position: absolute;
            z-index: 100000;
        }
        .wp-color-result { 
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        
        /* Select2 Styling */
        .select2-container { width: 100% !important; }
        .select2-container--default .select2-selection--multiple {
            border-radius: 6px;
            border: 2px solid #d0d0d0;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #0073aa;
        }
        
        /* Product Checkbox Labels */
        .product-checkbox-label {
            display: block;
            padding: 10px 15px;
            margin-bottom: 6px;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e5e5e5;
        }
        .product-checkbox-label:hover {
            background: linear-gradient(90deg, #e8f4f8 0%, #f0f8fc 100%);
            border-color: #0073aa;
        }
        
        /* Loading States */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .loading { animation: pulse 1.5s ease-in-out infinite; }
        
        /* Responsive */
        @media (max-width: 782px) {
            .kk-settings-container { padding: 15px; }
            .k-repeater-item { padding: 15px; }
            .sale-controls { flex-direction: column; align-items: stretch; }
        }
        ';
        
        wp_add_inline_style('wp-admin', $css);
    }
    
    private function add_inline_scripts() {
        $js = "
        jQuery(document).ready(function($) {
            function updateAccordionHeader(input) {
                var section = $(input).closest('.accordion-section');
                var sectionKey = section.data('section');
                if (!sectionKey) return;
                
                var startColor = $('input[name=\"k_' + sectionKey + '_gradient_start\"]').val();
                var endColor = $('input[name=\"k_' + sectionKey + '_gradient_end\"]').val();
                
                section.find('h4').css('background', 'linear-gradient(135deg, ' + startColor + ', ' + endColor + ')');
            }
            
            // Initialize color pickers
            function initColorPickers() {
                $('.k-color-picker').each(function() {
                    if (!$(this).hasClass('wp-color-picker')) {
                        var self = this;
                        $(this).wpColorPicker({
                            change: function(event, ui) {
                                setTimeout(function() {
                                    updateAccordionHeader(self);
                                }, 10);
                            }
                        });
                    }
                });
            }
            
            initColorPickers();
            
            // Re-initialize on tab change
            $('.nav-tab').on('click', function() {
                setTimeout(initColorPickers, 200);
            });
            
            // Initialize Select2
            $('.k-select2').select2({
                dir: 'rtl',
                width: '100%',
                allowClear: true,
                placeholder: 'انتخاب کنید...'
            });
            
            // Product Search with Select2
            $('.k-product-search').select2({
                ajax: {
                    url: ajaxurl,
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return { 
                            action: 'k_search_products', 
                            q: params.term, 
                            page: params.page || 1 
                        };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    },
                    cache: true
                },
                dir: 'rtl',
                width: '100%',
                minimumInputLength: 2,
                placeholder: 'جستجوی محصول (حداقل 2 حرف)...',
                language: {
                    inputTooShort: function() { return 'حداقل 2 حرف تایپ کنید...'; },
                    searching: function() { return 'در حال جستجو...'; },
                    noResults: function() { return 'محصولی یافت نشد'; }
                }
            });
            
            $(document).on('click', '.upload-btn, .upload-hero-bg, .upload-hero-mobile, .upload-avatar, .upload-icon-btn, .upload-icon, .upload-custom-btn-icon', function(e) {
                e.preventDefault();
                var button = $(this);
                var inputField = button.prev('input[type=\"text\"], input[type=\"hidden\"]');
                if (inputField.length === 0) {
                    inputField = button.siblings('input[type=\"text\"], input[type=\"hidden\"]').first();
                }
                var targetId = button.data('target');
                if (targetId) {
                    inputField = $('#' + targetId);
                }
                
                var frameTitle = 'انتخاب تصویر';
                var libraryType = 'image';
                
                if (button.hasClass('upload-story-media')) {
                    frameTitle = 'انتخاب رسانه (تصویر یا ویدیو)';
                    libraryType = ['image', 'video'];
                }
                
                var frame = wp.media({
                    title: frameTitle,
                    button: { text: 'استفاده از این تصویر' },
                    library: { type: libraryType },
                    multiple: false
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    inputField.val(attachment.url);
                    
                    // Show preview
                    var previewContainer = button.parent();
                    previewContainer.find('img.k-image-preview').remove();
                    
                    if (button.hasClass('upload-avatar') || button.hasClass('upload-icon-btn') || button.hasClass('upload-icon')) {
                        previewContainer.append('<img src=\"' + attachment.url + '\" class=\"k-image-preview avatar-preview\">');
                    } else {
                        previewContainer.append('<img src=\"' + attachment.url + '\" class=\"k-image-preview\">');
                    }
                });
                
                frame.open();
            });
            
            window.uploadStoryMedia = function(storyIndex, mediaIndex) {
                var frame = wp.media({
                    title: 'انتخاب رسانه (تصویر یا ویدیو)',
                    button: { text: 'انتخاب' },
                    library: { type: ['image', 'video'] },
                    multiple: false
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var urlInput = $('input[name=\"k_stories[' + storyIndex + '][media][' + mediaIndex + '][url]\"]');
                    var typeSelect = $('select[name=\"k_stories[' + storyIndex + '][media][' + mediaIndex + '][type]\"]');
                    var previewDiv = $('.media-preview-' + storyIndex + '-' + mediaIndex);
                    
                    urlInput.val(attachment.url);
                    
                    if (attachment.type === 'video') {
                        typeSelect.val('video');
                        previewDiv.html('<video src=\"' + attachment.url + '\" style=\"max-width:100px;max-height:100px;border-radius:4px;\"></video>');
                    } else {
                        typeSelect.val('image');
                        previewDiv.html('<img src=\"' + attachment.url + '\" style=\"max-width:100px;max-height:100px;object-fit:cover;border-radius:4px;\">');
                    }
                });
                
                frame.open();
            };
            
            window.uploadStoryImage = function(index, type) {
                var frame = wp.media({
                    title: 'انتخاب تصویر',
                    button: { text: 'انتخاب' },
                    library: { type: 'image' },
                    multiple: false
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('.story-' + type + '-' + index).val(attachment.url);
                    var preview = $('.story-' + type + '-preview-' + index);
                    preview.attr('src', attachment.url).css('display', 'block');
                });
                
                frame.open();
            };
            
            window.addStoryMedia = function(storyIndex) {
                var container = $('#story-media-list-' + storyIndex);
                var mediaIndex = container.find('.story-media-item').length;
                
                var html = '<div class=\"story-media-item\">' +
                    '<select name=\"k_stories[' + storyIndex + '][media][' + mediaIndex + '][type]\" style=\"width:100px;\">' +
                    '<option value=\"image\">تصویر</option>' +
                    '<option value=\"video\">ویدیو</option>' +
                    '</select>' +
                    '<input type=\"text\" name=\"k_stories[' + storyIndex + '][media][' + mediaIndex + '][url]\" placeholder=\"آدرس رسانه\" style=\"flex:1;\">' +
                    '<button type=\"button\" class=\"button\" onclick=\"uploadStoryMedia(' + storyIndex + ', ' + mediaIndex + ')\">انتخاب رسانه</button>' +
                    '<div class=\"media-preview-' + storyIndex + '-' + mediaIndex + '\" style=\"min-width:100px;\"></div>' +
                    '<input type=\"text\" name=\"k_stories[' + storyIndex + '][media][' + mediaIndex + '][link]\" placeholder=\"لینک (اختیاری)\" style=\"flex:1;margin-top:5px;\">' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف</button>' +
                    '</div>';
                container.append(html);
                mediaIndex++;
            };
            
            var navLinkIndex = $('#nav-links-repeater .k-repeater-item').length;
            $(document).on('click', '#add-nav-link', function() {
                var html = '<div class=\"k-repeater-item\">' +
                    '<input type=\"text\" name=\"k_nav_links[' + navLinkIndex + '][text]\" placeholder=\"متن لینک\" style=\"width:30%;\">' +
                    '<input type=\"text\" name=\"k_nav_links[' + navLinkIndex + '][url]\" placeholder=\"آدرس URL\" style=\"width:50%;\">' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف</button>' +
                    '</div>';
                $('#nav-links-repeater').append(html);
                navLinkIndex++;
            });
            
            var mobileMenuIndex = $('#mobile-menu-links-repeater .k-repeater-item').length;
            $(document).on('click', '#add-mobile-menu-link', function() {
                var html = '<div class=\"k-repeater-item\">' +
                    '<input type=\"text\" name=\"k_mobile_menu_links[' + mobileMenuIndex + '][text]\" placeholder=\"متن لینک\" style=\"width:30%;\">' +
                    '<input type=\"text\" name=\"k_mobile_menu_links[' + mobileMenuIndex + '][url]\" placeholder=\"آدرس URL\" style=\"width:50%;\">' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف</button>' +
                    '</div>';
                $('#mobile-menu-links-repeater').append(html);
                mobileMenuIndex++;
            });
            
            var heroIndex = $('#hero-repeater .k-repeater-item').length;
            $(document).on('click', '#add-hero-slide', function() {
                var html = '<div class=\"k-repeater-item hero-item\">' +
                    '<h4>اسلاید ' + (heroIndex + 1) + '</h4>' +
                    '<div class=\"image-upload-group\"><label>تصویر دسکتاپ:</label>' +
                    '<input type=\"hidden\" name=\"k_hero_slides[' + heroIndex + '][bg_image]\" class=\"hero-bg-input\">' +
                    '<button type=\"button\" class=\"button upload-hero-bg\">انتخاب تصویر</button></div>' +
                    '<div class=\"image-upload-group\"><label>تصویر موبایل:</label>' +
                    '<input type=\"hidden\" name=\"k_hero_slides[' + heroIndex + '][mobile_image]\" class=\"hero-mobile-input\">' +
                    '<button type=\"button\" class=\"button upload-hero-mobile\">انتخاب تصویر</button></div>' +
                    '<input type=\"text\" name=\"k_hero_slides[' + heroIndex + '][title]\" placeholder=\"عنوان\" class=\"regular-text\">' +
                    '<textarea name=\"k_hero_slides[' + heroIndex + '][description]\" placeholder=\"توضیحات\" rows=\"3\" class=\"large-text\"></textarea>' +
                    '<input type=\"text\" name=\"k_hero_slides[' + heroIndex + '][button_text]\" placeholder=\"متن دکمه\" class=\"regular-text\">' +
                    '<input type=\"text\" name=\"k_hero_slides[' + heroIndex + '][button_url]\" placeholder=\"لینک دکمه\" class=\"regular-text\">' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف اسلاید</button>' +
                    '</div>';
                $('#hero-repeater').append(html);
                heroIndex++;
            });
            
            var storyIndex = $('#stories-repeater .k-repeater-item').length;
            $(document).on('click', '#add-story', function() {
                var html = '<div class=\"k-repeater-item story-item\">' +
                    '<h4>استوری ' + (storyIndex + 1) + '</h4>' +
                    '<label>نام کاربری:</label>' +
                    '<input type=\"text\" name=\"k_stories[' + storyIndex + '][username]\" placeholder=\"نام کاربری\" class=\"regular-text\">' +
                    '<label>آواتار:</label>' +
                    '<div style=\"display:flex;gap:10px;align-items:center;margin-bottom:15px;\">' +
                    '<input type=\"text\" name=\"k_stories[' + storyIndex + '][avatar]\" class=\"story-avatar-' + storyIndex + ' regular-text\" placeholder=\"آدرس آواتار\" style=\"flex:1;\">' +
                    '<button type=\"button\" class=\"button upload-btn\" onclick=\"uploadStoryImage(' + storyIndex + ', \'avatar\')\">انتخاب آواتار</button>' +
                    '<img src=\"/placeholder.svg\" class=\"k-image-preview story-avatar-preview-' + storyIndex + '\" style=\"display:none;max-width:80px;border-radius:50%;\">' +
                    '</div>' +
                    '<label>رسانه‌های استوری:</label>' +
                    '<div class=\"story-media-list\" id=\"story-media-list-' + storyIndex + '\"></div>' +
                    '<button type=\"button\" class=\"button\" onclick=\"addStoryMedia(' + storyIndex + ')\">افزودن رسانه</button>' +
                    '<label style=\"margin-top:15px;display:block;\">لینک دکمه:</label>' +
                    '<input type=\"text\" name=\"k_stories[' + storyIndex + '][button_url]\" placeholder=\"لینک دکمه\" class=\"regular-text\">' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف استوری</button>' +
                    '</div>';
                $('#stories-repeater').append(html);
                storyIndex++;
            });
            
            window.addUsefulLink = function() {
                var container = $('#useful-links-repeater');
                var index = container.find('.k-repeater-item').length;
                var html = '<div class=\"k-repeater-item\">' +
                    '<input type=\"text\" name=\"k_footer_useful_links[' + index + '][text]\" placeholder=\"متن لینک\" style=\"width:30%;\">' +
                    '<input type=\"text\" name=\"k_footer_useful_links[' + index + '][url]\" placeholder=\"آدرس URL\" style=\"width:50%;\">' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف</button>' +
                    '</div>';
                container.append(html);
            };
            
            window.addCustomerServiceLink = function() {
                var container = $('#customer-service-links-repeater');
                var index = container.find('.k-repeater-item').length;
                var html = '<div class=\"k-repeater-item\">' +
                    '<input type=\"text\" name=\"k_footer_customer_service_links[' + index + '][text]\" placeholder=\"متن لینک\" style=\"width:30%;\">' +
                    '<input type=\"text\" name=\"k_footer_customer_service_links[' + index + '][url]\" placeholder=\"آدرس URL\" style=\"width:50%;\">' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف</button>' +
                    '</div>';
                container.append(html);
            };
            
            window.addCustomButton = function() {
                var container = $('#custom-buttons-repeater');
                var index = container.find('.k-repeater-item').length;
                var html = '<div class=\"k-repeater-item\">' +
                    '<input type=\"text\" name=\"k_footer_custom_buttons[' + index + '][text]\" placeholder=\"متن دکمه\" style=\"width:20%;\">' +
                    '<input type=\"text\" name=\"k_footer_custom_buttons[' + index + '][url]\" placeholder=\"لینک\" style=\"width:30%;\">' +
                    '<input type=\"hidden\" name=\"k_footer_custom_buttons[' + index + '][icon]\" class=\"custom-btn-icon-' + index + '\">' +
                    '<button type=\"button\" class=\"button upload-custom-btn-icon\" data-target=\"custom-btn-icon-' + index + '\">انتخاب آیکون</button>' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف</button>' +
                    '</div>';
                container.append(html);
                
                // Re-initialize color pickers
                setTimeout(initColorPickers, 100);
            };
            
            // Add event listener for adding new accordion
            $(document).on('click', '#add-extra-accordion', function(e) {
                e.preventDefault();
                addExtraAccordion();
            });
            
            // Improve getCategoryOptions function to fetch category options
            function getCategoryOptions() {
                var options = '';
                // First, get categories from the girl category section
                var selectEl = $('select[name=\"k_girls_category[]\"]');
                if (selectEl.length > 0) {
                    selectEl.find('option').each(function() {
                        if ($(this).val()) {
                            options += '<option value=\"' + $(this).val() + '\">' + $(this).text() + '</option>';
                        }
                    });
                }
                return options || '<option value=\"\">بدون دسته‌بندی</option>';
            }
            
            window.addExtraAccordion = function() {
                var container = $('#extra-accordions-container');
                var index = container.find('.k-repeater-item').length;
                var id = 'extra_' + Date.now();
                var categoryOptions = getCategoryOptions();
                
                var html = '<div class=\"extra-accordion-item k-repeater-item\" data-id=\"' + id + '\">' +
                    '<h4>بخش جدید آکاردئونی</h4>' +
                    '<table class=\"form-table\">' +
                    '<tr><th>عنوان</th><td><input type=\"text\" name=\"k_extra_accordions[' + id + '][title]\" class=\"regular-text\" placeholder=\"عنوان بخش\"></td></tr>' +
                    '<tr><th>دسته‌بندی</th><td><select name=\"k_extra_accordions[' + id + '][category][]\" multiple size=\"5\" style=\"min-width:300px;\">' + categoryOptions + '</select></td></tr>' +
                    '<tr><th>رنگ شروع گرادینت</th><td><input type=\"text\" name=\"k_extra_accordions[' + id + '][gradient_start]\" class=\"k-color-picker\" value=\"#667eea\"></td></tr>' +
                    '<tr><th>رنگ پایان گرادینت</th><td><input type=\"text\" name=\"k_extra_accordions[' + id + '][gradient_end]\" class=\"k-color-picker\" value=\"#764ba2\"></td></tr>' +
                    '<tr><th>رنگ دکمه</th><td><input type=\"text\" name=\"k_extra_accordions[' + id + '][button_bg]\" class=\"k-color-picker\" value=\"#667eea\"></td></tr>' +
                    '<tr><th>رنگ متن دکمه</th><td><input type=\"text\" name=\"k_extra_accordions[' + id + '][button_text]\" class=\"k-color-picker\" value=\"#ffffff\"></td></tr>' +
                    '<tr><th>آیکون</th><td><input type=\"text\" name=\"k_extra_accordions[' + id + '][icon]\" class=\"regular-text\" placeholder=\"ایموجی یا URL تصویر\"><button type=\"button\" class=\"button upload-icon\">انتخاب تصویر</button></td></tr>' +
                    '<tr><th>نمایش</th><td><select name=\"k_extra_accordions[' + id + '][layout]\"><option value=\"scroll\">اسکرول افقی</option><option value=\"grid\">گرید</option></select></td></tr>' +
                    '<tr><th>باز بودن پیش‌فرض</th><td><label><input type=\"checkbox\" name=\"k_extra_accordions[' + id + '][default_open]\" value=\"1\"> بله</label></td></tr>' +
                    '</table>' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف این بخش</button>' +
                    '</div>';
                container.append(html);
                
                // Re-initialize color pickers for new elements
                setTimeout(function() {
                    container.find('.k-color-picker').not('.wp-color-picker').each(function() {
                        $(this).wpColorPicker({
                            change: function() {}
                        });
                    });
                }, 100);
            };
            
            window.addBottomBarButton = function() {
                var container = $('#bottom-bar-buttons-repeater');
                var index = container.find('.k-repeater-item').length;
                if (index >= 6) {
                    alert('حداکثر 6 دکمه می‌توانید اضافه کنید.');
                    return;
                }
                var html = '<div class=\"k-repeater-item\">' +
                    '<input type=\"text\" name=\"k_bottom_bar_buttons[' + index + '][label]\" placeholder=\"عنوان دکمه\" style=\"width:20%;\">' +
                    '<input type=\"text\" name=\"k_bottom_bar_buttons[' + index + '][url]\" placeholder=\"لینک\" style=\"width:30%;\">' +
                    '<input type=\"hidden\" name=\"k_bottom_bar_buttons[' + index + '][image]\" class=\"bottom-bar-image-' + index + '\">' +
                    '<button type=\"button\" class=\"button upload-btn\" onclick=\"uploadBottomBarImage(' + index + ')\">انتخاب تصویر/آیکون</button>' +
                    '<img src=\"/placeholder.svg\" class=\"k-image-preview bottom-bar-image-preview-' + index + '\" style=\"display:none;width:40px;height:40px;object-fit:contain;\">' +
                    '<button type=\"button\" class=\"k-remove-btn\" onclick=\"this.parentElement.remove()\">حذف</button>' +
                    '</div>';
                container.append(html);
            };
            
            window.uploadBottomBarImage = function(index) {
                var frame = wp.media({
                    title: 'انتخاب تصویر/آیکون',
                    button: { text: 'انتخاب' },
                    library: { type: 'image' },
                    multiple: false
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('.bottom-bar-image-' + index).val(attachment.url);
                    var preview = $('.bottom-bar-image-preview-' + index);
                    preview.attr('src', attachment.url).css('display', 'inline-block');
                });
                
                frame.open();
            };
            
            // Make repeaters sortable
            $('.k-repeater').sortable({
                items: '.k-repeater-item',
                handle: 'h4',
                cursor: 'move',
                opacity: 0.7,
                placeholder: 'sortable-placeholder'
            });
            
            $('#k-settings-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitBtn = form.find(':submit');
                var originalText = submitBtn.val();
                
                submitBtn.val('در حال ذخیره...').prop('disabled', true).addClass('loading');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            showNotice('success', response.data.message || 'تغییرات با موفقیت ذخیره شد!');
                        } else {
                            showNotice('error', response.data.message || 'خطا در ذخیره‌سازی');
                        }
                    },
                    error: function() {
                        showNotice('error', 'خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.');
                    },
                    complete: function() {
                        submitBtn.val(originalText).prop('disabled', false).removeClass('loading');
                    }
                });
            });
            
            function showNotice(type, message) {
                var notice = $('<div class=\"k-notice k-notice-' + type + '\">' + message + '</div>');
                $('.kk-settings-container').prepend(notice);
                setTimeout(function() { 
                    notice.fadeOut(function() { 
                        $(this).remove(); 
                    }); 
                }, 4000);
            }
        });
        ";
        
        wp_add_inline_script('jquery', $js);
    }
    
    public function render_admin_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'header';
        $tabs = KK_Admin_Tabs::instance();
        ?>
        <div class="wrap kk-admin-wrap">
            <h1><?php echo esc_html__('پنل کنترل فروشگاه پوشاک Shop', 'khoshtip-kocholo'); ?></h1> <?php // Updated brand name ?>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=khoshtip-kocholo-control&tab=auth" class="nav-tab <?php echo $active_tab === 'auth' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('احراز هویت', 'khoshtip-kocholo'); ?>
                </a>
                <a href="?page=khoshtip-kocholo-control&tab=header" class="nav-tab <?php echo $active_tab === 'header' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('هدر', 'khoshtip-kocholo'); ?>
                </a>
                <a href="?page=khoshtip-kocholo-control&tab=stories" class="nav-tab <?php echo $active_tab === 'stories' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('استوری‌ها', 'khoshtip-kocholo'); ?>
                </a>
                <a href="?page=khoshtip-kocholo-control&tab=hero" class="nav-tab <?php echo $active_tab === 'hero' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('بنر اصلی', 'khoshtip-kocholo'); ?>
                </a>
                <a href="?page=khoshtip-kocholo-control&tab=sales" class="nav-tab <?php echo $active_tab === 'sales' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('حراج', 'khoshtip-kocholo'); ?>
                </a>
                <a href="?page=khoshtip-kocholo-control&tab=latest" class="nav-tab <?php echo $active_tab === 'latest' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('جدیدترین', 'khoshtip-kocholo'); ?>
                </a>
                <a href="?page=khoshtip-kocholo-control&tab=accordion" class="nav-tab <?php echo $active_tab === 'accordion' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('دکمه‌های محصول', 'khoshtip-kocholo'); ?>
                </a>
                <a href="?page=khoshtip-kocholo-control&tab=blog" class="nav-tab <?php echo $active_tab === 'blog' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('بلاگ', 'khoshtip-kocholo'); ?>
                </a>
                <a href="?page=khoshtip-kocholo-control&tab=footer" class="nav-tab <?php echo $active_tab === 'footer' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('فوتر', 'khoshtip-kocholo'); ?>
                </a>
            </h2>
            
            <form method="post" id="k-settings-form">
                <?php wp_nonce_field('k_save_nonce', 'nonce'); ?>
                <input type="hidden" name="action" value="k_save_tab_data" />
                <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>" />
                
                <div class="kk-settings-container">
                    <?php $tabs->render_tab($active_tab); ?>
                </div>
                
                <?php submit_button(__('💾 ذخیره تغییرات', 'khoshtip-kocholo'), 'primary large', 'submit', true, ['style' => 'font-size: 16px; padding: 12px 30px;']); ?>
            </form>
        </div>
        <?php
    }
    
    public function ajax_save_tab_data() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('شما دسترسی لازم را ندارید.', 'khoshtip-kocholo')]);
        }
        
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : '';
        
        if (empty($tab)) {
            wp_send_json_error(['message' => __('تب مشخص نشده است.', 'khoshtip-kocholo')]);
        }
        
        $tabs = KK_Admin_Tabs::instance();
        $tabs->save_tab_data($tab, $_POST);
        
        wp_send_json_success(['message' => __('✅ تغییرات با موفقیت ذخیره شد!', 'khoshtip-kocholo')]);
    }
    
    public function ajax_get_products_by_category() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما دسترسی لازم را ندارید.']);
        }
        
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        if ($category_id > 0) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_id
                )
            );
        }
        
        $query = new WP_Query($args);
        $products = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                
                if ($product) {
                    $regular_price = $product->get_regular_price();
                    $sale_price = $product->get_sale_price();
                    $has_discount = !empty($sale_price);
                    $price_display = '';
                    
                    if ($product->get_type() === 'variable') {
                        $min_price = $product->get_variation_regular_price('min', true);
                        $max_price = $product->get_variation_regular_price('max', true);
                        $price_display = wc_format_price_range($min_price, $max_price);
                    } else {
                        $price_display = wc_price($regular_price);
                    }
                    
                    $products[] = array(
                        'id' => $product_id,
                        'name' => $product->get_name(),
                        'regular_price' => $price_display,
                        'has_discount' => $has_discount,
                        'type' => $product->get_type()
                    );
                }
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success(array('products' => $products));
    }
    
    public function ajax_start_sale() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما دسترسی لازم را ندارید.']);
        }
        
        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : array();
        $discount_percent = isset($_POST['discount_percent']) ? intval($_POST['discount_percent']) : 0;
        $sale_hours = isset($_POST['sale_hours']) ? intval($_POST['sale_hours']) : 0;
        $sale_minutes = isset($_POST['sale_minutes']) ? intval($_POST['sale_minutes']) : 0;
        
        if (empty($product_ids) || $discount_percent < 1 || $discount_percent > 99) {
            wp_send_json_error(['message' => 'اطلاعات ورودی نامعتبر است.']);
        }
        
        // Calculate sale end time
        $total_minutes = ($sale_hours * 60) + $sale_minutes;
        $sale_end_timestamp = time() + ($total_minutes * 60);
        
        // Save timer settings
        update_option('k_sale_hours', $sale_hours);
        update_option('k_sale_minutes', $sale_minutes);
        update_option('k_sale_end_timestamp', $sale_end_timestamp);
        
        // Apply discounts
        $discounted_products = array();
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            
            if ($product) {
                if ($product->get_type() === 'variable') {
                    // Handle variable products
                    $variations = $product->get_children();
                    foreach ($variations as $variation_id) {
                        $variation = wc_get_product($variation_id);
                        if ($variation) {
                            $regular_price = $variation->get_regular_price();
                            if ($regular_price) {
                                $sale_price = $regular_price - ($regular_price * $discount_percent / 100);
                                $variation->set_sale_price($sale_price);
                                $variation->save();
                            }
                        }
                    }
                } else {
                    // Handle simple products
                    $regular_price = $product->get_regular_price();
                    if ($regular_price) {
                        $sale_price = $regular_price - ($regular_price * $discount_percent / 100);
                        $product->set_sale_price($sale_price);
                        $product->save();
                    }
                }
                $discounted_products[] = $product_id;
            }
        }
        
        // Save list of discounted products
        update_option('k_discounted_products', $discounted_products);
        
        wp_send_json_success([
            'message' => 'حراجی با موفقیت شروع شد و تخفیف‌ها اعمال گردید.'
        ]);
    }
    
    public function ajax_cancel_sale() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما دسترسی لازم را ندارید.']);
        }
        
        // Remove all discounts
        $discounted_products = get_option('k_discounted_products', array());
        
        foreach ($discounted_products as $product_id) {
            $product = wc_get_product($product_id);
            
            if ($product) {
                if ($product->get_type() === 'variable') {
                    $variations = $product->get_children();
                    foreach ($variations as $variation_id) {
                        $variation = wc_get_product($variation_id);
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
        }
        
        // Clear timer and discounted products list
        delete_option('k_sale_end_timestamp');
        delete_option('k_discounted_products');
        
        wp_send_json_success([
            'message' => 'حراجی لغو شد و تمام تخفیف‌ها حذف گردید.'
        ]);
    }
    
    public function ajax_remove_all_discounts() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما دسترسی لازم را ندارید.']);
        }
        
        $discounted_products = get_option('k_discounted_products', array());
        
        foreach ($discounted_products as $product_id) {
            $product = wc_get_product($product_id);
            
            if ($product) {
                if ($product->get_type() === 'variable') {
                    $variations = $product->get_children();
                    foreach ($variations as $variation_id) {
                        $variation = wc_get_product($variation_id);
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
        }
        
        delete_option('k_discounted_products');
        
        $tabs = KK_Admin_Tabs::instance();
        $discounted_products_list = $tabs->get_discounted_products_list();
        
        wp_send_json_success([
            'message' => 'تمام تخفیف‌ها حذف شدند.',
            'discounted_products' => $discounted_products_list
        ]);
    }
    
    public function ajax_remove_discount() {
        check_ajax_referer('k_save_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما دسترسی لازم را ندارید.']);
        }
        
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (!$product_id) {
            wp_send_json_error(['message' => 'شناسه محصول نامعتبر است.']);
        }
        
        $product = wc_get_product($product_id);
        
        if ($product) {
            if ($product->get_type() === 'variable') {
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation = wc_get_product($variation_id);
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
            $discounted_products = get_option('k_discounted_products', array());
            $discounted_products = array_diff($discounted_products, array($product_id));
            update_option('k_discounted_products', $discounted_products);
            
            $tabs = KK_Admin_Tabs::instance();
            $discounted_products_list = $tabs->get_discounted_products_list();
            
            wp_send_json_success([
                'message' => 'تخفیف محصول حذف شد.',
                'discounted_products' => $discounted_products_list
            ]);
        } else {
            wp_send_json_error(['message' => 'محصول یافت نشد.']);
        }
    }
}
