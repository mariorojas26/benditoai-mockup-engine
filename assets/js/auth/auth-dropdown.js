document.addEventListener("DOMContentLoaded", function () {
    const mobileQuery = window.matchMedia("(max-width: 768px)");

    function closeAllMenus() {
        document.querySelectorAll(".benditoai-user-menu.active").forEach(function (menu) {
            menu.classList.remove("active");
        });
    }

    function toggleMenu(menu) {
        const isOpen = menu.classList.contains("active");
        closeAllMenus();

        if (!isOpen) {
            menu.classList.add("active");
        }
    }

    // `pointerdown` improves response on touch devices.
    document.addEventListener("pointerdown", function (event) {
        if (!mobileQuery.matches) {
            return;
        }

        const trigger = event.target.closest(".benditoai-user-trigger");

        if (trigger) {
            const menu = trigger.closest(".benditoai-user-menu");

            if (!menu) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            toggleMenu(menu);
            return;
        }

        if (!event.target.closest(".benditoai-user-menu")) {
            closeAllMenus();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (!mobileQuery.matches) {
            return;
        }

        if (event.key === "Escape") {
            closeAllMenus();
        }
    });
});
