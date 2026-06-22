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

  function findRecipeTarget() {
    var byId = document.getElementById('receita');

    if (byId) {
      return byId;
    }

    return (
      document.querySelector('.recipe-section-heading') ||
      document.querySelector('.wprm-recipe-container') ||
      document.querySelector('.wprm-recipe')
    );
  }

  function scrollToRecipe() {
    var target = findRecipeTarget();
    var offset = 24;

    if (!target) {
      return false;
    }

    if (typeof target.scrollIntoView === 'function') {
      target.scrollIntoView({
        behavior: reducedMotion ? 'auto' : 'smooth',
        block: 'start',
      });

      return true;
    }

    var top = target.getBoundingClientRect().top + window.scrollY - offset;

    window.scrollTo({
      top: Math.max(0, top),
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

  function handleClick(event) {
    if (event) {
      event.preventDefault();
    }

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

    window.setTimeout(function () {
      observer.disconnect();
    }, 10000);
  }

  window.addEventListener('scroll', toggleVisibility, { passive: true });
  toggleVisibility();
  button.addEventListener('click', handleClick);
})();
