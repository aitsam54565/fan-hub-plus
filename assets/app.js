"use strict";
const root = document.documentElement;
try {
  const preferences = JSON.parse(localStorage.getItem("fh-display") || "{}");
  if (root.dataset.member !== "1") {
    if (["dark", "light"].includes(preferences.theme))
      root.dataset.theme = preferences.theme;
    if (["small", "medium", "large"].includes(preferences.font))
      root.dataset.font = preferences.font;
  }
} catch (_) {
  /* Storage is optional. */
}
for (const setting of ["theme", "font"]) {
  const select = document.getElementById("display-" + setting);
  if (!select) continue;
  select.value = root.dataset[setting];
  select.addEventListener("change", () => {
    root.dataset[setting] = select.value;
    try {
      localStorage.setItem(
        "fh-display",
        JSON.stringify({ theme: root.dataset.theme, font: root.dataset.font }),
      );
    } catch (_) {}
  });
}
const toggle = document.querySelector(".menu-toggle");
toggle?.addEventListener("click", () => {
  const open = document
    .getElementById("primary-nav")
    .classList.toggle("is-open");
  toggle.setAttribute("aria-expanded", String(open));
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape")
    document.querySelectorAll("details[open]").forEach((item) => {
      item.open = false;
    });
});
document.querySelectorAll("[data-password]").forEach((button) =>
  button.addEventListener("click", () => {
    const field = document.getElementById(button.dataset.password);
    const show = field.type === "password";
    field.type = show ? "text" : "password";
    button.textContent = show ? "Hide" : "Show";
    button.setAttribute("aria-pressed", String(show));
  }),
);
document.querySelectorAll("form[data-confirm]").forEach((form) =>
  form.addEventListener("submit", (event) => {
    if (!confirm(form.dataset.confirm)) event.preventDefault();
  }),
);
document.querySelectorAll("[data-share]").forEach((button) =>
  button.addEventListener("click", async () => {
    try {
      if (navigator.share)
        await navigator.share({ title: document.title, url: location.href });
      else if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(location.href);
        button.textContent = "Link copied";
      } else prompt("Copy this link", location.href);
    } catch (_) {
      button.textContent = "Share link";
    }
  }),
);
document.querySelectorAll("[data-media-loading]").forEach((wrapper) => {
  const media = wrapper.querySelector("iframe,video,audio,img");
  const status = wrapper.querySelector(".loading-state");
  if (!status) return;
  if (!media) {
    status.hidden = true;
    return;
  }
  const done = () => {
    status.hidden = true;
  };
  media.addEventListener("load", done);
  media.addEventListener("loadeddata", done);
  media.addEventListener("error", () => {
    status.textContent =
      "This resource could not be loaded. Try the original resource link.";
  });
  if (media.tagName === "IMG" && media.complete) done();
  if (["VIDEO", "AUDIO"].includes(media.tagName)) {
    status.textContent = "Press play to load this media.";
  }
});
const nearest = document.getElementById("find-nearby");
nearest?.addEventListener("click", () => {
  const status = document.getElementById("location-status");
  if (!navigator.geolocation) {
    status.textContent = "Location is unavailable. Use the city filter.";
    return;
  }
  status.textContent = "Waiting for location permission…";
  nearest.disabled = true;
  navigator.geolocation.getCurrentPosition(
    (position) => {
      const radians = (n) => (n * Math.PI) / 180;
      const lat = position.coords.latitude,
        lon = position.coords.longitude;
      const items = [...document.querySelectorAll("[data-event-lat]")];
      for (const item of items) {
        const latitude = Number(item.dataset.eventLat),
          longitude = Number(item.dataset.eventLon);
        const a =
          Math.sin(radians(latitude - lat) / 2) ** 2 +
          Math.cos(radians(lat)) *
            Math.cos(radians(latitude)) *
            Math.sin(radians(longitude - lon) / 2) ** 2;
        const km = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        item.dataset.distance = String(km);
        item.querySelector(".distance").textContent =
          km.toFixed(1) + " km away";
      }
      items
        .sort((a, b) => Number(a.dataset.distance) - Number(b.dataset.distance))
        .forEach((item) => item.parentElement.appendChild(item));
      status.textContent = items.length
        ? "Events with coordinates are ordered by distance. Your location stays in this browser."
        : "No listed events have coordinates yet. Try another month or city.";
      nearest.disabled = false;
    },
    () => {
      status.textContent =
        "Location was unavailable or permission was declined. You can still filter by city.";
      nearest.disabled = false;
    },
    { timeout: 10000, maximumAge: 300000, enableHighAccuracy: false },
  );
});
