(function () {
  const menu = document.querySelector('.menu');
  if (!menu) return;

  let isScrolled = false;
  const SCROLL_TRIGGER = 60;

  window.addEventListener('scroll', () => {
    if (window.scrollY > SCROLL_TRIGGER && !isScrolled) {
      menu.classList.add('is-scrolled');
      isScrolled = true;
    }

    if (window.scrollY <= SCROLL_TRIGGER && isScrolled) {
      menu.classList.remove('is-scrolled');
      isScrolled = false;
    }
  });
})();
