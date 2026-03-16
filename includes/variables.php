<?php
// includes/variables.php
if (!defined('ABSPATH')) exit;

// Pasos para crear nuevo mockup

// Para cualquier otra característica futura (material, fondo, efecto, etc.):

// 1) Definir array en variables.php.

// 2) Agregar input en el formulario (select, radio, checkbox…).

// 3) Recibir y validar en AJAX.

// 4) Pasar a benditoai_get_prompt.

// 5) Usar en el texto del prompt.

// 6) Probar generación.


// Colores disponibles
$benditoai_colores = array(
    'negro'   => "color negro",
    'blanco'  => "color blanco",
    'azul'    => "color azul",
    'morado'  => "color morado"
);

// Formatos de imagen
$benditoai_formatos = array(
    'instagram' => "en Formato vertical 4:5 optimizado para Instagram (1080x1350).",
    'cuadrado'  => "en Formato cuadrado 1:1 optimizado para redes sociales (1080x1080)."
);

// Tamaños o materiales (opcional futuro)
$benditoai_tamanos = array(
    'pequeno'  => "tamaño pequeño",
    'mediano'  => "tamaño mediano",
    'grande'   => "tamaño grande"
);

// Estilos de camisetas
$benditoai_estilos_camiseta = array(
    'normal'   => "estilo normal",
    'oversize' => "estilo oversize",
    'croptop'  => "estilo crop top"
);

// Entornos disponibles
$benditoai_entornos = array(
    'urbano'       => "urbana moderna",
    'minimalista'  => "minimalista y limpia",
    'agresivo'     => "con estilo agresivo y dinámico",
    'celestial'     => "con estilo celestial y misterioso con luces oscuras y destellos brillantes"
);


// Modelos disponibles
$benditoai_modelo = array(
    'si' => "el mockup lo debe usar un humano realista",
    'no' => "el mockup debe estar solo, sin humanos"
);