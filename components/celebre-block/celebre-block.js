(function () {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const AUTOPLAY_INTERVAL = 5000;

  function initCelebreBlockCarousel(section) {
    if (section.dataset.carouselInit === 'true') {
      return;
    }

    const track = section.querySelector('.celebre-block-track');
    const prev = section.querySelector('.celebre-block-nav--prev');
    const next = section.querySelector('.celebre-block-nav--next');

    if (!track || !prev || !next) {
      return;
    }

    section.dataset.carouselInit = 'true';

    let autoplayTimer = null;
    let isInView = false;

    const getMaxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth);
    const hasOverflow = () => getMaxScroll() > 2;
    const shouldAutoplay = () =>
      !prefersReducedMotion && isInView && !document.hidden && hasOverflow();

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
      prev.disabled = !overflow || track.scrollLeft <= 2;
      next.disabled = !overflow || track.scrollLeft >= maxScroll - 2;
    };

    prev.addEventListener('click', function () {
      track.scrollBy({
        left: -(track.clientWidth * 0.9),
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
      });
    });

    next.addEventListener('click', function () {
      track.scrollBy({
        left: track.clientWidth * 0.9,
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
      });
    });

    track.addEventListener('scroll', updateNavState, { passive: true });

    if ('ResizeObserver' in window) {
      const resizeObserver = new ResizeObserver(updateNavState);
      resizeObserver.observe(track);
    }

    if ('IntersectionObserver' in window) {
      const viewObserver = new IntersectionObserver(
        function (entries) {
          isInView = entries.some(function (entry) {
            return entry.isIntersecting;
          });
          refreshAutoplay();
        },
        { threshold: 0.2 }
      );

      viewObserver.observe(section);
    } else {
      isInView = true;
    }

    document.addEventListener('visibilitychange', refreshAutoplay);

    updateNavState();
    refreshAutoplay();
  }

  function initAllCelebreBlockCarousels(root) {
    root.querySelectorAll('.celebre-block--carousel').forEach(initCelebreBlockCarousel);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initAllCelebreBlockCarousels(document);
  });
})();
