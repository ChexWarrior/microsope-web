htmx.onLoad(function(content) {
  const board = document.querySelector('#board');
  const leftNav = document.querySelector('.nav-arrow.left');
  const rightNav = document.querySelector('.nav-arrow.right');
  let scrollInterval;

  // Scroll with click and mouse down.
  leftNav.addEventListener('dblclick', e => scrollBoard(-9999, board));
  leftNav.addEventListener('click', e => scrollBoard(-200, board));
  leftNav.addEventListener('mousedown', e => {
    scrollInterval = setInterval(scrollBoard, 100, -200, board);
  });
  rightNav.addEventListener('dblclick', e => scrollBoard(9999, board))
  rightNav.addEventListener('click', e => scrollBoard(200, board));
  rightNav.addEventListener('mousedown', e => {
    scrollInterval = setInterval(scrollBoard, 100, 200, board);
  });

  // Stop scrolling on mouse up or mouse leave.
  ['mouseup', 'mouseleave'].forEach(eventType => {
    leftNav.addEventListener(eventType, e => clearInterval(scrollInterval));
    rightNav.addEventListener(eventType, e => clearInterval(scrollInterval));
  });
});

function scrollBoard(scrollAmt, scrollElement) {
  scrollElement.scrollBy({
    'top': 0,
    'left': scrollAmt,
    'behavior': 'smooth'
  });
}
