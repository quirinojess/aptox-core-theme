(function () {
  'use strict';

  var button = document.querySelector('.back-to-top');

  if (!button) {
    return;
  }

  var revealOffset = 320;

  function toggleVisibility() {
    button.classList.toggle('is-visible', window.scrollY > revealOffset);
  }

  function scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
    });
  }

  window.addEventListener('scroll', toggleVisibility, { passive: true });
  toggleVisibility();
  button.addEventListener('click', scrollToTop);
})();
