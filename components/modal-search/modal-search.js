document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('openSearch');
  const modal = document.getElementById('searchModal');
  const closeBtn = modal.querySelector('.close');
  const input = modal.querySelector('#search');

  if (!openBtn || !modal) return;

  openBtn.addEventListener('click', () => {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    setTimeout(() => input.focus(), 220);
  });

  closeBtn.addEventListener('click', closeModal);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const openSeason = document.getElementById('openSeason');
  const seasonModal = document.getElementById('seasonModal');
  const seasonContent = seasonModal?.querySelector('.season-modal');
  const closeBtn = seasonModal?.querySelector('.close');

  if (!openSeason || !seasonModal || !seasonContent) return;

  openSeason.addEventListener('click', (e) => {
    e.stopPropagation();
    seasonModal.classList.add('is-open');
    seasonModal.setAttribute('aria-hidden', 'false');
  });

  seasonModal.addEventListener('click', (e) => {
    if (!seasonContent.contains(e.target)) {
      seasonModal.classList.remove('is-open');
      seasonModal.setAttribute('aria-hidden', 'true');
    }
  });

  closeBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    seasonModal.classList.remove('is-open');
    seasonModal.setAttribute('aria-hidden', 'true');
  });
});
