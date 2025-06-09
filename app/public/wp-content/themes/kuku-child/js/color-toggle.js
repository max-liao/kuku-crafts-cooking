document.addEventListener("DOMContentLoaded", () => {
  const btn = document.querySelector(".color-toggle");
  if (!btn) return;

  /* 1️⃣  Apply stored or OS-preferred scheme on load */
  const saved = localStorage.getItem("kuku-color-scheme");
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const useDark = saved ? saved === "dark" : prefersDark;

  if (useDark) document.body.classList.add("dark-mode");
  btn.textContent = useDark ? "☀️" : "🌙";

  /* 2️⃣  Toggle on click — block default <a> navigation */
  btn.addEventListener("click", (e) => {
    e.preventDefault(); // ⬅️ NEW
    const darkNow = document.body.classList.toggle("dark-mode");
    localStorage.setItem("kuku-color-scheme", darkNow ? "dark" : "light");
    btn.textContent = darkNow ? "☀️" : "🌙";
  });
});
