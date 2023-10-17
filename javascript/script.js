function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const offset = 76; // Adjust this value as needed
        const scrollPosition = section.offsetTop - offset;
        window.scrollTo({
            top: scrollPosition,
            behavior: 'smooth',
            duration: 50000 // Adjust the duration (in milliseconds) for slower scrolling
        });
    }
}
