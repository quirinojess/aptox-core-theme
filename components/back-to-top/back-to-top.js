(function () {
  'use strict';

  var button = document.querySelector('.back-to-top');

  if (!button) {
    return;
  }

  var isRecipe = button.classList.contains('back-to-top--recipe');
  var revealOffset = 320;
  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function toggleVisibility() {
    button.classList.toggle('is-visible', window.scrollY > revealOffset);
  }

  function scrollToRecipe() {
    var target = document.getElementById('receita');
    var offset = 20;

    if (!target) {
      return false;
    }

    var top =
      target.getBoundingClientRect().top + window.pageYOffset - offset;

    window.scrollTo({
      top: top,
      behavior: reducedMotion ? 'auto' : 'smooth',
    });

    return true;
  }

  function scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: reducedMotion ? 'auto' : 'smooth',
    });
  }

  function handleClick() {
    if (!isRecipe) {
      scrollToTop();
      return;
    }

    if (scrollToRecipe()) {
      return;
    }

    var observer = new MutationObserver(function () {
      if (scrollToRecipe()) {
        observer.disconnect();
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  }

  window.addEventListener('scroll', toggleVisibility, { passive: true });
  toggleVisibility();
  button.addEventListener('click', handleClick);
})();
