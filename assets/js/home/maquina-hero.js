(function () {
    "use strict";

    function initHero(hero) {
        if (!hero || hero.dataset.maquinaFloatReady === "true") {
            return;
        }

        const cards = Array.from(hero.querySelectorAll("[data-maquina-float-card]"));

        if (!cards.length) {
            return;
        }

        hero.dataset.maquinaFloatReady = "true";

        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            return;
        }

        if (!window.gsap || !window.ScrollTrigger) {
            return;
        }

        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;

        gsap.registerPlugin(ScrollTrigger);

        const floatMoves = [
            { y: -24, x: 10, rotation: "+=3.4", rotationY: -5, duration: 5.2 },
            { y: 22, x: -12, rotation: "-=3.1", rotationY: 6, duration: 5.8 },
            { y: -26, x: -10, rotation: "-=3.5", rotationY: 5, duration: 5.5 },
            { y: 24, x: 12, rotation: "+=3.2", rotationY: -6, duration: 6.1 },
        ];

        gsap.fromTo(cards, {
            autoAlpha: 0,
            scale: 0.96,
        }, {
            autoAlpha: 1,
            scale: 1,
            duration: 1.15,
            stagger: 0.12,
            ease: "power3.out",
            delay: 0.15,
        });

        let hoveredCard = null;
        const hoverItems = [];

        function enterCard(item) {
            if (!item || hoveredCard === item.card) {
                return;
            }

            if (hoveredCard) {
                leaveCard(hoverItems.find(function (hoverItem) {
                    return hoverItem.card === hoveredCard;
                }));
            }

            hoveredCard = item.card;
            item.card.classList.add("is-hovered");

            gsap.to(item.hoverLayer, {
                scale: 1.42,
                duration: 0.5,
                ease: "power3.out",
                overwrite: "auto",
            });
        }

        function leaveCard(item) {
            if (!item) {
                return;
            }

            item.card.classList.remove("is-hovered");

            gsap.to(item.hoverLayer, {
                scale: 1,
                duration: 0.56,
                ease: "power3.out",
                overwrite: "auto",
            });

            if (hoveredCard === item.card) {
                hoveredCard = null;
            }
        }

        cards.forEach(function (card, index) {
            const move = floatMoves[index] || floatMoves[0];
            const hoverLayer = card.querySelector(".maquina-float-card__hover") || card;
            const floatingLayer = card.querySelector(".maquina-float-card__body") || hoverLayer;

            gsap.set([hoverLayer, floatingLayer], {
                transformOrigin: "50% 50%",
            });

            gsap.to(floatingLayer, {
                x: move.x,
                y: move.y,
                rotation: move.rotation,
                rotationY: move.rotationY,
                duration: move.duration,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut",
                overwrite: false,
            });

            hoverItems.push({
                card: card,
                hoverLayer: hoverLayer,
                hitLayer: floatingLayer,
            });

            card.addEventListener("pointerenter", function () {
                if (window.matchMedia("(max-width: 820px)").matches) {
                    return;
                }

                enterCard(hoverItems[index]);
            });
        });

        document.addEventListener("mousemove", function (event) {
            const isTouchWidth = window.matchMedia("(max-width: 820px)").matches;

            if (isTouchWidth) {
                if (hoveredCard) {
                    leaveCard(hoverItems.find(function (item) {
                        return item.card === hoveredCard;
                    }));
                }

                return;
            }

            const activeItem = hoverItems.find(function (item) {
                const rect = item.hitLayer.getBoundingClientRect();
                const padding = 16;

                return event.clientX >= rect.left - padding &&
                    event.clientX <= rect.right + padding &&
                    event.clientY >= rect.top - padding &&
                    event.clientY <= rect.bottom + padding;
            });

            if (activeItem) {
                enterCard(activeItem);
                return;
            }

            if (hoveredCard) {
                leaveCard(hoverItems.find(function (item) {
                    return item.card === hoveredCard;
                }));
            }
        }, { passive: true });

        document.addEventListener("mouseleave", function () {
            if (!hoveredCard) {
                return;
            }

            leaveCard(hoverItems.find(function (item) {
                return item.card === hoveredCard;
            }));
        });

        const timeline = gsap.timeline({
            defaults: {
                ease: "none",
                duration: 1,
            },
            scrollTrigger: {
                trigger: hero,
                start: "top top",
                end: "bottom top",
                scrub: 1.15,
                invalidateOnRefresh: true,
            },
        });

        timeline
            .to(cards[0], { x: -92, y: -72, rotation: -14, rotationY: -10, scale: 0.96 }, 0)
            .to(cards[1], { x: -118, y: 78, rotation: 13, rotationY: 12, scale: 1.06 }, 0)
            .to(cards[2], { x: 96, y: -82, rotation: 14, rotationY: 10, scale: 1.05 }, 0)
            .to(cards[3], { x: 124, y: 70, rotation: -13, rotationY: -12, scale: 0.96 }, 0);
    }

    function initAll() {
        document.querySelectorAll(".maquina-hero").forEach(initHero);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initAll);
    } else {
        initAll();
    }
}());
