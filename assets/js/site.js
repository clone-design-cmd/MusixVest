/* =========================================================
   MusixVest — shared site behavior
   Small, framework-free helpers used across marketing pages:
   1) scroll-reveal for [data-reveal] elements
   2) a loading-state helper for form submit buttons
   Alpine.js (loaded separately) continues to own interactive
   widgets like the FAQ accordion and dropdown menus.
   ========================================================= */

(function () {
  "use strict";

  // ---------- Scroll reveal ----------
  // Progressively reveals elements marked [data-reveal] as they
  // enter the viewport. Falls back to "everything visible" if
  // IntersectionObserver isn't available, and does nothing extra
  // for prefers-reduced-motion (CSS already removes the transition).
  function initScrollReveal() {
    var targets = document.querySelectorAll("[data-reveal]");
    if (!targets.length) return;

    if (!("IntersectionObserver" in window)) {
      targets.forEach(function (el) { el.classList.add("is-visible"); });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15, rootMargin: "0px 0px -40px 0px" }
    );

    targets.forEach(function (el, i) {
      // Small stagger so grouped cards don't all pop at once.
      el.style.transitionDelay = Math.min(i % 4, 3) * 60 + "ms";
      observer.observe(el);
    });
  }

  // ---------- Loading-state helper for buttons ----------
  // Usage: wrap the visible label in <span class="btn-label">,
  // then call MV.setLoading(buttonEl, true/false).
  // Safe to call on a button with no .btn-label span too.
  window.MV = window.MV || {};
  window.MV.setLoading = function (btn, isLoading) {
    if (!btn) return;
    if (isLoading) {
      btn.classList.add("btn-loading");
      btn.setAttribute("aria-busy", "true");
      btn.disabled = true;
    } else {
      btn.classList.remove("btn-loading");
      btn.removeAttribute("aria-busy");
      btn.disabled = false;
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initScrollReveal);
  } else {
    initScrollReveal();
  }
})();
