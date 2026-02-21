<?php
/**
 * Blog Section Template - بخش بلاگ
 */

// Get blog data from shortcode
$blog_data = json_decode(do_shortcode('[k_blog_data]'), true);
$post_ids = $blog_data['post_ids'] ?? array();

if (empty($post_ids)) {
    return;
}
?>

<section class="blog-section" style="padding: 4rem 0; background: linear-gradient(to bottom, #fafafa, #ffffff);">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 700; color: #1a1a1a; margin-bottom: 0.5rem;">📚 آخرین مقالات بلاگ</h2>
            <p style="font-size: 1rem; color: #666; max-width: 600px; margin: 0 auto;">جدیدترین مطالب و اخبار را در اینجا بخوانید</p>
        </div>
        
        <!-- Desktop Grid (3 columns) -->
        <div class="blog-grid desktop-blog" style="display: none; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 2rem;">
            <?php 
            $count = 0;
            foreach ($post_ids as $post_id) : 
                if ($count >= 6) break; // Show max 6 posts
                $post = get_post($post_id);
                if (!$post) continue;
                
                $thumbnail = khoshtip_convert_to_webp_url(get_the_post_thumbnail_url($post_id, 'large'));
                $excerpt = has_excerpt($post_id) ? get_the_excerpt($post_id) : wp_trim_words($post->post_content, 20);
                $date = get_the_date('d F Y', $post_id);
                $author = get_the_author_meta('display_name', $post->post_author);
                $count++;
            ?>
                <article class="blog-card" style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" style="text-decoration: none; color: inherit; display: block;">
                        <?php if ($thumbnail) : ?>
                            <div style="position: relative; width: 100%; aspect-ratio: 16/9; overflow: hidden; background: #f5f5f5;">
                                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($post->post_title); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';" />
                            </div>
                        <?php else: ?>
                            <div style="width: 100%; aspect-ratio: 16/9; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 3rem;">📝</span>
                            </div>
                        <?php endif; ?>
                        <div style="padding: 1.5rem;">
                            <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem; font-size: 0.75rem; color: #999;">
                                <span>📅 <?php echo esc_html($date); ?></span>
                                <span>✍️ <?php echo esc_html($author); ?></span>
                            </div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem; color: #1a1a1a; line-height: 1.4;"><?php echo esc_html($post->post_title); ?></h3>
                            <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem; line-height: 1.6;"><?php echo esc_html($excerpt); ?></p>
                            <span style="display: inline-flex; align-items: center; gap: 0.5rem; color: #ff6b9d; font-weight: 600; font-size: 0.9rem;">
                                ادامه مطلب 
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Mobile Carousel -->
        <div class="blog-carousel mobile-blog">
            <div class="swiper blog-swiper" style="padding-bottom: 3rem;">
                <div class="swiper-wrapper">
                    <?php 
                    $count = 0;
                    foreach ($post_ids as $post_id) : 
                        if ($count >= 6) break;
                        $post = get_post($post_id);
                        if (!$post) continue;
                        
                        $thumbnail = khoshtip_convert_to_webp_url(get_the_post_thumbnail_url($post_id, 'large'));
                        $excerpt = has_excerpt($post_id) ? get_the_excerpt($post_id) : wp_trim_words($post->post_content, 20);
                        $date = get_the_date('d F Y', $post_id);
                        $count++;
                    ?>
                        <div class="swiper-slide">
                            <article class="blog-card" style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); height: 100%;">
                                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" style="text-decoration: none; color: inherit; display: block; height: 100%;">
                                    <?php if ($thumbnail) : ?>
                                        <div style="width: 100%; aspect-ratio: 16/9; overflow: hidden; background: #f5f5f5;">
                                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($post->post_title); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
                                        </div>
                                    <?php else: ?>
                                        <div style="width: 100%; aspect-ratio: 16/9; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                            <span style="font-size: 3rem;">📝</span>
                                        </div>
                                    <?php endif; ?>
                                    <div style="padding: 1.5rem;">
                                        <div style="font-size: 0.75rem; color: #999; margin-bottom: 0.75rem;">📅 <?php echo esc_html($date); ?></div>
                                        <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.75rem; color: #1a1a1a; line-height: 1.4;"><?php echo esc_html($post->post_title); ?></h3>
                                        <p style="font-size: 0.875rem; color: #666; margin-bottom: 1rem; line-height: 1.6;"><?php echo esc_html($excerpt); ?></p>
                                        <span style="color: #ff6b9d; font-weight: 600; font-size: 0.875rem;">ادامه مطلب ←</span>
                                    </div>
                                </a>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show desktop or mobile based on screen size
    if (window.innerWidth >= 768) {
        document.querySelector('.desktop-blog').style.display = 'grid';
        document.querySelector('.mobile-blog').style.display = 'none';
    } else {
        document.querySelector('.desktop-blog').style.display = 'none';
        document.querySelector('.mobile-blog').style.display = 'block';
        
        // Initialize Swiper for mobile
        new Swiper('.blog-swiper', {
            slidesPerView: 1.2,
            spaceBetween: 16,
            centeredSlides: false,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                480: {
                    slidesPerView: 1.5,
                    spaceBetween: 20,
                }
            }
        });
    }
});
</script>
