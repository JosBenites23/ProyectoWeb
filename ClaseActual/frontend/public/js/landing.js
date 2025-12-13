document.addEventListener('DOMContentLoaded', function () {
    gsap.registerPlugin(ScrollTrigger);

    const tl = gsap.timeline({
        scrollTrigger: {
            trigger: document.body,
            start: "top top",
            end: "bottom bottom",
            scrub: 1,
        },
        ease: "power1.inOut"
    });

    // --- Simplified Animation Sequence for Debugging ---

    // 1. Initial reveal animation (first 50% of the scroll)
    tl.to("#hero-key", { scale: 1 }, 0);
    tl.to("#logo-mask", { maskSize: "25vh" }, 0);
    
    // 2. Add a pause point
    tl.to({}, { duration: 0.5 });

    // 3. Simple Cross-Fade for the final transition
    
    // Fade out the first image
    tl.to("#hero-key", { 
        opacity: 0,
        duration: 0.3
    });

    // Simultaneously, fade out the mask
    tl.to("#logo-mask", {
        opacity: 0,
        duration: 0.3
    }, "<");

    // And fade in the final section (with the red background)
    tl.to("#final-image-section", {
        opacity: 1,
        duration: 0.3
    }, "<");

});
