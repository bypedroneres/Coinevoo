document.getElementById("about-link").addEventListener("click", function() {
    const aboutSection = document.querySelector(".about-section");
    aboutSection.scrollIntoView({ behavior: "smooth" });
});
