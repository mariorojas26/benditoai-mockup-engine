document.addEventListener("DOMContentLoaded", function () {
    function setMenuState(menu, isOpen) {
        const trigger = menu.querySelector(".benditoai-user-trigger");

        menu.classList.toggle("active", isOpen);

        if (trigger) {
            trigger.setAttribute("aria-expanded", isOpen ? "true" : "false");
            trigger.setAttribute("aria-label", isOpen ? "Cerrar menu de usuario" : "Abrir menu de usuario");
        }
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

        if (!event.target.closest(".benditoai-user-menu")) {
            closeAllMenus();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAllMenus();
        }
    });
});
