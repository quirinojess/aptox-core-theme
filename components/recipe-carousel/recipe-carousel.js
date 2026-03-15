document.addEventListener('DOMContentLoaded', () => {
  const sliders = document.querySelectorAll('.tags-track');

  sliders.forEach(slider => {
    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousedown', (e) => {
      isDown = true;
      slider.classList.add('is-dragging');
      startX = e.pageX - slider.offsetLeft;
      scrollLeft = slider.scrollLeft;
    });

    slider.addEventListener('mouseleave', () => {
      isDown = false;
      slider.classList.remove('is-dragging');
    });

    slider.addEventListener('mouseup', () => {
      isDown = false;
      slider.classList.remove('is-dragging');
    });

    slider.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - slider.offsetLeft;
      const walk = (x - startX) * 1.5;
      slider.scrollLeft = scrollLeft - walk;
    });
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

