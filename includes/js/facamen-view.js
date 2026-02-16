// frontend JS.
/* document.addEventListener('scroll', () => {
  document.querySelectorAll('.fade-in').forEach(eltop => {
    if (eltop.getBoundingClientRect().top < window.innerHeight * 0.9) {
      eltop.classList.add('visible');
    }
  });
}); */

document.addEventListener("DOMContentLoaded", () => {
  const elBottom = document.getElementById("elbottom");
  const elTop = document.getElementById("eltop");

  // Add visible class when page loads
  if (elBottom) elBottom.classList.add("visible");
  if (elTop) elTop.classList.add("visible");
});

// Optional: scroll fade-in for other elements
document.addEventListener("scroll", () => {
  document.querySelectorAll(".fade-in").forEach((element) => {
    if (element.getBoundingClientRect().top < window.innerHeight * 0.9) {
      element.classList.add("visible");
    }
  });
});

// Image Left
document.addEventListener("DOMContentLoaded", () => {
  const slideInImages = document.querySelectorAll(".slide-in-left");

  // Create observer
  const observer = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          observer.unobserve(entry.target); // Animate only once
        }
      });
    },
    { threshold: 0.2 }
  ); // Trigger when 20% of image is visible

  // Observe each image
  slideInImages.forEach((img) => {
    observer.observe(img);
  });
});

// Image Right
document.addEventListener("DOMContentLoaded", () => {
  const slideInImages = document.querySelectorAll(".slide-in-right");

  // Create observer
  const observer = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          observer.unobserve(entry.target); // Animate only once
        }
      });
    },
    { threshold: 0.2 }
  ); // Trigger when 20% of image is visible

  // Observe each image
  slideInImages.forEach((img) => {
    observer.observe(img);
  });
});

// Vertical scroll animations
document.addEventListener("DOMContentLoaded", () => {
  const scrollSections = document.querySelectorAll(".scroll-down, .scroll-up");

  const observer = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          observer.unobserve(entry.target); // Only animate once
        }
      });
    },
    { threshold: 0.2 }
  ); // trigger when 20% is visible

  scrollSections.forEach((section) => observer.observe(section));
});

// this is for the footer area
/* document.addEventListener("DOMContentLoaded", function () {
  const div = document.createElement("div");
  div.className = "abovefooter-container";
  div.innerHTML = "<p>This is full width!</p>";
  document.body.insertBefore(div, document.querySelector("footer")); */
/* document.body.insertAfter(div, document.querySelector("ficDiv")); */
//});
