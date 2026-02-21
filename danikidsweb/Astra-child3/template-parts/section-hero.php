<?php
/**
 * Hero Slider Section Template
 */

// Get hero data from shortcode
$hero_data = json_decode(do_shortcode('[k_hero_data]'), true);
$slides = $hero_data['slides'] ?? array();

if (empty($slides)) {
    return;
}
?>

<section class="hero-section">
    <div class="swiper hero-swiper">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $slide) : 
                // Convert URL images to WebP
                $bg_image = khoshtip_convert_to_webp_url($slide['background_image'] ?? '');
                $mobile_image = khoshtip_convert_to_webp_url($slide['mobile_image'] ?? '');
            ?>
                <div class="swiper-slide">
                    <!-- Wrapped entire slide in clickable link and removed all text overlays -->
                    <a href="<?php echo esc_url($slide['button_url']); ?>" class="hero-slide-link" style="display: block; position: relative; height: 80vh; overflow: hidden; text-decoration: none;">
                        <!-- Desktop/TV image - hidden on mobile -->
                        <img 
                            src="<?php echo esc_url($bg_image); ?>" 
                            alt="<?php echo esc_attr($slide['title']); ?>" 
                            class="hero-desktop-image"
                            loading="lazy"
                            onload="this.classList.add('loaded')"
                            style="width: 100%; height: 100%; object-fit: cover; display: none;" 
                        />
                        <!-- Mobile image - shown on mobile, shorter height -->
                        <img 
                            src="<?php echo esc_url($mobile_image); ?>" 
                            alt="<?php echo esc_attr($slide['title']); ?>" 
                            class="hero-mobile-image"
                            loading="lazy"
                            onload="this.classList.add('loaded')"
                            style="width: 100%; height: 100%; object-fit: cover; display: block;" 
                        />
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<style>
/* Show desktop image on larger screens, mobile on small screens */
@media (min-width: 768px) {
    .hero-desktop-image {
        display: block !important;
    }
    .hero-mobile-image {
        display: none !important;
    }
    .hero-slide-link {
        height: 80vh !important;
    }
}

@media (max-width: 767px) {
    .hero-slide-link {
        /* Increased mobile banner height by 10%: 36vh * 1.1 = 39.6vh */
        height: 39.6vh !important;
    }
}
</style>
