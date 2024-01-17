htmx.onLoad(function(content) {
  // Grab selectors for period and event
  const cardSelector = document.querySelector('#card-focus');

  if (cardSelector) {
    cardSelector.addEventListener('change', function(e) {
      focusCard(e.target.value);
    });
  }
});

function focusCard(id) {
  // Scroll to card.
  const focusedCard = document.querySelector(`#${id} > .card`);
  focusedCard.scrollIntoView({
    'inline': 'center',
    'block': 'center',
  });

  // Remove highlight from any previous card.
  const highlightedCards = document.querySelectorAll('.card');
  highlightedCards.forEach(function(card) {
    card.classList.remove('focus-border');
  });

  // Add highlight to current card.
  focusedCard.classList.add('focus-border');
}
