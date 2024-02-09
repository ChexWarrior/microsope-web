htmx.onLoad(function(content) {
  // Show add/edit buttons on a card for mouse over
  const cards = document.querySelectorAll(".card");
  cards.forEach(function(element) {
    const addEditButtons = element.querySelector(".add-edit-buttons");
    element.addEventListener("mouseenter", function(e) {
      addEditButtons.classList.remove("invisible");
    });

    element.addEventListener("mouseleave", function(e) {
      addEditButtons.classList.add("invisible");
    });
  });
});