 
document.addEventListener('DOMContentLoaded', function () {

  const featured = document.getElementById('decoracao-slide-post');
  const items = document.querySelectorAll('.decoracao-slide-item');

  if (!featured || !items.length) return;

  const img     = featured.querySelector('img');
  const title   = featured.querySelector('.decoracao-slide-title');
  const excerpt = featured.querySelector('.decoracao-slide-excerpt');
  const link    = featured.querySelector('.decoracao-slide-cta');

  items.forEach(item => {
    item.addEventListener('click', function () {

      items.forEach(i => i.classList.remove('is-active'));
      this.classList.add('is-active');

      featured.classList.add('is-transitioning');

      setTimeout(() => {
        if (img) {
          img.src = this.dataset.image;
          img.alt = this.dataset.title;
        }
        if (title)   title.textContent = this.dataset.title;
        if (excerpt) excerpt.textContent = this.dataset.excerpt;
        if (link)    link.href = this.dataset.link;

        featured.classList.remove('is-transitioning');
      }, 300);

    });
  });

});
 
