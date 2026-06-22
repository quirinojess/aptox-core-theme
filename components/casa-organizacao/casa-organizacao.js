(function () {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const AUTOPLAY_INTERVAL = 5000;

  function initCasaOrganizacao(section) {
    if (section.dataset.carouselInit === 'true') {
      return;
    }

    const filterButtons = Array.from(section.querySelectorAll('[data-filter-target]'));
    const panels = Array.from(section.querySelectorAll('[data-filter-panel]'));
    const prev = section.querySelector('.grid-recipe-nav--prev');
    const next = section.querySelector('.grid-recipe-nav--next');

    if (!filterButtons.length || !panels.length || !prev || !next) {
      return;
    }

    section.dataset.carouselInit = 'true';

    let autoplayTimer = null;
    let userPaused = false;
    let isInView = false;

    const getActiveTrack = () =>
      section.querySelector('[data-filter-panel]:not([hidden])');

    const getMaxScroll = (track) => Math.max(0, track.scrollWidth - track.clientWidth);

    const hasOverflow = (track) => getMaxScroll(track) > 2;

    const shouldAutoplay = () => {
      const track = getActiveTrack();
      return (
        track &&
        !prefersReducedMotion &&
        isInView &&
        !userPaused &&
        !document.hidden &&
        hasOverflow(track)
      );
    };

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
      const track = getActiveTrack();

      if (!track || !shouldAutoplay()) {
        return;
      }

      const maxScroll = getMaxScroll(track);
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
      const track = getActiveTrack();

      if (!track) {
        prev.hidden = true;
        next.hidden = true;
        return;
      }

      const maxScroll = getMaxScroll(track);
      const overflow = hasOverflow(track);

      prev.hidden = !overflow;
      next.hidden = !overflow;
      prev.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft >= maxScroll - 2;
    };

    const scrollTrack = (direction) => {
      const track = getActiveTrack();

      if (!track) {
        return;
      }

      track.scrollBy({
        left: direction * track.clientWidth * 0.9,
        behavior: 'smooth',
      });
    };

    const whenTrackReady = (track, callback) => {
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

    const setActiveFilter = (filterSlug) => {
      filterButtons.forEach((button) => {
        const isActive = filterSlug !== 'all' && button.dataset.filterTarget === filterSlug;
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      panels.forEach((panel) => {
        panel.hidden = panel.dataset.filterPanel !== filterSlug;
      });

      const track = getActiveTrack();

      if (track) {
        track.scrollLeft = 0;
        whenTrackReady(track, () => {
          updateNavState();
          refreshAutoplay();
        });
      } else {
        updateNavState();
        refreshAutoplay();
      }
    };

    filterButtons.forEach((button) => {
      button.addEventListener('click', () => {
        setActiveFilter(button.dataset.filterTarget);
      });
    });

    panels.forEach((panel) => {
      panel.addEventListener('scroll', updateNavState, { passive: true });
    });

    prev.addEventListener('click', () => scrollTrack(-1));
    next.addEventListener('click', () => scrollTrack(1));

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

    if ('IntersectionObserver' in window) {
      const viewObserver = new IntersectionObserver(
        (entries) => {
          isInView = entries.some((entry) => entry.isIntersecting);
          refreshAutoplay();
        },
        { threshold: 0.2 }
      );

      viewObserver.observe(section);
    } else {
      isInView = true;
    }

    const defaultFilter = section.dataset.defaultFilter || filterButtons[0].dataset.filterTarget;
    setActiveFilter(defaultFilter);
  }

  function initAllCasaOrganizacao(root) {
    root.querySelectorAll('.casa-organizacao--carousel').forEach(initCasaOrganizacao);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initAllCasaOrganizacao(document);
  });
})();
