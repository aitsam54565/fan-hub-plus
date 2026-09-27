"use strict";
const carousel = document.getElementById("heroCarousel");
const list = document.getElementById("carouselList");
let moving = false;
function moveSlide(direction) {
  if (moving || !list || list.children.length < 2) return;
  moving = true;
  if (direction === "next") list.appendChild(list.firstElementChild);
  else list.prepend(list.lastElementChild);
  carousel.classList.add(direction);
  setTimeout(() => {
    carousel.classList.remove(direction);
    moving = false;
  }, 500);
}
document
  .getElementById("nextBtn")
  ?.addEventListener("click", () => moveSlide("next"));
document
  .getElementById("prevBtn")
  ?.addEventListener("click", () => moveSlide("prev"));
if (list && list.children.length === 1) carousel.classList.add("single-slide");
