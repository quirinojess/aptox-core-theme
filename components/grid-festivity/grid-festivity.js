(function () {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const AUTOPLAY_INTERVAL = 5000;

  function initGridFestivityCarousel(section) {
    if (section.dataset.carouselInit === 'true') {
      return;
    }

    const track = section.querySelector('.grid-festivity-track');
    const prev = section.querySelector('.grid-festivity-nav--prev');
    const next = section.querySelector('.grid-festivity-nav--next');

    if (!track || !prev || !next) {
      return;
    }

    section.dataset.carouselInit = 'true';

    let autoplayTimer = null;
    let userPaused = false;
    let isInView = false;

    const getMaxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth);

    const hasOverflow = () => getMaxScroll() > 2;

    const shouldAutoplay = () =>
      !prefersReducedMotion && isInView && !userPaused && !document.hidden && hasOverflow();

    const stopAutoplay = () => {
      if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
      }
    };

    const startAutoplay = () => {
      stopAutoplay();

      if (!shouldAutoplay()) {
        return;
      }

      autoplayTimer = setInterval(autoplayStep, AUTOPLAY_INTERVAL);
    };

    const refreshAutoplay = () => {
      if (shouldAutoplay()) {
        startAutoplay();
      } else {
        stopAutoplay();
      }
    };

    const autoplayStep = () => {
      if (!shouldAutoplay()) {
        return;
      }

      const maxScroll = getMaxScroll();
      const atEnd = track.scrollLeft >= maxScroll - 5;

      if (atEnd) {
        track.scrollTo({
          left: 0,
          behavior: prefersReducedMotion ? 'auto' : 'smooth',
        });
        return;
      }

      track.scrollBy({
        left: track.clientWidth * 0.9,
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
      });
    };

    const updateNavState = () => {
      const maxScroll = getMaxScroll();
      const overflow = hasOverflow();

      prev.hidden = !overflow;
      next.hidden = !overflow;
      prev.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft >= maxScroll - 2;
    };

    const scrollTrack = (direction) => {
      track.scrollBy({
        left: direction * track.clientWidth * 0.9,
        behavior: 'smooth',
      });
    };

    const whenTrackReady = (callback) => {
      const images = track.querySelectorAll('img');
      let pending = 0;

      images.forEach((image) => {
        if (!image.complete) {
          pending += 1;
        }
      });

      const finish = () => {
        requestAnimationFrame(() => {
          requestAnimationFrame(callback);
        });
      };

      if (!pending) {
        finish();
        return;
      }

      const done = () => {
        pending -= 1;
        if (pending <= 0) {
          finish();
        }
      };

      images.forEach((image) => {
        if (image.complete) {
          return;
        }

        image.addEventListener('load', done, { once: true });
        image.addEventListener('error', done, { once: true });
      });
    };

    prev.addEventListener('click', () => scrollTrack(-1));
    next.addEventListener('click', () => scrollTrack(1));
    track.addEventListener('scroll', updateNavState, { passive: true });

    window.addEventListener('resize', () => {
      updateNavState();
      refreshAutoplay();
    });

    section.addEventListener('mouseenter', () => {
      userPaused = true;
      refreshAutoplay();
    });

    section.addEventListener('mouseleave', () => {
      userPaused = false;
      refreshAutoplay();
    });

    section.addEventListener('focusin', () => {
      userPaused = true;
      refreshAutoplay();
    });

    section.addEventListener('focusout', (event) => {
      if (!section.contains(event.relatedTarget)) {
        userPaused = false;
        refreshAutoplay();
      }
    });

    document.addEventListener('visibilitychange', () => {
      refreshAutoplay();
    });

    if ('ResizeObserver' in window) {
      const resizeObserver = new ResizeObserver(() => {
        updateNavState();
        refreshAutoplay();
      });

      resizeObserver.observe(track);
    }

    if ('IntersectionObserver' in window) {
      const viewObserver = new IntersectionObserver(
        (entries) => {
          isInView = entries.some((entry) => entry.isIntersecting);
          refreshAutoplay();
        },
        {
          threshold: 0.2,
        }
      );

      viewObserver.observe(section.closest('.home-lazy-section') || section);
    } else {
      isInView = true;
    }

    whenTrackReady(() => {
      updateNavState();
      refreshAutoplay();
    });
  }

  function initAllGridFestivityCarousels(root) {
    root.querySelectorAll('.grid-festivity--carousel').forEach(initGridFestivityCarousel);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initAllGridFestivityCarousels(document);
  });

  document.addEventListener('aptox:section-loaded', function (event) {
    if (event.detail?.root) {
      initAllGridFestivityCarousels(event.detail.root);
    }
  });
})();
