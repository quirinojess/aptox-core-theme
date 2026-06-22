document.addEventListener('DOMContentLoaded', () => {
  const trigger = document.querySelector('[data-load-more-global]');
  if (!trigger) return;

  const gridSelector = trigger.dataset.gridSelector || '.archive-grid';
  const cardSelector = trigger.dataset.cardSelector || '.archive-card';
  const grid = document.querySelector(gridSelector);
  if (!grid) return;

  let nextUrl = trigger.dataset.nextUrl || '';
  let isLoading = false;
  const baseLabel = trigger.textContent.trim() || 'Leia mais';

  const setLoading = (loading) => {
    isLoading = loading;
    trigger.disabled = loading;
    trigger.setAttribute('aria-busy', loading ? 'true' : 'false');
    trigger.textContent = loading ? 'Carregando...' : baseLabel;
  };

  const removeTrigger = () => {
    const wrapper = trigger.closest('.archive-load-more');
    if (wrapper) {
      wrapper.remove();
      return;
    }

    trigger.remove();
  };

  const extractCardsMarkup = (doc) => {
    const incomingGrid = doc.querySelector(gridSelector);
    if (!incomingGrid) return '';

    const cards = incomingGrid.querySelectorAll(cardSelector);
    let html = '';
    cards.forEach((card) => {
      html += card.outerHTML;
    });
    return html;
  };

  trigger.addEventListener('click', async () => {
    if (isLoading || !nextUrl) return;

    setLoading(true);

    try {
      const response = await fetch(nextUrl, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        setLoading(false);
        return;
      }

      const htmlText = await response.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(htmlText, 'text/html');
      const cardsMarkup = extractCardsMarkup(doc);

      if (cardsMarkup) {
        grid.insertAdjacentHTML('beforeend', cardsMarkup);
      }

      const nextTrigger = doc.querySelector('[data-load-more-global]');
      nextUrl = nextTrigger ? (nextTrigger.dataset.nextUrl || '') : '';

      if (!nextUrl) {
        removeTrigger();
        return;
      }

      trigger.dataset.nextUrl = nextUrl;
      setLoading(false);
    } catch (error) {
      setLoading(false);
    }
  });
});
