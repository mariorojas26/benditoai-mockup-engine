<?php

function bendidoai_register_admin_menu() {

    add_menu_page(
        'BendidoAI Dashboard',
        'BenditoAI',
        'manage_options',
        'bendidoai-dashboard',
        'bendidoai_render_dashboard',
        'dashicons-art',
        25
    );
}
add_action('admin_menu', 'bendidoai_register_admin_menu');


function bendidoai_render_dashboard() {
    ?>
    <div class="wrap">
        <h1>🚀 BenditoAI Mockup Engine</h1>
        <p>Bienvenido al panel administrativo del sistema.</p>

        <div class="bendidoai-dashboard-cards" style="display:flex; gap:20px; margin-top:20px;">

            <div class="bendidoai-card" style="flex:1; padding:20px; background:#fff; border:1px solid #ddd; border-radius:8px;">
                <h2>🎨 Crear Mockup</h2>
                <p>Genera mockups de tus productos directamente desde el dashboard.</p>
                <a href="/dashboard" class="button button-primary">Ir a Crear Mockup</a>
            </div>


        </div>

        <hr style="margin:30px 0;">

        <h2>📌 Tips de Uso</h2>
        <ul>
            <li>Sube siempre imágenes de buena resolución para mejores resultados.</li>
            <li>Elige el entorno adecuado para resaltar tu producto.</li>
            <li>Si seleccionas modelo, el foco siempre será la prenda.</li>
            <li>Prueba diferentes formatos para redes sociales.</li>
        </ul>

    </div>
    <?php
}