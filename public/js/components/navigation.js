htmx.onLoad(function(content) {
  // Grab selectors for period and event
  const periodSelect = document.querySelector('select.periods');
  const eventSelect = document.querySelector('select.events');

  if (periodSelect) {
    periodSelect.addEventListener('change', function (e) {
      // Set focus to the period.
      focusCard(`period-${e.target.value}`);
    });
  }

  if (eventSelect) {
    eventSelect.addEventListener('change', function (e) {
      // Set focus to the event.
      focusCard(`event-${e.target.value}`);
    });
  }
});

function focusCard(id) {
  // Scroll to card.
  const focusedCard = document.querySelector(`#${id} > .card`);
  focusedCard.scrollIntoView({
    'inline': 'center',
    'block': 'center'
  });

  // Remove highlight from any previous card.
  const highlightedCards = document.querySelectorAll('.card');
  highlightedCards.forEach(function(card) {
    card.classList.remove('focus-border');
  });

  // Add highlight to current card.
  focusedCard.classList.add('focus-border');
}
