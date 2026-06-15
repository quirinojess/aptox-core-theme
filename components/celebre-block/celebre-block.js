(function () {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const AUTOPLAY_INTERVAL = 5000;
  const FADE_DURATION = 360;

  function initCelebreBlockCarousel(section) {
    if (section.dataset.carouselInit === 'true') {
      return;
    }

    if (section.hidden || section.classList.contains('celebre-block--hidden')) {
      return;
    }

    const track = section.querySelector('.celebre-block-track');
    const prev = section.querySelector('.celebre-block-carousel__nav .celebre-block-nav--prev');
    const next = section.querySelector('.celebre-block-carousel__nav .celebre-block-nav--next');

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
    root.querySelectorAll('.celebre-block--carousel:not(.celebre-block--hidden):not([hidden])').forEach(initCelebreBlockCarousel);
  }

  function mountSeasonFilter(season) {
    const filterBar = season.querySelector('.celebre-season-filter-bar');

    if (!filterBar) {
      return;
    }

    const activePanel = season.querySelector('.celebre-block:not(.celebre-block--hidden):not([hidden])');

    if (!activePanel) {
      return;
    }

    const carouselControls = activePanel.querySelector('.celebre-block-carousel__controls');

    if (carouselControls) {
      const nav = carouselControls.querySelector('.celebre-block-carousel__nav');

      if (nav && filterBar.nextElementSibling !== nav) {
        carouselControls.insertBefore(filterBar, nav);
      } else if (!nav && filterBar.parentElement !== carouselControls) {
        carouselControls.appendChild(filterBar);
      }

      return;
    }

    const flatSlot = activePanel.querySelector('[data-celebre-filter-slot]');

    if (flatSlot && filterBar.parentElement !== flatSlot) {
      flatSlot.prepend(filterBar);
    }
  }

  function switchFestivityPanel(season, key, buttons, panels) {
    const next = season.querySelector('.celebre-block[data-festivity-key="' + key + '"]');
    const current = season.querySelector('.celebre-block:not(.celebre-block--hidden):not([hidden])');

    if (!next || next === current) {
      return;
    }

    if (!prefersReducedMotion && season.dataset.filterTransition === 'true') {
      return;
    }

    buttons.forEach(function (item) {
      const isActive = item.dataset.festivityKey === key;
      item.classList.toggle('is-active', isActive);
      item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    function revealPanel() {
      panels.forEach(function (panel) {
        const isActive = panel.dataset.festivityKey === key;

        panel.classList.toggle('celebre-block--hidden', !isActive);
        panel.hidden = !isActive;
        panel.classList.remove('is-leaving', 'is-entering');
      });

      if (prefersReducedMotion) {
        if (next.classList.contains('celebre-block--carousel')) {
          initCelebreBlockCarousel(next);
        }

        mountSeasonFilter(season);
        season.dataset.filterTransition = 'false';
        return;
      }

      next.classList.add('is-entering');

      if (next.classList.contains('celebre-block--carousel')) {
        initCelebreBlockCarousel(next);
      }

      mountSeasonFilter(season);

      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          next.classList.remove('is-entering');
          season.dataset.filterTransition = 'false';
        });
      });
    }

    if (prefersReducedMotion || !current) {
      revealPanel();
      return;
    }

    season.dataset.filterTransition = 'true';
    current.classList.add('is-leaving');

    let completed = false;

    const finishLeave = function () {
      if (completed) {
        return;
      }

      completed = true;
      current.removeEventListener('transitionend', onFadeOut);
      revealPanel();
    };

    const onFadeOut = function (event) {
      if (event.target !== current || event.propertyName !== 'opacity') {
        return;
      }

      finishLeave();
    };

    current.addEventListener('transitionend', onFadeOut);
    window.setTimeout(finishLeave, FADE_DURATION + 80);
  }

  function initCelebreSeasonFilter(root) {
    root.querySelectorAll('.celebre-season').forEach(function (season) {
      if (season.dataset.filterInit === 'true') {
        mountSeasonFilter(season);
        return;
      }

      const filter = season.querySelector('.celebre-season-filter');

      if (!filter) {
        return;
      }

      season.dataset.filterInit = 'true';

      const buttons = filter.querySelectorAll('.celebre-season-filter__tab[data-festivity-key]');
      const panels = season.querySelectorAll('.celebre-block[data-festivity-key]');

      buttons.forEach(function (button) {
        button.addEventListener('click', function () {
          const key = button.dataset.festivityKey;

          if (!key || button.classList.contains('is-active')) {
            return;
          }

          switchFestivityPanel(season, key, buttons, panels);
        });
      });

      mountSeasonFilter(season);
    });
  }

  function initCelebreSeason(root) {
    initCelebreSeasonFilter(root);
    initAllCelebreBlockCarousels(root);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initCelebreSeason(document);
  });

  document.addEventListener('aptox:section-loaded', function (event) {
    if (event.detail?.root) {
      initCelebreSeason(event.detail.root);
    }
  });
})();
