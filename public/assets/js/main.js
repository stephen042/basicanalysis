/* ============================================================
    [Master Scripts]

    Theme Name:     XTrady 
    Theme URL:      https://xtrady.vercel.app/xtrady
    Description:    XTrady - Forex & Stock Broker Trading Investments HTML Template
    Version:        1.0.0

============================================================== */
/*
========================================
*********** TABLE OF CONTENTS ********** 
    1. Roadmap Line JS  
    2. Navbar Shrink JS  
    3. Navbar Links Active JS  
    4. Odometer Counter Up JS  
    5. Scroll Back to Top JS  
    6. Services Slider JS  
    7. Pricing Slider JS  
    8. Team Slider JS  
    9. Testimonials Slider JS  
    10. Text Slide JS  
    11. Scroll Reveal JS  
    12. Skill Progress JS  
    13. Preloader JS  
    14. TradingView Ticker Tape JS  
    15. Fancybox JS  
    16. Play Button Animation JS  
    17. TradingView Widget JS  
    18. Accordion JS  
    19. Market View Widget JS   
    20. Contact Form ajax Js  
========================================*/

'use strict';
(function ($) {

  gsap.registerPlugin(ScrollTrigger);

  /* ========================================
    Roadmap Line Js
  ======================================== */
  if ($(".roadmap-features").length > 0) {
    gsap.to(".roadmap-line", {
      height: "100%",
      duration: 1,
      ease: "none",
      scrollTrigger: {
        trigger: ".roadmap-features",
        start: "top 15%",
        end: "bottom 80%",
        scrub: true,
      }
    });
  }

  /* ========================================
   Navbar shrink Js
  ======================================== */
  $(window).on('scroll', function () {
    var wScroll = $(this).scrollTop();
    if (wScroll > 1) {
      $('.navbar-main').addClass('navbar-shrink');
    } else {
      $('.navbar-main').removeClass('navbar-shrink');
    };
  });

  /* ========================================
     Navbar Links Active  Js
  ======================================== */
  if ($('.navbar-nav').length > 0) {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link, .dropdown-menu .dropdown-item');

    const removeActiveClass = () => {
      navLinks.forEach((link) => link.classList.remove('active'));
    };

    const setActiveLink = () => {
      const currentPath = window.location.pathname;
      removeActiveClass();

      navLinks.forEach((link) => {
        const linkPath = link.getAttribute('href');
        if (linkPath && currentPath.endsWith(linkPath)) {
          link.classList.add('active');

          const parentDropdown = link.closest('.dropdown-menu')?.previousElementSibling;
          if (parentDropdown) {
            parentDropdown.classList.add('active');
          }
        }
      });
    };
    setActiveLink();
  }



  /* ========================================
    Odometer Counter Up Js
  ======================================== */
  // data-odometer-final
  if ($('.odometer').length > 0) {
    $(window).on('scroll', function () {
      $('.odometer').each(function () {
        if ($(this).isInViewport()) {
          if (!$(this).data('odometer-started')) {
            $(this).data('odometer-started', true);
            this.innerHTML = $(this).data('odometer-final');
          }
        }
      });
    });
  }
  // isInViewport helper function
  $.fn.isInViewport = function () {
    let elementTop = $(this).offset().top;
    let elementBottom = elementTop + $(this).outerHeight();
    let viewportTop = $(window).scrollTop();
    let viewportBottom = viewportTop + $(window).height();
    return elementBottom > viewportTop && elementTop < viewportBottom;
  };

  /* ========================================
     Scroll back to top  Js
   ======================================== */
  if ($('.progress-wrap').length > 0) {
    const progressPath = document.querySelector('.progress-wrap path');
    const pathLength = progressPath.getTotalLength();

    // Set up the initial stroke styles
    progressPath.style.transition = 'none';
    progressPath.style.strokeDasharray = `${pathLength} ${pathLength}`;
    progressPath.style.strokeDashoffset = pathLength;
    progressPath.getBoundingClientRect();

    // Set transition for stroke-dashoffset
    progressPath.style.transition = 'stroke-dashoffset 10ms linear';

    const updateProgress = () => {
      const scroll = $(window).scrollTop();
      const height = $(document).height() - $(window).height();
      const progress = pathLength - (scroll * pathLength / height);
      progressPath.style.strokeDashoffset = progress;
    };

    updateProgress();
    $(window).on('scroll', updateProgress);

    const offset = 50;
    const duration = 550;

    $(window).on('scroll', () => {
      $('.progress-wrap').toggleClass('active-progress', $(window).scrollTop() > offset);
    });

    $('.progress-wrap').on('click', (event) => {
      event.preventDefault();
      $('html, body').animate({ scrollTop: 0 }, duration);
    });
  }

  /* ========================================
    Services slider Js
  ======================================== */
  if ($('.services-slider').length > 0) {
    var servicesSwiper = new Swiper('.services-slider', {
      slidesPerView: 2,
      spaceBetween: 24,
      speed: 700,
      autoplay: {
        delay: 4500,
        disableOnInteraction: false,
      },
      autoplay: false,
      pagination: false,
      navigation: {
        nextEl: ".services-next",
        prevEl: ".services-prev",
      },
      breakpoints: {
        0: { slidesPerView: 1 },
        768: { slidesPerView: 1 },
        1024: { slidesPerView: 2, },
        1400: { slidesPerView: 3 },
      },
    });
  }
  /* ========================================
    Pricing slider Js
  ======================================== */
  if ($('.pricing-slide').length > 0) {
    var servicesSwiper = new Swiper('.pricing-slide', {
      slidesPerView: 2,
      spaceBetween: 24,
      speed: 700,
      autoplay: {
        delay: 4500,
        disableOnInteraction: false,
      },
      autoplay: false,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      breakpoints: {
        0: { slidesPerView: 1 },
        768: { slidesPerView: 1 },
        1024: { slidesPerView: 2, },
        1400: { slidesPerView: 3 },
      },
    });
  }
  /* ========================================
    Team slider Js
  ======================================== */
  if ($('.team-slide').length > 0) {
    var servicesSwiper = new Swiper('.team-slide', {
      slidesPerView: 2,
      spaceBetween: 24,
      speed: 700,
      autoplay: {
        delay: 4500,
        disableOnInteraction: false,
      },
      autoplay: false,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      breakpoints: {
        0: { slidesPerView: 1 },
        500: { slidesPerView: 2 },
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 3, },
        1400: { slidesPerView: 4 },
      },
    });
  }

  /* ========================================
    Testimonials slider Js
  ======================================== */
  if ($('.testimonial-slide').length > 0) {
    var testimonialSwiper = new Swiper('.testimonial-slide', {
      slidesPerView: 2,
      spaceBetween: 24,
      speed: 700,
      autoplay: {
        delay: 4500,
        disableOnInteraction: false,
      },
      autoplay: false,
      pagination: false,
      navigation: {
        nextEl: ".testi-next",
        prevEl: ".testi-prev",
      },
      breakpoints: {
        0: { slidesPerView: 1 },
        500: { slidesPerView: 1 },
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 2, },
        1400: { slidesPerView: 3 },
      },
    });
  }

  /* ========================================
    Text Slide Js
  ======================================== */
  if ($('.text-slide').length > 0) {
    const testSlideSwiper = new Swiper('.text-slide', {
      loop: true,
      slidesPerView: 'auto',
      centeredSlides: true,
      allowTouchMove: false,
      spaceBetween: 24,
      speed: 4000,
      autoplay: {
        delay: 0,
        disableOnInteraction: false,
      },
    });
  }

  /* ========================================
    Scroll Reveal Js
  ======================================== */
  const scrollReveal = ScrollReveal({
    origin: 'top', distance: '60px', duration: 1300, delay: 100, mobile: false
  });
  const revealConfig = {
    base: { delay: 60, interval: 100, mobile: false },
    top: { distance: '60px', origin: 'top' },
    left: { origin: 'left' },
    right: { origin: 'right' },
    bottom: { origin: 'bottom' },
    scaleUp: { scale: 0.85 }
  };

  ScrollReveal().reveal('.top-reveal', {
    ...revealConfig.base,
    ...revealConfig.top
  });

  ScrollReveal().reveal('.left-reveal', {
    ...revealConfig.base,
    ...revealConfig.left
  });

  ScrollReveal().reveal('.right-reveal', {
    ...revealConfig.base,
    ...revealConfig.right
  });

  ScrollReveal().reveal('.bottom-reveal', {
    ...revealConfig.base,
    ...revealConfig.bottom
  });

  ScrollReveal().reveal('.scaleUp', {
    ...revealConfig.base,
    ...revealConfig.scaleUp
  });

  /* ========================================
      Skill Progress Js
  ======================================== */
  if ($('.skill-progress').length > 0) {
    function animateNumbers(element) {
      const target = +element.getAttribute('data-target');
      const duration = 1500; // 1.5 second
      const step = target / (duration / 20);
      let current = 0;
      const interval = setInterval(() => {
        current += step;
        if (current >= target) {
          current = target;
          clearInterval(interval);
        }
        element.textContent = Math.round(current) + "%";
      }, 20);
    }
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const progressBar = entry.target.querySelector('.progress-bar');
            const percentageText = entry.target.querySelector('.percentage');
            const targetWidth = percentageText.getAttribute('data-target') + '%';
            progressBar.style.width = targetWidth;
            progressBar.setAttribute('aria-valuenow', targetWidth);
            animateNumbers(percentageText);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 }
    );
    document.querySelectorAll('.skill-progress').forEach((item) => observer.observe(item));
  }

  /* ========================================
      Preloader Js
  ======================================== */
  window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    preloader.style.transition = 'height 0.5s, opacity 1s';
    preloader.style.opacity = '0';
    preloader.style.height = '0';
    preloader.style.borderBottomLeftRadius = '100%';
    preloader.style.borderBottomRightRadius = '100%';
    setTimeout(() => {
      preloader.style.display = 'none';
    }, 500);
  });

  /* ========================================
    TradingView Ticker Tape Js
  ======================================== */
  if (document.getElementById('tradingview-ticker-tape')) {
    (function () {
      const CONFIG = {
        symbols: [
          { "proName": "FX_IDC:EURUSD", "title": "EUR/USD" },
          { "proName": "FX_IDC:GBPUSD", "title": "GBP/USD" },
          { "proName": "FX_IDC:USDJPY", "title": "USD/JPY" },
          { "proName": "FX_IDC:USDCHF", "title": "USD/CHF" },
          { "proName": "FX_IDC:AUDUSD", "title": "AUD/USD" },
          { "proName": "FX_IDC:USDCAD", "title": "USD/CAD" },
          { "proName": "FX_IDC:NZDUSD", "title": "NZ$/USD" }
        ],
        settings: {
          showSymbolLogo: true,
          colorTheme: "dark",
          isTransparent: true,
          displayMode: "adaptive",
          locale: "en"
        }
      };

      function initTickerTape() {
        const container = document.getElementById('tradingview-ticker-tape');

        // Create script element
        const script = document.createElement('script');
        script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js';
        script.async = true;
        script.innerHTML = JSON.stringify({
          symbols: CONFIG.symbols,
          ...CONFIG.settings
        });

        // Clear and append
        container.innerHTML = '';
        container.appendChild(script);
      }

      function handleError() {
        const container = document.getElementById('tradingview-ticker-tape');
        container.innerHTML = '<p class="market-data-error">Market data unavailable</p>';
      }

      // Initialize
      try {
        if (document.readyState !== 'loading') {
          initTickerTape();
        } else {
          document.addEventListener('DOMContentLoaded', initTickerTape);
        }
      } catch (error) {
        console.error('Ticker tape initialization error:', error);
        handleError();
      }
    })();
  }

  /* ========================================
    Fancybox Js
  ======================================== */
  Fancybox.bind('[data-fancybox]', {});

  /* ========================================
    Play Button Animation Js
  ======================================== */
  if ($(".video-container").length > 0) {
    class PlayButtonAnimator {
      constructor(videoContainer, playButton) {
        this.videoContainer = videoContainer;
        this.playButton = playButton;

        this.initEvents();
      }

      initEvents() {
        this.videoContainer.addEventListener("mousemove", this.handleMouseMove.bind(this));
        this.videoContainer.addEventListener("mouseleave", this.handleMouseLeave.bind(this));
        this.videoContainer.addEventListener("mouseover", this.handleMouseOver.bind(this));
      }

      handleMouseMove(event) {
        const containerRect = this.videoContainer.getBoundingClientRect();
        const mouseX = event.clientX - containerRect.left;
        const mouseY = event.clientY - containerRect.top;

        const buttonWidth = this.playButton.offsetWidth;
        const buttonHeight = this.playButton.offsetHeight;
        const buttonX = mouseX - buttonWidth / 2;
        const buttonY = mouseY - buttonHeight / 2;

        const maxButtonX = containerRect.width - buttonWidth;
        const maxButtonY = containerRect.height - buttonHeight;
        this.playButton.style.left = Math.min(Math.max(buttonX, 0), maxButtonX) + "px";
        this.playButton.style.top = Math.min(Math.max(buttonY, 0), maxButtonY) + "px";
      }

      handleMouseLeave() {
        setTimeout(() => {
          this.playButton.style.left = "50%";
          this.playButton.style.top = "50%";
          this.playButton.style.transform = "translate(-50%, -50%) scale(1)";
          this.playButton.style.transition = "all 0.3s ease-out";
        }, 50);
      }

      handleMouseOver() {
        this.playButton.style.transition = "transform ease-out 0.3s";
        this.playButton.style.transform = "scale(1.1)";
      }
    }

    document.querySelectorAll('.video-container').forEach(container => {
      const playButton = container.querySelector('.play-button');
      new PlayButtonAnimator(container, playButton);
    });
  }

  /* ========================================
    TradingView Widget Js
  ======================================== */
  if ($(".tradingview-widget-container").length > 0) {
    document.addEventListener('DOMContentLoaded', function () {
      const script = document.createElement('script');
      script.src = 'https://d33t3vvu2t2yu5.cloudfront.net/tv.js';
      script.async = true;
      script.onload = function () {
        setTimeout(() => {
          const containers = document.querySelectorAll('.tradingview-widget-container');

          containers.forEach((container, index) => {
            if (container && container.parentNode) {
              if (!container.id) {
                container.id = `tradingview-widget-container-${index + 1}`;
              }

              new TradingView.widget({
                autosize: true,
                symbol: "FX_IDC:EURUSD",
                interval: "D",
                timezone: "EUR/USD",
                colorTheme: "dark",
                theme: "dark",
                style: "1",
                locale: "en",
                toolbar_bg: "rgba(0, 0, 0, 1)",
                hide_top_toolbar: true,
                save_image: false,
                hideideas: true,
                container_id: container.id
              });
            }
          });
        }, 100);
      };
      document.body.appendChild(script);
    });
  }

  /* ========================================
    Accordion Js
  ======================================== */
  if ($('.at-title').length > 0) {
    $(".at-title").on("click", function () {
      $(this)
        .toggleClass("active")
        .next(".at-tab")
        .slideToggle()
        .parent()
        .siblings()
        .find(".at-tab")
        .slideUp()
        .prev()
        .removeClass("active");
    });
  }

  /* ========================================
    Market View Widget Js
  ======================================== */
  if ($("#marketview-widget-placeholder").length > 0) {
    const container = document.getElementById('marketview-widget-placeholder');
    // Create widget container
    const widgetContainer = document.createElement('div');
    widgetContainer.className = 'marketview-widget-container';

    // Create widget element
    const widget = document.createElement('div');
    widget.className = 'marketview-widget-container__widget';
    widgetContainer.appendChild(widget);


    // Add to document
    container.appendChild(widgetContainer);

    // Create and configure script
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-market-overview.js';
    script.async = true;
    script.innerHTML = JSON.stringify({
      "colorTheme": "dark",
      "dateRange": "12M",
      "showChart": true,
      "locale": "en",
      "largeChartUrl": "",
      "isTransparent": true,
      "showSymbolLogo": true,
      "showFloatingTooltip": false,
      "width": "100%",
      "height": "550",
      "plotLineColorGrowing": "rgba(41, 98, 255, 1)",
      "plotLineColorFalling": "rgba(41, 98, 255, 1)",
      "gridLineColor": "rgba(42, 46, 57, 0)",
      "scaleFontColor": "rgba(219, 219, 219, 1)",
      "belowLineFillColorGrowing": "rgba(41, 98, 255, 0.12)",
      "belowLineFillColorFalling": "rgba(41, 98, 255, 0.12)",
      "belowLineFillColorGrowingBottom": "rgba(41, 98, 255, 0)",
      "belowLineFillColorFallingBottom": "rgba(41, 98, 255, 0)",
      "symbolActiveColor": "rgba(41, 98, 255, 0.12)",
      "tabs": [
        {
          "title": "Forex",
          "symbols": [
            { "s": "FX:EURUSD", "d": "EUR to USD" },
            { "s": "FX:GBPUSD", "d": "GBP to USD" },
            { "s": "FX:USDJPY", "d": "USD to JPY" },
            { "s": "FX:USDCHF", "d": "USD to CHF" },
            { "s": "FX:AUDUSD", "d": "AUD to USD" },
            { "s": "FX:USDCAD", "d": "USD to CAD" }
          ]
        },
        {
          "title": "Indices",
          "symbols": [
            { "s": "FOREXCOM:SPXUSD", "d": "S&P 500 Index" },
            { "s": "FOREXCOM:NSXUSD", "d": "US 100 Cash CFD" },
            { "s": "FOREXCOM:DJI", "d": "Dow Jones Industrial Average Index" },
            { "s": "INDEX:NKY", "d": "Japan 225" },
            { "s": "INDEX:DEU40", "d": "DAX Index" },
            { "s": "FOREXCOM:UKXGBP", "d": "FTSE 100 Index" }
          ]
        },
        {
          "title": "Futures",
          "symbols": [
            { "s": "BMFBOVESPA:ISP1!", "d": "S&P 500 Index Futures" },
            { "s": "BMFBOVESPA:EUR1!", "d": "Euro Futures" },
            { "s": "PYTH:WTI3!", "d": "WTI CRUDE OIL" },
            { "s": "BMFBOVESPA:ETH1!", "d": "Hydrous ethanol" },
            { "s": "BMFBOVESPA:CCM1!", "d": "Corn" }
          ]
        },
        {
          "title": "Bonds",
          "symbols": [
            { "s": "EUREX:FGBL1!", "d": "Euro Bund" },
            { "s": "EUREX:FBTP1!", "d": "Euro BTP" },
            { "s": "EUREX:FGBM1!", "d": "Euro BOBL" }
          ]
        }
      ]
    });
    widget.appendChild(script);
  }


  /* ============== Contact Form ajax Js =================== */
  if ($('#contact-form').length > 0) {
    $("#contact-form").submit(function (e) {
      e.preventDefault();

      var form = $(this);
      var formData = form.serialize();
      var messageDiv = form.find(".form-message");

      $.ajax({
        type: "POST",
        url: form.attr("action"),
        data: formData,
        success: function (response) {
          messageDiv.html('<div class="alert alert-success">' + response + '</div>');
          form[0].reset();
        },
        error: function (xhr) {
          let errorMsg = "Oops! Something went wrong. Please try again.";
          if (xhr.responseText) {
            errorMsg = xhr.responseText;
          }
          messageDiv.html('<div class="alert alert-danger">' + errorMsg + '</div>');
        },
      });
    });
  }



})(jQuery);
