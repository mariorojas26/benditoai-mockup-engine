<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_generar_campana','benditoai_generar_campana');

function benditoai_generar_campana(){

    if(!is_user_logged_in()){
        wp_send_json_error("No auth");
    }

    $user_id = get_current_user_id();

    // -----------------------------
    // VALIDACIONES
    // -----------------------------

    if(empty($_POST['producto']) || empty($_POST['product_image'])){
        wp_send_json_error("Faltan datos");
    }

    $producto = sanitize_text_field($_POST['producto']);
    $product_image = $_POST['product_image'];
    $use_model = $_POST['use_model'] ?? "0";
    $model_url = $_POST['model_url'] ?? '';
    $estilo = sanitize_text_field($_POST['estilo'] ?? '');
    $tono = sanitize_text_field($_POST['tono'] ?? '');

    // -----------------------------
    // LIMPIAR BASE64 PRODUCTO
    // -----------------------------

    $product_image = preg_replace('#^data:image/\w+;base64,#i', '', $product_image);

    if(empty($product_image)){
        wp_send_json_error("Imagen inválida");
    }

    // -----------------------------
    // MODELO (OPCIONAL)
    // -----------------------------

    $model_base64 = null;

    if($use_model == "1" && !empty($model_url)){

        $response = wp_remote_get($model_url);

        if(is_wp_error($response)){
            wp_send_json_error("Error cargando modelo");
        }

        $body_img = wp_remote_retrieve_body($response);

        if(empty($body_img)){
            wp_send_json_error("Modelo vacío");
        }

        $model_base64 = base64_encode($body_img);
    }

    // -----------------------------
    // PROMPT DINÁMICO 🔥
    // -----------------------------

    $prompt = "
Create a high-converting marketing campaign.

Product: $producto
Style: $estilo
Tone: $tono

RULES:

- Product must be the main focus
- Do NOT alter product design or colors
- Professional advertising composition
- Ultra realistic
- 4K quality
";

    if($use_model == "1"){
        $prompt .= "\nIntegrate a human model naturally interacting with the product.";
    }else{
        $prompt .= "\nNo people. Focus only on product presentation.";
    }

    // -----------------------------
    // GEMINI (CONDICIONAL 🔥)
    // -----------------------------

    if($use_model == "1" && $model_base64){

        // 👉 CON MODELO (2 imágenes)
        $response = benditoai_call_gemini_multi(
            $product_image,
            $model_base64,
            $prompt
        );

    }else{

        // 👉 SOLO PRODUCTO (1 imagen)
        $response = benditoai_call_gemini(
            $product_image,
            $prompt
        );

    }

    if(is_wp_error($response)){
        wp_send_json_error("Error IA");
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    $image_base64 = null;

    if(isset($body['candidates'][0]['content']['parts'])){
        foreach($body['candidates'][0]['content']['parts'] as $part){
            if(isset($part['inlineData']['data'])){
                $image_base64 = $part['inlineData']['data'];
                break;
            }
        }
    }

    if(!$image_base64){
        wp_send_json_error("La IA no devolvió imagen");
    }

    // -----------------------------
    // GUARDAR IMAGEN
    // -----------------------------

    $image = base64_decode($image_base64);

    $upload = wp_upload_dir();
    $filename = 'campana_' . time() . '.png';
    $path = $upload['path'] . '/' . $filename;

    file_put_contents($path, $image);

    $url = $upload['url'] . '/' . $filename;

    // -----------------------------
    // TOKENS 🔥 (tu sistema)
    // -----------------------------

    benditoai_use_token(1);
    $tokens = benditoai_get_user_tokens($user_id);

    // -----------------------------
    // RESPUESTA
    // -----------------------------

    wp_send_json_success([
        'image_url' => $url,
        'tokens' => $tokens
    ]);
}