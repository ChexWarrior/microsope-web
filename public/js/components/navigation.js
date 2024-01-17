htmx.onLoad(function(content) {
  // Grab selectors for period and event
  const cardSelector = document.querySelector('#card-focus');
  const boardSelector = document.querySelector('#board');

  if (cardSelector) {
    cardSelector.addEventListener('change', function(e) {
      focusCard(e.target.value, boardSelector);
    });
  }
});

function focusCard(id, board) {
  // Remove highlight from any previous card.
  const highlightedCards = document.querySelectorAll('.card');
  highlightedCards.forEach(function(card) {
    card.classList.remove('focus-border');
  });

  if (id) {
    // Scroll to card.
    const focusedCard = document.querySelector(`#${id} > .card`);
    focusedCard.scrollIntoView({
      'inline': 'center',
      'block': 'center',
    });

    // Add highlight to current card.
    focusedCard.classList.add('focus-border');
  }
  else {
    board.scrollTo(0, 0);
  }
}
