document.addEventListener('DOMContentLoaded', () => {
  const sliders = document.querySelectorAll('.tags-track');
  const dragThresholdPx = 8;

  sliders.forEach((slider) => {
    let activePointerId = null;
    let startX = 0;
    let startScrollLeft = 0;
    let suppressClick = false;

    const endDrag = (e) => {
      if (activePointerId === null) {
        return;
      }
      if (e && e.pointerId !== activePointerId) {
        return;
      }

      slider.classList.remove('is-dragging');
      try {
        slider.releasePointerCapture(activePointerId);
      } catch (_) {
      }
      activePointerId = null;
    };

    slider.addEventListener('pointerdown', (e) => {
      if (e.button !== 0) {
        return;
      }

      suppressClick = false;
      activePointerId = e.pointerId;
      startX = e.clientX;
      startScrollLeft = slider.scrollLeft;

      slider.classList.add('is-dragging');
      try {
        slider.setPointerCapture(e.pointerId);
      } catch (_) {
      }
    });

    slider.addEventListener('pointermove', (e) => {
      if (activePointerId === null || e.pointerId !== activePointerId) {
        return;
      }

      const dx = e.clientX - startX;
      if (Math.abs(dx) > dragThresholdPx) {
        suppressClick = true;
      }

      slider.scrollLeft = startScrollLeft - dx;
    });

    slider.addEventListener('pointerup', endDrag);
    slider.addEventListener('pointercancel', endDrag);

    slider.addEventListener(
      'click',
      (e) => {
        if (!suppressClick) {
          return;
        }
        e.preventDefault();
        e.stopPropagation();
        suppressClick = false;
      },
      true
    );
  });
});



document.addEventListener('DOMContentLoaded', function () {
  const sticky = document.getElementById('recipe-sticky');
  const toggle = document.getElementById('recipe-toggle');

  if (!sticky || !toggle) {
    console.warn('Recipe sticky: elementos não encontrados');
    return;
  }

  toggle.addEventListener('click', function () {
    const isOpen = sticky.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen);
  });
});


(function () {

  const map = {
    'Prep Time':  'Preparo',
    'Cook Time':  'Cozimento',
    'Total Time': 'Tempo total',
    'Servings':   'Porções'
  };

  function translateWPRM() {
    document.querySelectorAll(
      '.wprm-recipe-details-label'
    ).forEach(el => {
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
    subtree: true
  });

})();


(function () {

  function scrollToRecipe() {
    const target = document.getElementById('receita');
    if (!target) return false;

    target.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
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
      subtree: true
    });
  });

})();

