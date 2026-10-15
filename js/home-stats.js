/**
 * Animated circular home-page statistics.
 * Uses an IntersectionObserver when available and falls back to immediate animation.
 */
(() => {
  "use strict";

  function initHomeStats() {
    const section = document.querySelector(".home-stats-section");
    if (!section || section.dataset.statsInitialized === "true") return;
    section.dataset.statsInitialized = "true";

    const counters = Array.from(section.querySelectorAll("[data-count]"));
    const rings = Array.from(section.querySelectorAll(".home-stat-ring[data-progress]"));
    if (!counters.length || !rings.length) return;

    const reducedMotion = window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    function setProgress(progress) {
      rings.forEach((ring) => {
        const target = Math.max(0, Math.min(100, Number(ring.dataset.progress) || 0));
        ring.style.setProperty("--progress", (target * progress).toFixed(3));
        ring.style.background = `conic-gradient(var(--stat-accent, #a9002f) ${target * progress}%, #eee9ec 0)`;
      });

      counters.forEach((counter) => {
        const target = Math.max(0, Number(counter.dataset.count) || 0);
        counter.textContent = (target * progress).toFixed(2);
      });
    }

    function animate() {
      if (section.dataset.statsAnimated === "true") return;
      section.dataset.statsAnimated = "true";

      if (reducedMotion) {
        setProgress(1);
        return;
      }

      const duration = 3000; // 2 seconds
      let startTime = null;

      function frame(timestamp) {
        if (startTime === null) startTime = timestamp;
        const linear = Math.min(1, (timestamp - startTime) / duration);
        const eased = 1 - Math.pow(1 - linear, 3);
        setProgress(eased);

        if (linear < 1) {
          window.requestAnimationFrame(frame);
        } else {
          // Ensure the final values exactly match the supplied percentages.
          setProgress(1);
        }
      }

      window.requestAnimationFrame(frame);
    }

    // Set the visual state to zero before starting the animation.
    setProgress(0);

    if ("IntersectionObserver" in window) {
      const observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
          animate();
          observer.disconnect();
        }
      }, { threshold: 0.1 });
      observer.observe(section);
    } else {
      animate();
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHomeStats, { once: true });
  } else {
    initHomeStats();
  }
})();
