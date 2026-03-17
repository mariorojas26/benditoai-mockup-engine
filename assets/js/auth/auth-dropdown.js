document.addEventListener("DOMContentLoaded", function(){

    const menu = document.querySelector(".benditoai-user-menu");
    const trigger = document.querySelector(".benditoai-user-trigger");

    // Detectar si es móvil
    const isMobile = window.innerWidth <= 768;

    if(isMobile && trigger){

        trigger.addEventListener("click", function(e){
            e.stopPropagation();
            menu.classList.toggle("active");
        });

        document.addEventListener("click", function(){
            menu.classList.remove("active");
        });

    }

});