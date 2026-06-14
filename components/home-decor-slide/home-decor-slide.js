(function () {
  const AUTOPLAY_INTERVAL = 5000;
  const TRANSITION_MS = 300;

  function initHomeDecorSlide(root) {
    const featured = root.querySelector('#decoracao-slide-post');
    const items = Array.from(root.querySelectorAll('.decoracao-slide-item'));
    const slideSection = root.querySelector('.decoracao-slide') || featured?.closest('.decoracao-slide');

    if (!featured || !items.length || featured.dataset.slideInit === 'true') {
      return;
    }

    featured.dataset.slideInit = 'true';

    const img = featured.querySelector('img');
    const title = featured.querySelector('.decoracao-slide-title');
    const excerpt = featured.querySelector('.decoracao-slide-excerpt');
    const link = featured.querySelector('.decoracao-slide-cta');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let autoplayTimer = null;
    let currentIndex = -1;
    let userPaused = false;
    let isInView = false;
    let transitionTimer = null;

    const shouldAutoplay = () =>
      !prefersReducedMotion && isInView && !userPaused && !document.hidden && items.length > 0;

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

      autoplayTimer = setInterval(advanceSlide, AUTOPLAY_INTERVAL);
    };

    const refreshAutoplay = () => {
      if (shouldAutoplay()) {
        startAutoplay();
      } else {
        stopAutoplay();
      }
    };

    const activateItem = (item) => {
      if (!item) {
        return;
      }

      items.forEach((entry) => entry.classList.remove('is-active'));
      item.classList.add('is-active');

      if (transitionTimer) {
        clearTimeout(transitionTimer);
      }

      featured.classList.add('is-transitioning');

      transitionTimer = window.setTimeout(() => {
        if (img && item.dataset.image) {
          img.src = item.dataset.image;
          img.alt = item.dataset.title || '';
        }
        if (title) title.textContent = item.dataset.title || '';
        if (excerpt) excerpt.textContent = item.dataset.excerpt || '';
        if (link && item.dataset.link) link.href = item.dataset.link;

        featured.classList.remove('is-transitioning');
        transitionTimer = null;
      }, TRANSITION_MS);
    };

    const advanceSlide = () => {
      currentIndex = (currentIndex + 1) % items.length;
      activateItem(items[currentIndex]);
    };

    items.forEach((item) => {
      item.addEventListener('click', function () {
        currentIndex = items.indexOf(this);
        activateItem(this);
        userPaused = true;
        refreshAutoplay();
      });
    });

    if (slideSection) {
      slideSection.addEventListener('mouseenter', () => {
        userPaused = true;
        refreshAutoplay();
      });

      slideSection.addEventListener('mouseleave', () => {
        userPaused = false;
        refreshAutoplay();
      });

      slideSection.addEventListener('focusin', () => {
        userPaused = true;
        refreshAutoplay();
      });

      slideSection.addEventListener('focusout', (event) => {
        if (!slideSection.contains(event.relatedTarget)) {
          userPaused = false;
          refreshAutoplay();
        }
      });

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

        viewObserver.observe(slideSection.closest('.home-lazy-section') || slideSection);
      } else {
        isInView = true;
        refreshAutoplay();
      }
    } else {
      isInView = true;
      refreshAutoplay();
    }

    document.addEventListener('visibilitychange', () => {
      refreshAutoplay();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initHomeDecorSlide(document);
  });

  document.addEventListener('aptox:section-loaded', function (event) {
    if (event.detail?.root) {
      initHomeDecorSlide(event.detail.root);
    }
  });
})();
