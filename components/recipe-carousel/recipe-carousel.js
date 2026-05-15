document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.recipe-tags-carousel').forEach((carousel) => {
    const track = carousel.querySelector('.tags-track');
    const prev = carousel.querySelector('.recipe-tags-nav--prev');
    const next = carousel.querySelector('.recipe-tags-nav--next');

    if (!track || !prev || !next) {
      return;
    }

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

    prev.addEventListener('click', () => scrollTrack(-1));
    next.addEventListener('click', () => scrollTrack(1));

    track.addEventListener('scroll', updateNavState, { passive: true });
    window.addEventListener('resize', updateNavState);
    updateNavState();
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

(function () {
  function scrollToRecipe() {
    const target = document.getElementById('receita');
    if (!target) return false;

    target.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
    return true;
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-pular-receita');
    if (!btn) return;

    if (scrollToRecipe()) return;

    const observer = new MutationObserver(() => {
      if (scrollToRecipe()) {
        observer.disconnect();
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  });
})();
