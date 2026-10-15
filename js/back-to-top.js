(() => {
  "use strict";
  function initBackToTop() {
    // Remove accidental duplicate controls left by older page-specific scripts.
    const buttons = Array.from(document.querySelectorAll("#backToTop, .back-to-top"));
    if (!buttons.length) return;
    const button = buttons[0];
    buttons.slice(1).forEach((duplicate) => duplicate.remove());
    button.id = "backToTop";
    button.classList.add("back-to-top", "ios-glass-clear");
    if (!button.hasAttribute("aria-label")) button.setAttribute("aria-label", "Back to top");
    if (button.dataset.bound === "true") return;
    button.dataset.bound = "true";

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const updateVisibility = () => button.classList.toggle("is-visible", window.scrollY > 320);
    button.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: reducedMotion.matches ? "auto" : "smooth" });
    });
    window.addEventListener("scroll", updateVisibility, { passive: true });
    updateVisibility();
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initBackToTop, { once: true });
  else initBackToTop();
})();
