<?php
/**
 * Stories Section Template - Instagram Style with Multiple Media
 */

// Get stories data from shortcode
$stories_data = json_decode(do_shortcode('[k_stories_data]'), true);
$stories = $stories_data['stories'] ?? array();

if (empty($stories)) {
    return;
}
?>

<section class="stories-section" style="padding: 0.25rem 0; background-color: oklch(1 0 0); overflow-x: auto;">
    <div class="container">
        <div class="stories-container hide-scrollbar" style="display: flex; gap: 1.5rem; overflow-x: auto; padding: 0.25rem 0;">
            <?php foreach ($stories as $index => $story) : 
                $avatar_url = khoshtip_convert_to_webp_url($story['avatar_url'] ?? '');
            ?>
                <div class="story-item" style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; min-width: 100px; cursor: pointer;" data-story-index="<?php echo $index; ?>">
                    <!-- Updated gradient ring with wavy red colors (light to dark) -->
                    <div class="story-ring" style="position: relative; width: 86px; height: 86px; border-radius: 50%; padding: 3px; background: linear-gradient(45deg, #ff6b6b, #ee5a6f, #c44569, #a5446a, #ff6b6b); background-size: 300% 300%;">
                        <div style="width: 100%; height: 100%; border-radius: 50%; overflow: hidden; background: oklch(1 0 0); display: flex; align-items: center; justify-content: center;">
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($story['user_name']); ?>" data-ai-hint="portrait person" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                    </div>
                    <span style="font-size: 0.875rem; font-weight: 500; text-align: center; color: hsl(var(--foreground));"><?php echo esc_html($story['user_name']); ?></span>
                    
                    <!-- Story Modal (hidden by default) -->
                    <!-- Updated modal to support multiple media items with navigation -->
                    <div class="story-modal" data-story-modal="<?php echo $index; ?>" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.95); z-index: 300; align-items: center; justify-content: center;">
                        <div class="story-modal-container" style="position: relative; width: 90%; max-width: 500px; aspect-ratio: 9/16;">
                            <button class="story-modal-close" style="position: absolute; top: 1rem; right: 1rem; background: rgba(0,0,0,0.5); border: none; color: white; font-size: 2rem; cursor: pointer; z-index: 301; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0;">×</button>
                            
                            <!-- Progress bars for multiple media -->
                            <?php 
                            $mediaItems = $story['media'] ?? array();
                            if (empty($mediaItems) && !empty($story['content_url'])) {
                                $mediaItems = array(array('type' => 'image', 'url' => $story['content_url']));
                            }
                            ?>
                            <?php if (count($mediaItems) > 1) : ?>
                            <div class="story-progress-bars" style="position: absolute; top: 1rem; left: 1rem; right: 5rem; display: flex; gap: 4px; z-index: 301;">
                                <?php foreach ($mediaItems as $mIndex => $mediaItem) : ?>
                                    <div class="progress-bar-bg" style="flex: 1; height: 3px; background: rgba(255,255,255,0.3); border-radius: 2px; overflow: hidden;">
                                        <div class="progress-bar-fill progress-bar-<?php echo $index; ?>-<?php echo $mIndex; ?>" style="height: 100%; width: 0%; background: white; transition: width 0.1s linear;"></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Media slides container -->
                            <div class="story-slides" data-story-id="<?php echo $index; ?>" data-current-slide="0" data-total-slides="<?php echo count($mediaItems); ?>" style="width: 100%; height: 100%; position: relative; touch-action: pan-y;">
                                <?php 
                                foreach ($mediaItems as $mIndex => $mediaItem) : 
                                    $mediaType = $mediaItem['type'] ?? 'image';
                                    $mediaUrl = khoshtip_convert_to_webp_url($mediaItem['url'] ?? '');
                                    $mediaLink = $mediaItem['link'] ?? '';
                                ?>
                                    <div class="story-slide" data-slide-index="<?php echo $mIndex; ?>" data-slide-link="<?php echo esc_url($mediaLink); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: <?php echo $mIndex === 0 ? 'block' : 'none'; ?>;">
                                        <?php if ($mediaType === 'video') : ?>
                                            <video class="story-video" src="<?php echo esc_url($mediaUrl); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius);" playsinline></video>
                                        <?php else : ?>
                                            <img src="<?php echo esc_url($mediaUrl); ?>" alt="Story" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius);" />
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Repositioned navigation arrows to the sides outside the image in desktop, reversed direction on mobile -->
                            <?php if (count($mediaItems) > 1) : ?>
                            <!-- Fixed RTL navigation: Right button goes forward, Left button goes back -->
                            <!-- Right navigation arrow (desktop only, shows on right side) - Goes NEXT for RTL -->
                            <button class="story-nav-desktop story-nav-next" style="position: absolute; right: -60px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); border: none; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.75rem; cursor: pointer; z-index: 299; opacity: 0.8; transition: opacity 0.2s, background 0.2s;">›</button>
                            
                            <!-- Left navigation arrow (desktop only, shows on left side) - Goes PREV for RTL -->
                            <button class="story-nav-desktop story-nav-prev" style="position: absolute; left: -60px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); border: none; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.75rem; cursor: pointer; z-index: 299; opacity: 0.8; transition: opacity 0.2s, background 0.2s;">‹</button>
                            
                            <!-- Mobile touch areas: Right side goes NEXT, Left side goes PREV for RTL -->
                            <!-- Mobile touch area - Right side for NEXT -->
                            <div class="story-nav-mobile story-nav-touch-next" style="position: absolute; right: 0; top: 0; bottom: 0; width: 65%; cursor: pointer; z-index: 298;"></div>
                            <!-- Mobile touch area - Left side for PREV -->
                            <div class="story-nav-mobile story-nav-touch-prev" style="position: absolute; left: 0; top: 0; bottom: 0; width: 35%; cursor: pointer; z-index: 298;"></div>
                            <?php endif; ?>
                            
                            <!-- دکمه لینک برای هر رسانه که دارای لینک است -->
                            <a href="#" class="story-media-link" target="_blank" style="display: none; position: absolute; bottom: 5rem; left: 50%; transform: translateX(-50%); background-color: rgba(255, 255, 255, 0.9); color: #333; padding: 0.75rem 1.5rem; border-radius: 9999px; font-weight: 600; text-decoration: none; white-space: nowrap; z-index: 300; font-size: 0.875rem; backdrop-filter: blur(10px); border: 2px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                مشاهده بیشتر ←
                            </a>
                            
                            <?php if (!empty($story['button_url'])) : ?>
                                <a href="<?php echo esc_url($story['button_url']); ?>" style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); background-color: hsl(var(--primary)); color: hsl(var(--primary-foreground)); padding: 1rem 2rem; border-radius: 9999px; font-weight: 700; text-decoration: none; white-space: nowrap; z-index: 300;">مشاهده کالکشن</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Updated animation with wavy red gradient colors -->
<style>
@keyframes story-ring-rotate {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.story-ring {
    animation: story-ring-rotate 3s ease infinite;
    will-change: background-position;
}

/* Added hover effects for navigation arrows */
.story-nav-desktop:hover {
    opacity: 1 !important;
    background: rgba(0,0,0,0.7) !important;
}

/* استایل دکمه لینک رسانه با انیمیشن */
.story-media-link {
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translate(-50%, 20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}

.story-media-link:hover {
    background-color: white !important;
    box-shadow: 0 6px 16px rgba(0,0,0,0.2) !important;
}

/* Desktop: show arrow buttons, hide touch areas */
@media (min-width: 768px) {
    .story-nav-desktop {
        display: flex !important;
    }
    .story-nav-mobile {
        display: none !important;
    }
}

/* Mobile: hide arrow buttons, show touch areas */
@media (max-width: 767px) {
    .stories-container {
        gap: 0.75rem !important;
    }
    
    .story-item {
        min-width: 80px !important;
    }
    
    .story-ring {
        width: 77px !important;
        height: 77px !important;
    }
    
    .story-item span {
        font-size: 0.75rem !important;
    }
    
    .story-modal-container {
        width: 76.5% !important;
        max-width: 425px !important;
    }
    
    /* Hide desktop navigation arrows on mobile */
    .story-nav-desktop {
        display: none !important;
    }
    
    /* Show mobile touch areas */
    .story-nav-mobile {
        display: block !important;
    }
    
    /* تنظیم اندازه دکمه لینک در موبایل */
    .story-media-link {
        bottom: 4rem !important;
        font-size: 0.813rem !important;
        padding: 0.625rem 1.25rem !important;
    }
}

/* Optimize animation performance */
@media (prefers-reduced-motion: reduce) {
    .story-ring {
        animation: none;
        background: linear-gradient(45deg, #ff6b6b, #c44569);
    }
    
    .story-media-link {
        animation: none;
    }
}
</style>

<!-- Enhanced JavaScript with touch/swipe support and keyboard navigation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStoryModal = null;
    let currentStoryIndex = null;
    let currentSlideIndex = 0;
    let autoPlayTimer = null;
    let progressTimer = null;
    const SLIDE_DURATION = 5000; // 5 seconds per slide (for images only)
    
    // Touch/swipe detection variables
    let touchStartX = 0;
    let touchEndX = 0;
    let touchStartY = 0;
    let touchEndY = 0;
    const SWIPE_THRESHOLD = 50;
    
    // Open story modal
    document.querySelectorAll('.story-item').forEach(item => {
        item.addEventListener('click', function() {
            const storyIndex = this.dataset.storyIndex;
            const modal = document.querySelector(`[data-story-modal="${storyIndex}"]`);
            const slidesContainer = modal.querySelector('.story-slides');
            
            currentStoryModal = modal;
            currentStoryIndex = storyIndex;
            currentSlideIndex = 0;
            
            modal.style.display = 'flex';
            slidesContainer.dataset.currentSlide = '0';
            
            // Show first slide
            showSlide(storyIndex, 0);
            
            // Start auto-play
            startAutoPlay(storyIndex);
            
            // Setup touch listeners
            setupTouchListeners(slidesContainer);
        });
    });
    
    // Close modal
    document.querySelectorAll('.story-modal-close').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeModal();
        });
    });
    
    // Click on modal background to close
    document.querySelectorAll('.story-modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    });
    
    // Navigation
    document.querySelectorAll('.story-nav-desktop.story-nav-prev').forEach(nav => {
        nav.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = this.closest('.story-modal');
            const storyIndex = modal.dataset.storyModal;
            navigateNext(storyIndex);
        });
    });
    
    document.querySelectorAll('.story-nav-desktop.story-nav-next').forEach(nav => {
        nav.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = this.closest('.story-modal');
            const storyIndex = modal.dataset.storyModal;
            navigatePrev(storyIndex);
        });
    });
    
    // Mobile navigation
    document.querySelectorAll('.story-nav-mobile.story-nav-touch-prev').forEach(nav => {
        nav.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = this.closest('.story-modal');
            const storyIndex = modal.dataset.storyModal;
            navigateNext(storyIndex);
        });
    });
    
    document.querySelectorAll('.story-nav-mobile.story-nav-touch-next').forEach(nav => {
        nav.addEventListener('click', function(e) {
            e.stopPropagation();
            const modal = this.closest('.story-modal');
            const storyIndex = modal.dataset.storyModal;
            navigatePrev(storyIndex);
        });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (!currentStoryModal) return;
        
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            navigateNext(currentStoryIndex);
        } else if (e.key === 'ArrowRight' || e.key === ' ') {
            e.preventDefault();
            navigatePrev(currentStoryIndex);
        } else if (e.key === 'Escape') {
            e.preventDefault();
            closeModal();
        }
    });
    
    function setupTouchListeners(element) {
        element.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });
        
        element.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            handleSwipe();
        }, { passive: true });
    }
    
    function handleSwipe() {
        const diffX = touchEndX - touchStartX;
        const diffY = touchEndY - touchStartY;
        
        // Only handle horizontal swipes (ignore vertical scrolling)
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > SWIPE_THRESHOLD) {
            if (diffX > 0) {
                // Swipe right - go to previous
                navigatePrev(currentStoryIndex);
            } else {
                // Swipe left - go to next
                navigateNext(currentStoryIndex);
            }
        }
    }
    
    function showSlide(storyIndex, slideIndex) {
        const modal = document.querySelector(`[data-story-modal="${storyIndex}"]`);
        const slides = modal.querySelectorAll('.story-slide');
        const slidesContainer = modal.querySelector('.story-slides');
        const totalSlides = parseInt(slidesContainer.dataset.totalSlides);
        const mediaLinkButton = modal.querySelector('.story-media-link');
        
        if (slideIndex < 0 || slideIndex >= totalSlides) return;
        
        // Hide all slides
        slides.forEach(slide => slide.style.display = 'none');
        
        // Show current slide
        slides[slideIndex].style.display = 'block';
        slidesContainer.dataset.currentSlide = slideIndex;
        currentSlideIndex = slideIndex;
        
        const currentSlide = slides[slideIndex];
        const slideLink = currentSlide.dataset.slideLink;
        
        if (slideLink && slideLink.trim() !== '') {
            mediaLinkButton.href = slideLink;
            mediaLinkButton.style.display = 'block';
        } else {
            mediaLinkButton.style.display = 'none';
        }
        
        const video = slides[slideIndex].querySelector('video');
        if (video) {
            video.currentTime = 0;
            video.play();
            
            // Remove previous event listeners to avoid duplicates
            video.onended = null;
            video.onended = function() {
                navigatePrev(storyIndex);
            };
        }
        
        // Pause other videos
        slides.forEach((slide, idx) => {
            if (idx !== slideIndex) {
                const v = slide.querySelector('video');
                if (v) {
                    v.pause();
                    v.currentTime = 0;
                }
            }
        });
        
        // Update progress bars
        const progressBars = modal.querySelectorAll('.progress-bar-fill');
        progressBars.forEach((bar, idx) => {
            if (idx < slideIndex) {
                bar.style.width = '100%';
                bar.style.transition = 'none';
            } else if (idx === slideIndex) {
                bar.style.width = '0%';
                bar.style.transition = 'none';
                // Use setTimeout to ensure transition is reset
                setTimeout(() => {
                    bar.style.transition = 'width 0.1s linear';
                    animateProgressBar(bar, video);
                }, 50);
            } else {
                bar.style.width = '0%';
                bar.style.transition = 'none';
            }
        });
    }
    
    function animateProgressBar(bar, video) {
        clearInterval(progressTimer);
        let progress = 0;
        
        // If video exists, use video duration, otherwise use default SLIDE_DURATION
        const duration = video && video.duration > 0 ? video.duration * 1000 : SLIDE_DURATION;
        const increment = 100 / (duration / 50);
        
        progressTimer = setInterval(() => {
            progress += increment;
            if (progress >= 100) {
                progress = 100;
                clearInterval(progressTimer);
            }
            bar.style.width = progress + '%';
        }, 50);
    }
    
    function startAutoPlay(storyIndex) {
        clearTimeout(autoPlayTimer);
        
        const modal = document.querySelector(`[data-story-modal="${storyIndex}"]`);
        const currentSlide = modal.querySelectorAll('.story-slide')[currentSlideIndex];
        const video = currentSlide.querySelector('video');
        
        // If there's a video, don't set timer - video onended event will handle it
        // Otherwise use default duration for images
        if (!video) {
            autoPlayTimer = setTimeout(() => {
                navigatePrev(storyIndex);
            }, SLIDE_DURATION);
        }
    }

    function navigateNext(storyIndex) {
        const modal = document.querySelector(`[data-story-modal="${storyIndex}"]`);
        const slidesContainer = modal.querySelector('.story-slides');
        const totalSlides = parseInt(slidesContainer.dataset.totalSlides);
        const nextIndex = currentSlideIndex + 1;
        
        if (nextIndex >= totalSlides) {
            closeModal();
            return;
        }
        
        showSlide(storyIndex, nextIndex);
        startAutoPlay(storyIndex);
    }
    
    function navigatePrev(storyIndex) {
        const prevIndex = currentSlideIndex - 1;
        
        if (prevIndex < 0) return;
        
        showSlide(storyIndex, prevIndex);
        startAutoPlay(storyIndex);
    }
    
    function closeModal() {
        clearTimeout(autoPlayTimer);
        clearInterval(progressTimer);
        
        if (currentStoryModal) {
            // Pause and reset all videos
            currentStoryModal.querySelectorAll('video').forEach(v => {
                v.pause();
                v.currentTime = 0;
            });
            currentStoryModal.style.display = 'none';
        }
        
        currentStoryModal = null;
        currentStoryIndex = null;
        currentSlideIndex = 0;
    }
});
</script>
