// Sélectionner tous les boutons avec l'ID "dark-mode-toggle"
const darkModeToggles = document.querySelectorAll("#dark-mode-toggle");

// Ajouter un gestionnaire d'événement à chaque bouton
darkModeToggles.forEach(button => {
    button.addEventListener("click", function () {
        // Basculer le mode sombre sur le corps
        document.body.classList.toggle("dark-mode");

        // Modifier l'icône de chaque bouton
        const toggleIcon = button.querySelector("#toggle-icon");
        if (toggleIcon) {
            toggleIcon.textContent = document.body.classList.contains("dark-mode") ? "☀️" : "🌙";
            toggleIcon.classList.toggle("sun");
            toggleIcon.classList.toggle("moon");
        }
    });
});


// Add or remove 'scrolled' class based on scroll position
window.addEventListener("scroll", function() {
    const header = document.getElementById("main-header");
    header.classList.toggle("scrolled", window.scrollY > 50);

});

// Fade out loading screen on page load
window.addEventListener("load", function() {
    const loadingScreen = document.getElementById("loading-screen");
    loadingScreen.style.transition = "opacity 0.5s ease";
    loadingScreen.style.opacity = 0;
    setTimeout(() => loadingScreen.style.display = "none", 500);
});

// Move cursor point on logo based on mouse position
document.addEventListener("mousemove", function(event) {
    const logo = document.querySelector(".logo");
    const cursorPoint = document.querySelector(".cursor-point");
    const logoRect = logo.getBoundingClientRect();
    const offsetX = event.clientX - (logoRect.left + logoRect.width / 2);
    const offsetY = event.clientY - (logoRect.top + logoRect.height / 2);
    const maxRadius = 3;
    const distance = Math.sqrt(offsetX ** 2 + offsetY ** 2);
    const angle = Math.atan2(offsetY, offsetX);
    const limitedX = Math.cos(angle) * maxRadius;
    const limitedY = Math.sin(angle) * maxRadius;
    cursorPoint.style.transform = `translate(calc(-50% + ${distance > maxRadius ? limitedX : offsetX}px), calc(-50% + ${distance > maxRadius ? limitedY : offsetY}px))`;
});
// Toggle navbar on hamburger click
document.getElementById("hamburger").addEventListener("click", function () {
    const navbar = document.getElementById("navbar").querySelector("ul");
    navbar.classList.toggle("show");
});
