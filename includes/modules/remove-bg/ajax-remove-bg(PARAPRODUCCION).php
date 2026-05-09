<!-- Codigo adaptado para DEPLOY  -->

<?php
/**
 * NOTAS DE DEPLOY PARA PRODUCCIÓN
 * 
 * Para que este script funcione correctamente en un servidor de producción, 
 * se deben seguir los siguientes pasos:
 * 
 * 1️⃣ Instalar Python en el servidor:
 *    - Debe ser Python 3.8+ (o la versión que soporte rembg)
 *    - Asegurarse que el ejecutable de Python esté en el PATH del servidor
 *      Ejemplo Linux: python3
 *      Ejemplo Windows: python.exe o python3.exe
 * 
 * 2️⃣ Instalar la librería rembg con soporte CLI:
 *    - Esto permite que el script removebg.py pueda ejecutarse desde PHP
 *    - Comando CPU (Linux/Windows):
 *        python3 -m pip install "rembg[cpu,cli]"
 *      o
 *        python -m pip install "rembg[cpu,cli]"
 * 
 * 3️⃣ Mantener el script Python dentro del plugin:
 *    - La ruta utilizada en PHP es relativa: plugin_dir_path(__FILE__) . "python/removebg.py"
 *    - En producción, crear la carpeta:
 *        includes/modules/remove-bg/python/removebg.py
 *      y colocar allí el script removebg.py
 * 
 * 4️⃣ Permisos de lectura/escritura:
 *    - WordPress debe poder leer el script Python y escribir en:
 *        wp-content/uploads
 *    - Asegurarse de que PHP tenga permisos para ejecutar comandos externos (exec)
 * 
 * 5️⃣ Rutas dinámicas:
 *    - Nunca usar rutas absolutas de Windows (C:\Users\...)
 *    - Siempre usar plugin_dir_path() y wp_upload_dir() para manejar paths
 * 
 * 6️⃣ Probar manualmente en servidor:
 *    - Subir una imagen de prueba y ejecutar desde consola:
 *        python3 removebg.py test.png output.png
 *    - Verificar que genera el PNG sin fondo en la ruta de salida
 * 
 * 7️⃣ Configuración opcional:
 *    - Si el servidor no permite ejecutar Python, considerar usar la API de Gemini
 *      como fallback para remover fondos.
 * 
 * Una vez hechos estos pasos, este archivo ajax-remove-bg estará listo para producción.
 */

if (!defined('ABSPATH')) exit;

function benditoai_remove_background() {

    if (!is_user_logged_in()) {
        wp_send_json_error("No autorizado");
    }

    $user_id = get_current_user_id();

    /**
     * VERIFICAR CRÉDITOS
     */
    if (!benditoai_user_has_tokens($user_id, 1)) {
        wp_send_json_error("No tienes créditos disponibles.");
    }

    if (!isset($_FILES['imagen'])) {
        wp_send_json_error("No se recibió imagen.");
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $uploadedfile = $_FILES['imagen'];
    $movefile = wp_handle_upload($uploadedfile, ['test_form' => false]);

    if (!$movefile || isset($movefile['error'])) {
        wp_send_json_error("Error subiendo imagen.");
    }

    $upload_dir = wp_upload_dir();
    $input_path  = $movefile['file'];
    $output_filename = 'removebg_' . time() . '.png';
    $output_path = $upload_dir['path'] . '/' . $output_filename;

    // Ruta dinámica al script dentro del plugin
    $script = plugin_dir_path(__FILE__) . "python/removebg.py";

    // Ruta a Python (debe estar instalado en el servidor y en el PATH)
    $python = "python"; // o "python3" según el servidor

    // Comando seguro
    $command = escapeshellcmd("$python \"$script\" \"$input_path\" \"$output_path\"");

    // Logging para depuración
    file_put_contents($upload_dir['path'] . '/rembg-log.txt', "Ejecutando comando: $command\n", FILE_APPEND);

    exec($command . " 2>&1", $output_log, $return_var);

    file_put_contents(
        $upload_dir['path'] . '/rembg-log.txt',
        "Return var: $return_var\nOutput: " . implode("\n", $output_log) . "\n",
        FILE_APPEND
    );

    if ($return_var !== 0 || !file_exists($output_path)) {
        wp_send_json_error("Error eliminando fondo");
    }

    $url = $upload_dir['url'] . '/' . $output_filename;

    /**
     * DESCONTAR CRÉDITO SOLO SI TODO FUNCIONÓ
     */
    benditoai_decrease_tokens($user_id, 1);

    wp_send_json_success([
        'image_url' => $url
    ]);
}

add_action('wp_ajax_benditoai_remove_background', 'benditoai_remove_background');
