htmx.on("htmx:beforeSwap", function (e) {
  // Allow swapping on 400 status to show errors.
  if (e.detail.xhr.status === 400) {
    e.detail.shouldSwap = true;
  }
});
