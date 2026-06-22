(function () {
  const menu = document.querySelector('.menu');
  if (!menu) return;

  let isScrolled = false;
  let scrollTicking = false;
  const SCROLL_TRIGGER = 60;

  const updateScrollState = () => {
    scrollTicking = false;

    if (window.scrollY > SCROLL_TRIGGER && !isScrolled) {
      menu.classList.add('is-scrolled');
      isScrolled = true;
    }

    if (window.scrollY <= SCROLL_TRIGGER && isScrolled) {
      menu.classList.remove('is-scrolled');
      isScrolled = false;
    }
  };

  window.addEventListener(
    'scroll',
    () => {
      if (!scrollTicking) {
        requestAnimationFrame(updateScrollState);
        scrollTicking = true;
      }
    },
    { passive: true }
  );

  const switcher = document.querySelector('.season-switcher');

  if (!switcher) {
    return;
  }

  const trigger = switcher.querySelector('.season-switcher__trigger');
  const label = switcher.querySelector('.season-switcher__label');
  const seasonMenu = switcher.querySelector('.season-switcher__menu');
  const options = switcher.querySelectorAll('.season-switcher__option');
  const canHover = window.matchMedia('(hover: hover)').matches;

  let isOpen = false;

  const positionSeasonMenu = () => {
    if (!label || !seasonMenu) {
      return;
    }

    const switcherRect = switcher.getBoundingClientRect();
    const labelRect = label.getBoundingClientRect();
    const centerX = labelRect.left + labelRect.width / 2 - switcherRect.left;

    seasonMenu.style.left = `${centerX}px`;
    seasonMenu.style.transform = 'translateX(-50%)';
    switcher.style.setProperty('--season-switcher-bridge-left', `${centerX}px`);
  };

  const setOpen = (next) => {
    isOpen = next;
    switcher.classList.toggle('is-open', isOpen);

    if (trigger) {
      trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    if (seasonMenu) {
      seasonMenu.hidden = !isOpen;
    }

    if (isOpen) {
      positionSeasonMenu();
    }
  };

  positionSeasonMenu();
  window.addEventListener('resize', positionSeasonMenu);

  trigger?.addEventListener('click', (event) => {
    event.stopPropagation();
    positionSeasonMenu();
    setOpen(!isOpen);
  });

  document.addEventListener('click', (event) => {
    if (!switcher.contains(event.target)) {
      setOpen(false);
    }
  });

  if (canHover) {
    switcher.addEventListener('mouseenter', () => {
      positionSeasonMenu();
      setOpen(true);
    });

    switcher.addEventListener('mouseleave', () => {
      setOpen(false);
    });
  }

  options.forEach((option) => {
    option.addEventListener('click', () => {
      const slug = option.dataset.season;

      if (!slug || option.classList.contains('is-active')) {
        setOpen(false);
        return;
      }

      const url = new URL(window.location.href);
      url.searchParams.set('estacao', slug);
      window.location.assign(url.toString());
    });
  });
})();
