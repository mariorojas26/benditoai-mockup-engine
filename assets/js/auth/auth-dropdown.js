document.addEventListener("DOMContentLoaded", function () {
    const mobileQuery = window.matchMedia("(max-width: 768px)");

    function updateGlobalOpenState() {
        const hasOpenMenu = !!document.querySelector(".benditoai-user-menu.active");

        document.documentElement.classList.toggle("benditoai-menu-open", hasOpenMenu);
        document.body.classList.toggle("benditoai-menu-open", hasOpenMenu);
    }

    function updateMobileMenuPosition(menu) {
        const trigger = menu.querySelector(".benditoai-user-trigger");

        if (!trigger || !mobileQuery.matches) {
            menu.style.removeProperty("--benditoai-menu-top");
            return;
        }

        const triggerRect = trigger.getBoundingClientRect();
        menu.style.setProperty("--benditoai-menu-top", Math.ceil(triggerRect.bottom) + "px");
    }

    function setMenuState(menu, isOpen) {
        const trigger = menu.querySelector(".benditoai-user-trigger");

        if (isOpen) {
            updateMobileMenuPosition(menu);
        }

        menu.classList.toggle("active", isOpen);

        if (trigger) {
            trigger.setAttribute("aria-expanded", isOpen ? "true" : "false");
            trigger.setAttribute("aria-label", isOpen ? "Cerrar menu de usuario" : "Abrir menu de usuario");
        }

        updateGlobalOpenState();
    }

    function closeAllMenus(exceptMenu) {
        document.querySelectorAll(".benditoai-user-menu.active").forEach(function (menu) {
            if (menu !== exceptMenu) {
                setMenuState(menu, false);
            }
        });
    }

    function toggleMenu(menu) {
        const isOpen = menu.classList.contains("active");

        closeAllMenus(menu);
        setMenuState(menu, !isOpen);
    }

    document.addEventListener("click", function (event) {
        const trigger = event.target.closest(".benditoai-user-trigger");

        if (trigger) {
            const menu = trigger.closest(".benditoai-user-menu");

            if (!menu) {
                return;
            }

            event.preventDefault();
            toggleMenu(menu);
            return;
        }

        if (
            event.target.classList &&
            event.target.classList.contains("benditoai-user-menu") &&
            event.target.classList.contains("active")
        ) {
            closeAllMenus();
            return;
        }

        if (!event.target.closest(".benditoai-user-menu")) {
            closeAllMenus();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAllMenus();
        }
    });

    window.addEventListener("resize", function () {
        document.querySelectorAll(".benditoai-user-menu.active").forEach(updateMobileMenuPosition);
    });
});
