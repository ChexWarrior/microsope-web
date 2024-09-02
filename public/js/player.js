htmx.onLoad(function(content) {
  const playerItems = document.querySelectorAll('.players-list > li');

  playerItems.forEach(function(element)  {
    const editBtn = element.querySelector('button');
    element.addEventListener("mouseenter", function(e) {
      editBtn.classList.remove("invisible");
    });

    element.addEventListener("mouseleave", function(e) {
      editBtn.classList.add("invisible");
    });
  });
});