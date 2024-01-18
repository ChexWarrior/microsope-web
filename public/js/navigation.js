htmx.onLoad(function(content) {
  const board = document.querySelector('#board');
  const leftNav = document.querySelector('.nav-arrow.left');
  const rightNav = document.querySelector('.nav-arrow.right');
  let scrollInterval;

  // Start scrolling on mouse down
  leftNav.addEventListener('mousedown', e => {
    scrollInterval = scrollBoard(true, board)
  });
  leftNav.addEventListener('mouseup', e => clearInterval(scrollInterval));

  rightNav.addEventListener('mousedown', e => {
    scrollInterval = scrollBoard(false, board)
  });
  rightNav.addEventListener('mouseup', e => clearInterval(scrollInterval));
});

function scrollBoard(toLeft, scrollElement) {
  return setInterval(function() {
    scrollElement.scrollBy({
      'top': 0,
      'left': toLeft ? -200 : 200,
      'behavior': 'smooth'
    });
  }, 100);
}
