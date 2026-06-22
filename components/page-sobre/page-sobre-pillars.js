(function () {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const mobileCarousel = window.matchMedia('(max-width: 768px)');

  function initPillarsCarousel(section) {
    if (section.dataset.carouselInit === 'true') {
      return;
    }

    const track = section.querySelector('.info-grid-inner');
    const prev = section.querySelector('.page-sobre-pillars-nav--prev');
    const next = section.querySelector('.page-sobre-pillars-nav--next');

    if (!track || !prev || !next) {
      return;
    }

    section.dataset.carouselInit = 'true';

    const getMaxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth);
    const hasOverflow = () => mobileCarousel.matches && getMaxScroll() > 2;

    const getScrollStep = () => {
      const item = track.querySelector('.info-grid-item');

      if (!item) {
        return track.clientWidth * 0.9;
      }

      const gap = parseFloat(window.getComputedStyle(track).columnGap || window.getComputedStyle(track).gap || '0');

      return item.offsetWidth + gap;
    };

    const updateNavState = () => {
      if (!mobileCarousel.matches) {
        prev.hidden = true;
        next.hidden = true;
        return;
      }

      const maxScroll = getMaxScroll();
      const overflow = hasOverflow();

      prev.hidden = !overflow;
      next.hidden = !overflow;
      prev.disabled = !overflow || track.scrollLeft <= 2;
      next.disabled = !overflow || track.scrollLeft >= maxScroll - 2;
    };

    const scrollTrack = (direction) => {
      if (!mobileCarousel.matches) {
        return;
      }

      track.scrollBy({
        left: direction * getScrollStep(),
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
      });
    };

    prev.addEventListener('click', () => scrollTrack(-1));
    next.addEventListener('click', () => scrollTrack(1));
    track.addEventListener('scroll', updateNavState, { passive: true });
    window.addEventListener('resize', updateNavState);
    mobileCarousel.addEventListener('change', updateNavState);

    if ('ResizeObserver' in window) {
      const resizeObserver = new ResizeObserver(updateNavState);
      resizeObserver.observe(track);
    }

    updateNavState();
  }

  function initAll(root) {
    root.querySelectorAll('.page-sobre-pillars-layout').forEach(initPillarsCarousel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initAll(document));
  } else {
    initAll(document);
  }
})();
