// Main JavaScript for Khoshtip Kocholo Theme
;(() => {
  // Wait for DOM and jQuery to be ready
  if (typeof window.jQuery === "undefined") {
    console.error("jQuery is not loaded")
    return
  }

  const $ = window.jQuery
  const Swiper = window.Swiper

  $(document).ready(() => {
    // Mobile Menu Toggle
    $("[data-mobile-menu-trigger]").on("click", () => {
      $("[data-mobile-menu]").css("right", "0")
    })

    $("[data-mobile-menu-close]").on("click", () => {
      $("[data-mobile-menu]").css("right", "-100%")
    })

    // Search Modal Toggle
    $("[data-search-trigger]").on("click", () => {
      $("[data-search-modal]").css("display", "flex")
    })

    $("[data-search-modal-close]").on("click", () => {
      $("[data-search-modal]").css("display", "none")
    })

    // Close modals on outside click
    $("[data-search-modal]").on("click", function (e) {
      if (e.target === this) {
        $(this).css("display", "none")
      }
    })

    // Countdown Timer
    function initCountdown() {
      const countdownEl = $("#countdown-timer")
      if (countdownEl.length === 0) return

      const targetTimestamp = Number.parseInt(countdownEl.data("target-timestamp"))

      if (!targetTimestamp || isNaN(targetTimestamp)) {
        console.error("[v0] Invalid countdown timestamp")
        return
      }

      let intervalId

      function updateCountdown() {
        const now = new Date().getTime()
        const distance = targetTimestamp - now

        console.log("[v0] Timer update - Distance:", distance, "Target:", targetTimestamp, "Now:", now)

        if (distance < 0) {
          console.log("[v0] Timer expired - hiding sales section")
          $(".sales-section").fadeOut(500, function () {
            localStorage.setItem("k_sale_ended_at", targetTimestamp)
            $(this).remove()
          })
          if (intervalId) {
            clearInterval(intervalId)
            intervalId = null
          }
          return
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24))
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))
        const seconds = Math.floor((distance % (1000 * 60)) / 1000)

        if (days > 0) {
          $(".countdown-days-wrapper").show()
          $(".countdown-days").text(String(days).padStart(2, "0"))
        } else {
          $(".countdown-days-wrapper").hide()
        }

        $(".countdown-hours").text(String(hours).padStart(2, "0"))
        $(".countdown-minutes").text(String(minutes).padStart(2, "0"))
        $(".countdown-seconds").text(String(seconds).padStart(2, "0"))
      }

      const saleEndedAt = localStorage.getItem("k_sale_ended_at")
      if (saleEndedAt && Number.parseInt(saleEndedAt) === targetTimestamp) {
        console.log("[v0] Sale already ended in previous session - hiding immediately")
        $(".sales-section").hide().remove()
        return
      }

      updateCountdown()
      intervalId = setInterval(updateCountdown, 1000)
    }

    initCountdown()

    $("[data-accordion-trigger]").on("click", function () {
      const $button = $(this)
      const $item = $button.closest(".accordion-item")
      const $content = $item.find("[data-accordion-content]")
      const $icon = $button.find(".accordion-icon")
      const isActive = $button.hasClass("active")

      if (isActive) {
        // Close current accordion
        $button.removeClass("active")
        $content.slideUp(300)
        $icon.css("transform", "rotate(0deg)")
      } else {
        // Close all other accordions
        $("[data-accordion-trigger]").not($button).removeClass("active")
        $("[data-accordion-trigger]").not($button).find(".accordion-icon").css("transform", "rotate(0deg)")
        $("[data-accordion-content]").not($content).slideUp(300)

        // Open current accordion
        $button.addClass("active")
        $content.slideDown(300, () => {
          // Initialize Swiper after accordion opens
          initAccordionSwiper($content)
        })
        $icon.css("transform", "rotate(180deg)")
      }
    })

    // Initialize Hero Swiper
    if ($(".hero-swiper").length && typeof Swiper !== "undefined") {
      new Swiper(".hero-swiper", {
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
      })
    }

    // Initialize Sales Swiper with custom hover behavior
    if ($(".sales-swiper").length && typeof Swiper !== "undefined") {
      const salesSwiper = new Swiper(".sales-swiper", {
        slidesPerView: 2,
        spaceBetween: 16,
        loop: true,
        speed: 3000,
        autoplay: {
          delay: 0,
          disableOnInteraction: false,
        },
        freeMode: true,
        freeModeMomentum: false,
        breakpoints: {
          640: {
            slidesPerView: 2.5,
            spaceBetween: 20,
          },
          768: {
            slidesPerView: 3,
            spaceBetween: 20,
          },
          1024: {
            slidesPerView: 4,
            spaceBetween: 20,
          },
          1280: {
            slidesPerView: 5,
            spaceBetween: 20,
          },
        },
      })

      let hoverTimeout
      let isHovering = false
      let isTouching = false

      $(".sales-swiper").on("mouseenter", () => {
        isHovering = true
        salesSwiper.autoplay.stop()
        if (hoverTimeout) {
          clearTimeout(hoverTimeout)
          hoverTimeout = null
        }
      })

      $(".sales-swiper").on("mouseleave", () => {
        isHovering = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            salesSwiper.autoplay.start()
          }
        }, 3000)
      })

      $(".sales-swiper").on("touchstart", () => {
        isTouching = true
        salesSwiper.autoplay.stop()
        if (hoverTimeout) {
          clearTimeout(hoverTimeout)
          hoverTimeout = null
        }
      })

      $(".sales-swiper").on("touchend", () => {
        isTouching = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            salesSwiper.autoplay.start()
          }
        }, 3000)
      })

      $(".sales-swiper").on("touchcancel", () => {
        isTouching = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            salesSwiper.autoplay.start()
          }
        }, 3000)
      })
    }

    if (typeof Swiper !== "undefined") {
      const $firstAccordionContent = $(".accordion-item:first-child [data-accordion-content]")
      if ($firstAccordionContent.length && $firstAccordionContent.is(":visible")) {
        initAccordionSwiper($firstAccordionContent)
      }
    }

    // Initialize Latest Products Swiper
    if ($(".latest-products-section .latest-products-swiper").length && typeof Swiper !== "undefined") {
      const $latestSection = $(".latest-products-section")
      if ($latestSection.find(".accordion-item").hasClass("active")) {
        // Rotate icon to show it's open
        $latestSection.find(".accordion-icon").css("transform", "rotate(180deg)")
      }
    }

    // Initialize Blog Swiper (Mobile)
    if ($(".blog-swiper").length && typeof Swiper !== "undefined") {
      new Swiper(".blog-swiper", {
        slidesPerView: 2,
        spaceBetween: 15,
        centeredSlides: false,
        breakpoints: {
          640: {
            slidesPerView: 2.5,
          },
        },
      })
    }

    // Initialize Accordion Swiper with custom hover behavior
    function initAccordionSwiper($content) {
      if (typeof Swiper === "undefined") return

      const swiperEl = $content.find(".swiper")[0]
      if (!swiperEl || swiperEl.swiper) return

      const accordionSwiper = new Swiper(swiperEl, {
        slidesPerView: 2,
        spaceBetween: 16,
        loop: true,
        speed: 3000,
        autoplay: {
          delay: 0,
          disableOnInteraction: false,
        },
        freeMode: true,
        freeModeMomentum: false,
        breakpoints: {
          640: {
            slidesPerView: 2.5,
            spaceBetween: 20,
          },
          768: {
            slidesPerView: 3,
            spaceBetween: 20,
          },
          1024: {
            slidesPerView: 4,
            spaceBetween: 20,
          },
          1280: {
            slidesPerView: 5,
            spaceBetween: 20,
          },
        },
      })

      let hoverTimeout
      let isHovering = false
      let isTouching = false

      $(swiperEl).on("mouseenter", () => {
        isHovering = true
        accordionSwiper.autoplay.stop()
        if (hoverTimeout) {
          clearTimeout(hoverTimeout)
          hoverTimeout = null
        }
      })

      $(swiperEl).on("mouseleave", () => {
        isHovering = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            accordionSwiper.autoplay.start()
          }
        }, 3000)
      })

      $(swiperEl).on("touchstart", () => {
        isTouching = true
        accordionSwiper.autoplay.stop()
        if (hoverTimeout) {
          clearTimeout(hoverTimeout)
          hoverTimeout = null
        }
      })

      $(swiperEl).on("touchend", () => {
        isTouching = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            accordionSwiper.autoplay.start()
          }
        }, 3000)
      })

      $(swiperEl).on("touchcancel", () => {
        isTouching = false
        if (hoverTimeout) clearTimeout(hoverTimeout)

        hoverTimeout = setTimeout(() => {
          if (!isHovering && !isTouching) {
            accordionSwiper.autoplay.start()
          }
        }, 3000)
      })
    }
  })
})()
