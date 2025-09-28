// project-root/lib/js/app.js

document.addEventListener("DOMContentLoaded", () => {
  const btn = document.querySelector(".back-to-top");
  if (btn) {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }
});
