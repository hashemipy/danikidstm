<?php
/*
Plugin Name: بارگذاری سریع
Description: افزونه‌ای برای ایجاد محصولات متغیر با ویژگی‌های دلخواه، انتخاب گزینه‌های تعداد با جداکننده نقطه، حداقل تعداد خرید، اختصاص دسته‌بندی به محصول، ارسال به تلگرام و بهینه‌سازی سئو.
Version: 2.1.0
Author: نام شما
*/

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

/**
 * تابع بررسی دامنه مجاز افزونه
 */
function bs_protected_plugin_check_domain() {
    $allowed_domain = 'danikids.ir';
    $current_domain = parse_url(get_site_url(), PHP_URL_HOST);
    if (strtolower($current_domain) !== strtolower($allowed_domain)) {
        add_action('admin_notices', function () use ($allowed_domain, $current_domain) {
            echo '<div class="error"><p>این افزونه تنها برای دامنه <strong>' . esc_html($allowed_domain) . '</strong> مجاز است. دامنه فعلی شما: <strong>' . esc_html($current_domain) . '</strong>.</p></div>';
        });
        return false;
    }
    return true;
}

if (!bs_protected_plugin_check_domain()) {
    return;
}

// بررسی فعال بودن ووکامرس
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'spf_woocommerce_inactive_notice');
    return;
}

function spf_woocommerce_inactive_notice() {
    echo '<div class="error"><p>افزونه بارگذاری سریع نیازمند فعال بودن ووکامرس است.</p></div>';
}

// ایجاد منوی افزونه و تنظیمات
function spf_create_menu_page() {
    add_menu_page(
        'افزودن محصول سفارشی',
        'بارگذاری سریع',
        'manage_options',
        'spf-custom-product',
        'spf_display_product_form',
        'dashicons-upload',
        56
    );

    add_submenu_page(
        'spf-custom-product',
        'تنظیمات بارگذاری سریع',
        'تنظیمات',
        'manage_options',
        'spf-settings',
        'spf_display_settings_page'
    );

    // اضافه کردن زیرمنوی جدید برای اصلاح محصولات
    add_submenu_page(
        'spf-custom-product',
        'اصلاح محصولات',
        'اصلاح محصولات',
        'manage_options',
        'spf-edit-products',
        'spf_display_edit_products_page'
    );
}
add_action('admin_menu', 'spf_create_menu_page');

function spf_register_settings() {
    register_setting('spf_settings_group', 'spf_telegram_bot_token');
    register_setting('spf_settings_group', 'spf_telegram_chat_ids');
    register_setting('spf_settings_group', 'spf_admin_id');
    register_setting('spf_settings_group', 'spf_instagram_link');

    add_settings_section('spf_settings_section', '', null, 'spf-settings');

    add_settings_field(
        'spf_telegram_bot_token',
        'توکن ربات تلگرام:',
        'spf_telegram_bot_token_callback',
        'spf-settings',
        'spf_settings_section'
    );

    add_settings_field(
        'spf_telegram_chat_ids',
        'Telegram Channel IDs:',
        'spf_telegram_chat_ids_callback',
        'spf-settings',
        'spf_settings_section'
    );

    add_settings_field(
        'spf_admin_id',
        'Admin IDs:',
        'spf_admin_id_callback',
        'spf-settings',
        'spf_settings_section'
    );

    add_settings_field(
        'spf_instagram_link',
        'لینک اینستاگرام:',
        'spf_instagram_link_callback',
        'spf-settings',
        'spf_settings_section'
    );
}
add_action('admin_init', 'spf_register_settings');

function spf_telegram_bot_token_callback() {
    $value = esc_attr(get_option('spf_telegram_bot_token'));
    echo '<input type="text" name="spf_telegram_bot_token" value="' . $value . '" style="width:400px;" />';
}

function spf_telegram_chat_ids_callback() {
    $value = esc_attr(get_option('spf_telegram_chat_ids'));
    echo '<input type="text" name="spf_telegram_chat_ids" value="' . $value . '" style="width:400px; direction:ltr;" />';
    echo '<p>مثال: @channel1 @channel2 (با فاصله از هم جدا کنید)<br/>
          توجه: برای کانال‌های خصوصی، به جای نام کاربری، شناسه عددی (chat_id) مانند <code>-1001234567890</code> را وارد کنید.</p>';
}

function spf_admin_id_callback() {
    $value = esc_attr(get_option('spf_admin_id'));
    echo '<input type="text" name="spf_admin_id" value="' . $value . '" style="width:400px; direction:ltr;" />';
    echo '<p>مثال: @youradminid (چند آی‌دی با فاصله جدا شوند)</p>';
}

function spf_instagram_link_callback() {
    $value = esc_attr(get_option('spf_instagram_link'));
    echo '<input type="text" name="spf_instagram_link" value="' . $value . '" style="width:400px; direction:ltr;" />';
}

function spf_display_settings_page() {
    ?>
    <div class="wrap">
        <h1>تنظیمات بارگذاری سریع</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('spf_settings_group');
            do_settings_sections('spf-settings');
            submit_button();
            ?>
        </form>
        <br/>
        <button id="spf_help_button" class="button">راهنمای تنظیمات</button>
        <div id="spf_help_text" style="display: none; margin-top: 20px; border: 1px solid #ddd; padding: 10px;">
            <h2>راهنمای ساخت ربات تلگرام</h2>
            <ol>
                <li>به ربات <strong>@BotFather</strong> در تلگرام مراجعه کنید.</li>
                <li>دستور <code>/newbot</code> را ارسال کنید و نام و یوزرنیم دلخواه برای ربات خود انتخاب کنید.</li>
                <li>پس از ایجاد ربات، توکن دریافت خواهید کرد. این توکن را در قسمت "توکن ربات تلگرام" وارد کنید.</li>
            </ol>
            <h2>نحوه گرفتن توکن</h2>
            <p>توکن به شما از طریق ربات <strong>@BotFather</strong> ارائه می‌شود. پس از ایجاد ربات، پیام حاوی توکن به شما ارسال می‌شود.</p>
            <h2>ادمین کردن ربات در کانال</h2>
            <ol>
                <li>کانال یا گروه مورد نظر را در تلگرام باز کنید.</li>
                <li>به تنظیمات کانال یا گروه بروید و گزینه مدیریت اعضا یا Admins را انتخاب کنید.</li>
                <li>ربات خود را به عنوان ادمین اضافه کنید تا قادر به ارسال پیام و افزودن رسانه شود.</li>
            </ol>
            <h2>ارسال پیام به کانال‌های خصوصی</h2>
            <p>برای ارسال پیام به کانال‌های خصوصی، تنها کافی نیست که ربات به عنوان مدیر در کانال حضور داشته باشد. شما باید شناسه عددی (chat_id) آن کانال را دریافت کنید و به جای نام کاربری (مثلاً @channel) از این شناسه استفاده نمایید. معمولاً این شناسه به شکل منفی مانند <code>-1001234567890</code> است. برای دریافت شناسه کانال، از ربات <code>getChat</code> API تلگرام یا ابزارهای جانبی استفاده کنید و سپس کد دریافت شده را در فیلد <strong>Telegram Channel IDs</strong> افزونه وارد نمایید.</p>
        </div>
        <br/>
        <p style="font-size:12px; text-align:center; color:#666;">
            طراحی انواع افزونه های کاربردی برای فروشگاه شما<br/>
            t.me/HashemiPY
        </p>
        <script>
            jQuery(document).ready(function($){
                $('#spf_help_button').click(function(e){
                    e.preventDefault();
                    $('#spf_help_text').toggle();
                });
            });
        </script>
    </div>
    <?php
}

function spf_enqueue_scripts($hook) {
    if ('toplevel_page_spf-custom-product' != $hook && 'spf-settings' != $hook && 'spf-edit-products' != $hook) {
        return;
    }
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'spf_enqueue_scripts');

// نمایش صفحه اصلاح محصولات
function spf_display_edit_products_page() {
    // نمایش پیام موفقیت پس از ذخیره تغییرات
    if (isset($_GET['product_updated']) && $_GET['product_updated'] == 1) {
        echo '<div class="updated"><p>تغییرات محصول با موفقیت ذخیره شد!</p></div>';
    }

    // دریافت محصولات
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );
    $products = new WP_Query($args);
    ?>
    <div class="wrap">
        <h1>اصلاح محصولات</h1>
        <p>محصولی را انتخاب کنید تا گزینه‌های تعداد و حداقل تعداد خرید آن را ویرایش کنید.</p>
        
        <!-- انتخاب محصول -->
        <form method="get" action="">
            <input type="hidden" name="page" value="spf-edit-products" />
            <table class="form-table">
                <tr>
                    <th><label for="spf_product_select">انتخاب محصول:</label></th>
                    <td>
                        <select id="spf_product_select" name="product_id">
                            <option value="">یک محصول انتخاب کنید</option>
                            <?php
                            if ($products->have_posts()) {
                                while ($products->have_posts()) {
                                    $products->the_post();
                                    $product_id = get_the_ID();
                                    $sku = get_post_meta($product_id, '_sku', true);
                                    echo '<option value="' . esc_attr($product_id) . '">' . esc_html(get_the_title()) . ' (SKU: ' . esc_html($sku) . ')</option>';
                                }
                                wp_reset_postdata();
                            }
                            ?>
                        </select>
                        <input type="submit" class="button" value="نمایش فرم ویرایش" />
                    </td>
                </tr>
            </table>
        </form>

        <?php
        // اگر محصولی انتخاب شده، فرم ویرایش را نمایش بده
        if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
            $product_id = intval($_GET['product_id']);
            $product = wc_get_product($product_id);
            if ($product) {
                $quantity_options = get_post_meta($product_id, '_quantity_options', true);
                $min_quantity = get_post_meta($product_id, '_min_quantity', true);
                ?>
                <h2>ویرایش محصول: <?php echo esc_html($product->get_name()); ?></h2>
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th><label for="spf_quantity_options">گزینه‌های تعداد (عددی):</label></th>
                            <td>
                                <input type="text" id="spf_quantity_options" name="spf_quantity_options" value="<?php echo esc_attr($quantity_options); ?>" placeholder="مثال: 4.8.12" style="width:400px;" />
                                <p>مقادیر را با نقطه جدا کنید (مثال: 4.8.12).</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="spf_min_quantity">حداقل تعداد خرید:</label></th>
                            <td>
                                <input type="number" id="spf_min_quantity" name="spf_min_quantity" value="<?php echo esc_attr($min_quantity ? $min_quantity : 1); ?>" min="1" style="width:100px;" />
                                <p>حداقل تعداد قابل سفارش توسط مشتری.</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <?php wp_nonce_field('spf_edit_product_form', 'spf_edit_product_nonce'); ?>
                        <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />
                        <input type="submit" name="spf_submit_edit_product" class="button-primary" value="ذخیره تغییرات" />
                    </p>
                </form>
                <?php
            } else {
                echo '<div class="error"><p>محصول یافت نشد.</p></div>';
            }
        }
        ?>
    </div>
    <?php
}

// پردازش فرم ویرایش محصول
function spf_handle_edit_product_submission() {
    if (!isset($_POST['spf_submit_edit_product']) || !isset($_POST['spf_edit_product_nonce']) || !wp_verify_nonce($_POST['spf_edit_product_nonce'], 'spf_edit_product_form')) {
        echo '<div class="error"><p>خطا در اعتبارسنجی فرم. لطفاً مجدداً تلاش کنید.</p></div>';
        return;
    }

    $product_id = intval($_POST['product_id']);
    $quantity_options = sanitize_text_field($_POST['spf_quantity_options']);
    $min_quantity = intval($_POST['spf_min_quantity']);

    // ذخیره تغییرات
    update_post_meta($product_id, '_quantity_options', $quantity_options);
    update_post_meta($product_id, '_min_quantity', $min_quantity);

    // به‌روزرسانی ویژگی تعداد (pa_quantity) برای محصولات متغیر
    if ($quantity_options) {
        $qty_vals = array_filter(
            array_map('trim', explode('.', $quantity_options)),
            'is_numeric'
        );
        if ($qty_vals) {
            wp_set_object_terms($product_id, $qty_vals, 'pa_quantity');
            $attributes = get_post_meta($product_id, '_product_attributes', true);
            if (!$attributes) {
                $attributes = [];
            }
            $attributes['pa_quantity'] = [
                'name'         => 'pa_quantity',
                'value'        => '',
                'is_visible'   => 1,
                'is_variation' => 1,
                'is_taxonomy'  => 1,
            ];
            update_post_meta($product_id, '_product_attributes', $attributes);
        } else {
            // اگر گزینه‌های تعداد خالی شد، ویژگی تعداد را حذف کن
            wp_set_object_terms($product_id, [], 'pa_quantity');
            $attributes = get_post_meta($product_id, '_product_attributes', true);
            if (is_array($attributes) && isset($attributes['pa_quantity'])) {
                unset($attributes['pa_quantity']);
                update_post_meta($product_id, '_product_attributes', $attributes);
            }
        }
    }

    // ریدایرکت به همان صفحه با پیام موفقیت
    wp_safe_redirect(admin_url('admin.php?page=spf-edit-products&product_updated=1'));
    exit;
}

// بررسی ارسال فرم ویرایش
add_action('admin_init', 'spf_check_edit_product_submission');
function spf_check_edit_product_submission() {
    if (isset($_POST['spf_submit_edit_product'])) {
        spf_handle_edit_product_submission();
    }
}

// نمایش فرم افزودن محصول
function spf_display_product_form() {
    // نمایش پیام موفقیت پس از ریدایرکت
    if (isset($_GET['product_added']) && $_GET['product_added'] == 1) {
        echo '<div class="updated"><p>محصول با موفقیت اضافه شد!</p></div>';
    }

    $attribute_taxonomies = wc_get_attribute_taxonomies();
    
    $product_cats = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ));
    
    $channels_raw = get_option('spf_telegram_chat_ids');
    $channels_arr = array_filter(array_map('trim', preg_split('/\s+/', $channels_raw)));
    
    // دریافت آخرین SKU ثبت‌شده و تنظیم مقدار پیش‌فرض
    $default_sku = '';
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 1,
        'meta_key'       => '_sku',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'meta_query'     => array(
            array(
                'key'     => '_sku',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC'
            )
        )
    );
    $sku_query = new WP_Query($args);
    if ($sku_query->have_posts()) {
        $sku_query->the_post();
        $last_sku = get_post_meta(get_the_ID(), '_sku', true);
        wp_reset_postdata();
        $default_sku = intval($last_sku) + 1;
    } else {
        $default_sku = 1;
    }
    ?>
    <div class="wrap">
        <h1>افزودن محصول سفارشی جدید</h1>
        <form method="post" action="">
            <table class="form-table">
                <!-- فیلدهای فرم -->
                <tr>
                    <th><label for="spf_sku">کد محصول:</label></th>
                    <td><input type="text" id="spf_sku" name="spf_sku" value="<?php echo esc_attr($default_sku); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="spf_product_name">نام محصول:</label></th>
                    <td><input type="text" id="spf_product_name" name="spf_product_name" value="" /></td>
                </tr>
                <!-- دسته‌بندی محصول -->
                <tr>
                    <th><label for="spf_product_category">دسته‌بندی محصول:</label></th>
                    <td>
                        <select id="spf_product_category" name="spf_product_category">
                            <option value="">انتخاب کنید</option>
                            <?php
                            if (!is_wp_error($product_cats) && !empty($product_cats)) {
                                foreach ($product_cats as $cat) {
                                    echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <!-- فیلدهای سئو -->
                <tr>
                    <th><label for="spf_meta_title">Meta Title:</label></th>
                    <td><input type="text" id="spf_meta_title" name="spf_meta_title" value="" style="width:400px;" /></td>
                </tr>
                <tr>
                    <th><label for="spf_meta_description">Meta Description:</label></th>
                    <td><textarea id="spf_meta_description" name="spf_meta_description" style="width:400px;"></textarea></td>
                </tr>
                <tr>
                    <th><label for="spf_custom_url">Custom URL:</label></th>
                    <td><input type="text" id="spf_custom_url" name="spf_custom_url" value="" style="width:400px;" /></td>
                </tr>
                <!-- دکمه پیشنهاد هوشمند سئو -->
                <tr>
                    <th>پیشنهاد سئو:</th>
                    <td>
                        <button type="button" id="generate_seo_btn">پیشنهاد هوشمند سئو</button>
                        <div id="seo_suggestion_box" style="background:#eef; padding:10px; margin-top:10px;"></div>
                    </td>
                </tr>
                <!-- ویژگی‌های متغیر (رنگ، سایز و غیره، بدون تعداد) -->
                <?php
                if ($attribute_taxonomies) {
                    foreach ($attribute_taxonomies as $index => $tax) {
                        $attribute_taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
                        if ($attribute_taxonomy_name !== 'pa_quantity') {
                            $attribute_label = $tax->attribute_label;
                            ?>
                            <tr>
                                <th><label><?php echo esc_html($attribute_label); ?>:</label></th>
                                <td>
                                    <input type="checkbox" name="attributes[<?php echo esc_attr($index); ?>][enabled]" value="1" />
                                    استفاده از این ویژگی
                                    <br/>
                                    <input type="hidden" name="attributes[<?php echo esc_attr($index); ?>][name]" value="<?php echo esc_attr($attribute_taxonomy_name); ?>" />
                                    <input type="text" name="attributes[<?php echo esc_attr($index); ?>][values]" placeholder="مقادیر را وارد کنید (با نقطه جدا کنید)" style="width:400px;" />
                                </td>
                            </tr>
                            <?php
                        }
                    }
                } else {
                    echo '<tr><th>ویژگی‌ها:</th><td>هیچ ویژگی‌ای تعریف نشده است.</td></tr>';
                }
                ?>
                <!-- فیلد جدید: گزینه‌های تعداد -->
                <tr>
                    <th><label for="spf_quantity_options">گزینه‌های تعداد (عددی):</label></th>
                    <td>
                        <input type="text" id="spf_quantity_options" name="spf_quantity_options" placeholder="مثال: 4.8.12" style="width:400px;" />
                        <p>مقادیر را با نقطه جدا کنید (مثال: 4.8.12).</p>
                    </td>
                </tr>
                <!-- فیلد جدید: حداقل تعداد -->
                <tr>
                    <th><label for="spf_min_quantity">حداقل تعداد خرید:</label></th>
                    <td>
                        <input type="number" id="spf_min_quantity" name="spf_min_quantity" value="4" min="1" style="width:100px;" />
                        <p>حداقل تعداد قابل سفارش توسط مشتری.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="spf_price">قیمت (برای هر واحد):</label></th>
                    <td><input type="number" id="spf_price" name="spf_price" value="" /></td>
                </tr>
                <tr>
                    <th><label for="spf_stock">موجودی کل:</label></th>
                    <td><input type="number" id="spf_stock" name="spf_stock" value="" /></td>
                </tr>
                <tr>
                    <th><label for="spf_description">توضیحات محصول:</label></th>
                    <td><textarea id="spf_description" name="spf_description" rows="10"></textarea></td>
                </tr>
                <!-- تصویر اصلی -->
                <tr>
                    <th><label>تصویر اصلی:</label></th>
                    <td>
                        <img id="spf_main_image_preview" src="" style="max-width: 150px; display: block; margin-bottom: 10px; cursor:pointer;" title="برای حذف کلیک کنید" />
                        <?php wp_nonce_field('media-form'); ?>
                        <?php $main_image_id = ''; ?>
                        <input type="hidden" id="spf_main_image" name="spf_main_image" value="<?php echo esc_attr($main_image_id); ?>" />
                        <button type="button" class="button" id="spf_main_image_button">انتخاب تصویر</button>
                    </td>
                </tr>
                <!-- تصاویر اضافی -->
                <tr>
                    <th><label>تصاویر اضافی:</label></th>
                    <td>
                        <div id="spf_additional_images_preview" style="margin-bottom: 10px;"></div>
                        <?php $additional_images_ids = ''; ?>
                        <input type="hidden" id="spf_additional_images" name="spf_additional_images" value="<?php echo esc_attr($additional_images_ids); ?>" />
                        <button type="button" class="Tabs - vertical" id="spf_additional_images_button">انتخاب تصاویر</button>
                    </td>
                </tr>
                <!-- انتخاب کانال‌های تلگرام -->
                <tr>
                    <th>انتخاب کانال‌های تلگرام:</th>
                    <td>
                        <?php 
                        if (!empty($channels_arr)) {
                            foreach ($channels_arr as $channel) {
                                echo '<label style="display:block; margin-bottom:5px;">';
                                echo '<input type="checkbox" name="spf_selected_channels[]" value="' . esc_attr($channel) . '"> ' . esc_html($channel);
                                echo '</label>';
                            }
                        } else {
                            echo 'تنظیماتی جهت نمایش کانال وجود ندارد';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <?php wp_nonce_field('spf_product_form', 'spf_product_nonce'); ?>
                <input type="submit" name="spf_submit_product" class="button-primary" value="افزودن محصول" />
            </p>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($){
        // انتخاب تصویر اصلی
        var main_image_frame;
        $('#spf_main_image_button').click(function(e){
            e.preventDefault();
            if (main_image_frame) {
                main_image_frame.open();
                return;
            }
            main_image_frame = wp.media({
                title: 'انتخاب تصویر اصلی',
                button: { text: 'انتخاب' },
                multiple: false
            });
            main_image_frame.on('select', function(){
                var attachment = main_image_frame.state().get('selection').first().toJSON();
                $('#spf_main_image').val(attachment.id);
                $('#spf_main_image_preview').attr('src', attachment.url);
            });
            main_image_frame.open();
        });

        // حذف تصویر اصلی
        $('#spf_main_image_preview').click(function(e){
            e.preventDefault();
            if(confirm('آیا می‌خواهید تصویر اصلی را حذف کنید؟')){
                $(this).attr('src', '');
                $('#spf_main_image').val('');
            }
        });

        // انتخاب تصاویر اضافی
        var additional_images_frame;
        $('#spf_additional_images_button').click(function(e){
            e.preventDefault();
            if (additional_images_frame) {
                additional_images_frame.open();
                return;
            }
            additional_images_frame = wp.media({
                title: 'انتخاب تصاویر اضافی',
                button: { text: 'انتخاب' },
                multiple: true
            });
            additional_images_frame.on('select', function(){
                var selection = additional_images_frame.state().get('selection');
                var current_ids = $('#spf_additional_images').val();
                var ids = current_ids ? current_ids.split(',') : [];
                var previews = $('#spf_additional_images_preview').html();
                selection.each(function(attachment){
                    attachment = attachment.toJSON();
                    if(ids.indexOf(attachment.id.toString()) === -1) {
                        ids.push(attachment.id);
                        previews += '<div class="spf-additional-image" style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">';
                        previews += '<img src="'+attachment.url+'" style="max-width: 100px;"/>';
                        previews += '<span class="spf-remove-image" data-id="'+attachment.id+'" style="position:absolute; top:0; right:0; cursor:pointer; color:red; background:#fff; border:1px solid #ccc; padding:2px;">×</span>';
                        previews += '</div>';
                    }
                });
                $('#spf_additional_images').val(ids.join(','));
                $('#spf_additional_images_preview').html(previews);
            });
            additional_images_frame.open();
        });

        // حذف تصاویر اضافی
        $('#spf_additional_images_preview').on('click', '.spf-remove-image', function(e){
            e.preventDefault();
            var imageId = $(this).data('id').toString();
            $(this).closest('.spf-additional-image').remove();
            var currentIds = $('#spf_additional_images').val().split(',');
            currentIds = currentIds.filter(function(id){
                return id !== imageId;
            });
            $('#spf_additional_images').val(currentIds.join(','));
        });
        
        // رویداد دکمه پیشنهاد هوشمند سئو
        $('#generate_seo_btn').on('click', function(){
            var productName = $('#spf_product_name').val().trim();
            if(!productName) {
                alert('لطفاً ابتدا نام محصول را وارد کنید.');
                return;
            }
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'generate_ai_seo_suggestions',
                    product_name: productName,
                    _ajax_nonce: '<?php echo wp_create_nonce("generate_ai_seo_nonce"); ?>'
                },
                success: function(response){
                    if(response.success){
                        var suggestions = response.data;
                        $('#spf_meta_title').val(suggestions.meta_title);
                        $('#spf_meta_description').val(suggestions.meta_description);
                        $('#spf_custom_url').val(suggestions.custom_url);
                        $('#seo_suggestion_box').html("<strong>پیشنهادات دریافت شده:</strong><br>" +
                            "<em>Meta Title:</em> " + suggestions.meta_title + "<br>" +
                            "<em>Meta Description:</em> " + suggestions.meta_description + "<br>" +
                            "<em>Custom URL:</em> " + suggestions.custom_url);
                    } else {
                        alert('خطا: ' + response.data);
                    }
                },
                error: function(){
                    alert('خطا در برقراری ارتباط با سرور.');
                }
            });
        });
    });
    </script>
    <?php

    if (isset($_POST['spf_submit_product'])) {
        if (!isset($_POST['spf_product_nonce']) || !wp_verify_nonce($_POST['spf_product_nonce'], 'spf_product_form')) {
            echo '<div class="error"><p>خطا در اعتبارسنجی فرم. لطفاً مجدداً تلاش کنید.</p></div>';
            exit;
        }
        spf_handle_product_submission();
    }
}

// پردازش فرم ارسال محصول
function spf_handle_product_submission() {
    $sku = sanitize_text_field($_POST['spf_sku']);
    $product_name = sanitize_text_field($_POST['spf_product_name']);
    $price = floatval($_POST['spf_price']);
    $stock = intval($_POST['spf_stock']);
    $description = wp_kses_post($_POST['spf_description']);
    $main_image_id = intval($_POST['spf_main_image']);
    $additional_images_ids = sanitize_text_field($_POST['spf_additional_images']);
    $min_quantity = isset($_POST['spf_min_quantity']) ? intval($_POST['spf_min_quantity']) : 1;
    $quantity_options = isset($_POST['spf_quantity_options']) ? sanitize_text_field($_POST['spf_quantity_options']) : '';

    if (isset($_POST['spf_product_category']) && $_POST['spf_product_category'] !== '') {
        $product_cat_id = intval($_POST['spf_product_category']);
    }

    // دریافت فیلدهای سئو
    $meta_title = sanitize_text_field($_POST['spf_meta_title']);
    $meta_description = sanitize_textarea_field($_POST['spf_meta_description']);
    $custom_url = sanitize_text_field($_POST['spf_custom_url']);

    $selected_channels = isset($_POST['spf_selected_channels']) ? array_map('trim', $_POST['spf_selected_channels']) : [];

    // درج پست محصول
    $post_data = [
        'post_title'   => $product_name,
        'post_content' => $description,
        'post_status'  => 'publish',
        'post_type'    => 'product',
    ];
    if ($custom_url !== '') {
        $post_data['post_name'] = sanitize_title($custom_url);
    }
    $post_id = wp_insert_post($post_data);

    // متادیتای پایه
    update_post_meta($post_id, '_sku', $sku);
    update_post_meta($post_id, '_regular_price', $price);
    update_post_meta($post_id, '_price', $price);
    update_post_meta($post_id, '_stock', $stock);
    update_post_meta($post_id, '_stock_status', $stock > 0 ? 'instock' : 'outofstock');
    update_post_meta($post_id, '_manage_stock', 'yes');
    update_post_meta($post_id, '_min_quantity', $min_quantity);
    update_post_meta($post_id, '_quantity_options', $quantity_options);

    // ذخیره متادیتای سئو
    if ($meta_title !== '') {
        update_post_meta($post_id, '_yoast_wpseo_title', $meta_title);
    }
    if ($meta_description !== '') {
        update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_description);
    }

    // دسته‌بندی
    if (isset($product_cat_id)) {
        wp_set_object_terms($post_id, $product_cat_id, 'product_cat');
    }

    // پردازش ویژگی‌های دلخواه (رنگ، سایز و غیره)
    $attributes_data = isset($_POST['attributes']) ? $_POST['attributes'] : [];
    $attributes = [];
    foreach ($attributes_data as $attribute_info) {
        if (!empty($attribute_info['enabled'])) {
            $attr_name = sanitize_text_field($attribute_info['name']);
            $values = sanitize_text_field($attribute_info['values']);
            if ($values) {
                $terms = array_map('trim', explode('.', $values));
                wp_set_object_terms($post_id, $terms, $attr_name);
                $attributes[$attr_name] = [
                    'name'         => $attr_name,
                    'value'        => '',
                    'is_visible'   => 1,
                    'is_variation' => 1,
                    'is_taxonomy'  => 1,
                ];
            }
        }
    }

    // افزودن ویژگی تعداد
    if ($quantity_options !== '') {
        $qty_vals = array_filter(
            array_map('trim', explode('.', $quantity_options)),
            'is_numeric'
        );
        if ($qty_vals) {
            wp_set_object_terms($post_id, $qty_vals, 'pa_quantity');
            $attributes['pa_quantity'] = [
                'name'         => 'pa_quantity',
                'value'        => '',
                'is_visible'   => 1,
                'is_variation' => 1,
                'is_taxonomy'  => 1,
            ];
        }
    }

    // اگر ویژگی‌ای داشتیم (غیر از تعداد) => متغیر، واریشن بساز
    $has_real_attr = false;
    foreach ($attributes_data as $info) {
        if (!empty($info['enabled'])) {
            $has_real_attr = true;
            break;
        }
    }
    if ($attributes) {
        update_post_meta($post_id, '_product_attributes', $attributes);
    }

    if ($has_real_attr) {
        wp_set_object_terms($post_id, 'variable', 'product_type');
        create_product_variations($post_id, $attributes, $price, $stock);
    } else {
        wp_set_object_terms($post_id, 'simple', 'product_type');
    }

    // تنظیم تصویر اصلی
    if ($main_image_id) {
        set_post_thumbnail($post_id, $main_image_id);
        $alt = get_post_meta($main_image_id, '_wp_attachment_image_alt', true);
        if (empty($alt)) {
            update_post_meta($main_image_id, '_wp_attachment_image_alt', $product_name);
        }
        spf_compress_image($main_image_id);
    }

    // تصاویر اضافی
    if ($additional_images_ids) {
        $gallery = explode(',', $additional_images_ids);
        update_post_meta($post_id, '_product_image_gallery', implode(',', $gallery));
        foreach ($gallery as $id) {
            $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
            if (empty($alt)) {
                update_post_meta($id, '_wp_attachment_image_alt', $product_name);
            }
            spf_compress_image($id);
        }
    }

    // اسکیما و پایان
    spf_add_schema_markup($post_id, $product_name, $description, $price, $stock);
    
    // ارسال به تلگرام
    spf_send_product_to_telegram(
        $post_id, $product_name, $description,
        $main_image_id, $additional_images_ids,
        $selected_channels
    );

    // ریدایرکت به همان صفحه با پارامتر موفقیت
    wp_safe_redirect(admin_url('admin.php?page=spf-custom-product&product_added=1'));
    exit;
}

// ایجاد واریاسیون‌های محصول
function create_product_variations($post_id, $attributes, $price, $stock) {
    $attr_terms = array();
    foreach ($attributes as $attribute_name => $attribute_data) {
        $terms = wp_get_post_terms($post_id, $attribute_name, array('fields' => 'slugs'));
        if (!empty($terms)) {
            $attr_terms[$attribute_name] = $terms;
        }
    }
    $combinations = generate_combinations($attr_terms);
    foreach ($combinations as $combination) {
        $variation = array(
            'post_title'  => 'واریاسیون: ' . implode(' - ', $combination),
            'post_status' => 'publish',
            'post_parent' => $post_id,
            'post_type'   => 'product_variation',
        );
        $variation_id = wp_insert_post($variation);
        foreach ($combination as $attribute_name => $term_slug) {
            update_post_meta($variation_id, 'attribute_' . $attribute_name, $term_slug);
        }
        update_post_meta($variation_id, '_regular_price', $price);
        update_post_meta($variation_id, '_price', $price);
        update_post_meta($variation_id, '_manage_stock', 'no');
    }
}

// تولید ترکیب‌های ویژگی‌ها
function generate_combinations($arrays) {
    $result = array(array());
    foreach ($arrays as $property => $values) {
        $tmp = array();
        foreach ($result as $result_item) {
            foreach ($values as $value) {
                $tmp[] = array_merge($result_item, array($property => $value));
            }
        }
        $result = $tmp;
    }
    return $result;
}

// تولید Schema Markup
function spf_add_schema_markup($post_id, $product_name, $description, $price, $stock) {
    $schema = array(
        '@context' => 'https://schema.org/',
        '@type'    => 'Product',
        'name'     => $product_name,
        'description' => wp_strip_all_tags($description),
        'sku'      => get_post_meta($post_id, '_sku', true),
        'offers'   => array(
            '@type'         => 'Offer',
            'priceCurrency' => get_woocommerce_currency(),
            'price'         => $price,
            'availability'  => $stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url'           => get_permalink($post_id)
        )
    );
    update_post_meta($post_id, '_schema_markup', json_encode($schema));
}

// ارسال محصول به تلگرام
function spf_send_product_to_telegram($post_id, $product_name, $description, $main_image_id, $additional_images_ids, $selected_channels = array()) {
    if (empty($selected_channels)) {
        return;
    }
    $bot_token = get_option('spf_telegram_bot_token');
    $chat_ids_array = $selected_channels;
    
    $product_link = get_permalink($post_id);
    $sku = get_post_meta($post_id, '_sku', true);
    $message = "<b>{$product_name}</b>\nکد محصول: {$sku}\n\n{$description}\n\n🔗 <a href='{$product_link}'>خرید مستقیم از سایت</a>";
    $admin_id = get_option('spf_admin_id');
    if ($admin_id) {
        if (strpos($admin_id, '@') !== 0) {
            $admin_id = '@' . $admin_id;
        }
        $message .= "\n\nارتباط با ادمین {$admin_id}";
    }
    $instagram_link = get_option('spf_instagram_link');
    if ($instagram_link) {
        $message .= "\n\n📸 <a href='{$instagram_link}'>ورود به اینستاگرام</a>";
    }
    if (mb_strlen($message) > 1024) {
        $message = mb_substr($message, 0, 1020) . '...';
    }
    $media = array();
    if ($main_image_id) {
        $main_image_url = wp_get_attachment_url($main_image_id);
        $media[] = array(
            'type'       => 'photo',
            'media'      => $main_image_url,
            'caption'    => $message,
            'parse_mode' => 'HTML'
        );
    }
    if (!empty($additional_images_ids)) {
        $gallery_ids = explode(',', $additional_images_ids);
        foreach ($gallery_ids as $attachment_id) {
            $image_url = wp_get_attachment_url($attachment_id);
            $media[] = array(
                'type'  => 'photo',
                'media' => $image_url
            );
        }
    }
    $media_chunks = array_chunk($media, 10);
    foreach ($chat_ids_array as $chat_id) {
        $chat_id = trim($chat_id);
        foreach ($media_chunks as $chunk_index => $media_chunk) {
            $final_media = $media_chunk;
            if ($chunk_index > 0) {
                unset($final_media[0]['caption']);
                unset($final_media[0]['parse_mode']);
            }
            spf_send_media_group_to_telegram($bot_token, $chat_id, $final_media);
        }
    }
}

// ارسال گروهی تصاویر به تلگرام
function spf_send_media_group_to_telegram($bot_token, $chat_id, $media) {
    $data = array(
        'chat_id' => $chat_id,
        'media'   => json_encode($media),
    );
    $url = "https://api.telegram.org/bot{$bot_token}/sendMediaGroup";
    $response = wp_remote_post($url, array(
        'method'  => 'POST',
        'timeout' => 45,
        'body'    => $data,
    ));
    if (is_wp_error($response)) {
        error_log('Telegram Error: ' . $response->get_error_message());
    }
}

// بهینه‌سازی و فشرده‌سازی تصاویر
function spf_compress_image($attachment_id) {
    $file_path = get_attached_file($attachment_id);
    $editor = wp_get_image_editor($file_path);
    if (!is_wp_error($editor)) {
        $editor->set_quality(80);
        $result = $editor->save($file_path);
        if (!is_wp_error($result)) {
            $metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $metadata);
        }
    }
}

// AJAX Handler برای تولید پیشنهادات سئو
function spf_generate_ai_seo_suggestions() {
    check_ajax_referer('generate_ai_seo_nonce');
    
    $product_name = isset($_POST['product_name']) ? sanitize_text_field($_POST['product_name']) : '';
    if (empty($product_name)) {
        wp_send_json_error('نام محصول الزامی است.');
    }
    
    if (!defined('OPENAI_API_KEY')) {
        $meta_title = 'لباس کودک ' . $product_name . ' | جدیدترین مدل‌ها و کیفیت برتر - تولیدی دانیال';
        $meta_description = 'با خرید ' . $product_name . ' از تولید و پخش پوشاک بچگانه دانیال ، از پوشاک کودک با طراحی مدرن، کیفیت بالا و قیمت مناسب بهره‌مند شوید. .';
        $custom_url = 'labaas-koodak-' . strtolower(str_replace(' ', '-', $product_name));
        wp_send_json_success(array(
            'meta_title'       => $meta_title,
            'meta_description' => $meta_description,
            'custom_url'       => $custom_url
        ));
    }
    
    $prompt = "برای یک محصول لباس کودک با نام \"$product_name\" پیشنهادهای سئو ارائه بده. یک Meta Title، یک Meta Description و یک Custom URL پیشنهاد کن. پاسخ را در سه خط جداگانه ارائه بده.";
    
    $request_body = array(
        "model" => "text-davinci-003",
        "prompt" => $prompt,
        "max_tokens" => 150,
        "temperature" => 0.7
    );
    
    $response = wp_remote_post('https://api.openai.com/v1/completions', array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . OPENAI_API_KEY,
        ),
        'body' => json_encode($request_body)
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error('خطا در ارتباط با سرویس هوش مصنوعی.');
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!isset($body['choices'][0]['text'])) {
        wp_send_json_error('پاسخی از هوش مصنوعی دریافت نشد.');
    }
    
    $lines = explode("\n", trim($body['choices'][0]['text']));
    $meta_title = isset($lines[0]) ? trim($lines[0]) : 'لباس کودک ' . $product_name . ' | جدیدترین مدل‌ها  - دانیال';
    $meta_description = isset($lines[1]) ? trim($lines[1]) : 'با خرید ' . $product_name . ' از فروشگاه پخش پوشاک کودک دانیال ، از پوشاک کودک با طراحی مدرن، کیفیت بالا و قیمت مناسب بهره‌مند شوید.';
    $custom_url = isset($lines[2]) ? trim($lines[2]) : 'labaas-koodak-' . strtolower(str_replace(' ', '-', $product_name));
    
    wp_send_json_success(array(
        'meta_title'       => $meta_title,
        'meta_description' => $meta_description,
        'custom_url'       => $custom_url
    ));
}
add_action('wp_ajax_generate_ai_seo_suggestions', 'spf_generate_ai_seo_suggestions');

// غیرفعال کردن فیلد ورودی تعداد و اطمینان از انتخاب فقط از منوی کشویی
add_action('wp_footer', 'spf_quantity_control_script');
function spf_quantity_control_script() {
    if (!is_product()) {
        return;
    }
    global $product;
    $quantity_options = get_post_meta($product->get_id(), '_quantity_options', true);
    $min_quantity = get_post_meta($product->get_id(), '_min_quantity', true);

    if (!$quantity_options) {
        return;
    }

    $options = array_filter(array_map('intval', explode('.', $quantity_options)));

    ?>
    <script>
    jQuery(document).ready(function($){
        var form = $('form.cart');
        var qtyIn = form.find('input.qty');
        qtyIn.prop('readonly', true).css('opacity','0.5');
        form.find('.quantity .minus, .quantity .plus').hide();

        var varSelect = form.find('select[name="attribute_pa_quantity"]');
        if (varSelect.length) {
            varSelect.on('change', function(){
                qtyIn.val(parseInt($(this).val()));
            });
            varSelect.trigger('change');
        } else {
            $('<label class="spf-qty-label" style="display:block; margin-bottom:5px;">تعداد را انتخاب کنید</label>').insertBefore(qtyIn);
            var dropdown = $('<select class="spf-qty-dropdown"></select>').insertBefore(qtyIn);
            $.each(<?php echo json_encode(array_values($options)); ?>, function(i,v){
                dropdown.append($('<option/>').val(v).text(v));
            });
            dropdown.val(<?php echo intval($min_quantity); ?>).trigger('change');
            dropdown.on('change', function(){
                qtyIn.val($(this).val());
            });
        }
    });
    </script>
    <?php
}

// اعمال حداقل تعداد خرید و اعتبارسنجی
add_filter('woocommerce_quantity_input_args', 'spf_custom_quantity_input_args', 10, 2);
function spf_custom_quantity_input_args($args, $product) {
    if ($product->is_type('variable')) {
        $min_quantity = get_post_meta($product->get_id(), '_min_quantity', true);
        if ($min_quantity) {
            $args['min_value'] = $min_quantity;
            $args['input_value'] = $min_quantity;
        }
    }
    return $args;
}

// اعتبارسنجی تعداد در سبد خرید
add_filter('woocommerce_add_to_cart_validation', 'spf_validate_quantity_on_add_to_cart', 10, 5);
function spf_validate_quantity_on_add_to_cart($passed, $product_id, $quantity, $variation_id = 0, $variations = array()) {
    $quantity_options = get_post_meta($product_id, '_quantity_options', true);
    $min_quantity = get_post_meta($product_id, '_min_quantity', true);

    if ($quantity_options) {
        $allowed_quantities = array_map('intval', explode('.', $quantity_options));
        if (!in_array($quantity, $allowed_quantities)) {
            wc_add_notice(sprintf('لطفاً تعداد را از بین گزینه‌های مجاز (%s) انتخاب کنید.', implode(', ', $allowed_quantities)), 'error');
            return false;
        }
        if ($min_quantity && $quantity < $min_quantity) {
            wc_add_notice(sprintf('حداقل تعداد خرید %d است.', $min_quantity), 'error');
            return false;
        }
    }
    return $passed;
}

// اطمینان از ثبت درست ویژگی تعداد
add_action('woocommerce_before_single_product', 'spf_ensure_quantity_attribute');
function spf_ensure_quantity_attribute() {
    global $product;
    if ($product->is_type('variable')) {
        $quantity_options = get_post_meta($product->ປິດ_id, '_quantity_options', true);
        if ($quantity_options) {
            $quantity_values = array_map('trim', explode('.', $quantity_options));
            $quantity_attribute_name = 'pa_quantity';
            wp_set_object_terms($product->get_id(), $quantity_values, $quantity_attribute_name);
        }
    }
}
?>