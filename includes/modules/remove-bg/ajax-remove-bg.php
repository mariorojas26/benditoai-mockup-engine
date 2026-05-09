<?php
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

    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    $uploadedfile = $_FILES['imagen'];
    $movefile = wp_handle_upload($uploadedfile, ['test_form' => false]);

    if (!$movefile || isset($movefile['error'])) {
        wp_send_json_error("Error subiendo imagen.");
    }

    $upload_dir = wp_upload_dir();
    $input_path  = $movefile['file'];
    $output_filename = 'removebg_' . time() . '.png';
    $output_path = $upload_dir['path'] . '/' . $output_filename;

    // Ruta de Python
    $python = "C:\\Users\\heisemberthr\\AppData\\Local\\Python\\pythoncore-3.14-64\\python.exe";

    // Ruta al script
    $script = __DIR__ . "/python/removebg.py";

    // Comando
    $command = "\"$python\" \"$script\" \"$input_path\" \"$output_path\"";

    // Logging
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
     * DESCONTAR CRÉDITO
     */
    benditoai_decrease_tokens($user_id, 1);

    wp_send_json_success([
        'image_url' => $url
    ]);
}

add_action('wp_ajax_benditoai_remove_background', 'benditoai_remove_background');
