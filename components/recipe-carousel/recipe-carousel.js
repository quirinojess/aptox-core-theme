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

      const maxScroll = track.scrollWidth - track.clientWidth;
      const atStart = track.scrollLeft <= 2;
      const atEnd = track.scrollLeft >= maxScroll - 2;

      prev.disabled = atStart;
      next.disabled = atEnd || maxScroll <= 0;
    };

    const scrollTrack = (direction) => {
      track.scrollBy({
        left: direction * getScrollStep() * 3,
        behavior: 'smooth',
      });
    };

    const enableDesktopNav = () => {
      if (navReady) {
        updateNavState();
        return;
      }

      prev.hidden = false;
      next.hidden = false;
      prev.addEventListener('click', onPrevClick);
      next.addEventListener('click', onNextClick);
      navReady = true;
      updateNavState();
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
    window.addEventListener('resize', updateNavState);
    desktopNav.addEventListener('change', syncNavMode);
    syncNavMode();
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const sticky = document.getElementById('recipe-sticky');
  const toggle = document.getElementById('recipe-toggle');

  if (!sticky || !toggle) {
    return;
  }

  toggle.addEventListener('click', function () {
    const isOpen = sticky.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen);
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
