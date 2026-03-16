<?php
// includes/prompts.php
if (!defined('ABSPATH')) exit;

require_once BENDIDOAI_PLUGIN_PATH . 'includes/variables.php';

function benditoai_get_prompt($producto, $formato, $color, $estilo_camiseta = '', $entorno_texto = '', $modelo_texto = '') {

    global $benditoai_colores, $benditoai_formatos;

    // 1️⃣ Definir color seleccionado
    $color_texto = isset($benditoai_colores[$color]) ? $benditoai_colores[$color] : $benditoai_colores['blanco'];

    // 2️⃣ Definir texto de dimensión según formato
    $dimension_texto = isset($benditoai_formatos[$formato]) ? $benditoai_formatos[$formato] : $benditoai_formatos['instagram'];

    // 3️⃣ Valor por defecto para entorno
    if (empty($entorno_texto)) {
        $entorno_texto = "Escena estándar en estudio con iluminación natural y fondo limpio";
    }

    // 4️⃣ Prompts por producto usando variables
    $prompts = array(
     'mug' => "Genera un mockup moderno y atractivo usando el diseño proporcionado aplicado sobre una taza $color_texto.
$modelo_texto
El entorno de la escena debe ser $entorno_texto, sin que afecte la apariencia del modelo.
$dimension_texto
El diseño cargado por el usuario debe respetarse exactamente: no modificar, no añadir elementos, ni alterar colores o formas.
Estética comercial profesional.",

    'hoodie' => "Genera un mockup moderno y atractivo usando el diseño proporcionado aplicado sobre un hoodie con capucha $color_texto.
$modelo_texto
El entorno de la escena debe ser $entorno_texto, sin que afecte la apariencia del modelo.
Iluminación natural suave en estudio profesional.
$dimension_texto
El diseño cargado por el usuario debe respetarse exactamente: no modificar, no añadir elementos, ni alterar colores o formas.
Estética comercial profesional.",

    'camiseta' => "Genera un mockup moderno y atractivo usando el diseño proporcionado aplicado sobre una camiseta $estilo_camiseta $color_texto. debe verse estampada naturalmente y real,
$modelo_texto
El entorno de la escena debe ser $entorno_texto, sin que afecte la apariencia del modelo.
$dimension_texto
El diseño cargado por el usuario debe respetarse exactamente: no modificar, no añadir elementos, ni alterar colores o formas.
La camiseta debe ser el **foco principal** de la imagen; el modelo solo debe aparecer como soporte en segundo plano, sin robar protagonismo.
Estética comercial profesional."
);

    // 5️⃣ Retornar prompt según producto, fallback a 'mug'
    return isset($prompts[$producto]) ? $prompts[$producto] : $prompts['mug'];
}