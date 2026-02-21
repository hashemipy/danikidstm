<?php
/**
 * Single Post Template - قالب نمایش تک پست
 */

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();
        $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'full');
        $author = get_the_author_meta('display_name');
        $date = get_the_date('d F Y');
        $categories = get_the_category();
?>

<article class="single-post" style="background: #fff; min-height: 100vh;">
    <!-- Hero Section with Featured Image -->
    <?php if ($thumbnail) : ?>
    <div class="post-hero" style="position: relative; width: 100%; height: 400px; background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('<?php echo esc_url($thumbnail); ?>'); background-size: cover; background-position: center; display: flex; align-items: flex-end; padding: 2rem;">
        <div class="container" style="max-width: 800px; margin: 0 auto; color: #fff;">
            <div style="margin-bottom: 1rem; display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.875rem;">
                <?php if ($categories) : foreach ($categories as $cat) : ?>
                    <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.25rem 0.75rem; border-radius: 20px;"><?php echo esc_html($cat->name); ?></span>
                <?php endforeach; endif; ?>
            </div>
            <h1 style="font-size: clamp(1.75rem, 5vw, 3rem); font-weight: 800; line-height: 1.2; margin-bottom: 1rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?php the_title(); ?></h1>
            <div style="display: flex; gap: 2rem; font-size: 0.9rem; opacity: 0.95;">
                <span>✍️ <?php echo esc_html($author); ?></span>
                <span>📅 <?php echo esc_html($date); ?></span>
            </div>
        </div>
    </div>
    <?php else : ?>
    <div class="post-hero" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 4rem 2rem; text-align: center; color: #fff;">
        <div class="container" style="max-width: 800px; margin: 0 auto;">
            <?php if ($categories) : ?>
                <div style="margin-bottom: 1rem;">
                    <?php foreach ($categories as $cat) : ?>
                        <span style="background: rgba(255,255,255,0.2); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem; margin: 0 0.25rem;"><?php echo esc_html($cat->name); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <h1 style="font-size: clamp(1.75rem, 5vw, 3rem); font-weight: 800; line-height: 1.2; margin-bottom: 1rem;"><?php the_title(); ?></h1>
            <div style="display: flex; gap: 2rem; justify-content: center; font-size: 0.9rem; opacity: 0.95;">
                <span>✍️ <?php echo esc_html($author); ?></span>
                <span>📅 <?php echo esc_html($date); ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Post Content -->
    <div class="post-content" style="max-width: 800px; margin: 0 auto; padding: 3rem 1rem;">
        <div style="font-size: 1.125rem; line-height: 1.8; color: #333;">
            <?php the_content(); ?>
        </div>

        <!-- Post Navigation -->
        <div style="margin-top: 3rem; padding-top: 2rem; border-top: 2px solid #f0f0f0; display: flex; gap: 1rem; flex-wrap: wrap; justify-content: space-between;">
            <?php
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            ?>
            <?php if ($prev_post) : ?>
                <a href="<?php echo get_permalink($prev_post); ?>" style="flex: 1; min-width: 200px; padding: 1rem; background: #f9fafb; border-radius: 12px; text-decoration: none; color: inherit; transition: background 0.3s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#f9fafb'">
                    <div style="font-size: 0.75rem; color: #999; margin-bottom: 0.5rem;">→ مطلب قبلی</div>
                    <div style="font-weight: 600; color: #1a1a1a;"><?php echo esc_html($prev_post->post_title); ?></div>
                </a>
            <?php endif; ?>
            <?php if ($next_post) : ?>
                <a href="<?php echo get_permalink($next_post); ?>" style="flex: 1; min-width: 200px; padding: 1rem; background: #f9fafb; border-radius: 12px; text-decoration: none; color: inherit; text-align: left; transition: background 0.3s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#f9fafb'">
                    <div style="font-size: 0.75rem; color: #999; margin-bottom: 0.5rem;">مطلب بعدی ←</div>
                    <div style="font-weight: 600; color: #1a1a1a;"><?php echo esc_html($next_post->post_title); ?></div>
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php
    endwhile;
endif;

get_footer();
?>
