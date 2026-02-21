<?php
/**
 * Front Page Template
 * Homepage for Khoshtip Kocholo
 */

get_header();

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
?>

<main id="main" class="site-main">
    
    <?php
    
    // Stories Section
    get_template_part('template-parts/section', 'stories');
    
    // Hero Slider Section
    get_template_part('template-parts/section', 'hero');
    
    // Sales Section
    get_template_part('template-parts/section', 'sales');
    
    get_template_part('template-parts/section', 'product-accordion');
    
    // get_template_part('template-parts/section', 'categories');
    
    
    // Blog Section
    get_template_part('template-parts/section', 'blog');
    ?>
    
</main>

<?php
get_footer();
