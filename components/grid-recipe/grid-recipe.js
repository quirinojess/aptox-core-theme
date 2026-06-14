document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.grid-recipe-home').forEach((section) => {
    const track = section.querySelector('.grid-recipe-track');
    const prev = section.querySelector('.grid-recipe-nav--prev');
    const next = section.querySelector('.grid-recipe-nav--next');

    if (!track || !prev || !next) {
      return;
    }

    const updateNavState = () => {
      const maxScroll = track.scrollWidth - track.clientWidth;
      const hasOverflow = maxScroll > 2;

      prev.hidden = !hasOverflow;
      next.hidden = !hasOverflow;
      prev.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft >= maxScroll - 2;
    };

    const scrollTrack = (direction) => {
      track.scrollBy({
        left: direction * track.clientWidth * 0.9,
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
