document.addEventListener('DOMContentLoaded', function () {
  const DESKTOP_NAV_MQ = '(min-width: 768px) and (hover: hover) and (pointer: fine)';

  document.querySelectorAll('.recipe-tags-carousel').forEach((carousel) => {
    const track = carousel.querySelector('.tags-track');
    const prev = carousel.querySelector('.recipe-tags-nav--prev');
    const next = carousel.querySelector('.recipe-tags-nav--next');

    if (!track || !prev || !next) {
      return;
    }

    const desktopNav = window.matchMedia(DESKTOP_NAV_MQ);
    let navReady = false;

    const getMaxScroll = () => Math.max(0, track.scrollWidth - track.clientWidth);

    const hasOverflow = () => getMaxScroll() > 2;

    const getScrollStep = () => {
      const item = track.querySelector('.tag-item');
      if (!item) {
        return Math.round(track.clientWidth * 0.75);
      }

      const trackStyles = window.getComputedStyle(track);
      const gap = parseFloat(trackStyles.columnGap || trackStyles.gap) || 0;

      return item.offsetWidth + gap;
    };

    const updateNavState = () => {
      if (!navReady) {
        return;
      }

      const maxScroll = getMaxScroll();
      const overflow = hasOverflow();
      const atStart = track.scrollLeft <= 2;
      const atEnd = track.scrollLeft >= maxScroll - 2;

      prev.disabled = !overflow || atStart;
      next.disabled = !overflow || atEnd;
    };

    const scheduleNavUpdate = () => {
      requestAnimationFrame(() => {
        requestAnimationFrame(updateNavState);
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
        scheduleNavUpdate();
        if (typeof callback === 'function') {
          callback();
        }
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

    const scrollTrack = (direction) => {
      track.scrollBy({
        left: direction * getScrollStep() * 3,
        behavior: 'smooth',
      });
    };

    const enableDesktopNav = () => {
      if (navReady) {
        scheduleNavUpdate();
        return;
      }

      prev.hidden = false;
      next.hidden = false;
      prev.addEventListener('click', onPrevClick);
      next.addEventListener('click', onNextClick);
      navReady = true;
      scheduleNavUpdate();
    };

    const disableDesktopNav = () => {
      prev.hidden = true;
      next.hidden = true;
      prev.disabled = true;
      next.disabled = true;
      navReady = false;
    };

    const onPrevClick = () => scrollTrack(-1);
    const onNextClick = () => scrollTrack(1);

    const syncNavMode = () => {
      if (desktopNav.matches) {
        enableDesktopNav();
      } else {
        disableDesktopNav();
      }
    };

    track.addEventListener('scroll', updateNavState, { passive: true });
    window.addEventListener('resize', scheduleNavUpdate);
    desktopNav.addEventListener('change', syncNavMode);

    if ('ResizeObserver' in window) {
      const resizeObserver = new ResizeObserver(scheduleNavUpdate);
      resizeObserver.observe(track);

      const stickyContent = carousel.closest('#recipe-sticky-content');
      if (stickyContent) {
        resizeObserver.observe(stickyContent);
      }
    }

    const sticky = carousel.closest('#recipe-sticky');
    if (sticky) {
      const toggle = sticky.querySelector('#recipe-toggle');
      if (toggle) {
        toggle.addEventListener('click', scheduleNavUpdate);
      }

      sticky.querySelectorAll('[data-recipe-tab]').forEach((tab) => {
        tab.addEventListener('click', scheduleNavUpdate);
      });
    }

    syncNavMode();
    whenTrackReady();
  });

  document.querySelectorAll('.recipe-tag-filter__tags').forEach((track) => {
    let isDragging = false;
    let startX = 0;
    let scrollLeft = 0;
    let moved = false;

    track.addEventListener('pointerdown', (event) => {
      if (window.matchMedia('(min-width: 769px)').matches) {
        return;
      }

      // Touch uses native overflow scroll so vertical page scroll is not blocked.
      if (event.pointerType !== 'mouse' || event.button !== 0) {
        return;
      }

      isDragging = true;
      moved = false;
      startX = event.clientX;
      scrollLeft = track.scrollLeft;
      track.classList.add('is-dragging');
      track.setPointerCapture(event.pointerId);
    });

    track.addEventListener('pointermove', (event) => {
      if (!isDragging) {
        return;
      }

      const delta = event.clientX - startX;

      if (Math.abs(delta) > 4) {
        moved = true;
      }

      track.scrollLeft = scrollLeft - delta;
    });

    const endDrag = (event) => {
      if (!isDragging) {
        return;
      }

      isDragging = false;
      track.classList.remove('is-dragging');

      if (track.hasPointerCapture(event.pointerId)) {
        track.releasePointerCapture(event.pointerId);
      }
    };

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);

    track.addEventListener(
      'click',
      (event) => {
        if (moved) {
          event.preventDefault();
          event.stopImmediatePropagation();
          moved = false;
        }
      },
      true
    );
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const sticky = document.getElementById('recipe-sticky');
  const toggle = document.getElementById('recipe-toggle');

  if (!sticky || !toggle) {
    return;
  }

  const tabs = sticky.querySelectorAll('[data-recipe-tab]');
  const panels = sticky.querySelectorAll('[data-recipe-panel]');

  const activatePanel = (panelId) => {
    tabs.forEach((tab) => {
      const isActive = tab.dataset.recipeTab === panelId;

      tab.classList.toggle('is-active', isActive);
      tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
      tab.setAttribute('tabindex', isActive ? '0' : '-1');
    });

    panels.forEach((panel) => {
      const isActive = panel.dataset.recipePanel === panelId;

      panel.classList.toggle('is-active', isActive);
      panel.hidden = !isActive;
    });
  };

  const initialPanel = sticky.dataset.initialPanel || 'categories';
  activatePanel(initialPanel);

  tabs.forEach((tab) => {
    tab.addEventListener('click', (event) => {
      event.preventDefault();

      if (!sticky.classList.contains('is-open')) {
        sticky.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
      }

      activatePanel(tab.dataset.recipeTab);
    });
  });

  toggle.addEventListener('click', function () {
    const isOpen = sticky.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
});

(function () {
  const map = {
    'Prep Time': 'Preparo',
    'Cook Time': 'Cozimento',
    'Total Time': 'Tempo total',
    Servings: 'Porções',
  };

  function translateWPRM() {
    document.querySelectorAll('.wprm-recipe-details-label').forEach((el) => {
      const text = el.textContent.trim();
      if (map[text]) {
        el.textContent = map[text];
      }
    });
  }

  translateWPRM();

  const observer = new MutationObserver(translateWPRM);
  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
})();
